<?php
$Title = 'Dashboard | Edit Courier Details'; 
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
<h1> Edit Courier Details </h1>
<br>

<?php

$name_input          = new Input("courier_name","Courier Name",30,5);
$building_name_input = new Input("courier_building_name","Builing Name",20,5);
$street_input        = new Input("courier_street","Street/Area",20,5);
$city_input          = new Input("courier_city","City",20,5);
$state_input         = new Input("courier_state", "State");
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$pincode_input    = new Input("courier_pincode","Pincode",6,3);
$phone_input      = new Input("courier_phone","Phone",10,8);

$form = new Form(
    $name_input,
    $phone_input,
    $building_name_input,
    $street_input,
    $city_input,
    $state_input,
    $pincode_input,
);

$form->sql_table = "tbl_courier";
$form->sql_id = $id;
$form->sql_id_type = "i"; 
$form->sql_pk_name = "courier_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Courier '{$name_input->value}' was edited successfully!");
        redirect("/admin/couriers/");
    } 
}

$form->render();

?>
