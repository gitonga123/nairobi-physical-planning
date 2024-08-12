<?php
/**
 *
 * Zizi Payments Gateway
 *
 */

class ZiziGateway {

    private $suffix = "s";
    public $invoice_manager = null;

    public function __construct()
    {
        $this->invoice_manager = new InvoiceManager();

        if (empty($_SERVER['HTTPS'])) {
            $this->suffix = "";
        }
    }

    //Display a checkout for the current gateway
    public function checkout($invoice_id)
    {
		//OTB Add
		//SHOW INVOICE
		error_log('--------invoice_id-------'.$invoice_id);
		$checkout=$this->invoice_manager->generate_invoice_template($invoice_id);

        return $checkout;
    }

    //Display a checkout for the current gateway
    public function checkout_cart($application_id, $backend = false)
    {
        $invoice = null;

        $application = Doctrine_Core::getTable('FormEntry')->find(array($application_id));

        if($this->invoice_manager->has_unpaid_invoice($application_id))
        {
             $invoice = $this->invoice_manager->get_unpaid_invoice($application_id);
        }
        else 
        {
            if($application->getApproved() == 0)
            {
                $invoice = $this->invoice_manager->create_invoice_from_submission($application_id);
            }
            else 
            {
                if($backend == false)
                {
                    header("Location: /index.php//application/view/id/".$application_id);
                    exit;
                }
            }
        }

        //Before display the checkout, perform a remote check to confirm if the invoice has
        // been paid for
        /*$result = $this->invoice_manager->remote_reconcile($application->getFormId()."/".$application->getEntryId()."/".$invoice->getId());
        if($result == "paid")
        {
          $invoice->setPaid(2);
          $invoice->save();

          error_log("Pesaflow Remote Validated: ".$application->getApplicationId());

          if($backend == false)
          {
            header("Location: /index.php//application/view/id/".$application->getId());
          }

          exit;
        }*/

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
        $payment_amount = $invoice->getTotalAmount();
        $merchant_reference = $this->invoice_manager->get_merchant_reference($invoice->getId());

        $form = $application->getForm();

        //Params
        $apiClientID = $form->getPaymentCellulantCheckoutUrl();
        $key = $form->getPaymentCellulantMerchantUsername();
        $secret = $form->getPaymentCellulantMerchantPassword();
        $serviceID = $form->getPaymentCellulantServiceId();

        $payment_currency = $form->getPaymentCurrency();

        $amountExpected = $payment_amount;

        $q = Doctrine_Query::create()
           ->from("ApFormPayments a")
           ->where("a.form_id = ? AND a.record_id = ?", array($application->getFormId(), $application->getEntryId()))
           ->andWhere("a.status <> ? or a.payment_status <> ?", array(2, 'paid'));
        $transaction = $q->fetchOne();

        if($transaction)
        {
          //Should we update any existing transactions at this point?
        }
        else
        {
          //Add transaction details
          $transaction = new ApFormPayments();
          $transaction->setFormId($application->getFormId());
          $transaction->setRecordId($application->getEntryId());
          $transaction->setPaymentId($merchant_reference);
          $transaction->setDateCreated(date("Y-m-d H:i:s"));
          $transaction->setPaymentFullname($fullname);
          $transaction->setPaymentAmount($amountExpected);
          $transaction->setPaymentCurrency($payment_currency);
          $transaction->setPaymentMerchantType('Zizi');
          $transaction->setPaymentTestMode("0");

          $transaction->setPaymentStatus("pending");
          $transaction->setStatus(15);

          $transaction->save();
        }

        $callBackURLOnSuccess = null;
        $callBackURLOnFail = null;

        if($backend)
        {
            $callBackURLOnSuccess = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/backend.php/forms/view?id='.$application->getFormId().'&entryid='.$application->getEntryId().'&done=1&invoiceid='.$invoice->getId().'&status=201'; //redirect url, the page that will handle the response from pesaflow.
            $callBackURLOnFail = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/backend.php/forms/invalidpayment';
        }
        else
        {
            $callBackURLOnSuccess = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/index.php//forms/view?id='.$application->getFormId().'&entryid='.$application->getEntryId().'&done=1&invoiceid='.$invoice->getId().'&status=201'; //redirect url, the page that will handle the response from pesaflow.
            $callBackURLOnFail = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/index.php//forms/invalidpayment';
        }

        $iframe_url = "https://pesaflow.ecitizen.go.ke/PaymentAPI/iframev2.1.php?";

  	    $updateURL = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/index.php//payment/updateinvoice';

        $account_type = "";

        if($profile->getRegisteras() == 1)
        {
          $account_type = "citizen";
        }
        elseif($profile->getRegisteras() == 3)
        {
          $account_type = "alien";
        }
        elseif($profile->getRegisteras() == 4)
        {
          $account_type = "visitor";
        }

        $data_string = $apiClientID.$payment_amount.$serviceID.$idnumber.$payment_currency.$merchant_reference.$payment_description. $fullname.$secret;

        $hash = hash_hmac('sha256', $data_string, $key);

        $secureHash = base64_encode($hash);

        $checkout = <<<EOT
<iframe name="iframe" width="100%" height="900px" scrolling="no" frameBorder="0">
<p>Browser unable to load iFrame</p> </iframe>
<form id="details" method="post" action="{$iframe_url}" target="iframe">
<input type="hidden" name="apiClientID" value="{$apiClientID}"/>
<input type="hidden" name="secureHash" value="{$secureHash}"/>
<input type="hidden" name="currency" value="{$payment_currency}"/>
<input type="hidden" name="billDesc" value="{$payment_description}"/>
<input type="hidden" name="billRefNumber" value="{$merchant_reference}"/>
<input type="hidden" name="serviceID" value="{$serviceID}"/>
<input type="hidden" name="clientMSISDN" value="{$phonenumber}"/>
<input type="hidden" name="clientName" value="{$fullname}"/>
<input type="hidden" name="clientEmail" value="{$email}"/>
<input type="hidden" name="clientIDNumber" value="{$idnumber}"/>
<input type="hidden" name="clientType" value="{$account_type}"/>
<input type="hidden" name="amountExpected" value="{$payment_amount}"/>
<input type="hidden" name="callBackURLOnSuccess" value="{$callBackURLOnSuccess}"/>
<input type="hidden" name="callBackURLOnFail" value="{$callBackURLOnFail}"/>
<input type="hidden" name="notificationURL" value="{$updateURL}"/>
</form>
<script> document.getElementById("details").submit(); </script>
EOT;
	error_log('----Checkout-----'.$checkout);

        return $checkout;
    }

