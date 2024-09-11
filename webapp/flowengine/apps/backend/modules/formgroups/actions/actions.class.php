<?php

/**
 * formgroups actions.
 *
 * @package    permit
 * @subpackage formgroups
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class formgroupsActions extends sfActions
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
    $wizard_manager = new WizardManager();

    if($wizard_manager->is_first_run())
    {
      $this->redirect("/plan/dashboard");
    }
    //Get list of all objects
    $q = Doctrine_Query::create()
        ->from('FormGroups a')
        ->orderBy('a.group_name ASC');
    $this->groups = $q->execute();

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
    $this->form = new FormGroupsForm();

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

    $this->form = new FormGroupsForm();

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
    $this->forward404Unless($group = Doctrine_Core::getTable('FormGroups')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new FormGroupsForm($group);

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
    $this->forward404Unless($group = Doctrine_Core::getTable('FormGroups')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $this->form = new FormGroupsForm($group);

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
      $group = $form->save();

      $this->redirect('/plan/formgroups/index');
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
    $this->forward404Unless($group = Doctrine_Core::getTable('FormGroups')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $group->delete();

    $this->redirect('/plan/formgroups/index');
  }
}
