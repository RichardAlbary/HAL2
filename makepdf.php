<?php
// Get the required library for making the PDF
require('includes/fpdf/fpdf.php');

// Get the other libraries required for inserting into the database
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Define a bunch of variables from form data
$servicecounter = $_POST['ServiceCounter'];
$currencysign = iconv("UTF-8", "ISO-8859-1", "£");
$services = array();
$prices = array();
$notes = array();
$servicenumbers = array();
$x = 0;
while ($x < $servicecounter) {
    array_push($services, $_POST['service'][$x]);
    array_push($prices, $_POST['price'][$x]);
    array_push($notes, $_POST['notes'][$x]);
    array_push($servicenumbers, $_POST['serviceNumber'][$x]);
    $x++;
}
$title = $_POST['jobTitle'];

// Function for rounding properly
function vattotal($number) {
    // load argument into local result variable
    $result = $number;
    // multiply by 0.2, but use 3 points
    $result = bcmul($result, 0.2, 3);
    // check if the final character is 5 of higher
    $test = substr($result, -1);
    if ($test == '5' || $test == '6' || $test == '7' || $test == '8' || $test == '9') {
        $result = bcadd($result, 0.01, 2);
    }
    // if final character is under 5, trim the string down to 2 decimal places
    else {
        $result = bcadd($result, 0, 2);
    }
    // result
    return $result;
}

// Client details from form
$clientName = $_POST['ClientName'] . " " . $_POST['ClientSurname'];
$addressLine1 = $_POST['AddressLine1'];
$addressLine2 = $_POST['AddressLine2'];
$city = $_POST['City'];
$county = $_POST['County'];
$country = $_POST['Country'];
$postcode = $_POST['Postcode'];
$jobnumber = $_POST['JobNumber'];
$clientnumber = $_POST['ClientNumber'];
$vatapplicable = $_POST['vatStatus'];

// New line variable
$nl = "\n";

// Make the PDF invoice
// Fill the object with all the good PDFy stuff
$pdf = new FPDF();

// Page setup bits
$pdf->AddFont('HelveticaNeue', '', 'helveticaneue.php');
$pdf->AddFont('HelveticaNeue', 'B', 'HelveticaNeueMed.php');
$pdf->SetTopMargin(40);
$pdf->SetLeftMargin(30);
$pdf->SetFillColor(230);
$pdf->SetTextColor(129);
$pdf->SetDrawColor(129);

$pdf->AddPage();
$pdf->SetFont('HelveticaNeue', '', 10);

// Spiffing logo
$pdf->Image('includes/fpdf/spiffinglogo.jpg',21,19,90,16);

// Client name and address section
$pdf->SetXY(30,50);
$pdf->Write(4,$clientName . $nl . $addressLine1 . $nl . $addressLine2 . $nl . $city . $nl . $county . $nl . $postcode . $nl . $country);

// Invoice number and date section
$pdf->SetXY(156,50);
$pdf->SetFont('HelveticaNeue', 'B', 10);
$pdf->Write(4,"Invoice #");

$pdf->SetXY(159,63);
$pdf->SetFont('HelveticaNeue', 'B', 10);
$pdf->Write(4,"Date");
$pdf->SetXY(150,68);
$pdf->SetFont('HelveticaNeue', '', 10);
//This is where to insert the date
$date = date("d/m/Y");
$pdf->Cell(29,5,$date,0,0,'C',true);

// Details bar background
$pdf->Image('includes/fpdf/bar-background.jpg',150,100,29,171);

// Service details section
$pdf->SetXY(30,100);
$pdf->SetFont('HelveticaNeue', 'B', 10);
$pdf->Write(4,"Project");
$pdf->Line(30,105,179,105);
$pdf->SetXY(30,108);
//This is where to insert the project title
$pdf->Write(4,$title);
$pdf->SetXY(30,115);
// Tester line to replace the 'Details' one
//$test = substr($prices[0], 2);
//$pdf->Write(4,$test);
// The real line to replace the one above
$pdf->Write(4,"Details");

// This is where to put in the service details

$ycoordinate = 125;
$x = 0;

// This is where to put the goods total
$workingtotal = bcadd(0,0,2);

