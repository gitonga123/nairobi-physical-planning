<?php
/**
 * adddetailsSuccess.php template.
 *
 * Allows adding of additional details to a newly created client account
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/cp_machform/";
require($prefix_folder.'includes/init.php');

require($prefix_folder.'config.php');
require($prefix_folder.'includes/db-core.php');
require($prefix_folder.'includes/helper-functions.php');
require($prefix_folder.'includes/check-session.php');

require($prefix_folder.'includes/language.php');
require($prefix_folder.'includes/common-validator.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/filter-functions.php');
require($prefix_folder.'includes/entry-functions.php');
require($prefix_folder.'includes/view-functions.php');
require($prefix_folder.'includes/users-functions.php');

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

                                        header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST']."/plan/frusers/index/done/1");
                                        exit;
                                }else{
                                        echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
                                        exit;
                                }
                        }else{ //redirect to review page
                                $ssl_suffix = get_ssl_suffix();	

                                $_SESSION['review_id'] = $submit_result['review_id'];
                                header("Location: /plan/index/confirm?id={$input_array['form_id']}");
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
}

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

header("Content-Type: text/html; charset=UTF-8");
?>


			<form>
			<label style='height: 30px; margin-top: 0px;'>
			<div style='float: left; font-size: 20px; font-weight: 700;'>Additional Client Details
			</div>
			</label>
			</form>

<?php
	echo $markup;
	
	define('CAPTCHA_SESSION_ID', 'php_captcha');
	
?>

