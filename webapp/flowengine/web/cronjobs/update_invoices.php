<?php
//A cronjob to update draft applications that have been paid for

require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
sfContext::createInstance($configuration)->dispatch();

$invoice_manager = new InvoiceManager();

$filter = $_SERVER['argv'][1];
echo "Filter: ".$filter."\n";

$q = Doctrine_Query::create()
    ->from("FormEntry a")
    ->where("a.approved = ?", $filter)
    ->orderBy("a.id DESC");

echo "Records Found: ".$q->count()."\n";

$applications = $q->execute();

foreach($applications as $application)
{
    echo "Debug-inv: Cronjob: ".$application->getApplicationId()."\n";

    //$invoice_manager->update_invoices($application->getId());
}

$stats = "Successfully ran remote updates: ".sizeof($applications)." records updates";
error_log('Debug-da: '.$stats);

?>
