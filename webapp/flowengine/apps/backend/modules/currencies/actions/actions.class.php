<?php

/**
 * currencies actions.
 *
 * @package    symfony
 * @subpackage currencies
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class currenciesActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $wizard_manager = new WizardManager();

    if($wizard_manager->is_first_run())
    {
      $this->redirect("/backend.php/dashboard");
    }
    $this->currenciess = Doctrine_Core::getTable('Currencies')
      ->createQuery('a')
      ->execute();
    $this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new CurrenciesForm();
    $this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new CurrenciesForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($currencies = Doctrine_Core::getTable('Currencies')->find(array($request->getParameter('id'))), sprintf('Object currencies does not exist (%s).', $request->getParameter('id')));
    $this->form = new CurrenciesForm($currencies);
    $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($currencies = Doctrine_Core::getTable('Currencies')->find(array($request->getParameter('id'))), sprintf('Object currencies does not exist (%s).', $request->getParameter('id')));
    $this->form = new CurrenciesForm($currencies);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
   // $request->checkCSRFProtection();

    $this->forward404Unless($currencies = Doctrine_Core::getTable('Currencies')->find(array($request->getParameter('id'))), sprintf('Object currencies does not exist (%s).', $request->getParameter('id')));
    $currencies->delete();
    $this->getUser()->setFlash("Success", "Record Deleted Successfuly") ;
    $this->redirect('/backend.php/currencies/index/');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $currencies = $form->save();
      $this->getUser()->setFlash("Success", "Record saved Successfuly") ;
      //
      $this->redirect('/backend.php/currencies/index/');
    }
  }
}
