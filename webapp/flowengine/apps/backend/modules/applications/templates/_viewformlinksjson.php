<?php

$form_id  = $link->getFormId();
$entry_id = $link->getEntryId();



$nav = trim($_GET['nav']);

if(empty($form_id) || empty($entry_id)){
    die("Invalid Request");
}

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

//check permission, is the user allowed to access this page?
if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
    $user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

    //this page need edit_entries or view_entries permission
    if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
        $_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

        $ssl_suffix = mf_get_ssl_suffix();
        header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
        exit;
    }
}

//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
if(!empty($nav)){
    $all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id);
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
					 payment_tax_rate
			     from
			     	 ".MF_TABLE_PREFIX."forms
			    where
			    	 form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

if(!empty($row)){
    $form_name = htmlspecialchars($row['form_name']);
    $payment_enable_merchant = (int) $row['payment_enable_merchant'];
    if($payment_enable_merchant < 1){
        $payment_enable_merchant = 0;
    }

    $payment_price_amount = (double) $row['payment_price_amount'];
    $payment_merchant_type = $row['payment_merchant_type'];
    $payment_price_type = $row['payment_price_type'];
    $form_payment_currency = strtoupper($row['payment_currency']);
    $payment_ask_billing = (int) $row['payment_ask_billing'];
    $payment_ask_shipping = (int) $row['payment_ask_shipping'];

    $payment_enable_tax = (int) $row['payment_enable_tax'];
    $payment_tax_rate 	= (float) $row['payment_tax_rate'];
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
						`payment_merchant_type`,
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
    $payment_merchant_type = $row['payment_merchant_type'];
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

        //calculate tax if enabled
        if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
            $payment_tax_amount = ($payment_tax_rate / 100) * $payment_amount;
            $payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
            $payment_amount += $payment_tax_amount;
        }

        $payment_currency = $form_payment_currency;
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
$param['checkbox_image'] = 'images/icons/59_blue_16.png';
$entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$param, $sf_user->getCulture());

//get entry information (date created/updated/ip address)
$query = "select
					date_format(date_created,'%e %b %Y - %r') date_created,
					date_format(date_updated,'%e %b %Y - %r') date_updated,
					ip_address
				from
					`".MF_TABLE_PREFIX."form_{$form_id}`
			where id=?";
$params = array($entry_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

$date_created = $row['date_created'];
if(!empty($row['date_updated'])){
    $date_updated = $row['date_updated'];
}else{
    $date_updated = '&nbsp;';
}
$ip_address   = $row['ip_address'];

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

$q = Doctrine_Query::create()
     ->from('ApForms a')
     ->where('a.form_id = ?', $link->getFormId());
$form = $q->fetchOne();
$formtype = $form->getFormDescription();
$return['data'] = array();
$return['name'] = $form->getFormName().' (Submitted on' . $link->getDateOfSubmission(); ')';
$return['title'] = $formtype;
//Print Out Application Details
foreach ($entry_details as $data){
    $return['data'] = array(
    'element_type' => $data['element_type'],
    'label' => $data['label'],
    'value' => $data['value']
    );

}

return $return;