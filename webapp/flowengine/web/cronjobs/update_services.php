<?php
//A cronjob to update draft applications that have been paid for

require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
sfContext::createInstance($configuration)->dispatch();

$permit_manager = new PermitManager();

$filter = $_SERVER['argv'][1];
$date_of_submission = $_SERVER['argv'][2];
echo "Filter: ".$filter."\n";

$q = Doctrine_Query::create()
    ->from("FormEntry a")
    ->where("a.approved = ?", $filter)
    ->andWhere("a.date_of_submission LIKE ?", "%".$date_of_submission."%")
    ->orderBy("a.id DESC");

echo "Records Found: ".$q->count()."\n";

$applications = $q->execute();

foreach($applications as $application)
{
    echo "Debug-inv: Cronjob: ".$application->getApplicationId()."\n";

    if($permit_manager->needs_permit_for_current_stage($application->getId()))
    {
        $permit_manager->create_permit($application->getId());
        echo "Debug-inv: Created permit for: ".$application->getApplicationId()."\n";
    }
}

$stats = "Successfully ran service updates: ".sizeof($applications)." records updates";
error_log('Debug-da: '.$stats);

?>
