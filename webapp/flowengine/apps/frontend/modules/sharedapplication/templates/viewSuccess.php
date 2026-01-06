<?php
/**
 * viewSuccess.php template.
 *
 * Displays full application details
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";

require($prefix_folder . 'includes/init.php');

require($prefix_folder . '../../../config/form_builder_config.php');
require($prefix_folder . 'includes/db-core.php');
require($prefix_folder . 'includes/helper-functions.php');
require($prefix_folder . 'includes/check-session.php');

require($prefix_folder . 'includes/entry-functions.php');
require($prefix_folder . 'includes/post-functions.php');
require($prefix_folder . 'includes/users-functions.php');

function GetDays($sStartDate, $sEndDate)
{
	$aDays[] = $sStartDate;
	$start_date = $sStartDate;
	$end_date = $sEndDate;
	$current_date = $start_date;
	while (strtotime($current_date) <= strtotime($end_date)) {
		$aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
		$current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
	}


	return $aDays;
}



function DuplicateMySQLRecord($table, $id_field, $id)
{
	// load the original record into an array
	$result = mysql_query("SELECT * FROM {$table} WHERE {$id_field}={$id}");
	$original_record = mysql_fetch_assoc($result);

	// insert the new record and get the new auto_increment id
	mysql_query("INSERT INTO {$table} (`{$id_field}`) VALUES (NULL)");
	$newid = mysql_insert_id();

	// generate the query to update the new record with the previous values
	$query = "UPDATE {$table} SET ";
	foreach ($original_record as $key => $value) {
		if ($key != $id_field) {
			$query .= '`' . $key . '` = "' . str_replace('"', '\"', $value) . '", ';
		}
	}
	$query = substr($query, 0, strlen($query) - 2); // lop off the extra trailing comma
	$query .= " WHERE {$id_field}={$newid}";
	mysql_query($query) or die(mysql_error());

	// return the new id
	return $newid;
}

//OTB SETTING entry to zero linked application is not been edited
/*if($application->getDeclined() == "1" && $application->getParentSubmission() == "0")
{
    $q = Doctrine_Query::create()
       ->from("ApplicationReference a")
       ->where("a.application_id = ?", $application->getId());
    $apprefs = $q->execute();
    if(sizeof($apprefs) > 1)
    {
        //echo "Should clone and redirect";
        $newid = DuplicateMySQLRecord ("ap_form_".$application->getFormId(), "id", $application->getEntryId());

        $newentry = new FormEntry();
        $newentry->setFormId($application->getFormId());
        $newentry->setEntryId($application->getEntryId());
        $newentry->setApproved($application->getApproved());
        $newentry->setApplicationId($application->getApplicationId());
        $newentry->setUserId($application->getUserId());
        $newentry->setParentSubmission($application->getId());
        $newentry->setDeclined("1");
        $newentry->setDateOfSubmission($application->getDateOfSubmission());
        $newentry->setDateOfResponse($application->getDateOfResponse());
        $newentry->setDateOfIssue($application->getDateOfIssue());
        $newentry->setObservation($application->getObservation());
        $newentry->save();

        $application->setEntryId($newid);

        $application->setPreviousSubmission($newentry->getId());
        $application->save();

        //header("Location: ".public_path()."index.php/application/view/id/".$newentry->getId());
        //exit;
    }
}*/


$form_id = $application->getFormId();
$entry_id = $application->getEntryId();

$nav = trim($_GET['nav']);

if (empty($form_id) || empty($entry_id)) {
	die("Invalid Request");
}

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

//check permission, is the user allowed to access this page?
if (empty($_SESSION['mf_user_privileges']['priv_administer'])) {
	$user_perms = mf_get_user_permissions($dbh, $form_id, $_SESSION['mf_user_id']);

	//this page need edit_entries or view_entries permission
	if (empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])) {
		$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

		$ssl_suffix = mf_get_ssl_suffix();
		header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/restricted.php");
		exit;
	}
}

//get form name
$query = "select
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
			     	 " . MF_TABLE_PREFIX . "forms
			    where
			    	 form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);

if (!empty($row)) {
	$form_name = htmlspecialchars($row['form_name']);
	$payment_enable_merchant = (int) $row['payment_enable_merchant'];
	if ($payment_enable_merchant < 1) {
		$payment_enable_merchant = 0;
	}

	$payment_price_amount = (double) $row['payment_price_amount'];
	$payment_merchant_type = $row['payment_merchant_type'];
	$payment_price_type = $row['payment_price_type'];
	$form_payment_currency = strtoupper($row['payment_currency']);
	$payment_ask_billing = (int) $row['payment_ask_billing'];
	$payment_ask_shipping = (int) $row['payment_ask_shipping'];

	$payment_enable_tax = (int) $row['payment_enable_tax'];
	$payment_tax_rate = (float) $row['payment_tax_rate'];
}

