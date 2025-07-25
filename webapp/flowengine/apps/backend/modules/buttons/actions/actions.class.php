<?php

/**
 * buttons actions.
 *
 * @package    permit
 * @subpackage buttons
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class buttonsActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
       ->from("Buttons a")
       ->leftJoin("a.Submenus b")
       ->andWhere("b.id = ?", $request->getParameter('filter'));
    $this->buttonss  = $q->execute();
    $this->filter = $request->getParameter('filter');
	$this->setLayout(false);
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new ButtonsForm();
	$this->setLayout(false);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new ButtonsForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($buttons = Doctrine_Core::getTable('Buttons')->find(array($request->getParameter('id'))), sprintf('Object buttons does not exist (%s).', $request->getParameter('id')));
    $this->form = new ButtonsForm($buttons);
    $this->filter = $request->getParameter('filter');
	  $this->setLayout(false);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($buttons = Doctrine_Core::getTable('Buttons')->find(array($request->getParameter('id'))), sprintf('Object buttons does not exist (%s).', $request->getParameter('id')));
    $this->form = new ButtonsForm($buttons);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {

    $this->forward404Unless($buttons = Doctrine_Core::getTable('Buttons')->find(array($request->getParameter('id'))), sprintf('Object buttons does not exist (%s).', $request->getParameter('id')));
    $buttons->delete();

    //$this->redirect('/plan/settings/workflow?load=actions');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $buttons = $form->save();
	  
      //check if credential exists for this button
      $q = Doctrine_Query::create()
        ->from('MfGuardPermission a')
       ->where('a.name = ?', 'accessbutton'.$buttons->getId());
      $credential = $q->execute();
      if($q->count() == 0)
      {
        $credential = new MfGuardPermission();
        $credential->setName('accessbutton'.$buttons->getId());
        $credential->setDescription("Access to ".$buttons->getTitle()." button");
        $credential->save();
        error_log("Adding credential");
      }
      
        error_log("Done credential");
      $link = "";
      
      if($request->getPostParameter("link_action") != "" || $request->getPostParameter("link_action") != "0")
      {
        $link = $request->getPostParameter("link_action");
      }
      
      if($request->getPostParameter("link_moveto") != "" || $request->getPostParameter("link_moveto") != "0")
      {
        $link .= $request->getPostParameter("link_moveto");
      }
      
      if($link)
      {
        if($request->getPostParameter("link_action"))
        {
          $buttons->setLink($link);
        }
        $buttons->save();
      }

      $q = Doctrine_Query::create()
        ->from("MfGuardPermission a")
        ->where("a.name = ?", "accessbutton".$buttons->getId());
      $permission = $q->fetchOne();
      if($permission)
      {
        $grouppermissions = $permission->getMfGuardGroupPermission();
        foreach($grouppermissions as $grouppermission)
        {
          $grouppermission->delete();
        }
      }

      $groups = $request->getPostParameter('allowed_groups');

      foreach($groups as $group)
      {
          $q = Doctrine_Query::create()
          ->from("MfGuardGroup a")
          ->where("a.id = ?", $group)
          ->orderBy("a.name ASC");
        $group = $q->fetchOne();
        if($group)
        {
          $found = false;
          $grouppermissions = $group->getPermissions();
          foreach($grouppermissions as $grouppermission)
          {
              if($permission->getId() == $grouppermission->getId())
              {
                //permission already exists
              }
              else
              {
                $found = true;
              }
          }

          if($found)
          {
            //add permission to group
            $permissiongroup = new MfGuardGroupPermission();
            $permissiongroup->setGroupId($group->getId());
            $permissiongroup->setPermissionId($permission->getId());
            $permissiongroup->save();
          }
        }
      }

      //$this->redirect('/plan/settings/workflow?load=actions');
    }
  }
}
