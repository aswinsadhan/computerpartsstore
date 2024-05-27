<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 

check_auth_redirect_if_not();
check_role_or_redirect("customer");

if(!isset($_GET['id'])){
    redirect('/site/orders/');
}
if(empty($_GET['id'])){
    redirect('/site/orders/');
}
if(!is_numeric($_GET['id'])){
    redirect('/site/orders/');
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
        tbl_customer.email = ? AND tbl_payment.payment_id = ? AND (
        tbl_cart_master.status = 'payment-complete'
        OR tbl_cart_master.status = 'shipped'
        OR tbl_cart_master.status = 'in-transit'
        OR tbl_cart_master.status = 'delivered'
        OR tbl_cart_master.status = 'out-for-delivery' )
    ORDER BY date_added DESC
");

$stmt->bind_param("si",$_SESSION['user']['email'],$id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
getCartItmes($orders,true);
$order = $orders[0];

foreach ($order['products'] as $i => $product) {
    unset($order['products'][$i]['product_image']);
}
ob_end_clean();

$stmt->close();

header('Content-type: application/json');
echo json_encode($order);
?>
