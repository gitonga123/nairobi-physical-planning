<?php 

class overrideWorkflowTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "override-workflow";
        $this->briefDescription    = 'Send all applications in the specified stage to their respective workflows';
 
        $this->detailedDescription = <<<EOF
The [permitflow:override-workflow|INFO] task sends all applications in the specified stage to their respective workflows
 as configured in the override workflow settings.
 
  [./symfony permitflow:override-workflow|INFO]

  business id to check [--filter|COMMENT] = stage_id.
EOF;

    $this->addArgument('filter', sfCommandArgument::REQUIRED, 'stage id to check', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for applications to override');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $application_manager = new ApplicationManager();

        $success = 0;
        $failed = 0;

        $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->where("a.approved = ?", $arguments["filter"])
           ->orderBy("a.id DESC");
        $applications = $q->execute();

        foreach($applications as $application)
        {
            try 
            {
                $initial_stage = $application->getApproved();
                $next_stage = $application_manager->get_submission_stage($application->getFormId(), $application->getEntryId());

                $this->logSection('permitflow', "Overriding ".$application->getApplicationId()." from ".$initial_stage." to ".$next_stage);

                $application->setApproved($next_stage);
                $application->save();
                $success++;
            }catch(Exception $ex)
            {
                $this->logSection('permitflow', $ex->getMessage());
                $failed++;
            }
        }

        $this->logSection('permitflow', 'Completed override-workflow task with '.$success.' successful and '.$failed.' failed.');

    }

}