//if payment enabled, get the details
if (!empty($payment_enable_merchant)) {
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
						" . MF_TABLE_PREFIX . "form_payments
				   WHERE
				   		form_id = ? and record_id = ? and `status` = 1
				ORDER BY
						payment_date DESC
				   LIMIT 1";
	$params = array($form_id, $entry_id);

	$sth = mf_do_query($query, $params, $dbh);
	$row = mf_do_fetch_result($sth);

	$payment_id = $row['payment_id'];
	$payment_date = $row['payment_date'];
	$payment_status = $row['payment_status'];
	$payment_fullname = $row['payment_fullname'];
	$payment_amount = (double) $row['payment_amount'];
	$payment_currency = strtoupper($row['payment_currency']);
	$payment_test_mode = (int) $row['payment_test_mode'];
	$payment_merchant_type = $row['payment_merchant_type'];
	$billing_street = htmlspecialchars(trim($row['billing_street']));
	$billing_city = htmlspecialchars(trim($row['billing_city']));
	$billing_state = htmlspecialchars(trim($row['billing_state']));
	$billing_zipcode = htmlspecialchars(trim($row['billing_zipcode']));
	$billing_country = htmlspecialchars(trim($row['billing_country']));

	$same_shipping_address = (int) $row['same_shipping_address'];

	if (!empty($same_shipping_address)) {
		$shipping_street = $billing_street;
		$shipping_city = $billing_city;
		$shipping_state = $billing_state;
		$shipping_zipcode = $billing_zipcode;
		$shipping_country = $billing_country;
	} else {
		$shipping_street = htmlspecialchars(trim($row['shipping_street']));
		$shipping_city = htmlspecialchars(trim($row['shipping_city']));
		$shipping_state = htmlspecialchars(trim($row['shipping_state']));
		$shipping_zipcode = htmlspecialchars(trim($row['shipping_zipcode']));
		$shipping_country = htmlspecialchars(trim($row['shipping_country']));
	}

	if (!empty($billing_street) || !empty($billing_city) || !empty($billing_state) || !empty($billing_zipcode) || !empty($billing_country)) {
		$billing_address = "{$billing_street}<br />{$billing_city}, {$billing_state} {$billing_zipcode}<br />{$billing_country}";
	}

	if (!empty($shipping_street) || !empty($shipping_city) || !empty($shipping_state) || !empty($shipping_zipcode) || !empty($shipping_country)) {
		$shipping_address = "{$shipping_street}<br />{$shipping_city}, {$shipping_state} {$shipping_zipcode}<br />{$shipping_country}";
	}

	if (!empty($row)) {
		$payment_has_record = true;

		if (empty($payment_id)) {
			//if the payment has record but has no payment id, then the record was being inserted manually (the payment status was being set manually by user)
			//in this case, we consider this record empty
			$payment_has_record = false;
		}
	} else {
		//if the entry doesn't have any record within ap_form_payments table
		//we need to calculate the total amount
		$payment_has_record = false;
		$payment_status = "unpaid";

		if ($payment_price_type == 'variable') {
			$payment_amount = (double) mf_get_payment_total($dbh, $form_id, $entry_id, 0, 'live');
		} else if ($payment_price_type == 'fixed') {
			$payment_amount = $payment_price_amount;
		}

		//calculate tax if enabled
		if (!empty($payment_enable_tax) && !empty($payment_tax_rate)) {
			$payment_tax_amount = ($payment_tax_rate / 100) * $payment_amount;
			$payment_tax_amount = round($payment_tax_amount, 2); //round to 2 digits decimal
			$payment_amount += $payment_tax_amount;
		}

		$payment_currency = $form_payment_currency;
	}

	switch ($payment_currency) {
		case 'USD':
			$currency_symbol = '&#36;';
			break;
		case 'EUR':
			$currency_symbol = '&#8364;';
			break;
		case 'GBP':
			$currency_symbol = '&#163;';
			break;
		case 'AUD':
			$currency_symbol = '&#36;';
			break;
		case 'CAD':
			$currency_symbol = '&#36;';
			break;
		case 'JPY':
			$currency_symbol = '&#165;';
			break;
		case 'THB':
			$currency_symbol = '&#3647;';
			break;
		case 'HUF':
			$currency_symbol = '&#70;&#116;';
			break;
		case 'CHF':
			$currency_symbol = 'CHF';
			break;
		case 'CZK':
			$currency_symbol = '&#75;&#269;';
			break;
		case 'SEK':
			$currency_symbol = 'kr';
			break;
		case 'DKK':
			$currency_symbol = 'kr';
			break;
		case 'PHP':
			$currency_symbol = '&#36;';
			break;
		case 'MYR':
			$currency_symbol = 'RM';
			break;
		case 'PLN':
			$currency_symbol = '&#122;&#322;';
			break;
		case 'BRL':
			$currency_symbol = 'R&#36;';
			break;
		case 'HKD':
			$currency_symbol = '&#36;';
			break;
		case 'MXN':
			$currency_symbol = 'Mex&#36;';
			break;
		case 'TWD':
			$currency_symbol = 'NT&#36;';
			break;
		case 'TRY':
			$currency_symbol = 'TL';
			break;
		case 'NZD':
			$currency_symbol = '&#36;';
			break;
		case 'SGD':
			$currency_symbol = '&#36;';
			break;
		default:
			$currency_symbol = '';
			break;
	}
}


//get entry details for particular entry_id
$param['checkbox_image'] = '/form_builder/images/icons/59_blue_16.png';
$entry_details = mf_get_entry_details($dbh, $form_id, $entry_id, $param);
//$attachments_details = mf_get_attachments_details($dbh,$form_id,$entry_id,$param);

//get entry information (date created/updated/ip address)
$query = "select
					date_format(date_created,'%e %b %Y - %r') date_created,
					date_format(date_updated,'%e %b %Y - %r') date_updated,
					ip_address
				from
					`" . MF_TABLE_PREFIX . "form_{$form_id}`
			where id=?";
$params = array($entry_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);

$date_created = $row['date_created'];
if (!empty($row['date_updated'])) {
	$date_updated = $row['date_updated'];
} else {
	$date_updated = '&nbsp;';
}
$ip_address = $row['ip_address'];

//check for any 'signature' field, if there is any, we need to include the javascript library to display the signature
$query = "select
					count(form_id) total_signature_field
				from
					" . MF_TABLE_PREFIX . "form_elements
			   where
			   		element_type = 'signature' and
			   		element_status=1 and
			   		form_id=?";
$params = array($form_id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);
if (!empty($row['total_signature_field'])) {
	$disable_jquery_loading = true;
	$signature_pad_init = '<script type="text/javascript" src="js/jquery.min.js"></script>' . "\n" .
		'<!--[if lt IE 9]><script src="js/signaturepad/flashcanvas.js"></script><![endif]-->' . "\n" .
		'<script type="text/javascript" src="js/signaturepad/jquery.signaturepad.min.js"></script>' . "\n" .
		'<script type="text/javascript" src="js/signaturepad/json2.min.js"></script>' . "\n";
}

$header_data = <<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="css/entry_print.css" media="print">
{$signature_pad_init}
EOT;

?>




