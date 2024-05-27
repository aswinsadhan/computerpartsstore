<?php
$Title = 'Cart | HardwareWorld'; 
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../partials/header.php"); 

$stmt = $db->prepare("SELECT 
    tbl_customer.customer_id,
    tbl_cart_master.cart_master_id,
    cart_child_id,
    tbl_product.product_id as product_id,
    quantity,
    product_name,
    product_description,
    subcategory_name,
    brand_name,
    tbl_cart_child.date_added as date_added
    FROM tbl_cart_master
    INNER JOIN tbl_cart_child
        ON tbl_cart_master.cart_master_id = tbl_cart_child.cart_master_id
    INNER JOIN tbl_product
        on tbl_cart_child.product_id = tbl_product.product_id
    INNER JOIN tbl_subcategory
        ON tbl_product.subcategory_id = tbl_subcategory.subcategory_id
    INNER JOIN tbl_brand
        ON tbl_product.brand_id = tbl_brand.brand_id
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    WHERE tbl_cart_master.status = 'in cart' AND tbl_customer.email = ?
    ORDER BY tbl_cart_child.date_added DESC
");

$stmt->bind_param("s",$_SESSION['user']['email']);

$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$subtotal = 0;

$err = NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAvailable = true;
    if(count($cart_items) < 1){
        $err = "No items in cart to buy.";
        $isAvailable = false;
    }
    foreach ($cart_items as $product) {
        $stock = getProductStock($product['product_id']);
        if($stock < $product['quantity']){
            $err = "One of the product you've added is currently out of stock";
            $isAvailable = false;
        } 
    }
    if($isAvailable){
        $stmt = $db->prepare("INSERT INTO tbl_order (cart_master_id) VALUES (?)");
        $stmt->bind_param("i",$product['cart_master_id']);
        $stmt->execute();
        $stmt->close();
        $stmt = $db->prepare("UPDATE tbl_cart_master SET status='ordered' WHERE cart_master_id=?");
        $stmt->bind_param("i",$product['cart_master_id']);
        $stmt->execute();
        $stmt->close();
        redirect("/site/orders/?type=open");
    }

}


?>
<link rel="stylesheet" href="/static/css/products.css"> 
<link rel="stylesheet" href="/static/css/cart.css"> 

<div class="cart-main-container">
    <div class="products-container">
    <h1> Your Shopping Cart </h1>

    <?php Messages::show(); ?>
    <br>
    <div class="side-container">

        <div class="cart-products-container">
        <?php
        if(!$cart_items) {

            echo "<div class=\"no-items-container\">";
                echo "<p> No Items in cart. </p>";
            echo "</div>";
        }
        foreach ($cart_items as $product) {
            echo "<div class=\"product-container\">";
            echo '<img class="product-image" src="/site/products/image.php?id='.$product['product_id'].'" loading="lazy"/>';
            echo '<div class="product-details">';
                echo "<div style=\"display:flex;align-items:flex-start\">";
                echo "<a target=\"_blank\" href=\"/site/products/view.php?id=${product['product_id']}\">";
                echo "<h1>${product['product_name']}</h1>";
                echo "</a>";
                echo "<a class=\"icon-button\" style=\"background: red\" href=\"/site/cart/delete.php?id={$product['cart_child_id']}\"><i class=\"fa-solid fa-trash\"></i></a>";
                echo "</div>";
                echo "<p class=\"product-details-subtext\">${product['subcategory_name']}</p>";
                echo "<p class=\"product-details-subtext\">${product['brand_name']}</p><br>";
                echo "<p class=\"product-details-subtext\">Quantity:<b> ${product['quantity']}</b></p><br>";
                $stock = getProductStock($product['product_id']);
                if($stock){
                    $price = getProductPrice($product['product_id']);
                    $price_formatted  = number_format($price);
                    $total = $price * $product['quantity']; 
                    $subtotal += $total;
                    $total_formatted = number_format($price * $product['quantity']); 
                    echo "<p class=\"product-price\">₹ ${total_formatted}</p>";
                    if($stock < 20){
                        echo "<p class=\"product-stock-text-warning\">Only {$stock} left in stock! Buy Now!</p>";
                    } else {
                        echo "<p class=\"product-stock-text\">In Stock</p>";
                    }
                } else {
                    echo "<p class=\"product-stock-text-warning\">Out of stock</p>";
                }
            echo '</div>';
        echo '</div>';
        }
        ?>
        </div>
        <div class="summary-section">
            <h1> Summary </h1>
            <p> Subtotal </p>
            <p class="product-price">₹ <?php echo number_format($subtotal) ?></p>
            <br>
            <br>
            <form method="POST">
                <input type="submit" class="link-button" style="background: #28bd37;" value="Buy Now"></input>
            </form>
            <br>
            <?php
                if($err){
                    echo "<p class=\"error\">{$err}</p>";
                }
            ?>
        </div>
    </div>
</div>


</div>

