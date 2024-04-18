<?php

/**
 * contactSuccess.php template.
 *
 * Displays a contact us form
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require($prefix_folder . 'includes/init.php');

header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");

require($prefix_folder . '../../../config/form_builder_config.php');
require($prefix_folder . 'includes/language.php');
require($prefix_folder . 'includes/db-core.php');
require($prefix_folder . 'includes/common-validator.php');
require($prefix_folder . 'includes/view-functions.php');
require($prefix_folder . 'includes/post-functions.php');
require($prefix_folder . 'includes/filter-functions.php');
require($prefix_folder . 'includes/entry-functions.php');
require($prefix_folder . 'includes/helper-functions.php');
require($prefix_folder . 'includes/theme-functions.php');
#require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
//require($prefix_folder.'lib/swift-mailer/swift_required.php');
require($prefix_folder . 'lib/HttpClient.class.php');
require($prefix_folder . 'lib/recaptchalib.php');
require($prefix_folder . 'lib/recaptchalib2.php');
require($prefix_folder . 'lib/php-captcha/php-captcha.inc.php');
require($prefix_folder . 'lib/text-captcha.php');
require($prefix_folder . 'hooks/custom_hooks.php');

$dbh         = mf_connect_db();
$ssl_suffix = mf_get_ssl_suffix();


if (mf_is_form_submitted()) { //if form submitted
    $input_array   = mf_sanitize($_POST);
    $submit_result = mf_process_form($dbh, $input_array);

    if (!isset($input_array['password'])) { //if normal form submitted

        if ($submit_result['status'] === true) {
            if (!empty($submit_result['form_resume_url'])) { //the user saving a form, display success page with the resume URL
                $_SESSION['mf_form_resume_url'][$input_array['form_id']] = $submit_result['form_resume_url'];

                header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['entry_id']}&done=1");
                exit;
            } else if ($submit_result['logic_page_enable'] === true) { //the page has skip logic enable and a custom destination page has been set
                $target_page_id = $submit_result['target_page_id'];

                if (is_numeric($target_page_id)) {
                    header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&mf_page={$target_page_id}");
                    exit;
                } else if ($target_page_id == 'payment') {
                    //redirect to payment page, based on selected merchant
                    $form_properties = mf_get_form_properties($dbh, $input_array['form_id'], array('payment_merchant_type'));

                    if ($form_properties['payment_merchant_type'] == 'stripe') {
                        //allow access to payment page
                        $_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
                        $_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['entry_id'];

                        header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/payment.php?id={$input_array['form_id']}");
                        exit;
                    } else if ($form_properties['payment_merchant_type'] == 'paypal_standard') {
                        echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
                        exit;
                    }
                } else if ($target_page_id == 'review') {
                    if (!empty($submit_result['origin_page_number'])) {
                        $page_num_params = '&mf_page_from=' . $submit_result['origin_page_number'];
                    }

                    $_SESSION['review_id'] = $submit_result['review_id'];
                    header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/confirm?id={$input_array['form_id']}{$page_num_params}");
                    exit;
                } else if ($target_page_id == 'success') {
                    //redirect to success page
                    if (empty($submit_result['form_redirect'])) {
                        header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['entry_id']}&done=1");
                        exit;
                    } else {
                        echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
                        exit;
                    }
                }
            } else if (!empty($submit_result['review_id'])) { //redirect to review page

                if (!empty($submit_result['origin_page_number'])) {
                    $page_num_params = '&mf_page_from=' . $submit_result['origin_page_number'];
                }

                $_SESSION['review_id'] = $submit_result['review_id'];
                header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/confirmApplication?id={$input_array['form_id']}{$page_num_params}");
                exit;
            } else {
                if (!empty($submit_result['next_page_number'])) { //redirect to the next page number
                    $_SESSION['mf_form_access'][$input_array['form_id']][$submit_result['next_page_number']] = true;

                    header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&mf_page={$submit_result['next_page_number']}");
                    exit;
                } else { //otherwise display success message or redirect to the custom redirect URL or payment page

                    if (mf_is_payment_has_value($dbh, $input_array['form_id'], $submit_result['entry_id'])) {
                        //redirect to credit card payment page, if the merchant is being enabled and the amount is not zero

                        //allow access to payment page
                        $_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
                        $_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['entry_id'];

                        header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/payment.php?id={$input_array['form_id']}");
                        exit;
                    } else {
                        //redirect to success page
                        if (empty($submit_result['form_redirect'])) {
                            header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['entry_id']}&done=1");
                            exit;
                        } else {
                            echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
                            exit;
                        }
                    }
                }
            }
        } else if ($submit_result['status'] === false) { //there are errors, display the form again with the errors
            $old_values     = $submit_result['old_values'];
            $custom_error     = @$submit_result['custom_error'];
            $error_elements = $submit_result['error_elements'];

            $form_params = array();
            $form_params['page_number'] = $input_array['page_number'];
            $form_params['populated_values'] = $old_values;
            $form_params['error_elements'] = $error_elements;
            $form_params['custom_error'] = $custom_error;

            $markup = mf_display_form($dbh, $input_array['form_id'], $form_params);
        }
    } else { //if password form submitted

        if ($submit_result['status'] === true) { //on success, display the form
            $markup = mf_display_form($dbh, $input_array['form_id']);
        } else {
            $custom_error = $submit_result['custom_error']; //error, display the pasword form again

            $form_params = array();
            $form_params['custom_error'] = $custom_error;
            $markup = mf_display_form($dbh, $input_array['form_id'], $form_params);
        }
    }
} else {

    $form_id         = (int) trim($formid);
    $page_number    = (int) trim($_GET['mf_page']);

    $page_number     = mf_verify_page_access($form_id, $page_number);

    $resume_key        = trim($_GET['mf_resume']);
    if (!empty($resume_key)) {
        $_SESSION['mf_form_resume_key'][$form_id] = $resume_key;
    }

    if (!empty($_GET['done']) && (!empty($_SESSION['mf_form_completed'][$form_id]) || !empty($_SESSION['mf_form_resume_url'][$form_id]))) {
        $markup = mf_display_success($dbh, $form_id);

        //If form details are submitted successfully, then create submission entry for reviewers to see
        $q = Doctrine_Query::create()
            ->from("ApColumnPreferences a")
            ->where("a.form_id = ?", $_GET['id']);
        $form_preference = $q->fetchOne();
        if ($form_preference) {
            $submission = new FormEntry();
            $submission->setFormId($_GET['id']);
            $submission->setEntryId($_GET['entryid']);
            $submission->setDeclined('0');
            $submission->setUserId($sf_user->getGuardUser()->getId());

            $app_identifier = $form_preference->getElementName();
            $identifier_type = $form_preference->getPosition();
            $identifier_start = $form_preference->getStartingPoint();

            $new_record_id = $_GET['entryid'];

            if ($identifier_type == "0") //Pick First Letter of Field, Increment
            {
                $app_identifier = parseFull($form_id, $new_record_id, $app_identifier, $identifier_type);

                $new_app_id = $app_identifier;

                //Get the last form entry record
                $q = Doctrine_Query::create()
                    ->from("FormEntry a")
                    ->where("a.application_id LIKE ?", "%" . $app_identifier . "%")
                    ->orderBy("a.id DESC");
                $last_entry = $q->fetchOne();

                if ($last_entry) {
                    $last_id = $last_entry->getApplicationId();

                    $new_app_id = ++$last_id;
                } else {
                    $new_app_id = $new_app_id . $identifier_start;
                }
            }
            if ($identifier_type == "1") //Pick Whole Field, Increment
            {
                $app_identifier = parseFull($form_id, $new_record_id, $app_identifier, $identifier_type);
                $new_app_id = $app_identifier . $identifier_start;
                $new_app_id = ++$new_app_id;
            }
            if ($identifier_type == "2") //Pick First Letter of Field, Don't Increment
            {
                $app_identifier = parse($form_id, $new_record_id, $app_identifier, $identifier_type);
                $new_app_id = $app_identifier . $identifier_start;
            }
            if ($identifier_type == "3") //Pick Whole Field, Don't Increment
            {
                $app_identifier = parseFull($form_id, $new_record_id, $app_identifier, $identifier_type);
                $new_app_id = $app_identifier;
            }

            $submission->setApplicationId($new_app_id);
            $submission->setApproved($identifier_start);

            $submission->save();
        }
    } else {
        $form_params = array();
        $form_params['page_number'] = $page_number;
        $markup = mf_display_form($dbh, $form_id, $form_params);
    }
}
$q = Doctrine_Query::create()
    ->from('ApForms a')
    ->where('a.form_id = ?', $formid);
$formObj = $q->fetchOne();
$form_name = $formObj->getFormName();
$form_description = $formObj->getFormDescription();

$form_name = "";

$sql = "SELECT * FROM ext_translations WHERE field_id = '" . $_GET['id'] . "' AND field_name = 'form_name' AND table_class = 'ap_forms' AND locale = '" . $_SESSION['locale'] . "'";
$rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($sql);
if ($rows) {
    $form_name = $rows;
} else {
    $form_name = $formObj->getFormName();
}


?>
<div class="col-md-7 col-lg-8 col-xl-9">
    <div class="card">
        <!-- Page-Title -->
        <div class="card-heading ms-5 mt-2">
            <h2 class="card-title">
                <?php echo __('Help us improve our service delivery') ?>
            </h2>
        </div>
        <!-- Page-Title -->
        <div class="card-body">
            <div class="card-heading ms-4">
                <?php echo __('We would love to hear your thoughts, concerns or problems with anything so we can improve') ?>

            </div>
            <div class="card-body">
                <?php
                echo $markup;
                ?>
                <?php
                define('CAPTCHA_SESSION_ID', 'php_captcha');
                ?>
            </div>
        </div>
    </div>
</div>