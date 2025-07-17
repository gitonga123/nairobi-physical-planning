<?php
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	
	require($prefix_folder.'includes/language.php');
	require($prefix_folder.'includes/common-validator.php');
	require($prefix_folder.'includes/view-functions.php');
	require($prefix_folder.'includes/theme-functions.php');
	require($prefix_folder.'includes/post-functions.php');
	require($prefix_folder.'includes/entry-functions.php');
	#require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
	//require($prefix_folder.'lib/swift-mailer/swift_required.php');
	require($prefix_folder.'lib/HttpClient.class.php');
	require($prefix_folder.'hooks/custom_hooks.php');
	
	$dbh 		  = mf_connect_db();
	$form_id 	  = (int) trim($_REQUEST['id']);

	if($form_id == null)
	{
		$form_id = $sf_user->getAttribute("form_id");
		$_SESSION['mf_payment_record_id'][$form_id] = $sf_user->getAttribute("entry_id");
		$_SESSION['mf_form_payment_access'][$form_id]  = true;
		$_SESSION['mf_form_completed'][$form_id] = true;
	}

	$paid_form_id = (int) trim($_POST['form_id_redirect']);

	$_SESSION['profile_id'] = false;

	if(!empty($paid_form_id) && $_SESSION['mf_payment_completed'][$paid_form_id] === true){
		//when payment succeeded, $paid_form_id should contain the form id number
		$form_properties = mf_get_form_properties($dbh,$paid_form_id,array('form_redirect_enable','form_redirect','form_review','form_page_total','payment_delay_notifications','logic_success_enable'));
		
		//process any delayed notifications
		mf_process_delayed_notifications($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id]);
		
		//redirect to success page, which might be coming from the logic, the default success page or the custom redirect URL being set on form properties
		if(!empty($form_properties['logic_success_enable']) && (($logic_redirect_url = mf_get_logic_success_redirect_url($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id])) != '')){
			echo "<script type=\"text/javascript\">top.location.replace('{$logic_redirect_url}')</script>";
			exit;
		}else if(!empty($form_properties['form_redirect_enable']) && !empty($form_properties['form_redirect'])){
			
			//parse redirect URL for any template variables first
			$form_properties['form_redirect'] = mf_parse_template_variables($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id],$form_properties['form_redirect']);

			echo "<script type=\"text/javascript\">top.location.replace('{$form_properties['form_redirect']}')</script>";
			exit;
		}else{
			$ssl_suffix = mf_get_ssl_suffix();
			
			header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/view?id={$paid_form_id}&done=1");
			exit;
		}
	}else{
		//display payment form
		if(empty($form_id)){
			die('ID required.');
		}else{
			$form_params = array();
			$record_id = $_SESSION['mf_payment_record_id'][$form_id];
			error_log('--------record_id--------'.$record_id);
			if($_GET['invoice'])
			{
				$q = Doctrine_Query::create()
					->from("MfInvoice a")
					->where("a.id = ?", $_GET['invoice']);
				$invoice = $q->fetchOne();

				$application = $invoice->getFormEntry();
				
				$form_id = $application->getFormId();
				$record_id = $application->getEntryId();
			}
			error_log('--------record_id--invoice------'.$record_id);
			//if payment token exist, the user is resuming payment from previously unpaid entry
			if(!empty($_GET['pay_token'])){
				$form_params['pay_token'] = $_GET['pay_token'];
			}

			if($_GET["app_id"])
			{
				$_SESSION['mf_payment_record_id'][$form_id] = $record_id;
				$_SESSION['mf_form_payment_access'][$form_id]  = true;
				$_SESSION['mf_form_completed'][$form_id] = true;
			}

			//Check if application is created. If not then create one. This should be a draft since payment is required
			//We will use the application manager to create new applications or drafts from form submissions
			$application_manager = new ApplicationManager();

			//Check if an application already exists for the form submission to prevent double entry
			if($application_manager->application_exists($form_id, $record_id)) {
				//If save as draft/resume later was clicked then do nothing
				$submission = $application_manager->get_application($form_id, $record_id);
			}
			else {
				//If save as draft/resume later was clicked then create draft application
				$submission = $application_manager->create_application($form_id, $record_id, $sf_user->getGuardUser()->getId(), true);
				//Register plan
				$api=new ApiCalls();
				$api->registerPlan($form_id,$submission);
				
			}

			$application_manager->update_invoices($submission->getId());
			$sf_user->setAttribute('application_id', $submission->getId());

			$markup = null;
			error_log('-----SUBMISSION----'.$submission->getId());
			if($_SESSION['invoice'])
			{
				$form_params['invoice'] = $_SESSION['invoice'];
				$_SESSION['mf_invoice'] = $_SESSION['invoice'];
				$markup    = mf_display_form_payment($dbh,$form_id,$record_id,$form_params);
			}
			elseif($_GET['invoice'])
			{
				$form_params['invoice'] = $_GET['invoice'];
				$_SESSION['mf_invoice'] = $_GET['invoice'];
				$markup    = mf_display_form_payment($dbh,$form_id,$record_id,$form_params);
			}
			else 
			{
				//OTB get recent invoice
				$q=Doctrine_Query::create()
					->from('MfInvoice i')
					->where('i.app_id =? and i.paid =?',array($submission->getId(),1))
					->orderBy('i.id desc');
				$unpaid_invoice=$q->fetchOne();
				if($unpaid_invoice){
					$form_params['invoice'] = $unpaid_invoice->getId();
					$_SESSION['mf_invoice'] = $unpaid_invoice->getId();
				}
				$markup    = mf_display_form_payment($dbh,$form_id,$record_id,$form_params);
			}
				
			header("Content-Type: text/html; charset=UTF-8");
			?>
			<div class="panel panel-default p-b-0">

				<?php
				echo $markup;
				?>

			</div>
			<?php
		}
	}
?>