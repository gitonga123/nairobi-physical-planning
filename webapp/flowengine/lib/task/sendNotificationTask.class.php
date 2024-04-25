<?php 

class sendNotificationTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "send-notifications";
        $this->briefDescription    = 'Send queued notifications';
 
        $this->detailedDescription = <<<EOF
The [permitflow:send-notifications|INFO] task checks the queue for any mail or sms
notifications and sends them.
 
  [./symfony permitflow:send-notifications|INFO]

  Specify the limit for the number of notifications to send per task with the [--limit|COMMENT] argument.
EOF;

        $this->addArgument('limit', sfCommandArgument::OPTIONAL, 'Limit the number of notifications to send per task', 100);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking queue for notifications....');

        $templateparser = new Templateparser();
        $notifier = new mailnotifications();
        
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $q = Doctrine_Query::create()
            ->from("NotificationQueue a")
            ->limit($arguments['limit']);
        $notifications = $q->execute();

        $success = 0;
        $failed = 0;

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
                }

                $notification->delete();
                $success++;
            }catch(Exception $ex)
            {
                $failed++;
                $this->logSection('permitflow', 'Send notification failed on ID: '.$notification->getId().' due to '.$ex);
            }
        }

        $this->logSection('permitflow', 'Completed send-notifications task with '.$success.' successful and '.$failed.' failed.');
    }
}