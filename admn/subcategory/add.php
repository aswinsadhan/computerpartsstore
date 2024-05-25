<?php
$Title = 'Dashboard | Add Sub Category'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
?>

<h1> Add SubCategory </h1>
<br>

<?php

$name_input = new Input("subcategory_name","Subcategory Name",30,3);
$name_description = new Input("subcategory_description","Subcategory Description",INF,5,"textarea");
$category_input = new Input("category_id","Category",INF,INF,"select");
$category_input->mysqli_pk_name = "category_id";
$category_input->mysqli_select_attribute = "category_name";
$category_input->mysqli_type = "i";
$category_input->mysqli_table = "tbl_category";
$category_input->fetchSelectValues();

$form= new Form(
    $name_input,
    $category_input,
    $name_description,
);
$form->sql_table = "tbl_subcategory";

$form->submit_button_text = "Add";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()){
        $form->save();
        Messages::add("success","Subcategory '{$name_input->value}' was added successfully!");
        redirect('/admin/subcategory/');
    }
}
$form->render();

?>

</div>


