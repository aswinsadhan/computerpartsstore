<?php
$Title = 'Dashboard | Products'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT 
    product_id,
    product_name,
    product_description,
    subcategory_name,
    brand_name,
    tbl_product.status as status,
    tbl_product.date_added as date_added
    FROM tbl_product 
    INNER JOIN tbl_subcategory
        ON tbl_product.subcategory_id = tbl_subcategory.subcategory_id
    INNER JOIN tbl_brand
        ON tbl_product.brand_id = tbl_brand.brand_id
    ORDER BY tbl_product.status DESC");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="admin-heading">
    <h1> Product Details </h1>
    <div>
    <a class="link-button" style="background: #28bd37;" href="/admin/products/add.php"><i class="fa-solid fa-add"></i>Add Product</a>
    </div>
</div>
<?php Messages::show(); ?>
<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</td>
    <th>Product image</th>
    <th>Name</th>
    <th>Description</th>
    <th>Subcategory</th>
    <th>Brand</th>
    <th>Date added</th>
    <th>status</th>
    <th>Actions</th>
</tr>

<?php
foreach ($products as $product) {
    echo "<tr class=\"".($product['status'] == 1 ? "row-active":"row-inactive")."\">";
    echo "<td>{$product['product_id']}</td>";
    echo '<td><img src="/site/products/image.php?id='.$product['product_id'].'" loading="lazy"/></td>';
    echo "<td>{$product['product_name']}</td>";
    echo "<td>";
    foreach (explode("\n",$product['product_description']) as $key => $value) {
        echo "<li>{$value}</li>";

    }
    echo "</td>";
    echo "<td>{$product['subcategory_name']}</td>";
    echo "<td>{$product['brand_name']}</td>";
    echo "<td>{$product['date_added']}</td>";
    echo "<td>".($product['status'] == 1 ? "active":"inactive")."</td>";
    echo "<td class=\"action-td\">
            <a class=\"icon-button\" href=\"/admin/products/edit.php?id={$product['product_id']}\"><i class=\"fa-solid fa-pen\"></i></a>
            <a class=\"icon-button\" style=\"background: red\" href=\"/admin/products/delete.php?id={$product['product_id']}\"><i class=\"fa-solid fa-trash\"></i></a>
         </td>";
    echo "</tr>";
}
?>

</table>
</div>

