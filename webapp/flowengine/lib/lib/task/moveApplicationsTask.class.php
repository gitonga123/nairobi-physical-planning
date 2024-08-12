<?php

class moveApplicationsTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "move-applications";
        $this->briefDescription    = 'Move applications FROM stage_id TO stage_id';

        $this->detailedDescription = <<<EOF
The [permitflow:move-applications|INFO] task is move applications in one stage to a destination stage.
  [./symfony permitflow:update-remote-permits|INFO]
 
Specify the id of the originating stage id  [--from|COMMENT] argument.
Specify the id of the destination stage id [--end|COMMENT] argument.
EOF;

        $this->addArgument('from', sfCommandArgument::REQUIRED, 'Originating Stage Id', null);
        $this->addArgument('to', sfCommandArgument::REQUIRED, 'Destination Stage Id', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Get the stage details....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $databaseManager->getDatabase("doctrine")->getConnection();
        try {
            $success = 0;
            $failed = 0;
            $application_manager = new ApplicationManager();

            $from = $arguments['from'];
            $to = $arguments['to'];
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $from);
            $fromStage = $q->fetchOne();
            if ($fromStage) {
                $q = Doctrine_Query::create()
                    ->from("SubMenus a")
                    ->where("a.id = ?", $to);
                $toStage = $q->fetchOne();
                if ($toStage) {
                    $q = Doctrine_Query::create()
                        ->from("FormEntry f")
                        ->where("f.approved = ?", $from)
                        ->limit(10);
                    $applications = $q->execute();
                    foreach ($applications as $application) {
                        $status = $application_manager->moveApplication($application, $to);
                        $status ?  $success += 1 : $failed += 1;
                        $this->logSection('permitflow', "Completed moving application id {$application->getId()} no {$application->getApplicationId()} from {$from} to {$to}");
                    }
                } else {
                    $this->logSection('permitflow', "Stage not found ---> ${to}");
                }
            } else {
                $this->logSection('permitflow', "Stage not found ---> ${from}");
            }
        } catch (\Exception $ex) {
            $failed += 1;
            $this->logSection('permitflow', $ex->getMessage(), 'error');
        }
        $this->logSection('permitflow', 'Completed move-applications task with ' . $success . ' successful and ' . $failed . ' failed.');
    }
}
