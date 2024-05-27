<html>
    <head>
        <link rel="stylesheet" href="/static/css/defaults.css"> 
        <link href="/static/fontawesome/css/fontawesome.css" rel="stylesheet">
        <link href="/static/fontawesome/css/brands.css" rel="stylesheet">
        <link href="/static/fontawesome/css/solid.css" rel="stylesheet">
        <link rel="stylesheet" href="/static/css/forms.css"> 
        <link rel="stylesheet" href="/static/css/sidebar.css"> 
        <base href="/">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <script src="/static/js/sidenav.js" defer></script>
        <title><?php echo $Title; ?></title>
    </head>
<body>
<div class="top-nav">
        <a class="top-nav-heading" href="/">
        <i class="fa-solid fa-microchip"></i>
        <h1 >HARDWARE <br> WORLD</h1>
        </a>
    <div class="top-nav-right">
        <input class="side-menu" type="checkbox" id="side-menu"/>
        <label class="hamb" for="side-menu"><span class="hamb-line"></span></label>
        <div class="top-nav-links">
        <?php 
            if(!is_authenticated()) {
                echo "<a href='/site/products'>Products</a>";
                echo "<a href='/site/brands.php'>Brands</a>";
                echo "<a href='/site/categories.php'>Categories</a>";
                echo "<a href='/auth/login.php'>Login</a>";
                echo "<a href='/auth/register.php'>Register</a>";
            } else {
                echo "<a href='/site/products'>Products</a>";
                echo "<a href='/site/brands.php'>Brands</a>";
                echo "<a href='/site/categories.php'>Categories</a>";
                echo "<a href='/site/cart/view.php'>Cart</a>";
                echo "<a href='/site/orders/?type=paid'>Orders</a>";
                echo "<a href='/auth/logout.php'>Logout</a>";
            }
        ?>
        </div>
        <!--<a href="/auth/register.php">Register</a> -->
    </div>
</div>
<main>
<div class="sidebar">
    <br>
    <br>

    <a href="/site/products">
        <div class="sidebar-item">
            <i class="fa-solid fa-basket-shopping"></i>
            Products
        </div>
    </a>

    <a href="/site/brands.php">
        <div class="sidebar-item">
            <i class="fa-solid fa-trademark"></i>
            Brands 
        </div>
    </a>

    <a href="/site/categories.php">
        <div class="sidebar-item">
            <i class="fa-solid fa-list"></i>
            Categories 
        </div>
    </a>

    <?php 
        if(!is_authenticated()) {
            echo '<a href="/auth/login.php/">
                <div class="sidebar-item">
                    <i class="fa-solid fa-user-lock"></i>
                    Login
                </div>
            </a>';
            echo '<a href="/auth/register.php/">
                <div class="sidebar-item">
                    <i class="fa-solid fa-user-plus"></i>
                    Register 
                </div>
            </a>';


        } else {
            echo '<a href="/site/cart/view.php">
                <div class="sidebar-item">
                    <i class="fa-solid fa-cart-shopping"></i>
                    Cart
                </div>
            </a>';
            echo '<a href="/site/orders/?type=paid">
                <div class="sidebar-item">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                    Orders 
                </div>
            </a>';
            echo '<a href="/auth/logout.php">
                <div class="sidebar-item">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Log out 
                </div>
            </a>';
        }
    ?>
</div>
