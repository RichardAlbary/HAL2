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
$jobtitle = $_POST['jobTitle'];
$deadlineforjob = $_POST['deadlineForJob'];
$jobStatus = $_POST['jobStatus'];
$furtherDetails = $_POST['furtherDetails'];
$VATStatus = $_POST['VATStatus'];
$VATExemptionReason = $_POST['VATExemptionReason'];
$clientNumber = $_POST['clientNumber'];

// Create an object for the record we are going to create.
$object = new stdClass();

// Bind variable of POST data to object.
$object->ClientNumber = $clientNumber;
$object->JobTitle = $jobtitle;
$object->DeadlineForJob = $deadlineforjob;
$object->JobStatus = $jobStatus;
$object->FurtherDetails = $furtherDetails;
$object->VATable = $VATStatus;
$object->VATExemptionReason = $VATExemptionReason;

// Post job details in the job table.
$result = JFactory::getDbo()->insertObject('o7ot5_jobs', $object, 'JobNumber');

// Obtain the job number of the newly created entry.
$jobNumber = $object->JobNumber;

// Redirect site user to newly created job page
header('Location: /jobs/job-detail?'.$jobNumber);

?>
