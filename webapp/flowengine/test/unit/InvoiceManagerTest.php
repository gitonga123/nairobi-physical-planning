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
 
$t = new lime_test(1);

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


$t->ok("This test has run");
