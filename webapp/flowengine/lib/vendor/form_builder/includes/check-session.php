<?php
/********************************************************************************
 MachForm - Unmanned

 Disabling all user checks from here and allowing admin
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/

	$_SESSION['mf_logged_in'] = true;
	$_SESSION['mf_user_id']	  = 1;
	$_SESSION['mf_user_privileges']['priv_administer'] = true;
	$_SESSION['mf_user_privileges']['priv_new_forms']  = true;
	$_SESSION['mf_user_privileges']['priv_new_themes'] = true;
	
?>