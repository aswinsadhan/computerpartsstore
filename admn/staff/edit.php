<?php
$Title = 'Dashboard | Edit Staff Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin");

if(!isset($_GET['id'])){
    redirect('/admin/staff/');
}
if(empty($_GET['id'])){
    redirect('/admin/staff/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/staff/');
}

$id = $_GET['id'];

?>

<h1> Edit Staff Details </h1>
<br>

<?php
$fname_input      = new Input("staff_fname","First Name",15,2);
$lname_input      = new Input("staff_lname","Last Name",15,2);
$phone_input      = new Input("staff_phone","Phone",10,8);
$house_name_input = new Input("staff_house_name","House Name",20,5);
$street_input     = new Input("staff_street","Street/Area",20,5);
$city_input       = new Input("staff_city","City",20,5);
$state_input      = new Input("staff_state", "State");
$pincode_input    = new Input("staff_pincode","Pincode",6,3);
$state_input->type = "select";
$state_input->selectOptions = INDIAN_STATES;
$salary_input     = new Input("staff_salary","Salary",9,4,"text","i");

$form = new Form(
    $fname_input,
    $lname_input,
    $phone_input,
    $house_name_input,
    $street_input,
    $city_input,
    $state_input,
    $pincode_input,
    $salary_input
);

$form->sql_table = "tbl_staff";
$form->sql_id = $id;
$form->sql_id_type = "i"; 
$form->sql_pk_name = "staff_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","Staff '{$fname_input->value}' was edited successfully!");
        redirect("/admin/staff/");
    } 
}

$form->render();

?>
