<?php
$Title = 'Dashboard | Subcategory Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT 
    subcategory_id,
    subcategory_description,
    subcategory_name,
    category_name,
    tbl_subcategory.date_added,
    tbl_subcategory.status as status
    FROM tbl_subcategory
    INNER JOIN tbl_category
    ON tbl_category.category_id = tbl_subcategory.category_id
    ORDER BY tbl_category.category_id,tbl_subcategory.status DESC");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="admin-heading">
    <h1> Subcategory Details </h1>
    <div>
    <a class="link-button" style="background: #28bd37;" href="/admin/subcategory/add.php"><i class="fa-solid fa-add"></i>Add Subcategory</a>
    </div>
</div>
<?php Messages::show(); ?>
<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</td>
    <th>Subsubcategory Name</th>
    <th>Category</th>
    <th>Subsubcategory Description</th>
    <th>Date Added</th>
    <th>status</th>
    <th>Actions</th>
</tr>

<?php
foreach ($categories as $subcategory) {
    echo "<tr class=\"".($subcategory['status'] == 1 ? "row-active":"row-inactive")."\">";
    echo "<td>{$subcategory['subcategory_id']}</td>";
    echo "<td>{$subcategory['subcategory_name']}</td>";
    echo "<td>{$subcategory['category_name']}</td>";
    echo "<td>{$subcategory['subcategory_description']}</td>";
    echo "<td>{$subcategory['date_added']}</td>";
    echo "<td>".($subcategory['status'] == 1 ? "active":"inactive")."</td>";
    echo "<td class=\"action-td\">
            <a class=\"icon-button\" href=\"/admin/subcategory/edit.php?id={$subcategory['subcategory_id']}\"><i class=\"fa-solid fa-pen\"></i></a>
            <a class=\"icon-button\" style=\"background: red\" href=\"/admin/subcategory/delete.php?id={$subcategory['subcategory_id']}\"><i class=\"fa-solid fa-trash\"></i></a>
         </td>";
    echo "</tr>";
}
?>

</table>
</div>

