<?php 

class paymentsStageTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "check-payments-stage";
        $this->briefDescription    = 'Check for payments stage triggers';
 
        $this->detailedDescription = <<<EOF
The [permitflow:check-payments-stage|INFO] task checks each stage for payments settings and checks if there 
are any applications that need to be moved from those stages.
 
  [./symfony permitflow:check-payments-stage|INFO]

  Specify the stage to which to check for payment triggers [--stage|COMMENT] argument.
EOF;

        $this->addArgument('stage', sfCommandArgument::OPTIONAL, 'Stage to check for payment triggers', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for payment triggers in stages');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $success = 0;
        $failed = 0;

        $q = null;

        if($arguments['stage'])
        {
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $arguments['stage'])
                ->andWhere("a.stage_type = 3 and a.stage_property = 2");
        }
        else 
        {
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.stage_type = 3 and a.stage_property = 2");
        }

        $stages = $q->execute();

        foreach($stages as $stage)
        {
            $this->logSection('permitflow', 'Found stage ID: '.$stage->getId());

            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->where("a.approved = ?", $stage->getId());
            $applications = $q->execute();

            foreach($applications as $application)
            {
                try
                {
                    $q = Doctrine_Query::create()
                        ->from("MfInvoice a")
                        ->where("a.paid <> 2")
                        ->andWhere("a.app_id = ?", $application->getId());
                    if($q->count() == 0)
                    {
                        $q = Doctrine_Query::create()
                            ->from("MfInvoice a")
                            ->where("a.paid = 2")
                            ->andWhere("a.app_id = ?", $application->getId());
                        if($q->count() > 0)
                        {
                            $this->logSection('permitflow', 'Found application ID: '.$application->getId().': Move from '.$application->getApproved().' to '.$stage->getStageTypeMovement());
                            $application->setApproved($stage->getStageTypeMovement());
                            $application->save();
                            $success++;
                        }
                    }

                }catch(Exception $ex)
                {
                    $failed++;
                    $this->logSection('permitflow', 'Check payments settings failed on ID: '.$application->getId().' due to '.$ex);
                }
            }
        }
        

        $this->logSection('permitflow', 'Completed check-payments-stage task with '.$success.' successful and '.$failed.' failed.');
    }
}