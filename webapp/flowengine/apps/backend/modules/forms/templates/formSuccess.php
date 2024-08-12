<?php
use_helper("I18N");
error_reporting(-1);

$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require ($prefix_folder . 'includes/init.php');

require ($prefix_folder . 'includes/db-core.php');
require ($prefix_folder . 'includes/helper-functions.php');
require ($prefix_folder . 'includes/check-session.php');

require ($prefix_folder . 'includes/language.php');
require ($prefix_folder . 'includes/view-functions_backend.php');
require ($prefix_folder . 'includes/users-functions.php');


$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

$form_id = (int) trim($_REQUEST['id']);
$unlock_hash = trim($_REQUEST['unlock']);

$is_new_form = false;

//check the form_id
//if blank or zero, create a new form first, otherwise load the form
if (empty($form_id)) {
	$is_new_form = true;
	//insert into ap_forms table and set the status to draft
	//set the status within 'form_active' field
	//form_active: 0 - Inactive / Disabled temporarily
	//form_active: 1 - Active
	//form_active: 2 - Draft
	//form_active: 9 - Deleted

	//check user privileges, is this user has privilege to create new form?
	if (empty($_SESSION['mf_user_privileges']['priv_new_forms'])) {
		$_SESSION['MF_DENIED'] = "You don't have permission to create new forms.";

		$ssl_suffix = mf_get_ssl_suffix();
		header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/restricted.php");
		exit;
	}


	//generate random form_id number, based on existing value
	$query = "select max(form_id) max_form_id from " . MF_TABLE_PREFIX . "forms";
	$params = array();

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	if (empty($row['max_form_id'])) {
		$last_form_id = 10000;
	} else {
		$last_form_id = $row['max_form_id'];
	}

	$form_id = $last_form_id + rand(100, 1000);

	//insert into ap_permissions table, so that this user can add fields
	$query = "insert into " . MF_TABLE_PREFIX . "permissions(form_id,user_id,edit_form,edit_entries,view_entries) values(?,?,1,1,1)";
	$params = array($form_id, $_SESSION['mf_user_id']);
	mf_do_query($query, $params, $dbh);

	$default_form_captcha_type = 'r';
	if (!empty($mf_settings['recaptcha_site_key']) && !empty($mf_settings['recaptcha_secret_key'])) {
		//if recaptcha keys exist already, use recaptcha V2 as default for new forms
		$default_form_captcha_type = 'n';
	}

	$query = "INSERT INTO `" . MF_TABLE_PREFIX . "forms` (
							form_id,
							form_name,
							form_description,
							form_redirect,
							form_redirect_enable,
							form_active,
							form_success_message,
							form_password,
							form_frame_height,
							form_unique_ip,
							form_captcha,
							form_captcha_type,
							form_review,
							form_label_alignment,
							form_resume_enable,
							form_limit_enable,
							form_limit,
							form_language,
							form_schedule_enable,
							form_schedule_start_date,
							form_schedule_end_date,
							form_schedule_start_hour,
							form_schedule_end_hour,
							form_lastpage_title,
							form_submit_primary_text,
							form_submit_secondary_text,
							form_submit_primary_img,
							form_submit_secondary_img,
							form_submit_use_image,
							form_page_total,
							form_pagination_type,
							form_review_primary_text,
							form_review_secondary_text,
							form_review_primary_img,
							form_review_secondary_img,
							form_review_use_image,
							form_review_title,
							form_review_description,
							form_custom_script_enable,
							form_custom_script_url,
							form_code,
							form_type,
							form_group,
							form_stage,
							form_department,
							form_department_stage,
							form_idn
							)
					VALUES (?,
							'Untitled Form',
							'This is your form description. Click here to edit.',
							'',
							0,
							2,
							'Success! Your submission has been saved!',
							'',
							0,
							0,
							0,
							'{$default_form_captcha_type}',
							0,
							'top_label',
							0,
							0,
							0,
							'english',
							0,
							'',
							'',
							'',
							'',
							'Untitled Page',
							'Submit',
							'Previous',
							'',
							'',
							0,
							1,
							'steps',
							'Submit',
							'Previous',
							'',
							'',
							0,
							'Review Your Entry',
							'Please review your entry below. Click Submit button to finish.',
							0,
							'',
							'',
							2,
							'',
							'',
							'',
							'',
							''
						   );";
	mf_do_query($query, array($form_id), $dbh);
} else {
	//check permission, is the user allowed to access this page?
	if (empty($_SESSION['mf_user_privileges']['priv_administer'])) {
		$user_perms = mf_get_user_permissions($dbh, $form_id, $_SESSION['mf_user_id']);

		//this page need edit_form permission
		if (empty($user_perms['edit_form'])) {
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			$ssl_suffix = mf_get_ssl_suffix();
			header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/restricted.php");
			exit;
		}
	}

	$is_form_locked = false;

	//get lock status for this form
	$query = "select lock_date from " . MF_TABLE_PREFIX . "form_locks where form_id = ? and user_id <> ?";
	$params = array($form_id, $_SESSION['mf_user_id']);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	if (!empty($row['lock_date'])) {
		$lock_date = strtotime($row['lock_date']);
		$current_date = date(time());

		$seconds_diff = $current_date - $lock_date;
		$lock_expiry_time = 60 * 60; //1 hour expiry

		//if there is a lock and the lock hasn't expired yet
		if ($seconds_diff < $lock_expiry_time) {
			$is_form_locked = true;
		}
	}

	//if the form is locked and no unlock key, redirect to warning page
	if ($is_form_locked === true && empty($unlock_hash)) {
		$ssl_suffix = mf_get_ssl_suffix();

		header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/form_locked.php?id=" . $form_id);
		exit;
	}

	//if this is an existing form, delete the previous unsaved form fields
	$query = "DELETE FROM `" . MF_TABLE_PREFIX . "form_elements` where form_id = ? AND element_status='2'";
	$params = array($form_id);
	mf_do_query($query, $params, $dbh);

	//the ap_element_options table has "live" column, which has 3 possible values:
	// 0 - the option is being deleted
	// 1 - the option is active
	// 2 - the option is currently being drafted, not being saved yet and will be deleted by edit_form.php if the form is being edited the next time
	$query = "DELETE FROM `" . MF_TABLE_PREFIX . "element_options` where form_id = ? AND live='2'";
	$params = array($form_id);
	mf_do_query($query, $params, $dbh);

	//lock this form, to prevent other user editing the same form at the same time
	$query = "delete from " . MF_TABLE_PREFIX . "form_locks where form_id=?";
	$params = array($form_id);
	mf_do_query($query, $params, $dbh);


	$new_lock_date = date("Y-m-d H:i:s");
	$query = "insert into " . MF_TABLE_PREFIX . "form_locks(form_id,user_id,lock_date) values(?,?,?)";
	$params = array($form_id, $_SESSION['mf_user_id'], $new_lock_date);
	mf_do_query($query, $params, $dbh);
}
//get the HTML markup of the form
$markup = mf_display_raw_form($dbh, $form_id);

//get the properties for each form field
//get form data
$query = "select
					 form_name,
					 form_name_hide,
					 form_active,
					 form_description,
					 form_redirect,
					 form_redirect_enable,
					 form_success_message,
					 form_password,
					 form_unique_ip,
					 form_unique_ip_maxcount,
					 form_unique_ip_period,
					 form_captcha,
					 form_captcha_type,
					 form_review,
					 form_resume_enable,
					 form_resume_subject,
					 form_resume_content,
					 form_resume_from_name,
					 form_resume_from_email_address,
					 form_limit_enable,
					 form_limit,
					 form_language,
					 form_frame_height,
					 form_label_alignment,
					 form_lastpage_title,
					 form_schedule_enable,
					 form_schedule_start_date,
					 form_schedule_end_date,
					 form_schedule_start_hour,
					 form_schedule_end_hour,
					 form_submit_primary_text,
					 form_submit_secondary_text,
					 form_submit_primary_img,
					 form_submit_secondary_img,
					 form_submit_use_image,
					 form_page_total,
					 form_pagination_type,
					 form_review_primary_text,
					 form_review_secondary_text,
					 form_review_primary_img,
					 form_review_secondary_img,
					 form_review_use_image,
					 form_review_title,
					 form_review_description,
					 form_custom_script_enable,
					 form_custom_script_url,
					 form_code,
					 form_idn,
					 form_type,
					 form_stage,
					 form_group,
					 form_department,
					 form_department_stage
			     from
			     	 " . MF_TABLE_PREFIX . "forms
			    where
			    	 form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);

$form = new stdClass();
if (!empty($row)) {
	$form->id = $form_id;
	$form->name = $row['form_name'];
	$form->name_hide = (int) $row['form_name_hide'];
	$form->active = (int) $row['form_active'];
	$form->description = $row['form_description'];
	$form->redirect = $row['form_redirect'];
	$form->redirect_enable = (int) $row['form_redirect_enable'];
	$form->success_message = $row['form_success_message'];
	$form->password = $row['form_password'];
	$form->frame_height = $row['form_frame_height'];
	$form->unique_ip = (int) $row['form_unique_ip'];
	$form->unique_ip_maxcount = (int) $row['form_unique_ip_maxcount'];
	$form->unique_ip_period = $row['form_unique_ip_period'];
	$form->captcha = (int) $row['form_captcha'];
	$form->captcha_type = $row['form_captcha_type'];
	$form->review = (int) $row['form_review'];

	if (empty($row['form_language'])) {
		$form->language = 'english';
	} else {
		$form->language = $row['form_language'];
	}
	mf_set_language($form->language);

	$form->resume_enable = (int) $row['form_resume_enable'];

	$form->resume_subject = $row['form_resume_subject'];
	if (empty($form->resume_subject)) {
		$form->resume_subject = $mf_lang['resume_email_subject'];
	}

	$form->resume_content = $row['form_resume_content'];
	if (empty($form->resume_content)) {
		$form->resume_content = $mf_lang['resume_email_content'];
	}

	$form->resume_from_name = $row['form_resume_from_name'];
	if (empty($form->resume_from_name)) {
		$form->resume_from_name = html_entity_decode($mf_settings['default_from_name'], ENT_QUOTES);
	}

	$form->resume_from_email_address = $row['form_resume_from_email_address'];
	if (empty($form->resume_from_email_address)) {
		$form->resume_from_email_address = $mf_settings['default_from_email'];
	}

	$form->limit_enable = (int) $row['form_limit_enable'];
	$form->limit = (int) $row['form_limit'];
	$form->label_alignment = $row['form_label_alignment'];
	$form->schedule_enable = (int) $row['form_schedule_enable'];


	$form->schedule_start_date = $row['form_schedule_start_date'];
	if (!empty($row['form_schedule_start_hour'])) {
		$form->schedule_start_hour = date('h:i:a', strtotime($row['form_schedule_start_hour']));
	} else {
		$form->schedule_start_hour = '';
	}
	$form->schedule_end_date = $row['form_schedule_end_date'];
	if (!empty($row['form_schedule_end_hour'])) {
		$form->schedule_end_hour = date('h:i:a', strtotime($row['form_schedule_end_hour']));
	} else {
		$form->schedule_end_hour = '';
	}
	$form_lastpage_title = $row['form_lastpage_title'];
	$form_submit_primary_text = $row['form_submit_primary_text'];
	$form_submit_secondary_text = $row['form_submit_secondary_text'];
	$form_submit_primary_img = $row['form_submit_primary_img'];
	$form_submit_secondary_img = $row['form_submit_secondary_img'];
	$form_submit_use_image = (int) $row['form_submit_use_image'];
	$form->page_total = (int) $row['form_page_total'];
	$form->pagination_type = $row['form_pagination_type'];

	$form->review_primary_text = $row['form_review_primary_text'];
	$form->review_secondary_text = $row['form_review_secondary_text'];
	$form->review_primary_img = $row['form_review_primary_img'];
	$form->review_secondary_img = $row['form_review_secondary_img'];
	$form->review_use_image = (int) $row['form_review_use_image'];
	$form->review_title = $row['form_review_title'];
	$form->review_description = $row['form_review_description'];
	$form->custom_script_enable = (int) $row['form_custom_script_enable'];
	$form->custom_script_url = $row['form_custom_script_url'];

	$form->idn = $row['form_idn'];
	$form->code = $row['form_code'];
	$form->type = $row['form_type'];
	$form->stage = $row['form_stage'];
	$form->group = $row['form_group'];
	$form->department = $row['form_department'];
	$form->department_stage = $row['form_department_stage'];
}

//get element options first and store it into array
$query = "select
					element_id,
					option_id,
					`position`,
					`option_text`,
					option_is_default
			    from
			    	" . MF_TABLE_PREFIX . "element_options
			   where
			   		form_id = ? and live=1
			order by
					element_id asc,`position` asc";
$params = array($form_id);
$sth = mf_do_query($query, $params, $dbh);

while ($row = mf_do_fetch_result($sth)) {
	$element_id = $row['element_id'];
	$option_id = $row['option_id'];
	$options_lookup[$element_id][$option_id]['position'] = $row['position'];
	$options_lookup[$element_id][$option_id]['option'] = $row['option_text'];
	$options_lookup[$element_id][$option_id]['option_is_default'] = $row['option_is_default'];
}

//get the last option id for each options and store it into array
//we need it when the user adding a new option, so that we could assign the last option id + 1
$query = "select
					element_id,
					max(option_id) as last_option_id
			    from
			    	" . MF_TABLE_PREFIX . "element_options
			   where
			   		form_id = ?
			group by
					element_id";
$params = array($form_id);
$sth = mf_do_query($query, $params, $dbh);

while ($row = mf_do_fetch_result($sth)) {
	$element_id = $row['element_id'];
	$last_option_id_lookup[$element_id] = $row['last_option_id'];
}


//get elements data
$element = array();
$query = "select
					element_id,
					element_title,
					element_guidelines,
					element_size,
					element_is_required,
					element_is_unique,
					element_is_readonly,
					element_is_private,
					element_type,
					element_position,
					element_default_value,
					element_enable_placeholder,
					element_constraint,
					element_css_class,
					element_range_min,
					element_range_max,
					element_range_limit_by,
					element_choice_columns,
					element_choice_has_other,
					element_choice_other_label,
					element_choice_limit_rule,
					element_choice_limit_qty,
					element_choice_limit_range_min,
					element_choice_limit_range_max,
					element_choice_max_entry,
					element_time_showsecond,
					element_time_24hour,
					element_address_hideline2,
					element_address_us_only,
					element_date_enable_range,
					element_date_range_min,
					element_date_range_max,
					element_date_enable_selection_limit,
					element_date_selection_max,
					element_date_disable_past_future,
					element_date_past_future,
					element_date_disable_weekend,
					element_date_disable_specific,
					element_date_disabled_list,
					element_file_enable_type_limit,
					element_file_block_or_allow,
					element_file_type_list,
					element_file_as_attachment,
					element_file_enable_advance,
					element_file_auto_upload,
					element_file_enable_multi_upload,
					element_file_max_selection,
					element_file_enable_size_limit,
					element_file_size_max,
					element_submit_use_image,
					element_submit_primary_text,
					element_submit_secondary_text,
					element_submit_primary_img,
					element_submit_secondary_img,
					element_page_title,
					element_matrix_allow_multiselect,
					element_matrix_parent_id,
					element_section_display_in_email,
					element_section_enable_scroll,
					element_number_enable_quantity,
					element_number_quantity_link,
					element_text_default_type,
					element_text_default_length,
					element_text_default_random_type,
					element_text_default_prefix,
					element_text_default_case,
					element_option_query,
					element_field_name,
					element_field_value,
					element_field_error_message,
					element_remote_username,
					element_remote_password,
					element_remote_server_field,
					element_remote_value,
					element_price_class,
					element_mark_file_with_qr_code,
					element_file_qr_all_pages,
					element_file_qr_page_position,
					element_file_qr_users,
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
					element_existing_form,
					element_existing_stage,
					element_select_options
				from
					" . MF_TABLE_PREFIX . "form_elements
			   where
			   		form_id = ? and element_status='1'
			order by
					element_position asc";
