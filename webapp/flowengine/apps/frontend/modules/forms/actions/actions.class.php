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
                  return $this->renderText(json_encode([]));
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
                  $transaction->setPaymentMerchantType('Jambo Payment');
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
                  $transaction->setPaymentMerchantType('Jambo Payment');
                  $transaction->setPaymentTestMode("0");
                  $transaction->setPaymentDate(date("Y-m-d H:i:s"));
                  $transaction->setInvoiceId($this->invoice->getId());
                  $transaction->setStatus(2);
                  $transaction->setPaymentStatus("pending");
                  $transaction->setPaymentCurrency('KES');
                  $transaction->save();
            }

            $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/initiate_payment/';

            $stream = new Stream();

            $query_response = $stream->sendRequest([
                  'url' => $url,
                  'method' => 'POST',
                  'ssl' => 'none',
                  'contentType' => 'json',
                  'data' => [
                        'phone_number' => $request->getPostParameter('phone_number'),
                        'amount' => $this->invoice->getTotalAmount(),
                        'bill_number' => $billing_reference_number,
                        "callback_url" => 'http://localhost.uasin.test/index.php/forms/processPayment'
                  ]
            ]);

            error_log(print_r($query_response, true));

            return $this->renderText(json_encode(['status' => $query_response->status, 'content' => $query_response->content]));
      }

      public function executeProcessPayments(sfWebRequest $request)
      {
            $response = $request->getContent();
            $response = json_decode($response);
            error_log("Api Response --->");
            error_log($response);
      }
}
