<?php
// Load necessary libraries
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

// Initiate variables, assign values from POST data
$servicename = $_POST['serviceName'];
$servicetotal = $_POST['serviceTotal'];
$jobnumber = $_POST['jobNumber'];

// Create an object for the record we are going to create.
$object = new stdClass();

// Bind variable of POST data to object.
$object->JobNumber = $jobnumber;
$object->ServiceName = $servicename;
$object->ServicePrice = $servicetotal;
$object->ServiceStatus = "Pending";

// Post service details to service table.
$result = JFactory::getDbo()->insertObject('o7ot5_service', $object);

// Get the primary key for newly created job
$jobNumber = $object->JobNumber;

// Redirect user to newly created page
header('Location: /jobs/job-detail?'.$jobNumber);

?>
