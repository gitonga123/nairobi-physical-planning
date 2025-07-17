<?php
//A cronjob to update draft applications that have been paid for

require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
sfContext::createInstance($configuration)->dispatch();

$application_manager = new ApplicationManager();

$q = Doctrine_Query::create()
    ->from("FormEntry a")
    ->leftJoin("a.mfInvoice b")
    ->where("a.approved = 0")
    ->andWhere("b.paid = 2")
    ->orderBy("a.id DESC")
    ->limit(100);
$applications = $q->execute();

foreach($applications as $application)
{
    error_log("Debug-da: Cronjob: ".$application->getApplicationId());
    $application_manager->publish_draft($application->getId());
}

$stats = "Successfully ran remote updates: ".sizeof($applications)." records updates";
error_log('Debug-da: '.$stats);

?>
