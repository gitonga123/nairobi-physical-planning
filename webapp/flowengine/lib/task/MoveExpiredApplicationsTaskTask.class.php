<?php

class MoveExpiredApplicationsTaskTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'Move Expired Applications to Next Stage'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'permitflow';
    $this->name             = 'move_expired_applications';
    $this->briefDescription = 'Move applications from one stage to another';
    $this->detailedDescription = <<<EOF
The [MoveExpiredApplicationsTask|INFO] task does things.
Call it with:

  [php symfony permitflow:move_expired_applications|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $this->logSection('Permitflow', 'STARTING EXPIRATION CRONJOB', 0, 'INFO');
    try {
      $foundexpired = false;
      $application_manager = new ApplicationManager();
      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.max_duration <> ? AND a.max_duration <> ?', array('', 0));
      $submenus = $q->execute();

      foreach ($submenus as $submenu) {
        $stage_applications = Doctrine_Query::create()
          ->from('FormEntry f')->where('f.approved = ?', $submenu->getId())->execute();
        foreach ($stage_applications as $stage_application) {
          $q = Doctrine_Query::create()->from('FormEntry a')
            ->where('a.id = ?', $stage_application->getId());
          $application = $q->fetchOne();

          $q = Doctrine_Query::create()
            ->from('ApplicationReference a')
            ->where('a.application_id = ?', $application->getId())
            ->andWhere('a.stage_id = ?', $submenu->getId())
            ->orderBy('a.id DESC');
          $reference = $q->fetchOne();
          if ($reference) {
            if ($this->getDaysDiff($reference->getStartDate(), date('Y-m-d H:i:s')) >= $submenu->getMaxDuration()) {
              $foundexpired = true;

              // Check stage for expired stage settings
              $q = Doctrine_Query::create()
                ->from('SubMenus a')
                ->where('a.id = ?', $application->getApproved());
              $stage = $q->fetchOne();

              if ($stage && $stage->getStageExpiredMovement()) {
                $initial_stage = $stage->getId();
                $q = Doctrine_Query::create()->from('FormEntry a')
                  ->where('a.id = ?', $stage_application->getId());
                $application = $q->fetchOne();
                $application_manager->moveApplication($application, $stage->getStageExpiredMovement());
              }
            }
          } else {
            if ($this->getDaysDiff($application->getDateOfSubmission(), date('Y-m-d H:i:s')) >= $submenu->getMaxDuration()) {
              $foundexpired = true;

              // Check stage for expired stage settings
              $q = Doctrine_Query::create()
                ->from('SubMenus a')
                ->where('a.id = ?', $application->getApproved());
              $stage = $q->fetchOne();

              if ($stage && $stage->getStageExpiredMovement()) {
                $q = Doctrine_Query::create()->from('FormEntry a')
                  ->where('a.id = ?', $stage_application->getId());
                $application = $q->fetchOne();
                $application_manager->moveApplication($application, $stage->getStageExpiredMovement());
              }
            }
          }
        }
      }
      if ($foundexpired) {
        $this->logSection('Permitflow', 'Debug-e: Found expired applications', 0, 'INFO');
      } else {
        $this->logSection('Permitflow', 'Debug-e: Did not find expired applications', 0, 'ERROR');
      }
    } catch (\Exception $th) {
      $this->logSection('Permitflow', $th->getMessage(), 0, 'ERROR');
    }
  }

  protected function getDaysDiff($startdate, $enddate)
  {
    $date1 = new DateTime($startdate);
    $date2 = new DateTime($enddate);

    $diff = $date2->diff($date1)->format("%a");
    return $diff;
  }
}