    //Display a checkout for the current gateway
    public function checkout_profile($profile_id, $amount, $backend = false)
    {
        $invoice = null;

        $user_profile = Doctrine_Core::getTable('MfUserProfile')->find(array($profile_id));

        $user = Doctrine_Core::getTable('SfGuardUser')->find(array($user_profile->getUserId()));

        $profile = $user->getSfGuardUserProfile();

        $fullname = $profile->getFullname();

        $fullname = str_replace("'", "", $fullname);

        $idnumber = $user->getUsername();
        $email = $user->getEmailAddress();

        //One of email or phonenumber is required
        $phonenumber = $profile->getMobile();

        //Get Payment Details
        $payment_description = $user_profile->getTitle();
        $payment_amount = $amount;
        $merchant_reference = $user_profile->getFormId()."/".$user_profile->getEntryId()."/".$user_profile->getId();

        $q = Doctrine_Query::create()
           ->from("ApForms a")
           ->where("a.form_id = ?", $user_profile->getFormId());
        $form = $q->fetchOne();

        //Params
        $apiClientID = $form->getPaymentCellulantCheckoutUrl();
        $key = $form->getPaymentCellulantMerchantUsername();
        $secret = $form->getPaymentCellulantMerchantPassword();
        $serviceID = $form->getPaymentCellulantServiceId();

        $payment_currency = $form->getPaymentCurrency();

        $amountExpected = $payment_amount;

        $q = Doctrine_Query::create()
           ->from("ApFormPayments a")
           ->where("a.form_id = ? AND a.record_id = ?", array($user_profile->getFormId(), $user_profile->getEntryId()))
           ->andWhere("a.status <> ? or a.payment_status <> ?", array(2, 'paid'));
        $transaction = $q->fetchOne();

        if($transaction)
        {
          //Should we update any existing transactions at this point?
        }
        else
        {
          //Add transaction details
          $transaction = new ApFormPayments();
          $transaction->setFormId($user_profile->getFormId());
          $transaction->setRecordId($user_profile->getEntryId());
          $transaction->setPaymentId($merchant_reference);
          $transaction->setDateCreated(date("Y-m-d H:i:s"));
          $transaction->setPaymentFullname($fullname);
          $transaction->setPaymentAmount($amountExpected);
          $transaction->setPaymentCurrency($payment_currency);
          $transaction->setPaymentMerchantType('Pesaflow');
          $transaction->setPaymentTestMode("0");

          $transaction->setPaymentStatus("pending");
          $transaction->setStatus(15);

          $transaction->save();
        }

        $callBackURLOnSuccess = null;
        $callBackURLOnFail = null;

        if($backend)
        {
            $callBackURLOnSuccess = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/backend.php/profile/create?id='.$user_profile->getFormId().'&entryid='.$user_profile->getEntryId().'&done=1&profile='.$user_profile->getId().'&status=201'; //redirect url, the page that will handle the response from pesaflow.
            $callBackURLOnFail = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/backend.php/forms/invalidpayment';
        }
        else
        {
            $callBackURLOnSuccess = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/index.php//profile/create?id='.$user_profile->getFormId().'&entryid='.$user_profile->getEntryId().'&done=1&profile='.$user_profile->getId().'&status=201'; //redirect url, the page that will handle the response from pesaflow.
            $callBackURLOnFail = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/index.php//forms/invalidpayment';
        }

        $iframe_url = "https://pesaflow.ecitizen.go.ke/PaymentAPI/iframev2.1.php?";

  	    $updateURL = 'http'.$this->suffix.'://'.$_SERVER['HTTP_HOST'].'/index.php//payment/updateprofile';

        $account_type = "";

        if($profile->getRegisteras() == 1)
        {
          $account_type = "citizen";
        }
        elseif($profile->getRegisteras() == 3)
        {
          $account_type = "alien";
        }
        elseif($profile->getRegisteras() == 4)
        {
          $account_type = "visitor";
        }

        $data_string = $apiClientID.$payment_amount.$serviceID.$idnumber.$payment_currency.$merchant_reference.$payment_description. $fullname.$secret;

        $hash = hash_hmac('sha256', $data_string, $key);

        $secureHash = base64_encode($hash);

        $checkout = <<<EOT
<iframe name="iframe" width="100%" height="900px" scrolling="no" frameBorder="0">
<p>Browser unable to load iFrame</p> </iframe>
<form id="details" method="post" action="{$iframe_url}" target="iframe">
<input type="hidden" name="apiClientID" value="{$apiClientID}"/>
<input type="hidden" name="secureHash" value="{$secureHash}"/>
<input type="hidden" name="currency" value="{$payment_currency}"/>
<input type="hidden" name="billDesc" value="{$payment_description}"/>
<input type="hidden" name="billRefNumber" value="{$merchant_reference}"/>
<input type="hidden" name="serviceID" value="{$serviceID}"/>
<input type="hidden" name="clientMSISDN" value="{$phonenumber}"/>
<input type="hidden" name="clientName" value="{$fullname}"/>
<input type="hidden" name="clientEmail" value="{$email}"/>
<input type="hidden" name="clientIDNumber" value="{$idnumber}"/>
<input type="hidden" name="clientType" value="{$account_type}"/>
<input type="hidden" name="amountExpected" value="{$payment_amount}"/>
<input type="hidden" name="callBackURLOnSuccess" value="{$callBackURLOnSuccess}"/>
<input type="hidden" name="callBackURLOnFail" value="{$callBackURLOnFail}"/>
<input type="hidden" name="notificationURL" value="{$updateURL}"/>
</form>
<script> document.getElementById("details").submit(); </script>
EOT;

        return $checkout;
    }

