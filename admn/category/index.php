<?php
$Title = 'Dashboard | Category Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT * FROM tbl_category ORDER BY status DESC");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="admin-heading">
    <h1> Category Details </h1>
    <div>
    <a class="link-button" style="background: #28bd37;" href="/admin/category/add.php"><i class="fa-solid fa-add"></i>Add Category</a>
    </div>
</div>
<?php Messages::show(); ?>
<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</td>
    <th>Category Name</th>
    <th>Category Description</th>
    <th>Date Added</th>
    <th>status</th>
    <th>Actions</th>
</tr>

<?php
foreach ($categories as $category) {
    echo "<tr class=\"".($category['status'] == 1 ? "row-active":"row-inactive")."\">";
    echo "<td>{$category['category_id']}</td>";
    echo "<td>{$category['category_name']}</td>";
    echo "<td>{$category['category_description']}</td>";
    echo "<td>{$category['date_added']}</td>";
    echo "<td>".($category['status'] == 1 ? "active":"inactive")."</td>";
    echo "<td class=\"action-td\">
            <a class=\"icon-button\" href=\"/admin/category/edit.php?id={$category['category_id']}\"><i class=\"fa-solid fa-pen\"></i></a>
            <a class=\"icon-button\" style=\"background: red\" href=\"/admin/category/delete.php?id={$category['category_id']}\"><i class=\"fa-solid fa-trash\"></i></a>
         </td>";
    echo "</tr>";
}
?>

</table>
</div>

