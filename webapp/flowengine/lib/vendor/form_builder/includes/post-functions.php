<?php
function mf_process_form($dbh, $input)
{
	global $mf_lang;
	//the default is not to store file upload as blob, unless defined otherwise within config.php file
	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB', false);

	$form_id = (int) trim($input['form_id']);
	$edit_id = (int) trim($input['edit_id']);

	if (empty($input['page_number'])) {
		$page_number = 1;
	} else {
		$page_number = (int) $input['page_number'];
	}
	error_log('-------page_number-----' . $page_number);
	$is_committed = false;

	$mf_settings = mf_get_settings($dbh);

	//this function handle password submission and general form submission
	//check for password requirement
	$query = "select 
						form_password,
						form_language,
						form_review,
						form_page_total,
						logic_field_enable,
						logic_page_enable,
						logic_success_enable  
					from 
						`" . MF_TABLE_PREFIX . "forms` where form_id=?";
	$params = array($form_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	$form_review = $row['form_review'];
	$form_page_total = (int) $row['form_page_total'];
	$logic_field_enable = (int) $row['logic_field_enable'];
	$logic_page_enable = (int) $row['logic_page_enable'];
	$logic_success_enable = (int) $row['logic_success_enable'];

	if (!empty($row['form_password'])) {
		$require_password = true;
	} else {
		$require_password = false;
	}

	if (!empty($logic_success_enable)) {
		$process_result['logic_success_enable'] = true;
	}

	if (!empty($row['form_language'])) {
		mf_set_language($row['form_language']);
	}

	//if this form require password and no session has been set
	if ($require_password && (empty($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] != $form_id)) {

		$query = "select count(form_id) valid_password from `" . MF_TABLE_PREFIX . "forms` where form_id=? and form_password=?";
		$params = array($form_id, $input['password']);

		$sth = mf_do_query($query, $params, $dbh);
		$row = mf_do_fetch_result($sth);

		if (!empty($row['valid_password'])) {
			$process_result['status'] = true;
			$_SESSION['user_authenticated'] = $form_id;
		} else {
			$process_result['status'] = false;
			$process_result['custom_error'] = $mf_lang['form_pass_invalid'];
		}
		error_log('---form password-----status-------' . $process_result['status']);
		return $process_result;
	}

	$delay_notifications = false;
	$form_properties = array();

	$form_properties = mf_get_form_properties(
		$dbh,
		$form_id,
		array(
			'payment_enable_merchant',
			'payment_delay_notifications',
			'payment_merchant_type',
			'payment_enable_discount',
			'payment_discount_element_id',
			'payment_discount_code',
			'payment_onsubmission'
		)
	);

	//delay notifications if this option turned on within payment setting page
	//this option is not available for check/cash
	if (
		($form_properties['payment_enable_merchant'] == 1) && !empty($form_properties['payment_delay_notifications']) &&
		in_array($form_properties['payment_merchant_type'], array('stripe', 'paypal_standard', 'authorizenet', 'paypal_rest', 'braintree'))
	) {
		$delay_notifications = true;
	}


	$element_child_lookup['address'] = 5;
	$element_child_lookup['simple_name'] = 1;
	$element_child_lookup['simple_name_wmiddle'] = 2;
	$element_child_lookup['name'] = 3;
	$element_child_lookup['name_wmiddle'] = 4;
	$element_child_lookup['phone'] = 2;
	$element_child_lookup['date'] = 2;
	$element_child_lookup['europe_date'] = 2;
	$element_child_lookup['time'] = 3;
	$element_child_lookup['money'] = 1; //this applies to dollar,euro and pound. yen don't have child
	$element_child_lookup['checkbox'] = 1; //this is just a dumb value
	$element_child_lookup['matrix'] = 1; //this is just a dumb value
	//OTB ADD
	$element_child_lookup['plinth_area'] = 1; //this is just a dumb value
	$element_child_lookup['total_plinth_area'] = 1; //this is just a dumb value

	//never trust user input, get a list of input fields based on info stored on table
	//element has real child -> address, simple_name, name, simple_name_wmiddle, name_wmiddle
	//element has virtual child -> phone, date, europe_date, time, money

	$is_edit_page = false;
	if (!empty($edit_id) && $_SESSION['mf_logged_in'] === true) {
		//if this is edit_entry page, process all elements on all pages at once
		$page_number_clause = '';
		$params = array($form_id);
		$is_edit_page = true;
	} else {
		$page_number_clause = 'and element_page_number =?';
		$params = array($form_id, $page_number);
	}
	error_log('-------is_edit_page------');
	error_log(var_export($is_edit_page, true));

	$query = "SELECT 
						element_id,
       					element_title,
       					element_is_required,
       					element_is_unique,
       					element_is_private,
       					element_type, 
       					element_constraint,
       					element_total_child,
       					element_file_enable_multi_upload,
       					element_file_max_selection,
       					element_file_enable_type_limit,
       					element_file_block_or_allow,
       					element_file_type_list,
       					element_range_max,
       					element_range_min,
       					element_range_limit_by,
       					element_choice_has_other,
       					element_time_showsecond,
       					element_time_24hour,
       					element_matrix_parent_id,
       					element_matrix_allow_multiselect,
       					element_date_enable_range,
       					element_date_range_min,
       					element_date_range_max,
       					element_date_past_future,
       					element_date_disable_past_future,
       					element_date_enable_selection_limit,
						element_date_selection_max,
						element_date_disable_weekend,
						element_date_disable_specific,
						element_date_disabled_list,
						element_choice_limit_rule,
						element_choice_limit_qty,
						element_choice_limit_range_min,
						element_choice_limit_range_max,
						element_field_name,
						element_field_value,
						element_option_query,
						element_remote_username,
						element_remote_password,
						element_remote_server_field,
						element_remote_post,
						element_remote_value,
						element_notify_contact,
						element_plotsize,
						element_actualplotratio,
						element_permittedgroundcoverage,
						element_zone,
						element_permitteduser,
						element_footprint,
						element_grandtotalplintharea,
						element_file_ifc,
						element_plot_no,
						element_block_no,
						element_subcounty,
						element_owner_phone,
						element_owner_address,
						element_owner_email,
						element_ward,
						element_plot_area,
						element_plot_length,
						element_plot_width,
						element_ownertype,
						element_table_name,
						element_field_value,
						element_field_name,
						element_option_query,
						element_existing_form,
						element_existing_stage,
						element_select_options
					FROM 
						" . MF_TABLE_PREFIX . "form_elements 
				   WHERE 
				   		form_id=? and element_status = '1' {$page_number_clause} and element_type <> 'page_break' and element_type <> 'section'
				ORDER BY 
						element_id asc";

	$sth = mf_do_query($query, $params, $dbh);


	$element_to_get = array();
	$private_elements = array(); //admin-only fields
	$matrix_childs_array = array();


	while ($row = mf_do_fetch_result($sth)) {
		if ($row['element_type'] == 'section') {
			continue;
		}

		//store element info
		$element_info[$row['element_id']]['title'] = $row['element_title'];
		$element_info[$row['element_id']]['type'] = $row['element_type'];
		$element_info[$row['element_id']]['is_required'] = $row['element_is_required'];
		$element_info[$row['element_id']]['is_unique'] = $row['element_is_unique'];
		$element_info[$row['element_id']]['is_private'] = $row['element_is_private'];
		$element_info[$row['element_id']]['constraint'] = $row['element_constraint'];
		$element_info[$row['element_id']]['file_enable_multi_upload'] = $row['element_file_enable_multi_upload'];
		$element_info[$row['element_id']]['file_max_selection'] = $row['element_file_max_selection'];
		$element_info[$row['element_id']]['file_enable_type_limit'] = $row['element_file_enable_type_limit'];
		$element_info[$row['element_id']]['file_block_or_allow'] = $row['element_file_block_or_allow'];
		$element_info[$row['element_id']]['file_type_list'] = $row['element_file_type_list'];
		$element_info[$row['element_id']]['range_min'] = $row['element_range_min'];
		$element_info[$row['element_id']]['range_max'] = $row['element_range_max'];
		$element_info[$row['element_id']]['range_limit_by'] = $row['element_range_limit_by'];
		$element_info[$row['element_id']]['choice_has_other'] = $row['element_choice_has_other'];
		$element_info[$row['element_id']]['time_showsecond'] = (int) $row['element_time_showsecond'];
		$element_info[$row['element_id']]['time_24hour'] = (int) $row['element_time_24hour'];
		$element_info[$row['element_id']]['matrix_parent_id'] = (int) $row['element_matrix_parent_id'];
		$element_info[$row['element_id']]['matrix_allow_multiselect'] = (int) $row['element_matrix_allow_multiselect'];
		$element_info[$row['element_id']]['date_enable_range'] = (int) $row['element_date_enable_range'];
		$element_info[$row['element_id']]['date_range_max'] = $row['element_date_range_max'];
		$element_info[$row['element_id']]['date_range_min'] = $row['element_date_range_min'];
		$element_info[$row['element_id']]['date_past_future'] = $row['element_date_past_future'];
		$element_info[$row['element_id']]['date_disable_past_future'] = (int) $row['element_date_disable_past_future'];
		$element_info[$row['element_id']]['date_enable_selection_limit'] = (int) $row['element_date_enable_selection_limit'];
		$element_info[$row['element_id']]['date_selection_max'] = (int) $row['element_date_selection_max'];
		$element_info[$row['element_id']]['date_disable_weekend'] = (int) $row['element_date_disable_weekend'];
		$element_info[$row['element_id']]['date_disable_specific'] = (int) $row['element_date_disable_specific'];
		$element_info[$row['element_id']]['date_disabled_list'] = $row['element_date_disabled_list'];
		$element_info[$row['element_id']]['option_query'] = $row['element_option_query'];
		$element_info[$row['element_id']]['field_name'] = $row['element_field_name'];
		$element_info[$row['element_id']]['field_value'] = $row['element_remote_value'];
		$element_info[$row['element_id']]['remote_username'] = $row['element_remote_username'];
		$element_info[$row['element_id']]['remote_password'] = $row['element_remote_password'];
		$element_info[$row['element_id']]['remote_server_field'] = $row['element_remote_server_field'];
		$element_info[$row['element_id']]['remote_post'] = $row['element_remote_post'];
		$element_info[$row['element_id']]['plot_no'] = $row['element_plot_no'];
		$element_info[$row['element_id']]['block_no'] = $row['element_block_no'];
		$element_info[$row['element_id']]['subcounty'] = $row['element_subcounty'];
		$element_info[$row['element_id']]['owner_phone'] = $row['element_owner_phone'];
		$element_info[$row['element_id']]['owner_address'] = $row['element_owner_address'];
		$element_info[$row['element_id']]['owner_email'] = $row['element_owner_email'];
		$element_info[$row['element_id']]['ward'] = $row['element_ward'];
		$element_info[$row['element_id']]['plot_area'] = $row['element_plot_area'];
		$element_info[$row['element_id']]['plot_length'] = $row['element_plot_length'];
		$element_info[$row['element_id']]['plot_width'] = $row['element_plot_width'];
		$element_info[$row['element_id']]['file_ifc'] = $row['element_file_ifc'];
		$element_info[$row['element_id']]['ownertype'] = $row['element_ownertype'];
		$element_info[$row['element_id']]['table_name'] = $row['element_table_name'];
		$element_info[$row['element_id']]['field_value'] = $row['element_field_value'];
		$element_info[$row['element_id']]['option_query'] = $row['element_option_query'];
		$element_info[$row['element_id']]['existing_form'] = $row['element_existing_form'];
		$element_info[$row['element_id']]['existing_stage'] = $row['element_existing_stage'];
		$element_info[$row['element_id']]['select_options'] = $row['element_select_options'];

		if (!empty($row['element_choice_limit_rule'])) {
			$element_info[$row['element_id']]['choice_limit_rule'] = $row['element_choice_limit_rule'];
		} else {
			$element_info[$row['element_id']]['choice_limit_rule'] = 'atleast';
		}

		if (!empty($row['element_choice_limit_qty'])) {
			$element_info[$row['element_id']]['choice_limit_qty'] = (int) $row['element_choice_limit_qty'];
		} else {
			$element_info[$row['element_id']]['choice_limit_qty'] = 1;
		}

		$element_info[$row['element_id']]['choice_limit_range_min'] = (int) $row['element_choice_limit_range_min'];
		$element_info[$row['element_id']]['choice_limit_range_max'] = (int) $row['element_choice_limit_range_max'];


		//get element form name, complete with the childs
		if (empty($element_child_lookup[$row['element_type']]) || ($row['element_constraint'] == 'yen')) { //elements with no child
			$element_to_get[] = 'element_' . $row['element_id'];
		} else { //elements with child
			if ($row['element_type'] == 'checkbox' || ($row['element_type'] == 'matrix' && !empty($row['element_matrix_allow_multiselect']))) {

				//for checkbox, get childs elements from ap_element_options table 
				$sub_query = "select 
										option_id 
									from 
										" . MF_TABLE_PREFIX . "element_options 
								   where 
								   		form_id=? and element_id=? and live=1 
								order by 
										`position` asc";
				$params = array($form_id, $row['element_id']);

				$sub_sth = mf_do_query($sub_query, $params, $dbh);
				while ($sub_row = mf_do_fetch_result($sub_sth)) {
					$element_to_get[] = "element_{$row['element_id']}_{$sub_row['option_id']}";
					$checkbox_childs[$row['element_id']][] = $sub_row['option_id']; //store the child into array for further reference
				}

				//if this is the parent of the matrix (checkbox matrix only), get the child as well
				if ($row['element_type'] == 'matrix' && !empty($row['element_matrix_allow_multiselect'])) {

					$temp_matrix_child_element_id_array = explode(',', trim($row['element_constraint']));

					foreach ($temp_matrix_child_element_id_array as $mc_element_id) {
						$sub_query = "select 
											option_id 
										from 
											" . MF_TABLE_PREFIX . "element_options 
									   where 
									   		form_id=? and element_id=? and live=1 
									order by 
											`position` asc";
						$params = array($form_id, $mc_element_id);

						$sub_sth = mf_do_query($sub_query, $params, $dbh);
						while ($sub_row = mf_do_fetch_result($sub_sth)) {
							$element_to_get[] = "element_{$mc_element_id}_{$sub_row['option_id']}";
							$checkbox_childs[$mc_element_id][] = $sub_row['option_id']; //store the child into array for further reference
						}

					}
				}
			} else if ($row['element_type'] == 'matrix' && empty($row['element_matrix_allow_multiselect'])) { //radio button matrix, each row doesn't have childs
				$element_to_get[] = 'element_' . $row['element_id'];
			} else {
				$max = $element_child_lookup[$row['element_type']] + 1;

				for ($j = 1; $j <= $max; $j++) {
					$element_to_get[] = "element_{$row['element_id']}_{$j}";
				}
			}
		}


		//if the back button pressed after review page, or this is multipage form, we need to store the file info
		if ((!empty($_SESSION['review_id']) && !empty($form_review)) || ($form_page_total > 1) || ($is_edit_page === true)) {
			if ($row['element_type'] == 'file') {
				$existing_file_id[] = $row['element_id'];
			}
		}

		//if this is matrix field, particularly the child rows, we need to store the id into temporary array
		//we need to loop through it later, to set the "required" property based on the matrix parent value
		if ($row['element_type'] == 'matrix' && !empty($row['element_matrix_parent_id'])) {
			$matrix_childs_array[$row['element_id']] = $row['element_matrix_parent_id'];
		}

		//if this is text field, check for the coupon code field status
		if (
			($form_properties['payment_enable_merchant'] == 1) && ($is_edit_page === false) && !empty($form_properties['payment_enable_discount']) &&
			!empty($form_properties['payment_discount_element_id']) && !empty($form_properties['payment_discount_code']) &&
			($form_properties['payment_discount_element_id'] == $row['element_id'])
		) {

			$element_info[$row['element_id']]['is_coupon_field'] = true;
		}

		//extra security measure for file upload
		//even though the user disabled 'file type limit', we need to enforce it here and block dangerous files
		if ($row['element_type'] == 'file') {

			//if the 'Limit File Upload Type' disabled by user, enable it here and check for dangerous files
			if (empty($row['element_file_enable_type_limit'])) {
				$element_info[$row['element_id']]['file_enable_type_limit'] = 1;
				$element_info[$row['element_id']]['file_block_or_allow'] = 'b'; //block
				$element_info[$row['element_id']]['file_type_list'] = 'php,php3,php4,php5,phtml,exe,pl,cgi,html,htm,js';
			} else {
				//if the limit being enabled but the list type is empty
				if (empty($element_info[$row['element_id']]['file_type_list'])) {
					$element_info[$row['element_id']]['file_block_or_allow'] = 'b'; //block
					$element_info[$row['element_id']]['file_type_list'] = 'php,php3,php4,php5,phtml,exe,pl,cgi,html,htm,js';
				} else {
					//if the list is not empty, and it set to block files, make sure to add dangerous file types into the list
					if ($element_info[$row['element_id']]['file_block_or_allow'] == 'b') {
						$element_info[$row['element_id']]['file_type_list'] .= ',php,php3,php4,php5,phtml,exe,pl,cgi,html,htm,js';
					}
				}
			}
		}
	}


	//loop through each matrix childs array
	//if the parent matrix has required=1, the child need to be set the same
	//if the parent matrix allow multi select, the child need to be set the same
	if (!empty($matrix_childs_array)) {
		foreach ($matrix_childs_array as $matrix_child_element_id => $matrix_parent_element_id) {
			if (!empty($element_info[$matrix_parent_element_id]['is_required'])) {
				$element_info[$matrix_child_element_id]['is_required'] = 1;
			}
			if (!empty($element_info[$matrix_parent_element_id]['matrix_allow_multiselect'])) {
				$element_info[$matrix_child_element_id]['matrix_allow_multiselect'] = 1;
			}
		}
	}

	if (!empty($existing_file_id)) {
		$existing_file_id_list = '';
		foreach ($existing_file_id as $value) {
			$existing_file_id_list .= 'element_' . $value . ',';
		}
		$existing_file_id_list = rtrim($existing_file_id_list, ',');


		if (!empty($_SESSION['review_id'])) {
			$current_session_id = $_SESSION['review_id'];
			$query = "select {$existing_file_id_list} from " . MF_TABLE_PREFIX . "form_{$form_id}_review where `id`=?";
		} else if ($is_edit_page === true) { //if this is edit_entry.php page
			$current_session_id = $edit_id;
			$query = "select {$existing_file_id_list} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
		} else {
			$current_session_id = session_id();
			$query = "select {$existing_file_id_list} from " . MF_TABLE_PREFIX . "form_{$form_id}_review where `session_id`=?";
		}

		$params = array($current_session_id);

		$sth = mf_do_query($query, $params, $dbh);
		$row = mf_do_fetch_result($sth);
		foreach ($existing_file_id as $value) {
			if (!empty($row['element_' . $value])) {
				//EXISTING FILES
				//if advanced removing missing/deleted files
				if ($element_info[$value]['file_enable_multi_upload']) {
					//get files
					$existing_files_arr = explode('|', $row['element_' . $value]);
					if (count($existing_files_arr)) {
						$found_files_arr = [];
						foreach ($existing_files_arr as $file) {
							$listfile_name = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$file}";
							//populate with found files
							if (file_exists($listfile_name)) {
								$found_files_arr[] = $file;
							}
						}
						$element_info[$value]['existing_file'] = implode('|', $found_files_arr);
						//update column

						$files_found = mf_sanitize($element_info[$value]['existing_file']);
						//CLEAR ARRAY[]
						$params_update = [];
						$update_values = "element_{$value}=:element_{$value}";
						$params_update[':element_' . $value] = $files_found;

						$params_update[':id'] = $current_session_id;
						$query = "update " . MF_TABLE_PREFIX . "form_{$form_id} set {$update_values} where id=:id";
						mf_do_query($query, $params_update, $dbh);
					}
				} else {
					$element_info[$value]['existing_file'] = $row['element_' . $value];
				}
			}
		}
	}


	//pick user input
	$user_input = array();
	//OTB ADD
	//Empty input produces the same hash
	$empty_value = true;
	foreach ($element_to_get as $element_name) {
		if (strlen(@$input[$element_name])) {
			$empty_value = false;
		}
		$user_input[$element_name] = @$input[$element_name];
	}
	error_log('--------user_input----');
	error_log(print_r($user_input, true));
	$user_input_hash = md5(implode('', $user_input));
	error_log('------------user_input_hash-------' . $user_input_hash);
	//if entry hash exist, check for double submission
	//the default is to check for double submission, unles defined otherwise within the config.php file
	//OTB caused issues if no new input is submitted 
	//defined('MF_DISCARD_DUPLICATE_ENTRY') or define('MF_DISCARD_DUPLICATE_ENTRY',true);

	/*if(!empty($_SESSION['mf_entry_hash'][$form_id]) && MF_DISCARD_DUPLICATE_ENTRY === true){
				 
				 //if current entry hash is the same as previous, then the user is submitting the same info
				 //this is most likely double submission, so we can discard this
				 if($user_input_hash == $_SESSION['mf_entry_hash'][$form_id] && !$empty_value){
					 $process_result['status'] = true;
					 error_log('---user_input_hash-----status-------'.$process_result['status']);
					 return $process_result;
				 }
			 }*/

	//if conditional logic for field is being enabled, and this is not edit entry page
	//we need to check the status of all elements which become "hidden" due to conditions
	//any hidden fields should be discarded, so that it won't be required and won't be displayed within review page/email
	if (!empty($logic_field_enable) && $is_edit_page === false) {
		$hidden_elements = array();
		$hidden_elements = mf_get_hidden_elements($dbh, $form_id, $page_number, $input);
		if (!empty($hidden_elements)) {
			foreach ($hidden_elements as $element_id => $hidden_status) {
				$element_info[$element_id]['is_hidden'] = $hidden_status;

				if ($element_info[$element_id]['is_hidden'] == 1) {
					$element_info[$element_id]['is_required'] = 0;
				}

				//if this is matrix field, particularly the parent, we need to set the required property of the childs as well
				if ($element_info[$element_id]['type'] == 'matrix' && empty($element_info[$element_id]['matrix_parent_id'])) {
					foreach ($matrix_childs_array as $matrix_child_element_id => $matrix_parent_element_id) {
						if ($matrix_parent_element_id == $element_id) {
							if ($hidden_status == 1) {
								$element_info[$matrix_child_element_id]['is_required'] = 0;
								$element_info[$matrix_child_element_id]['is_hidden'] = 1;
							} else {

								//only set to 'required' if the field is actually having 'required' attribute from the beginning
								if (!empty($element_info[$matrix_parent_element_id]['is_required'])) {
									$element_info[$matrix_child_element_id]['is_required'] = 1;
									$element_info[$matrix_child_element_id]['is_hidden'] = 0;
								}
							}
						}
					}
				}
			}
		}
	} else if ((!empty($logic_field_enable) && $is_edit_page === true) || (!empty($logic_page_enable) && $is_edit_page === true)) {
		//if this edit entry page and has logic enabled (either skip page or field logic), disable all "required" fields
		foreach ($element_info as $element_id => $value) {
			$element_info[$element_id]['is_required'] = 0;
		}
	}

	$error_elements = array();
	$table_data = array();
	//validate input based on rules specified for each field
	foreach ($user_input as $element_name => $element_data) {

		//get element_id from element_name
		$exploded = array();
		$exploded = explode('_', $element_name);
		$element_id = $exploded[1];

		$rules = array();
		$target_input = array();

		$element_type = $element_info[$element_id]['type'];


		//if this is private fields and not logged-in as admin, bypass operation below, just supply the default value if any
		//if this is private fields and logged-in as admin and this is not edit-entry page, bypass operation below as well
		if ((($element_info[$element_id]['is_private'] == 1) && empty($_SESSION['mf_logged_in'])) || (($element_info[$element_id]['is_private'] == 1) && !empty($_SESSION['mf_logged_in']) && $is_edit_page === false)) {
			if (!empty($element_info[$element_id]['default_value'])) {
				$table_data['element_' . $element_id] = $element_info[$element_id]['default_value'];
			}
			continue;
		}


		//if this is matrix field, we need to convert the field type into radio button or checkbox
		if ('matrix' == $element_type) {
			$is_matrix_field = true;
			if (!empty($element_info[$element_id]['matrix_allow_multiselect'])) {
				$element_type = 'checkbox';
			} else {
				$element_type = 'radio';
			}
		} else {
			$is_matrix_field = false;
		}

		if ('text' == $element_type) { //Single Line Text

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
					$user_input[$element_name] = '';
				}
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			if (!empty($user_input[$element_name]) || is_numeric($user_input[$element_name])) {
				if (!empty($element_info[$element_id]['range_max']) && !empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['range_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_min'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_max'])) {
					$rules[$element_name]['max_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['min_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_min'];
				}
			}

			if ($element_info[$element_id]['is_coupon_field'] === true) {
				$rules[$element_name]['coupon'] = $form_id;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'coupon' rule
			}

			if (!empty($element_info[$element_id]['option_query'])) {
				$rules[$element_name]['remote_url'] = $element_info[$element_id]['option_query'] . "^" . $element_info[$element_id]['field_name'] . "^" . $element_info[$element_id]['field_value'] . "^" . $element_info[$element_id]['remote_username'] . "^" . $element_info[$element_id]['remote_password'] . "^" . $form_id . "^" . $element_info[$element_id]['remote_post'];
				$target_input['dbh'] = $dbh;
				$target_input['user_input'] = $input;
			}

			//OTB Add
			if ($element_info[$element_id]['plot_no']) {
				error_log('-------Plot-----');
				$rules[$element_name]['plot_no'] = true;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh
			}//OTB Add
			if ($element_info[$element_id]['subcounty']) {
				error_log('-------Plot-----');
				$rules[$element_name]['subcounty'] = true;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh
			}

			$target_input[$element_name] = $element_data;

			$validation_result = mf_validate_element($target_input, $rules);
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occurred
			$form_data[$element_name]['default_value'] = htmlspecialchars($element_data);

			//prepare data for table column
			$table_data[$element_name] = $element_data;

		} elseif ('textarea' == $element_type) { //Paragraph

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
					$user_input[$element_name] = '';
				}
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			if (!empty($user_input[$element_name]) || is_numeric($user_input[$element_name])) {
				if (!empty($element_info[$element_id]['range_max']) && !empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['range_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_min'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_max'])) {
					$rules[$element_name]['max_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['min_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_min'];
				}
			}

			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($element_data);

			//prepare data for table column
			$table_data[$element_name] = $element_data;

		} elseif ('signature' == $element_type) { //Signature

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
				}
			}

			$target_input[$element_name] = $element_data;
			if ($target_input[$element_name] == '[]') { //this is considered as empty signature
				$target_input[$element_name] = '';
			}
			$validation_result = mf_validate_element($target_input, $rules);
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($element_data, ENT_NOQUOTES);

			//prepare data for table column
			$table_data[$element_name] = $element_data;

		} elseif ('radio' == $element_type) { //Multiple Choice

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
					$user_input[$element_name . '_other'] = '';
				}
			}

			//if this field has 'other' label
			if (!empty($element_info[$element_id]['choice_has_other'])) {
				if (empty($element_data) && !empty($input[$element_name . '_other'])) {
					$element_data = $input[$element_name . '_other'];

					//save old data into array, for form redisplay in case errors occured
					$form_data[$element_name . '_other']['default_value'] = $element_data;
					$table_data[$element_name . '_other'] = $element_data;

					//make sure to set the main element value to 0
					$form_data[$element_name]['default_value'] = 0;
					$table_data[$element_name] = 0;
				}
			}

			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);

			if ($validation_result !== true) {
				if ($is_matrix_field && !empty($matrix_childs_array[$element_id])) {
					$error_elements[$matrix_childs_array[$element_id]] = $validation_result;
				} else {
					$error_elements[$element_id] = $validation_result;
				}
			}

			//save old data into array, for form redisplay in case errors occured
			if (empty($form_data[$element_name . '_other']['default_value'])) {
				$form_data[$element_name]['default_value'] = $element_data;
			}

			//prepare data for table column
			if (empty($table_data[$element_name . '_other'])) {
				$table_data[$element_name] = $element_data;
			}

		} elseif ('number' == $element_type) { //Number

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
					$user_input[$element_name] = '';
				}
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			//check for numeric if not empty
			if (!empty($user_input[$element_name])) {
				$rules[$element_name]['numeric'] = true;
			}

			if ((!empty($user_input[$element_name]) || is_numeric($user_input[$element_name])) && ($element_info[$element_id]['range_limit_by'] == 'd')) {
				if (!empty($element_info[$element_id]['range_max']) && !empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['range_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_min'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_max'])) {
					$rules[$element_name]['max_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['min_length'] = $element_info[$element_id]['range_limit_by'] . '#' . $element_info[$element_id]['range_min'];
				}
			} else if ((!empty($user_input[$element_name]) || is_numeric($user_input[$element_name])) && ($element_info[$element_id]['range_limit_by'] == 'v')) {
				if (!empty($element_info[$element_id]['range_max']) && !empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['range_value'] = $element_info[$element_id]['range_min'] . '#' . $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_max'])) {
					$rules[$element_name]['max_value'] = $element_info[$element_id]['range_max'];
				} else if (!empty($element_info[$element_id]['range_min'])) {
					$rules[$element_name]['min_value'] = $element_info[$element_id]['range_min'];
				}
			}

			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($element_data);

			//prepare data for table column
			$table_data[$element_name] = $element_data;

			//if the user removed the number, set the value to null
			if ($table_data[$element_name] == "") {
				$table_data[$element_name] = null;
			}

		} elseif ('url' == $element_type) { //Website

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
				}
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			$rules[$element_name]['website'] = true;

			if ($element_data == 'http://') {
				$element_data = '';
			}

			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($element_data);

			//prepare data for table column
			$table_data[$element_name] = $element_data;

		} elseif ('email' == $element_type) { //Email

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($element_data)) {
					$element_data = '';
				}
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			$rules[$element_name]['email'] = true;


			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($element_data);

			//prepare data for table column
			$table_data[$element_name] = $element_data;

		} elseif ('simple_name' == $element_type) { //Simple Name

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 2 elements total	
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed on next loop

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
				}
			}

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_2]['required'] = true;
			}

			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_2] = $user_input[$element_name_2];

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];

		} elseif ('simple_name_wmiddle' == $element_type) { //Simple Name with Middle

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other elements, 3 elements total	
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed on next loop
			$processed_elements[] = $element_name_3;

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_3]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2]) && !strlen($user_input[$element_name_3])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
					$user_input[$element_name_3] = '';
				}
			}

			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_3] = $user_input[$element_name_3];

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];
			$table_data[$element_name_3] = $user_input[$element_name_3];

		} elseif ('name' == $element_type) { //Name -  Extended

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 4 elements total	
			//only element no 2&3 matters (first and last name)
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';
			$element_name_4 = substr($element_name, 0, -1) . '4';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed next
			$processed_elements[] = $element_name_3;
			$processed_elements[] = $element_name_4;

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name_2]['required'] = true;
				$rules[$element_name_3]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2]) && !strlen($user_input[$element_name_3]) && !strlen($user_input[$element_name_4])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
					$user_input[$element_name_3] = '';
					$user_input[$element_name_4] = '';
				}
			}

			$target_input[$element_name_2] = $user_input[$element_name_2];
			$target_input[$element_name_3] = $user_input[$element_name_3];


			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);
			$form_data[$element_name_4]['default_value'] = htmlspecialchars($user_input[$element_name_4]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];
			$table_data[$element_name_3] = $user_input[$element_name_3];
			$table_data[$element_name_4] = $user_input[$element_name_4];

		} elseif ('name_wmiddle' == $element_type) { //Name -  Extended, with Middle

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 5 elements total	
			//only element no 2,3,4 matters (first, middle, last name)
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';
			$element_name_4 = substr($element_name, 0, -1) . '4';
			$element_name_5 = substr($element_name, 0, -1) . '5';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed next
			$processed_elements[] = $element_name_3;
			$processed_elements[] = $element_name_4;
			$processed_elements[] = $element_name_5;

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name_2]['required'] = true;
				$rules[$element_name_4]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2]) && !strlen($user_input[$element_name_3]) && !strlen($user_input[$element_name_4]) && !strlen($user_input[$element_name_5])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
					$user_input[$element_name_3] = '';
					$user_input[$element_name_4] = '';
					$user_input[$element_name_5] = '';
				}
			}

			$target_input[$element_name_2] = $user_input[$element_name_2];
			$target_input[$element_name_4] = $user_input[$element_name_4];


			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);
			$form_data[$element_name_4]['default_value'] = htmlspecialchars($user_input[$element_name_4]);
			$form_data[$element_name_5]['default_value'] = htmlspecialchars($user_input[$element_name_5]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];
			$table_data[$element_name_3] = $user_input[$element_name_3];
			$table_data[$element_name_4] = $user_input[$element_name_4];
			$table_data[$element_name_5] = $user_input[$element_name_5];

		} elseif ('time' == $element_type) { //Time

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 4 elements total	

			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';
			$element_name_4 = substr($element_name, 0, -1) . '4';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed next
			$processed_elements[] = $element_name_3;
			$processed_elements[] = $element_name_4;

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name_2]['required'] = true;
				$rules[$element_name_3]['required'] = true;
				if (empty($element_info[$element_id]['time_24hour'])) {
					$rules[$element_name_4]['required'] = true;
				}
			}

			//check time validity if any of the compound field entered
			$time_entry_exist = false;
			if (!empty($user_input[$element_name]) || !empty($user_input[$element_name_2]) || !empty($user_input[$element_name_3])) {
				$rules['element_time']['time'] = true;
				$time_entry_exist = true;
			}

			//for backward compatibility with machform v2 and beyond
			if ($element_info[$element_id]['constraint'] == 'show_seconds') {
				$element_info[$element_id]['time_showsecond'] = 1;
			}

			if ($time_entry_exist && empty($element_info[$element_id]['time_showsecond'])) {
				$user_input[$element_name_3] = '00';
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules['element_time_no_meridiem']['unique'] = $form_id . '#' . substr($element_name, 0, -2); //to check uniquenes we need to use 24 hours HH:MM:SS format
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			$target_input[$element_name_2] = $user_input[$element_name_2];
			$target_input[$element_name_3] = $user_input[$element_name_3];
			$target_input[$element_name_4] = $user_input[$element_name_4];
			if ($time_entry_exist) {
				$target_input['element_time'] = trim($user_input[$element_name] . ':' . $user_input[$element_name_2] . ':' . $user_input[$element_name_3] . ' ' . $user_input[$element_name_4]);
				$target_input['element_time_no_meridiem'] = @date("G:i:s", strtotime($target_input['element_time']));
			}


			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);
			$form_data[$element_name_4]['default_value'] = htmlspecialchars($user_input[$element_name_4]);

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($target_input['element_time_no_meridiem'])) {
					$target_input['element_time_no_meridiem'] = null;
				}
			}

			//prepare data for table column
			$table_data[substr($element_name, 0, -2)] = @$target_input['element_time_no_meridiem'];

		} elseif ('address' == $element_type) { //Address

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 6 elements total, element #2 (address line 2) is optional	
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';
			$element_name_4 = substr($element_name, 0, -1) . '4';
			$element_name_5 = substr($element_name, 0, -1) . '5';
			$element_name_6 = substr($element_name, 0, -1) . '6';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed next
			$processed_elements[] = $element_name_3;
			$processed_elements[] = $element_name_4;
			$processed_elements[] = $element_name_5;
			$processed_elements[] = $element_name_6;

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_3]['required'] = true;
				$rules[$element_name_4]['required'] = true;
				$rules[$element_name_5]['required'] = true;
				$rules[$element_name_6]['required'] = true;

			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2]) && !strlen($user_input[$element_name_3]) && !strlen($user_input[$element_name_4]) && !strlen($user_input[$element_name_5]) && !strlen($user_input[$element_name_6])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
					$user_input[$element_name_3] = '';
					$user_input[$element_name_4] = '';
					$user_input[$element_name_5] = '';
					$user_input[$element_name_6] = '';
				}
			}

			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_3] = $user_input[$element_name_3];
			$target_input[$element_name_4] = $user_input[$element_name_4];
			$target_input[$element_name_5] = $user_input[$element_name_5];
			$target_input[$element_name_6] = $user_input[$element_name_6];


			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);
			$form_data[$element_name_4]['default_value'] = htmlspecialchars($user_input[$element_name_4]);
			$form_data[$element_name_5]['default_value'] = htmlspecialchars($user_input[$element_name_5]);
			$form_data[$element_name_6]['default_value'] = htmlspecialchars($user_input[$element_name_6]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];
			$table_data[$element_name_3] = $user_input[$element_name_3];
			$table_data[$element_name_4] = $user_input[$element_name_4];
			$table_data[$element_name_5] = $user_input[$element_name_5];
			$table_data[$element_name_6] = $user_input[$element_name_6];

		} elseif ('money' == $element_type) { //Price

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 2 elements total (for currency other than yen)	
			if ($element_info[$element_id]['constraint'] != 'yen') { //if other than yen
				$base_element_name = substr($element_name, 0, -1);
				$element_name_2 = $base_element_name . '2';
				$processed_elements[] = $element_name_2;

				if ($element_info[$element_id]['is_required']) {
					$rules[$base_element_name]['required'] = true;
				}

				if ($element_info[$element_id]['is_hidden']) {
					if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2])) {
						$user_input[$element_name] = '';
						$user_input[$element_name_2] = '';
					}
				}

				//check for numeric if not empty
				if (!empty($user_input[$element_name]) || !empty($user_input[$element_name_2])) {
					$rules[$base_element_name]['numeric'] = true;
				}

				if ($element_info[$element_id]['is_unique']) {
					$rules[$base_element_name]['unique'] = $form_id . '#' . substr($element_name, 0, -2);
					$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
				}

				$target_input[$base_element_name] = $user_input[$element_name] . '.' . $user_input[$element_name_2]; //join dollar+cent
				if ($target_input[$base_element_name] == '.') {
					$target_input[$base_element_name] = '';
				}

				//save old data into array, for form redisplay in case errors occured
				$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
				$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);

				//prepare data for table column
				if (
					!empty($user_input[$element_name]) || !empty($user_input[$element_name_2])
					|| $user_input[$element_name] === '0' || $user_input[$element_name_2] === '0'
				) {
					$table_data[substr($element_name, 0, -2)] = $user_input[$element_name] . '.' . $user_input[$element_name_2];
				}

				//if the user removed the number, set the value to null
				if ($user_input[$element_name] == "" && $user_input[$element_name_2] == "") {
					$table_data[substr($element_name, 0, -2)] = null;
				}
			} else {
				if ($element_info[$element_id]['is_required']) {
					$rules[$element_name]['required'] = true;
				}

				if ($element_info[$element_id]['is_hidden']) {
					if (!strlen($user_input[$element_name])) {
						$user_input[$element_name] = '';
					}
				}

				//check for numeric if not empty
				if (!empty($user_input[$element_name])) {
					$rules[$element_name]['numeric'] = true;
				}

				if ($element_info[$element_id]['is_unique']) {
					$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
					$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
				}

				$target_input[$element_name] = $user_input[$element_name];

				//save old data into array, for form redisplay in case errors occured
				$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);

				//prepare data for table column
				$table_data[$element_name] = $user_input[$element_name];

				//if the user removed the number, set the value to null
				if ($table_data[$element_name] == "") {
					$table_data[$element_name] = null;
				}

			}



			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

		} elseif ('checkbox' == $element_type) { //Checkboxes
			error_log('---------CHECKBOX-------');
			$all_checkbox_value = '';
			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}


			$all_child_array = array();
			$all_child_array = $checkbox_childs[$element_id];


			$base_element_name = 'element_' . $element_id . '_';

			if (!empty($element_info[$element_id]['choice_has_other'])) {
				if (!empty($input[$base_element_name . 'other'])) {
					$all_checkbox_value = '1';
				}

				//save old data into array, for form redisplay in case errors occured
				$form_data[$base_element_name . 'other']['default_value'] = $input[$base_element_name . 'other'];

				if ($element_info[$element_id]['is_hidden']) {
					if (!strlen($input[$base_element_name . 'other'])) {
						$input[$base_element_name . 'other'] = '';
					}
				}

				$table_data[$base_element_name . 'other'] = $input[$base_element_name . 'other'];
			} else {
				$all_checkbox_value = '';
			}

			if ($element_info[$element_id]['is_required']) {
				error_log('---------REQUIRED--------');
				//checking 'required' for checkboxes is more complex
				//we need to get total child, and join it into one element
				//only one element is required to be checked
				error_log('---------------CHILD ARRAY---------');
				error_log(print_r($all_child_array, true));
				foreach ($all_child_array as $i) {

					$all_checkbox_value .= $user_input[$base_element_name . $i];
					$processed_elements[] = $base_element_name . $i;

					//save old data into array, for form redisplay in case errors occured
					$form_data[$base_element_name . $i]['default_value'] = $user_input[$base_element_name . $i];

					//prepare data for table column
					$table_data[$base_element_name . $i] = $user_input[$base_element_name . $i];

				}

				$rules[$base_element_name]['required'] = true;
				error_log('--------------all_checkbox_value--------' . $all_checkbox_value);
				$target_input[$base_element_name] = $all_checkbox_value;
				$validation_result = mf_validate_element($target_input, $rules);
				
				error_log('------------VALIDATION------' . $validation_result);
				if ($validation_result !== true) {
					if ($is_matrix_field && !empty($matrix_childs_array[$element_id])) {
						$error_elements[$matrix_childs_array[$element_id]] = $validation_result;
					} else {
						$error_elements[$element_id] = $validation_result;
					}
				} else if ($validation_result === true) {
					//check for choices limit
					$rules = array();
					$target_input = array();

					$rules[$base_element_name]['choice_limit'] = $element_info[$element_id]['choice_limit_rule'] . '#' . $element_info[$element_id]['choice_limit_qty'] . '#' . $element_info[$element_id]['choice_limit_range_min'] . '#' . $element_info[$element_id]['choice_limit_range_max'];

					$target_input[$base_element_name] = $all_checkbox_value;
					$validation_result = mf_validate_element($target_input, $rules);

					if ($validation_result !== true) {
						$error_elements[$element_id] = $validation_result;
					}
				}
				error_log('---------------Error element-------' . $error_elements[$element_id]);

			} else { //if not required, we only need to capture all data
				error_log('----------NOT REQUIRED---------');
				foreach ($all_child_array as $i) {

					//save old data into array, for form redisplay in case errors occured
					$form_data[$base_element_name . $i]['default_value'] = $user_input[$base_element_name . $i];

					if ($element_info[$element_id]['is_hidden']) {
						if (!strlen($user_input[$base_element_name . $i])) {
							$user_input[$base_element_name . $i] = '';
						}
					}

					//prepare data for table column
					$table_data[$base_element_name . $i] = $user_input[$base_element_name . $i];
				}

			}
		} elseif ('select' == $element_type) { //Drop Down

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name])) {
					$user_input[$element_name] = '';
				}
			}
			error_log('-----------Element data--------------');
			error_log(var_export($element_data, true));
			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);
			
			error_log('---------VALIDATION----------');
			error_log(var_export($validation_result, true));
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}
			error_log('---------INPUT----' . $user_input[$element_name]);
			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = $user_input[$element_name];
			error_log('---------default_value----' . $form_data[$element_name]['default_value']);
			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			error_log('---------table_data----' . $table_data[$element_name]);

		} elseif ('date' == $element_type || 'europe_date' == $element_type) { //Date

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 3 elements total	

			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed next
			$processed_elements[] = $element_name_3;

			if (!empty($element_info[$element_id]['is_required'])) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_2]['required'] = true;
				$rules[$element_name_3]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2]) && !strlen($user_input[$element_name_3])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
					$user_input[$element_name_3] = '';
				}
			}

			$rules['element_date']['date'] = 'yyyy/mm/dd';

			if (!empty($element_info[$element_id]['is_unique'])) {
				$rules['element_date']['unique'] = $form_id . '#' . substr($element_name, 0, -2);
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			if (!empty($element_info[$element_id]['date_enable_range'])) {
				if (!empty($element_info[$element_id]['date_range_max']) || !empty($element_info[$element_id]['date_range_min'])) {
					$rules['element_date']['date_range'] = $element_info[$element_id]['date_range_min'] . '#' . $element_info[$element_id]['date_range_max'];
				}
			}

			//disable past/future dates, if enabled. this rule override the date range rule being set above
			if (!empty($element_info[$element_id]['date_disable_past_future']) && $is_edit_page === false) {
				$today_date = date('Y-m-d', time());

				if ($element_info[$element_id]['date_past_future'] == 'p') { //disable past dates
					$rules['element_date']['date_range'] = $today_date . '#0000-00-00';
				} else if ($element_info[$element_id]['date_past_future'] == 'f') { //disable future dates
					$rules['element_date']['date_range'] = '0000-00-00#' . $today_date;
				}
			}

			//check for weekend dates rule
			if (!empty($element_info[$element_id]['date_disable_weekend'])) {
				$rules['element_date']['date_weekend'] = true;
			}

			//get disabled dates (either coming from 'date selection limit' or 'disable specific dates' rules)
			$disabled_dates = array();

			//get disabled dates from 'date selection limit' rule
			if (!empty($element_info[$element_id]['date_enable_selection_limit']) && !empty($element_info[$element_id]['date_selection_max'])) {

				//if this is edit entry page, bypass the date selection limit rule when the selection being made is the same
				$disabled_date_where_clause = '';
				if (!empty($edit_id) && ($_SESSION['mf_logged_in'] === true)) {
					$disabled_date_where_clause = "AND `id` <> {$edit_id}";
				}

				$sub_query = "select 
										selected_date 
									from (
											select 
												  date_format(element_{$element_id},'%Y-%c-%e') as selected_date,
												  count(element_{$element_id}) as total_selection 
										      from 
										      	  " . MF_TABLE_PREFIX . "form_{$form_id} 
										     where 
										     	  status=1 and element_{$element_id} is not null {$disabled_date_where_clause} 
										  group by 
										  		  element_{$element_id}
										 ) as A
								   where 
										 A.total_selection >= ?";

				$params = array($element_info[$element_id]['date_selection_max']);
				$sub_sth = mf_do_query($sub_query, $params, $dbh);

				while ($sub_row = mf_do_fetch_result($sub_sth)) {
					$disabled_dates[] = $sub_row['selected_date'];
				}
			}


			//get disabled dates from 'disable specific dates' rules
			if (!empty($element_info[$element_id]['date_disable_specific']) && !empty($element_info[$element_id]['date_disabled_list'])) {
				$exploded = array();
				$exploded = explode(',', $element_info[$element_id]['date_disabled_list']);
				foreach ($exploded as $date_value) {
					$disabled_dates[] = date('Y-n-j', strtotime(trim($date_value)));
				}
			}

			if (!empty($disabled_dates)) {
				$rules['element_date']['disabled_dates'] = $disabled_dates;
			}

			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_2] = $user_input[$element_name_2];
			$target_input[$element_name_3] = $user_input[$element_name_3];

			$base_element_name = substr($element_name, 0, -2);
			if ('date' == $element_type) { //MM/DD/YYYY
				$target_input['element_date'] = $user_input[$element_name_3] . '-' . $user_input[$element_name] . '-' . $user_input[$element_name_2];

				//prepare data for table column
				$table_data[$base_element_name] = $user_input[$element_name_3] . '-' . $user_input[$element_name] . '-' . $user_input[$element_name_2];
			} else { //DD/MM/YYYY
				$target_input['element_date'] = $user_input[$element_name_3] . '-' . $user_input[$element_name_2] . '-' . $user_input[$element_name];

				//prepare data for table column
				$table_data[$base_element_name] = $user_input[$element_name_3] . '-' . $user_input[$element_name_2] . '-' . $user_input[$element_name];
			}

			$test_empty = str_replace('-', '', $target_input['element_date']); //if user not submitting any entry, remove the dashes
			if (empty($test_empty)) {
				unset($target_input['element_date']);
				$table_data[$base_element_name] = '';
			}

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);

		} elseif ('simple_phone' == $element_type) { //Simple Phone

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name])) {
					$user_input[$element_name] = '';
				}
			}

			if (!empty($user_input[$element_name])) {
				$rules[$element_name]['simple_phone'] = true;
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			$target_input[$element_name] = $user_input[$element_name];

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];

		} elseif ('phone' == $element_type) { //Phone - US format

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 3 elements total	

			$element_name_2 = substr($element_name, 0, -1) . '2';
			$element_name_3 = substr($element_name, 0, -1) . '3';

			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed next
			$processed_elements[] = $element_name_3;

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_2]['required'] = true;
				$rules[$element_name_3]['required'] = true;
			}

			$rules['element_phone']['phone'] = true;

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2]) && !strlen($user_input[$element_name_3])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
					$user_input[$element_name_3] = '';
				}
			}


			if ($element_info[$element_id]['is_unique']) {
				$rules['element_phone']['unique'] = $form_id . '#' . substr($element_name, 0, -2);
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_2] = $user_input[$element_name_2];
			$target_input[$element_name_3] = $user_input[$element_name_3];
			$target_input['element_phone'] = $user_input[$element_name] . $user_input[$element_name_2] . $user_input[$element_name_3];

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);
			$form_data[$element_name_3]['default_value'] = htmlspecialchars($user_input[$element_name_3]);

			//prepare data for table column
			$table_data[substr($element_name, 0, -2)] = $user_input[$element_name] . $user_input[$element_name_2] . $user_input[$element_name_3];

		} elseif ('email' == $element_type) { //Email

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
			}

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name])) {
					$user_input[$element_name] = '';
				}
			}

			if ($element_info[$element_id]['is_unique']) {
				$rules[$element_name]['unique'] = $form_id . '#' . $element_name;
				$target_input['dbh'] = $dbh; //we need to pass the $dbh for this 'unique' rule
			}

			$rules[$element_name]['email'] = true;

			$target_input[$element_name] = $element_data;
			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];

		} elseif ('file' == $element_type) { //File
			$listfile_name = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/listfile_{$input[$element_name . '_token']}.php";
			if (!file_exists($listfile_name)) {
				$check_filetype = false;
				if ($element_info[$element_id]['is_required']) {
					$rules[$element_name]['required_file'] = true;
					$rules[$element_name]['filetype'] = true;
					$check_filetype = true;

					//if form review enabled, and user pressed back button after going to review page
					//or if this is multipage form
					//disable the required file checking if file already uploaded
					if (!empty($_SESSION['review_id']) || ($form_page_total > 1) || ($is_edit_page === true)) {
						if (!empty($element_info[$element_id]['existing_file'])) {
							unset($rules[$element_name]['required_file']);

							//file type validation should only disabled when no file being uploaded
							if ($_FILES[$element_name]['size'] == 0) {
								unset($rules[$element_name]['filetype']);
								$check_filetype = false;
							}
						}
					}
				} else {
					if ($_FILES[$element_name]['size'] > 0) {
						$rules[$element_name]['filetype'] = true;
						$check_filetype = true;
					}
				}

				if ($check_filetype == true && !empty($element_info[$element_id]['file_enable_type_limit'])) {
					if ($element_info[$element_id]['file_block_or_allow'] == 'b') { //block file type
						$target_input['file_block_or_allow'] = 'b';
					} elseif ($element_info[$element_id]['file_block_or_allow'] == 'a') {
						$target_input['file_block_or_allow'] = 'a';
					}

					$target_input['file_type_list'] = $element_info[$element_id]['file_type_list'];
				}

				$target_input[$element_name] = $element_name; //special for file, only need to pass input name
				$validation_result = mf_validate_element($target_input, $rules);
				
				if ($validation_result !== true) {
					$error_elements[$element_id] = $validation_result;
				} else {
					//if validation passed, store uploaded file info into array
					if ($_FILES[$element_name]['size'] > 0) {
						$uploaded_files[] = $element_name;
					}
				}
			} else { //if files were uploaded using advance uploader
				//file type validation already done in upload.php, so we don't need to do validation again here
				//store uploaded file list into array
				$current_element_uploaded_files_advance = array();
				$current_element_uploaded_files_advance = file($listfile_name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				array_shift($current_element_uploaded_files_advance); //remove the first index of the array
				array_pop($current_element_uploaded_files_advance); //remove the last index of the array
				$uploaded_files_advance[$element_id]['listfile_name'] = $listfile_name;
				$uploaded_files_advance[$element_id]['listfile_content'] = $current_element_uploaded_files_advance;
				//save old token into array, for form redisplay in case errors occured
				$form_data[$element_name]['file_token'] = $input[$element_name . '_token'];

			}


		} elseif ('plinth_area' == $element_type) { // OTB patch - Plinth Area

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 2 elements total
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed on next loop

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
				}
			}

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_2]['required'] = true;
			}


			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_2] = $user_input[$element_name_2];

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];

		} elseif ('total_plinth_area' == $element_type) { // OTB patch - total plinth area

			if (!empty($processed_elements) && is_array($processed_elements) && in_array($element_name, $processed_elements)) {
				continue;
			}

			//compound element, grab the other element, 2 elements total
			$element_name_2 = substr($element_name, 0, -1) . '2';
			$processed_elements[] = $element_name_2; //put this element into array so that it won't be processed on next loop

			if ($element_info[$element_id]['is_hidden']) {
				if (!strlen($user_input[$element_name]) && !strlen($user_input[$element_name_2])) {
					$user_input[$element_name] = '';
					$user_input[$element_name_2] = '';
				}
			}

			if ($element_info[$element_id]['is_required']) {
				$rules[$element_name]['required'] = true;
				$rules[$element_name_2]['required'] = true;
			}

			$target_input[$element_name] = $user_input[$element_name];
			$target_input[$element_name_2] = $user_input[$element_name_2];

			$validation_result = mf_validate_element($target_input, $rules);
			
			if ($validation_result !== true) {
				$error_elements[$element_id] = $validation_result;
			}

			//save old data into array, for form redisplay in case errors occured
			$form_data[$element_name]['default_value'] = htmlspecialchars($user_input[$element_name]);
			$form_data[$element_name_2]['default_value'] = htmlspecialchars($user_input[$element_name_2]);

			//prepare data for table column
			$table_data[$element_name] = $user_input[$element_name];
			$table_data[$element_name_2] = $user_input[$element_name_2];

		}

	}

	//get form redirect info, if any
	//get form properties data
	$query = "select 
						 form_redirect,
						 form_redirect_enable,
						 form_email,
						 form_unique_ip,
						 form_unique_ip_maxcount,
						 form_unique_ip_period,
						 form_limit,
						 form_limit_enable,
						 form_captcha,
						 form_captcha_type,
						 form_review,
						 form_page_total,
						 form_resume_enable,
						 form_name,
						 esl_enable,
						 esl_from_name,
						 esl_from_email_address,
						 esl_bcc_email_address,
						 esl_replyto_email_address,
						 esl_subject,
						 esl_content,
						 esl_plain_text,
						 esl_pdf_enable,
						 esl_pdf_content,
						 esr_enable,
						 esr_email_address,
						 esr_from_name,
						 esr_from_email_address,
						 esr_bcc_email_address,
						 esr_replyto_email_address,
						 esr_subject,
						 esr_content,
						 esr_plain_text,
						 esr_pdf_enable,
						 esr_pdf_content,
						 webhook_enable,
						 payment_enable_merchant,
						 payment_merchant_type,
						 ifnull(payment_paypal_email,'') payment_paypal_email,
						 payment_paypal_language,
						 payment_currency,
						 payment_show_total,
						 payment_total_location,
						 payment_enable_recurring,
						 payment_recurring_cycle,
						 payment_recurring_unit,
						 payment_price_type,
						 payment_price_amount,
						 payment_price_name,
						 payment_enable_tax,
						 payment_tax_rate,
						 payment_enable_discount,
						 payment_discount_type,
						 payment_discount_amount,
						 payment_discount_element_id,
						 logic_email_enable,
						 logic_webhook_enable,
						 payment_onsubmission
				     from 
				     	 `" . MF_TABLE_PREFIX . "forms` 
				    where 
				    	 form_id=?";

	$params = array($form_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	if (!empty($row['form_redirect_enable'])) {
		$form_redirect = $row['form_redirect'];
	}

	$form_unique_ip = $row['form_unique_ip'];
	$form_unique_ip_maxcount = (int) $row['form_unique_ip_maxcount'];
	$form_unique_ip_period = $row['form_unique_ip_period'];

	$form_limit = (int) $row['form_limit'];
	$form_limit_enable = (int) $row['form_limit_enable'];

	$form_email = $row['form_email'];
	$form_captcha = $row['form_captcha'];

	$form_captcha_type = $row['form_captcha_type'];

	$form_review = $row['form_review'];
	$form_page_total = $row['form_page_total'];
	$form_name = $row['form_name'];

	$user_ip_address = $_SERVER['REMOTE_ADDR'];

	$esl_enable = $row['esl_enable'];
	$esl_from_name = $row['esl_from_name'];
	$esl_from_email_address = $row['esl_from_email_address'];
	$esl_replyto_email_address = $row['esl_replyto_email_address'];
	$esl_bcc_email_address = $row['esl_bcc_email_address'];
	$esl_subject = $row['esl_subject'];
	$esl_content = $row['esl_content'];
	$esl_plain_text = $row['esl_plain_text'];
	$esl_pdf_enable = $row['esl_pdf_enable'];
	$esl_pdf_content = $row['esl_pdf_content'];

	$esr_enable = $row['esr_enable'];
	$esr_email_address = $row['esr_email_address'];
	$esr_from_name = $row['esr_from_name'];
	$esr_from_email_address = $row['esr_from_email_address'];
	$esr_bcc_email_address = $row['esr_bcc_email_address'];
	$esr_replyto_email_address = $row['esr_replyto_email_address'];
	$esr_subject = $row['esr_subject'];
	$esr_content = $row['esr_content'];
	$esr_plain_text = $row['esr_plain_text'];
	$esr_pdf_enable = $row['esr_pdf_enable'];
	$esr_pdf_content = $row['esr_pdf_content'];

	$webhook_enable = (int) $row['webhook_enable'];

	$payment_enable_merchant = (int) $row['payment_enable_merchant'];
	$payment_onsubmission = (int) $row['payment_onsubmission '];
	$payment_merchant_type = $row['payment_merchant_type'];
	$payment_paypal_email = $row['payment_paypal_email'];
	$payment_paypal_language = $row['payment_paypal_language'];

	$payment_currency = $row['payment_currency'];
	$payment_show_total = (int) $row['payment_show_total'];
	$payment_total_location = $row['payment_total_location'];
	$payment_enable_recurring = (int) $row['payment_enable_recurring'];
	$payment_recurring_cycle = (int) $row['payment_recurring_cycle'];
	$payment_recurring_unit = $row['payment_recurring_unit'];

	$payment_price_type = $row['payment_price_type'];
	$payment_price_amount = (float) $row['payment_price_amount'];
	$payment_price_name = $row['payment_price_name'];

	$payment_enable_tax = (int) $row['payment_enable_tax'];
	$payment_tax_rate = (float) $row['payment_tax_rate'];

	$payment_enable_discount = (int) $row['payment_enable_discount'];
	$payment_discount_type = $row['payment_discount_type'];
	$payment_discount_amount = (float) $row['payment_discount_amount'];
	$payment_discount_element_id = (int) $row['payment_discount_element_id'];

	$logic_email_enable = (int) $row['logic_email_enable'];
	$logic_webhook_enable = (int) $row['logic_webhook_enable'];

	//if the user is saving a form to resume later, we need to discard all validation errors
	//if(!empty($input['generate_resume_url']) && !empty($row['form_resume_enable']) && ($form_page_total > 1)){
	if ($input['save_as_draft'] && !empty($row['form_resume_enable']) && ($form_page_total > 1)) {
		error_log('---------generate_resume_url-------' . $input['generate_resume_url'] . '----------form_resume_enable------' . $row['form_resume_enable']);
		$is_saving_form_resume = true;
		$error_elements = array();
	} else {
		error_log('-----------EMPTY-----form_resume_enable---form_resume_enable----');
		$is_saving_form_resume = false;
	}

	$process_result['form_redirect'] = $form_redirect;
	$process_result['old_values'] = $form_data;
	$process_result['error_elements'] = $error_elements;

	//if this is edit_entry page, unique ip address validation should be bypassed
	$check_unique_ip = false;

	if (!empty($edit_id) && ($_SESSION['mf_logged_in'] === true)) {
		$check_unique_ip = false;
	} else if (!empty($form_unique_ip)) {
		$check_unique_ip = true;
	}

	//check for ip address
	if ($check_unique_ip === true) {
		//if ip address checking enabled, compare user ip address with value in db
		if ($form_unique_ip_period == 'h') { //hourly limit
			$form_unique_ip_period_condition = 'AND (date(date_created) = curdate() AND hour(date_created) = hour(curtime()))';
		} else if ($form_unique_ip_period == 'd') { //daily limit
			$form_unique_ip_period_condition = 'AND (date(date_created) = curdate())';
		} else if ($form_unique_ip_period == 'w') { //weekly limit
			$form_unique_ip_period_condition = 'AND (year(date_created) = year(curdate()) AND week(date_created) = week(curdate()))';
		} else if ($form_unique_ip_period == 'm') { //monthly limit
			$form_unique_ip_period_condition = 'AND (year(date_created) = year(curdate()) AND month(date_created) = month(curdate()))';
		} else if ($form_unique_ip_period == 'y') { //yearly limit
			$form_unique_ip_period_condition = 'AND (year(date_created) = year(curdate()))';
		} else if ($form_unique_ip_period == 'l') { //lifetime limit
			$form_unique_ip_period_condition = '';
		}

		$query = "select count(*) total_ip from `" . MF_TABLE_PREFIX . "form_{$form_id}` where ip_address=? and `status`=1 {$form_unique_ip_period_condition}";
		$params = array($user_ip_address);

		$sth = mf_do_query($query, $params, $dbh);
		$row = mf_do_fetch_result($sth);
		$current_ip_entries_count = (int) $row['total_ip'];

		if ($current_ip_entries_count >= $form_unique_ip_maxcount) {
			$process_result['custom_error'] = $mf_lang['form_limited'];
		}

	}

	//if this is edit_entry page, submission limit should be bypassed
	$check_form_limit = false;

	if (!empty($edit_id) && ($_SESSION['mf_logged_in'] === true)) {
		$check_form_limit = false;
	} else if (!empty($form_limit) && !empty($form_limit_enable)) {
		$check_form_limit = true;
	}

	if ($check_form_limit === true) {
		if ($payment_enable_merchant) {
			//if the form has payment enabled, we only count paid records or unpaid records within the last 30 minutes
			$query = "select 
								count(*) total_row 
							from
								(select 
									  A.`id`,
									  A.`status`,
									  A.date_created,  
									  (select payment_status from " . MF_TABLE_PREFIX . "form_payments where record_id=A.`id` and form_id = ?) payment_status
								  from 
								      " . MF_TABLE_PREFIX . "form_{$form_id} A where A.`status` = 1) B
						   where 
							    (B.`payment_status` = 'paid' and B.`status` = 1) OR
							    (B.`payment_status` is null and B.status = 1 and B.date_created > now()-interval 30 minute)";
			$params = array($form_id);
		} else {
			$query = "select count(*) total_row from " . MF_TABLE_PREFIX . "form_{$form_id} where `status`=1";
			$params = array();
		}

		$sth = mf_do_query($query, $params, $dbh);
		$row = mf_do_fetch_result($sth);

		$total_entries = $row['total_row'];

		if ($total_entries >= $form_limit) {
			$process_result['custom_error'] = $mf_lang['form_limited'];
		}
	}

	if (!empty($_SESSION['edit_entry']['form_id']) && $_SESSION['edit_entry']['form_id'] === $form_id) {
		//when editing an entry, the captcha shouldn't be checked
		$is_bypass_captcha = true;
	} else if (!empty($_SESSION['captcha_passed'][$form_id]) && $_SESSION['captcha_passed'][$form_id] === true) {
		//if the user already validated the captcha once for that session (e.g. on multi-page form), no need to check it again
		$is_bypass_captcha = true;
	} else {
		$is_bypass_captcha = false;
	}

	//check for captcha if enabled and there is no errors from previous fields
	//on multipage form, captcha should be validated on the last page only
	if (!empty($form_captcha) && empty($error_elements) && ($is_bypass_captcha !== true)) {

		if ($form_page_total == 1 || ($form_page_total == $page_number)) {

			if ($form_captcha_type == 'i') {//if simple image captcha is being used

				if (!empty($_POST['captcha_response_field'])) {

					$captcha_response_field = trim($_POST['captcha_response_field']);
					if (PhpCaptcha::Validate($captcha_response_field) !== true) {
						$error_elements['element_captcha'] = 'incorrect-captcha-sol';
						$process_result['error_elements'] = $error_elements;
					} else {
						//captcha succesfully validated
						//set a session variable, so that the user won't need to fill it again, if this is a multi-page form
						$_SESSION['captcha_passed'][$form_id] = true;
					}
				} else { //user not entered the words at all

					$error_elements['element_captcha'] = 'el-required';
					$process_result['error_elements'] = $error_elements;
				}

			} else if ($form_captcha_type == 't') {//if simple text captcha is being used

				if (!empty($_POST['captcha_response_field'])) {

					$captcha_response_field = strtolower(trim($_POST['captcha_response_field']));

					if ($captcha_response_field != strtolower($_SESSION['MF_TEXT_CAPTCHA_ANSWER'])) {
						$error_elements['element_captcha'] = 'incorrect-text-captcha-sol';
						$process_result['error_elements'] = $error_elements;
					} else {
						unset($_SESSION['MF_TEXT_CAPTCHA_ANSWER']);

						//captcha succesfully validated
						//set a session variable, so that the user won't need to fill it again, if this is a multi-page form
						$_SESSION['captcha_passed'][$form_id] = true;
					}
				} else { //user not entered the words at all

					$error_elements['element_captcha'] = 'el-text-required';
					$process_result['error_elements'] = $error_elements;
				}

			} else if ($form_captcha_type == 'r') { //if reCaptcha is being used
				if (!empty($_POST['recaptcha_response_field'])) {
					$recaptcha_response = recaptcha_check_answer(
						RECAPTCHA_PRIVATE_KEY,
						$user_ip_address,
						$_POST["recaptcha_challenge_field"],
						$_POST["recaptcha_response_field"]
					);

					if ($recaptcha_response !== false) { //if false, then we can't connect to captcha server, bypass captcha checking            	
						if ($recaptcha_response->is_valid === false) {
							$error_elements['element_captcha'] = $recaptcha_response->error;
							$process_result['error_elements'] = $error_elements;
						} else {
							//captcha succesfully validated
							//set a session variable, so that the user won't need to fill it again, if this is a multi-page form
							$_SESSION['captcha_passed'][$form_id] = true;
						}
					}
				} else { //user not entered the words at all
					$error_elements['element_captcha'] = 'el-required';
					$process_result['error_elements'] = $error_elements;
				}
			} else if ($form_captcha_type == 'n') { //if reCaptcha V2 is being used
				if (!empty($_POST['g-recaptcha-response'])) {
					$reCaptcha2 = new ReCaptcha($mf_settings['recaptcha_secret_key']);
					$recaptcha_response = $reCaptcha2->verifyResponse(
						$user_ip_address,
						$_POST["g-recaptcha-response"]
					);

					//if the response's success is not null, then we weren't able to connect to captcha server, bypass captcha checking 
					if ($recaptcha_response != null) {
						if ($recaptcha_response->success === false) {
							$error_elements['element_captcha'] = $recaptcha_response->errorCodes;
							$process_result['error_elements'] = $error_elements;
						} else {
							//captcha succesfully validated
							//set a session variable, so that the user won't need to fill it again, if this is a multi-page form
							$_SESSION['captcha_passed'][$form_id] = true;
						}
					}

				} else { //user not entered the words at all
					$error_elements['element_captcha'] = 'el-required';
					$process_result['error_elements'] = $error_elements;
				}
			}
		}

	}

	//if the 'previous' button being clicked, we need to discard any validation errors
	if (!empty($input['submit_secondary']) || !empty($input['submit_secondary_x'])) {
		$process_result['error_elements'] = '';
		$process_result['custom_error'] = '';
		$error_elements = array();
	}

	//insert ip address and date created
	$table_data['ip_address'] = $user_ip_address;
	$table_data['date_created'] = date("Y-m-d H:i:s");


	$is_inserted = false;

	//start insert data into table ----------------------		
	//dynamically create the field list and field values, based on the input given
	if (!empty($table_data) && empty($error_elements) && empty($process_result['custom_error'])) {
		error_log('----------------TABLE DATA----------');
		error_log(var_export($table_data, true));
		$has_value = false;

		$field_list = '';
		$field_values = '';

		foreach ($table_data as $key => $value) {
			error_log('--------KEY------' . $key . '--------Value-----' . $value);
			if ($value == '') { //don't insert blank entry
				continue;
			}

			$field_list .= "`{$key}`,";
			$field_values .= ":{$key},";
			$params_table_data[':' . $key] = $value;

			if (!empty($value)) {
				$has_value = true;
			}
		}

		//add session_id to query if 'form review' enabled or this is multipage forms 
		if (!empty($form_review) || ($form_page_total > 1)) {
			//save previously uploaded file list, so users don't need to reupload files 
			//get all file uploads elements first

			$session_id = session_id();
			$file_uploads_array = array();

			$query = "SELECT 
									element_id 
								FROM 
									" . MF_TABLE_PREFIX . "form_elements 
							   WHERE 
							   		form_id=? AND 
							   		element_type='file' AND 
							   		element_is_private=0 AND
							   		element_status=1";
			$params = array($form_id);

			$sth = mf_do_query($query, $params, $dbh);
			while ($row = mf_do_fetch_result($sth)) {
				$file_uploads_array[] = 'element_' . $row['element_id'];
			}

			$file_uploads_column = implode('`,`', $file_uploads_array);
			$file_uploads_column = '`' . $file_uploads_column . '`';

			if (!empty($file_uploads_array)) {

				if (!empty($_SESSION['review_id'])) { //if this is single page form and has review enabled
					$query = "SELECT {$file_uploads_column} FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where id=?";
					$params = array($_SESSION['review_id']);
				} elseif ($form_page_total > 1) { //if this is multi page form
					$query = "SELECT {$file_uploads_column} FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where session_id=?";
					$params = array($session_id);
				}


				$sth = mf_do_query($query, $params, $dbh);
				$row = mf_do_fetch_result($sth);
				foreach ($file_uploads_array as $element_name) {
					if (!empty($row[$element_name])) {
						$uploaded_file_lookup[$element_name] = $row[$element_name];
					}
				}
			}



			//add session_id to query if 'form review' enabled 

			$field_list .= "`session_id`,";
			$field_values .= ":session_id,";
			$params_table_data[':session_id'] = $session_id;
		}


		if ($has_value) { //if blank form submitted, dont insert anything
			error_log('----HAS VALUE-----TRUE------');
			error_log('-------FIELD LIST------');
			error_log('------FIELD LIST---' . $field_list);
			error_log('------FIELD VALUE---' . $field_values);
			//start insert query ----------------------------------------	
			$field_list = substr($field_list, 0, -1);
			$field_values = substr($field_values, 0, -1);

			if (!empty($edit_id) && ($_SESSION['mf_logged_in'] === true)) {
				error_log('-----------EDIT ID not empty---------');
				//if this is edit_entry page submission, update the table
				//save revision first
				$application_manager = new ApplicationManager();
				//save previous and return new id
				error_log('-------form_id-------' . $form_id . '-----edit_id------' . $edit_id);
				$new_edit_id = $application_manager->revision_save($form_id, $edit_id);
				error_log('-----------new_edit_id--------' . $new_edit_id);
				$update_values = '';
				$params_update = array();
				error_log('--------Table data---------');
				error_log(print_r($table_data, true));

				unset($table_data['date_created']);
				$table_data['date_updated'] = date("Y-m-d H:i:s");

				foreach ($table_data as $key => $value) {
					$update_values .= "`$key`=:$key,";
					$params_update[':' . $key] = $value;
				}
				$params_update[':id'] = $new_edit_id;

				$update_values = substr($update_values, 0, -1);

				$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}` set 
												$update_values
										  where 
									  	  		`id`=:id;";
				mf_do_query($query, $params_update, $dbh);

				$record_insert_id = $new_edit_id;
				error_log('----------record_insert_id------' . $record_insert_id);
				$process_result['new_edit_id'] = $record_insert_id;
			} else {
				//insert to temporary table, if form review is enabled or this is multipage form
				if (!empty($form_review) || ($form_page_total > 1)) {
					if ($form_page_total > 1) {
						//if this is the first page and the first time being submitted, do insert table
						//otherwise, do update table
						error_log('--------form_page_total-----' . $form_page_total);
						$do_review_insert = false;

						if ($input['page_number'] == 1) {
							$session_id = session_id();
							$query = "SELECT count(`id`) as total_row from " . MF_TABLE_PREFIX . "form_{$form_id}_review where session_id=?";
							$params = array($session_id);

							$sth = mf_do_query($query, $params, $dbh);
							$row = mf_do_fetch_result($sth);
							if ($row['total_row'] == 0) {
								$do_review_insert = true;
							}
						}
						error_log('---------do_review_insert-----' . $do_review_insert);

						//if this is the first page, do insert
						if ($do_review_insert) {
							$query = "INSERT INTO `" . MF_TABLE_PREFIX . "form_{$form_id}_review` ($field_list) VALUES ($field_values);";
							mf_do_query($query, $params_table_data, $dbh);

							$record_insert_id = (int) $dbh->lastInsertId();
						} else {
							//otherwise, do update
							//dynamically create the sql update string, based on the input given
							$update_values = '';
							$params_update = array();
							foreach ($table_data as $key => $value) {
								$update_values .= "`$key`=:$key,";
								$params_update[':' . $key] = $value;
							}

							$update_values = substr($update_values, 0, -1);

							$params_update[':session_id'] = $session_id;

							$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}_review` set 
															$update_values
													  where 
												  	  		session_id=:session_id;";

							mf_do_query($query, $params_update, $dbh);

							$query = "SELECT `id` from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where session_id=?";
							$params = array($session_id);

							$sth = mf_do_query($query, $params, $dbh);
							$row = mf_do_fetch_result($sth);

							$record_insert_id = $row['id'];
							error_log('--------------record_insert_id------' . $record_insert_id);
							//if this is the last page of the form, check if form review enabled or not
							//if enabled, simply get the record_insert_id and send it as review_id
							//otherwise, commit form review

							if ($input['page_number'] == $form_page_total && (!empty($input['submit_primary']) || !empty($input['submit_primary_x'])) && !$is_saving_form_resume) {
								error_log('-------page_number---' . $input['page_number'] . '------form_page_total---' . $form_page_total);
								if (!empty($form_review)) {
									error_log('-------form_review-----' . $form_review);
									//pass the current page number, so the user could go back from the preview page
									$process_result['origin_page_number'] = $input['page_number'];
								} else {
									error_log('-------form_review--empty---' . $form_review);
									$query = "SELECT `id` from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where session_id=?";
									$params = array($session_id);

									$sth = mf_do_query($query, $params, $dbh);
									$row = mf_do_fetch_result($sth);

									$commit_options = array();
									$commit_options['send_notification'] = false;
									$commit_options['send_logic_notification'] = false;

									$commit_result = mf_commit_form_review($dbh, $form_id, $row['id'], $commit_options);

									$record_insert_id = $commit_result['record_insert_id'];
									error_log('---------record_insert_id-------' . $record_insert_id);
									$is_committed = true;

									$process_result['entry_id'] = $record_insert_id;
									$_SESSION['mf_form_completed'][$form_id] = true;

								}
							}

						}
					} else {
						error_log('-----------form_page_total--<1---' . $form_page_total);
						$query = "SELECT `id` from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where session_id=?";
						$params = array($session_id);

						$sth = mf_do_query($query, $params, $dbh);
						$row = mf_do_fetch_result($sth);

						$record_insert_id = $row['id'];

						if (empty($record_insert_id)) {
							error_log('----------REVIEW EMPTY-----');
							$query = "INSERT INTO `" . MF_TABLE_PREFIX . "form_{$form_id}_review` ($field_list) VALUES ($field_values);";
							mf_do_query($query, $params_table_data, $dbh);

							$record_insert_id = (int) $dbh->lastInsertId();
						} else {
							error_log('----------REVIEW NOT EMPTY-----');
							$update_values = '';
							$params_update = array();

							foreach ($table_data as $key => $value) {
								$update_values .= "`$key`=:$key,";
								$params_update[':' . $key] = $value;
							}
							$params_update[':id'] = $record_insert_id;

							$update_values = substr($update_values, 0, -1);

							$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}_review` set 
															$update_values
													  where 
												  	  		`id`=:id;";

							mf_do_query($query, $params_update, $dbh);

						}
					}
				} else {
					error_log('------- empty ---form_review----form_page_total------');
					$query = "INSERT INTO `" . MF_TABLE_PREFIX . "form_{$form_id}` ($field_list) VALUES ($field_values);";
					mf_do_query($query, $params_table_data, $dbh);

					$record_insert_id = (int) $dbh->lastInsertId();
				}
			}
			//end insert query ------------------------------------------

			$is_inserted = true;
		}
	}
	//end insert data into table -------------------------		

	//upload the files
	$write_to_permanent_file = false;
	$write_to_temporary_file = false;

	if ($is_inserted) {
		if (!empty($edit_id) && $_SESSION['mf_logged_in'] === true) {
			//if this ie edit_entry page, always write to permanent file
			$write_to_permanent_file = true;

		} else {

			if ($form_page_total <= 1) { //if this is single page form
				if (empty($form_review)) { //if review disabled, upload the files into permanent filename
					$write_to_permanent_file = true;
				} else { //if this single form has review enabled
					$write_to_temporary_file = true;
				}

			} else {//if this is multipage form
				if ($input['page_number'] == $form_page_total && (!empty($input['submit_primary']) || !empty($input['submit_primary_x'])) && $is_committed) {
					$write_to_permanent_file = true;
				} else {
					$write_to_temporary_file = true;
				}
			}
		}
	}
	if ($write_to_permanent_file === true) {
		error_log('-----------permanent----------');
		//START writing into permanent file ------------------------
		//within one form, it is possible to use a mix of standard file upload field and the advanced/ajax uploader
		//we need to be able processing both at the same time

		//if files were uploaded using standard file upload fields
		if (!empty($uploaded_files)) {
			error_log('--------------uploaded_files------------');
			error_log(print_r($uploaded_files, true));
			foreach ($uploaded_files as $element_name) {

				$file_token = md5(uniqid(rand(), true)); //add random token to uploaded filename, to increase security

				//move file and check for invalid file
				$destination_file = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$element_name}_{$file_token}-{$record_insert_id}-{$_FILES[$element_name]['name']}";
				$destination_file = mf_sanitize($destination_file);
				error_log('---------destination_file-------' . $destination_file);
				if (move_uploaded_file($_FILES[$element_name]['tmp_name'], $destination_file)) {
					$query = "update " . MF_TABLE_PREFIX . "form_{$form_id} set {$element_name}=? where id=?";

					$file_element = "{$element_name}_{$file_token}-{$record_insert_id}-{$_FILES[$element_name]['name']}";
					$file_element = mf_sanitize($file_element);
					$params = array($file_element, $record_insert_id);

					mf_do_query($query, $params, $dbh);

					if (MF_STORE_FILES_AS_BLOB === true) {
						mf_ap_form_files_insert($dbh, $form_id, $destination_file);
					}
				}
			}
		}

		//if files were uploaded using advance uploader
		if (!empty($uploaded_files_advance)) {
			error_log('----------UPLOAD ADVANCE-------');
			if (!empty($edit_id) && $_SESSION['mf_logged_in'] === true) {
				//if this is edit_entry, we need to get existing file records and merge the data with the new uploaded files
				$uploaded_element_names = array();
				$uploaded_element_ids = array_keys($uploaded_files_advance);
				foreach ($uploaded_element_ids as $element_id) {
					$uploaded_element_names[] = 'element_' . $element_id;
				}
				$uploaded_element_names_joined = implode(',', $uploaded_element_names);

				$query = "SELECT {$uploaded_element_names_joined} from `" . MF_TABLE_PREFIX . "form_{$form_id}` where `id`=?";
				$params = array($edit_id);

				$sth = mf_do_query($query, $params, $dbh);
				$row = mf_do_fetch_result($sth);

				$existing_files_data = array();
				$multi_upload_info = array();
				foreach ($uploaded_element_names as $element_name) {
					$existing_files_data[$element_name] = trim($row[$element_name]);

					$element_name_exploded = explode('_', $element_name);
					$multi_upload_info[$element_name] = $element_info[$element_name_exploded[1]]['file_enable_multi_upload'];
				}
			}

			//loop through each list
			foreach ($uploaded_files_advance as $element_id => $values) {
				$current_listfile_name = $values['listfile_name'];
				$current_listfile_content = $values['listfile_content'];

				$file_list_array = array();
				foreach ($current_listfile_content as $tmp_filename_path) {
					$tmp_filename_only = basename($tmp_filename_path);
					$filename_value = substr($tmp_filename_only, strpos($tmp_filename_only, '-') + 1);
					$filename_value = str_replace('|', '', str_replace('.tmp', '', $filename_value));

					$new_file_token = md5(uniqid(rand(), true)); //add random token to uploaded filename, to increase security
					$new_filename = "element_{$element_id}_{$new_file_token}-{$record_insert_id}-{$filename_value}";
					$destination_filename = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/" . $new_filename;
					error_log('--------------new_filename---------' . $new_filename);
					error_log('--------------destination_filename---------' . $destination_filename);
					//remove tmp name and change it into permanent name
					//store all the permanent name into a variable
					if (file_exists($tmp_filename_path)) {
						rename($tmp_filename_path, $destination_filename);

						if (MF_STORE_FILES_AS_BLOB === true) {
							$file_options['deleted_file'] = $tmp_filename_path;
							mf_ap_form_files_insert($dbh, $form_id, $destination_filename, $file_options);
						}
					}

					$file_list_array[] = $new_filename;
				}

				//delete the listfile for the current element_id
				unlink($current_listfile_name);

				//update the table with the file name list
				if (!empty($edit_id) && $_SESSION['mf_logged_in'] === true) {
					//if this is edit_entry, we need to get existing file records and merge the data with the new uploaded files
					//which depends on the multi upload setting for each file upload field
					//if multi upload enabled, we need to merge the data. otherwise, replace the old data
					if (!empty($multi_upload_info['element_' . $element_id])) { //if multi upload enabled, merge the data
						$new_files_array = $file_list_array;

						if (!empty($existing_files_data['element_' . $element_id])) {
							$old_files_array = explode('|', $existing_files_data['element_' . $element_id]);

							$merged_files_array = array_merge($new_files_array, $old_files_array);
							$merged_files_array = array_unique($merged_files_array);
						} else {
							$merged_files_array = $new_files_array;
						}

						$file_list_joined[$element_id] = implode('|', $merged_files_array);

					} else { //replace the old data with the new file
						$file_list_joined[$element_id] = implode('|', $file_list_array);
					}
				} else {
					$file_list_joined[$element_id] = implode('|', $file_list_array);
				}
			}

			//update the table with the file name list
			$update_values = '';
			$params_update = array();

			foreach ($file_list_joined as $element_id => $file_joined) {
				$file_joined = mf_sanitize($file_joined);
				$update_values .= "element_{$element_id}=:element_{$element_id},";
				$params_update[':element_' . $element_id] = $file_joined;
			}
			$update_values = rtrim($update_values, ',');

			$params_update[':id'] = $record_insert_id;

			$query = "update " . MF_TABLE_PREFIX . "form_{$form_id} set {$update_values} where id=:id";

			mf_do_query($query, $params_update, $dbh);

		}
		//END writing into permanent file ------------------------
	} else if ($write_to_temporary_file === true) {
		error_log('-----------temporary----------');
		//START writing into temporary file ------------------------
		//if files were uploaded using standard file upload fields
		if (!empty($uploaded_files)) {
			error_log('-----------------uploaded_files------------------');
			error_log(print_r($uploaded_files, true));

			$record_review_id = session_id();
			foreach ($uploaded_files as $element_name) {
				$file_token = md5(uniqid(rand(), true)); //add random token to uploaded filename, to increase security

				//move file and check for invalid file
				$destination_file = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$element_name}_{$file_token}-{$record_insert_id}-{$_FILES[$element_name]['name']}.tmp";
				$destination_file = mf_sanitize($destination_file);
				error_log('------------destination_file---------' . $destination_file);
				if (move_uploaded_file($_FILES[$element_name]['tmp_name'], $destination_file)) {
					error_log('-----UPLOAD SUCCESSFUL :)-----------');
					$query = "update " . MF_TABLE_PREFIX . "form_{$form_id}_review set {$element_name}=? where session_id=?";
					$file_element = "{$element_name}_{$file_token}-{$record_insert_id}-{$_FILES[$element_name]['name']}";
					$file_element = mf_sanitize($file_element);

					$params = array($file_element, $record_review_id);
					mf_do_query($query, $params, $dbh);
				}

				if (!empty($uploaded_file_lookup[$element_name])) {
					unset($uploaded_file_lookup[$element_name]);
				}
			}
		}

		//if files were uploaded using advance uploader
		if (!empty($uploaded_files_advance)) {
			error_log('-----------------uploaded_files_advance------------------');
			error_log(print_r($uploaded_files_advance, true));
			//loop through each list
			foreach ($uploaded_files_advance as $element_id => $values) {
				$current_listfile_name = $values['listfile_name'];
				$current_listfile_content = $values['listfile_content'];

				$file_list_array = array();
				foreach ($current_listfile_content as $tmp_filename_path) {
					$tmp_filename_only = basename($tmp_filename_path);
					$filename_value = substr($tmp_filename_only, strpos($tmp_filename_only, '-') + 1);
					$filename_value = str_replace('|', '', str_replace('.tmp', '', $filename_value));

					$new_file_token = md5(uniqid(rand(), true)); //add random token to uploaded filename, to increase security
					$new_filename = "element_{$element_id}_{$new_file_token}-{$record_insert_id}-{$filename_value}";
					$destination_filename = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/" . $new_filename . ".tmp";
					error_log('--------------new_filename------' . $new_filename);
					error_log('--------------destination_filename------' . $destination_filename);

					//assign new temporary name, using new token and record id
					//store all the temporary name into a variable
					if (file_exists($tmp_filename_path)) {
						rename($tmp_filename_path, $destination_filename);
					}

					$file_list_array[] = $new_filename;
				}

				//delete the listfile for the current element_id
				unlink($current_listfile_name);

				//update the table with the file name list
				$file_list_joined[$element_id] = implode('|', $file_list_array);
			}

			//update the table with the file name list
			$update_values = '';
			$params_update = array();

			foreach ($file_list_joined as $element_id => $file_joined) {
				$file_joined = mf_sanitize($file_joined);
				$update_values .= "element_{$element_id}=:element_{$element_id},";
				$params_update[':element_' . $element_id] = $file_joined;
			}
			$update_values = rtrim($update_values, ',');

			$params_update[':id'] = $record_insert_id;

			$query = "update " . MF_TABLE_PREFIX . "form_{$form_id}_review set {$update_values} where id=:id";
			mf_do_query($query, $params_update, $dbh);

		}



		//if the user goes to review page and then go back to the form page or navigate within multipage form, $uploaded_file_lookup will contain the list of the previously submitted files
		//if the multi upload option enabled, make sure to update the previouly uploaded file to the current record during form submit
		//when updating the table, make sure to MERGE existing data within the table and the new one
		//otherwise, if the multi upload is not enabled, we need to delete previous files and don't update the table with the old files data

		if (!empty($uploaded_file_lookup)) {
			error_log('-----------------uploaded_file_lookup-----------');
			error_log(print_r($uploaded_file_lookup, true));

			//get the existing data within the table
			$uploaded_element_names = array_keys($uploaded_file_lookup);
			$uploaded_element_names_joined = implode(',', $uploaded_element_names);

			$query = "SELECT {$uploaded_element_names_joined} from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where `id`=?";
			$params = array($record_insert_id);

			$sth = mf_do_query($query, $params, $dbh);
			$row = mf_do_fetch_result($sth);

			$existing_files_data = array();
			$multi_upload_info = array();
			foreach ($uploaded_element_names as $element_name) {
				$existing_files_data[$element_name] = $row[$element_name];

				$element_name_exploded = explode('_', $element_name);
				$multi_upload_info[$element_name] = $element_info[$element_name_exploded[1]]['file_enable_multi_upload'];
			}


			//merge the data
			foreach ($uploaded_file_lookup as $element_name => $filename) {
				$new_files_array = array();
				$old_files_array = array();

				$new_files_array = explode('|', $filename);
				$old_files_array = explode('|', $existing_files_data[$element_name]);

				if (!empty($multi_upload_info[$element_name])) { //if multi upload enabled, merge the data
					$merged_files_array = array_merge($new_files_array, $old_files_array);
					$merged_files_array = array_unique($merged_files_array);
				} else {//otherwise, just use the new one
					$merged_files_array = $old_files_array;

					//delete the old files as well, if the files aren't the same with the new one
					if ($filename != $existing_files_data[$element_name]) {
						foreach ($new_files_array as $filename) {
							$filename = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$filename}.tmp";
							if (file_exists($filename)) {
								unlink($filename);
							}
						}
					}
				}
				$merged_files_joined = implode('|', $merged_files_array);
				$merged_files_data[$element_name] = $merged_files_joined;
			}


			$update_clause = '';
			foreach ($merged_files_data as $element_name => $filename) {
				$filename = addslashes(mf_sanitize($filename));
				$update_clause .= "`{$element_name}`='{$filename}',";
			}
			$update_clause = rtrim($update_clause, ",");

			$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}_review` SET {$update_clause} WHERE id=?";
			$params = array($record_insert_id);

			mf_do_query($query, $params, $dbh);

		}
		//END writing into temporary file ------------------------
	}


	//process any rules to skip pages, if this functionality is being enabled
	$process_result['logic_page_enable'] = false;
	error_log('----------is_edit_page-------' . $is_edit_page);
	if ($is_inserted === true && $is_edit_page === false && $is_saving_form_resume === false && !empty($logic_page_enable)) {
		error_log('-----is_inserted-----true--is_edit_page--false---' . $is_inserted);
		//if the back button being clicked, don't evaluate the logic conditions
		//simply get the previous page from the array
		if (!empty($input['submit_secondary']) || !empty($input['submit_secondary_x'])) {
			error_log('-------submit_secondary----' . $input['submit_secondary'] . '----------' . $input['submit_secondary_x']);
			$pages_history = array();
			$pages_history = $_SESSION['mf_pages_history'][$form_id];
			error_log(print_r($pages_history, true));
			$page_number_array_index = array_search($page_number, $pages_history);
			$previous_page_number = $pages_history[$page_number_array_index - 1];

			$process_result['logic_page_enable'] = true;
			$process_result['target_page_id'] = $previous_page_number;
		} else {
			//submit/continue button being clicked
			//get all the destination pages from ap_page_logic
			//only get pages with larger page number. the skip page logic can't move backward
			error_log('---------------submit/continue----clicked------');
			$query = "SELECT 
								page_id,
								rule_all_any 
							FROM 
								" . MF_TABLE_PREFIX . "page_logic 
						   WHERE 
								form_id = ? and (page_id > {$page_number} or page_id in('payment','review','success'))  
						ORDER BY 
								page_id asc";
			$params = array($form_id);
			$sth = mf_do_query($query, $params, $dbh);

			$page_logic_array = array();
			$i = 0;
			while ($row = mf_do_fetch_result($sth)) {
				$page_logic_array[$i]['page_id'] = $row['page_id'];
				$page_logic_array[$i]['rule_all_any'] = $row['rule_all_any'];
				$i++;
			}

			//evaluate the condition for each destination page
			//once a condition results true, break the loop and send the result
			if (!empty($page_logic_array)) {

				foreach ($page_logic_array as $value) {
					$target_page_id = $value['page_id'];
					$rule_all_any = $value['rule_all_any'];

					$current_page_conditions_status = array();

					$query = "SELECT 
										A.element_name,
										A.rule_condition,
										A.rule_keyword,
										(select 
												B.element_page_number 
										 from 
												" . MF_TABLE_PREFIX . "form_elements B 
									   	where 
									   			B.form_id=A.form_id and 
									   			B.element_id = (substring(A.element_name,
									   												locate('_',A.element_name)+1, 
									   												(if((locate('_',A.element_name, locate('_',A.element_name)+1)) = 0,100,(locate('_',A.element_name, locate('_',A.element_name)+1)))) - (locate('_',A.element_name)+1))) and 
									   	B.element_status = 1
								  		) as element_page_number 
								    FROM 
										" . MF_TABLE_PREFIX . "page_logic_conditions A
								   WHERE 
								 		A.form_id = ? AND A.target_page_id = ?";
					$params = array($form_id, $target_page_id);

					$sth = mf_do_query($query, $params, $dbh);

					$conditions_exist = false;
					while ($row = mf_do_fetch_result($sth)) {

						$condition_params = array();
						$condition_params['form_id'] = $form_id;
						$condition_params['element_name'] = $row['element_name'];
						$condition_params['rule_condition'] = $row['rule_condition'];
						$condition_params['rule_keyword'] = $row['rule_keyword'];

						//if this is the last page of a form and the form doesn't have review enabled
						//we need to check the condition from ap_form_xx table, not from review table
						if (($form_page_total > 1) && ($form_page_total == $page_number) && empty($form_review)) {
							$condition_params['use_main_table'] = true;
							$condition_params['entry_id'] = $record_insert_id;
						}

						$current_page_conditions_status[] = mf_get_condition_status_from_table($dbh, $condition_params);

						//we only need to evaluate the skip logic rule if one of the logic condition field is within the current page
						//so that the skip logic page won't be triggered by each page submission
						if ($row['element_page_number'] == $page_number) {
							$conditions_exist = true;
						}
					}

					if ($conditions_exist === false) {
						continue;
					}

					if ($rule_all_any == 'all') {
						if (in_array(false, $current_page_conditions_status)) {
							$all_conditions_status = false;
						} else {
							$all_conditions_status = true;
						}
					} else if ($rule_all_any == 'any') {
						if (in_array(true, $current_page_conditions_status)) {
							$all_conditions_status = true;
						} else {
							$all_conditions_status = false;
						}
					}

					if ($all_conditions_status === true) {
						//all conditions for this target page has been met, break the loop and send it to $process_result
						$process_result['logic_page_enable'] = true;
						$process_result['target_page_id'] = $target_page_id;

						//allow access to the next destination page
						error_log('------------target_page_id------' . $target_page_id);
						if (is_numeric($target_page_id)) {
							$_SESSION['mf_form_access'][$form_id][$target_page_id] = true;
						} else if ($target_page_id == 'review') {
							$process_result['review_id'] = $record_insert_id;
							$process_result['origin_page_number'] = $input['page_number'];
						} else if ($target_page_id == 'payment' || $target_page_id == 'success') {
							//if the destination is payment page or success page 
							//we need to commit the data first

							//if the page already committed, don't commit again though
							//this might be happened when a multipage form doesn't have review enabled
							if ($is_committed === false) {
								$commit_options = array();

								if ($delay_notifications) {
									$commit_options['send_notification'] = false;
								}

								$session_id = session_id();
								$query = "SELECT `id` from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where session_id=?";
								$params = array($session_id);

								$sth = mf_do_query($query, $params, $dbh);
								$row = mf_do_fetch_result($sth);

								$commit_result = mf_commit_form_review($dbh, $form_id, $row['id'], $commit_options);
								$process_result['entry_id'] = $commit_result['record_insert_id'];
							}

							if ($target_page_id == 'success') {
								$_SESSION['mf_form_completed'][$form_id] = true;

								$bypass_merchant_redirect_url = true;
							}
						}

						break;
					}


				} //end foreach page_logic_array
			}
		}
	}

	//start sending notification email to admin ------------------------------------------
	if (
		($is_inserted && !empty($esl_enable) && !empty($form_email) && empty($form_review) && ($form_page_total == 1) && empty($edit_id) && ($delay_notifications === false)) ||
		($is_inserted && !empty($esl_enable) && !empty($form_email) && $is_committed && empty($edit_id) && ($delay_notifications === false))
	) {
		//get parameters for the email

		//from name
		if (!empty($esl_from_name)) {
			if (is_numeric($esl_from_name)) {
				$admin_email_param['from_name'] = '{element_' . $esl_from_name . '}';
			} else {
				$admin_email_param['from_name'] = $esl_from_name;
			}
		} else {
			$admin_email_param['from_name'] = 'MachForm';
		}

		//from email address
		if (!empty($esl_from_email_address)) {
			if (is_numeric($esl_from_email_address)) {
				$admin_email_param['from_email'] = '{element_' . $esl_from_email_address . '}';
			} else {
				$admin_email_param['from_email'] = $esl_from_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$admin_email_param['from_email'] = "no-reply@{$domain}";
		}

		//bcc
		if (!empty($esl_bcc_email_address)) {
			$admin_email_param['bcc_emails'] = $esl_bcc_email_address;
		}

		//reply-to email address
		if (!empty($esl_replyto_email_address)) {
			if (is_numeric($esl_replyto_email_address)) {
				$admin_email_param['replyto_email'] = '{element_' . $esl_replyto_email_address . '}';
			} else {
				$admin_email_param['replyto_email'] = $esl_replyto_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$admin_email_param['replyto_email'] = "no-reply@{$domain}";
		}

		//subject
		if (!empty($esl_subject)) {
			$admin_email_param['subject'] = $esl_subject;
		} else {
			$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
		}

		//content
		if (!empty($esl_content)) {
			$admin_email_param['content'] = $esl_content;
		} else {
			$admin_email_param['content'] = '{entry_data}';
		}

		//pdf attachment
		if (!empty($esl_pdf_enable)) {
			$admin_email_param['pdf_enable'] = true;
			$admin_email_param['pdf_content'] = $esl_pdf_content;
		}

		$admin_email_param['as_plain_text'] = $esl_plain_text;
		$admin_email_param['target_is_admin'] = true;
		$admin_email_param['machform_base_path'] = $input['machform_base_path'];
		$admin_email_param['check_hook_file'] = true;
		mf_send_notification($dbh, $form_id, $record_insert_id, $form_email, $admin_email_param);

	}
	//end emailing notifications to admin ----------------------------------------------


	//start sending notification email to user ------------------------------------------
	if (
		($is_inserted && !empty($esr_enable) && !empty($esr_email_address) && empty($form_review) && ($form_page_total == 1) && empty($edit_id) && ($delay_notifications === false)) ||
		($is_inserted && !empty($esr_enable) && !empty($esr_email_address) && $is_committed && empty($edit_id) && ($delay_notifications === false))
	) {
		//get parameters for the email

		//to email
		if (is_numeric($esr_email_address)) {
			$esr_email_address = '{element_' . $esr_email_address . '}';
		}

		//from name
		if (!empty($esr_from_name)) {
			if (is_numeric($esr_from_name)) {
				$user_email_param['from_name'] = '{element_' . $esr_from_name . '}';
			} else {
				$user_email_param['from_name'] = $esr_from_name;
			}
		} else {
			$user_email_param['from_name'] = 'MachForm';
		}

		//from email address
		if (!empty($esr_from_email_address)) {
			if (is_numeric($esr_from_email_address)) {
				$user_email_param['from_email'] = '{element_' . $esr_from_email_address . '}';
			} else {
				$user_email_param['from_email'] = $esr_from_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$user_email_param['from_email'] = "no-reply@{$domain}";
		}

		//bcc
		if (!empty($esr_bcc_email_address)) {
			$user_email_param['bcc_emails'] = $esr_bcc_email_address;
		}

		//reply-to email address
		if (!empty($esr_replyto_email_address)) {
			if (is_numeric($esr_replyto_email_address)) {
				$user_email_param['replyto_email'] = '{element_' . $esr_replyto_email_address . '}';
			} else {
				$user_email_param['replyto_email'] = $esr_replyto_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$user_email_param['replyto_email'] = "no-reply@{$domain}";
		}

		//subject
		if (!empty($esr_subject)) {
			$user_email_param['subject'] = $esr_subject;
		} else {
			$user_email_param['subject'] = '{form_name} - Receipt';
		}

		//content
		if (!empty($esr_content)) {
			$user_email_param['content'] = $esr_content;
		} else {
			$user_email_param['content'] = '{entry_data}';
		}

		//pdf attachment
		if (!empty($esr_pdf_enable)) {
			$user_email_param['pdf_enable'] = true;
			$user_email_param['pdf_content'] = $esr_pdf_content;
		}

		$user_email_param['as_plain_text'] = $esr_plain_text;
		$user_email_param['target_is_admin'] = false;
		$user_email_param['machform_base_path'] = $input['machform_base_path'];

		mf_send_notification($dbh, $form_id, $record_insert_id, $esr_email_address, $user_email_param);
	}
	//end emailing notifications to user ---------------------------------------------	

	//send all notifications triggered by email-logic ---------------------
	//email logic is not affected by 'delay notifications until paid' option
	if (
		($is_inserted && !empty($logic_email_enable) && empty($form_review) && ($form_page_total == 1) && empty($edit_id)) ||
		($is_inserted && !empty($logic_email_enable) && $is_committed && empty($edit_id))
	) {
		$logic_email_param = array();
		$logic_email_param['machform_base_path'] = $input['machform_base_path'];

		mf_send_logic_notifications($dbh, $form_id, $record_insert_id, $logic_email_param);
	}

	//send webhook notification
	if (
		($is_inserted && !empty($webhook_enable) && empty($form_review) && ($form_page_total == 1) && empty($edit_id) && ($delay_notifications === false)) ||
		($is_inserted && !empty($webhook_enable) && $is_committed && empty($edit_id) && ($delay_notifications === false))
	) {
		mf_send_webhook_notification($dbh, $form_id, $record_insert_id, 0);
	}

	//send webhook notifications triggered by logic
	//webhook logic is not affected by 'delay notifications until paid' option
	if (
		($is_inserted && !empty($logic_webhook_enable) && empty($form_review) && ($form_page_total == 1) && empty($edit_id)) ||
		($is_inserted && !empty($logic_webhook_enable) && $is_committed && empty($edit_id))
	) {
		mf_send_logic_webhook_notifications($dbh, $form_id, $record_insert_id);
	}

	//Start OTB Patch - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
	$user_id = $_SESSION['new_userid'] ? $_SESSION['new_userid'] : $_SESSION['symfony/user/sfUser/attributes']['sfGuardSecurityUser']['user_id'];
	$query = "SELECT
						registeras
					FROM sf_guard_user_profile
				   WHERE
						user_id=? limit 1";
	$params = array($user_id);
	$sth = mf_do_query($query, $params, $dbh);
	$userprofile = mf_do_fetch_result($sth);
	$sf_category_id = $userprofile ? $userprofile['registeras'] : False;
	if ($sf_category_id) {
		$query = "SELECT
							*
						FROM sf_guard_user_categories
					   WHERE
							formid=? AND
							id=?";
		$params = array($form_id, $sf_category_id);

		$sth = mf_do_query($query, $params, $dbh);
		$usercategory = mf_do_fetch_result($sth);
		//error_log(print_r($usercategory,true));
	}

	if ($usercategory && $usercategory['member_no_element_id']) {
		$member_no = trim($input['element_' . $usercategory['member_no_element_id']]);
		$memberships = array();
		if (strlen($member_no)) {
			//$memberships=Doctrine_Core::getTable('MembersDatabase')->findByMembersNoAndUserCategoryId($member_no, $usercategory['id']);
			//Get Memebership database
			$query_member_db = "SELECT ";
			$query_member_db .= "id,element_" . $usercategory['member_database_member_no_field'] . " , element_" . $usercategory['member_database_member_email_field'];
			$query_member_db .= " FROM ap_form_" . $usercategory['member_database'];
			$query_member_db .= " WHERE element_" . $usercategory['member_database_member_no_field'] . " = '$member_no'";
			$query_member_db .= isset($usercategory['member_database_member_one_single_use']) ? " AND element_" . $usercategory['member_database_member_one_single_use'] . "_1 = 0" : "";
			//error_log('------>'.$query.'<--------');
			$memberships = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query_member_db);
			//error_log(print_r($memberships,true));
			error_log("member_no is this --- # " . $member_no . " and memberships ---# " . count($memberships));
		}
		if (count($memberships) == 0) {
			$prev_err = $error_elements[$usercategory['member_no_element_id']];
			$error_elements[$usercategory['member_no_element_id']] = $prev_err . ' ' . $usercategory['member_association_name'] . " membership number doesn't exist.";
			$process_result['error_elements'] = $error_elements;
			//error_log(print_r($error_elements,true));
		} else {
			//Check if member number matches member email
			$member_email = array();
			foreach ($memberships as $member) {
				$member_email[] = trim($member["element_" . $usercategory['member_database_member_email_field']]);
			}
			//Check comparison 
			if (!$member_email or count($member_email) == 0) {
				$prev_err = $error_elements[$usercategory['validation_email_element_id']];
				$error_elements[$usercategory['validation_email_element_id']] = $prev_err . " There is no email present for this " . $usercategory['member_association_name'] . " membership in the " . $usercategory['member_association_name'] . " database. Kindly contact " . sfConfig::get('app_organisation_name') . " so that your " . $usercategory['member_association_name'] . " membership can be verified.";
				$process_result['error_elements'] = $error_elements;
			} else {
				if ($usercategory['membership_email_match'] == 1) {
					//Check if any of the result match what the user has input
					$found = false;
					foreach ($member_email as $email) {
						//case insensitive check
						error_log('---Email--' . $email . '----Validate email--' . $input['element_' . $usercategory['validation_email_element_id']]);
						//if(strcasecmp($email,$input['element_'.$usercategory['validation_email_element_id']]) == 0){
						//$found=true;
						//}
						if (strtolower($email) == strtolower(trim($input['element_' . $usercategory['validation_email_element_id']]))) {
							$found = true;
						}
					}
					if (!$found) {
						$prev_err = $error_elements[$usercategory['validation_email_element_id']];
						$error_elements[$usercategory['validation_email_element_id']] = $prev_err . " " . $usercategory['member_association_name'] . " membership email for " . $member_no . " doesn't match the email given here.";
						$process_result['error_elements'] = $error_elements;
					} else {
						if ($usercategory['send_verification_email'] == 1) {
							$membersManager = new MembersManager();
							$membersManager->sendMemberVerificationMail($member_no, $usercategory, $memberships);
						}
					}
				}
			}
		}
	}
	//End OTB Patch - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
	//if there is no error message or elements, send true as status
	if (empty($error_elements) && empty($process_result['custom_error'])) {
		$process_result['status'] = true;
		error_log('------NO ERROR------');
		if ($form_page_total > 1) { //if this is multipage form
			error_log('----------Multipage-----form_page_total------' . $form_page_total);
			$_SESSION['mf_form_loaded'][$form_id][$page_number] = true;

			if ($is_saving_form_resume) {
				//if the user is saving his progress instead of submitting the form
				//copy the record from review table into main form table and set the status to incomplete (status=2)
				//insert any uploaded files into database
				//also generate resume url
				$has_invalid_resume_email = false;
				/*$resume_email 				   = trim($input['element_resume_email']);

										   $rules = array();
										   $target_input = array();

										   $target_input['element_resume_email'] = $resume_email;
										   $rules['element_resume_email']['required'] = true;
										   $rules['element_resume_email']['email'] = true;

										   $validation_result = mf_validate_element($target_input,$rules);
									   
										   if($validation_result !== true){
											   $has_invalid_resume_email = true;
											   $error_elements['element_resume_email'] = $validation_result; 
												   
											   $process_result['status'] = false;
											   $process_result['error_elements'] = $error_elements;
											   $process_result['old_values']['element_resume_email'] = $input['element_resume_email'];
										   }*/

				if (!$has_invalid_resume_email) {
					error_log('------------has_invalid_resume_email-----false-----' . $has_invalid_resume_email);
					//get any file upload fields
					if (MF_STORE_FILES_AS_BLOB === true) {
						$query = "SELECT element_id FROM " . MF_TABLE_PREFIX . "form_elements WHERE form_id = ? and element_status = 1 and element_type = 'file'";
						$params = array($form_id);

						$saved_files_id = array();
						$sth = mf_do_query($query, $params, $dbh);
						while ($row = mf_do_fetch_result($sth)) {
							$saved_files_id[] = $row['element_id'];
						}
					}

					//get all column name except session_id and id
					$query = "SELECT * FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` WHERE session_id=?";
					$params = array($session_id);

					$sth = mf_do_query($query, $params, $dbh);
					$row = mf_do_fetch_result($sth);

					$columns = array();
					foreach ($row as $column_name => $column_data) {
						if ($column_name != 'id' && $column_name != 'session_id') {
							$columns[] = $column_name;
						}
					}

					$columns_joined = implode("`,`", $columns);
					$columns_joined = '`' . $columns_joined . '`';

					//if there is no resume key, generate new one
					if (empty($row['resume_key'])) {
						$form_resume_key = substr(strtolower(md5(uniqid(rand(), true))), 0, 10);
					} else {
						$form_resume_key = $row['resume_key'];
					}

					//copy all uploaded files into database
					if (MF_STORE_FILES_AS_BLOB === true && !empty($saved_files_id)) {
						foreach ($saved_files_id as $element_id) {
							$filename_record = $row['element_' . $element_id];

							if (empty($filename_record)) {
								continue;
							}

							//if the file upload field is using advance uploader, $filename would contain multiple file names, separated by pipe character '|'
							$filename_array = array();
							$filename_array = explode('|', $filename_record);

							foreach ($filename_array as $filename) {
								$filename = $input['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$filename}.tmp";
								mf_ap_form_files_insert($dbh, $form_id, $filename);
							}
						}
					}

					//delete previous entry on ap_form_x table
					//OTB get id first
					$query = "SELECT id from `" . MF_TABLE_PREFIX . "form_{$form_id}` WHERE resume_key=? and status=2";
					$params = array($form_resume_key);
					$sth = mf_do_query($query, $params, $dbh);
					$row = mf_do_fetch_result($sth);
					$form_entry_id = $row['id'];

					$query = "DELETE from `" . MF_TABLE_PREFIX . "form_{$form_id}` WHERE resume_key=? and status=2";
					$params = array($form_resume_key);
					mf_do_query($query, $params, $dbh);

					//OTB DELETE ALSO FORM ENTRY DRAFT
					$query = "DELETE from `form_entry` WHERE form_id=? and entry_id=? and approved=0";
					$params = array($form_id, $form_entry_id);
					mf_do_query($query, $params, $dbh);

					//copy from ap_form_x_review to ap_form_x
					$query = "INSERT INTO `" . MF_TABLE_PREFIX . "form_{$form_id}`($columns_joined) SELECT {$columns_joined} from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` WHERE session_id=?";
					$params = array($session_id);

					mf_do_query($query, $params, $dbh);

					$new_record_id = (int) $dbh->lastInsertId();
					error_log('-------------new_record_id-------' . $new_record_id);
					$process_result['entry_id'] = $new_record_id;
					$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}` set `status`=2,resume_key='{$form_resume_key}' where `id`=?";
					$params = array($new_record_id);

					mf_do_query($query, $params, $dbh);

					//delete from ap_form_x_review table
					$query = "DELETE from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` WHERE session_id=?";
					$params = array($session_id);

					mf_do_query($query, $params, $dbh);

					//calculate the hash of user input and store into session
					//we'll use this to check for accidental double submission
					$_SESSION['mf_entry_hash'][$form_id] = $user_input_hash;

					//pass form resume key
					$process_result['form_resume_key'] = $form_resume_key;

					//pass form resume url
					$form_resume_url = $mf_settings['base_url'] . "view?id={$form_id}&mf_resume={$form_resume_key}";

					$process_result['form_resume_url'] = $form_resume_url;

					if (!empty($resume_email)) {
						//send the resume link to the provided email
						mf_send_resume_link($dbh, $form_id, $new_record_id, $form_resume_url, $resume_email);
					}

					//remove form history from session
					$_SESSION['mf_form_loaded'][$form_id] = array();
					unset($_SESSION['mf_form_loaded'][$form_id]);

					//remove form access session
					$_SESSION['mf_form_access'][$form_id] = array();
					unset($_SESSION['mf_form_access'][$form_id]);

					$_SESSION['mf_form_resume_url'][$form_id] = array();
					unset($_SESSION['mf_form_resume_url'][$form_id]);

					//remove pages history
					$_SESSION['mf_pages_history'][$form_id] = array();
					unset($_SESSION['mf_pages_history'][$form_id]);

					//unset the form resume session, if any
					$_SESSION['mf_form_resume_loaded'][$form_id] = false;
					unset($_SESSION['mf_form_resume_loaded'][$form_id]);
				}

			} else {
				error_log('----------is_saving_form_resume--false---' . $is_saving_form_resume);
				//get the next page number and send it
				//don't send page number if this is already the last page, unless back button being clicked
				if ($input['page_number'] < $form_page_total) {
					error_log('---------page_number---' . $input['page_number'] . '--< form_page_total-----' . $form_page_total);
					if (!empty($input['submit_primary']) || !empty($input['submit_primary_x'])) {
						$process_result['next_page_number'] = $page_number + 1;
					} elseif (!empty($input['submit_secondary']) || !empty($input['submit_secondary_x'])) {
						$process_result['next_page_number'] = $page_number - 1;
					} else {
						$process_result['next_page_number'] = $page_number + 1;
					}
				} else { //if this is the last page
					error_log('--------LAst Page--------');
					if (!empty($input['submit_primary']) || !empty($input['submit_primary_x'])) {
						if (!empty($form_review)) {
							$process_result['review_id'] = $record_insert_id;
							$process_result['entry_id'] = $record_insert_id;
						}
					} elseif (!empty($input['submit_secondary']) || !empty($input['submit_secondary_x'])) {
						$process_result['next_page_number'] = $page_number - 1;
					} else {
						if (!empty($form_review)) {
							$process_result['review_id'] = $record_insert_id;
							$process_result['entry_id'] = $record_insert_id;
						}
					}
				}
			}
		} else {//if this is single page form
			error_log('----------Single page----------');
			//if 'form review' enabled, send review_id
			if (!empty($form_review)) {
				$process_result['review_id'] = $record_insert_id;
			} else {
				//form submitted successfully, set the session to display success page
				$_SESSION['mf_form_completed'][$form_id] = true;
				$process_result['entry_id'] = $record_insert_id;

				//calculate the hash of user input and store into session
				//we'll use this to check for accidental double submission
				$_SESSION['mf_entry_hash'][$form_id] = $user_input_hash;
			}
		}

		//if this is edit entry page and payment is enabled
		//make sure to re-calculate the payment total amount, in case of changes
		//this is only applicable when the payment status is not 'paid'
		if ($is_edit_page === true && $payment_enable_merchant) {
			$is_discount_applicable = false;

			//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
			if ($payment_enable_discount) {
				$query = "select element_{$payment_discount_element_id} coupon_element from " . MF_TABLE_PREFIX . "form_{$form_id} where `id` = ? and `status` = 1";
				$params = array($edit_id);

				$sth = mf_do_query($query, $params, $dbh);
				$row = mf_do_fetch_result($sth);

				if (!empty($row['coupon_element'])) {
					$is_discount_applicable = true;
				}
			}

			if ($payment_price_type == 'variable') {
				$payment_amount = (double) mf_get_payment_total($dbh, $form_id, $edit_id, 0, 'live');
			} else if ($payment_price_type == 'fixed') {
				$payment_amount = $payment_price_amount;
			}

			//calculate discount if applicable
			if ($is_discount_applicable) {
				$payment_calculated_discount = 0;

				if ($payment_discount_type == 'percent_off') {
					//the discount is percentage
					$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_amount;
					$payment_calculated_discount = round($payment_calculated_discount, 2); //round to 2 digits decimal
				} else {
					//the discount is fixed amount
					$payment_calculated_discount = round($payment_discount_amount, 2); //round to 2 digits decimal
				}

				$payment_amount -= $payment_calculated_discount;
			}

			//calculate tax if enabled
			if (!empty($payment_enable_tax) && !empty($payment_tax_rate)) {
				//$payment_tax_amount = ($payment_tax_rate / 100) * $payment_amount;
				//$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
				$payment_tax_amount = $payment_tax_rate;

				$payment_amount += $payment_tax_amount;
			}

			//do update to ap_form_payments table
			$query = "update " . MF_TABLE_PREFIX . "form_payments set payment_amount = ? where form_id = ? and record_id = ? and payment_status <> 'paid'";
			$params = array($payment_amount, $form_id, $edit_id);
			mf_do_query($query, $params, $dbh);
		}
	} else {
		error_log('------------FORM ERRORs--------------');
		$process_result['status'] = false;
	}


	//parse redirect URL from the form properties for any template variables
	if (
		($is_inserted && !empty($process_result['form_redirect']) && empty($form_review) && ($form_page_total == 1) && empty($edit_id)) ||
		($is_inserted && !empty($process_result['form_redirect']) && $is_committed && empty($edit_id))
	) {
		$process_result['form_redirect'] = mf_parse_template_variables($dbh, $form_id, $process_result['entry_id'], $process_result['form_redirect']);
		$process_result['form_redirect'] = str_replace(array('&amp;', '&#039;', '&quot;'), array('&', '%27', '%22'), htmlentities($process_result['form_redirect'], ENT_QUOTES));
	}

	//if success page logic enabled, check all rules and get the correct redirect url
	if (
		($is_inserted && !empty($logic_success_enable) && empty($form_review) && ($form_page_total == 1) && empty($edit_id)) ||
		($is_inserted && !empty($logic_success_enable) && $is_committed && empty($edit_id))
	) {
		$process_result['form_redirect'] = '';

		$logic_redirect_url = mf_get_logic_success_redirect_url($dbh, $form_id, $process_result['entry_id']);

		if (!empty($logic_redirect_url)) {
			$process_result['form_redirect'] = $logic_redirect_url;
		}
	}

	//get payment processor URL, if applicable for this form
	if (
		($is_inserted && empty($form_review) && ($form_page_total == 1) && empty($edit_id)) ||
		($is_inserted && $is_committed && empty($edit_id))
	) {

		$merchant_redirect_url = @mf_get_merchant_redirect_url($dbh, $form_id, $record_insert_id);

		if (!empty($merchant_redirect_url) && $bypass_merchant_redirect_url !== true) {
			$process_result['form_redirect'] = $merchant_redirect_url;
		}
	}


	//save the entry id into session for success message
	if (!empty($process_result['entry_id'])) {
		$_SESSION['mf_success_entry_id'] = $process_result['entry_id'];
	}
	return $process_result;
}


//process form review submit
//move the record from temporary review table to the actual table
function mf_commit_form_review($dbh, $form_id, $record_id, $options = array())
{

	$mf_settings = mf_get_settings($dbh);

	//by default, this function will send notification email
	if ($options['send_notification'] === false) {
		$send_notification = false;
	} else {
		$send_notification = true;
	}

	//by default, this function will send logic notification email
	if ($options['send_logic_notification'] === false) {
		$send_logic_notification = false;
	} else {
		$send_logic_notification = true;
	}

	//move data from ap_form_x_review table to ap_form_x table
	//get all column name except session_id and id
	$query = "SELECT * FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` WHERE id=?";
	$params = array($record_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	$columns = array();
	foreach ($row as $column_name => $column_data) {
		if ($column_name != 'id' && $column_name != 'session_id' && $column_name != 'status' && $column_name != 'resume_key') {
			$columns[] = $column_name;
		}
	}

	$columns_joined = implode("`,`", $columns);
	$columns_joined = '`' . $columns_joined . '`';

	//copy data from review table
	$query = "INSERT INTO `" . MF_TABLE_PREFIX . "form_{$form_id}`($columns_joined) SELECT {$columns_joined} from `" . MF_TABLE_PREFIX . "form_{$form_id}_review` WHERE id=?";
	$params = array($record_id);

	mf_do_query($query, $params, $dbh);

	$new_record_id = (int) $dbh->lastInsertId();

	//update date_created with the current time
	$date_created = date("Y-m-d H:i:s");
	$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}` SET date_created = ? WHERE `id` = ?";
	$params = array($date_created, $new_record_id);

	mf_do_query($query, $params, $dbh);

	//check for resume_key from the review table
	//if there is resume_key, we need to delete the incomplete record within ap_form_x table which contain that resume_key
	$query = "SELECT `resume_key` FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` WHERE id=?";
	$params = array($record_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	if (!empty($row['resume_key'])) {
		$query = "DELETE from `" . MF_TABLE_PREFIX . "form_{$form_id}` where resume_key=? and `status`=2";
		$params = array($row['resume_key']);

		mf_do_query($query, $params, $dbh);
		//OTB ADD delete form entry entry
		$query = "DELETE from `form_entry` where form_id =? and entry_id =? and approved = 0";
		$params = array($form_id, $_SESSION['mf_success_entry_id']);

		mf_do_query($query, $params, $dbh);
	}

	//rename file uploads, if any
	//get all file uploads elements first

	//the default is not to store file upload as blob, unless defined otherwise within config.php file
	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB', false);

	$query = "SELECT 
						element_id 
					FROM 
						" . MF_TABLE_PREFIX . "form_elements 
				   WHERE 
				   		form_id=? AND 
				   		element_type='file' AND 
				   		element_status=1 AND
				   		element_is_private=0";
	$params = array($form_id);

	$file_uploads_array = array();

	$sth = mf_do_query($query, $params, $dbh);
	while ($row = mf_do_fetch_result($sth)) {
		$file_uploads_array[] = 'element_' . $row['element_id'];
	}

	if (!empty($file_uploads_array)) {
		$file_uploads_column = implode('`,`', $file_uploads_array);
		$file_uploads_column = '`' . $file_uploads_column . '`';

		$query = "SELECT {$file_uploads_column} FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where id=?";
		$params = array($record_id);

		$sth = mf_do_query($query, $params, $dbh);
		$row = mf_do_fetch_result($sth);
		$file_update_query = '';

		foreach ($file_uploads_array as $element_name) {
			$filename_record = $row[$element_name];

			if (empty($filename_record)) {
				continue;
			}

			//if the file upload field is using advance uploader, $filename would contain multiple file names, separated by pipe character '|'
			$filename_array = array();
			$filename_array = explode('|', $filename_record);

			$file_joined_value = '';
			foreach ($filename_array as $filename) {
				$target_filename = $options['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$filename}.tmp";

				//when an entry was submitted and then marked as incomplete, the filename is not being renamed
				//so we need to check different file name
				if (!file_exists($target_filename)) {
					$target_filename = $options['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$filename}";
				}

				$regex = '/^element_([0-9]*)_([0-9a-zA-Z]*)-([0-9]*)-(.*)$/';
				$matches = array();
				preg_match($regex, $filename, $matches);
				$filename_noelement = $matches[4];

				$file_token = md5(uniqid(rand(), true)); //add random token to uploaded filename, to increase security
				$destination_filename = $options['machform_data_path'] . $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$element_name}_{$file_token}-{$new_record_id}-{$filename_noelement}";

				$filename_noelement = addslashes(stripslashes($filename_noelement));
				$file_joined_value .= "{$element_name}_{$file_token}-{$new_record_id}-{$filename_noelement}|";

				if (file_exists($target_filename)) {

					rename($target_filename, $destination_filename);

					if (MF_STORE_FILES_AS_BLOB === true) {
						$file_options['deleted_file'] = $target_filename;
						mf_ap_form_files_insert($dbh, $form_id, $destination_filename, $file_options);
					}
				}
			}

			//build update query
			$file_joined_value = rtrim($file_joined_value, '|');
			$file_update_query .= "`{$element_name}`='{$file_joined_value}',";
		}

		$file_update_query = rtrim($file_update_query, ',');
		if (!empty($file_update_query)) {
			$query = "UPDATE `" . MF_TABLE_PREFIX . "form_{$form_id}` SET {$file_update_query} WHERE id=?";
			$params = array($new_record_id);

			mf_do_query($query, $params, $dbh);
		}
	}


	$_SESSION['mf_form_completed'][$form_id] = true;

	//send notification emails
	//get form properties data
	$query = "select 
						 form_redirect,
						 form_redirect_enable,
						 form_email,
						 esl_enable,
						 esl_from_name,
						 esl_from_email_address,
						 esl_bcc_email_address,
						 esl_replyto_email_address,
						 esl_subject,
						 esl_content,
						 esl_plain_text,
						 esl_pdf_enable,
						 esl_pdf_content,
						 esr_enable,
						 esr_email_address,
						 esr_from_name,
						 esr_from_email_address,
						 esr_bcc_email_address,
						 esr_replyto_email_address,
						 esr_subject,
						 esr_content,
						 esr_plain_text,
						 esr_pdf_enable,
						 esr_pdf_content,
						 logic_email_enable,
						 logic_webhook_enable,
						 logic_success_enable,
						 webhook_enable 
				     from 
				     	 `" . MF_TABLE_PREFIX . "forms` 
				    where 
				    	 form_id=?";
	$params = array($form_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	if (!empty($row['form_redirect_enable'])) {
		$form_redirect = $row['form_redirect'];
	}
	$form_email = $row['form_email'];

	$esl_from_name = $row['esl_from_name'];
	$esl_from_email_address = $row['esl_from_email_address'];
	$esl_bcc_email_address = $row['esl_bcc_email_address'];
	$esl_replyto_email_address = $row['esl_replyto_email_address'];
	$esl_subject = $row['esl_subject'];
	$esl_content = $row['esl_content'];
	$esl_plain_text = $row['esl_plain_text'];
	$esl_enable = $row['esl_enable'];
	$esl_pdf_enable = $row['esl_pdf_enable'];
	$esl_pdf_content = $row['esl_pdf_content'];

	$esr_email_address = $row['esr_email_address'];
	$esr_from_name = $row['esr_from_name'];
	$esr_from_email_address = $row['esr_from_email_address'];
	$esr_bcc_email_address = $row['esr_bcc_email_address'];
	$esr_replyto_email_address = $row['esr_replyto_email_address'];
	$esr_subject = $row['esr_subject'];
	$esr_content = $row['esr_content'];
	$esr_plain_text = $row['esr_plain_text'];
	$esr_enable = $row['esr_enable'];
	$esr_pdf_enable = $row['esr_pdf_enable'];
	$esr_pdf_content = $row['esr_pdf_content'];

	$logic_email_enable = (int) $row['logic_email_enable'];
	$logic_webhook_enable = (int) $row['logic_webhook_enable'];
	$logic_success_enable = (int) $row['logic_success_enable'];

	$webhook_enable = (int) $row['webhook_enable'];

	//start sending notification email to admin ------------------------------------------
	if (!empty($esl_enable) && !empty($form_email) && $send_notification === true) {
		//get parameters for the email

		//from name
		if (!empty($esl_from_name)) {
			if (is_numeric($esl_from_name)) {
				$admin_email_param['from_name'] = '{element_' . $esl_from_name . '}';
			} else {
				$admin_email_param['from_name'] = $esl_from_name;
			}
		} else {
			$admin_email_param['from_name'] = 'MachForm';
		}

		//from email address
		if (!empty($esl_from_email_address)) {
			if (is_numeric($esl_from_email_address)) {
				$admin_email_param['from_email'] = '{element_' . $esl_from_email_address . '}';
			} else {
				$admin_email_param['from_email'] = $esl_from_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$admin_email_param['from_email'] = "no-reply@{$domain}";
		}

		//reply-to email address
		if (!empty($esl_replyto_email_address)) {
			if (is_numeric($esl_replyto_email_address)) {
				$admin_email_param['replyto_email'] = '{element_' . $esl_replyto_email_address . '}';
			} else {
				$admin_email_param['replyto_email'] = $esl_replyto_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$admin_email_param['replyto_email'] = "no-reply@{$domain}";
		}

		//subject
		if (!empty($esl_subject)) {
			$admin_email_param['subject'] = $esl_subject;
		} else {
			$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
		}

		//bcc
		if (!empty($esl_bcc_email_address)) {
			$admin_email_param['bcc_emails'] = $esl_bcc_email_address;
		}

		//content
		if (!empty($esl_content)) {
			$admin_email_param['content'] = $esl_content;
		} else {
			$admin_email_param['content'] = '{entry_data}';
		}

		//pdf attachment
		if (!empty($esl_pdf_enable)) {
			$admin_email_param['pdf_enable'] = true;
			$admin_email_param['pdf_content'] = $esl_pdf_content;
		}

		$admin_email_param['as_plain_text'] = $esl_plain_text;
		$admin_email_param['target_is_admin'] = true;
		$admin_email_param['machform_base_path'] = $options['machform_path'];
		$admin_email_param['check_hook_file'] = true;

		mf_send_notification($dbh, $form_id, $new_record_id, $form_email, $admin_email_param);

	}
	//end emailing notifications to admin ----------------------------------------------


	//start sending notification email to user ------------------------------------------
	if (!empty($esr_enable) && !empty($esr_email_address) && $send_notification === true) {
		//get parameters for the email

		//to email 
		if (is_numeric($esr_email_address)) {
			$esr_email_address = '{element_' . $esr_email_address . '}';
		}

		//from name
		if (!empty($esr_from_name)) {
			if (is_numeric($esr_from_name)) {
				$user_email_param['from_name'] = '{element_' . $esr_from_name . '}';
			} else {
				$user_email_param['from_name'] = $esr_from_name;
			}
		} else {
			$user_email_param['from_name'] = 'MachForm';
		}

		//from email address
		if (!empty($esr_from_email_address)) {
			if (is_numeric($esr_from_email_address)) {
				$user_email_param['from_email'] = '{element_' . $esr_from_email_address . '}';
			} else {
				$user_email_param['from_email'] = $esr_from_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$user_email_param['from_email'] = "no-reply@{$domain}";
		}

		//reply-to email address
		if (!empty($esr_replyto_email_address)) {
			if (is_numeric($esr_replyto_email_address)) {
				$user_email_param['replyto_email'] = '{element_' . $esr_replyto_email_address . '}';
			} else {
				$user_email_param['replyto_email'] = $esr_replyto_email_address;
			}
		} else {
			$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
			$user_email_param['replyto_email'] = "no-reply@{$domain}";
		}

		//subject
		if (!empty($esr_subject)) {
			$user_email_param['subject'] = $esr_subject;
		} else {
			$user_email_param['subject'] = '{form_name} - Receipt';
		}

		//bcc
		if (!empty($esr_bcc_email_address)) {
			$user_email_param['bcc_emails'] = $esr_bcc_email_address;
		}

		//content
		if (!empty($esr_content)) {
			$user_email_param['content'] = $esr_content;
		} else {
			$user_email_param['content'] = '{entry_data}';
		}

		//pdf attachment
		if (!empty($esr_pdf_enable)) {
			$user_email_param['pdf_enable'] = true;
			$user_email_param['pdf_content'] = $esr_pdf_content;
		}

		$user_email_param['as_plain_text'] = $esr_plain_text;
		$user_email_param['target_is_admin'] = false;
		$user_email_param['machform_base_path'] = $options['machform_path'];

		mf_send_notification($dbh, $form_id, $new_record_id, $esr_email_address, $user_email_param);
	}
	//end emailing notifications to user ----------------------------------------------

	//send all notifications triggered by email-logic ---------------------
	//email logic is not affected by 'delay notifications until paid' option within the payment setting page
	if (!empty($logic_email_enable) && $send_logic_notification === true) {
		$logic_email_param = array();
		$logic_email_param['machform_base_path'] = $options['machform_path'];

		mf_send_logic_notifications($dbh, $form_id, $new_record_id, $logic_email_param);
	}


	//send webhook notification
	if (!empty($webhook_enable) && $send_notification === true) {
		mf_send_webhook_notification($dbh, $form_id, $new_record_id, 0);
	}

	//send webhook notification triggered by logic
	//email logic is not affected by 'delay notifications until paid' option within the payment setting page
	if (!empty($logic_webhook_enable) && $send_logic_notification === true) {
		mf_send_logic_webhook_notifications($dbh, $form_id, $new_record_id);
	}

	//delete all entry from this user in review table
	$session_id = session_id();
	$query = "DELETE FROM `" . MF_TABLE_PREFIX . "form_{$form_id}_review` where id=? or session_id=?";
	$params = array($record_id, $session_id);

	mf_do_query($query, $params, $dbh);

	//remove form history from session
	$_SESSION['mf_form_loaded'][$form_id] = array();
	unset($_SESSION['mf_form_loaded'][$form_id]);

	//remove form access session
	$_SESSION['mf_form_access'][$form_id] = array();
	unset($_SESSION['mf_form_access'][$form_id]);

	$_SESSION['mf_form_resume_url'][$form_id] = array();
	unset($_SESSION['mf_form_resume_url'][$form_id]);

	//remove pages history
	$_SESSION['mf_pages_history'][$form_id] = array();
	unset($_SESSION['mf_pages_history'][$form_id]);

	//unset the form resume session, if any
	$_SESSION['mf_form_resume_loaded'][$form_id] = false;
	unset($_SESSION['mf_form_resume_loaded'][$form_id]);

	//get merchant redirect url, if enabled for this form
	$merchant_redirect_url = mf_get_merchant_redirect_url($dbh, $form_id, $new_record_id);
	if (!empty($merchant_redirect_url)) {
		$form_redirect = $merchant_redirect_url;
	}

	//parse redirect URL from the form properties for any template variables
	if (!empty($form_redirect)) {
		$form_redirect = mf_parse_template_variables($dbh, $form_id, $new_record_id, $form_redirect);
		$form_redirect = str_replace(array('&amp;', '&#039;', '&quot;'), array('&', '%27', '%22'), htmlentities($form_redirect, ENT_QUOTES));
	}

	//if success page logic enabled, check all rules and get the correct redirect url
	if (!empty($logic_success_enable)) {
		//when logic success being enabled, the default redirect url 'form_redirect_enable' is being turned off (on save_logic_settings.php)
		//however, this rule shouldn't be applied for paypal url
		if (empty($merchant_redirect_url)) {
			$form_redirect = '';
		}

		$logic_redirect_url = mf_get_logic_success_redirect_url($dbh, $form_id, $new_record_id);

		if (!empty($logic_redirect_url)) {
			$form_redirect = $logic_redirect_url;
		}

		$commit_result['logic_success_enable'] = true;
	}

	$commit_result['form_redirect'] = $form_redirect;
	$commit_result['record_insert_id'] = $new_record_id;

	//save the entry id into session for success message
	$_SESSION['mf_success_entry_id'] = $new_record_id;

	return $commit_result;
}

//this is a helper function to check POST variable
//if there is submit button being sent, return true
function mf_is_form_submitted()
{
	if (!empty($_POST['submit_form']) || !empty($_POST['submit_primary']) || !empty($_POST['submit_secondary'])) {
		return true;
	} else {
		return false;
	}
}

//this function checks if the user is allowed to see this particular form page
function mf_verify_page_access($form_id, $page_number)
{
	if (empty($form_id)) {
		die('ID required.');
	}

	if (empty($page_number)) {
		return 1; //send the user to page 1 of the form if no page_number being specified
	} else {
		if ($_SESSION['mf_form_access'][$form_id][$page_number] === true) {
			return $page_number;
		} else {
			return 1;
		}
	}
}

//generate the merchant redirect URL for particular form
//the redirect URL contain complete payment information
function mf_get_merchant_redirect_url($dbh, $form_id, $entry_id)
{

	global $mf_lang;

	$mf_settings = mf_get_settings($dbh);

	$mf_settings['base_url'] = trim($mf_settings['base_url'], '/') . '/';
	$merchant_redirect_url = '';


	$payment_has_value = false;

	$query = "select 
						 payment_enable_merchant,
						 payment_merchant_type,
						 ifnull(payment_paypal_email,'') payment_paypal_email,
						 payment_paypal_language,
						 payment_currency,
						 payment_show_total,
						 payment_total_location,
						 payment_enable_recurring,
						 payment_recurring_cycle,
						 payment_recurring_unit,
						 payment_enable_trial,
						 payment_trial_period,
						 payment_trial_unit,
						 payment_trial_amount,
						 payment_price_type,
						 payment_price_amount,
						 payment_price_name,
						 payment_paypal_enable_test_mode,
						 payment_enable_tax,
						 payment_tax_rate,
						 form_redirect,
						 form_redirect_enable,
						 form_language,
						 payment_enable_discount,
						 payment_discount_type,
						 payment_discount_amount,
						 payment_discount_element_id
				     from 
				     	 `" . MF_TABLE_PREFIX . "forms` 
				    where 
				    	 form_id=?";

	$params = array($form_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	$payment_enable_merchant = (int) $row['payment_enable_merchant'];
	if (!empty($row['form_language'])) {
		mf_set_language($row['form_language']);
	}

	$payment_merchant_type = $row['payment_merchant_type'];
	$payment_paypal_email = $row['payment_paypal_email'];
	$payment_paypal_language = $row['payment_paypal_language'];

	$payment_currency = $row['payment_currency'];
	$payment_show_total = (int) $row['payment_show_total'];
	$payment_total_location = $row['payment_total_location'];
	$payment_enable_recurring = (int) $row['payment_enable_recurring'];
	$payment_recurring_cycle = (int) $row['payment_recurring_cycle'];
	$payment_recurring_unit = $row['payment_recurring_unit'];

	$form_redirect_enable = (int) $row['form_redirect_enable'];
	$form_redirect = $row['form_redirect'];

	$payment_paypal_enable_test_mode = (int) $row['payment_paypal_enable_test_mode'];
	if (!empty($payment_paypal_enable_test_mode)) {
		$paypal_url = "www.sandbox.paypal.com";
	} else {
		$paypal_url = "www.paypal.com";
	}

	$payment_enable_trial = (int) $row['payment_enable_trial'];
	$payment_trial_period = (int) $row['payment_trial_period'];
	$payment_trial_unit = $row['payment_trial_unit'];
	$payment_trial_amount = $row['payment_trial_amount'];

	$payment_price_type = $row['payment_price_type'];
	$payment_price_amount = (float) $row['payment_price_amount'];
	$payment_price_name = $row['payment_price_name'];

	$payment_enable_tax = (int) $row['payment_enable_tax'];
	$payment_tax_rate = (float) $row['payment_tax_rate'];

	$payment_enable_discount = (int) $row['payment_enable_discount'];
	$payment_discount_type = $row['payment_discount_type'];
	$payment_discount_amount = (float) $row['payment_discount_amount'];
	$payment_discount_element_id = (int) $row['payment_discount_element_id'];

	$is_discount_applicable = false;

	//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
	if (!empty($payment_enable_discount) && !empty($payment_enable_merchant)) {
		$query = "select element_{$payment_discount_element_id} coupon_element from " . MF_TABLE_PREFIX . "form_{$form_id} where `id` = ? and `status` = 1";
		$params = array($entry_id);

		$sth = mf_do_query($query, $params, $dbh);
		$row = mf_do_fetch_result($sth);

		if (!empty($row['coupon_element'])) {
			$is_discount_applicable = true;
		}
	}

	if (!empty($form_redirect_enable) && !empty($form_redirect)) {
		$form_redirect = mf_parse_template_variables($dbh, $form_id, $entry_id, $form_redirect);
		$form_redirect = str_replace(array('&amp;', '&#039;', '&quot;'), array('&', '%27', '%22'), htmlentities($form_redirect, ENT_QUOTES));
	}

	if ($payment_enable_merchant) { //if merchant is enabled

		//paypal website payment standard
		if ($payment_merchant_type == 'paypal_standard') {

			//get current entry timestamp
			$query = "select unix_timestamp(date_created) entry_timestamp from " . MF_TABLE_PREFIX . "form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($entry_id);

			$sth = mf_do_query($query, $params, $dbh);
			$row = mf_do_fetch_result($sth);
			$entry_timestamp = $row['entry_timestamp'];

			$paypal_params = array();

			$paypal_params['charset'] = 'UTF-8';
			$paypal_params['upload'] = 1;
			$paypal_params['business'] = $payment_paypal_email;
			$paypal_params['currency_code'] = $payment_currency;
			$paypal_params['custom'] = $form_id . '_' . $entry_id . '_' . $entry_timestamp;
			$paypal_params['rm'] = 2; //the buyer’s browser is redirected to the return URL by using the POST method, and all payment variables are included
			$paypal_params['lc'] = $payment_paypal_language;
			$paypal_params['bn'] = 'AppNitro_SP';

			if (!empty($form_redirect)) {
				$paypal_params['return'] = $form_redirect;
			} else {
				$paypal_params['return'] = $mf_settings['base_url'] . 'view?id=' . $form_id . '&done=1';
			}

			$paypal_params['notify_url'] = $mf_settings['base_url'] . 'paypalipn';
			$paypal_params['no_shipping'] = 1;

			if (!empty($payment_enable_recurring)) { //this is recurring payment
				$paypal_params['cmd'] = '_xclick-subscriptions';
				$paypal_params['src'] = 1; //subscription payments recur, until user cancel it
				$paypal_params['sra'] = 1; //reattempt failed recurring payments before canceling
				$paypal_params['item_name'] = $payment_price_name;
				$paypal_params['p3'] = $payment_recurring_cycle;
				$paypal_params['t3'] = strtoupper($payment_recurring_unit[0]);

				if ($paypal_params['t3'] == 'Y' && $payment_recurring_cycle > 5) {
					$paypal_params['p3'] = 5; //paypal can only handle 5-year-period recurring payments, maximum	
				}

				if ($payment_price_type == 'fixed') { //this is fixed amount payment	
					$paypal_params['a3'] = $payment_price_amount;

					if (!empty($payment_price_amount) && ($payment_price_amount !== '0.00')) {
						$payment_has_value = true;

						//calculate discount if applicable
						if ($is_discount_applicable) {
							$payment_calculated_discount = 0;

							if ($payment_discount_type == 'percent_off') {
								//the discount is percentage
								$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_price_amount;
								$payment_calculated_discount = round($payment_calculated_discount, 2); //round to 2 digits decimal
							} else {
								//the discount is fixed amount
								$payment_calculated_discount = round($payment_discount_amount, 2); //round to 2 digits decimal
							}

							//if discount amount is equal or more than the total amount
							//we need to discard the charge amount
							if ($payment_calculated_discount >= $payment_price_amount) {
								$payment_has_value = false;
							}

							$payment_price_amount -= $payment_calculated_discount;

							$paypal_params['a3'] = $payment_price_amount;
							$paypal_params['item_name'] .= " (-{$mf_lang['discount']})";
						}

						//calculate tax if enabled
						if (!empty($payment_enable_tax) && !empty($payment_tax_rate)) {
							//$payment_tax_amount = ($payment_tax_rate / 100) * $payment_price_amount;
							//$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
							$payment_tax_amount = $payment_tax_rate;

							$paypal_params['a3'] = $payment_price_amount + $payment_tax_amount;
							$paypal_params['item_name'] .= " (+{$mf_lang['tax']} {$payment_tax_rate}%)";
						}
					}
				} else if ($payment_price_type == 'variable') {

					$total_payment_amount = 0;

					//get price fields information from ap_element_prices table
					$query = "select 
											A.element_id,
											A.option_id,
											A.price,
											B.element_title,
											B.element_type,
											(select `option_text` from " . MF_TABLE_PREFIX . "element_options where form_id=A.form_id and element_id=A.element_id and option_id=A.option_id and live=1 limit 1) option_title
										from
											" . MF_TABLE_PREFIX . "element_prices A left join " . MF_TABLE_PREFIX . "form_elements B on (A.form_id=B.form_id and A.element_id=B.element_id)
										where
											A.form_id = ?
										order by 
											A.element_id,A.option_id asc";

					$params = array($form_id);
					$sth = mf_do_query($query, $params, $dbh);

					$price_field_columns = array();

					while ($row = mf_do_fetch_result($sth)) {
						$element_id = (int) $row['element_id'];
						$option_id = (int) $row['option_id'];
						$element_type = $row['element_type'];

						if ($element_type == 'checkbox') {
							$column_name = 'element_' . $element_id . '_' . $option_id;
						} else {
							$column_name = 'element_' . $element_id;
						}

						if (!in_array($column_name, $price_field_columns)) {
							$price_field_columns[] = $column_name;
							$price_field_types[$column_name] = $row['element_type'];
						}

						$price_values[$element_id][$option_id] = $row['price'];

						if ($element_type == 'money') {
							$price_titles[$element_id][$option_id] = $row['element_title'];
						} else {
							$price_titles[$element_id][$option_id] = $row['option_title'];
						}
					}
					$price_field_columns_joined = implode(',', $price_field_columns);

					//get quantity fields
					$quantity_fields_info = array();
					$quantity_field_columns = array();

					$query = "select 
									 		element_id,
									 		element_number_quantity_link
										from 
											" . MF_TABLE_PREFIX . "form_elements 
									   where
									   		form_id = ? and
									   		element_status = 1 and
									   		element_type = 'number' and
									   		element_number_enable_quantity = 1 and
									   		element_number_quantity_link is not null
									group by 
											element_number_quantity_link 
									order by
									   		element_id asc";
					$params = array($form_id);
					$sth = mf_do_query($query, $params, $dbh);

					while ($row = mf_do_fetch_result($sth)) {
						$quantity_fields_info[$row['element_number_quantity_link']] = $row['element_id'];
						$quantity_field_columns[] = 'element_' . $row['element_id'];
					}

					if (!empty($quantity_fields_info)) {
						$quantity_field_columns_joined = implode(',', $quantity_field_columns);
						$price_field_columns_joined = $price_field_columns_joined . ',' . $quantity_field_columns_joined;
					}

					//check the value of the price fields from the ap_form_x table
					$query = "select {$price_field_columns_joined} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
					$params = array($entry_id);
					$sth = mf_do_query($query, $params, $dbh);
					$row = mf_do_fetch_result($sth);

					$processed_column_name = array();
					$selected_item_names = array();

					foreach ($price_field_columns as $column_name) {
						if (!empty($row[$column_name]) && !in_array($column_name, $processed_column_name)) {
							$temp = explode('_', $column_name);
							$element_id = (int) $temp[1];
							$option_id = (int) $temp[2];

							$item_name = '';

							if ($price_field_types[$column_name] == 'money') {
								$item_name = $price_titles[$element_id][0];

								if (!empty($quantity_fields_info['element_' . $element_id])) {
									$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

									if (empty($quantity) || $quantity < 0) {
										$quantity = 0;
									}
								} else {
									$quantity = 1;
								}

								$total_payment_amount += ($row[$column_name] * $quantity);
							} else if ($price_field_types[$column_name] == 'checkbox') {
								$item_name = $price_titles[$element_id][$option_id];

								if (!empty($quantity_fields_info['element_' . $element_id . '_' . $option_id])) {
									$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id . '_' . $option_id]];

									if (empty($quantity) || $quantity < 0) {
										$quantity = 0;
									}
								} else {
									$quantity = 1;
								}

								$total_payment_amount += ($price_values[$element_id][$option_id] * $quantity);
							} else { //dropdown or multiple choice
								$option_id = $row[$column_name];
								$item_name = $price_titles[$element_id][$option_id];

								if (!empty($quantity_fields_info['element_' . $element_id])) {
									$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

									if (empty($quantity) || $quantity < 0) {
										$quantity = 0;
									}
								} else {
									$quantity = 1;
								}

								$total_payment_amount += ($price_values[$element_id][$option_id] * $quantity);
							}

							if (!empty($item_name)) {
								$selected_item_names[] = $item_name;
							}

							$processed_column_name[] = $column_name;
						}
					}

					$paypal_params['item_name'] = implode(' - ', $selected_item_names);
					$paypal_params['a3'] = $total_payment_amount;

					if (!empty($total_payment_amount) && ($total_payment_amount !== '0.00')) {
						$payment_has_value = true;

						//calculate discount if applicable
						if ($is_discount_applicable) {
							$payment_calculated_discount = 0;

							if ($payment_discount_type == 'percent_off') {
								//the discount is percentage
								$payment_calculated_discount = ($payment_discount_amount / 100) * $total_payment_amount;
								$payment_calculated_discount = round($payment_calculated_discount, 2); //round to 2 digits decimal
							} else {
								//the discount is fixed amount
								$payment_calculated_discount = round($payment_discount_amount, 2); //round to 2 digits decimal
							}

							//if discount amount is equal or more than the total amount
							//we need to discard the charge amount
							if ($payment_calculated_discount >= $total_payment_amount) {
								$payment_has_value = false;
							}

							$total_payment_amount -= $payment_calculated_discount;

							$paypal_params['a3'] = $total_payment_amount;
							$paypal_params['item_name'] .= " (-{$mf_lang['discount']})";
						}

						//calculate tax if enabled
						if (!empty($payment_enable_tax) && !empty($payment_tax_rate)) {
							//$payment_tax_amount = ($payment_tax_rate / 100) * $total_payment_amount;
							//$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
							$payment_tax_amount = $payment_tax_rate;

							$paypal_params['a3'] = $total_payment_amount + $payment_tax_amount;
							$paypal_params['item_name'] .= " (+{$mf_lang['tax']} {$payment_tax_rate}%)";
						}
					}
				}//end of variable-recurring payment

				//trial periods
				if (!empty($payment_enable_trial)) {
					//set trial price
					if ($payment_trial_amount === '0.00') {
						$payment_trial_amount = 0;
					}
					$paypal_params['a1'] = $payment_trial_amount;

					//set trial period
					$paypal_params['p1'] = $payment_trial_period;
					$paypal_params['t1'] = strtoupper($payment_trial_unit[0]);

					//check for limits being set by PayPal
					if ($paypal_params['t1'] == 'Y' && $payment_trial_period > 5) {
						$paypal_params['p1'] = 5; //max 5 years recurring
					}
				}
			} else { //non recurring payment
				$paypal_params['cmd'] = '_cart';

				if ($payment_price_type == 'fixed') { //this is fixed amount payment

					$paypal_params['item_name_1'] = $payment_price_name;
					$paypal_params['amount_1'] = $payment_price_amount;

					if (!empty($payment_price_amount) && ($payment_price_amount !== '0.00')) {
						$payment_has_value = true;

						//calculate discount if applicable
						if ($is_discount_applicable) {
							$payment_calculated_discount = 0;

							if ($payment_discount_type == 'percent_off') {
								//the discount is percentage
								$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_price_amount;
								$payment_calculated_discount = round($payment_calculated_discount, 2); //round to 2 digits decimal
							} else {
								//the discount is fixed amount
								$payment_calculated_discount = round($payment_discount_amount, 2); //round to 2 digits decimal
							}

							//if discount amount is equal or more than the total amount
							//we need to discard the charge amount
							if ($payment_calculated_discount >= $payment_price_amount) {
								$payment_has_value = false;
							}

							$payment_price_amount -= $payment_calculated_discount;
							$paypal_params['discount_amount_cart'] = $payment_calculated_discount;
						}

						//calculate tax if enabled
						if (!empty($payment_enable_tax) && !empty($payment_tax_rate)) {
							//$payment_tax_amount = ($payment_tax_rate / 100) * $payment_price_amount;
							//$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
							$payment_tax_amount = $payment_tax_rate;

							$paypal_params['tax_cart'] = $payment_tax_amount;
						}

					}
				} else if ($payment_price_type == 'variable') { //this is variable amount payment

					//get price fields information from ap_element_prices table
					$query = "select 
											A.element_id,
											A.option_id,
											A.price,
											B.element_title,
											B.element_type,
											(select `option_text` from " . MF_TABLE_PREFIX . "element_options where form_id=A.form_id and element_id=A.element_id and option_id=A.option_id and live=1 limit 1) option_title
										from
											" . MF_TABLE_PREFIX . "element_prices A left join " . MF_TABLE_PREFIX . "form_elements B on (A.form_id=B.form_id and A.element_id=B.element_id)
										where
											A.form_id = ?
										order by 
											A.element_id,A.option_id asc";
					$params = array($form_id);
					$sth = mf_do_query($query, $params, $dbh);

					$price_field_columns = array();

					while ($row = mf_do_fetch_result($sth)) {
						$element_id = (int) $row['element_id'];
						$option_id = (int) $row['option_id'];
						$element_type = $row['element_type'];

						if ($element_type == 'checkbox') {
							$column_name = 'element_' . $element_id . '_' . $option_id;
						} else {
							$column_name = 'element_' . $element_id;
						}

						if (!in_array($column_name, $price_field_columns)) {
							$price_field_columns[] = $column_name;
							$price_field_types[$column_name] = $row['element_type'];
						}

						$price_values[$element_id][$option_id] = $row['price'];

						if ($element_type == 'money') {
							$price_titles[$element_id][$option_id] = $row['element_title'];
						} else {
							$price_titles[$element_id][$option_id] = $row['option_title'];
						}
					}
					$price_field_columns_joined = implode(',', $price_field_columns);

					//get quantity fields
					$quantity_fields_info = array();
					$quantity_field_columns = array();

					$query = "select 
									 		element_id,
									 		element_number_quantity_link
										from 
											" . MF_TABLE_PREFIX . "form_elements 
									   where
									   		form_id = ? and
									   		element_status = 1 and
									   		element_type = 'number' and
									   		element_number_enable_quantity = 1 and
									   		element_number_quantity_link is not null
									group by 
											element_number_quantity_link 
									order by
									   		element_id asc";
					$params = array($form_id);
					$sth = mf_do_query($query, $params, $dbh);

					while ($row = mf_do_fetch_result($sth)) {
						$quantity_fields_info[$row['element_number_quantity_link']] = $row['element_id'];
						$quantity_field_columns[] = 'element_' . $row['element_id'];
					}

					if (!empty($quantity_fields_info)) {
						$quantity_field_columns_joined = implode(',', $quantity_field_columns);
						$price_field_columns_joined = $price_field_columns_joined . ',' . $quantity_field_columns_joined;
					}

					//check the value of the price fields from the ap_form_x table
					$query = "select {$price_field_columns_joined} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
					$params = array($entry_id);
					$sth = mf_do_query($query, $params, $dbh);
					$row = mf_do_fetch_result($sth);

					$i = 1;
					$processed_column_name = array();

					foreach ($price_field_columns as $column_name) {

						if (!empty($row[$column_name]) && !in_array($column_name, $processed_column_name)) {

							$temp = explode('_', $column_name);
							$element_id = (int) $temp[1];
							$option_id = (int) $temp[2];

							$item_name = '';
							$amount = '';

							if ($price_field_types[$column_name] == 'money') {
								$item_name = $price_titles[$element_id][0];
								$amount = $row[$column_name];

								if (!empty($quantity_fields_info['element_' . $element_id])) {
									$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

									if (empty($quantity) || $quantity < 0) {
										$quantity = 0;
									}
								} else {
									$quantity = 1;
								}
							} else if ($price_field_types[$column_name] == 'checkbox') {
								$item_name = $price_titles[$element_id][$option_id];
								$amount = $price_values[$element_id][$option_id];

								if (!empty($quantity_fields_info['element_' . $element_id . '_' . $option_id])) {
									$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id . '_' . $option_id]];

									if (empty($quantity) || $quantity < 0) {
										$quantity = 0;
									}
								} else {
									$quantity = 1;
								}
							} else { //dropdown or multiple choice
								$option_id = $row[$column_name];
								$item_name = $price_titles[$element_id][$option_id];
								$amount = $price_values[$element_id][$option_id];

								if (!empty($quantity_fields_info['element_' . $element_id])) {
									$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

									if (empty($quantity) || $quantity < 0) {
										$quantity = 0;
									}
								} else {
									$quantity = 1;
								}
							}

							$processed_column_name[] = $column_name;

							if (!empty($amount) && ($amount !== '0.00')) {
								$payment_has_value = true;

								$paypal_params['item_name_' . $i] = $item_name;
								$paypal_params['amount_' . $i] = $amount;
								$paypal_params['quantity_' . $i] = $quantity;
								$i++;
							}

						}
					}

					$payment_price_amount = (double) mf_get_payment_total($dbh, $form_id, $entry_id, 0, 'live');

					//calculate discount if applicable
					if ($is_discount_applicable) {
						$payment_calculated_discount = 0;

						if ($payment_discount_type == 'percent_off') {
							//the discount is percentage
							$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_price_amount;
							$payment_calculated_discount = round($payment_calculated_discount, 2); //round to 2 digits decimal
						} else {
							//the discount is fixed amount
							$payment_calculated_discount = round($payment_discount_amount, 2); //round to 2 digits decimal
						}

						//if discount amount is equal or more than the total amount
						//we need to discard the charge amount
						if ($payment_calculated_discount >= $payment_price_amount) {
							$payment_has_value = false;
						}

						$payment_price_amount -= $payment_calculated_discount;
						$paypal_params['discount_amount_cart'] = $payment_calculated_discount;
					}

					//calculate tax if enabled
					if (!empty($payment_enable_tax) && !empty($payment_tax_rate) && $payment_has_value) {

						//$payment_tax_amount = ($payment_tax_rate / 100) * $payment_price_amount;
						//$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
						$payment_tax_amount = $payment_tax_rate;

						$paypal_params['tax_cart'] = $payment_tax_amount;
					}


				}//end of non-recurring variable payment
			}//end of non-recurring payment


			$merchant_redirect_url = 'https://' . $paypal_url . '/cgi-bin/webscr?' . http_build_query($paypal_params, '', '&');

		}//end paypal standard		
	}

	if ($payment_has_value) {
		return $merchant_redirect_url;
	} else {
		return ''; //if total amount is zero, don't redirect to PayPal
	}

}

//return true if a payment-enabled form is being submitted and has value (not zero)
//currently this is only being used for stripe, authorize.net, braintree and paypal pro
function mf_is_payment_has_value($dbh, $form_id, $entry_id)
{

	$payment_has_value = false;

	$props = array(
		'payment_enable_merchant',
		'payment_merchant_type',
		'payment_price_amount',
		'payment_price_type',
		'payment_delay_notifications',
		'form_review',
		'form_page_total',
		'payment_enable_discount',
		'payment_discount_type',
		'payment_discount_amount',
		'payment_discount_element_id',
		'payment_onsubmission'
	);
	$form_properties = mf_get_form_properties($dbh, $form_id, $props);

	$payment_enable_discount = (int) $form_properties['payment_enable_discount'];
	$payment_discount_type = $form_properties['payment_discount_type'];
	$payment_discount_amount = (float) $form_properties['payment_discount_amount'];
	$payment_discount_element_id = (int) $form_properties['payment_discount_element_id'];

	$is_discount_applicable = false;
	error_log('-----Merchant----' . $form_properties['payment_enable_merchant'] . '--------onsubmission---' . $form_properties['payment_onsubmission'] . '-------merchant type------' . $form_properties['payment_merchant_type']);
	//if(($form_properties['payment_enable_merchant'] == 1) && in_array($form_properties['payment_merchant_type'], array('stripe','authorizenet','paypal_rest','braintree','pesaflow_standard','pesaflow_cart'))){
	if ($form_properties['payment_enable_merchant'] && $form_properties['payment_onsubmission'] && strlen($form_properties['payment_merchant_type'])) {
		error_log('------TRUE-merchant---------');
		//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
		if (!empty($payment_enable_discount)) {
			$query = "select element_{$payment_discount_element_id} coupon_element from " . MF_TABLE_PREFIX . "form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($entry_id);

			$sth = mf_do_query($query, $params, $dbh);
			$row = mf_do_fetch_result($sth);

			if (!empty($row['coupon_element'])) {
				$is_discount_applicable = true;
			}
		}

		if ($form_properties['payment_price_type'] == 'variable') {
			$total_payment_amount = (double) mf_get_payment_total($dbh, $form_id, $entry_id, 0, 'live');
		} else if ($form_properties['payment_price_type'] == 'fixed') {
			$total_payment_amount = (double) $form_properties['payment_price_amount'];
		}

		//calculate discount if applicable
		if ($is_discount_applicable) {
			$payment_calculated_discount = 0;

			if ($payment_discount_type == 'percent_off') {
				//the discount is percentage
				$payment_calculated_discount = ($payment_discount_amount / 100) * $total_payment_amount;
				$payment_calculated_discount = round($payment_calculated_discount, 2); //round to 2 digits decimal
			} else {
				//the discount is fixed amount
				$payment_calculated_discount = round($payment_discount_amount, 2); //round to 2 digits decimal
			}

			$total_payment_amount -= $payment_calculated_discount;
		}

		if (!empty($total_payment_amount) && $total_payment_amount > 0) {
			$payment_has_value = true;
		}
	}

	return $payment_has_value;
}

//get the total payment of a submission from ap_form_x_review or ap_form_x table
//this function doesn't include tax calculation
function mf_get_payment_total($dbh, $form_id, $record_id, $exclude_page_number, $target_table = 'review')
{

	$form_id = (int) $form_id;
	$total_payment_amount = 0;

	//get price fields information from ap_element_prices table
	$query = "select 
						A.element_id,
						A.option_id,
						A.price,
						B.element_title,
						B.element_type,
						B.element_price_class,
						B.element_option_query,
						B.element_remote_username,
						B.element_remote_password,
						B.element_remote_server_field,
						B.element_field_value,
						B.element_remote_value,
						(select `option_text` from " . MF_TABLE_PREFIX . "element_options where form_id=A.form_id and element_id=A.element_id and option_id=A.option_id and live=1 limit 1) option_title
					from
						" . MF_TABLE_PREFIX . "element_prices A left join " . MF_TABLE_PREFIX . "form_elements B on (A.form_id=B.form_id and A.element_id=B.element_id)
				   where
						A.form_id = ? and B.element_page_number <> ?
				order by 
						A.element_id,A.option_id asc";

	$params = array($form_id, $exclude_page_number);
	$sth = mf_do_query($query, $params, $dbh);

	$price_field_columns = array();
	$price_calculator_rows = array();

	while ($row = mf_do_fetch_result($sth)) {

		$element_id = (int) $row['element_id'];
		$option_id = (int) $row['option_id'];
		$element_type = $row['element_type'];


		if ($element_type == 'checkbox') {
			$column_name = 'element_' . $element_id . '_' . $option_id;
		} else {
			$column_name = 'element_' . $element_id;
		}

		if (!in_array($column_name, $price_field_columns)) {
			$price_field_columns[] = $column_name;
			$price_field_types[$column_name] = $row['element_type'];
		}

		$price_calculator_rows[$column_name]['price_class'] = $row['element_price_class'];

		$price_calculator_rows[$column_name]['remote_url'] = $row['element_option_query'];
		$price_calculator_rows[$column_name]['remote_username'] = $row['element_remote_username'];
		$price_calculator_rows[$column_name]['remote_password'] = $row['element_remote_password'];
		$price_calculator_rows[$column_name]['remote_server_field'] = $row['element_remote_server_field'];
		$price_calculator_rows[$column_name]['remote_value'] = $row['element_remote_value'];

		$price_values[$element_id][$option_id] = $row['price'];
	}

	if (empty($price_field_columns)) {
		return 0;
	}


	$price_field_columns_joined = implode(',', $price_field_columns);

	//get quantity fields
	$quantity_fields_info = array();
	$quantity_field_columns = array();

	$query = "select 
				 		element_id,
				 		element_number_quantity_link
					from 
						" . MF_TABLE_PREFIX . "form_elements 
				   where
				   		form_id = ? and
				   		element_status = 1 and
				   		element_type = 'number' and
				   		element_number_enable_quantity = 1 and
				   		element_number_quantity_link is not null
				group by 
						element_number_quantity_link 
				order by
				   		element_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query, $params, $dbh);

	while ($row = mf_do_fetch_result($sth)) {
		$quantity_fields_info[$row['element_number_quantity_link']] = $row['element_id'];
		$quantity_field_columns[] = 'element_' . $row['element_id'];
	}

	if (!empty($quantity_fields_info)) {
		$quantity_field_columns_joined = implode(',', $quantity_field_columns);
		$price_field_columns_joined = $price_field_columns_joined . ',' . $quantity_field_columns_joined;
	}

	//check the value of the price fields from the ap_form_x_review or ap_form_x table
	if ($target_table == 'review') {
		$query = "select {$price_field_columns_joined} from " . MF_TABLE_PREFIX . "form_{$form_id}_review where `session_id`=?";
	} else {
		$query = "select {$price_field_columns_joined} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
	}

	$params = array($record_id);
	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	$processed_column_name = array();

	foreach ($price_field_columns as $column_name) {
		if (!empty($row[$column_name]) && !in_array($column_name, $processed_column_name)) {
			$temp = explode('_', $column_name);
			$element_id = (int) $temp[1];
			$option_id = (int) $temp[2];

			if ($price_field_types[$column_name] == 'money') {
				if (!empty($quantity_fields_info['element_' . $element_id])) {
					$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

					if (empty($quantity) || $quantity < 0) {
						$quantity = 0;
					}
				} else {
					$quantity = 1;
				}

				$column_name = 'element_' . $element_id;

				if ($price_calculator_rows[$column_name]['price_class'] == "ag_stamp_duty") {
					$price_calculator = new PriceCalculator();

					$total_payment_amount += $price_calculator->AGStampDuty(($row[$column_name] * $quantity));
				} elseif ($price_calculator_rows[$column_name]['price_class'] == "lands_stamp_duty") {
					$price_calculator = new PriceCalculator();

					$total_payment_amount += $price_calculator->LandsStampDuty(($row[$column_name] * $quantity));
				} elseif ($price_calculator_rows[$column_name]['price_class'] == "lands_stamp_duty2") {
					$price_calculator = new PriceCalculator();

					$total_payment_amount += $price_calculator->LandsStampDuty2(($row[$column_name] * $quantity));
				} elseif ($price_calculator_rows[$column_name]['price_class'] == "dataflow_dynamic") {
					$price_calculator = new PriceCalculator();

					if ($target_table == 'review') {
						$query = "select element_{$price_calculator_rows[$column_name]['remote_value']} from " . MF_TABLE_PREFIX . "form_{$form_id}_review where `session_id`=?";
					} else {
						$query = "select element_{$price_calculator_rows[$column_name]['remote_value']} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
					}

					$params = array($record_id);
					$sth = mf_do_query($query, $params, $dbh);
					$review_row = mf_do_fetch_result($sth);

					if ($target_table == 'review') {
						$query = "update " . MF_TABLE_PREFIX . "form_{$form_id}_review set " . $column_name . " = '0' where `session_id`=?";
					} else {
						$query = "update " . MF_TABLE_PREFIX . "form_{$form_id} set " . $column_name . " = '0' where `id`=?";
					}

					$params = array($record_id);
					mf_do_query($query, $params, $dbh);

					$dataflow_field_value = $review_row["element_" . $price_calculator_rows[$column_name]['remote_value']];

					$remote_url = str_replace('$value', $dataflow_field_value, $price_calculator_rows[$column_name]['remote_url']);

					$dataflow_price = $price_calculator->DataflowWithPenaltiesDynamicCalculator($remote_url, $price_calculator_rows[$column_name]['remote_username'], $price_calculator_rows[$column_name]['remote_password'], $price_calculator_rows[$column_name]['remote_server_field']);

					if ($dataflow_price) {
						$total_payment_amount += (($dataflow_price / 100) * $total_payment_amount); //It is a percentage of the total 
						//Ensure the field is located at the end of the form
					} else {
						$total_payment_amount += 0;
					}

				} elseif ($price_calculator_rows[$column_name]['price_class'] == "dataflow_fixed") {
					$price_calculator = new PriceCalculator();

					if ($target_table == 'review') {
						$query = "select element_{$price_calculator_rows[$column_name]['remote_value']} from " . MF_TABLE_PREFIX . "form_{$form_id}_review where `session_id`=?";
					} else {
						$query = "select element_{$price_calculator_rows[$column_name]['remote_value']} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
					}

					$params = array($record_id);
					$sth = mf_do_query($query, $params, $dbh);
					$review_row = mf_do_fetch_result($sth);

					if ($target_table == 'review') {
						$query = "update " . MF_TABLE_PREFIX . "form_{$form_id}_review set " . $column_name . " = '0' where `session_id`=?";
					} else {
						$query = "update " . MF_TABLE_PREFIX . "form_{$form_id} set " . $column_name . " = '0' where `id`=?";
					}

					$params = array($record_id);
					mf_do_query($query, $params, $dbh);

					$dataflow_field_value = $review_row["element_" . $price_calculator_rows[$column_name]['remote_value']];

					$remote_url = str_replace('$value', $dataflow_field_value, $price_calculator_rows[$column_name]['remote_url']);

					$dataflow_price = $price_calculator->DataflowWithPenaltiesFixedCalculator($remote_url, $price_calculator_rows[$column_name]['remote_username'], $price_calculator_rows[$column_name]['remote_password'], $price_calculator_rows[$column_name]['remote_server_field']);

					if ($dataflow_price) {
						$total_payment_amount += $dataflow_price;
					} else {
						$total_payment_amount += 0;
					}

					$params = array($record_id);
					mf_do_query($query, $params, $dbh);
				} else {
					$total_payment_amount += ($row[$column_name] * $quantity);
				}

			} else if ($price_field_types[$column_name] == 'checkbox') {

				if (!empty($quantity_fields_info['element_' . $element_id . '_' . $option_id])) {
					$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id . '_' . $option_id]];

					if (empty($quantity) || $quantity < 0) {
						$quantity = 0;
					}
				} else {
					$quantity = 1;
				}

				$total_payment_amount += ($price_values[$element_id][$option_id] * $quantity);
			} else { //dropdown or multiple choice

				if (!empty($quantity_fields_info['element_' . $element_id])) {
					$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

					if (empty($quantity) || $quantity < 0) {
						$quantity = 0;
					}
				} else {
					$quantity = 1;
				}

				$option_id = $row[$column_name];
				$total_payment_amount += ($price_values[$element_id][$option_id] * $quantity);
			}

			$processed_column_name[] = $column_name;
		}
	}

	return $total_payment_amount;
}

//get a list/array of all items to be paid within a form
function mf_get_payment_items($dbh, $form_id, $record_id, $target_table = 'review')
{

	$payment_items = array();
	$form_id = (int) $form_id;

	//get price fields information from ap_element_prices table
	$query = "select 
						A.element_id,
						A.option_id,
						A.price,
						B.element_title,
						B.element_type,
						(select `option_text` from " . MF_TABLE_PREFIX . "element_options where form_id=A.form_id and element_id=A.element_id and option_id=A.option_id and live=1 limit 1) option_title
					from
						" . MF_TABLE_PREFIX . "element_prices A left join " . MF_TABLE_PREFIX . "form_elements B on (A.form_id=B.form_id and A.element_id=B.element_id)
				   where
						A.form_id = ? 
				order by 
						B.element_position,A.option_id asc";

	$params = array($form_id);
	$sth = mf_do_query($query, $params, $dbh);

	$price_field_columns = array();

	while ($row = mf_do_fetch_result($sth)) {

		$element_id = (int) $row['element_id'];
		$option_id = (int) $row['option_id'];
		$element_type = $row['element_type'];

		if ($element_type == 'checkbox') {
			$column_name = 'element_' . $element_id . '_' . $option_id;
		} else {
			$column_name = 'element_' . $element_id;
		}

		if (!in_array($column_name, $price_field_columns)) {
			$price_field_columns[] = $column_name;
			$price_field_types[$column_name] = $row['element_type'];
		}

		$price_values[$element_id][$option_id] = $row['price'];
	}

	if (empty($price_field_columns)) {
		return false;
	}

	$price_field_columns_joined = implode(',', $price_field_columns);

	//get quantity fields
	$quantity_fields_info = array();
	$quantity_field_columns = array();

	$query = "select 
				 		element_id,
				 		element_number_quantity_link
					from 
						" . MF_TABLE_PREFIX . "form_elements 
				   where
				   		form_id = ? and
				   		element_status = 1 and
				   		element_type = 'number' and
				   		element_number_enable_quantity = 1 and
				   		element_number_quantity_link is not null
				group by 
						element_number_quantity_link 
				order by
				   		element_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query, $params, $dbh);

	while ($row = mf_do_fetch_result($sth)) {
		$quantity_fields_info[$row['element_number_quantity_link']] = $row['element_id'];
		$quantity_field_columns[] = 'element_' . $row['element_id'];
	}

	if (!empty($quantity_fields_info)) {
		$quantity_field_columns_joined = implode(',', $quantity_field_columns);
		$price_field_columns_joined = $price_field_columns_joined . ',' . $quantity_field_columns_joined;
	}

	//get price-ready fields for this form and put them into array
	//price-ready fields are the following types: price, checkboxes, multiple choice, dropdown
	$query = "select 
						element_title,
						element_id,
						element_type 
					from 
						" . MF_TABLE_PREFIX . "form_elements 
				   where 
				   		form_id=? and 
				   		element_status=1 and 
				   		element_is_private=0 and 
				   		element_type in('radio','money','select','checkbox') 
			    order by 
			    		element_position asc";
	$params = array($form_id);

	$sth = mf_do_query($query, $params, $dbh);
	$price_field_array = array();
	$price_field_options_lookup = array();

	while ($row = mf_do_fetch_result($sth)) {
		$element_id = $row['element_id'];
		$price_field_array[$element_id]['element_title'] = htmlspecialchars(strip_tags($row['element_title']));
		$price_field_array[$element_id]['element_type'] = $row['element_type'];

		if ($row['element_type'] != 'money') {
			//get the choices for the field
			$sub_query = "select 
									option_id,
									`option_text` 
								from 
									" . MF_TABLE_PREFIX . "element_options 
							   where 
							   		form_id=? and 
							   		live=1 and 
							   		element_id=? 
							order by 
									`position` asc";
			$sub_params = array($form_id, $element_id);
			$sub_sth = mf_do_query($sub_query, $sub_params, $dbh);
			$i = 0;
			while ($sub_row = mf_do_fetch_result($sub_sth)) {
				$price_field_options_lookup[$element_id][$sub_row['option_id']] = htmlspecialchars($sub_row['option']);
				$i++;
			}

		}
	}

	//check the value of the price fields from the ap_form_x_review or ap_form_x table
	if ($target_table == 'review') {
		$query = "select {$price_field_columns_joined} from " . MF_TABLE_PREFIX . "form_{$form_id}_review where `session_id`=?";
	} else {
		$query = "select {$price_field_columns_joined} from " . MF_TABLE_PREFIX . "form_{$form_id} where `id`=?";
	}

	$params = array($record_id);
	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	$processed_column_name = array();

	$i = 0;
	foreach ($price_field_columns as $column_name) {
		if (!empty($row[$column_name]) && !in_array($column_name, $processed_column_name)) {
			$temp = explode('_', $column_name);
			$element_id = (int) $temp[1];
			$option_id = (int) $temp[2];

			if ($price_field_types[$column_name] == 'money') {
				$payment_items[$i]['type'] = 'money';
				$payment_items[$i]['amount'] = $row[$column_name];
				$payment_items[$i]['title'] = $price_field_array[$element_id]['element_title'];

				if (!empty($quantity_fields_info['element_' . $element_id])) {
					$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

					if (empty($quantity) || $quantity < 0) {
						$quantity = 0;
					}
				} else {
					$quantity = 1;
				}
				$payment_items[$i]['quantity'] = $quantity;
			} else if ($price_field_types[$column_name] == 'checkbox') {
				$payment_items[$i]['type'] = 'checkbox';
				$payment_items[$i]['amount'] = $price_values[$element_id][$option_id];
				$payment_items[$i]['title'] = $price_field_array[$element_id]['element_title'];
				$payment_items[$i]['sub_title'] = $price_field_options_lookup[$element_id][$option_id];

				if (!empty($quantity_fields_info['element_' . $element_id . '_' . $option_id])) {
					$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id . '_' . $option_id]];

					if (empty($quantity) || $quantity < 0) {
						$quantity = 0;
					}
				} else {
					$quantity = 1;
				}
				$payment_items[$i]['quantity'] = $quantity;
			} else if ($price_field_types[$column_name] == 'radio' || $price_field_types[$column_name] == 'select') { //this is dropdown or multiple choice
				$option_id = $row[$column_name];
				$payment_items[$i]['type'] = $price_field_types[$column_name];
				$payment_items[$i]['amount'] = $price_values[$element_id][$option_id];
				$payment_items[$i]['title'] = $price_field_array[$element_id]['element_title'];
				$payment_items[$i]['sub_title'] = $price_field_options_lookup[$element_id][$option_id];

				if (!empty($quantity_fields_info['element_' . $element_id])) {
					$quantity = $row['element_' . $quantity_fields_info['element_' . $element_id]];

					if (empty($quantity) || $quantity < 0) {
						$quantity = 0;
					}
				} else {
					$quantity = 1;
				}
				$payment_items[$i]['quantity'] = $quantity;
			}

			$processed_column_name[] = $column_name;
			$i++;
		}
	}

	return $payment_items;
}

