<?php
$Title = 'Categories | HardwareWorld'; 
include("../config/all_config.php"); 
include("../lib/all_lib.php"); 
include("../partials/header.php"); 

$stmt = $db->prepare("SELECT * FROM tbl_category WHERE status = 1");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach($categories as $key => $category){
    $stmt = $db->prepare("SELECT * 
        FROM tbl_subcategory
        WHERE
            status = 1 
            AND category_id = ?");
    $stmt->bind_param("i",$category['category_id']);
    $stmt->execute();
    $subcategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach($subcategories as $key2 => $subcategory){
        $stmt = $db->prepare("SELECT * 
            FROM tbl_product
            WHERE
                status = 1
                AND subcategory_id = ?
        ");
        $stmt->bind_param("i",$subcategory['subcategory_id']);
        $stmt->execute();
        $subcategories[$key2]['product'] = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    $categories[$key]['subcategories'] = $subcategories;
}




?>
<link rel="stylesheet" href="/static/css/category.css"> 

<div class="category-main-container">
    <h1> Categories Available</h1>
    <?php
    foreach ($categories as $category) {
        if(count($category['subcategories']) > 0){
        echo '<div class="category-container">';
            echo "<h1>{$category['category_name']}s</h1>";
            echo '<br>';
            echo "<p class=\"category-description\">{$category['category_description']}</p>";
            echo '<div class="subcategory-container">';
            foreach ($category['subcategories'] as $subcategory) {
                if($subcategory['product']){
                echo '<div class="subcategory-item">';
                    echo "<h1>{$subcategory['subcategory_name']}</h1>";
                    echo "<p>{$subcategory['subcategory_description']}</p>";
                    echo "<br>";
                    echo '<img class="product-image" src="/site/products/image.php?id='.$subcategory['product']['product_id'].'" loading="lazy"/>';
                    echo "<p>{$subcategory['product']['product_name']}</p>";
                    echo '<br>';
                    echo "<a class=\"link-button\" href=\"/site/products?q={$subcategory['subcategory_name']}\"></i>View More {$subcategory['subcategory_name']}</a>";
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
