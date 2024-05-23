<?php
$Title = 'Dashboard | Brands'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT * FROM tbl_brand ORDER BY status DESC");
$stmt->execute();
$brands = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="admin-heading">
    <h1> Brand Details </h1>
    <div>
    <a class="link-button" style="background: #28bd37;" href="/admin/brands/add.php"><i class="fa-solid fa-add"></i>Add Brand</a>
    </div>
</div>
<?php Messages::show(); ?>
<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</td>
    <th>Brand Name</th>
    <th>Brand Description</th>
    <th>Date Added</th>
    <th>status</th>
    <th>Actions</th>
</tr>

<?php
foreach ($brands as $brand) {
    echo "<tr class=\"".($brand['status'] == 1 ? "row-active":"row-inactive")."\">";
    echo "<td>{$brand['brand_id']}</td>";
    echo "<td>{$brand['brand_name']}</td>";
    echo "<td>{$brand['brand_description']}</td>";
    echo "<td>{$brand['date_added']}</td>";
    echo "<td>".($brand['status'] == 1 ? "active":"inactive")."</td>";
    echo "<td class=\"action-td\">
            <a class=\"icon-button\" href=\"/admin/brands/edit.php?id={$brand['brand_id']}\"><i class=\"fa-solid fa-pen\"></i></a>
            <a class=\"icon-button\" style=\"background: red\" href=\"/admin/brands/delete.php?id={$brand['brand_id']}\"><i class=\"fa-solid fa-trash\"></i></a>
         </td>";
    echo "</tr>";
}
?>

</table>
</div>

