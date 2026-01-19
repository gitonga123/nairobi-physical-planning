<?php
/**
 * confirmSuccess.php template.
 *
 * Displays a dynamically generated application form
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
require($prefix_folder.'includes/init.php');

require($prefix_folder.'../../../config/form_builder_config.php');
require($prefix_folder.'includes/db-core.php');
require($prefix_folder.'includes/helper-functions.php');

require($prefix_folder.'includes/language.php');
require($prefix_folder.'includes/common-validator.php');
require($prefix_folder.'includes/view-functions.php');
require($prefix_folder.'includes/theme-functions.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/entry-functions.php');
#require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
//require($prefix_folder.'lib/swift-mailer/swift_required.php');
require($prefix_folder.'lib/HttpClient.class.php');
require($prefix_folder.'hooks/custom_hooks.php');

//get data from database
$dbh 		= mf_connect_db();
$ssl_suffix = mf_get_ssl_suffix();

$form_id    = (int) trim($_REQUEST['id']);

var_dump(print_r($_POST, true));

if(!empty($_POST['review_submit']) || !empty($_POST['review_submit_x'])){ //if form submitted
	error_log('-------------review_submit-------------review_submit_x-------');
    //commit data from review table to actual table
    //however, we need to check if this form has payment enabled or not

    //if the form doesn't have any payment enabled, continue with commit and redirect to success page
    $form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','payment_delay_notifications','payment_merchant_type','payment_onsubmission'));

    if(!$form_properties['payment_enable_merchant']){
		error_log('---------------payment_enable_merchant false-------');
        $record_id 	   = $_SESSION['review_id'];
        $commit_result = mf_commit_form_review($dbh,$form_id,$record_id);

        unset($_SESSION['review_id']);

        if(empty($commit_result['form_redirect'])){
            header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
            exit;
        }else{
            echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
            exit;
        }
    }else{
        error_log('---------------payment_enable_merchant true-------');
       // error_log('-------------------SESSION------------');
        //error_log(var_export($_SESSION,true));
        //if the form has payment enabled, continue commit and redirect to payment page
        $record_id 	    = $_SESSION['review_id'];
        $commit_options = array();

        //delay notifications only available on some merchants
        if(!empty($form_properties['payment_delay_notifications']) && in_array($form_properties['payment_merchant_type'], array('jambo_pay', 'malipo','stripe','paypal_standard','authorizenet','paypal_rest','braintree','pesaflow_standard','pesaflow_cart'))){
            $commit_options['send_notification'] = false;
        }
        error_log('--------mf_success_entry_id-------'.$_SESSION['mf_success_entry_id']);
        $commit_result = mf_commit_form_review($dbh,$form_id,$record_id,$commit_options);
        // ADD delete previous entry with resume key
        error_log('--------mf_success_entry_id-------'.$_SESSION['mf_success_entry_id']);
        error_log('--------mf_commit_form_review----------');
        error_log(print_r($commit_result,true));
        unset($_SESSION['review_id']);

        if(in_array($form_properties['payment_merchant_type'], array('jambo_pay', 'malipo','stripe','authorizenet','paypal_rest','braintree','pesaflow_standard','pesaflow_cart')) || $form_properties['payment_onsubmission']){
            if(mf_is_payment_has_value($dbh,$form_id,$commit_result['record_insert_id'])){
                error_log('-------mf_is_payment_has_value true---');
                //allow access to payment page
                $_SESSION['mf_form_payment_access'][$form_id] = true;
                $_SESSION['mf_payment_record_id'][$form_id] = $commit_result['record_insert_id'];

                header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/payment?id={$form_id}");
                exit;
            }else{
                error_log('-------mf_is_payment_has_value false---');
                error_log(mf_is_payment_has_value($dbh,$form_id,$commit_result['record_insert_id']));
                //if the amount is zero, display success page instead
                if(empty($commit_result['form_redirect'])){
                    error_log('--------form_redirect empty----');
                    header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
                    exit;
                }else{
                    error_log('--------form_redirect mot empty----');
                    echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
                    exit;
                }
            }
        }else if($form_properties['payment_merchant_type'] == 'paypal_standard'){
            //error_log('----------paypal_standard-----------');
            if(empty($commit_result['form_redirect'])){
                header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
                exit;
            }else{
                echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
                exit;
            }
        }else if($form_properties['payment_merchant_type'] == 'check'){
            //error_log('----------check-----------');
            //redirect to either success page or custom redirect URL
            if(empty($commit_result['form_redirect'])){
                header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
                exit;
            }else{
                echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
                exit;
            }
        }
        // Add malipo as payment option
        else if($form_properties['payment_merchant_type'] == 'malipo'){
            error_log("Debug>>> malipo ------------ We should call the API endpoint that will request Malipo to create payment request for payments items set on the form") ;
            if(empty($commit_result['form_redirect'])){
                header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
                exit;
            }else{
                echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
                exit;
            }
        }
        else{
           error_log('------------UnKNOWN merchant----------');
           error_log($form_properties['payment_merchant_type']);
            if(empty($commit_result['form_redirect'])){
                header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
                exit;
            }else{
                echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
                exit;
            }
        }

    }

}elseif (!empty($_POST['review_back']) || !empty($_POST['review_back_x'])){
	error_log('-------------review_back-------------review_back_x-------');
    //go back to form
    $origin_page_num = (int) $_POST['mf_page_from'];
    header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/view?id={$form_id}&mf_page={$origin_page_num}");
    exit;
}else{
	error_log('------------- -------------empty review_back_x------- empty review_back-------');
    if(empty($form_id)){
        die('ID required.');
    }

    if(!empty($_GET['done']) && !empty($_SESSION['mf_form_completed'][$form_id])){
        //Check if application is created. If not then create one.
        $record_id = $_SESSION['mf_success_entry_id'];
        error_log('-----------record_id------'.$record_id);

        //We will use the application manager to create new applications or drafts from form submissions
        $application_manager = new ApplicationManager();

        //Check if an application already exists for the form submission to prevent double entry
        if($application_manager->application_exists($form_id, $record_id)) {
            error_log('---------Application exists---------------');
            //If save as draft/resume later was clicked then do nothing
            $submission = $application_manager->get_application($form_id, $record_id);
        }
        else {
            error_log('---------Application doesnt exists---------------');
            //If save as draft/resume later was clicked then create draft application
            $submission = $application_manager->create_application($form_id, $record_id, $sf_user->getGuardUser()->getId(), false);
        }

        $markup = mf_display_success($dbh,$form_id);
    }else{
        if(empty($_SESSION['review_id'])){
            die("Your session has been expired. Please <a href='view?id={$form_id}'>click here</a> to start again.");
        }else{
            $record_id = $_SESSION['review_id'];
        }

        $from_page_num = (int) $_GET['mf_page_from'];
        if(empty($from_page_num)){
            $form_page_num = 1;
        }

        $form_params = array();
        $form_params['locale'] = $sf_user->getCulture();

        $markup = mf_display_form_review($dbh,$form_id,$record_id,$from_page_num, $form_params);
    }
}

$query = "select
                A.form_name,
                ifnull(B.entries_sort_by,'id-desc') entries_sort_by,
                ifnull(B.entries_filter_type,'all') entries_filter_type,
                ifnull(B.entries_enable_filter,0) entries_enable_filter
            from
                ".MF_TABLE_PREFIX."forms A left join ".MF_TABLE_PREFIX."entries_preferences B
                on
                A.form_id=B.form_id and B.user_id=?
            where
                A.form_id = ?";
$params = array($_SESSION['mf_user_id'],$form_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

if(!empty($row)){

    $row['form_name'] = mf_trim_max_length($row['form_name'],65);

    if(!empty($row['form_name'])){
        $form_name = htmlspecialchars($row['form_name']);
    }else{
        $form_name = 'Untitled Form (#'.$form_id.')';
    }
}
?>


<?php
    header("Content-Type: text/html; charset=UTF-8");
    echo $markup;
?>
