<?php

// Standard required stuff

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

// End of required stuff


// This is the good bit

// Get a db connection.
$db = JFactory::getDbo();

// Create a new query objects.
$jobquery = $db->getQuery(true);
$clientquery = $db->getQuery(true);
$invoicequery = $db->getQuery(true);

// db query for job details
$jobquery
    ->select('*')
    ->from($db->quoteName('o7ot5_jobs'));

// Reset the query using our newly populated query object.
$db->setQuery($jobquery);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
$jobresults = $db->loadObjectList();


// db query for client details
$clientquery
    ->select('*')
    ->from($db->quoteName('o7ot5_client'));

// Reset the query using our newly populated query object.
$db->setQuery($clientquery);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
$clientresults = $db->loadObjectList();


// db query for invoice details
$invoicequery
    ->select('*')
    ->from($db->quoteName('o7ot5_invoices'));

// Reset the query using our newly populated query object.
$db->setQuery($invoicequery);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
$invoiceresults = $db->loadObjectList();


// Create a new xml file with all the details loaded into arrays

$myFile = "searchlinks.xml";
$fh = fopen($myFile, 'w') or die("can't open file");

$myFile = '<?xml version="1.0" encoding="utf-8"?>';
$myFile .= "<xml version='2.0'>";
$myFile .= '<pages>';
// here is where to do all the iterative stuff.
foreach ($jobresults as $jobresult) {
    $myFile .= '<link>';
    $myFile .= '<title>' . $jobresult -> JobTitle . '</title>';
    $myFile .= '<url>http://hal.spiffingtestdomain.com/jobs/job-detail?' . $jobresult -> JobNumber . '</url>';
    $myFile .= '<type>Job</type>';
    $myFile .= '</link>';
}
foreach ($clientresults as $clientresult) {
    $myFile .= '<link>';
    $myFile .= '<title>' . $clientresult -> ClientName . ' ' . $clientresult -> ClientSurname . '</title>';
    $myFile .= '<url>http://hal.spiffingtestdomain.com/clients/client-detail?' . $clientresult -> ClientNumber . '</url>';
    $myFile .= '<type>Client</type>';
    $myFile .= '</link>';
    if ($clientresult -> OtherPayeeName1 != null) {
        $myFile .= '<link>';
        $myFile .= '<title>' . $clientresult -> OtherPayeeName1 . '</title>';
        $myFile .= '<url>http://hal.spiffingtestdomain.com/clients/client-detail?' . $clientresult -> ClientNumber . '</url>';
        $myFile .= '<type>Other Payee Name</type>';
        $myFile .= '</link>';
    }
    if ($clientresult -> OtherPayeeName2 != null) {
        $myFile .= '<link>';
        $myFile .= '<title>' . $clientresult -> OtherPayeeName2 . '</title>';
        $myFile .= '<url>http://hal.spiffingtestdomain.com/clients/client-detail?' . $clientresult -> ClientNumber . '</url>';
        $myFile .= '<type>Other Payee Name</type>';
        $myFile .= '</link>';
    }
}
foreach ($invoiceresults as $invoiceresult) {
    $myFile .= '<link>';
    $myFile .= '<title>' . $invoiceresult -> InvoiceNumber . '</title>';
    $myFile .= '<url>http://hal.spiffingtestdomain.com/jobs/job-detail?' . $invoiceresult -> JobNumber . '</url>';
    $myFile .= '<type>Invoice</type>';
    $myFile .= '</link>';
}
$myFile .= '</pages>';
$myFile .= '</xml>';

fwrite($fh, $myFile);
fclose($fh);


$response = "Loaded " . count($jobresults) . " jobs, " . count($clientresults) . " clients, & " . count($invoiceresults) . " invoices.";
echo $response;

?>