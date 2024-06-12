<?php
$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require($prefix_folder . 'includes/init.php');

require($prefix_folder . '../../../config/form_builder_config.php');
require($prefix_folder . 'includes/db-core.php');
require($prefix_folder . 'includes/helper-functions.php');

require($prefix_folder . 'includes/language.php');
require($prefix_folder . 'includes/common-validator.php');
require($prefix_folder . 'includes/view-functions.php');
require($prefix_folder . 'includes/theme-functions.php');
require($prefix_folder . 'includes/post-functions.php');
require($prefix_folder . 'includes/entry-functions.php');
#require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
//require($prefix_folder.'lib/swift-mailer/swift_required.php');
require($prefix_folder . 'lib/HttpClient.class.php');
require($prefix_folder . 'hooks/custom_hooks.php');

$dbh 		  = mf_connect_db();
$form_id 	  = (int) trim($_REQUEST['id']);

if ($form_id == null) {
	$form_id = $sf_user->getAttribute("form_id");
	$_SESSION['mf_payment_record_id'][$form_id] = $sf_user->getAttribute("entry_id");
	$_SESSION['mf_form_payment_access'][$form_id]  = true;
	$_SESSION['mf_form_completed'][$form_id] = true;
}

$paid_form_id = (int) trim($_POST['form_id_redirect']);

$_SESSION['profile_id'] = false;

