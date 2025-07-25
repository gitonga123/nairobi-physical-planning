<?php
/**
 * Reconcile actions.
 *
 * Performs reconciliation between a remote payment gateway and permitflow transactions
 *
 * @package    frontend
 * @subpackage reconcile
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class reconcileActions extends sfActions
{
    /**
     * Executes 'Queryservicetotal' action
     *
     * Query for the total collected from a service for a specific day
     *
     * @param sfRequest $request A request object
     */
    public function executeQuerydailytotal(sfWebRequest $request)
    {
      $api_key = $request->getParameter("api_key");
      $api_secret = $request->getParameter("api_secret");
      $service_id = $request->getParameter("service_id");
      $date = $request->getParameter("date");

      //replace pesaflow service_id with permitflow service_id
      $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.payment_cellulant_service_id = ?", $service_id);
      $form = $q->fetchOne();

      if($form)
      {
          $service_id = $form->getFormId();

          $query_details = null;

          $invoice_manager = new InvoiceManager();

          $query_details = $invoice_manager->api_query_daily_total($api_key, $api_secret, $service_id, $date);

          return $this->renderText(json_encode($query_details));
       }
       else
       {
         $query_details = null;
         $query_details['error'] = 'Service ID Unknown';
         return $this->renderText(json_encode($query_details));
       }
    }

    /**
     * Executes 'Queryperiodtotal' action
     *
     * Query for the total collected from a service for a specified period
     *
     * @param sfRequest $request A request object
     */
    public function executeQueryperiodtotal(sfWebRequest $request)
    {
      $api_key = $request->getParameter("api_key");
      $api_secret = $request->getParameter("api_secret");
      $service_id = $request->getParameter("service_id");
      $from_date = $request->getParameter("from_date");
      $to_date = $request->getParameter("to_date");

      //replace pesaflow service_id with permitflow service_id
      $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.payment_cellulant_service_id = ?", $service_id);
      $form = $q->fetchOne();

      if($form)
      {
          $service_id = $form->getFormId();

          $query_details = null;

          $invoice_manager = new InvoiceManager();

          $query_details = $invoice_manager->api_query_period_total($api_key, $api_secret, $service_id, $from_date, $to_date);

          return $this->renderText(json_encode($query_details));
      }
      else
      {
          $query_details = null;
          $query_details['error'] = 'Service ID Unknown';
          return $this->renderText(json_encode($query_details));
      }
    }

    /**
     * Executes 'Queryinvoice' action
     *
     * Query for Invoice details
     *
     * @param sfRequest $request A request object
     */
    public function executeQuerytransaction(sfWebRequest $request)
    {
        $api_key = $request->getParameter("api_key");
        $api_secret = $request->getParameter("api_secret");
        $invoice_no = $request->getParameter("invoiceNumber");

        $query_details = null;

        $invoice_manager = new InvoiceManager();

        $query_details = $invoice_manager->api_query_invoice($api_key, $api_secret, $invoice_no);

        return $this->renderText(json_encode($query_details));
    }

    /**
     * Executes 'Confirm' action
     *
     * Query for confirm details
     *
     * @param sfRequest $request A request object
     */
    public function executeConfirmtransaction(sfWebRequest $request)
    {
        $api_key = $request->getParameter("api_key");
        $api_secret = $request->getParameter("api_secret");
        $invoice_no = $request->getParameter("invoiceNumber");

        $transaction_details = array();

        $transaction_details['transaction_id'] = $request->getParameter("invoiceNumber");

        if($request->getParameter("transactiondate"))
        {
          $transaction_details['transaction_date'] = $request->getParameter("transactiondate");
        }
        elseif($request->getParameter("transaction_date"))
        {
          $transaction_details['transaction_date'] = $request->getParameter("transaction_date");
        }

        if($request->getParameter("transactionstatus"))
        {
          $transaction_details['transaction_status'] = $request->getParameter("transactionstatus");
        }
        elseif($request->getParameter("transaction_status"))
        {
          $transaction_details['transaction_status'] = $request->getParameter("transaction_status");
        }

        $transaction_details['amount_paid'] = $request->getParameter("amount");
        $transaction_details['paid_by'] = $request->getParameter("paidby");

        $update_details = null;

        $invoice_manager = new InvoiceManager();

        $update_details = $invoice_manager->api_confirm_invoice($api_key, $api_secret, $invoice_no, $transaction_details);

        return $this->renderText(json_encode($update_details));
    }
}
