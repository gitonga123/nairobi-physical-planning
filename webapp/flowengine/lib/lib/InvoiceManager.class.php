<?php

/**
 *
 * Invoicing class that will manage the creation and modification of invoices
 *
 * Created by PhpStorm.
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 12:28 AM
 */

use jlawrence\eos\Parser;
use Dompdf\Dompdf;

class InvoiceManager
{

    //Public constructor for the invoice manager class
    public function __construct()
    {
    }

    //output invoice to html
    public function generate_invoice_template($invoice_id, $pdf = false)
    {
        $templateparser = new TemplateParser();

        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $invoice = $q->fetchOne();

        //get application, if its in payment confirmation then move to submissions
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $invoice->getAppId())
            ->limit(1);
        $application = $q->fetchOne();
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.id = ?", $invoice->getTemplateId());
        $invoicetemplate = $q->fetchOne();

        $html = "<html>
			<body>
			";

        $expired = false;
        $cancelled = false;

        $db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

        $db_date_event = strtotime($db_date_event);

        if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3")) {
            $expired = true;
        }

        if ($invoice->getPaid() == "3") {
            $cancelled = true;
        }

        $invoice_content = $templateparser->parseInvoice($application->getId(), $application->getFormId(), $application->getEntryId(), $invoice->getId(), $invoicetemplate->getContent());

        if ($pdf) {
            $ssl_suffix = "s";

            if (empty($_SERVER['HTTPS'])) {
                $ssl_suffix = "";
            }

            //replace src=" for images with src="http://localhost
            $invoice_content = str_replace('src="', 'src="http' . $ssl_suffix . '://' . $_SERVER['HTTP_HOST'] . '/', $invoice_content);
        }

        $html .= $invoice_content;

        $html .= "
			</body>
			</html>";
        return html_entity_decode($html);
    }

    //output invoice to pdf
    public function save_to_pdf($invoice_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $invoice = $q->fetchOne();

        $html = $this->generate_invoice_template($invoice_id, true);
        #error_log('----------HTML------'.$html);
        #require_once(dirname(__FILE__)."/vendor/dompdf/dompdf_config.inc.php");

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', TRUE);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream($invoice->getInvoiceNumber() . ".pdf");
    }

    //Validate request from external API
    public function api_validate_request($api_key, $api_secret)
    {
        $q = Doctrine_Query::create()
            ->from("InvoiceApiAccount a")
            ->where("a.api_key = ? and a.api_secret = ?", array($api_key, $api_secret));
        $mdas = $q->count();

        if ($mdas > 0) {
            $mda = $q->fetchOne();
            error_log("Invoice API: Valid Request From " . $mda->getMdaName() . " - " . $mda->getMdaBranch());
            return true;
        } else {
            error_log("Invoice API: Bad Request For " . $api_key . " - " . $api_secret);
            return false;
        }
    }

    //Validate request from external API
    public function api_fetch_mda($api_key, $api_secret)
    {
        $q = Doctrine_Query::create()
            ->from("InvoiceApiAccount a")
            ->where("a.api_key = ?", $api_key)
            ->limit(1);
        $mda = $q->fetchOne();

        return $mda;
    }

    //Get an invoice from the transaction id/reference no
    public function get_invoice_by_reference($reference)
    {
        $exploded = explode('/', $reference);
        $form_id           = (int) $exploded[0];
        $entry_id          = (int) $exploded[1];
        $invoice_id          = (int) $exploded[2];

        if ($invoice_id != "1") {
            $q = Doctrine_Query::create()
                ->from("MfInvoice a")
                ->where("a.id = ?", $invoice_id)
                ->orderBy("a.id DESC")
                ->limit(1);
            $invoice = $q->fetchOne();

            if ($invoice) {
                return $invoice;
            } else {
                $q = Doctrine_Query::create()
                    ->from("MfInvoice a")
                    ->leftJoin("a.FormEntry b")
                    ->where("b.form_id = ? AND b.entry_id = ?", array($form_id, $entry_id))
                    ->orderBy("a.id DESC")
                    ->limit(1);
                $invoice = $q->fetchOne();

                if ($invoice) {
                    return $invoice;
                } else {
                    return false;
                }
            }
        } else {
            $q = Doctrine_Query::create()
                ->from("MfInvoice a")
                ->leftJoin("a.FormEntry b")
                ->where("b.form_id = ? AND b.entry_id = ?", array($form_id, $entry_id))
                ->orderBy("a.id DESC")
                ->limit(1);
            $invoice = $q->fetchOne();

            if ($invoice) {
                return $invoice;
            } else {
                return false;
            }
        }
    }

    //Query for the total collected from a service for a specific day
    public function api_query_daily_total($api_key, $api_secret, $service_id, $date)
    {
        $query_details = array();

        //check if api_key matches the one set in the config files
        if ($this->api_validate_request($api_key, $api_secret)) {
            //Get Institution Name
            $mda = $this->api_fetch_mda($api_key, $api_secret);

            error_log("Invoice Query API: Daily Query request from " . $mda->getMdaName() . " - " . $mda->getMdaBranch() . " on " . $invoice_no);

            $qt = Doctrine_Query::create()
                ->select('SUM(b.amount) as total')
                ->from('MfInvoice a')
                ->leftJoin('a.FormEntry c')
                ->leftJoin('a.mfInvoiceDetail b')
                ->where('b.description LIKE ? OR b.description LIKE ?', array("%Total%", "%submission fee%"))
                ->andWhere('a.paid = ?', 2)
                ->andWhere('c.approved <> 0')
                ->andWhere('c.form_id = ?', $service_id)
                ->andWhere('a.updated_at LIKE ?', "%" . $date . "%")
                ->orderBy('a.id DESC');
            $total = $qt->fetchOne();

            $query_details['total'] = $total->getTotal();
        }

        return $query_details;
    }

    //Query for the total collected from a service for a specific day
    public function api_query_period_total($api_key, $api_secret, $service_id, $from_date, $to_date)
    {
        $query_details = array();

        //check if api_key matches the one set in the config files
        if ($this->api_validate_request($api_key, $api_secret)) {
            //Get Institution Name
            $mda = $this->api_fetch_mda($api_key, $api_secret);

            error_log("Invoice Query API: Period Query request from " . $mda->getMdaName() . " - " . $mda->getMdaBranch() . " on " . $invoice_no);

            $qt = Doctrine_Query::create()
                ->select('SUM(b.amount) as total')
                ->from('MfInvoice a')
                ->leftJoin('a.FormEntry c')
                ->leftJoin('a.mfInvoiceDetail b')
                ->where('b.description LIKE ? OR b.description LIKE ?', array("%Total%", "%submission fee%"))
                ->andWhere('a.paid = ?', 2)
                ->andWhere('c.approved <> 0')
                ->andWhere('c.form_id = ?', $service_id)
                ->andWhere('a.updated_at BETWEEN ? AND ?', array($from_date, $to_date))
                ->orderBy('a.id DESC');
            $total = $qt->fetchOne();

            $query_details['total'] = $total->getTotal();
        }

        return $query_details;
    }

    //Query invoice details from external API
    public function api_query_invoice($api_key, $api_secret, $invoice_no, $merchant_identifier = '')
    {
        $query_details = array();

        //check if api_key matches the one set in the config files
        if ($this->api_validate_request($api_key, $api_secret)) {
            //Get Institution Name
            $mda = $this->api_fetch_mda($api_key, $api_secret);

            error_log("Invoice Query API: Query request from " . $mda->getMdaName() . " - " . $mda->getMdaBranch() . " on " . $invoice_no);

            $invoice = $this->get_invoice_by_invoice_number($invoice_no);

            if (strlen($merchant_identifier)) {
                //check if merchant identifier match
                if (strcmp($invoice->getFormEntry()->getMerchantIdentifier(), $merchant_identifier) !== 0) {
                    //failed merchant identification
                    $query_details['status'] = '01';
                    $query_details['message'] = 'Invalid plan id';
                    $query_details['data'] = [];
                    return $query_details;
                }
            }

            if ($invoice) {
                $application = $invoice->getFormEntry();

                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId())
                    ->limit(1);
                $user = $q->fetchOne();

                /**
                 * Send back the following details:
                 *  - invoice_number
                 *  - total_amount
                 *  - invoice_status
                 *  - date_of_invoice
                 *  - application_id
                 *  - user's email
                 *  - user's mobile
                 *  - user's name
                 * */

                $query_details['data']['invoice_number'] = $invoice->getInvoiceNumber();
                $query_details['data']['total_amount'] = $invoice->getTotalAmount();
                $query_details['data']['currency'] = $invoice->getCurrency();
                $query_details['data']['invoice_status'] = $this->is_invoice_expired($invoice->getId()) ? "Expired" : $invoice->getStatus();
                $query_details['data']['invoice_date'] = date('c', strtotime($invoice->getCreatedAt()));
                $query_details['data']['application_id'] = $application->getApplicationId();
                $query_details['data']['plan_id'] = $application->getMerchantIdentifier();
                $query_details['data']['user_email'] = $user->getEmail();
                $query_details['data']['user_mobile'] = $user->getMobile();
                $query_details['data']['user_fullname'] = $user->getFullname();
                $query_details['status'] = "00";
                $query_details['message'] = "Success";
            } else {
                //failed invoice
                $query_details['status'] = '01';
                $query_details['message'] = 'Invalid invoice no';
                $query_details['data'] = [];
            }
        } else {
            //failed validation
            $query_details['status'] = '01';
            $query_details['message'] = 'Invalid API key/API secret';
            $query_details['data'] = [];
        }

        return $query_details;
    }

    //Query invoice details from external API
    public function api_update_invoice($api_key, $api_secret, $invoice_no, $transaction_details)
    {
        $update_details = array();

        $transaction_id = $transaction_details['transaction_id'];
        $transaction_date = $transaction_details['transaction_date'];
        $transaction_status = $transaction_details['transaction_status'];
        $amount_paid = $transaction_details['amount_paid'];
        $paid_by = $transaction_details['paid_by'];

        if (empty($transaction_id)) {
            $update_details['update_status'] = "invalid transaction id";
            return $update_details;
        }

        if (empty($amount_paid)) {
            $update_details['update_status'] = "invalid transaction amount";
            return $update_details;
        }

        $invoice_manager = new InvoiceManager();

        //check if api_key matches the one set in the config files
        if ($invoice_manager->api_validate_request($api_key, $api_secret)) {
            //Get Institution Name
            $mda = $invoice_manager->api_fetch_mda($api_key, $api_secret);

            $invoice = $this->get_invoice_by_reference($transaction_id);

            if ($invoice && $invoice->getPaid() != 2) {
                $application = $invoice->getFormEntry();

                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId())
                    ->limit(1);
                $user = $q->fetchOne();

                $update_details['invoice_number'] = $invoice->getInvoiceNumber();

                foreach ($invoice->getMfInvoiceDetail() as $detail) {
                    if ($detail->getDescription() == "Total") {
                        $update_details['total_amount'] = $detail->getAmount();
                        $update_details['currency'] = "KES";
                    }
                }

                if ($invoice->getPaid() == 1) {
                    $update_details['invoice_status'] = "pending";
                } elseif ($invoice->getPaid() == 15) {
                    $update_details['invoice_status'] = "pending confirmation";
                } elseif ($invoice->getPaid() == 2) {
                    $update_details['invoice_status'] = "paid";
                } elseif ($invoice->getPaid() == 3) {
                    $update_details['invoice_status'] = "cancelled";
                }

                $update_details['date_of_invoice'] = $invoice->getCreatedAt();
                $update_details['application_id'] = $application->getApplicationId();
                $update_details['user_email'] = $user->getEmail();
                $update_details['user_mobile'] = $user->getMobile();
                $update_details['user_fullname'] = $user->getFullname();

                //insert transaction details
                $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
                mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);

                $sql = "INSERT INTO `ap_form_payments` (`form_id`, `record_id`, `payment_id`, `date_created`, `payment_date`, `payment_status`, `payment_fullname`, `payment_amount`, `payment_currency`, `payment_test_mode`, `payment_merchant_type`, `status`, `billing_street`, `billing_city`, `billing_state`, `billing_zipcode`, `billing_country`, `same_shipping_address`, `shipping_street`, `shipping_city`, `shipping_state`, `shipping_zipcode`, `shipping_country`) VALUES ('" . $application->getFormId() . "', '" . $application->getEntryId() . "', '" . $transaction_id . "', '" . date("Y-m-d H:i:s") . "', '" . $transaction_date . "', '" . $transaction_status . "', '" . $paid_by . "', '" . $amount_paid . "', 'KES', '0', '" . $mda->getMdaName() . " - " . $mda->getMdaBranch() . "', '" . $transaction_status . "', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL);";
                mysql_query($sql, $dbconn);

                //update invoice
                if (doubleval($amount_paid) >= doubleval($update_details['total_amount']) && ($transaction_status == "completed" || $transaction_status == "paid")) {
                    $invoice->setPaid(2);
                    $invoice->save();

                    $update_details['invoice_status'] = "paid";
                    $update_details['update_status'] = "success";
                } else {
                    if (!($transaction_status == "completed" || $transaction_status == "paid")) {
                        if ($transaction_status == "cancelled" || $transaction_status == "failed") {
                            $invoice->setPaid(3);
                            $invoice->save();

                            //Cancel any generated permits
                            $permits = $application->getSavedPermits();
                            foreach ($permits as $permit) {
                                $permit->delete();
                            }
                        }
                    } else {
                        $update_details['update_status'] = "invalid amount";
                    }
                }

                //return invoice details

                /**
                 * Send back the following details:
                 *  - invoice_number
                 *  - total_amount
                 *  - invoice_status
                 *  - date_of_invoice
                 *  - application_id
                 *  - user's email
                 *  - user's mobile
                 *  - user's name
                 * */
            } else {
                if ($invoice && $invoice->getPaid() == 2) {
                    $update_details['update_status'] = "already paid";
                } else {
                    $update_details['update_status'] = "invalid invoice";
                }
            }
        } else {
            $update_details['update_status'] = "unauthorized";
        }

        return $update_details;
    }

    //Confirm transaction details from external API
    public function api_confirm_invoice($api_key, $api_secret, $invoice_no, $transaction_details)
    {
        $update_details = array();

        $transaction_id = $transaction_details['transaction_id'];
        $transaction_date = $transaction_details['transaction_date'];
        $transaction_status = $transaction_details['transaction_status'];
        $amount_paid = $transaction_details['amount_paid'];
        $paid_by = $transaction_details['paid_by'];

        if (empty($transaction_id)) {
            $update_details['update_status'] = "invalid transaction id";
            return $update_details;
        }

        if (empty($amount_paid)) {
            $update_details['update_status'] = "invalid transaction amount";
            return $update_details;
        }

        $invoice_manager = new InvoiceManager();

        //check if api_key matches the one set in the config files
        if ($invoice_manager->api_validate_request($api_key, $api_secret)) {
            //Get Institution Name
            $mda = $invoice_manager->api_fetch_mda($api_key, $api_secret);

            $invoice = $this->get_invoice_by_reference($transaction_id);

            if ($invoice) {
                $application = $invoice->getFormEntry();

                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId())
                    ->limit(1);
                $user = $q->fetchOne();

                $update_details['invoice_number'] = $invoice->getInvoiceNumber();

                foreach ($invoice->getMfInvoiceDetail() as $detail) {
                    if ($detail->getDescription() == "Total") {
                        $update_details['total_amount'] = $detail->getAmount();
                        $update_details['currency'] = "KES";
                    }
                }

                if ($invoice->getPaid() == 1) {
                    $update_details['invoice_status'] = "pending";
                } elseif ($invoice->getPaid() == 15) {
                    $update_details['invoice_status'] = "pending confirmation";
                } elseif ($invoice->getPaid() == 2) {
                    $update_details['invoice_status'] = "paid";
                } elseif ($invoice->getPaid() == 3) {
                    $update_details['invoice_status'] = "cancelled";
                }

                $update_details['date_of_invoice'] = $invoice->getCreatedAt();
                $update_details['application_id'] = $application->getApplicationId();
                $update_details['user_email'] = $user->getEmail();
                $update_details['user_mobile'] = $user->getMobile();
                $update_details['user_fullname'] = $user->getFullname();

                //update invoice
                if (doubleval($amount_paid) >= doubleval($update_details['total_amount']) && ($transaction_status == "completed" || $transaction_status == "paid")) {
                    $db_connection = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
                    mysql_select_db(sfConfig::get('app_mysql_db'), $db_connection);

                    $sql = "UPDATE mf_invoice SET remote_validate = 1 WHERE id = " . $invoice->getId();
                    $result = mysql_query($sql, $db_connection);

                    $update_details['invoice_status'] = "paid";
                    $update_details['update_status'] = "success";
                } else {
                    if (!($transaction_status == "completed" || $transaction_status == "paid")) {
                        if ($transaction_status == "cancelled" || $transaction_status == "failed") {
                            $db_connection = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
                            mysql_select_db(sfConfig::get('app_mysql_db'), $db_connection);

                            $sql = "UPDATE mf_invoice SET remote_validate = 1 WHERE id = " . $invoice->getId();
                            $result = mysql_query($sql, $db_connection);

                            //Cancel any generated permits
                            $permits = $application->getSavedPermits();
                            foreach ($permits as $permit) {
                                $permit->delete();
                            }
                        }
                    } else {
                        $update_details['update_status'] = "invalid amount";
                    }
                }

                //return invoice details

                /**
                 * Send back the following details:
                 *  - invoice_number
                 *  - total_amount
                 *  - invoice_status
                 *  - date_of_invoice
                 *  - application_id
                 *  - user's email
                 *  - user's mobile
                 *  - user's name
                 * */
            } else {
                $update_details['update_status'] = "invalid invoice";
            }
        } else {
            $update_details['update_status'] = "unauthorized";
        }

        return $update_details;
    }

    //Generate a new invoice on submission using form payment settings
    public function create_invoice_from_submission($application_id)
    {
        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";

        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        require_once($prefix_folder . 'includes/language.php');
        require_once($prefix_folder . 'includes/common-validator.php');
        require_once($prefix_folder . 'includes/view-functions.php');
        require_once($prefix_folder . 'includes/theme-functions.php');
        require_once($prefix_folder . 'includes/post-functions.php');

        $dbh         = mf_connect_db();

        $submission = $this->get_application_by_id($application_id);

        //Create invoice
        //Note ADD per stage
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ? and a.applicationstage = ?", array($submission->getFormId(), $submission->getApproved()));
        $invoicetemplate = $q->fetchOne();
        if (!$invoicetemplate) {
            $q = Doctrine_Query::create()
                ->from('Invoicetemplates a')
                ->where("a.applicationform = ?", $submission->getFormId())
                ->limit(1);
            $invoicetemplate = $q->fetchOne();
        }
        if ($invoicetemplate) {

            $invoice = new MfInvoice();
            $invoice->setAppId($submission->getId());

            if ($invoicetemplate->getInvoiceNumber()) {
                $q = Doctrine_Query::create()
                    ->from('MfInvoice a')
                    ->where("a.template_id = ?", $invoicetemplate->getId())
                    ->orderBy("a.id DESC")
                    ->limit(1);
                $lastinvoice = $q->fetchOne();

                if ($lastinvoice) {
                    $invoice_number = $lastinvoice->getInvoiceNumber();
                    $invoice_number++;
                    $invoice->setInvoiceNumber($invoice_number);
                } else {
                    $invoice->setInvoiceNumber($invoicetemplate->getInvoiceNumber());
                }

                $invoice->setTemplateId($invoicetemplate->getId());
            } else {
                $invoice->setInvoiceNumber("INV-" . $submission->getId());
            }

            $invoice->setPaid(1);
            $invoice->setCreatedAt(date("Y-m-d H:i:s"));
            $invoice->setUpdatedAt(date("Y-m-d H:i:s"));
            // option to set invoice as not approved 
            $invoice->setInvoiceApproved(0); // This will set the invoice as not approved and allow us to control access to "Pay Now" option on an Invoice after generation.

            $invoice->save();

            //Get total paid and seperate service fee from amount
            $grand_total = 0;
            $service_fee = 0;

            $query = "select
                        form_code,
                        payment_currency,
                        payment_price_type,
                        payment_price_amount,
                        payment_enable_tax,
                        payment_tax_rate,
                        payment_discount_code,
                        payment_tax_amount,
						payment_price_name,
						form_name
                        from
                           `ap_forms`
                       where
                          form_id=?";

            $params = array($submission->getFormId());

            $sth = mf_do_query($query, $params, $dbh);
            $row = mf_do_fetch_result($sth);

            $payment_currency = $row['payment_currency'];
            $payment_price_type = $row['payment_price_type'];
            $payment_price_amount = (float)$row['payment_price_amount'];

            $payment_enable_tax = (int)$row['payment_enable_tax'];
            $payment_tax_rate = (float)$row['payment_tax_rate'];
            $payment_tax_amount = (float)$row['payment_tax_amount'];
            $payment_tax_code = (float)$row['payment_discount_code'];
            $payment_price_name = $row['payment_price_name'];
            $form_name = $row['form_name'];

            $service_code = $row['form_code'];

            //make sure the amount paid match or larger
            $payment_amount = "";
            if ($payment_price_type == 'variable') {
                $payment_amount = (float)mf_get_payment_total($dbh, $submission->getFormId(), $submission->getEntryId(), 0, 'live');
            } else if ($payment_price_type == 'fixed') {
                $payment_amount = (float)$payment_price_amount;
            }

            $total_amount = $payment_amount;

            if (!empty($payment_enable_tax)) {
                if ($payment_tax_amount) {
                    $service_fee = $payment_tax_amount;
                    $total_amount = $total_amount + $service_fee;
                } else {
                    $before_total = ($total_amount * 100) / (100 + $payment_tax_rate);
                    $service_fee = round(($total_amount - $before_total)); //round to 2 digits decimal
                    $total_amount = $before_total;
                }
            }

            if ($service_fee > 0) {
                $invoicedetail = new MfInvoiceDetail();
                $invoicedetail->setInvoiceId($invoice->getId());
                if ($payment_tax_code) {
                    $invoicedetail->setDescription($payment_tax_code . " : Convenience fee");
                } else {
                    $invoicedetail->setDescription("Convenience fee");
                }
                $invoicedetail->setAmount($service_fee);
                $invoicedetail->setCreatedAt(date("Y-m-d H:i:s"));
                $invoicedetail->setUpdatedAt(date("Y-m-d H:i:s"));
                $invoicedetail->save();
                $grand_total += $service_fee;
            }

            $invoicedetail = new MfInvoiceDetail();
            $invoicedetail->setInvoiceId($invoice->getId());
            if ($service_code && !strlen($payment_price_name)) {
                $invoicedetail->setDescription($service_code . " : Application fee");
            } else {
                if (strlen($payment_price_name)) {
                    $invoicedetail->setDescription($payment_price_name . " : Application fee");
                } elseif (strlen($form_name)) {
                    $invoicedetail->setDescription($form_name . " : Application fee");
                } else {
                    $invoicedetail->setDescription("Application fee");
                }
            }
            $invoicedetail->setAmount($payment_amount);
            $invoicedetail->setCreatedAt(date("Y-m-d H:i:s"));
            $invoicedetail->setUpdatedAt(date("Y-m-d H:i:s"));
            $invoicedetail->save();

            // check if there other submission fees for this particular form
            // get a list of all submission fees
            $q = Doctrine_Query::create()
                ->from('fee a')
                ->leftJoin('a.Invoicetemplates b')
                ->andWhere("a.invoiceid = b.id")
                ->Where('a.submission_fee = ?', true)
                ->andWhere('b.applicationform = ?', $submission->getFormId());
            $otherFees = $q->execute();
            if ($otherFees) {
                $query = "SELECT * FROM ap_form_" . $submission->getFormId() . " WHERE id = '" . $submission->getEntryId() . "'";

                $application_form = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll();
                $fee_amount = 0;
                foreach ($otherFees as $fee) {
                    $amount_found = $this->getFeeAmount($fee, $application_form[0], $submission->getFormId());
                    $fee_amount = $fee_amount + $amount_found;
                    if ($amount_found > 0) {
                        $invoicedetail = new MfInvoiceDetail();
                        $invoicedetail->setInvoiceId($invoice->getId());
                        $invoicedetail->setDescription($fee->getFeeCode() . " :" . $fee->getDescription());
                        $invoicedetail->setAmount($amount_found);
                        $invoicedetail->setCreatedAt(date("Y-m-d H:i:s"));
                        $invoicedetail->setUpdatedAt(date("Y-m-d H:i:s"));
                        $invoicedetail->save();
                    }

                    $amount_found = 0;
                }

                $payment_amount = $payment_amount + $fee_amount;
            }

            $grand_total += $payment_amount;


            $invoice->setMdaCode(sfConfig::get('app_mda_code'));
            $invoice->setBranch(sfConfig::get('app_branch'));

            $due_date = date("Y-m-d H:i:s");
            $expires_at = date("Y-m-d H:i:s");

            if ($invoicetemplate->getMaxDuration()) {
                $date = strtotime("+" . $invoicetemplate->getMaxDuration() . " day", time());
                $expires_at = date('Y-m-d', $date);
            } else if ($invoicetemplate->getDueDuration()) {
                $date = strtotime("+" . $invoicetemplate->getDueDuration() . " day", time());
                $expires_at = date('Y-m-d', $date);
            } else {
                $date = strtotime("+" . 30 . " day", time());
                $expires_at = date('Y-m-d', $date);
            }

            $q = Doctrine_Query::create()
                ->from('SfGuardUser a')
                ->where('a.id = ?', $submission->getUserId())
                ->limit(1);
            $sf_user = $q->fetchOne();

            $invoice->setDueDate($due_date);
            $invoice->setExpiresAt($expires_at);
            $invoice->setPayerId($sf_user->getUsername());
            $invoice->setPayerName($sf_user->getProfile()->getFullname());
            $invoice->setDocRefNumber($submission->getApplicationId());
            $invoice->setCurrency($payment_currency);
            $invoice->setServiceCode($service_code);
            $invoice->setTotalAmount($grand_total);
            $invoice->save();

            //post invoice
            //check if merchant enabled
            if ($submission->getForm() && $submission->getForm()->getPaymentEnableMerchant()) {
                //Post invoice
                $api = new ApiCalls();
                $api->postInvoice($submission, $invoice, true);
            }
            return $invoice;
        }
    }

    //Generate a new invoice on submission using form payment settings
    public function create_invoice_from_different($application_id, $difference_total)
    {
        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');
        require_once($prefix_folder . 'includes/check-session.php');

        require_once($prefix_folder . 'includes/language.php');
        require_once($prefix_folder . 'includes/entry-functions.php');
        require_once($prefix_folder . 'includes/post-functions.php');
        require_once($prefix_folder . 'includes/users-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $submission = $this->get_application_by_id($application_id);

        $unpaid_invoice = $this->has_unpaid_invoice($application_id);

        //Create invoice
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ?", $submission->getFormId())
            ->limit(1);
        $invoicetemplate = $q->fetchOne();

        if ($invoicetemplate && $unpaid_invoice == false) {

            $invoice = new MfInvoice();
            $invoice->setAppId($submission->getId());

            if ($invoicetemplate->getInvoiceNumber()) {
                $q = Doctrine_Query::create()
                    ->from('MfInvoice a')
                    ->where("a.template_id = ?", $invoicetemplate->getId())
                    ->orderBy("a.id DESC")
                    ->limit(1);
                $lastinvoice = $q->fetchOne();

                if ($lastinvoice) {
                    $invoice_number = $lastinvoice->getInvoiceNumber();
                    $invoice_number++;
                    $invoice->setInvoiceNumber($invoice_number);
                } else {
                    $invoice->setInvoiceNumber($invoicetemplate->getInvoiceNumber());
                }

                $invoice->setTemplateId($invoicetemplate->getId());
            } else {
                $invoice->setInvoiceNumber("INV-" . $submission->getId());
            }

            $invoice->setPaid(1);
            $invoice->setCreatedAt(date("Y-m-d H:i:s"));
            $invoice->setUpdatedAt(date("Y-m-d H:i:s"));

            $invoice->save();

            //Get total paid and seperate service fee from amount
            $grand_total = 0;
            $service_fee = 0;

            $query = "select
                        form_code,
                        payment_currency,
                        payment_price_type,
                        payment_price_amount,
                        payment_enable_tax,
                        payment_tax_rate,
                        payment_discount_code,
                        payment_tax_amount
                        from
                           `ap_forms`
                       where
                          form_id=?";

            $params = array($submission->getFormId());

            $sth = mf_do_query($query, $params, $dbh);
            $row = mf_do_fetch_result($sth);

            $payment_currency = $row['payment_currency'];
            $payment_price_type = $row['payment_price_type'];

            $service_code = $row['form_code'];

            //make sure the amount paid match or larger
            $payment_amount = "";
            if ($payment_price_type == 'variable') {
                $payment_amount = (float)mf_get_payment_total($dbh, $submission->getFormId(), $submission->getEntryId(), 0, 'live');
            } else if ($payment_price_type == 'fixed') {
                $payment_amount = (float)$payment_price_amount;
            }

            $total_amount = $difference_total;

            $invoicedetail = new MfInvoiceDetail();
            $invoicedetail->setInvoiceId($invoice->getId());
            if ($service_code) {
                $invoicedetail->setDescription($service_code . " - Submission fee");
            } else {
                /*$q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_id = ?", $submission->getFormId())
                    ->limit(1);
                $form = $q->fetchOne();
                if ($form) {
                    $invoicedetail->setDescription($form->getFormName() . " - Submission fee");
                } else {
                    $invoicedetail->setDescription("Submission fee");
                }*/
                //Note ADD
                $application_manager = new ApplicationManager();
                $menu = Doctrine_Query::create()
                    ->from('Menus m')
                    ->where('m.service_form = ? and m.service_type = ?', array($submission->getFormId(), 2))
                    ->fetchOne();

                $field_data = $application_manager->get_field_data($submission->getFormId(), $submission->getEntryId(), $menu->getServiceFeeField());
                //First check the main service fee configured
                $q = Doctrine_Query::create()
                    ->from("ApElementOptions a")
                    ->where("a.form_id = ?", $menu->getServiceForm())
                    ->andWhere("a.element_id = ?", $menu->getServiceFeeField())
                    ->andWhere("a.option_id = ?", $field_data)
                    ->andWhere("a.live = 1")
                    ->orderBy("a.option_text ASC");
                $element_option = $q->fetchOne();

                if ($element_option) {
                    $invoicedetail->setDescription($element_option->getOptionText());
                } else {
                    $invoicedetail->setDescription("Top up fee");
                }
            }
            $invoicedetail->setAmount($total_amount);
            $invoicedetail->setCreatedAt(date("Y-m-d H:i:s"));
            $invoicedetail->setUpdatedAt(date("Y-m-d H:i:s"));
            $invoicedetail->save();

            $invoice->setMdaCode(sfConfig::get('app_mda_code'));
            $invoice->setBranch(sfConfig::get('app_branch'));

            $due_date = date("Y-m-d H:i:s");
            $expires_at = date("Y-m-d H:i:s");

            if ($invoicetemplate->getMaxDuration()) {
                $date = strtotime("+" . $invoicetemplate->getMaxDuration() . " day", time());
                $expires_at = date('Y-m-d', $date);
            } else if ($invoicetemplate->getDueDuration()) {
                $date = strtotime("+" . $invoicetemplate->getDueDuration() . " day", time());
                $expires_at = date('Y-m-d', $date);
            } else {
                $date = strtotime("+" . 30 . " day", time());
                $expires_at = date('Y-m-d', $date);
            }

            $q = Doctrine_Query::create()
                ->from('SfGuardUser a')
                ->where('a.id = ?', $submission->getUserId())
                ->limit(1);
            $sf_user = $q->fetchOne();

            $invoice->setDueDate($due_date);
            $invoice->setExpiresAt($expires_at);
            $invoice->setPayerId($sf_user->getUsername());
            $invoice->setPayerName($sf_user->getProfile()->getFullname());
            $invoice->setDocRefNumber($submission->getApplicationId());
            $invoice->setCurrency($payment_currency);
            $invoice->setServiceCode($service_code);
            $invoice->setTotalAmount($total_amount);
            $invoice->save();

            return $invoice->getId();
        }
    }

    //Generate a new invoice from invoicing task
    public function create_invoice_from_task($application_id, $descriptions, $amounts, $task = false)
    {
        $descriptions1 = $descriptions;
        $amounts1 = $amounts;

        $currency = sfConfig::get('app_currency');

        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $submission = $this->get_application_by_id($application_id);
        //Note ADD per stage
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ? and a.applicationstage = ?", array($submission->getFormId(), $submission->getApproved()));
        $invoicetemplate = $q->fetchOne();
        if (!$invoicetemplate) {
            $q = Doctrine_Query::create()
                ->from('Invoicetemplates a')
                ->where("a.applicationform = ?", $submission->getFormId())
                ->limit(1);
            $invoicetemplate = $q->fetchOne();
        }

        $invoice = new MfInvoice();
        $invoice->setAppId($submission->getId());

        if ($invoicetemplate->getInvoiceNumber()) {
            $q = Doctrine_Query::create()
                ->from('MfInvoice a')
                ->where("a.template_id = ?", $invoicetemplate->getId())
                ->orderBy("a.id DESC")
                ->limit(1);
            $lastinvoice = $q->fetchOne();
            $$invoice_number = '';

            //incase of update of invoice number
            if ($lastinvoice && strpos($lastinvoice->getInvoiceNumber(), substr($invoicetemplate->getInvoiceNumber(), 0, 3)) !== false) {
                $invoice_number = $lastinvoice->getInvoiceNumber();
                $invoice_number++;
                $invoice->setInvoiceNumber($invoice_number);
            } else {
                $invoice->setInvoiceNumber($invoicetemplate->getInvoiceNumber());
            }

            $invoice->setTemplateId($invoicetemplate->getId());
        } else {
            $invoice->setInvoiceNumber("NKR-INV-" . $submission->getId());
        }

        $invoice->setCreatedAt(date("Y-m-d H:i:s"));
        $invoice->setUpdatedAt(date("Y-m-d H:i:s"));

        $invoice->setPaid(1);
        $invoice->save();


        $grand_total = 0;

        $index = 0;



        $query  = "select
                form_code,
                payment_currency,
                payment_price_type,
                payment_price_amount,
                payment_enable_tax,
                payment_tax_rate,
                payment_tax_amount,
                payment_discount_code,
				payment_price_name,
				payment_onsubmission,
				payment_enable_merchant
                from
                   `" . MF_TABLE_PREFIX . "forms`
               where
                  form_id=?";

        $params = array($submission->getFormId());

        $sth = mf_do_query($query, $params, $dbh);
        $row = mf_do_fetch_result($sth);

        $payment_price_type          = $row['payment_price_type'];
        $payment_enable_tax          = (int) $row['payment_enable_tax'];
        $payment_tax_rate              = (float) $row['payment_tax_rate'];
        $payment_tax_amount          = (float) $row['payment_tax_amount'];
        $payment_tax_code         = $row['payment_discount_code'];
        $payment_price_name         = $row['payment_price_name'];
        $payment_price_amount         = $row['payment_price_amount'];
        $payment_onsubmission         = $row['payment_onsubmission'];
        $payment_enable_merchant         = $row['payment_enable_merchant'];

        $currency = $row['payment_currency'];

        $total_taxable = 0;

        while (list($key, $val) = @each($descriptions)) {
            if ($val != "Total") {
                $total_taxable += $amounts[$index];
            }
            $index++;
        }
        if (!$payment_onsubmission && !$task) {
            //Add convenience fee
            if ($payment_price_type == 'variable') {
                //calculate tax if enabled
                if (!empty($payment_enable_tax)) {
                    if ($payment_tax_amount) {
                        $total_taxable += $payment_tax_amount;

                        $invoicedetail = new MfInvoiceDetail();
                        $invoicedetail->setDescription($payment_tax_code . " : Convenience Fee");
                        $invoicedetail->setAmount($payment_tax_amount);
                        $invoicedetail->setInvoiceId($invoice->getId());
                        $invoicedetail->save();
                    } else {
                        $payment_tax_amount = ($payment_tax_rate / 100) * $total_taxable;
                        $payment_tax_amount = round($payment_tax_amount, 2); //round to 2 digits decimal

                        $invoicedetail = new MfInvoiceDetail();
                        $invoicedetail->setDescription($payment_tax_code . " : Convenience Fee");
                        $invoicedetail->setAmount($payment_tax_amount);
                        $invoicedetail->setInvoiceId($invoice->getId());
                        $invoicedetail->save();

                        $total_taxable += $payment_tax_amount;
                    }
                } else {
                    //ADD variable price set hint ap_element_prices
                }
            } else if ($payment_price_type == 'fixed') {
                //calculate tax if enabled
                $tax_label = '';
                if (!empty($payment_enable_tax)) {
                    if ($payment_tax_amount) {
                        $total_taxable += $payment_tax_amount;

                        $invoicedetail = new MfInvoiceDetail();
                        $invoicedetail->setDescription($payment_tax_code . " : Convenience Fee");
                        $invoicedetail->setAmount($payment_tax_amount);
                        $invoicedetail->setInvoiceId($invoice->getId());
                        $invoicedetail->save();
                    } else {
                        $payment_tax_amount = ($payment_tax_rate / 100) * $total_taxable;
                        $payment_tax_amount = round($payment_tax_amount); //round to 2 digits decimal

                        $invoicedetail = new MfInvoiceDetail();
                        $invoicedetail->setDescription($payment_tax_code . " : Convenience Fee");
                        $invoicedetail->setAmount($payment_tax_amount);
                        $invoicedetail->setInvoiceId($invoice->getId());
                        $invoicedetail->save();

                        $total_taxable += $payment_tax_amount;
                    }
                } else {
                    //Note ADD
                    //Use payment_price_amount & payment_price_name
                    $invoicedetail = new MfInvoiceDetail();
                    $invoicedetail->setDescription($payment_price_name);
                    $invoicedetail->setAmount($payment_price_amount);
                    $invoicedetail->setInvoiceId($invoice->getId());
                    $invoicedetail->save();
                }
            }
        }
        $index = 0;

        while (list($key, $val) = @each($descriptions1)) {
            if ($task) {
                $q = Doctrine_Query::create()
                    ->from("Fee a")
                    ->where("a.id = ?", $val);
                $fee = $q->fetchOne();
                $val = empty($fee) ? $val : $fee->getFeeCode() . ":" . $fee->getDescription();
            }
            if ($val != "Total") {
                if ($amounts1[$index] != 0) {
                    $invoicedetail = new MfInvoiceDetail();
                    $invoicedetail->setDescription($val);
                    $invoicedetail->setAmount($amounts1[$index]);
                    $invoicedetail->setInvoiceId($invoice->getId());
                    $invoicedetail->save();
                }
            } else {
                if ($total_taxable == 0) {
                    continue;
                }

                $invoicedetail = new MfInvoiceDetail();
                $invoicedetail->setDescription($val);
                $invoicedetail->setAmount($total_taxable);
                $invoicedetail->setInvoiceId($invoice->getId());
                $invoicedetail->save();
            }

            $index++;
        }

        $due_date = date("Y-m-d H:i:s");
        $expires_at = date("Y-m-d H:i:s");

        if ($invoicetemplate->getMaxDuration()) {
            $date = strtotime("+" . $invoicetemplate->getMaxDuration() . " day", time());
            $expires_at = date('Y-m-d', $date);
        } else if ($invoicetemplate->getDueDuration()) {
            $date = strtotime("+" . $invoicetemplate->getDueDuration() . " day", time());
            $expires_at = date('Y-m-d', $date);
        } else {
            $date = strtotime("+" . 30 . " day", time());
            $expires_at = date('Y-m-d', $date);
        }

        $invoice->setMdaCode(sfConfig::get('app_mda_code'));
        $invoice->setBranch(sfConfig::get('app_branch'));
        $invoice->setDueDate($due_date);
        $invoice->setExpiresAt($expires_at);

        $q = Doctrine_Query::create()
            ->from('sfGuardUser a')
            ->where('a.id = ?', $submission->getUserId())
            ->limit(1);
        $user = $q->fetchOne();
        if ($user) {
            $invoice->setPayerId($user->getUsername());
        }

        $q = Doctrine_Query::create()
            ->from('sfGuardUserProfile a')
            ->where('a.user_id = ?', $submission->getUserId())
            ->limit(1);
        $userprofile = $q->fetchOne();
        if ($userprofile) {
            $invoice->setPayerName($userprofile->getFullname());
        }

        $invoice->setDocRefNumber($submission->getApplicationId());
        $invoice->setCurrency($currency);
        $invoice->setServiceCode($row['form_code']);
        $invoice->setTotalAmount($total_taxable);
        $invoice->save();

        //post invoice
        //check if merchant enabled
        if ($payment_enable_merchant) {
            //Post invoice
            $api = new ApiCalls();
            $api->postInvoice($submission, $invoice);
        }

        return $invoice;
    }

    //Update an existing unpaid invoice (Prevents an application from having many unused invoices)
    public function update_invoice($application_id, $invoice_id, $descriptions, $amounts)
    {
        $payment_currency = sfConfig::get('app_currency');

        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $submission = $this->get_application_by_id($application_id);
        $invoice = $this->get_invoice_by_id($invoice_id);

        $invoice_details = $invoice->getMfInvoiceDetail();
        foreach ($invoice_details as $detail) {
            $detail->delete();
        }

        $invoice->setPaid(1);
        $invoice->save();

        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ?", $submission->getFormId())
            ->limit(1);
        $invoicetemplate = $q->fetchOne();


        $grand_total = 0;

        $index = 0;
        while (list($key, $val) = @each($descriptions)) {
            $invoicedetail = new MfInvoiceDetail();
            $invoicedetail->setDescription($val);
            $invoicedetail->setAmount($amounts[$index]);
            $invoicedetail->setInvoiceId($invoice->getId());
            $invoicedetail->setCreatedAt(date("Y-m-d H:i:s"));
            $invoicedetail->setUpdatedAt(date("Y-m-d H:i:s"));
            $invoicedetail->save();
            if ($val != "Total") {
                $grand_total += $amounts[$index];
            }
            $index++;
        }

        $query  = "select
            form_code,
            payment_currency,
            payment_price_type,
            payment_price_amount,
            payment_enable_tax,
            payment_tax_rate,
            payment_tax_amount
            from
                `" . MF_TABLE_PREFIX . "forms`
            where
                form_id=?";

        $params = array($submission->getFormId());

        $sth = mf_do_query($query, $params, $dbh);
        $row = mf_do_fetch_result($sth);

        $payment_currency      = $row['payment_currency'];
        $service_code = $row['form_code'];


        $due_date = date("Y-m-d H:i:s");
        $expires_at = date("Y-m-d H:i:s");

        if ($invoicetemplate->getMaxDuration()) {
            $date = strtotime("+" . $invoicetemplate->getMaxDuration() . " day", time());
            $expires_at = date('Y-m-d', $date);
        } else if ($invoicetemplate->getDueDuration()) {
            $date = strtotime("+" . $invoicetemplate->getDueDuration() . " day", time());
            $expires_at = date('Y-m-d', $date);
        } else {
            $date = strtotime("+" . 30 . " day", time());
            $expires_at = date('Y-m-d', $date);
        }

        $invoice->setMdaCode(sfConfig::get('app_mda_code'));
        $invoice->setBranch(sfConfig::get('app_branch'));
        $invoice->setDueDate($due_date);
        $invoice->setExpiresAt($expires_at);

        $q = Doctrine_Query::create()
            ->from('sfGuardUser a')
            ->where('a.id = ?', $submission->getUserId())
            ->limit(1);
        $user = $q->fetchOne();
        if ($user) {
            $invoice->setPayerId($user->getUsername());
        }

        $q = Doctrine_Query::create()
            ->from('sfGuardUserProfile a')
            ->where('a.user_id = ?', $submission->getUserId())
            ->limit(1);
        $userprofile = $q->fetchOne();
        if ($userprofile) {
            $invoice->setPayerName($userprofile->getFullname());
        }

        $invoice->setDocRefNumber($submission->getApplicationId());
        $invoice->setCurrency($payment_currency);
        $invoice->setServiceCode($service_code);
        $invoice->setTotalAmount($grand_total);
        $invoice->save();
    }

    //Check if an application already has an invoice
    public function has_invoice($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return true;
        } else {
            return false;
        }
    }

    //Check if an application has an unpaid invoice
    public function has_unpaid_invoice($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id)
            ->andWhere('a.paid <> 2 AND a.paid <> 3')
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return true;
        } else {
            return false;
        }
    }

    //Get number of invoices
    public function invoice_count($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id)
            ->andWhere('a.paid = 2');

        return $q->count();
    }

    //Check if an application has a paid invoice
    public function has_paid_invoice($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id)
            ->andWhere('a.paid = 2')
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return true;
        } else {
            return false;
        }
    }

    //Return an unpaid invoice
    public function get_unpaid_invoice($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id)
            ->andWhere('a.paid <> 2')
            ->andWhere('a.paid <> 3')
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return $existing_invoice;
        } else {
            $q = Doctrine_Query::create()
                ->from('MfInvoice a')
                ->where('a.app_id = ?', $application_id)
                ->andWhere('a.paid <> 2')
                ->limit(1);
            $existing_invoice = $q->fetchOne();

            if ($existing_invoice) {
                return $existing_invoice;
            } else {
                return false;
            }
        }
    }

    //Return paid invoice
    public function get_paid_invoice($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id)
            ->andWhere('a.paid = 2')
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return $existing_invoice;
        } else {
            return false;
        }
    }

    //Returns an existing invoice
    public function get_invoice_by_id($invoice_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return $existing_invoice;
        } else {
            return false;
        }
    }

    public function get_invoice_total_owed($invoice_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            $total_amount = $existing_invoice->getTotalAmount();

            //Get all ap_form_payments that are paid and subtract from total amount
            $prefix_folder = dirname(__FILE__) . "/vendor/cp_machform/";

            require_once($prefix_folder . 'includes/init.php');

            require_once($prefix_folder . 'config.php');
            require_once($prefix_folder . 'includes/db-core.php');
            require_once($prefix_folder . 'includes/helper-functions.php');

            $dbh         = mf_connect_db();

            $submission = $this->get_application_by_id($existing_invoice->getAppId());

            $query = "select payment_amount from " . MF_TABLE_PREFIX . "form_payments where form_id = ? and record_id = ? and (payment_status = ? or payment_status = ?)";
            $params = array($submission->getFormId(), $submission->getEntryId(), 'paid', 'completed');
            $sth = mf_do_query($query, $params, $dbh);
            $count = 0;
            while ($row = mf_do_fetch_result($sth)) {
                $total_amount = $total_amount - $row['payment_amount'];
            }

            return $total_amount;
        } else {
            return false;
        }
    }

    //Returns an existing invoice
    public function get_merchant_reference($invoice_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return $existing_invoice->getFormEntry()->getFormId() . "/" . $existing_invoice->getFormEntry()->getEntryId() . "/" . $existing_invoice->getId();
        } else {
            return false;
        }
    }

    //Returns an existing application
    public function get_application_by_id($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id)
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) //Already submitted then tell client its already submitted
        {
            return $existing_app;
        } else {
            return false;
        }
    }

    //Query the payment status of an invoice if a transaction id exists
    public function update_payment_status($invoice_id)
    {
        $db_connection = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
        mysql_select_db(sfConfig::get('app_mysql_db'), $db_connection);

        $prefix_folder = dirname(__FILE__) . "/vendor/cp_machform/";

        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . 'config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        require_once($prefix_folder . 'includes/language.php');
        require_once($prefix_folder . 'includes/common-validator.php');
        require_once($prefix_folder . 'includes/view-functions.php');
        require_once($prefix_folder . 'includes/theme-functions.php');
        require_once($prefix_folder . 'includes/post-functions.php');
        require_once($prefix_folder . 'includes/entry-functions.php');
        require_once($prefix_folder . 'hooks/custom_hooks.php');

        require_once($prefix_folder . 'includes/OAuth.php');

        $dbh         = mf_connect_db();

        $invoice = $this->get_invoice_by_id($invoice_id);
        $submission = $this->get_application_by_id($invoice->getAppId());

        $query = "select * from " . MF_TABLE_PREFIX . "form_payments where form_id = ? and record_id = ?";
        $params = array($submission->getFormId(), $submission->getEntryId());
        $sth = mf_do_query($query, $params, $dbh);
        $count = 0;
        while ($row = mf_do_fetch_result($sth)) {
            $count++;
            if (!empty($row)) {
                $paid = true;

                if ($row['billing_state'] != "" && $invoice->getPaid() != 2 && $invoice->getPaid() != 3) {
                    //Query pesapal status
                    //get form properties data
                    $query  = "select
                                              form_name,
                                              form_has_css,
                                              form_redirect,
                                              form_language,
                                              form_review,
                                              form_review_primary_text,
                                              form_review_secondary_text,
                                              form_review_primary_img,
                                              form_review_secondary_img,
                                              form_review_use_image,
                                              form_review_title,
                                              form_review_description,
                                              form_resume_enable,
                                              form_page_total,
                                              form_lastpage_title,
                                              form_pagination_type,
                                              form_theme_id,
                                              payment_show_total,
                                              payment_total_location,
                                              payment_enable_merchant,
                                              payment_merchant_type,
                                              payment_currency,
                                              payment_price_type,
                                              payment_price_name,
                                              payment_price_amount,
                                              payment_ask_billing,
                                              payment_ask_shipping,
                                              payment_pesapal_live_secret_key,
                                              payment_pesapal_live_public_key,
                                              payment_pesapal_test_secret_key,
                                              payment_pesapal_test_public_key,
                                              payment_pesapal_enable_test_mode,
                                              payment_enable_recurring,
                                              payment_recurring_cycle,
                                              payment_recurring_unit,
                                              payment_enable_trial,
                                              payment_trial_period,
                                              payment_trial_unit,
                                              payment_trial_amount,
                                              payment_delay_notifications,
                                              payment_enable_tax,
                                              payment_tax_rate,
                                              payment_tax_amount
                                             from
                                                " . MF_TABLE_PREFIX . "forms
                                            where
                                               form_id = ?";
                    $params = array($submission->getFormId());

                    $sth = mf_do_query($query, $params, $dbh);
                    $rowtop = mf_do_fetch_result($sth);

                    if ($rowtop['payment_merchant_type'] == "pesapal") {

                        $consumer_key = null;
                        $consumer_secret = null;
                        $statusrequestAPI = null;

                        if ($rowtop['payment_pesapal_enable_test_mode']) {
                            $consumer_key = trim($rowtop['payment_pesapal_test_secret_key']); //Register a merchant account on
                            //demo.pesapal.com and use the merchant key for testing.
                            //When you are ready to go live make sure you change the key to the live account
                            //registered on www.pesapal.com!
                            $consumer_secret = trim($rowtop['payment_pesapal_test_public_key']); // Use the secret from your test
                            //account on demo.pesapal.com. When you are ready to go live make sure you
                            //change the secret to the live account registered on www.pesapal.com!
                            $statusrequestAPI = trim('http://demo.pesapal.com/api/querypaymentstatus'); //change to
                        } else {
                            $consumer_key = trim($rowtop['payment_pesapal_live_secret_key']); //Register a merchant account on
                            //demo.pesapal.com and use the merchant key for testing.
                            //When you are ready to go live make sure you change the key to the live account
                            //registered on www.pesapal.com!
                            $consumer_secret = trim($rowtop['payment_pesapal_live_public_key']); // Use the secret from your test
                            //account on demo.pesapal.com. When you are ready to go live make sure you
                            //change the secret to the live account registered on www.pesapal.com!
                            $statusrequestAPI = trim('https://www.pesapal.com/api/querypaymentstatus'); //change to
                        }
                        //https://demo.pesapal.com/api/querypaymentstatus' when you are ready to go live!
                        // Parameters sent to you by PesaPal IPN
                        $pesapalTrackingId = trim($row['billing_state']);
                        $pesapal_merchant_reference = trim($row['payment_id']);

                        if ($pesapalTrackingId != '') {
                            $token = $params = NULL;
                            $consumer = new OAuthConsumer($consumer_key, $consumer_secret);
                            $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

                            //get transaction status
                            $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
                            $request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
                            $request_status->set_parameter("pesapal_transaction_tracking_id", $pesapalTrackingId);
                            $request_status->sign_request($signature_method, $consumer, $token);


                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $request_status);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_HEADER, 1);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            if (defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True') {
                                $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
                                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
                                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                                curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
                            }

                            $response = curl_exec($ch);

                            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                            $raw_header = substr($response, 0, $header_size - 4);
                            $headerArray = explode("\r\n\r\n", $raw_header);
                            $header = $headerArray[count($headerArray) - 1];

                            //transaction status
                            $elements = preg_split("/=/", substr($response, $header_size));
                            $status = $elements[1];

                            curl_close($ch);

                            //UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH pesapal_transaction_tracking_id $pesapalTrackingId
                            if ($status == "COMPLETED") {
                                $invoice->setPaid(2);
                                $invoice->save();
                            } elseif ($status == "FAILED") {
                                $invoice->setPaid(3);
                                $invoice->save();
                            }
                        }
                    } elseif ($rowtop['payment_merchant_type'] == "cellulant") {
                        $invoiceNumber = $row['billing_zipcode'];
                        $beepTransactionID = $row['billing_state'];

                        $url = "http://197.159.100.249:9000/hub/services/paymentGateway/XML/index.php/";
                        $client = new IXR_Client($url);
                        $client->debug = false;


                        $credentials = array(
                            "username" => $rowtop['payment_cellulant_merchant_username'],
                            "password" => $rowtop['payment_cellulant_merchant_password'],
                        );
                        $dataPacket = array(
                            "invoiceNumber" => $invoiceNumber,
                            "beepTransactionID" => $beepTransactionID,
                        );

                        $request[] = $dataPacket;
                        $payload = array("credentials" => $credentials, "packet" => $request);

                        $client->query(
                            'BEEP.queryInvoicePayStatus',
                            $payload
                        );
                        $response = $client->getResponse();

                        if (!$response) {
                            $error_message = $client->getErrorMessage();
                            error_log("Cellulant Error: " . $error_message);
                        }

                        if ($response['results']['statusCode'] == "253") {
                            $invoice->setPaid(2);
                            $invoice->save();
                        } elseif ($response['results']['statusCode'] == "195" || $response['results']['statusCode'] == "251") {
                            $invoice->setPaid(3);
                            $invoice->save();
                        }
                    }
                }
            }
        }

        return $invoice;
    }

    public function can_make_partial_payment($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id)
            ->limit(1);
        $application = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ?", $application->getFormId())
            ->limit(1);
        $invoicetemplate = $q->fetchOne();

        if ($invoicetemplate && $invoicetemplate->getPaymentType() == "2") {
            return true;
        } else {
            return false;
        }
    }

    //Checks for any invoice that should have been marked as paid becaused all partial payments were submitted
    public function update_invoices($application_id)
    {
        $application_manager = new ApplicationManager();
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.app_id = ?", $application_id);
        $invoices = $q->execute();

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.id = ?", $application_id);
        $application = $q->fetchOne();

        foreach ($invoices as $invoice) {
            $form = $application->getForm();

            if ($invoice->getPaid() != 2) {
                if ($form->getPaymentMerchantType() == "braintree") {
                    if ($form->getPaymentBraintreeEnableTestMode()) {
                        $invoice->setPaid(2);
                        $invoice->save();
                    }
                } elseif ($form->getPaymentMerchantType() == "pesaflow_standard" || $form->getPaymentMerchantType() == "pesaflow_cart") {
                    $result = $this->remote_reconcile($invoice->getFormEntry()->getFormId() . "/" . $invoice->getFormEntry()->getEntryId() . "/" . $invoice->getId());

                    //If response is paid, then mark invoice as paid
                    if ($result == "paid") {
                        $invoice->setPaid(2);
                        $invoice->save();

                        error_log("Pesaflow Remote Validated: " . $invoice->getFormEntry()->getApplicationId());
                    } else {
                        error_log("Pesaflow Remote Pending: " . $invoice->getFormEntry()->getApplicationId());
                    }
                }
            } else {
                //if invoice is paid but the application is still in the draft stage. Try to execute triggers by saving the invoice again.
                if ($application->getApproved() == 0) {
                    $invoice->save();
                }
            }

            //If there are no unpaid invoices and application is a draft then submit it 
            if ($this->has_unpaid_invoice($application_id) == false && $application->getApproved() == 0) {
                //$application_manager = new ApplicationManager();
                $application_manager->publish_draft($application->getId());

                $application_manager->update_services($application->getId());
            }
        }

        //If all invoices as paid then move application to the next stage if settings exists
        if (!$this->has_unpaid_invoice($application->getId())) {
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $application->getApproved());
            $stage = $q->fetchOne();

            if ($stage && $stage->getStageType() == 3) {
                if ($stage->getStageProperty() == 2) {
                    //Move application to another stage
                    $next_stage = $stage->getStageTypeMovement();
                    if (intval($next_stage) === 1) {
                        error_log("Application form --- " . $application->getFormId());
                        error_log("Form Entry ---" . $application->getEntryId());
                        $stage_to_send =  $application_manager->get_submission_stage($application->getFormId(), $application->getEntryId());
                        if ($stage_to_send) {
                            $next_stage = $stage_to_send;
                        }
                        $application->setApproved($next_stage);
                    } else {
                        $application->setApproved($next_stage);
                    }
                    $application->save();
                }
            }
        }
    }

    //Automatic triggers
    public function update_invoices_all($application_id)
    {
        $application = $this->get_application_by_id($application_id);

        if ($application) {
            foreach ($application->getMfInvoice() as $invoice) {
                $invoice->save();
            }
        }
    }

    //Checks if the specified invoice is expired
    public function is_invoice_expired($invoice_id)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();

        if ($existing_invoice->getPaid() == 2) {
            return false;
        } else {

            $q = Doctrine_Query::create()
                ->from('Invoicetemplates a')
                ->where('a.applicationform = ?', $existing_invoice->getFormEntry()->getFormId())
                ->limit(1);
            $invoice_template = $q->fetchOne();

            if ($invoice_template->getExpirationType() == 3) {
                //Check if the invoice expires based on yearly expiry dates
                $first_day_of_current_year = date("Y-01-01");
                if (strtotime($existing_invoice->getCreatedAt()) > strtotime($first_day_of_current_year)) {
                    return false;
                } else {
                    return true;
                }
            } elseif ($invoice_template->getExpirationType() == 2) {
                //Check if the invoice expires based on monthly expiry dates
                $first_day_of_current_month = date("Y-m-01");
                if (strtotime($existing_invoice->getCreatedAt()) > strtotime($first_day_of_current_month)) {
                    return false;
                } else {
                    return true;
                }
            } else {
                $db_date_event = str_replace('/', '-', $existing_invoice->getExpiresAt());

                $db_date_event = strtotime($db_date_event);

                if (time() > $db_date_event) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    //Returns an existing invoice
    public function update_invoice_partial($invoice_id, $new_total)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("MfInvoiceDetail a")
            ->where("a.invoice_id = ?", $invoice_id)
            ->orderBy("a.id DESC");
        $detail = $q->fetchOne();

        $detail->setAmount($new_total);
        $detail->save();

        $total_amount = 0;
        foreach ($q->execute() as $detail) {
            $total_amount = $total_amount + $detail->getAmount();
        }

        $existing_invoice->setTotalAmount($total_amount);
        $existing_invoice->save();

        return $existing_invoice;
    }

    //Confirm payment on remote payment gateway if available
    public function remote_reconcile($billing_reference)
    {
        $request_url = "https://pesaflow.ecitizen.go.ke/PaymentAPI/getStatus.php?billRefNumber=" . $billing_reference;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True') {
            $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
        }

        $response = curl_exec($ch);

        $start_pos = strpos($response, "{");
        $end_pos = strpos($response, "}");

        $short_response = substr($response, $start_pos, (($end_pos - $start_pos) + 1));

        $array_response = json_decode($short_response, true);
        error_log('----------------');
        error_log(print_r($array_response, true));

        error_log("Response: " . $short_response);

        if ($array_response['status'] == 4 || $array_response['status'] == 1) {
            $invoice = $this->get_invoice_by_reference($billing_reference);

            try {
                $invoice->setRemoteValidate(1);
                $invoice->save();

                //Update transaction table
                $q = Doctrine_Query::create()
                    ->from("ApFormPayments a")
                    ->where("a.payment_id = ?", $billing_reference)
                    ->andWhere("a.status <> ? or a.payment_status <> ?", array(2, 'paid'));
                $transaction = $q->fetchOne();

                if ($transaction && $transaction->getPaymentStatus() != "paid") {
                    $transaction->setStatus(2);
                    $transaction->setPaymentStatus("paid");
                    $transaction->setPaymentDate(date("Y-m-d H:i:s"));

                    if ($array_response['transaction_id']) {
                        $transaction->setBillingState($array_response['transaction_id']);
                    }

                    $transaction->save();
                }
            } catch (Exception $ex) {
                error_log("Pesaflow: Could not update " . $billing_reference . " transaction to paid " . $ex);
            }

            return "paid";
        } else {
            $invoice = $this->get_invoice_by_reference($billing_reference);

            try {
                //Update transaction table
                $q = Doctrine_Query::create()
                    ->from("ApFormPayments a")
                    ->where("a.payment_id = ?", $billing_reference);
                $transaction = $q->fetchOne();

                if ($transaction) {
                    $transaction->setStatus(1);
                    $transaction->setPaymentStatus("pending");
                    $transaction->setPaymentDate(date("Y-m-d H:i:s"));
                    $transaction->save();
                }
            } catch (Exception $ex) {
                error_log("Pesaflow: Could not update " . $billing_reference . " transaction to pending " . $ex);
            }

            return "pending";
        }
    }

    //Confirm payment on remote payment gateway if available without needing to fetch invoice
    public function basic_remote_reconcile($billing_reference)
    {
        $request_url = "https://pesaflow.ecitizen.go.ke/PaymentAPI/getStatus.php?billRefNumber=" . $billing_reference;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True') {
            $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
        }

        $response = curl_exec($ch);

        $start_pos = strpos($response, "{");
        $end_pos = strpos($response, "}");

        $short_response = substr($response, $start_pos, (($end_pos - $start_pos) + 1));

        $array_response = json_decode($short_response, true);

        error_log("Response: " . $short_response);

        if ($array_response['status'] == 4 || $array_response['status'] == 1) {
            return "paid";
        } else {
            return "pending";
        }
    }

    //Generate new invoice from latest invoice
    public function generate_renewal_invoice($application_id)
    {
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.app_id = ?", $application_id)
            ->andWhere("a.paid = 2")
            ->orderBy("a.id DESC");
        $invoice = $q->fetchOne();

        if ($invoice) {
            $new_invoice = new MfInvoice();
            $new_invoice->setAppId($invoice->getAppId());
            $new_invoice->setInvoiceNumber("");
            $new_invoice->setPaid(1);
            $new_invoice->setCreatedAt(date("Y-m-d H:m:s"));
            $new_invoice->setUpdatedAt(date("Y-m-d H:m:s"));
            $new_invoice->setDueDate(date("Y-m-d H:m:s", strtotime("+30 days")));
            $new_invoice->setExpiresAt(date("Y-m-d H:m:s", strtotime("+30 days")));
            $new_invoice->setPayerId($invoice->getPayerId());
            $new_invoice->setPayerName($invoice->getPayerName());
            $new_invoice->setDocRefNumber($invoice->getDocRefNumber());
            $new_invoice->setCurrency($invoice->getCurrency());
            $new_invoice->setServiceCode($invoice->getServiceCode());
            $new_invoice->setTotalAmount($invoice->getTotalAmount());
            $new_invoice->save();

            $invoice_details = $invoice->getMfInvoiceDetail();
            foreach ($invoice_details as $detail) {
                $new_invoicedetail = new MfInvoiceDetail();
                $new_invoicedetail->setInvoiceId($new_invoice->getId());
                $new_invoicedetail->setDescription($detail->getDescription());
                $new_invoicedetail->setAmount($detail->getAmount());
                $new_invoicedetail->setCreatedAt(date("Y-m-d H:m:s"));
                $new_invoicedetail->setUpdatedAt(date("Y-m-d H:m:s"));
                $new_invoicedetail->save();
            }
        }
    }

    //Generate new invoice from latest invoice
    public function generate_renewal_penalty_invoice($application_id, $penalty_id, $permit_id, $description, $amount)
    {
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.app_id = ?", $application_id)
            ->orderBy("a.id DESC");
        $invoice = $q->fetchOne();

        if ($invoice) {
            $new_invoice = new MfInvoice();
            $new_invoice->setAppId($invoice->getAppId());
            $new_invoice->setInvoiceNumber("");
            $new_invoice->setPaid(1);
            $new_invoice->setCreatedAt(date("Y-m-d H:m:s"));
            $new_invoice->setUpdatedAt(date("Y-m-d H:m:s"));
            $new_invoice->setDueDate(date("Y-m-d H:m:s", strtotime("+30 days")));
            $new_invoice->setExpiresAt(date("Y-m-d H:m:s", strtotime("+30 days")));
            $new_invoice->setPayerId($invoice->getPayerId());
            $new_invoice->setPayerName($invoice->getPayerName());
            $new_invoice->setDocRefNumber($invoice->getDocRefNumber());
            $new_invoice->setCurrency($invoice->getCurrency());
            $new_invoice->setServiceCode($invoice->getServiceCode());
            $new_invoice->setTotalAmount($amount);
            $new_invoice->save();

            $new_invoicedetail = new MfInvoiceDetail();
            $new_invoicedetail->setInvoiceId($new_invoice->getId());
            $new_invoicedetail->setDescription("Convenience fee");
            $new_invoicedetail->setAmount(50);
            $new_invoicedetail->setCreatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->setUpdatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->save();

            $new_invoicedetail = new MfInvoiceDetail();
            $new_invoicedetail->setInvoiceId($new_invoice->getId());
            $new_invoicedetail->setDescription($description);
            $new_invoicedetail->setAmount($amount);
            $new_invoicedetail->setCreatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->setUpdatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->save();

            $q = Doctrine_Query::create()
                ->from("Penalty a")
                ->where("a.template_id = ?", $penalty_id)
                ->andWhere("a.permit_id = ?", $permit_id)
                ->andWhere("a.paid = 0");
            if ($q->count() > 0) {
                $penalty = $q->fetchOne();
                $penalty->setInvoiceId($new_invoice->getId());
                $penalty->save();
            }

            return $new_invoice->getId();
        } else {
            return 0;
        }
    }

    //Generate new invoice from latest invoice
    public function update_invoice_with_amount($invoice_id, $description, $amount)
    {
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.id = ?", $invoice_id)
            ->andWhere("a.paid = 1")
            ->orderBy("a.id DESC");
        $invoice = $q->fetchOne();

        if ($invoice) {
            $current_total = $invoice->getTotalAmount();
            $new_total = $current_total + $amount;

            $invoice->setTotalAmount($new_total);
            $invoice->save();

            $new_invoicedetail = new MfInvoiceDetail();
            $new_invoicedetail->setInvoiceId($invoice->getId());
            $new_invoicedetail->setDescription($description);
            $new_invoicedetail->setAmount($amount);
            $new_invoicedetail->setCreatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->setUpdatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->save();
        }
    }

    //Generate new invoice from latest invoice
    public function reverse_duplicate_with_amount($invoice_id, $description, $amount)
    {
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.id = ?", $invoice_id)
            ->andWhere("a.paid = 1")
            ->orderBy("a.id DESC");
        $invoice = $q->fetchOne();

        if ($invoice) {
            $current_total = $invoice->getTotalAmount();

            $q = Doctrine_Query::create()
                ->from("MfInvoiceDetail a")
                ->where("a.invoice_id = ?", $invoice_id)
                ->andWhere("a.description LIKE ?", "Penalty%")
                ->orderBy("a.id DESC");
            $penalties = $q->count();

            $q = Doctrine_Query::create()
                ->from("MfInvoiceDetail a")
                ->where("a.invoice_id = ?", $invoice_id)
                ->andWhere("a.description = ?", $description)
                ->orderBy("a.id DESC");

            $invoice_details = $q->execute();

            foreach ($invoice_details as $detail) {
                if ($penalties == 0) {
                    break;
                }

                $current_total = $current_total - $detail->getAmount();

                $detail->delete();

                $duplicates--;
            }

            $invoice->setTotalAmount($current_total);
            $invoice->save();
        }
    }

    //Generate new invoice from latest invoice
    public function update_renewal_penalty_invoice($invoice_id, $description, $amount)
    {
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.id = ?", $invoice_id)
            ->andWhere("a.paid = 1")
            ->orderBy("a.id DESC");
        $invoice = $q->fetchOne();

        if ($invoice && $amount > 0) {
            $current_total = $invoice->getTotalAmount();
            $new_total = $current_total + $amount;

            $invoice->setTotalAmount($new_total);
            $invoice->save();

            $new_invoicedetail = new MfInvoiceDetail();
            $new_invoicedetail->setInvoiceId($invoice->getId());
            $new_invoicedetail->setDescription($description);
            $new_invoicedetail->setAmount($amount);
            $new_invoicedetail->setCreatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->setUpdatedAt(date("Y-m-d H:m:s"));
            $new_invoicedetail->save();
        }
    }

    //Apply penalty to application
    public function apply_penalty($application_id, $penalty_id)
    {
        //1. Fetch Penalty Details
        $q = Doctrine_Query::create()
            ->from("PenaltyTemplate a")
            ->where("a.id = ?", $penalty_id);
        $penalty_template = $q->fetchOne();

        //2. Depending on penalty type, calculate penalty amount
        $penalty_amount = 0;

        if ($penalty_template->getPenaltyType() == 1) //Get percentage of last invoice
        {
            //Lets assume total of all invoices is the total submission (Considering top up fees)
            $q = Doctrine_Query::create()
                ->select("SUM(a.total_amount) AS total")
                ->from("MfInvoice a")
                ->where("a.app_id = ?", $application_id)
                ->andWhere("a.paid = 1")
                ->orderBy("a.id ASC");
            $invoice_total = $q->fetchOne();

            //Lets manually subtract convenience
            $total = $invoice_total->getTotal() - 50;

            $penalty_amount = round(($penalty_template->getPenaltyAmount() / 100) * $total);
        }
        //ADD
        elseif ($penalty_template->getPenaltyType() == 3) {
            //get service fee
            $application_manager = new ApplicationManager();
            //get menu
            $application = Doctrine_Core::getTable('FormEntry')->findOneBy('id', $application_id);
            $menu_id = Doctrine_Core::getTable('Menus')->findByServiceForm($application->getFormId());
            //error_log('--------Menu id ------'.$menu_id[0]['id']);
            $service_fee = $application_manager->get_service_fee_desc($menu_id[0]['id'], $application_id);
            //error_log(print_r($service_fee,true));
            if (count($service_fee) && $service_fee['fee_amt'] != 0) {
                //Do percentage
                $penalty_amount = round(($penalty_template->getPenaltyAmount() / 100) * $service_fee['fee_amt']);
            }
        } else //Get fixed amount
        {
            $penalty_amount = $penalty_template->getPenaltyAmount();
        }

        return $penalty_amount;
    }

    //Generate new invoice for business
    public function generate_cyclic_invoices($application_id, $service_id, $auto_run = true, $year_plus = false)
    {
        $first_time_submission = true;

        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.application_id = ?", $application_id);

        if ($q->count() > 0) {
            $first_time_submission = false;
        }

        $q = Doctrine_Query::create()
            ->from('Menus a')
            ->where('a.id = ?', $service_id);
        $service = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id);
        $application = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.app_id = ?', $application_id);
        $invoices_count = $q->count();

        //Create any missing permits first to prevent double bulling
        $application_manager = new ApplicationManager();
        if ($auto_run) {
            $application_manager->update_services($application->getId());
        }

        //error_log("Cyclic-b #1: Checking cyclic bill for ".$application_id);

        $main_fee_text = null;
        $main_fee_amount = null;

        //Does the application new renewal. If yes, then check the settings for the fees to regenerate
        error_log('------Renewal ------' . $application->needsRenewal());
        if ($application->needsRenewal() || $invoices_count == 0) {
            // ADD - TO PREVENT DOUBLE BILLING
            $amount_payable = $this->get_total_payment_amount($application->getId());
            error_log('-----Payable amount---' . $amount_payable);
            // ADD - For +1 year
            if ($year_plus) {
                $q = Doctrine_Query::create()
                    ->from('MfInvoice i')
                    ->where('i.app_id = ? and i.paid <> ? and i.total_amount = ? and i.created_at >= ?', array($application->getId(), 3, $amount_payable, ((date('Y') + 1) . '-1-1')));
            } else {
                $q = Doctrine_Query::create()
                    ->from('MfInvoice i')
                    ->where('i.app_id = ? and i.paid <> ? and i.total_amount = ? and i.created_at >= ?', array($application->getId(), 3, $amount_payable, date('Y-1-1')));
            }
            error_log('------Count invoices ------' . $q->count());
            if (!$q->count()) {
                error_log("Cyclic-b #1: Renewal needed for " . $application_id);
                $field_data = $application_manager->get_field_data($application->getFormId(), $application->getEntryId(), $service->getServiceFeeField());

                //First check the main service fee configured
                $q = Doctrine_Query::create()
                    ->from("ApElementOptions a")
                    ->where("a.form_id = ?", $service->getServiceForm())
                    ->andWhere("a.element_id = ?", $service->getServiceFeeField())
                    ->andWhere("a.option_id = ?", $field_data)
                    ->andWhere("a.live = 1")
                    ->orderBy("a.option_text ASC");
                $element_option = $q->fetchOne();

                if ($element_option) {
                    //FEE FOR BUSINESS ACTIVITY -- 
                    $q = Doctrine_Query::create()
                        ->from("ServiceFees a")
                        ->where("a.service_id = ?", $service->getId())
                        ->andWhere("a.field_id = ?", $service->getServiceFeeField())
                        ->andWhere("a.option_id = ?", $element_option->getAeoId());
                    $option_fee = $q->fetchOne();

                    //If a fee has been configured for the data the user submitted then add it to the invoice
                    if ($option_fee) {
                        //error_log("Cyclic-b #2: Found main fee of ".$top_amount." for ".$application_id.": ".$element_option->getOptionText());
                        $main_fee_text = $element_option->getOptionText();
                        $main_fee_amount = $option_fee->getTotalAmount();
                    }
                }

                //Add any other fees to the invoice
                $other_fees = array();

                $q = Doctrine_Query::create()
                    ->from("ServiceOtherFees a")
                    ->where("a.service_id = ?", $service->getId());
                $fees = $q->execute();

                foreach ($fees as $fee) {
                    error_log("Cyclic-b #3: Found other fixed fees of " . $fee->getAmount() . ": " . $fee->getServiceCode());
                    if ($fee->getAsFirstSubmissionFee() && $first_time_submission == false) {
                        continue;
                    } elseif ($fee->getAsRenewalFee() && $first_time_submission == true) {
                        continue;
                    }
                    //service fee -- convenience associated with the service id menu
                    $other_fees[] = array(
                        "service_code" => $fee->getServiceCode(),
                        "amount" => $fee->getAmount()
                    );
                }

                //Add any more fees that depend on a dropdown
                $q = Doctrine_Query::create()
                    ->from("MoreFees a")
                    ->where("a.service_id = ?", $service->getId());
                $more_fees = $q->execute();

                foreach ($more_fees as $more_fee) {
                    $more_field_data = $application_manager->get_field_data($application->getFormId(), $application->getEntryId(), $more_fee->getFieldId());

                    error_log("Cyclic-b #4: Checking more fee " . $more_fee->getFeeTitle() . ": Form " . $service->getServiceForm() . "/ Element " . $more_fee->getFieldId() . "/ Option " . $more_field_data . "/");

                    $q = Doctrine_Query::create()
                        ->from("ApElementOptions a")
                        ->where("a.form_id = ?", $service->getServiceForm())
                        ->andWhere("a.element_id = ?", $more_fee->getFieldId())
                        ->andWhere("a.option_id = ?", $more_field_data)
                        ->andWhere("a.live = 1")
                        ->orderBy("a.option_text ASC");
                    $more_element_option = $q->fetchOne();

                    if ($more_element_option) {
                        error_log("Cyclic-b #4: Checking option " . $more_element_option->getAeoId() . " for dropdown " . $more_fee->getFieldId() . ": " . $more_fee->getFeeTitle());

                        $q = Doctrine_Query::create()
                            ->from("ServiceMoreFees a")
                            ->where("a.service_id = ?", $service->getId())
                            ->andWhere("a.fee_id = ?", $more_fee->getId())
                            ->andWhere("a.field_id = ?", $more_fee->getFieldId())
                            ->andWhere("a.option_id = ?", $more_element_option->getAeoId());
                        $more_option_fee = $q->fetchOne();

                        if ($more_option_fee) {
                            error_log("Cyclic-b #4: Found more fees of " . $more_option_fee->getTotalAmount() . ": " . $more_fee->getFeeTitle());

                            $other_fees[] = array(
                                "service_code" => $more_fee->getFeeTitle(),
                                "amount" => $more_option_fee->getTotalAmount()
                            );
                        }
                    }
                }

                //Add any more fees that depend on multiplier
                $q = Doctrine_Query::create()
                    ->from("MultiplierFees a")
                    ->where("a.service_id = ?", $service->getId());
                $multiplier_fees = $q->execute();

                foreach ($multiplier_fees as $multiplier_fee) {
                    $multiplier_field_data = $application_manager->get_field_data($application->getFormId(), $application->getEntryId(), $multiplier_fee->getFieldId());

                    if ($multiplier_field_data) {
                        $multiplier_amount = $multiplier_field_data * $multiplier_fee->getMultiplierAmount();

                        $q = Doctrine_Query::create()
                            ->from("ApFormElements a")
                            ->where("a.form_id = ?", $service->getServiceForm())
                            ->andWhere("a.element_id = ?", $multiplier_fee->getFieldId());
                        $element = $q->fetchOne();

                        $other_fees[] = array(
                            "service_code" => $element->getElementTitle(),
                            "amount" => $multiplier_amount
                        );
                    }
                }

                if ($main_fee_amount > 0) {
                    //Gather all the fees and generate the invoice
                    $this->create_invoice_from_amount($application_id, $main_fee_text, $main_fee_amount, $other_fees, $year_plus);
                } else {
                    error_log("Amount is less than zero. Don't create invoice");
                }
            } else {
                error_log("Invoice found! Don't recreate.");
            }
        }
    }

    //Generate a new invoice from invoicing task
    public function create_invoice_from_amount($application_id, $description, $amount, $other_fees, $year_plus = false)
    {
        $submission = $this->get_application_by_id($application_id);

        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ?", $submission->getFormId())
            ->limit(1);
        $invoicetemplate = $q->fetchOne();

        if ($invoicetemplate == null) {
            $q = Doctrine_Query::create()
                ->from('Invoicetemplates a')
                ->limit(1);
            $invoicetemplate = $q->fetchOne();
        }

        $invoice = new MfInvoice();
        $invoice->setAppId($submission->getId());

        if ($invoicetemplate->getInvoiceNumber()) {
            $q = Doctrine_Query::create()
                ->from('MfInvoice a')
                ->where("a.template_id = ?", $invoicetemplate->getId())
                ->orderBy("a.id DESC")
                ->limit(1);
            $lastinvoice = $q->fetchOne();

            if ($lastinvoice) {
                $invoice_number = $lastinvoice->getInvoiceNumber();
                $invoice_number++;
                $invoice->setInvoiceNumber($invoice_number);
            } else {
                $invoice->setInvoiceNumber($invoicetemplate->getInvoiceNumber());
            }

            $invoice->setTemplateId($invoicetemplate->getId());
        } else {
            $invoice->setInvoiceNumber("INV-" . $submission->getId());
        }
        //Note ADD - SET +1 Current year
        if ($year_plus) {
            $invoice->setCreatedAt((date("Y") + 1) . "-1-1");
        } else {
            $invoice->setCreatedAt(date("Y-m-d H:i:s"));
        }
        $invoice->setUpdatedAt(date("Y-m-d H:i:s"));

        $invoice->setPaid(1);
        $invoice->save();

        $grand_amount = 0;

        foreach ($other_fees as $fee) {
            $invoicedetail = new MfInvoiceDetail();
            $invoicedetail->setDescription($fee['service_code']);
            $invoicedetail->setAmount($fee['amount']);
            $invoicedetail->setInvoiceId($invoice->getId());
            $invoicedetail->save();

            $grand_amount = $grand_amount + $fee['amount'];
        }

        $invoicedetail = new MfInvoiceDetail();
        $invoicedetail->setDescription($description);
        $invoicedetail->setAmount($amount);
        $invoicedetail->setInvoiceId($invoice->getId());
        $invoicedetail->save();

        $grand_amount = $grand_amount + $amount;

        $expires_at = date("Y-m-d H:i:s");

        if ($invoicetemplate->getMaxDuration() && $invoicetemplate->getExpirationType() == 1) {
            $date = strtotime("+" . $invoicetemplate->getMaxDuration() . " day", time());
            $expires_at = date('Y-m-d', $date);
        } elseif ($invoicetemplate->getExpirationType() == 2) {
            $expires_at = date('Y-m-t');
        } elseif ($invoicetemplate->getExpirationType() == 3) {
            $expires_at = date('Y-12-t');
        } else {
            $date = strtotime("+" . 30 . " day", time());
            $expires_at = date('Y-m-d', $date);
        }

        $invoice->setMdaCode(sfConfig::get('app_mda_code'));
        $invoice->setBranch(sfConfig::get('app_branch'));
        $invoice->setDueDate($expires_at);
        $invoice->setExpiresAt($expires_at);

        $q = Doctrine_Query::create()
            ->from('sfGuardUser a')
            ->where('a.id = ?', $submission->getUserId())
            ->limit(1);
        $user = $q->fetchOne();
        if ($user) {
            $invoice->setPayerId($user->getUsername());
        }

        $q = Doctrine_Query::create()
            ->from('sfGuardUserProfile a')
            ->where('a.user_id = ?', $submission->getUserId())
            ->limit(1);
        $userprofile = $q->fetchOne();
        if ($userprofile) {
            $invoice->setPayerName($userprofile->getFullname());
        }

        $invoice->setDocRefNumber($submission->getApplicationId());
        $invoice->setCurrency(sfConfig::get('app_currency'));
        $invoice->setServiceCode($row['form_code']);
        $invoice->setTotalAmount($grand_amount);
        $invoice->save();
    }

    //Generate new invoice for business
    public function get_total_payment_amount($application_id)
    {
        $first_time_submission = true;

        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.application_id = ?", $application_id);

        if ($q->count() > 0) {
            $first_time_submission = false;
        }

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id);
        $application = $q->fetchOne();

        //Note ADD allow old form to be billed
        if ($application->getServiceId()) {
            $q = Doctrine_Query::create()
                ->from('Menus a')
                ->where('a.id = ?', $application->getServiceId());
            $service = $q->fetchOne();
        } else {
            //CHECK MENU FOR OLD FORM ID
            $q = Doctrine_Query::create()
                ->from('Menus a')
                ->where('a.service_form = ? and a.service_type = ?', array($application->getFormId(), 2));
            $service = $q->fetchOne();
        }
        $total_payment_amount = 0;

        $application_manager = new ApplicationManager();
        $field_data = $application_manager->get_field_data($application->getFormId(), $application->getEntryId(), $service->getServiceFeeField());

        //First check the main service fee configured
        $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $service->getServiceForm())
            ->andWhere("a.element_id = ?", $service->getServiceFeeField())
            ->andWhere("a.option_id = ?", $field_data)
            ->andWhere("a.live = 1")
            ->orderBy("a.option_text ASC");
        $element_option = $q->fetchOne();

        if ($element_option) {
            $q = Doctrine_Query::create()
                ->from("ServiceFees a")
                ->where("a.service_id = ?", $service->getId())
                ->andWhere("a.field_id = ?", $service->getServiceFeeField())
                ->andWhere("a.option_id = ?", $element_option->getAeoId());
            $option_fee = $q->fetchOne();

            //If a fee has been configured for the data the user submitted then add it to the invoice
            if ($option_fee) {
                error_log("Cyclic-b #2: Found main fee of " . $option_fee->getTotalAmount() . " for " . $application_id . ": " . $element_option->getOptionText());
                $total_payment_amount = $total_payment_amount + $option_fee->getTotalAmount();
            }
        }

        //Add any other fees to the invoice
        $other_fees = array();

        $q = Doctrine_Query::create()
            ->from("ServiceOtherFees a")
            ->where("a.service_id = ?", $service->getId());
        $fees = $q->execute();

        foreach ($fees as $fee) {
            if ($fee->getAsFirstSubmissionFee() && $first_time_submission == false) {
                continue;
            } elseif ($fee->getAsRenewalFee() && $first_time_submission == true) {
                continue;
            }

            error_log("Cyclic-b #3: Found other fixed fees of " . $fee->getAmount() . ": " . $fee->getServiceCode());
            $total_payment_amount = $total_payment_amount + $fee->getAmount();
        }

        //Add any more fees that depend on a dropdown
        $q = Doctrine_Query::create()
            ->from("MoreFees a")
            ->where("a.service_id = ?", $service->getId());
        $more_fees = $q->execute();

        foreach ($more_fees as $more_fee) {
            $more_field_data = $application_manager->get_field_data($application->getFormId(), $application->getEntryId(), $more_fee->getFieldId());

            error_log("Cyclic-b #4: Checking more fee " . $more_fee->getFeeTitle() . ": Form " . $service->getServiceForm() . "/ Element " . $more_fee->getFieldId() . "/ Option " . $more_field_data . "/");

            $q = Doctrine_Query::create()
                ->from("ApElementOptions a")
                ->where("a.form_id = ?", $service->getServiceForm())
                ->andWhere("a.element_id = ?", $more_fee->getFieldId())
                ->andWhere("a.option_id = ?", $more_field_data)
                ->andWhere("a.live = 1")
                ->orderBy("a.option_text ASC");
            $more_element_option = $q->fetchOne();

            if ($more_element_option) {
                error_log("Cyclic-b #4: Checking option " . $more_element_option->getAeoId() . " for dropdown " . $more_fee->getFieldId() . ": " . $more_fee->getFeeTitle());

                $q = Doctrine_Query::create()
                    ->from("ServiceMoreFees a")
                    ->where("a.service_id = ?", $service->getId())
                    //Note ADD
                    ->andWhere("a.fee_id = ?", $more_fee->getId())
                    ->andWhere("a.field_id = ?", $more_fee->getFieldId())
                    ->andWhere("a.option_id = ?", $more_element_option->getAeoId());
                $more_option_fee = $q->fetchOne();

                if ($more_option_fee) {
                    error_log("Cyclic-b #4: Found more fees of " . $more_option_fee->getTotalAmount() . ": " . $more_fee->getFeeTitle());

                    $total_payment_amount = $total_payment_amount + $more_option_fee->getTotalAmount();
                }
            }
        }

        return $total_payment_amount;
    }

    //Checks for any invoice that should have been marked as paid becaused all partial payments were submitted
    public function refresh_invoices($application_id)
    {
        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.id = ?", $application_id);
        $application = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.app_id = ?", $application_id)
            ->andWhere("a.paid = 2");
        if ($q->count() == 0) {
            $q = Doctrine_Query::create()
                ->from("MfInvoice a")
                ->where("a.app_id = ?", $application_id);
            $invoices = $q->execute();

            foreach ($invoices as $invoice) {
                $invoice_details = $invoice->getMfInvoiceDetail();
                foreach ($invoice_details as $invoice_detail) {
                    $invoice_detail->delete();
                }
                $invoice->delete();
            }
        }

        if ($application->getBusinessId() != "" && $application->getBusinessId() != 0) {
            $this->generate_cyclic_invoices($application->getId(), $application->getServiceId());
        } else {
            $this->update_invoices($application->getId());
        }
    }
    //Note Start Patch - For Implementing Dynamic Fees
    public function getFeeAmount($fee, $application_form, $application_form_id, $estimate_requested = false)
    {
        $fee_amount = 0;
        $base_field_value = str_replace(',', '', $this->getFieldValue($fee->getBaseField(), $application_form, $application_form_id));

        // extract integers from string
        $base_field_value = preg_replace("/[^0-9\.]/", '', $base_field_value);

        if ($fee->getFeeType() == 'percentage') {

            $fee_amount = (floatval($fee->getAmount()) / 100) * floatval($base_field_value);
        } else if ($fee->getFeeType() == 'range' or $fee->getFeeType() == 'range_percentage') {
            $q = Doctrine_Query::create()
                ->from('FeeRange a')
                ->where('a.fee_id = ?', $fee->getId())
                ->orderBy('a.id ASC');
            $fee_ranges = $q->execute();
            foreach ($fee_ranges as $r) {
                $condition_met = false;
                //Get condition matching result

                $q = Doctrine_Query::create()
                    ->from('FeeRangeCondition a')
                    ->where('a.fee_range_id = ?', $r->getId())
                    ->orderBy('a.id ASC');
                $fee_range_conditions = $q->execute();

                $condition_met_array = array();
                foreach ($fee_range_conditions as $f_con) {
                    $coniditon_field_value = $f_con->getConditionField() ? $this->getFieldValue($f_con->getConditionField(), $application_form, $application_form_id) : false;
                    // error_log("<========== Fee Id =====>" . $fee->id);
                    // error_log("Condition field value --->" . $coniditon_field_value);
                    // error_log("getConditionOperator operator --->" . $f_con->getConditionOperator());
                    // error_log("getConditionValue value --->" . $f_con->getConditionValue());
                    // error_log("getConditionField field --->" . $f_con->getConditionField());
                    if ($coniditon_field_value && $f_con->getConditionOperator() == 1) {
                        $condition_met = strtolower(str_replace(" ", "", $f_con->getConditionValue())) == strtolower(str_replace(" ", "", $coniditon_field_value)) ? true : false;
                    } else if ($coniditon_field_value && $f_con->getConditionOperator() == 2) {
                        $condition_met = $coniditon_field_value < $f_con->getConditionValue() ? true : false;
                    } else if ($coniditon_field_value && $f_con->getConditionOperator() == 3) {
                        $condition_met = $coniditon_field_value > $f_con->getConditionValue() ? true : false;
                    } else if ($coniditon_field_value && $f_con->getConditionOperator() == 4) {
                        $condition_met = $coniditon_field_value <= $f_con->getConditionValue() ? true : false;
                    } else if ($coniditon_field_value && $f_con->getConditionOperator() == 5) {
                        $condition_met = $coniditon_field_value >= $f_con->getConditionValue() ? true : false;
                    } else if ($coniditon_field_value && $f_con->getConditionOperator() == 6) {
                        $condition_met = str_replace(' ', '', strtolower($f_con->getConditionValue())) !== str_replace(' ', '', strtolower($coniditon_field_value));
                    } else if ($coniditon_field_value && $f_con->getConditionOperator() == 7) {
                        // implement using arrays
                        $conditional_array = explode(":", $f_con->getConditionValue());
                        if (count($conditional_array) > 1) {
                            $cleaned_array = array_map('trim', $conditional_array);
                            $conditon_field_value = trim($coniditon_field_value);
                            $condition_met = in_array(strtolower($conditon_field_value), array_map('strtolower', $cleaned_array));
                        } else {
                            $needle = strtolower(str_replace(" ", "", $f_con->getConditionValue()));
                            $haystack = strtolower(str_replace(" ", "", $coniditon_field_value));
                            $condition_met =  strpos($haystack, $needle) !== false ? true : false;
                        }
                        // error_log("===>Is like field checking for fee --->" . $fee->getId() . " Condition met --->" . $condition_met);
                    } else if (!$coniditon_field_value && $f_con->getConditionField()) {
                        $condition_met = false;
                    } else if (!$f_con->getConditionField()) {
                        $condition_met = true;
                    }
                    // error_log("Condition checked and it " . $condition_met . "\n");

                    array_push($condition_met_array, $condition_met);
                    $condition_met = false;
                }
                // error_log("condition array values --->" . print_r($condition_met_array, true));
                // error_log("\n\n");
                if (count($condition_met_array) > 0) {
                    if ($r->getConditionSetOperator() == "and") {
                        $condition_met = !in_array(false, $condition_met_array); //all the conditions must be met i.e. true, if the configuration is "All of the conditions are met"
                        // error_log("Condition's are meant ---->" . $condition_met);
                    } else {
                        $condition_met = in_array(true, $condition_met_array); //all the conditions do not have to be met i.e. true, if the configuration is "Any of the conditions are met"
                    }
                } else {
                    $condition_met = true;
                }
                // error_log('---Final condition --' . $condition_met);
                // error_log('Get the value type --->' . $r->getValueType());

                if ($r->getValueType() == "formula") {
                    //require_once("vendor/Noteafrica/eos-1.0.0/eos.class.php");
                    //$eq = new eqEOS();
                    $equation = str_replace('{base_field}', $base_field_value, $r->getResultValue());
                    $equation = str_replace('{conditon_value}', $coniditon_field_value, $equation);

                    $equation = $this->parseFormula($application_form, $application_form_id, $equation, $estimate_requested);
                    $range_result = Parser::solveIF($equation);

                    // error_log("Formula Range Result --->" . $range_result);
                } else {
                    $range_result = $r->getResultValue();
                }
                $range_result = round($range_result); //round off to 3 decimal places
                // error_log('-----Range value --' . $range_result);
                if ($base_field_value && $condition_met) {
                    if ($fee->getFeeType() == 'range_percentage') {
                        $fee_amount += ($range_result / 100) * ($base_field_value);
                    } else {
                        $fee_amount += $range_result;
                    }
                } else if (!$base_field_value && $condition_met) {
                    $fee_amount += $range_result;
                }
                // error_log('---Fee amount --' . $fee_amount);
            }
            if ($fee_amount < $fee->getMinimumFee()) {
                $fee_amount = $fee->getMinimumFee();
            }
            // error_log('---Fee amount after check minimum fee' . $fee->getMinimumFee() . ' --' . $fee_amount);
        } else if ($fee->getFeeType() == 'formula') {
            //require_once("vendor/Noteafrica/eos-1.0.0/eos.class.php");
            //$eq = new eqEOS();
            error_log("Am at this point ---- 6");
            $equation = str_replace('{base_field}', $base_field_value, $fee->getAmount());
            $equation = $this->parseFormula($application_form, $application_form_id, $equation, $estimate_requested);
            $fee_amount = Parser::solveIF($equation);
            $fee_amount = round($fee_amount); //round off to 3 decimal places
        } else {
            $fee_amount = $fee->getAmount();
        }
        return round($fee_amount);
    }
    public function parseFormula($application_form, $application_form_id, $formula, $estimate_requested = false)
    {
        if ($estimate_requested) {
            foreach ($application_form as $key => $value) {
                if (strpos($formula, '{fm_' . $key . '}') !== false) {
                    $field_id = explode("element_", $key);
                    $field_value = $this->getFieldValue(explode('element_', $key)[1], $application_form, $application_form_id);
                    $formula = str_replace('{fm_' . $key . '}', $field_value, $formula);
                }
            }
            return $formula;
        } else {
            error_log('-----------Form Id PArseFormula----' . $application_form_id . '--------------Entry Id-----' . $application_form['id']);
            $templateparser = new TemplateParser();
            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.form_id = ? and a.entry_id = ?', array($application_form_id, $application_form['id']))
                ->orderBy('a.id ASC');
            $app = $q->fetchOne();
            error_log('--------Form ID PArseFormula----' . $app->getId() . "The formula ---->" . $formula);
            return $templateparser->parse($app->getId(), $application_form_id, $application_form['id'], $formula);
        }
    }

    public function getFieldValue($element_id, $application_form, $application_form_id)
    {
        if ($element_id) {
            $q = Doctrine_Query::create()
                ->from('ApFormElements a')
                ->where('a.element_id = ?', $element_id)
                ->andWhere('a.form_id = ?', $application_form_id)
                ->andWhereIn('a.element_type', array('select', 'checkbox', 'radio', 'simple_name'));
            $option_elements = $q->fetchOne();
            if ($option_elements) {
                if ($option_elements->getElementType() == 'select' || $option_elements->getElementType() == 'radio') {
                    $q = Doctrine_Query::create()
                        ->from('ApElementOptions a')
                        ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ? and live=1', array($application_form_id, $element_id, $application_form['element_' . $element_id]));
                    $option = $q->fetchOne();
                    return $option ? $option->getOptionText() : False;
                } else if ($option_elements->getElementType() == 'simple_name') {
                    $name = $application_form['element_' . $element_id . '_1'] . $application_form['element_' . $element_id . '_2'];
                    return $name;
                } else if ($option_elements->getElementType() == 'checkbox') { //for checkbox, return values as strings separated by commas
                    $q = Doctrine_Query::create()
                        ->from('ApElementOptions a')
                        ->where('a.form_id = ? AND a.element_id = ? and live=1', array($application_form_id, $element_id));
                    $options = $q->execute();
                    $optionsString = false;
                    foreach ($options as $option) {
                        if ($application_form['element_' . $element_id . '_' . $option->getOptionId()] == 1) {
                            $optionsString .= $option->getOptionText() . ",";
                        }
                    }
                    $optionsString = rtrim($optionsString, ",");
                    return $optionsString ? $optionsString : False;
                }
            } else {
                return $application_form['element_' . $element_id];
            }
        } else {
            return False;
        }
    }
    //Note End Patch - For Implementing Dynamic Fees

    //Note Start Patch - Only get invoices in an 'invoicing' type stage, where payments can be made
    public function invoice_can_be_paid($invoice)
    {
        $q = Doctrine_Query::create()
            ->from("SubMenus a")
            ->where("a.stage_type = 3");
        $payment_stage_ids = array_column($q->fetchArray(), 'id');

        if (in_array($invoice->getFormEntry()->getApproved(), $payment_stage_ids)) {
            return true;
        } else {
            return false;
        }
    }
    //Note End Patch - Only get invoices in an 'invoicing' type stage, where payments can be made\

    /* Note - Start Calculator */
    public function getEstimationInputFields($form_id)
    {
        $Note_helper = new NoteHelper();
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where('a.applicationform = ?', $form_id)
            ->orderBy('a.id ASC');
        $inv_templates = $q->execute();
        $element_ids = array('fees_exist' => 'no');

        foreach ($inv_templates as $inv_template) {

            $q = Doctrine_Query::create()
                ->from('Fee a')
                ->where('a.invoiceid = ?', $inv_template->getId())
                ->orderBy('a.id ASC');
            $fees = $q->execute();

            if (count($fees) > 0) {
                $element_ids['fees_exist'] = 'yes';
            }
            foreach ($fees as $fee) {
                if ($fee->getBaseField() and !in_array($fee->getBaseField(), $element_ids)) {
                    array_push($element_ids, $fee->getBaseField());
                }

                $element_ids = $this->getElementsInFormula($form_id, $fee->getAmount(), $element_ids); //Get fields specified if fee amount is a formula
                if ($fee->getFeeType() == "range" or $fee->getFeeType() == "range_percentage") {
                    $q = Doctrine_Query::create()
                        ->from('FeeRange a')
                        ->where('a.fee_id = ?', $fee->getId())
                        ->orderBy('a.id ASC');
                    $fee_ranges = $q->execute();
                    foreach ($fee_ranges as $r) {
                        $q = Doctrine_Query::create()
                            ->from('FeeRangeCondition a')
                            ->where('a.fee_range_id = ?', $r->getId())
                            ->orderBy('a.id ASC');
                        $fee_range_conditions = $q->execute();
                        foreach ($fee_range_conditions as $f_con) {
                            if ($f_con->getConditionField() and !in_array($f_con->getConditionField(), $element_ids)) {
                                array_push($element_ids, $f_con->getConditionField());
                            }
                        }
                        $element_ids = $this->getElementsInFormula($form_id, $r->getResultValue(), $element_ids); //Get fields specified if value of range condition is a formula
                    }
                }
            }
        }
        return $element_ids;
    }

    public function getElementsInFormula($form_id, $formula, $element_ids)
    {
        $Note_helper = new NoteHelper();
        foreach ($Note_helper->getFormElements($form_id) as $element) {
            if (strpos($formula, '{fm_element_' . $element->getElementId() . '}') !== false and !in_array($element->getElementId(), $element_ids)) {
                array_push($element_ids, $element->getElementId());
            }
        }

        return $element_ids;
    }

    public function calculateEstimate($input_data, $form_id)
    {
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where('a.applicationform = ?', $form_id)
            ->orderBy('a.id ASC');
        $inv_templates = $q->execute();
        $fee_arr = array();
        foreach ($inv_templates as $inv_template) {
            $fee_arr[$inv_template->getId()]['total_amount'] = 0;
            $fee_arr[$inv_template->getId()]['title'] = $inv_template->getTitle();
            $q = Doctrine_Query::create()
                ->from('Fee a')
                ->where('a.invoiceid = ?', $inv_template->getId())
                ->andWhere('a.submission_fee = ?', false)
                ->orderBy('a.id ASC');
            $fees = $q->execute();
            foreach ($fees as $fee) {
                $fee_arr[$inv_template->getId()]['total_amount'] += $this->getFeeAmount($fee, $input_data, $form_id, $input_data);
            }
        }
        return $fee_arr;
    }
    /* Note - End Calculator */
    //Note Start - Use Expired stage settings to reset expired invoice accordingly - originated from Kisumu requirements
    public function update_expired_invoices($application_id, $stage_expired_invoice_action)
    {
        $application = $this->get_application_by_id($application_id);

        if ($application) {
            foreach ($application->getMfInvoice() as $invoice) {
                //Note only update for expired invoices
                if ($this->is_invoice_expired($invoice->getId())) {
                    if ($stage_expired_invoice_action == 1) {
                        $invoice->setPaid(1);
                    } else if ($stage_expired_invoice_action == 2) {
                        $invoice->setPaid(15);
                    } else if ($stage_expired_invoice_action == 3) {
                        $invoice->setPaid(2);
                    } else if ($stage_expired_invoice_action == 4) {
                        $q = Doctrine_Query::create()
                            ->from('Invoicetemplates a')
                            ->where("a.applicationform = ?", $application->getFormId())
                            ->limit(1);
                        $invoicetemplate = $q->fetchOne();

                        $expires_at = date("Y-m-d H:i:s");

                        if ($invoicetemplate->getMaxDuration()) {
                            $date = strtotime("+" . $invoicetemplate->getMaxDuration() . " day", time());
                            $expires_at = date('Y-m-d', $date);
                        } else if ($invoicetemplate->getDueDuration()) {
                            $date = strtotime("+" . $invoicetemplate->getDueDuration() . " day", time());
                            $expires_at = date('Y-m-d', $date);
                        } else {
                            $date = strtotime("+" . 30 . " day", time());
                            $expires_at = date('Y-m-d', $date);
                        }

                        $invoice->setPaid(1);
                        $invoice->setDueDate(date("Y-m-d H:i:s"));
                        $invoice->setExpiresAt($expires_at);
                    }
                    $invoice->save();
                }
            }
        } else {
            error_log('---------No APP----' . $application_id);
        }
    }
    //Note End - Use Expired stage settings to reset expired invoice accordingly - originated from Kisumu requirements

    //output invoice to html using the old parser
    public function generate_invoice_template_old_parser($invoice_id, $pdf = false)
    {
        $templateparser = new TemplateParser();

        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $invoice = $q->fetchOne();

        //get application, if its in payment confirmation then move to submissions
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $invoice->getAppId())
            ->limit(1);
        $application = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where("a.applicationform = ?", $application->getFormId())
            ->limit(1);
        $invoicetemplate = $q->fetchOne();

        $html = "<html>
			<body>
			";

        $expired = false;
        $cancelled = false;

        $db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

        $db_date_event = strtotime($db_date_event);

        if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3")) {
            $expired = true;
        }

        if ($invoice->getPaid() == "3") {
            $cancelled = true;
        }

        $invoice_content = $templateparser->parseInvoice($application->getId(), $application->getFormId(), $application->getEntryId(), $invoice->getId(), $invoicetemplate->getContent());

        if ($pdf) {
            $ssl_suffix = "s";

            if (empty($_SERVER['HTTPS'])) {
                $ssl_suffix = "";
            }

            //replace src=" for images with src="http://localhost
            $invoice_content = str_replace('src="/', 'src="http' . $ssl_suffix . '://' . $_SERVER['HTTP_HOST'] . '/', $invoice_content);
        }

        $html .= $invoice_content;

        $html .= "
			</body>
			</html>";

        return html_entity_decode($html);
    }

    //output invoice to pdf

    //Returns an existing invoice
    public function get_invoice_by_invoice_number($invoice_number)
    {
        error_log("Invoice Number ---->");
        error_log($invoice_number);
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.invoice_number LIKE ?', $invoice_number)
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) //Already submitted then tell client its already submitted
        {
            return $existing_invoice;
        } else {
            return false;
        }
    }

    public function getInvoiceByNumberTransactionMessageId($invoice_no, $message_id, $transaction_id)
    {
        /*  // spliting number 
        $parts = explode("-", $invoice_no);
        //
        $result = $invoice_no;
        //
        if (count($parts) >= 4) {
            $result = implode("-", array_slice($parts, 0, -1));
            //echo $result;
        } else {
            $result = $invoice_no;
        } */
        //
        error_log("Debug>>> getInvoiceByNumberTransactionMessageId ::::: " . $transaction_id);
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            //->where('a.invoice_number LIKE ?', $invoice_no)
            //->andWhere('a.message_id LIKE ?', $message_id)
            ->where('a.transaction_id LIKE ?', $transaction_id)
            ->limit(1);
        $existing_invoice = $q->fetchOne();
        if ($existing_invoice) {
            //Already submitted then tell client its already submitted {
            return $existing_invoice;
        } else {
            return false;
        }
    }
    public function getTransactionNumber($invoice_id)
    {
        return uniqid($invoice_id);
    }
    //
    public function check_total_amount_status($invoice_id, $amount = 0)
    {
        //check if partial is allowed
        error_log("Debug:::: Invoice ID >>>>>>>>>>> " . $invoice_id);
        $invoice = $this->get_invoice_by_id($invoice_id);
        $q = Doctrine_Query::create()
            ->select('SUM(p.payment_amount) as total')
            ->from('ApFormPayments p');
        if (intval($invoice->getInvoicetemplates()->getPaymentType()) === 1) {
            //full
            if (floatval($invoice->getTotalAmount()) >= floatval($amount)) {
                //return paid
                error_log('------TOTAL Amount match 1----- ' . $invoice->getTotalAmount());
                error_log('------TOTAL Amount match 2----- ' . floatval($amount));
                return 2;
            } else {
                $q->where('p.invoice_id = ? and p.status = ?', array($invoice_id, 2));
                $payments = $q->fetchArray();

                error_log('------TOTAL Amount does not match 1----- ' . $invoice->getTotalAmount());
                error_log('------TOTAL Amount does not match 2----- ' . floatval($amount));
                //check payments
                if (floatval($payments[0]['total']) >= floatval($invoice->getTotalAmount())) {
                    return 2;
                } else {
                    //failed
                    return 3;
                }
            }
        } elseif (intval($invoice->getInvoicetemplates()->getPaymentType()) === 2) {
            error_log('----part payment-----');
            //partial
            //get all part payment records
            $q->where('p.invoice_id = ? and p.status = ?', array($invoice_id, 3));
            $payments = $q->fetchArray();
            $total_amount = 0;
            $total_amount += floatval($payments[0]['total']) + floatval($amount);
            error_log('-----total_amount-----' . $total_amount);
            if ($total_amount >= floatval($invoice->getTotalAmount())) {
                error_log('-----Total >= invoice amount------');
                return 2;
            } else {
                error_log('-----Total < invoice amount------');
                return 1;
            }
        }
    }
}
