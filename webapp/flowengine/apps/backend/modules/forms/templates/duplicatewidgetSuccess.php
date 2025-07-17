<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');
	require($prefix_folder.'includes/users-functions.php');
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$form_id  = (int) $_POST['form_id'];
	$chart_id = (int) $_POST['chart_id'];
	$duplicate_success = false;
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_report permission
		if(empty($user_perms['edit_report'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this report.";

			$ssl_suffix = mf_get_ssl_suffix();						
			header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//generate new access key for the widget
	$new_access_key = $form_id.'x'.substr(strtolower(md5(uniqid(rand(), true))),0,10);

	//get new chart_id for this new widget
	$query = "select ifnull(max(`chart_id`),0) + 1 as new_chart_id from ".MF_TABLE_PREFIX."report_elements where form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	$new_chart_id = $row['new_chart_id'];

	//copy ap_report_elements table
	$query = "show columns from ".MF_TABLE_PREFIX."report_elements";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'chart_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_report_elements table
	$query = "insert into 
							`".MF_TABLE_PREFIX."report_elements`(form_id, chart_id, `{$columns_joined}`) 
					   select 
							? , ?, `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."report_elements` 
						where 
							form_id = ? and chart_id = ?";
	$params = array($form_id,$new_chart_id,$form_id,$chart_id);
	mf_do_query($query,$params,$dbh);

	//update access key and widget title for the duplicate widget
	$query  = "UPDATE ".MF_TABLE_PREFIX."report_elements 
				  SET 
				  	access_key = ?,
				  	chart_title = concat(chart_title,' Copy') 
			    WHERE 
			        form_id = ? AND chart_id = ?";
	$params = array($new_access_key, $form_id, $new_chart_id);
	mf_do_query($query,$params,$dbh);

	
	//copy ap_report_filters table
	//get the columns of ap_report_filters table
	$query = "show columns from ".MF_TABLE_PREFIX."report_filters";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'arf_id' || $row['Field'] == 'chart_id' ){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_report_filters table
	$query = "insert into 
							`".MF_TABLE_PREFIX."report_filters`(form_id, chart_id, `{$columns_joined}`) 
					   select 
							? , ?, `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."report_filters` 
						where 
							form_id = ? and chart_id = ? order by arf_id asc";
	$params = array($form_id,$new_chart_id,$form_id,$chart_id);
	mf_do_query($query,$params,$dbh);

	
	
	$duplicate_success = true;

	$response_data = new stdClass();
	
	if($duplicate_success){
		$response_data->status    	= "ok";
	}else{
		$response_data->status    	= "error";
	}
	
	$response_data->form_id 	= $form_id;
	$response_json = json_encode($response_data);
	
	$_SESSION['MF_SUCCESS'] = 'Your widget has been duplicated.';
	
	echo $response_json;
	
?>