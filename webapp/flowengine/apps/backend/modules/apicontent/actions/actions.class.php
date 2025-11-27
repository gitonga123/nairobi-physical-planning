<?php

/**
 * apicontent actions.
 *
 * @package    symfony
 * @subpackage merchant
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class apicontentActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->apiContent = Doctrine_Core::getTable('ApiContent')
      ->createQuery('a')
      ->execute();
    $this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new ApiContentForm();
    $this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new ApiContentForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('ApiContent c')
      ->where('c.id = ?', $request->getParameter('id'));
    $apiContent = $q->fetchOne();
    $this->forward404Unless($apiContent, sprintf('Object ApiContent does not exist (%s).', $request->getParameter('id')));
    $this->form = new ApiContentForm($apiContent);
    $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('ApiContent c')
      ->where('c.id = ?', $request->getParameter('id'));
    $apiContent = $q->fetchOne();
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($apiContent, sprintf('Object ApiContent does not exist (%s).', $request->getParameter('id')));
    $this->form = new ApiContentForm($apiContent);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    // $request->checkCSRFProtection();
    $q = Doctrine_Query::create()
      ->from('ApiContent c')
      ->where('c.id = ?', $request->getParameter('id'));
    $apiContent = $q->fetchOne();
    $this->forward404Unless($apiContent, sprintf('Object ApiContent does not exist (%s).', $request->getParameter('id')));
    $apiContent->delete();

    $this->getUser()->setFlash("Success", "Record Deleted Successfuly");
    $this->redirect('/plan/apicontent/index/');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid()) {
      $apiContent = $form->save();

      $this->getUser()->setFlash("Success", "Record saved Successfuly");
      //
      $this->redirect('/plan/apicontent/index/');
    }
  }

  //rabbit
  public function executeRabbittest(sfWebRequest $request)
  {
    $api_call = new ApiCalls();
    $submission = Doctrine_Query::create()->from('FormEntry e')->where('e.id = ?', $request->getParameter('id'))->fetchOne();
    $api_call->registerPlan($request->getParameter('form_id'), $submission);
    exit();
  }
  public function executeRabbittestinv(sfWebRequest $request)
  {
    $api_call = new ApiCalls();
    $submission = Doctrine_Query::create()->from('FormEntry e')->where('e.id = ?', $request->getParameter('id'))->fetchOne();
    $invoice = Doctrine_Query::create()->from('MfInvoice i')->where('i.id = ?', $request->getParameter('invoice_id'))->fetchOne();
    $api_call->postInvoice($submission, $invoice);
    exit();
  }
}
