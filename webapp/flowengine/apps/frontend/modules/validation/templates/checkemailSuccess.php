<?php
/**
 * checkemailSuccess.php template.
 *
 * Validates whether an account with a similar email exists
 *
 * @package    frontend
 * @subpackage validation
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

if($email)
 {
	$q = Doctrine_Query::create()
	   ->from('sfGuardUserProfile a')
	   ->where('a.email = ?', $email);
	$usernames = $q->execute();
	if($usernames && sizeof($usernames) > 0)
	{
		$status = "<font style='font-size: 14px;'><img src='".public_path()."assets_frontend/images/stop.png'> Email is already in use. Please enter a different email.</font>";
	}
	else
	{
		$status = "<img src='".public_path()."assets_frontend/images/ok.png'>";		
	}
 }
 else
 {
	$status = "<font style='font-size: 14px;'><img src='".public_path()."assets_frontend/images/stop.png'> Please Enter an Email address</fony>";
 }
echo html_entity_decode($status);
?>
