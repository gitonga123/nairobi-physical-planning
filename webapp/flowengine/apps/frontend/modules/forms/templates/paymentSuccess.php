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