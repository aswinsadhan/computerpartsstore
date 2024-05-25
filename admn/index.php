<?php
$Title = 'Dashboard | Purchases '; 
include("../config/all_config.php"); 
include("../lib/all_lib.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("admin","staff");
include("../partials/dashboard_header.php"); 


$total_spent = 0;
$stmt = $db->prepare("SELECT 
    purchase_master_id,
    tbl_purchase_master.vendor_id as vendor_id,
    vendor_name,
    vendor_email,
    staff_fname,
    staff_lname,
    tbl_staff.email as staff_email,
    tbl_purchase_master.staff_id as staff_id, 
    tbl_purchase_master.date_added as date_added,
    tbl_purchase_master.status as status 
    FROM tbl_purchase_master 
    INNER JOIN tbl_staff
        ON tbl_purchase_master.staff_id = tbl_staff.staff_id
    INNER JOIN tbl_vendor
        ON tbl_purchase_master.vendor_id = tbl_vendor.vendor_id
    WHERE tbl_purchase_master.status = 1 
    ORDER BY tbl_purchase_master.status DESC,tbl_purchase_master.date_added");
$stmt->execute();
$purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($purchases as $i => $purchase) {

    $stmt = $db->prepare("SELECT 
        purchase_child_id,
        tbl_purchase_master.purchase_master_id as purchase_master_id,
        tbl_purchase_child.product_id as product_id,
        product_name,
        cost_price,
        selling_price,
        quantity
        FROM tbl_purchase_child 
        INNER JOIN tbl_purchase_master 
            ON tbl_purchase_child.purchase_master_id = tbl_purchase_master.purchase_master_id 
        INNER JOIN tbl_product
            ON tbl_product.product_id = tbl_purchase_child.product_id
        WHERE tbl_purchase_child.purchase_master_id = ?
        ");
    $stmt->bind_param("i",$purchase["purchase_master_id"]);
    $stmt->execute();
    $purchaseItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $purchases[$i]['purchase_items'] = $purchaseItems;
    $total_cost = 0; 
    foreach ($purchaseItems as $key => $item) {
        $total_cost += $item['cost_price'] * $item['quantity'];
    }
    $purchases[$i]['total_cost'] = $total_cost;
    $total_spent += $total_cost;
}

$total_bought = 0;
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
    WHERE
        tbl_cart_master.status = 'payment-complete'
        OR tbl_cart_master.status = 'shipped'
        OR tbl_cart_master.status = 'in-transit'
        OR tbl_cart_master.status = 'out-for-delivery'
        OR tbl_cart_master.status = 'delivered'
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
        product_image,
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
        $price = getOldProductPrice($item['product_id'],$cart['order_id']) * $item['quantity'];
        $cartItems[$key]['price'] = $price;  
        $total_cost += $price;
    }
    $carts[$i]['total_cost'] = $total_cost;
    $carts[$i]['cart_items'] = $cartItems;
    $total_bought += $total_cost;
}

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_customer INNER JOIN tbl_login ON tbl_customer.email = tbl_login.email WHERE tbl_login.status = 1");
$stmt->execute();
$customer_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_staff INNER JOIN tbl_login ON tbl_staff.email = tbl_login.email WHERE tbl_login.status = 1");
$stmt->execute();
$staff_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_courier INNER JOIN tbl_login ON tbl_courier.email = tbl_login.email WHERE tbl_login.status = 1");
$stmt->execute();
$courier_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_vendor WHERE status = 1");
$stmt->execute();
$vendor_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_category WHERE status = 1");
$stmt->execute();
$category_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_subcategory WHERE status = 1");
$stmt->execute();
$subcategory_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_brand WHERE status = 1");
$stmt->execute();
$brand_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_product WHERE status = 1");
$stmt->execute();
$product_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

?>

<link rel="stylesheet" href="/static/css/dashboard.css"> 

<div class="admin-heading">
    <h1 class="heading"> DASHBOARD </h1>
</div>
<br>
<br>
<div class="admin-tile-container">
    <div class="admin-tile">
        <i class="fa-solid fa-indian-rupee-sign"></i>
        <p>₹<?php echo number_format($total_spent); ?> in Expenses </p>
    </div>

    <div class="admin-tile">
        <i class="fa-solid fa-indian-rupee-sign"></i>
        <p>₹<?php echo number_format($total_bought); ?> in Revenue </p>
    </div>

    <?php $isloss = (($total_bought-$total_spent) < 0) ?>
    <div class="admin-tile">
        <i class="fa-solid fa-indian-rupee-sign"></i>
        <p class="<?php if($isloss) { echo("error");}?>"> ₹<?php echo number_format($total_bought - $total_spent); ?> Net Income </p>
    </div>

</div>
<br>
<br>
<div class="admin-tile-container">

    <div class="admin-tile">
        <i class="fa-solid fa-user"></i>
        <p><?php echo $customer_count; ?> Customers</p>
    </div>

    <div class="admin-tile">
        <i class="fa-solid fa-chalkboard-user"></i>
        <p><?php echo $staff_count; ?> Staff</p>
    </div>
    <div class="admin-tile">
        <i class="fa-solid fa-box"></i>
        <p><?php echo $courier_count; ?> Couriers </p>
    </div>
    <div class="admin-tile">
        <i class="fa-solid fa-briefcase"></i>
        <p><?php echo $vendor_count; ?> Vendor </p>
    </div>

    <div class="admin-tile">
        <i class="fa-solid fa-trademark"></i>
        <p><?php echo $brand_count; ?> Brands </p>
    </div>

    <div class="admin-tile">
        <i class="fa-solid fa-list"></i>
        <p><?php echo $category_count; ?> Categories </p>
    </div>

    <div class="admin-tile">
        <i class="fa-solid fa-bars-staggered"></i>
        <p><?php echo $subcategory_count; ?> Subcategories </p>
    </div>
    <div class="admin-tile">
        <i class="fa-solid fa-tags"></i>
        <p><?php echo $product_count; ?> Products </p>
    </div>


</div>