    //Validate payment details received after redirect from checkout
    public function validate($invoice_id, $request_details, $payment_settings)
    {
        $invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

        //Check the status using the API and not the status from the URL
        $payment_status = "pending";

        $payment_status = $this->invoice_manager->remote_reconcile($application->getFormId()."/".$application->getEntryId()."/".$invoice->getId());

        if (isset($payment_status)) {

            $q = Doctrine_Query::create()
               ->from("ApFormPayments a")
               ->where("a.form_id = ? AND a.record_id = ?", array($application->getFormId(), $application->getEntryId()))
               ->andWhere("a.status <> ?", 2)
               ->andWhere("a.payment_amount = ?", $invoice->getTotalAmount());
            $transaction = $q->fetchOne();

            if($transaction)
            {
                //Update transaction details
                $transaction->setBillingState($request_details['transaction_id']);
                $transaction->setPaymentDate(date("Y-m-d H:i:s"));

                if($payment_status == "paid")
                {
                    $transaction->setStatus(2);
                    $transaction->setPaymentStatus("paid");
                }
                else
                {
                    $transaction->setStatus(15);
                    $transaction->setPaymentStatus("pending");
                }

                $transaction->save();
            }

            error_log("Pesaflow: ".$request_details['status']."/".$this->invoice_manager->get_invoice_total_owed($invoice->getId()));

            if($payment_status == "paid")
            {
                //Payment is successful
                $invoice->setPaid(2);
                $invoice->setUpdatedAt(date("Y-m-d H:i:s"));
            }
            else
            {
                //Payment is incomplete
                $invoice->setPaid(15);
                $invoice->setUpdatedAt(date("Y-m-d H:i:s"));
            }

            //Update invoice and allow any triggers to take place
            $invoice->save();

            error_log("Pesaflow - Debug-x: Successful Validation - ".$application->getApplicationId());

            return true;
        }
        else
        {
            error_log("Pesaflow - Debug-x: Missing Status - ".$application->getApplicationId());
            return false;
        }
    }

