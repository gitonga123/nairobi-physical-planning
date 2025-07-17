<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		die("You don't have permission to administer MachForm.");
	}

	$_SESSION['filter_users'] = array();
	unset($_SESSION['filter_users']);
	
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>