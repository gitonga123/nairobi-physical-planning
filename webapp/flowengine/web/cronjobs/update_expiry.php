#!/usr/bin/php
<?php
try {
    require_once(dirname(__FILE__) . '/../../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('backend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

    $db_connection = mysqli_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));

    if (!$db_connection) {
        error_log("Debug-db Connection: DB connection failed");

        return;
    }

    new sfDatabaseManager($configuration);

    function GetDaysDiff($startdate, $enddate)
    {
        $date1 = new DateTime($startdate);
        $date2 = new DateTime($enddate);

        $diff = $date2->diff($date1)->format("%a");
        return $diff;
    }

    echo "/**** STARTING EXPIRATION CRONJOB ***/\n";

    $foundexpired = false;
    $q = Doctrine_Core::getTable('SubMenus')
        ->createQuery('a')
        ->where('a.max_duration <> ? AND a.max_duration <> ?', array('', 0));
    $submenus = $q->execute();
    foreach ($submenus as $submenu) {
        echo "/** STAGE: " . $submenu->getTitle() . " **/\n";

        mysqli_select_db($db_connection, sfConfig::get('app_mysql_db'));

        $sql = "SELECT * FROM form_entry WHERE approved = " . $submenu->getId();
        $results = mysqli_query($db_connection, $sql);

        echo "/* TOTAL APPLICATIONS TO CHECK: " . mysqli_num_rows($results) . " */\n";
        while ($application = mysqli_fetch_assoc($results)) {
            $q = Doctrine_Core::getTable('FormEntry')
                ->createQuery('a')
                ->where('a.id = ?', $application['id']);
            $application = $q->fetchOne();

            echo "- Checking: " . $application['application_id'] . " */\n";
            $q = Doctrine_Core::getTable('ApplicationReference')
                ->createQuery('a')
                ->where('a.application_id = ?', $application->getId())
                ->andWhere('a.stage_id = ?', $submenu->getId())
                ->orderBy('a.id DESC');
            $reference = $q->fetchOne();
            if ($reference) {
                if (GetDaysDiff($reference->getStartDate(), date('Y-m-d H:i:s')) > $submenu->getMaxDuration()) {
                    echo "-- " . $application->getApplicationId() . " has expired */\n";
                    $foundexpired = true;

                    // Check stage for expired stage settings
                    $q = Doctrine_Core::getTable('SubMenus')
                        ->createQuery('a')
                        ->where('a.id = ?', $application->getApproved());
                    $stage = $q->fetchOne();

                    if ($stage && $stage->getStageExpiredMovement()) {
                        $initial_stage = $stage->getId();
                        $application->setApproved($stage->getStageExpiredMovement());
                        $application->setDeclined($stage->getStageExpiredMovementDecline()); // OTB Send to expired as declined logic
                        $application->save();

                        cancelPendingTasks($initial_stage);
                    }
                }
            } else {
                if (GetDaysDiff($application->getDateOfSubmission(), date('Y-m-d H:i:s')) > $submenu->getMaxDuration()) {
                    echo "-- " . $application->getApplicationId() . " has expired */\n";
                    $foundexpired = true;

                    // Check stage for expired stage settings
                    $q = Doctrine_Core::getTable('SubMenus')
                        ->createQuery('a')
                        ->where('a.id = ?', $application->getApproved());
                    $stage = $q->fetchOne();

                    if ($stage && $stage->getStageExpiredMovement()) {
                        $initial_stage = $stage->getId();
                        $application->setApproved($stage->getStageExpiredMovement());
                        $application->setDeclined($stage->getStageExpiredMovementDecline()); // OTB Send to expired as declined logic
                        $application->save();

                        cancelPendingTasks($initial_stage);
                    }
                }
            }
        }
    }
    if ($foundexpired) {
        error_log("Debug-e: Found expired applications");
    } else {
        error_log("Debug-e: Did not find expired applications");
    }

    function cancelPendingTasks($initial_stage)
    {
        $tasks = Doctrine_Core::getTable('Task')->createQuery('t')->where('t.task_stage = ?', $initial_stage)->where('t.status = ? ', 1)->execute();

        foreach ($tasks as $task) {
            $task->setStatus(55);
            $task->save();
        }
    }

    //code...
} catch (\Exception $th) {
    error_log($th->getMessage());
}
