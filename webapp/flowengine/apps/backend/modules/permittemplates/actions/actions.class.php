<?php

/**
 * permittemplates actions.
 *
 * @package    backend
 * @subpackage permittemplates
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class permittemplatesActions extends sfActions
{

  public function executeIndex(sfWebRequest $request)
  {
      //Audit 
      Audit::audit("", "Accessed permit settings"); 

      $wizard_manager = new WizardManager();

      if($wizard_manager->is_first_run())
      {
        $this->redirect("/plan/dashboard");
      }
      
      if($request->getParameter("filter"))
      {
        //Save filter to session
        $this->getUser()->setAttribute('filter', $request->getParameter("filter"));

        $q = Doctrine_Query::create()
          ->from("Menus a")
          ->where("a.id = ?", $request->getParameter("filter", $this->getUser()->getAttribute("filter")));
        $service = $q->fetchOne();

        if($service)
        {
          $this->getUser()->setAttribute('service_type', $service->getServiceType());
        }

        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.menu_id = ?", $request->getParameter("filter", $this->getUser()->getAttribute("filter")));
        $stages = $q->execute();

        $stages_array = array();

        foreach($stages as $stage)
        {
            $stages_array[] = "a.applicationstage = ".$stage->getId();
        }

        $stages_query = implode(" OR ", $stages_array);

        if($stages_query == "")
        {
          $q = Doctrine_Query::create()
            ->from("Permits a");
          $this->templates = $q->execute();
        }
        else 
        {
          $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where($stages_query);
          $this->templates = $q->execute();
        }
      }
      else 
      {
        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.menu_id = ?", $this->getUser()->getAttribute('filter'));
        $stages = $q->execute();

        $stages_array = array();

        foreach($stages as $stage)
        {
            $stages_array[] = "a.applicationstage = ".$stage->getId();
        }

        $stages_query = implode(" OR ", $stages_array);

        if($stages_query == "")
        {
          $q = Doctrine_Query::create()
            ->from("Permits a");
          $this->templates = $q->execute();
        }
        else 
        {
          $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where($stages_query);
          $this->templates = $q->execute();
        }
      }

      $this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new PermitsForm();

    $this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Created a new permit template");

    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new PermitsForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($permit = Doctrine_Core::getTable('Permits')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new PermitsForm($permit);

    $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Updated an existing permit template");

    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($permit = Doctrine_Core::getTable('Permits')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    $this->form = new PermitsForm($permit);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Deleted existing permit template");

    $this->forward404Unless($permit = Doctrine_Core::getTable('Permits')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    $permit->delete();

    $this->redirect('/plan/permittemplates/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $permit = $form->save();

      
      $this->redirect('/plan/permittemplates/index');
    }

  }
  public function executeSigninggroups(sfWebRequest $request)
  {
      $permit_template_id = $request->getParameter('id');
      $this->template = Doctrine_Query::create()
          ->from('Permits')
          ->where('id = ?', $permit_template_id)
          ->fetchOne();

      $prefix = "can-sign-permit-$permit_template_id";
      $this->prefix = $prefix;

      if ($request->getMethod() == 'POST') {
          $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
          # confirm if such a permission exists
          $allowed_groups = (array)$request->getPostParameter('allowed_groups[]');

          if (!($permission = Doctrine_Query::create()
              ->from('MfGuardPermission a')
              ->where('a.name = ? ', $prefix)
              ->fetchOne())) {
              $permission = new MfGuardPermission();
              $permission->setName($prefix);
              $permission->setDescription("Can Sign " . $this->template->getTitle());
              $permission->save();
          }

          # delete all group permissions for this permit
          $q = "DELETE FROM mf_guard_group_permission WHERE permission_id = " . $permission->getId();
          $conn->execute($q);

          foreach ($allowed_groups as $group_id) {
              $new_perm_group = new MfGuardGroupPermission();
              $new_perm_group->setGroupId($group_id);
              $new_perm_group->setPermissionId($permission->getId());
              $new_perm_group->save();
          }

          $this->redirect('/plan/permittemplates/index');
      }


      $this->groups = Doctrine_Query::create()
          ->from("MfGuardGroup a")
          ->orderBy("a.name ASC")
          ->execute();

  }
}
