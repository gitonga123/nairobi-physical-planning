<?php
/**
 * paymentSuccess.php template.
 *
 * Displays payment checkout
 *
 * @package    frontend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

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
$form_id 	  = null;
$paid_form_id = null;
$profile = null;

if($_SESSION['profile_id'])
{
$q = Doctrine_Query::create()
	->from('MfUserProfile a')
	->where('a.id = ?', $_SESSION['profile_id']);
$profile = $q->fetchOne();

$form_id 	  = $profile->getFormId();
$paid_form_id = $profile->getFormId();
$_SESSION['mf_payment_record_id'][$form_id] = $profile->getEntryId();
}
else 
{
$form_id 	  = (int) trim($_REQUEST['id']);
$paid_form_id = (int) trim($_POST['form_id_redirect']);
}

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

		$user = $sf_user->getGuardUser();

		$userprofile = null;

		$q = Doctrine_Query::create()
		   ->from("MfUserProfile a")
		   ->where("a.form_id = ? AND a.entry_id = ?", array($form_id, $record_id));
		if($q->count() == 0)
		{
			$userprofile = new MfUserProfile();
			$userprofile->setUserId($user->getId());
			$userprofile->setFormId($form_id);
			$userprofile->setEntryId($record_id);
			$userprofile->setCreatedAt(date("Y-m-d"));
			$userprofile->setUpdatedAt(date("Y-m-d"));
			$userprofile->setDeleted(1);
			$userprofile->save();
		}
		else 
		{
			$userprofile = $q->fetchOne();
		}

		$sf_user->setAttribute('profile_id', $userprofile->getId());

		$markup = null;

		$_SESSION['mf_invoice'] = false;
		$_SESSION['profile_id'] = $userprofile->getId();
		$_SESSION['user_id'] = $user->getId();

		$markup    = mf_display_form_payment($dbh,$form_id,$record_id,$form_params);
			
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