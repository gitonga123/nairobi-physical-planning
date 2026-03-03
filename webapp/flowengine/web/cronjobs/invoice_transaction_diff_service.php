<?php
//A cronjob to detect invoices that don't have any transactions

require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
sfContext::createInstance($configuration)->dispatch();

$invoice_manager = new InvoiceManager();

$from_date = ($_SERVER['argv'][1]?$_SERVER['argv'][1]:$_GET['from_date']);
$to_date = ($_SERVER['argv'][2]?$_SERVER['argv'][2]:$_GET['to_date']);
$form_id = ($_SERVER['argv'][3]?$_SERVER['argv'][3]:$_GET['form_id']);

$diff_log = "";

$diff_log .="/**** STARTED Invoice/Transaction Diff Cronjob ".date("Y-m-d H:i:s")." ***/\n <br>";
error_log("Cronlog: Ended Invoice/Transaction Diff Cronjob ".date("Y-m-d H:i:s"));

$q = Doctrine_Query::create()
   ->from("MfInvoice a")
   ->leftJoin("a.FormEntry b")
   ->where("b.form_id = ?", $form_id)
   ->andWhere("a.updated_at BETWEEN ? AND ?", array($from_date." 00:00:00", $to_date." 23:59:59"))
   ->andWhere("a.paid = 2")
   ->orderBy("a.updated_at DESC");
   
$invoices = $q->execute();

$validated_invoices = 0;
$resolved_invoices = array();
$conflict_invoices = array();

foreach($invoices as $invoice)
{
    $q = Doctrine_Query::create()
       ->from("ApFormPayments a")
       ->where("a.payment_id = ?", $invoice->getFormEntry()->getFormId()."/".$invoice->getFormEntry()->getEntryId()."/".$invoice->getId());
    
    if($q->count() > 0)
    {
        $validated_invoices++;
        error_log("Cronlog: Validated ID: ".$invoice->getId().", Application: ".$invoice->getFormEntry()->getApplicationId());
    }
    else
    {
        $billing_reference = $invoice->getFormEntry()->getFormId()."/".$invoice->getFormEntry()->getEntryId()."/".$invoice->getId();
        
        if($invoice_manager->remote_reconcile($billing_reference) == "paid")
        {
            $user = Doctrine_Core::getTable('SfGuardUser')->find(array($invoice->getFormEntry()->getUserId()));
            $fullname = $user->getProfile()->getFullname();
                
            $transaction = new ApFormPayments();
            $transaction->setFormId($invoice->getFormEntry()->getFormId());
            $transaction->setRecordId($invoice->getFormEntry()->getEntryId());
            $transaction->setPaymentId($billing_reference);
            $transaction->setDateCreated(date("Y-m-d H:i:s"));
            $transaction->setPaymentFullname($fullname);
            $transaction->setPaymentAmount($invoice->getTotalAmount());
            $transaction->setPaymentCurrency($invoice->getCurrency());
            $transaction->setPaymentMerchantType('Pesaflow');
            $transaction->setPaymentTestMode("0");

            $transaction->setBillingState($billing_reference);
            $transaction->setPaymentDate(date("Y-m-d H:i:s"));

            $transaction->setStatus(2);
            $transaction->setPaymentStatus("paid");

            $transaction->save();
            
            $resolved_invoices[] = "++ Invoice ID: ".$invoice->getId().", Application: ".$invoice->getFormEntry()->getApplicationId().", Transaction Added";
            error_log("Cronlog: ++ Invoice ID: ".$invoice->getId().", Application: ".$invoice->getFormEntry()->getApplicationId().", Transaction Added");
        }
        else 
        {
            $conflict_invoices[] = "-- Invoice ID: ".$invoice->getId().", Application: ".$invoice->getFormEntry()->getApplicationId().", No Transaction Found";
            error_log("Cronlog: -- Invoice ID: ".$invoice->getId().", Application: ".$invoice->getFormEntry()->getApplicationId().", No Transaction Found");
        }
    }
}

$diff_log.="/**** ENDED Invoice/Transaction Diff Cronjob ".date("Y-m-d H:i:s")." ***/\n <br>";

$diff_log.="/**** Valid Invoices: ".$validated_invoices." ***/\n <br>";

$diff_log.="/**** Resolved: ".sizeof($resolved_invoices).": \n <br>";
$count = 0;
foreach($resolved_invoices as $invoice)
{
    $count++;
    $diff_log.=" ## ".$count.": ".$invoice." \n <br>";
}


$diff_log.="***/\n <br>";

$diff_log.="/**** Conflicts: ".sizeof($conflict_invoices).": \n <br>";
$count = 0;
foreach($conflict_invoices as $invoice)
{
    $count++;
    $diff_log.=" ## ".$count.": ".$invoice." \n <br>";
}


$diff_log.="***/\n <br>";

try 
{
    $log_file = fopen(dirname(__FILE__)."/logs/invoice_diff_".date("Y-m-d-H_i_s").".log", "w") or die("Unable to create log file!");
    fwrite($log_file, $diff_log);
    fclose($log_file);
    
    $q = Doctrine_Query::create()
        ->from("ApSettings a")
        ->where("a.id = 1")
        ->orderBy("a.id DESC");
    $apsettings = $q->fetchOne();
    if($apsettings)
    {
            $organisation_name = $apsettings->getOrganisationName();
            $organisation_email = $apsettings->getOrganisationEmail();
    }
    
    //Also send log by mail 
    $notifier = new mailnotifications();
    $notifier->sendemail($organisation_email, "thomasjgx@gmail.com","Invoice Diff Log - ".$organisation_name." ".date("Y-m-d H:i:s"),$reconciliation_log);
}
catch(Exception $ex)
{
    error_log("Cronlog: Could not create log file: ".$ex);
    error_log("Cronlog: Dumping log to console...");
    echo $reconciliation_log;
}

error_log("Cronlog: Ended Invoice/Transaction Diff Cronjob ".date("Y-m-d H:i:s"));