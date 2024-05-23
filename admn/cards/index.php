<?php
$Title = 'Dashboard | Card Details'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT card_id,tbl_customer.email,card_name,card_no,card_expiry FROM tbl_card
    INNER JOIN tbl_customer
        ON tbl_card.customer_id = tbl_customer.customer_id 
");
$stmt->execute();
$cards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="admin-heading">
    <h1> Card Details </h1>
    <div>
    </div>
</div>

<br>

<div style="overflow-x:auto;">
<table>
<tr>
    <th>card id</td>
    <th>Customer Email</th>
    <th>Card No</th>
    <th>Card Name</th>
    <th>Card Expiry</th>
</tr>

<?php
foreach ($cards as $card) {
    $card_no = str_repeat('*', strlen($card['card_no']) - 4) . substr($card['card_no'], -4);
    echo "<tr>";
    echo "<td>{$card['card_id']}</td>";
    echo "<td>{$card['email']}</td>";
    echo "<td>{$card_no}</td>";
    echo "<td>{$card['card_name']}</td>";
    echo "<td>{$card['card_expiry']}</td>";
}
?>

</table>
</div>

