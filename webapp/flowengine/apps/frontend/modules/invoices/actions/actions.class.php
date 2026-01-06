<?php
/*
 * Invoices actions.
 *
 * Displays all of the currently logged in client's invoices
 *
 * @package    frontend
 * @subpackage invoices
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class invoicesActions extends sfActions
{
    /**
     * Executes 'Index' action
     *
     * Displays list of all of the currently logged in client's invoices
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        if ($request->getGetParameter("filter")) {
            $q = Doctrine_Query::create()
                ->from('MfInvoice a')
                ->leftJoin('a.FormEntry b')
                ->where('b.user_id = ?', $this->getUser()->getGuardUser()->getId())
                ->andWhere('b.approved = ?', $request->getGetParameter("filter"))
                ->orderBy('a.id DESC');
        } else {
            $q = Doctrine_Query::create()
                ->from('MfInvoice a')
                ->leftJoin('a.FormEntry b')
                ->where('b.user_id = ?', $this->getUser()->getGuardUser()->getId())
                ->orderBy('a.id DESC');
        }

        $this->pager = new sfDoctrinePager('MfInvoice', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();

        $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Invoices ");

        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'View' action
     *
     * Displays a full invoice
     *
     * @param sfRequest $request A request object
     */
    public function executeView(sfWebRequest $request)
    {

        $otb_helper = new OTBHelper();

        $this->getUser()->setAttribute("checkout", false);

        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->invoice = $q->fetchOne();

        if (empty($this->invoice)) {
            $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Invoice Not Found");

            return $this->redirect("/index.php/errors/notfound");
        }

        if ($request->getParameter("confirm") == md5($this->invoice->getId())) {
            $this->invoice->setPaid("15");
            $this->invoice->setUpdatedAt(date("Y-m-d H:i:s"));
            $this->invoice->save();
        }

        error_log("Invoice status is " . $this->invoice->getPaid());
        if ($this->invoice->getPaid() == 1) {
            $token = $_SESSION['jambo_backup_token'];
            // check if invoice is paid;
            $billing_reference_number = $this->invoice->getFormEntry()->getFormId() . "" . $this->invoice->getFormEntry()->getEntryId() . "" . $this->invoice->getId();

            error_log("Invoice bill reference number --->" . $billing_reference_number);
            $result = $otb_helper->check_payment_jambo_pay($token, $billing_reference_number);

            error_log('Resulting response from otb check');

            error_log(json_encode($result));
            if ($result['success']) {
                $otb_helper->updateInvoiceToPaid($billing_reference_number, $this->invoice->id, $result['receipt']);
            }
        }

        $this->list_print_urls = [];

        if ($this->invoice->getPaid() == 2 && !empty($this->invoice->getReceiptNumber())) {
            $receipt_data = $this->invoice->getReceiptNumber();

            $from_string_ids = trim($receipt_data);

            $receipt_ids = json_decode($from_string_ids, true);

            if (is_array($receipt_ids) && !empty($receipt_ids)) {
                $api_url = sfConfig::get('app_api_jambo_url');

                foreach ($receipt_ids as $key => $receipt_number) {
                    $my_string = "{$api_url}api/v1/print/receipt/{$receipt_number}/Physical_Planning";
                    array_push($this->list_print_urls, $my_string);
                }
            }

        }

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $this->invoice->getAppId());
        $this->application = $q->fetchOne();

        $this->getResponse()->setTitle(Functions::site_settings()->getOrganisationName() . "| Invoice " . $this->invoice->getInvoiceNumber());

        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Viewreceipt' action
     *
     * Displays invoice receipts
     *
     * @param sfRequest $request A request object
     */
    public function executeViewreceipt(sfWebRequest $request)
    {
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Attach' action
     *
     * Attach Receipt/Online Payment
     *
     * @param sfRequest $request A request object
     */
    public function executeAttach(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $request->getParameter("invoiceid"));
        $this->invoice = $q->fetchOne();
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Payonline' action
     *
     * Allows client to make mobile payments
     *
     * @param sfRequest $request A request object
     */
    public function executePayonline(sfWebRequest $request)
    {

        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->invoice = $q->fetchOne();
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Printinvoice' action
     *
     * Prints an invoice to PDF
     *
     * @param sfRequest $request A request object
     */
    public function executePrintinvoice(sfWebRequest $request)
    {
        $invoice_manager = new InvoiceManager();
        $invoice_manager->save_to_pdf($request->getParameter("id"));

        exit;
    }


    /**
     * Executes 'Pay' action
     *
     * Pay for invoices
     *
     * @param sfRequest $request A request object
     */
    public function executePay(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $request->getParameter("id"));
        $invoice = $q->fetchOne();

        if ($invoice->getPaid() == 2) {
            $this->redirect("/index.php/invoices/view/id/" . $invoice->getId());
        } else {
            $application = $invoice->getFormEntry();

            $this->getUser()->setAttribute('form_id', $application->getFormId());
            $this->getUser()->setAttribute('entry_id', $application->getEntryId());
            $this->getUser()->setAttribute('invoice_id', $invoice->getId());

            $this->redirect("/index.php/forms/payment/invoice/" . $invoice->getId());
        }
    }
}
