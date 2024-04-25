<?php 

class clearUnpaidPenaltiesTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "clear-unpaid-penalties";
        $this->briefDescription    = 'Clear all unpaid penalties and their invoices';
 
        $this->detailedDescription = <<<EOF
The [permitflow:clear-unpaid-penalties|INFO] task clear all unpaid penalties and their invoices
 
  [./symfony permitflow:clear-unpaid-penalties|INFO]
EOF;
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Clearing all unpaid penalties and their invoices');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $query  = "DELETE a, b FROM penalty a LEFT JOIN mf_invoice b on a.invoice_id = b.id WHERE a.paid = 0";

        $sth = mf_do_query($query,array(),$dbh);

        $this->logSection('permitflow', 'Done.');
        
    }
}