<?php

/**
 * department actions.
 *
 * @package    permit
 * @subpackage department
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class departmentActions extends sfActions
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
        ->from('Department a')
        ->orderBy('a.department_name ASC');
    $this->departments = $q->execute();

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
    $this->form = new DepartmentForm();
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
    //Audit 
    Audit::audit("", "Created department");

    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new DepartmentForm();

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
    $this->forward404Unless($department = Doctrine_Core::getTable('Department')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new DepartmentForm($department);
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
    //Audit 
    Audit::audit("", "Updated department");

    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($department = Doctrine_Core::getTable('Department')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $this->form = new DepartmentForm($department);

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
      $department = $form->save();

      $this->redirect('/backend.php/users/index');
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
    //Audit 
    Audit::audit("", "Deleted department");

    $this->forward404Unless($department = Doctrine_Core::getTable('Department')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $department->delete();

    $this->redirect('/backend.php/users/index');
  }

}