if (!empty($paid_form_id) && $_SESSION['mf_payment_completed'][$paid_form_id] === true) {
	//when payment succeeded, $paid_form_id should contain the form id number
	$form_properties = mf_get_form_properties($dbh, $paid_form_id, array('form_redirect_enable', 'form_redirect', 'form_review', 'form_page_total', 'payment_delay_notifications', 'logic_success_enable'));

	//process any delayed notifications
	mf_process_delayed_notifications($dbh, $paid_form_id, $_SESSION['mf_payment_record_id'][$paid_form_id]);

	//redirect to success page, which might be coming from the logic, the default success page or the custom redirect URL being set on form properties
	if (!empty($form_properties['logic_success_enable']) && (($logic_redirect_url = mf_get_logic_success_redirect_url($dbh, $paid_form_id, $_SESSION['mf_payment_record_id'][$paid_form_id])) != '')) {
		echo "<script type=\"text/javascript\">top.location.replace('{$logic_redirect_url}')</script>";
		exit;
	} else if (!empty($form_properties['form_redirect_enable']) && !empty($form_properties['form_redirect'])) {

		//parse redirect URL for any template variables first
		$form_properties['form_redirect'] = mf_parse_template_variables($dbh, $paid_form_id, $_SESSION['mf_payment_record_id'][$paid_form_id], $form_properties['form_redirect']);

		echo "<script type=\"text/javascript\">top.location.replace('{$form_properties['form_redirect']}')</script>";
		exit;
	} else {
		$ssl_suffix = mf_get_ssl_suffix();

		header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/view?id={$paid_form_id}&done=1");
		exit;
	}
} else {
	//display payment form
	if (empty($form_id)) {
		die('ID required.');
	} else {
		$form_params = array();
		$record_id = $_SESSION['mf_payment_record_id'][$form_id];
		error_log('--------record_id--------' . $record_id);
		if ($_GET['invoice']) {
			$q = Doctrine_Query::create()
				->from("MfInvoice a")
				->where("a.id = ?", $_GET['invoice']);
			$invoice = $q->fetchOne();

			$application = $invoice->getFormEntry();

			$form_id = $application->getFormId();
			$record_id = $application->getEntryId();
		}
		error_log('--------record_id--invoice------' . $record_id);
		//if payment token exist, the user is resuming payment from previously unpaid entry
		if (!empty($_GET['pay_token'])) {
			$form_params['pay_token'] = $_GET['pay_token'];
		}

		if ($_GET["app_id"]) {
			$_SESSION['mf_payment_record_id'][$form_id] = $record_id;
			$_SESSION['mf_form_payment_access'][$form_id]  = true;
			$_SESSION['mf_form_completed'][$form_id] = true;
		}

		//Check if application is created. If not then create one. This should be a draft since payment is required
		//We will use the application manager to create new applications or drafts from form submissions
		$application_manager = new ApplicationManager();

		//Check if an application already exists for the form submission to prevent double entry
		if ($application_manager->application_exists($form_id, $record_id)) {
			//If save as draft/resume later was clicked then do nothing
			$submission = $application_manager->get_application($form_id, $record_id);
		} else {
			//If save as draft/resume later was clicked then create draft application
			$submission = $application_manager->create_application($form_id, $record_id, $sf_user->getGuardUser()->getId(), true);
			//Register plan
			$api = new ApiCalls();
			$api->registerPlan($form_id, $submission);
		}

		$application_manager->update_invoices($submission->getId());
		$sf_user->setAttribute('application_id', $submission->getId());

		$markup = null;
		error_log('-----SUBMISSION----' . $submission->getId());
		if ($_SESSION['invoice']) {
			$form_params['invoice'] = $_SESSION['invoice'];
			$_SESSION['mf_invoice'] = $_SESSION['invoice'];
			$markup    = mf_display_form_payment($dbh, $form_id, $record_id, $form_params);
		} elseif ($_GET['invoice']) {
			$form_params['invoice'] = $_GET['invoice'];
			$_SESSION['mf_invoice'] = $_GET['invoice'];
			$markup    = mf_display_form_payment($dbh, $form_id, $record_id, $form_params);
		} else {
			//OTB get recent invoice
			$q = Doctrine_Query::create()
				->from('MfInvoice i')
				->where('i.app_id =? and i.paid =?', array($submission->getId(), 1))
				->orderBy('i.id desc');
			$unpaid_invoice = $q->fetchOne();
			if ($unpaid_invoice) {
				$form_params['invoice'] = $unpaid_invoice->getId();
				$_SESSION['mf_invoice'] = $unpaid_invoice->getId();
			}
			$markup    = mf_display_form_payment($dbh, $form_id, $record_id, $form_params);
		}

		header("Content-Type: text/html; charset=UTF-8");
?>
		<div class="col-md-7 col-lg-8 col-xl-9">

			<div class="row">
				<div class="card card-default p-b-0">
					<div style="margin: 15px;">
						<?php echo $markup; ?>
						<div class="form_container mt-5">
							<h2>Checkout</h2>
							<form class="form-horizontal" id="checkout_initial_payment" action="<?php echo '/index.php/forms/initiatePayment/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>">
								<div id="response_area_id"></div>
								<div class="form-group" style="margin: 2px;">
									<label for="phone_number">Phone Number:</label>
									<input type="text" class="form-control" id="phone_number" placeholder="Phone Number" name="phone_number" value="<?php echo $user->getProfile()->getMobile(); ?>">
								</div>
								<div class="form-group p-t-10" style="margin: 2px; margin-top:10px;">
									<button type="submit" class="btn btn-sm btn-dark" id="initiate_payment_loader">
										<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
										Initiate Payment
									</button>
								</div>
							</form>
						</div>
						<div class="form_container" style="display: none;" id="form_checkout_container">
							<div id="checkout_form_options_id"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

<?php
	}
}
?>
<script>
	// global files
	const wallet_url = "<?php echo '/index.php/forms/verifyOtp/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";
	const confirm_payment_url = "<?php echo '/index.php/forms/confirmMpesaPayment/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";
	const redirect_url = "<?php echo '/index.php/invoices/view/id/' . $invoice->getId(); ?>";
	const regenerate_otp_url = "<?php echo '/index.php/forms/regeneratejamboonetimepassword/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";

	function setButtonLoading(buttonId, isLoading, message = 'Initiating') {
		const button = $(`#${buttonId}`);
		if (isLoading) {
			button.prop('disabled', true).html('<span><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' + message + '...</span>');
		} else {
			button.prop('disabled', false).html('Initiate Payment');
		}
	}

	function showAlert(areaId, type, message) {
		let value_info = ''
		switch (type) {
			case 'success':
				value_info = 'Success';
				break;
			case 'info':
				value_info = 'Alert';
				break;
			case 'warning':
				value_info = 'Failed';
				break;
			case 'error':
				value_info = 'Error';
				break;
			case 'danger':
				value_info = 'Error';
				break;
			default:
				value_info = '';
				break;
		}
		const alertHtml = `<div class="alert alert-${type} alert-dismissible">
								<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
								<strong>${value_info}!</strong> ${message}
							</div>`;
		$(`#${areaId}`).html(alertHtml);
	}

	function regenerateOtpFunction() {
		$("#regenerate_otp_function_div").show();
		const regenerateButton = $('#regenerate_otp_button');
		setButtonLoading('initiate_payment_loader', true);
		regenerateButton.prop('disabled', true).text('Regenerating...');
		$.ajax({
			url: regenerate_otp_url,
			type: 'POST',
			data: {
				invoice_id: "<?php echo $invoice->getId(); ?>"
			},
			success: function(response) {
				const data = JSON.parse(response);
				if (data.success) {
					showAlert('response_wallet_area_id', 'success', 'A new OTP has been sent to your phone.');
					setTimeout(() => {
						$('#response_wallet_area_id').html('');
					}, 1000 * 30);
					regenerateButton.prop('disabled', true);
					$("#otp_value").val('');
				} else {
					showAlert('response_wallet_area_id', 'danger', 'Failed to regenerate OTP. Please try again.');
				}
				regenerateButton.prop('disabled', false).text('Regenerate OTP');
				setButtonLoading('initiate_payment_loader', false);
			},
			error: function() {
				showAlert('response_wallet_area_id', 'danger', 'Something went wrong. Please try again later.');
				regenerateButton.prop('disabled', false).text('Regenerate OTP');
				setButtonLoading('initiate_payment_loader', false);
			}
		});
	}
