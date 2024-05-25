<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/users/');
}
if(empty($_GET['id'])){
    redirect('/admin/users/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM tbl_login WHERE email = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if($user['status'] == 1){
    $stmt = $db->prepare("UPDATE tbl_login SET status=0 WHERE email=?");
} else {
    $stmt = $db->prepare("UPDATE tbl_login SET status=1 WHERE email=?");
}
$stmt->bind_param("s",$id);
$stmt->execute();
redirect($_SERVER['HTTP_REFERER']);
?>

