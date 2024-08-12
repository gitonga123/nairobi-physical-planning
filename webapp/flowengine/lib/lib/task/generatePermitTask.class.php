<?php 

class generatePermitTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "generate-permit";
        $this->briefDescription    = 'Manually create a permit for an application';
 
        $this->detailedDescription = <<<EOF
The [permitflow:generate-permit|INFO] task creates a permit for an application that would otherwise not be possible
to create without creating the neccesary actions.
 
  [./symfony permitflow:generate-permit|INFO]

  Specify the application number of the application [--application|COMMENT] argument.
  Specify the template id of the permit template [--template|COMMENT] argument.
EOF;

        $this->addArgument('application_number', sfCommandArgument::REQUIRED, 'Specify the application number of the application', null);
        $this->addArgument('template_id', sfCommandArgument::REQUIRED, 'Specify the template id of the permit template', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Fetching application to update....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $success = 0;
        $failed = 0;

        if($arguments['application_number'] && $arguments['template_id'])
        {
            $permit_manager = new PermitManager();
            $permit_manager->create_permit_with_template($arguments['application_number'], $arguments['template_id']);
            $success++;
        }
        else 
        {
            $failed = 0;
        }

        $this->logSection('permitflow', 'Completed create-permit-manual task with '.$success.' successful and '.$failed.' failed.');
    }
}