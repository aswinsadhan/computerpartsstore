<?php
$Title = 'Home | HardwareWorld'; 
include("./config/all_config.php"); 
include("./lib/all_lib.php"); 
include("./partials/header.php"); 

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


$stmt = $db->prepare("SELECT count(*) as count FROM tbl_product WHERE status = 1");
$stmt->execute();
$product_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $db->prepare("SELECT count(*) as count FROM tbl_brand WHERE status = 1");
$stmt->execute();
$brand_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();


?>
<link rel="stylesheet" href="/static/css/home.css"> 
<div class="home-hero-section">
    <h1> HARD&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WARE</h1>
    <h1 class="second-title">WORLD</h1>
    <img src="/static/img/hero.png">
    <br>
    <br>
    <br>
    <p>The best shopping site for computer hardware </p>
</div>

<div class="section flex-vert-center">
    <div>
        <h1>We have over <?php echo $product_count - 1 ?>+<br> Unique Products</h1>
        <br>
        <br>
        <a class="link-button" href="/site/products/"></i>View Products</a>
    </div>
    <img src="/static/img/case.jpeg" > 
</div>

<div class="section flex-vert-center">
    <img src="/static/img/brands.jpg" > 
    <div>
        <h1>Over <?php echo $brand_count - 1 ?>+ <br> Popular Brands </h1>
        <br>
        <br>
    </div>
</div>

<div class="section flex-vert-center half">
    <div class="info-item">
        <i class="fa-solid fa-truck"></i>
        <br>
        <br>
        <p>Free Delivery</p>
    </div>
    <div class="info-item">
        <i class="fa-regular fa-dollar"></i>
        <br>
        <br>
        <p>Best Prices</p>
    </div>
    <div class="info-item">
        <i class="fa-solid fa-hand"></i>
        <br>
        <br>
        <p>Best Quality</p>
    </div>
</div>

<div class="category-main-container">
    <h1> Categories Available</h1>
    <?php
    foreach ($categories as $category) {
        if(count($category['subcategories']) > 0){
        echo '<div class="category-container">';
            echo "<h1>{$category['category_name']}s</h1>";
            echo '<br>';
            echo '<br>';
            echo '<br>';
            echo '<div class="subcategory-container">';
            foreach ($category['subcategories'] as $subcategory) {
                if($subcategory['product']){
                echo '<div class="subcategory-item">';
                    echo "<h1>{$subcategory['subcategory_name']}</h1>";
                    echo '<img class="product-image" src="/site/products/image.php?id='.$subcategory['product']['product_id'].'" loading="lazy"/>';
                    echo "<p>{$subcategory['product']['product_name']}</p>";
                    echo '<br>';
                    echo '<br>';
                    echo "<a class=\"link-button\" href=\"/site/products?q={$subcategory['subcategory_name']}\"></i>View More Products</a>";
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
<br>
<br>
<br>
<br>