$params = array($form_id);
$sth = mf_do_query($query, $params, $dbh);

$j = 0;
while ($row = mf_do_fetch_result($sth)) {
	$element_id = $row['element_id'];

	//lookup element options first
	$option_id_array = array();
	$element_options = array();

	if (!empty($options_lookup[$element_id])) {

		$i = 1;
		foreach ($options_lookup[$element_id] as $option_id => $data) {
			$element_options[$option_id] = new stdClass();
			$element_options[$option_id]->position = $i;
			$element_options[$option_id]->option = $data['option'];
			$element_options[$option_id]->is_default = $data['option_is_default'];
			$element_options[$option_id]->is_db_live = 1;

			$option_id_array[$element_id][$i] = $option_id;

			$i++;
		}
	}


	//populate elements
	$element[$j] = new stdClass();
	$element[$j]->title = $row['element_title'];
	$element[$j]->guidelines = $row['element_guidelines'];
	$element[$j]->size = $row['element_size'];
	$element[$j]->is_required = $row['element_is_required'];
	$element[$j]->is_unique = $row['element_is_unique'];
	$element[$j]->is_readonly = $row['element_is_readonly'];
	$element[$j]->is_private = $row['element_is_private'];
	$element[$j]->type = $row['element_type'];
	$element[$j]->position = $row['element_position'];
	$element[$j]->id = $row['element_id'];
	$element[$j]->is_db_live = 1;

	$element[$j]->default_value = $row['element_default_value'];
	$element[$j]->enable_placeholder = (int) $row['element_enable_placeholder'];
	$element[$j]->constraint = $row['element_constraint'];
	$element[$j]->css_class = $row['element_css_class'];
	$element[$j]->range_min = (int) $row['element_range_min'];
	$element[$j]->range_max = (int) $row['element_range_max'];
	$element[$j]->range_limit_by = $row['element_range_limit_by'];
	$element[$j]->choice_columns = (int) $row['element_choice_columns'];
	$element[$j]->choice_has_other = (int) $row['element_choice_has_other'];
	$element[$j]->choice_other_label = $row['element_choice_other_label'];
	$element[$j]->choice_limit_rule = $row['element_choice_limit_rule'];
	$element[$j]->choice_limit_qty = (int) $row['element_choice_limit_qty'];
	$element[$j]->choice_limit_range_min = (int) $row['element_choice_limit_range_min'];
	$element[$j]->choice_limit_range_max = (int) $row['element_choice_limit_range_max'];
	$element[$j]->choice_max_entry = (int) $row['element_choice_max_entry'];
	$element[$j]->time_showsecond = (int) $row['element_time_showsecond'];
	$element[$j]->time_24hour = (int) $row['element_time_24hour'];
	$element[$j]->address_hideline2 = (int) $row['element_address_hideline2'];
	$element[$j]->address_us_only = (int) $row['element_address_us_only'];
	$element[$j]->date_enable_range = (int) $row['element_date_enable_range'];
	$element[$j]->date_range_min = $row['element_date_range_min'];
	$element[$j]->date_range_max = $row['element_date_range_max'];
	$element[$j]->date_enable_selection_limit = (int) $row['element_date_enable_selection_limit'];
	$element[$j]->date_selection_max = (int) $row['element_date_selection_max'];
	$element[$j]->date_disable_past_future = (int) $row['element_date_disable_past_future'];
	$element[$j]->date_past_future = $row['element_date_past_future'];
	$element[$j]->date_disable_weekend = (int) $row['element_date_disable_weekend'];
	$element[$j]->date_disable_specific = (int) $row['element_date_disable_specific'];
	$element[$j]->date_disabled_list = $row['element_date_disabled_list'];
	$element[$j]->file_enable_type_limit = (int) $row['element_file_enable_type_limit'];
	$element[$j]->file_block_or_allow = $row['element_file_block_or_allow'];
	$element[$j]->file_type_list = $row['element_file_type_list'];
	$element[$j]->file_as_attachment = (int) $row['element_file_as_attachment'];
	$element[$j]->file_enable_advance = (int) $row['element_file_enable_advance'];
	$element[$j]->file_auto_upload = (int) $row['element_file_auto_upload'];
	$element[$j]->file_enable_multi_upload = (int) $row['element_file_enable_multi_upload'];
	$element[$j]->file_max_selection = (int) $row['element_file_max_selection'];
	$element[$j]->file_enable_size_limit = (int) $row['element_file_enable_size_limit'];
	$element[$j]->file_size_max = (int) $row['element_file_size_max'];
	$element[$j]->submit_use_image = (int) $row['element_submit_use_image'];
	$element[$j]->submit_primary_text = $row['element_submit_primary_text'];
	$element[$j]->submit_secondary_text = $row['element_submit_secondary_text'];
	$element[$j]->submit_primary_img = $row['element_submit_primary_img'];
	$element[$j]->submit_secondary_img = $row['element_submit_secondary_img'];
	$element[$j]->page_title = $row['element_page_title'];
	$element[$j]->matrix_allow_multiselect = (int) $row['element_matrix_allow_multiselect'];
	$element[$j]->matrix_parent_id = (int) $row['element_matrix_parent_id'];
	$element[$j]->section_display_in_email = (int) $row['element_section_display_in_email'];
	$element[$j]->section_enable_scroll = (int) $row['element_section_enable_scroll'];
	$element[$j]->number_enable_quantity = (int) $row['element_number_enable_quantity'];
	$element[$j]->number_quantity_link = $row['element_number_quantity_link'];
	$element[$j]->text_default_type = $row['element_text_default_type'];
	$element[$j]->text_default_length = (int) $row['element_text_default_length'];
	$element[$j]->text_default_random_type = $row['element_text_default_random_type'];
	$element[$j]->text_default_prefix = $row['element_text_default_prefix'];
	$element[$j]->text_default_case = $row['element_text_default_case'];

	$element[$j]->option_query = $row['element_option_query'];
	$element[$j]->field_name = $row['element_field_name'];
	$element[$j]->field_value = $row['element_field_value'];
	$element[$j]->field_error_message = $row['element_field_error_message'];
	$element[$j]->remote_username = $row['element_remote_username'];
	$element[$j]->remote_password = $row['element_remote_password'];
	$element[$j]->remote_server_field = $row['element_remote_server_field'];
	$element[$j]->remote_value = $row['element_remote_value'];
	$element[$j]->price_class = $row['element_price_class'];
	$element[$j]->mark_file_with_qr_code = $row['element_mark_file_with_qr_code']; //OTB Africa - QR Code Marking for File Authenticity Verification
	$element[$j]->file_qr_all_pages = $row['element_file_qr_all_pages']; //OTB Africa - QR Code Marking for File Authenticity Verification
	$element[$j]->file_qr_page_position = $row['element_file_qr_page_position']; //OTB Africa - QR Code Marking for File Authenticity Verification
	$element[$j]->file_qr_users = $row['element_file_qr_users']; //OTB Africa - QR Code Marking for File Authenticity Verification
	$element[$j]->notify_contact = $row['element_notify_contact']; //OTB Africa - Additional notify contacts
	$element[$j]->footprint = $row['element_footprint']; //OTB patch footprint property
	$element[$j]->plotsize = $row['element_plotsize']; //OTB patch 
	$element[$j]->actualplotratio = $row['element_actualplotratio']; //OTB patch 
	$element[$j]->permittedgroundcoverage = $row['element_permittedgroundcoverage']; //OTB patch
	$element[$j]->zone = $row['element_zone']; //OTB patch 
	$element[$j]->permitteduser = $row['element_permitteduser']; //OTB patch 
	$element[$j]->grandtotalplintharea = $row['element_grandtotalplintharea']; //OTB patch 
	$element[$j]->file_ifc = $row['element_file_ifc']; //OTB patch 
	$element[$j]->plot_no = $row['element_plot_no']; //OTB patch
	$element[$j]->block_no = $row['element_block_no']; //OTB patch
	$element[$j]->subcounty = $row['element_subcounty']; //OTB patch
	$element[$j]->owner_phone = $row['element_owner_phone'];
	$element[$j]->owner_address = $row['element_owner_address'];
	$element[$j]->owner_email = $row['element_owner_email'];
	$element[$j]->ward = $row['element_ward'];
	$element[$j]->plot_area = $row['element_plot_area'];
	$element[$j]->plot_length = $row['element_plot_length'];
	$element[$j]->plot_width = $row['element_plot_width'];
	$element[$j]->ownertype = $row['element_ownertype']; //OTB patch 
	$element[$j]->table_name = $row['element_table_name']; //OTB patch 
	$element[$j]->existing_form = $row['element_existing_form']; //OTB patch 
	$element[$j]->existing_stage = $row['element_existing_stage']; //OTB patch 
	$element[$j]->select_options = $row['element_select_options']; //OTB patch 
	if (!empty($element_options)) {
		$element[$j]->options = $element_options;
		$element[$j]->last_option_id = $last_option_id_lookup[$element_id];
	} else {
		$element[$j]->options = '';
	}

	//if the element is a matrix field and not the parent, store the data into a lookup array for later use when rendering the markup
	if ($row['element_type'] == 'matrix' && !empty($row['element_matrix_parent_id'])) {

		$parent_id = $row['element_matrix_parent_id'];
		$row_position = count($matrix_elements[$parent_id]) + 2;
		$element_id = $row['element_id'];

		$matrix_elements[$parent_id][$element_id] = new stdClass();
		$matrix_elements[$parent_id][$element_id]->is_db_live = 1;
		$matrix_elements[$parent_id][$element_id]->position = $row_position;
		$matrix_elements[$parent_id][$element_id]->row_title = $row['element_title'];

		$column_data = array();
		$col_position = 1;
		foreach ($element_options as $option_id => $value) {
			$column_data[$option_id] = new stdClass();
			$column_data[$option_id]->is_db_live = 1;
			$column_data[$option_id]->position = $col_position;
			$column_data[$option_id]->column_title = $value->option;
			$col_position++;
		}

		$matrix_elements[$parent_id][$element_id]->column_data = $column_data;

		//remove it from the main element array
		$element[$j] = array();
		unset($element[$j]);
		$j--;
	}

	$j++;
}

//if this is multipage form, add the lastpage submit property into the element list
if ($form->page_total > 1) {
	$element[$j] = new stdClass();
	$element[$j]->id = 'lastpage';
	$element[$j]->type = 'page_break';
	$element[$j]->page_title = $form_lastpage_title;
	$element[$j]->submit_primary_text = $form_submit_primary_text;
	$element[$j]->submit_secondary_text = $form_submit_secondary_text;
	$element[$j]->submit_primary_img = $form_submit_primary_img;
	$element[$j]->submit_secondary_img = $form_submit_secondary_img;
	$element[$j]->submit_use_image = $form_submit_use_image;
}


$jquery_data_code = '';

//build the json code for form fields
$all_element = array('elements' => $element);
foreach ($element as $data) {
	//if this is matrix element, attach the children data into options property and merge with current (matrix parent) options
	if ($data->type == 'matrix') {
		$matrix_elements[$data->id][$data->id] = new stdClass();
		$matrix_elements[$data->id][$data->id]->is_db_live = 1;
		$matrix_elements[$data->id][$data->id]->position = 1;
		$matrix_elements[$data->id][$data->id]->row_title = $data->title;

		$column_data = array();
		$col_position = 1;
		foreach ($data->options as $option_id => $value) {
			$column_data[$option_id] = new stdClass();
			$column_data[$option_id]->is_db_live = 1;
			$column_data[$option_id]->position = $col_position;
			$column_data[$option_id]->column_title = $value->option;
			$col_position++;
		}

		$matrix_elements[$data->id][$data->id]->column_data = $column_data;

		$temp_array = array();
		$temp_array = $matrix_elements[$data->id];

		asort($temp_array);

		$matrix_elements[$data->id] = array();
		$matrix_elements[$data->id] = $temp_array;

		$data->options = array();
		$data->options = $matrix_elements[$data->id];
	}
	$field_settings = json_encode($data);
	$jquery_data_code .= "\$('#li_{$data->id}').data('field_properties',{$field_settings});\n";
}


//build the json code for form settings
$json_form = json_encode($form);
$jquery_data_code .= "\$('#form_header').data('form_properties',{$json_form});\n";



$header_data = <<<EOT
<link type="text/css" href="/form_builder/js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link type="text/css" href="/form_builder/css/edit_form.css" rel="stylesheet" />
<link type="text/css" href="/form_builder/js/datepick/smoothness.datepick.css" rel="stylesheet" />
EOT;

$current_nav_tab = 'manage_forms';

require ($prefix_folder . 'includes/header.php');
?>

<div id="editor_loading">
	Loading... Please wait...
</div>

<div id="content">
	<div class="form_editor">
		<span id="selected_field_image" class="icon-arrow-right2 arrow-field-prop"></span>

		<?php
		echo $markup;

		?>

		<div id="bottom_bar" style="display: none">

			<div id="bottom_bar_content" class="buttons_bar">

				<a id="bottom_bar_save_form" href="#" class="bb_button bb_green" alt="Save Form" title="Save Form">
					<span class="icon-disk"></span>Save Form
				</a>

				<a id="bottom_bar_add_field" class="bb_button bb_grey" href="#" alt="Add a New Field"
					title="Add a New Field">
					<span class="icon-plus-circle"></span>Add Field
				</a>

				<div id="bottom_bar_field_action">
					<span class="icon-arrow-right2 arrow-field-prop"></span>
					<a id="bottom_bar_duplicate_field" href="#" class="bb_button bb_grey" alt="Duplicate Selected Field"
						title="Duplicate Selected Field">
						<span class="icon-copy"></span>Duplicate
					</a>

					<a id="bottom_bar_delete_field" href="#" class="bb_button bb_red" alt="Delete Selected Field"
						title="Delete Selected Field">
						<span class="icon-remove"></span>Delete
					</a>
				</div>
			</div>
			<div id="bottom_bar_loader">
				<span>
					<img src="/form_builder/images/loader.gif" width="32" height="32" />
					<span id="bottom_bar_msg">Please wait... Synching...</span>
				</span>
			</div>

		</div>
		<div id="bottom_bar_limit"></div>
		<?php if ($is_new_form) { ?>
			<div id="no_fields_notice">
				<span class="icon-arrow-right"
					style="margin-bottom: 20px;color: #529214;font-size: 50px;display: block"></span>
				<h3>Your form has no fields yet!</h3>
				<p><span style="color: #529214; font-weight: bold;">Click the buttons</span> on the right sidebar or <span
						style="color: #529214; font-weight: bold;">Drag it here</span> to add new field.</p>
			</div>
		<?php } ?>
	</div>

</div><!-- /#content -->

