<?php
$Title = 'Dashboard | Orders'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");
include("../../partials/dashboard_header.php"); 

$stmt = $db->prepare("SELECT 
    tbl_order.order_id,
    tbl_cart_master.cart_master_id,
    email,
    tbl_cart_master.status as status 
    FROM tbl_order
    INNER JOIN tbl_cart_master
        ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    WHERE tbl_cart_master.status = 'ordered'
    ORDER BY tbl_cart_master.status DESC");

$stmt->execute();
$carts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($carts as $i => $cart) {

    $stmt = $db->prepare("SELECT 
        cart_child_id,
        tbl_cart_master.cart_master_id as cart_master_id,
        tbl_cart_child.product_id as product_id,
        product_name,
        quantity
        FROM tbl_cart_child 
        INNER JOIN tbl_cart_master 
            ON tbl_cart_child.cart_master_id = tbl_cart_master.cart_master_id 
        INNER JOIN tbl_product
            ON tbl_product.product_id = tbl_cart_child.product_id
        WHERE tbl_cart_child.cart_master_id = ?
        ");
    $stmt->bind_param("i",$cart["cart_master_id"]);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $total_cost = 0; 
    foreach ($cartItems as $key => $item) {
        $price = getProductPrice($item['product_id']);
        $cartItems[$key]['price'] = $price;  
        $total_cost += $price;
    }
    $carts[$i]['total_cost'] = $total_cost;
    $carts[$i]['cart_items'] = $cartItems;
}


?>

<div class="admin-heading">
    <h1> Unpaid Orders </h1>
    <div>
    <!--<a class="link-button" style="background: #28bd37;" href="/admin/cart/new.php"><i class="fa-solid fa-add"></i>New Purchase</a>-->
    </div>
</div>

<br>

<div style="overflow-x:auto;">
<table>
    <tr>
    <th>Order Id</th>
    <th>Customer Email</th>
    <th>Status</th>
    <th colspan="4">Order Details</th>
<?php
    foreach ($carts as $i => $cart) {
        echo "<tr>";
        $productCount = count($cart['cart_items']);
        $productCount += 2;
        echo "<td rowspan=\"$productCount\">{$cart['order_id']}</td>";
        echo "<td rowspan=\"$productCount\">{$cart['email']}</td>";
        //echo "<td rowspan=\"$productCount\">₹{$cart['total_cost']}</td>";
        echo "<td rowspan=\"$productCount\">{$cart['status']}</td>";
        echo "</tr>";
        echo "<tr class=\"".($cart['status'] != "deleted" ? "row-active":"row-inactive")."\">";
            echo "<th>id</th>";
            echo "<th>Product Image</th>";
            echo "<th>Product</th>";
            echo "<th>Quantity</th>";
        echo "</tr>";
        foreach($cart['cart_items'] as $j => $cartItem){
            //$productName = (strlen($cartItem['product_name']) > 50) ? substr($cartItem['product_name'],0,25).'...' : $cartItem['product_name'];
            echo "<tr>";
                echo "<td>{$cartItem['cart_child_id']}</td>";
                echo '<td><img src="/site/products/image.php?id='.$cartItem['product_id'].'" loading="lazy"/></td>';
                echo "<td>{$cartItem['product_name']}</td>";
                echo "<td>{$cartItem['quantity']}</td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "</tr>";
    }
?>
</table>
</div>
