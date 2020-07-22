<?php

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

$servicename = $_POST['serviceName'];

if ($_POST['deadlineForService'] != null) {
    $deadlineForService = $_POST['deadlineForService'];
}
else {
    $deadlineForService == 0000-00-00;
}
$serviceStatus = $_POST['serviceStatus'];
$servicePrice = $_POST['priceForService'];
$notes = $_POST['notes'];
$servicenumber = $_POST['serviceNumber'];
$jobNumber = $_POST['jobNumber'];
// Create an object for the record we are going to update.
$object = new stdClass();

// Must be a valid primary key value.
$object->ServiceNumber = $servicenumber;
$object->ServiceName = $servicename;
$object->ServicePrice = $servicePrice;
$object->ServiceDeadline = $deadlineForService;
$object->ServiceStatus = $serviceStatus;
$object->Notes = $notes;

// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('o7ot5_service', $object, 'ServiceNumber');
print_r($object);
header('Location: /jobs/services?'.$jobNumber);

?>