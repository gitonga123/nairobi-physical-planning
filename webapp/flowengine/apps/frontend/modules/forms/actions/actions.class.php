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
      private $cache;
      private $token;

      private $key;
      public function initialize($context, $moduleName, $actionName)
      {
            parent::initialize($context, $moduleName, $actionName);

            $this->cache = new sfFileCache([
                  'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
            ]);
            $user = $this->getUser();
            $username = '';

            if ($user->isAuthenticated()) {
                  $username = $user->getUsername();
            }


            $this->key = "jambo_token_{$username}";


            if (empty($this->cache->get($this->key))) {
                  $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo2ODgzLCJpc19hY3RpdmUiOnRydWUsInVzZXJuYW1lIjoiMjU0NzEwNTk0Mjk4IiwiZmlyc3RfbmFtZSI6IkRhbmllbCIsImxhc3RfbmFtZSI6Ik1VVFdJUkkiLCJleHAiOjE3MjE3NDIwNDMsInBlcm1pc3Npb25zIjp7ImFjY2Vzc19zZWxmX3NlcnZpY2VfcG9ydGFsIjp0cnVlLCJjcmVhdGVfYmlsbCI6dHJ1ZSwicmVnaXN0ZXJfYnVzaW5lc3MiOnRydWUsInJlcXVlc3RfaW5zcGVjdGlvbiI6dHJ1ZSwicmVxdWVzdF9saWNlbnNlIjp0cnVlLCJsb2dfcGF5bWVudCI6dHJ1ZSwiYWNjZXNzX2FkbWluIjpmYWxzZSwidmlld19kYXNoYm9hcmQiOmZhbHNlfSwicm9sZXMiOlsiY2l0aXplbiJdLCJyZXZlbnVlX3N0cmVhbV9yb2xlcyI6e30sImN1c3RvbWVyIjoiNjUzNGFjN2MtNDViZC00MmU5LTlmOWQtN2RjMTA0MzVhMWQ1IiwiaWRfbm8iOiIzMDExNTgzNSIsInN1Yl9jb3VudGllcyI6W10sImVtYWlsIjoibXV0d2lyaWRhbmllbHNjaUBnbWFpbC5jb20iLCJwaG9uZSI6IjI1NDcxMDU5NDI5OCJ9.DBZ3IgiuUwZRhesRIRfkIojZu6bnnNBCfZTzBEdRa7k";

                  $_SESSION['jambo_backup_token'] = $token;
                  $this->cache->set($this->key, $token, 3600);
            }

            $this->token = $this->cache->get($this->key);
      }
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
                  ->orderBy('a.ordering ASC');
            $this->groups = $q->execute();

            if ($request->getParameter("profile")) {
                  //If profile is not active then redirect to profile
                  $profile = Functions::get_client_profile($request->getParameter("profile"));

                  if ($profile->getDeleted() == 0) {
                        $this->getUser()->setAttribute("current_profile", $request->getParameter("profile"));
                        $this->setLayout("layoutprofile");
                  } else {
                        $this->redirect("/plan/profile/view/id/" . $profile->getId());
                  }
            } else {
                  $this->getUser()->setAttribute("current_profile", false);
                  //$this->setLayout("layoutdash");

            }

            $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Submit Applications");

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
            $this->current_profile = $this->getUser()->getAttribute("current_profile");

            $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Submit Application");

            if ($this->current_profile) {
                  $this->setLayout("layoutmentordash");
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
            $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Confirm Applications");
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


            $q = Doctrine_Query::create()
                  ->from('MfInvoice m')
                  ->where('m.id = ?', $request->getParameter("invoice"));

            $this->invoice = $q->fetchOne();

            if ($this->invoice->getPaid() == 1) {
                  // check if invoice is paid;
                  $billing_reference_number = $this->invoice->getFormEntry()->getFormId() . "" . $this->invoice->getFormEntry()->getEntryId() . "" . $this->invoice->getId();
                  $result = $this->check_payment_jambo_pay($billing_reference_number);
                  if ($result['success']) {
                        $this->updateInvoiceToPaid($billing_reference_number, $this->invoice->id, $result['receipt']);
                        $this->redirect('/plan/invoices/view/id/' . $this->invoice->getId());
                  }
            }

            $this->user = Doctrine_Core::getTable('sfGuardUser')->find($this->getUser()->getGuardUser()->getId());

            $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Payment");

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

            $applicationManager = new ApplicationManager();


            return $this->renderText(json_encode(['status' => 500, 'content' => "Something Went Wrong. Please try again later."]));

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
                  return $this->renderText(json_encode(['status' => 404, 'content' => ['msg' => 'invoice/application not found']]));
            }

            $q = Doctrine_Query::create()
                  ->from("ApFormPayments a")
                  ->where("a.invoice_id = ? AND a.status = ?", [$this->invoice->getId(), 1])
                  ->orderBy('a.afp_id desc');
            $transaction = $q->fetchOne();

            $billing_reference_number = $this->invoice->getFormEntry()->getFormId() . "" . $this->invoice->getFormEntry()->getEntryId() . "" . $this->invoice->getId();

            error_log("Billing reference number ---->{$billing_reference_number}");

            if ($transaction) {
                  //Update transaction details
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("pending");
                  $transaction->setPaymentMerchantType('SISIBOPAY');
                  $transaction->setPaymentAmount($this->invoice->getTotalAmount());
                  $transaction->setInvoiceId($this->invoice->getId());
                  $transaction->setPaymentCurrency('KES');

                  $transaction->save();
            } else {

                  //Add a new transaction if one doesn't exist
                  $transaction = new ApFormPayments();
                  $transaction->setFormId($this->invoice->getFormEntry()->getFormId());
                  $transaction->setRecordId($this->invoice->getFormEntry()->getEntryId());
                  $transaction->setPaymentId($billing_reference_number);
                  $transaction->setDateCreated(date("Y-m-d H:i:s"));
                  $transaction->setPaymentAmount($this->invoice->getTotalAmount());
                  $transaction->setPaymentMerchantType('SISIBOPAY');
                  $transaction->setPaymentTestMode("0");
                  $transaction->setPaymentDate(date("Y-m-d H:i:s"));
                  $transaction->setInvoiceId($this->invoice->getId());
                  $transaction->setStatus(1);
                  $transaction->setPaymentStatus("pending");
                  $transaction->setPaymentCurrency('KES');
                  $transaction->save();
            }

            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/initiate_payment/';

            $stream = new Stream();

            $subcounty_name = $applicationManager->getSubCountyNameFromApplication(
                  $this->application->getFormId(),
                  $this->application->getEntryId()
            );

            error_log("Sub county name is ----> {$subcounty_name}");

            $subcounty = $this->subcountyList($subcounty_name);
            $create_bill_action = $this->createBill(
                  [
                        'sub_county' => $subcounty,
                        'bill_number' => $billing_reference_number,
                        "revenue_stream" => "Physical_Planning"
                  ],
                  $this->invoice->getId()
            );

            error_log("Create bill action reference details --->");
            error_log(json_encode($create_bill_action));

            if ($create_bill_action['success'] && isset($create_bill_action['bill_ref'])) {
                  $this->invoice->setDocRefNumber($create_bill_action['bill_ref']);
                  $this->invoice->save();
            }

            $callback_url = sfConfig::get('app_amkatek_callback_payment');

            error_log("Callback url --->{$callback_url}");
            $payload = [
                  'phone_number' => $request->getPostParameter('phone_number'),
                  'amount' => $this->invoice->getTotalAmount(),
                  'bill_number' => $billing_reference_number,
                  'callback_url' => $callback_url
            ];

            error_log("Sample payload ---->", );
            error_log(print_r($payload, true));

            error_log("Url bill sent to ----> {$url}");

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'data' => $payload,
                  'headers' => [
                        "Authorization" => "JWT " . $this->token,
                  ]
            ]);

            error_log("Response ---> initite payment");

            error_log($query_response->status);

            error_log(json_encode($query_response->content));

            if ($query_response->content['verify_otp']) {
                  $this->cache->set("{$this->key}_jambo_wallet_otp", $query_response->content['otp'], 3600);
            }
            $this->cache->set("{$this->key}_jambo_pay_ref", $query_response->content["ref"], 3600);

            if (!empty($query_response->content["ref"])) {
                  $transaction->setNarration($query_response->content["ref"]);

                  $transaction->save();

                  $this->invoice->setTransactionId($query_response->content["ref"]);

            }

            return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content]));
      }

      public function executeVerifyOtp(sfWebRequest $request)
      {
            $user_otp = $request->getPostParameter('user_otp');

            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/authorize_wallet_payment/';

            $stream = new Stream();

            $invoice_id = $request->getParameter('invoice');

            $q = Doctrine_Query::create()
                  ->from('MfInvoice a')
                  ->where('a.id = ?', $invoice_id)
                  ->limit(1);
            $this->invoice = $q->fetchOne();


            $ref = $this->cache->get("{$this->key}_jambo_pay_ref");

            error_log("Verify OTP URL --->{$url}");
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
                        "Authorization" => "JWT " . $this->token,
                  )
            ]);

            $response_content = $query_response->content;

            if (isset($response_content['ref']) || $query_response->status == 201) {
                  return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content, 'success' => true]));
            }
            return $this->renderText(json_encode(['status' => 403, 'content' => ['msg' => 'OTP Invalid regenerate a new one.', 'success' => false]]));
            // return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content]));
      }

      public function executeRegeneratejamboonetimepassword($request)
      {
            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/regenerate_otp/';

            $stream = new Stream();

            $ref = $this->cache->get("{$this->key}_jambo_pay_ref");
            error_log("Regenerate OTP URL --->{$url}");
            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'data' => [
                        'ref' => $ref
                  ],
                  'headers' => array(
                        "Authorization" => "JWT " . $this->token,
                  )
            ]);

            if ($query_response->status == 201) {
                  return $this->renderText(json_encode(['success' => true, 'status' => $query_response->status, 'content' => $query_response->content]));
            }

            $this->cache->set("{$this->key}_jambo_token", $query_response->content['otp'], 3600);

            return $this->renderText(json_encode(['success' => false, 'status' => $query_response->status, 'content' => ['msg' => 'Check your phone for an OTP']]));
      }

      public function executeProcessPayments(sfWebRequest $request)
      {
            $response = $request->getContent();
            $response = json_decode($response, true);

            if (strtolower($response['status']) == 'success') {
                  $q = Doctrine_Query::create()
                        ->from("ApFormPayments a")
                        ->where("a.payment_id = ?", $response['bill_number'])
                        ->orWhere("a.narration = ?", $response['ref'])
                        ->orderBy('a.afp_id desc');
                  $transaction = $q->fetchOne();

                  if ($transaction) {
                        $transaction->setPaymentTestMode($response['mode_of_payment']);

                        $transaction->setPaymentStatus('paid');
                        $transaction->setStatus(2);

                        $transaction->save();


                        $q = Doctrine_Query::create()
                              ->from('MfInvoice m')
                              ->where('m.id = ?', $transaction->getInvoiceId());

                        $invoice = $q->fetchOne();

                        $invoice->setPaid(2);
                        if (array_key_exists('receipt_numbers', $response)) {
                              $invoice->setReceiptNumber(json_encode($response['receipt_numbers']));
                        }

                        $invoice->save();

                        return $this->renderText(json_encode(['status' => 200, 'data' => ['msg' => 'paid'], 'payload' => $response]));
                  } else {
                        return $this->renderText(json_encode(['status' => 404, 'data' => ['msg' => 'Bill Reference not found.'], 'payload' => $response]));
                  }
            } else {
                  return $this->renderText(json_encode(['status' => 500, 'data' => ['msg' => 'Something went Wrong.'], 'payload' => $response]));
            }
      }

      public function updateInvoiceToPaid($billing_reference_number, $invoice_id, $receipt)
      {
            $q = Doctrine_Query::create()
                  ->from("ApFormPayments a")
                  ->where("a.payment_id = ?", $billing_reference_number)
                  ->where("a.invoice_id = ?", $invoice_id)
                  ->orderBy('a.afp_id desc');
            $transaction = $q->fetchOne();

            $transaction->setPaymentStatus('paid');
            $transaction->setStatus(2);

            $transaction->save();


            $q = Doctrine_Query::create()
                  ->from('MfInvoice m')
                  ->where('m.id = ?', $transaction->getInvoiceId());

            $invoice = $q->fetchOne();

            $invoice->setPaid(2);
            $invoice->setReceiptNumber(json_encode($receipt));

            $invoice->save();

            return true;
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

            error_log("Invoice is checking if paid ---->{$this->invoice->getPaid()}");
            if ($this->invoice && $this->invoice->getPaid() == 2) {
                  return $this->renderText(json_encode(['success' => true, 'status' => 200, 'data' => ['msg' => 'Payment Successful.']]));
            }

            $billing_reference_number = $this->invoice->getFormEntry()->getFormId() . "" . $this->invoice->getFormEntry()->getEntryId() . "" . $this->invoice->getId();

            error_log("Billing Reference Number ---> {$billing_reference_number}");

            $result = $this->check_payment_jambo_pay($billing_reference_number);

            if (!$result['success']) {
                  return $this->renderText(json_encode(['success' => false, 'status' => 404, 'data' => ['msg' => 'Payment Not Found.']]));
            } else {

                  $this->updateInvoiceToPaid($billing_reference_number, $this->invoice->id, $result['receipt']);
                  return $this->renderText(json_encode(['success' => true, 'status' => 200, 'data' => ['msg' => 'Payment Successful.']]));
            }
      }

      public function check_payment_jambo_pay($billing_reference_number)
      {
            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/bill/status/';

            $stream = new Stream();

            error_log("Checkout SISIBO Pay URL --->{$url}");

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'headers' => array(
                        "Authorization" => "JWT " . $this->token,
                  ),
                  'data' => [
                        'bill_number' => $billing_reference_number
                  ]
            ]);

            if ($query_response->status == 200 || $query_response->status == 201) {
                  $content = $query_response->content;
                  error_log("Payment confirmation is ---->");
                  error_log(print_r($content, true));
                  error_log("Paid status ---->");

                  if (strtolower($content['status']) == 'paid') {
                        return ['success' => true, 'receipt' => $content['receipt_numbers']];
                  } else {
                        return ['success' => false, 'message' => 'Invoice still unpaid'];
                  }

            } else {
                  return ['success' => false];
            }
      }

      public function subcountyList($subcounty_name)
      {
            $sub_counties_key = "subcounty_list_jambo";
            $counties_list = $this->cache->get($sub_counties_key);
            if ($counties_list) {
                  $counties = json_decode($counties_list, true);
                  $found_result = $this->search_county_by_name($counties, 'title', $subcounty_name);

                  if ($found_result['success']) {
                        return $found_result['id'];
                  }

                  return '';
            } else {
                  $url = sfConfig::get('app_api_jambo_url') . 'api/v1/county/sub_counties/';

                  $stream = new Stream();

                  error_log("Sub county list URL --->{$url}");

                  $query_response = $stream->sendRequest([
                        'url' => $url,
                        'method' => 'GET',
                        'ssl' => 'none',
                        'contentType' => 'json',
                        'headers' => [
                              "Authorization" => "JWT {$this->token}",
                        ]
                  ]);

                  if ($query_response->status == 200 || $query_response->status == 201) {
                        $content = $query_response->content['results'];
                        $this->cache->set($sub_counties_key, json_encode($content), 3600);
                        $found_result = $this->search_county_by_name($content, 'title', $subcounty_name);

                        if ($found_result['success']) {
                              return $found_result['id'];
                        }

                        return '';
                  }
            }

            return '';
      }

      public function wardList()
      {
            $api = '/api/v1/county/wards/';
      }

      public function executeSubcounties()
      {
            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/county/sub_counties/';

            $stream = new Stream();

            error_log("Sub county list URL --->{$url}");

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'GET',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'headers' => [
                        "Authorization" => "JWT {$this->token}",
                  ]
            ]);

            return $this->renderText(json_encode($query_response->content));

      }

      public function executeWards()
      {
            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/county/wards/';

            $stream = new Stream();

            error_log("Ward sub county list URL --->{$url}");

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'GET',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'headers' => [
                        "Authorization" => "JWT {$this->token}",
                  ]
            ]);

            return $this->renderText(json_encode($query_response->content));

      }



      public function createBill($data, $invoice)
      {
            $stream = new Stream();

            $q = Doctrine_Query::create()
                  ->from("MfInvoiceDetail a")
                  ->where("a.invoice_id = ?", $invoice);
            $invoice_details = $q->execute();
            $items = [];

            foreach ($invoice_details as $detail) {
                  array_push($items, [
                        'description' => $detail->description,
                        'amount' => $detail->amount
                  ]);
            }

            $data['items'] = $items;

            $url = sfConfig::get('app_api_jambo_url') . 'api/v1/create_bill/';

            error_log("Create bill URL --->{$url}");
            error_log(json_encode($data));

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'headers' => [
                        "Authorization" => "JWT " . $this->token,
                  ],
                  'data' => $data
            ]);

            error_log("Response ---> Create bill");

            error_log($query_response->status);

            error_log(json_encode($query_response->content));


            if ($query_response->status == 200 || $query_response->status == 201) {
                  $this->cache->set("{$this->key}_bill_ref", $query_response->content['bill_ref'], 3600);

                  error_log($query_response->content['bill_ref']);

                  return ['success' => true, 'bill_ref' => $query_response->content['bill_ref']];
            } else {
                  error_log("Unable to create the bill at the moment");

                  return ['success' => false, 'bill_ref' => ''];
            }
      }

      public function search_county_by_name($array, $key, $name)
      {
            $results = ['success' => false];

            $random = $results;

            if (is_array($array)) {

                  foreach ($array as $subarray => $value) {
                        if (strtolower($value['title']) == strtolower($name)) {
                              $results = ['success' => true, 'name' => $value['title'], 'id' => $value['id']];
                        }

                        $random = ['success' => true, 'name' => $value['title'], 'id' => $value['id']];
                  }
            }

            if (!$results['success']) {
                  return $random;
            }
            return $results;
      }

      public function executeCachedPlotDetails(sfWebRequest $request)
      {
            $cache = new sfFileCache([
                  'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
            ]);

            $cached_key = trim($request->getParameter('key'));

            $cached_plot_details = $cache->get($cached_key);

            return $this->json(['success' => true, 'plot_details' => $cached_plot_details]);
      }

      private function json($content, $status = 200)
      {
            $this->getResponse()->setHttpHeader('Content-Type', 'application/json');
            $this->getResponse()->setContent(json_encode($content));
            $this->getResponse()->setStatusCode($status);
            return sfView::NONE;
      }
}
