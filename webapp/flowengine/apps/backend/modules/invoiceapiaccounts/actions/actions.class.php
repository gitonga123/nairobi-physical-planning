<?php

/**
 * invoiceapiaccountsactions.
 *
 * @package    backend
 * @subpackage invoiceapiaccounts
 * @author     Thomas Juma
 * @version    2.5: 2017-01-24
 */
class invoiceapiaccountsActions extends sfActions
{
  /**
	 * Executes 'index' function
	 *
	 * Display a list of existing objects
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeIndex(sfWebRequest $request)
  {
    //Get list of all objects
    $q = Doctrine_Query::create()
        ->from('InvoiceApiAccount a')
        ->orderBy('a.mda_name ASC');
    $this->accounts = $q->execute();

    $this->setLayout("layout-settings");
  }

  /**
	 * Executes 'new' function
	 *
	 * Create a new object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeNew(sfWebRequest $request)
  {
    $this->form = new InvoiceApiAccountForm();

	  $this->setLayout("layout-settings");
  }

  /**
	 * Executes 'create' function
	 *
	 * Save a new object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new InvoiceApiAccountForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  /**
	 * Executes 'edit' function
	 *
	 * Edit an existing object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($account = Doctrine_Core::getTable('InvoiceApiAccount')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new InvoiceApiAccountForm($account);

    $this->setLayout("layout-settings");
  }

  /**
	 * Executes 'update' action
	 *
	 * Update an existing object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($account = Doctrine_Core::getTable('InvoiceApiAccount')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $this->form = new InvoiceApiAccountForm($account);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  /**
	 * Executes 'processForm' function
	 *
	 * Validate the form and save the object
	 *
	 * @param sfRequest $request A request object
	 */
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $account = $form->save();

      $this->redirect('/plan/invoiceapiaccounts/index');
    }
  }

  /**
	 * Executes 'delete' action
	 *
	 * Delete the object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeDelete(sfWebRequest $request)
  {
    $this->forward404Unless($account = Doctrine_Core::getTable('InvoiceApiAccount')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $account->delete();

    $this->redirect('/plan/invoiceapiaccounts/index');
  }

}
