<?php

/**
 * credentials actions.
 *
 * @package    permit
 * @subpackage credentials
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class credentialsActions extends sfActions
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
	  $q = Doctrine_Query::create()
       ->from('mfGuardPermission a')
	    ->orderBy('a.name ASC');
    $this->roles = $q->execute();
	 
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
    $this->form = new mfGuardPermissionForm();

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

    $this->form = new mfGuardPermissionForm();

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
    $this->forward404Unless($mf_guard_permission = Doctrine_Core::getTable('mfGuardPermission')->find(array($request->getParameter('id'))), sprintf('Object mf_guard_group does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new mfGuardPermissionForm($mf_guard_permission);

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
    $this->forward404Unless($mf_guard_permission = Doctrine_Core::getTable('mfGuardPermission')->find(array($request->getParameter('id'))), sprintf('Object mf_guard_group does not exist (%s).', $request->getParameter('id')));
    $this->form = new mfGuardPermissionForm($mf_guard_permission);

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
      $mf_guard_permission = $form->save();

      $this->redirect('/plan/credentials/index');
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
    $this->forward404Unless($mf_guard_permission = Doctrine_Core::getTable('mfGuardPermission')->find(array($request->getParameter('id'))), sprintf('Object mf_guard_permission does not exist (%s).', $request->getParameter('id')));
    
    $audit = new Audit();
    $audit->saveAudit("", "deleted credentials of id ".$mf_guard_permission->getId());

    $mf_guard_permission->delete();

    $this->redirect('/plan/credentials/index');
  }
}
