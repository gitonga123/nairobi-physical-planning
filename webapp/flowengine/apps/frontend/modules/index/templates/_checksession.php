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
if($sf_user->getGuardUser() == null)
{
  header("Location: /plan/signon/logout");
}

$sf_user->setAttribute('backend', false);
?>
