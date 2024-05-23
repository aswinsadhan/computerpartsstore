<?php
$Title = 'Dashboard | Add Brand'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
?>

<h1> Add Brand </h1>
<br>

<?php

$name_input = new Input("brand_name","Brand Name",30,3);
$name_description = new Input("brand_description","Brand Description",INF,5,"textarea");

$form= new Form(
    $name_input,
    $name_description,
);
$form->sql_table = "tbl_brand";

$form->submit_button_text = "Add";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()){
        $form->save();
        Messages::add("success","Brand was added successfully!");
        redirect('/admin/brands/');
    }
}
$form->render();

?>

</div>