//get the "hidden" status of all fields within a form page, depend on the conditions for each field
function mf_get_hidden_elements($dbh, $form_id, $page_number, $user_input, $options = array())
{

	//get all fields within current page which has has conditions
	$query = "SELECT 
						A.element_id 
					FROM 
						" . MF_TABLE_PREFIX . "form_elements A LEFT JOIN " . MF_TABLE_PREFIX . "field_logic_elements B 
					  ON 
					  	A.form_id=B.form_id and A.element_id=B.element_id
				   WHERE 
				   		A.form_id = ? and 
				   		A.element_status = 1 and 
				   		A.element_page_number = ? and 				   		
				   		B.element_id is not null
				ORDER BY 
						A.element_position asc";
	$params = array($form_id, $page_number);
	$sth = mf_do_query($query, $params, $dbh);

	$required_fields_array = array();
	while ($row = mf_do_fetch_result($sth)) {
		$required_fields_array[] = $row['element_id'];
	}

	$hidden_elements_status = array();

	//loop through each field and check for the conditions
	if (!empty($required_fields_array)) {
		foreach ($required_fields_array as $element_id) {
			$current_element_conditions_status = array();

			$query = "select rule_show_hide,rule_all_any from " . MF_TABLE_PREFIX . "field_logic_elements where form_id = ? and element_id = ?";
			$params = array($form_id, $element_id);
			$sth = mf_do_query($query, $params, $dbh);
			$row = mf_do_fetch_result($sth);

			$rule_show_hide = $row['rule_show_hide'];
			$rule_all_any = $row['rule_all_any'];

			//get all conditions for current field
			$query = "SELECT 
						A.target_element_id,
						A.element_name,
						A.rule_condition,
						A.rule_keyword,
						(select 
							   B.element_page_number 
						   from 
						   	   " . MF_TABLE_PREFIX . "form_elements B 
						  where 
						  		form_id=A.form_id and 
						  		element_id=trim(leading 'element_' from substring_index(A.element_name,'_',2))
						) condition_element_page_number,
						(select 
							   C.element_type 
						   from 
						   	   " . MF_TABLE_PREFIX . "form_elements C 
						  where 
						  		form_id=A.form_id and 
						  		element_id=trim(leading 'element_' from substring_index(A.element_name,'_',2))
						) condition_element_type
					FROM 
						" . MF_TABLE_PREFIX . "field_logic_conditions A 
				   WHERE
						A.form_id = ? and A.target_element_id = ?";
			$params = array($form_id, $element_id);
			$sth = mf_do_query($query, $params, $dbh);

			$i = 0;
			$logic_conditions_array = array();

			while ($row = mf_do_fetch_result($sth)) {
				$logic_conditions_array[$i]['element_name'] = $row['element_name'];
				$logic_conditions_array[$i]['element_type'] = $row['condition_element_type'];
				$logic_conditions_array[$i]['rule_condition'] = $row['rule_condition'];
				$logic_conditions_array[$i]['rule_keyword'] = $row['rule_keyword'];
				$logic_conditions_array[$i]['element_page_number'] = (int) $row['condition_element_page_number'];
				$i++;
			}

			//loop through each condition which is not coming from the current page
			foreach ($logic_conditions_array as $value) {

				if ($value['element_page_number'] == $page_number) {
					continue;
				}

				$condition_params = array();
				$condition_params['form_id'] = $form_id;
				$condition_params['element_name'] = $value['element_name'];
				$condition_params['rule_condition'] = $value['rule_condition'];
				$condition_params['rule_keyword'] = $value['rule_keyword'];

				//this block is being used by mf_send_notification()
				if ($options['use_main_table'] == true) {
					$condition_params['use_main_table'] = true;
					$condition_params['entry_id'] = $options['entry_id'];
				}

				$current_element_conditions_status[] = mf_get_condition_status_from_table($dbh, $condition_params);
			}

			//loop through each condition which is coming from the current page
			foreach ($logic_conditions_array as $value) {

				if ($value['element_page_number'] != $page_number) {
					continue;
				}

				$condition_params = array();
				$condition_params['form_id'] = $form_id;
				$condition_params['element_name'] = $value['element_name'];
				$condition_params['rule_condition'] = $value['rule_condition'];
				$condition_params['rule_keyword'] = $value['rule_keyword'];

				//this block is being used by mf_send_notification()
				if ($options['use_main_table'] == true) {
					$condition_params['use_main_table'] = true;
					$condition_params['entry_id'] = $options['entry_id'];
				}

				$current_element_conditions_status[] = mf_get_condition_status_from_input($dbh, $condition_params, $user_input);
			}

			//decide the status of the current element_id based on all conditions
			//any field which is hidden due to conditions, should have 'is_hidden' being set to 1
			if ($rule_all_any == 'all') {
				if (in_array(false, $current_element_conditions_status)) {
					$all_conditions_status = false;
				} else {
					$all_conditions_status = true;
				}
			} else if ($rule_all_any == 'any') {
				if (in_array(true, $current_element_conditions_status)) {
					$all_conditions_status = true;
				} else {
					$all_conditions_status = false;
				}
			}

			if ($rule_show_hide == 'show') {
				if ($all_conditions_status === true) {
					$element_status = true; //show
				} else {
					$element_status = false; //hide
				}
			} else if ($rule_show_hide == 'hide') {
				if ($all_conditions_status === true) {
					$element_status = false; //hide
				} else {
					$element_status = true; //show
				}
			}

			if ($element_status === true) {
				$hidden_elements_status[$element_id] = 0; //the field is not hidden
			} else {
				$hidden_elements_status[$element_id] = 1; //the field is hidden
			}

		} //end foreach required fields	
	}

	return $hidden_elements_status;
}

