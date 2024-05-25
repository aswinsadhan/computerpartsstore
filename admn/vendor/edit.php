<?php
$Title = 'Dashboard | Edit Vendor Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");

if(!isset($_GET['id'])){
    redirect('/admin/users/');
}
if(empty($_GET['id'])){
    redirect('/admin/users/');
}

$id = $_GET['id'];

?>
<h1> Edit Vendor Details </h1>
<br>

<?php

$name_input          = new Input("vendor_name","Vendor Name",30,5);
$building_name_input = new Input("vendor_building_name","Builing Name",20,5);
$street_input        = new Input("vendor_street","Street/Area",20,5);
$city_input          = new Input("vendor_city","City",20,5);
$state_input         = new Input("vendor_state", "State");
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$pincode_input    = new Input("vendor_pincode","Pincode",6,3);
$phone_input      = new Input("vendor_phone","Phone",10,8);

$form = new Form(
    $name_input,
    $phone_input,
    $building_name_input,
    $street_input,
    $city_input,
    $state_input,
    $pincode_input,
);

$form->sql_table = "tbl_vendor";
$form->sql_id = $id;
$form->sql_id_type = "i"; 
$form->sql_pk_name = "vendor_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Vendor '{$name_input->value}' was edited successfully!");
        redirect("/admin/vendor/");
    } 
}

$form->render();

?>
