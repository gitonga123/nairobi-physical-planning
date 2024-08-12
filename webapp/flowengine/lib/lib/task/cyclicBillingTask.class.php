<?php 

class cyclicBillingTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "cyclic-billing";
        $this->briefDescription    = 'Generate cyclic bills';
 
        $this->detailedDescription = <<<EOF
The [permitflow:cyclic-billing|INFO] task checks each stage for expiration settings and checks if there 
are any applications that are overdue in those stages.
 
  [./symfony permitflow:cyclic-billing|INFO]

  business id to check [--filter|COMMENT] = business_id.
EOF;

        $this->addArgument('filter', sfCommandArgument::OPTIONAL, 'business id to check', null);
        $this->addArgument('delete', sfCommandArgument::OPTIONAL, 'delete unpaid invoices to regenerate new fees', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for services with cyclic billing');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();
        
        $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $business_manager = new BusinessManager();

        $success = 0;
        $failed = 0;

        $q = Doctrine_Query::create()
           ->from("Menus a")
           ->where("a.service_type = ?", 2);
        $services = $q->execute();

        foreach($services as $service)
        {
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
                        $this->logSection('permitflow', 'Checking '.$business->getTitle().' for with cyclic billing');
                        
                        if($arguments['delete'])
                        {
                            $business_manager->clear_cyclic_bills($business->getId());
                        }
                        
                        $business_manager->generate_cyclic_bills($business->getId());
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
                //Get all business profiles linked to this service
                $query  = "SELECT * FROM mf_user_profile WHERE form_id = ".$service->getServiceForm()." AND deleted = 0";

                $params = array();

                $sth = mf_do_query($query,$params,$dbh);

                while($business_rw = mf_do_fetch_result($sth))
                {
                    try
                    {
                        if($arguments['delete'])
                        {
                            $business_manager->clear_cyclic_bills($business_rw['id']);
                        }

                        $business_manager->generate_cyclic_bills($business_rw['id']);
                        $success++;
                    }catch(Exception $ex)
                    {
                        $this->logSection('permitflow', $ex->getMessage());
                        $failed++;
                    }
                }
            }
        }

        $this->logSection('permitflow', 'Completed cyclic-billing task with '.$success.' successful and '.$failed.' failed.');

    }

}