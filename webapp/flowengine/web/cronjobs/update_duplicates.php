<?php
    require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

    $prefix_folder = dirname(__FILE__)."/../../permitflow_src/lib/vendor/cp_machform/";

    include_once($prefix_folder.'includes/OAuth.php');
    require_once($prefix_folder.'includes/init.php');

    require_once($prefix_folder.'config.php');
    require_once($prefix_folder.'includes/db-core.php');
    require_once($prefix_folder.'includes/helper-functions.php');
    require_once($prefix_folder.'includes/check-session.php');

    require_once($prefix_folder.'includes/entry-functions.php');
    require_once($prefix_folder.'includes/post-functions.php');
    require_once($prefix_folder.'includes/users-functions.php');


    $dbh = mf_connect_db();

    //Check for duplicate application number and update
    $sql = "SELECT application_id, COUNT(*) FROM form_entry GROUP BY application_id HAVING COUNT(*) > 1";
    $params = array();
    $results = mf_do_query($sql,$params,$dbh);

    while($row = mf_do_fetch_result($results))
    {
        error_log("Debug-d: Duplicate Record Numbers Found by CronJob: ".$row['application_id']);

        //Check for duplicate application numbers and update the latest one
        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.approved <> ?", 0)
            ->andWhere("a.parent_submission = ?", 0)
            ->andWhere("a.deleted_status = ?", 0)
            ->andWhere("a.application_id = ?", $row['application_id'])
            ->orderBy("a.date_of_submission DESC");
        $duplicate_records = $q->execute();

        foreach($duplicate_records as $duplicate_record)
        {
            //Check which application was submitted last, update the number of the latter one
            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->where("a.approved <> ?", 0)
                ->andWhere("a.parent_submission = ?", 0)
                ->andWhere("a.deleted_status = ?", 0)
                ->andWhere("a.application_id = ?", $duplicate_record->getApplicationId())
                ->andWhere("a.id <> ?", $duplicate_record->getId())
                ->orderBy("a.date_of_submission DESC");
            $other_duplicate_records = $q->execute();

            foreach($other_duplicate_records as $other_duplicate_record)
            {
                $q = Doctrine_Query::create()
                    ->from('FormEntry a')
                    ->where('a.approved <> 0 AND a.declined <> 1 AND a.parent_submission = 0')
                    ->andWhere('a.form_id = ?', $duplicate_record->getFormId())
                    ->orderBy("a.application_id DESC");
                $last_app = $q->fetchOne();

                $new_app_id = ""; //submission identifier
                $identifier_start = ""; //first stage

                if($last_app)
                {
                    $new_app_id = $last_app->getApplicationId();
                    $new_app_id = ++$new_app_id;

                    $duplicate_record->setApplicationId($new_app_id);
                    $duplicate_record->save();
                }

                error_log("Debug-d: Updating Duplicate Record's Number via CronJob: ".$duplicate_record->getApplicationId());
                break;
            }
        }
    }
?>

