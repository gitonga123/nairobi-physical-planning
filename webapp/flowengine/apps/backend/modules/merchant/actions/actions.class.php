<?php

/**
 * merchant actions.
 *
 * @package    symfony
 * @subpackage merchant
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class merchantActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $wizard_manager = new WizardManager();

    if($wizard_manager->is_first_run())
    {
      $this->redirect("/backend.php/dashboard");
    }
    $this->merchants = Doctrine_Core::getTable('Merchant')
      ->createQuery('a')
      ->execute();
    $this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new MerchantForm();
    $this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new MerchantForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($merchant = Doctrine_Core::getTable('Merchant')->find(array($request->getParameter('id'))), sprintf('Object merchant does not exist (%s).', $request->getParameter('id')));
    $this->form = new MerchantForm($merchant);
    $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($merchant = Doctrine_Core::getTable('Merchant')->find(array($request->getParameter('id'))), sprintf('Object merchant does not exist (%s).', $request->getParameter('id')));
    $this->form = new MerchantForm($merchant);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
   // $request->checkCSRFProtection();

    $this->forward404Unless($merchant = Doctrine_Core::getTable('Merchant')->find(array($request->getParameter('id'))), sprintf('Object merchant does not exist (%s).', $request->getParameter('id')));
    $merchant->delete();

   $this->getUser()->setFlash("Success", "Record Deleted Successfuly") ;
    $this->redirect('/backend.php/merchant/index/');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $merchant = $form->save();

      $this->getUser()->setFlash("Success", "Record saved Successfuly") ;
      //
      $this->redirect('/backend.php/merchant/index/');
    }
  }
}
