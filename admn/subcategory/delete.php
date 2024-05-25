<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/subcategory/');
}
if(empty($_GET['id'])){
    redirect('/admin/subcategory/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/subcategory/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM tbl_subcategory WHERE subcategory_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$subcategory = $stmt->get_result()->fetch_assoc();
if($subcategory['status'] == 1){
    Messages::add("warning","All products under the selected subcategory has been also disabled.");
    $stmt = $db->prepare("UPDATE tbl_subcategory SET status=0 WHERE subcategory_id=?");
    $stmt2 = $db->prepare("UPDATE tbl_product SET status=0 WHERE subcategory_id=?");
    $stmt2->bind_param("i",$id);
    $stmt2->execute();
} else {
    Messages::add("info","The products under the selected subcategory must be individualy enabled.");
    $stmt3 = $db->prepare("SELECT * FROM tbl_category WHERE category_id=?");
    $stmt3->bind_param("i",$subcategory['category_id']);
    $stmt3->execute();
    $category = $stmt3->get_result()->fetch_assoc();
    if($category['status'] != 0){
        $stmt = $db->prepare("UPDATE tbl_subcategory SET status=1 WHERE subcategory_id=?");
    }
}
$stmt->bind_param("i",$id);
$stmt->execute();

redirect($_SERVER['HTTP_REFERER']);
?>

