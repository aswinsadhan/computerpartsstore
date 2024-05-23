<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
include("../../lib/fpdf.php"); 
check_auth_redirect_if_not();
check_role_or_redirect("staff","admin");

$dateError = NULL;
function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}
$dated = false;
if(
    isset($_GET['start_date']) && 
    isset($_GET['end_date']) && 
    !empty($_GET['start_date']) &&
    !empty($_GET['end_date']) && 
    validateDate($_GET['start_date']) &&
    validateDate($_GET['end_date']) &&
    (strtotime($_GET['start_date']) <= strtotime($_GET['end_date']))
){
    $dated = true;
} else {
    $dated = false;
    $dateError = "Invalid Dates Selected";
}

$DATED_SQL = "SELECT 
    tbl_order.order_id,
    tbl_cart_master.cart_master_id,
    email,
    tbl_payment.date as date_added,
    tbl_cart_master.status as status 
    FROM tbl_order
    INNER JOIN tbl_cart_master
        ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
    INNER JOIN tbl_payment
        ON tbl_order.order_id = tbl_payment.order_id
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    WHERE (tbl_payment.date >= ? AND tbl_payment.date<= ? ) AND (
        tbl_cart_master.status = 'payment-complete'
        OR tbl_cart_master.status = 'shipped'
        OR tbl_cart_master.status = 'in-transit'
        OR tbl_cart_master.status = 'out-for-delivery'
        OR tbl_cart_master.status = 'delivered' )
    ORDER BY tbl_payment.date";

$NORMAL_SQL = "SELECT 
    tbl_order.order_id,
    tbl_cart_master.cart_master_id,
    email,
    tbl_payment.date as date_added,
    tbl_cart_master.status as status 
    FROM tbl_order
    INNER JOIN tbl_cart_master
        ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
    INNER JOIN tbl_payment
        ON tbl_order.order_id = tbl_payment.order_id
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    WHERE
        tbl_cart_master.status = 'payment-complete'
        OR tbl_cart_master.status = 'shipped'
        OR tbl_cart_master.status = 'in-transit'
        OR tbl_cart_master.status = 'out-for-delivery'
        OR tbl_cart_master.status = 'delivered'
    ORDER BY tbl_payment.date";

$stmt = $db->prepare($NORMAL_SQL);
if($dated){

    $start_date = date("Y-m-d H:i:s",strtotime($_GET['start_date']));
    $end_date = $_GET['end_date'];
    $end_date = $end_date . 23 . " hours " . 59 . " minutes " . 59 . " seconds ";
    $end_date = date("Y-m-d H:i:s",strtotime($end_date));
    $stmt = $db->prepare($DATED_SQL);
    $stmt->bind_param("ss",$start_date,$end_date);
}

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
        $price = getOldProductPrice($item['product_id'],$cart['order_id']);
        $cartItems[$key]['price'] = $price;  
        $total_cost += ($price * $item['quantity']);
    }
    $carts[$i]['total_cost'] = $total_cost;
    $carts[$i]['cart_items'] = $cartItems;
}



class PDF extends FPDF
{
function Header(){
}
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

function FancyTable($header, $cart)
{
    // Colors, line width and bold font
    $this->SetFillColor(255,255,255);
    $this->SetTextColor(0);
    //$this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.5);
    $this->SetFont('Arial','B',10);
    // Header
    $w = array(130, 18, 15, 25);
    $this->SetFillColor(53, 98, 222);
    $this->SetTextColor(255);
    for($i=0;$i<count($header);$i++)
    $this->Cell($w[$i],8,$header[$i],1,0,'C',true);
    $this->SetFillColor(220,220,220);
    $this->SetTextColor(0);
    $this->Ln();
    // Color and font restoration
    $this->SetTextColor(0);
    $this->SetFont('Arial','',10);
    // Data
    $fill = false;

    foreach ($cart['cart_items'] as $i => $product) 
    {
        $fill = !$fill;
        $y = $this->GetY();
        $x = $this->GetX() + $w[0];
        $x0 = $this->GetX();
        $this->SetFont('Arial','',10);
        $this->MultiCell($w[0],15,"",'LR','L',$fill);
        $this->SetXY($x0, $y);
        $this->MultiCell($w[0],7.5,$product['product_name'],'LRT','L',$fill);
        $this->SetXY($x, $y);
        $this->SetFont('Arial','',12);
        $x = $this->GetX() + $w[1];
        $this->MultiCell($w[1],15,$product['quantity'],'LRBT','C',$fill);
        $this->SetXY($x, $y);
        $x = $this->GetX() + $w[2];
        $this->MultiCell($w[2],15,$product['price'],'LRBT','C',$fill);
        $this->SetXY($x, $y);
        $x = $this->GetX() + $w[3];
        $this->MultiCell($w[3],15,$product['price'] * $product['quantity'],'LRBT','R',$fill);
        $this->SetXY($x, $y);
        $this->Ln();
        $this->Cell(array_sum($w),0,"",'TB');
        $this->Ln();
        if($i == 11){
            $this->AddPage();
        }
        if($i == 11+17){
            $this->AddPage();
        }
        if($i == 11+17+17){
            $this->AddPage();
        }
    }
    $this->Ln();
}
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetMargins(10, 10);
$pdf->AddPage();

$pdf->SetFont('Arial','B',13);
// Title
$pdf->SetFillColor(230,230,0);
$pdf->SetLineWidth(0.4);
$pdf->Cell(190,10,'Order Report | hardwareworld.xyz',0,0,'C');
// Line break
$pdf->Ln(20);

foreach ($carts as $i => $cart) {
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(100,10,"Order Number : {$cart['order_id']}");
    $pdf->Ln();
    $pdf->Cell(40,10,"Purchase date : ".date("F j, Y, g:i a",strtotime($cart['date_added'])));
    $pdf->Ln();
    $pdf->Cell(40,10,"Bought by: {$cart['email']}");
    $pdf->Ln();

    $titles = array("Product Name","Quantity","Price","Total");
    $pdf->FancyTable($titles,$cart);

    $pdf->Ln();
    $pdf->SetFont('Arial','B',13);
    $pdf->Cell($pdf->GetPageWidth()-21,20,"Total Cost: Rs. {$cart['total_cost']}",0,0,'R',0);

    if(count($carts)-1 != $i){
        $pdf->AddPage();
    }
}


$pdf->Output();
//var_dump($order);
// Colored table

?>
