<?php

/**
 * editSuccess.php template.
 *
 * Allows client and resubmit an application
 *
 * @package    frontend
 * @subpackage application
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

if($application->getStage()->getAllowEdit() == 0 || !$sf_user->mfHasCredential("accesssubmenu".$application->getApproved())){
    exit;
}

$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

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
        $payments_manager = new PaymentsManager();
        $payments_manager->generate_invoice_from_difference($application->getId());
        //OTB conflict can occur if task id is saved for a different application - safer to return user to application view
        /*if($sf_user->getAttribute("task_id"))
        {
            header("Location: /backend.php/tasks/view/id/".$sf_user->getAttribute("task_id"));
            exit;
        }
        else 
        {*/
            header("Location: /backend.php/applications/view/id/".$application->getId());
            exit;
        //}

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
        $form_params['is_application'] = true;
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
    $form_params['is_application'] = true;
    $form_params['page_number'] = 0; //display all pages (if any) as a single page

    $form_markup = mf_display_form($dbh,$form_id,$form_params);
}
?>
<div class="contentpanel">

    <div class="row">
        <div class="col-sm-12">
        <?php
        //Displays the user panel
        include_partial('tasks/task_user_info', array('application' => $application));
        ?>
        </div>
    </div>

    <div class="row">

    <div class="col-sm-12">

    <div class="panel panel-default">

     <div class="panel-heading">
              <h5 class="bug-key-title"><?php echo $application->getApplicationId(); ?></h5>
              <div class="panel-title">
                <?php
                  $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_id = ?", $application->getFormId())
                      ->limit(1);
                  $form = $q->fetchOne();
                  if($form)
                  {
                      echo $form->getFormName();
                  }
                ?>
              </div>
       </div>

      <div class="panel-body padding-0 panel-bordered" style="border-top:0;">

        <?php echo $form_markup; ?>

      </div>

    </div>

    </div>

   </div>
    
</div>

<script language="javascript">
    function filter_dropdown(form_id, element_id, link_id, value) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                document.getElementById("li_" + link_id + "_filter").innerHTML = xhttp.responseText;
            }
        };
        xhttp.open("GET", "/plan/forms/filterdropdown?form_id=" + form_id + "&element_id=" + element_id + "&link_id=" + link_id + "&option_id=" + value, true);
        xhttp.send();
    }
</script>