<div id="sidebar">
	<div id="builder_tabs">
		<ul id="builder_tabs_btn" style="display: none">
			<li id="btn_add_field"><a href="#tab_add_field">Add a Field</a></li>
			<li id="btn_field_properties"><a href="#tab_field_properties">Field Properties</a></li>
			<li id="btn_form_properties"><a href="#tab_form_properties">Form Properties</a></li>
		</ul>
		<div id="tab_add_field">
			<div id="social" class="box">
				<ul>
					<li id="btn_single_line_text" class="box">
						<a id="a_single_line_text" href="#" title="Single Line Text">
							<span class="icon-font-size icon-font"></span><span class="blabel">Single Line Text</span>
						</a>
					</li>

					<li id="btn_number" class="box">
						<a id="a_number" href="#" title="Number">
							<span class="icon-seven-segment8 icon-font"></span><span class="blabel">Number</span>
						</a>
					</li>

					<li id="btn_paragraph_text" class="box">
						<a id="a_paragraph_text" href="#" title="Paragraph Text">
							<span class="icon-paragraph-left icon-font"></span><span class="blabel">Paragraph
								Text</span>
						</a>
					</li>
					<li id="btn_checkboxes" class="box">
						<a id="a_checkboxes" href="#" title="Checkboxes">
							<span class="icon-checkbox icon-font"></span><span class="blabel">Checkboxes</span>
						</a>
					</li>

					<li id="btn_multiple_choice" class="box">
						<a id="a_multiple_choice" href="#" title="Multiple Choice">
							<span class="icon-list icon-font"></span><span class="blabel">Multiple Choice</span>
						</a>
					</li>

					<li id="btn_drop_down" class="box">
						<a id="a_drop_down" href="#" title="Drop Down">
							<span class="icon-menu icon-font"></span><span class="blabel">Drop Down</span>
						</a>
					</li>

					<li id="btn_name" class="box">
						<a id="a_name" href="#" title="Name">
							<span class="icon-user2 icon-font"></span><span class="blabel">Name</span>
						</a>
					</li>
					<li id="btn_date" class="box">
						<a id="a_date" href="#" title="Date">
							<span class="icon-calendar icon-font"></span><span class="blabel">Date</span>
						</a>
					</li>

					<li id="btn_time" class="box">
						<a id="a_time" href="#" title="Time">
							<span class="icon-alarm icon-font"></span><span class="blabel">Time</span>
						</a>
					</li>

					<li id="btn_phone" class="box">
						<a id="a_phone" href="#" title="Phone">
							<span class="icon-phone icon-font"></span><span class="blabel">Phone</span>
						</a>
					</li>

					<li id="btn_address" class="box">
						<a id="a_address" href="#" title="Address">
							<span class="icon-home icon-font"></span><span class="blabel">Address</span>
						</a>
					</li>
					<li id="btn_website" class="box">
						<a id="a_website" href="#" title="Web Site">
							<span class="icon-link icon-font"></span><span class="blabel">Web Site</span>
						</a>
					</li>

					<li id="btn_price" class="box">
						<a id="a_price" href="#" title="Price">
							<span class="icon-coins icon-font"></span><span class="blabel">Price</span>
						</a>
					</li>

					<li id="btn_email" class="box">
						<a id="a_email" href="#" title="Email">
							<span class="icon-envelope-opened icon-font"></span><span class="blabel">Email</span>
						</a>
					</li>

					<li id="btn_matrix" class="box">
						<a id="a_matrix" href="#" title="Matrix Choice">
							<span class="icon-grid icon-font"></span><span class="blabel">Matrix Choice</span>
						</a>
					</li>
					<li id="btn_file_upload" class="box">
						<a id="a_file_upload" href="#" title="File Upload">
							<span class="icon-file-upload icon-font"></span><span class="blabel">File Upload</span>
						</a>
					</li>
					<li id="btn_section_break" class="box">
						<a id="a_section_break" href="#" title="Section Break">
							<span class="icon-marker icon-font"></span><span class="blabel">Section Break</span>
						</a>
					</li>
					<li id="btn_page_break" class="box">
						<a id="a_page_break" href="#" title="Page Break">
							<span class="icon-file-plus icon-font"></span><span class="blabel">Page Break</span>
						</a>
					</li>
					<li id="btn_signature" class="box">
						<a id="a_signature" href="#" title="Signature">
							<span class="icon-quill icon-font"></span><span class="blabel">Signature</span>
						</a>
					</li>
					<!-- OTB patch Field for plinth area -->
					<li id="btn_plinth_area" class="box">
						<a id="a_plinth_area" href="#" title="<?php echo __('Custom Plinth Area'); ?>">
							<span class="icon-grid icon-font"></span><span class="blabel">
								<?php echo __('Custom Plinth Area'); ?></span>
						</a>
					</li>
					<li id="btn_total_plinth_area" class="box">
						<a id="a_total_plinth_area" href="#" title="<?php echo __('Total Plinth Area'); ?>">
							<span class="icon-grid icon-font"></span><span class="blabel">
								<?php echo __('Total Plinth Area'); ?></span>
						</a>
					</li>

				</ul>

				<div class="clear"></div>

			</div><!-- /#social -->
		</div>

		<div id="tab_field_properties" style="display: none">
			<div id="field_properties_pane" class="box"> <!-- Start field properties pane -->
				<form style="display: block;" id="element_properties" action="" onsubmit="return false;">

					<div id="element_inactive_msg">
						<div class="bullet_bar_top">
							<img class="left" src="/form_builder/images/bullet_green.png" />
							<img class="right" src="/form_builder/images/bullet_green.png" />
						</div>

						<span class="icon-arrow-left"
							style="margin-top: 80px;color: #529214;font-size: 50px;display: block"></span>
						<h3>Please select a field</h3>
						<p id="eim_p">Click on a field on the left to change its properties.</p>

						<div class="bullet_bar_bottom">
							<img class="left" src="/form_builder/images/bullet_green.png" />
							<img class="right" src="/form_builder/images/bullet_green.png" />
						</div>
					</div>

					<div id="element_properties_form">
						<div class="bullet_bar_top">
							<img class="left" src="/form_builder/images/bullet_green.png" />
							<img class="right" src="/form_builder/images/bullet_green.png" />
						</div>
						<div class="num" id="element_position">12</div>
						<ul id="all_properties">
							<li id="prop_element_label">
								<label class="desc" for="element_label">Field Label <img class="helpmsg"
										src="/form_builder/images/icons/help2.png" style="vertical-align: top"
										title="Field Label is one or two words placed directly above the field." />
								</label>
								<textarea id="element_label" name="element_label" class="textarea" /></textarea>
							</li>

							<li class="leftCol" id="prop_element_type">
								<label class="desc" for="element_type">
									Field Type
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Field Type detemines what kind of data can be collected by your field. After you save the form, the field type cannot be changed." />
								</label>
								<select class="select full" id="element_type" name="element_type" autocomplete="off"
									tabindex="12">
									<option value="text">Single Line Text</option>
									<option value="textarea">Paragraph Text</option>
									<option value="radio">Multiple Choice</option>
									<option value="checkbox">Checkboxes</option>
									<option value="select">Drop Down</option>
									<option value="number">Number</option>
									<option value="simple_name">Name</option>
									<option value="date">Date</option>
									<option value="time">Time</option>
									<option value="phone">Phone</option>
									<option value="money">Price</option>
									<option value="url">Web Site</option>
									<option value="email">Email</option>
									<option value="address">Address</option>
									<option value="file">File Upload</option>
									<option value="section">Section Break</option>
									<option value="matrix">Matrix Choice</option>
								</select>
							</li>

							<li class="rightCol" id="prop_element_size">
								<label class="desc" for="element_size">
									Field Size
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="This property sets the visual appearance of the field in your form. It does not limit nor increase the amount of data that can be collected by the field." />
								</label>
								<select class="select full" id="element_size" autocomplete="off" tabindex="13">
									<option value="small">Small</option>
									<option value="medium">Medium</option>
									<option value="large">Large</option>
								</select>
							</li>

							<li class="rightCol" id="prop_element_select_options">
								<label class="desc" for="element_select_options">
									Choice source
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="This property sets the source of dropdown options." />
								</label>
								<select class="select full" id="element_select_options" autocomplete="off"
									tabindex="13">
									<option value="default">Default</option>
									<option value="table">Table</option>
									<option value="query">Query</option>
									<option value="application">Application</option>
								</select>
							</li>

							<li class="rightCol" id="prop_choice_columns">
								<label class="desc" for="element_choice_columns">
									Choice Columns
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Set the number of columns being used to display the choices. Inline columns means the choices are sitting next to each other." />
								</label>
								<select class="select full" id="element_choice_columns" autocomplete="off">
									<option value="1">One Column</option>
									<option value="2">Two Columns</option>
									<option value="3">Three Columns</option>
									<option value="9">Inline</option>
								</select>
							</li>

							<li class="rightCol" id="prop_date_format">
								<label class="desc" for="field_size">
									Date Format
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="You can choose between American and European Date Formats" />
								</label>
								<select class="select full" id="date_type" autocomplete="off">
									<option id="element_date" value="date">MM / DD / YYYY</option>
									<option id="element_europe_date" value="europe_date">DD / MM / YYYY</option>
								</select>
							</li>

							<li class="rightCol" id="prop_name_format">
								<label class="desc" for="name_format">
									Name Format
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Two formats available. A normal name field, or an extended name field with title and suffix." />
								</label>
								<select class="select full" id="name_format" autocomplete="off">
									<option id="element_simple_name" value="simple_name" selected="selected">Normal
									</option>
									<option id="element_name" value="name" selected="selected">Normal + Title</option>
									<option id="element_simple_name_wmiddle" value="simple_name_wmiddle"
										selected="selected">Full</option>
									<option id="element_name_wmiddle" value="name_wmiddle">Full + Title</option>
								</select>
							</li>

							<li class="rightCol" id="prop_phone_format">
								<label class="desc" for="field_size">
									Phone Format
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="You can choose between American and International Phone Formats" />
								</label>
								<select class="select full" id="phone_format" name="phone_format" autocomplete="off">
									<option id="element_phone" value="phone">### - ### - ####</option>
									<option id="element_simple_phone" value="simple_phone">International</option>
								</select>
							</li>

							<li class="rightCol" id="prop_currency_format">
								<label class="desc" for="field_size">
									Currency Format
								</label>
								<select class="select full" id="money_format" name="money_format" autocomplete="off">
									<option id="element_money_kes" value="kes">KES - Kenya Shillings</option>
									<option id="element_money_usd" value="dollar">&#36; - Dollars</option>
									<option id="element_money_euro" value="euro">&#8364; - Euros</option>
									<option id="element_money_pound" value="pound">&#163; - Pounds Sterling</option>
									<option id="element_money_yen" value="yen">&#165; - Yen</option>
									<option id="element_money_baht" value="baht">&#3647; - Baht</option>
									<option id="element_money_forint" value="forint">&#70;&#116; - Forint</option>
									<option id="element_money_franc" value="franc">CHF - Francs</option>
									<option id="element_money_koruna" value="koruna">&#75;&#269; - Koruna</option>
									<option id="element_money_krona" value="krona">kr - Krona</option>
									<option id="element_money_pesos" value="pesos">&#36; - Pesos</option>
									<option id="element_money_rand" value="rand">R - Rand</option>
									<option id="element_money_reais" value="reais">R&#36; - Reais</option>
									<option id="element_money_ringgit" value="ringgit">RM - Ringgit</option>
									<option id="element_money_rupees" value="rupees">Rs - Rupees</option>
									<option id="element_money_zloty" value="zloty">&#122;&#322; - Złoty</option>
									<option id="element_money_riyals" value="riyals">&#65020; - Riyals</option>
								</select>
							</li>
							<li class="clear" id="prop_table_name_conf">
								<fieldset>
									<label class="desc" for="prop_table_name_conf">
										<?php echo __('Configure table'); ?>
									</label>
									<hr>
									<span>
										<label class="text" for="prop_table_name"><?php echo __('Table'); ?></label><br>
										<input id="table_name" name="table_name" class="text" value="" type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="DB table to connect to" /><br>
									</span><br>
									<span>
										<label class="text"
											for="prop_field_value"><?php echo __('Field value'); ?></label><br>
										<input id="field_value" name="field_value" class="text" value="" type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Table column for value" /><br>
									</span><br>
									<span>
										<label class="text"
											for="prop_field_name"><?php echo __('Field name'); ?></label><br>
										<input id="field_name" name="field_name" class="text" value="" type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Table column for name" /><br>
									</span><br>
								</fieldset>
							</li>
							<li class="clear" id="prop_option_query_conf">
								<fieldset>
									<label class="desc" for="prop_option_query_conf">
										<?php echo __('Query options'); ?>
									</label>
									<hr>
									<span>
										<label class="text"
											for="prop_option_query"><?php echo __('Query'); ?></label><br>
										<textarea id="option_query" name="option_query"></textarea>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="DB table to connect to" /><br>
									</span><br>
									<span>
										<label class="text"
											for="prop_field_value_option_query"><?php echo __('Field value'); ?></label><br>
										<input id="field_value_option_query" name="field_value_option_query"
											class="text" value="" type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Table column for value" /><br>
									</span><br>
									<span>
										<label class="text"
											for="prop_field_name_option_query"><?php echo __('Field name'); ?></label><br>
										<input id="field_name_option_query" name="field_name_option_query" class="text"
											value="" type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Table column for name" /><br>
									</span><br>
								</fieldset>
							</li>
							<li class="clear" id="prop_application_conf">
								<fieldset>
									<label class="desc" for="prop_application_conf">
										<?php echo __('Applications options'); ?>
									</label>
									<hr>
									<span>
										<label class="text"
											for="prop_existing_form"><?php echo __('Existing form'); ?></label><br>
										<select class="select full" id="existing_form" name="existing_form"
											autocomplete="off">
											<?php
											$q = Doctrine_Query::create()
												->from('ApForms f')
												->where('f.form_active =? AND f.form_type =?', [1, 1]);
											$app_forms = $q->execute();
											foreach ($app_forms as $app_form):
												?>
												<option value="<?php echo $app_form->getFormId() ?>">
													<?php echo $app_form->getFormName() ?>
												</option>
											<?php endforeach; ?>
										</select>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Submission form" /><br>
									</span><br>
									<span>
										<label class="text"
											for="prop_existing_stage"><?php echo __('Existing stage'); ?></label><br>
										<select class="select full" id="existing_stage" name="existing_stage"
											autocomplete="off">
											<?php
											$q = Doctrine_Query::create()
												->from('SubMenus m')
												->where('m.deleted =?', 0);
											$existing_stages = $q->execute();
											foreach ($existing_stages as $existing_stage):
												?>
												<option value="<?php echo $existing_stage->getId() ?>">
													<?php echo $existing_stage->getTitle() ?>
												</option>
											<?php endforeach; ?>
										</select>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Existing stage" /><br>
									</span><br>
								</fieldset>
							</li>
							<li class="clear" id="prop_price_class">
								<label class="desc" for="field_size">
									<?php echo __('Apply Price Calculator'); ?>
								</label>
								<select class="select full" id="price_class" name="price_class" autocomplete="off">
									<option>None</option>
									<option id="element_price_stamp" value="dataflow_dynamic">
										<?php echo __('Dataflow (Percentages)'); ?>
									</option>
									<option id="element_price_stamp" value="dataflow_fixed">
										<?php echo __('Dataflow (Fixed)'); ?>
									</option>
									<option id="element_price_stamp" value="ag_stamp_duty">
										<?php echo __('AG Stamp Duty'); ?>
									</option>
									<option id="element_price_stamp" value="lands_stamp_duty">
										<?php echo __('Lands Stamp Duty 4%'); ?>
									</option>
									<option id="element_price_stamp" value="lands_stamp_duty2">
										<?php echo __('Lands Stamp Duty 2%'); ?>
									</option>
								</select>
								<hr>
								<span>
									<label class="text"
										for="prop_text_as_password"><?php echo __('Remote Field Containing Dataset'); ?></label><br>
									<select id="prop_remote_value_pr" name="prop_remote_value_pr">
										<?php
										$appform = $_GET['id'];

										$q = Doctrine_Query::create()
											->from('apFormElements a')
											->where('a.form_id = ?', $appform)
											->andWhere('a.element_status = ?', 1)
											->orderBy('a.element_position ASC');
										$elements = $q->execute();

										foreach ($elements as $element) {
											echo "<option value='" . $element->getElementId() . "'>" . $element->getElementTitle() . "</option>";
										}
										?>
									</select>
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Select the remote field that will pull a dataset containing a value you want to use in the remote url e.g. get remote activity code {c44} from dataset" /><br>
								</span><br>

								<span>
									<label class="text"
										for="prop_text_as_password"><?php echo __('Remote Server URL'); ?></label><br>
									<input id="prop_remote_url_pr" name="prop_remote_url_pr" class="text" value=""
										type="text">
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="The remote URL used to check the value with" /><br>
								</span><br>

								<span>
									<label class="text"
										for="prop_text_as_password"><?php echo __('Remote Server Username'); ?></label><br>
									<input id="prop_remote_username_pr" name="prop_remote_username_pr" class="text"
										value="" type="text">
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="The remote username if authentication is required" /><br>
								</span><br>

								<span>
									<label class="text"
										for="prop_text_as_password"><?php echo __('Remote Server Password'); ?></label><br>
									<input id="prop_remote_password_pr" name="prop_remote_password_pr" class="text"
										value="" type="password">
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="The remote password if authentication is required" /><br>
								</span><br>

								<span>
									<label class="text"
										for="prop_text_as_password"><?php echo __('Remote Server Field'); ?></label><br>
									<input id="prop_remote_server_field_pr" name="prop_remote_server_field_pr"
										class="text" value="" type="text">
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="The field in the remote dataset containing the total amount to add to the price" /><br>
								</span><br>
							</li>

							<li class="clear" id="prop_choices">
								<fieldset class="choices">
									<legend>
										Choices
										<img class="helpmsg" src="/form_builder/images/icons/help3.png"
											style="vertical-align: top; "
											title="Use the plus and minus buttons to add and delete choices. Click on the choice to make it the default selection." />
									</legend>
									<ul id="element_choices">
										<li>
											<input type="radio" title="Select this choice as the default."
												class="choices_default" name="choices_default" />
											<input type="text" value="First option" autocomplete="off" class="text"
												id="choice_1" />
											<img title="Add" alt="Add" src="/form_builder/images/icons/add.png"
												style="vertical-align: middle">
											<img title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png"
												style="vertical-align: middle">
										</li>
									</ul>

									<div style="text-align: center;padding-top: 5px;padding-bottom: 10px">
										<img src="/form_builder/images/icons/page_go.png" style="vertical-align: top" />
										<a href="#" id="bulk_import_choices">bulk insert choices</a>
									</div>

								</fieldset>

							</li>

							<li class="clear" id="prop_choices_other">
								<fieldset class="choices">
									<legend>
										Choices Options
									</legend>

									<span>
										<input id="prop_choices_other_checkbox" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_choices_other_checkbox">Allow Client to Add
											Other Choice</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enable this option if you would like to allow your client to write his own answer if none of the other choices are applicable. A text field will be added to the last choice. Enter the label below this checkbox." />
										<div style="margin-bottom: 5px;margin-top: 3px;padding-left: 20px">
											<img src="/form_builder/images/icons/tag_green.png"
												style="vertical-align: middle"> <input id="prop_other_choices_label"
												style="width: 220px" class="text" value="" size="25" type="text">
										</div>
										<span id="prop_choices_randomize_span" style="display: none">
											<input id="prop_choices_randomize" class="checkbox" value=""
												type="checkbox">
											<label class="choice" for="prop_choices_randomize">Randomize Choices</label>
											<img class="helpmsg" src="/form_builder/images/icons/help2.png"
												style="vertical-align: top"
												title="Enable this option if you would like the choices to be shuffled around each time the form being displayed." />
										</span>
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_matrix_row">
								<fieldset class="choices">
									<legend>
										Rows
										<img class="helpmsg" src="/form_builder/images/icons/help3.png"
											style="vertical-align: top; "
											title="Enter rows labels here. Use the plus and minus buttons to add and delete matrix row. " />
									</legend>
									<ul id="element_matrix_row">
										<li>
											<input type="text" value="First Question" autocomplete="off" class="text"
												id="matrixrow_1" />
											<img title="Add" alt="Add" src="/form_builder/images/icons/add.png"
												style="vertical-align: middle">
											<img title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png"
												style="vertical-align: middle">
										</li>
									</ul>

									<div style="text-align: center;padding-top: 5px;padding-bottom: 10px">
										<img src="/form_builder/images/icons/page_go.png" style="vertical-align: top" />
										<a href="#" id="bulk_import_matrix_row">bulk insert rows</a>
									</div>

								</fieldset>

							</li>
							<li class="clear" id="prop_matrix_column">
								<fieldset class="choices">
									<legend>
										Columns
										<img class="helpmsg" src="/form_builder/images/icons/help3.png"
											style="vertical-align: top; "
											title="Enter column labels here. Use the plus and minus buttons to add and delete matrix column. " />
									</legend>
									<ul id="element_matrix_column">
										<li>
											<input type="text" value="First Question" autocomplete="off" class="text"
												id="matrixcolumn_1" />
											<img title="Add" alt="Add" src="/form_builder/images/icons/add.png"
												style="vertical-align: middle">
											<img title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png"
												style="vertical-align: middle">
										</li>
									</ul>

									<div style="text-align: center;padding-top: 5px;padding-bottom: 10px">
										<img src="/form_builder/images/icons/page_go.png" style="vertical-align: top" />
										<a href="#" id="bulk_import_matrix_column">bulk insert columns</a>
									</div>

								</fieldset>

							</li>
							<li id="prop_breaker"></li>
							<li class="leftCol" id="prop_options">
								<fieldset class="fieldset">
									<legend>Rules</legend>
									<span style="display: block">
										<input id="element_required" class="checkbox" value="" type="checkbox">
										<label class="choice" for="element_required">Required</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this rule will make sure that a user fills out a particular field. A message will be displayed to the user if they have not filled out the field." />
									</span>
									<span id="element_unique_span" style="display: block">
										<input id="element_unique" class="checkbox" value="" type="checkbox">
										<label class="choice" for="element_unique">No Duplicates</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this rule will verify that the data entered into this field is unique and has not been submitted previously." />
									</span>
									<span id="element_readonly_span" style="display: block">
										<input id="element_readonly" class="checkbox" value="" type="checkbox">
										<label class="choice" for="element_readonly">Read Only</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="If enabled, users won't be able to change the value of this field." />
									</span>
									<!-- OTB patch Custom Check for footprint element -->
									<span id="element_footprint_span">
										<input id="element_footprint" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_footprint"><?php echo __('Is FootPrint'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as footprint type used in the formular for calculating actual ground coverage." />
									</span><br>

									<!-- Identify element as plot size -->
									<span id="element_plotsize_span">
										<input id="element_plotsize" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_plotsize"><?php echo __('Is PlotSize'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as plotsize used in calcualting Actual Plot ratio and Actual Ground Coverage." />
									</span><br>

									<!-- Identify element as actual plot ratio -->
									<span id="element_actualplotration_span">
										<input id="element_actualplotratio" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_actualplotratio"><?php echo __('Is Actual Plot Ratio'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as actualplotratio used in calcualting Actual Plot ratio and the value is automatically pre-filled." />
									</span><br>

									<!-- Identify element as permitted ground coverage -->
									<span id="element_permittedgroundcoverage_span">
										<input id="element_permittedgroundcoverage" class="checkbox" value=""
											type="checkbox">
										<label class="choice"
											for="element_permittedgroundcoverage"><?php echo __('Is Actual Ground Coverage'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as actualplotratio used in calcualting Permitted Ground Coverage and the value is automatically pre-filled." />
									</span><br>

									<!-- Identify field as zone element -->
									<span id="element_zone_span">
										<input id="element_zone" class="checkbox" value="" type="checkbox">
										<label class="choice" for="element_zone"><?php echo __('Is Zone'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as zone used to pre-fill permitted user for a selected zone." />
									</span><br>

									<!-- Identify element as permitted ground coverage -->
									<span id="element_permitteduser_span">
										<input id="element_permitteduser" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_permitteduser"><?php echo __('Is Permitted User'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as permitted user field used to warn users if they exceed permitted plot ratio and ground coverage." />
									</span><br>

									<!-- Identify field as estimated project cost -->
									<span id="element_projectcost_span">
										<input id="element_projectcost" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_projectcost"><?php echo __('Is Project Cost'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as project cost field and is automatically prefilled with cost = total new plinth area * 30,000." />
									</span><br>

									<!-- Identify field as element_groundcoveragereason for exceeding -->
									<span id="element_groundcoveragereason_span">
										<input id="element_groundcoveragereason" class="checkbox" value=""
											type="checkbox">
										<label class="choice"
											for="element_groundcoveragereason"><?php echo __('Is Ground Coverage Exceed Reason'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field used for user to type is reasons for exceeding permitted ground coverage. works with textarea field type only" />
									</span><br>
									<!-- Identify fiels as element_grandtotalplintharea -->
									<span id="element_grandtotalplintharea_span">
										<input id="element_grandtotalplintharea" class="checkbox" value=""
											type="checkbox">
										<label class="choice"
											for="element_grandtotalplintharea"><?php echo __('Used for holding Plinth Area Grand Total'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as a field used for calculatin Grand Total of new and existing plinth areas" />
									</span><br>
									<!-- Identify fiels as element_grandtotalfootprintarea -->
									<span id="element_grandtotalfootprintarea_span">
										<input id="element_grandtotalfootprintarea" class="checkbox" value=""
											type="checkbox">
										<label class="choice"
											for="element_grandtotalfootprintarea"><?php echo __('Used for holding Footprint Area Grand Total'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field as a field used for calculatin Grand Total of new and existing footprint areas" />
									</span><br>
									<!-- Identify field as  element_plotratioreason for exceeding -->
									<span id="element_plotratioreason_span">
										<input id="element_plotratioreason" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_plotratioreason"><?php echo __('Is Plot Ratio Exceed Reason'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field used for user to type is reasons for exceeding permitted plot ratio. works with textarea field type only " />
									</span><br>
									<!-- Identify file upload that are for IFC -->
									<span id="element_file_ifc_span">
										<input id="element_file_ifc" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_file_ifc"><?php echo __('Is IFC file upload'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field used as IFC upload file" />
									</span><br>
									<!-- Identify plot no -->
									<span id="element_plot_no_span">
										<input id="element_plot_no" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_plot_no"><?php echo __('Is Plot no field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as plot no" /> </span><br>
									<!-- Identify plot no -->
									<span id="element_block_no_span">
										<input id="element_block_no" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_block_no"><?php echo __('Is Block no field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as block no" />
									</span><br>


									<!-- Identify Sub county -->
									<span id="element_subcounty_span">
										<input id="element_subcounty" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_subcounty"><?php echo __('Is SubCounty field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as subcounty field." />
									</span><br>

									<!-- Identify Owner Phone -->
									<span id="element_owner_phone_span">
										<input id="element_owner_phone" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_owner_phone"><?php echo __('Is Owner Phone field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as owner phone field." />
									</span><br>

									<!-- Identify Owner Address -->
									<span id="element_owner_address_span">
										<input id="element_owner_address" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_owner_address"><?php echo __('Is Owner Address field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as owner address field." />
									</span><br>

									<!-- Identify Owner Email -->
									<span id="element_owner_email_span">
										<input id="element_owner_email" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_owner_email"><?php echo __('Is Owner Email field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as owner email field." />
									</span><br>

									<!-- Identify Ward -->
									<span id="element_ward_span">
										<input id="element_ward" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_ward"><?php echo __('Is Ward field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as ward field." />
									</span><br>

									<!-- Identify Plot Area -->
									<span id="element_plot_area_span">
										<input id="element_plot_area" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_plot_area"><?php echo __('Is Plot Area field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as plot area field." />
									</span><br>

									<!-- Identify Plot Length -->
									<span id="element_plot_length_span">
										<input id="element_plot_length" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_plot_length"><?php echo __('Is Plot Length field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as plot length field." />
									</span><br>

									<!-- Identify Plot Width -->
									<span id="element_plot_width_span">
										<input id="element_plot_width" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_plot_width"><?php echo __('Is Plot Width field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as plot width field." />
									</span><br>


									<!-- Identify owner type -->
									<span id="element_ownertype_span">
										<input id="element_ownertype" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="element_ownertype"><?php echo __('Is Owner Name field'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will identify this field use as owner name field" />
									</span><br>
								</fieldset>
							</li>

							<li class="rightCol" id="prop_access_control">
								<fieldset class="fieldset">
									<legend>Field Visibility</legend>

									<input id="element_public" name="element_visibility" class="radio" value=""
										checked="checked" type="radio">
									<label class="choice" for="element_public">Visible</label>
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="This is the default option. The field will be accessible by anyone when the form is made public." />

									<span style="display: block;margin: 1px 0">
										<input id="element_hidden" name="element_visibility" class="radio" value=""
											type="radio">
										<label class="choice" for="element_hidden">Hidden</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Field will not be shown to users when the form is made public. Useful to collect information that is not entered by the users (e.g. populated from URL parameters)" />
									</span>

									<span id="admin_only_span">
										<input id="element_private" name="element_visibility" class="radio" value=""
											type="radio">
										<label class="choice" for="element_private">Admin Only</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Similar as hidden, but the field can't be used to collect any data from the public form. The field will only visible inside MachForm entry manager. Useful to add additional information to a submitted entry." />
									</span>
								</fieldset>
							</li>

							<li class="clear" id="prop_time_options">
								<fieldset class="choices">
									<legend>
										Time Options
									</legend>

									<span>
										<input id="prop_time_showsecond" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_time_showsecond">Show Seconds Field</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will enable Seconds field on your time field." />
										<br />
										<input id="prop_time_24hour" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_time_24hour">Use 24 Hour Format</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="This will enable 24-hour notation in the form hh:mm (for example 14:23) or hh:mm:ss (for example, 14:23:45)" />
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_text_options">
								<fieldset class="choices">
									<legend>
										Text Option
									</legend>

									<span>
										<input id="prop_text_as_password" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_text_as_password">Display as Password
											Field</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will display the field as a password field and masked all the characters (shown as asterisks or circles). <br/><br/>Please be aware that there is <u>no encryption</u> being made for the password field. You will be able to see it from the admin panel/email as a plain text." />
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_dataflow_options">
								<fieldset class="choices">
									<legend>
										Dataflow Option
									</legend>

									<span>
										<label class="text"
											for="prop_remote_url"><?php echo __('Remote Server URL'); ?></label><br>
										<input id="prop_remote_url" name="prop_remote_url" class="text" value=""
											type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The remote URL used to check the value with" /><br>
									</span><br>

									<span>
										<label class="text"
											for="prop_text_as_password"><?php echo __('Remote Result Criteria'); ?></label><br>
										<select id="prop_remote_criteria" name="prop_remote_criteria">
											<option>Choose criteria...</option>
											<option value='records'>Results contains records</option>
											<option value='norecords'>Results contains no records</option>
											<option value='value'>Result matches below value</option>
										</select>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="What kind of result should be expected" /><br>
									</span><br>

									<span>
										<label class="text"
											for="prop_remote_value"><?php echo __('Remote Value'); ?></label><br>
										<input id="prop_remote_value" name="prop_remote_value" class="text" value=""
											type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="The expected remote value" /><br>
									</span><br>

									<span>
										<label class="text"
											for="prop_remote_username"><?php echo __('Remote Server Username'); ?></label><br>
										<input id="prop_remote_username" name="prop_remote_username" class="text"
											value="" type="text">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The remote username if authentication is required" /><br>
									</span><br>

									<span>
										<label class="text"
											for="prop_remote_password"><?php echo __('Remote Server Password'); ?></label><br>
										<input id="prop_remote_password" name="prop_remote_password" class="text"
											value="" type="password">
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The remote password if authentication is required" /><br>
									</span><br>

									<span>
										<label class="text"
											for="prop_field_value"><?php echo __('Template for Remote Result'); ?></label><br>
										<textarea id="prop_field_value" name="prop_field_value"
											class="textarea"></textarea>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter a value if you want the result from the remote url to match this value" /><br>
									</span><br>

									<span>
										<label class="text"
											for="prop_field_error_message"><?php echo __('Custom Error Template for Remote Result'); ?></label><br>
										<textarea id="prop_field_error_message" name="prop_field_error_message"
											class="textarea"></textarea>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter the default error message for this field" /><br>
									</span><br>


								</fieldset>

							</li>

							<li class="clear" id="prop_choice_limit">
								<fieldset class="choices" style="padding-bottom: 10px">
									<legend style="background-color: #dc6666; border-color: #dc6666">
										Choices Limit
									</legend>

									<span style="padding-left: 5px">
										<label class="choice">Client must select</label>
										<select class="select" id="prop_choice_limit_rule" name="prop_choice_limit_rule"
											autocomplete="off" style="margin: 0 3px">
											<option value="atleast">at least</option>
											<option value="atmost">at most</option>
											<option value="exactly">exactly</option>
											<option value="between">between</option>
										</select>

										<span id="prop_choice_limit_qty_span" style="display: none">
											<input id="prop_choice_limit_qty" style="width: 20px" class="text" value=""
												maxlength="255" type="text">
											<label>choice</label>
										</span>
										<span id="prop_choice_limit_range_span" style="display: none">
											<input id="prop_choice_limit_range_min" style="width: 20px" class="text"
												value="" maxlength="255" type="text"> and
											<input id="prop_choice_limit_range_max" style="width: 20px" class="text"
												value="" maxlength="255" type="text">
										</span>
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_matrix_options">
								<fieldset class="choices">
									<legend>
										Matrix Option
									</legend>

									<span>
										<input id="prop_matrix_allow_multiselect" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_matrix_allow_multiselect">Allow Multiple Answers
											Per Row</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this option will allow your client to select multiple answers for each row. This option can only being set once, when you initially added the matrix field. Once you have saved the form, this option can't be changed." />
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_address_options">
								<fieldset class="choices">
									<legend>
										Address Options
									</legend>

									<span>
										<input id="prop_address_hideline2" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_address_hideline2">Hide Address Line 2</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Hide the 'Address Line 2' field from the address field." />
										<br />
										<input id="prop_address_us_only" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_address_us_only">Restrict to U.S. State
											Selection</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will limit the country selection to United States only and the state field will be populated with U.S. state list" />
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_date_options">
								<fieldset class="choices">
									<legend>
										Date Options
									</legend>

									<span>
										<input id="prop_date_range" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_date_range">Enable Minimum and/or Maximum
											Dates</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="You can set minimum and/or maximum dates within which a date may be chosen." />

										<div id="prop_date_range_details" style="display: none;">

											<div style="margin-left: 23px;margin-top: 10px">
												Set range using
												<select class="select" id="prop_date_range_format_switcher"
													name="prop_date_range_format_switcher" autocomplete="off">
													<option value="fixed">fixed</option>
													<option value="relative">relative</option>
												</select> dates:
											</div>

											<div id="prop_date_range_fixed">
												<div id="form_date_range_minimum">
													<label class="desc">Minimum Date:</label>

													<span>
														<input type="text" value="" maxlength="2" size="2"
															style="width: 2em;" class="text" name="date_range_min_mm"
															id="date_range_min_mm">
														<label for="date_range_min_mm">MM</label>
													</span>

													<span>
														<input type="text" value="" maxlength="2" size="2"
															style="width: 2em;" class="text" name="date_range_min_dd"
															id="date_range_min_dd">
														<label for="date_range_min_dd">DD</label>
													</span>

													<span>
														<input type="text" value="" maxlength="4" size="4"
															style="width: 3em;" class="text" name="date_range_min_yyyy"
															id="date_range_min_yyyy">
														<label for="date_range_min_yyyy">YYYY</label>
													</span>

													<span style="height: 30px;padding-right: 10px;">
														<input type="hidden" value="" maxlength="4" size="4"
															style="width: 3em;" class="text"
															name="linked_picker_range_min" id="linked_picker_range_min">
														<div style="display: none"><img id="date_range_min_pick_img"
																alt="Pick date."
																src="/form_builder/images/icons/calendar.png"
																class="trigger"
																style="margin-top: 3px; cursor: pointer" /></div>
													</span>
												</div>

												<div id="form_date_range_maximum">
													<label class="desc">Maximum Date:</label>

													<span>
														<input type="text" value="" maxlength="2" size="2"
															style="width: 2em;" class="text" name="date_range_max_mm"
															id="date_range_max_mm">
														<label for="date_range_max_mm">MM</label>
													</span>

													<span>
														<input type="text" value="" maxlength="2" size="2"
															style="width: 2em;" class="text" name="date_range_max_dd"
															id="date_range_max_dd">
														<label for="date_range_max_dd">DD</label>
													</span>

													<span>
														<input type="text" value="" maxlength="4" size="4"
															style="width: 3em;" class="text" name="date_range_max_yyyy"
															id="date_range_max_yyyy">
														<label for="date_range_max_yyyy">YYYY</label>
													</span>

													<span>
														<input type="hidden" value="" maxlength="4" size="4"
															style="width: 3em;" class="text"
															name="linked_picker_range_max" id="linked_picker_range_max">
														<div style="display: none"><img id="date_range_max_pick_img"
																alt="Pick date."
																src="/form_builder/images/icons/calendar.png"
																class="trigger"
																style="margin-top: 3px; cursor: pointer" /></div>
													</span>
												</div>
											</div>

											<div id="prop_date_range_relative" style="display: none">
												<div id="form_date_range_minimum_relative">
													<label for="date_range_min_relative" class="desc">Minimum
														Date:</label>
													<input type="text" value="" size="3" maxlength="4"
														style="width: 2.2em;" class="text"
														name="date_range_min_relative" id="date_range_min_relative">
													days ago
												</div>

												<div id="form_date_range_maximum_relative">
													<label for="date_range_max_relative" class="desc">Maximum
														Date:</label>
													<input type="text" value="" size="3" maxlength="4"
														style="width: 2.2em;" class="text"
														name="date_range_max_relative" id="date_range_max_relative">
													days ahead
												</div>
											</div>

											<div style="clear: both"></div>

										</div>

										<div style="clear: both"></div>

										<input id="prop_date_selection_limit" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_date_selection_limit">Enable Date Selection
											Limit</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="This is useful for reservation or booking form, so that you could allocate each day for a maximum number of customers. For example, setting the value to 5 will ensure that the same date can't be booked/selected by more than 5 customers." />
										<div id="form_date_selection_limit" style="display: none">
											Only allow each date to be selected
											<input id="date_selection_max" style="width: 20px" class="text" value=""
												maxlength="255" type="text"> times
										</div>
										<div style="clear: both"></div>

										<input id="prop_date_past_future_selection" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_date_past_future_selection">Disable</label>
										<select class="select medium" id="prop_date_past_future"
											name="prop_date_past_future" autocomplete="off" disabled="disabled">
											<option id="element_date_past" value="p">All Past Dates</option>
											<option id="element_date_future" value="f">All Future Dates</option>
										</select>

										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this option will disable either past or future dates selection." />
										<div style="clear: both"></div>

										<input id="prop_date_disable_weekend" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_date_disable_weekend">Disable Weekend
											Dates</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this option will disable all weekend dates." />
										<div style="clear: both"></div>

										<input id="prop_date_disable_specific" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_date_disable_specific">Disable Specific
											Dates</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="You can disable any specific dates to prevent them from being selected by your clients. Use the datepicker to disable multiple dates." />
										<div id="form_date_disable_specific" style="display: none">
											<textarea class="textarea" rows="10" cols="100"
												style="width: 175px;height: 45px" id="date_disabled_list"></textarea>
											<div style="display: none"><img id="date_disable_specific_pick_img"
													alt="Pick date." src="/form_builder/images/icons/calendar.png"
													class="trigger" style="vertical-align: top; cursor: pointer" />
											</div>
										</div>
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_choice_max_entry">
								<fieldset class="choices" style="padding-bottom: 10px">
									<legend>
										Choice Limit
									</legend>

									<span>
										<input id="prop_choice_max_entry_enable" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_choice_max_entry_enable">Enable Choice
											Limit</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Limit the number of entries per choice option. Once a choice option has reached the maximum limit, it won't be available again for future selection. This is useful for reservation or booking form." />
										<div id="form_choice_max_entry" style="display: none;padding-bottom: 0px">
											Only allow each choice to be selected
											<input id="choice_max_entry" style="width: 20px" class="text" value=""
												maxlength="255" type="text"> times
										</div>
										<div style="clear: both"></div>
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_section_options">
								<fieldset class="choices">
									<legend>
										Section Break Options
									</legend>

									<span>
										<input id="prop_section_email_display" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_section_email_display">Display Section Break in
											Email</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enable this option if you need to display the content of the section break within the notification email, review page and entries page." />

										<div style="clear: both"></div>

										<input id="prop_section_enable_scroll" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_section_enable_scroll">Enable Scrollbar</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The section break will be set to a fixed height and a vertical scrollbar will be displayed as needed. This is useful to display large amount of text, such as terms and conditions, or contract agreement." />
										<div id="div_section_size" style="display: none">
											Section Break Size:
											<select class="select" id="prop_section_size" autocomplete="off"
												tabindex="13" style="width: 100px">
												<option value="small">Small</option>
												<option value="medium">Medium</option>
												<option value="large">Large</option>
											</select>
										</div>
										<div style="clear: both"></div>
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_file_options">
								<fieldset class="choices">
									<legend>
										Upload Options
									</legend>

									<span>

										<input id="prop_file_enable_type_limit" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_file_enable_type_limit">Limit File Upload
											Type</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="You can block or only allow certain file types to be uploaded. Enter the file types extension into the textbox, separate them with commas. (example: jpg,gif,png,bmp)" />
										<div id="form_file_limit_type" style="display: none">
											<select class="select" id="prop_file_block_or_allow"
												name="prop_file_block_allow" autocomplete="off" style="width: 90px">
												<option id="element_file_allow" value="a">Only Allow</option>
												<option id="element_file_block" value="b">Block</option>
											</select> <label class="choice" for="file_type_list">file types listed
												below:</label>
											<textarea class="textarea" rows="10" cols="100"
												style="width: 230px; height: 30px;margin-top: 5px"
												id="file_type_list"></textarea>
										</div>
										<div style="clear: both"></div>
										<input id="prop_file_as_attachment" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_file_as_attachment">Send File as Email
											Attachment</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="By default, all file uploads will be sent to your email as a download link. Checking this option will send the file as email attachment instead. WARNING: Don't enable this option if you expect to receive large files from your clients. If the files attached are larger than the allowed memory limit on your server, the email won't be sent." />

										<div style="clear: both"></div>

										<input id="prop_file_enable_advance" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_file_enable_advance">Enable Advanced
											Uploader</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this option will enable advanced functionality, such as Upload Progress Bar, Multiple File Uploads, AJAX uploads, File Size Limit, etc. This option is recommended to be enabled." />

									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_file_advance_options">
								<fieldset class="choices">
									<legend>
										Advanced Uploader Options
									</legend>

									<span>
										<input id="prop_file_auto_upload" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_file_auto_upload">Automatically Upload
											Files</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="By default, the upload button or the form submit button need to be clicked to start uploading the file. By checking this option, the file will be automatically being uploaded as soon as the file being selected." />
										<div style="clear: both"></div>

										<input id="prop_file_multi_upload" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_file_multi_upload">Allow Multiple File
											Upload</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this option will allow multiple files to be uploaded. You can also limit the maximum number of files to be uploaded." />
										<div id="form_file_max_selection">
											Limit selection to a maximum
											<input id="file_max_selection" style="width: 20px" class="text" value=""
												maxlength="255" type="text"> files
										</div>
										<div style="clear: both"></div>

										<input id="prop_file_limit_size" class="checkbox" value="" type="checkbox">
										<label class="choice" for="prop_file_limit_size">Limit File Size</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="You can set the maximum size of a file allowed to be uploaded here." />
										<div id="form_file_limit_size">
											Limit each file to a maximum
											<input id="file_size_max" style="width: 20px" class="text" value=""
												maxlength="255" type="text"> MB
										</div>


									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_range">
								<fieldset class="range">
									<legend>
										Range
									</legend>

									<div style="padding-left: 2px">
										<span>
											<label for="element_range_min" class="desc">Min</label>
											<input type="text" value="" class="text" name="element_range_min"
												id="element_range_min">
										</span>
										<span>
											<label for="element_range_max" class="desc">Max</label>
											<input type="text" value="" class="text" name="element_range_max"
												id="element_range_max">
										</span>
										<span>
											<label for="element_range_limit_by" class="desc">Limit By
												<img class="helpmsg" src="/form_builder/images/icons/help2.png"
													style="vertical-align: top"
													title="You can limit the amount of characters typed to be between certain characters or words, or between certain values in the case of number field. Leave the value blank or 0 if you don't want to set any limit." /></label>
											<select class="select" name="element_range_limit_by"
												id="element_range_limit_by">
												<option value="c">Characters</option>
												<option value="w">Words</option>
											</select>
											<select class="select" name="element_range_number_limit_by"
												id="element_range_number_limit_by">
												<option value="v">Value</option>
												<option value="d">Digits</option>
											</select>
										</span>

									</div>
								</fieldset>
							</li>

							<li class="clear" id="prop_default_value_text">
								<fieldset class="choices">
									<legend>
										Default Value
									</legend>

									<div class="left" style="padding-bottom: 5px">
										<input id="element_default_value_text_static"
											name="element_default_value_text_type" class="radio" value=""
											checked="checked" type="radio">
										<label class="choice" for="element_default_value_text_static">Text</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The field will be prepopulated with the text you enter." />
									</div>

									<div class="left" style="padding-left: 15px;padding-bottom: 5px">
										<input id="element_default_value_text_random"
											name="element_default_value_text_type" class="radio" value="" type="radio">
										<label class="choice" for="element_default_value_text_random">Random</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The field will be prepopulated with random letters, numbers or both." />
									</div>

									<div id="default_value_text_static_div">
										<input id="element_default_value_text" class="text large"
											name="element_default_value_text" value="" type="text">
									</div>

									<div id="default_value_text_random_div">
										<span>
											<strong>Length</strong> &#8674; <input id="element_text_default_length"
												style="width: 20px" class="text" value="" maxlength="255" type="text">
											characters of
											<select class="select" id="element_text_default_random_type"
												name="element_text_default_random_type" autocomplete="off">
												<option value="letter">letters</option>
												<option value="number">numbers</option>
												<option value="alphanum">alphanumeric</option>
												<option value="all">all</option>
											</select>
										</span>
										<span>
											<strong>Prefix</strong> &#8674; <input id="element_text_default_prefix"
												style="width: 60px" class="text" value="" maxlength="255" type="text">
										</span>
										<span>
											<strong>Letters Case</strong> &#8674;
											<select class="select" id="element_text_default_case"
												name="element_text_default_case" autocomplete="off">
												<option value="u">uppercase</option>
												<option value="l">lowercase</option>
												<option value="b">both</option>
											</select>
										</span>
									</div>

									<span id="prop_default_value_text_autohide_span">
										<input id="element_enable_placeholder_text" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="element_enable_placeholder_text">Auto Hide Default
											Value</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="As soon as the user types in the field, the default value will be automatically removed. Useful to provide short hint that describe the expected value of the field.<br/><br/>Tech terms: The default value will be used for the 'placeholder' attribute" />
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_number_advance_options">
								<fieldset class="choices">
									<legend>
										Advanced Option
									</legend>

									<span>
										<input id="prop_number_enable_quantity" class="checkbox" value=""
											type="checkbox">
										<label class="choice" for="prop_number_enable_quantity">Enable as Quantity
											field</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enable this option if your form has payment enabled and need to use quantity field to calculate the total price. Select the target field for the calculation from the dropdown list. Target field type must be one of the following: Multiple Choice, Drop Down, Checkboxes, Price." />
										<div id="prop_number_quantity_link_div" style="display: none">
											Calculate with this field: <br />
											<select class="select large" id="prop_number_quantity_link"
												name="prop_number_quantity_link" style="width: 95%" autocomplete="off">
												<option value=""> -- No Supported Fields Available --</option>
											</select>
										</div>
									</span>

								</fieldset>

							</li>

							<li class="clear" id="prop_default_value" style="margin-top: 10px">
								<label class="desc" for="element_default">
									Default Value
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="By setting this value, the field will be prepopulated with the text you enter." />
								</label>

								<input id="element_default_value" class="text large" name="element_default_value"
									value="" type="text">
							</li>

							<li class="clear" id="prop_default_phone">
								<label class="desc" for="element_default_phone">
									Default Value
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="By setting this value, the field will be prepopulated with the text you enter." />
								</label>

								<input id="element_default_phone1" class="text" size="3" maxlength="3"
									name="element_default_phone1" value="" type="text"> -
								<input id="element_default_phone2" class="text" size="3" maxlength="3"
									name="element_default_phone2" value="" type="text"> -
								<input id="element_default_phone3" class="text" size="4" maxlength="4"
									name="element_default_phone3" value="" type="text">
							</li>

							<li class="clear" id="prop_default_date">
								<label class="desc" for="element_default_date">
									Default Date
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Use the format ##/##/#### or any English date words, such as 'today', 'tomorrow', 'last friday', '+1 week', 'last day of next month', '3 days ago', 'monday next week'" />
								</label>

								<input id="element_default_date" class="text large" name="element_default_date" value=""
									type="text">
							</li>

							<li class="clear" id="prop_default_time">
								<label class="desc" for="element_default_time">
									Default Time
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Example values: 'now', '+5 minute', '+2 hour', '-10 minute', '+2 hour 30 minute'" />
								</label>

								<input id="element_default_time" class="text large" name="element_default_time" value=""
									type="text">
							</li>

							<li class="clear" id="prop_default_value_textarea" style="margin-top: 10px">
								<label class="desc" for="element_default_textarea">
									Default Value
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="By setting this value, the field will be prepopulated with the text you enter." />
								</label>

								<textarea class="textarea" rows="10" cols="50" id="element_default_value_textarea"
									name="element_default_value_textarea"></textarea>
							</li>

							<li class="clear" id="prop_placeholder" style="margin-bottom: 10px">
								<span>
									<input id="element_enable_placeholder" class="checkbox" value="" type="checkbox">
									<label class="choice" for="element_enable_placeholder">Auto Hide Default
										Value</label>
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="As soon as the user types in the field, the default value will be automatically removed. Useful to provide short hint that describe the expected value of the field.<br/><br/>Tech terms: The default value will be used for the 'placeholder' attribute" />
								</span>
							</li>

							<li class="clear" id="prop_default_country">
								<label class="desc" for="fieldaddress_default">
									Default Country
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="By setting this value, the country field will be prepopulated with the selection you make." />
								</label>
								<select class="select" id="element_countries" name="element_countries">
									<option value=""></option>

									<optgroup label="North America">
										<option value="Antigua and Barbuda">Antigua and Barbuda</option>
										<option value="Bahamas">Bahamas</option>
										<option value="Barbados">Barbados</option>
										<option value="Belize">Belize</option>
										<option value="Canada">Canada</option>
										<option value="Costa Rica">Costa Rica</option>
										<option value="Cuba">Cuba</option>
										<option value="Dominica">Dominica</option>
										<option value="Dominican Republic">Dominican Republic</option>
										<option value="El Salvador">El Salvador</option>
										<option value="Grenada">Grenada</option>
										<option value="Guatemala">Guatemala</option>
										<option value="Haiti">Haiti</option>
										<option value="Honduras">Honduras</option>
										<option value="Jamaica">Jamaica</option>
										<option value="Mexico">Mexico</option>
										<option value="Nicaragua">Nicaragua</option>
										<option value="Panama">Panama</option>
										<option value="Puerto Rico">Puerto Rico</option>
										<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
										<option value="Saint Lucia">Saint Lucia</option>
										<option value="Saint Vincent and the Grenadines">Saint Vincent and the
											Grenadines</option>
										<option value="Trinidad and Tobago">Trinidad and Tobago</option>
										<option value="United States">United States</option>
									</optgroup>

									<optgroup label="South America">
										<option value="Argentina">Argentina</option>
										<option value="Bolivia">Bolivia</option>
										<option value="Brazil">Brazil</option>
										<option value="Chile">Chile</option>
										<option value="Columbia">Columbia</option>
										<option value="Ecuador">Ecuador</option>
										<option value="Guyana">Guyana</option>
										<option value="Paraguay">Paraguay</option>
										<option value="Peru">Peru</option>
										<option value="Suriname">Suriname</option>
										<option value="Uruguay">Uruguay</option>
										<option value="Venezuela">Venezuela</option>
									</optgroup>

									<optgroup label="Europe">
										<option value="Albania">Albania</option>
										<option value="Andorra">Andorra</option>
										<option value="Armenia">Armenia</option>
										<option value="Austria">Austria</option>
										<option value="Azerbaijan">Azerbaijan</option>
										<option value="Belarus">Belarus</option>
										<option value="Belgium">Belgium</option>
										<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
										<option value="Bulgaria">Bulgaria</option>
										<option value="Croatia">Croatia</option>
										<option value="Cyprus">Cyprus</option>
										<option value="Czech Republic">Czech Republic</option>
										<option value="Denmark">Denmark</option>
										<option value="Estonia">Estonia</option>
										<option value="Finland">Finland</option>
										<option value="France">France</option>
										<option value="Georgia">Georgia</option>
										<option value="Germany">Germany</option>
										<option value="Greece">Greece</option>
										<option value="Guernsey">Guernsey</option>
										<option value="Hungary">Hungary</option>
										<option value="Iceland">Iceland</option>
										<option value="Ireland">Ireland</option>
										<option value="Italy">Italy</option>
										<option value="Latvia">Latvia</option>
										<option value="Liechtenstein">Liechtenstein</option>
										<option value="Lithuania">Lithuania</option>
										<option value="Luxembourg">Luxembourg</option>
										<option value="Macedonia">Macedonia</option>
										<option value="Malta">Malta</option>
										<option value="Moldova">Moldova</option>
										<option value="Monaco">Monaco</option>
										<option value="Montenegro">Montenegro</option>
										<option value="Netherlands">Netherlands</option>
										<option value="Norway">Norway</option>
										<option value="Poland">Poland</option>
										<option value="Portugal">Portugal</option>
										<option value="Romania">Romania</option>
										<option value="San Marino">San Marino</option>
										<option value="Serbia">Serbia</option>
										<option value="Slovakia">Slovakia</option>
										<option value="Slovenia">Slovenia</option>
										<option value="Spain">Spain</option>
										<option value="Sweden">Sweden</option>
										<option value="Switzerland">Switzerland</option>
										<option value="Ukraine">Ukraine</option>
										<option value="United Kingdom">United Kingdom</option>
										<option value="Vatican City">Vatican City</option>
									</optgroup>

									<optgroup label="Asia">
										<option value="Afghanistan">Afghanistan</option>
										<option value="Bahrain">Bahrain</option>
										<option value="Bangladesh">Bangladesh</option>
										<option value="Bhutan">Bhutan</option>
										<option value="Brunei Darussalam">Brunei Darussalam</option>
										<option value="Myanmar">Myanmar</option>
										<option value="Cambodia">Cambodia</option>
										<option value="China">China</option>
										<option value="East Timor">East Timor</option>
										<option value="Hong Kong">Hong Kong</option>
										<option value="India">India</option>
										<option value="Indonesia">Indonesia</option>
										<option value="Iran">Iran</option>
										<option value="Iraq">Iraq</option>
										<option value="Israel">Israel</option>
										<option value="Japan">Japan</option>
										<option value="Jordan">Jordan</option>
										<option value="Kazakhstan">Kazakhstan</option>
										<option value="North Korea">North Korea</option>
										<option value="South Korea">South Korea</option>
										<option value="Kuwait">Kuwait</option>
										<option value="Kyrgyzstan">Kyrgyzstan</option>
										<option value="Laos">Laos</option>
										<option value="Lebanon">Lebanon</option>
										<option value="Malaysia">Malaysia</option>
										<option value="Maldives">Maldives</option>
										<option value="Mongolia">Mongolia</option>
										<option value="Nepal">Nepal</option>
										<option value="Oman">Oman</option>
										<option value="Pakistan">Pakistan</option>
										<option value="Philippines">Philippines</option>
										<option value="Qatar">Qatar</option>
										<option value="Russia">Russia</option>
										<option value="Saudi Arabia">Saudi Arabia</option>
										<option value="Singapore">Singapore</option>
										<option value="Sri Lanka">Sri Lanka</option>
										<option value="Syria">Syria</option>
										<option value="Taiwan">Taiwan</option>
										<option value="Tajikistan">Tajikistan</option>
										<option value="Thailand">Thailand</option>
										<option value="Turkey">Turkey</option>
										<option value="Turkmenistan">Turkmenistan</option>
										<option value="United Arab Emirates">United Arab Emirates</option>
										<option value="Uzbekistan">Uzbekistan</option>
										<option value="Vietnam">Vietnam</option>
										<option value="Yemen">Yemen</option>
									</optgroup>

									<optgroup label="Oceania">
										<option value="Australia">Australia</option>
										<option value="Fiji">Fiji</option>
										<option value="Kiribati">Kiribati</option>
										<option value="Marshall Islands">Marshall Islands</option>
										<option value="Micronesia">Micronesia</option>
										<option value="Nauru">Nauru</option>
										<option value="New Zealand">New Zealand</option>
										<option value="Palau">Palau</option>
										<option value="Papua New Guinea">Papua New Guinea</option>
										<option value="Samoa">Samoa</option>
										<option value="Solomon Islands">Solomon Islands</option>
										<option value="Tonga">Tonga</option>
										<option value="Tuvalu">Tuvalu</option>
										<option value="Vanuatu">Vanuatu</option>
									</optgroup>

									<optgroup label="Africa">
										<option value="Algeria">Algeria</option>
										<option value="Angola">Angola</option>
										<option value="Benin">Benin</option>
										<option value="Botswana">Botswana</option>
										<option value="Burkina Faso">Burkina Faso</option>
										<option value="Burundi">Burundi</option>
										<option value="Cameroon">Cameroon</option>
										<option value="Cape Verde">Cape Verde</option>
										<option value="Central African Republic">Central African Republic</option>
										<option value="Chad">Chad</option>
										<option value="Comoros">Comoros</option>
										<option value="Congo">Congo</option>
										<option value="Djibouti">Djibouti</option>
										<option value="Egypt">Egypt</option>
										<option value="Equatorial Guinea">Equatorial Guinea</option>
										<option value="Eritrea">Eritrea</option>
										<option value="Ethiopia">Ethiopia</option>
										<option value="Gabon">Gabon</option>
										<option value="Gambia">Gambia</option>
										<option value="Ghana">Ghana</option>
										<option value="Guinea">Guinea</option>
										<option value="Guinea-Bissau">Guinea-Bissau</option>
										<option value="Côte d'Ivoire">Côte d'Ivoire</option>
										<option value="Kenya">Kenya</option>
										<option value="Lesotho">Lesotho</option>
										<option value="Liberia">Liberia</option>
										<option value="Libya">Libya</option>
										<option value="Madagascar">Madagascar</option>
										<option value="Malawi">Malawi</option>
										<option value="Mali">Mali</option>
										<option value="Mauritania">Mauritania</option>
										<option value="Mauritius">Mauritius</option>
										<option value="Morocco">Morocco</option>
										<option value="Mozambique">Mozambique</option>
										<option value="Namibia">Namibia</option>
										<option value="Niger">Niger</option>
										<option value="Nigeria">Nigeria</option>
										<option value="Rwanda">Rwanda</option>
										<option value="Sao Tome and Principe">Sao Tome and Principe</option>
										<option value="Senegal">Senegal</option>
										<option value="Seychelles">Seychelles</option>
										<option value="Sierra Leone">Sierra Leone</option>
										<option value="Somalia">Somalia</option>
										<option value="South Africa">South Africa</option>
										<option value="Sudan">Sudan</option>
										<option value="Swaziland">Swaziland</option>
										<option value="United Republic of Tanzania">Tanzania</option>
										<option value="Togo">Togo</option>
										<option value="Tunisia">Tunisia</option>
										<option value="Uganda">Uganda</option>
										<option value="Zambia">Zambia</option>
										<option value="Zimbabwe">Zimbabwe</option>
									</optgroup>
								</select>
							</li>

							<li class="clear" id="prop_phone_default">
								<label class="desc" for="element_phone_default1">
									Default Value
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="By setting this value, the field will be prepopulated with the text you enter." />
								</label>

								( <input id="element_phone_default1" class="text" size="3" name="text" value=""
									tabindex="11" maxlength="3"
									onkeyup="set_properties(JJ('#element_phone_default1').val().toString()+JJ('#element_phone_default2').val().toString()+JJ('#element_phone_default3').val().toString(), 'default_value')"
									onblur="set_properties(JJ('#element_phone_default1').val().toString()+JJ('#element_phone_default2').val().toString()+JJ('#element_phone_default3').val().toString(), 'default_value')"
									type="text"> )

								<input id="element_phone_default2" class="text" size="3" name="text" value=""
									tabindex="11" maxlength="3"
									onkeyup="set_properties(JJ('#element_phone_default1').val().toString()+JJ('#element_phone_default2').val().toString()+JJ('#element_phone_default3').val().toString(), 'default_value')"
									onblur="set_properties(JJ('#element_phone_default1').val().toString()+JJ('#element_phone_default2').val().toString()+JJ('#element_phone_default3').val().toString(), 'default_value')"
									type="text"> -
								<input id="element_phone_default3" class="text" size="4" name="text" value=""
									tabindex="11" maxlength="4"
									onkeyup="set_properties(JJ('#element_phone_default1').val().toString()+JJ('#element_phone_default2').val().toString()+JJ('#element_phone_default3').val().toString(), 'default_value')"
									onblur="set_properties(JJ('#element_phone_default1').val().toString()+JJ('#element_phone_default2').val().toString()+JJ('#element_phone_default3').val().toString(), 'default_value')"
									type="text">
							</li>
							<!--OTB Start Notification to others-->
							<li class="rightCol" id="prop_notify_others">
								<fieldset class="fieldset">
									<legend><?php echo __('Copy all Notifications to this contact'); ?></legend>
									<input id="element_notify_contact" class="checkbox" value="" type="checkbox">
									<label class="choice"
										for="element_notify_contact"><?php echo __('Copy all Notifications to this contact?'); ?></label>
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Notifications to client will be copied to this contact." /> <br>
								</fieldset>
							</li>
							<!--OTB End Notification to others-->
							<!--Start OTB Africa - QR Code Marking for File Authenticity Verification-->
							<li class="clear" id="prop_file_authenticity_verification">
								<fieldset class="choices">
									<legend>
										<?php echo __('File Authenticity Verification'); ?>
									</legend>

									<span>
										<input id="prop_mark_file_with_qr_code" class="checkbox" value=""
											type="checkbox">
										<label class="choice"
											for="prop_mark_file_with_qr_code"><?php echo __('Mark Uploaded Files with QR Code'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Mark this file with a QR code that can be used to verify its authenticity. File must be a pdf." />
										<div style="clear: both"></div>
										<input id="prop_file_qr_all_pages" class="checkbox" value="" type="checkbox">
										<label class="choice"
											for="prop_file_qr_all_pages"><?php echo __('Mark QR Code on every page?'); ?></label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="Place QR code on every page?." />
										<div style="clear: both"></div>
										<div id="form_file_qr_page_position" style="display: block">
											<label class="choice"
												for="prop_file_qr_page_position"><?php echo __('QR Code Position'); ?></label>
											<select class="select" id="prop_file_qr_page_position"
												name="prop_file_qr_page_position" autocomplete="off"
												style="width: 90px">
												<option id="element_top_left" value="top_left">
													<?php echo __('Top Left'); ?>
												</option>
												<option id="element_top_right" value="top_right">
													<?php echo __('Top Right'); ?>
												</option>
												<option id="element_bottom_left" value="bottom_left">
													<?php echo __('Bottom Left'); ?>
												</option>
												<option id="element_bottom_right" value="bottom_right">
													<?php echo __('Bottom Right'); ?>
												</option>
											</select>
											<img class="helpmsg" src="/form_builder/images/icons/help2.png"
												style="vertical-align: top"
												title="Mark this file with a QR code that can be used to verify its authenticity." />
										</div>
										<div style="clear: both"></div>
										<div id="form_file_qr_users" style="display: block">
											<label class="choice"
												for="prop_file_qr_users"><?php echo __('QR Code Visible To:'); ?></label>
											<select class="select" id="prop_file_qr_users" name="prop_file_qr_users"
												autocomplete="off" style="width: 90px">
												<option id="element_clients" value="clients">
													<?php echo __('Clients Only'); ?>
												</option>
												<option id="element_reviewers" value="reviewers">
													<?php echo __('Reviewers Only'); ?>
												</option>
												<option id="element_both" value="both">
													<?php echo __('Both Clients and Reviewers'); ?>
												</option>
											</select>
										</div>
										<div style="clear: both"></div>
									</span>

								</fieldset>

							</li>
							<!---End OTB Africa - QR Code Marking for File Authenticity Verification-->
							<li class="clear" id="prop_instructions">
								<label class="desc" for="element_instructions">
									Guidelines for User
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="This text will be displayed to your users while they're filling out particular field." />
								</label>

								<textarea class="textarea" rows="10" cols="50" id="element_instructions"></textarea>
							</li>

							<li class="clear" id="prop_custom_css">
								<label class="desc" for="element_custom_css">
									Custom CSS Class
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="This is an advanced option. You can add custom CSS classnames to the parent element of the field. This is useful if you would like to customize the styling for each of your field using your own CSS code. These custom class names will not appear live in the form builder, only on the live form." />
								</label>

								<input id="element_custom_css" class="text large" name="element_custom_css" value=""
									maxlength="255" type="text">
							</li>

							<li class="clear" id="prop_page_break_button" style="margin-top: 50px;margin-bottom: 50px">
								<fieldset style="padding-top: 15px">
									<legend>Page Submit Buttons</legend>

									<div class="left" style="padding-bottom: 5px">
										<input id="prop_submit_use_text" name="submit_use_image" class="radio" value="0"
											type="radio">
										<label class="choice" for="prop_submit_use_text">Use Text Button</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="This is the default and recommended option. All buttons will use simple text. You can change the text being used on each page submit/back button." />
									</div>

									<div class="left" style="padding-left: 5px;padding-bottom: 5px">
										<input id="prop_submit_use_image" name="submit_use_image" class="radio"
											value="1" type="radio">
										<label class="choice" for="prop_submit_use_image">Use Image Button</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Select this option if you prefer to use your own submit/back image buttons. Make sure to enter the full URL address to your images." />
									</div>

									<div id="div_submit_use_text" class="left"
										style="padding-left: 8px;padding-bottom: 10px;width: 92%">
										<label class="desc" for="submit_primary_text">Submit Button</label>
										<input id="submit_primary_text" class="text large" name="submit_primary_text"
											value="" type="text">
										<label id="lbl_submit_secondary_text" class="desc" for="submit_secondary_text"
											style="margin-top: 10px">Back Button</label>
										<input id="submit_secondary_text" class="text large"
											name="submit_secondary_text" value="" type="text">
									</div>

									<div id="div_submit_use_image" class="left"
										style="padding-left: 8px;padding-bottom: 10px;width: 92%; display: none">
										<label class="desc" for="submit_primary_img">Submit Button. Image URL:</label>
										<input id="submit_primary_img" class="text large" name="submit_primary_img"
											value="http://" type="text">
										<label id="lbl_submit_secondary_img" class="desc" for="submit_secondary_img"
											style="margin-top: 10px">Back Button. Image URL:</label>
										<input id="submit_secondary_img" class="text large" name="submit_secondary_img"
											value="http://" type="text">
									</div>
								</fieldset>
							</li>

						</ul>
						<div class="bullet_bar_bottom">
							<img style="float: left" src="/form_builder/images/bullet_green.png" />
							<img style="float: right" src="/form_builder/images/bullet_green.png" />
						</div>
					</div>

				</form>
			</div> <!-- end field properties pane -->
		</div>

		<div id="tab_form_properties" style="display: none">
			<div id="form_properties_pane" class="box">
				<div id="form_properties_holder">
					<div class="bullet_bar_top">
						<img style="float: left" src="/form_builder/images/bullet_pink.png" />
						<img style="float: right" src="/form_builder/images/bullet_pink.png" />
					</div>

					<!--  start form properties pane -->
					<form id="form_properties" action="" onsubmit="return false;">
						<ul id="all_form_properties">
							<li class="form_prop">
								<label class="desc" for="form_title">Form Title
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="The title of your form displayed to the user when they see your form." />
								</label>
								<input id="form_title" name="form_title" class="text large" value="" tabindex="1"
									type="text">
							</li>
							<li class="form_prop">
								<label class="desc" for="form_description">Description
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="This will appear directly below the form name. Useful for displaying a short description or any instructions, notes, guidelines." />
								</label>
								<textarea class="textarea small" rows="10" cols="50" id="form_description"
									tabindex="2"></textarea>
							</li>
							<li class="form_prop" style="margin-bottom: 20px">
								<span>
									<input id="form_name_hide" class="checkbox" value="" type="checkbox">
									<label class="choice" for="form_name_hide">Hide Title and Description from Public
										View</label>
								</span>
							</li>

							<li class="form_prop">
								<label class="desc" for="form_title"><?php echo __('Product/Service Code'); ?>
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="The code of your form displayed to the user when they see your form." />
								</label>
								<input id="form_code" name="form_code" style="width: 99%;" class="text large" value=""
									tabindex="1" type="text">
							</li>

							<li class="form_prop">
								<label class="desc" for="form_type"><?php echo __('Type of Form'); ?>
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="Enter the identification number for the first submission. The rest will incrinate from here for other submissions." />
								</label>
								<select id="form_type" name="form_type">
									<option value="0"><?php echo __('General Form'); ?></option>
									<option value="1"><?php echo __('Application Form'); ?></option>
									<option value="2"><?php echo __('Comment Sheet'); ?></option>
									<option value="3"><?php echo __('Inspection Sheet for Profiles'); ?></option>
									<option value="4"><?php echo __('Membership'); ?></option>
								</select>
							</li>

							<div id="form_application_properties" name="form_type_properties" style="display: none;">
								<li class="form_prop">
									<label class="desc" for="form_group"><?php echo __('Form Groups'); ?>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter the identification number for the first submission. The rest will incrinate from here for other submissions." />
									</label><br>
									<select id="form_group" name="form_group">
										<option><?php echo __('Choose a form category'); ?></option>
										<?php
										$q = Doctrine_Query::create()
											->from("FormGroups a")
											->orderBy("a.group_name ASC");
										$formgroups = $q->execute();
										foreach ($formgroups as $formgroup) {
											echo "<option value='" . $formgroup->getGroupId() . "'>" . $formgroup->getGroupName() . "</option>";
										}
										?>
									</select>
								</li>

								<li class="form_prop">
									<label class="desc" for="form_idn"><?php echo __('Submission Identification'); ?>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter the identification number for the first submission. The rest will incrinate from here for other submissions." />
									</label><br>
									<input id="form_idn" name="form_idn" class="text large" value="" tabindex="1"
										type="text">
								</li>

								<li class="form_prop">
									<label class="desc" for="form_stage"><?php echo __('Submission Stage'); ?>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Choose the first stage in a workflow to which submissions from this form will go." />
									</label><br>
									<select name="form_stage" id="form_stage">
										<option><?php echo __('Choose a stage'); ?></option>
										<?php
										$q = Doctrine_Query::create()
											->from("Menus a")
											->orderBy("a.title ASC");
										$stage_groups = $q->execute();

										foreach ($stage_groups as $stage_group) {
											?>
											<optgroup label="<?php echo $stage_group->getTitle(); ?>">
												<?php
												$q = Doctrine_Query::create()
													->from("SubMenus a")
													->where("a.menu_id = ?", $stage_group->getId())
													->andWhere("a.deleted = 0")
													->orderBy("a.order_no ASC");
												$stages = $q->execute();

												foreach ($stages as $stage) {
													?>
													<option value="<?php echo $stage->getId(); ?>">
														<?php echo $stage->getTitle(); ?>
													</option>
													<?php
												}
												?>
											</optgroup>
											<?php
										}
										?>
									</select>
								</li>
							</div>

							<div id="form_commentsheet_properties" name="form_type_properties" style="display: none;">

								<li class="form_prop">
									<label class="desc"
										for="form_department"><?php echo __('Departments allowed to access this form<br> (If comment sheet)'); ?>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter the identification number for the first submission. The rest will incrinate from here for other submissions." />
									</label><br>
									<select id="form_department" name="form_department">
										<option value="0"><?php echo __('All'); ?></option>
										<?php
										$q = Doctrine_Query::create()
											->from("Department a")
											->orderBy("a.department_name ASC");
										$departments = $q->execute();
										foreach ($departments as $department) {
											?>
											<option value="<?php echo $department->getId(); ?>">
												<?php echo $department->getDepartmentName(); ?>
											</option>
											<?php
										}
										?>
									</select>
								</li>

								<li class="form_prop">
									<label class="desc"
										for="form_department_stage"><?php echo __('Stage at which the department is allowed to access this form'); ?>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Choose the first stage in a workflow to which submissions from this form will go." />
									</label><br>
									<select name="form_department_stage" id="form_department_stage">
										<option><?php echo __('Choose a stage'); ?></option>
										<?php
										$q = Doctrine_Query::create()
											->from("Menus a")
											->orderBy("a.title ASC");
										$stage_groups = $q->execute();

										foreach ($stage_groups as $stage_group) {
											?>
											<optgroup label="<?php echo $stage_group->getTitle(); ?>">
												<?php
												$q = Doctrine_Query::create()
													->from("SubMenus a")
													->where("a.menu_id = ?", $stage_group->getId())
													->andWhere("a.deleted = 0")
													->orderBy("a.order_no ASC");
												$stages = $q->execute();

												foreach ($stages as $stage) {
													?>
													<option value="<?php echo $stage->getId(); ?>">
														<?php echo $stage->getTitle(); ?>
													</option>
													<?php
												}
												?>
											</optgroup>
											<?php
										}
										?>
									</select>
								</li>
							</div>

							<li id="form_prop_confirmation" class="form_prop">
								<fieldset>
									<legend>Submission Confirmation</legend>

									<div class="left" style="padding-bottom: 5px">
										<input id="form_success_message_option" name="confirmation" class="radio"
											value="" checked="checked" type="radio">
										<label class="choice" for="form_success_message_option">Show Text</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="This message will be displayed after your users have successfully submitted an entry. <br/><br/>You can enter any HTML codes, Javascript codes or Merge Tags as well." />
									</div>

									<div class="left" style="padding-left: 15px;padding-bottom: 5px">
										<input id="form_redirect_option" name="confirmation" class="radio" value=""
											type="radio">
										<label class="choice" for="form_redirect_option">Redirect to Web Site</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top" title="After your users have successfully submitted an entry, you can redirect them to another
								website/URL of your choice.<br/><br/>You can insert Merge Tags into the URL to pass form data." />
									</div>

									<textarea class="textarea" rows="10" cols="50" id="form_success_message"
										tabindex="9"></textarea>

									<input id="form_redirect_url" class="text hide" name="form_redirect_url"
										value="http://" type="text">
								</fieldset>
							</li>

							<li id="form_prop_toggle" class="form_prop">
								<div style="text-align: right">
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="All settings below this point are optional. You can leave it as it is if you don't need it." />
									<a href="" id="form_prop_toggle_a">show more options</a> <img
										style="vertical-align: top;cursor: pointer"
										src="/form_builder/images/icons/resultset_next.gif" id="form_prop_toggle_img" />
								</div>
							</li>

							<li id="form_prop_language" class="leftCol advanced_prop form_prop">
								<label class="desc">
									Language
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top"
										title="You can choose the language being used to display your form messages." />
								</label>
								<div>
									<select autocomplete="off" id="form_language" class="select large">
										<option value="bulgarian">Bulgarian</option>
										<option value="chinese">Chinese (Traditional)</option>
										<option value="chinese_simplified">Chinese (Simplified)</option>
										<option value="danish">Danish</option>
										<option value="dutch">Dutch</option>
										<option value="english">English</option>
										<option value="estonian">Estonian</option>
										<option value="finnish">Finnish</option>
										<option value="french">French</option>
										<option value="german">German</option>
										<option value="greek">Greek</option>
										<option value="hungarian">Hungarian</option>
										<option value="indonesia">Indonesia</option>
										<option value="italian">Italian</option>
										<option value="japanese">Japanese</option>
										<option value="norwegian">Norwegian</option>
										<option value="polish">Polish</option>
										<option value="portuguese">Portuguese</option>
										<option value="romanian">Romanian</option>
										<option value="russian">Russian</option>
										<option value="slovak">Slovak</option>
										<option value="spanish">Spanish</option>
										<option value="swedish">Swedish</option>
									</select>
								</div>
							</li>

							<li id="form_prop_label_alignment" class="rightCol advanced_prop form_prop">
								<label for="form_label_alignment" class="desc">Label Alignment
									<img class="helpmsg" src="/form_builder/images/icons/help2.png"
										style="vertical-align: top" title="Set the field label placement" />
								</label>
								<div>
									<select autocomplete="off" id="form_label_alignment" class="select large">
										<option value="top_label">Top Aligned</option>
										<option value="left_label">Left Aligned</option>
										<option value="right_label">Right Aligned</option>
									</select>
								</div>
							</li>





							<li id="form_prop_processing" class="clear advanced_prop form_prop">
								<fieldset>
									<legend>Processing Options</legend>
									<span>
										<input id="form_resume" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_resume">Allow Clients to Save and Resume
											Later</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Checking this will display additional link at the bottom of your form which would allow your clients to save their progress and resume later. This option only available if your form has at least two pages (has one or more Page Break field)." />
									</span><br>
									<span>
										<input id="form_review" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_review">Show Review Page Before
											Submitting</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="If enabled, your clients will be prompted to a preview page that lets them double check their entries before submitting the form." />
									</span><br>
								</fieldset>
							</li>

							<li class="clear advanced_prop form_prop" id="form_prop_resume"
								style="display: none;zoom: 1">
								<fieldset style="padding-top: 15px;padding-bottom: 20px">
									<legend>Resume Email Options</legend>

									<label class="desc" for="form_review_title">
										Resume Email Subject
									</label>

									<input id="form_resume_subject" class="text large" name="form_resume_subject"
										value="" maxlength="255" type="text">

									<label class="desc" for="form_resume_content">
										Resume Email Content
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="You can insert merge tags and HTML codes into Resume Email Subject and Resume Email Content." />
									</label>

									<textarea class="textarea" rows="10" cols="50" id="form_resume_content"
										style="height: 5em"></textarea>

									<div style="border-bottom: 1px dashed green; height: 15px;margin: 5px 10px 10px 0">
									</div>

									<label class="desc" for="form_resume_from_name">
										From Name
									</label>
									<input id="form_resume_from_name" class="text large" name="form_resume_from_name"
										value="" maxlength="255" type="text">

									<label class="desc" for="form_resume_from_email_address">
										From Email
									</label>
									<input id="form_resume_from_email_address" class="text large"
										name="form_resume_from_email_address" value="" maxlength="255" type="text">

								</fieldset>
							</li>

							<li class="clear advanced_prop form_prop" id="form_prop_review"
								style="display: none;zoom: 1">
								<fieldset style="padding-top: 15px">
									<legend>Review Page Options</legend>

									<label class="desc" for="form_review_title">
										Review Page Title
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter the title to be displayed on the review page." />
									</label>

									<input id="form_review_title" class="text large" name="form_review_title" value=""
										maxlength="255" type="text">

									<label class="desc" for="form_review_description">
										Review Page Description
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Enter some brief description to be displayed on the review page." />
									</label>

									<textarea class="textarea" rows="10" cols="50" id="form_review_description"
										style="height: 2.5em"></textarea>
									<div style="border-bottom: 1px dashed green; height: 15px;margin-right: 10px"></div>
									<div class="left" style="padding-bottom: 5px;margin-top: 12px">
										<input id="form_review_use_text" name="form_review_option" class="radio"
											value="0" type="radio">
										<label class="choice" for="form_review_use_text">Use Text Button</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="This is the default and recommended option. All buttons on review page will use simple text." />
									</div>

									<div class="left" style="padding-left: 5px;padding-bottom: 5px;margin-top: 12px">
										<input id="form_review_use_image" name="form_review_option" class="radio"
											value="1" type="radio">
										<label class="choice" for="form_review_use_image">Use Image Button</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Select this option if you prefer to use your own submit/back image buttons. Make sure to enter the full URL address to your images." />
									</div>

									<div id="div_review_use_text" class="left"
										style="padding-left: 8px;padding-bottom: 10px;width: 92%">
										<label class="desc" for="review_primary_text">Submit Button</label>
										<input id="review_primary_text" class="text large" name="review_primary_text"
											value="" type="text">
										<label id="lbl_review_secondary_text" class="desc" for="review_secondary_text"
											style="margin-top: 3px">Back Button</label>
										<input id="review_secondary_text" class="text large"
											name="review_secondary_text" value="" type="text">
									</div>

									<div id="div_review_use_image" class="left"
										style="padding-left: 8px;padding-bottom: 10px;width: 92%;display: none">
										<label class="desc" for="review_primary_img">Submit Button. Image URL:</label>
										<input id="review_primary_img" class="text large" name="review_primary_img"
											value="http://" type="text">
										<label id="lbl_review_secondary_img" class="desc" for="review_secondary_img"
											style="margin-top: 3px">Back Button. Image URL:</label>
										<input id="review_secondary_img" class="text large" name="review_secondary_img"
											value="http://" type="text">
									</div>
								</fieldset>
							</li>

							<li id="form_prop_protection" class="advanced_prop form_prop">
								<fieldset>
									<legend>Protection &amp; Limit</legend>
									<span>
										<input id="form_password_option" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_password_option">Turn On Password
											Protection</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="If enabled, all users accessing the public form will then be required to type in the password to access the form. Your form is password protected." />
										<div id="form_password" style="display: none">
											<img src="/form_builder/images/icons/key.png" alt="Password : "
												style="vertical-align: middle">
											<input id="form_password_data" autocomplete="off" style="width: 50%"
												class="text" value="" size="25" type="text">
										</div>
									</span>

									<span style="clear: both;display: block">
										<input id="form_captcha" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_captcha">Turn On Spam Protection
											(CAPTCHA)</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="If enabled, an image with random words will be generated and users will be required to enter the correct words in order to submit the form. This is useful to prevent abuse from bots or automated programs usually written to generate spam." />
										<div id="form_captcha_type_option" style="display: block">

											<label class="choice" for="form_captcha_type">Type: </label>
											<select class="select" id="form_captcha_type" name="form_captcha_type"
												autocomplete="off">
												<option value="n">reCAPTCHA V2</option>
												<option value="r">reCAPTCHA</option>
												<option value="i">Simple Image</option>
												<option value="t">Simple Text</option>
											</select>
											<img class="helpmsg" src="/form_builder/images/icons/help2.png"
												style="vertical-align: top" title="You can select the difficulty level of the spam protection.
											 <br/>
											 <br/>
											reCAPTCHA : Display an image with distorted words. An audio also included. This is the most secure but also the hardest to read. Some people might find this annoying.
											 <br/>
											 <br/>
											 reCAPTCHA V2 : Improved version of reCAPTCHA. If you use this, you'll need to fill your keys into the Settings page.
											 <br/>
											 <br/>
											Simple Image : Display an image with a clear and sharp words. Most people will find this easy to read.
											 <br/>
											 <br/>
											Simple Text : Display a text (not an image) which contain simple question to solve." />
										</div>
									</span>
									<span style="clear: both;display: block">
										<input id="form_unique_ip" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_unique_ip">Limit Entry Per IP Address</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="Use this to prevent each client from filling out your form more than the allowed limit within a certain period. This is done by comparing client's IP Address." />
										<div id="form_unique_ip_div" style="display: block">
											Maximum
											<input id="form_unique_ip_maxcount" style="width: 25px" class="text"
												value="" maxlength="255" type="text"> entries per
											<select autocomplete="off" id="form_unique_ip_period" class="select">
												<option value="h">hour</option>
												<option value="d">day</option>
												<option value="w">week</option>
												<option value="m">month</option>
												<option value="y">year</option>
												<option value="l">lifetime</option>
											</select>
										</div>
									</span>
									<span style="clear: both;display: block">
										<input id="form_limit_option" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_limit_option">Limit Submission</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="The form will be turned off after reaching the number of entries defined here." />
										<div id="form_limit_div" style="display: none">
											<img src="/form_builder/images/icons/flag_red.png"
												alt="Maximum accepted entries : " style="vertical-align: middle">
											Maximum accepted entries:
											<input id="form_limit" style="width: 20%" class="text" value=""
												maxlength="255" type="text">
										</div>
									</span>
								</fieldset>
							</li>


							<li id="form_prop_scheduling" class="clear advanced_prop form_prop">

								<fieldset>
									<legend>Automatic Scheduling</legend>
									<div style="padding-bottom: 10px">
										<input id="form_schedule_enable" class="checkbox" value="" style="float: left"
											type="checkbox">
										<label class="choice"
											style="float: left;margin-left: 5px;margin-right:3px;line-height: 1.7"
											for="form_schedule_enable">Enable Automatic Scheduling</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="If you would like to schedule your form to become active at certain period of time only, enable this option." />
									</div>
									<div id="form_prop_scheduling_start" style="display: none">

										<label class="desc">Only Accept Submission From Date: </label>

										<span>
											<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;"
												class="text" name="scheduling_start_mm" id="scheduling_start_mm">
											<label for="scheduling_start_mm">MM</label>
										</span>

										<span>
											<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;"
												class="text" name="scheduling_start_dd" id="scheduling_start_dd">
											<label for="scheduling_start_dd">DD</label>
										</span>

										<span>
											<input type="text" value="" maxlength="4" size="4" style="width: 3em;"
												class="text" name="scheduling_start_yyyy" id="scheduling_start_yyyy">
											<label for="scheduling_start_yyyy">YYYY</label>
										</span>

										<span id="scheduling_cal_start">
											<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;"
												class="text" name="linked_picker_scheduling_start"
												id="linked_picker_scheduling_start">
											<div style="display: none"><img id="scheduling_start_pick_img"
													alt="Pick date." src="/form_builder/images/icons/calendar.png"
													class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
										</span>
										<span>
											<select name="scheduling_start_hour" id="scheduling_start_hour"
												class="select">
												<option value="01">1</option>
												<option value="02">2</option>
												<option value="03">3</option>
												<option value="04">4</option>
												<option value="05">5</option>
												<option value="06">6</option>
												<option value="07">7</option>
												<option value="08">8</option>
												<option value="09">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
											</select>
											<label for="scheduling_start_hour">HH</label>
										</span>
										<span>
											<select name="scheduling_start_minute" id="scheduling_start_minute"
												class="select">
												<option value="00">00</option>
												<option value="15">15</option>
												<option value="30">30</option>
												<option value="45">45</option>
											</select>
											<label for="scheduling_start_minute">MM</label>
										</span>
										<span>
											<select name="scheduling_start_ampm" id="scheduling_start_ampm"
												class="select">
												<option value="am">AM</option>
												<option value="pm">PM</option>
											</select>
											<label for="scheduling_start_ampm">AM/PM</label>
										</span>

									</div>

									<div id="form_prop_scheduling_end" style="display: none">

										<label class="desc">Until Date:</label>
										<span>
											<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;"
												class="text" name="scheduling_end_mm" id="scheduling_end_mm">
											<label for="scheduling_end_mm">MM</label>
										</span>

										<span>
											<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;"
												class="text" name="scheduling_end_dd" id="scheduling_end_dd">
											<label for="scheduling_end_dd">DD</label>
										</span>

										<span>
											<input type="text" value="" maxlength="4" size="4" style="width: 3em;"
												class="text" name="scheduling_end_yyyy" id="scheduling_end_yyyy">
											<label for="scheduling_end_yyyy">YYYY</label>
										</span>

										<span id="scheduling_cal_end">
											<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;"
												class="text" name="linked_picker_scheduling_end"
												id="linked_picker_scheduling_end">
											<div style="display: none"><img id="scheduling_end_pick_img"
													alt="Pick date." src="/form_builder/images/icons/calendar.png"
													class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
										</span>
										<span>
											<select name="scheduling_end_hour" id="scheduling_end_hour" class="select">
												<option value="01">1</option>
												<option value="02">2</option>
												<option value="03">3</option>
												<option value="04">4</option>
												<option value="05">5</option>
												<option value="06">6</option>
												<option value="07">7</option>
												<option value="08">8</option>
												<option value="09">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
											</select>
											<label for="scheduling_end_hour">HH</label>
										</span>
										<span>
											<select name="scheduling_end_minute" id="scheduling_end_minute"
												class="select">
												<option value="00">00</option>
												<option value="15">15</option>
												<option value="30">30</option>
												<option value="45">45</option>
											</select>
											<label for="scheduling_end_minute">MM</label>
										</span>
										<span>
											<select name="scheduling_end_ampm" id="scheduling_end_ampm" class="select">
												<option value="am">AM</option>
												<option value="pm">PM</option>
											</select>
											<label for="scheduling_end_ampm">AM/PM</label>
										</span>

									</div>

								</fieldset>
							</li>

							<li id="form_prop_advanced_option" class="clear advanced_prop form_prop">

								<fieldset>
									<legend>Advanced Option</legend>
									<div style="padding-bottom: 10px">
										<input id="form_custom_script_enable" class="checkbox" value="1"
											style="float: left" type="checkbox">
										<label class="choice"
											style="float: left;margin-left: 5px;margin-right:3px;line-height: 1.7"
											for="form_custom_script_enable">Load Custom Javascript File</label>
										<img class="helpmsg" src="/form_builder/images/icons/help2.png"
											style="vertical-align: top"
											title="You can register your own custom javascript file to run inline with the form. Your script will be loaded each time the form is being displayed." />
									</div>
									<div id="form_custom_script_div"
										style="display: none; margin-left: 25px;margin-bottom: 10px">
										<label class="desc" for="form_custom_script_url">Script URL:</label>
										<input id="form_custom_script_url" name="form_custom_script_url"
											style="width: 90%" class="text" value="" type="text">
									</div>

								</fieldset>
							</li>

							<li id="form_prop_breaker" class="clear advanced_prop form_prop"></li>

							<li id="prop_pagination_style" class="clear">
								<fieldset class="choices">
									<legend>
										Pagination Header Style
										<img class="helpmsg" src="/form_builder/images/icons/help3.png"
											style="vertical-align: top; "
											title="When a form has multiple pages, the pagination header will be displayed on top of your form to let your clients know their progress. This is useful to help your clients understand how much of the form has been completed and how much left to be filled out." />
									</legend>
									<ul>
										<li>
											<input type="radio" id="pagination_style_steps" name="pagination_style"
												class="choices_default" title="Complete Steps">
											<label for="pagination_style_steps" class="choice">Complete Steps</label>
											<img class="helpmsg" src="/form_builder/images/icons/help2.png"
												style="vertical-align: top"
												title="A complete series of all page titles will be displayed, along with the page numbers. The respective page title will be highlighted as the client continue to the next pages. Use this style if your form only has small number of pages." />
										</li>
										<li>
											<input type="radio" id="pagination_style_percentage" name="pagination_style"
												class="choices_default" title="Progress Bar">
											<label for="pagination_style_percentage" class="choice">Progress Bar</label>
											<img class="helpmsg" src="/form_builder/images/icons/help2.png"
												style="vertical-align: top"
												title="A progress bar with a percentage number and the current active page title will be displayed. Use this style if your form has many pages or you need to put longer page title for each page." />
										</li>
										<li>
											<input type="radio" id="pagination_style_disabled" name="pagination_style"
												class="choices_default" title="Disable Pagination Header">
											<label for="pagination_style_disabled" class="choice">Disable</label>
											<img class="helpmsg" src="/form_builder/images/icons/help2.png"
												style="vertical-align: top"
												title="Select this option if you prefer to disable the pagination header completely." />
										</li>
									</ul>
								</fieldset>
							</li>

							<li id="prop_pagination_titles" class="clear">
								<fieldset class="choices">
									<legend>
										Page Titles
										<img class="helpmsg" src="/form_builder/images/icons/help3.png"
											style="vertical-align: top; "
											title="Each page on your form will have its own title which you can specify here. This is useful to organize the form into meaningful content groups. Ensure that the titles of your form pages match your clients' expectations and succintly explain what each page is for. " />
									</legend>
									<ul id="pagination_title_list">
										<li>
											<label for="pagetitleinput_1">1.</label>
											<input type="text" value="" autocomplete="off" class="text"
												id="pagetitle_1" />
										</li>
									</ul>
								</fieldset>

							</li>

						</ul>
					</form>
					<!--  end form properties pane -->

					<div class="bullet_bar_bottom">
						<img style="float: left" src="/form_builder/images/bullet_pink.png" />
						<img style="float: right" src="/form_builder/images/bullet_pink.png" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!-- /#sidebar -->

<div id="dialog-message" title="Error. Unable to complete the task." class="buttons" style="display: none">
	<img src="/form_builder/images/icons/warning.png" title="Warning" />
	<p>
		There was a problem connecting with your website server.<br />
		Please try again within few minutes.<br /><br />
		If the problem persist, please contact us and we'll get back to you immediately!
	</p>
</div>
<div id="dialog-warning" title="Error Title" class="buttons" style="display: none">
	<img src="/form_builder/images/icons/warning.png" title="Warning" />
	<p id="dialog-warning-msg">
		Error
	</p>
</div>
<div id="dialog-confirm-field-delete" title="Are you sure you want to delete this field?" class="buttons"
	style="display: none">
	<span class="icon-bubble-notification"></span>
	<p>
		This action cannot be undone.<br />
		<strong>All data collected by the field will be deleted as well.</strong><br /><br />
	</p>

</div>
<div id="dialog-confirm-choice-delete" title="Are you sure you want to delete this choice?" class="buttons"
	style="display: none">
	<span class="icon-bubble-notification"></span>
	<p>
		This action cannot be undone.<br />
		<strong>All data associated with this choice will be deleted as well.</strong><br /><br />
	</p>

</div>
<div id="dialog-form-saved" title="Success! Your form has been saved" class="buttons" style="display: none">
	<span class="icon-checkmark-circle"></span>
	<p>
		<strong>Do you want to continue editing this form?</strong><br /><br />
	</p>
</div>
<div id="dialog-insert-choices" title="Bulk insert choices" class="buttons" style="display: none">
	<form class="dialog-form">
		<ul>
			<li>
				<label for="bulk_insert_choices" class="description">You can insert a list of choices here. Separate the
					choices with new line. </label>
				<div>
					<textarea cols="90" rows="8" class="element textarea medium" name="bulk_insert_choices"
						id="bulk_insert_choices"></textarea>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="dialog-insert-matrix-rows" title="Bulk insert rows" class="buttons" style="display: none">
	<form class="dialog-form">
		<ul>
			<li>
				<label for="bulk_insert_rows" class="description">You can insert a list of rows here. Separate the rows
					with new line. </label>
				<div>
					<textarea cols="90" rows="8" class="element textarea medium" name="bulk_insert_rows"
						id="bulk_insert_rows"></textarea>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="dialog-insert-matrix-columns" title="Bulk insert columns" class="buttons" style="display: none">
	<form class="dialog-form">
		<ul>
			<li>
				<label for="bulk_insert_columns" class="description">You can insert a list of columns here. Separate the
					labels with new line. </label>
				<div>
					<textarea cols="90" rows="8" class="element textarea medium" name="bulk_insert_columns"
						id="bulk_insert_columns"></textarea>
				</div>
			</li>
		</ul>
	</form>
</div>
<?php
$footer_data = <<<EOT
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.sortable.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="/form_builder/js/jquery.tools.min.js"></script>
<script type="text/javascript" src="/form_builder/js/builder1.8.2.js"></script>
<script type="text/javascript" src="/form_builder/js/datepick/jquery.datepick.js"></script>
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}
    });
</script>
EOT;
require ($prefix_folder . 'includes/footer.php');
?>