<?php 

class cleanPermitsTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "clean-permits";
        $this->briefDescription    = 'Clean any saved permits with deleted permit templates';
 
        $this->detailedDescription = <<<EOF
The [permitflow:clean-permits|INFO] task will clean any saved permits with deleted permit templates.
 
  [./symfony permitflow:clean-permits|INFO]

  Specify the application number of the application [--application|COMMENT] argument.
  Specify the stage id of the permits [--stage|COMMENT] argument.
EOF;

        $this->addArgument('application_number', sfCommandArgument::OPTIONAL, 'Specify the application number of the application', null);
        $this->addArgument('stage_id', sfCommandArgument::OPTIONAL, 'Specify the stage id of the permits', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Fetching application to update....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $success = 0;
        $failed = 0;

        $permit_manager = new PermitManager();
        $business_manager = new BusinessManager();

        if($arguments['application_number'] && $arguments['application_number'] != "false")
        {
            $permit_manager->clean_permits($arguments['application_number']);
            $success++;
        }
        elseif($arguments['stage_id'])
        {
            $q = Doctrine_Query::create()
               ->from("FormEntry a")
               ->where("a.approved = ?", $arguments['stage_id']);
            $applications = $q->execute();
            foreach($applications as $application)
            {
                $permit_manager->clean_permits($application->getApplicationId());
                if($application->getBusinessId())
                {
                    error_log("Running cyclic cronjob since application may need renewal");
                    $business_manager->generate_cyclic_bills($application->getBusinessId());
                }
                $success++;
            }
        }
        else 
        {
            $failed = 0;
        }

        $this->logSection('permitflow', 'Completed clean-permits task with '.$success.' successful and '.$failed.' failed.');
    }
}