<?php
include("../../config/all_config.php"); 
//include("../../lib/all_lib.php"); 

if(!isset($_GET['id'])){
    redirect('/site/products/');
}
if(empty($_GET['id'])){
    redirect('/site/products/');
}
if(!is_numeric($_GET['id'])){
    redirect('/site/products/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT product_image FROM tbl_product WHERE product_id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$image = $stmt->get_result()->fetch_assoc()['product_image'];
$stmt->close();

header("Content-type: image/jpeg");
header("Content-Length: ".strlen($image));

echo $image;
die();

?>
