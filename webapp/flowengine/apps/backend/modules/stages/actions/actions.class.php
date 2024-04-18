<?php

/**
 * submenus actions.
 *
 * @package    backend
 * @subpackage stages
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class stagesActions extends sfActions
{
  /**
   * Executes 'Index' action 
   * 
   * Displays list of services
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
      //Audit 
      Audit::audit("", "Accessed stage settings");

      if($request->getParameter("move"))
      {
        $q = Doctrine_Query::create()
           ->from("SubMenus a")
           ->where("a.id = ?", $request->getParameter("move"));
        $stage = $q->fetchOne();

        if($stage)
        {
          $stage->setOrderNo($request->getParameter("to"));
          $stage->save();

          $q = Doctrine_Query::create()
             ->from("SubMenus a")
             ->where("a.menu_id = ?", $stage->getMenuId())
             ->andWhere("a.id <> ?", $stage->getId())
             ->andWhere("a.order_no >= ?", $request->getParameter("to"));
          $other_stages = $q->execute();

          foreach($other_stages as $other_stage)
          {
            $order = $other_stage->getOrderNo();
            $order++;

            $other_stage->setOrderNo($order);
            $other_stage->save();
          }
        }
      }

      if($request->getParameter("filter") == "")
      {
		      $this->filter = $this->getUser()->getAttribute('menufilter');
      }
      else
      {
    		  $this->filter = $request->getParameter("filter");

    		  $this->getUser()->setAttribute('menufilter', $this->filter);
  	  }

      $this->stages = Doctrine_Core::getTable('SubMenus')
        ->createQuery('a')
    	  ->where('a.menu_id = ?', $this->filter)
    	  ->andWhere('a.deleted = ?','0')
    	  ->orderBy('a.order_no ASC')
        ->execute();

      $q = Doctrine_Query::create()
         ->from("Menus a")
         ->where("a.id = ?", $this->filter);
      $this->service = $q->fetchOne();

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
    $this->form = new SubMenusForm();

	  $this->filter = $request->getParameter("filter");

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
    //Audit 
    Audit::audit("", "Created new stage");

    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new SubMenusForm();

    $this->new = true;

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
    $this->forward404Unless($sub_menus = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Object sub_menus does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new SubMenusForm($sub_menus);

	  $this->filter = $request->getParameter("filter");

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
    //Audit 
    Audit::audit("", "Updated existing stage");

    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($sub_menus = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Object sub_menus does not exist (%s).', $request->getParameter('id')));
    $this->form = new SubMenusForm($sub_menus);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  /**
  * Executes 'Delete' action
  *
  * Delete a service
  *
  * @param sfRequest $request A request object
  */
  public function executeDelete(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Deleted stage");

    $this->forward404Unless($sub_menus = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Object sub_menus does not exist (%s).', $request->getParameter('id')));

    $existing_applications = Doctrine_Core::getTable('FormEntry')
      ->createQuery('a')
      ->where('a.approved = ?', $sub_menus->getId())
      ->execute();

    if(sizeof($existing_applications) <= 0)
    {
      $sub_menus->setDeleted("1");
      $sub_menus->save();
    }

    $this->redirect('/backend.php/stages/index');
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
      $sub_menus = $form->save();
      if($this->new)
      {
      	$sub_menus->setOrderNo($sub_menus->getId());
      	$sub_menus->save();
  	  }

  	  if($request->getPostParameter("send_notification"))
  	  {
  	  	$q = Doctrine_Query::create()
    		    ->from("Notifications a")
    		    ->where("a.submenu_id = ?", $sub_menus->getId());
    		$notification = $q->fetchOne();
    		if($notification) //Edit existing notification
    		{
    			$notification->setTitle($request->getPostParameter("mail_subject"));
    			$notification->setContent($request->getPostParameter("mail_content"));
    			$notification->setSms($request->getPostParameter("sms_content"));
    			$notification->setAutosend($request->getPostParameter("send_options"));
    			$notification->setSubmenuId($sub_menus->getId());
    			$notification->save();
    		}
    		else //Add new notification
    		{
    			$notification = new Notifications();
    			$notification->setTitle($request->getPostParameter("mail_subject"));
    			$notification->setContent($request->getPostParameter("mail_content"));
    			$notification->setSms($request->getPostParameter("sms_content"));
    			$notification->setAutosend($request->getPostParameter("send_options"));
    			$notification->setSubmenuId($sub_menus->getId());
    			$notification->save();
    		}
  	  }
  	  else
  	  {
  	  	$q = Doctrine_Query::create()
  		    ->from("Notifications a")
  		    ->where("a.submenu_id = ?", $sub_menus->getId());
    		$notification = $q->fetchOne();
    		if($notification)
    		{
    			$notification->delete();
    		}
  	  }

      if($request->getPostParameter("sub_menus[stage_type]"))
      {
          if($request->getPostParameter("sub_menus[stage_type]") == 2)
          {
              $sub_menus->setStageProperty($request->getPostParameter("assessment_properties"));
              $sub_menus->setStageTypeMovement($request->getPostParameter("assessment_next_stage"));
              $sub_menus->setStageTypeNotification($request->getPostParameter("assessment_notification"));
          }
          elseif($request->getPostParameter("sub_menus[stage_type]") == 8)
          {
              $sub_menus->setStageProperty($request->getPostParameter("dispatch_properties"));
              $sub_menus->setStageTypeMovement($request->getPostParameter("dispatch_next_stage"));
              $sub_menus->setStageTypeNotification($request->getPostParameter("dispatch_notification"));
          }
          elseif($request->getPostParameter("sub_menus[stage_type]") == 3)
          {
              $sub_menus->setStageProperty($request->getPostParameter("invoicing_properties"));
              $sub_menus->setStageTypeMovement($request->getPostParameter("invoicing_next_stage_pass"));
              $sub_menus->setStageTypeMovementFail($request->getPostParameter("invoicing_next_stage_fail"));
              $sub_menus->setStageTypeNotification($request->getPostParameter("invoicing_notification"));
          }
          elseif($request->getPostParameter("sub_menus[stage_type]") == 10)
          {
              $sub_menus->setStageProperty($request->getPostParameter("renewals_properties"));
              $sub_menus->setStageTypeMovement($request->getPostParameter("renewals_next_stage_pass"));
              $sub_menus->setStageTypeMovementFail($request->getPostParameter("renewals_next_stage_fail"));
              $sub_menus->setStageTypeNotification($request->getPostParameter("renewals_notification"));
          }
          elseif($request->getPostParameter("sub_menus[stage_type]") == 5)
          {
              $sub_menus->setStageProperty($request->getPostParameter("correction_properties"));
              $sub_menus->setStageTypeMovement($request->getPostParameter("correction_next_stage"));
              $sub_menus->setStageTypeNotification($request->getPostParameter("correction_notification"));
          }
          elseif($request->getPostParameter("sub_menus[stage_type]") == 12)
          {
              $sub_menus->setStageProperty($request->getPostParameter("expired_properties"));
              $sub_menus->setStageTypeMovement($request->getPostParameter("expired_next_stage"));
              $sub_menus->setStageTypeNotification($request->getPostParameter("expired_properties_notification"));
          }
          $sub_menus->save();
      }

      //$translation = new translation();
      //$translation->setTranslation("submenus","title",$sub_menus->getId(),$sub_menus->getTitle());

      //$sub_menus->setStageExpiredMovement($request->getPostParameter("stage_expired_movement"));

      /*if($request->getPostParameter("max_duration") != "")
      {
  	    $sub_menus->setMaxDuration($request->getPostParameter("max_duration"));
      }
      else 
      {
        $sub_menus->setMaxDuration(0);
      }*/

      //$sub_menus->setAllowEdit($request->getPostParameter("allow_edit"));
  	  //$sub_menus->setChangeIdentifier($request->getPostParameter("change_application_number"));
  	  //$sub_menus->setNewIdentifier($request->getPostParameter("new_identifier"));
  	  //$sub_menus->setNewIdentifierStart($request->getPostParameter("new_identifier_start"));
  	  //$sub_menus->setMenuId($this->getUser()->getAttribute('menufilter'));
  	  //$sub_menus->save();

      //Assign default assessment reviewers
      $q = Doctrine_Query::create()
        ->from("WorkflowReviewers a")
        ->where("a.workflow_id = ?", $sub_menus->getId());
      $workflow_reviewers = $q->execute();

      foreach ($workflow_reviewers as $reviewer) {
          $reviewer->delete();
      }

      $allowed_reviewers = $request->getPostParameter('allowed_reviewers');
      foreach($allowed_reviewers as $allowed_reviewer)
      {
          $workflow_reviewer = new WorkflowReviewers();
          $workflow_reviewer->setWorkflowId($sub_menus->getId());
          $workflow_reviewer->setReviewerId($allowed_reviewer);
          $workflow_reviewer->save();
      }

  	  //Check settings for changing application identifier
  	  /*$q = Doctrine_Query::create()
  	   ->from('ApForms a')
  	   ->where('a.form_id <> 6 AND a.form_id <> 7 AND a.form_id <> 15 AND a.form_id <> 16 AND a.form_id <> 17 AND a.form_id <> 18 AND a.form_id <> 19');
  	  $forms = $q->execute();
  	  foreach($forms as $form)
  	  {
  			$q = Doctrine_Query::create()
  			   ->from('AppChange a')
  			   ->where('a.form_id = ? AND a.stage_id = ?', array($form->getFormId(), $sub_menus->getId()));
  			$appchanges = $q->execute();
  			foreach($appchanges as $appchange)
  			{
  				$appchange->delete();
  			}
  	  }

  	  if($request->getPostParameter("change_identifier") == "1")
  	  {
  		  foreach($forms as $form)
  		  {
  				if($request->getPostParameter("application_identifier_".$form->getFormId()) != "")
  				{
  					$appchange = new AppChange();
  					$appchange->setStageId($sub_menus->getId());
  					$appchange->setFormId($form->getFormId());
  					$appchange->setIdentifierType($request->getPostParameter("identifier_type_".$form->getFormId()));
  					$appchange->setAppIdentifier($request->getPostParameter("application_identifier_".$form->getFormId()));
  					$appchange->setIdentifierStart($request->getPostParameter("starting_point_".$form->getFormId()));
  					$appchange->save();
  				}
  		  }
  	  }*/

      $this->redirect('/backend.php/stages/index');
    }
  }


  /**
   * Push order up.
   *
   * @return Response
   */
  public function executeOrderup(sfWebRequest $request)
  {
    $this->forward404Unless($menu = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Object menus does not exist (%s).', $request->getParameter('id')));

    $q = Doctrine_Query::create()
         ->from("SubMenus a")
         ->where("a.order_no < ?", $menu->getOrderNo())
         ->andWhere("a.deleted = 0")
         ->andWhere("a.menu_id = ?", $menu->getMenuId())
         ->orderBy('a.order_no DESC');
    $previous_menu = $q->fetchOne();

    if($previous_menu)
    {
      $current_order = $menu->getOrderNo();
      $previous_order = $previous_menu->getOrderNo();

      $previous_menu->setOrderNo(-1); //temporary set page order to prevent conflict
      $previous_menu->save();

      $menu->setOrderNo($previous_order);
      $menu->save();

      $previous_menu->setOrderNo($current_order);
      $previous_menu->save();
    }

    return $this->redirect("/backend.php/stages/index");
  }

  /**
   * Push order up.
   *
   * @return Response
   */
  public function executeOrderdown(sfWebRequest $request)
  {
    $menu = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter("id")));

    $q = Doctrine_Query::create()
         ->from("SubMenus a")
         ->where("a.order_no > ?", $menu->getOrderNo())
         ->andWhere("a.deleted = 0")
         ->andWhere("a.menu_id = ?", $menu->getMenuId())
         ->orderBy('a.order_no ASC');
    $previous_menu = $q->fetchOne();

    if($previous_menu)
    {
      $current_order = $menu->getOrderNo();
      $previous_order = $previous_menu->getOrderNo();

      $previous_menu->setOrderNo(-1); //temporary set page order to prevent conflict
      $previous_menu->save();

      $menu->setOrderNo($previous_order);
      $menu->save();

      $previous_menu->setOrderNo($current_order);
      $previous_menu->save();
    }

    return $this->redirect("/backend.php/stages/index");
  }

  public function executeActions(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed action settings");

    $this->forward404Unless($this->stage = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Stage does not exist (%s).', $request->getParameter('id')));

    if($request->getPostParameter("action"))
    {
      //Assign actions
  	  $actionnames = $request->getPostParameter("name");
  	  $actiontypes = $request->getPostParameter("action");
  	  $actionstages = $request->getPostParameter("stage");
  	  $actiongroups = $request->getPostParameter("group");

  	  $count = 0;
  	  foreach($actionnames as $actionname)
  	  {
  	  	 $button = new Buttons();
         $button->setTitle($actionname);
         $button->setTooltip($actionname);
  	  	 $button->setLink($actiontypes[$count].'moveto='.$actionstages[$count]);
  	  	 $button->save();

  	  	 //Assign action to stage
  		  $stageaction = new SubMenuButtons();
  		  $stageaction->setSubMenuId($this->stage->getId());
  		  $stageaction->setButtonId($button->getId());
  		  $stageaction->save();

  		  $credential = null;

  		   $q = Doctrine_Query::create()
  			 ->from('MfGuardPermission a')
  			 ->where('a.name = ?', 'accessbutton'.$button->getId());
  		  $similarcredential = $q->execute();
  		  if(sizeof($similarcredential) == 0)
  		  {
  			  $credential = new MfGuardPermission();
  			  $credential->setName('accessbutton'.$button->getId());
  			  $credential->setDescription("Access to ".$button->getTitle()." button");
  			  $credential->save();
  		  }
  		  else
  		  {
  			  $credential = $q->fetchOne();
  		  }

  		  //Assign credentials to groups
  		  $groupcredential = new MfGuardGroupPermission();
  		  $groupcredential->setGroupId($actiongroups[$count]);
  		  $groupcredential->setPermissionId($credential->getId());
  		  $groupcredential->save();

  	  	 $count++;
  	  }

      $this->redirect("/backend.php/stages/index/filter/".$this->stage->getMenuId());
    }

	  $this->setLayout("layout-settings");
  }

  public function executeGroups(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed group settings");

    $this->forward404Unless($this->stage = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Stage does not exist (%s).', $request->getParameter('id')));

    if($request->getPostParameter("allowed_groups"))
    {
      $groups = $request->getPostParameter('allowed_groups');

      $q = Doctrine_Query::create()
  	   ->from("MfGuardPermission a")
  	   ->where("a.name = ?", "accesssubmenu".$this->stage->getId());
  	  $permission = $q->fetchOne();

      if($permission)
  	  {
  	  	$grouppermissions = $permission->getMfGuardGroupPermission();
  	  	foreach($grouppermissions as $grouppermission)
  	  	{
    			$grouppermission->delete();
    		}
  	  }
      else
      {
          $permission = new MfGuardPermission();
          $permission->setName("accesssubmenu".$this->stage->getId());
          $permission->save();
      }

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
                    if($permission) {
                        if ($permission->getId() == $grouppermission->getId()) {
                            //permission already exists
                        } else {
                            $found = true;
                        }
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

      if($request->getPostParameter("name"))
      {
        //Assign actions
    	  $groupnames = $request->getPostParameter("name");

    	  foreach($groupnames as $groupname)
    	  {
          $group = new MfGuardGroup();
          $group->setName($groupname);
          $group->save();

          //Assign the new group to the stage
          if($permission && $group)
          {
            $permissiongroup = new MfGuardGroupPermission();
            $permissiongroup->setGroupId($group->getId());
            $permissiongroup->setPermissionId($permission->getId());
            $permissiongroup->save();
          }

          //Assign the new group to the stage actions
          $q = Doctrine_Query::create()
             ->from("SubMenuButtons a")
             ->where("a.sub_menu_id = ?", $this->stage->getId());
          $actions = $q->execute();

          foreach($actions as $action)
          {
            $q = Doctrine_Query::create()
               ->from("MfGuardPermission a")
               ->where("a.name = ?", "accessbutton".$action->getButtonId());
            $action_permission = $q->fetchOne();

            if($action_permission)
            {
              $permissiongroup = new MfGuardGroupPermission();
              $permissiongroup->setGroupId($group->getId());
              $permissiongroup->setPermissionId($action_permission->getId());
              $permissiongroup->save();
            }
          }
        }
      }

      $this->redirect("/backend.php/stages/index/filter/".$this->stage->getMenuId());
    }

	  $this->setLayout("layout-settings");
  }

  public function executeTasks(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed task settings");

    $this->forward404Unless($this->stage = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Stage does not exist (%s).', $request->getParameter('id')));

    if($request->getPostParameter("allowed_task"))
    {
      $q = Doctrine_Query::create()
         ->from("SubMenuTasks a")
         ->where("a.sub_menu_id = ?", $this->stage->getId());
      $previous_tasks = $q->execute();
      foreach($previous_tasks as $previous_task)
      {
        $previous_task->delete();
      }

      $task = $request->getPostParameter('allowed_task');

      $submenutask = new SubMenuTasks();
      $submenutask->setSubMenuId($this->stage->getId());
      $submenutask->setTaskId($task);
      $submenutask->save();

      $this->redirect("/backend.php/stages/index/filter/".$this->stage->getMenuId());
    }

	  $this->setLayout("layout-settings");
  }

  public function executeInspections(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed inspection settings");

    $this->forward404Unless($this->stage = Doctrine_Core::getTable('SubMenus')->find(array($request->getParameter('id'))), sprintf('Stage does not exist (%s).', $request->getParameter('id')));

    if($request->getPostParameter("allowed_departments"))
    {
      $service = $this->stage->getMenus();
      
      //Clear existing inspections
      $q = Doctrine_Query::create()
        ->from("ServiceInspections a")
        ->andWhere("a.stage_id = ?", $this->stage->getId());
      $service_inspections = $q->execute();

      foreach($service_inspections as $service_inspection)
      {
        $service_inspection->delete();
      }

      //Add new inspections
      foreach($request->getPostParameter("allowed_departments") as $department)
      {
        $service_inspection = new ServiceInspections();
        $service_inspection->setServiceId($service->getId());
        $service_inspection->setDepartmentId($department);
        $service_inspection->setStageId($this->stage->getId());
        $service_inspection->save();
      }
    }

    $this->setLayout("layout-settings");
  }

}
