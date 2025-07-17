<?php
    require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();


    //parse the url through a template parser incase there are any fields from the form
    $templateparser = new TemplateParser();

    $from_date = ($_SERVER['argv'][1]?$_SERVER['argv'][1]:$_GET['from_date']);
    $to_date = ($_SERVER['argv'][2]?$_SERVER['argv'][2]:$_GET['to_date']);
    $form_id = ($_SERVER['argv'][3]?$_SERVER['argv'][3]:$_GET['form_id']);

    $from_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($from_date)) . "-1 day"));
    $to_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($to_date)) . "+1 day"));

    $records_count = 0;

    if(!empty($from_date) && !empty($to_date))
    {
        error_log('Debug-v: Found two date filters for updating remote result');
        $permits = null;

        if($form_id)
        {
            $q = Doctrine_Query::create()
                ->from('SavedPermit a')
                ->leftJoin('a.FormEntry b')
                ->where('a.date_of_issue BETWEEN ? AND ?', array($from_date,$to_date))
                ->andWhere('b.form_id = ?', $form_id);
            $permits = $q->execute();
        }
        else
        {
            $q = Doctrine_Query::create()
                ->from('SavedPermit a')
                ->where('a.date_of_issue BETWEEN ? AND ?', array($from_date,$to_date));
            $permits = $q->execute();
        }
        foreach($permits as $permit)
        {
            try{
                $permit->save();

                error_log("Remote Update Success: ".$permit->getFormEntry()->getApplicationId());

            }catch(Exception $ex)
            {
                error_log("Debug-v: Remote update error: ".$permit->getFormEntry()->getApplicationId());
            }
        }
    }
    elseif(!empty($from_date) && empty($to_date))
    {
      error_log('Debug-v: Found one date filters for updating remote result');
      $permits = null;

      if($form_id)
      {
          $q = Doctrine_Query::create()
              ->from('SavedPermit a')
              ->leftJoin('a.FormEntry b')
              ->where('a.date_of_issue LIKE ?', "%".$from_date."%")
              ->andWhere('b.form_id = ?', $form_id);
          $permits = $q->execute();
      }
      else
      {
          $q = Doctrine_Query::create()
              ->from('SavedPermit a')
              ->where('a.date_of_issue LIKE ?', "%".$from_date."%");
          $permits = $q->execute();
      }
      foreach($permits as $permit)
      {
          try{
              $permit->save();

              error_log("Remote Update Success: ".$permit->getFormEntry()->getApplicationId());

          }catch(Exception $ex)
          {
              error_log("Debug-v: Remote update error: ".$permit->getFormEntry()->getApplicationId());
          }
      }
    }
    else
    {
        error_log('Debug-v: No date filters found for updating remote results for permit');
    }

    $stats = "Successfully ran remote updates: ".$records_count." records updates";
    error_log('Debug-v: '.$stats);

?>
