<?php 

class updateApplicationId extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "update-application-id";
        $this->briefDescription    = 'Update form entry with incremented application id';
 
        $this->detailedDescription = <<<EOF
The [permitflow:update-application-id|INFO] task updates form entry with new incremented application id.
 
  [./symfony permitflow:update-application-id|INFO]
 
Specify application id  [--id|COMMENT] argument.
EOF;
 
        $this->addArgument('id', sfCommandArgument::REQUIRED, 'Application id', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Updating application......');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();


		$q=Doctrine_Query::create()
			->from('FormEntry a')
			->where('a.id = ?',$arguments['id']);
		$application=$q->fetchOne();

        $success = 0;
        $failed = 0;
		if($application){
			//get application id
			$application_manager=new ApplicationManager();
			if($application->getServiceId()){
				//business profile exist
				//get application id
				$application_no=$application_manager->generate_cyclic_number($application->getServiceId());
				//save
				$application->setApplicationId($application_no);
				$application->save();
				$this->logSection('permitflow', 'Application '.$application->getId().' updated to '.$application_no);
				$success++;
			}else{
				//old form
				//get application id
				$application_no=$application_manager->generate_application_number($application->getFormId());
				//save
				$application->setApplicationId($application_no);
				$application->save();
				$this->logSection('permitflow', 'Application '.$application->getId().' updated to '.$application_no);
				$success++;
			}
		}else{
			$this->logSection('permitflow', 'Application '.$application->getId().' not updated');
			$failed++;
		}
        $this->logSection('permitflow', 'Completed update-remote-permits task with '.$success.' successful and '.$failed.' failed.');
    }
}