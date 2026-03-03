<?php
//A cronjob to ensure all paid transactions within a given time period are actually on pesaflow

require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
sfContext::createInstance($configuration)->dispatch();

$invoice_manager = new InvoiceManager();

$reconciliation_log = "";

$reconciliation_log.="/**** STARTED PESAFLOW RECONCILE MONTHLY ".date("Y-m-d H:i:s")." ***/\n <br>";
error_log("Cronlog: Ended Pesaflow Service Reconciliation Cronjob ".date("Y-m-d H:i:s"));

$q = Doctrine_Query::create()
    ->from("ApFormPayments a")
    ->where("a.payment_date LIKE ?", date("Y-m", strtotime('-1 day'))."%")
    ->orderBy("a.payment_date DESC");

$transactions = $q->execute();

error_log("Cronlog: Transactions found: ".sizeof($transactions));

$confirmed_transactions = 0;
$failed_transactions = 0;
$conflicted_transactions = array();

foreach($transactions as $transaction)
{
    if($invoice_manager->remote_reconcile($transaction->getPaymentId()) == "paid")
    {
        if($invoice && $invoice->getPaid() <> 2)
        {
            $conflicted_transactions[] = "++ Ref: ".$transaction->getPaymentId().", Application: ".$invoice->getFormEntry()->getApplicationId().", Invoice should be paid";
        }
        else 
        {
            $confirmed_transactions++;
        }
    }
    else
    {
        $invoice = $invoice_manager->get_invoice_by_reference($transaction->getPaymentId());
        
        if($invoice && $invoice->getPaid() == 2)
        {
            $conflicted_transactions[] = "-- Ref: ".$transaction->getPaymentId().", Application: ".$invoice->getFormEntry()->getApplicationId().", Invoice should not be paid";
        }
        else 
        {
            $failed_transactions++;
        }
    }
}

$reconciliation_log.="/**** ENDED PESAFLOW RECONCILE MONTHLY ".date("Y-m-d H:i:s")." ***/\n <br>";

$reconciliation_log.="/**** Confirmed: ".$confirmed_transactions." ***/\n <br>";
$reconciliation_log.="/**** Failed: ".$failed_transactions." ***/\n <br>";
$reconciliation_log.="/**** Conflicts: ".sizeof($conflicted_transactions).": \n <br>";

$count = 0;
foreach($conflicted_transactions as $transaction)
{
    $count++;
    $reconciliation_log.=" ## ".$count.": ".$transaction." \n <br>";
}


$reconciliation_log.="***/\n <br>";

try 
{
    $log_file = fopen(dirname(__FILE__)."/logs/reconciliation_".date("Y-m-d-H_i_s").".log", "w") or die("Unable to create log file!");
    fwrite($log_file, $reconciliation_log);
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
    $notifier->sendemail($organisation_email, "thomasjgx@gmail.com","Reconciliation Log (Monthly) - ".$organisation_name." ".date("Y-m-d H:i:s"),$reconciliation_log);
}
catch(Exception $ex)
{
    error_log("Cronlog: Could not create log file: ".$ex);
    error_log("Cronlog: Dumping log to console...");
    echo $reconciliation_log;
}

error_log("Cronlog: Ended Pesaflow Service Reconciliation Cronjob ".date("Y-m-d H:i:s"));