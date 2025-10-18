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

      $invoice->setPaid(2);

      $randomMinutes = rand(30, 45);

      $new_date = strtotime("+{$randomMinutes} seconds", strtotime(date('Y-m-d H:i:s')));

      $invoice->setUpdatedAt($new_date);

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
