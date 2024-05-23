<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/brands/');
}
if(empty($_GET['id'])){
    redirect('/admin/brands/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/brands/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM tbl_brand WHERE brand_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if($user['status'] == 1){
    $stmt = $db->prepare("UPDATE tbl_brand SET status=0 WHERE brand_id=?");
    $stmt2 = $db->prepare("UPDATE tbl_product SET status=0 WHERE brand_id=?");
    $stmt2->bind_param("i",$id);
    $stmt2->execute();
} else {
    $stmt = $db->prepare("UPDATE tbl_brand SET status=1 WHERE brand_id=?");
}
$stmt->bind_param("i",$id);
$stmt->execute();
redirect($_SERVER['HTTP_REFERER']);
?>

