<?php
$Title = 'Dashboard | Add Product'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
?>

<h1> Add Product </h1>
<br>

<?php

$name_input          = new Input("product_name","Product Name",100,5);
$product_description = new Input("product_description","Product Description",INF,5,"textarea");
$subcategory_input = new Input("subcategory_id","Sub Category",INF,INF,"select");
$subcategory_input->mysqli_pk_name = "subcategory_id";
$subcategory_input->mysqli_select_attribute = "subcategory_name";
$subcategory_input->mysqli_table = "tbl_subcategory";
$subcategory_input->mysqli_type = "i";
$subcategory_input->fetchSelectValues();

$brand_input = new Input("brand_id","Brand",INF,INF,"select");
$brand_input->mysqli_pk_name = "brand_id";
$brand_input->mysqli_select_attribute = "brand_name";
$brand_input->mysqli_table = "tbl_brand";
$brand_input->mysqli_type = "i";
$brand_input->fetchSelectValues();
$product_image_input = new Input("product_image","Product Image",INF,INF,"file");
$product_image_input->blank = true;

$form= new Form(
    $name_input,
    $product_description,
    $brand_input,
    $subcategory_input,
    $product_image_input,

);

$form->submit_button_text = "Add";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($form->validate()){

        $db->begin_transaction();

        try {

            $PRODUCT_INSERT_SQL = "
                INSERT INTO tbl_product (
                    product_name,
                    product_description,
                    subcategory_id,
                    brand_id,
                    product_image
                ) VALUES (?,?,?,?,?)
            ";
            $stmt = $db->prepare($PRODUCT_INSERT_SQL);
            $image = file_get_contents($_FILES[$product_image_input->name]['tmp_name']);
            $stmt->bind_param("ssiis",
                $name_input->value,
                $product_description->value,
                $subcategory_input->value,
                $brand_input->value,
                $image
            );
            $stmt->execute();
            $stmt->close();

            $db->commit();
            Messages::add("success","New Product was added successfully!");
            redirect('/admin/products/');
        } catch (mysqli_sql_exception $exception) {
            echo $exception;
            $db->rollback();
        }

    }
}

echo "<form method=\"{$form->method}\" enctype=\"multipart/form-data\">";
$name_input->render();
$product_description->render();
$subcategory_input->render();
$brand_input->render();
$product_image_input->render();
global $csrf_token;
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf_token}\" />";
echo "<input type=\"submit\" value=\"{$form->submit_button_text}\" />";
echo "</form>";
echo "<br>";

?>

</div>


