<?php
/**
 * inspectSuccess.php template.
 *
 * Displays inspection sheets
 *
 * @package    backend
 * @subpackage profiles
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Users'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
      <li><a href="/backend.php/frusers/index"><?php echo __('Users'); ?></a></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
    <div class="row">

		<div class="panel panel-default">

            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $business->getTitle(); ?></h3>
            </div>

            <div class="panel-body" style="margin: 0px; padding: 0px;">

                <?php 
                if($inspection_count >= 1 || $filter != null)
                {
                    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
                    require_once($prefix_folder.'includes/init.php');

                    header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");

                    require_once($prefix_folder.'../../../config/form_builder_config.php');
                    require_once($prefix_folder.'includes/language.php');
                    require_once($prefix_folder.'includes/db-core.php');
                    require_once($prefix_folder.'includes/common-validator.php');
                    require_once($prefix_folder.'includes/view-functions.php');
                    require_once($prefix_folder.'includes/post-functions.php');
                    require_once($prefix_folder.'includes/filter-functions.php');
                    require_once($prefix_folder.'includes/entry-functions.php');
                    require_once($prefix_folder.'includes/helper-functions.php');
                    require_once($prefix_folder.'includes/theme-functions.php');
                    //require_once($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
                    //require($prefix_folder.'lib/swift-mailer/swift_required.php');
                    require_once($prefix_folder.'lib/HttpClient.class.php');
                    require_once($prefix_folder.'lib/recaptchalib.php');
                    require_once($prefix_folder.'lib/recaptchalib2.php');
                    require_once($prefix_folder.'lib/php-captcha/php-captcha.inc.php');
                    require_once($prefix_folder.'lib/text-captcha.php');
                    require_once($prefix_folder.'hooks/custom_hooks.php');

                    $dbh 		= mf_connect_db();
                    $ssl_suffix = mf_get_ssl_suffix();

                    if(mf_is_form_submitted()){ //if form submitted
                        $input_array   = mf_sanitize($_POST);
                        $submit_result = mf_process_form($dbh,$input_array);

                        if(!isset($input_array['password'])){ //if normal form submitted
                            
                            if($submit_result['status'] === true){
                                if(!empty($submit_result['form_resume_url'])){ //the user saving a form, display success page with the resume URL
                                    $_SESSION['mf_form_resume_url'][$input_array['form_id']] = $submit_result['form_resume_url'];
                                    
                                    header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&done=1");
                                    exit;
                                }else if($submit_result['logic_page_enable'] === true){ //the page has skip logic enable and a custom destination page has been set
                                    $target_page_id = $submit_result['target_page_id'];

                                    if(is_numeric($target_page_id)){
                                        header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&mf_page={$target_page_id}");
                                        exit;
                                    }else if($target_page_id == 'review'){
                                        if(!empty($submit_result['origin_page_number'])){
                                            $page_num_params = '&mf_page_from='.$submit_result['origin_page_number'];
                                        }

                                        $_SESSION['review_id'] = $submit_result['review_id'];
                                        header("Location: /backend.php/profiles/confirm?id={$input_array['form_id']}{$page_num_params}");
                                        exit;
                                    }else if($target_page_id == 'success'){
                                        //redirect to success page
                                        if(!empty($submit_result['logic_success_enable']) && (($logic_redirect_url = mf_get_logic_success_redirect_url($dbh,$input_array['form_id'],$submit_result['entry_id'])) != '') ){
                                            echo "<script type=\"text/javascript\">top.location.replace('{$logic_redirect_url}')</script>";
                                            exit;
                                        }else if(empty($submit_result['form_redirect'])){		
                                            header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&done=1");
                                            exit;
                                        }else{
                                            echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
                                            exit;
                                        }
                                    }

                                }else if(!empty($submit_result['review_id'])){ //redirect to review page
                                    
                                    if(!empty($submit_result['origin_page_number'])){
                                        $page_num_params = '&mf_page_from='.$submit_result['origin_page_number'];
                                    }
                                    
                                    $_SESSION['review_id'] = $submit_result['review_id'];
                                    header("Location: /backend.php/profiles/confirm?id={$input_array['form_id']}{$page_num_params}");
                                    exit;
                                }else{
                                    if(!empty($submit_result['next_page_number'])){ //redirect to the next page number
                                        $_SESSION['mf_form_access'][$input_array['form_id']][$submit_result['next_page_number']] = true;
                                                                    
                                        header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&mf_page={$submit_result['next_page_number']}");
                                        exit;
                                    }else{ //otherwise display success message or redirect to the custom redirect URL or payment page
                                        
                                        if(mf_is_payment_has_value($dbh,$input_array['form_id'],$submit_result['entry_id'])){
                                            //redirect to credit card payment page, if the merchant is being enabled and the amount is not zero

                                            //allow access to payment page
                                            $_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
                                            $_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['entry_id'];

                                            header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/payment?id={$input_array['form_id']}");
                                            exit;
                                        }else{
                                            //redirect to success page
                                            if(empty($submit_result['form_redirect'])){		
                                                header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&done=1");
                                                exit;
                                            }else{
                                                echo "<script type=\"text/javascript\">top.location.replace('{$submit_result['form_redirect']}')</script>";
                                                exit;
                                            }
                                        }
                                    }
                                }
                            }else if($submit_result['status'] === false){ //there are errors, display the form again with the errors
                                $old_values 	= $submit_result['old_values'];
                                $custom_error 	= @$submit_result['custom_error'];
                                $error_elements = $submit_result['error_elements'];
                                
                                $form_params = array();
                                $form_params['page_number'] = $input_array['page_number'];
                                $form_params['populated_values'] = $old_values;
                                $form_params['error_elements'] = $error_elements;
                                $form_params['custom_error'] = $custom_error;
                                
                                $markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
                            }
                        }else{ //if password form submitted
                            
                            if($submit_result['status'] === true){ //on success, display the form
                                $markup = mf_display_form($dbh,$input_array['form_id']);
                            }else{
                                $custom_error = $submit_result['custom_error']; //error, display the pasword form again
                                
                                $form_params = array();
                                $form_params['custom_error'] = $custom_error;
                                $markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
                            }
                        }
                    }else{
                        $form_id 		= (int) trim($inspection_sheet->getFormId());
                        $page_number	= (int) trim($_GET['mf_page']);
                        
                        $page_number 	= mf_verify_page_access($form_id,$page_number);
                        
                        $resume_key		= trim($_GET['mf_resume']);
                        if(!empty($resume_key)){
                            $_SESSION['mf_form_resume_key'][$form_id] = $resume_key;
                        }
                        
                        if(!empty($_GET['done']) && (!empty($_SESSION['mf_form_completed'][$form_id]) || !empty($_SESSION['mf_form_resume_url'][$form_id]))){
                            //Check if application is created. If not then create one.
                            $record_id = $_SESSION['mf_success_entry_id'];

                            $user = Functions::current_user();

                            $userprofileinspection = new MfUserProfileInspection();
                            $userprofileinspection->setProfileId($business->getId());
                            $userprofileinspection->setReviewerId($user->getNid());
                            $userprofileinspection->setFormId($form_id);
                            $userprofileinspection->setEntryId($record_id);
                            $userprofileinspection->setCreatedAt(date("Y-m-d"));
                            $userprofileinspection->setUpdatedAt(date("Y-m-d"));
                            $userprofileinspection->setDeleted(0);
                            $userprofileinspection->save();
                                
                            header("Location: /backend.php/profiles/view/id/".$business->getId());
                            exit;
                        }else{
                            $form_params = array();
                            $form_params['page_number'] = $page_number;
                            $markup = mf_display_form($dbh,$form_id,$form_params);
                        }
                    }

                    $query = "select 
                                    A.form_name,
                                    ifnull(B.entries_sort_by,'id-desc') entries_sort_by,
                                    ifnull(B.entries_filter_type,'all') entries_filter_type,
                                    ifnull(B.entries_enable_filter,0) entries_enable_filter			  
                                from 
                                    ".MF_TABLE_PREFIX."forms A left join ".MF_TABLE_PREFIX."entries_preferences B 
                                    on 
                                    A.form_id=B.form_id and B.user_id=? 
                                where 
                                    A.form_id = ?";
                    $params = array($_SESSION['mf_user_id'],$form_id);

                    $sth = mf_do_query($query,$params,$dbh);
                    $row = mf_do_fetch_result($sth);

                    if(!empty($row)){
                        
                        $row['form_name'] = mf_trim_max_length($row['form_name'],65);

                        if(!empty($row['form_name'])){		
                            $form_name = htmlspecialchars($row['form_name']);
                        }else{
                            $form_name = 'Untitled Form (#'.$form_id.')';
                        }	
                    }

                    ?>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php
                                header("Content-Type: text/html; charset=UTF-8");
                                echo $markup;
                            ?>
                        </div>
                    </div>
                    <?php
                }
                else 
                {
                    //Display list of inspection sheets for reviewer to choose
                }
                ?>

            </div>

        </div>

    </div>
</div>