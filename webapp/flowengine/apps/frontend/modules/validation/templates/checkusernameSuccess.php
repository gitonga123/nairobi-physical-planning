<?php
/**
 * checkusernameSuccess.php template.
 *
 * Validates whether an account with a similar username exists
 *
 * @package    frontend
 * @subpackage validation
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
if($username)
 {
	$q = Doctrine_Query::create()
	   ->from('sfGuardUser a')
	   ->where('a.username = ?', $username);
	$usernames = $q->execute();
	if($usernames && sizeof($usernames) > 0)
	{
		$status = "<font style='font-size: 14px;'><img src='".public_path()."assets_frontend/images/stop.png'> Username is already in use. Please enter a different username.</font>";
	}
	else
	{
		$status = "<img src='".public_path()."assets_frontend/images/ok.png'>";		
	}
 }
 else
 {
	$status = "<font style='font-size: 14px;'><img src='".public_path()."assets_frontend/images/stop.png'> Please Enter a Username</fony>";
 }
echo html_entity_decode($status);
?>
