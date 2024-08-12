<?php
/**
 *
 * Manages gateways
 *
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 12:26 AM
 */

class PaymentsManager {

	public $invoice_manager = null;
	public $gateway = null;

    //Constructor for PaymentsManager class
    public function __construct()
    {
        $this->invoice_manager = new InvoiceManager();
    }

    //Is a checkout authorized for the specified invoice
    public function authorize_checkout($invoice_id)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

    	if($invoice->getPaid() == "2" || $invoice->getPaid() == "3")
    	{
    		return false;
    	}
    	else
    	{
    		//If merchant is enabeld on the form
    		$q = Doctrine_Query::create()
    		   ->from("ApForms a")
    		   ->where("a.form_active = 1")
    		   //->andWhere("a.payment_enable_merchant = 1")
    		   ->andWhere("a.form_id = ?", $invoice->getFormEntry()->getFormId());
    		$form = $q->count();

    		if($form)
    		{
    			$this->acquire_gateway($invoice->getId());
    			return true;
    		}
    		else
    		{
    			return false;
    		}
    	}
    }

    //Is the redirection url authorized for the specified invoice
    public function authorize_validation($invoice_id)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

    	if($invoice->getPaid() == "2" || $invoice->getPaid() == "3")
    	{
    		return false;
    	}
    	else
    	{
    		//If merchant is enabeld on the form
    		$q = Doctrine_Query::create()
    		   ->from("ApForms a")
    		   ->where("a.form_active = 1")
    		   //->andWhere("a.payment_enable_merchant = 1")
    		   ->andWhere("a.form_id = ?", $invoice->getFormEntry()->getFormId());
    		$form = $q->count();
			error_log('----COUNT------'.$form);

    		if($form)
    		{
    			$this->acquire_gateway($invoice->getId());
    			return true;
    		}
    		else
    		{
    			return false;
    		}
    	}
    }

    //Set the gateway to be used for the specified invoice
    public function acquire_gateway($invoice_id)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

    	$q = Doctrine_Query::create()
			   ->from("ApForms a")
			   ->where("a.form_active = 1")
			   //->andWhere("a.payment_enable_merchant = 1")
			   ->andWhere("a.form_id = ?", $invoice->getFormEntry()->getFormId());
			$form = $q->fetchOne();

			if($form)
			{
				if($form->getPaymentMerchantType() == "ecitizen")
				{
					$this->gateway = new PesaflowGateway();
					return true;
				}
				elseif($form->getPaymentMerchantType() == "jambopay")
				{
					$this->gateway = new JambopayGateway();
					return true;
				}
				elseif($form->getPaymentMerchantType() == "cash"|| $form->getPaymentMerchantType() == "check")
				{
					$this->gateway = new CashGateway();
					return true;
				}
				else
			  {
					return false;
				}
			}
			else
			{
				return false;
			}
    }

		//Set the gateway to be used for the specified invoice
    public function acquire_gateway_by_name($gateway)
    {
				if($gateway == "ecitizen")
				{
					$this->gateway = new PesaflowGateway();
					return true;
				}
				elseif($gateway == "jambopay")
				{
					$this->gateway = new JambopayGateway();
					return true;
				}
				elseif($gateway == "cash")
				{
					$this->gateway = new CashGateway();
					return true;
				}
				elseif($gateway == 'zizi'){
					$this->gateway = new ZiziGateway();
					return true;
				}
				else
			  {
					return false;
				}
    }

	//Display the checkout for the specified invoice
	public function display_partial_checkout($invoice_id, $backend = false)
	{
		$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

		if($this->authorize_checkout($invoice_id))
		{
			$payment_settings = $this->get_payment_settings($invoice_id);
			return $this->gateway->checkout($invoice_id, $payment_settings, $backend);
		}
		else
		{
			return "Unauthorized Checkout";
		}
	}

    //Display the checkout for the specified invoice
    public function display_checkout($invoice_id, $backend = false)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

    	if($this->authorize_checkout($invoice_id))
    	{
    		if($this->validate_invoice($invoice_id))
    		{
	    		$payment_settings = $this->get_payment_settings($invoice_id);
	    		return $this->gateway->checkout($invoice_id, $payment_settings, $backend);
	    	}
	    	else
	    	{
                $q = Doctrine_Query::create()
                   ->from("MfInvoice a")
                   ->where("a.app_id = ?", $invoice->getFormEntry()->getId());
                $inv_count = $q->count();

                if($inv_count == 1)
                {
					//Regenerate invoice total
					$new_invoice_id = $this->update_invoice_total($invoice_id, $this->get_form_total($invoice_id, $invoice->getFormEntry()->getFormId(), $invoice->getFormEntry()->getEntryId()));

					$invoice_id = $new_invoice_id;
					sfContext::getInstance()->getUser()->setAttribute('invoice_id', $invoice_id);

					$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);
                }

	    		$payment_settings = $this->get_payment_settings($invoice_id);
	    		return $this->gateway->checkout($invoice_id, $payment_settings, $backend);
	    	}
    	}
    	else
    	{
    		return "Unauthorized Checkout";
    	}
    }

    //Get payment settings
    public function get_payment_settings($invoice_id)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);
    	$form_id = $invoice->getFormEntry()->getFormId();

    	$prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
		require_once($prefix_folder.'includes/init.php');

		require_once($prefix_folder.'../../../config/form_builder_config.php');
		require_once($prefix_folder.'includes/db-core.php');
		require_once($prefix_folder.'includes/helper-functions.php');

		$dbh = mf_connect_db();
		$mf_settings = mf_get_settings($dbh);

		$sql = "SELECT * FROM ap_forms WHERE form_id = ?";
		$params = array($form_id);
		$sth = mf_do_query($sql,$params,$dbh);

		return mf_do_fetch_result($sth);
    }

    //Validate that the amount on the invoice is the actual amount that needs to be paid (e.g. incase draft changes affect pricing)
    public function validate_invoice($invoice_id)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

    	$form = $invoice->getFormEntry()->getForm();

    	if($form && $form->getPaymentEnableInvoice() == "0")
    	{
    		$form_id = $invoice->getFormEntry()->getFormId();
    		$record_id = $invoice->getFormEntry()->getEntryId();


    		$total_payment_amount = $this->get_form_total($invoice_id, $form_id, $record_id);


			if($invoice->getTotalAmount() != $total_payment_amount)
			{
				//If invoice total does not match form total then destroy invoice totals and recreate a new total
				return false;
			}
			else
			{
				return true;
			}
    	}
    	else
    	{
    		return true;
    	}
    }

    //Validate that the amount on the invoice is the actual amount that needs to be paid (e.g. incase draft changes affect pricing)
    public function validate_all_invoices($application_id)
    {
        $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->where("a.id = ?", $application_id);

        $application = $q->fetchOne();

        $form = $application->getForm();

        if($form && $form->getPaymentEnableInvoice() == "0")
        {
            $form_id = $application->getFormId();
            $record_id = $application->getEntryId();

            $total_payment_amount = 0;

            $invoices_total = 0;

            foreach($application->getMfInvoice() as $invoice)
            {
								if($invoice->getPaid() == 2 || $invoice->getPaid() == 1)
								{
	                $invoices_total = $invoices_total + $invoice->getTotalAmount();
	                $total_payment_amount = $this->get_form_total($invoice->getId(), $form_id, $record_id);
								}
            }

            if($invoices_total < $total_payment_amount)
            {
                //If invoice total does not match form total then destroy invoice totals and recreate a new total
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }

    //Get total from form settings if payment on submission is enabled
    public function generate_invoice_from_difference($application_id)
    {
        $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->where("a.id = ?", $application_id);

        $application = $q->fetchOne();

        $form = $application->getForm();
			//If invoice belongs to a renewal service we need to get total from all the service fees configured
			if($application->getBusinessId())
			{
				error_log('------Business id found------');
				$total_payment_amount = $this->invoice_manager->get_total_payment_amount($application_id);
				error_log('------------Total amount-------'.$total_payment_amount);
				$invoices_total = 0;

				foreach($application->getMfInvoice() as $invoice)
				{
					//OTB ADD CHECK FOR PAID INVOICE FOR THE CURRENT YEAR
					if($invoice->getPaid() == 2 && date('Y',strtotime($invoice->getCreatedAt())) == date('Y'))
					{
						//OTB ADD - CHECK IF PAYMENTS AREN"T ARRERRS AND THE REST
						$extra_fees=0;
						foreach($invoice->getMfInvoiceDetail() as $inv_details){
							if(is_numeric($inv_details->getDescription())){
								//check the desc on fee 
								$q=Doctrine_Query::create()
									->from('Fee f')
									->where('f.id = ?',$inv_details->getDescription());
								$fee=$q->fetchOne();
								if($fee){
									switch($fee->getDescription()){
										case 'Penalties':
											$extra_fees+=$inv_details->getAmount();
											break;
										/*case 'Arrears':
											$extra_fees+=$inv_details->getAmount();
											break;*/
										case 'Fines':
											$extra_fees+=$inv_details->getAmount();
											break;
									}
								}
							}
							if($inv_details->getDescription() === '111111 - Convenience Fee'){
								$extra_fees+=$inv_details->getAmount();
							}
						}
						$invoices_total = $invoices_total + ($invoice->getTotalAmount()-$extra_fees);
					}
				}
				error_log('-----------Total paid invoices-----'.$invoices_total);
				//If total number of paid invoices is zero, then delete all invoices and generate new invoice batch
				if($invoices_total == 0)
				{
					foreach($application->getMfInvoice() as $invoice)
					{
						//OTB ADD ONLY CANCEL INVOICES FOR THE CURRENT YEAR
						if($invoice->getPaid() <> 2 && $invoice->getPaid() <> 3 && date('Y',strtotime($invoice->getCreatedAt())) == date('Y'))
						{
							//OTB ADD
							//$invoice->delete();
							$invoice->setPaid(3);
							$invoice->save();
						}
					}
					error_log('-------Zero invoices ---Cyclic billing run----');
					$this->invoice_manager->generate_cyclic_invoices($application->getId(), $application->getServiceId());
				}
				else 
				{
					if(($invoices_total > 0 && $invoices_total < $total_payment_amount))
					{
						//If invoice total does not match form total then destroy invoice totals and recreate a new total
						$difference=0;
						if($invoices_total > 0){
							$difference = $total_payment_amount - $invoices_total;
						}
						if($difference > 0){
							error_log('-----------Difference found-----'.$difference.'-----Invoice difference run----');
							$invoice_id = $this->invoice_manager->create_invoice_from_different($application_id, $difference);

							return $invoice_id;
						}
					}
				}
			}
			else
			{
				error_log('------------NO business id -------');
				//OTB ADD CHECK IF MENUS IS SET FOR THE OLD Form
				$q=Doctrine_Query::create()
					->from('Menus m')
					->where('m.service_form = ? and m.service_type = ?',array($application->getFormId(),2));
				$service=$q->fetchOne();
				if($service && $service->getServiceFeeField()){
					error_log('------Service found -----'.$service->getId());
					$total_payment_amount = $this->invoice_manager->get_total_payment_amount($application_id);
					error_log('-----EXecuted ---Amount to be paid---'.$total_payment_amount);
					$invoices_total = 0;

					foreach($application->getMfInvoice() as $invoice)
					{
						//OTB ADD CHECK FOR PAID INVOICE FOR THE CURRENT YEAR
						if($invoice->getPaid() == 2 && date('Y',strtotime($invoice->getCreatedAt())) == date('Y'))
						{
							//OTB ADD - CHECK IF PAYMENTS AREN"T ARRERRS AND THE REST
							$extra_fees=0;
							foreach($invoice->getMfInvoiceDetail() as $inv_details){
								if(is_numeric($inv_details->getDescription())){
									//check the desc on fee 
									$q=Doctrine_Query::create()
										->from('Fee f')
										->where('f.id = ?',$inv_details->getDescription());
									$fee=$q->fetchOne();
									if($fee){
										switch($fee->getDescription()){
											case 'Penalties':
												$extra_fees+=$inv_details->getAmount();
												break;
											/*case 'Arrears':
												$extra_fees+=$inv_details->getAmount();
												break;*/
											case 'Fines':
												$extra_fees+=$inv_details->getAmount();
												break;
										}
									}
								}
								if($inv_details->getDescription() === '111111 - Convenience Fee'){
									$extra_fees+=$inv_details->getAmount();
								}
							}							
							$invoices_total = $invoices_total + ($invoice->getTotalAmount()-$extra_fees);
						}
					}
					error_log('--------Invoice paid total-----'.$invoices_total);
					//If total number of paid invoices is zero, then delete all invoices and generate new invoice batch
					if($invoices_total == 0)
					{
						foreach($application->getMfInvoice() as $invoice)
						{
							//OTB ADD ONLY CANCEL INVOICES FOR THE CURRENT YEAR
							if($invoice->getPaid() <> 2 && $invoice->getPaid() <> 3 && date('Y',strtotime($invoice->getCreatedAt())) == date('Y'))
							{
								//OTB ADD
								//$invoice->delete();
								$invoice->setPaid(3);
								$invoice->save();
							}
						}
						error_log('--------Zero invoices ---- cyclic billing run-----');
						$this->invoice_manager->generate_cyclic_invoices($application->getId(), $service->getId());
					}
					else 
					{
						if($invoices_total > 0 && ($invoices_total < $total_payment_amount))
						{
							//If invoice total does not match form total then destroy invoice totals and recreate a new total
							$difference=0;
							if($invoices_total > 0){
								$difference = $total_payment_amount - $invoices_total;
							}
							if($difference > 0){
								error_log('-------Difference found-----'.$difference.'------Invoice from difference run-----');
								$invoice_id = $this->invoice_manager->create_invoice_from_different($application_id, $difference);

								return $invoice_id;
							}
						}
					}
				}
				elseif($form && $form->getPaymentEnableInvoice() == "0")
				{
					error_log('--------Payment enable invoice set to zero------');
					$form_id = $application->getFormId();
					$record_id = $application->getEntryId();

					$total_payment_amount = 0;
					$invoices_total = 0;

					foreach($application->getMfInvoice() as $invoice)
					{
						if($invoice->getPaid() == 2)
						{
							$invoices_total = $invoices_total + $invoice->getTotalAmount();
						}

						$total_payment_amount = $this->get_form_total($invoice->getId(), $form_id, $record_id);
					}

					if($invoices_total < $total_payment_amount)
					{
						//If invoice total does not match form total then destroy invoice totals and recreate a new total
						$difference = $total_payment_amount - $invoices_total;

						$invoice_id = $this->invoice_manager->create_invoice_from_different($application_id, $difference);

						return $invoice_id;
					}
				}
			}
    }

    //Get total from form settings if payment on submission is enabled
    public function get_form_total($invoice_id, $form_id, $record_id)
    {
    	$total_payment_amount = 0;

    	$prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
		require_once($prefix_folder.'includes/init.php');

		require_once($prefix_folder.'../../../config/form_builder_config.php');
		require_once($prefix_folder.'includes/db-core.php');
		require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $payment_settings = $this->get_payment_settings($invoice_id);

        $payment_enable_tax = $payment_settings['payment_enable_tax'];
        $payment_tax_amount = $payment_settings['payment_tax_amount'];
        $payment_tax_rate = $payment_settings['payment_tax_rate'];
        $payment_price_type = $payment_settings['payment_price_type'];
        $payment_price_amount = $payment_settings['payment_price_amount'];

    	//If payment in submission is enabled then try and calculate total from the form settings
		if($payment_price_type == 'variable'){

			$total_payment_amount = (double) mf_get_payment_total($dbh,$form_id,$record_id,0,'live');
			$payment_items = mf_get_payment_items($dbh,$form_id,$record_id,'live');

			//calculate tax/convenience fee if enabled
			if(!empty($payment_enable_tax)){
				if($payment_tax_amount)
				{
					$total_payment_amount += $payment_tax_amount;
				}
				else
				{
					$payment_tax_amount = ($payment_tax_rate / 100) * $total_payment_amount;
					$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal

					$total_payment_amount += $payment_tax_amount;
				}
			}
		}else if($payment_price_type == 'fixed'){
			$total_payment_amount = $payment_price_amount;

			//calculate tax if enabled
			if(!empty($payment_enable_tax)){
				if($payment_tax_amount)
				{
					$total_payment_amount += $payment_tax_amount;
				}
				else
				{
					$payment_tax_amount = ($payment_tax_rate / 100) * $total_payment_amount;
					$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal

					$total_payment_amount += $payment_tax_amount;
				}
			}
		}

		return $total_payment_amount;
    }

    //Regenerate invoice total
    public function update_invoice_total($invoice_id, $total_amount)
    {
    	$invoice = $this->invoice_manager->get_invoice_by_id($invoice_id);

    	$amounts = $invoice->getMfInvoiceDetail();

    	$total_description = "";

    	foreach($amounts as $amount)
    	{
    		$total_description = $amount->getDescription();
    	}

			$invoice->setPaid(3);
			$invoice->save();

			//Regenerate a new invoice and cancel the current invoice
			$new_invoice = new MfInvoice();
			$new_invoice->setAppId($invoice->getAppId());

			$new_invoice->setTemplateId($invoice->getTemplateId());
			$new_invoice->setInvoiceNumber($invoice->getInvoiceNumber());

			$new_invoice->setPaid(15);
			$new_invoice->setCreatedAt(date("Y-m-d H:i:s"));
			$new_invoice->setUpdatedAt(date("Y-m-d H:i:s"));
			$new_invoice->setExpiresAt($invoice->getExpiresAt());
    	$new_invoice->setTotalAmount($total_amount);

			$new_invoice->save();

    	$amount = new MfInvoiceDetail();
    	$amount->setDescription($total_description);
    	$amount->setAmount($total_amount);
    	$amount->setInvoiceId($new_invoice->getId());
    	$amount->save();

			return $new_invoice->getId();
    }

    //Process the redirection url from the checkout
    public function process_validation($invoice_id, $request_details)
    {
    	if($this->acquire_gateway($invoice_id))
    	{
    		$payment_settings = $this->get_payment_settings($invoice_id);
    		return $this->gateway->validate($invoice_id, $request_details, $payment_settings);
    	}
    	else
    	{
    		return false;
    	}
    }

    //Process an IPN request
    public function process_ipn($gateway, $request_details)
    {
		if($this->acquire_gateway_by_name($gateway))
    	{
    		return $this->gateway->ipn($request_details);
    	}
    	else
    	{
    		return false;
    	}
    }

	//Process an IPN request
    public function process_ipn_profile($gateway, $request_details)
    {
			if($this->acquire_gateway_by_name($gateway))
    	{
    		return $this->gateway->ipn_profile($request_details);
    	}
    	else
    	{
    		return false;
    	}
    }

    //Validate request from external API
    public function api_validate_request($api_key, $api_secret)
    {
        $q = Doctrine_Query::create()
            ->from("InvoiceApiAccount a")
            ->where("a.api_key = ? and a.api_secret =?", array($api_key,$api_secret));
        $mdas = $q->fetchOne();

        if($mdas)
        {
            error_log("Invoice API: Valid Request From ".$mdas->getMdaName()." - ".$mdas->getMdaBranch());
            return true;
        }
        else
        {
            error_log("Invoice API: Bad Request For ".$api_key." - ".$api_secret);
            return false;
        }
    }

		public function process_payment_url()
		{
			return "/index.php//forms/payment/checkout/now";
		}
}
