<?php
/**
 * _checksession.php template.
 *
 * Checks whether the user is logged in correctly
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 **/

//If no user is authenticated then signout. Backend session and Frontend session mix ups
if($sf_user->getAttribute("userid") == null || $sf_user->getAttribute("backend") != true)
{
  header("Location: /plan/logout");
  exit;
}

$login_manager = new LoginManager();
if($login_manager->two_factor_pass() == false)
{
  header("Location: /plan/login/twofactor");
  exit;
}
?>
