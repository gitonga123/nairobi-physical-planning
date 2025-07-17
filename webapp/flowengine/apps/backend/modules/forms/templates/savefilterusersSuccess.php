<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');
	
	require($prefix_folder.'includes/filter-functions.php');
	
	$filter_properties_array = mf_sanitize($_POST['filter_prop']);
	$filter_type = mf_sanitize($_POST['filter_type']);

	if(empty($filter_type) || empty($filter_properties_array)){
		die("This file can't be opened directly.");
	}

	//we only need to save the filter into session variable
	$_SESSION['filter_users'] = array();

	$i=0;
	foreach($filter_properties_array as $data){
		$_SESSION['filter_users'][$i]['element_name'] 	  = $data['element_name'];
		$_SESSION['filter_users'][$i]['filter_condition'] = $data['condition'];
		$_SESSION['filter_users'][$i]['filter_keyword']   = $data['keyword'];
		$i++;
	}
	
	$_SESSION['filter_users_type'] = $filter_type;

	$response_data = new stdClass();
	$response_data->status    	= "ok";
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>