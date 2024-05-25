<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/vendor/');
}
if(empty($_GET['id'])){
    redirect('/admin/vendor/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/users/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM tbl_vendor WHERE vendor_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if($user['status'] == 1){
    $stmt = $db->prepare("UPDATE tbl_vendor SET status=0 WHERE vendor_id=?");
} else {
    $stmt = $db->prepare("UPDATE tbl_vendor SET status=1 WHERE vendor_id=?");
}
$stmt->bind_param("i",$id);
$stmt->execute();
redirect($_SERVER['HTTP_REFERER']);
?>

