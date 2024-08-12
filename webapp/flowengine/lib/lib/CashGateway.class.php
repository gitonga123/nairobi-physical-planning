<?php
/**
 *
 * Manages Pesaflow Payments Gateway
 *
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 12:26 AM
 */

class CashGateway {

    private $suffix = "s";
    public $invoice_manager = null;

    //Constructor for PesaflowGateway class
    public function __construct()
    {
        $this->invoice_manager = new InvoiceManager();

        if (empty($_SERVER['HTTPS'])) {
            $this->suffix = "";
        }
    }

    //Display a checkout for the current gateway
    public function checkout($invoice_id, $payment_settings, $backend = false)
    {
        $invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);
        $application = $invoice->getFormEntry();

        $user = Doctrine_Core::getTable('SfGuardUser')->find(array($application->getUserId()));

        $fullname = $user->getProfile()->getFullname();
        $idnumber = $user->getUsername();
        $email = $user->getEmailAddress();

        //One of email or phonenumber is required
        $phonenumber = $user->getProfile()->getMobile();

        $amountExpected = $invoice->getTotalAmount();
		$merchant_reference=$invoice->getFormEntry()->getFormId().'/'.$invoice->getFormEntry()->getEntryId().'/'.$invoice_id;

        $q = Doctrine_Query::create()
           ->from("ApFormPayments a")
           ->where("a.form_id = ? AND a.record_id = ?", array($invoice->getFormEntry()->getFormId(), $invoice->getFormEntry()->getEntryId()))
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
          $transaction->setFormId($invoice->getFormEntry()->getFormId());
          $transaction->setRecordId($invoice->getFormEntry()->getEntryId());
          $transaction->setPaymentId($merchant_reference);
          $transaction->setDateCreated(date("Y-m-d H:i:s"));
          $transaction->setPaymentFullname($fullname);
          $transaction->setPaymentAmount($amountExpected);
          $transaction->setPaymentCurrency($invoice->getCurrency());
          $transaction->setPaymentMerchantType('Cash/Check');
          $transaction->setPaymentTestMode("0");

          $transaction->setPaymentStatus("pending");
          $transaction->setStatus(15);

          $transaction->save();
        }

        $callback_url = "";

        if($backend)
        {
            $callback_url = '/backend.php/applications/confirmpayment?id=' . $application->getFormId() . '&entryid=' . $application->getEntryId() . "&done=1&invoiceid=" . $invoice->getId();
        }
        else
        {
            $callback_url = '/plan/forms/confirmpayment?id=' . $application->getFormId() . '&entryid=' . $application->getEntryId() . "&done=1&invoiceid=" . $invoice->getId();
        }

        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";


        require($prefix_folder . 'includes/init.php');

        header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");

		require($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/language.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/common-validator.php');
        require_once($prefix_folder . 'includes/view-functions.php');
        require_once($prefix_folder . 'includes/post-functions.php');
        require_once($prefix_folder . 'includes/filter-functions.php');
        require_once($prefix_folder . 'includes/entry-functions.php');
        require_once($prefix_folder . 'includes/helper-functions.php');
        require_once($prefix_folder . 'includes/theme-functions.php');
        require_once($prefix_folder . 'lib/recaptchalib.php');
        require_once($prefix_folder . 'lib/php-captcha/php-captcha.inc.php');
        require_once($prefix_folder . 'lib/text-captcha.php');
        require_once($prefix_folder . 'hooks/custom_hooks.php');

        $dbh = mf_connect_db();

        if (mf_is_form_submitted()) {
          $input_array = mf_sanitize($_POST);
          $submit_result = mf_process_form($dbh, $input_array);

          if ($submit_result['status'] === true) {

            $form_id = $input_array['form_id'];
            $new_record_id = $submit_result['entry_id'];

            //Create receipt for invoice
            $receipt = new UploadReceipt();
            $receipt->setFormId($form_id);
            $receipt->setEntryId($new_record_id);
            $receipt->setInvoiceId($invoice_id);
            $receipt->save();

            header($callback_url);
            echo "<script type=\"text/javascript\">top.location.replace('{$callback_url}')</script>";
            exit;
          }
          else
          {
            $old_values = $submit_result['old_values'];
            $custom_error = @$submit_result['custom_error'];
            $error_elements = $submit_result['error_elements'];

            $form_params = array();
            $form_params['page_number'] = $input_array['page_number'];
            $form_params['populated_values'] = $old_values;
            $form_params['error_elements'] = $error_elements;
            $form_params['custom_error'] = $custom_error;

            $form_params['is_application'] = true;
            $checkout = mf_display_form($dbh, $input_array['form_id'], $form_params, sfContext::getInstance()->getUser()->getCulture());
          }
        }
        else
        {
          $checkout = mf_display_form($dbh, 11755, $form_params, sfContext::getInstance()->getUser()->getCulture());
        }

        return $checkout;
    }

    //Validate payment details received after redirect from checkout
    public function validate($invoice_id, $request_details, $payment_settings)
    {
		error_log('---------VALIDATE----------');
        $invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

        $q = Doctrine_Query::create()
           ->from("ApFormPayments a")
           ->where("a.form_id = ? AND a.record_id = ?", array($invoice->getFormEntry()->getFormId(), $invoice->getFormEntry()->getEntryId()))
           ->andWhere("a.status <> ?", 2);
        $transaction = $q->fetchOne();

        if($transaction)
        {
            //Update transaction details
            $transaction->setBillingState("cash");
            $transaction->setPaymentDate(date("Y-m-d H:i:s"));

            $transaction->setStatus(2);
            $transaction->setPaymentStatus("paid");

            $transaction->save();
        }

        //Payment is successful
        $invoice->setPaid(15);
        $invoice->setUpdatedAt(date("Y-m-d"));

        //Update invoice and allow any triggers to take place
        $invoice->save();
		//OTB Start - Combine cash and electronic payments
		$application = $invoice->getFormEntry();
        $q = Doctrine_Query::create()
           ->from("SubMenus a")
           ->where("a.id = ?", $application->getApproved());
        $current_stage = $q->fetchOne();		
		if($current_stage and $current_stage->getStagePaymentConfirmation()){
			$application->setApproved($current_stage->getStagePaymentConfirmation());
			$application->save();
		}
		//OTB End - Combine cash and electronic payments
        error_log("Cash - Debug-x: Successful Validation - ".$invoice->getFormEntry()->getApplicationId());

        return true;
    }

    //Process payment notifications received from external payment server
    public function ipn($request_details)
    {

        error_log("Cash - No IPN");
        return false;
    }
}
