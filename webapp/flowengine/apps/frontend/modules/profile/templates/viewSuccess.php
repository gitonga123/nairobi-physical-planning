<?php
/**
 * indexSuccess.php template.
 *
 * Displays client dashboard
 *
 * @package    frontend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
require($prefix_folder.'includes/init.php');

require($prefix_folder.'../../../config/form_builder_config.php');
require($prefix_folder.'includes/db-core.php');
require($prefix_folder.'includes/helper-functions.php');
require($prefix_folder.'includes/check-session.php');

require($prefix_folder.'includes/language.php');
require($prefix_folder.'includes/entry-functions.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/users-functions.php');

$form_id  = $profile->getFormId();
$entry_id = $profile->getEntryId();
$nav = trim($_GET['nav']);

if(empty($form_id) || empty($entry_id)){
	die("Invalid Request");
}

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

try
{
	$business_manager = new BusinessManager();
	error_log('Cyclic-b: Checking '.$profile->getTitle().' for with cyclic billing');
	$business_manager->generate_cyclic_bills($profile->getId());
}catch(Exception $ex)
{
	error_log('Cyclic-b: permitflow: '.$ex->getMessage());
}

//determine the 'incomplete' status of current entry
$query = "select
				`status`
			from
				`".MF_TABLE_PREFIX."form_{$form_id}`
		where id=?";
$params = array($entry_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

$entry_status = $row['status'];

$is_incomplete_entry = false;
if($entry_status == 2){
	$is_incomplete_entry = true;
}

//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
if(!empty($nav)){

	$entries_options = array();
	$entries_options['is_incomplete_entry'] = $is_incomplete_entry;

	$all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id,$entries_options);
	$entry_key = array_keys($all_entry_id_array,$entry_id);
	$entry_key = $entry_key[0];

	if($nav == 'prev'){
		$entry_key--;
	}else{
		$entry_key++;
	}

	$entry_id = $all_entry_id_array[$entry_key];

	//if there is no entry_id, fetch the first/last member of the array
	if(empty($entry_id)){
		if($nav == 'prev'){
			$entry_id = array_pop($all_entry_id_array);
		}else{
			$entry_id = $all_entry_id_array[0];
		}
	}
}

//get entry information (date created/updated/ip address/resume key)
$query = "select
				date_format(date_created,'%e %b %Y - %r') date_created,
				date_created date_created_raw,
				date_format(date_updated,'%e %b %Y - %r') date_updated,
				ip_address,
				resume_key
			from
				`".MF_TABLE_PREFIX."form_{$form_id}`
		where id=?";
$params = array($entry_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

$date_created = $row['date_created'];
$date_created_raw = $row['date_created_raw'];
if(!empty($row['date_updated'])){
	$date_updated = $row['date_updated'];
}else{
	$date_updated = '&nbsp;';
}
$ip_address   = $row['ip_address'];
$entry_status = $row['status'];
$form_resume_key = $row['resume_key'];

if($is_incomplete_entry && !empty($form_resume_key)){
	$form_resume_url = $mf_settings['base_url']."view.php?id={$form_id}&mf_resume={$form_resume_key}";
}

//get form name
$query 	= "select
					form_name,
					payment_enable_merchant,
					payment_merchant_type,
					payment_price_type,
					payment_price_amount,
					payment_currency,
					payment_ask_billing,
					payment_ask_shipping,
					payment_enable_tax,
					payment_tax_rate,
					payment_enable_discount,
					payment_discount_type,
					payment_discount_amount,
					payment_discount_element_id,
					form_resume_enable
				from
					".MF_TABLE_PREFIX."forms
			where
					form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

if(!empty($row)){
	$row['form_name'] = mf_trim_max_length($row['form_name'],65);

	$form_name = htmlspecialchars($row['form_name']);
	$payment_enable_merchant = (int) $row['payment_enable_merchant'];

	$payment_price_amount = (double) $row['payment_price_amount'];
	$payment_merchant_type = $row['payment_merchant_type'];
	$payment_price_type = $row['payment_price_type'];
	$form_payment_currency = strtoupper($row['payment_currency']);
	$payment_ask_billing = (int) $row['payment_ask_billing'];
	$payment_ask_shipping = (int) $row['payment_ask_shipping'];

	$payment_enable_tax = (int) $row['payment_enable_tax'];
	$payment_tax_rate 	= (float) $row['payment_tax_rate'];

	$payment_enable_discount = (int) $row['payment_enable_discount'];
	$payment_discount_type 	 = $row['payment_discount_type'];
	$payment_discount_amount = (float) $row['payment_discount_amount'];
	$payment_discount_element_id = (int) $row['payment_discount_element_id'];
	$form_resume_enable = (int) $row['form_resume_enable'];
}else{
	die("Error. Unknown form ID.");
}

$is_discount_applicable = false;

//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
if(!empty($payment_enable_discount)){
	$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
	$params = array($entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['coupon_element'])){
		$is_discount_applicable = true;
	}
}

//if payment enabled, get the details
if(!empty($payment_enable_merchant)){
	$query = "SELECT
					`payment_id`,
						date_format(payment_date,'%e %b %Y - %r') payment_date,
					`payment_status`,
					`payment_fullname`,
					`payment_amount`,
					`payment_currency`,
					`payment_test_mode`,
					`status`,
					`billing_street`,
					`billing_city`,
					`billing_state`,
					`billing_zipcode`,
					`billing_country`,
					`same_shipping_address`,
					`shipping_street`,
					`shipping_city`,
					`shipping_state`,
					`shipping_zipcode`,
					`shipping_country`
				FROM
					".MF_TABLE_PREFIX."form_payments
				WHERE
					form_id = ? and record_id = ? and `status` = 1
			ORDER BY
					payment_date DESC
				LIMIT 1";
	$params = array($form_id,$entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$payment_id 		= $row['payment_id'];
	$payment_date 		= $row['payment_date'];
	$payment_status 	= $row['payment_status'];
	$payment_fullname 	= $row['payment_fullname'];
	$payment_amount 	= (double) $row['payment_amount'];
	$payment_currency 	= strtoupper($row['payment_currency']);
	$payment_test_mode 	= (int) $row['payment_test_mode'];
	$billing_street 	= htmlspecialchars(trim($row['billing_street']));
	$billing_city 		= htmlspecialchars(trim($row['billing_city']));
	$billing_state 		= htmlspecialchars(trim($row['billing_state']));
	$billing_zipcode 	= htmlspecialchars(trim($row['billing_zipcode']));
	$billing_country 	= htmlspecialchars(trim($row['billing_country']));

	$same_shipping_address = (int) $row['same_shipping_address'];

	if(!empty($same_shipping_address)){
		$shipping_street 	= $billing_street;
		$shipping_city		= $billing_city;
		$shipping_state		= $billing_state;
		$shipping_zipcode	= $billing_zipcode;
		$shipping_country	= $billing_country;
	}else{
		$shipping_street 	= htmlspecialchars(trim($row['shipping_street']));
		$shipping_city 		= htmlspecialchars(trim($row['shipping_city']));
		$shipping_state 	= htmlspecialchars(trim($row['shipping_state']));
		$shipping_zipcode 	= htmlspecialchars(trim($row['shipping_zipcode']));
		$shipping_country 	= htmlspecialchars(trim($row['shipping_country']));
	}

	if(!empty($billing_street) || !empty($billing_city) || !empty($billing_state) || !empty($billing_zipcode) || !empty($billing_country)){
		$billing_address  = "{$billing_street}<br />{$billing_city}, {$billing_state} {$billing_zipcode}<br />{$billing_country}";
	}

	if(!empty($shipping_street) || !empty($shipping_city) || !empty($shipping_state) || !empty($shipping_zipcode) || !empty($shipping_country)){
		$shipping_address = "{$shipping_street}<br />{$shipping_city}, {$shipping_state} {$shipping_zipcode}<br />{$shipping_country}";
	}

	if(!empty($row)){
		$payment_has_record = true;

		if(empty($payment_id)){
			//if the payment has record but has no payment id, then the record was being inserted manually (the payment status was being set manually by user)
			//in this case, we consider this record empty
			$payment_has_record = false;
		}
	}else{
		//if the entry doesn't have any record within ap_form_payments table
		//we need to calculate the total amount
		$payment_has_record = false;
		$payment_status = "unpaid";

		if($payment_price_type == 'variable'){
			$payment_amount = (double) mf_get_payment_total($dbh,$form_id,$entry_id,0,'live');
		}else if($payment_price_type == 'fixed'){
			$payment_amount = $payment_price_amount;
		}

		//calculate discount if applicable
		if($is_discount_applicable){
			$payment_calculated_discount = 0;

			if($payment_discount_type == 'percent_off'){
				//the discount is percentage
				$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_amount;
				$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
			}else{
				//the discount is fixed amount
				$payment_calculated_discount = round($payment_discount_amount,2); //round to 2 digits decimal
			}

			$payment_amount -= $payment_calculated_discount;
		}

		//calculate tax if enabled
		if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
			$payment_tax_amount = ($payment_tax_rate / 100) * $payment_amount;
			$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
			$payment_amount += $payment_tax_amount;
		}

		$payment_currency = $form_payment_currency;
	}

	//build payment resume URL if the status is unpaid
	if($payment_status == 'unpaid'){
		if($payment_merchant_type == 'paypal_standard'){
			$payment_resume_url = mf_get_merchant_redirect_url($dbh,$form_id,$entry_id);
		}else if(in_array($payment_merchant_type, array('stripe','paypal_rest','authorizenet','braintree'))){
			$payment_resume_token = base64_encode($entry_id.'-'.md5($date_created_raw));
			$payment_resume_url   = "/plan//forms/payment?id={$form_id}&pay_token={$payment_resume_token}";
		}
	}

	switch ($payment_currency) {
		case 'USD' : $currency_symbol = '&#36;';break;
		case 'EUR' : $currency_symbol = '&#8364;';break;
		case 'GBP' : $currency_symbol = '&#163;';break;
		case 'AUD' : $currency_symbol = '&#36;';break;
		case 'CAD' : $currency_symbol = '&#36;';break;
		case 'JPY' : $currency_symbol = '&#165;';break;
		case 'THB' : $currency_symbol = '&#3647;';break;
		case 'HUF' : $currency_symbol = '&#70;&#116;';break;
		case 'CHF' : $currency_symbol = 'CHF';break;
		case 'CZK' : $currency_symbol = '&#75;&#269;';break;
		case 'SEK' : $currency_symbol = 'kr';break;
		case 'DKK' : $currency_symbol = 'kr';break;
		case 'PHP' : $currency_symbol = '&#36;';break;
		case 'IDR' : $currency_symbol = 'Rp';break;
		case 'MYR' : $currency_symbol = 'RM';break;
		case 'PLN' : $currency_symbol = '&#122;&#322;';break;
		case 'BRL' : $currency_symbol = 'R&#36;';break;
		case 'HKD' : $currency_symbol = '&#36;';break;
		case 'MXN' : $currency_symbol = 'Mex&#36;';break;
		case 'TWD' : $currency_symbol = 'NT&#36;';break;
		case 'TRY' : $currency_symbol = 'TL';break;
		case 'NZD' : $currency_symbol = '&#36;';break;
		case 'SGD' : $currency_symbol = '&#36;';break;
		default: $currency_symbol = ''; break;
	}
}


//get entry details for particular entry_id
$param['checkbox_image'] = '/form_builder/images/icons/59_blue_16.png';
$param['show_image_preview'] = true;

$entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$param);

//get the list of the custom email logic template, if any
$query = "select
				rule_id
			from
				".MF_TABLE_PREFIX."email_logic
			where
				form_id=? and
				template_name='custom'
		order by
				rule_id asc";
$params = array($form_id);

$sth = mf_do_query($query,$params,$dbh);

$custom_logic_email_template_array = array();
while($row = mf_do_fetch_result($sth)){
	$custom_logic_email_template_array[] = $row['rule_id'];
}

//check for any 'signature' field, if there is any, we need to include the javascript library to display the signature
$query = "select
				count(form_id) total_signature_field
			from
				".MF_TABLE_PREFIX."form_elements
			where
				element_type = 'signature' and
				element_status=1 and
				form_id=?";
$params = array($form_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);
if(!empty($row['total_signature_field'])){
	$disable_jquery_loading = true;
	$signature_pad_init = '<script type="text/javascript" src="/form_builder/js/jquery.min.js"></script>'."\n".
							'<!--[if lt IE 9]><script src="/form_builder/js/signaturepad/flashcanvas.js"></script><![endif]-->'."\n".
							'<script type="text/javascript" src="/form_builder/js/signaturepad/jquery.signaturepad.min.js"></script>'."\n".
							'<script type="text/javascript" src="/form_builder/js/signaturepad/json2.min.js"></script>'."\n";
}

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php
				$status = "";

				if($profile->getDeleted())
				{
					$status = " <span class='label label-danger'>".__("Not Active")."</span>";
				}

				echo $profile->getTitle().$status;

				if($profile->getUserId() == $current_user->getId() && !$profile->getDeleted())
				{
					?>
					<a href="/plan//profile/edit/id/<?php echo $profile->getId() ?>" class="btn btn-primary btn-sm pull-right" style="margin-top: -4px; color: #FFFFFF;"><span class="fa fa-pencil"></span> <?php echo __("Edit Profile"); ?></a>
					<a href="/plan//profile/adduser/id/<?php echo $profile->getId() ?>" class="btn btn-primary btn-sm pull-right" style="margin-top: -4px; margin-right: 5px; color: #FFFFFF;"><span class="fa fa-plus"></span>  <?php echo __("Add User"); ?></a>
					<?php 
				}
			?>
			<a href="/plan//dashboard" class="btn btn-primary btn-sm pull-right" style="margin-top: -4px; color: #FFFFFF; margin-left: 5px;  margin-right: 5px;"><span class="fa fa-arrow-circle-left"></span>  <?php echo __("Back to Dashboard"); ?></a>
        </h3>
    </div>
    <div class="panel-body">


		<div class="panel with-nav-tabs panel-default">
            <div class="panel-heading">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab1default" data-toggle="tab"><span class="fa fa-bars"></span> <?php echo __("Business Details"); ?></a></li>
					<?php if(!$profile->getDeleted()){ ?>
                    <li><a href="#tab2default" data-toggle="tab"><span class="fa fa-download"></span> <?php echo __("Services"); ?></a></li>
                    <li><a href="#tab3default" data-toggle="tab"><span class="fa fa-money"></span> <?php echo __("Payments"); ?></a></li>
                    <li><a href="#tab4default" data-toggle="tab"><span class="fa fa-users"></span> <?php echo __("Users"); ?></a></li>
                    <li><a href="#tab5default" data-toggle="tab"><span class="fa fa-list"></span> <?php echo __("Inspections"); ?></a></li>
					<?php } ?>
                </ul>
            </div>
            <div class="panel-body p-0">
                <div class="tab-content">
                    <div class="tab-pane fade in active form-horizontal p20" id="tab1default">

						<?php 
						$q = Doctrine_Query::create()
						->from("ApFormPayments a")
						->where("a.payment_id = ?", $profile->getFormId()."/".$profile->getEntryId()."/".$profile->getId())
						->andWhere("a.payment_status = ?", "pending")
						->orderBy("a.afp_id DESC")
						->limit(1);
						$payments = $q->execute();
						foreach($payments as $payment)
						{
							$sf_user->setAttribute('profile_id', $profile->getId());
							$_SESSION['profile_id'] = $profile->getId();
							?>
							<div class="alert alert-success">
								<h4><?php echo __("Profile Activation Payment"); ?></h4> <?php echo __("Payment Reference"); ?>: <?php echo $profile->getFormId()."/".$profile->getEntryId()."/".$profile->getId(); ?>. <br><br>
								<a href="/plan//profile/payment" class="btn btn-success"><?php echo __("Add Payment"); ?></a>
							</div>
							<?php 
						}
						?>

                        <table class="table m-b-0">
							<tbody>
								<?php
								$toggle = false;

								foreach ($entry_details as $data){
									if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
										continue;
									}

									if($toggle){
										$toggle = false;
										$row_style = 'class="alt"';
									}else{
										$toggle = true;
										$row_style = '';
									}

									$row_markup = '';
									$element_id = $data['element_id'];

									if($data['element_type'] == 'section') {
										if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
											$section_separator = '<br/>';
										}else{
											$section_separator = '';
										}

										$section_break_content = '<span class="mf_section_title"><strong>'.nl2br($data['label']).'</strong></span>'.$section_separator.'<span class="mf_section_content">'.nl2br($data['value']).'</span>';

										$row_markup .= "<tr {$row_style}>\n";
										$row_markup .= "<td width=\"100%\" colspan=\"2\">{$section_break_content}</td>\n";
										$row_markup .= "</tr>\n";
									}else if($data['element_type'] == 'signature') {
										if($data['element_size'] == 'small'){
											$canvas_height = 70;
											$line_margin_top = 50;
										}else if($data['element_size'] == 'medium'){
											$canvas_height = 130;
											$line_margin_top = 95;
										}else{
											$canvas_height = 260;
											$line_margin_top = 200;
										}

										$signature_markup = <<<EOT
						<div id="mf_sigpad_{$element_id}" class="mf_sig_wrapper {$data['element_size']}">
							<canvas class="mf_canvas_pad" width="309" height="{$canvas_height}"></canvas>
						</div>
						<script type="text/javascript">
							$(function(){
								var sigpad_options_{$element_id} = {
									drawOnly : true,
									displayOnly: true,
									bgColour: '#fff',
									penColour: '#000',
									output: '#element_{$element_id}',
									lineTop: {$line_margin_top},
									lineMargin: 10,
									validateFields: false
								};
								var sigpad_data_{$element_id} = {$data['value']};
								$('#mf_sigpad_{$element_id}').signaturePad(sigpad_options_{$element_id}).regenerate(sigpad_data_{$element_id});
							});
						</script>
EOT;

										$row_markup .= "<tr>\n";
										$row_markup .= "<td style='width:30%;'><strong>{$data['label']}</strong></td>\n";
										$row_markup .= "<td>{$signature_markup}</td>\n";
										$row_markup .= "</tr>\n";
									}else{
										$row_markup .= "<tr {$row_style}>\n";
										$row_markup .= "<td style='width:30%;'><strong>{$data['label']}</strong></td>\n";
										$row_markup .= "<td>".nl2br($data['value'])."</td>\n";
										$row_markup .= "</tr>\n";
									}

									echo $row_markup;
								}
						?>
						</table>
                    </div>
					<?php if(!$profile->getDeleted()){ ?>
					<div class="tab-pane fade in form-horizontal" id="tab2default">
						<?php if($profile->getDeleted() == 0){ ?>
						<div class="table-responsive">
							<table class="table table-hover table-special table-striped">
								<thead>
									<tr>
										<th><?php echo __("Service"); ?></th>
										<th><?php echo __("Ref No."); ?></th>
										<th><?php echo __("Status"); ?></th>
										<th class="text-right"><?php echo __("Actions"); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($latest_services->getResults() as $application): ?>
									<tr>
										<td>
											<h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
											<p><?php echo date('d F Y H:m:s', strtotime($application->getDateOfSubmission())); ?></p>
										</td>
										<td><?php echo $application->getApplicationId(); ?></td>
										<td><span class="label label-success"><?php echo $application->getStatusName(); ?></span></td>
										<td class="text-right">
											<a class="btn btn-xs btn-default"  title='<?php echo __('View Service'); ?>' href='/plan//application/view/id/<?php echo $application->getId(); ?>/profile/<?php echo $profile->getId(); ?>'><?php echo __("View"); ?> </a>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
								<tr>
									<th colspan="12">
										<p class="table-showing pull-left"><strong><?php echo $latest_services->getNbResults(); ?></strong> <?php echo __("services in this profile"); ?>

											<?php if ($latest_services->haveToPaginate()): ?>
												- <?php echo __("page"); ?> <strong><?php echo $latest_services->getPage() ?>/<?php echo $latest_services->getLastPage() ?></strong>
											<?php endif; ?></p>

										<?php if ($latest_services->haveToPaginate()): ?>
											<ul class="pagination pagination-sm mb0 mt0 pull-right">
												<li><a href="/plan//dashboard/index/bpage/1">
														<i class="fa fa-angle-left"></i>
													</a></li>

												<li> <a href="/plan//dashboard/index/bpage/<?php echo $latest_services->getPreviousPage() ?>">
														<i class="fa fa-angle-left"></i>
													</a></li>

												<?php foreach ($latest_services->getLinks() as $page): ?>
													<?php if ($page == $latest_services->getPage()): ?>
														<li class="active"><a href=""><?php echo $page ?></a>
													<?php else: ?>
														<li><a href="/plan//dashboard/index/bpage/<?php echo $page ?>"><?php echo $page ?></a></li>
													<?php endif; ?>
												<?php endforeach; ?>

												<li> <a href="/plan//dashboard/index/bpage/<?php echo $latest_services->getNextPage() ?>">
														<i class="fa fa-angle-right"></i>
													</a></li>

												<li> <a href="/plan//dashboard/index/bpage/<?php echo $latest_services->getLastPage() ?>">
														<i class="fa fa-angle-right"></i>
													</a></li>
											</ul>
										<?php endif; ?>

									</th>
								</tr>
								</tfoot>
							</table>
						</div>
						<?php } ?>
					</div>
					<div class="tab-pane fade in form-horizontal" id="tab3default">
						<?php if($profile->getDeleted() == 0){ ?>
						<div class="table-responsive">
							<table class="table table-hover table-special table-striped">
								<thead>
									<tr>
										<th width="30%"><?php echo __("Service"); ?></th>
										<th><?php echo __("Bill Ref No."); ?></th>
										<th><?php echo __("Bill Status"); ?></th>
										<th><?php echo __("Amount"); ?></th>
										<th><?php echo __("Due Date"); ?></th>
										<th class="text-right"><?php echo __("Actions"); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($latest_invoices->getResults() as $invoice): ?>
									<?php $application = $invoice->getFormEntry(); ?>
									<tr>
										<td>
											<h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
											<p><?php echo date('d F Y H:m:s', strtotime($application->getDateOfSubmission())); ?></p>
										</td>
										<td><?php echo $application->getFormId()."/".$application->getEntryId()."/".$invoice->getId(); ?></td>
										<td><span class="label <?php if($invoice->getPaid() != 2 ){ ?>label-danger<?php }else{ ?>label-success<?php } ?>"><?php echo $invoice->getStatus(); ?></span></td>
										<td><?php echo $invoice->getCurrency()." ".$invoice->getTotalAmount(); ?></td>
										<td><?php echo $invoice->getCreatedAt(); ?></td>
										<td class="text-right">
											<a class="btn btn-xs btn-default"  title='<?php echo __('View Invoice'); ?>' href='/plan//invoices/view/id/<?php echo $invoice->getId(); ?>'><?php echo __("View"); ?> </a>

											<?php if($invoice->getPaid() == 1 || $invoice->getPaid() == 15){ ?>
											<a href="/plan//forms/payment?id=<?php echo $application->getFormId(); ?>&app_id=<?php echo $application->getEntryId(); ?>&invoice=<?php echo $invoice->getId(); ?>" class="btn btn-primary btn-xs">
											<?php echo __("Pay now"); ?>
											</a>
											<?php } ?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
								<tr>
									<th colspan="12">
										<p class="table-showing pull-left"><strong><?php echo $latest_invoices->getNbResults(); ?></strong> <?php echo __("invoices in this stage"); ?>

											<?php if ($latest_invoices->haveToPaginate()): ?>
												- <?php echo __("page"); ?> <strong><?php echo $latest_invoices->getPage() ?>/<?php echo $latest_invoices->getLastPage() ?></strong>
											<?php endif; ?></p>

										<?php if ($latest_invoices->haveToPaginate()): ?>
											<ul class="pagination pagination-sm mb0 mt0 pull-right">
												<li><a href="/plan//dashboard/index/bpage/1">
														<i class="fa fa-angle-left"></i>
													</a></li>

												<li> <a href="/plan//dashboard/index/bpage/<?php echo $latest_invoices->getPreviousPage() ?>">
														<i class="fa fa-angle-left"></i>
													</a></li>

												<?php foreach ($latest_invoices->getLinks() as $page): ?>
													<?php if ($page == $latest_invoices->getPage()): ?>
														<li class="active"><a href=""><?php echo $page ?></a>
													<?php else: ?>
														<li><a href="/plan//dashboard/index/bpage/<?php echo $page ?>"><?php echo $page ?></a></li>
													<?php endif; ?>
												<?php endforeach; ?>

												<li> <a href="/plan//dashboard/index/bpage/<?php echo $latest_invoices->getNextPage() ?>">
														<i class="fa fa-angle-right"></i>
													</a></li>

												<li> <a href="/plan//dashboard/index/bpage/<?php echo $latest_invoices->getLastPage() ?>">
														<i class="fa fa-angle-right"></i>
													</a></li>
											</ul>
										<?php endif; ?>

									</th>
								</tr>
								</tfoot>
							</table>
						</div>
						<?php } ?>
					</div>
					<div class="tab-pane fade in form-horizontal" id="tab4default">
						<table class="table table-hover table-special table-striped">
							<thead>
								<tr>
									<th><?php echo __("Full name"); ?></th>
									<th><?php echo __("ID number"); ?></th>
									<th><?php echo __("Email"); ?></th>
									<th><?php echo __("Mobile"); ?></th>
									<th class="text-right"><?php echo __("Actions"); ?></th>
								</tr>
							</thead>
							<tbody>
						<?php
						foreach($users as $user_share)
						{
							$user = $user_share->getUser();
							$user_profile = $user->getSfGuardUserProfile();
							?>
								<tr>
									<td><?php echo $user_profile->getFullname(); ?></td>
									<td><?php echo $user->getUsername(); ?></td>
									<td><?php echo $user->getEmailAddress(); ?></td>
									<td><?php echo $user_profile->getMobile(); ?></td>
									<td>
										<a onclick="return confirm('Are you sure?');" href="/plan//profile/removeuser/id/<?php echo $user->getId(); ?>" class="btn btn-danger pull-right"><?php echo __("Remove User"); ?></a>
									</td>
								</tr>
							<?php
						}
						?>
						</table>
					</div>
					<div class="tab-pane fade in form-horizontal" id="tab5default">
						<div class="table-responsive">
							<table class="table table-hover table-special table-striped">
								<thead>
									<tr>
										<th><?php echo __("Reviewer"); ?></th>
										<th><?php echo __("Date of Inspection"); ?></th>
										<th class="text-right"><?php echo __("Actions"); ?></th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach($inspections as $inspect)
									{
										?>
										<tr>
											<td>
												<?php
												$reviewer = $inspect->getReviewer();

												echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
												?>
											</td>
											<td><?php echo $inspect->getCreatedAt(); ?></td>
											<td class="text-right">
												<a class="btn btn-xs btn-default"  title='<?php echo __('View'); ?>' href='/plan//profile/inspection/id/<?php echo $inspect->getId(); ?>'><?php echo __("View"); ?> </a>
											</td>
										</tr>
										<?php
									}
								?>
								</tbody>
							</table>
						</div>
					</div>
					<?php 
					}
					?>
				</div>
			</div>
		</div>

	</div>
</div>
