<?php
$Title = 'Dashboard | Update Delivery Status'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin","staff","courier");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/delivery/');
}
if(empty($_GET['id'])){
    redirect('/admin/delivery/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/delivery/');
}
$id = $_GET['id'];
$stmt = $db->prepare("SELECT 
    tbl_payment.order_id,
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
    tbl_payment.date as date_added,
    tbl_courier.courier_id,
    tbl_courier.email,
    tbl_cart_master.status,
    delivery_date,
    tbl_delivery.delivery_id
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
    WHERE tbl_cart_master.cart_master_id = ? AND (
        tbl_cart_master.status = 'payment-complete'
        OR tbl_cart_master.status = 'shipped'
        OR tbl_cart_master.status = 'in-transit'
        OR tbl_cart_master.status = 'out-for-delivery'
        OR tbl_cart_master.status = 'delivered' ) 
    ORDER BY date_added DESC
");
$stmt->bind_param("s",$id);

$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

getCartItmes($orders,true);
$order = $orders[0];

$status_input = new Input("status","Delivery Status",INF,INF,"select","s");
$status_input->selectOptions = array(
    "shipped" => "Order Shipped",
    "in-transit" => "In transit",
    "out-for-delivery" => "Out for Delivery",
    "delivered" => "Delivery Complete",
);

$form = new Form(
    $status_input
);
$form->sql_table = "tbl_cart_master";
$form->sql_id = $order['cart_master_id'];
$form->sql_id_type = "s"; 
$form->sql_pk_name = "cart_master_id"; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Delivery no {$order['delivery_id']} was updated successfully!");
        redirect("/admin/delivery/");
    } 
}

?>

<h1> Update Delivery Status </h1>

<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</th>
    <th>Delivery Address</th>
    <th>Date Added</th>
    <th>Delivery Date</th>
    <th>Status</th>
    <th colspan="4">Products to be Delivered </th>
</tr>

<?php

    $productCount = count($order['products']);
    $productCount += 2;
    
    $address = "{$order['customer_fname']} {$order['customer_lname']} <br>
        ${order['customer_house_name']} <br>
        ${order['customer_street']} <br>
        ${order['customer_city']} <br>
        ${order['customer_state']} <br>
        ${order['customer_pincode']}
    ";
    echo "<tr>";
    echo "<td rowspan=\"$productCount\">{$order['delivery_id']}</td>";
    echo "<td rowspan=\"$productCount\">{$address}</td>";
    echo "<td rowspan=\"$productCount\">{$order['date_added']}</td>";
    echo "<td rowspan=\"$productCount\">{$order['delivery_date']}</td>";
    if($order['status'] == "payment-complete"){
            echo "<td rowspan=\"$productCount\">Waiting to Pick up order.</td>";
    } else if($order['status'] == "shipped") {
        echo "<td rowspan=\"$productCount\">Shipped</td>";
    } else if($order['status'] == "in-transit") {
        echo "<td rowspan=\"$productCount\">In Transit</td>";
    } else if($order['status'] == "out-for-delivery") {
        echo "<td rowspan=\"$productCount\"> Out for Delivery</td>";
    } else if($order['status'] == "delivered") {
        echo "<td rowspan=\"$productCount\"> Delivery Complete </td>";
        echo "<p> <b>Delivery Status:</b> Delivery Complete </p>";
    }
    echo "</tr>";
    echo "<tr>";
        echo "<th>Product id</th>";
        echo "<th>Product Image</th>";
        echo "<th>Product</th>";
        echo "<th>Quantity</th>";
    echo "</tr>";
    foreach($order['products'] as $j => $orderItem){
        //$productName = (strlen($purchaseItem['product_name']) > 50) ? substr($purchaseItem['product_name'],0,25).'...' : $purchaseItem['product_name'];
        echo "<tr>";
            echo "<td>{$orderItem['product_id']}</td>";
            echo '<td><img src="/site/products/image.php?id='.$orderItem['product_id'].'" loading="lazy"/></td>';
            echo "<td>{$orderItem['product_name']}</td>";
            echo "<td>{$orderItem['quantity']}</td>";
        echo "</tr>";
    }
    echo "<tr>";
    echo "</tr>";

?>
</table>
</div>
<br>
<br>
<br>
<?php
    $form->render();
?>
