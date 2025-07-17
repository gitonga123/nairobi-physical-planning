<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');

	require($prefix_folder.'includes/filter-functions.php');
	require($prefix_folder.'includes/theme-functions.php');
	require($prefix_folder.'includes/users-functions.php');
		
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	if(empty($_POST['form_id'])){
		die("Error! You can't open this file directly");
	}
	
	$form_id = (int) $_POST['form_id'];
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("Access Denied. You don't have permission to delete this form.");
		}
	}

	//depends on the config file 
	//when deleting the form, we can simply set the status of the form_active to '9' which means deleted
	//or delete the form data completely
	if(MF_CONF_TRUE_DELETE === true){ //true deletion
		
		//remove from ap_forms
		$query = "delete from ".MF_TABLE_PREFIX."forms where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
		
		//remove from ap_form_elements
		$query = "delete from ".MF_TABLE_PREFIX."form_elements where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
		
		//remove from ap_element_options
		$query = "delete from ".MF_TABLE_PREFIX."element_options where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
		
		//remove from ap_column_preferences
		$query = "delete from ".MF_TABLE_PREFIX."column_preferences where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_entries_preferences
		$query = "delete from ".MF_TABLE_PREFIX."entries_preferences where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_form_locks
		$query = "delete from ".MF_TABLE_PREFIX."form_locks where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_element_prices
		$query = "delete from ".MF_TABLE_PREFIX."element_prices where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_field_logic_elements table
		$query = "delete from ".MF_TABLE_PREFIX."field_logic_elements where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_field_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."field_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_page_logic table
		$query = "delete from ".MF_TABLE_PREFIX."page_logic where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_page_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."page_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_email_logic table
		$query = "delete from ".MF_TABLE_PREFIX."email_logic where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_email_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."email_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_webhook_options table
		$query = "delete from ".MF_TABLE_PREFIX."webhook_options where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_webhook_parameters table
		$query = "delete from ".MF_TABLE_PREFIX."webhook_parameters where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_success_logic_options table
		$query = "delete from ".MF_TABLE_PREFIX."success_logic_options where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_success_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."success_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_reports table
		$query = "delete from ".MF_TABLE_PREFIX."reports where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_report_elements table
		$query = "delete from ".MF_TABLE_PREFIX."report_elements where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_report_filters table
		$query = "delete from ".MF_TABLE_PREFIX."report_filters where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//remove from ap_grid_columns table
		$query = "delete from ".MF_TABLE_PREFIX."grid_columns where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
		
		//remove review table
		$query = "drop table if exists `".MF_TABLE_PREFIX."form_{$form_id}_review`";
		$params = array();
		mf_do_query($query,$params,$dbh);
		
		//remove the actual form table
		$query = "drop table if exists `".MF_TABLE_PREFIX."form_{$form_id}`";
		$params = array();
		mf_do_query($query,$params,$dbh);
		
		//remove form folder
		@mf_full_rmdir($mf_settings['upload_dir']."/form_{$form_id}");
		if($mf_settings['upload_dir'] != $mf_settings['data_dir']){
			@mf_full_rmdir($mf_settings['data_dir']."/form_{$form_id}");
		}

		//remove ap_form_xxx_files table
		$query = "drop table if exists ".MF_TABLE_PREFIX."form_{$form_id}_files";
		$params = array();
		mf_do_query($query,$params,$dbh);
		
	}else{ //safe deletion
		$query = "update ".MF_TABLE_PREFIX."forms set form_active=9 where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
	}
	
	//delete entries from ap_permissions table, regardless of the config
	$query = "delete from ".MF_TABLE_PREFIX."permissions where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);


	$_SESSION['MF_SUCCESS'] = 'The form has been deleted.';
  
   	echo '{ "status" : "ok" }';
	
?>