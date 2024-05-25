<?php
$Title = 'Dashboard | Vendors'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT 
    vendor_email as email,
    vendor_id,
    vendor_name,
    vendor_phone,
    vendor_building_name,
    vendor_street,
    vendor_city,
    vendor_state,
    vendor_pincode,
    tbl_vendor.date_added as date_added,
    tbl_staff.email as staff_email,
    tbl_vendor.status
    FROM tbl_vendor 
    INNER JOIN tbl_staff
        ON tbl_vendor.staff_id = tbl_staff.staff_id
    ORDER BY tbl_vendor.status DESC");
$stmt->execute();
$vendors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="admin-heading">
    <h1> Vendor Details </h1>
    <div>
    <a class="link-button" style="background: #28bd37;" href="/admin/vendor/add.php"><i class="fa-solid fa-add"></i>Add Vendor</a>
    </div>
</div>
<?php Messages::show(); ?>
<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</td>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Building Name</th>
    <th>Street</th>
    <th>City</th>
    <th>State</th>
    <th>Pincode</th>
    <th>Added By</td>
    <th>Date added</th>
    <th>status</th>
    <th>Actions</th>
</tr>

<?php
foreach ($vendors as $vendor) {
    echo "<tr class=\"".($vendor['status'] == 1 ? "row-active":"row-inactive")."\">";
    echo "<td>{$vendor['vendor_id']}</td>";
    echo "<td>{$vendor['vendor_name']}</td>";
    echo "<td>{$vendor['email']}</td>";
    echo "<td>{$vendor['vendor_phone']}</td>";
    echo "<td>{$vendor['vendor_building_name']}</td>";
    echo "<td>{$vendor['vendor_street']}</td>";
    echo "<td>{$vendor['vendor_city']}</td>";
    echo "<td>{$vendor['vendor_state']}</td>";
    echo "<td>{$vendor['vendor_pincode']}</td>";
    echo "<td>{$vendor['staff_email']}</td>";
    echo "<td>{$vendor['date_added']}</td>";
    echo "<td>".($vendor['status'] == 1 ? "active":"inactive")."</td>";
    echo "<td class=\"action-td\">
            <a class=\"icon-button\" href=\"/admin/vendor/edit.php?id={$vendor['vendor_id']}\"><i class=\"fa-solid fa-pen\"></i></a>
            <a class=\"icon-button\" style=\"background: red\" href=\"/admin/vendor/delete.php?id={$vendor['vendor_id']}\"><i class=\"fa-solid fa-trash\"></i></a>
         </td>";
    echo "</tr>";
}
?>

</table>
</div>

