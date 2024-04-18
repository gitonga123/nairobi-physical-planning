<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';
 
$t = new lime_test(1);

$databaseManager = new sfDatabaseManager($this->configuration);
$connection = $databaseManager->getDatabase("doctrine")->getConnection();

$notifications = new mailnotifications();

$notifications->sendemail("test@campfossa.org", "thomasjgx@gmail.com", "Mail Permitflow Test", "This is a mail test");

$t->pass('Passed.');