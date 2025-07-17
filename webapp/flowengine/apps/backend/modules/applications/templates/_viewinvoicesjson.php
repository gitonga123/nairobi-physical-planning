<?php
/**
 * _viewinvoices.php partial.
 *
 * Displays any invoices attached to an application
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

//get form id and entry id
$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();
$return = array();
//Iterate through any invoices attached to this application
$invcount = 0;

$templateparser = new TemplateParser();


foreach($application->getMfInvoice() as $invoice)
{
	$data = array();
	//Display information about each invoice
	$invcount++;
	$str = __("Invoice") . ' ' . $invcount . '(';
	$expired = false;

	$db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

	$db_date_event = strtotime($db_date_event);

	if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3"))
	{
		$expired = true;
	}

	if($expired)
	{
		$str .= " Expired";
	}
	else
	{
		if($invoice->getPaid() == "1"){
			$str .= ' ' . __("Pending");
		}else if($invoice->getPaid() == "15"){
			$str .= ' ' . "Pending Confirmation";
		}elseif($invoice->getPaid() == "2"){
			$str .= ' ' . __("Paid");
		}elseif($invoice->getPaid() == "3"){
			$str .= ' ' . __("Cancelled");
		}
	}
	$str .= ')';

	if($invoice->getExpiresAt()){ $str .= " Expires on ".$invoice->getExpiresAt();
		$data['name'] = $str;
	}

	$q = Doctrine_Query::create()
			->from('Invoicetemplates a')
			->where("a.applicationform = ?", $application->getFormId());
	$invoicetemplate = $q->fetchOne();
	if(!$invoicetemplate)
	{
		$q = Doctrine_Query::create()
				->from('Invoicetemplates a');
		$invoicetemplate = $q->fetchOne();
	}

	$expired = false;
	$cancelled = false;

	$db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

	$db_date_event = strtotime($db_date_event);

	if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3"))
	{
		$expired = true;
	}
	$data['expired'] = $expired;

	if($invoice->getPaid() == "3")
	{
		$cancelled = true;
	}
	$data['cancelled'] = $cancelled;
	$data['html'] =  html_entity_decode($templateparser->parseInvoice($application->getId(), $application->getFormId(), $application->getEntryId(), $invoice->getId(), $invoicetemplate->getContent()));
	$data['mda_code'] = $invoice->getMdaCode();
	$data['service_code'] = $invoice->getServiceCode();
	$data['branch'] = $invoice->getBranch();
	$data['invoice_number'] = $invoice->getInvoiceNumber();
	$data['due_date'] = $invoice->getDueDate();
	$data['expires_at'] = $invoice->getExpiresAt();
	$data['gpayer_id'] = $invoice->getPayerId();
	$data['payer_name'] = $invoice->getPayerName();
	$data['total_amount'] = $invoice->getTotalAmount();
	$data['currency'] = $invoice->getCurrency();
	$data['doc_ref_number'] = $invoice->getDocRefNumber();

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$query = "select * from ".MF_TABLE_PREFIX."form_payments where form_id = ? and record_id = ? and `status` = 1";
	$params = array($application->getFormId(),$application->getEntryId());
	$sth = mf_do_query($query,$params,$dbh);
	$count = 0;
	$found_ref = false;
	while($row = mf_do_fetch_result($sth))
	{
		$count++;
		if(!empty($row)){
			$paid = true;
			$found_ref = true;
			$data['payments'] = $row;
		}
	}
	if($found_ref == false)
	{
		$data['ref_num'] = $application->getFormId()."/".$application->getEntryId()."/".sizeof($application->getMfInvoice());
	}
	$return[] = $data;
}

return $return;