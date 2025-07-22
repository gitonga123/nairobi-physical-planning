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
	//require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');
	//require($prefix_folder.'lib/swift-mailer/swift_required.php');
	require($prefix_folder.'lib/HttpClient.class.php');
	require($prefix_folder.'hooks/custom_hooks.php');

	//get data from database
	$dbh 		= mf_connect_db();
	$ssl_suffix = mf_get_ssl_suffix();

	$form_id    = (int) trim($_REQUEST['id']);

	if(!empty($_POST['review_submit']) || !empty($_POST['review_submit_x'])){ //if form submitted

		//commit data from review table to actual table
		//however, we need to check if this form has payment enabled or not

		//if the form doesn't have any payment enabled, continue with commit and redirect to success page
		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','payment_delay_notifications','payment_merchant_type'));

		if($form_properties['payment_enable_merchant'] != 1){
			error_log("Debug::: -------------------->>>>>> Payments enabled !") ;
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
		}else{
			error_log("Debug::: -------------------->>>>>> No Payments enabled !") ;
			//if the form has payment enabled, continue commit and redirect to payment page
			$record_id 	    = $_SESSION['review_id'];
			$commit_options = array();

			//delay notifications only available on some merchants
			if(!empty($form_properties['payment_delay_notifications']) && in_array($form_properties['payment_merchant_type'], array('stripe','paypal_standard','authorizenet','paypal_rest','braintree','pesaflow_standard','pesaflow_cart'))){
				$commit_options['send_notification'] = false;
			}

			$commit_result = mf_commit_form_review($dbh,$form_id,$record_id,$commit_options);

			unset($_SESSION['review_id']);

			if(in_array($form_properties['payment_merchant_type'], array('jambo_pay','malipo','stripe','authorizenet','paypal_rest','braintree','pesaflow_standard','pesaflow_cart'))){
				if(mf_is_payment_has_value($dbh,$form_id,$commit_result['record_insert_id'])){
					//allow access to payment page
					$_SESSION['mf_form_payment_access'][$form_id] = true;
					$_SESSION['mf_payment_record_id'][$form_id] = $commit_result['record_insert_id'];

					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/payment?id={$form_id}");
					exit;
				}else{
					//if the amount is zero, display success page instead
					if(empty($commit_result['form_redirect'])){
						header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
						exit;
					}else{
						echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
						exit;
					}
				}
			}else if($form_properties['payment_merchant_type'] == 'paypal_standard'){
				if(empty($commit_result['form_redirect'])){
					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
					exit;
				}else{
					echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
					exit;
				}
			}else if($form_properties['payment_merchant_type'] == 'check'){
				//redirect to either success page or custom redirect URL
				if(empty($commit_result['form_redirect'])){
					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
					exit;
				}else{
					echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
					exit;
				}
			}

		}

	}elseif (!empty($_POST['review_back']) || !empty($_POST['review_back_x'])){
		//go back to form
		$origin_page_num = (int) $_POST['mf_page_from'];
		header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/view.php?id={$form_id}&mf_page={$origin_page_num}");
		exit;
	}else{

		if(empty($form_id)){
			die('ID required.');
		}

		if(!empty($_GET['done']) && !empty($_SESSION['mf_form_completed'][$form_id])){
			$markup = mf_display_success($dbh,$form_id);
		}else{
			if(empty($_SESSION['review_id'])){
				die("Your session has been expired. Please <a href='view.php?id={$form_id}'>click here</a> to start again.");
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

	$current_nav_tab = 'manage_forms';
	require($prefix_folder.'includes/header.php');
?>
<div id="content">
			<div class="panel panel-default">
				<div class="panel-heading">
						<div id="me_form_title" <?php if(!empty($total_incomplete_entries)){ echo 'style="max-width: 80%"'; } ?>>
							<h2><?php echo "<a class=\"breadcrumb\" href='/plan/forms/index'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Entries</h2>
							<br>
							<p>Edit and manage your form entries</p>
						</div>

						<?php if(!empty($total_incomplete_entries)){ ?>
							<div id="me_incomplete_entries_info">
								<a style="color: #fff" href="manage_incomplete_entries.php?id=<?php echo $form_id; ?>"><?php echo $total_incomplete_entries; ?> incomplete entries</a>
							</div>
						<?php } ?>


				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
                <?php
                    header("Content-Type: text/html; charset=UTF-8");
                    echo $markup;
                ?>
                </div>
			</div>
		</div>
	</div>
</div>
