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
						<div class="form_container">
							<h2>Checkout</h2>
							<form class="form-horizontal" id="checkout_initial_payment" action="<?php echo '/index.php/forms/initiatePayment/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>">
								<div id="response_area_id"></div>
								<div class="form-group" style="margin: 2px;">
									<label for="phone_number">Phone Number:</label>
									<input type="text" class="form-control" id="phone_number" placeholder="Phone Number" name="phone_number" value="254710594298">
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
	const wallet_url = "<?php echo '/index.php/forms/verifyOtp/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";
</script>

<script>
	function initializePaymentButtonLoading() {
		const text = `<span>
					<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
						Initiating...
				</span>
				`;

		$("#initiate_payment_loader").prop('disabled', true).html(
			text
		);
	}

	function initializePaymentButtonDone() {
		const text = `Initiate Payment`;
		$("#initiate_payment_loader").show();
		$("#initiate_payment_loader").prop('disabled', false).html(
			text
		);
	}
	$(document).ready(function() {
		function initiatePayment() {
			initializePaymentButtonLoading();
			var formData = $("#checkout_initial_payment").serialize(); // Serialize form data

			$.ajax({
				url: $("#checkout_initial_payment").attr('action'), // Get form action URL
				type: "POST", // Use POST method for form data
				data: formData,
				success: function(response) {
					// Handle successful form submission
					const data = JSON.parse(response);

					console.log(JSON.stringify(data, null, 3));

					if (data?.content?.errors) {
						$("#response_area_id").html(
							`<div class="alert alert-danger alert-dismissible">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong>Error!</strong> ${data?.content?.errors}.
                                <strong>Please try again later.</strong>
                            </div>`
						);
						$("#initiate_payment_loader").hide();
						return;
					}

					if (data.status === 201) {
						let payment_option = ``;

						if (data?.content?.content?.verify_otp) {
							payment_option += `
                                <div class="card shadow mt-3 mb-4">
                                    <div class="card-header bg-default text-white text-center d-flex align-items-center">
                                        <img src="/asset_mentor/assets/img/jambo_pay_wallet.png" class="img-fluid mr-3" width="100px" height="auto">
                                        <h4 class="mb-0">
                                            Wallet
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <form id="wallet_checkout_id" class="form-horizontal" action="${wallet_url}">
                                            <div class="form-group row">
                                                <label class="control-label col-sm-2" for="otp">OTP:</label>
                                                <div class="col-sm-10">
                                                    <input type="text" class="form-control" id="otp" placeholder="Enter OTP Sent to your phone" name="user_otp" autocomplete="off">
                                                </div>
                                            </div>
											<div id="response_wallet_area_id"></div>
                                            <div class="form-group row">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" class="btn btn-outline-primary" id="verify_otp_button">Verify</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>`;
						} else {
							payment_option += `
                                <div class="card shadow mt-3 mb-4">
                                    <div class="card-header bg-default text-white text-center d-flex align-items-center">
                                        <img src="/asset_mentor/assets/img/icons8-mpesa-480.png" class="img-fluid mr-3" width="50px" height="auto">
                                        <h4 class="mb-0">
                                            M-PESA
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <strong>Success!</strong> Please check your phone for an M-PESA POP UP.
                                        </div>
                                        <a class="btn btn-outline-success btn-block mt-3" href="#" id="resend_payment">Click here to resend the M-PESA Popup</a>
                                    </div>
                                </div>
                                `;
						}
						initializePaymentButtonDone();
						$('#form_checkout_container').show();
						$("#checkout_form_options_id").html(payment_option);

						// Attach click event to the resend button
						$("#resend_payment").click(function(event) {
							event.preventDefault();
							initiatePayment(); // Re-initiate the payment process
						});

						$("#wallet_checkout_id").submit(function(event) {
							event.preventDefault(); // Prevent default form submission
							$("#verify_otp_button").prop('disabled', true).text('Verifying...');
							// Submit the OTP form using AJAX
							$.ajax({
								url: $("#wallet_checkout_id").attr('action'), // Get form action URL
								type: "POST", // Use POST method for form data
								data: $("#wallet_checkout_id").serialize(),
								success: function(response) {
									const otpResponseData = JSON.parse(response);

									if (otpResponseData.status === 200) {
										$("#verify_otp_button").prop('disabled', true).text('Processing...');
										// OTP valid, handle successful verification (e.g., redirect to success page)
										$("#response_wallet_area_id").html(
											`<div class="alert alert-success alert-dismissible">
                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                            <strong>Success!</strong> Redirecting...
                                        </div>`
										);

										// ... your success logic here
									} else {
										// Handle invalid OTP error
										$("#verify_otp_button").prop('disabled', false).text('Verify');
										$("#response_wallet_area_id").html(
											`<div class="alert alert-danger alert-dismissible">
                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                            <strong>Error!</strong> ${otpResponseData?.content?.msg || "Invalid OTP. Please try again."}
                                        </div>`
										);
									}
								},
								error: function(jqXHR, textStatus, errorThrown) {
									$("#response_wallet_area_id").html(
										`<div class="alert alert-danger alert-dismissible">
                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                            <strong>Error!</strong> ${textStatus || "Something Went Wrong! Try Again later..."}
                                        </div>`
									);
									$("#verify_otp_button").prop('disabled', false).text('Verify');
								}
							});
						});
					} else {
						$("#response_area_id").html(
							`<div class="alert alert-danger alert-dismissible">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong>Error!</strong> ${data?.content?.msg || "Something Went Wrong! Try again later."}.
                                <strong>Please try again later.</strong>
                            </div>`
						);
						$("#initiate_payment_loader").hide();
						initializePaymentButtonDone();
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					initializePaymentButtonDone();
					$("#response_area_id").html(
						`<div class="alert alert-danger alert-dismissible">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong>Error!</strong> ${textStatus || "Something Went Wrong! Try again later."}.
                                <strong>Please try again later.</strong>
                            </div>`
					);
					// Handle errors during submission
					console.error("Error submitting form:", textStatus, errorThrown);

				}
			});
		}

		// Attach the initial form submission to initiatePayment function
		$("#checkout_initial_payment").submit(function(event) {
			event.preventDefault(); // Prevent default form submission
			initiatePayment();
		});

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