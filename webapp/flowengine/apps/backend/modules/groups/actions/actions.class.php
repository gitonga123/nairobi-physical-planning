<?php

/**
 * groups actions.
 *
 * @package    permit
 * @subpackage groups
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class groupsActions extends sfActions
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
       ->from('mfGuardGroup a')
	    ->orderBy('a.name ASC');
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
    $this->form = new mfGuardGroupForm();

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

    $this->form = new mfGuardGroupForm();

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
    $this->forward404Unless($mf_guard_group = Doctrine_Core::getTable('mfGuardGroup')->find(array($request->getParameter('id'))), sprintf('Object mf_guard_group does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new mfGuardGroupForm($mf_guard_group);

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
    $this->forward404Unless($mf_guard_group = Doctrine_Core::getTable('mfGuardGroup')->find(array($request->getParameter('id'))), sprintf('Object mf_guard_group does not exist (%s).', $request->getParameter('id')));
    $this->form = new mfGuardGroupForm($mf_guard_group);

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
      $mf_guard_group = $form->save();

      $this->redirect('/plan/groups/index');
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
    $this->forward404Unless($mf_guard_group = Doctrine_Core::getTable('mfGuardGroup')->find(array($request->getParameter('id'))), sprintf('Object mf_guard_group does not exist (%s).', $request->getParameter('id')));
    

    $audit = new Audit();
    $audit->saveAudit("", "deleted group of id ".$mf_guard_group->getId());

    $mf_guard_group->delete();

    $this->redirect('/plan/groups/index');
  }
  
  /**
	 * Executes 'duplicate' action
	 *
	 * Duplicate group with its permissions
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeDuplicate(sfWebRequest $request)
  {
        $q = Doctrine_Query::create()
          ->from('mfGuardGroup a')
	        ->where('a.id = ?', $request->getParameter("id"));
        $group = $q->fetchOne();
        
        $roles = $group->getPermissions();
        
        $newgroup = new MfGuardGroup();
        $newgroup->setName($group->getName()." Copy Group");
        $newgroup->setDescription($group->getDescription()." (Copy)");
        $newgroup->save();
        
        foreach($roles as $role)
        {
            $group_permission = new MfGuardGroupPermission();
            $group_permission->setGroupId($newgroup->getId());
            $group_permission->setPermissionId($role->getId());
            $group_permission->save();
        }
  
        $this->redirect("/plan/groups/index");
  }
}
