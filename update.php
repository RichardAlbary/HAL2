<?php

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);  
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);

$firstName = $_POST['firstName'];
$surname = $_POST['surname'];
$emailaddress = $_POST['emailaddress'];
$addressLine1 = $_POST['addressLine1'];
$addressLine2 = $_POST['addressLine2'];
$city = $_POST['city'];
$county = $_POST['county'];
$country = $_POST['country'];
$postcode = $_POST['postcode'];
$phoneNumber1 = $_POST['phoneNumber1'];
$phoneNumber2 = $_POST['phoneNumber2'];
$otherPayee1 = $_POST['otherPayee1'];
$otherPayee2 = $_POST['otherPayee2'];
$clientNumber = $_POST['clientNumber'];
// Create an object for the record we are going to update.
$object = new stdClass();

// Must be a valid primary key value.
$object->ClientNumber = $clientNumber;
$object->ClientName = $firstName;
$object->ClientSurname = $surname;
$object->ClientEmailAddress = $emailaddress;
$object->AddressLine1 = $addressLine1;
$object->AddressLine2 = $addressLine2;
$object->City = $city;
$object->County = $county;
$object->Country = $country;
$object->Postcode = $postcode;
$object->PhoneNumber1 = $phoneNumber1;
$object->PhoneNumber2 = $phoneNumber2;
$object->OtherPayeeName1 = $otherPayee1;
$object->OtherPayeeName2 = $otherPayee2;

// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('o7ot5_client', $object, 'ClientNumber');

header('Location: /clients/client-detail?'.$clientNumber);

?>