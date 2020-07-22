<?php
// Load necessary libraries
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once('includes/fpdf/fpdf.php');
require_once('includes/fpdi/autoload.php');
require_once('includes/fpdi/Fpdi.php');
$currencysign = iconv("UTF-8", "ISO-8859-1", "£");

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

// Initiate variables, assign values from POST data
$invoiceNumber = $_POST['DiscountInvoiceNumber'];
$clientNumber = $_POST['DiscountClientNumber'];
$discountAmount = bcadd($_POST['DiscountAmount'], 0, 2);
$discountType = $_POST['DiscountType'];
$jobnumber = $_POST['DiscountJobNumber'];

// Update the invoice entry with the discount settings
// Get a db connection
$db = JFactory::getDBO();

// Create a new Query object.
$invoicequery = $db->getQuery(true);

// db query for client details
$invoicequery
    ->select('*')
    ->from($db->quoteName('o7ot5_invoices'))
    ->where($db->quoteName('InvoiceNumber')." = " . $invoiceNumber);

// Reset the query using out newly populated query object
$db->setQuery($invoicequery);

// Load the results as a list of stdClass objects
$invoiceResults = $db->loadObjectList();
$invoiceResult = $invoiceResults[0];

// Get the current invoice total
$invoiceTotal = $invoiceResult->GoodsTotal;

// Record the discount for the DB entry and process the discount's effect on the total
if ($discountType == "Percentage") {
    $invoiceResult->DiscountPercentage = $discountAmount;

    // Calculate new invoice total
    $percentageToReduceBy = $discountAmount;

    // Calculate the amount to reduce the total by
    $amountToReduceBy = ($invoiceTotal / 100) * $percentageToReduceBy;

    // Apply the discount amount to the total
    $invoiceTotal = bcsub($invoiceTotal, $amountToReduceBy, 2);
}

else if ($discountType == "Value") {
    $invoiceResult->DiscountAmount = $discountAmount;
    
    // Apply the discount amount to the total
    $invoiceTotal = bcsub($invoiceTotal, $discountAmount, 2);
}

// Replace the invoice total with the newly calculated total
$discountedInvoiceGoodsTotal = $invoiceTotal;


// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('o7ot5_invoices', $invoiceResult, 'InvoiceNumber');

// Get the invoice details, which will now include the amount received.
$invoiceAmountReceived = $invoiceResult->AmountReceived;
// $invoiceGoodsTotal already set
$invoiceGoodsTotal = $invoiceResult->GoodsTotal;
$invoiceVATTotal = bcdiv($invoiceGoodsTotal, 5, 2);
$invoiceGrandTotal = bcadd($invoiceGoodsTotal, $invoiceVATTotal, 2);
// Discounting a percentage
if ($discountType == "Percentage") {
    // Need to get invoiceGrandTotal with discount rate applied
    $discountMultiplier = bcdiv(100, $discountAmount, 2);
    $invoiceTotalDiscount = bcdiv($invoiceGrandTotal, $discountMultiplier, 2);
    $invoiceTotalIncludingDiscount = bcsub($invoiceGrandTotal, $invoiceTotalDiscount, 2);
    $invoiceAmountToPay = bcsub($invoiceTotalIncludingDiscount, $invoiceAmountReceived, 2);
}
// Discounting an amount
else if ($discountType == "Value") {
    $invoiceTotalIncludingDiscount = bcsub($invoiceGrandTotal, $discountAmount, 2);
    $invoiceAmountToPay = bcsub($invoiceTotalIncludingDiscount, $invoiceAmountReceived, 2);
}

$zero = bcadd(0,0,2);
$paid = false;
if ($invoiceAmountToPay === $zero) {
    $paid = true;
}

// Using the invoice details obtained above, overprint new content on the invoice showing amount paid, and amount outstanding.

$pdf = new \setasign\Fpdi\Fpdi();
$pdf->AddFont('HelveticaNeue', '', 'helveticaneue.php');
$pdf->AddFont('HelveticaNeue', 'B', 'HelveticaNeueMed.php');

// add a page
$pdf->AddPage();
// set the source file

$pdf->setSourceFile('invoices/invoice' . $invoiceNumber . '.pdf');

// import page 1
$tplId = $pdf->importPage(1);

// use the imported page and place it at point 10,10 with a width of 100 mm
$pdf->useTemplate($tplId, 0, 0);

$pdf->Image('includes/fpdf/bar-background.jpg',150,240,29,31);

// Totals section
// Labels
$pdf->SetFont('HelveticaNeue', 'B', 10);
$pdf->SetTextColor(129);
if ($invoiceAmountReceived != null) {
    $pdf->SetXY(124,255);
    $pdf->Cell(25,5,"Amount received",0,0,'R');
}

// Figures
$pdf->SetFont('HelveticaNeue', '', 10);
$pdf->SetXY(150,240);

$pdf->Cell(25,5,$currencysign.$invoiceGoodsTotal,0,0,'R');
$pdf->SetXY(150,245);
// This is where to put the vat total
$pdf->Cell(25,5,$currencysign.$invoiceVATTotal,0,0,'R');

// HERE IS THE ACTUAL CODE FOR THIS UPDATE
$pdf->SetTextColor(129);
$pdf->SetFont('HelveticaNeue', 'B', 10);
$pdf->SetXY(124, 250);
$pdf->Cell(25,5,"Discount",0,0,'R');
$pdf->SetXY(150, 250);
$pdf->SetFont('HelveticaNeue', '', 10);
if ($discountType == "Value") {
    $pdf->Cell(25,5,$currencysign.$discountAmount,0,0,'R');
}
else if ($discountType == "Percentage") {
    $pdf->Cell(25,5,$discountAmount.'%',0,0,'R');
}
if ($invoiceAmountReceived != null) {
    $pdf->SetXY(150,255);
    $pdf->Cell(25,5,$currencysign.$invoiceAmountReceived,0,0,'R');
}
$pdf->SetXY(150,260);
// This is where to put the invoice total
$pdf->Cell(25,5,$currencysign.$invoiceAmountToPay,0,0,'R');

if($paid != false) {
    $pdf->Image('includes/fpdf/paid.jpg',141,5,44,44);
}

if ($pdf != null) {
    echo "this far";
    $filepath = 'invoices/invoice' . $invoiceNumber . '.pdf';
    $pdf->Output($filepath, "F");
}

else {

}

// Redirect user to updated page
header('Location: /jobs/invoicing?'.$jobnumber);

?>
