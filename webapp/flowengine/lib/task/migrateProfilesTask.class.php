<?php 

class migrateProfilesTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "migrate-profiles";
        $this->briefDescription    = 'Creates profiles from existing applications';
 
        $this->detailedDescription = <<<EOF
The [permitflow:migrate-profiles|INFO] task creates profiles from existing applications in a workflow. The user will need to specify 
the user category, and the permit template from which to generate the profiles from. Since only approved applications should be used.
 
  [./symfony permitflow:migrate-profiles|INFO]

  Specify the user category id for which to generate the profiles [--user_category|COMMENT] argument.
  Specify the permit id from which to get the profile data [--permit_id|COMMENT] argument.
EOF;

        $this->addArgument('user_category', sfCommandArgument::REQUIRED, 'Specify the user category', null);
        $this->addArgument('permit_id', sfCommandArgument::REQUIRED, 'Specify the permit template id of the approved applications', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Starting migration....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $success = 0;
        $failed = 0;

        $q = Doctrine_Query::create()
           ->from("SfGuardUserCategories a")
           ->where("a.id = ?", $arguments['user_category']);
        $user_category = $q->fetchOne(); 

        if($user_category)
        {
            $business_profile_form = $user_category->getFormid();

            $this->logSection('permitflow', 'Found profile with form id: '.$business_profile_form);

            $q = Doctrine_Query::create()
                ->from("SavedPermit a")
                ->where("a.type_id = ?", $arguments['permit_id']);
            $permits = $q->count(); 

            $this->logSection('permitflow', 'Found '.$permits.' need to be created');

            $query  = "select * from saved_permit where type_id = ? group by application_id";

            $params = array($arguments['permit_id']);

            $sth = mf_do_query($query,$params,$dbh);

            while($permit_rw = mf_do_fetch_result($sth))
            {
                $q = Doctrine_Query::create()
                    ->from("SavedPermit a")
                    ->where("a.id = ?", $permit_rw['id']);
                $permit = $q->fetchOne(); 

                $application = $permit->getFormEntry();
                
                $q = Doctrine_Query::create()
                    ->from("MfUserProfile a")
                    ->where("a.form_id = ? AND a.entry_id = ?", array($business_profile_form, $application->getEntryId()));
                if($q->count() == 0)
                {
                    $this->logSection('permitflow', 'Converting '.$application->getApplicationId());

                    //Get list of all columns
                    $master_columns = "";
                    $master_columns_array = array();

                    $new_columns_array = array();

                        //New Table
                        $query  = "DESCRIBE ap_form_".$business_profile_form;

                        $params = array();

                        $sth2 = mf_do_query($query,$params,$dbh);

                        while($row = mf_do_fetch_result($sth2))
                        {
                            $new_columns_array[] = $row['Field'];
                        }

                        //Master Table
                        $query  = "DESCRIBE ap_form_".$application->getFormId();

                        $params = array();

                        $sth2 = mf_do_query($query,$params,$dbh);

                        while($row = mf_do_fetch_result($sth2))
                        {
                            if(in_array($row['Field'], $new_columns_array))
                            {
                                $master_columns_array[] = $row['Field'];
                            }
                        }

                        $master_columns = implode(", ", $master_columns_array);

                    //Need to use mysql to copy content from application_form_id to business_profile_form table
                    $query  = "insert into ap_form_".$business_profile_form." (".$master_columns.") 
                        select ".$master_columns." from ap_form_".$application->getFormId()." where id = ".$application->getEntryId();

                    $params = array();

                    $sth1 = mf_do_query($query,$params,$dbh);

                    $user = $application->getSfGuardUser();

                    $userprofile = new MfUserProfile();
                    $userprofile->setUserId($user->getId());
                    $userprofile->setFormId($business_profile_form);
                    $userprofile->setEntryId($application->getEntryId());
                    $userprofile->setCreatedAt(date("Y-m-d"));
                    $userprofile->setUpdatedAt(date("Y-m-d"));
                    $userprofile->setDeleted(0);
                    $userprofile->save();

                    $this->logSection('permitflow', '-- DONE --');

                    $success++;
                }
                else{
                    $this->logSection('permitflow', 'Duplicate found for '.$application->getApplicationId());
                }
            }
        }

        $this->logSection('permitflow', 'Completed migrate-profiles task with '.$success.' successful and '.$failed.' failed.');
    }
}