<?php
use_helper('I18N');

$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";

require $prefix_folder . 'includes/init.php';

header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");

//require($prefix_folder.'config.php');
require $prefix_folder . 'includes/language.php';
require $prefix_folder . 'includes/db-core.php';
require $prefix_folder . 'includes/common-validator.php';
require $prefix_folder . 'includes/view-functions.php';
require $prefix_folder . 'includes/post-functions.php';
require $prefix_folder . 'includes/filter-functions.php';
require $prefix_folder . 'includes/entry-functions.php';
require $prefix_folder . 'includes/helper-functions.php';
require $prefix_folder . 'includes/theme-functions.php';
require $prefix_folder . 'lib/recaptchalib.php';
require $prefix_folder . 'lib/php-captcha/php-captcha.inc.php';
require $prefix_folder . 'lib/text-captcha.php';
require $prefix_folder . 'hooks/custom_hooks.php';

$dbh = mf_connect_db();
$ssl_suffix = mf_get_ssl_suffix();

if (mf_is_form_submitted()) {
	//if form submitted
	$input_array = mf_sanitize($_POST);
	$_SESSION['new_userid'] = $sf_user->getAttribute('new_user_id', '');
	$submit_result = mf_process_form($dbh, $input_array);

	if (!isset($input_array['password'])) {
		//if normal form submitted
		if ($submit_result['status'] === true) {
			if (true) {
				//the user saving a form, display success page with the resume URL
				$_SESSION['mf_form_resume_url'][$input_array['form_id']] = $submit_result['form_resume_url'];

				if (sfConfig::get('app_sso_secret')) {
					$profileinfo = new MfUserProfile();
					$profileinfo->setFormId($input_array['form_id']);
					$profileinfo->setEntryId($submit_result['membership_entry_id']);
					$profileinfo->setUserId($sf_user->getGuardUser()->getId());
					$profileinfo->save();
				} else {

					$profileinfo = new MfUserProfile();
					$profileinfo->setFormId($input_array['form_id']);
					$profileinfo->setEntryId($submit_result['membership_entry_id']);
					$profileinfo->setUserId($_SESSION['new_userid']);
					$profileinfo->save();
				}

				if (sfConfig::get('app_sso_secret')) {
					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . "/plan/mfRegister/notification?id={$_GET['formid']}&done=2");
					exit;
				} else {
					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . "/plan/mfRegister/notification?id={$_GET['formid']}&done=1");
					exit;
				}
			} else if ($submit_result['logic_page_enable'] === true) {
				//the page has skip logic enable and a custom destination page has been set
				$target_page_id = $submit_result['target_page_id'];

				if (is_numeric($target_page_id)) {
					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?formid={$_GET['formid']}&id={$input_array['form_id']}&mf_page={$target_page_id}");
					exit;
				} else if ($target_page_id == 'payment') {
					//redirect to payment page, based on selected merchant
					$form_properties = mf_get_form_properties($dbh, $input_array['form_id'], array('payment_merchant_type'));

					if ($form_properties['payment_merchant_type'] == 'stripe') {
						//allow access to payment page
						$_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
						$_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['membership_entry_id'];

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
						header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?formid={$_GET['formid']}&id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&done=1");
						exit;
					} else {
						echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
						exit;
					}
				}

			} else if (!empty($submit_result['review_id'])) {
				//redirect to review page

				if (!empty($submit_result['origin_page_number'])) {
					$page_num_params = '&mf_page_from=' . $submit_result['origin_page_number'];
				}

				$_SESSION['review_id'] = $submit_result['review_id'];
				header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/confirmApplication?id={$input_array['form_id']}{$page_num_params}");
				exit;
			} else {
				if (!empty($submit_result['next_page_number'])) {
					//redirect to the next page number
					$_SESSION['mf_form_access'][$input_array['form_id']][$submit_result['next_page_number']] = true;

					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?formid={$_GET['formid']}&id={$input_array['form_id']}&mf_page={$submit_result['next_page_number']}");
					exit;
				} else {
					//otherwise display success message or redirect to the custom redirect URL or payment page

					if (mf_is_payment_has_value($dbh, $input_array['form_id'], $submit_result['membership_entry_id'])) {
						//redirect to credit card payment page, if the merchant is being enabled and the amount is not zero

						//allow access to payment page
						$_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
						$_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['membership_entry_id'];

						header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/payment.php?id={$input_array['form_id']}");
						exit;
					} else {
						//redirect to success page
						if (empty($submit_result['form_redirect'])) {
							header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?formid={$_GET['formid']}&id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&done=1");
							exit;
						} else {
							echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
							exit;
						}
					}
				}
			}
		} else if ($submit_result['status'] === false) {
			//there are errors, display the form again with the errors
			$old_values = $submit_result['old_values'];
			$custom_error = @$submit_result['custom_error'];
			$error_elements = $submit_result['error_elements'];

			$form_params = array();
			$form_params['page_number'] = $input_array['page_number'];
			$form_params['populated_values'] = $old_values;
			$form_params['error_elements'] = $error_elements;
			$form_params['custom_error'] = $custom_error;

			$markup = mf_display_form($dbh, $input_array['form_id'], $form_params);
		}
	} else {
		//if password form submitted

		if ($submit_result['status'] === true) {
			//on success, display the form
			$markup = mf_display_form($dbh, $input_array['form_id']);
		} else {
			$custom_error = $submit_result['custom_error']; //error, display the pasword form again

			$form_params = array();
			$form_params['custom_error'] = $custom_error;
			$markup = mf_display_form($dbh, $input_array['form_id'], $form_params);
		}
	}
} else {
	$form_id = (int) trim($_GET['formid']);
	$page_number = (int) trim($_GET['mf_page']);

	$page_number = mf_verify_page_access($form_id, $page_number);

	$resume_key = trim($_GET['mf_resume']);
	if (!empty($resume_key)) {
		$_SESSION['mf_form_resume_key'][$form_id] = $resume_key;
	}

	if (!empty($_GET['done']) && (!empty($_SESSION['mf_form_completed'][$form_id]) || !empty($_SESSION['mf_form_resume_url'][$form_id]))) {
		$markup = mf_display_success($dbh, $form_id);

		$additional_form = new mfUserProfile();
		$additional_form->setFormId($form_id);
		$additional_form->setEntryId($_GET['entryid']);
		$additional_form->setUserId($userid);
		$additional_form->save();
	} else {
		$form_params = array();
		$form_params['page_number'] = $page_number;
		$markup = mf_display_form($dbh, $form_id, $form_params);
	}

	$q = Doctrine_Query::create()
		->from('ApForms a')
		->where('a.form_id = ?', $_GET['formid']);
	$formObj = $q->fetchOne();
	$form_name = $formObj->getFormName();
	$form_description = $formObj->getFormDescription();

	$form_name = "";

	$sql = "SELECT * FROM ext_translations WHERE field_id = ? AND field_name = 'form_name' AND table_class = 'ap_forms' AND locale = ?";
	$sth = mf_do_query($sql, array($_GET['id'], $_SESSION['locale']), $dbh);
	while ($row = mf_do_fetch_result($sth)) {
		$form_name = $row['trl_content'];
	}
	if (!strlen($form_name)) {
		$form_name = $formObj->getFormName();
	}
}

?>



<?php
echo $markup;

define('CAPTCHA_SESSION_ID', 'php_captcha');

?>

