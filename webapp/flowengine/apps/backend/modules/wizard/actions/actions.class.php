<?php

/**
 * workflow actions.
 *
 * @package    permit
 * @subpackage workflow
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class wizardActions extends sfActions
{
  public function executeSecurity(sfWebRequest $request)
  {
    $wizard_manager = new WizardManager();

    if($request->getParameter("skip"))
    {
      $this->step = $request->getParameter("skip") + 1;
    }

	  if($request->getPostParameter("step") == 1)
	  {
		  //Add Groups
		  $groupnames = $request->getPostParameter("name");
		  $groupdescriptions = $request->getPostParameter("description");

		  $count = 0;
		  foreach($groupnames as $groupname)
		  {
		  	if(strlen($groupname) > 0)
		  	{
		  		try
			    {
			      $q = Doctrine_Query::create()
					 ->from("MfGuardGroup a")
					 ->where("a.name = ?", $groupname);
				  $existinggroup = $q->fetchOne();
				  if($existinggroup)
				  {
				  	  //ignore so that there are no double entries
				  }
				  else
				  {
					  $newgroup = new MfGuardGroup();
					  $newgroup->setName($groupname);
					  $newgroup->setDescription($groupdescriptions[$count]);
					  $newgroup->save();
				  }
				  $count++;
				}catch(Exception $ex)
				{
				  	//Group Already Exists!
				}
			}
		  }

		  if($count > 0)
		  {
			  $this->step = 2;
		  }
		  else
		  {
		  	  $q = Doctrine_Query::create()
				 ->from("MfGuardGroup a")
				 ->orderBy("a.name ASC");
		  	  $groups = $q->execute();

		  	  if(sizeof($groups) > 0)
		  	  {
		  	  	 $this->step = 2;
		  	  }
		  	  else
		  	  {
			  	$this->step = 1;
			  }
		  }
	  }
	  elseif($request->getPostParameter("step") == 2)
	  {
		  //Assign roles to groups
		  $q = Doctrine_Query::create()
			 ->from("MfGuardGroup a")
			 ->orderBy("a.name ASC");
		  $groups = $q->execute();

		  $count = 0;
		  foreach($groups as $group)
		  {
		      if(sizeof($group->getUsers()) > 0)
		      {
		        continue;
		      }
			  $permissions = $request->getPostParameter("group".$group->getId());
			  foreach($permissions as $permission)
			  {
			  	$q = Doctrine_Query::create()
			  	   ->from("MfGuardGroupPermission a")
			  	   ->where("a.group_id = ? AND a.permission_id = ?", array($group->getId(), $permission));
			  	$existingpermission = $q->execute();
			  	if(sizeof($existingpermission) <= 0)
			  	{
				  $grouppermission = new MfGuardGroupPermission();
				  $grouppermission->setGroupId($group->getId());
				  $grouppermission->setPermissionId($permission);
				  $grouppermission->save();
				}
			  }
			  $count++;
		  }

		  if($count > 0)
		  {
			  $this->step = 3;
		  }
		  else
		  {
		  	  $q = Doctrine_Query::create()
				 ->from("MfGuardGroupPermission a")
				 ->orderBy("a.name ASC");
		  	  $grouppermissions = $q->execute();

		  	  if(sizeof($grouppermissions) > 0)
		  	  {
		  	  	 $this->step = 3;
		  	  }
		  	  else
		  	  {
			  	$this->step = 2;
			  }
		  }
	  }
	  elseif($request->getPostParameter("step") == 3)
	  {
		  //Assign admin settings
		  $q = Doctrine_Query::create()
		    ->from("CfUser a")
		    ->where("a.nid = ?", 1);
		  $adminuser = $q->fetchOne();

		  if($adminuser && sizeof($adminuser->getGroups()) <= 0)
		  {
		  	  $q = Doctrine_Query::create()
				->from("MfGuardGroup a")
				->orderBy("a.id ASC");
			  $existingadmingroup = $q->fetchOne();

			  try
			  {
			  	if(sizeof($existingadmingroup) > 0)
			  	{
				  $admingroup = new MfGuardUserGroup();
				  $admingroup->setUserId($adminuser->getNid());
				  $admingroup->setGroupId($existingadmingroup->getId());
				  $admingroup->save();
				}
			  }catch(Exception $ex)
			  {
			  	  //Admin Group Already Exists!
			  }
		  }


		  //Assign users to groups
		  $firstnames = $request->getPostParameter("firstname");
		  $lastnames = $request->getPostParameter("lastname");
		  $passwords = $request->getPostParameter("userpassword");
		  $usernames = $request->getPostParameter("username");
		  $useremails = $request->getPostParameter("useremail");
		  $usergroups = $request->getPostParameter("group");

		  $count = 0;
		  foreach($firstnames as $firstname)
		  {
		  	if(strlen($usernames[$count]) > 0 && strlen($useremails[$count]) > 0 && $usernames[$count] != "admin")
		  	{
		  		try
			    {

			      $q = Doctrine_Query::create()
					 ->from("CfUser a")
					 ->where('a.Struserid = ?', $usernames[$count]);
				  $existinguser = $q->execute();
				  if(sizeof($existinguser) > 0)
				  {
				  	  //ignore so that there are no double entries
				  }
				  else
				  {
					$newuser = new CfUser();
					$newuser->setStruserid($usernames[$count]);
					$newuser->setStrfirstname($firstnames[$count]);
					$newuser->setStrlastname($lastnames[$count]);
						$hash = password_hash($passwords[$count], PASSWORD_BCRYPT);
					$newuser->setStrpassword($hash);
					$newuser->setStremail($useremails[$count]);
					$newuser->setStruserid($useremails[$count]);
					$newuser->setStrpassword($hash);
					$newuser->setBdeleted(0);
					$newuser->setTslastaction(0);
					$newuser->setStrstreet("");
					$newuser->setStrcountry("");
					$newuser->setStrzipcode("");
					$newuser->setStrcity("");
					$newuser->setStrphoneMain1("");
					$newuser->setStrphoneMain2("");
					$newuser->setStrphoneMobile("");
					$newuser->setStrfax("");
					$newuser->setStrorganisation("");
					$newuser->setStrdepartment("");
					$newuser->setStrcostcenter("");
					$newuser->setUserdefined1Value("");
					$newuser->setUserdefined2Value("");
					$newuser->setNsubstitutetimevalue(0);
					$newuser->setStrsubstitutetimeunit(0);
					$newuser->setBusegeneralsubstituteconfig(0);
					$newuser->setBusegeneralemailconfig(0);
					$newuser->setEnableEmail(0);
					$newuser->setEnableChat(0);
					$newuser->setAboutMe("");
					$newuser->setProfilePic("");
					$newuser->setAddress("");
					$newuser->setTwitter("");
					$newuser->setFacebook("");
					$newuser->setYoutube("");
					$newuser->setLinkedin("");
					$newuser->setPinterest("");
					$newuser->setInstagram("");
					$newuser->setStrtoken("");
					$newuser->setStrtemppassword("");
					$newuser->save();

					  $newusergroup = new MfGuardUserGroup();
					  $newusergroup->setUserId($newuser->getNid());
					  $newusergroup->setGroupId($usergroups[$count]);
					  $newusergroup->save();
				  }
			  	  $count++;
				}catch(Exception $ex)
				{
				  	  //User Already Exists!
                    error_log("Debug-w: ".$ex);
				}
			}
		  }

		  $this->step = 4;
	  }

    $this->setLayout("layout-settings");

      if($wizard_manager->is_first_run())
      {
          $this->redirect("/plan/dashboard");
      }
  }

  public function executeWorkflow(sfWebRequest $request)
  {
      $wizard_manager = new WizardManager();

      if($request->getParameter("skip"))
      {
        $this->step = $request->getParameter("skip") + 1;
        $this->getUser()->setAttribute('post_resume', $this->step);
      }
      elseif($request->getParameter("skip") == "0")
      {
        $this->step = 1;
        $this->getUser()->setAttribute('post_resume', $this->step);
      }

      if($this->step == 5)
      {
          $this->redirect("/plan/services/index");
      }

	  if($request->getPostParameter("step") == 1)
	  {
		  //Assign departments
		  $departments = $request->getPostParameter("departmentname");
		  $departmentdescriptions = $request->getPostParameter("departmentdescription");

		  $count = 0;
		  foreach($departments as $department)
		  {
		  	$q = Doctrine_Query::create()
		  	   ->from("Department a")
		  	   ->where("a.department_name = ?", $department);
		  	$existingdepartment = $q->execute();
		  	if(strlen($department) > 0 && sizeof($existingdepartment) <= 0)
		  	{
		  		try
			    {
				  $newdepartment = new Department();
				  $newdepartment->setDepartmentName($department);
				  $newdepartment->save();
				  $count++;
				}catch(Exception $ex)
				{
				  	  //User Already Exists!
                      error_log($ex);
				}
			}
		  }

		  if($count > 0)
		  {
			  $this->step = 2;
		  }
		  else
		  {
			  $this->step = 1;
		  }
	  }
	  elseif($request->getPostParameter("step") == 2)
	  {
		  //Assign reviewers to departments

		  $count = 0;

          $q = Doctrine_Query::create()
              ->from("Department a")
              ->orderBy("a.department_name ASC");
          $departments = $q->execute();

          foreach($departments as $department)
          {
              $reviewers = $request->getPostParameter("department".$department->getId());
              foreach($reviewers as $reviewer)
              {
                  $q = Doctrine_Query::create()
                      ->from("CfUser a")
                      ->where("a.nid = ?", $reviewer);
                  $reviewer_details = $q->fetchOne();
                  if($reviewer_details)
                  {
                      $reviewer_details->setStrdepartment($department->getId());
                      $reviewer_details->save();
                      $count++;
                  }
              }
          }

		  if($count > 0)
		  {
			  $this->step = 3;
		  }
		  else
		  {
			  $this->step = 2;
		  }
	  }
	  elseif($request->getPostParameter("step") == 3)
	  {
		  //Assign stages
		  $stages = $request->getPostParameter("stagename");
      $stage_types = $request->getPostParameter("stagetype");
		//OTB ADD workflow category
		$workflow_category=new WorkflowCategory();
		$workflow_category->setTitle($request->getPostParameter("workflow_category"));
		$workflow_category->setDescription($request->getPostParameter("workflow_category_desc"));
		$workflow_category->save();
		
      //Add Parent Stage
      $parentstage = new Menus();
      $parentstage->setTitle($request->getPostParameter("workflow_title"));
	  $parentstage->setOrderNo(0);
	  //save category
	  $parentstage->setCategoryId($workflow_category->getId());
      $parentstage->save();

      $this->getUser()->setAttribute('service_id', $parentstage->getId());

		  $credential = null;

		  $q = Doctrine_Query::create()
			 ->from('MfGuardPermission a')
			 ->where('a.name = ?', 'accessmenu'.$parentstage->getId());
		  $similarcredential = $q->execute();
		  if(sizeof($similarcredential) == 0)
		  {
		  	try
		  	{
			  $credential = new MfGuardPermission();
			  $credential->setName('accessmenu'.$parentstage->getId());
			  $credential->setDescription("Access to ".$parentstage->getTitle()." stage");
			  $credential->save();
			}catch(Exception $ex)
			{
				//Permission already exists
			}
		  }
		  else
		  {
			  $credential = $q->fetchOne();
		  }

		  //Assign credentials to groups
		  $q = Doctrine_Query::create()
		     ->from("MfGuardGroup a");
		  $groups = $q->execute();
		  foreach($groups as $group)
		  {
			  	try{
					  $groupcredential = new MfGuardGroupPermission();
					  $groupcredential->setGroupId($group->getId());
					  $groupcredential->setPermissionId($credential->getId());
					  $groupcredential->save();
				}catch(Exception $ex)
				{
					//Group permission already existed
                    error_log($ex);
				}
		  }

		  //Add SubStages
		  $count = 0;
		  foreach($stages as $stagename)
		  {
		  	if(strlen($stagename) > 0)
		  	{
		      try
		      {
				  $stage = new SubMenus();
				  $stage->setMenuId($parentstage->getId());
				  $stage->setTitle($stagename);
                  $stage->setStageType($stage_types[$count]);
				  $stage->setOrderNo($count);
				  $stage->save();

			  	  $count++;
			  }catch(Exception $ex)
			  {
                  error_log($ex);
			  }

			  $credential = null;

			  $q = Doctrine_Query::create()
				 ->from('MfGuardPermission a')
				 ->where('a.name = ?', 'accesssubmenu'.$stage->getId());
			  $similarcredential = $q->execute();
			  if(sizeof($similarcredential) == 0)
			  {
			  	try{
				  $credential = new MfGuardPermission();
				  $credential->setName('accesssubmenu'.$stage->getId());
				  $credential->setDescription("Access to ".$stage->getTitle()." sub stage");
				  $credential->save();
				}catch(Exception $ex)
				{
					//Permission already exists
                    error_log($ex);
				}
			  }
			  else
			  {
				  $credential = $q->fetchOne();
			  }

			  //Assign credentials to groups
			  $q = Doctrine_Query::create()
				 ->from("MfGuardGroup a");
			  $groups = $q->execute();
			  foreach($groups as $group)
			  {
			  	try{
				  $groupcredential = new MfGuardGroupPermission();
				  $groupcredential->setGroupId($group->getId());
				  $groupcredential->setPermissionId($credential->getId());
				  $groupcredential->save();
				}catch(Exception $ex)
				{
					//Permission already exists
                    error_log($ex);
				}
			  }
			}
		  }

		  if($count > 0)
		  {
			  $this->step = 4;
		  }
		  else
		  {
			  $this->step = 3;
		  }
	  }
	  elseif($request->getPostParameter("step") == 4)
	  {
		  //Assign stages
		  $stages = $request->getPostParameter("stage");

		  //Add SubStages
		  $count = 0;
		  foreach($stages as $stage)
		  {
			  $stageactions = $request->getPostParameter("stageactions[".$stage."]");
			  foreach($stageactions as $stageaction)
			  {
				  if($stageaction == 1) //Move to Next Stage
				  {
					  $title = "Send to Next Stage";
					  $link = "/plan/forms/move?moveto=".$stages[$count + 1];
				  }
				  elseif($stageaction == 2) //Move to Previous Stage
				  {
					  $title = "Send Back";
					  $link = "/plan/forms/move?moveto=".$stages[$count - 1];
				  }
				  elseif($stageaction == 3) //Skip Next Stage
				  {
					  $title = "Send to Next Stage";
					  $link = "/plan/forms/move?moveto=".$stages[$count + 2];
				  }
				  elseif($stageaction == 4) //Skip Previous Stage
				  {
					  $title = "Send Back";
					  $link = "/plan/forms/move?moveto=".$stages[$count - 2];
				  }
				  elseif($stageaction == 5) //Move to Next Stage as Declined
				  {
					  $title = "Send to Next Stage and Decline";
					  $link = "/plan/forms/decline?moveto=".$stages[$count + 1];
				  }
				  elseif($stageaction == 6) //Move to Previous Stage as Declined
				  {
					  $title = "Send to Previous Stage and Decline";
					  $link = "/plan/forms/decline?moveto=".$stages[$count - 1];
				  }
				  elseif($stageaction == 7) //Move to Next Stage as Approved
				  {
					  $title = "Send to Next Stage and Approve";
					  $link = "/plan/forms/approve?approved=1&moveto=".$stages[$count + 1];
				  }

				  $action = new Buttons();
				  $action->setTitle($title);
				  $action->setLink($link);
				  $action->setImg("");
				  $action->setTooltip("");
		  		  $action->save();

				  //Assign action to stage
				  $stageaction = new SubMenuButtons();
				  $stageaction->setSubMenuId($stage);
				  $stageaction->setButtonId($action->getId());
				  $stageaction->setOrderNo(0);
				  $stageaction->setBackend(0);
				  $stageaction->save();

				  $credential = null;

				   $q = Doctrine_Query::create()
					 ->from('MfGuardPermission a')
					 ->where('a.name = ?', 'accessbutton'.$action->getId());
				  $similarcredential = $q->execute();
				  if(sizeof($similarcredential) == 0)
				  {
			  		try{
					  $credential = new MfGuardPermission();
					  $credential->setName('accessbutton'.$action->getId());
					  $credential->setDescription("Access to ".$action->getTitle()." button");
					  $credential->save();
					}catch(Exception $ex)
					{
						//Permission Already exists
					}
				  }
				  else
				  {
					  $credential = $q->fetchOne();
				  }

				  //Assign credentials to groups
				  $groupactions = $request->getPostParameter("groupactions[".$stage."]");
				  foreach($groupactions as $groupaction)
				  {
				  	try{
					  $groupcredential = new MfGuardGroupPermission();
					  $groupcredential->setGroupId($groupaction);
					  $groupcredential->setPermissionId($credential->getId());
					  $groupcredential->save();
					}catch(Exception $ex)
					{
						//Permission already exists
					}
				  }
			  }

			  $count++;
		  }

		  if($count > 0)
		  {
			  $this->step = 5;
		  }
		  else
		  {
			  $this->step = 4;
		  }
	  }
	  elseif($request->getPostParameter("step") == 5)
	  {
      $service_id = $this->getUser()->getAttribute('service_id');

      $q = Doctrine_Query::create()
         ->from("SubMenus a")
         ->where("a.menu_id = ?", $service_id);
      $stages = $q->execute();

      $stages_array = array();
      $comment_stages_array = array();

      foreach($stages as $stage)
      {
          $stages_array[] = "a.form_stage = ".$stage->getId();
          $comment_stages_array[] = "a.form_department_stage = ".$stage->getId();
      }

      $stages_query = implode(" OR ", $stages_array);

	  	$q = Doctrine_Query::create()
		     ->from("ApForms a")
		     ->where("a.form_active = 1")
         ->andWhere($stages_query);
		  $forms = $q->execute();

  	  if(sizeof($forms) > 5)
  	  {
	  	    $this->step = 8;
  	  }
  	  else
  	  {
	  	    $this->step = 5;
  	  }
	  }
	  elseif($request->getPostParameter("step") == 7)
	  {
		  //Assign fees
		  $feetitles = $request->getPostParameter("feetitle");
		  $feeamounts = $request->getPostParameter("feeamount");
		  $feeforms = $request->getPostParameter("feeform");
		  $feestages = $request->getPostParameter("feestage");

		  $count = 0;

		  foreach($feetitles as $feetitle)
		  {
		  	if(strlen($feetitle) > 0)
		  	{
			  $fee = new Fee();
			  $fee->setDescription($feetitle);
			  $fee->setAmount($feeamounts[$count]);
			  $fee->setApplicationForm($feeforms[$count]);
			  $fee->setApplicationStage($feestages[$count]);
			  $fee->save();
			}
			  $count++;
		  }

		  if($count > 0)
		  {
			  $this->step = 8;
		  }
		  else
		  {
			  $this->step = 7;
		  }
	  }
	  elseif($request->getPostParameter("step") == 8)
	  {
			$this->step = 9;
	  }
	  elseif($request->getPostParameter("step") == 9)
	  {
			$this->redirect("/plan/services/index");
	  }
    $this->setLayout("layout-settings");

    if($wizard_manager->is_first_run())
    {
        $this->redirect("/plan/dashboard");
    }

    $this->getUser()->setAttribute('post_resume', $this->step);
  }

    public function executeBindaction(sfWebRequest $request)
    {
        error_log("Debug-b: Binding from ".$request->getPostParameter("from")." to ".$request->getPostParameter("to"));

        $from = $request->getPostParameter("from");
        $to = $request->getPostParameter("to");

        $q = Doctrine_Query::create()
            ->from('Buttons a')
            ->where('a.title = ?','accessbutton'.$from.'to'.$to);
        $existing_action = $q->count();

        if($existing_action == 0)
        {

            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $to);
            $stage = $q->fetchOne();

            $action = new Buttons();
            $action->setTitle('Send to '.$stage->getTitle());
            $action->setLink('/plan/forms/move?moveto='.$to);
			$action->setImg("");
			$action->setTooltip("");
            $action->save();

            //Assign action to stage
            $stageaction = new SubMenuButtons();
            $stageaction->setSubMenuId($from);
            $stageaction->setButtonId($action->getId());
		    $stageaction->setOrderNo(0);
            $stageaction->setBackend(0);
            $stageaction->save();

            $q = Doctrine_Query::create()
                ->from('MfGuardPermission a')
                ->where('a.name = ?', 'accessbutton'.$from.'to'.$to);
            $similarcredential = $q->count();
            if($similarcredential == 0)
            {
                try{
                    $credential = new MfGuardPermission();
                    $credential->setImg('accessbutton'.$from.'to'.$to);
                    $credential->setName('accessbutton'.$from.'to'.$to);
                    $credential->setDescription("Access to ".$action->getTitle()." button");
                    $credential->save();

                    $q = Doctrine_Query::create()
                        ->from("MfGuardGroup a")
                        ->orderBy("a.name ASC");
                    $groups = $q->execute();

                    foreach($groups as $group) {
                        $permissiongroup = new MfGuardGroupPermission();
                        $permissiongroup->setGroupId($group->getId());
                        $permissiongroup->setPermissionId($credential->getId());
                        $permissiongroup->save();
                    }
                }catch(Exception $ex)
                {
                    //Permission Already exists
                }
            }
            else
            {
                $credential = $q->fetchOne();
            }
        }

        exit;
    }

    public function executeUnbindaction(sfWebRequest $request)
    {
        error_log("Debug-b: Unbinding from ".$request->getPostParameter("from")." to ".$request->getPostParameter("to"));

        $from = $request->getPostParameter("from");
        $to = $request->getPostParameter("to");

        $q = Doctrine_Query::create()
            ->from('SubMenuButtons a')
            ->where('a.name = ?', 'accessbutton'.$from.'to'.$to);
        $similarcredentials = $q->execute();
        foreach($similarcredentials as $credential)
        {
            $credential->delete();
        }

        $q = Doctrine_Query::create()
            ->from('Buttons a')
            ->where('a.img = ?','accessbutton'.$from.'to'.$to);
        $existing_actions = $q->execute();
        foreach($existing_actions as $action)
        {
            $q = Doctrine_Query::create()
                ->from('SubMenuButtons a')
                ->where('a.button_id = ?',$action->getId());
            $menu_buttons = $q->execute();
            foreach($menu_buttons as $menu_button)
            {
                $menu_button->delete();
            }

            $action->delete();
        }

        exit;
    }
}
