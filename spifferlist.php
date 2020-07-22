<?php
// Get a db connection.
$db = JFactory::getDbo();

// Create a new query objects.
$clientquery = $db->getQuery(true);
$jobquery = $db->getQuery(true);
$servicequery = $db->getQuery(true);
$invoicequery = $db->getQuery(true);

// db query for client details
$clientquery
    ->select($db->quoteName(array('ClientName', 'ClientSurname', 'ClientNumber')))
    ->from($db->quoteName('o7ot5_client'));

// db query for job details
$jobquery
    ->select($db->quoteName(array('JobNumber', 'JobTitle', 'ClientNumber')))
    ->from($db->quoteName('o7ot5_jobs'))
    ->where($db->quoteName('JobStatus') ." = 'Active'");

// db query for service details
$servicequery
    ->select($db->quoteName(array('JobNumber', 'ServiceName', 'ServiceStatus', 'InvoiceNumber', 'ServiceDeadline')))
    ->from($db->quoteName('o7ot5_service'));

// db query for invoice details
$invoicequery
    ->select('*')
    ->from($db->quoteName('o7ot5_invoices'));

// Reset the query using our newly populated query object.
$db->setQuery($clientquery);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
$clientresults = $db->loadObjectList();

// Reset the query using our newly populated query object.
$db->setQuery($jobquery);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
$jobresults = $db->loadObjectList();

// Reset the query using our newly populated query object.
$db->setQuery($servicequery);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
$serviceresults = $db->loadObjectList();

// Reset the query using our newly populated query object.
$db->setQuery($invoicequery);

// Load the results as a list of stdClass objects
$invoiceresults = $db->loadObjectList();

// Make a container on the page for the results
?>
<div class="spifferList">
<?php
$finalresults = [];
// Repeat over each job with an active status, assign it to an array for that client number. Add the name of the new array to an index array, too.
foreach ($jobresults as $jobresult) {
    $clientnumber = $jobresult -> ClientNumber;
    $arrayname = "activeclient".$clientnumber;
    if (${"activeclient".$clientnumber} == null) {
        $$arrayname = [$jobresult];
        array_push($finalresults, ${"activeclient".$clientnumber});
    }
    else {
        array_push(${"activeclient".$clientnumber}, $jobresult);
    }
}
// Repeat over each client and match up those with extant arrays bearing a matching ID. Remove the rest.
    $index = 0;
