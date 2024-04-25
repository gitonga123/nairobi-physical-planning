<?php 

class checkJsonTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "check-json";
        $this->briefDescription    = 'Updates the json form-data for all applications';
 
        $this->detailedDescription = <<<EOF
The [permitflow:check-json|INFO] task will update the json form-data for all applications.
 
  [./symfony permitflow:check-json|INFO]

  Specify the application number of the application [--application|COMMENT] argument.
EOF;

        $this->addArgument('application_number', sfCommandArgument::OPTIONAL, 'Specify the application number of the application', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Fetching applications to update....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $application_manager = new ApplicationManager();
        $business_manager = new BusinessManager();

        $success = 0;
        $failed = 0;

        if($arguments['application_number'] && $arguments['application_number'] != "false")
        {
            $q = Doctrine_Query::create()
               ->from("FormEntry a")
               ->where("a.application_id = ?", $arguments['application_number']);
            $application = $q->fetchOne();

            if($application)
            {
                try{
                    $application_manager->check_and_update_json($application->getId());
                    $this->logSection("permitflow", 'Updated json for '.$application->getApplicationId());
                    $success++;
                }
                catch(Exception $error)
                {
                    $this->logSection("permitflow", 'Could not check json for '.$application->getApplicationId().": ".$error);
                    $failed++;
                }
            }
        }
        else 
        {
            $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
            require_once($prefix_folder.'includes/init.php');

            require_once($prefix_folder.'../../../config/form_builder_config.php');
            require_once($prefix_folder.'includes/db-core.php');
            require_once($prefix_folder.'includes/helper-functions.php');

            $dbh = mf_connect_db();
            $mf_settings = mf_get_settings($dbh);

            //Check Applications
            $query  = "SELECT * FROM form_entry WHERE approved <> 0 ORDER BY id DESC";

            $params = array();

            $sth = mf_do_query($query,$params,$dbh);

            while($app_rw = mf_do_fetch_result($sth))
            {
                $q = Doctrine_Query::create()
                    ->from("FormEntry a")
                    ->where("a.id = ?", $app_rw['id']);
                $application = $q->fetchOne();

                try{
                    $application_manager->check_and_update_json($application->getId());
                    $this->logSection("permitflow", 'Updated json for '.$application->getApplicationId());
                    $success++;
                }
                catch(Exception $error)
                {
                    $this->logSection("permitflow", 'Could not check json for '.$application->getApplicationId().": ".$error);
                    $failed++;
                }
            }

            //Check Businesses
            $query  = "SELECT * FROM mf_user_profile ORDER BY id DESC";

            $params = array();

            $sth = mf_do_query($query,$params,$dbh);

            while($business_rw = mf_do_fetch_result($sth))
            {
                $q = Doctrine_Query::create()
                    ->from("MfUserProfile a")
                    ->where("a.id = ?", $business_rw['id']);
                $business = $q->fetchOne();

                try{
                    $business_manager->check_and_update_json($business->getId());
                    $this->logSection("permitflow", 'Updated json for '.$business->getTitle());
                    $success++;
                }
                catch(Exception $error)
                {
                    $this->logSection("permitflow", 'Could not check json for '.$business->getTitle().": ".$error);
                    $failed++;
                }
            }
        }

        $this->logSection('permitflow', 'Completed check-json task with '.$success.' successful and '.$failed.' failed.');
    }
}