<?php
$Title = 'Brands | HardwareWorld'; 
include("../config/all_config.php"); 
include("../lib/all_lib.php"); 
include("../partials/header.php"); 

$stmt = $db->prepare("SELECT * FROM tbl_brand WHERE status = 1");
$stmt->execute();
$brands = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach($brands as $key => $brand){
    $stmt = $db->prepare("SELECT * 
        FROM tbl_product
        WHERE
            status = 1 
            AND brand_id = ?
        LIMIT 6 
    ");
    $stmt->bind_param("i",$brand['brand_id']);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $brands[$key]['products'] = $products;
}



?>
<link rel="stylesheet" href="/static/css/category.css"> 

<div class="category-main-container">
    <h1> Brands Available</h1>
    <?php
    foreach ($brands as $brand) {
        if(count($brand['products']) > 0){
        echo '<div class="category-container">';
            echo "<h1>{$brand['brand_name']}</h1>";
            echo '<br>';
            echo "<p class=\"category-description\">{$brand['brand_description']}</p>";
            echo '<br>';
            echo '<div class="subcategory-container">';
            foreach ($brand['products'] as $product) {
                if($product){
                echo '<div class="subcategory-item">';
                    echo "<p>{$product['product_name']}</p>";
                    echo '<br>';
                    echo '<img class="product-image" src="/site/products/image.php?id='.$product['product_id'].'" loading="lazy"/>';
                    echo '<br>';
                    echo '<br>';
                    echo "<a class=\"link-button\" href=\"/site/products?q={$brand['brand_name']}\"></i>View More Products</a>";
                echo '</div>';
                }
            }
            echo '</div>';
        echo'</div>';
        }

    }
    ?>
</div>

<br>
<br>
<br>
<br>
<br>
<br>
