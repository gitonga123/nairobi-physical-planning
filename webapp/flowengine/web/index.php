<?php


require_once(__DIR__.'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', false);
sfContext::createInstance($configuration)->dispatch();