<div class="pageheader">
	<h4><i class="fa fa-share"></i>Shared Application</h4>
	<div class="breadcrumb-wrapper">

		<ol class="breadcrumb">
			<li><a href="#">Applications</a></li>
			<li class="active"><?php
			$q = Doctrine_Query::create()
				->from('ApForms a')
				->where('a.form_id = ?', $application->getFormId());
			$form = $q->fetchOne();

			$form_name = "";

			$q = Doctrine_Query::create()
				->from('ExtTranslations t')
				->where('t.field_id = ? and t.field_name = ? and t.table_class =? and t.locale = ?', array($application->getFormId(), 'form_name', 'ap_forms', $_SESSION['locale']));
			$translations = $q->fetchOne();
			if ($translations) {
				$form_name = $translations->getTrlContent();
			} else {
				$form_name = $form->getFormName();
			}

			echo $form_name;
			?></li>
		</ol>
	</div>
	<div style="float:right">
		<?php
		$q = Doctrine_Query::create()
			->from("FormEntryLinks a")
			->where("a.formentryid = ?", $application->getId());
		$links = $q->execute();
		foreach ($links as $link) {
			if ($application->getDeclined() && $link->getUserId() == $sf_user->getGuardUser()->getId()): ?>
				<?php $q = Doctrine_Query::create()
					->from('ApForms f')
					->where('f.form_id = ?', $link->getFormId());
				$link_form = $q->fetchOne();
				?>
				<a href="/index.php/sharedapplication/edit?link=<?php echo $link->getId(); ?>"
					class="btn btn-primary dropdown-toggle waves-effect"><i
						class="fa fa-edit"></i><?php echo __('Edit') . ' ' . $link_form->getFormName() ?> </a>
			<?php endif; ?>
		<?php } ?>
		<?php $otbhelper = new OTBHelper();
		if ($otbhelper->isSharedStage($application->getApproved())):
			?>
			<!-- OTB Start Patch >> Share Button -->
			<a title='<?php echo __('Share Application'); ?>'
				href='<?php echo public_path('index.php/application/share/id/' . $application->getId()); ?>'
				class="btn btn-primary dropdown-toggle waves-effect"><?php echo __("Share"); ?> </a>
			<!-- OTB End Patch >> Share Button -->
		<?php endif; ?>
	</div>
</div>




