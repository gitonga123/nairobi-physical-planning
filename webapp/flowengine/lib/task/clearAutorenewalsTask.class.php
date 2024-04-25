<?php 

class clearAutorenewalsTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "clear-autorenewals";
        $this->briefDescription    = 'Clear autorenewals';
 
        $this->detailedDescription = <<<EOF
The [permitflow:clear-autorenewals|INFO] task clear all unpaid auto-renewals linked to applications that are still in the approval process
 
  [./symfony permitflow:clear-autorenewals|INFO]

  filter to check [--filter|COMMENT] = business_id.
EOF;
        $this->addArgument('filter', sfCommandArgument::OPTIONAL, 'business id to check', null);
        $this->addArgument('linked_form_id', sfCommandArgument::OPTIONAL, 'linked form id', null);
        $this->addArgument('linked_element_id', sfCommandArgument::OPTIONAL, 'linked element id that acts as title', null);
        $this->addArgument('final_permit', sfCommandArgument::OPTIONAL, 'final permit id', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Clears all unpaid auto-renewals linked to applications that are still in the approval process');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $business_manager = new BusinessManager();

        $sucess = 0;
        $failed = 0;

        if($arguments['filter'] && ($arguments['filter'] != "false" && $arguments['filter'] != false))
        {
            $q = Doctrine_Query::create()
                ->from("MfUserProfile a")
                ->where("a.id = ?", $arguments['filter'])
                ->andWhere("a.deleted = 0");
            $business = $q->fetchOne();

            if($business)
            {
                try
                {
                    $this->logSection('permitflow', 'Checking '.$business->getTitle().' for linked applications');
                    
                    $q = Doctrine_Query::create()
                       ->from("MfInvoice a")
                       ->leftJoin("a.FormEntry b")
                       ->where("b.business_id = ?", $business->getId())
                       ->andWhere("a.paid = 2");
                    
                    if($q->count() == 0)
                    {
                        error_log("Business is unpaid...");

                        //Look for linked applications without final permits
                        $q = Doctrine_Query::create()
                           ->from("FormEntry a")
                           ->where("a.form_id = ?", $arguments['linked_form_id'])
                           ->andWhere("a.entry_id = ?", $business->getEntryId())
                           ->andWhere("a.business_id is NULL OR a.business_id = 0");
                        $linked_applications = $q->execute();
                        
                        foreach($linked_applications as $linked_application)
                        {   
                            // Check for similarties between both applications
                            if($business_manager->are_linked($business, $linked_application, $arguments['linked_element_id']))
                            {
                                error_log("Found linked application: ".$linked_application->getApplicationId());

                                //Check if linked application has the required permits. If not then delete business
                                $q = Doctrine_Query::create()
                                    ->from("SavedPermit a")
                                    ->where("a.application_id = ?", $linked_application->getId())
                                    ->andWhere("a.type_id = ".$arguments['final_permit']);
                                
                                if($q->count() == 0)
                                {
                                    $business->delete();
                                }
                            }
                        }
                    }
                    else 
                    {
                        error_log("Skipping businesss ".$business->getTitle());
                    }
                    
                    $success++;
                }catch(Exception $ex)
                {
                    $this->logSection('permitflow', $ex->getMessage());
                    $failed++;
                }
            }
        }
        else 
        {
            $databaseManager = new sfDatabaseManager($this->configuration);
            $connection = $databaseManager->getDatabase("doctrine")->getConnection();
            
            $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
            require_once($prefix_folder.'includes/init.php');

            require_once($prefix_folder.'../../../config/form_builder_config.php');
            require_once($prefix_folder.'includes/db-core.php');
            require_once($prefix_folder.'includes/helper-functions.php');

            $dbh = mf_connect_db();
            $mf_settings = mf_get_settings($dbh);
            
            //Get all business profiles linked to this service
            $query  = "SELECT * FROM mf_user_profile WHERE deleted = 0";

            $params = array();

            $sth = mf_do_query($query,$params,$dbh);

            while($business_rw = mf_do_fetch_result($sth))
            {
                try
                {
                    $this->logSection('permitflow', 'Checking #'.$business_rw['id'].' for linked applications');

                    $q = Doctrine_Query::create()
                        ->from("MfUserProfile a")
                        ->where("a.id = ?", $business_rw['id'])
                        ->andWhere("a.deleted = 0");
                    $business = $q->fetchOne();

                    $q = Doctrine_Query::create()
                       ->from("MfInvoice a")
                       ->leftJoin("a.FormEntry b")
                       ->where("b.business_id = ?", $business->getId())
                       ->andWhere("a.paid = 2");
                    
                    if($q->count() == 0)
                    {
                        error_log("Business is unpaid...");

                        //Look for linked applications without final permits
                        $q = Doctrine_Query::create()
                           ->from("FormEntry a")
                           ->where("a.form_id = ?", $arguments['linked_form_id'])
                           ->andWhere("a.entry_id = ?", $business->getEntryId())
                           ->andWhere("a.business_id is NULL OR a.business_id = 0");
                        $linked_applications = $q->execute();
                        
                        foreach($linked_applications as $linked_application)
                        {   
                            // Check for similarties between both applications
                            if($business_manager->are_linked($business, $linked_application, $arguments['linked_element_id']))
                            {
                                error_log("Found linked application: ".$linked_application->getApplicationId());

                                //Check if linked application has the required permits. If not then delete business
                                $q = Doctrine_Query::create()
                                    ->from("SavedPermit a")
                                    ->where("a.application_id = ?", $linked_application->getId())
                                    ->andWhere("a.type_id = ".$arguments['final_permit']);
                                
                                if($q->count() == 0)
                                {
                                    $business->delete();
                                }
                            }
                        }
                    }
                    else 
                    {
                        error_log("Skipping businesss ".$business->getTitle());
                    }
                    
                    $success++;
                }catch(Exception $ex)
                {
                    $this->logSection('permitflow', $ex->getMessage());
                    $failed++;
                }
            }
        }

        $this->logSection('permitflow', 'Completed clear-autorenewals task with '.$success.' successful and '.$failed.' failed.');
    }
}