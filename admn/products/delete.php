<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/products/');
}
if(empty($_GET['id'])){
    redirect('/admin/products/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/products/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM tbl_product WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if($product['status'] == 1){
    $stmt = $db->prepare("UPDATE tbl_product SET status=0 WHERE product_id=?");
} else {

    $stmt3 = $db->prepare("SELECT * FROM tbl_subcategory WHERE subcategory_id=?");
    $stmt3->bind_param("i",$product['subcategory_id']);
    $stmt3->execute();
    $subcategory = $stmt3->get_result()->fetch_assoc();

    $stmt4 = $db->prepare("SELECT * FROM tbl_brand WHERE brand_id=?");
    $stmt4->bind_param("i",$product['brand_id']);
    $stmt4->execute();
    $brand = $stmt4->get_result()->fetch_assoc();

    if($subcategory['status'] != 0 && $brand['status'] != 0){
        $stmt = $db->prepare("UPDATE tbl_product SET status=1 WHERE product_id=?");
    }

}
$stmt->bind_param("i",$id);
$stmt->execute();
redirect($_SERVER['HTTP_REFERER']);
?>

