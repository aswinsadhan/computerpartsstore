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
    WHERE tbl_purchase_master.date_added >= ? AND tbl_purchase_master.date_added <= ? AND tbl_purchase_master.status = 1
    ORDER BY tbl_purchase_master.status DESC,tbl_purchase_master.date_added";

$NORMAL_SQL = "SELECT 
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
    ORDER BY tbl_purchase_master.status DESC,tbl_purchase_master.date_added";


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

function FancyTable($header, $purchase)
{
    // Colors, line width and bold font
    $this->SetFillColor(255,255,255);
    $this->SetTextColor(0);
    //$this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.5);
    $this->SetFont('Arial','B',10);
    // Header
    $w = array(110, 18, 15, 15,30);
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

    foreach ($purchase['purchase_items'] as $i => $product) 
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
        $this->MultiCell($w[2],15,$product['selling_price'],'LRBT','C',$fill);
        $this->SetXY($x, $y);
        $x = $this->GetX() + $w[3];
        $this->MultiCell($w[3],15,$product['cost_price'],'LRBT','R',$fill);
        $this->SetXY($x, $y);
        $x = $this->GetX() + $w[4];
        $this->MultiCell($w[4],15,$product['cost_price'] * $product['quantity'] ,'LRBT','R',$fill);
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
$pdf->Cell(190,10,'Purchase Report | hardwareworld.xyz',0,0,'C');
// Line break
$pdf->Ln(20);

foreach ($purchases as $i => $purchase) {
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(100,10,"Purchase Number : {$purchase['purchase_master_id']}");
    $pdf->Ln();
    $pdf->Cell(40,10,"Purchase date : ".date("F j, Y, g:i a",strtotime($purchase['date_added'])));
    $pdf->Ln();
    $pdf->Cell(40,10,"Added by: {$purchase['staff_email']}");
    $pdf->Ln();
    $pdf->Cell(40,10,"Vendor:  {$purchase['vendor_name']}");
    $pdf->Ln();

    $titles = array("Product Name","Quantity","Price","Cost","Total Cost");
    $pdf->FancyTable($titles,$purchase);

    $pdf->Ln();
    $pdf->SetFont('Arial','B',13);
    $pdf->Cell($pdf->GetPageWidth()-21,20,"Total Cost: Rs. {$purchase['total_cost']}",0,0,'R',0);

    if(count($purchases)-1 != $i){
        $pdf->AddPage();
    }
}


$pdf->Output();
//var_dump($order);
// Colored table

?>