foreach ($clientresults as $client) {
    $clientnumber = $client -> ClientNumber;
    if (${"activeclient".$clientnumber} == null){
        unset($clientresults[$index]);
    }
    $index++;
}
// Repeat over remaining clients in array, list them out and include their relevant job details in a sub-list.
foreach ($clientresults as $client){
    ?>
    <div class="spifferlistentry">
        <div class="spiffername">
            <a target="_blank" href="/clients/client-detail?<?php echo $client -> ClientNumber; ?>">
                <?php echo $client -> ClientName . " " . $client -> ClientSurname;?>
            </a>
            <a onclick="showhide(<?php echo $client -> ClientNumber; ?>)">
                <div class="caret" id="caret<?php echo $client -> ClientNumber; ?>"></div>
            </a>
        </div>
        <div class="jobs" id="spifferjobs<?php echo $client -> ClientNumber; ?>">
        <?php
            $clientnumber = $client -> ClientNumber;
            // Iterate over the active jobs for this client, print each job title, linked to the job page.
            foreach(${"activeclient".$clientnumber} as $job) {
                ?>
                <div class="job">
                    <a target="_blank" href="/jobs/job-detail?<?php echo $job -> JobNumber; ?>"><?php echo $job -> JobTitle; ?></a>
                </div>
                <a onclick="showhideinvoice(<?php echo $job -> JobNumber; ?>)">
                    <div class="caret invoicecaret" id="invoicecaret<?php echo $job -> JobNumber; ?>"></div>
                </a>
                
                <div class="invoices" id="invoices<?php echo $job -> JobNumber; ?>">
                    <?php 
                        foreach ($invoiceresults as $invoice) {
                            if ($invoice->JobNumber == $job->JobNumber){
                                ?>
                                <div class="invoice">
                                    <div><strong>Invoice: <?php echo $invoice->InvoiceNumber; ?></strong></div>
                                    <?php
                                        $i == 0;
                                        foreach ($serviceresults as $service) {
                                            if ($service->InvoiceNumber == $invoice->InvoiceNumber) {
                                                ?>
                                                <div class="service">
                                                <?php
                                                echo $service->ServiceName;
                                                echo " - Status: ";
                                                echo $service->ServiceStatus;
                                                if ($service->ServiceStatus == 'Active') {
                                                    echo " - Deadline: ";
                                                    $deadline = $service->ServiceDeadline;
                                                    $today = date("Y-m-d");
                                                    if ($deadline > $today) {
                                                        ?> <span class="timeleft"><?php echo $deadline;?></span> <?php
                                                    }
                                                    else if ($deadline == $today) {
                                                        ?> <span class="notimeleft"><?php echo $deadline;?></span> <?php
                                                    }
                                                    else {
                                                        ?> <span class="overdue"><?php echo $deadline;?></span> <?php
                                                    }
                                                    
                                                }
                                                if ($i != 0) {
                                                    echo " ";                                                  
                                                }
                                                else {
                                                    
                                                }
                                                ?></div><?php
                                            }
                                            $i++;
                                        }
                                    
                                    $grandTotal = $invoice->GrandTotal;
                                    $discountAmount = $invoice->DiscountAmount;
                                    $discountPercentage = $invoice->DiscountPercentage;
                                    $amountPaid = $invoice->AmountReceived;
                                    if ($discountAmount != null) {
                                        $workingTotal = $grandTotal;
                                        $workingTotal = bcsub($workingTotal, $discountAmount, 2);
                                        $workingTotal = bcsub($workingTotal, $amountPaid, 2);
                                        $amountOwed = $workingTotal;
                                    }
                                    else if ($discountPercentage != null) {
                                        $workingTotal = $grandTotal;
                                        $percentage = bcsub(100, $discountPercentage, 2);
                                        $percentage = bcdiv($percentage, 100, 2);
                                        $workingTotal = bcmul($workingTotal, $percentage, 2);
                                        $workingTotal = bcsub($workingTotal, $amountPaid, 2);
                                        $amountOwed = $workingTotal;
                                    }
                                    else {
                                        $amountOwed = bcsub($grandTotal, $amountPaid, 2);
                                    }
                                    ?>
                                    
                                    <div><strong>Amount Owed: £<?php echo $amountOwed; ?></strong></div>
                                </div>
                                <?php
                                
                            }
                        }
                    ?>
                </div>
            
                <?php/*?>
                <div class="services" id="services<?php echo $job -> JobNumber; ?>">
                <?php
                // Iterate over all services for this job, print each service name, together with its status.
                foreach ($serviceresults as $service) {
                    if($service -> JobNumber == $job -> JobNumber) {
                        ?>
                            
                            <div class="service">
                                <div class="servicedetails"><?php echo $service -> ServiceName; ?> - Status: <?php echo $service -> ServiceStatus;?></div>
                            <?php
                                foreach ($invoiceresults as $invoice) {
                                    // check current service entry invoice number, check invoiceresults for that invoice
                                    
                                    if ($service->InvoiceNumber == $invoice->InvoiceNumber) {
                                        $grandtotal = $invoice->GrandTotal;
                                        $discountAmount = $invoice->DiscountAmount;
                                        $discountPercentage = $invoice->DiscountPercentage;
                                        if ($discountAmount != null) {
                                            $amountOwed = bcsub($grandtotal, $discountAmount, 2);
                                        }
                                        else if ($discountPercentage != null) {
                                            $discountPrimer = bcsub(100, $discountPercentage, 2);
                                            $discountPrimer2 = bcdiv($discountPrimer, 100, 2);
                                            $amountToPay = bcmul($grandTotal, $discountPrimer2, 2);
                                            $amountOwed = bcsub($amountToPay, $amountReceived, 2);
                                        }
                                        else {
                                            $amountOwed = $grandtotal;
                                        }
                                        $percentOwed = bcdiv($amountOwed, $grandtotal, 2);
                                        $percentOwed = bcmul($percentOwed, 100, 0);
                                        ?><div class="amountowed">Amount owed: <?php echo $amountOwed; ?>(<?php echo $percentOwed; ?>%)</div><?php
                                    }
                                }
                            ?>
                            </div>
                        <?php
                    }
                }
                ?></div> <?php*/?>
            <?php
            }
        ?>
        </div>
    </div>
    <?php
}
// Close the container on the page for the results
?>
</div>