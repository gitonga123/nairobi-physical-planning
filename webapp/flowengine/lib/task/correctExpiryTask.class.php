<?php 

class correctExpiryTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "correct-expiry";
        $this->briefDescription    = 'Corrects the expiry dates for all applications in a particular stage';
 
        $this->detailedDescription = <<<EOF
The [permitflow:generate-permit|INFO] task creates a permit for an application that would otherwise not be possible
to create without creating the neccesary actions.
 
  [./symfony permitflow:correct-expiry|INFO]

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

        if($arguments['application_number'])
        {
            $permit_manager->update_expiry_single($arguments['application_number']);
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
                $permit_manager->update_expiry_single($application->getApplicationId());
                $success++;
            }
        }
        else 
        {
            $failed = 0;
        }

        $this->logSection('permitflow', 'Completed correct-expiry task with '.$success.' successful and '.$failed.' failed.');
    }
}