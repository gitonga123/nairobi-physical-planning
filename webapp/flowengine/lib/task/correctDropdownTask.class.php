<?php

class correctDropdownTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "correct-dropdown";
        $this->briefDescription    = 'Tries to match the options selected in one column, with the options selected in another';

        $this->detailedDescription = <<<EOF
The [permitflow:correct-dropdown|INFO] task tries to match the options selected in one column, with the options selected in another e.g
If someone deletes a form field by mistake and creates another form field but the option ids dont match up. As a result we may also need to
update any of the existing invoices that have not been paid for.

  [./symfony permitflow:correct-dropdown|INFO]

  Specify the form id with the inconsistent data [--form_id|COMMENT] argument.
  Specify the new field id with the inconsistent data  [--new_field_id|COMMENT] argument.
  Specify the old field id with the correct data [--old_field_id|COMMENT] argument.
  Match only the first few letters of an option [--trancate|COMMENT] argument.
EOF;

        $this->addArgument('old_form_id', sfCommandArgument::REQUIRED, 'Specify the form id', null);
        $this->addArgument('old_field_id', sfCommandArgument::REQUIRED, 'Specify the old field id', null);
        $this->addArgument('new_form_id', sfCommandArgument::REQUIRED, 'Specify the form id', null);
        $this->addArgument('new_field_id', sfCommandArgument::REQUIRED, 'Specify the new field id', null);
        $this->addArgument('trancate', sfCommandArgument::OPTIONAL, 'Match only the first few letters of an option', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Starting correction procedure....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $invoice_manager = new InvoiceManager();

        $prefix_folder = dirname(__FILE__)."/../vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $success = 0;
        $failed = 0;

        $query  = "select id, element_".$arguments['old_field_id']." from ap_form_".$arguments['old_form_id'];

        $params = array();

        $sth = mf_do_query($query,$params,$dbh);

        while($old_row = mf_do_fetch_result($sth))
        {
            $old_option_id = $old_row['element_'.$arguments['old_field_id']];

            $q = Doctrine_Query::create()
               ->from("ApElementOptions a")
               ->where("a.form_id = ?", $arguments['old_form_id'])
               ->andWhere("a.element_id = ?", $arguments['old_field_id'])
               ->andWhere("a.option_id = ?", $old_option_id);
            $old_option = $q->fetchOne();

            if($old_option)
            {
                $old_option_text = $old_option->getOptionText();

                $new_option = null;

                if($arguments['trancate'])
                {
                    $old_option_text = substr($old_option_text, 0, $arguments['trancate']);

                    $q = Doctrine_Query::create()
                        ->from("ApElementOptions a")
                        ->where("a.form_id = ?", $arguments['new_form_id'])
                        ->andWhere("a.element_id = ?", $arguments['new_field_id'])
                        ->andWhere("a.option_text LIKE ?", $old_option_text."%");
                    $new_option = $q->fetchOne();
                }
                else
                {
                    $q = Doctrine_Query::create()
                        ->from("ApElementOptions a")
                        ->where("a.form_id = ?", $arguments['new_form_id'])
                        ->andWhere("a.element_id = ?", $arguments['new_field_id'])
                        ->andWhere("a.option_text = ?", $old_option_text);
                    $new_option = $q->fetchOne();
                }

                $query  = "select id, element_".$arguments['new_field_id']." from ap_form_".$arguments['new_form_id']." where id = ".$old_row['id'];

                $params = array();

                $sth = mf_do_query($query,$params,$dbh);

                while($new_row = mf_do_fetch_result($sth))
                {
                  if($new_option)
                  {
                      $query1  = "update ap_form_".$arguments['new_form_id']." set element_".$arguments['new_field_id']." = ".$new_option->getOptionId()." where id = ".$new_row['id'];

                      $params1 = array();

                      $sth1 = mf_do_query($query1,$params1,$dbh);

                      $q = Doctrine_Query::create()
                          ->from("FormEntry a")
                          ->where("a.form_id = ? AND a.entry_id = ?", array($arguments['new_form_id'], $new_row['id']));
                      $application = $q->fetchOne();

                      if($application)
                      {
                          $invoice_manager->refresh_invoices($application->getId());
                          $this->logSection('permitflow', 'Updated invoices found for: '.$application->getApplicationId());
                      }

                      $this->logSection('permitflow', 'Matching Option found for: '.$old_option_text);
                      $success++;
                  }
                  else
                  {
                      $this->logSection('permitflow', 'No Matching Option found for: '.$old_option_text);
                      $failed++;
                  }
                }
            }
            else
            {
                $this->logSection('permitflow', 'No Old Option found for: '.$old_option_id);
                $failed++;
            }
        }

        $this->logSection('permitflow', 'Completed correct-dropdown task with '.$success.' successful and '.$failed.' failed.');
    }
}
