<?php

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

$servicename = $_POST['serviceName'];
$servicetotal = $_POST['serviceTotal'];
$jobnumber = $_POST['jobNumber'];

// Create an object for the record we are going to update.
$object = new stdClass();

// Must be a valid primary key value.
$object->JobNumber = $jobnumber;
$object->ServiceName = $servicename;
$object->ServicePrice = $servicetotal;
$object->ServiceStatus = "Pending";

// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->insertObject('o7ot5_service', $object);
$jobNumber = $object->JobNumber;
header('Location: /jobs/job-detail?'.$jobNumber);

?>