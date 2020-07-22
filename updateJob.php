<?php

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

$jobtitle = $_POST['jobTitle'];
$deadlineforjob = $_POST['deadlineForJob'];
$jobStatus = $_POST['jobStatus'];
$furtherDetails = $_POST['furtherDetails'];
$VATStatus = $_POST['VATStatus'];
$VATExemptionReason = $_POST['VATExemptionReason'];
$jobNumber = $_POST['jobNumber'];
// Create an object for the record we are going to update.
$object = new stdClass();

// Must be a valid primary key value.
$object->JobNumber = $jobNumber;
$object->JobTitle = $jobtitle;
$object->DeadlineForJob = $deadlineforjob;
$object->JobStatus = $jobStatus;
$object->FurtherDetails = $furtherDetails;
$object->VATable = $VATStatus;
$object->VATExemptionReason = $VATExemptionReason;

// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('o7ot5_jobs', $object, 'JobNumber');
print_r($object);
header('Location: /jobs/job-detail?'.$jobNumber);

?>