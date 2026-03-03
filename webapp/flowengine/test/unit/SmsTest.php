<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';
 
$t = new lime_test(1);

$notifications = new mailnotifications();

$notifications->sendsms("0703138826", "OTP Permitflow Test");

$t->pass('Passed.');