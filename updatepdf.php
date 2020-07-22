<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Get the required library for making the PDF

require_once('includes/fpdf/fpdf.php');
require_once('includes/fpdi/autoload.php');
require_once('includes/fpdi/Fpdi.php');
$currencysign = iconv("UTF-8", "ISO-8859-1", "£");

$pdf = new \setasign\Fpdi\Fpdi();
// add a page
$pdf->AddPage();
// set the source file

$pdf->setSourceFile('invoices/invoice8016.pdf');

// import page 1
$tplId = $pdf->importPage(1);

// use the imported page and place it at point 10,10 with a width of 100 mm
$pdf->useTemplate($tplId, 1, 1, 1, 1, true);

if ($pdf != null) {
    $filepath = 'invoices/invoice8016updated.pdf';
    $pdf->Output($filepath, "F");
}

else {

}



//header('Location: '.$filepath);
?>