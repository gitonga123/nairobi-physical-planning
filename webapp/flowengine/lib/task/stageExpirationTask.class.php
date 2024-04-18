<?php 

class stageExpirationTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "check-stage-expiration";
        $this->briefDescription    = 'Check for overdue applications in stages';
 
        $this->detailedDescription = <<<EOF
The [permitflow:check-stage-expiration|INFO] task checks each stage for expiration settings and checks if there 
are any applications that are overdue in those stages.
 
  [./symfony permitflow:check-stage-expiration|INFO]

  Specify the stage to which to check for overdue applications [--stage|COMMENT] argument.
EOF;

        $this->addArgument('stage', sfCommandArgument::REQUIRED, 'Stage to check for overdue applications', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for overdue applications in stages');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();
		$invoice_manager=new InvoiceManager();
        $q = Doctrine_Query::create()
            ->from("SubMenus a")
            ->where("a.id = ?", $arguments['stage']);
        $stage = $q->fetchOne();

        $success = 0;
        $failed = 0;
        
        $q = Doctrine_Query::create()
            ->from("ApplicationReference a")
            ->leftJoin("a.FormEntry b")
            ->where("a.stage_id = ?", $stage->getId())
            ->andWhere("a.stage_id = b.approved")
            ->orderBy("a.id DESC");
        $application_references = $q->execute();
        foreach($application_references as $reference)
        {
            try 
            {
				error_log('---------Start date---'.$reference->getStartDate().'--------'.$stage->getMaxDuration().'-------Days past------'.Functions::get_days_since($reference->getStartDate(), date('Y-m-d H:m:s')));
                if(Functions::get_days_since($reference->getStartDate(), date('Y-m-d H:m:s')) > $stage->getMaxDuration())
                {
                    if($stage->getStageExpiredMovement()) {
                        $application = $reference->getFormEntry();
                        $application->setApproved($stage->getStageExpiredMovement());
                        $application->setDeclined($stage->getStageExpiredMovementDecline());//OTB Send to expired as declined logic
                        $application->save();
                        
                        $invoice_manager->update_invoices($application->getId());
                    }
                }
                $success++;
            }catch(Exception $ex)
            {
                $failed++;
                $this->logSection('permitflow', 'Check stage expiration failed on ID: '.$reference->getId().' due to '.$ex);
            }
        }

        $this->logSection('permitflow', 'Completed check-stage-expiration task with '.$success.' successful and '.$failed.' failed.');
    }
}