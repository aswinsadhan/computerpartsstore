<?php
$Title = 'Dashboard | Edit Category Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");

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

?>
<h1> Edit Category Details </h1>
<br>

<?php

$name_input = new Input("category_name","Category Name",30,3);
$name_description = new Input("category_description","Category Description",INF,5,"textarea");

$form= new Form(
    $name_input,
    $name_description,
);
$form->sql_table = "tbl_category";
$form->sql_id = $id;
$form->sql_id_type = "i"; 
$form->sql_pk_name = "category_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Category {$name_input->value} was edited successfully!");
        redirect("/admin/category/");
    } 
}

$form->render();

?>