    //Process payment notifications received from external payment server
    public function ipn($request_details)
    {
        $update_details = array();
        $invoice = trim($request_details->invoice);
        $transaction_id = trim($request_details->transaction_id);
        $amount_paid = trim($request_details->amount);

        if($request_details->transactiondate)
        {
          $transaction_date = $request_details->transactiondate;
        }
        elseif($request_details->transaction_date)
        {
          $transaction_date = $request_details->transaction_date;
        }

        if($request_details->transactionstatus)
        {
          $transaction_status = $request_details->transactionstatus;
        }
        elseif($request_details->transaction_status)
        {
          $transaction_status = $request_details->transaction_status;
        }

        if(empty($transaction_id))
        {
			$update_details['status']='01';
			$update_details['message']='Invalid Transaction id';
			$update_details['data']=[];
        }

        if(empty($amount_paid) || floatval($amount_paid))
        {
			$update_details['status']='01';
			$update_details['message']='Invalid Transaction amount';
			$update_details['data']=[];
        }
		//Check transaction status for accepted
		$respose_statues=['00','01','02']; 
		if(!in_array($transaction_status,$respose_statues,true)){
			$update_details['status']='01';
			$update_details['message']='Invalid Transaction status';
			$update_details['data']=[];
		}

        $amount_paid = floatval($request_details->amount);
        $paid_by = trim($request_details->paid_by);
	  try
	  {
      error_log('-------Invoice-------'.$invoice);
		$invoice_found=$this->invoice_manager->get_invoice_by_invoice_number($invoice);
		if($invoice_found){
			$application = $invoice_found->getFormEntry();
			$user = Doctrine_Core::getTable('SfGuardUser')->find(array($application->getUserId()));
			$fullname = $user->getProfile()->getFullname();
		}else{
      error_log('------Invoice not found------');
			$update_details['status']='01';
			$update_details['message']='Invalid Invoice';
			$update_details['data']=[];
		}
        if($invoice_found->getPaid() == 1)
        {
			$status=$this->invoice_manager->check_total_amount_status($invoice_found->getId(),$amount_paid);
			error_log('-------status----'.$status);
			//pending
          $q = Doctrine_Query::create()
             ->from("ApFormPayments a")
             ->where("a.invoice_id = ? AND a.status = ?", array($invoice_found->getId(),15))
			 ->orderBy('a.afp_id desc');
          $transaction = $q->fetchOne();

          if($transaction)
          {
			  error_log('----Transaction found----');
              //Update transaction details
              $transaction->setBillingState($transaction_id);
              $transaction->setPaymentDate(date("Y-m-d H:i:s",strtotime($transaction_date)));
              $transaction->setPaymentFullname($paid_by);
              if($transaction_status == '00')
              {
				if($status === 2){
				  $transaction->setStatus(2);
				  $transaction->setPaymentStatus("paid");
				  //invoice
				  $invoice_found->setPaid(2);
				}elseif($status === 1){
				  $transaction->setStatus(3);
				  $transaction->setPaymentStatus("part");
				  //invoice
				  $invoice_found->setPaid(1);
				}else{
				  $transaction->setStatus(1);
				  $transaction->setPaymentStatus("failed");
				  //invoice
				  $invoice_found->setPaid($status);
				}
              }
              elseif($transaction_status == '01')
              {
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("failed");
				  //invoice
				  $invoice_found->setPaid(3);
              }
              elseif($transaction_status == '02')
              {
				  if($status === 2){
					  $transaction->setStatus(2);
					  $transaction->setPaymentStatus("paid");
					  //invoice
					  $invoice_found->setPaid(2);
				  }else{
					  $transaction->setStatus(3);
					  $transaction->setPaymentStatus("part");
					  //invoice
					  $invoice_found->setPaid($status);
				  }
              }else{
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("failed");
				  //invoice
				  $invoice_found->setPaid(3);
			  }
			  $transaction->setPaymentAmount($amount_paid);
          }
          else
          {
			  error_log('----Transaction not found----');

              //Add a new transaction if one doesn't exist
              $transaction = new ApFormPayments();
              $transaction->setFormId($invoice_found->getFormEntry()->getFormId());
              $transaction->setRecordId($invoice_found->getFormEntry()->getEntryId());
              $transaction->setPaymentId($invoice_found->getFormEntry()->getFormId()."/".$invoice_found->getFormEntry()->getEntryId()."/".$invoice_found->getId());
              $transaction->setDateCreated(date("Y-m-d H:i:s"));
              $transaction->setPaymentFullname($paid_by);
              $transaction->setPaymentAmount($amount_paid);
              $transaction->setPaymentCurrency($invoice_found->getCurrency());
              $transaction->setPaymentMerchantType('zizi');
              $transaction->setPaymentTestMode("0");

              $transaction->setBillingState($transaction_id);
              $transaction->setPaymentDate(date("Y-m-d H:i:s"));
              $transaction->setInvoiceId($invoice_found->getId());

              if($transaction_status == '00')
              {
				if($status === 2){
				  $transaction->setStatus(2);
				  $transaction->setPaymentStatus("paid");
				  //invoice
				  $invoice_found->setPaid(2);
				}elseif($status === 1){
				  $transaction->setStatus(3);
				  $transaction->setPaymentStatus("part");
				  //invoice
				  $invoice_found->setPaid(1);
				}else{
				  $transaction->setStatus(1);
				  $transaction->setPaymentStatus("failed");
				  //invoice
				  $invoice_found->setPaid($status);
				}
              }
              elseif($transaction_status == '01')
              {
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("failed");
				  //invoice
				  $invoice_found->setPaid(3);
              }
              elseif($transaction_status == '02')
              {
				  if($status === 2){
					  $transaction->setStatus(2);
					  $transaction->setPaymentStatus("paid");
					  //invoice
					  $invoice_found->setPaid(2);
				  }else{
					  $transaction->setStatus(3);
					  $transaction->setPaymentStatus("part");
					  //invoice
					  $invoice_found->setPaid($status);
				  }
              }else{
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("failed");
				  //invoice
				  $invoice_found->setPaid(3);
			  }
          }
		  $transaction->save();
		  $invoice_found->save();
			$data=[];
			$data['invoice_status'] = $invoice_found->getStatus();
			$data['total_amount'] = floatval($invoice_found->getTotalAmount());
			$data['currency'] = $invoice_found->getCurrency();
			$data['date_of_invoice'] = $invoice_found->getCreatedAt();
			$data['invoice_due_date'] = $invoice_found->getExpiresAt();
			$data['invoice_number'] = $invoice_found->getInvoiceNumber();
			$data['application_id'] = $application->getApplicationId();
			$data['plan_id'] = $application->getMerchantIdentifier();
			$data['user_email'] = $user->getProfile()->getEmail();
			$data['user_mobile'] = $user->getProfile()->getMobile();
			$data['user_fullname'] = $user->getProfile()->getFullname();
			//response
			$update_details['status']='00';
			$update_details['message']='Transaction updated';
			$update_details['data']=$data;
        }
        else
        {
			$update_details['status']='01';
			$update_details['message']='Invalid Invoice';
			$update_details['data']=[];
        }

        error_log("Zizi IPN: ".$transaction_status."/".$amount_paid);


      }
      catch(Exception $ex)
      {
		$update_details['status']='01';
		$update_details['message']='Exception '.$ex->getMessage();
		$update_details['data']=[];
      }

        return $update_details;
    }