</script>

<script>
	$(document).ready(function() {
		function confirmPayment(currentInterval = 1, maxInterval = 10) {
			console.log("Confirm payment being executed ---->", currentInterval, maxInterval);
			setButtonLoading('initiate_payment_loader', true);
			$.ajax({
				url: confirm_payment_url,
				type: 'GET',
				success: function(response) {
					const data = JSON.parse(response);
					if (data?.success) {
						showAlert('mpesa_confirmation_id', 'success', 'Redirecting...');
						window.location.href = redirect_url;
					} else {
						showAlert('mpesa_confirmation_id', 'info', 'Waiting for Payment to proceed...');
					}
					setButtonLoading('initiate_payment_loader', false);
				},
				error: function() {
					showAlert('mpesa_confirmation_id', 'warning', 'Failed! Something went Wrong. Please try again later.');
					setButtonLoading('initiate_payment_loader', false);
				}
			});
			setTimeout(confirmPayment, Math.min(currentInterval + 1, maxInterval) * 60 * 1000);
		}

		function initialCheckingPayment() {
			setButtonLoading('initiate_payment_loader', true);
			$.ajax({
				url: confirm_payment_url,
				type: 'GET',
				success: function(response) {
					const data = JSON.parse(response);
					if (data?.success) {
						showAlert('mpesa_confirmation_id', 'success', 'Redirecting...');
						window.location.href = redirect_url;
					} else {
						showAlert('mpesa_confirmation_id', 'info', 'Waiting for Payment to proceed...');
						confirmPayment();
					}
					setButtonLoading('initiate_payment_loader', false);
				},
				error: function() {
					setButtonLoading('initiate_payment_loader', false);
				}
			});
		}

		function handleOTPVerification() {
			$('#wallet_checkout_id').submit(function(event) {
				event.preventDefault();
				const verifyButton = $('#verify_otp_button');
				const regenerateOTP = $("#regenerate_otp_function_div");
				verifyButton.prop('disabled', true).text('Verifying...');
				$.ajax({
					url: $(this).attr('action'),
					type: 'POST',
					data: $(this).serialize(),
					success: function(response) {
						const otpResponseData = JSON.parse(response);
						if (otpResponseData.status === 201 || otpResponseData.content.success) {
							showAlert('response_wallet_area_id', 'success', 'Redirecting...');
							confirmPayment();
						} else {
							showAlert('response_wallet_area_id', 'danger', otpResponseData?.content?.msg || 'Invalid OTP. Please try again.');
							verifyButton.prop('disabled', false).text('Verify');
							regenerateOTP.show();

						}
					},
					error: function() {
						showAlert('response_wallet_area_id', 'danger', 'Something Went Wrong! Try again later...');
						verifyButton.prop('disabled', false).text('Verify');
					}
				});
			});
		}

		function initiatePayment() {
			setButtonLoading('initiate_payment_loader', true);
			const formData = $('#checkout_initial_payment').serialize();
			$.ajax({
				url: $('#checkout_initial_payment').attr('action'),
				type: 'POST',
				data: formData,
				success: function(response) {
					const data = JSON.parse(response);
					if (data?.content?.errors) {
						showAlert('response_area_id', 'danger', data.content.errors);
						setButtonLoading('initiate_payment_loader', false);
						return;
					}
					if (data.status === 201) {
						let paymentOption = '';
						if (data?.content?.verify_otp) {
							paymentOption = `<div class="card shadow mt-3 mb-4">
												<div class="card-header bg-default text-white text-center d-flex align-items-center">
													<img src="/asset_mentor/assets/img/jambo_pay_wallet.png" class="img-fluid mr-3" width="100px" height="auto">
													<h4 class="mb-0">Wallet</h4>
												</div>
												<div class="card-body">
													<form id="wallet_checkout_id" class="form-horizontal" action="${wallet_url}">
														<div id="mpesa_confirmation_id"></div>
														<div class="form-group row">
															<label class="control-label col-sm-2" for="otp">OTP:</label>
															<div class="col-sm-10">
																<input type="text" class="form-control" id="otp_value" placeholder="Enter OTP Sent to your phone" name="user_otp" autocomplete="off">
															</div>
														</div>
														<div id="response_wallet_area_id"></div>
														<div class="form-group row">
															<div class="col-12 d-flex justify-content-between">
																<div class="col-sm-offset-2 col-sm-10">
																	<button type="submit" class="btn btn-outline-primary" id="verify_otp_button">Verify</button>
																</div>
																<div id="regenerate_otp_function_div" style="display: none;">
																	<button type="button" onclick="regenerateOtpFunction()" class="btn btn-outline-dark" id="regenerate_otp_function">Re-send OTP</button>
																</div>
															</div>
														</div>
													</form>
												</div>
											</div>`;
						} else {
							paymentOption = `<div class="card shadow mt-3 mb-4">
												<div class="card-header bg-default text-white">
													<img src="/asset_mentor/assets/img/mpesa_2.png" class="img-fluid mr-3" width="100px" height="auto">
												</div>
												<div class="card-body">
													<div id="mpesa_confirmation_id">
														<div class="alert alert-success fade show" role="alert">
															<button type="button" class="close" data-dismiss="alert" aria-label="Close">
																<span aria-hidden="true">&times;</span>
															</button>
															<strong>Success!</strong> Please check your phone for an M-PESA POP UP.
														</div>
													</div>
													<div class="d-flex justify-content-between mt-2 mb-3">
														<div>
															<a class="btn btn-outline-success btn-block mt-3" href="#" id="resend_payment">Click here to resend the M-PESA Popup</a>
														</div>
														<div>
															<a class="btn btn-outline-dark btn-block mt-3" href="#" id="confirm_payment" title="I have paid">Complete Payment</a>
														</div>
													</div>
												</div>
											</div>`;
						}
						$('#form_checkout_container').show();
						$('#checkout_form_options_id').html(paymentOption);
						$('#resend_payment').click(function(event) {
							event.preventDefault();
							initiatePayment();

						});
						$('#confirm_payment').click(function(event) {
							event.preventDefault();
							confirmPayment();
						});
						handleOTPVerification();

						console.log("After one minute check if payment has gone through");

						setTimeout(() => initialCheckingPayment(), 60 * 1000);
					} else {
						showAlert('response_area_id', 'danger', data?.content?.msg || 'Something Went Wrong! Try again later.');
					}
					setButtonLoading('initiate_payment_loader', false);
				},
				error: function() {
					showAlert('response_area_id', 'danger', 'Something Went Wrong! Try again later.');
					setButtonLoading('initiate_payment_loader', false);
				}
			});
		}


		$('#checkout_initial_payment').submit(function(event) {
			event.preventDefault();
			initiatePayment();
		});

		// $(document).on('click', '#regenerate_otp_button', function(event) {
		// 	event.preventDefault();
		// 	regenerate_otp_function();
		// });
	});
</script>


<style>
	.col-md-3 {
		display: block;
		float: left;
		margin: 1% 0 1% 1.6%;
		background-color: #eee;
		padding: 50px 0;
	}

	.col:first-of-type {
		margin-left: 0;
	}


	/* ALL LOADERS */

	.loader {
		width: 100px;
		height: 100px;
		border-radius: 100%;
		position: relative;
		margin: 0 auto;
	}

	/* LOADER 4 */

	#loader-4 span {
		display: inline-block;
		width: 20px;
		height: 20px;
		border-radius: 100%;
		background-color: #3498db;
		margin: 35px 5px;
		opacity: 0;
	}

	#loader-4 span:nth-child(1) {
		animation: opacitychange 1s ease-in-out infinite;
	}

	#loader-4 span:nth-child(2) {
		animation: opacitychange 1s ease-in-out 0.33s infinite;
	}

	#loader-4 span:nth-child(3) {
		animation: opacitychange 1s ease-in-out 0.66s infinite;
	}

	@keyframes opacitychange {

		0%,
		100% {
			opacity: 0;
		}

		60% {
			opacity: 1;
		}
	}

	.btn-outline-success:hover {
		background-color: #28a745;
		color: #fff;
	}
</style>