<?php
$Title = 'Orders | HardwareWorld'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/header.php"); 
 
function isTabSelected($url) {
    if(strpos($_SERVER['REQUEST_URI'],$url) !== false){
        return "tab-selected";
    } else {
        return "";
    }
}

function isTabSelectedBool($url) {
    if(strpos($_SERVER['REQUEST_URI'],$url) !== false){
        return true;
    } else {
        return false;
    }
}
$stmt = NULL; 

if(isTabSelectedBool("?type=open")){
    $stmt = $db->prepare("SELECT 
        order_id, 
        tbl_customer.customer_id,
        tbl_customer.customer_fname,
        tbl_customer.customer_lname,
        tbl_customer.customer_house_name,
        tbl_customer.customer_street,
        tbl_customer.customer_city,
        tbl_customer.customer_state,
        tbl_customer.customer_pincode,
        tbl_customer.customer_phone,
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
    ");
} else {
    $stmt = $db->prepare("SELECT 
        tbl_payment.order_id, 
        tbl_payment.payment_id, 
        tbl_customer.customer_id,
        tbl_customer.customer_fname,
        tbl_customer.customer_lname,
        tbl_customer.customer_house_name,
        tbl_customer.customer_street,
        tbl_customer.customer_city,
        tbl_customer.customer_state,
        tbl_customer.customer_pincode,
        tbl_customer.customer_phone,
        tbl_cart_master.cart_master_id,
        tbl_cart_master.status,
        tbl_payment.date as date_added,
        tbl_courier.courier_name,
        tbl_courier.courier_building_name,
        tbl_courier.courier_street,
        tbl_courier.courier_city,
        tbl_courier.courier_state,
        tbl_courier.courier_pincode,
        tbl_courier.courier_phone,
        card_no,
        card_name,
        delivery_date
        FROM tbl_payment
        INNER JOIN tbl_order
            ON tbl_order.order_id = tbl_payment.order_id
        INNER JOIN tbl_cart_master
            ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
        INNER JOIN tbl_customer
            ON tbl_cart_master.customer_id = tbl_customer.customer_id
        INNER JOIN tbl_delivery
            ON tbl_payment.payment_id = tbl_delivery.payment_id
        INNER JOIN tbl_courier
            ON tbl_payment.courier_id = tbl_courier.courier_id
        INNER JOIN tbl_card
            ON tbl_payment.card_id = tbl_card.card_id
        WHERE 
            tbl_customer.email = ? AND (
            tbl_cart_master.status = 'payment-complete'
            OR tbl_cart_master.status = 'shipped'
            OR tbl_cart_master.status = 'in-transit'
            OR tbl_cart_master.status = 'delivered'
            OR tbl_cart_master.status = 'out-for-delivery' )
        ORDER BY date_added DESC
    ");
}

$stmt->bind_param("s",$_SESSION['user']['email']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


if(isTabSelectedBool("?type=open")){
    getCartItmes($orders);
} else {
    getCartItmes($orders,true);
}

$stmt->close();

$err = NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderIndex = (int) $_POST['orderindex'];
    if(count($orders[$orderIndex]['products']) < 1){
        $err = "No items in order to buy.";
    }
    $isAvailable = true;
    foreach ($orders[$orderIndex]['products'] as $product) {
        $stock = getProductStock($product['product_id']);
        if($stock < $product['quantity']){
            $err = "One of the orders you've added is not in sufficent stock right now";
            $isAvailable = false;
        }
    }
    if($isAvailable){
        redirect("/site/payment/newcard.php?orderid={$orders[$orderIndex]['order_id']}");
    }
}


?>
<link rel="stylesheet" href="/static/css/products.css"> 
<link rel="stylesheet" href="/static/css/orders.css"> 


<div class="orders-main-container">
    <h1>Your Orders</h1>
    <?php Messages::show(); ?>
    <div class="order-tabs-container">
    <a href="/site/orders/?type=paid" class="<?php echo isTabSelected("?type=paid");?>">Your Orders</a>
        <a href="/site/orders/?type=open" class="<?php echo isTabSelected("?type=open");?>">Open Orders</a>
    </div>
    <?php 
        if(isTabSelectedBool("?type=open")){
            if(!$orders){
                echo '<p class="empty-msg"> You dont have any orders yet to pay for. </p>';
            }
            foreach ($orders as $orderIndex => $order) {
                echo '<div class="order-item">';
                    echo '<div class="order-header">';
                        echo "<p><b>Order Number</b><br>{$order['order_id']}</p>";
                        echo "<p><b>Date Ordered</b><br>". date("F j, Y, g:i a",strtotime($order['date_added'])) .  "</p>";
                        echo "<p><b>SubTotal</b><br>₹{$order['subtotal']}</p>";
                        echo '<div style="display:flex;flex-direction:column;align-items: end;">';
                            echo '<form method="POST">';
                                echo "<input type=\"hidden\" name=\"orderindex\" value=\"{$orderIndex}\">";
                                echo '<input type="submit" class="link-button" style="background: #28bd37;margin-bottom:5px;" value="Proceed to Pay"></input>';
                                echo "<a  class=\"link-button\" style=\"background: red;width:100%; text-align: center;\" href=\"/site/orders/delete.php?id={$order['cart_master_id']}\">Delete Order</a>";
                                echo '<br>';
                            echo '</form>';
                            if($err){
                                echo "<p class=\"error\">{$err}</p>";
                            }
                        echo '</div>';
                    echo '</div>';
                    echo '<div class="products-list">';
                        echo "<table>";
                        foreach ($order['products'] as $product) {
                            echo "<tr>";
                                echo '<td><img  src="/site/products/image.php?id='.$product['product_id'].'" loading="lazy"/>';
                                echo "<td class=\"product-name\">{$product['product_name']}</td>";
                                echo "<td><b>Quantity: </b>{$product['quantity']}</td>";
                                echo "<td>";
                                if($product['stock']){
                                    echo "<p class=\"product-stock-text\"><b>Current stock: </b>{$product['stock']}</p>";
                                } else {
                                    echo "<p class=\"product-stock-text error\"><b class=\"error\">Out of Stock</b></p>";
                                }
                                echo "</td>";

                                if($product['stock']){
                                    echo "<td>₹{$product['total']}</td>";
                                }
                            echo "</tr>";
                        }
                        echo "</table>";
                    echo '</div>';

                echo '</div>';
            }
        } else {
            if(!$orders){
                echo '<p class="empty-msg"> You have not purchased anything yet. </p>';
            }
            foreach ($orders as $orderIndex => $order) {
                echo '<div class="order-item">';
                    echo '<div class="order-header">';
                        $card_no = str_repeat('*', strlen($order['card_no']) - 4) . substr($order['card_no'], -4);
                        echo "<p><b>Order Number</b><br>{$order['order_id']}</p>";
                        echo "<p><b>Payment Date</b><br>". date("F j, Y, g:i a",strtotime($order['date_added'])) .  "</p>";
                        echo "<p><b>Card Used</b><br> {$card_no}</p>";
                        echo "<p><b>SubTotal</b><br>₹{$order['subtotal']}</p>";
                        echo "<a  class=\"link-button\" target=\"_blank\" href=\"/site/orders/invoice.php?id={$order['payment_id']}\">View or Print Invoice</a>";
                    echo '</div>';
                    echo '<div class="delivery-container">';
                        $address = "{$order['customer_fname']} {$order['customer_lname']} <br>
                            ${order['customer_house_name']} <br>
                            ${order['customer_street']} <br>
                            ${order['customer_city']} <br>
                            ${order['customer_state']} <br>
                            ${order['customer_pincode']}
                        ";
                        $courierAddress = "{$order['courier_name']} <br>
                            ${order['courier_building_name']} <br>
                            ${order['courier_street']} <br>
                            ${order['courier_city']} <br>
                            ${order['courier_state']} <br>
                            ${order['courier_pincode']} <br>
                            Phone: ${order['courier_phone']}
                        ";
                        echo "<div>";
                        if($order['status'] == "payment-complete"){
                            echo "<p> <b>Delivery Status:</b> Processing </p>";
                        } else if($order['status'] == "shipped") {
                            echo "<p> <b>Delivery Status:</b> Shipped </p>";
                        } else if($order['status'] == "in-transit") {
                            echo "<p> <b>Delivery Status:</b> In Transit </p>";
                        } else if($order['status'] == "out-for-delivery") {
                            echo "<p> <b>Delivery Status:</b> Out for Delivery </p>";
                        } else if($order['status'] == "delivered") {
                            echo "<p> <b>Delivery Status:</b> Delivery Complete </p>";
                        }
                        if($order['status'] != "delivered") {
                            echo "<p> <b>Expected Time of Delivery : </b>". date("F j, Y, g:i a",strtotime($order['delivery_date'])). "</p>";
                        }
                        echo "</div>";
                        echo "<p> <b>Delivery Adderss</b> <br> {$address} </p>";
                        echo "<p> <b>Delivery Partner</b> <br> {$courierAddress} </p>";
                    echo '</div>';
                    echo '<div class="products-list">';
                        echo "<table>";
                        foreach ($order['products'] as $product) {
                            echo "<tr>";
                                echo '<td><img src="/site/products/image.php?id='.$product['product_id'].'" loading="lazy"/></td>';
                                echo "<td class=\"product-name\">{$product['product_name']}</td>";
                                echo "<td><b>Quantity: </b>{$product['quantity']}</td>";
                                echo "<td>₹".getOldProductPrice($product['product_id'],$order['order_id'])."</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    echo '</div>';

                echo '</div>';
            }

        } 
    ?>

</div>



