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
 
$t = new lime_test(11);

$application_manager = new ApplicationManager();

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
$query  = "DELETE FROM ap_form_10124";
$params = array();
mf_do_query($query,$params,$dbh);

//Create test form_builder entries for user
$query  = "INSERT INTO ap_form_10124 (`element_1`, `element_2`) VALUES('Test 1 Description', 1);";
$params = array();
mf_do_query($query,$params,$dbh);

$application_one_form_id = 10124;
$application_one_entry_id = (int) $dbh->lastInsertId();

$query  = "INSERT INTO ap_form_10124 (`element_1`, `element_2`) VALUES('Test 2 Description', 2);";
$params = array();
mf_do_query($query,$params,$dbh);

$application_two_form_id = 10124;
$application_two_entry_id = (int) $dbh->lastInsertId();

$query  = "INSERT INTO ap_form_10124 (`element_1`, `element_2`) VALUES('Test 3 Description', 3);";
$params = array();
mf_do_query($query,$params,$dbh);

$application_three_form_id = 10124;
$application_three_entry_id = (int) $dbh->lastInsertId();

$query  = "INSERT INTO ap_form_10124 (`element_1`, `element_2`) VALUES('Test 4 Description', 1);";
$params = array();
mf_do_query($query,$params,$dbh);

$application_four_form_id = 10124;
$application_four_entry_id = (int) $dbh->lastInsertId();

//Create Business Profiles

$query  = "INSERT INTO ap_form_10997 (`element_1`, `element_2`) VALUES('Test Business 1', 1);";
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

$query  = "INSERT INTO ap_form_10997 (`element_1`, `element_2`) VALUES('Test Business 2', 1);";
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

//Create test form_builder entries for business
$query  = "INSERT INTO ap_form_10124 (`element_1`, `element_2`) VALUES('Test 1 Description for Business', 1);";
$params = array();
mf_do_query($query,$params,$dbh);

$business_application_one_form_id = 10124;
$business_application_one_entry_id = (int) $dbh->lastInsertId();

$query  = "INSERT INTO ap_form_10124 (`element_1`, `element_2`) VALUES('Test 2 Description for Business', 2);";
$params = array();
mf_do_query($query,$params,$dbh);

$business_application_two_form_id = 10124;
$business_application_two_entry_id = (int) $dbh->lastInsertId();

//Get service id of the renewal service
$q = Doctrine_Query::create()
   ->from("Menus a")
   ->where("a.service_type = 2");
$service = $q->fetchOne();

/**
*
* Application Manager: Functions test
*
**/

//Create application test

    //1. Test if application is draft (no payment)
    $submission_complete = $application_manager->create_application($application_one_form_id, $application_one_entry_id, 1, true);

    $t->is($application_manager->is_draft($submission_complete->getFormId(), $submission_complete->getEntryId()), true);

    //2. Test if application is not a draft (no payment)
    $submission_draft = $application_manager->create_application($application_two_form_id, $application_two_entry_id, 1, false);

    $t->is($application_manager->is_draft($submission_draft->getFormId(), $submission_draft->getEntryId()), false);

    //3. Test if application is draft (with payment)
    $submission_complete_with_pay = $application_manager->create_application($application_three_form_id, $application_three_entry_id, 1, true);

    $t->is($application_manager->is_draft($submission_complete_with_pay->getFormId(), $submission_complete_with_pay->getEntryId()), true);

    $q = Doctrine_Query::create()
       ->from("MfInvoice a")
       ->where("a.app_id = ?", $submission_complete_with_pay->getId())
       ->andWhere("a.paid = 1");

    $t->is($q->count(), 0);

    //4. Test if application is not a draft (with payment)
    $submission_draft_with_pay = $application_manager->create_application($application_four_form_id, $application_four_entry_id, 1, false);

    $t->is($application_manager->is_draft($submission_draft_with_pay->getFormId(), $submission_draft_with_pay->getEntryId()), false);

    //Generate invoices
    $application_manager->update_invoices($submission_draft_with_pay->getId());

    $q = Doctrine_Query::create()
       ->from("MfInvoice a")
       ->where("a.app_id = ?", $submission_draft_with_pay->getId())
       ->andWhere("a.paid = 1");

    $t->is($q->count(), 1);

//Create business application test
$business_submission_one = $application_manager->create_business_application($service->getId(), $business_application_one_form_id, $business_application_one_entry_id, 1, $business_one_id, "");
$t->is($application_manager->is_draft($business_submission_one->getFormId(), $business_submission_one->getEntryId()), false);

$business_submission_two = $application_manager->create_business_application($service->getId(), $business_application_two_form_id, $business_application_two_entry_id, 1, $business_two_id, "");
$t->is($application_manager->is_draft($business_submission_two->getFormId(), $business_submission_two->getEntryId()), false);

//Publish draft test 
$published_submission = $application_manager->publish_draft($submission_complete->getId());
$t->is($application_manager->is_draft($published_submission->getFormId(), $published_submission->getEntryId()), false);

//Resubmit application test
$published_submission->setDeclined(1);
$published_submission->save();

$t->is($published_submission->getDeclined(), 1);

$resubmission = $application_manager->resubmit_application($published_submission->getId());
$t->is($published_submission->getDeclined(), 0);

