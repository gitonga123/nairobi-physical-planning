<?php

/**
 * invoices actions.
 *
 * @package    permit
 * @subpackage invoices
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class invoicesActions extends sfActions
{
    //Used by ajax to check remote payment gateway for bill status
    public function executeReconcile(sfWebRequest $request)
    {
        $invoice_manager = new InvoiceManager();
        $result = $invoice_manager->remote_reconcile($request->getParameter("reference"));

        if ($result == "paid") {
            $invoice = $invoice_manager->get_invoice_by_reference($request->getParameter("reference"));

            //If response is paid, then mark invoice as paid
            if ($invoice && $invoice->getPaid() != 2) {
                $invoice->setPaid(2);
                $invoice->save();
            }
        } else {
            $invoice = $invoice_manager->get_invoice_by_reference($request->getParameter("reference"));

            //If response is pending, then marked invoice as pending if it was marked as paid
            if ($invoice && $invoice->getPaid() == 2) {
                $invoice->setPaid(15);
                $invoice->save();
            }
        }

        $this->setLayout(false);
        echo $result;
        exit;
    }

    public function executeBarcode(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.id = ?", $request->getParameter("id"));
        $invoice = $q->fetchOne();

        if ($request->getParameter("size")) {
            $_GET["size"] = $request->getParameter("size");
        }

        // Get pararameters that are passed in through $_GET or set to the default value
        $text = $invoice->getFormEntry()->getApplicationId() . ": " . $invoice->getTotalAmount();
        $size = (isset($_GET["size"]) ? $_GET["size"] : "20");
        $orientation = (isset($_GET["orientation"]) ? $_GET["orientation"] : "horizontal");
        $code_type = (isset($_GET["codetype"]) ? $_GET["codetype"] : "code128");
        $code_string = "";
        // Translate the $text into barcode the correct $code_type
        if (in_array(strtolower($code_type), array("code128", "code128b"))) {
            $chksum = 104;
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
            $code_string = "211214" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code128a") {
            $chksum = 103;
            $text = strtoupper($text); // Code 128A doesn't support lower case
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
            $code_string = "211412" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code39") {
            $code_array = array("0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211");
            // Convert to uppercase
            $upper_text = strtoupper($text);
            for ($X = 1; $X <= strlen($upper_text); $X++) {
                $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
            }
            $code_string = "1211212111" . $code_string . "121121211";
        } elseif (strtolower($code_type) == "code25") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
            $code_array2 = array("3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1");
            for ($X = 1; $X <= strlen($text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($text, ($X - 1), 1) == $code_array1[$Y])
                        $temp[$X] = $code_array2[$Y];
                }
            }
            for ($X = 1; $X <= strlen($text); $X += 2) {
                if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                    $temp1 = explode("-", $temp[$X]);
                    $temp2 = explode("-", $temp[($X + 1)]);
                    for ($Y = 0; $Y < count($temp1); $Y++)
                        $code_string .= $temp1[$Y] . $temp2[$Y];
                }
            }
            $code_string = "1111" . $code_string . "311";
        } elseif (strtolower($code_type) == "codabar") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D");
            $code_array2 = array("1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221");
            // Convert to uppercase
            $upper_text = strtoupper($text);
            for ($X = 1; $X <= strlen($upper_text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y])
                        $code_string .= $code_array2[$Y] . "1";
                }
            }
            $code_string = "11221211" . $code_string . "1122121";
        }
        // Pad the edges of the barcode
        $code_length = 20;
        for ($i = 1; $i <= strlen($code_string); $i++)
            $code_length = $code_length + (int) (substr($code_string, ($i - 1), 1));
        if (strtolower($orientation) == "horizontal") {
            $img_width = $code_length;
            $img_height = $size;
        } else {
            $img_width = $size;
            $img_height = $code_length;
        }
        $image = imagecreate($img_width, $img_height);
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++) {
            $cur_size = $location + (substr($code_string, ($position - 1), 1));
            if (strtolower($orientation) == "horizontal")
                imagefilledrectangle($image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black));
            else
                imagefilledrectangle($image, 0, $location, $img_width, $cur_size, ($position % 2 == 0 ? $white : $black));
            $location = $cur_size;
        }
        // Draw barcode to the screen
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);

        $this->setLayout(false);
        exit;
    }

    public function executeIndex(sfWebRequest $request)
    {
        $invoice_manager = new InvoiceManager();
        $agency_manager = new AgencyManager();

        $this->filter_status = $request->getParameter("filter_status");
        $this->filter = $request->getParameter("filter"); //OTB code refactoring
        $this->fromdate = $request->getParameter("fromdate") ? date('Y-m-d', strtotime($request->getParameter("fromdate"))) : false; //OTB code refactoring
        $this->todate = $request->getParameter("todate") ? date('Y-m-d', strtotime($request->getParameter("todate"))) : false; //OTB code refactoring
        if (!$request->isXmlHttpRequest()) {
            $this->getUser()->setAttribute('filter_status_inv', $this->filter_status);
            $this->getUser()->setAttribute('filter_inv', $this->filter);
            $this->getUser()->setAttribute('fromdate_inv', $this->fromdate);
            $this->getUser()->setAttribute('todate_inv', $this->todate);
        }
        $q_form = Doctrine_Query::create()
            ->from('ApForms a')
            ->where('a.form_active = ? AND a.form_type = ? AND a.payment_enable_merchant = ?', [1, 1, 1])
            ->orderBy('a.form_name ASC');
        $applicationforms = $q_form->execute();
        $agency_manager = new AgencyManager();
        $this->form_options = [];
        foreach ($applicationforms as $applicationform) {
            if ($agency_manager->checkAgencyStageAccess($this->getUser()->getAttribute('userid'), $applicationform->getFormStage())) { //OTB - Managing agency access
                $selected = "";
                if ($applicationform->getFormId() == $this->filter) {
                    $selected = 'selected="selected"';
                }

                $this->form_options[] = '<option value="' . $applicationform->getFormId() . '" ' . $selected . '>' . $applicationform->getFormName() . '</option>';
            }
        }
        if ($request->getParameter("export")) {
            $columns = [];
            $columns[] = "#";
            $columns[] = 'Form Title';
            $columns[] = 'Application Id';
            $columns[] = 'Applicant';
            $columns[] = 'Stage';
            $columns[] = 'Invoice No.';
            $columns[] = 'Date registered';
            $columns[] = 'Date paid';
            $columns[] = 'Date of expiry';
            $columns[] = 'Payee';
            $columns[] = 'Amount';
            $columns[] = 'Currency';
            $columns[] = 'Status';

            $records = [];
            $q = $this->_invoiceQuery();
            foreach ($q->execute() as $invoice) {
                $data = [];
                $data[] = $invoice->getId();
                $data[] = $invoice->getFormEntry()->getForm()->getFormName();
                $data[] = $invoice->getFormEntry()->getApplicationId();
                if ($invoice->getFormEntry()->getSfGuardUserProfile()) {
                    $data[] = $invoice->getFormEntry()->getSfGuardUserProfile()->getFullname();
                } else {
                    $data[] = '';
                }
                if ($invoice->getFormEntry()->getStage()) {
                    $data[] = $invoice->getFormEntry()->getStage()->getTitle();
                } else {
                    $data[] = '';
                }
                $data[] = $invoice->getInvoiceNumber();
                $data[] = date('jS M Y H:i:s', strtotime($invoice->getCreatedAt()));
                if ($invoice->getUpdatedAt()) {
                    $data[] = date('jS M Y H:i:s', strtotime($invoice->getUpdatedAt()));
                }
                if ($invoice->getExpiresAt()) {
                    $data[] = date('jS M Y H:i:s', strtotime($invoice->getExpiresAt()));
                }
                $data[] = $invoice->getPayerName();
                $data[] = $invoice->getTotalAmount();
                $data[] = $invoice->getCurrency();
                $data[] = $invoice->getStatus();

                $records[] = $data;
            }

            Outputsheet::ReportGenerator("Invoice Report -" . date("Y-m-d"), $columns, $records);
            exit;
        }

        if ($request->isXmlHttpRequest() || $request->getParameter('draw')) {
            //columns
            $columns = array('i.id', 'f.form_name', 'e.application_id', 'p.fullname', 's.title', 'i.invoice_number', 'i.created_at', 'i.updated_at', 'i.expires_at', 'i.payer_name', 'i.total_amount', 'i.currency', 'i.paid');
            $q = $this->_invoiceQuery($columns, $request);
            $result = array(
                "draw" => intval($request->getParameter('draw')),
                "recordsTotal" => $this->_invoiceQuery(null, $request)->count(),
                "recordsFiltered" => $q->count(),
                "data" => []
            );
            //ORDER
            $q->orderBy($columns[$request->getParameter('order')[0]['column']] . ' ' . $request->getParameter('order')[0]['dir']);
            //For pagination
            $q->offset($request->getParameter('start'));
            $q->limit($request->getParameter('length'));

            foreach ($q->execute() as $invoice) {
                error_log('------INVOICE ID------' . $invoice->getId());
                $data = new stdClass;
                $data->id = $invoice->getId();
                $data->form_name = $invoice->getFormEntry()->getForm()->getFormName();
                $data->inv_no = $invoice->getInvoiceNumber();
                $data->inv_id = $invoice->getId();
                $data->payee = $invoice->getPayerName();
                $data->amount = $invoice->getTotalAmount();
                $data->currency = $invoice->getCurrency();
                $data->status = $invoice->getStatus();
                $data->date_created = date('jS M Y H:i:s', strtotime($invoice->getCreatedAt()));
                if ($invoice->getUpdatedAt()) {
                    $data->updated_date = date('jS M Y H:i:s', strtotime($invoice->getUpdatedAt()));
                }
                if ($invoice->getExpiresAt()) {
                    $data->expiry_date = date('jS M Y H:i:s', strtotime($invoice->getExpiresAt()));
                }
                $data->application_id = $invoice->getFormEntry()->getApplicationId();
                $data->app_id = $invoice->getFormEntry()->getId();
                if ($invoice->getFormEntry()->getSfGuardUserProfile()) {
                    $data->user = $invoice->getFormEntry()->getSfGuardUserProfile()->getFullname();
                } else {
                    $data->user = '';
                }
                if ($invoice->getFormEntry()->getStage()) {
                    $data->stage = $invoice->getFormEntry()->getStage()->getTitle();
                } else {
                    $data->stage = '';
                }
                $result['data'][] = $data;
            }
            $this->getResponse()->setContent(json_encode($result));
            return sfView::NONE;
        }
        $this->setLayout('layout');
    }

    public function executeTransactions(sfWebRequest $request)
    {
        $this->filter_status = $request->getParameter("filter_status", 2);
        $this->filter = $request->getParameter("filter"); //OTB code refactoring
        $this->fromdate = $request->getParameter("fromdate") ? date('Y-m-d', strtotime($request->getParameter("fromdate"))) : false; //OTB code refactoring
        $this->todate = $request->getParameter("todate") ? date('Y-m-d', strtotime($request->getParameter("todate"))) : false; //OTB code refactoring
        if (!$request->isXmlHttpRequest()) {
            $this->getUser()->setAttribute('filter_status_', $this->filter_status);
            $this->getUser()->setAttribute('filter_', $this->filter);
            $this->getUser()->setAttribute('fromdate_', $this->fromdate);
            $this->getUser()->setAttribute('todate_', $this->todate);
        }
        $q_form = Doctrine_Query::create()
            ->from('ApForms a')
            ->where('a.form_active = ? AND a.form_type = ? AND a.payment_enable_merchant = ?', [1, 1, 1])
            ->orderBy('a.form_name ASC');
        $applicationforms = $q_form->execute();
        $agency_manager = new AgencyManager();
        $this->form_options = [];
        foreach ($applicationforms as $applicationform) {
            if ($agency_manager->checkAgencyStageAccess($this->getUser()->getAttribute('userid'), $applicationform->getFormStage())) { //OTB - Managing agency access
                $selected = "";
                if ($applicationform->getFormId() == $this->filter) {
                    $selected = 'selected="selected"';
                }

                $this->form_options[] = '<option value="' . $applicationform->getFormId() . '" ' . $selected . '>' . $applicationform->getFormName() . '</option>';
            }
        }



        if ($request->getParameter("export")) {
            $columns = [];
            $columns[] = "#";
            $columns[] = 'Form Title';
            $columns[] = 'Application Id';
            $columns[] = 'Invoice No.';
            $columns[] = 'Payment Id';
            $columns[] = 'Date registered';
            $columns[] = 'Date paid';
            $columns[] = 'Payee';
            $columns[] = 'Amount';
            $columns[] = 'Currency';
            $columns[] = 'Merchant';
            $columns[] = 'Status';

            $records = [];
            $q = $this->_paymentsQuery();
            foreach ($q->execute() as $payment) {
                $data = [];
                $data[] = $payment->getAfpId();
                $data[] = $payment->getApForms()->getFormName();
                if ($payment->getMfInvoice()) {
                    $data[] = $payment->getMfInvoice()->getInvoiceNumber();
                    $data[] = $payment->getMfInvoice()->getFormEntry()->getApplicationId();
                } else {
                    $merchant_reference = explode("/", $payment->getPaymentId());
                    $invoice = Doctrine_Core::getTable('MfInvoice')->find($merchant_reference[2]);
                    if ($invoice) {
                        $data[] = $invoice->getInvoiceNumber();
                        $data[] = $invoice->getFormEntry()->getApplicationId();
                    } else {
                        $data[] = "";
                        $data[] = "";
                    }
                }
                $data[] = $payment->getPaymentId();
                $data[] = date('jS M Y H:i:s', strtotime($payment->getDateCreated()));
                if ($payment->getPaymentDate()) {
                    $data[] = date('jS M Y H:i:s', strtotime($payment->getPaymentDate()));
                } else {
                    $data[] = 'N/A';
                }
                $data[] = $payment->getPaymentFullname();
                $data[] = $payment->getPaymentAmount();
                $data[] = $payment->getPaymentCurrency();
                $data[] = $payment->getPaymentMerchantType();
                $data[] = $payment->paymentstatus();
                $records[] = $data;
            }

            Outputsheet::ReportGenerator("Payments Report -" . date("Y-m-d"), $columns, $records);
            exit;
        }
        if ($request->isXmlHttpRequest() || $request->getParameter('draw')) {
            //columns
            $columns = array('a.afp_id', 'f.form_name', 'e.application_id', 'i.invoice_number', 'a.payment_id', 'a.payment_fullname', 'a.payment_amount', 'a.payment_currency', 'a.payment_merchant_type', 'a.status');
            $q = $this->_paymentsQuery($columns, $request);
            $result = array(
                "draw" => intval($request->getParameter('draw')),
                "recordsTotal" => $this->_paymentsQuery(null, $request)->count(),
                "recordsFiltered" => $q->count(),
                "data" => []
            );
            //ORDER
            $q->orderBy($columns[$request->getParameter('order')[0]['column']] . ' ' . $request->getParameter('order')[0]['dir']);
            //For pagination
            $q->offset($request->getParameter('start'));
            $q->limit($request->getParameter('length'));

            foreach ($q->execute() as $payment) {
                $data = new stdClass;
                $data->id = $payment->getAfpId();
                $data->form_name = $payment->getApForms()->getFormName();
                $data->inv_no = '';
                $data->inv_id = '';
                $data->application_id = '';
                $data->app_id = '';
                if ($payment->getMfInvoice()) {
                    $data->inv_no = $payment->getMfInvoice()->getInvoiceNumber();
                    $data->inv_id = $payment->getMfInvoice()->getId();
                    $data->application_id = $payment->getMfInvoice()->getFormEntry()->getApplicationId();
                    $data->app_id = $payment->getMfInvoice()->getFormEntry()->getId();
                } else {
                    $merchant_reference = explode("/", $payment->getPaymentId());
                    $invoice = Doctrine_Core::getTable('MfInvoice')->find($merchant_reference[2]);
                    if ($invoice) {
                        $data->inv_no = $invoice->getInvoiceNumber();
                        $data->inv_id = $invoice->getId();
                        $data->application_id = $invoice->getFormEntry()->getApplicationId();
                        $data->app_id = $invoice->getFormEntry()->getId();
                    }
                }
                $data->payment_id = $payment->getPaymentId();
                $data->data_created = date('jS M Y H:i:s', strtotime($payment->getDateCreated()));
                if ($payment->getPaymentDate()) {
                    $data->payment_date = date('jS M Y H:i:s', strtotime($payment->getPaymentDate()));
                } else {
                    $data->payment_date = 'N/A';
                }
                $data->payee = $payment->getPaymentFullname();
                $data->amount = $payment->getPaymentAmount();
                $data->currency = $payment->getPaymentCurrency();
                $data->merchant = $payment->getPaymentMerchantType();
                $data->status = $payment->paymentstatus();
                $result['data'][] = $data;
            }
            $this->getResponse()->setContent(json_encode($result));
            return sfView::NONE;
        }
        $this->setLayout('layout');
    }

    public function executeReport(sfWebRequest $request)
    {
        $payment_status = $request->getParameter("filter_status", 2);

        $this->fromdate = date('Y-m-d', strtotime($request->getPostParameter("fromdate")));
        $this->todate = date('Y-m-d', strtotime($request->getPostParameter("todate")));

        if ($request->getPostParameter("application_form")) {
            $this->q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.form_id = ?", $request->getPostParameter("application_form"))
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->orderBy("a.payment_date DESC");
            $this->filter = $request->getParameter("filter");

            $qt = Doctrine_Query::create()
                ->select('SUM(a.payment_amount) as total')
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.form_id = ?", $request->getPostParameter("application_form"))
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"));
            $this->total = $qt->fetchOne();
        } elseif ($request->getPostParameter("application_service")) {
            $sub_menus_array = array();

            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.menu_id = ?", $request->getPostParameter("application_service"));
            $stages = $q->execute();

            foreach ($stages as $stage) {
                $sub_menus_array[] = $stage->getId();
            }

            $sub_menus_query = implode(" OR b.approved = ", $sub_menus_array);

            $this->q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->leftJoin("a.FormEntry b")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->andWhere("a.record_id = b.entry_id")
                ->andWhere("b.approved = " . $sub_menus_query)
                ->orderBy("a.payment_date DESC");

            $qt = Doctrine_Query::create()
                ->select('SUM(a.payment_amount) as total')
                ->from("ApFormPayments a")
                ->leftJoin("a.FormEntry b")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->andWhere("a.record_id = b.entry_id")
                ->andWhere("b.approved = " . $sub_menus_query);
            $this->total = $qt->fetchOne();
        } else {
            $this->q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->orderBy("a.payment_date DESC");

            $qt = Doctrine_Query::create()
                ->select('SUM(a.payment_amount) as total')
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"));
            $this->total = $qt->fetchOne();
        }

        $columns = "";
        $columns[] = "Date Of Issue";
        $columns[] = "Invoice No";
        $columns[] = "Service Code";
        $columns[] = "Application";
        $columns[] = "User";
        $columns[] = "Phone Number";
        $columns[] = "Fee";
        $columns[] = "Reference Number";
        $columns[] = "Payment Mode";
        $columns[] = "Payment Status";

        $records = "";

        $payments = $this->q->execute();

        $grand_total_amount = 0;

        foreach ($payments as $payment) {
            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->where("a.form_id = ? and a.entry_id = ?", array($payment->getFormId(), $payment->getRecordId()));
            $application = $q->fetchOne();
            if ($application) {

                $merchant_reference = explode("/", $payment->getPaymentId());

                $q = Doctrine_Query::create()
                    ->from("MfInvoice a")
                    ->where("a.id = ?", $merchant_reference[2]);
                $invoice = $q->fetchOne();

                if ($invoice == null) {
                    continue;
                }

                $record_columns = "";

                $record_columns[] = $payment->getPaymentDate();
                $record_columns[] = $invoice->getInvoiceNumber();

                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_id = ?", $application->getFormId());
                $form = $q->fetchOne();
                if ($form) {
                    $record_columns[] = $form->getFormCode();
                } else {
                    $record_columns[] = "";
                }
                $record_columns[] = $application->getApplicationId();

                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId());
                $user = $q->fetchOne();
                if ($user) {
                    $record_columns[] = ucwords(strtolower($user->getFullname()));
                    $record_columns[] = $user->getMobile();
                } else {
                    $record_columns[] = "";
                }

                $totalfound = false;
                foreach ($invoice->getMfInvoiceDetail() as $fee) {
                    if ($fee->getDescription() == "Total") {
                        $totalfound = true;
                        $record_columns[] = $fee->getAmount();
                        $grand_total_amount += $fee->getAmount();
                    }
                }

                if ($totalfound == false) {
                    $grand_total = 0;
                    foreach ($invoice->getMfInvoiceDetail() as $fee) {
                        $pos = strpos($fee->getDescription(), "Convenience fee");
                        if ($pos === false) {
                            //add amount to grand total
                        } else {
                            continue;
                        }
                        $grand_total += $fee->getAmount();
                    }
                    $record_columns[] = $grand_total;
                    $grand_total_amount += $grand_total;
                }

                $record_columns[] = $payment->getPaymentId();
                $record_columns[] = ucfirst($payment->getPaymentMerchantType());

                if ($invoice->getPaid() == "1") {
                    $record_columns[] = "Not Paid";
                } elseif ($invoice->getPaid() == "15") {
                    $record_columns[] = "Pending Confirmation";
                } elseif ($invoice->getPaid() == "2") {
                    $record_columns[] = "Paid";
                }

                $records[] = $record_columns;
            }
        }

        $record_columns = array();
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "Total";
        $record_columns[] = $grand_total_amount;
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";

        $records[] = $record_columns;

        Outputsheet::ReportGenerator("Billing Report " . date("Y-m-d"), $columns, $records);
        exit;
    }

    public function executeBusinessreport(sfWebRequest $request)
    {
        $payment_status = $request->getParameter("filter_status", 2);

        $this->fromdate = date('Y-m-d', strtotime($request->getPostParameter("fromdate")));
        $this->todate = date('Y-m-d', strtotime($request->getPostParameter("todate")));

        if ($request->getPostParameter("application_form")) {
            $this->q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.form_id = ?", $request->getPostParameter("application_form"))
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->orderBy("a.payment_date DESC");
            $this->filter = $request->getParameter("filter");

            $qt = Doctrine_Query::create()
                ->select('SUM(a.payment_amount) as total')
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.form_id = ?", $request->getPostParameter("application_form"))
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"));
            $this->total = $qt->fetchOne();
        } elseif ($request->getPostParameter("application_service")) {
            $sub_menus_array = array();

            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.menu_id = ?", $request->getPostParameter("application_service"));
            $stages = $q->execute();

            foreach ($stages as $stage) {
                $sub_menus_array[] = $stage->getId();
            }

            $sub_menus_query = implode(" OR b.approved = ", $sub_menus_array);

            $this->q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->leftJoin("a.FormEntry b")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->andWhere("a.record_id = b.entry_id")
                ->andWhere("b.approved = " . $sub_menus_query)
                ->orderBy("a.payment_date DESC");

            $qt = Doctrine_Query::create()
                ->select('SUM(a.payment_amount) as total')
                ->from("ApFormPayments a")
                ->leftJoin("a.FormEntry b")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->andWhere("a.record_id = b.entry_id")
                ->andWhere("b.approved = " . $sub_menus_query);
            $this->total = $qt->fetchOne();
        } else {
            $this->q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"))
                ->orderBy("a.payment_date DESC");

            $qt = Doctrine_Query::create()
                ->select('SUM(a.payment_amount) as total')
                ->from("ApFormPayments a")
                ->where("a.status = ?", $payment_status)
                ->andWhere("a.payment_date BETWEEN ? AND ?", array($this->fromdate . " 00:00:00", $this->todate . " 23:59:59"));
            $this->total = $qt->fetchOne();
        }

        $columns = "";
        $columns[] = "Date Of Issue";
        $columns[] = "Invoice No";
        $columns[] = "Service Code";
        $columns[] = "Application";
        $columns[] = "User";
        $columns[] = "Phone Number";
        $columns[] = "Fee";
        $columns[] = "Reference Number";
        $columns[] = "Payment Mode";
        $columns[] = "Payment Status";

        $records = "";

        $grand_total_amount = 0;

        $payments = $this->q->execute();

        foreach ($payments as $payment) {
            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->where("a.form_id = ? and a.entry_id = ?", array($payment->getFormId(), $payment->getRecordId()));
            $application = $q->fetchOne();
            if ($application) {

                $merchant_reference = explode("/", $payment->getPaymentId());

                $q = Doctrine_Query::create()
                    ->from("MfInvoice a")
                    ->where("a.id = ?", $merchant_reference[2]);
                $invoice = $q->fetchOne();

                if ($invoice == null) {
                    continue;
                }

                $record_columns = "";

                $record_columns[] = $payment->getPaymentDate();
                $record_columns[] = $invoice->getInvoiceNumber();

                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_id = ?", $application->getFormId());
                $form = $q->fetchOne();
                if ($form) {
                    $record_columns[] = $form->getFormCode();
                } else {
                    $record_columns[] = "";
                }
                $record_columns[] = $application->getApplicationId();

                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId());
                $user = $q->fetchOne();
                if ($user) {
                    $record_columns[] = ucwords(strtolower($user->getFullname()));
                    $record_columns[] = $user->getMobile();
                } else {
                    $record_columns[] = "";
                }

                $totalfound = false;
                foreach ($invoice->getMfInvoiceDetail() as $fee) {
                    if ($fee->getDescription() == "Total") {
                        $totalfound = true;
                        $record_columns[] = $fee->getAmount();
                        $grand_total_amount += $fee->getAmount();
                    }
                }

                if ($totalfound == false) {
                    $grand_total = 0;
                    foreach ($invoice->getMfInvoiceDetail() as $fee) {
                        $pos = strpos($fee->getDescription(), "Convenience fee");
                        if ($pos === false) {
                            //add amount to grand total
                        } else {
                            continue;
                        }
                        $grand_total += $fee->getAmount();
                    }
                    $record_columns[] = $grand_total;
                    $grand_total_amount += $grand_total;
                }

                $record_columns[] = $payment->getPaymentId();
                $record_columns[] = ucfirst($payment->getPaymentMerchantType());

                if ($invoice->getPaid() == "1") {
                    $record_columns[] = "Not Paid";
                } elseif ($invoice->getPaid() == "15") {
                    $record_columns[] = "Pending Confirmation";
                } elseif ($invoice->getPaid() == "2") {
                    $record_columns[] = "Paid";
                }

                $records[] = $record_columns;
            }
        }

        $record_columns = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "Total";
        $record_columns[] = $grand_total_amount;
        $record_columns[] = "";
        $record_columns[] = "";
        $record_columns[] = "";

        $records[] = $record_columns;

        Outputsheet::ReportGenerator("Billing Report " . date("Y-m-d"), $columns, $records);
        exit;
    }

    public function executeConvenience(sfWebRequest $request)
    {
        if ($request->getPostParameter('search')) {
            $this->q = Doctrine_Query::create()
                ->from('MfInvoice a')
                ->leftJoin('a.FormEntry b')
                ->where('a.invoice_number LIKE ? AND b.approved <> 0', "%" . $request->getPostParameter('search') . "%")
                ->orWhere('b.application_id LIKE ?', "%" . $request->getPostParameter('search') . "%")
                ->orWhere('concat(b.form_id , "/" , b.entry_id, "/1") LIKE ?', "%" . $request->getPostParameter('search') . "%")
                ->orderBy('a.id DESC');
            $this->search = $request->getPostParameter('search');
            $this->filter = 0;

            $qt = Doctrine_Query::create()
                ->select('SUM(b.amount) as total')
                ->from('MfInvoice a')
                ->leftJoin('a.FormEntry c')
                ->leftJoin('a.mfInvoiceDetail b')
                ->where('a.invoice_number LIKE ? OR c.application_id LIKE ? OR concat(c.form_id , "/" , c.entry_id, "/1") LIKE ?', array("%" . $request->getPostParameter('search') . "%", "%" . $request->getPostParameter('search') . "%", "%" . $request->getPostParameter('search') . "%"))
                ->andWhere('b.description LIKE ?', array("%Convenience fee%"))
                ->andWhere('a.paid = 2')
                ->andWhere('c.approved <> 0')
                ->orderBy('a.id DESC');
            $this->total = $qt->fetchOne();
        } else {
            if ($request->getPostParameter('application_form') && !empty($request->getPostParameter('application_form'))) {
                if ($request->getPostParameter('fromdate') || $request->getParameter('fromdate')) {
                    $this->filter = $request->getPostParameter('application_form');

                    if ($request->getParameter('fromdate')) {
                        $this->fromdate = $request->getParameter('fromdate');
                        $this->todate = $request->getParameter('todate');
                    } else {
                        $this->fromdate = $request->getPostParameter('fromdate');
                        $this->todate = $request->getPostParameter('todate');
                    }

                    if ($this->fromdate == $this->todate) {
                        $this->fromdate = date("Y-m-d", strtotime($this->fromdate));

                        $this->q = Doctrine_Query::create()
                            ->from('MfInvoice a')
                            ->leftJoin('a.FormEntry b')
                            ->where('b.form_id = ?', $this->filter)
                            ->andWhere('a.created_at LIKE ?', "%" . $this->fromdate . "%")
                            ->andWhere('b.approved <> 0')
                            ->orderBy('a.id DESC');

                        $qt = Doctrine_Query::create()
                            ->select('SUM(b.amount) as total')
                            ->from('MfInvoice a')
                            ->leftJoin('a.FormEntry c')
                            ->leftJoin('a.mfInvoiceDetail b')
                            ->where('c.form_id = ?', $this->filter)
                            ->andWhere('a.created_at LIKE ?', "%" . $this->fromdate . "%")
                            ->andWhere('b.description LIKE ?', array("%Convenience fee%"))
                            ->andWhere('a.paid = 2')
                            ->andWhere('c.approved <> 0')
                            ->orderBy('a.id DESC');
                        $this->total = $qt->fetchOne();
                    } else {
                        $this->q = Doctrine_Query::create()
                            ->from('MfInvoice a')
                            ->leftJoin('a.FormEntry b')
                            ->where('b.form_id = ?', $this->filter)
                            ->andWhere('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->todate)) . "+1 day"))))
                            ->andWhere('b.approved <> 0')
                            ->orderBy('a.id DESC');

                        $qt = Doctrine_Query::create()
                            ->select('SUM(b.amount) as total')
                            ->from('MfInvoice a')
                            ->leftJoin('a.FormEntry c')
                            ->leftJoin('a.MfInvoiceDetail b')
                            ->where('c.form_id = ?', $this->filter)
                            ->andWhere('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->todate)) . "+1 day"))))
                            ->andWhere('b.description LIKE ?', array("%Convenience fee%"))
                            ->andWhere('a.paid = 2')
                            ->andWhere('c.approved <> 0')
                            ->orderBy('a.id DESC');
                        $this->total = $qt->fetchOne();
                    }
                } else {
                    $this->filter = $request->getPostParameter('application_form');

                    $this->q = Doctrine_Query::create()
                        ->from('MfInvoice a')
                        ->leftJoin('a.FormEntry b')
                        ->where('b.form_id = ?', $this->filter)
                        ->andWhere('b.approved <> 0')
                        ->orderBy('a.id DESC');

                    $qt = Doctrine_Query::create()
                        ->select('SUM(b.amount) as total')
                        ->from('MfInvoice a')
                        ->leftJoin('a.FormEntry c')
                        ->leftJoin('a.mfInvoiceDetail b')
                        ->where('c.form_id = ?', $request->getPostParameter('application_form'))
                        ->andWhere('b.description LIKE ?', array("%Convenience fee%"))
                        ->andWhere('a.paid = 2')
                        ->andWhere('c.approved <> 0')
                        ->orderBy('a.id DESC');
                    $this->total = $qt->fetchOne();
                }
            } else {
                if ($request->getPostParameter('fromdate') || $request->getParameter('fromdate')) {
                    $this->filter = 0;

                    if ($request->getParameter('fromdate')) {
                        $this->fromdate = $request->getParameter('fromdate');
                        $this->todate = $request->getParameter('todate');
                    } else {
                        $this->fromdate = $request->getPostParameter('fromdate');
                        $this->todate = $request->getPostParameter('todate');
                    }

                    if ($this->fromdate == $this->todate) {
                        $this->fromdate = date("Y-m-d", strtotime($this->fromdate));

                        $this->q = Doctrine_Query::create()
                            ->from('MfInvoice a')
                            ->where('a.created_at LIKE ?', "%" . $this->fromdate . "%")
                            ->orderBy('a.id DESC');

                        $qt = Doctrine_Query::create()
                            ->select('SUM(b.amount) as total')
                            ->from('MfInvoice a')
                            ->leftJoin('a.mfInvoiceDetail b')
                            ->where('a.created_at LIKE ?', "%" . $this->fromdate . "%")
                            ->andWhere('b.description LIKE ?', array("%Convenience fee%"))
                            ->andWhere('a.paid = 2')
                            ->orderBy('a.id DESC');
                        $this->total = $qt->fetchOne();
                    } else {
                        $this->q = Doctrine_Query::create()
                            ->from('MfInvoice a')
                            ->where('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->todate)) . "+1 day"))))
                            ->orderBy('a.id DESC');

                        $qt = Doctrine_Query::create()
                            ->select('SUM(b.amount) as total')
                            ->from('MfInvoice a')
                            ->leftJoin('a.mfInvoiceDetail b')
                            ->where('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($this->todate)) . "+1 day"))))
                            ->andWhere('b.description LIKE ?', array("%Convenience fee%"))
                            ->andWhere('a.paid = 2')
                            ->orderBy('a.id DESC');
                        $this->total = $qt->fetchOne();
                    }
                } else {
                    $this->q = Doctrine_Query::create()
                        ->from('MfInvoice a')
                        ->orderBy('a.id DESC');

                    $qt = Doctrine_Query::create()
                        ->select('SUM(b.amount) as total')
                        ->from('MfInvoice a')
                        ->leftJoin('a.mfInvoiceDetail b')
                        ->where('b.description LIKE ?', array("%Convenience fee%"))
                        ->andWhere('a.paid = 2')
                        ->orderBy('a.id DESC');
                    $this->total = $qt->fetchOne();

                    $this->filter = 0;
                }
            }
        }

        if ($request->getParameter("export")) {
            $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
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

            $columns = "";
            $columns[] = "Date Of Issue";
            $columns[] = "Invoice No";
            $columns[] = "Service Code";
            $columns[] = "Application";
            $columns[] = "User";
            $columns[] = "Fee";
            $columns[] = "Reference Number";
            $columns[] = "Payment Mode";
            $columns[] = "Payment Status";

            $records = "";

            $invoices = $this->q->execute();

            foreach ($invoices as $invoice) {
                $application = $invoice->getFormEntry();

                if (empty($application)) {
                    continue;
                }

                $query = "select * from " . MF_TABLE_PREFIX . "form_payments where form_id = ? and record_id = ? and `status` = 1";
                $params = array($application->getFormId(), $application->getEntryId());
                $sth = mf_do_query($query, $params, $dbh);
                $row = mf_do_fetch_result($sth);

                $record_columns = "";

                $record_columns[] = $invoice->getCreatedAt();
                $record_columns[] = $invoice->getInvoiceNumber();
                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_id = ?", $application->getFormId());
                $form = $q->fetchOne();
                if ($form) {
                    $record_columns[] = $form->getFormCode();
                } else {
                    $record_columns[] = "";
                }
                $record_columns[] = $invoice->getFormEntry()->getApplicationId();
                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId());
                $user = $q->fetchOne();
                if ($user) {
                    $record_columns[] = ucwords(strtolower($user->getFullname()));
                } else {
                    $record_columns[] = "";
                }

                $totalfound = false;
                foreach ($invoice->getMfInvoiceDetail() as $fee) {
                    $mystring = $fee->getDescription();
                    $findme = "Convenience fee";
                    $pos = strpos($mystring, $findme);

                    if ($pos === false) {
                    } else {
                        $totalfound = true;
                        $record_columns[] = sfConfig::get('app_currency') . ". " . $fee->getAmount();
                    }
                }

                if ($totalfound == false) {
                    $grand_total = 0;
                    foreach ($invoice->getMfInvoiceDetail() as $fee) {
                        $mystring = $fee->getDescription();
                        $findme = "Convenience fee";
                        $pos = strpos($mystring, $findme);

                        if ($pos === false) {
                            continue;
                        }
                        $grand_total += $fee->getAmount();
                    }
                    $record_columns[] = sfConfig::get('app_currency') . ". " . $grand_total;
                }

                $record_columns[] = $row['payment_id'];
                $record_columns[] = ucfirst($row['payment_merchant_type']);

                if ($invoice->getPaid() == "1") {
                    $record_columns[] = "Not Paid";
                } elseif ($invoice->getPaid() == "15") {
                    $record_columns[] = "Pending Confirmation";
                } elseif ($invoice->getPaid() == "2") {
                    $record_columns[] = "Paid";
                }

                $records[] = $record_columns;
            }

            if ($this->total) {
                $record_columns = "";
                $record_columns[] = "";
                $record_columns[] = "";
                $record_columns[] = "";
                $record_columns[] = "";
                $record_columns[] = "Total";
                $record_columns[] = $this->total->getTotal();
                $record_columns[] = "";
                $record_columns[] = "";
                $record_columns[] = "";

                $records[] = $record_columns;
            }

            Outputsheet::ReportGenerator("Convenience Fee Report " . date("Y-m-d"), $columns, $records);
            exit;
        }

        $this->pager = new sfDoctrinePager('MfInvoice', 10);
        $this->pager->setQuery($this->q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
    }
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeUnconfirmed(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->leftJoin('a.mfInvoice b WITH b.paid = ?', '1')
            ->orderBy('a.id DESC');
        $this->applications = $q->execute();
    }
    public function executeView(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->invoice = $q->fetchOne();

        if ($request->getPostParameter("remote_reference")) {
            $invoice_manager = new InvoiceManager();

            $result = $invoice_manager->remote_reconcile($this->invoice->getFormEntry()->getFormId() . "/" . $this->invoice->getFormEntry()->getEntryId() . "/" . $request->getPostParameter("remote_reference"));

            //If response is paid, then mark invoice as paid
            if ($result == "paid") {
                $this->invoice->setPaid(2);
                $this->invoice->save();

                error_log("Pesaflow Remote Validated: " . $this->invoice->getFormEntry()->getApplicationId());
            }
        }

        if ($request->getParameter("confirm") == md5($this->invoice->getId())) {
            if ($this->getUser()->mfHasCredential('approvepaymentoverride')) {
                $this->invoice->setPaid("2");
                $this->invoice->setUpdatedAt(date("Y-m-d H:i:s"));
                $this->invoice->save();
                $audit = new Audit();
                $audit->saveAudit("", "Confirmed payment for invoice #" . $this->invoice->getId());

                $this->redirect("/plan/invoices/view/id/" . $this->invoice->getId());
            }
        }

        if ($request->getParameter("cancel") == md5($this->invoice->getId())) {
            if ($this->getUser()->mfHasCredential('approvepaymentsupport')) {
                if ($this->invoice->getPaid() == "1") {
                    $this->invoice->setPaid("3");
                    $this->invoice->setUpdatedAt(date("Y-m-d H:i:s"));
                    $this->invoice->save();
                    $audit = new Audit();
                    $audit->saveAudit("", "Cancelled payment for invoice #" . $this->invoice->getId());
                } else {
                    $this->invoice->setPaid("1");
                    $this->invoice->setUpdatedAt(date("Y-m-d H:i:s"));
                    $this->invoice->save();
                    $audit = new Audit();
                    $audit->saveAudit("", "UnCancel payment for invoice #" . $this->invoice->getId());
                }

                $this->redirect("/plan/invoices/view/id/" . $this->invoice->getId());
            }
        }
    }

    public function executePrint(sfWebRequest $request)
    {
        $invoice_manager = new InvoiceManager();
        $invoice_manager->save_to_pdf($request->getParameter("id"));

        exit;
    }

    public function executeViewreceipt(sfWebRequest $request)
    {

        $this->module = $request->getParameter('module');
        $this->action = $request->getParameter('action');

        $this->notifier = new notifications($this->getMailer());
    }

    private function _paymentsQuery($cols = null, $request = null)
    {
        $filter_status = $this->getUser()->getAttribute('filter_status_', 2);
        $filter = $this->getUser()->getAttribute('filter_');
        $fromdate = $this->getUser()->getAttribute('fromdate_');
        $todate = $this->getUser()->getAttribute('todate_');

        $fromdate = $fromdate ? date('Y-m-d', strtotime($fromdate)) : false; //OTB code refactoring

        $todate = $todate ? date('Y-m-d', strtotime($todate)) : false; //OTB code refactoring
        $q = Doctrine_Query::create()
            ->from("ApFormPayments a")
            ->leftJoin('a.ApForms f')
            ->leftJoin('a.MfInvoice i')
            ->leftJoin('i.FormEntry e');

        $q->where("a.status = ?", $filter_status); //OTB code refactoring
        if ($fromdate && $todate) {
            error_log('---------form-----' . $fromdate . '------to------' . $todate);
            $q->andWhere("a.date_created BETWEEN ? AND ?", array($fromdate . " 00:00:00", $todate . " 23:59:59"));
        }
        if ($filter) {
            $q->andWhere("a.form_id = ?", $filter);
        }
        if (null === $cols)
            return $q;

        $search = $request->getParameter('search')['value'];

        if ("" === $search)
            return $q;
        $sql = [];
        $params = [];

        foreach ($cols as $i => $col) {
            $sql[] = $col . " LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $q->addWhere("(" . implode(" OR ", $sql) . ")", $params);
        return $q;
    }
    private function _invoiceQuery($cols = null, $request = null)
    {
        $filter_status = $this->getUser()->getAttribute("filter_status_inv", 1);
        $filter = $this->getUser()->getAttribute("filter_inv"); //OTB code refactoring
        $fromdate = $this->getUser()->getAttribute("fromdate_inv") ? date('Y-m-d', strtotime($this->getUser()->getAttribute("fromdate_inv"))) : false; //OTB code refactoring
        $todate = $this->getUser()->getAttribute("todate_inv") ? date('Y-m-d', strtotime($this->getUser()->getAttribute("todate_inv"))) : false; //OTB code refactoring
        $q = Doctrine_Query::create()
            ->from("MfInvoice i")
            ->leftJoin('i.FormEntry e')
            ->leftJoin('e.Form f')
            ->leftJoin('e.Stage s')
            ->leftJoin('e.SfGuardUserProfile p');
        if ($filter_status) {
            $q->where("i.paid = ?", $filter_status); //OTB code refactoring
        }
        if ($fromdate && $todate) {
            $q->andWhere("i.created_at BETWEEN ? AND ?", array($fromdate . " 00:00:00", $todate . " 23:59:59"));
        }
        if ($filter) {
            $q->andWhere("e.form_id = ?", $filter);
        }
        if (null === $cols)
            return $q;

        $search = $request->getParameter('search')['value'];

        if ("" === $search)
            return $q;
        $sql = [];
        $params = [];

        foreach ($cols as $i => $col) {
            $sql[] = $col . " LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $q->addWhere("(" . implode(" OR ", $sql) . ")", $params);
        return $q;
    }

    public function executeCheckpaymentstatus(sfWebRequest $request)
    {
        $url = sfConfig::get('app_api_jambo_url') . 'api/v1/bill/status/';

        $token = $_SESSION['jambo_token_backend'];

        $stream = new Stream();


        $billing_reference_number = $request->getParameter('bill_ref');

        $invoice_id = $request->getParameter('id');


        if (!$invoice_id || !$billing_reference_number) {
            return $this->renderText(json_encode(['status' => 404, 'content' => ['msg' => 'invoice not found.', 'success' => false]]));
        }

        error_log("Checkout SISIBO Pay URL --->{$url}");

        $query_response = $stream->sendRequest([
            'url' => $url,
            'method' => 'POST',
            'ssl' => 'none',
            'contentType' => 'json',
            'headers' => array(
                "Authorization" => "JWT " . $token,
            ),
            'data' => [
                'bill_number' => $billing_reference_number
            ]
        ]);



        if ($query_response->status == 200 || $query_response->status == 201) {
            $content = $query_response->content;
            error_log("Payment confirmation is ---->");
            error_log(print_r($content, true));
            error_log("Paid status ---->{$content['status']}");

            if (strtolower($content['status']) == 'paid') {
                $processed = $this->execute_process_payment($$query_response->content);

                if (!$processed) {
                    throw new sfException('Something Went Wrong. Please try again later.', 500);
                }
                $this->getUser()->setFlash('notice', 'Invoice Paid');
                return $this->redirect('/plan/applications/view/id/' . $invoice_id);
            } else {
                $this->getUser()->setFlash('notice', 'Invoice Still Unpaid');

                return $this->redirect('/plan/applications/view/id/' . $invoice_id);
            }

        } else {
            throw new sfException('Something Went Wrong. Please try again later.', 500);
        }
    }

    private function execute_process_payment($response)
    {
        try {
            $response = json_decode($response, true);

            error_log("Callback url coming hot");

            error_log(print_r($response, true));

            if (strtolower($response['status']) == 'success') {
                $ipn = new MalipoGateway();
                $message = '';
                $status_code = '';

                $processing_response = $ipn->jambo_pay_ipn($response);

                switch ($processing_response) {
                    case 'transaction_not_found':
                        $message = 'Bill Reference not found.';
                        $status_code = 404;
                        break;
                    case 'invoice_not_found':
                        $message = 'Bill Reference not found.';
                        $status_code = 404;
                        break;
                    case 'paid':
                        $message = 'Paid';
                        $status_code = 200;
                        break;
                    default:
                        $message = 'Paid';
                        $status_code = 200;
                        break;
                }

                return true;
            } else {
                return false;
            }
        } catch (\Exception $error) {
            return false;
        }
    }
}
