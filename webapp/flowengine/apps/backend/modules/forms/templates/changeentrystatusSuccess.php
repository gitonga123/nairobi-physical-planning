<?php
	$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');

	require($prefix_folder.'includes/filter-functions.php');
	require($prefix_folder.'includes/entry-functions.php');
	require($prefix_folder.'includes/users-functions.php');
	
	$form_id 		   	= (int) trim($_POST['form_id']);
	$entry_id 		   	= (int) trim($_POST['entry_id']);
	$user_id		   	= (int) $_SESSION['mf_user_id'];
	
	if(empty($form_id) || empty($entry_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries permission
		if(empty($user_perms['edit_entries'])){
			die("Access Denied. You don't have permission to edit this entry.");
		}
	}

	//generate resume key	
	$form_resume_key = substr(strtolower(md5(uniqid(rand(), true))),0,10);

	//update the record with the resume key
	$query = "UPDATE `".MF_TABLE_PREFIX."form_{$form_id}` set `status`=2,resume_key='{$form_resume_key}' where `id`=?";
	$params = array($entry_id);
						
	mf_do_query($query,$params,$dbh);

	$form_resume_url = "/plan/forms/view?id={$form_id}&mf_resume={$form_resume_key}";

	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	$response_data->entry_id 	= $entry_id;
	$response_data->resume_url 	= $form_resume_url;

	$response_json = json_encode($response_data);
		
	echo $response_json;
?>