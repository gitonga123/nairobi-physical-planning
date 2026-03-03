<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);

$databaseManager = new sfDatabaseManager($configuration);
$connection = $databaseManager->getDatabase("doctrine")->getConnection();
sfContext::createInstance($configuration);

$prefix_folder = dirname(__FILE__)."/../../lib/vendor/form_builder/";
require_once($prefix_folder.'includes/init.php');

require_once($prefix_folder.'../../../config/form_builder_config.php');
require_once($prefix_folder.'includes/db-core.php');
require_once($prefix_folder.'includes/helper-functions.php');

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);
 
$t = new lime_test(6);

$business_manager = new BusinessManager();

/**
*
* Application Manager: Load Test Data
*
**/

//Load fixtures with empty data to clear tables
Doctrine_Core::loadData(sfConfig::get('sf_test_dir').'/reset_fixtures');

//Load fixtures with test configurations
Doctrine_Core::loadData(sfConfig::get('sf_test_dir').'/fixtures');

//Clear test form_builder entries
$query  = "DELETE FROM ap_form_10997";
$params = array();
mf_do_query($query,$params,$dbh);

//Create Business Profiles

$option_id_one = 0;
$option_id_two = 0;

$q = Doctrine_Query::create()
   ->from("ApElementOptions a")
   ->where("a.form_id = ?", 10997)
   ->andWhere("a.element_id = ?", 2)
   ->orderBy("a.option_id ASC");

$count = 0;

foreach($q->execute() as $option)
{
    $count++;

    if($count == 1)
    {
        $option_id_one = $option->getOptionId();
    }
    elseif($count == 2)
    {
        $option_id_two = $option->getOptionId();
    }
}

$query  = "INSERT INTO ap_form_10997 (`element_1`, `element_2`) VALUES('Test Business 1', ".$option_id_one.");";
$params = array();
mf_do_query($query,$params,$dbh);

$userprofile = new MfUserProfile();
$userprofile->setUserId(1);
$userprofile->setFormId(10997);
$userprofile->setEntryId((int) $dbh->lastInsertId());
$userprofile->setCreatedAt(date("Y-m-d"));
$userprofile->setUpdatedAt(date("Y-m-d"));
$userprofile->setDeleted(0);
$userprofile->save();

$business_one_id = $userprofile->getId();

$query  = "INSERT INTO ap_form_10997 (`element_1`, `element_2`) VALUES('Test Business 2', ".$option_id_two.");";
$params = array();
mf_do_query($query,$params,$dbh);

$userprofile = new MfUserProfile();
$userprofile->setUserId(1);
$userprofile->setFormId(10997);
$userprofile->setEntryId((int) $dbh->lastInsertId());
$userprofile->setCreatedAt(date("Y-m-d"));
$userprofile->setUpdatedAt(date("Y-m-d"));
$userprofile->setDeleted(0);
$userprofile->save();

$business_two_id = $userprofile->getId();

/**
*
* Business Manager: Functions test
*
**/

//Test first business with amount 10
$business_manager->generate_cyclic_bills($business_one_id);

$q = Doctrine_Query::create()
   ->from("FormEntry a")
   ->where("a.business_id = ?", $business_one_id);

$t->is($q->count(), 1);

$business_application_one = $q->fetchOne();

$q = Doctrine_Query::create()
    ->from("MfInvoice a")
    ->where("a.app_id = ?", $business_application_one->getId())
    ->andWhere("a.paid = 1");

$t->is($q->count(), 1);

$business_invoice_one = $q->fetchOne();

$t->is($business_invoice_one->getTotalAmount(), "10");

//Test second business with amount 20
$business_manager->generate_cyclic_bills($business_two_id);

$q = Doctrine_Query::create()
   ->from("FormEntry a")
   ->where("a.business_id = ?", $business_two_id);

$t->is($q->count(), 1);

$business_application_two = $q->fetchOne();

$q = Doctrine_Query::create()
    ->from("MfInvoice a")
    ->where("a.app_id = ?", $business_application_two->getId())
    ->andWhere("a.paid = 1");

$t->is($q->count(), 1);

$business_invoice_two = $q->fetchOne();

$t->is($business_invoice_two->getTotalAmount(), "20");