    //Process payment notifications received from external payment server
    public function ipn_profile($request_details)
    {
      try
      {
        $update_details = array();

        $transaction_id = $request_details["invoiceNumber"];
        $amount_paid = $request_details["amount"];

        if($request_details["transactiondate"])
        {
          $transaction_date = $request_details["transactiondate"];
        }
        elseif($request_details["transaction_date"])
        {
          $transaction_date = $request_details["transaction_date"];
        }

        if($request_details["transactionstatus"])
        {
          $transaction_status = $request_details["transactionstatus"];
        }
        elseif($request_details["transaction_status"])
        {
          $transaction_status = $request_details["transaction_status"];
        }

        if(empty($transaction_id))
        {
            $update_details['update_status'] = "invalid transaction id";
            return $update_details;
        }

        if(empty($amount_paid))
        {
            $update_details['update_status'] = "invalid transaction amount";
            return $update_details;
        }
        $paid_by = $request_details["paidby"];

        $profile = $this->invoice_manager->get_profile_by_reference($transaction_id);

        $user = Doctrine_Core::getTable('SfGuardUser')->find(array($profile->getUserId()));
        $fullname = $user->getProfile()->getFullname();

        if($invoice->getPaid() <> 2)
        {
          $q = Doctrine_Query::create()
             ->from("ApFormPayments a")
             ->where("a.form_id = ? AND a.record_id = ?", array($profile->getFormId(), $profile->getEntryId()))
             ->andWhere("a.status <> ?", 2);
          $transaction = $q->fetchOne();

          if($transaction)
          {
              //Update transaction details
              $transaction->setBillingState($request_details['transaction_id']);
              $transaction->setPaymentDate(date("Y-m-d H:i:s"));

              if($transaction_status == 'completed')
              {
                  $transaction->setStatus(2);
                  $transaction->setPaymentStatus("paid");
              }
              elseif($transaction_status == 'failed')
              {
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("failed");
              }
              else
              {
                  $transaction->setStatus(15);
                  $transaction->setPaymentStatus("pending");
              }

              $transaction->save();
          }
          else
          {
              $user = Doctrine_Core::getTable('SfGuardUser')->find(array($profile->getUserId()));

              $fullname = $user->getProfile()->getFullname();

              //Add a new transaction if one doesn't exist
              $transaction = new ApFormPayments();
              $transaction->setFormId($profile->getFormId());
              $transaction->setRecordId($profile->getEntryId());
              $transaction->setPaymentId($profile->getFormId()."/".$profile->getEntryId()."/".$profile->getId());
              $transaction->setDateCreated(date("Y-m-d H:i:s"));
              $transaction->setPaymentFullname($fullname);
              $transaction->setPaymentMerchantType('Pesaflow');
              $transaction->setPaymentTestMode("0");

              $transaction->setBillingState($transaction_id);
              $transaction->setPaymentDate(date("Y-m-d H:i:s"));

              if($transaction_status == 'completed')
              {
                  $transaction->setStatus(2);
                  $transaction->setPaymentStatus("paid");
              }
              elseif($transaction_status == 'failed')
              {
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("failed");
              }
              else
              {
                  $transaction->setStatus(15);
                  $transaction->setPaymentStatus("pending");
              }

              $transaction->save();
          }

          if($transaction_status == 'completed' && $this->invoice_manager->get_invoice_total_owed($invoice->getId()) <= 0)
          {
              //Payment is successful
              $profile->setDeleted(0);
          }
          elseif($transaction_status == 'failed')
          {
              //Payment has failed
              $invoice->setDeleted(1);
          }
          else
          {
              //Payment is incomplete
              $profile->setDeleted(1);
          }

          //Update invoice and allow any triggers to take place
          $profile->save();
        }
        else
        {
          $update_details['update_status'] = "already paid";
        }

        error_log("Pesaflow IPN Profile: ".$request_details['status']."/".$profile->getId());


        //return invoice/payment details for confirmation
        if($profile->getDeleted() == 0)
        {
            $update_details['invoice_status'] = "paid";
        }
        else
        {
            $update_details['invoice_status'] = "pending confirmation";
        }

        $update_details['application_id'] = $profile->getTitle();
        $update_details['user_email'] = $user->getEmailAddress();
        $update_details['user_mobile'] = $user->getProfile()->getMobile();
        $update_details['user_fullname'] = $user->getProfile()->getFullname();
      }
      catch(Exception $ex)
      {
        error_log("Debug-pesa: ".$ex);
      }

        return $update_details;
    }
}
