<?php
function redirect($url){
    header("Location: {$url}");
    die();
}

function isSidebarItemActive($url) {
    if(strpos($_SERVER['REQUEST_URI'],$url) !== false){
        return "active";
    } else {
        return " ";
    }
}

function getProductStock($id) {
    global $db;
    $stmt = $db->prepare("SELECT 
        *
        FROM tbl_purchase_child
        INNER JOIN tbl_purchase_master
        ON tbl_purchase_child.purchase_master_id = tbl_purchase_master.purchase_master_id
        WHERE product_id = ? AND tbl_purchase_master.status = 1
        ORDER BY date_added"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if(!$purchases){
        return 0;
    }

    $stmt = $db->prepare("SELECT
        sum(tbl_cart_child.quantity) as total_purchase
        FROM tbl_payment
        INNER JOIN tbl_order
            ON tbl_payment.order_id = tbl_order.order_id
        INNER JOIN tbl_cart_master
            ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
        INNER JOIN tbl_cart_child
            ON tbl_cart_master.cart_master_id = tbl_cart_child.cart_master_id
        WHERE tbl_cart_child.product_id = ?;
"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $totalOrdered = $stmt->get_result()->fetch_assoc()['total_purchase'];

    $currentPurchase;

    foreach ($purchases as $key => $purchase) {
        if($totalOrdered < $purchase['quantity']){
            $currentPurchase = $purchase;
            break;
        } else {
            $totalOrdered -= $purchase['quantity'];
        }
    }
    if(isset($currentPurchase)){
        return $currentPurchase['quantity'] - $totalOrdered;
    } else {
        return 0;
    }
}

function getProductPrice($id) {
    global $db;
    $stock = getProductStock($id);
    if($stock){
        $stmt = $db->prepare("SELECT 
            *
            FROM tbl_purchase_child
            INNER JOIN tbl_purchase_master
            ON tbl_purchase_child.purchase_master_id = tbl_purchase_master.purchase_master_id
            WHERE product_id = ? AND tbl_purchase_master.status = 1
            ORDER BY date_added"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

         $stmt = $db->prepare("SELECT
            sum(tbl_cart_child.quantity) as total_purchase
            FROM tbl_payment
            INNER JOIN tbl_order
                ON tbl_payment.order_id = tbl_order.order_id
            INNER JOIN tbl_cart_master
                ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
            INNER JOIN tbl_cart_child
                ON tbl_cart_master.cart_master_id = tbl_cart_child.cart_master_id
            WHERE tbl_cart_child.product_id = ?;"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $totalOrdered = $stmt->get_result()->fetch_assoc()['total_purchase'];

        $currentPurchase;

        foreach ($purchases as $key => $purchase) {

            if($totalOrdered < $purchase['quantity']){
                $currentPurchase = $purchase;
                break;
            } else {
                $totalOrdered -= $purchase['quantity'];
            }

        }
        return $currentPurchase['selling_price'];
    }
}

function getOldProductPrice($id,$orderid) {
    global $db;
    $stmt = $db->prepare("SELECT 
        *
        FROM tbl_purchase_child
        INNER JOIN tbl_purchase_master
        ON tbl_purchase_child.purchase_master_id = tbl_purchase_master.purchase_master_id
        WHERE product_id = ? AND tbl_purchase_master.status = 1
        ORDER BY date_added"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $db->prepare("SELECT
        tbl_order.order_id as order_id,
        tbl_cart_child.quantity as quantity
        FROM tbl_payment
        INNER JOIN tbl_order
            ON tbl_payment.order_id = tbl_order.order_id
        INNER JOIN tbl_cart_master
            ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
        INNER JOIN tbl_cart_child
            ON tbl_cart_master.cart_master_id = tbl_cart_child.cart_master_id
        WHERE tbl_cart_child.product_id = ?
        ORDER BY tbl_payment.date
        ;"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalOrdered = 0;
    foreach ($orders as $order) {
        $totalOrdered += $order['quantity'];
    }

    $currentPurchase = $purchases[0];
    $purchasesIndex = 0;
    foreach ($orders as $order) {
        if($order['order_id'] != $orderid){
            if($currentPurchase['quantity'] > $order['quantity']){
                $currentPurchase['quantity'] -= $order['quantity'];
            } else {
                $currentPurchase =  $purchases[++$purchasesIndex];
            }
        } else {
            return $currentPurchase['selling_price'];
        }
    }

    //foreach ($purchases as $key => $purchase) {
        //if($totalOrdered < $purchase['quantity']){
            //$currentPurchase = $purchase;
            //break;
        //} else {
            //$totalOrdered -= $purchase['quantity'];
        //}

    //}
    //return $currentPurchase['selling_price'];
}


function isPurchaseUsed($purchaseId){

    global $db;

    $stmt = $db->prepare("SELECT 
        *
        FROM tbl_purchase_child
        INNER JOIN tbl_purchase_master
        ON tbl_purchase_child.purchase_master_id = tbl_purchase_master.purchase_master_id
        WHERE tbl_purchase_master.purchase_master_id = ?"
    );
    $stmt->bind_param("i", $purchaseId);
    $stmt->execute();
    $main_purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($main_purchases as  $purchase) {

        $product_id = $purchase['product_id'];

        $stmt = $db->prepare("SELECT 
            *
            FROM tbl_purchase_child
            INNER JOIN tbl_purchase_master
            ON tbl_purchase_child.purchase_master_id = tbl_purchase_master.purchase_master_id
            WHERE product_id = ?
            ORDER BY date_added"
        );
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt = $db->prepare("SELECT
            tbl_order.order_id as order_id,
            tbl_cart_child.quantity as quantity
            FROM tbl_payment
            INNER JOIN tbl_order
                ON tbl_payment.order_id = tbl_order.order_id
            INNER JOIN tbl_cart_master
                ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
            INNER JOIN tbl_cart_child
                ON tbl_cart_master.cart_master_id = tbl_cart_child.cart_master_id
            WHERE tbl_cart_child.product_id = ?
            ORDER BY tbl_payment.date
            ;"
        );

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $totalOrdered = 0;
        foreach ($orders as $order) {
            $totalOrdered += $order['quantity'];
        }

        $currentPurchase = $purchases[0];

        $purchasesIndex = 0;

        foreach ($orders as $order) {
                if($currentPurchase['quantity'] > $order['quantity']){
                    $currentPurchase['quantity'] -= $order['quantity'];
                    if($currentPurchase['purchase_master_id'] == $purchaseId){
                        return true;
                    }
                } else {
                    $currentPurchase =  $purchases[++$purchasesIndex];
                }
        }

    }
    return false;

}

function getCartItmes(&$orders,$isOld=false) {
    global $db;
    foreach ($orders as $key => $order) {
        $stmt = $db->prepare("
            SELECT
                tbl_customer.customer_id,
                tbl_cart_master.cart_master_id,
                cart_child_id,
                tbl_product.product_id as product_id,
                quantity,
                product_name,
                product_image,
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
                    WHERE 
                        tbl_cart_master.cart_master_id = ?
        ");
        $stmt->bind_param("i",$order['cart_master_id']);
        $stmt->execute();
        $orderProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $subtotal = 0;
        foreach ($orderProducts as $productIndex => $product) {
            $stock = getProductStock($product['product_id']);
            $orderProducts[$productIndex]['stock'] = $stock;
            $price;
            if($isOld){
                $price = getOldProductPrice($product['product_id'],$order['order_id']);
            } else {
                $price = getProductPrice($product['product_id']);
            }
            $orderProducts[$productIndex]['price'] = $price;
            $total = $price * $product['quantity']; 
            $orderProducts[$productIndex]['total'] = $total;
            $subtotal += $total;
        }
        $orders[$key]['products'] = $orderProducts;
        $orders[$key]['subtotal'] = $subtotal;

    }
}



?>
