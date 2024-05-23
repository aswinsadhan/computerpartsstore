<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

if(!isset($_GET['id'])){
    redirect('/admin/category/');
}
if(empty($_GET['id'])){
    redirect('/admin/category/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/category/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM tbl_category WHERE category_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if($user['status'] == 1){
    Messages::add("warning","All subcategories and products under the selected category has been also disabled.");
    $stmt = $db->prepare("UPDATE tbl_category SET status=0 WHERE category_id=?");
    $stmt2 = $db->prepare("UPDATE tbl_subcategory SET status=0 WHERE category_id=?");
    $stmt2->bind_param("i",$id);
    $stmt2->execute();    
    $stmt2->close();
    $stmt3 = $db->prepare("SELECT * FROM tbl_subcategory WHERE category_id=?");
    $stmt3->bind_param("i",$id);
    $stmt3->execute();
    $subcategories = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt3->close();
    foreach ($subcategories as $key => $subcategory) {
        $subid = $subcategory['subcategory_id'];
        $stmt4 = $db->prepare("UPDATE tbl_product SET status=0 WHERE subcategory_id=?");
        $stmt4->bind_param("i",$subid);
        $stmt4->execute();    
        $stmt4->close();    
    }

} else {
    Messages::add("info","The subcategories and products under the selected category must be individualy enabled.");
    $stmt = $db->prepare("UPDATE tbl_category SET status=1 WHERE category_id=?");
}
$stmt->bind_param("i",$id);
$stmt->execute();
redirect($_SERVER['HTTP_REFERER']);
?>

