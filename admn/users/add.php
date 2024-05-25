<?php
$Title = 'Dashboard | Add Users'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin");
?>

<h1> Add User</h1>
<br>
<br>

<?php

$email_input = new Input("email","Email",50,5,"email");
$pass_input = new Input("password","Password",INF,8,"password");
$usertype_input = new Input("type","User type");
$usertype_input->type = "select";
$usertype_input->selectOptions = array(
    "admin" => "admin"
);

$userstatus_input = new Input("status","User Status");
$userstatus_input->type = "select";
$userstatus_input->mysqli_type = "i";
$userstatus_input->selectOptions = array(
    1 => "active", 
    0 => "inactive",
);

$form = new Form($email_input,$pass_input,$usertype_input,$userstatus_input);
$form->sql_table = "tbl_login";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()) {
        $form->save();
        Messages::add("success","User was added successfully!");
        redirect("/admin/users/");
    } 
}

$form->render();

?>



