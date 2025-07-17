<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');

	require($prefix_folder.'includes/filter-functions.php');
	
	$form_id 	= (int) trim($_POST['form_id']);
	$user_id	= (int) $_SESSION['mf_user_id'];

	$incomplete_entries = (int) $_POST['incomplete_entries']; //if this is operation targetted to incomplete entries, this will contain '1'
	if(empty($incomplete_entries)){
		$incomplete_entries = 0;
	}

	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	
	//first delete all previous filter
	$query = "delete from `".MF_TABLE_PREFIX."form_filters` where form_id=? and user_id=? and incomplete_entries=?";
	$params = array($form_id,$user_id,$incomplete_entries);
	mf_do_query($query,$params,$dbh);

	//update existing record within ap_entries_preferences
	if(empty($incomplete_entries)){
		$query = "update ".MF_TABLE_PREFIX."entries_preferences set entries_enable_filter=0,entries_filter_type='all' where form_id=? and user_id=?";
	}else{
		$query = "update ".MF_TABLE_PREFIX."entries_preferences set entries_incomplete_enable_filter=0,entries_incomplete_filter_type='all' where form_id=? and user_id=?";
	}
	
	$params = array($form_id,$user_id);
	mf_do_query($query,$params,$dbh);
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>