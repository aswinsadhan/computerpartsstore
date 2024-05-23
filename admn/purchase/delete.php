<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/purchase/');
}
if(empty($_GET['id'])){
    redirect('/admin/purchase/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/purchase/');
}

$id = $_GET['id'];

if(isPurchaseUsed($id)){
    redirect('/admin/purchase/');
}

$stmt = $db->prepare("SELECT * FROM tbl_purchase_master WHERE purchase_master_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if($user['status'] == 1){
    $stmt = $db->prepare("UPDATE tbl_purchase_master SET status=0 WHERE purchase_master_id=?");
    $stmt->bind_param("i",$id);
} else {
    $date = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE tbl_purchase_master SET status=1,date_added=?  WHERE purchase_master_id=?");
    $stmt->bind_param("si",$date,$id);
}
$stmt->execute();
redirect($_SERVER['HTTP_REFERER']);
?>


