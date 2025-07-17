<?php

/**
 * viewSuccess.php template.
 *
 * Displays a dynamically generated application form
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require $prefix_folder . 'includes/init.php';

header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");

require $prefix_folder . '../../../config/form_builder_config.php';
require $prefix_folder . 'includes/language.php';
require $prefix_folder . 'includes/db-core.php';
require $prefix_folder . 'includes/common-validator.php';
require $prefix_folder . 'includes/view-functions.php';
require $prefix_folder . 'includes/post-functions.php';
require $prefix_folder . 'includes/filter-functions.php';
require $prefix_folder . 'includes/entry-functions.php';
require $prefix_folder . 'includes/helper-functions.php';
require $prefix_folder . 'includes/theme-functions.php';
#require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
//require($prefix_folder.'lib/swift-mailer/swift_required.php');
require $prefix_folder . 'lib/HttpClient.class.php';
require $prefix_folder . 'lib/recaptchalib.php';
require $prefix_folder . 'lib/recaptchalib2.php';
require $prefix_folder . 'lib/php-captcha/php-captcha.inc.php';
require $prefix_folder . 'lib/text-captcha.php';
require $prefix_folder . 'hooks/custom_hooks.php';

$dbh = mf_connect_db();
$ssl_suffix = mf_get_ssl_suffix();

if (mf_is_form_submitted()) {
	//if form submitted
	if ($_POST['save_as_draft'] || $_POST['save_as_draft2']) {
		$_SESSION['save_as_draft'] = true;
	} else {
		$_SESSION['save_as_draft'] = false;
	}

	$input_array = mf_sanitize($_POST);
	$submit_result = mf_process_form($dbh, $input_array);
	if (!isset($input_array['password'])) {
		//if normal form submitted
		if ($submit_result['status'] === true) {
			$q = Doctrine_Query::create()
				->from('mfUserProfile a')
				->where('a.user_id = ?', $sf_user->getGuardUser()->getId());
			$profile = $q->fetchOne();

			if (!$profile || empty($profile)) {
				$userprofile = new MfUserProfile();
				$userprofile->setUserId($sf_user->getGuardUser()->getId());
				$userprofile->setFormId($input_array['form_id']);
				$userprofile->setEntryId($submit_result['membership_entry_id']);
				$userprofile->setCreatedAt(date("Y-m-d"));
				$userprofile->setUpdatedAt(date("Y-m-d"));
				$userprofile->save();
			} else {
				$profile->setUserId($sf_user->getGuardUser()->getId());
				$profile->setFormId($input_array['form_id']);
				$profile->setEntryId($submit_result['membership_entry_id']);
				$profile->save();
			}
			if (!empty($submit_result['form_resume_url'])) {
				//the user saving a form, display success page with the resume URL
				// error_log('-------------form_resume_url--------' . $submit_result['form_resume_url']);
				$_SESSION['mf_form_resume_url'][$input_array['form_id']] = $submit_result['form_resume_url'];
				// error_log('----------save_as_draft-------' . $_SESSION['save_as_draft']);
				if ($_SESSION['save_as_draft']) {
					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&draft=1&done=1");
				} else {
					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&done=1");
				}
				exit;
			} else if ($submit_result['logic_page_enable'] === true) {
				//the page has skip logic enable and a custom destination page has been set
				//// error_log('------------logic_page_enable-------' . $submit_result['logic_page_enable']);
				$target_page_id = $submit_result['target_page_id'];
				//// error_log('------------target_page_id------' . $target_page_id);
				if (is_numeric($target_page_id)) {
					header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&mf_page={$target_page_id}");
					exit;
				} else if ($target_page_id == 'payment') {
					// error_log('Target page id is payment');
					//redirect to payment page, based on selected merchant
					$form_properties = mf_get_form_properties($dbh, $input_array['form_id'], array('payment_merchant_type', 'payment_onsubmission'));

					if (in_array($form_properties['payment_merchant_type'], array('stripe', 'authorizenet', 'paypal_rest', 'braintree', 'pesaflow_standard', 'pesaflow_cart'))) {
						//allow access to payment page
						$_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
						$_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['membership_entry_id'];

						header("Location: /plan/forms/payment?id={$input_array['form_id']}");
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
					header("Location: /plan/forms/confirm?id={$input_array['form_id']}{$page_num_params}");
					exit;
				} else if ($target_page_id == 'success') {
					//redirect to success page
					if (!empty($submit_result['logic_success_enable']) && (($logic_redirect_url = mf_get_logic_success_redirect_url($dbh, $input_array['form_id'], $submit_result['membership_entry_id'])) != '')) {
						echo "<script type=\"text/javascript\">top.location.replace('{$logic_redirect_url}')</script>";
						exit;
					} else if (empty($submit_result['form_redirect'])) {
						if ($_SESSION['save_as_draft']) {
							header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&draft=1&done=1");
						} else {
							header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&done=1");
						}
						exit;
					} else {
						echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
						exit;
					}
				}

			} else if (!empty($submit_result['review_id'])) {
				//redirect to review page
				// error_log('-------------review_id------' . $submit_result['review_id']);
				if (!empty($submit_result['origin_page_number'])) {
					$page_num_params = '&mf_page_from=' . $submit_result['origin_page_number'];
				}

				$_SESSION['review_id'] = $submit_result['review_id'];
				header("Location: /plan/forms/confirm?id={$input_array['form_id']}{$page_num_params}");
				exit;
			} else {
				if (!empty($submit_result['next_page_number'])) {
					//redirect to the next page number
					// error_log('------------next_page_number------' . $submit_result['next_page_number']);
					if ($_SESSION['save_as_draft']) {
						header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&draft=1&done=1");
					} else {
						// error_log('---------next page number--------' . $submit_result['next_page_number']);
						$_SESSION['mf_form_access'][$input_array['form_id']][$submit_result['next_page_number']] = true;

						header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&mf_page={$submit_result['next_page_number']}");
					}
					exit;
				} else {
					//otherwise display success message or redirect to the custom redirect URL or payment page
					// error_log('next page number empty');
					if (mf_is_payment_has_value($dbh, $input_array['form_id'], $submit_result['membership_entry_id'])) {
						// error_log('mf_is_payment_has_value is true');
						//redirect to credit card payment page, if the merchant is being enabled and the amount is not zero

						//allow access to payment page
						$_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
						// error_log('---------entry_id---' . $submit_result['membership_entry_id']);
						$_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['membership_entry_id'];

						header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/payment?id={$input_array['form_id']}");
						exit;
					} else {
						// error_log('mf_is_payment_has_value is false');
						//redirect to success page
						if (empty($submit_result['form_redirect'])) {
							if ($_SESSION['save_as_draft']) {
								header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&draft=1&done=1");
							} else {
								// error_log('form_redirect is empty');
								header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?id={$input_array['form_id']}&entryid={$submit_result['membership_entry_id']}&done=1");
							}
							exit;
						} else {
							// error_log('form_redirect not empty ' . $submit_result['form_redirect']);
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
			// error_log('------OLD VAL---------');
			// error_log(print_r($old_values, true));
			$form_params = array();
			$form_params['page_number'] = $input_array['page_number'];
			// error_log('--------Page no------' . $input_array['page_number']);
			$form_params['populated_values'] = $old_values;
			$form_params['error_elements'] = $error_elements;
			$form_params['custom_error'] = $custom_error;
			$form_params['locale'] = $sf_user->getCulture();

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
			$form_params['locale'] = $sf_user->getCulture();
			$markup = mf_display_form($dbh, $input_array['form_id'], $form_params);
		}
	}
} else {
	$form_id = (int) trim($_GET['id']);
	$page_number = (int) trim($_GET['mf_page']);

	$page_number = mf_verify_page_access($form_id, $page_number);

	$resume_key = trim($_GET['mf_resume']);
	if (!empty($resume_key)) {
		$_SESSION['mf_form_resume_key'][$form_id] = $resume_key;
	}
	if ($_GET['linkto']) {
		$_SESSION["main_application"] = $_GET['linkto'];
	}
	//if(!empty($_GET['done']) && (!empty($_SESSION['mf_form_completed'][$form_id]) || !empty($_SESSION['mf_form_resume_url'][$form_id]))){
	if (!empty($_GET['done'])) {
		// error_log('------------DONE-----------');
		$markup = mf_display_success($dbh, $form_id);
		$user = $sf_user->getGuarduser();

		$q = Doctrine_Query::create()
			->from('mfUserProfile a')
			->where('a.user_id = ?', $user->getId());
		$profile = $q->fetchOne();

		if ($profile) {
			// error_log('-------------profile--------------');
			$markup = mf_display_success($dbh, $form_id);
		} else {
			// error_log('------------NO existing-----profile--------------');
			$userprofile = new MfUserProfile();
			$userprofile->setUserId($sf_user->getGuardUser()->getId());
			$userprofile->setFormId($_GET['id']);
			$userprofile->setEntryId($_GET['entryid']);
			$userprofile->setCreatedAt(date("Y-m-d"));
			$userprofile->setUpdatedAt(date("Y-m-d"));
			$userprofile->save();
			$markup = mf_display_success($dbh, $form_id);
		}

	} else {
		// error_log('----------NOT DONE---------');
		$form_params = array();
		$form_params['page_number'] = $page_number;
		$form_params['locale'] = $sf_user->getCulture();
		//OTB destroy session
		$_SESSION['mf_invoice'] = false;

		$markup = mf_display_form($dbh, $form_id, $form_params);
	}
}

$query = "select
                A.form_name,
                ifnull(B.entries_sort_by,'id-desc') entries_sort_by,
                ifnull(B.entries_filter_type,'all') entries_filter_type,
                ifnull(B.entries_enable_filter,0) entries_enable_filter
            from
                " . MF_TABLE_PREFIX . "forms A left join " . MF_TABLE_PREFIX . "entries_preferences B
                on
                A.form_id=B.form_id and B.user_id=?
            where
                A.form_id = ?";
$params = array($_SESSION['mf_user_id'], $form_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);

if (!empty($row)) {

	$row['form_name'] = mf_trim_max_length($row['form_name'], 65);

	if (!empty($row['form_name'])) {
		$form_name = htmlspecialchars($row['form_name']);
	} else {
		$form_name = 'Untitled Form (#' . $form_id . ')';
	}
}
?>
<!--div id="main_body" style="margin: 0px;"-->
<?php

header("Content-Type: text/html; charset=UTF-8");
echo $markup;
?>
<!--/div-->
