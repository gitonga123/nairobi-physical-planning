<?php
/**
 * adddetailsSuccess.php template.
 *
 * Displays list of all of the currently logged in client's shared applications
 *
 * @package    frontend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>
<?php
 
	header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");
	$prefix_folder = dirname(__FILE__)."/../../../../..";
	require($prefix_folder.'/lib/vendor/cp_form/config.php');
	$prefix_folder = dirname(__FILE__)."/../../../../..";
	require($prefix_folder.'/lib/vendor/cp_form/includes/language.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/db-core.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/common-validator.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/view-functions-backend.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/post-functions.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/filter-functions.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/entry-functions.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/helper-functions.php');
	require($prefix_folder.'/lib/vendor/cp_form/hooks/custom_hooks.php');
	require($prefix_folder.'/lib/vendor/cp_form/lib/class.phpmailer.php');
	require($prefix_folder.'/lib/vendor/cp_form/lib/recaptchalib.php');
	require($prefix_folder.'/lib/vendor/cp_form/lib/php-captcha/php-captcha.inc.php');
		
	//get data from database
	connect_db();
	
	if(!empty($_POST['submitbuttonname'])){ //if form submitted
		$input_array   = ap_sanitize_input($_POST);
		
		$input_array['user_id'] = $sf_user->getAttribute('new_user_id','');
		
		$input_array['form_id'] = 15;
		
		$submit_result = process_form($input_array);
		
		if(!isset($input_array['password'])){ //if normal form submitted
			if($submit_result['status'] === true){
				if(empty($submit_result['review_id'])){
					if(empty($submit_result['form_redirect'])){
						$ssl_suffix = get_ssl_suffix();						
						
						header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST']."/backend.php/frusers/index/done/1");
						exit;
					}else{
						echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
						exit;
					}
				}else{ //redirect to review page
					$ssl_suffix = get_ssl_suffix();	
					
					$_SESSION['review_id'] = $submit_result['review_id'];
					header("Location: /backend.php/index/confirm?id={$input_array['form_id']}");
					exit;
				}
			}else{
				$old_values = $submit_result['old_values'];
				$custom_error = @$submit_result['custom_error'];
				$error_elements = $submit_result['error_elements'];
							
				$markup = display_form_backend($input_array['form_id'],$old_values,$error_elements,$custom_error);
			}
		}else{ //if password form submitted
			if($submit_result['status'] === true){ //on success, display the form
				$markup = display_form_backend($input_array['form_id']);
			}else{
				$custom_error = $submit_result['custom_error']; //error, display the pasword form again
				$markup = display_form_backend($input_array['form_id'],null,null,$custom_error);
			}
		}
	}else{
		$form_id = (int) trim($formid);
		if(empty($form_id)){
			die('ID required.');
		}
		
		//check for delete file option
		//this is available for form with review enabled
		if(!empty($_GET['delete_file']) && !empty($_SESSION['review_id'])){
			$element_id = (int) trim($_GET['delete_file']);
			delete_review_file_entry($form_id,$_SESSION['review_id'],$element_id);
		}
		
		if(!empty($_GET['done'])){
			$markup = display_success($form_id);
		}else{
			$markup = display_form_backend($form_id);
		}
	}
	
	header("Content-Type: text/html; charset=UTF-8");
?>

<div style="float: left;">
<ul class="breadcrumb">
					<li><a href="#">Manage Security</a></li>
					<li><a href="/backend.php/frusers/index">Members</a></li>
					<li><a href="#">Additional Details</a></li>
</ul>
</div>

<div class="g12">

<?php
	echo $markup;
	
	define('CAPTCHA_SESSION_ID', 'php_captcha');
	
?>

</div>
