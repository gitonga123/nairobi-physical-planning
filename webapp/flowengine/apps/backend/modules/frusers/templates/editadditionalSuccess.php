<?php
/**
 * editSuccess.php template.
 *
 * Allows editing of additional client details
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
require($prefix_folder.'includes/init.php');

require($prefix_folder.'../../../config/form_builder_config.php');
require($prefix_folder.'includes/db-core.php');
require($prefix_folder.'includes/helper-functions.php');
require($prefix_folder.'includes/check-session.php');

require($prefix_folder.'includes/language.php');
require($prefix_folder.'includes/common-validator.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/filter-functions.php');
require($prefix_folder.'includes/entry-functions.php');
require($prefix_folder.'includes/view-functions.php');
require($prefix_folder.'includes/users-functions.php');

$form_id  = (int) trim($formid);
$entry_id = (int) trim($entryid);

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);


//check permission, is the user allowed to access this page?
if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
    $user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

    //this page need edit_entries permission
    if(empty($user_perms['edit_entries'])){
        $_SESSION['MF_DENIED'] = "You don't have permission to edit this entry.";

        $ssl_suffix = mf_get_ssl_suffix();
        header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
        exit;
    }
}

//get form name
$query 	= "select
					 form_name
			     from
			     	 ".MF_TABLE_PREFIX."forms
			    where
			    	 form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

if(!empty($row)){
    $form_name = htmlspecialchars($row['form_name']);
}

//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
if(!empty($nav)){
    $all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id);
    $entry_key = array_keys($all_entry_id_array,$entry_id);
    $entry_key = $entry_key[0];

    if($nav == 'prev'){
        $entry_key--;
    }else{
        $entry_key++;
    }

    $entry_id = $all_entry_id_array[$entry_key];

    //if there is no entry_id, fetch the first/last member of the array
    if(empty($entry_id)){
        if($nav == 'prev'){
            $entry_id = array_pop($all_entry_id_array);
        }else{
            $entry_id = $all_entry_id_array[0];
        }
    }
}


if(mf_is_form_submitted()){ //if form submitted
    $input_array   = mf_sanitize($_POST);
    $submit_result = mf_process_form($dbh,$input_array);

    if($submit_result['status'] === true){

    }else if($submit_result['status'] === false){ //there are errors, display the form again with the errors
        $old_values 	= $submit_result['old_values'];
        $custom_error 	= @$submit_result['custom_error'];
        $error_elements = $submit_result['error_elements'];

        $form_params = array();
        $form_params['populated_values'] = $old_values;
        $form_params['error_elements']   = $error_elements;
        $form_params['custom_error'] 	 = $custom_error;
        $form_params['edit_id']			 = $input_array['edit_id'];
        $form_params['integration_method'] = 'php';
        $form_params['page_number'] = 0; //display all pages (if any) as a single page

        $form_markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
    }

}else{ //otherwise, display the form with the values
    //set session value to override password protected form
    $_SESSION['user_authenticated'] = $form_id;

    //set session value to bypass unique checking
    $_SESSION['edit_entry']['form_id']  = $form_id;
    $_SESSION['edit_entry']['entry_id'] = $entry_id;

    $form_values = mf_get_entry_values($dbh,$form_id,$entry_id);

    $form_params = array();
    $form_params['populated_values'] = $form_values;
    $form_params['edit_id']			 = $entry_id;
    $form_params['integration_method'] = 'php';
    $form_params['page_number'] = 0; //display all pages (if any) as a single page

    $form_markup = mf_display_form($dbh,$form_id,$form_params);
}

$q = Doctrine_Query::create()
                  ->from('mfUserProfile a')
                  ->where('a.form_id = ?', $form_id)
                  ->andWhere('a.entry_id = ?', $entry_id);
$mfprofile = $q->fetchOne();	

$q = Doctrine_Query::create()
                  ->from('SfGuardUserProfile a')
                  ->where('a.user_id = ?', $mfprofile->getUserId());
$profile = $q->fetchOne();
?>

<div class="pageheader">
<h2><i class="fa fa-envelope"></i><span>Create an new client from here</span></h2>
<div class="breadcrumb-wrapper">

<ol class="breadcrumb">
<li>
<a href="/backend.php">Home</a>
</li>
<li>
<a href="/backend.php/applications/list/get/your">Applications</a>
</li>
<li class="active">Stages</li>
</ol>
</div>
</div>

<div class="contentpanel">
<div class="row">
    <?php echo $form_markup; ?>
</div>
</div>