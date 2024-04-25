<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');
	
	require($prefix_folder.'includes/filter-functions.php');
	
	$form_id = (int) trim($_POST['form_id']);
	
	parse_str($_POST['widget_pos']); 
	$widget_positions = $widget_pos; //contain the positions of the widgets
	unset($el_pos);
	

	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	
	//update widget positions
	$query = "UPDATE ".MF_TABLE_PREFIX."report_elements SET chart_position = ? WHERE form_id = ? AND chart_id = ?";

	$i = 1;
	foreach($widget_positions as $chart_id){
		$params = array($i,$form_id,$chart_id);
		mf_do_query($query,$params,$dbh);
		$i++;
	}

	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>