//get the redirect URL from logic rules of a particular form
function mf_get_logic_success_redirect_url($dbh, $form_id, $entry_id)
{
	$logic_redirect_url = '';

	//get all the rules from ap_success_logic_options table that has success_type = redirect
	$query = "SELECT 
								rule_id,
								rule_all_any,
								redirect_url  
					FROM 
						" . MF_TABLE_PREFIX . "success_logic_options 
				   WHERE 
						form_id = ? and success_type = 'redirect' 
				ORDER BY 
						rule_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query, $params, $dbh);

	$success_logic_array = array();
	$i = 0;
	while ($row = mf_do_fetch_result($sth)) {
		$success_logic_array[$i]['rule_id'] = $row['rule_id'];
		$success_logic_array[$i]['rule_all_any'] = $row['rule_all_any'];
		$success_logic_array[$i]['redirect_url'] = $row['redirect_url'];
		$i++;
	}

	//evaluate the condition for each rule
	//if the condition true, get the success message
	if (!empty($success_logic_array)) {
		foreach ($success_logic_array as $value) {
			$target_rule_id = $value['rule_id'];
			$rule_all_any = $value['rule_all_any'];

			$current_rule_conditions_status = array();

			$query = "SELECT 
								element_name,
								rule_condition,
								rule_keyword 
							FROM 
								" . MF_TABLE_PREFIX . "success_logic_conditions 
						   WHERE 
						   		form_id = ? AND target_rule_id = ?";
			$params = array($form_id, $target_rule_id);

			$sth = mf_do_query($query, $params, $dbh);
			while ($row = mf_do_fetch_result($sth)) {

				$condition_params = array();
				$condition_params['form_id'] = $form_id;
				$condition_params['element_name'] = $row['element_name'];
				$condition_params['rule_condition'] = $row['rule_condition'];
				$condition_params['rule_keyword'] = $row['rule_keyword'];
				$condition_params['use_main_table'] = true;
				$condition_params['entry_id'] = $entry_id;

				$current_rule_conditions_status[] = mf_get_condition_status_from_table($dbh, $condition_params);
			}

			if ($rule_all_any == 'all') {
				if (in_array(false, $current_rule_conditions_status)) {
					$all_conditions_status = false;
				} else {
					$all_conditions_status = true;
				}
			} else if ($rule_all_any == 'any') {
				if (in_array(true, $current_rule_conditions_status)) {
					$all_conditions_status = true;
				} else {
					$all_conditions_status = false;
				}
			}

			if ($all_conditions_status === true) {
				$logic_redirect_url = $value['redirect_url'];
				break;
			}
		} //end foreach $success_logic_array
	} //end !empty success_logic_array

	//parse redirect URL for any template variables
	if (!empty($logic_redirect_url)) {
		$logic_redirect_url = mf_parse_template_variables($dbh, $form_id, $entry_id, $logic_redirect_url);
		$logic_redirect_url = str_replace(array('&amp;', '&#039;', '&quot;'), array('&', '%27', '%22'), htmlentities($logic_redirect_url, ENT_QUOTES));
	}

	return $logic_redirect_url;
}
?>