<?php

/**
 *
 * KCB BUNI GATEWAY
 */

class KCBGateway
{
    public $invoice_manager;
    public function __construct()
    {
        $this->invoice_manager = new InvoiceManager();
    }

    public function searchBill($billId, $messageId)
    {
        $invoice = $this->invoice_manager->get_invoice_by_invoice_number($billId);
        // you can shift the condition to a switch.
        if ($invoice) {
            if ($invoice->getPaid() == 1) {
                if ($invoice->getTransactionId()) {
                    $transactionId = $invoice->getTransactionId();
                } else {
                    $transactionId = $this->invoice_manager->getTransactionNumber($invoice->getId());
                    $invoice->setMessageId($messageId);
                    $invoice->setTransactionId("");
                    $invoice->save();
                }

                $application = $invoice->getFormEntry();
                $utilityName = $application->getForm();

                $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId())
                    ->limit(1);
                $user = $q->fetchOne();
                return [
                    'messageID' => $messageId,
                    'transactionId' => strtoupper($transactionId),
                    'utilityName' => $utilityName->getFormName(),
                    'customerName' => $user->getFullname(),
                    'amount' => $invoice->getTotalAmount(),
                    'statusCode' => '0',
                ];
            } else {
                if ($invoice->getPaid() == 2) {
                    return [
                        'messageID' => $messageId,
                        'transactionId' => '',
                        'utilityName' => '',
                        'customerName' => '',
                        'amount' => $invoice->getTotalAmount(),
                        'statusCode' => '1',
                        'statusMessage' => 'Invoice Already Paid. Please re-check the invoice no.'
                    ];
                } else if ($invoice->getPaid() == 3) {
                    return [
                        'messageID' => $messageId,
                        'transactionId' => '',
                        'utilityName' => '',
                        'customerName' => '',
                        'amount' => $invoice->getTotalAmount(),
                        'statusCode' => '1',
                        'statusMessage' => 'Invoice Cancelled. Please re-check the invoice no.'
                    ];
                } else {
                    return [
                        'messageID' => $messageId,
                        'transactionId' => '',
                        'utilityName' => '',
                        'customerName' => '',
                        'amount' => $invoice->getTotalAmount(),
                        'statusCode' => '1',
                        'statusMessage' => 'Invoice Not found!'
                    ];
                }
            }
        } else {
            return [
                'messageID' => '',
                'transactionId' => '',
                'utilityName' => '',
                'customerName' => '',
                'amount' => '',
                'statusCode' => '1',
                'statusMessage' => 'Invoice Not Found!'
            ];
        }
    }

    // process payment notifications received from external payment server
    // process payments from KCB buni
    public function ipn($request_details, $invoice)
    {

        $update_details = array();
        $transaction_id = strtoupper(trim($request_details->transactionId));
        $amount_paid = trim($request_details->transactionAmt);
        $transaction_date = $request_details->transactionDate;
        $update_details = [];
        $update_details['transactionId'] = $transaction_id;
        $update_details['messageId'] = $request_details->messageId;

        if (empty($transaction_id)) {
            $update_details['statusCode'] = '1';
            $update_details['statusMessage'] = 'Invalid Transaction id';
            return $update_details;
        }

        if (empty($amount_paid) || floatval($amount_paid) == 0) {
            $update_details['statusCode'] = '1';
            $update_details['message'] = 'Invalid Transaction amount';
            return $update_details;
        }

        $amount_paid = floatval($amount_paid);
        $paid_by = trim($request_details->firstName) + " " + trim($request_details->middleName) + " " + trim($request_details->lastName);
        try {
            if ($invoice->getPaid() == 1) {
                $status = $this->invoice_manager->check_total_amount_status($invoice->getId(), $amount_paid);
                if ($status === 2) {
                    //pending
                    $q = Doctrine_Query::create()
                        ->from("ApFormPayments a")
                        ->where("a.invoice_id = ? AND a.status = ?", array($invoice->getId(), 15))
                        ->orderBy('a.afp_id desc');
                    $transaction = $q->fetchOne();

                    if ($transaction) {
                        error_log('----Transaction found----');
                        //Update transaction details
                        $transaction->setBillingState($transaction_id);
                        $transaction->setPaymentDate(date("Y-m-d H:i:s"));
                        $transaction->setPaymentFullname($paid_by);
                        $transaction->setStatus(2);
                        $transaction->setPaymentStatus("paid");
                        $transaction->setPaymentMerchantType('KCB BUNI');
                        $transaction->setPaymentAmount($amount_paid);
                        $transaction->setInvoiceId($invoice->getId());
                        $transaction->setPaymentCurrency($request_details->currency);
                        $transaction->setNarration($request_details->narration);
                        $transaction->setOthers(json_encode($request_details));
                        $transaction->setDebitMSISDN($request_details->debitMSISDN);
                        //invoice
                        $invoice->setPaid(2);
                        $invoice->setTransactionId($transaction_id);
                    } else {
                        error_log('----Transaction not found----');

                        //Add a new transaction if one doesn't exist
                        $transaction = new ApFormPayments();
                        $transaction->setFormId($invoice->getFormEntry()->getFormId());
                        $transaction->setRecordId($invoice->getFormEntry()->getEntryId());
                        $transaction->setPaymentId($invoice->getFormEntry()->getFormId() . "/" . $invoice->getFormEntry()->getEntryId() . "/" . $invoice->getId());
                        $transaction->setDateCreated(date("Y-m-d H:i:s"));
                        $transaction->setPaymentFullname($paid_by);
                        $transaction->setPaymentAmount($amount_paid);
                        $transaction->setPaymentMerchantType('KCB BUNI');
                        $transaction->setPaymentTestMode("0");

                        $transaction->setBillingState($transaction_id);
                        $transaction->setPaymentDate(date("Y-m-d H:i:s"));
                        $transaction->setInvoiceId($invoice->getId());
                        $transaction->setStatus(2);
                        $transaction->setPaymentStatus("paid");
                        $transaction->setPaymentCurrency($request_details->currency);
                        $transaction->setNarration($request_details->narration);
                        $transaction->setOthers(json_encode($request_details));
                        $transaction->setDebitMSISDN($request_details->debitMSISDN);
                        //invoice
                        $invoice->setTransactionId($transaction_id);
                        $invoice->setPaid(2);
                    }
                    $transaction->save();
                    $invoice->save();
                    //response
                    $update_details['statusCode'] = '0';
                    $update_details['statusMessage'] = 'Transaction updated successfully';
                } else {
                    $update_details['statusCode'] = '1';
                    $update_details['statusMessage'] = 'Invalid Amount. Partial Payments not allowed.';
                }
            } else {
                $update_details['statusCode'] = '1';
                $update_details['statusMessage'] = 'Invoice Already Paid. Please re-check the invoice no.';
            }
        } catch (Exception $ex) {
            $update_details['statusCode'] = '1';
            $update_details['statusMessage'] = 'Exception ' . $ex->getMessage();
        }

        return $update_details;
    }
}
