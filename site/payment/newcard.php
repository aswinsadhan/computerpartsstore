<?php
$Title = 'Payment | Hardware World'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/header.php"); 

if(!isset($_GET['orderid'])){
    redirect('/site/orders/?type=open');
}
if(empty($_GET['orderid'])){
    redirect('/site/orders/?type=open');
}
if(!is_numeric($_GET['orderid'])){
    redirect('/site/orders/?type=open');
}
$id = $_GET['orderid'];

$stmt = $db->prepare("SELECT 
    order_id, 
    tbl_customer.customer_id,
    tbl_cart_master.cart_master_id,
    tbl_order.date as date_added
    FROM tbl_order
    INNER JOIN tbl_cart_master
        ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    WHERE 
        tbl_cart_master.status = 'ordered'
        AND tbl_customer.email = ?
        AND tbl_order.order_id = ?
");


$stmt->bind_param("si",$_SESSION['user']['email'],$id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

getCartItmes($orders);


if(!$orders){
    redirect('/site/orders/?type=open');
}
$order = $orders[0];

if(count($order['products']) < 1){
    redirect('/site/orders/?type=open');
}
foreach ($order['products'] as $product) {
    $stock = getProductStock($product['product_id']);
    if($stock < $product['quantity']){
        redirect('/site/orders/?type=open');
    }
}



$stmt = $db->prepare("SELECT * FROM tbl_card WHERE customer_id = ?");
$stmt->bind_param("i",$order['customer_id']);
$stmt->execute();
$cards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if($cards){
    foreach ($cards as $key => $value) {
        $cardcvv_input = new Input("card_cvv","CVV/CVC",3,3,"password","i");
        $cardcvv_input->displayLabel = false;
        $form = new Form(
            $cardcvv_input
        );
        $form->submit_button_text = "Proceed to Pay";
        $cards[$key]['form'] = $form; 
    }
}

if(!$order){
    redirect('/site/orders/?type=open');
}

$cardno_input = new Input("card_no","Card Number",16,16,"text","i");
$card_name_input = new Input("card_name","Card Holder Name",30,5,"text");
$cardexp_year_input = new Input("card_exp_year","Year",4,4,"text","i");
    $cardexp_year_input->displayLabel = false;
$cardexp_month_input = new Input("card_exp_month","Month",2,1,"text","i");
    $cardexp_month_input->displayLabel = false;
$cardcvv_input = new Input("card_cvv","CVV/CVC",3,3,"password","i");
$hidden_input      = new Input("hidden","hidden",INF,INF,"hidden");

$form = new Form(
    $cardno_input,
    $card_name_input,
    $cardcvv_input,
    $cardexp_month_input,
    $cardexp_year_input,
);

$form->submit_button_text = "Proceed to Pay";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['oldcard'])){
        $card = $cards[(int)$_POST['oldcard']];
        $form = $card['form'];
        if($form->validate()){
            $db->begin_transaction();

            try {

                $stmt = $db->prepare("SELECT * FROM tbl_courier
                    INNER JOIN tbl_login  
                        ON tbl_courier.email = tbl_login.email
                    WHERE tbl_login.status = 1  
                    ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $courier = $stmt->get_result()->fetch_assoc();

            $stmt = $db->prepare("INSERT INTO tbl_payment
                    (card_id,order_id,courier_id)
                    VALUES (?,?,?)");
            $stmt->bind_param("iii",
                $card['card_id'],
                $order['order_id'],
                $courier['courier_id']
            );
            $stmt->execute();
            $payment_id = $db->insert_id;
            $stmt->close();

            $date = date("Y-m-d");
            $future = $date . " + " . rand(5,10) . " days " . rand(8,19) . " hours ";
            $delivery_date = date("Y-m-d H:i:s",strtotime($future));

            $stmt = $db->prepare("INSERT INTO tbl_delivery
                    (payment_id,delivery_date)
                    VALUES (?,?)");
            $stmt->bind_param("is",
                $payment_id,
                $delivery_date
            );
            $stmt->execute();

            $stmt = $db->prepare("UPDATE tbl_cart_master SET status='payment-complete' WHERE cart_master_id=?");
            $stmt->bind_param("i",$order['cart_master_id']);
            $stmt->execute();
            $stmt->close();

            $db->commit();

            Messages::add("success","Payment completed successfully!");
            redirect("/site/orders/?type=paid");

            } catch (Exception $exception) {
                echo $exception;
                $db->rollback();
            }

        }
    } else if($form->validate()){
        $cardexp_month_input_int = (int)  $cardexp_month_input->value;
        $date_year = (int) date("Y");

        $card = NULL;
        if(!preg_match('/\s/',$cardno_input->value)){
            $stmt = $db->prepare("SELECT * FROM tbl_card WHERE card_no = ? AND customer_id = ?");
            $stmt->bind_param("si",$cardno_input->value,$order['customer_id']);
            $stmt->execute();
            $card = $stmt->get_result()->fetch_assoc();
        }
        if (preg_match('/\s/',$cardno_input->value)) {
            array_push($cardno_input->errors,"No whitespaces allowed in Card Number");
        } else if ($cardexp_month_input_int < 1 || $cardexp_month_input_int > 12 ) {
            array_push($cardexp_month_input->errors,"Please enter a valid month");
        } else if(((int)$cardexp_year_input->value) < $date_year){
            array_push($cardexp_year_input->errors,"Please enter a valid expiration year");
        } else if($card){
            array_push($cardno_input->errors,"A card with this Card Number already exists in this account");
       } else {

            $db->begin_transaction();

            try {

            $date_expiry = $cardexp_year_input->value . $cardexp_month_input->value . "01"; 
            $stmt = $db->prepare("
                    INSERT INTO tbl_card
                    (customer_id,card_name,card_no,card_expiry)
                    VALUES (?,?,?,?)");

            $stmt->bind_param("isss",
                $order['customer_id'],
                $card_name_input->value,
                $cardno_input->value,
                $date_expiry
            );
            $stmt->execute();
            $card_id = $db->insert_id;
            $stmt->close();

            $stmt = $db->prepare("SELECT * FROM tbl_courier ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $courier = $stmt->get_result()->fetch_assoc();

            $stmt = $db->prepare("INSERT INTO tbl_payment
                    (card_id,order_id,courier_id)
                    VALUES (?,?,?)");
            $stmt->bind_param("iii",
                $card_id,
                $order['order_id'],
                $courier['courier_id']
            );
            $stmt->execute();
            $payment_id = $db->insert_id;
            $stmt->close();

            $date = date("Y-m-d");
            $future = $date . " + " . rand(5,10) . " days " . rand(8,19) . " hours ";
            $delivery_date = date("Y-m-d H:i:s",strtotime($future));

            $stmt = $db->prepare("INSERT INTO tbl_delivery
                    (payment_id,delivery_date)
                    VALUES (?,?)");
            $stmt->bind_param("is",
                $payment_id,
                $delivery_date
            );
            $stmt->execute();


            $stmt = $db->prepare("UPDATE tbl_cart_master SET status='payment-complete' WHERE cart_master_id=?");
            $stmt->bind_param("i",$order['cart_master_id']);
            $stmt->execute();
            $stmt->close();

            $db->commit();
            redirect("/site/orders/?type=paid");

            } catch (Exception $exception) {
                echo $exception;
                $db->rollback();
            }
        }
    }
}
?>
<link rel="stylesheet" href="/static/css/newcard.css"> 



<div class="payment-main-container">
<div class="payment-container">
<div class="payment-header">
    <h1> Enter your card details  </h1>
    <p><b>Amount </b><br>₹<?php echo "{$order['subtotal']}"; ?></p>
</div>
<br>
<div class="payment-content">
<div class="card-details">
    <?php
    echo "<form method=\"{$form->method}\">";

    $cardno_input->render();
    $card_name_input->render();

    echo "<br><br><label> Expiration Date </label><br>";

    echo '<div class="form-row">';
        echo "<div>";
        $cardexp_month_input->render();
        echo "</div>";
        echo "<div>";
        $cardexp_year_input->render();
        echo "</div>";
    echo "</div>";

    $cardcvv_input->render();
    $hidden_input->render();
    global $csrf_token;

    echo "<input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf_token}\" />";
    echo "<input type=\"submit\" value=\"{$form->submit_button_text}\" />";
    echo "</form>";
    echo "<br>";
    ?>
</div>

<?php
    if($cards){
        echo '<div class="card-list">';
            echo '<h2> Cards you\'ve used before </h2>';
            foreach ($cards as $cardIndex => $card) {
                $card_no = str_repeat('*', strlen($card['card_no']) - 4) . substr($card['card_no'], -4);
                echo '<div class="card-item">';
                    echo '<div class="card-details">';
                        echo "<p class=\"card-no\">{$card_no}</p>";
                        echo "<p class=\"card-no\">{$card['card_name']}</p>";
                        echo '<br>';
                        echo "<p >Expiry: {$card['card_expiry']}</p>";
                    echo '</div>';
                    echo "<form method=\"{$form->method}\">";
                    $card['form']->inputs[0]->render();
                    global $csrf_token;
                    echo "<input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf_token}\" />";
                    echo "<input type=\"hidden\" name=\"oldcard\" value=\"{$cardIndex}\" />";
                    echo "<input class=\"card-submit\" type=\"submit\" value=\"{$form->submit_button_text}\" />";
                    echo "</form>";

                echo '</div>';
            }
        echo '</div>';

    }
?>

</div>

</div>

</div>
