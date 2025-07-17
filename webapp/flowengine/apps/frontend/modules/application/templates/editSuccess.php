<?php

/**
 * editSuccess.php template.
 *
 * Displays a dynamically generated application form
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require($prefix_folder . 'includes/init.php');

require($prefix_folder . '../../../config/form_builder_config.php');
require($prefix_folder . 'includes/db-core.php');
require($prefix_folder . 'includes/helper-functions.php');
require($prefix_folder . 'includes/check-session.php');

require($prefix_folder . 'includes/language.php');
require($prefix_folder . 'includes/common-validator.php');
require($prefix_folder . 'includes/post-functions.php');
require($prefix_folder . 'includes/filter-functions.php');
require($prefix_folder . 'includes/entry-functions.php');
require($prefix_folder . 'includes/view-functions.php');
require($prefix_folder . 'includes/users-functions.php');


$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();
$nav = trim($_GET['nav']);

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

//get form name
$query     = "select 
                    form_name
                from 
                    " . MF_TABLE_PREFIX . "forms 
            where 
                    form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);

$row['form_name'] = mf_trim_max_length($row['form_name'], 65);

if (!empty($row)) {
    $form_name = htmlspecialchars($row['form_name']);
} else {
    die("Error. Unknown form ID.");
}

//get entry status information 
$query = "select 
                `status`,
                `resume_key` 
            from 
                `" . MF_TABLE_PREFIX . "form_{$form_id}` 
        where id=?";
$params = array($entry_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);
$entry_status = $row['status'];
$form_resume_key = $row['resume_key'];
$is_incomplete_entry = false;

if ($entry_status == 2) {
    $is_incomplete_entry = true;
    //OTB redirect to paginated page
    header("Location: {$mf_settings['base_url']}view?id={$form_id}&mf_resume={$form_resume_key}");
    exit();
}

//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
if (!empty($nav)) {
    $entries_options = array();
    $entries_options['is_incomplete_entry'] = $is_incomplete_entry;

    $all_entry_id_array = mf_get_filtered_entries_ids($dbh, $form_id, $entries_options);
    $entry_key = array_keys($all_entry_id_array, $entry_id);
    $entry_key = $entry_key[0];

    if ($nav == 'prev') {
        $entry_key--;
    } else {
        $entry_key++;
    }

    $entry_id = $all_entry_id_array[$entry_key];

    //if there is no entry_id, fetch the first/last member of the array
    if (empty($entry_id)) {
        if ($nav == 'prev') {
            $entry_id = array_pop($all_entry_id_array);
        } else {
            $entry_id = $all_entry_id_array[0];
        }
    }
}

if (mf_is_form_submitted()) { //if form submitted
    $input_array   = mf_sanitize($_POST);
    $submit_result = mf_process_form($dbh, $input_array);
    error_log('---------Submit result------');
    error_log(print_r($submit_result, true));
    error_log('---------input_array------');
    error_log(print_r($input_array, true));

    if ($submit_result['status'] === true) {
        $_SESSION['MF_SUCCESS'] = 'Entry #' . $input_array['edit_id'] . ' has been updated.';

        $application_manager = new ApplicationManager();
        $invoice_manager = new InvoiceManager();
        $application = $application_manager->get_application($input_array['form_id'], $submit_result['new_edit_id']);
        error_log('---------$application->getDeclined()--------' . $application->getDeclined());
        if ($application->getDeclined() == 1) {
            error_log('-----------Resubmit application---');
            $application = $application_manager->resubmit_application($application->getId());
        }

        if ($application->getApproved() == 0) {
            if ($invoice_manager->has_unpaid_invoice($application->getId())) {
                $invoice = $invoice_manager->get_unpaid_invoice($application->getId());

                header("Location: /plan/invoices/pay/id/" . $invoice->getId());
                exit;
            }
        }

        header("Location: /plan/application/view/id/" . $application->getId());
        exit;
    } else if ($submit_result['status'] === false) { //there are errors, display the form again with the errors
        $old_values     = $submit_result['old_values'];
        $custom_error     = @$submit_result['custom_error'];
        $error_elements = $submit_result['error_elements'];

        $form_params = array();
        $form_params['populated_values'] = $old_values;
        $form_params['error_elements']   = $error_elements;
        $form_params['custom_error']      = $custom_error;
        $form_params['edit_id']             = $input_array['edit_id'];
        $form_params['integration_method'] = 'php';
        $form_params['page_number'] = 0; //display all pages (if any) as a single page

        $form_markup = mf_display_form($dbh, $input_array['form_id'], $form_params);
    }
} else { //otherwise, display the form with the values
    //set session value to override password protected form
    $_SESSION['user_authenticated'] = $form_id;

    //set session value to bypass unique checking
    $_SESSION['edit_entry']['form_id']  = $form_id;
    $_SESSION['edit_entry']['entry_id'] = $entry_id;

    $form_values = mf_get_entry_values($dbh, $form_id, $entry_id);

    $form_params = array();
    $form_params['populated_values'] = $form_values;
    $form_params['edit_id']             = $entry_id;
    $form_params['integration_method'] = 'php';
    $form_params['page_number'] = 0; //display all pages (if any) as a single page

    $form_markup = mf_display_form($dbh, $form_id, $form_params);
}
?>
<div class="col-md-8 col-lg-9 col-xl-10">

    <div class="card">
        <div class="card-header">
            <?php include_partial('dashboard/notifications', array('corrections_applications' => $corrections_applications, 'renewal_applications' => $renewal_applications, 'transferring_applications' => $transferring_applications)) ?>
        </div>
        <div class="card-body">
            <?php
            header("Content-Type: text/html; charset=UTF-8");
            echo $form_markup;
            ?>
        </div>
    </div>
</div>