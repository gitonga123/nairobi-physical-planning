<?php

/**
 *
 * Malipo Payments Gateway
 */

class MalipoGateway
{
	public $invoice_manager;
	public function __construct()
	{
		$this->invoice_manager = new InvoiceManager();
	}

	public function searchBill($billId, $messageId)
	{
		$invoice = $this->invoice_manager->get_invoice_by_invoice_number($billId);
		// you can shift the condition to a switch.
		if ($invoice) {
			if ($invoice->getPaid() == 1) {
				if ($invoice->getTransactionId()) {
					$transactionId = $invoice->getTransactionId();
				} else {
					$transactionId = $this->invoice_manager->getTransactionNumber($invoice->getId());
					$invoice->setMessageId($messageId);
					$invoice->setTransactionId("");
					$invoice->save();
				}

				$application = $invoice->getFormEntry();
				$utilityName = $application->getForm();

				$q = Doctrine_Query::create()
					->from("SfGuardUserProfile a")
					->where("a.user_id = ?", $application->getUserId())
					->limit(1);
				$user = $q->fetchOne();
				return [
					'messageID' => $messageId,
					'transactionId' => strtoupper($transactionId),
					'utilityName' => $utilityName->getFormName(),
					'customerName' => $user->getFullname(),
					'amount' => $invoice->getTotalAmount(),
					'statusCode' => '0',
				];
			} else {
				if ($invoice->getPaid() == 2) {
					return [
						'messageID' => $messageId,
						'transactionId' => '',
						'utilityName' => '',
						'customerName' => '',
						'amount' => $invoice->getTotalAmount(),
						'statusCode' => '1',
						'statusMessage' => 'Invoice Already Paid. Please re-check the invoice no.',
					];
				} else if ($invoice->getPaid() == 3) {
					return [
						'messageID' => $messageId,
						'transactionId' => '',
						'utilityName' => '',
						'customerName' => '',
						'amount' => $invoice->getTotalAmount(),
						'statusCode' => '1',
						'statusMessage' => 'Invoice Cancelled. Please re-check the invoice no.',
					];
				} else {
					return [
						'messageID' => $messageId,
						'transactionId' => '',
						'utilityName' => '',
						'customerName' => '',
						'amount' => $invoice->getTotalAmount(),
						'statusCode' => '1',
						'statusMessage' => 'Invoice Not found!',
					];
				}
			}
		} else {
			return [
				'messageID' => '',
				'transactionId' => '',
				'utilityName' => '',
				'customerName' => '',
				'amount' => '',
				'statusCode' => '1',
				'statusMessage' => 'Invoice Not Found!',
			];
		}
	}
	// generate bill send to malipo and redirect to malipo url for payments
	// the response you get from malipo after bill creation is what you direct user to for payments
	public function checkout_malipo_payment_links($invoice_id)
	{
		error_log("---------- Start Malipo -----------------");
		//error_log($application_id) ;

		$q = Doctrine_Query::create()
			->from('MfInvoice a')
			->where('a.id = ?', $invoice_id)
			->limit(1);
		$invoice = $q->fetchOne();
		// application
		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->where("a.id = ?", $invoice->getAppId());
		$application = $q->fetchOne();
		// payer details
		$user = Doctrine_Core::getTable('SfGuardUser')->find(array($application->getUserId()));

		$profile = $user->getSfGuardUserProfile();

		$fullname = $profile->getFullname();

		$fullname = str_replace("'", "", $fullname);

		$idnumber = $user->getUsername();
		$email = $user->getEmailAddress();

		//One of email or phonenumber is required
		$phonenumber = $profile->getMobile();

		//Get Payment Details
		$payment_description = $application->getForm()->getFormName();
		//
		$form = $application->getForm();

		// Invoice parameters to send to Malipo for payment links
		//$url = "https://revenueapi.amkatek.com/flowengine/malipoapi/v1.0/payment/request/link" ;
		$url = sfConfig::get('app_amkatek_payment_api_url');
		$callback_url = sfConfig::get('app_amkatek_callback_payment');

		// current timestamp
		$timestamp = time();
		$transaction_details = $invoice->getInvoiceNumber() . "-" . $timestamp;
		//$transaction_details = $timestamp ;
		// Request data
		$data = array(
			'phoneNumber' => $this->convertPhoneNumber($phonenumber),
			'description' => $form->getFormName(),
			"callbackUrl" => $callback_url,
			"merchantTransactionRef" => $transaction_details,
			'checkoutItems' => array(
				array(
					'name' => $form->getFormName(),
					'unitPrice' => $invoice->getTotalAmount(),
					'quantity' => 1,
					'description' => $form->getFormName(),
				),
			),
		);
		// transaction
		$invoice->setTransactionId($transaction_details);
		$invoice->save();
		// logins
		//sfConfig::get('app_ifc_server'),
		$access_token = sfConfig::get('app_amkatek_access_token');
		$secret = sfConfig::get('app_amkatek_secret');

		// Convert data to JSON
		$jsonData = json_encode($data);

		// cURL options
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonData),
				'Accesstoken: ' . $access_token,
				'Apisecret: ' . $secret,
			),
		);

		// Initialize cURL session
		$curl = curl_init();
		curl_setopt_array($curl, $options);

		// Execute the request
		$response = curl_exec($curl);

		// Check for errors
		if ($response === false) {
			$error = curl_error($curl);
			// Handle the error accordingly
			echo ("Error:: Payment failed");
			//die("cURL Error: " . $error);
		}

		// Close cURL session
		curl_close($curl);
		/////////////////
		$link = str_replace('"', '', $response);
		echo ("Location:: move to >>>>>>>>>>>>> " . $link);
		header("Location: " . $link);
		//////////////////
		//header("Location: ".$response);
		exit;
	}
	// phone number processing
	public function convertPhoneNumber($phoneNumber)
	{
		// Remove any non-digit characters from the phone number
		$phoneNumber = preg_replace('/\D/', '', $phoneNumber);

		// Check if the phone number starts with '0'
		if (substr($phoneNumber, 0, 1) === '0') {
			// Remove the leading '0' and prepend the country code
			$formattedNumber = '+254' . substr($phoneNumber, 1);
		} elseif (substr($phoneNumber, 0, 1) === '+') {
			// Phone number already starts with '+', remove any leading '+' and return
			$formattedNumber = '+' . substr($phoneNumber, 1);
		} elseif (substr($phoneNumber, 0, 3) === '254') {
			// Phone number starts with '254', prepend the country code
			$formattedNumber = '+' . $phoneNumber;
		} else {
			// The phone number doesn't match any expected format, return as is
			$formattedNumber = $phoneNumber;
		}

		return $formattedNumber;
	}

	// process payment notifications received from external payment server
	// process payments from malipo payments
	public function malipo_ipn($request_details, $invoice)
	{

		$update_details = array();
		$transaction_id = strtoupper(trim($request_details->transactionId));
		$amount_paid = trim($request_details->transactionAmt);
		$transaction_date = $request_details->transactionDate;
		$update_details = [];
		$update_details['transactionId'] = $transaction_id;
		$update_details['messageId'] = $request_details->messageId;
		// mpesa payment status
		$mpesa_status = trim($request_details->paymentStatus);
		if ($mpesa_status == "FAILED") {
			$update_details['statusCode'] = '1';
			$update_details['statusMessage'] = 'Transaction Failed. Please try again later';
			return $update_details;
		}

		if (empty($transaction_id)) {
			$update_details['statusCode'] = '1';
			$update_details['statusMessage'] = 'Invalid Transaction id';
			return $update_details;
		}

		if (empty($amount_paid) || floatval($amount_paid) == 0) {
			$update_details['statusCode'] = '1';
			$update_details['message'] = 'Invalid Transaction amount';
			return $update_details;
		}

		$amount_paid = floatval($amount_paid);
		$paid_by = trim($request_details->firstName) + " " + trim($request_details->middleName) + " " + trim($request_details->lastName);
		try {
			if ($invoice->getPaid() == 1) {
				$status = $this->invoice_manager->check_total_amount_status($invoice->getId(), $amount_paid);
				if ($status === 2) {
					//pending
					$q = Doctrine_Query::create()
						->from("ApFormPayments a")
						->where("a.invoice_id = ? AND a.status = ?", array($invoice->getId(), 15))
						->orderBy('a.afp_id desc');
					$transaction = $q->fetchOne();

					if ($transaction) {
						error_log('----Transaction found----');
						//Update transaction details
						$transaction->setBillingState($transaction_id);
						$transaction->setPaymentDate(date("Y-m-d H:i:s"));
						$transaction->setPaymentFullname($paid_by);
						$transaction->setStatus(2);
						$transaction->setPaymentStatus("paid");
						$transaction->setPaymentMerchantType('MALIPO API');
						$transaction->setPaymentAmount($amount_paid);
						$transaction->setInvoiceId($invoice->getId());
						$transaction->setPaymentCurrency('KES');
						$transaction->setNarration($request_details->narration);
						$transaction->setOthers(json_encode($request_details));
						$transaction->setDebitMSISDN($request_details->debitMSISDN);
						//invoice
						$invoice->setPaid(2);
						$invoice->setTransactionId($transaction_id);
					} else {
						error_log('----Transaction not found----');

						//Add a new transaction if one doesn't exist
						$transaction = new ApFormPayments();
						$transaction->setFormId($invoice->getFormEntry()->getFormId());
						$transaction->setRecordId($invoice->getFormEntry()->getEntryId());
						$transaction->setPaymentId($invoice->getFormEntry()->getFormId() . "/" . $invoice->getFormEntry()->getEntryId() . "/" . $invoice->getId());
						$transaction->setDateCreated(date("Y-m-d H:i:s"));
						$transaction->setPaymentFullname($paid_by);
						$transaction->setPaymentAmount($amount_paid);
						$transaction->setPaymentMerchantType('MALIPO API');
						$transaction->setPaymentTestMode("0");

						$transaction->setBillingState($transaction_id);
						$transaction->setPaymentDate(date("Y-m-d H:i:s"));
						$transaction->setInvoiceId($invoice->getId());
						$transaction->setStatus(2);
						$transaction->setPaymentStatus("paid");
						$transaction->setPaymentCurrency('KES');
						$transaction->setNarration($request_details->narration);
						$transaction->setOthers(json_encode($request_details));
						$transaction->setDebitMSISDN($request_details->debitMSISDN);
						//invoice
						$invoice->setTransactionId($transaction_id);
						$invoice->setPaid(2);
					}
					$transaction->save();
					$invoice->save();
					//response
					$update_details['statusCode'] = '0';
					$update_details['statusMessage'] = 'Transaction updated successfully';
				} else {
					$update_details['statusCode'] = '1';
					$update_details['statusMessage'] = 'Invalid Amount. Partial Payments not allowed.';
				}
			} else {
				$update_details['statusCode'] = '1';
				$update_details['statusMessage'] = 'Invoice Already Paid. Please re-check the invoice no.';
			}
		} catch (Exception $ex) {
			$update_details['statusCode'] = '1';
			$update_details['statusMessage'] = 'Exception ' . $ex->getMessage();
		}

		return $update_details;
	}


	public function jambo_pay_ipn($response)
	{

		error_log("Bill Number details below ---->");

		error_log("Bill Number ----->" . $response['bill_number']);
		error_log("Reference ----->" . $response['reference']);

		$reference = isset($response['reference']) ? $response['reference'] : $response['ref'];

		$q = Doctrine_Query::create()
			->from("ApFormPayments a")
			->where("a.payment_id = ?", $response['bill_number'])
			->orWhere("a.narration = ?", $reference)
			->orderBy('a.afp_id desc');
		$transaction = $q->fetchOne();

		$q_in = Doctrine_Query::create()
			->from('MfInvoice m')
			->where('m.id = ?', $transaction->getInvoiceId());
		$invoice = $q_in->fetchOne();

		$billing_reference_number = $invoice->getFormEntry()->getFormId() . "" . $invoice->getFormEntry()->getEntryId() . "" . $invoice->getId();

		if (!$transaction) {
			$transaction = new ApFormPayments();
			$transaction->setFormId($invoice->getFormEntry()->getFormId());
			$transaction->setRecordId($invoice->getFormEntry()->getEntryId());
			$transaction->setPaymentId($billing_reference_number);
			$transaction->setDateCreated(date("Y-m-d H:i:s"));
			$transaction->setPaymentAmount($invoice->getTotalAmount());
			$transaction->setPaymentMerchantType('SISIBOPAY');
			$transaction->setPaymentTestMode("0");
			$transaction->setPaymentDate(date("Y-m-d H:i:s"));
			$transaction->setInvoiceId($invoice->getId());
			$transaction->setStatus(1);
			$transaction->setPaymentStatus("pending");
			$transaction->setPaymentCurrency('KES');
			$transaction->save();
		}

		
		if (!$invoice) {
			return 'invoice_not_found';
		}

		$transaction->setPaymentMerchantType('SISIBOPay - ' . $response['mode_of_payment']);

		$transaction->setPaymentStatus('paid');
		$transaction->setPaymentDate(date("Y-m-d H:i:s"));
		$transaction->setStatus(2);

		$transaction->save();

		error_log("Transaction updated above ---->");
		error_log($transaction);

		$invoice->setPaid(2);

		// update invoice receipt number
		if (array_key_exists('receipt_number', $response)) {
			$invoice->setReceiptNumber(json_encode($response['receipt_number']));
		}

		$invoice->save();

		error_log("Invoice updated above ---->");
		error_log($invoice);

		return 'paid';
	}
}
