<?php 

class reverseMissingFeeTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "reverse-missing-fee";
        $this->briefDescription    = 'Reverse missing fee to all applications with pending invoices / create invoice with mising fee for paid invoices';
 
        $this->detailedDescription = <<<EOF
The [permitflow:reverse-missing-fee|INFO] task removes missing fee from all applications with pending invoices / create invoice with mising fee for paid invoices.
 
  [./symfony permitflow:reverse-missing-fee|INFO]

  form id to check [--filter|COMMENT] = form_id
  fee id [--fee_id|COMMENT] = fee_id
  trigger creation of invoices for already paid applications [--create|COMMENT] = create_boolean.
EOF;

    $this->addArgument('filter', sfCommandArgument::REQUIRED, 'service_id to check', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for applications with missing form fee');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();
        
        $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $invoice_manager = new InvoiceManager();

        $success = 0;
        $failed = 0;

        $query  = "SELECT a.id as id, a.app_id as app_id, a.paid as paid FROM mf_invoice a LEFT JOIN mf_invoice_detail b ON a.id = b.invoice_id LEFT JOIN form_entry c ON a.app_id = c.id WHERE c.approved <> 0 AND c.service_id = ".$arguments['filter']." AND b.description <> 'Penalty Month 1' ORDER BY a.id DESC";

        $params = array();

        $sth = mf_do_query($query,$params,$dbh);

        while($invoice_rw = mf_do_fetch_result($sth))
        {
            try 
            {
                if($invoice_rw['paid'] == 1)
                {
                    $this->logSection('permitflow', "Remove duplicates in invoice:".$invoice_rw['id']." with fee: ".$fee_description." of amount: ".$fee_amount);
                    $invoice_manager->reverse_duplicate_with_amount($invoice_rw['id'], "Application Form Fee", 500);
                }

                $success++;
            }catch(Exception $ex)
            {
                $this->logSection('permitflow', $ex->getMessage());
                $failed++;
            }
        }

        $this->logSection('permitflow', 'Completed add-missing-fee task with '.$success.' successful and '.$failed.' failed.');

    }

}