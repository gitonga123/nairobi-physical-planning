<?php
/**
 * Services actions.
 *
 * Allows user to manage services
 *
 * @package    backend
 * @subpackage services
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class servicesActions extends sfActions
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
        if(!$this->getUser()->mfHasCredential('access_workflow'))
        {	
            $this->redirect('/backend.php/errors/notallowed');
        }

        $q = Doctrine_Query::create()
            ->from("WorkflowCategory c")
            ->orderBy("c.order_id ASC");
        $this->categories = $q->execute();
        
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
        $this->form = new MenusForm();

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

        $this->form = new MenusForm();

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
        $this->forward404Unless($workflow = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $this->form = new MenusForm($workflow);

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
        $this->forward404Unless($workflow = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

        $this->form = new MenusForm($workflow);

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
            $workflow = $form->save();

            //check if credential exists for this menu
            $q = Doctrine_Query::create()
               ->from('MfGuardPermission a')
                   ->where('a.name = ?', 'accessmenu'.$workflow->getId());
            $credential = $q->execute();
            if(sizeof($credential) == 0)
            {
                    $credential = new MfGuardPermission();
                    $credential->setName('accessmenu'.$workflow->getId());
                    $credential->setDescription("Access to ".$workflow->getTitle()." menu");
                    $credential->save();
            }

            $groups = $request->getPostParameter('allowed_groups');

            $q = Doctrine_Query::create()
                ->from("MfGuardPermission a")
                ->where("a.name = ?", "accessmenu".$workflow->getId());
            $permission = $q->fetchOne();
            if($permission)
            {
                //continue since permission Exists
            }
            else
            {
                $permission = new MfGuardPermission();
                $permission->setName('accessmenu'.$workflow->getId());
                $permission->setDescription("Access to ".$workflow->getTitle()." menu");
                $permission->save();
            }


            $q = Doctrine_Query::create()
                ->from("MfGuardPermission a")
                ->where("a.name = ?", "accessmenu".$workflow->getId());
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

            $this->redirect('/backend.php/services/index');
        }
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
        if(!$this->getUser()->mfHasCredential('managestages'))
        {
            $this->redirect('/backend.php/errors/notallowed');
        }

        $q = Doctrine_Query::create()
            ->from("Menus a")
            ->where("a.id = ?", $request->getParameter("id"));
        $service = $q->fetchOne();

        if($service)
        {
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.menu_id = ?", $request->getParameter("id"));
            $stages = $q->execute();

            foreach($stages as $stage)
            {
                $stage->delete();
            }

            $service->delete();
        }

        $this->redirect('/backend.php/services/index');
    }

    /**
    * Executes 'Duplicate' action
    *
    * Duplicates an entire workflow starting with the menu/stage
    *
    * @param sfRequest $request A request object
    */
    public function executeDuplicate(sfWebRequest $request)
    {
        $stage_map = array();

        $this->forward404Unless($menu = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object menus does not exist (%s).', $request->getParameter('id')));

        //Menus
        $newmenu = new Menus();
        $newmenu->setTitle($menu->getTitle()." Copy ".date("Y-m-d h:m:s"));
        $newmenu->setOrderNo($menu->getOrderNo()+1);
        $newmenu->save();

        $q = Doctrine_Query::create()
            ->from("MfGuardPermission a")
            ->where("a.name LIKE ?","%accessmenu".$menu->getId()."%");
        $existing_permission = $q->execute();

        //Assign same groups to this workflow
        //*******
        $existing_groups = array();
        foreach($existing_permission as $permission)
        {
            $q = Doctrine_Query::create()
                ->from("MfGuardGroupPermission a")
                ->where("a.permission_id = ?", $permission->getId());
            $groups = $q->execute();

            foreach($groups as $group)
            {
                $existing_groups[$group->getGroupId()] = $group->getGroupId();
            }
        }

        if(sizeof($existing_groups) == 0)
        {
            echo "No Groups Assigned to Workflow Found";
            exit;
        }

        if(sizeof($existing_permission) > 0)
        {
            //Mf_guard_permission (create menu permission and assign to new group)
            $credential = new MfGuardPermission();
            $credential->setName('accessmenu'.$newmenu->getId());
            $credential->setDescription("Access to ".$newmenu->getTitle()." menu");
            $credential->save();

            if($credential)
            {
                foreach($existing_groups as $existing_group)
                {
                    $group_permission = new MfGuardGroupPermission();
                    $group_permission->setGroupId($existing_group);
                    $group_permission->setPermissionId($credential->getId());
                    $group_permission->save();
                }
            }
        }

        //Sub_menus
        $q = Doctrine_Query::create()
            ->from("SubMenus a")
            ->where("a.menu_id = ?", $menu->getId())
            ->andWhere("a.deleted = 0");
        $submenus = $q->execute();
        foreach($submenus as $submenu)
        {
            //Menus
            $newsubmenu = new SubMenus();
            $newsubmenu->setTitle($submenu->getTitle());
            $newsubmenu->setOrderNo($submenu->getOrderNo()+1);
            $newsubmenu->setMenuId($newmenu->getId());

            $newsubmenu->setStageType($submenu->getStageType());

            $newsubmenu->setStageProperty($submenu->getStageProperty());
            $newsubmenu->setStageTypeMovement($submenu->getStageTypeMovement());
            $newsubmenu->setStageTypeMovementFail($submenu->getStageTypeMovementFail());
            $newsubmenu->setStageTypeNotification($submenu->getStageTypeNotification());

            $newsubmenu->setStageExpiredMovement($submenu->getStageExpiredMovement());
            $newsubmenu->setMaxDuration($submenu->getMaxDuration());
            $newsubmenu->setChangeIdentifier($submenu->getChangeIdentifier());
            $newsubmenu->setNewIdentifier($submenu->getNewIdentifier());
            $newsubmenu->setNewIdentifierStart($submenu->getNewIdentifierStart());

            $newsubmenu->save();

            //Set allowed tasks
            $q = Doctrine_Query::create()
                ->from("SubMenuTasks a")
                ->where("a.sub_menu_id = ?", $submenu->getId());
            $allowed_tasks = $q->execute();

            foreach($allowed_tasks as $allowed_task)
            {
                $submenutask = new SubMenuTasks();
                $submenutask->setSubMenuId($allowed_task->getSubMenuId());
                $submenutask->setTaskId($allowed_task->getTaskId());
                $submenutask->save();
            }

            //Add new stage to map
            $stage_map[$submenu->getId()] = $newsubmenu->getId();

            //Assign existing group for this stage to the new permission
            $existing_group = "";

            $q = Doctrine_Query::create()
                ->from("MfGuardPermission a")
                ->where("a.name LIKE ?","%accesssubmenu".$submenu->getId()."%");
            $existing_permission = $q->execute();

            //Assign same groups to this workflow
            //*******
            $existing_groups = array();
            foreach($existing_permission as $permission)
            {
                $q = Doctrine_Query::create()
                    ->from("MfGuardGroupPermission a")
                    ->where("a.permission_id = ?", $permission->getId());
                $groups = $q->execute();

                foreach($groups as $group)
                {
                    $existing_groups[$group->getGroupId()] = $group->getGroupId();
                }
            }

            if(sizeof($existing_permission) > 0)
            {
                //Mf_guard_permission (create submenu permission and assign to new group)
                $credential = new MfGuardPermission();
                $credential->setName('accesssubmenu'.$newsubmenu->getId());
                $credential->setDescription("Access to ".$newsubmenu->getTitle()." submenu");
                $credential->save();

                foreach($existing_groups as $existing_group)
                {
                    $group_permission = new MfGuardGroupPermission();
                    $group_permission->setGroupId($existing_group);
                    $group_permission->setPermissionId($credential->getId());
                    $group_permission->save();
                }
            }
            //Sub_menus

            //Buttons
            $q = Doctrine_Query::create()
                ->from("SubMenuButtons a")
                ->where("a.sub_menu_id = ?", $submenu->getId());
            $submenubuttons = $q->execute();
            foreach($submenubuttons as $submenubutton)
            {
                $q = Doctrine_Query::create()
                    ->from("Buttons a")
                    ->where("a.id = ?", $submenubutton->getButtonId());
                $button = $q->fetchOne();

                if(empty($button))
                {
                    continue;
                }

                //Assign existing group for this action to the permission
                $existing_group = "";

                if(!empty($button)){
                    $newbutton = new Buttons();
                    $newbutton->setTitle($button->getTitle());
                    $link = $button->getLink();

                    $newbutton->setLink($link);

                    $newbutton->save();
                }

                if($newbutton)
                {
                    $q = Doctrine_Query::create()
                        ->from("MfGuardPermission a")
                        ->where("a.name LIKE ?","%accessbutton".$button->getId()."%");
                        $existing_permission = $q->execute();

                        //Assign same groups to this workflow
                        //*******
                        $existing_groups = array();
                        foreach($existing_permission as $permission)
                        {
                        $q = Doctrine_Query::create()
                            ->from("MfGuardGroupPermission a")
                            ->where("a.permission_id = ?", $permission->getId());
                        $groups = $q->execute();

                        foreach($groups as $group)
                        {
                            $existing_groups[$group->getGroupId()] = $group->getGroupId();
                        }
                        }

                        if(sizeof($existing_permission) > 0)
                        {
                            //Mf_guard_permission (create button permission and assign to new group)
                            $credential = new MfGuardPermission();
                            $credential->setName('accessbutton'.$newbutton->getId());
                            $credential->setDescription("Access to ".$newbutton->getTitle()." button");
                            $credential->save();

                            foreach($existing_groups as $existing_group)
                            {
                                $group_permission = new MfGuardGroupPermission();
                                $group_permission->setGroupId($existing_group);
                                $group_permission->setPermissionId($credential->getId());
                                $group_permission->save();
                            }
                        }

                    //Sub_menu_buttons
                    $newsubmenubutton = new SubMenuButtons();
                    $newsubmenubutton->setSubMenuId($newsubmenu->getId());
                    $newsubmenubutton->setButtonId($newbutton->getId());
                    $newsubmenubutton->save();
                }
            }
        }

        //Iterate through all sub_menus and replace old ids with
        foreach($stage_map as $key => $value)
        {
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $value);
            $submenu = $q->fetchOne();

            //Buttons
            $q = Doctrine_Query::create()
                ->from("SubMenuButtons a")
                ->where("a.sub_menu_id = ?", $submenu->getId());
            $submenubuttons = $q->execute();
            foreach($submenubuttons as $submenubutton)
            {
                $q = Doctrine_Query::create()
                    ->from("Buttons a")
                    ->where("a.id = ?", $submenubutton->getButtonId());
                $button = $q->fetchOne();

                if(!empty($button)){

                    $link = $button->getLink();

                    foreach($stage_map as $key_inner => $value_inner)
                    {
                    $find_me = "moveto=".$key_inner;
                    $replace_with = "moveto=".$value_inner;

                    $link = str_replace($find_me, $replace_with, $link);

                    error_log("Find: $find_me/, Replace with: $replace_with/, Find in: $link");
                    }

                    $button->setLink($link);

                    $button->save();
                }
            }
        }


        $this->redirect("/backend.php/services/index");
    }

    /**
    * Executes 'fees' function
    *
    * Fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeFees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $this->service = $service;

        $this->element_id = $request->getParameter("element_id", $this->service->getServiceFeeField());

        $this->setLayout("layout-settings");
    }

    /**
    * Executes 'savefees' function
    *
    * Save fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeSavefees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $element_id = $request->getPostParameter("dropdown_field");

        $service->setServiceFeeField($element_id);
        $service->save();

        $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $service->getServiceForm())
            ->andWhere("a.element_id = ?", $element_id)
            ->orderBy("a.option_text ASC");
        $options = $q->execute();

        $post_options = $request->getPostParameter("options");

        foreach($options as $option)
        {
            $option_id = $option->getAeoId();
            $amount = $post_options[$option_id];

            //Update or Save Option Fee
            $q = Doctrine_Query::create()
               ->from("ServiceFees a")
               ->where("a.service_id = ?", $service->getId())
               ->andWhere("a.field_id = ?", $element_id)
               ->andWhere("a.option_id = ?", $option_id);
            $option_fee = $q->fetchOne();

            if($option_fee)
            {
                $option_fee->setTotalAmount($amount);
                $option_fee->save();
            }
            else 
            {
                $option_fee = new ServiceFees();
                $option_fee->setServiceId($service->getId());
                $option_fee->setFieldId($element_id);
                $option_fee->setOptionId($option_id);
                $option_fee->setTotalAmount($amount);
                $option_fee->save();
            }
        }

        $this->redirect("/backend.php/services/index");
    }

    /**
    * Executes 'otherfees' function
    *
    * Other fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeOtherfees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $this->service = $service;

        $q = Doctrine_Query::create()
           ->from("ServiceOtherFees a")
           ->where("a.service_id = ?", $service->getId());
        $this->fees = $q->execute();

        $this->setLayout("layout-settings");
    }

    /**
    * Executes 'saveotherfees' function
    *
    * Save other fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeSaveotherfees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
    
        //Delete existing fees for this service
        $q = Doctrine_Query::create()
           ->from("ServiceOtherFees a")
           ->where("a.service_id = ?", $service->getId());
        $fees = $q->execute();

        foreach($fees as $fee)
        {
            $fee->delete();
        }

        $other_fees_codes = $request->getPostParameter("other_fees_code");
        $other_fees_amounts = $request->getPostParameter("other_fees_amount");

        $count = 0;
        foreach($other_fees_codes as $code)
        {
            if($code)
            {
                $new_fee = new ServiceOtherFees();
                $new_fee->setServiceId($service->getId());
                $new_fee->setServiceCode($code);
                $new_fee->setAmount($other_fees_amounts[$count]);

                if($request->getPostParameter("other_fees_first_time_".$count))
                {
                    $new_fee->setAsFirstSubmissionFee(1);
                }
                else 
                {
                    $new_fee->setAsFirstSubmissionFee(0);
                }

                if($request->getPostParameter("other_fees_renewal_".$count))
                {
                    $new_fee->setAsRenewalFee(1);
                }
                else 
                {
                    $new_fee->setAsRenewalFee(0);
                }

                $new_fee->save();
            }
            $count++;
        }

        $this->redirect("/backend.php/services/otherfees/id/".$service->getId());
    }

    /**
    * Executes 'morefees' function
    *
    * More fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeMorefees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $this->service = $service;

        $q = Doctrine_Query::create()
           ->from("MoreFees a")
           ->where("a.service_id = ?", $service->getId());
        $this->fees = $q->execute();

        $this->setLayout("layout-settings");
    }

    /**
    * Executes 'Newmorefees' function
    *
    * Fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeNewmorefees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('serviceid'))), sprintf('Object content does not exist (%s).', $request->getParameter('serviceid')));
        
        $this->service = $service;

        $this->element_id = $request->getParameter("element_id", 0);

        $this->setLayout("layout-settings");
    }

    /**
    * Executes 'savemorefees' function
    *
    * Save fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeSavenewmorefees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $fee_title = $request->getPostParameter("fee_title");
        $element_id = $request->getPostParameter("dropdown_field");

        $more_fee = new MoreFees();
        $more_fee->setFeeTitle($fee_title);
        $more_fee->setServiceId($service->getId());
        $more_fee->setFieldId($element_id);
        $more_fee->save();

        $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $service->getServiceForm())
            ->andWhere("a.element_id = ?", $element_id)
            ->orderBy("a.option_text ASC");
        $options = $q->execute();

        $post_options = $request->getPostParameter("options");

        foreach($options as $option)
        {
            $option_id = $option->getAeoId();
            $amount = $post_options[$option_id];

            //Update or Save Option Fee
            $q = Doctrine_Query::create()
               ->from("ServiceMoreFees a")
               ->where("a.service_id = ?", $service->getId())
               ->andWhere("a.field_id = ?", $element_id)
               ->andWhere("a.option_id = ?", $option_id);
            $option_fee = $q->fetchOne();

            if($option_fee)
            {
                $option_fee->setTotalAmount($amount);
                $option_fee->save();
            }
            else 
            {
                $option_fee = new ServiceMoreFees();
                $option_fee->setServiceId($service->getId());
                $option_fee->setFeeId($more_fee->getId());
                $option_fee->setFieldId($element_id);
                $option_fee->setOptionId($option_id);
                $option_fee->setTotalAmount($amount);
                $option_fee->save();
            }
        }

        $this->redirect("/backend.php/services/morefees/id/".$service->getId());
    }

    /**
    * Executes 'Editmorefees' function
    *
    * Fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeEditmorefees(sfWebRequest $request)
    {
        $this->forward404Unless($this->service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('serviceid'))), sprintf('Object content does not exist (%s).', $request->getParameter('serviceid')));
        $this->forward404Unless($this->fee = Doctrine_Core::getTable('MoreFees')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

        $this->setLayout("layout-settings");
    }

    /**
    * Executes 'saveeditmorefees' function
    *
    * Save fees for an existing service
    *
    * @param sfRequest $request A request object
    */
    public function executeSaveeditmorefees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('serviceid'))), sprintf('Object content does not exist (%s).', $request->getParameter('serviceid')));
        $this->forward404Unless($more_fee = Doctrine_Core::getTable('MoreFees')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $fee_title = $request->getPostParameter("fee_title");
        $element_id = $request->getPostParameter("dropdown_field");

        $more_fee->setFeeTitle($fee_title);
        $more_fee->setServiceId($service->getId());
        $more_fee->setFieldId($element_id);
        $more_fee->save();

        $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $service->getServiceForm())
            ->andWhere("a.element_id = ?", $element_id)
            ->orderBy("a.option_text ASC");
        $options = $q->execute();

        $post_options = $request->getPostParameter("options");

        foreach($options as $option)
        {
            $option_id = $option->getAeoId();
            $amount = $post_options[$option_id];

            //Update or Save Option Fee
            $q = Doctrine_Query::create()
               ->from("ServiceMoreFees a")
               ->where("a.service_id = ?", $service->getId())
               ->andWhere("a.fee_id = ?", $more_fee->getId())
               ->andWhere("a.field_id = ?", $element_id)
               ->andWhere("a.option_id = ?", $option_id);
            $option_fee = $q->fetchOne();

            if($option_fee)
            {
                $option_fee->setTotalAmount($amount);
                $option_fee->save();
            }
            else 
            {
                $option_fee = new ServiceMoreFees();
                $option_fee->setServiceId($service->getId());
                $option_fee->setFieldId($element_id);
                $option_fee->setFeeId($more_fee->getId());
                $option_fee->setOptionId($option_id);
                $option_fee->setTotalAmount($amount);
                $option_fee->save();
            }
        }

        $this->redirect("/backend.php/services/morefees/id/".$service->getId());
    }

    /**
     * Executes 'Delete' action
     *
     * Delete a service
     *
     * @param sfRequest $request A request object
     */
    public function executeDeletemorefees(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('serviceid'))), sprintf('Object content does not exist (%s).', $request->getParameter('serviceid')));
        $this->forward404Unless($more_fee = Doctrine_Core::getTable('MoreFees')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

        $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $service->getServiceForm())
            ->andWhere("a.element_id = ?", $more_fee->getFieldId())
            ->orderBy("a.option_text ASC");
        $options = $q->execute();

        foreach($options as $option)
        {
            $option_id = $option->getAeoId();
            $amount = $post_options[$option_id];

            //Update or Save Option Fee
            $q = Doctrine_Query::create()
               ->from("ServiceMoreFees a")
               ->where("a.service_id = ?", $service->getId())
               ->andWhere("a.fee_id = ?", $more_fee->getId())
               ->andWhere("a.field_id = ?", $more_fee->getFieldId());
            $option_fees = $q->execute();

            foreach($option_fees as $option_fee)
            {
                $option_fee->delete();
            }
            
        }

        $more_fee->delete();

        $this->redirect("/backend.php/services/morefees/id/".$service->getId());
    }

    /**
    * Executes 'multiplier' function
    *
    * Multiply invoices
    *
    * @param sfRequest $request A request object
    */
    public function executeMultiplier(sfWebRequest $request)
    {
        $this->forward404Unless($service = Doctrine_Core::getTable('Menus')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
        
        $this->service = $service;

        if($request->getPostParameter("fee_field"))
        {
            $multiplier_fee = new MultiplierFees();
            $multiplier_fee->setServiceId($this->service->getId());
            $multiplier_fee->setFieldId($request->getPostParameter("fee_field"));
            $multiplier_fee->setMultiplierAmount($request->getPostParameter("fee_amount"));
            $multiplier_fee->save();
        }   

        if($request->getParameter("delete"))
        {
            $q = Doctrine_Query::create()
                ->from("MultiplierFees a")
                ->where("a.id = ?", $request->getParameter("delete"));
            $delete_fee = $q->fetchOne();

            if($delete_fee)
            {
                $delete_fee->delete();
            }
        }

        $q = Doctrine_Query::create()
           ->from("MultiplierFees a")
           ->where("a.service_id = ?", $this->service->getId());
        $this->fees = $q->execute();
    }
}