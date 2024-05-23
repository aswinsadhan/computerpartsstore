<?php
$Title = 'Dashboard | Edit Customer Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin");

if(!isset($_GET['id'])){
    redirect('/admin/users/');
}
if(empty($_GET['id'])){
    redirect('/admin/users/');
}

$id = $_GET['id'];
?>
<h1> Edit Customer Details </h1>
<br>

<?php


$fname_input      = new Input("customer_fname","First Name",15,2);
$lname_input      = new Input("customer_lname","Last Name",15,2);

$house_name_input = new Input("customer_house_name","House Name",20,5);
$street_input     = new Input("customer_street","Street/Area",20,5);
$city_input       = new Input("customer_city","City",20,5);
$state_input      = new Input("customer_state", "State");
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$pincode_input    = new Input("customer_pincode","Pincode",6,3);
$phone_input      = new Input("customer_phone","Phone",10,8);

$form = new Form(
    $fname_input,
    $lname_input,
    $phone_input,
    $house_name_input,
    $street_input,
    $city_input,
    $state_input,
    $pincode_input,
);

$form->sql_table = "tbl_customer";
$form->sql_id = $id;
$form->sql_id_type = "i"; 
$form->sql_pk_name = "customer_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Customer '{$fname_input->value}' was edited successfully!");
        redirect("/admin/customers/");
    } 
}

$form->render();

?>
