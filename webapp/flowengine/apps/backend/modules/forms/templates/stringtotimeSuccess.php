<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";

	require($prefix_folder.'includes/init.php');
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');
	
	require($prefix_folder.'includes/filter-functions.php');

	$dbh = mf_connect_db();
	
	$_POST = mf_sanitize($_POST);
	
	$default_date = trim($_POST['default_date']);
	$input_format = trim($_POST['date_format']);

	$default_time = trim($_POST['default_time']);
	$time_24hour  = trim($_POST['time_24hour']);
		
	$response_data 	  = new stdClass();
	
	if(!empty($default_date)){
		$slash_pos = strpos($default_date,'/');
		if(($input_format == 'europe_date') && !empty($slash_pos) ){
			//if the input format is europe date (dd/mm/yyyy) and the input is ##/##/#### we need to convert the input into mm/dd/yyyy format
			//since the strtotime function only accept mm/dd/yyyy
			$exploded = explode('/',$default_date);
			$default_date = $exploded[1].'/'.$exploded[0].'/'.$exploded[2];
		}
		
		$timestamp = strtotime($default_date);

		if(($timestamp !== false) && ($timestamp != -1)){
			$response_data->status    			= "ok";
			$response_data->default_date 		= date('d-m-Y', $timestamp);
			
		}else{
			$response_data->status    			= "false";
		}
	}else if(!empty($default_time)){
		$timestamp = strtotime($default_time);

		if(($timestamp !== false) && ($timestamp != -1)){

			if(!empty($time_24hour)){
				$default_time = date('H-i-s-A', $timestamp);
			}else{
				$default_time = date('h-i-s-A', $timestamp);
			}

			$response_data->status    			= "ok";
			$response_data->default_time 		= $default_time;
		}else{
			$response_data->status    			= "false";
		}
	}
	
	
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
	
?>