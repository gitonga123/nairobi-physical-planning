<?php

/**
 * Forms actions.
 *
 * Dynamic Form Generator Components for application forms
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class formsActions extends sfActions
{
      /**
       * Executes 'Groups' action
       *
       * Displays form categories
       *
       * @param sfRequest $request A request object
       */
      public function executeGroups(sfWebRequest $request)
      {
            $q = Doctrine_Query::create()
                  ->from('FormGroups a')
                  ->orderBy('a.group_name ASC');
            $this->groups = $q->execute();

            if ($request->getParameter("profile")) {
                  //If profile is not active then redirect to profile
                  $profile = Functions::get_client_profile($request->getParameter("profile"));

                  if ($profile->getDeleted() == 0) {
                        $this->getUser()->setAttribute("current_profile", $request->getParameter("profile"));
                        $this->setLayout("layoutprofile");
                  } else {
                        $this->redirect("/index.php/profile/view/id/" . $profile->getId());
                  }
            } else {
                  $this->getUser()->setAttribute("current_profile", false);
                  //$this->setLayout("layoutdash");

            }

            $this->setLayout("layoutmentordash");
      }

      /**
       * Executes 'Info' action
       *
       * Displays a dynamically generated application form
       *
       * @param sfRequest $request A request object
       */
      public function executeInfo(sfWebRequest $request)
      {
            $q = Doctrine_Query::create()
                  ->from('ApForms a')
                  ->where('a.form_id = ?', $request->getParameter("id"));
            $this->form = $q->fetchOne();

            if ($this->getUser()->getAttribute("current_profile")) {
                  $this->setLayout("layoutprofile");
            } else {
                  //$this->setLayout("layoutdash");
                  $this->setLayout("layoutmentordash");
            }
      }

      /**
       * Executes 'View' action
       *
       * Displays a dynamically generated application form
       *
       * @param sfRequest $request A request object
       */
      public function executeView(sfWebRequest $request)
      {
            if ($this->getUser()->getAttribute("current_profile")) {
                  $this->setLayout("layoutprofile");
            } else {
                  //$this->setLayout("layoutformbuilder");
                  $this->setLayout("layoutmentordashsubmit");
            }
      }

      /**
       * Executes 'Confirm' action
       *
       * Displays a dynamically generated application form
       *
       * @param sfRequest $request A request object
       */
      public function executeConfirm(sfWebRequest $request)
      {
            if ($this->getUser()->getAttribute("current_profile")) {
                  $this->setLayout("layoutprofile");
            } else {
                  $this->setLayout("layoutmentordashsubmit");
            }
      }

      /**
       * Executes 'Payment' action
       *
       * Displays a dynamically generated application form
       *
       * @param sfRequest $request A request object
       */
      public function executePayment(sfWebRequest $request)
      {
            $q = Doctrine_Query::create()
                  ->from('FormEntry a')
                  ->where('a.entry_id = ?', $request->getParameter("app_id"))
                  ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
            $this->application = $q->fetchOne();

            if (!$this->application) {
                  $this->forward404('Application Not Found.');
            }


            $q =  Doctrine_Query::create()
                  ->from('MfInvoice m')
                  ->where('m.id = ?', $request->getParameter("invoice"));

            $this->invoice = $q->fetchOne();

            $this->user = Doctrine_Core::getTable('sfGuardUser')->find($this->getUser()->getGuardUser()->getId());

            if ($this->getUser()->getAttribute("current_profile")) {
                  $this->setLayout("layoutprofile");
            } else {
                  $this->setLayout("layoutdash");
            }

            if ($request->getParameter("invoice")) {
                  $_GET['invoice'] = $request->getParameter("invoice");
            }

            $this->setLayout("layoutmentordash");
      }

      /**
       * Executes 'Paymentbraintree' action
       *
       * Displays a dynamically generated application form
       *
       * @param sfRequest $request A request object
       */
      public function executePaymentbraintree(sfWebRequest $request)
      {
            $this->setLayout(false);
      }

      public function executeDownload(sfWebRequest $request)
      {
            $this->setLayout(false);
      }

      public function executeUpload(sfWebRequest $request)
      {
            $this->setLayout(false);
      }

      public function executeViewimg(sfWebRequest $request)
      {
            $this->setLayout(false);
      }

      /**
       * Executes 'Filterdropdown' action
       *
       * Filter a dropdown based on selected option
       *
       * @param sfRequest $request A request object
       */
      public function executeFilterdropdown(sfWebRequest $request)
      {
            $form_id = $request->getParameter("form_id");
            $element_id = $request->getParameter("element_id");
            $link_id = $request->getParameter("link_id");
            $option_id = $request->getParameter("option_id");

            $q = Doctrine_Query::create()
                  ->from("ApDropdownFilters a")
                  ->where("a.form_id = ? AND a.element_id = ? AND a.link_id = ? AND a.option_id = ?", array($form_id, $element_id, $link_id, $option_id));
            $filters = $q->execute();

            $filter_options = array();

            foreach ($filters as $filter) {
                  $filter_options[] = "a.option_id = " . $filter->getLioptionId();
            }

            $filter_options_query = implode(" OR ", $filter_options);

            $q = Doctrine_Query::create()
                  ->from("ApElementOptions a")
                  ->where("a.form_id = ?", $form_id)
                  ->andWhere("a.element_id = ?", $link_id)
                  ->andWhere($filter_options_query)
                  ->orderBy("a.position ASC");
            $options = $q->execute();

            $filter_js = "";

            $q = Doctrine_Query::create()
                  ->from("ApDropdownFilters a")
                  ->where("a.form_id = ? AND a.element_id = ?", array($form_id, $link_id));

            if ($q->count() > 0) {
                  $filter = $q->fetchOne();

                  $filter_js = "onChange='filter_dropdown(" . $form_id . ", " . $link_id . ", " . $filter->getLinkId() . ", this.value);'";
            }

            echo "<select class='element select' id='element_" . $link_id . "' name='element_" . $link_id . "' " . $filter_js . ">";
            echo "<option></option>";
            foreach ($options as $option) {
                  echo "<option value='" . $option->getOptionId() . "'>" . $option->getOptionText() . "</option>";
            }
            echo "</select>";
            exit;
      }
      public function executeDelete(sfWebRequest $request)
      {
            $this->setLayout(false);
      }
      /**
       * Executes 'Confirmpayment' action
       *
       * Payment form
       *
       * @param sfRequest $request A request object
       */
      public function executeConfirmpayment(sfWebRequest $request)
      {
            if (empty($this->getUser()->getAttribute("invoice_id"))) {
                  $this->getUser()->setAttribute("invoice_id", $request->getParameter("invoiceid"));
            }

            $this->setLayout("layoutdashfull");
      }

      public function executeInitiatePayment(sfWebRequest $request)
      {
            $invoice_id = $request->getParameter('invoice');

            error_log("Invoice id is -->");
            error_log($invoice_id);

            $q = Doctrine_Query::create()
                  ->from('FormEntry a')
                  ->where('a.id = ?', $request->getParameter("application"))
                  ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
            $this->application = $q->fetchOne();

            error_log("application  id is -->");
            error_log($request->getParameter("application"));

            $q = Doctrine_Query::create()
                  ->from('MfInvoice a')
                  ->where('a.id = ?', $invoice_id)
                  ->limit(1);
            $this->invoice = $q->fetchOne();

            if (!$this->invoice || !$this->application) {
                  return $this->renderText(json_encode(['status' => 404, 'content' => ['msg' => 'invoice/application not found']]));
            }

            $q = Doctrine_Query::create()
                  ->from("ApFormPayments a")
                  ->where("a.invoice_id = ? AND a.status = ?", array($this->invoice->getId(), 1))
                  ->orderBy('a.afp_id desc');
            $transaction = $q->fetchOne();

            $billing_reference_number = $this->invoice->getFormEntry()->getFormId() . "" . $this->invoice->getFormEntry()->getEntryId() . "" . $this->invoice->getId();


            if ($transaction) {
                  error_log('----Transaction found----');
                  //Update transaction details
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("pending");
                  $transaction->setPaymentMerchantType('Jambo Pay');
                  $transaction->setPaymentAmount($this->invoice->getTotalAmount());
                  $transaction->setInvoiceId($this->invoice->getId());
                  $transaction->setPaymentCurrency('KES');

                  $transaction->save();
            } else {
                  error_log('----Transaction not found----');

                  //Add a new transaction if one doesn't exist
                  $transaction = new ApFormPayments();
                  $transaction->setFormId($this->invoice->getFormEntry()->getFormId());
                  $transaction->setRecordId($this->invoice->getFormEntry()->getEntryId());
                  $transaction->setPaymentId($billing_reference_number);
                  $transaction->setDateCreated(date("Y-m-d H:i:s"));
                  $transaction->setPaymentAmount($this->invoice->getTotalAmount());
                  $transaction->setPaymentMerchantType('Jambo Pay');
                  $transaction->setPaymentTestMode("0");
                  $transaction->setPaymentDate(date("Y-m-d H:i:s"));
                  $transaction->setInvoiceId($this->invoice->getId());
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("pending");
                  $transaction->setPaymentCurrency('KES');
                  $transaction->save();
            }

            $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/initiate_payment/';

            $stream = new Stream();

            if (isset($_SESSION['jambo_token'])) {
                  $token = $_SESSION['jambo_token'];
            } else {
                  $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyMTQsImlzX2FjdGl2ZSI6dHJ1ZSwidXNlcm5hbWUiOiIyNTQ3MTA1OTQyOTgiLCJmaXJzdF9uYW1lIjoiREFOSUVMIiwibGFzdF9uYW1lIjoiTVVUV0lSSSIsImV4cCI6MTcxNzY1MjU4OCwicGVybWlzc2lvbnMiOnsiYWNjZXNzX3NlbGZfc2VydmljZV9wb3J0YWwiOnRydWUsImNyZWF0ZV9iaWxsIjp0cnVlLCJyZWdpc3Rlcl9idXNpbmVzcyI6dHJ1ZSwicmVxdWVzdF9pbnNwZWN0aW9uIjp0cnVlLCJyZXF1ZXN0X2xpY2Vuc2UiOnRydWUsImxvZ19wYXltZW50Ijp0cnVlLCJhY2Nlc3NfYWRtaW4iOmZhbHNlLCJ2aWV3X2Rhc2hib2FyZCI6ZmFsc2V9LCJyb2xlcyI6WyJjaXRpemVuIl0sInJldmVudWVfc3RyZWFtX3JvbGVzIjp7fSwiY3VzdG9tZXIiOiI3NWY5NzA5NS00ZTkzLTQ0OGMtOTliZS00YTYwNmFhN2JkNzEiLCJpZF9ubyI6IjMwMTE1ODM1IiwiZW1haWwiOiJtdXR3aXJpZGFuaWVsc2NpQGdtYWlsLmNvbSIsInBob25lIjoiMjU0NzEwNTk0Mjk4In0.o-l-orFsrCuGHYYqmPYGkjnj-NuAduj6rjdsLxUPphc";
            }

            error_log("Amount to pay ---->" . $this->invoice->getTotalAmount());

            $callback_url = sfConfig::get('app_jambo_pay_callback') . 'index.php/payment/processpayments';

            error_log("Callback url below --->" . $callback_url);

            error_log("initiate request payment ---->");

            $payload = [
                  'phone_number' => $request->getPostParameter('phone_number'),
                  'amount' => $this->invoice->getTotalAmount(),
                  'bill_number' => $billing_reference_number,
                  'callback_url' => sfConfig::get('app_jambo_pay_callback') . 'index.php/payment/processpayments'
            ];

            error_log(print_r($payload, true));


            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'data' => $payload,
                  'headers' => array(
                        "Authorization" => "JWT " . $token,
                  )
            ]);

            error_log(print_r($query_response->content, true));

            if ($query_response->content['verify_otp']) {
                  $_SESSION['jambo_wallet_otp'] = $query_response->content['otp'];
            }

            $_SESSION['jambo_pay_ref'] = $query_response->content["ref"];

            if (!empty($query_response->content["ref"])) {
                  $transaction->setNarration($query_response->content["ref"]);

                  $transaction->save();

                  $this->invoice->setTransactionId($query_response->content["ref"]);
                  $this->invoice->save();
            }


            error_log("Session keys aare as at below");
            error_log($_SESSION['jambo_pay_ref']);
            return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content]));
      }

      public function executeVerifyOtp(sfWebRequest $request)
      {
            $user_otp = $request->getPostParameter('user_otp');

            error_log("User otp --->" . $user_otp);

            // $otp = $_SESSION['jambo_wallet_otp'];

            // if (!($user_otp == $otp)) {
            //       return $this->renderText(json_encode(['status' => 403, 'content' => ['msg' => 'OTP Invalid regenerate a new one.']]));
            // }
            $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/authorize_wallet_payment/';

            $stream = new Stream();
            if (isset($_SESSION['jambo_token'])) {
                  $token = $_SESSION['jambo_token'];
            } else {
                  $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyMTQsImlzX2FjdGl2ZSI6dHJ1ZSwidXNlcm5hbWUiOiIyNTQ3MTA1OTQyOTgiLCJmaXJzdF9uYW1lIjoiREFOSUVMIiwibGFzdF9uYW1lIjoiTVVUV0lSSSIsImV4cCI6MTcxNzY1MjU4OCwicGVybWlzc2lvbnMiOnsiYWNjZXNzX3NlbGZfc2VydmljZV9wb3J0YWwiOnRydWUsImNyZWF0ZV9iaWxsIjp0cnVlLCJyZWdpc3Rlcl9idXNpbmVzcyI6dHJ1ZSwicmVxdWVzdF9pbnNwZWN0aW9uIjp0cnVlLCJyZXF1ZXN0X2xpY2Vuc2UiOnRydWUsImxvZ19wYXltZW50Ijp0cnVlLCJhY2Nlc3NfYWRtaW4iOmZhbHNlLCJ2aWV3X2Rhc2hib2FyZCI6ZmFsc2V9LCJyb2xlcyI6WyJjaXRpemVuIl0sInJldmVudWVfc3RyZWFtX3JvbGVzIjp7fSwiY3VzdG9tZXIiOiI3NWY5NzA5NS00ZTkzLTQ0OGMtOTliZS00YTYwNmFhN2JkNzEiLCJpZF9ubyI6IjMwMTE1ODM1IiwiZW1haWwiOiJtdXR3aXJpZGFuaWVsc2NpQGdtYWlsLmNvbSIsInBob25lIjoiMjU0NzEwNTk0Mjk4In0.o-l-orFsrCuGHYYqmPYGkjnj-NuAduj6rjdsLxUPphc";
            }

            $invoice_id = $request->getParameter('invoice');

            $q = Doctrine_Query::create()
                  ->from('MfInvoice a')
                  ->where('a.id = ?', $invoice_id)
                  ->limit(1);
            $this->invoice = $q->fetchOne();

            error_log("Below is the amount ---->");
            error_log($this->invoice->getTotalAmount());

            $ref = $_SESSION['jambo_pay_ref'];

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'data' => [
                        'ref' => $ref,
                        "otp" => $user_otp,
                        'amount' => $this->invoice->getTotalAmount(),
                  ],
                  'headers' => array(
                        "Authorization" => "JWT " . $token,
                  )
            ]);

            $response_content = $query_response->content;

            error_log("Verify OTP Wallet response ---->");

            error_log($response_content);
            error_log(print_r($response_content, true));

            if (!empty($response_content['ref'])) {
                  return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content])); 
            }
            return $this->renderText(json_encode(['status' => 403, 'content' => ['msg' => 'OTP Invalid regenerate a new one.']]));
            // return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content]));
      }

      public function executeRegenerateOTPToken($request)
      {
            $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/regenerate_otp/';

            $stream = new Stream();
            if (isset($_SESSION['jambo_token'])) {
                  $token = $_SESSION['jambo_token'];
            } else {
                  $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyMTQsImlzX2FjdGl2ZSI6dHJ1ZSwidXNlcm5hbWUiOiIyNTQ3MTA1OTQyOTgiLCJmaXJzdF9uYW1lIjoiREFOSUVMIiwibGFzdF9uYW1lIjoiTVVUV0lSSSIsImV4cCI6MTcxNzY1MjU4OCwicGVybWlzc2lvbnMiOnsiYWNjZXNzX3NlbGZfc2VydmljZV9wb3J0YWwiOnRydWUsImNyZWF0ZV9iaWxsIjp0cnVlLCJyZWdpc3Rlcl9idXNpbmVzcyI6dHJ1ZSwicmVxdWVzdF9pbnNwZWN0aW9uIjp0cnVlLCJyZXF1ZXN0X2xpY2Vuc2UiOnRydWUsImxvZ19wYXltZW50Ijp0cnVlLCJhY2Nlc3NfYWRtaW4iOmZhbHNlLCJ2aWV3X2Rhc2hib2FyZCI6ZmFsc2V9LCJyb2xlcyI6WyJjaXRpemVuIl0sInJldmVudWVfc3RyZWFtX3JvbGVzIjp7fSwiY3VzdG9tZXIiOiI3NWY5NzA5NS00ZTkzLTQ0OGMtOTliZS00YTYwNmFhN2JkNzEiLCJpZF9ubyI6IjMwMTE1ODM1IiwiZW1haWwiOiJtdXR3aXJpZGFuaWVsc2NpQGdtYWlsLmNvbSIsInBob25lIjoiMjU0NzEwNTk0Mjk4In0.o-l-orFsrCuGHYYqmPYGkjnj-NuAduj6rjdsLxUPphc";
            }
            $ref = $_SESSION['jambo_pay_ref'];
            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'data' => [
                        'ref' => $ref
                  ],
                  'headers' => array(
                        "Authorization" => "JWT " . $token,
                  )
            ]);

            if ($query_response->status !== 200 || $query_response->status !== 201) {
                  return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content]));
            }

            $_SESSION['jambo_wallet_otp'] = $query_response->content['otp'];

            return $this->renderText(json_encode(['status' => $query_response->status, 'content' => ['msg' => 'Check your phone for an OTP']]));
      }

      public function executeProcessPayments(sfWebRequest $request)
      {
            $response = $request->getContent();
            $response = json_decode($response, true);

            error_log(print_r($response, true));

            if (strtolower($response['status']) == 'success') {
                  $q = Doctrine_Query::create()
                        ->from("ApFormPayments a")
                        ->where("a.payment_id = ?", $response['bill_number'])
                        ->where("a.narration = ?", $response['ref'])
                        ->orderBy('a.afp_id desc');
                  $transaction = $q->fetchOne();

                  error_log($transaction);

                  if ($transaction) {
                        $transaction->setPaymentTestMode($response['mode_of_payment']);

                        $transaction->setPaymentStatus('paid');
                        $transaction->setStatus(2);

                        $transaction->save();


                        $q =  Doctrine_Query::create()
                              ->from('MfInvoice m')
                              ->where('m.id = ?', $transaction->getInvoiceId());

                        $invoice = $q->fetchOne();

                        $invoice->setPaid(2);

                        $invoice->save();

                        return $this->renderText(json_encode(['status' => 200, 'data' => ['msg' => 'paid'], 'payload' => $response]));
                  } else {
                        return $this->renderText(json_encode(['status' => 404, 'data' => ['msg' => 'Bill Reference not found.'], 'payload' => $response]));
                  }
            } else {
                  return $this->renderText(json_encode(['status' => 500, 'data' => ['msg' => 'Something went Wrong.'], 'payload' => $response]));
            }
      }

      public function executeConfirmMpesaPayment(sfWebRequest $request)
      {
            $invoice_id = $request->getParameter('invoice');
            $q = Doctrine_Query::create()
                  ->from('FormEntry a')
                  ->where('a.id = ?', $request->getParameter("application"))
                  ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
            $this->application = $q->fetchOne();

            $q = Doctrine_Query::create()
                  ->from('MfInvoice a')
                  ->where('a.id = ?', $invoice_id)
                  ->limit(1);
            $this->invoice = $q->fetchOne();

            if (!$this->invoice || !$this->application) {
                  return $this->renderText(json_encode(['success' => false, 'status' => 404, 'content' => ['msg' => 'invoice/application not found']]));
            }

            error_log($this->invoice->getPaid());

            if ($this->invoice && $this->invoice->getPaid() == 2) {
                  return $this->renderText(json_encode(['success' => true, 'status' => 200, 'data' => ['msg' => 'Payment Successful.']]));
            }

            return $this->renderText(json_encode(['success' => false, 'status' => 404, 'data' => ['msg' => 'Payment Not Found.']]));
      }
}
