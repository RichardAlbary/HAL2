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
$invoiceNumber = $_POST['InvoiceNumber'];
$clientNumber = $_POST['ClientNumber'];
$value = $_POST['Value'];
$date = $_POST['Date'];
$payeename = $_POST['PayeeName'];
$jobnumber = $_POST['JobNumber'];

// Create an object for the record we are going to create.
$object = new stdClass();

// Bind variable of POST data to object.
$object->InvoiceNumber = $invoiceNumber;
$object->ClientNumber = $clientNumber;
$object->Value = $value;
$object->Date = $date;
$object->PayeeName = $payeename;
$object->JobNumber = $jobnumber;

// Insert payment data into payments table.
$result = JFactory::getDbo()->insertObject('o7ot5_payments', $object, 'PaymentNumber');


// Update the client details with the payee name, if they have paid under a new name
// Get a db connection
$db = JFactory::getDBO();

// Create a new Query object.
$clientquery = $db->getQuery(true);

// db query for client details
$clientquery
    ->select('*')
    ->from($db->quoteName('o7ot5_client'))
    ->where($db->quoteName('ClientNumber')." = " . $clientNumber);

// Reset the query using out newly populated query object
$db->setQuery($clientquery);

// Load the results as a list of stdClass objects
$clientResults = $db->loadObjectList();
$clientResult = $clientResults[0];

$clientname = $clientResult->ClientName . " " . $clientResult->ClientSurname;
$otherpayeename1 = $clientResult->OtherPayeeName1;
$otherpayeename2 = $clientResult->OtherPayeeName2;
// Check the details of the client, see if the payee name matches the client name, or either of the other payee names
if (strpos($clientname, $payeename) === false) {
    if (strpos($otherpayeename1, $payeename) !== false || strpos($otherpayeename2, $payeename) !== false) {
        
    }
    else if ($otherpayeename1 == null) {
        $clientResult->OtherPayeeName1 = $payeename;
    }
    else if ($otherpayeename2 == null) {
        $clientResult->OtherPayeeName2 = $payeename;
    }
}

// Update their details in the client table using id as the primary key.
$result = JFactory::getDbo()->updateObject('o7ot5_client', $clientResult, 'ClientNumber');


// Update the invoice entry with the amount that has been received
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

$invoiceAmountReceived = $invoiceResult->AmountReceived;
$invoiceAmountReceived = bcadd($invoiceAmountReceived, $value, 2);
$invoiceResult->AmountReceived = $invoiceAmountReceived;

// Update invoice details in the invoices table using id as the primary key.
$result = JFactory::getDbo()->updateObject('o7ot5_invoices', $invoiceResult, 'InvoiceNumber');


// Get the invoice details, which will now include the amount received. Allow for all 3 totals, as well as possible discount.
// $invoiceAmountReceived already set
$invoiceGoodsTotal = $invoiceResult->GoodsTotal;
$invoiceVATTotal = $invoiceResult->VATTotal;
$invoiceGrandTotal = $invoiceResult->GrandTotal;
if ($invoiceResult->DiscountAmount != null) {
    $invoiceTotalIncludingDiscount = bcsub($invoiceGrandTotal, $invoiceResult->DiscountAmount, 2);
}
else if ($invoiceResult->DiscountPercentage != null) {
    $discountPercentage = $invoiceResult->DiscountPercentage;
    $invoiceTotalDiscount = bcdiv($invoiceGrandTotal, $discountPercentage, 2);
    $invoiceTotalIncludingDiscount = bcsub($invoiceGrandTotal, $invoiceTotalDiscount, 2);
}
$invoiceAmountToPay = bcsub($invoiceTotalIncludingDiscount, $invoiceAmountReceived, 2);
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
$pdf->SetXY(124,255);
$pdf->Cell(25,5,"Amount received",0,0,'R');

// Figures
$pdf->SetFont('HelveticaNeue', '', 10);
$pdf->SetXY(150,240);

$pdf->Cell(25,5,$currencysign.$invoiceGoodsTotal,0,0,'R');
$pdf->SetXY(150,245);
// This is where to put the vat total
$pdf->Cell(25,5,$currencysign.$invoiceVATTotal,0,0,'R');

// Discount Section
if ($invoiceResult->DiscountPercentage != null || $invoiceResult->DiscountAmount != null) {
    $pdf->SetXY(124, 250);
    $pdf->SetFont('HelveticaNeue', 'B', 10);
    $pdf->Cell(25,5,"Discount",0,0,'R');
    $pdf->SetXY(150, 250);
    $pdf->SetFont('HelveticaNeue', '', 10);
    if ($invoiceResult->DiscountAmount != null) {
        $pdf->Cell(25,5,$currencysign.$invoiceResult->DiscountAmount,0,0,'R');
    }
    else if ($invoiceResult->DiscountPercentage != null) {
        $pdf->Cell(25,5,$invoiceResult->DiscountPercentage.'%',0,0,'R');
    }
}

$pdf->SetXY(150,255);
$pdf->Cell(25,5,$currencysign.$invoiceAmountReceived,0,0,'R');

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

// Redirect user to invoicing page for the job
header('Location: /jobs/invoicing?'.$jobnumber);

?>
