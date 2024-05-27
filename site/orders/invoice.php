<?php
include("../../config/all_config.php"); 
include("../../lib/all_lib.php"); 
require("../../lib/fpdf.php");

check_auth_redirect_if_not();
check_role_or_redirect("customer");

if(!isset($_GET['id'])){
    redirect('/site/orders/');
}
if(empty($_GET['id'])){
    redirect('/site/orders/');
}
if(!is_numeric($_GET['id'])){
    redirect('/site/orders/');
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT 
    tbl_payment.order_id, 
    tbl_customer.customer_id,
    tbl_customer.customer_fname,
    tbl_customer.customer_lname,
    tbl_customer.customer_house_name,
    tbl_customer.customer_street,
    tbl_customer.customer_city,
    tbl_customer.customer_state,
    tbl_customer.customer_pincode,
    tbl_customer.customer_phone,
    tbl_cart_master.cart_master_id,
    tbl_cart_master.status,
    tbl_payment.date as date_added,
    tbl_courier.courier_name,
    tbl_courier.courier_building_name,
    tbl_courier.courier_street,
    tbl_courier.courier_city,
    tbl_courier.courier_state,
    tbl_courier.courier_pincode,
    tbl_courier.courier_phone,
    card_no,
    card_name,
    delivery_date
    FROM tbl_payment
    INNER JOIN tbl_order
        ON tbl_order.order_id = tbl_payment.order_id
    INNER JOIN tbl_cart_master
        ON tbl_order.cart_master_id = tbl_cart_master.cart_master_id
    INNER JOIN tbl_customer
        ON tbl_cart_master.customer_id = tbl_customer.customer_id
    INNER JOIN tbl_delivery
        ON tbl_payment.payment_id = tbl_delivery.payment_id
    INNER JOIN tbl_courier
        ON tbl_payment.courier_id = tbl_courier.courier_id
    INNER JOIN tbl_card
        ON tbl_payment.card_id = tbl_card.card_id
    WHERE 
        tbl_customer.email = ? AND tbl_payment.payment_id = ? AND (
        tbl_cart_master.status = 'payment-complete'
        OR tbl_cart_master.status = 'shipped'
        OR tbl_cart_master.status = 'in-transit'
        OR tbl_cart_master.status = 'delivered'
        OR tbl_cart_master.status = 'out-for-delivery' )
    ORDER BY date_added DESC
");

$stmt->bind_param("si",$_SESSION['user']['email'],$id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
getCartItmes($orders,true);
$order = $orders[0];



//$stmt = $db->prepare("SELECT tbl_cart_master.cart_master_id FROM tbl_cart_master
    //INNER JOIN tbl_customer
        //ON tbl_cart_master.customer_id = tbl_customer.customer_id
    //WHERE tbl_customer.email = ?
    //AND tbl_cart_master.cart_master_id = ?
//");

//$stmt->bind_param("si", $_SESSION['user']['email'],$id);
//$stmt->execute();
//$cart_master = $stmt->get_result()->fetch_assoc();
//
//

class PDF extends FPDF
{
function Header(){
}
// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

function FancyTable($header, $data)
{
    global $order;
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


    foreach ($order['products'] as $i => $product) 
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
        $this->MultiCell($w[2],15,getOldProductPrice($product['product_id'],$order['order_id']),'LRBT','C',$fill);
        $this->SetXY($x, $y);
        $x = $this->GetX() + $w[3];
        $this->MultiCell($w[3],15,(getOldProductPrice($product['product_id'],$order['order_id']) * $product['quantity']),'LRBT','R',$fill);
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
$pdf->Cell(190,10,'Invoice | hardwareworld.xyz',0,0,'C');
// Line break
$pdf->Ln(20);


$pdf->SetFont('Arial','',12);

$pdf->Cell(100,10,"Order Number : {$order['order_id']}");
$pdf->Ln();

$pdf->Cell(40,10,"Payment date : ".date("F j, Y, g:i a",strtotime($order['date_added'])));
$pdf->Ln();

$card_no = str_repeat('*', strlen($order['card_no']) - 4) . substr($order['card_no'], -4);
$pdf->Cell(40,10,"Card Used : {$card_no}");
$pdf->Ln();

$pdf->SetFont('Arial','B',10);
$pdf->SetXY(120,30);
$pdf->Cell(40,5,"Billed To :");
$pdf->Ln();
$pdf->SetFont('Arial','',10);
$pdf->SetXY(120,35);
$address = "{$order['customer_fname']} {$order['customer_lname']} 
${order['customer_house_name']} 
${order['customer_street']}
${order['customer_city']}
${order['customer_state']}
${order['customer_pincode']}
";
$pdf->MultiCell(50,5,$address,'','L',0);

$pdf->SetFont('Arial','B',10);
$pdf->SetXY(160,30);
$pdf->Cell(40,5,"Delivery By: ");
$pdf->Ln();
$pdf->SetFont('Arial','',10);
$pdf->SetXY(160,35);
$address = "{$order['courier_name']} 
${order['courier_building_name']} 
${order['courier_street']}
${order['courier_city']} 
${order['courier_state']}
${order['courier_pincode']}
Phone: ${order['courier_phone']}

";

$pdf->SetFont('Arial','',10);
$pdf->MultiCell(50,5,$address,'','L',0);

$titles = array("Product Name","Quantity","Price","Total");
$pdf->FancyTable($titles,array());

$pdf->Ln();
$pdf->SetFont('Arial','B',13);
$pdf->Cell($pdf->GetPageWidth()-21,20,"Subtotal: Rs. {$order['subtotal']}",0,0,'R',0);

$pdf->Output();
//var_dump($order);

// Colored table


?>

