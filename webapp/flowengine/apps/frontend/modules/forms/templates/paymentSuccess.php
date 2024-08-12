<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
		<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
	</symbol>
	<symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
		<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
	</symbol>
	<symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
		<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
	</symbol>
</svg>
<div class="col-md-7 col-lg-8 col-xl-9">
	<div class="col-12">
		<nav class="user-tabs">
			<ul class="nav nav-tabs nav-tabs-bottom nav-justified">
				<li>
					<a class="nav-link active" href="# <?php $application->getApplicationId(); ?>" data-bs-toggle="tab"> Payment for Application No. - <?php echo $application->getApplicationId(); ?></a>
				</li>
			</ul>
		</nav>
		<?php if ($invoice->getPaid() == 1) : ?>
			<div class="accordion mb-3" id="accordionCheckoutForm">
				<div class="accordion-item">
					<h2 class="accordion-header" id="headingTwo">
						<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
							Checkout
						</button>
					</h2>
					<div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<form class="form-horizontal" id="checkout_initial_payment" action="<?php echo 'plan/forms/initiatePayment/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>">
								<div id="response_area_id"></div>
								<div class="form-group" style="margin: 2px;">
									<label for="phone_number">Phone Number:</label>
									<input type="text" class="form-control input-lg" id="phone_number" placeholder="Phone Number" name="phone_number" value="<?php echo $user->getProfile()->getMobile(); ?>">
								</div>
								<div class="form-group p-t-10" style="margin: 2px; margin-top:10px;">
									<button type="submit" class="btn btn-md btn-dark" id="initiate_payment_loader">
										<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
										Initiate Payment
									</button>
								</div>

							</form>
							<div class="form_container" style="display: none;" id="form_checkout_container">
								<div id="checkout_form_options_id"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php elseif ($invoice->getPaid() == 2) : ?>

			<div class="alert alert-success mt-2" role="alert">
				<div class="d-flex align-items-center">
					<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:">
						<use xlink:href="#check-circle-fill" />
					</svg>
					Invoice Already Paid.
				</div>
			</div>
			<div class="mb-3">

				<div class="d-flex justify-content-between">
					<a class="btn btn-outline-info btn-block me-5" href="plan/application/view/id/<?php echo $application->getId(); ?>">Application Details</a>

					<a class="btn btn-outline-success btn-block" href="plan/invoices/view/id/<?php echo $invoice->getId(); ?>">Invoice Details</a>

				</div>

			</div>
		<?php else : ?>
			<div class="alert alert-warning mt-2" role="alert">
				<div class="d-flex align-items-center">
					<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:">
						<use xlink:href="#check-circle-fill" />
					</svg>
					Invoice Invalid (Expired/Cancelled)
				</div>
			</div>
			<div class="mb-3">

				<div class="d-flex justify-content-between">
					<a class="btn btn-outline-info btn-block me-5" href="plan/application/view/id/<?php echo $application->getId(); ?>">Application Details</a>

					<a class="btn btn-outline-success btn-block" href="plan/invoices/view/id/<?php echo $invoice->getId(); ?>">Invoice Details</a>

				</div>

			</div>
		<?php endif; ?>
		<div class="accordion mb-3" id="accordionInvoice Info">
			<div class="accordion-item">
				<h2 class="accordion-header" id="headingOne">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
						Invoice # <?php echo $invoice->getInvoiceNumber(); ?> - Fee(s)
					</button>
				</h2>

				<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
					<div class="accordion-body">
						<ul class="list-group">
							<li class="list-group-item active d-flex justify-content-between align-items-center">
								<strong>Description</strong>
								<strong>Amount(<?php echo $invoice->getCurrency(); ?>)</strong>
							</li>
							<?php foreach ($invoice->MfInvoiceDetail as $fee) :
								$code = '';
								$description = $fee->getDescription();
								$pieces = explode(":", $description);
								if (sizeof($pieces) > 1) {
									$description = $pieces[1];
									$code = $pieces[0];

									$description = "<span>{$code}</span><span>{$description}</span>";
								}

							?>
								<li class="list-group-item d-flex justify-content-between align-items-center">
									<?php echo $description; ?>
									<span><?php echo $fee->getAmount(); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
						<p class="mt-3"><strong>Total Amount: <?php echo  $invoice->getCurrency() . '&nbsp;&nbsp;' . $invoice->getTotalAmount(); ?></strong></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	// global files
	const wallet_url = "<?php echo 'plan/forms/verifyOtp/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";
	const confirm_payment_url = "<?php echo 'plan/forms/confirmMpesaPayment/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";
	const redirect_url = "<?php echo 'plan/invoices/view/id/' . $invoice->getId(); ?>";
	const regenerate_otp_url = "<?php echo 'plan/forms/regeneratejamboonetimepassword/application/' . $application->getId() . '/invoice/' . $invoice->getId(); ?>";

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
		const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
								<strong>${value_info}!</strong> ${message}
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
			setButtonLoading('confirm_payment', true);
			$.ajax({
				url: confirm_payment_url,
				type: 'GET',
				success: function(response) {
					console.log(response);
					const data = JSON.parse(response);
					if (data?.success) {
						showAlert('mpesa_confirmation_id', 'success', 'Redirecting...');
						window.location.href = redirect_url;
					} else {
						showAlert('mpesa_confirmation_id', 'info', data?.data?.msg);
						setTimeout(function() {
							showAlert('mpesa_confirmation_id', 'info', 'Waiting for Payment to proceed...');
						}, 5000); // 5000 milliseconds = 5 seconds

					}
					setButtonLoading('initiate_payment_loader', false);
					setButtonLoading('confirm_payment', false);
				},
				error: function() {
					showAlert('mpesa_confirmation_id', 'warning', 'Failed! Something went Wrong. Please try again later.');
					setButtonLoading('initiate_payment_loader', false);
					setButtonLoading('confirm_payment', false);
				}
			});
			currentInterval = currentInterval + 1;
			setTimeout(confirmPayment, Math.min(currentInterval, maxInterval) * 60 * 1000);
		}

		function initialCheckingPayment() {
			setButtonLoading('initiate_payment_loader', true);
			$.ajax({
				url: confirm_payment_url,
				type: 'GET',
				success: function(response) {
					console.log(response);
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
						if (otpResponseData.status === 201 || otpResponseData.success) {
							$("#regenerate_otp_function_div").hide();
							showAlert('response_wallet_area_id', 'success', 'Redirecting...');
							setTimeout(() => {
								window.location.href = redirect_url;
							}, 60 * 1000);

						} else if (otpResponseData.status == 400) {
							$("#regenerate_otp_function_div").hide();
							showAlert('response_wallet_area_id', 'info', otpResponseData.content?.message[0] || "Transaction already completed. Redirecting...");
							setTimeout(() => {
								window.location.href = redirect_url;
							}, 60 * 1000);

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
			$("#mpesa_confirmation_id").html();
			$("#response_wallet_area_id").html();
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