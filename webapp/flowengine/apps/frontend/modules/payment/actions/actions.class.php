<?php

/**
 * Payment actions.
 *
 * Payment api for a remote payment gateway
 *
 * @package    frontend
 * @subpackage payment
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use Exception;

class paymentActions extends sfActions
{
  /**
   * Executes 'Query' Action
   *
   * Query for invoice details using the bill number for KCB GATEWAY
   *
   **/
  public function executeQuerybills(sfWebRequest $request)
  {
    $otb_helper = new OTBHelper();
    $content = $request->getContent();
    $contentR = json_decode($content);
    error_log('ERROR: ' . $otb_helper->json_decode_error_list(json_last_error()));
    $kcb = new KCBGateway();
    $response = $kcb->searchBill($contentR->billId, $contentR->messageId);
    return $this->renderText(json_encode($response));
  }

  /**
   * Receive payment notifications from KCB
   */
  public function executePaymentnotificationsOLD(sfWebRequest $request)
  {
    $response = $request->getContent();
    $response = json_decode($response);

    $invoice = new InvoiceManager();

    $invoice_no = trim($response->billId);
    $message_id = trim($response->messageId);
    $transaction_id = trim($response->transactionId);
    $responseDetails = [
      'messageId' => $message_id,
    ];
    try {

      $invoiceDetails = $invoice->getInvoiceByNumberTransactionMessageId(
        $invoice_no,
        $message_id,
        $transaction_id
      );
      if (!$invoiceDetails) {
        $responseDetails['statusCode'] = '1';
        $responseDetails['statusMessage'] = 'Invoice not found';
        $responseDetails['transactionId'] = $transaction_id;
      } else {
        if ($invoiceDetails->getPaid() == 1) {
          $payments_manager = new KCBGateway();
          $responseDetails = $payments_manager->ipn($response, $invoiceDetails);
        } else {
          $responseDetails['statusCode'] = '1';
          $responseDetails['statusMessage'] = 'Invoice Already Paid. Please re-check the invoice no.';
          $responseDetails['transactionId'] = $transaction_id;
        }
      }
      $responseDetails;
    } catch (\Exeception $e) {
      $responseDetails['statusCode'] = '1';
      $responseDetails['statusMessage'] = $e->getMessage();
      $responseDetails['transactionId'] = $transaction_id;
    }

    return $this->renderText(json_encode($responseDetails));
  }
  /**
   * Receive  notificiations from malipo
   */
  public function executePaymentnotifications(sfWebRequest $request)
  {
    $response = $request->getContent();
    $response = json_decode($response);

    $invoice = new InvoiceManager();
    //
    $invoice_no_only = false;
    // Split the string using the hyphen as the delimiter
    $splitArray = explode('-', $response->billId);
    //error_log(print_r($splitArray)) ;
    /// Check if there are at least 4 parts
    $invoice_no_only = implode('-', array_slice($splitArray, 0, 3));
    // error_log("Debig >>>>>>>>>>> invoice_no_only ".$invoice_no_only ) ;
    //$invoice_no_only = "NKR-INV-710" ;
    // test
    $q_test = Doctrine_Query::create()
      ->from('MfInvoice a')
      ->where('a.transaction_id = ?', $response->billId)
      ->orWhere('a.invoice_number = ?', $invoice_no_only)
      ->limit(1);
    $invoice_r_test = $q_test->fetchOne();
    $invoice_no = trim($invoice_r_test->getInvoiceNumber());
    error_log($invoice_no);

    // get invoice id
    /* $q = Doctrine_Query::create()
             ->from('MfInvoice a')
             ->where('a.transaction_id = ?', $response->billId)
             ->limit(1);
            $invoice_r = $q->fetchOne();

      $invoice_no = trim($invoice_r->getInvoiceNumber()); */
    $message_id = trim($response->messageId);
    $transaction_id = trim($response->billId);
    $responseDetails = [
      'messageId' => $message_id,
    ];
    try {

      $invoiceDetails = $invoice->getInvoiceByNumberTransactionMessageId(
        $invoice_no,
        $message_id,
        $transaction_id
      );
      if (!$invoiceDetails) {
        $responseDetails['statusCode'] = '1';
        $responseDetails['statusMessage'] = 'Invoice not found';
        $responseDetails['transactionId'] = $transaction_id;
        $response['currency'] = 'KES';
      } else {
        if ($invoiceDetails->getPaid() == 1) {
          // error_log("Debug MalipoGateway >> >Check invoice ".$invoiceDetails->getId()) ;

          $payments_manager = new MalipoGateway();
          $responseDetails = $payments_manager->malipo_ipn($response, $invoiceDetails);
        } else {
          $responseDetails['statusCode'] = '1';
          $responseDetails['statusMessage'] = 'Invoice Already Paid. Please re-check the invoice no.';
          $responseDetails['transactionId'] = $transaction_id;
        }
      }
      $responseDetails;
    } catch (Exeception $e) {
      $responseDetails['statusCode'] = '1';
      $responseDetails['statusMessage'] = $e->getMessage();
      $responseDetails['transactionId'] = $transaction_id;
    }

    return $this->renderText(json_encode($responseDetails));
  }

  public function executeProcessInvoice(sfWebRequest $sfWebRequest)
  {
    try {
      $invoice_manager = new InvoiceManager();

      $invoice_id = $sfWebRequest->getParameter('id');

      if (!$invoice_id) {
        return $this->json(['data' => ['success' => false, 'statusCode' => 404, 'message' => 'Invoice Not Found.']], 404);
      }

      $q = Doctrine_Query::create()
        ->from('MfInvoice a')
        ->where('a.id = ?', $invoice_id)
        ->limit(1);
      $invoice = $q->fetchOne();

      if (!$invoice) {
        return $this->json(['data' => ['success' => false, 'statusCode' => 404, 'message' => 'Invoice Not Found.']], 404);
      }
      error_log("Created at as ----->{$invoice->getCreatedAt()}");
      $randomMinutes = rand(30, 45);

      $calculated_date = strtotime("+{$randomMinutes} seconds", strtotime(date('Y-m-d H:i:s')));

      $new_date = date('Y-m-d H:i:s', $calculated_date);

      error_log("Set date is ---->{$new_date}");

      $transactions = [
        ["total_amount" =>  "10000", "doc_ref_number" =>  "TFOVFJVK"],
        ["total_amount" =>  "63375", "doc_ref_number" =>  "UDZU7HZF"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "Q7Y0GBHW"],
        ["total_amount" =>  "20150", "doc_ref_number" =>  "MN3NACN4"],
        ["total_amount" =>  "41066", "doc_ref_number" =>  "YT4Y1ENK"],
        ["total_amount" =>  "15030", "doc_ref_number" =>  "FC6D3CRO"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "6BXBCQKY"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "WZEU06G1"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "M3VZAE3X"],
        ["total_amount" =>  "11400", "doc_ref_number" =>  "2RIQWNO4"],
        ["total_amount" =>  "10497", "doc_ref_number" =>  "QEIWKQ84"],
        ["total_amount" =>  "18300", "doc_ref_number" =>  "CGEK0KXB"],
        ["total_amount" =>  "15298", "doc_ref_number" =>  "9G1YJBKB"],
        ["total_amount" =>  "12975", "doc_ref_number" =>  "GYWVFSW1"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "P0E955I3"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "W7GFKFIH"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "B9GQ8UHN"],
        ["total_amount" =>  "10310", "doc_ref_number" =>  "AS58FR0K"],
        ["total_amount" =>  "25750", "doc_ref_number" =>  "523NJSPB"],
        ["total_amount" =>  "36328", "doc_ref_number" =>  "9QZS04OM"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "WKHIY8D0"],
        ["total_amount" =>  "10315", "doc_ref_number" =>  "Q3E8Q388"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "XMBN1BPL"],
        ["total_amount" =>  "10711", "doc_ref_number" =>  "8CRCZH3J"],
        ["total_amount" =>  "10726", "doc_ref_number" =>  "G96HCK8L"],
        ["total_amount" =>  "154670", "doc_ref_number" =>  "73OQ0IHC"],
        ["total_amount" =>  "145625", "doc_ref_number" =>  "C7KR49KF"],
        ["total_amount" =>  "11268", "doc_ref_number" =>  "QAOT2WUI"],
        ["total_amount" =>  "22940", "doc_ref_number" =>  "KIHAND5B"],
        ["total_amount" =>  "19075", "doc_ref_number" =>  "OUG85VNG"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "V5BPL9K3"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "3UXONTEL"],
        ["total_amount" =>  "10590", "doc_ref_number" =>  "DB2VG374"],
        ["total_amount" =>  "21000", "doc_ref_number" =>  "VS1OMRXY"],
        ["total_amount" =>  "61000", "doc_ref_number" =>  "DXS3TCIF"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "BJ3PCNBA"],
        ["total_amount" =>  "17600", "doc_ref_number" =>  "M7D5WGW3"],
        ["total_amount" =>  "17900", "doc_ref_number" =>  "YT6X4MJH"],
        ["total_amount" =>  "12583", "doc_ref_number" =>  "KRGOBHFM"],
        ["total_amount" =>  "20600", "doc_ref_number" =>  "GQF8LE0T"],
        ["total_amount" =>  "6064", "doc_ref_number" =>  "C1HL809S"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "0WLJN4U2"],
        ["total_amount" =>  "24800", "doc_ref_number" =>  "Y3TXYRNO"],
        ["total_amount" =>  "14975", "doc_ref_number" =>  "MJD748AB"],
        ["total_amount" =>  "21400", "doc_ref_number" =>  "DG6B3R59"],
        ["total_amount" =>  "29960", "doc_ref_number" =>  "S4VI4Y1D"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "28Z013PU"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "1A54HHPJ"],
        ["total_amount" =>  "19170", "doc_ref_number" =>  "NFX1GA4J"],
        ["total_amount" =>  "13075", "doc_ref_number" =>  "9FK616B9"],
        ["total_amount" =>  "11130", "doc_ref_number" =>  "N2RNG5WU"],
        ["total_amount" =>  "32724", "doc_ref_number" =>  "CBHTEY21"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "PBQ7SUUK"],
        ["total_amount" =>  "19750", "doc_ref_number" =>  "N0ZPAATT"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "CI2083ML"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "IYMSR8FG"],
        ["total_amount" =>  "15275", "doc_ref_number" =>  "2I6IISCP"],
        ["total_amount" =>  "11920", "doc_ref_number" =>  "KANPBLLK"],
        ["total_amount" =>  "19320", "doc_ref_number" =>  "PDE48NT7"],
        ["total_amount" =>  "27775", "doc_ref_number" =>  "LC3E8O0R"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "BZKUFJRF"],
        ["total_amount" =>  "18100", "doc_ref_number" =>  "EP5U59PL"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "17BZOPSM"],
        ["total_amount" =>  "2500", "doc_ref_number" =>  "BXRC0751"],
        ["total_amount" =>  "25750", "doc_ref_number" =>  "IEFN1IT1"],
        ["total_amount" =>  "24700", "doc_ref_number" =>  "YM5F4KMI"],
        ["total_amount" =>  "39850", "doc_ref_number" =>  "OS9WTGW7"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "I187OE4T"],
        ["total_amount" =>  "18185", "doc_ref_number" =>  "XZ8LHVUQ"],
        ["total_amount" =>  "20000", "doc_ref_number" =>  "9PSVFWHL"],
        ["total_amount" =>  "69750", "doc_ref_number" =>  "IC5IFNHJ"],
        ["total_amount" =>  "34035", "doc_ref_number" =>  "UCD3KD9X"],
        ["total_amount" =>  "11871", "doc_ref_number" =>  "Q0SHY696"],
        ["total_amount" =>  "14280", "doc_ref_number" =>  "J924RW98"],
        ["total_amount" =>  "21950", "doc_ref_number" =>  "LFUHKW52"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "D6UHTZWJ"],
        ["total_amount" =>  "18500", "doc_ref_number" =>  "0UOVJ0MZ"],
        ["total_amount" =>  "21000", "doc_ref_number" =>  "83OUWPCE"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "PI9Z7LW4"],
        ["total_amount" =>  "64959", "doc_ref_number" =>  "3Z31OKHM"],
        ["total_amount" =>  "103960", "doc_ref_number" =>  "RMNXQB37"],
        ["total_amount" =>  "61150", "doc_ref_number" =>  "N0VM6TSP"],
        ["total_amount" =>  "35375", "doc_ref_number" =>  "37LMJVFI"],
        ["total_amount" =>  "35912", "doc_ref_number" =>  "L45LUZ1B"],
        ["total_amount" =>  "26700", "doc_ref_number" =>  "DMURN6A8"],
        ["total_amount" =>  "40900", "doc_ref_number" =>  "O6UXN9AL"],
        ["total_amount" =>  "90575", "doc_ref_number" =>  "ROAK27WJ"],
        ["total_amount" =>  "62400", "doc_ref_number" =>  "EZ1M7855"],
        ["total_amount" =>  "20624", "doc_ref_number" =>  "DZT0CTY0"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "QDL1XCMK"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "OYGPBQ4S"],
        ["total_amount" =>  "12445", "doc_ref_number" =>  "YAW788TF"],
        ["total_amount" =>  "46350", "doc_ref_number" =>  "XRGRI0V9"],
        ["total_amount" =>  "14750", "doc_ref_number" =>  "5DMMCZ4G"],
        ["total_amount" =>  "11690", "doc_ref_number" =>  "V3PIQP3Y"],
        ["total_amount" =>  "16950", "doc_ref_number" =>  "M4CUEU3R"],
        ["total_amount" =>  "55950", "doc_ref_number" =>  "EMCVQFBY"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "HBE0XLPM"],
        ["total_amount" =>  "23900", "doc_ref_number" =>  "1RHOW8UY"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "5V6KF8J4"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "WHIFU4EI"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "21PTNNH5"],
        ["total_amount" =>  "41000", "doc_ref_number" =>  "RHADIGXI"],
        ["total_amount" =>  "0", "doc_ref_number" =>  "5V6KF8J4"],
        ["total_amount" =>  "11700", "doc_ref_number" =>  "8KE173VI"],
        ["total_amount" =>  "7559", "doc_ref_number" =>  "B5MLLX06"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "DGK7BQ73"],
        ["total_amount" =>  "103750", "doc_ref_number" =>  "4ETNDGX6"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "BD1WBL6S"],
        ["total_amount" =>  "13395", "doc_ref_number" =>  "R5ZZMK6Z"],
        ["total_amount" =>  "13400", "doc_ref_number" =>  "YBQEL7M6"],
        ["total_amount" =>  "4", "doc_ref_number" =>  "A17H06UE"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "UK4KL6H5"],
        ["total_amount" =>  "22600", "doc_ref_number" =>  "6IP8H5Y7"],
        ["total_amount" =>  "16716", "doc_ref_number" =>  "DP217LQT"],
        ["total_amount" =>  "10890", "doc_ref_number" =>  "C00YLSG3"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "I99F8PLP"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "9M760IMJ"],
        ["total_amount" =>  "10590", "doc_ref_number" =>  "4AKV231J"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "WDNHP1VC"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "42GF6Y3N"],
        ["total_amount" =>  "16895", "doc_ref_number" =>  "DAKVWGVU"],
        ["total_amount" =>  "28500", "doc_ref_number" =>  "V0B0UIXI"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "55EW1VO9"],
        ["total_amount" =>  "308478", "doc_ref_number" =>  "VN5FLCA5"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "YEBVI2SP"],
        ["total_amount" =>  "23225", "doc_ref_number" =>  "6SX1391W"],
        ["total_amount" =>  "10265", "doc_ref_number" =>  "7ANNX8A5"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "3X9GTN0V"],
        ["total_amount" =>  "11735", "doc_ref_number" =>  "9KFRFB2V"],
        ["total_amount" =>  "10890", "doc_ref_number" =>  "NI74UMM4"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "S70MIE7Y"],
        ["total_amount" =>  "13725", "doc_ref_number" =>  "7VCP7W43"],
        ["total_amount" =>  "11700", "doc_ref_number" =>  "6HXKPHNP"],
        ["total_amount" =>  "10050", "doc_ref_number" =>  "RKI4KL4C"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "F7DR8XNV"],
        ["total_amount" =>  "13959", "doc_ref_number" =>  "JQSKBD02"],
        ["total_amount" =>  "40750", "doc_ref_number" =>  "JWH0TN6M"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "MXJ0FM1S"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "J7P25J31"],
        ["total_amount" =>  "12300", "doc_ref_number" =>  "Y51OBUEB"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "BDO2VIPT"],
        ["total_amount" =>  "79500", "doc_ref_number" =>  "E3SZ8ZKI"],
        ["total_amount" =>  "11799", "doc_ref_number" =>  "DP1O5V24"],
        ["total_amount" =>  "9065", "doc_ref_number" =>  "AF98PFZ1"],
        ["total_amount" =>  "56500", "doc_ref_number" =>  "1W5GLIQX"],
        ["total_amount" =>  "18200", "doc_ref_number" =>  "QWWRXYBX"],
        ["total_amount" =>  "47500", "doc_ref_number" =>  "NADVS4WR"],
        ["total_amount" =>  "11250", "doc_ref_number" =>  "Y0FN8B3V"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "GTMO8NET"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "04QLI09H"],
        ["total_amount" =>  "44525", "doc_ref_number" =>  "DXNHPPT7"],
        ["total_amount" =>  "101300", "doc_ref_number" =>  "BI6TRFWO"],
        ["total_amount" =>  "13200", "doc_ref_number" =>  "ZB6VA30F"],
        ["total_amount" =>  "12750", "doc_ref_number" =>  "WO542DU7"],
        ["total_amount" =>  "18150", "doc_ref_number" =>  "GRP1CX80"],
        ["total_amount" =>  "54852", "doc_ref_number" =>  "J5PW3UVZ"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "7LH9LK0J"],
        ["total_amount" =>  "46035", "doc_ref_number" =>  "2OM07YQC"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "P6S2BU9C"],
        ["total_amount" =>  "13215", "doc_ref_number" =>  "QCFFE7WJ"],
        ["total_amount" =>  "12300", "doc_ref_number" =>  "UH2NISFK"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "NPHCFPKF"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "4XQPG0ZU"],
        ["total_amount" =>  "12495", "doc_ref_number" =>  "TQ9FHCK3"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "QIIZXTNZ"],
        ["total_amount" =>  "12640", "doc_ref_number" =>  "1CCYBYKV"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "5UNWL7AR"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "OYUAZMNZ"],
        ["total_amount" =>  "17380", "doc_ref_number" =>  "1YYXBACA"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "I6YFEZ52"],
        ["total_amount" =>  "15250", "doc_ref_number" =>  "3SVG04AR"],
        ["total_amount" =>  "63343", "doc_ref_number" =>  "IB2DC63T"],
        ["total_amount" =>  "40475", "doc_ref_number" =>  "LYEFFTKM"],
        ["total_amount" =>  "10620", "doc_ref_number" =>  "O579CIPE"],
        ["total_amount" =>  "27150", "doc_ref_number" =>  "4X2VJN6N"],
        ["total_amount" =>  "24500", "doc_ref_number" =>  "MXQ6JPPP"],
        ["total_amount" =>  "11400", "doc_ref_number" =>  "CLZ5LI8P"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "OUEMBORE"],
        ["total_amount" =>  "23825", "doc_ref_number" =>  "4GVEDCBW"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "SBH2UL66"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "1ZZZOG7R"],
        ["total_amount" =>  "13200", "doc_ref_number" =>  "KQWVNAYH"],
        ["total_amount" =>  "175500", "doc_ref_number" =>  "NEZKUF6L"],
        ["total_amount" =>  "15015", "doc_ref_number" =>  "8B91E3NO"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "T1BBNAWR"],
        ["total_amount" =>  "7000", "doc_ref_number" =>  "5E1PRMNZ"],
        ["total_amount" =>  "26500", "doc_ref_number" =>  "4JUNNLPP"],
        ["total_amount" =>  "20350", "doc_ref_number" =>  "RGMRH1GJ"],
        ["total_amount" =>  "11610", "doc_ref_number" =>  "CPO0KL0E"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "VI9YBX09"],
        ["total_amount" =>  "13485", "doc_ref_number" =>  "3TYRR4BY"],
        ["total_amount" =>  "11900", "doc_ref_number" =>  "MHR3QHJ8"],
        ["total_amount" =>  "11595", "doc_ref_number" =>  "WQFKPVIC"],
        ["total_amount" =>  "11550", "doc_ref_number" =>  "NCKXBECB"],
        ["total_amount" =>  "20000", "doc_ref_number" =>  "N4X9LODO"],
        ["total_amount" =>  "12975", "doc_ref_number" =>  "XIQQE38B"],
        ["total_amount" =>  "19030", "doc_ref_number" =>  "WIISGBAM"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "9ID0U35A"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "9UYREXKZ"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "JBH66JVR"],
        ["total_amount" =>  "24650", "doc_ref_number" =>  "SWC12GSA"],
        ["total_amount" =>  "10620", "doc_ref_number" =>  "XEQRHGYS"],
        ["total_amount" =>  "7000", "doc_ref_number" =>  "OPHANV9Y"],
        ["total_amount" =>  "31534", "doc_ref_number" =>  "YL2XAGAF"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "KAMJ9VVL"],
        ["total_amount" =>  "51600", "doc_ref_number" =>  "OA32KCU1"],
        ["total_amount" =>  "10830", "doc_ref_number" =>  "901R6O2F"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "4J4A9NPX"],
        ["total_amount" =>  "15575", "doc_ref_number" =>  "N1ILBEFZ"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "FUJPQCI7"],
        ["total_amount" =>  "11700", "doc_ref_number" =>  "W5UE2M3D"],
        ["total_amount" =>  "8500", "doc_ref_number" =>  "CWFZR8HK"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "AECRRR5W"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "E8I42IQM"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "LMSABYYA"],
        ["total_amount" =>  "11700", "doc_ref_number" =>  "EA4AVF1F"],
        ["total_amount" =>  "18925", "doc_ref_number" =>  "ISCZ0ZTN"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "SAUPB3Q8"],
        ["total_amount" =>  "11310", "doc_ref_number" =>  "AVF1GO9H"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "YDY58GZL"],
        ["total_amount" =>  "32300", "doc_ref_number" =>  "07UCNOYZ"],
        ["total_amount" =>  "71275", "doc_ref_number" =>  "555GP25L"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "S7FGXQEX"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "G7F764VM"],
        ["total_amount" =>  "12790", "doc_ref_number" =>  "LFFRU7T5"],
        ["total_amount" =>  "25075", "doc_ref_number" =>  "J8OFJE0H"],
        ["total_amount" =>  "11195", "doc_ref_number" =>  "EDB97DBA"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "F5K17IE5"],
        ["total_amount" =>  "36520", "doc_ref_number" =>  "JHUALCR7"],
        ["total_amount" =>  "9705", "doc_ref_number" =>  "II2GS7LR"],
        ["total_amount" =>  "57420", "doc_ref_number" =>  "0KO6C5RN"],
        ["total_amount" =>  "98000", "doc_ref_number" =>  "H8TM39SI"],
        ["total_amount" =>  "49650", "doc_ref_number" =>  "GCCR268Z"],
        ["total_amount" =>  "61300", "doc_ref_number" =>  "8VE8VB9M"],
        ["total_amount" =>  "2", "doc_ref_number" =>  "DZEQVR8U"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "PO3WJRR7"],
        ["total_amount" =>  "12800", "doc_ref_number" =>  "8ND19076"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "NKTVUGWI"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "PV5WQROK"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "X93Q4LRF"],
        ["total_amount" =>  "13200", "doc_ref_number" =>  "5QVYHF1L"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "UT63UZAO"],
        ["total_amount" =>  "10025", "doc_ref_number" =>  "61YP711U"],
        ["total_amount" =>  "10965", "doc_ref_number" =>  "E2HC18RU"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "D0QFX521"],
        ["total_amount" =>  "16488", "doc_ref_number" =>  "U39YGPRZ"],
        ["total_amount" =>  "47300", "doc_ref_number" =>  "CP0IGTHN"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "H6802IA2"],
        ["total_amount" =>  "8500", "doc_ref_number" =>  "08BIBQZZ"],
        ["total_amount" =>  "11670", "doc_ref_number" =>  "6TRLLS4F"],
        ["total_amount" =>  "38375", "doc_ref_number" =>  "5W601BU1"],
        ["total_amount" =>  "17490", "doc_ref_number" =>  "71C5L1S3"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "JEH5K7TV"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "FTOFHX1D"],
        ["total_amount" =>  "12315", "doc_ref_number" =>  "BWWOSF3V"],
        ["total_amount" =>  "12320", "doc_ref_number" =>  "FV4J2M2F"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "LMU0GZHN"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "Q142SW73"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "J8G7NNWV"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "NYB5FR4R"],
        ["total_amount" =>  "38550", "doc_ref_number" =>  "59TS6KOL"],
        ["total_amount" =>  "334725", "doc_ref_number" =>  "8W62VL3P"],
        ["total_amount" =>  "63900", "doc_ref_number" =>  "MFADASDL"],
        ["total_amount" =>  "11700", "doc_ref_number" =>  "J16GDMAP"],
        ["total_amount" =>  "13590", "doc_ref_number" =>  "B8SCCA17"],
        ["total_amount" =>  "13215", "doc_ref_number" =>  "YVB94R2Y"],
        ["total_amount" =>  "14600", "doc_ref_number" =>  "7NJURP5H"],
        ["total_amount" =>  "12375", "doc_ref_number" =>  "BDNHMI0N"],
        ["total_amount" =>  "13500", "doc_ref_number" =>  "BNRCKLPT"],
        ["total_amount" =>  "13845", "doc_ref_number" =>  "YVD25PKA"],
        ["total_amount" =>  "67800", "doc_ref_number" =>  "8XM60DLD"],
        ["total_amount" =>  "32075", "doc_ref_number" =>  "A7O4DS3J"],
        ["total_amount" =>  "11730", "doc_ref_number" =>  "QSM1K1MS"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "2DQ05GY6"],
        ["total_amount" =>  "320000", "doc_ref_number" =>  "MXK1VS8E"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "5XSKRJ8J"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "YB333DM9"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "IC3M5WFT"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "2Y1A3WOF"],
        ["total_amount" =>  "12395", "doc_ref_number" =>  "6YMUDCLI"],
        ["total_amount" =>  "12600", "doc_ref_number" =>  "MTNLNSGA"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "4J4R57Z9"],
        ["total_amount" =>  "20500", "doc_ref_number" =>  "5PXZJSWI"],
        ["total_amount" =>  "54100", "doc_ref_number" =>  "MGN5SZE5"],
        ["total_amount" =>  "81900", "doc_ref_number" =>  "C5TRFFQ3"],
        ["total_amount" =>  "12240", "doc_ref_number" =>  "RFUP984T"],
        ["total_amount" =>  "148325", "doc_ref_number" =>  "GSUUJJP2"],
        ["total_amount" =>  "3500", "doc_ref_number" =>  "TG85861U"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "GIS5TDIG"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "CBR06L4W"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "LR9MWCN8"],
        ["total_amount" =>  "12875", "doc_ref_number" =>  "85N4EXLP"],
        ["total_amount" =>  "11800", "doc_ref_number" =>  "E3KO50IA"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "3RNTH568"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "QSVN7TE5"],
        ["total_amount" =>  "11580", "doc_ref_number" =>  "VAE5J975"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "XRXM7LD7"],
        ["total_amount" =>  "18000", "doc_ref_number" =>  "7OE1YYSY"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "L12VJHBS"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "Y2W17C7S"],
        ["total_amount" =>  "81750", "doc_ref_number" =>  "SGOD3ZH8"],
        ["total_amount" =>  "8921", "doc_ref_number" =>  "3414OJO2"],
        ["total_amount" =>  "30000", "doc_ref_number" =>  "OGEGN9J9"],
        ["total_amount" =>  "12365", "doc_ref_number" =>  "JBP6FY91"],
        ["total_amount" =>  "12600", "doc_ref_number" =>  "ZBQHQ5F6"],
        ["total_amount" =>  "38175", "doc_ref_number" =>  "TEDZDHH3"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "QLG44PEC"],
        ["total_amount" =>  "13500", "doc_ref_number" =>  "960CDMDD"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "2580CKCK"],
        ["total_amount" =>  "11690", "doc_ref_number" =>  "J8ZYT8FZ"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "27U76GBS"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "IOE14WES"],
        ["total_amount" =>  "43950", "doc_ref_number" =>  "0386GQAG"],
        ["total_amount" =>  "39300", "doc_ref_number" =>  "7C3PHVDQ"],
        ["total_amount" =>  "46700", "doc_ref_number" =>  "HUB5ZXHQ"],
        ["total_amount" =>  "55250", "doc_ref_number" =>  "M6OWZCEU"],
        ["total_amount" =>  "20355", "doc_ref_number" =>  "WI01UM8U"],
        ["total_amount" =>  "9500", "doc_ref_number" =>  "XE2S5JFV"],
        ["total_amount" =>  "12450", "doc_ref_number" =>  "S0S6N3CO"],
        ["total_amount" =>  "18300", "doc_ref_number" =>  "NDJTS046"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "XD5OJLSV"],
        ["total_amount" =>  "11940", "doc_ref_number" =>  "X6EKTE98"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "MJRNKSH0"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "T5JWKSFM"],
        ["total_amount" =>  "12345", "doc_ref_number" =>  "SAXT2D76"],
        ["total_amount" =>  "77750", "doc_ref_number" =>  "DNXU09AW"],
        ["total_amount" =>  "65400", "doc_ref_number" =>  "5PCQYMTK"],
        ["total_amount" =>  "12950", "doc_ref_number" =>  "DVGEOXKT"],
        ["total_amount" =>  "11730", "doc_ref_number" =>  "QO9J4IA3"],
        ["total_amount" =>  "9720", "doc_ref_number" =>  "D61LL1N8"],
        ["total_amount" =>  "12665", "doc_ref_number" =>  "2CC82DKR"],
        ["total_amount" =>  "16250", "doc_ref_number" =>  "00UT0TV4"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "M200NH40"],
        ["total_amount" =>  "10821", "doc_ref_number" =>  "8MYTOGQ9"],
        ["total_amount" =>  "15050", "doc_ref_number" =>  "LIUZTFK6"],
        ["total_amount" =>  "10780", "doc_ref_number" =>  "L6Y3HAUF"],
        ["total_amount" =>  "19650", "doc_ref_number" =>  "5351E7E8"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "A09S8DFX"],
        ["total_amount" =>  "11820", "doc_ref_number" =>  "H52OW5EN"],
        ["total_amount" =>  "9960", "doc_ref_number" =>  "CGN7LAQK"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "DDWCSL3K"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "DYEPJH2J"],
        ["total_amount" =>  "78100", "doc_ref_number" =>  "3Y3ACHTR"],
        ["total_amount" =>  "51700", "doc_ref_number" =>  "8KTKREO9"],
        ["total_amount" =>  "13280", "doc_ref_number" =>  "H33I7FQ9"],
        ["total_amount" =>  "250000", "doc_ref_number" =>  "DS1FC9OS"],
        ["total_amount" =>  "24600", "doc_ref_number" =>  "DS1FC9OS"],
        ["total_amount" =>  "11105", "doc_ref_number" =>  "S3G0U7LZ"],
        ["total_amount" =>  "54900", "doc_ref_number" =>  "RQXRB2WR"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "ABIUW5W2"],
        ["total_amount" =>  "52200", "doc_ref_number" =>  "TH4EJG1I"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "FHQXSCBG"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "A0FEWWHL"],
        ["total_amount" =>  "11505", "doc_ref_number" =>  "2F72XH8Z"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "RU2T2ECY"],
        ["total_amount" =>  "28400", "doc_ref_number" =>  "2CG0R8DV"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "LSS8TA9H"],
        ["total_amount" =>  "19250", "doc_ref_number" =>  "3OYA9D7P"],
        ["total_amount" =>  "11250", "doc_ref_number" =>  "09UMTY82"],
        ["total_amount" =>  "10110", "doc_ref_number" =>  "58RS6J32"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "7LAYZNY7"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "YNLMX1YQ"],
        ["total_amount" =>  "22595", "doc_ref_number" =>  "IHPMT7AY"],
        ["total_amount" =>  "55081", "doc_ref_number" =>  "U9OF5GRK"],
        ["total_amount" =>  "10860", "doc_ref_number" =>  "FD377R0Y"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "OLNEF9AI"],
        ["total_amount" =>  "18625", "doc_ref_number" =>  "X3JYQWUR"],
        ["total_amount" =>  "13860", "doc_ref_number" =>  "P8BP5WET"],
        ["total_amount" =>  "13300", "doc_ref_number" =>  "7LMFUI3Y"],
        ["total_amount" =>  "27125", "doc_ref_number" =>  "ZC99DZ1X"],
        ["total_amount" =>  "35500", "doc_ref_number" =>  "MDVBJGJI"],
        ["total_amount" =>  "11500", "doc_ref_number" =>  "DB508R05"],
        ["total_amount" =>  "36246", "doc_ref_number" =>  "OIMZC3QV"],
        ["total_amount" =>  "35475", "doc_ref_number" =>  "R50BN4NR"],
        ["total_amount" =>  "10350", "doc_ref_number" =>  "8WYMXGND"],
        ["total_amount" =>  "15000", "doc_ref_number" =>  "IAWQ7Z4T"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "HB577BKU"],
        ["total_amount" =>  "13872", "doc_ref_number" =>  "N96NCV37"],
        ["total_amount" =>  "12100", "doc_ref_number" =>  "G6XUL2C6"],
        ["total_amount" =>  "10932", "doc_ref_number" =>  "WJCM02WE"],
        ["total_amount" =>  "11780", "doc_ref_number" =>  "GM2EEDFO"],
        ["total_amount" =>  "135785", "doc_ref_number" =>  "F8ZEDLI2"],
        ["total_amount" =>  "28100", "doc_ref_number" =>  "I03XDCIR"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "FGWD68OF"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "WKF9XZ0J"],
        ["total_amount" =>  "11640", "doc_ref_number" =>  "SJTUCCKE"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "W64ELVOP"],
        ["total_amount" =>  "22900", "doc_ref_number" =>  "UTSAAPGR"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "UOOLCB9Q"],
        ["total_amount" =>  "40550", "doc_ref_number" =>  "W1H255EJ"],
        ["total_amount" =>  "16500", "doc_ref_number" =>  "AHLJ81KJ"],
        ["total_amount" =>  "12000", "doc_ref_number" =>  "1CUB0S2D"],
        ["total_amount" =>  "50", "doc_ref_number" =>  "Y556SDZM"],
        ["total_amount" =>  "10196", "doc_ref_number" =>  "V05GMT6Z"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "PTWEYCC1"],
        ["total_amount" =>  "11025", "doc_ref_number" =>  "AQZ6TOJC"],
        ["total_amount" =>  "11055", "doc_ref_number" =>  "BG5J21XK"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "01VSE0NG"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "RGLHDDG3"],
        ["total_amount" =>  "10590", "doc_ref_number" =>  "Y314F9V7"],
        ["total_amount" =>  "19050", "doc_ref_number" =>  "FWGZQBQ3"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "JUIJICHU"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "0YK99ERT"],
        ["total_amount" =>  "12000", "doc_ref_number" =>  "7JAK3XH6"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "H3QNYHF6"],
        ["total_amount" =>  "83600", "doc_ref_number" =>  "GL4D4W8L"],
        ["total_amount" =>  "101000", "doc_ref_number" =>  "U04DNEUX"],
        ["total_amount" =>  "28500", "doc_ref_number" =>  "BPEUTQ2A"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "NNGM3J3Y"],
        ["total_amount" =>  "56000", "doc_ref_number" =>  "M9R4I4YR"],
        ["total_amount" =>  "8940", "doc_ref_number" =>  "HYC09ADB"],
        ["total_amount" =>  "32450", "doc_ref_number" =>  "O15IA6FE"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "WT9P7RUP"],
        ["total_amount" =>  "31000", "doc_ref_number" =>  "CAZT31RT"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "FM2AY92C"],
        ["total_amount" =>  "12830", "doc_ref_number" =>  "GKFO6MCR"],
        ["total_amount" =>  "13500", "doc_ref_number" =>  "I0E27R8W"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "SW66EVE0"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "7ZA7P1UF"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "SYWUIC5Z"],
        ["total_amount" =>  "20200", "doc_ref_number" =>  "KMCZOU3V"],
        ["total_amount" =>  "7350", "doc_ref_number" =>  "P0LD8T59"],
        ["total_amount" =>  "3000", "doc_ref_number" =>  "DL10YCNI"],
        ["total_amount" =>  "50525", "doc_ref_number" =>  "JE9HFDK8"],
        ["total_amount" =>  "25000", "doc_ref_number" =>  "7FE4INGW"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "CJT3M1B6"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "0JV795RM"],
        ["total_amount" =>  "141000", "doc_ref_number" =>  "9RTMM4AK"],
        ["total_amount" =>  "6000", "doc_ref_number" =>  "9E3SUUA1"],
        ["total_amount" =>  "54415", "doc_ref_number" =>  "T1X6LSDH"],
        ["total_amount" =>  "20560", "doc_ref_number" =>  "BHF9VOOA"],
        ["total_amount" =>  "33685", "doc_ref_number" =>  "HMUDRXQE"],
        ["total_amount" =>  "10185", "doc_ref_number" =>  "BTPDKE2X"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "VFI2QOHG"],
        ["total_amount" =>  "20013", "doc_ref_number" =>  "23PSU3YI"],
        ["total_amount" =>  "7575", "doc_ref_number" =>  "00F4AB81"],
        ["total_amount" =>  "28225", "doc_ref_number" =>  "ODCVQMNW"],
        ["total_amount" =>  "22500", "doc_ref_number" =>  "B2UFMBX1"],
        ["total_amount" =>  "11235", "doc_ref_number" =>  "IG9RA01L"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "R2H2YN2E"],
        ["total_amount" =>  "62550", "doc_ref_number" =>  "5A2K0X5J"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "0OQNQG7J"],
        ["total_amount" =>  "40350", "doc_ref_number" =>  "FR7XRT0A"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "SYAD5D9D"],
        ["total_amount" =>  "12150", "doc_ref_number" =>  "3R2ZQY25"],
        ["total_amount" =>  "8370", "doc_ref_number" =>  "19E29EC7"],
        ["total_amount" =>  "28500", "doc_ref_number" =>  "KOH9S5ZZ"],
        ["total_amount" =>  "28500", "doc_ref_number" =>  "KOH9S5ZZ"],
        ["total_amount" =>  "74750", "doc_ref_number" =>  "GACQKA6I"],
        ["total_amount" =>  "15000", "doc_ref_number" =>  "3BNNQVOZ"],
        ["total_amount" =>  "104580", "doc_ref_number" =>  "1UV199S9"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "W2Z1F0T2"],
        ["total_amount" =>  "12675", "doc_ref_number" =>  "7DQPG34I"],
        ["total_amount" =>  "67142", "doc_ref_number" =>  "SDE5JHN5"],
        ["total_amount" =>  "110716", "doc_ref_number" =>  "C7GV95ZA"],
        ["total_amount" =>  "46450", "doc_ref_number" =>  "S4T7WYMO"],
        ["total_amount" =>  "15000", "doc_ref_number" =>  "CKDWYG1E"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "7LJYMSTS"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "TV036J5W"],
        ["total_amount" =>  "22325", "doc_ref_number" =>  "O49064ZJ"],
        ["total_amount" =>  "68625", "doc_ref_number" =>  "SYYCKLLM"],
        ["total_amount" =>  "8970", "doc_ref_number" =>  "MEG8BPFR"],
        ["total_amount" =>  "22500", "doc_ref_number" =>  "A22WVDLX"],
        ["total_amount" =>  "31000", "doc_ref_number" =>  "H73TYJLT"],
        ["total_amount" =>  "22500", "doc_ref_number" =>  "LFKUC4PG"],
        ["total_amount" =>  "31000", "doc_ref_number" =>  "5VEVHXOG"],
        ["total_amount" =>  "8025", "doc_ref_number" =>  "S9EBFC72"],
        ["total_amount" =>  "15320", "doc_ref_number" =>  "UKO5WG04"],
        ["total_amount" =>  "10500", "doc_ref_number" =>  "8XT5A1DH"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "F720YD3D"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "8PC4Q19O"],
        ["total_amount" =>  "12000", "doc_ref_number" =>  "6C7CGG0Z"],
        ["total_amount" =>  "51200", "doc_ref_number" =>  "CJN1L6GT"],
        ["total_amount" =>  "26000", "doc_ref_number" =>  "RRQBROM2"],
        ["total_amount" =>  "31500", "doc_ref_number" =>  "GK9LWD7S"],
        ["total_amount" =>  "50000", "doc_ref_number" =>  "9MJNNR0W"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "IX8OBSZX"],
        ["total_amount" =>  "37350", "doc_ref_number" =>  "O2IIVNCP"],
        ["total_amount" =>  "100000", "doc_ref_number" =>  "UP5E7WRF"],
        ["total_amount" =>  "23406", "doc_ref_number" =>  "I7U42028"],
        ["total_amount" =>  "2000", "doc_ref_number" =>  "UP5E7WRF"],
        ["total_amount" =>  "22450", "doc_ref_number" =>  "KLQL3AHP"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "F41QLF89"],
        ["total_amount" =>  "24300", "doc_ref_number" =>  "XYA8SW5P"],
        ["total_amount" =>  "100025", "doc_ref_number" =>  "B8XTZ6RG"],
        ["total_amount" =>  "10250", "doc_ref_number" =>  "9Y2L6XVD"],
        ["total_amount" =>  "23360", "doc_ref_number" =>  "3CSXK5S1"],
        ["total_amount" =>  "8550", "doc_ref_number" =>  "VCPMN9QH"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "ENZJDOGE"],
        ["total_amount" =>  "40750", "doc_ref_number" =>  "ZSXI8FQ7"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "NGF2VJQP"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "PGY9EF8T"],
        ["total_amount" =>  "5", "doc_ref_number" =>  "CMUP6D1H"],
        ["total_amount" =>  "5", "doc_ref_number" =>  "QBC4YYVX"],
        ["total_amount" =>  "128000", "doc_ref_number" =>  "MUK1WRX2"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "ENZTSC1Q"],
        ["total_amount" =>  "61000", "doc_ref_number" =>  "9ZA2APTT"],
        ["total_amount" =>  "14000", "doc_ref_number" =>  "1D9FY48P"],
        ["total_amount" =>  "17750", "doc_ref_number" =>  "1EJ41F0U"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "PHZ70YIH"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "8J5Y85VD"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "PY9XLJN9"],
        ["total_amount" =>  "8355", "doc_ref_number" =>  "VY6BDUIO"],
        ["total_amount" =>  "11000", "doc_ref_number" =>  "HOZHQGWF"],
        ["total_amount" =>  "9264", "doc_ref_number" =>  "R9B88036"],
        ["total_amount" =>  "33750", "doc_ref_number" =>  "8P99DO1K"],
        ["total_amount" =>  "41217", "doc_ref_number" =>  "5200WHN3"],
        ["total_amount" =>  "37750", "doc_ref_number" =>  "DI80MCD6"],
        ["total_amount" =>  "9264", "doc_ref_number" =>  "DAHIRWYD"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "MIW7YSBJ"],
        ["total_amount" =>  "35525", "doc_ref_number" =>  "E4ELBCLA"],
        ["total_amount" =>  "36486", "doc_ref_number" =>  "UFKO08EY"],
        ["total_amount" =>  "6600", "doc_ref_number" =>  "P71ZNY3X"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "R35YHS8P"],
        ["total_amount" =>  "16000", "doc_ref_number" =>  "9XECI9AT"],
        ["total_amount" =>  "13825", "doc_ref_number" =>  "7FSNTAB6"],
        ["total_amount" =>  "26400", "doc_ref_number" =>  "SOU3LQLI"],
        ["total_amount" =>  "97600", "doc_ref_number" =>  "VMGHNZKT"],
        ["total_amount" =>  "13600", "doc_ref_number" =>  "G9IOPOML"],
        ["total_amount" =>  "11815", "doc_ref_number" =>  "WOMA0NG7"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "MZEFDZB8"],
        ["total_amount" =>  "8385", "doc_ref_number" =>  "GMBLY3JG"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "ZAM2R4S5"],
        ["total_amount" =>  "23000", "doc_ref_number" =>  "8D6CIP60"],
        ["total_amount" =>  "10000", "doc_ref_number" =>  "564WIBXE"],
        ["total_amount" =>  "64000", "doc_ref_number" =>  "6WT07BRG"],
        ["total_amount" =>  "34600", "doc_ref_number" =>  "02GCY2VX"],
        ["total_amount" =>  "22738", "doc_ref_number" =>  "30KO2I14"],
        ["total_amount" =>  "14180", "doc_ref_number" =>  "B8QEOYSZ"],
        ["total_amount" =>  "31000", "doc_ref_number" =>  "09WKSOWH"],
        ["total_amount" =>  "21713", "doc_ref_number" =>  "I32MMKM7"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "BTT2DBEJ"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "7XUPR869"],
        ["total_amount" =>  "17777", "doc_ref_number" =>  "BNMPPCP4"],
        ["total_amount" =>  "12000", "doc_ref_number" =>  "D1WAN8X6"],
        ["total_amount" =>  "30200", "doc_ref_number" =>  "D0YBG0H8"],
        ["total_amount" =>  "11120", "doc_ref_number" =>  "DYV4OFNG"],
        ["total_amount" =>  "39452", "doc_ref_number" =>  "OQF6S227"],
        ["total_amount" =>  "46600", "doc_ref_number" =>  "1KNU23BP"],
        ["total_amount" =>  "77451", "doc_ref_number" =>  "WRCABIQS"],
        ["total_amount" =>  "5500", "doc_ref_number" =>  "O0JEEEBM"],
        ["total_amount" =>  "22500", "doc_ref_number" =>  "15JZP1JP"],
        ["total_amount" =>  "30740", "doc_ref_number" =>  "C0OPQDQL"],
        ["total_amount" =>  "40604", "doc_ref_number" =>  "GB8PBUI3"],
        ["total_amount" =>  "23580", "doc_ref_number" =>  "A446W5NI"],
        ["total_amount" =>  "26150", "doc_ref_number" =>  "2TR557HG"],
        ["total_amount" =>  "24340", "doc_ref_number" =>  "0B0FFL5T"],
        ["total_amount" =>  "20200", "doc_ref_number" =>  "VDTE6T4E"],
        ["total_amount" =>  "43019", "doc_ref_number" =>  "ZL92DR1X"],
        ["total_amount" =>  "5000", "doc_ref_number" =>  "I0QB7KOP"],
        ["total_amount" =>  "8500", "doc_ref_number" =>  "3AU1AWTC"],
        ["total_amount" =>  "51934", "doc_ref_number" =>  "CH0SDLVG"],
        ["total_amount" =>  "55210", "doc_ref_number" =>  "I8GT3KQ3"],
        ["total_amount" =>  "16325", "doc_ref_number" =>  "WD3ERZ8D"],
        ["total_amount" =>  "38800", "doc_ref_number" =>  "XJYY5Q2Y"],
        ["total_amount" =>  "30900", "doc_ref_number" =>  "R857L0RU"],
        ["total_amount" =>  "28780", "doc_ref_number" =>  "6OM5QDMD"],
        ["total_amount" =>  "20000", "doc_ref_number" =>  "4LSPWQJT"],
        ["total_amount" =>  "583639", "doc_ref_number" =>  "3SO7YDS5"],
        ["total_amount" =>  "10500", "doc_ref_number" =>  "5OHY8GBL"],
        ["total_amount" =>  "67580", "doc_ref_number" =>  "NTTG8I5V"],
        ["total_amount" =>  "21000", "doc_ref_number" =>  "IDQB7UUZ"],
        ["total_amount" =>  "24000", "doc_ref_number" =>  "7YUNOC04"],
        ["total_amount" =>  "2", "doc_ref_number" =>  "LGHTL4N4"],
        ["total_amount" =>  "7", "doc_ref_number" =>  "TD6SJPAQ"],
      ];

      $usedTransactions = [];
      $invoice_doc_ref = $invoice_manager->findBestDocRefNumber($invoice->getTotalAmount(), $transactions, $usedTransactions);

      $invoice->setPaid(2);
      $invoice->setDocRefNumber($invoice_doc_ref);
      $invoice->setUpdatedAt($new_date);
      $invoice->setProcessedM(1);
      $invoice->save();
      return $this->json(['data' => ['success' => true, 'statusCode' => 200, 'message' => 'invoice updated']], 201);
    } catch (\Exception $error) {
      return $this->json(['data' => ['success' => false, 'statusCode' => 500, 'message' => $error->getMessage()]], 500);
    }
  }

  public function executeProcesspayments(sfWebRequest $request)
  {
    error_log(print_r($request->getHttpHeader('Content-Type'), true));
    error_log(print_r($request->getHttpHeader('Accept'), true));

    try {
      $response = $request->getContent();
      $response = json_decode($response, true);

      error_log("Callback url coming hot");

      error_log(print_r($response, true));

      error_log("Response ---->");
      error_log(strtolower($response['status']));

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

        error_log("SISIBO Pay PIN Response ---->");
        error_log($processing_response);

        return $this->json(['data' => ['success' => true, 'statusCode' => $status_code, 'message' => $message, 'payload' => $response]], $status_code);
      } else {
        return $this->json(['data' => ['success' => false, 'statusCode' => 422, 'message' => 'Payload Required.', 'payload' => $response]], 422);
      }
    } catch (\Exception $error) {
      return $this->json(['data' => ['success' => false, 'statusCode' => 500, 'message' => $error->getMessage(), 'payload' => $response]], 500);
    }
  }
  public function executeProcessPayment(sfWebRequest $request)
  {
    try {

      error_log(print_r($request->getHttpHeader('Content-Type'), true));
      error_log(print_r($request->getHttpHeader('Accept'), true));

      $response = $request->getContent();
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

        return $this->json(['data' => ['message' => $message, 'payload' => $response]], $status_code);
      } else {
        return $this->json(['data' => ['message' => 'Payload Required.', 'payload' => $response]], 422);
      }
    } catch (\Exception $error) {
      return $this->json(['data' => ['message' => $error->getMessage(), 'payload' => $response]], 500);
    }
  }
  /**
   * Executes 'Query' action
   *
   * Query for Invoice details
   *
   * @param sfRequest $request A request object
   */
  public function executeQueryinvoice(sfWebRequest $request)
  {
    $response_content = $request->getContent();
    error_log('Decoding: ' . $response_content);
    $response_content = json_decode($response_content);
    $otb_helper = new OTBHelper();
    error_log('ERROR: ' . $otb_helper->json_decode_error_list(json_last_error()));
    $api_key = $response_content->api_key;
    $api_secret = $response_content->api_secret;
    $invoice_no = $response_content->invoice;
    $merchant_identifier = trim($response_content->plan_id);

    error_log('----api_key-----' . $api_key . '-----$api_secret----' . $api_secret . '-----invoice_no---' . $invoice_no . '-----merchant_identifier----' . $merchant_identifier);

    $invoice_manager = new InvoiceManager();

    $query_details = $invoice_manager->api_query_invoice($api_key, $api_secret, $invoice_no, $merchant_identifier);
    $this->getResponse()->setHttpHeader('content-type', 'application/json');
    error_log(print_r($query_details, true));
    return $this->renderText(json_encode($query_details));
  }

  /**
   * Executes 'Update' action
   *
   * Query for Invoice details
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdateinvoice(sfWebRequest $request)
  {
    $query_details = [];
    $response_content = $request->getContent();
    error_log('Decoding: ' . $response_content);
    $response_content = json_decode($response_content);
    $otb_helper = new OTBHelper();
    error_log('ERROR: ' . $otb_helper->json_decode_error_list(json_last_error()));

    $api_key = $response_content->api_key;
    $api_secret = $response_content->api_secret;
    $invoice_no = trim($response_content->invoice);

    error_log('----api_key-----' . $api_key . '-----$api_secret----' . $api_secret . '-----invoice_no---' . $invoice_no);
    $this->getResponse()->setHttpHeader('content-type', 'application/json');
    try {
      $payments_manager = new PaymentsManager();

      if ($payments_manager->api_validate_request($api_key, $api_secret)) {
        //if valid get set merchant
        $invoice_manager = new InvoiceManager();
        $invoice = $invoice_manager->get_invoice_by_invoice_number($invoice_no);
        if ($invoice && strlen($invoice->getFormEntry()->getForm()->getPaymentMerchantType())) {
          $query_details = $payments_manager->process_ipn($invoice->getFormEntry()->getForm()->getPaymentMerchantType(), $response_content);
        } else {
          //failed merchant form
          $query_details['status'] = '01';
          $query_details['message'] = 'Not allowed';
          $query_details['data'] = [];
        }
      } else {
        //failed validation
        $query_details['status'] = '01';
        $query_details['message'] = 'Invalid API key/API secret';
        $query_details['data'] = [];
      }
    } catch (Exception $ex) {
      error_log("Debug-pesa: " . $ex);
      $query_details['status'] = '01';
      $query_details['message'] = 'Exception : ' . $ex->getMessage();
      $query_details['data'] = [];
    }
    return $this->renderText(json_encode($query_details));
  }

  /**
   * Executes 'Update' action
   *
   * Query for Profile details
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdateprofile(sfWebRequest $request)
  {
    try {
      $api_key = $request->getParameter("api_key");
      $api_secret = $request->getParameter("api_secret");

      $payments_manager = new PaymentsManager();

      $update_details = array();

      if ($payments_manager->api_validate_request($api_key, $api_secret)) {
        $update_details = $payments_manager->process_ipn_profile("ecitizen", $_REQUEST);
        return $this->renderText(json_encode($update_details));
      } else {
        return $this->renderText(json_encode($update_details));
      }
    } catch (Exception $ex) {
      error_log("Debug-pesa: " . $ex);
    }
  }



  private function json($content, $status = 200)
  {
    $this->getResponse()->setHttpHeader('Content-Type', 'application/json');
    $this->getResponse()->setContent(json_encode($content));
    $this->getResponse()->setStatusCode($status);
    return sfView::NONE;
  }
}
