<?php
	$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');

	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/filter-functions.php');
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$delete_success = false;
	
	$error_message = '';
	
	if(!empty($_POST['filename']) &&!empty($_POST['form_id']) && !empty($_POST['element_id']) && !empty($_POST['holder_id']) && !empty($_POST['key_id']) && !empty($_POST['is_db_live']) && !empty($_POST['saved_filename'])){
		
		$filename 	= trim($_POST['filename']);
		$form_id 	= (int) $_POST['form_id'];
		$element_id = (int) $_POST['element_id'];
		$holder_id = trim($_POST['holder_id']);
		$saved_filename = trim($_POST['saved_filename']);
		$key_id = (int) $_POST['key_id'];
		$is_db_live = (int) $_POST['is_db_live'];
		
		
		if(!is_writable($machform_data_path.$mf_settings['upload_dir']."/form_{$form_id}/files")){
			//return error
			$error_message = "Unable to write into upload folder!";
		}else{
		
			//move file and check for invalid file
			$destination_file = $machform_data_path.$mf_settings['upload_dir']."/form_{$form_id}/files/element_{$element_id}_{$saved_filename}-{$key_id}-{$filename}";
			
			
			//destination file already exists
			if(file_exists($destination_file)){
				try{
					unlink($destination_file);
					$delete_success = true;
				}catch(Exception $e){
					$error_message = $e->getMessage();
				}
			}else{
				$delete_success = true;
				$error_message  = "File not found!";
			}

		}
	}
	
	$response_data = new stdClass();
	
	if($delete_success){
		$response_data->status    	= "ok";
		$response_data->message 	= $filename.' '.$error_message;
		$response_data->holder_id 	= $holder_id;
		$response_data->element_id 	= $element_id;
	}else{
		$response_data->status    	= "error";
		$response_data->message 	= $error_message;
		$response_data->holder_id 	= $holder_id;
		$response_data->element_id 	= $element_id;
	}
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
	
	//we need to use output buffering to be able capturing error messages
	$output = ob_get_contents();
	ob_end_clean();
	
	echo $output;
	
	//OTB ADD
	exit;
?>