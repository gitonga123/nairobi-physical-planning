<?php
    require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();
    
    $db_connection = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
    
    echo "/**** STARTED NOTIFICATIONS ".date("Y-m-d H:i:s")." ***/\n";
    error_log("Cronlog: Started Notifications cronjob ".date("Y-m-d H:i:s"));
    
    $limit = 2000;
    $sent_count = 0;
    $fail_count = 0;
    
    $q = Doctrine_Query::create()
       ->from("NotificationQueue a")
       ->limit($limit);
    $notifications = $q->execute();
    
    $templateparser = new Templateparser();
    $notifier = new mailnotifications();
    
    foreach($notifications as $notification)
    {
        try 
        {
            $q = Doctrine_Query::create()
               ->from("FormEntry a")
               ->where("a.id = ?", $notification->getApplicationId());
            $application = $q->fetchOne();
            
            $q = Doctrine_Query::create()
                ->from('sfGuardUserProfile b')
                ->where('b.user_id = ?', $notification->getUserId())
                ->limit(1);
            $userprofile = $q->fetchOne();
            
            if($application && $userprofile)
            {
               
                $message = $templateparser->parse($application->getId(),$application->getFormId(),$application->getEntryId(), $notification->getNotification());
                
                if($notification->getNotificationType() == "email")
                {
                    $notifier->sendemail(sfConfig::get('app_organisation_email'),$userprofile->getEmail(),$application->getApplicationId(),$message);
                }
                elseif($notification->getNotificationType() == "sms")
                {
                    $notifier->sendsms($userprofile->getMobile(), $message);
                }
                
                echo "Sending to ".$userprofile->getEmail()." (".$userprofile->getMobile()."): ".$message."\n";
            
            }
            
            $notification->delete();
            $sent_count++;
        }catch(Exception $ex)
        {
            $fail_count++;
            error_log("Cronlog: Failed to send notification: ".$ex);
        }
    }
    
    echo "/**** STOPPED NOTIFICATION ".date("Y-m-d H:i:s").": Success - ".$sent_count.", Failed - ".$fail_count." ***/\n";
    error_log("Cronlog: Stopped Notifications cronjob ".date("Y-m-d H:i:s"));
 ?>