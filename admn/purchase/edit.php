<?php
$Title = 'Dashboard | Edit Purchase'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");

if(!isset($_GET['id'])){
    redirect('/admin/purchase/');
}
if(empty($_GET['id'])){
    redirect('/admin/purchase/');
}
if(!is_numeric($_GET['id'])){
    redirect('/admin/purchase/');
}


$id = $_GET['id'];

if(isPurchaseUsed($id)){
    redirect('/admin/purchase/');
}

$stmt = $db->prepare("SELECT * FROM tbl_purchase_master WHERE purchase_master_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();

$stmt = $db->prepare("SELECT * FROM tbl_purchase_child WHERE purchase_master_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$purchaseItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<style>
select { width: 300px; } 
tr:nth-child(odd){background-color: #dfdcdc;}
</style>

<div class="admin-heading">
    <h1> Edit Purchase </h1>
</div>
<br>


<?php

$vendor_input = new Input("vendor_id","Vendor",INF,INF,"select");
$vendor_input->mysqli_pk_name = "vendor_id";
$vendor_input->mysqli_select_attribute = "vendor_name";
$vendor_input->mysqli_type = "i";
$vendor_input->mysqli_table = "tbl_vendor";
$vendor_input->fetchSelectValues();

$staff_input = new Input("staff_id","Added By",INF,INF,"select");
$staff_input->mysqli_pk_name = "staff_id";
$staff_input->mysqli_select_attribute = "email";
$staff_input->mysqli_type = "i";
$staff_input->mysqli_table = "tbl_staff";
$staff_input->noStatus = true;
$staff_input->fetchSelectValues();

$hidden_input      = new Input("hidden","hidden",INF,INF,"hidden");

$count_input      = new Input("count_input","hidden",INF,INF,"hidden");
$count_input->blank = true;

$purchase_inputs = array();

$form = new Form(
    $vendor_input,
    $count_input
);

function generatePurchaeInputs() {
    global $count_input;
    global $form;
    global $purchase_inputs;
    global $purchaseItems;
    global $purchase;

    for ($i=0; $i < (int)$count_input->value; $i++) { 

        $product_input = new Input("product_id{$i}","Product",INF,INF,"select");
        $product_input->mysqli_pk_name = "product_id";
        $product_input->mysqli_select_attribute = "product_name";
        $product_input->mysqli_table = "tbl_product";
        $product_input->mysqli_type = "i";
        $product_input->fetchSelectValues();
        $product_input->displayLabel = false;
        $product_input->id = $purchaseItems[$i]['purchase_child_id'];
        $cost_input = new Input("cost_salary{$i}","Cost",9,1,"text","i");
        $cost_input->displayLabel = false;
        $cost_input->id = $purchaseItems[$i]['purchase_child_id'];
        $selling_input = new Input("selling_price{$i}","Selling Price",9,1,"text","i");
        $selling_input->displayLabel = false;
        $selling_input->id = $purchaseItems[$i]['purchase_child_id'];
        $quantity = new Input("quantity{$i}","Quantity",9,1,"text","i");
        $quantity->displayLabel = false;
        $quantity->id = $purchaseItems[$i]['purchase_child_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $product_input->value = $purchaseItems[$i]['product_id']; 
            $cost_input->value = $purchaseItems[$i]['cost_price']; 
            $selling_input->value = $purchaseItems[$i]['selling_price']; 
            $quantity->value = $purchaseItems[$i]['quantity']; 
        }


        $purchase_child_inputs = array();

        array_push($purchase_child_inputs,$product_input,$cost_input,$selling_input,$quantity);
        array_push($form->inputs,...$purchase_child_inputs);
        array_push($purchase_inputs,$purchase_child_inputs);
    }
}

$staff_input->value = $purchase['staff_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $count_input->value = count($purchaseItems);
    $vendor_input->value = $purchase['vendor_id'];
    generatePurchaeInputs();
}

$form->submit_button_text = "Edit Purchase";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count_input->value = $_POST['count_input'];
    generatePurchaeInputs();
    if($form->validate()){
        $staff_id = $staff_input->value;
        $db->begin_transaction();
        try {
            $date = date('Y-m-d H:i:s');
            $PURCHASE_MASTER_UPDATE_SQL = "
                UPDATE tbl_purchase_master SET date_added=?,vendor_id=?,staff_id=?
                WHERE purchase_master_id = ? 
            ";
            $stmt = $db->prepare($PURCHASE_MASTER_UPDATE_SQL);
            $stmt->bind_param("siii",
                $date,
                $vendor_input->value,
                $staff_id,
                $id
            );
            $stmt->execute();
            $stmt->close();

            $master_id = $db->insert_id;

            for ($i=0; $i < count($purchase_inputs); $i++) { 
                $purchase_child_inputs = $purchase_inputs[$i];
                $PURCHASE_CHILD_UPDATE_SQL = "
                    UPDATE tbl_purchase_child SET product_id=?,cost_price=?,selling_price=?,quantity=?
                    WHERE purchase_child_id = ?
                ";
                $stmt = $db->prepare($PURCHASE_CHILD_UPDATE_SQL);
                $childParams = array();
                for ($j=0; $j < count($purchase_child_inputs) ; $j++) { 
                    array_push($childParams,$purchase_child_inputs[$j]->value);
                }
                array_push($childParams,$purchase_child_inputs[0]->id);
                $stmt->bind_param("iiiii",
                    ...$childParams
                );

                $stmt->execute();
                $stmt->close();
            }
            $db->commit();
            Messages::add("success","Purchase id '{$id}' was edited successfully!");
            redirect('/admin/purchase/');
        } catch (mysqli_sql_exception $exception) {
            echo $exception;
            $db->rollback();
        }
    }
}

echo "<form method=\"{$form->method}\">";

echo '<div class="form-row">';
    echo "<div>";
    $vendor_input->render();
    echo "</div>";
echo "</div>";
$staff_input->render();

echo "<label> Purchase Items </label><br>";

echo "<table style=\"overflow-x:auto;\">
<tr>
    <th>Product </td>
    <th>Cost</th>
    <th>Selling Price</th>
    <th>quantity</th>
</tr>";

for ($i=0; $i < count($purchase_inputs); $i++) { 
    $purchase_child_inputs = $purchase_inputs[$i];
    echo "<tr>";
    for ($j=0; $j < count($purchase_child_inputs) ; $j++) { 
        echo "<td>";
            $purchase_child_inputs[$j]->render();
        echo "</td>";
    }
    echo "</tr>";
}

echo "</table>";

global $csrf_token;
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf_token}\" />";
$count_input->render();

$hidden_input->render();

echo "<input type=\"submit\" value=\"{$form->submit_button_text}\" />";
echo "</form>";
echo "<br>";

?>

</div>


