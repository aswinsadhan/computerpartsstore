<?php
$Title = 'Dashboard | New Purchase'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/dashboard_header.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
?>
<style>
select { width: 300px; } 
tr:nth-child(odd){background-color: #dfdcdc;}
</style>

<div class="admin-heading">
    <h1> New Purchase </h1>
    <div>
    <button class="link-button" onclick="addPurchaseItems(event)" style="background: #28bd37;"><i class="fa-solid fa-add"></i>Add Purchase Item</button>
    </div>
</div>
<br>


<?php

$vendor_input = new Input("vendor_id","Vendor",INF,INF,"select");
$vendor_input->mysqli_pk_name = "vendor_id";
$vendor_input->mysqli_select_attribute = "vendor_name";
$vendor_input->mysqli_type = "i";
$vendor_input->mysqli_table = "tbl_vendor";
$vendor_input->fetchSelectValues();

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

    for ($i=0; $i < (int)$count_input->value; $i++) { 

        $product_input = new Input("product_id{$i}","Product",INF,INF,"select");
        $product_input->mysqli_pk_name = "product_id";
        $product_input->mysqli_select_attribute = "product_name";
        $product_input->mysqli_table = "tbl_product";
        $product_input->mysqli_type = "i";
        $product_input->fetchSelectValues();
        $product_input->displayLabel = false;
        $cost_input = new Input("cost_salary{$i}","Cost",9,1,"text","i");
        $cost_input->displayLabel = false;
        $selling_input = new Input("selling_price{$i}","Selling Price",9,1,"text","i");
        $selling_input->displayLabel = false;
        $quantity = new Input("quantity{$i}","Quantity",9,1,"text","i");
        $quantity->displayLabel = false;

        $purchase_child_inputs = array();

        array_push($purchase_child_inputs,$product_input,$cost_input,$selling_input,$quantity);
        array_push($form->inputs,...$purchase_child_inputs);
        array_push($purchase_inputs,$purchase_child_inputs);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $count_input->value = "1";
    generatePurchaeInputs();
}

$form->submit_button_text = "Add Purchase";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count_input->value = $_POST['count_input'];
    generatePurchaeInputs();
    if($form->validate()){
        if($_SESSION['user']['type'] == 'admin'){
            array_push($hidden_input->errors,"Admin users cannot add purchases");
        } else {

            $stmt = $db->prepare("SELECT * FROM tbl_staff WHERE email = ?");
            $stmt->bind_param("s", $_SESSION['user']['email']);
            $stmt->execute();
            $staff = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $staff_id = $staff['staff_id'];


            $db->begin_transaction();
            try {
                $PURCHASE_MASTER_INSERT_SQL = "
                    INSERT INTO tbl_purchase_master (vendor_id,staff_id)
                    VALUES (?,?)
                ";
                $stmt = $db->prepare($PURCHASE_MASTER_INSERT_SQL);
                $stmt->bind_param("ii",
                    $vendor_input->value,
                    $staff['staff_id']
                );
                $stmt->execute();
                $stmt->close();

                $master_id = $db->insert_id;

                $ids = array();
                $last_insert_ids = array();

                for ($i=0; $i < count($purchase_inputs); $i++) { 
                    $purchase_child_inputs = $purchase_inputs[$i];
                    $PURCHASE_CHILD_INSERT_SQL = "
                        INSERT INTO tbl_purchase_child (purchase_master_id,product_id,cost_price,selling_price,quantity)
                        VALUES (?,?,?,?,?)
                    ";
                    $PURCHASE_CHILD_UPDATE_SQL = "
                        UPDATE tbl_purchase_child SET quantity=quantity + ?
                        WHERE purchase_child_id = ? 
                    ";
                    $childParams = array($master_id);
                    for ($j=0; $j < count($purchase_child_inputs) ; $j++) { 
                        array_push($childParams,$purchase_child_inputs[$j]->value);
                    }
                    if(array_search($childParams[1],$ids) === false){
                        $stmt = $db->prepare($PURCHASE_CHILD_INSERT_SQL);
                        array_push($ids,$childParams[1]);
                        $stmt->bind_param("iiiii",
                            ...$childParams
                        );
                        $stmt->execute();
                        array_push($last_insert_ids,$db->insert_id);
                        $stmt->close();
                    } else {
                        $stmt = $db->prepare($PURCHASE_CHILD_UPDATE_SQL);
                        $quantity = $childParams[4];
                        $index = array_search($childParams[1],$ids);
                        $purchase_child_id = $last_insert_ids[$index];
                        var_dump($purchase_child_id);
                        $stmt->bind_param("ii",$quantity,$purchase_child_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                $db->commit();

                Messages::add("success","Purchase was added successfully!");
                redirect('/admin/purchase/');
            } catch (mysqli_sql_exception $exception) {
                echo $exception;
                $db->rollback();
            }
        }
    }
}

echo "<form method=\"{$form->method}\">";

echo '<div class="form-row">';
    echo "<div>";
    $vendor_input->render();
    echo "</div>";
echo "</div>";

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


