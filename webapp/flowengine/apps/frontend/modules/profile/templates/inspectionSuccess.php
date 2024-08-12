<?php
/**
 * inspectionSuccess.php template.
 *
 * Displays inspection sheet
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
require($prefix_folder.'includes/check-session.php');

require($prefix_folder.'includes/language.php');
require($prefix_folder.'includes/entry-functions.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/users-functions.php');

$form_id  = $inspection->getFormId();
$entry_id = $inspection->getEntryId();
$nav = trim($_GET['nav']);

if(empty($form_id) || empty($entry_id)){
	die("Invalid Request");
}

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

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
			?>
			<a href="/plan//profile/view/id/<?php echo $profile->getId(); ?>" class="btn btn-primary btn-sm pull-right" style="margin-top: -4px; color: #FFFFFF; margin-left: 5px;  margin-right: 5px;"><?php echo __("Back to Profile"); ?></a>
        </h3>
    </div>
    <div class="panel-body">
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
</div>