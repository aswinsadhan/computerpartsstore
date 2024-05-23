<?php
$Title = 'Dashboard | Edit Brand Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");

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

?>
<h1> Edit Brand Details </h1>
<br>

<?php

$name_input = new Input("brand_name","Brand Name",30,3);
$name_description = new Input("brand_description","Brand Description",INF,5,"textarea");

$form= new Form(
    $name_input,
    $name_description,
);
$form->sql_table = "tbl_brand";
$form->sql_id = $id;
$form->sql_id_type = "i"; 
$form->sql_pk_name = "brand_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Brand {$name_input->value} was edited successfully!");
        redirect("/admin/brands/");
    } 
}

$form->render();

?>