while ($x < $servicecounter) {
    $pdf->SetXY(30,$ycoordinate);
    $pdf->SetFont('HelveticaNeue', '', 10);
    $servicename = $services[$x];
    if ($servicename == null) {
        $x++;
        continue;
    }
    // This is where to put the service names
    // $pdf->Write(4,"This invoice is for ".$servicecounter." services");
    $pdf->Write(4,$servicename);
    $pdf->SetXY(150,$ycoordinate);
    // This is where to put the service total
    $serviceprice = $prices[$x];
    $serviceprice1 = substr($serviceprice, 2);
    
    $totalsofar = bcadd($workingtotal, 0, 2);
    $workingtotal = bcadd($totalsofar, $serviceprice1, 2);
    
    $pdf->Cell(25,5,$currencysign.$serviceprice1,0,0,'R');
    $ycoordinate = $ycoordinate + 7;
    echo $notes[$x];
    if ($notes[$x] != null) {
        $ycoordinate = $ycoordinate - 3;
        $pdf->SetXY(33, $ycoordinate);
        $pdf->Write(4,"- ".$notes[$x]);
        $ycoordinate = $ycoordinate + 7;
    }
    $x++;
}

$pdf->Line(30,235,179,235);

// VAT Details section
$pdf->Image('includes/fpdf/vatdetails.jpg',30,239,67,29);
$pdf->Ln();

// Totals section
// Labels
$pdf->SetFont('HelveticaNeue', 'B', 10);
$pdf->SetXY(124,240);
$pdf->Cell(25,5,"Goods total",0,0,'R');
$pdf->SetXY(124,245);
$pdf->Cell(25,5,"VAT total",0,0,'R');
$pdf->SetTextColor(167,76,84);
$pdf->SetXY(124,260);
$pdf->Cell(25,5,"Invoice total",0,0,'R');

// Figures
$pdf->SetFont('HelveticaNeue', '', 10);
$pdf->SetTextColor(129);
$pdf->SetXY(150,240);

$nonvattotal = $workingtotal;
$pdf->Cell(25,5,$currencysign.$nonvattotal,0,0,'R');
$pdf->SetXY(150,245);

// This is where to put the vat total
if ($vatapplicable == "Yes") {
    $vattotal = vattotal($nonvattotal);
}
else {
    $vattotal = "0.00";
}
$pdf->Cell(25,5,$currencysign.$vattotal,0,0,'R');

$pdf->SetXY(150,260);
// This is where to put the invoice total
$grandtotal = bcadd($nonvattotal, $vattotal, 2);
$pdf->Cell(25,5,$currencysign.$grandtotal,0,0,'R');

// Footer Section
$pdf->SetFont('HelveticaNeue', '', 6);
$pdf->SetXY(30,270);
$pdf->MultiCell(149,3,"SpiffingCovers, 6 Jolliffe's Court, 51-57 High Street, Wivenhoe, Colchester, Essex, CO7 9AZ, United Kingdom \nTel: +44 (0) 1206 585200 Email: enquiries@spiffingcovers.com Web: spiffingcovers.com",0);

// Insert a new entry into the database
$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);


// Create an object for the Invoice table record we are going to insert.
$object = new stdClass();

// Must be a valid primary key value.
$object->ClientNumber = $clientnumber;
$datefordatabase = date("Y/m/d");
$object->Date = $datefordatabase;
$object->GoodsTotal = $nonvattotal;
$object->VATTotal = $vattotal;
$object->GrandTotal = $grandtotal;
$object->JobNumber = $jobnumber;

// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->insertObject('o7ot5_invoices', $object, 'InvoiceNumber');
$invoiceNumber = $object->InvoiceNumber;

// Iterate over all of the included services to give them InvoiceNumber entries

$x = 0;

while ($x < $servicecounter) {
    if ($services[$x] == null) {
        $x++;
        continue;
    }
    
    // Create an object to update the service table record.
    $object = new stdClass();

    // Must be a valid primary key value.
    $object->ServiceNumber = $servicenumbers[$x];
    $object->InvoiceNumber = $invoiceNumber;
    echo $idtoupdate;
    // Update their details in the users table using id as the primary key.
    $result = JFactory::getDbo()->updateObject('o7ot5_service', $object, 'ServiceNumber');
    $x++;
}

// Finishes off the invoice with the invoice number
$pdf->SetXY(150,55);
$pdf->SetFont('HelveticaNeue', '', 10);
//This is where to insert the invoice number
$pdf->Cell(29,5,$invoiceNumber,0,0,'C',true);

// Output the PDF
$filepath = 'invoices/invoice'. $invoiceNumber .'.pdf';
$pdf->Output(F, $filepath);

header('Location: '.$filepath);
?>