<?php
$Title = 'Dashboard | Couriers'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT 
    tbl_courier.email as email,
    courier_id,
    courier_name,
    courier_phone,
    courier_building_name,
    courier_street,
    courier_city,
    courier_state,
    courier_pincode,
    tbl_courier.date_added as date_added,
    tbl_staff.email as staff_email,
    status
    FROM tbl_courier 
    INNER JOIN tbl_login 
        ON tbl_courier.email = tbl_login.email 
    INNER JOIN tbl_staff
        ON tbl_courier.staff_id = tbl_staff.staff_id
    ORDER BY tbl_login.status DESC");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="admin-heading">
    <h1> Courier Details </h1>
    <div>
    <a class="link-button" style="background: #28bd37;" href="/admin/couriers/add.php"><i class="fa-solid fa-add"></i>Add Courier</a>
    </div>
</div>
<?php Messages::show(); ?>
<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>id</td>
    <th>Email</th>
    <th>Name</th>
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
foreach ($users as $user) {
    echo "<tr class=\"".($user['status'] == 1 ? "row-active":"row-inactive")."\">";
    echo "<td>{$user['courier_id']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$user['courier_name']}</td>";
    echo "<td>{$user['courier_phone']}</td>";
    echo "<td>{$user['courier_building_name']}</td>";
    echo "<td>{$user['courier_street']}</td>";
    echo "<td>{$user['courier_city']}</td>";
    echo "<td>{$user['courier_state']}</td>";
    echo "<td>{$user['courier_pincode']}</td>";
    echo "<td>{$user['staff_email']}</td>";
    echo "<td>{$user['date_added']}</td>";
    echo "<td>".($user['status'] == 1 ? "active":"inactive")."</td>";
    echo "<td class=\"action-td\">
            <a class=\"icon-button\" href=\"/admin/couriers/edit.php?id={$user['courier_id']}\"><i class=\"fa-solid fa-pen\"></i></a>
            <a class=\"icon-button\" style=\"background: red\" href=\"/admin/users/delete.php?id={$user['email']}\"><i class=\"fa-solid fa-trash\"></i></a>
         </td>";
    echo "</tr>";
}
?>

</table>
</div>
