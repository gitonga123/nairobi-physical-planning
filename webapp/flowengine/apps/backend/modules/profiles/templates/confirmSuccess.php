<?php
/**
 * confirmSuccess.php template.
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
      <li><a href="/plan"><?php echo __('Home'); ?></a></li>
      <li><a href="/plan/frusers/index"><?php echo __('Users'); ?></a></li>
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
                    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";	
                    require_once($prefix_folder.'includes/init.php');

                    require_once($prefix_folder.'../../../config/form_builder_config.php');
                    require_once($prefix_folder.'includes/db-core.php');
                    require_once($prefix_folder.'includes/helper-functions.php');

                    require_once($prefix_folder.'includes/language.php');
                    require_once($prefix_folder.'includes/common-validator.php');
                    require_once($prefix_folder.'includes/view-functions.php');
                    require_once($prefix_folder.'includes/theme-functions.php');
                    require_once($prefix_folder.'includes/post-functions.php');
                    require_once($prefix_folder.'includes/entry-functions.php');
                    //require_once($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
                    //require($prefix_folder.'lib/swift-mailer/swift_required.php');
                    require_once($prefix_folder.'lib/HttpClient.class.php');
                    require_once($prefix_folder.'hooks/custom_hooks.php');
                        
                    //get data from database
                    $dbh 		= mf_connect_db();
                    $ssl_suffix = mf_get_ssl_suffix();

                    $form_id    = (int) trim($_REQUEST['id']);

                    if(!empty($_POST['review_submit']) || !empty($_POST['review_submit_x'])){ //if form submitted
                        
                        //commit data from review table to actual table
                        //however, we need to check if this form has payment enabled or not

                        //if the form doesn't have any payment enabled, continue with commit and redirect to success page
                        $form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','payment_delay_notifications','payment_merchant_type'));
                        
                        $record_id 	   = $_SESSION['review_id'];
                        $commit_result = mf_commit_form_review($dbh,$form_id,$record_id);
                        
                        unset($_SESSION['review_id']);
                        
                        if(empty($commit_result['form_redirect'])){
                            header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
                            exit;
                        }else{
                            echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
                            exit;
                        }
                        
                    }elseif (!empty($_POST['review_back']) || !empty($_POST['review_back_x'])){ 
                        //go back to form
                        $origin_page_num = (int) $_POST['mf_page_from'];
                        header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/view?id={$form_id}&mf_page={$origin_page_num}");
                        exit;
                    }else{
                                
                        if(empty($form_id)){
                            die('ID required.');
                        }
                        
                        if(!empty($_GET['done']) && !empty($_SESSION['mf_form_completed'][$form_id])){
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
                            
                            header("Location: /plan/profiles/view/id/".$business->getId());
                            exit;
                        }else{
                            if(empty($_SESSION['review_id'])){
                                die("Your session has been expired. Please <a href='view?id={$form_id}'>click here</a> to start again.");
                            }else{
                                $record_id = $_SESSION['review_id'];
                            }
                            
                            $from_page_num = (int) $_GET['mf_page_from'];
                            if(empty($from_page_num)){
                                $form_page_num = 1;
                            }
                            
                            $markup = mf_display_form_review($dbh,$form_id,$record_id,$from_page_num);
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
                    <div class="panel panel-default p-b-0">
                    <?php
                        header("Content-Type: text/html; charset=UTF-8");
                        echo $markup;
                    ?>
                    </div>

            </div>

        </div>

    </div>
</div>