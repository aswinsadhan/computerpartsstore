<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("customer","admin");

if(!isset($_GET['id'])){
    redirect('/site/cart/view.php');
}
if(empty($_GET['id'])){
    redirect('/site/cart/view.php');
}
if(!is_numeric($_GET['id'])){
    redirect('/site/cart/view.php');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT tbl_cart_master.cart_master_id FROM tbl_cart_master
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    WHERE status = 'deleted' 
        AND tbl_customer.email = ?
");

$stmt->bind_param("s", $_SESSION['user']['email']);
$stmt->execute();
$cart_master = $stmt->get_result()->fetch_assoc();

$stmt->close();

$stmt = $db->prepare("UPDATE tbl_cart_child SET cart_master_id= ? WHERE cart_child_id=?");
$stmt->bind_param("ii", $cart_master['cart_master_id'],$id);
$stmt->execute();
$stmt->close();

redirect($_SERVER['HTTP_REFERER']);
?>