<div class="contentpanel">

	<div class="row">



		<?php
		if ($application->getApproved() == "0") {
		} else {
			$q = Doctrine_Query::create()
				->from('SubMenus a')
				->where('a.id = ?', $application->getApproved());
			$submenu = $q->fetchOne();
		}


		$q = Doctrine_Query::create()
			->from('SubMenuButtons a')
			->where('a.sub_menu_id = ?', $application->getApproved());
		$submenubuttons = $q->execute();

		foreach ($submenubuttons as $submenubutton) {
			$q = Doctrine_Query::create()
				->from('Buttons a')
				->where('a.id = ?', $submenubutton->getButtonId());
			$buttons = $q->execute();
			foreach ($buttons as $button) {
				$pos = strpos($button->getLink(), "generatepermit");
				if ($pos === false) {

				} else {
					$q = Doctrine_Query::create()
						->from("SavedPermit a")
						->where("a.application_id = ?", $application->getId());
					$permits = $q->execute();
					foreach ($permits as $permit):
						?>

						<a href="<?php echo public_path('index.php/permits/view/id/' . $permit->getId()); ?>"
							class="btn-xs btn-primary panel-edit">View Permit</a>
						<?php
					endforeach;
				}
			}
		}
		?>


		<!--OTB linked-->
		<?php
		//OTB Fix Show linkto if a user is permitted to access a particular form 
		$user_registered_as = Doctrine_Query::create()
			->from('sfGuardUserProfile u')
			->where('u.user_id = ?', $sf_user->getGuardUser()->getId());
		$user_registered_as_res = $user_registered_as->fetchOne();
		//if we have something
		if ($user_registered_as_res) {
			$q = Doctrine_Query::create()
				->from('ApForms f')
				->where('f.form_stage =? and f.form_active =? and f.form_type =?', array($application->getApproved(), 1, 1));
			$forms_link = $q->execute();

			foreach ($forms_link as $form_link) {
				error_log('---------FORM ID-------' . $form_link->getFormId());
				if ($form_link->getFormId() != $application->getFormId()) {
					$q = Doctrine_Query::create()
						->from('SfGuardUserCategoriesForms f')
						->where('f.categoryid = ? and f.formid = ?', array($user_registered_as_res->getRegisterAs(), $form_link->getFormId()));
					$cat_forms = $q->fetchOne();
					if ($cat_forms) {
						//
						echo "<a class=\"btn btn-primary\" href='/index.php/forms/view?id=" . $form_link->getFormId() . "&linkto=" . $application->getId() . "'>" . 'Apply for' . " " . $form_link->getFormName() . "</a>";
					}

					$action_count++;
				}
			}
		}
		?>
		<!--OTB linked-->










		<div class="panel panel-dark widget-btns">
			<div class="panel-heading">
				<h3 class="panel-title"> <?php echo $application->getApplicationId(); ?> <span><?php
					if ($application->getApproved() == "0") {
						//Draft
					} else {
						$q = Doctrine_Query::create()
							->from('SubMenus a')
							->where('a.id = ?', $application->getApproved());
						$submenu = $q->fetchOne();
						if ($submenu) {
							echo "</h3>";
							echo "<p class=\"text-muted\">" . $submenu->getTitle() . "</p>";
						}
					}
					?>

						<div class="panel-btns">
							<div class="pull-right">


								<?php
								$disabled = "disabled='disabled'";

								$q = Doctrine_Query::create()
									->from("AttachedPermit a")
									->where("a.application_id = ?", $application->getId());
								$attachedpermits = $q->execute();

								if (sizeof($attachedpermits) > 0) {
									$disabled = "";
								}

								?>

								<?php
								$q = Doctrine_Query::create()
									->from("FormEntryLinks a")
									->where("a.formentryid = ?", $application->getId())
									->andWhere("a.form_id = ?", 61);
								$links = $q->execute();
								if (sizeof($links) > 0) {
									$link = $q->fetchOne();
									?>
									<a href="/index.php/forms/edit?id=<?php echo $link->getId(); ?>"
										class="btn-xs btn-primary panel-edit">Edit Additional Information</a>
									<?php
								} else {
									if ($application->getFormId() == "60" || $application->getFormId() == "47" || $application->getFormId() == "48" || $application->getFormId() == "49") {
										?>
										<a <?php echo $disabled; ?>
											href="/index.php/forms/view?id=61&linkto=<?php echo $application->getId(); ?>"
											class="btn-xs btn-primary panel-edit">Submit Additional Information</a>
										<?php
									}
								}
								?>
								<?php
								$q = Doctrine_Query::create()
									->from("FormEntryLinks a")
									->where("a.formentryid = ?", $application->getId())
									->andWhere("a.form_id = ? OR a.form_id = ? OR a.form_id = ? OR a.form_id = ? OR a.form_id = ?", array('25', '942', '27', '58', '59'));
								$links = $q->execute();
								if (sizeof($links) > 0) {
									$link = $q->fetchOne();
									?>
									<a href="/index.php/forms/edit?id=<?php echo $link->getId(); ?>"
										class="btn-xs btn-primary panel-edit">Edit Occupancy Permit</a>

									<?php
								} else {
									if ($application->getFormId() == "60" || $application->getFormId() == "47" || $application->getFormId() == "48" || $application->getFormId() == "49") {
										$occupancyformid = "";
										if ($application->getFormId() == "60") {
											$occupancyformid = "942";
										} else if ($application->getFormId() == "47") {
											$occupancyformid = "27";
										} else if ($application->getFormId() == "48") {
											$occupancyformid = "58";
										} else if ($application->getFormId() == "49") {
											$occupancyformid = "59";
										}
										?>
										<a <?php echo $disabled; ?>
											href="/index.php/forms/view?id=25&linkto=<?php echo $application->getId(); ?>"
											class="btn-xs btn-primary panel-edit">Apply for Occupancy Permit</a>

										<?php
									}
								}
								?>

							</div>
						</div>
			</div>

			<div class="panel-body panel-body-nopadding">
				<ul class="nav nav-tabs nav-justified">
					<li style="margin-left:10px;" class="active"><a href="#tabs1" data-toggle="tab">Details</a></li>
					<?php
					$q = Doctrine_Query::create()
						->from("FormEntryLinks a")
						->where("a.formentryid = ?", $application->getId());
					$links = $q->execute();
					foreach ($links as $link) {
						$q = Doctrine_Query::create()
							->from("ApForms a")
							->where("a.form_id = ?", $link->getFormId());
						$linkedform = $q->fetchOne();
						if ($linkedform) {
							?>
							<li><a href="#tabsf<?php echo $link->getId(); ?>"
									data-toggle="tab"><?php echo $linkedform->getFormName(); ?></a></li>
							<?php
						}
					}
					?>
					<li><a href="#tabs4" data-toggle="tab">Reviewers</a></li>
					<?php
					$q = Doctrine_Query::create()
						->from('Task a')
						->where('a.application_id = ?', $application->getId())
						->andWhere('a.status = ?', '1');
					$pendingtasks = $q->execute();
					if (sizeof($pendingtasks) == 0) {
						?>
						<li><a href="#tabs5" data-toggle="tab">Comments</a></li>
						<?php
					}
					?>
					<?php
					if (sizeof($application->getMfInvoice()) > 0) {
						?>
						<li><a href="#tabs6" data-toggle="tab">Invoices</a></li>
						<?php
					}
					?>
					<li><a href="#tabs7" data-toggle="tab">Messages</a></li>
					<?php
					$q = Doctrine_Query::create()
						->from("AttachedPermit a")
						->where("a.application_id = ?", $application->getId());
					$attachedpermit = $q->fetchOne();
					if ($attachedpermit) {
						?>
						<li><a href="#tabs8" data-toggle="tab">Permits</a></li>
						<?php
					}

					if ($application->getPreviousSubmission() != "0") {
						?>
						<li><a href="#tabs9" data-toggle="tab">Previous Submission</a></li>
						<?php
					}
					?>
				</ul>
				<div class="tab-content">
					<div id="tabs1" class="tab-pane active">
						<form class="form-bordered form-horizontal">
							<?php
							$toggle = false;

							foreach ($entry_details as $data) {
								if ($data['element_type'] == "section") {
									?>
									<div class="form-group">
										<label class="col-sm-2 control-label"><i
												class="bold-label"><?php echo $data['label']; ?></i></label>
									</div>
									<?php
								} else {
									?>
									<div class="form-group">
										<label class="col-sm-2 control-label"><i
												class="bold-label"><?php echo $data['label']; ?></i></label>
										<div class="col-sm-8">
											<?php echo nl2br($data['value']); ?>
										</div>
									</div>
									<?php
								}
							}
							?>
						</form>

					</div>
					<div id="tabs4" class="tab-pane">
						<table class="table mb0">
							<thead>
								<?php
								$q = Doctrine_Query::create()
									->from('Department a');
								$departments = $q->execute();
								$count_deps = 0;
								foreach ($departments as $department) {
									//Check if this department has any tasks
									$q = Doctrine_Query::create()
										->from('CfUser a')
										->where('a.strdepartment = ?', $department->getDepartmentName());
									$reviewers = $q->execute();
									$count_tasks = 0;
									foreach ($reviewers as $reviewer) {
										$q = Doctrine_Query::create()
											->from('Task a')
											->where('a.owner_user_id = ?', $reviewer->getNid())
											->andWhere('a.application_id = ?', $application->getId());
										$count_tasks = $count_tasks + sizeof($q->execute());
									}
									if ($count_tasks <= 0) {
										continue;
									}
									$count_deps++;
									?>

									<thead>
										<th colspan="6" style="background:#d8dbde;">
											<?php echo $department->getDepartmentName(); ?>
										</th>
										</th>
									</thead>
								<tbody>
									<tr>
										<th>Reviewer</th>
										<th>Task</th>
										<th>Start Date</th>
										<th>End Date</th>
										<th>Status</th>
									</tr>
									<tr>
										<?php
										$q = Doctrine_Query::create()
											->from('CfUser a')
											->where('a.strdepartment = ?', $department->getDepartmentName());
										$reviewers = $q->execute();
										foreach ($reviewers as $reviewer) {
											$q = Doctrine_Query::create()
												->from('Task a')
												->where('a.owner_user_id = ?', $reviewer->getNid())
												->andWhere('a.application_id = ?', $application->getId());
											$tasks = $q->execute();
											foreach ($tasks as $task) {
												$tasktype = "";
												if ($task->getType() == "1") {
													$tasktype = "Review";
												}
												if ($task->getType() == "2") {
													$tasktype = "Commenting";
												}
												if ($task->getType() == "6") {
													$tasktype = "Inspection";
												}
												if ($task->getType() == "3") {
													$tasktype = "Invoicing";
												}
												if ($task->getType() == "4") {
													$tasktype = "Scanning";
												}
												if ($task->getType() == "5") {
													$tasktype = "Stamping";
												}
												echo "<td>" . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . "</td>";
												echo "<td>" . $tasktype . "</td>";
												echo "<td>" . $task->getStartDate() . "</td>";
												echo "<td>" . $task->getEndDate() . "</td>";
												echo "<td>";
												if ($task->getStatus() == "1") {
													echo "Pending";
												} else if ($task->getStatus() == "2") {
													echo "Completing";
												} else if ($task->getStatus() == "25") {
													echo "Completed";
												} else if ($task->getStatus() == "5") {
													echo "Cancelling";
												} else if ($task->getStatus() == "55") {
													echo "Cancelled";
												} else if ($task->getStatus() == "3") {
													echo "PostPoned";
												} else if ($task->getStatus() == "4") {
													echo "Transferring";
												} else if ($task->getStatus() == "45") {
													echo "Transferred";
												}
												echo "</td></tr>";
											}
										}
										?>
										<?php

								}
								?>

							</tbody>
						</table>
						<?php
						if ($count_deps == 0) {
							?>
							<table class="table mb0">
								<tbody>
									<tr>
										<td>
											<i class="bold-label">No records found</i>
										</td>
									</tr>
								</tbody>
							</table>
							<?php
						}
						?>
					</div>
					<?php
					$q = Doctrine_Query::create()
						->from("FormEntryLinks a")
						->where("a.formentryid = ?", $application->getId());
					$links = $q->execute();
					foreach ($links as $link) {
						$q = Doctrine_Query::create()
							->from("ApForms a")
							->where("a.form_id = ?", $link->getFormId());
						$linkedform = $q->fetchOne();

						if ($linkedform) {
							?>
							<div id="tabsf<?php echo $link->getId(); ?>" class="tab-pane table-responsive">
								<?php
								//get form name
								$form_id = $link->getFormId();
								$entry_id = $link->getEntryId();

								$entry_details = mf_get_entry_details($dbh, $form_id, $entry_id, $param);
								?>
								<table class="table table-card-box m-b-0">
									<tbody>
										<?php
										$toggle = false;

										foreach ($entry_details as $data) {
											if ($data['element_type'] == "section") {

												?>
												<tr>
													<td colspan="2"><strong><?php echo $data['label']; ?></strong></td>
												</tr>
												<?php
											} else {
												?>
												<tr>
													<td><strong><?php echo $data['label']; ?> :</strong></td>
													<td><?php if ($data['value']) {
														echo nl2br($data['value']);
													} else {
														echo "-";
													} ?></td>
												</tr>
												<?php
											}
										}
										?>
									</tbody>
								</table>
							</div>
							<?php
						}
					}
					?>
					<div id="tabs5" class="tab-pane">
						<?php include_partial('application/application_comments', array('application' => $application, 'form_id' => $form_id, 'entry_id' => $entry_id)); ?>
						<?php
						/*$comment_count = 0;
																													 $q = Doctrine_Query::create()
																														->from('CfFormslot a');
																													 $slots = $q->execute();
																													 foreach($slots as $slot)
																													 {
																																	 $q = Doctrine_Query::create()
																																		->from('Comments a')
																																		->where('a.circulation_id = ?', $application->getCirculationId())
																																		->andWhere('a.slot_id = ?', $slot->getNid());
																																	 $comments = $q->execute();
																																	 if(sizeof($comments) > 0)
																																	 {
																																		 $comment_count++;
																																		 $COM_COUNT++;
																																	 }
																													 }

																													 $q = Doctrine_Query::create()
																														->from('ApprovalCondition a')
																														->where('a.entry_id = ?', $application->getId());
																													 $conditions = $q->execute();
																													 if(sizeof($conditions) > 0)
																													 {
																														 $conditions_count++;
																																		 $COM_COUNT++;
																													 }

																													 $comment_count = 0;
																													 $q = Doctrine_Query::create()
																														->from('CfFormslot a');
																													 $slots = $q->execute();
																													 foreach($slots as $slot)
																													 {
																																	 $q = Doctrine_Query::create()
																																		->from('Conditions a')
																																		->where('a.circulation_id = ?', $application->getCirculationId())
																																		->andWhere('a.slot_id = ?', $slot->getNid());
																																	 $conditions = $q->execute();
																																	 if(sizeof($conditions) > 0)
																																	 {
																																		 $conditions_count++;
																																		 $COM_COUNT++;
																																	 }
																													 }

																													 if($application->getCirculationId())
																													 {
																														 $COM_COUNT++;
																													 }

																													 if($COM_COUNT > 0)
																													 {
																											 ?>
																											 <?php
																													 }
																												 ?>

																								 <?php
																								 {
																									 $q = Doctrine_Query::create()
																										  ->from('EntryDecline a')
																										  ->where('a.entry_id = ?', $application->getId())
																										  ->andWhere('a.resolved = ?', '0');
																									 $declines = $q->execute();
																									 if(sizeof($declines) > 0)
																									 {
																										 ?>
																										 <table class="table">
																										  <thead>
																											 <th colspan="6" style="background:#d8dbde;">
																											 Previous Reasons for Decline
																											 </th>
																										  </thead>
																										 <tbody>
																										 <?php
																												 foreach($declines as $decline)
																												 {
																													 ?>
																													 <tr>
																														 <td colspan="2"><?php echo "<li> ".$decline->getDescription()."</li>"; ?></td>
																													   </tr>
																													 <?php
																												 }
																										 ?>
																												 </tbody>
																											 </table>
																										 <?php
																									 }
																								 }
																								 ?>
																													 <?php
																												 if($COM_COUNT > 0)
																												 {
																												 ?>
																												 <table class="table">
																												  <thead>
																											 <th colspan="6" style="background:#d8dbde;">
																											 Comments Summary
																											 </th>
																												  </thead>
																												 <tbody>
																												 <?php
																													 $comment_count = 0;
																													 $q = Doctrine_Query::create()
																														->from('CfFormslot a');
																													 $slots = $q->execute();
																													 foreach($slots as $slot)
																													 {

																														 //Check if comments are hidden for this stage of approval/submenu
																														 if($submenu && $submenu->getHideComments() == "1")
																														 {
																															 break;
																														 }
																																	 $q = Doctrine_Query::create()
																																		->from('Comments a')
																																		->where('a.circulation_id = ?', $application->getCirculationId())
																																		->andWhere('a.slot_id = ?', $slot->getNid());
																																	 $comments = $q->execute();
																																	 if(sizeof($comments) > 0)
																																	 {
																																		 $comment_count++;
																																	 ?>
																																		  <tr>
																																			 <td style="background:#eeeeee; -webkit-border-radius: 4px 4px 0 0; -moz-border-radius: 4px 4px 0 0; border-radius: 4px 4px 0 0;"  colspan="2"><h5><?php echo $slot->getStrname(); ?></h5></td>
																																		   </tr>
																																				 <?php
																																				 foreach($comments as $comment)
																																				 {
																																						 $q = Doctrine_Query::create()
																																							->from('CfInputfield a')
																																							->where('a.nid = ?', $comment->getFieldId());
																																						 $field = $q->fetchOne();
																																						 ?>
																																						   <tr>
																																							 <td><strong><?php echo $field->getStrname(); ?>:</strong></td>
																																							 <td><?php echo $comment->getComment(); ?></td>
																																						   </tr>
																																						 <?php
																																				 }
																																				 ?>
																																	 <?php
																																	 }
																													 }
																												 ?>

																												 <?php
																													 if($comment_count <= 0)
																													 {
																														 ?>
																														   <tr>
																															 <td colspan="2">No Comments</td>
																														   </tr>
																														 <?php
																													 }
																												 ?>
																											 <?php
																												 $COM_COUNT++;
																											 }
																											 ?>

																											 <?php
																												 $q = Doctrine_Query::create()
																													->from('ApprovalCondition a')
																													->where('a.entry_id = ?', $application->getId());
																												 $conditions = $q->execute();
																												 if(sizeof($conditions) > 0)
																												 {
																													 $conditions_count++;
																												 }

																												 if($conditions_count > 0)
																												 {
																												 ?>

																											  <tr>
																												 <td style="background:#eeeeee; -webkit-border-radius: 4px 4px 0 0; -moz-border-radius: 4px 4px 0 0; border-radius: 4px 4px 0 0;"  colspan="2"><h3>Conditions Of Approval Summary</h3></td>
																											   </tr>
																												 <?php
																												 $q = Doctrine_Query::create()
																													->from('ApprovalCondition a')
																													->where('a.entry_id = ?', $application->getId());
																												 $conditions = $q->execute();
																												 if(sizeof($conditions) > 0)
																												 {
																													 $conditions_count++;
																												 ?>
																															 <?php
																															 foreach($conditions as $condition)
																															 {
																																	 $q = Doctrine_Query::create()
																																		->from('ConditionsOfApproval a')
																																		->where('a.id = ?', $condition->getConditionId());
																																	 $condition = $q->fetchOne();
																																	 if($condition){
																																				 ?>
																																				   <tr>
																																					 <td><strong><?php echo $condition->getShortName(); ?>:</strong></td>
																																					 <td><?php echo $condition->getDescription(); ?></td>
																																				   </tr>
																																				 <?php
																																	 }
																															 }
																															 ?>
																												 <?php
																												 }
																												 ?>

																												 <?php
																													 if($conditions_count <= 0)
																													 {
																														 ?>
																														 <tr>
																															 <td colspan="2">No Conditions</td>
																														 </tr>
																														 <?php
																													 }
																												 ?>
																											 <?php
																												 $COM_COUNT++;
																											 }
																											 ?>


																											 <?php
																													 $comment_count = 0;
																													 $q = Doctrine_Query::create()
																														->from('CfFormslot a');
																													 $slots = $q->execute();
																													 foreach($slots as $slot)
																													 {
																																	 $q = Doctrine_Query::create()
																																		->from('Conditions a')
																																		->where('a.circulation_id = ?', $application->getCirculationId())
																																		->andWhere('a.slot_id = ?', $slot->getNid());
																																	 $conditions = $q->execute();
																																	 if(sizeof($conditions) > 0)
																																	 {
																																		 $conditions_count++;
																																	 }
																													 }

																													 if($conditions_count > 0)
																													 {
																												 ?>



																											 <tr>
																												 <td style="background:#eeeeee; -webkit-border-radius: 4px 4px 0 0; -moz-border-radius: 4px 4px 0 0; border-radius: 4px 4px 0 0;"  colspan="2"><h3>Subject To Summary</h3></td>
																											   </tr>
																												 <?php
																													 $comment_count = 0;
																													 $q = Doctrine_Query::create()
																														->from('CfFormslot a');
																													 $slots = $q->execute();
																													 foreach($slots as $slot)
																													 {
																																	 $q = Doctrine_Query::create()
																																		->from('Conditions a')
																																		->where('a.circulation_id = ?', $application->getCirculationId())
																																		->andWhere('a.slot_id = ?', $slot->getNid());
																																	 $conditions = $q->execute();
																																	 if(sizeof($conditions) > 0)
																																	 {
																																		 $conditions_count++;
																																	 ?>
																																			 <tr>
																																				 <td style="background:#eeeeee; -webkit-border-radius: 4px 4px 0 0; -moz-border-radius: 4px 4px 0 0; border-radius: 4px 4px 0 0;"  colspan="2"><h5><?php echo $slot->getStrname(); ?></h5></td>
																																			   </tr>
																																				 <?php
																																				 foreach($conditions as $condition)
																																				 {
																																						 $q = Doctrine_Query::create()
																																							->from('CfInputfield a')
																																							->where('a.nid = ?', $condition->getFieldId());
																																						 $field = $q->fetchOne();
																																						 ?>
																																						   <tr>
																																							 <td><strong><?php echo $field->getStrname(); ?>:</strong></td>
																																							 <td><?php echo $condition->getConditionText(); ?></td>
																																						   </tr>
																																						 <?php
																																				 }
																																				 ?>
																																	 <?php
																																	 }
																													 }
																												 ?>

																												 <?php
																													 if($conditions_count <= 0)
																													 {
																														 ?>
																														 <tr>
																															 <td colspan="2">No Additional Conditions</td>
																														 </tr>
																														 <?php
																													 }
																												 ?>

																											 <?php
																												 $COM_COUNT++;
																											 }
																											 ?>
																												 </tbody>
																											 </table>
																											 */ ?>
					</div>
					<?php
					if (sizeof($application->getMfInvoice()) > 0) {
						?>
						<div id="tabs6" class="tab-pane">
							<?php
							foreach ($application->getMfInvoice() as $invoice) {
								?>


								<div class="panel-group panel-group-dark mb0" id="accordion1">
									<?php
									//Iterate through any invoices attached to this application
									$invcount = 0;

									$templateparser = new TemplateParser();

									foreach ($application->getMfInvoice() as $invoice) {
										//Display information about each invoice
										$invcount++;
										?>
										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title">
													<a data-toggle="collapse" class="collapsed" data-parent="#accordion1"
														href="#collapseTwo<?php echo $invoice->getId(); ?>">
														<?php echo __('Invoice'); ?> 			<?php echo $invcount; ?> (<?php
																			$expired = false;

																			$db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

																			$db_date_event = strtotime($db_date_event);

																			if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2")) {
																				$expired = true;
																			}

																			if ($expired) {
																				echo "Expired";
																			} else {
																				if ($invoice->getPaid() == "1") {
																					echo __("Pending");
																				} else if ($invoice->getPaid() == "15") {
																					echo "Pending Confirmation";
																				} elseif ($invoice->getPaid() == "2") {
																					echo __("Paid");
																				}
																			}
																			?>)
													</a>
												</h4>
											</div>
											<div id="collapseTwo<?php echo $invoice->getId(); ?>"
												class="panel-collapse collapse <?php if ($invcount == 1) { ?> in <?php } ?>">
												<div class="panel-body">

													<?php
													$q = Doctrine_Query::create()
														->from('Invoicetemplates a')
														->where("a.applicationform = ?", $application->getFormId());
													$invoicetemplate = $q->fetchOne();
													echo html_entity_decode($templateparser->parseInvoice($application->getId(), $application->getFormId(), $application->getEntryId(), $invoice->getId(), $invoicetemplate->getContent()));
													?>

													<div class="mb40"></div>

													<?php
													$query = "select * from " . MF_TABLE_PREFIX . "form_payments where form_id = ? and record_id = ? and `status` = 1";
													$params = array($application->getFormId(), $application->getEntryId());
													$sth = mf_do_query($query, $params, $dbh);

													$count = 0;
													while ($row = mf_do_fetch_result($sth)) {
														$count++;
														$paid = false;

														if (!empty($row)) {
															?>
															<h4><u>#<?php echo $count; ?> Payment Details</u></h4>

															<form class="form-bordered">
																<div class="form-group">
																	<label class="col-sm-4">
																		<i class="bold-label">
																			Date Of Payment
																		</i></label>
																	<div class="col-sm-8">
																		<?php echo $row['payment_date']; ?>
																	</div>
																</div>
																<div class="form-group">
																	<label class="col-sm-4">
																		<i class="bold-label">
																			Paid By
																		</i></label>
																	<div class="col-sm-8">
																		<?php echo $row['payment_fullname']; ?>
																	</div>
																</div>
																<div class="form-group">
																	<label class="col-sm-4">
																		<i class="bold-label">
																			Payment Status
																		</i></label>
																	<div class="col-sm-8">
																		<?php echo ucfirst($row['payment_status']); ?>
																	</div>
																</div>
																<div class="form-group">
																	<label class="col-sm-4">
																		<i class="bold-label">
																			Mode of Payment
																		</i></label>
																	<div class="col-sm-8">
																		<?php echo ucfirst($row['payment_merchant_type']); ?>
																	</div>
																</div>
																<div class="form-group">
																	<label class="col-sm-4">
																		<i class="bold-label">
																			Reference Number
																		</i></label>
																	<div class="col-sm-8">
																		<?php echo $row['payment_id']; ?>
																	</div>
																</div>
																<div class="form-group">
																	<label class="col-sm-4">
																		<i class="bold-label">
																			Amount Paid
																		</i></label>
																	<div class="col-sm-8">
																		<?php echo $row['payment_currency']; ?>
																		<?php echo $row['payment_amount']; ?>
																	</div>
																</div>
															</form>
															<?php
															$paid = true;
														}
													}
													?>

													<div class="text-right btn-invoice" style="padding-right: 10px;">
														<?php

														$list_print_urls = [];

														if ($invoice->getPaid() == 2 && !empty($invoice->getReceiptNumber())) {
															$receipt_data = $invoice->getReceiptNumber();

															$from_string_ids = trim($receipt_data);

															$receipt_ids = json_decode($from_string_ids, true);

															if (is_array($receipt_ids) && !empty($receipt_ids)) {
																$api_url = sfConfig::get('app_api_jambo_url');

																foreach ($receipt_ids as $key => $receipt_number) {
																	$my_string = "{$api_url}api/v1/print/receipt/{$receipt_number}/Physical_Planning";
																	array_push($list_print_urls, $my_string);
																}
															}

														}
														if (count($list_print_urls) > 0) {
															foreach ($list_print_urls as $key => $receipt_number) {
																$index += 1;
																?>
																<a title="Download Receipt" href="<?php echo $receipt_number ?>"
																	class="btn btn-primary"><i class="fas fa-file-download"></i>
																	<?php echo __(" Receipt - {$index}");
																	?>
																</a>
																<?php
															}
														} ?>
														<button class="btn btn-white" id="printinvoice" type="button"
															onClick="window.location='/index.php/invoices/printinvoice/id/<?php echo $invoice->getId(); ?>';"><i
																class="fa fa-print mr5"></i>
															<?php echo __('Print Invoice'); ?></button>
														<?php
														if ($invoice->getPaid() == 1) {
															?>
															<button class="btn btn-white" id="makepayment" type="button"
																onClick="window.location='/index.php/forms/payment?id=<?php echo $application->getFormId(); ?>&entryid=<?php echo $application->getEntryId(); ?>&invoiceid=<?php echo $invoice->getId(); ?>';"><i
																	class="fa fa-print mr5"></i>
																<?php echo __('Make Payment'); ?></button>
														<?php }
														if ($invoice->getPaid() == 15) {
															?>
															<button class="btn btn-white" id="makepayment" type="button"
																onClick="window.location='/index.php/forms/payment?id=<?php echo $application->getFormId(); ?>&entryid=<?php echo $application->getEntryId(); ?>&invoiceid=<?php echo $invoice->getId(); ?>';"><i
																	class="fa fa-print mr5"></i>
																<?php echo __('Add Payment'); ?></button>
														<?php } ?>
													</div>

													<div class="mb40"></div>

													<ul>

														<?php
														if ($paid == false) {
															//Iterate through all receipts attached to this invoice
															$count = 0;
															foreach ($invoice->getUploadReceipt() as $receipt) {
																$count++;
																echo "<li><font style='font-weight: 900;'>" . __('Receipt') . " " . $count . "</font><br> <a target='_blank' href='/index.php/invoices/viewreceipt?form_id=" . $receipt->getFormId() . "&id=" . $receipt->getEntryId() . "'>(" . __('View Receipt') . "</a></li>";
															}
														}
														?>

													</ul>

												</div>
											</div>
										</div>

										<?php

									}

									//If no invoices have been attached to this application
									if ($invcount == 0) {
										echo "<table class=\"table mb0\">
                                                          <tbody>
                                                          <tr>
                                                          <td><i class=\"bold-label\">" . __("No Records Found") . "</i></td>
                                                          </tr>
                                                          </tbody>
                                                          </table>
                                              ";
									}

									?>
								</div>

								<?php
							}
							?>
						</div>
						<?php
					}
					?>

					<?php
					$q = Doctrine_Query::create()
						->from("AttachedPermit a")
						->where("a.application_id = ?", $application->getId());
					$attachedpermit = $q->fetchOne();
					if ($attachedpermit) {
						?>
						<div id="tabs8" class="tab-pane">
							<form class="form-bordered form-horizontal">
								<?php
								$toggle = false;


								$permit_details = mf_get_entry_details($dbh, $attachedpermit->getFormId(), $attachedpermit->getEntryId(), $param);
								foreach ($permit_details as $data) {
									if ($data['element_type'] == "section") {
										?>
										<div class="form-group">
											<label class="col-sm-4"><i class="bold-label"><?php echo $data['label']; ?></i></label>
										</div>
										<?php
									} else {
										?>
										<div class="form-group">
											<label class="col-sm-2 control-label"><i
													class="bold-label"><?php echo $data['label']; ?></i></label>
											<div class="col-sm-8">
												<?php echo nl2br($data['value']); ?>
											</div>
										</div>
										<?php
									}
								}
								?>
							</form>
						</div>
						<?php
					}
					?>

					<div id="tabs7" class="tab-pane">
						<form action="/index.php/application/view/id/<?php echo $application->getId(); ?>" method="post"
							autocomplete="off" data-ajax="false">
							<?php

							$q = Doctrine_Query::create()
								->from('Communications a')
								->where('a.application_id = ?', $application->getId())
								->orderBy('a.id DESC');
							$communications = $q->execute();
							foreach ($communications as $communication) {
								$messages[] = $communication;
							}

							?>

							<?php
							if (sizeof($messages) <= 0) {
								?>
								<table class="table mb0">
									<tbody>
										<tr>
											<td>
												<i class="bold-label">No records found</i>
											</td>
										</tr>
									</tbody>
								</table>
								<?php
							} else {
								?>
								<?php foreach ($messages as $message) {

									if ($message->getArchitectId() != "") {
										$q = Doctrine_Query::create()
											->from('SfGuardUser a')
											->where('a.id = ?', $message->getArchitectId());
										$architect = $q->fetchOne();

										$fullname = $architect->getProfile()->getFullname();
									} else if ($message->getReviewerId() != "") {
										$message->setMessageRead("1");
										$message->save();
										$q = Doctrine_Query::create()
											->from('CfUser a')
											->where('a.nid = ?', $message->getReviewerId());
										$reviewer = $q->fetchOne();

										$fullname = $reviewer->getStrdepartment() . " - " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();
									}
									?>

									<div class="read-panel pl20 pr20">
										<div class="media">
											<div class="media-body">
												<span class="media-meta pull-right"></span>
												<h4 class="text-primary"><?php echo $fullname; ?> </h4>
												<small class="text-muted"><?php echo $message->getActionTimestamp(); ?></small>
											</div>
										</div><!-- media -->
										<?php echo $message->getContent(); ?>

										<?php
								} ?>
									<?php
							}
							?>
							</div><!-- read-panel -->
							<div class="mb20"></div>
							<div class="panel panel-default">
								<div class="panel-body pt0">
									<textarea name='txtmessage' id="wysiwyg" placeholder="Enter text here..."
										class="form-control" rows="10" data-autogrow="true"></textarea>
								</div>
							</div>
							<div class="panel-footer">
								<button type='submit' class="btn btn-primary">Reply </button>
							</div>
						</form>
					</div>

				</div>
			</div><!--Panel-body-->
		</div><!--Panel-dark-->

	</div><!-- /.row -->

</div><!-- /Content panel-->