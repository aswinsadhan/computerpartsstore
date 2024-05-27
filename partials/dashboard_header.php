<html>
    <head>
        <link href="/static/fontawesome/css/fontawesome.css" rel="stylesheet">
        <link href="/static/fontawesome/css/brands.css" rel="stylesheet">
        <link href="/static/fontawesome/css/solid.css" rel="stylesheet">
        <link rel="stylesheet" href="/static/css/defaults.css"> 
        <link rel="stylesheet" href="/static/css/forms.css"> 
        <link rel="stylesheet" href="/static/css/admin.css"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <base href="/">
        <title><?php echo $Title; ?></title>
        <script src="/static/js/sidenav.js" defer></script>
        <script src="/static/js/admin.js" defer></script>
        </script>
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
                    echo "<a href='/auth/login.php'>Login</a>";
                } else {
                    echo "<a href='/auth/logout.php'>Logout</a>";
                }
            ?>
        </div>
    </div>
</div>

<main>

<div class="sidebar">
    <?php 
    if(check_role("admin","staff")){
        echo "<a href=\"/admin/\"><h1 class=\"sidebar-header\">DASHBOARD</h1></a>";
    } else {
        echo "<h1 class=\"sidebar-header\">DASHBOARD</h1>";

    }
    if(check_role("admin")){
    echo "<a href=\"/admin/users\" class=\"sidebar-item ".isSidebarItemActive("/admin/users")."\">
        <i class=\"fa-solid fa-user\"></i>
        Users
    </a>";
    } else if(check_role("staff")){
    echo '<div class="sidebar-item">
        <i class="fa-solid fa-user"></i>
        Users
    </div>';

    }
    if(check_role("admin")){
    echo "<a href=\"/admin/customers/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/customers")."\">
            <i class=\"fa-solid fa-bag-shopping\"></i>
            Customers
        </div>
    </a>";
    }
    if(check_role("admin")){
    echo "<a href=\"/admin/staff/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/staff")."\">
            <i class=\"fa-solid fa-chalkboard-user\"></i>
            Staff
        </div>
    </a>";
    }
    if(check_role("admin","staff")){
    echo "<a href=\"/admin/couriers/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/couriers")."\">
            <i class=\"fa-solid fa-box\"></i>
            Couriers
        </div>
    </a>";
    }

    if(check_role("admin","staff")){
    echo "<a href=\"/admin/vendor/\">
        <div class=\"sidebar-item ".isSidebarItemActive("/admin/vendor")."\">
            <i class=\"fa-solid fa-briefcase\"></i>
            Vendors
        </div>
    </a>";
    }
    
    if(check_role("admin","staff")){
    echo "<a href=\"/admin/products\">
        <div class=\"sidebar-item\">
            Products
        </div>
        <a href=\"/admin/category/\">
            <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/category")."\">
                <i class=\"fa-solid fa-list\"></i>
                Categories
            </div>
        </a>
        <a href=\"/admin/subcategory/\">
            <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/subcategory")."\">
                <i class=\"fa-solid fa-bars-staggered\"></i>
                Subcategories
            </div>
        </a>
        <a href=\"/admin/brands/\">
            <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/brands")."\">
                <i class=\"fa-solid fa-trademark\"></i>
                Brand
            </div>
        </a>
        <a href=\"/admin/products/\">
            <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/products")."\">
                <i class=\"fa-solid fa-tags\"></i>
                Products
            </div>
        </a>
    </a>
    <a href=\"/admin/purchase/\">
        <div class=\"sidebar-item ".isSidebarItemActive("/admin/purchase")."\">
            <i class=\"fa-solid fa-basket-shopping\"></i>
            Purchases
        </div>
    </a>

    <a href=\"/admin/cart/\">
        <div class=\"sidebar-item ".isSidebarItemActive("/admin/cart")."\">
            <i class=\"fa-solid fa-cart-shopping\"></i>
            User Carts
        </div>
    </a>";
    }


    echo '<div class="sidebar-item">
        Sales 
    </div>';

    if(check_role("admin","staff")){
    echo "<a href=\"/admin/orders/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/orders")."\">
            <i class=\"fa-solid fa-cart-arrow-down\"></i>
            Unpaid Orders
        </div>
    </a>
    <a href=\"/admin/cards/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/cards")."\">
        <i class=\"fa-solid fa-credit-card\"></i>
            Cards
        </div>
    </a>
    <a href=\"/admin/payments/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/payments")."\">
            <i class=\"fa-solid fa-dollar-sign\"></i>
            Paid Orders
        </div>
    </a>";
    }
    if(check_role("admin","staff","courier")){
    echo "<a href=\"/admin/delivery/\">
        <div class=\"sidebar-sub-item ".isSidebarItemActive("/admin/delivery")."\">
            <i class=\"fa-solid fa-truck\"></i>
            Deliveries
        </div>
    </a>";
    }
    ?>
    <a href="/auth/logout.php">
        <div class="sidebar-item">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Log out 
        </div>
    </a>
</div>
<div class="content">
