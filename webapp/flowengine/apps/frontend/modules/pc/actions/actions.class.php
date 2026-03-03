<?php

class pcActions extends sfActions
{
  /**
   * Executes 'Index' action
   *
   * Displays list of all of the currently logged in client's permits
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
  }

  /**
   *
   * Execute 'Open Request' action
   * 
   * Allows external systems to request for a permit without having an account
   *
   **/
  public function executeOr(sfWebRequest $request)
  {
    if ($request->getParameter("ref")) {
      $permitrefno = $request->getParameter("ref");

      $q = Doctrine_Query::create()
        ->from('SavedPermit a')
        ->leftJoin('a.FormEntry b')
        ->where('a.permit_id = ? OR b.application_id = ? OR a.id = ?', array($permitrefno, $permitrefno, $permitrefno))
        ->andWhere('a.permit_status = 1');
      $permitref = $q->fetchOne();

      if ($permitref) {
        $permit_manager = new PermitManager();

        $this->template = $permit_manager->generate_permit_qr_template($permitref->getId(), false);
      } else {
        $this->template = '<div><h3><p style="color:red;font-weight:bold;">This is an invalid permit. Kindly report anyone using permit reference number ' . $permitrefno . '</p></h3></div><br/>';
      }
    } else {
      $this->template = "Invalid Request";
    }

    $this->setLayout('layout-mentor');
  }


  /**
   *
   * Execute 'Invoice Request' action
   * 
   * Allows external systems to request for a permit without having an account
   *
   **/
  public function executeIr(sfWebRequest $request)
  {
    if ($request->getParameter("ref")) {
      $invoiceRefno = $request->getParameter("ref");

      $q = Doctrine_Query::create()
        ->from('MfInvoice a')
        ->leftJoin('a.FormEntry b')
        ->where('a.id = ? ', $invoiceRefno);
      $invoice = $q->fetchOne();

      if ($invoice) {
        $invoice_manager = new InvoiceManager();

        $this->template = $invoice_manager->generate_invoice_template($invoice->getId(), false, true);
      } else {
        $this->template = '<div><h3><p style="color:red;font-weight:bold;">This is an invalid invoice. Kindly report anyone using invoice Reference Number ' . $invoiceRefno . '</p></h3></div><br/>';
      }
    } else {
      $this->template = "Invalid Request";
    }

    $this->setLayout('layout-mentor');
  }
}
