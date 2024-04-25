<?php 

class updateRemoteTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "update-remote-permits";
        $this->briefDescription    = 'Sends data from permits to remote servers';
 
        $this->detailedDescription = <<<EOF
The [permitflow:update-remote-permits|INFO] task is sends data from permits to remote servers
as is configured in the permit templates.
 
  [./symfony permitflow:update-remote-permits|INFO]
 
Specify the start date of the first permit using the  [--from|COMMENT] argument.
Specify the end date of the last permit using the [--end|COMMENT] argument.
Specify a service id to filter only specific services using the [--service|COMMENT] argument
EOF;
 
        $this->addArgument('from', sfCommandArgument::REQUIRED, 'Start date when the first permit was issued', null);
        $this->addArgument('to', sfCommandArgument::REQUIRED, 'End date when the last permit was issued', null);
        $this->addArgument('service', sfCommandArgument::OPTIONAL, 'Filter only permits belongin to a specific service', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Fetching list of permits to update....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $q = null;

        if($arguments['service'])
        {
            $q = Doctrine_Query::create()
               ->from("SavedPermit a")
               ->leftJoin("a.FormEntry b")
               ->where("b.form_id = ?", $arguments['service'])
               ->andWhere('a.date_of_issue BETWEEN ? AND ?', array($arguments['from'],$arguments['to']));
        }
        else 
        {
            $q = Doctrine_Query::create()
               ->from("SavedPermit a")
               ->leftJoin("a.FormEntry b")
               ->where('a.date_of_issue BETWEEN ? AND ?', array($arguments['from'],$arguments['to']));
        }

        $success = 0;
        $failed = 0;

        foreach($q->execute() as $permit)
        {
            try{
                //On save function triggers remote push incase of changes
                $permit->save();
                $success++;
            }catch(Exception $ex)
            {
                $failed++;
                $this->logSection('permitflow', 'Remote update failed on ID: '.$permit->getId().' due to '.$ex);
            }
        }

        $this->logSection('permitflow', 'Completed update-remote-permits task with '.$success.' successful and '.$failed.' failed.');
    }
}