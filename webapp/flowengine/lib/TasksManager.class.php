<?php

/**
 *
 * TasksManager class that manages the creation and update of tasks
 *
 */

class TasksManager {

    //Public constructor for the tasks manager class
    public function __construct()
    {

    }

    /**
    *
    * Assign a task to a reviewer
    *
    **/
    public function assign_task($task_type, $assigned_by, $assigned_to, $start_date, $end_date, $application_id)
    {
      $application = Doctrine_Core::getTable('FormEntry')->find(array($application_id));
      if($application)
      {
        //Should check if user already has a pending task of similar type. Prevent double tasking.
        $q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.status <> 25 AND a.status <> 55 AND a.active = 1")
            ->andWhere("a.application_id = ?", $application->getId())
            ->andWhere("a.task_stage = ?", $application->getApproved())
            ->andWhere("a.owner_user_id = ?", $assigned_to);
        $assigned_tasks = $q->count();
        if($assigned_tasks == 0)
        {
          //Create task
          $task = new Task();
          $task->setType($task_type);
          $task->setCreatorUserId($assigned_by);
          $task->setOwnerUserId($assigned_to);
          $task->setDuration("0");
          $task->setStartDate($start_date);
          $task->setEndDate($end_date);
          $task->setPriority('3');
          $task->setIsLeader("0");
          $task->setActive("1");
          $task->setStatus("1");
          $task->setLastUpdate(date('Y-m-d'));
          $task->setDateCreated(date('Y-m-d'));
          $task->setRemarks("");
          $task->setApplicationId($application_id);
          $task->setTaskStage($application->getApproved());
          $task->save();

          //Move application to the next stage if automatic trigger is set
           error_log("Debug-task: Marking form entry flag assessment in progress to 1");

           $application->setAssessmentInprogress(1);
           $application->save();

          return $task->getId();
        }
        else {
          return false;
        }
      }
      else {
        return false;
      }
    }

    /**
    *
    * Pick a task from a list of available tasks
    *
    **/
    public function pick_task($task_type, $assigned_to, $application_id)
    {
      $application = Doctrine_Core::getTable('FormEntry')->find(array($application_id));
      if($application)
      {
        //Return id of pending task if available
        $q = Doctrine_Query::create()
            ->from("Task a")
            ->leftJoin("a.Owner b")
            ->leftJoin("a.Application c")
            ->leftJoin("a.Creator d")
            ->where("a.owner_user_id = ? and a.status = ? and c.id = ?", array($assigned_to, 1, $application_id))
            ->orderBy("a.id DESC");
        $pending_task = $q->fetchOne();
        if($q->count())
        {
           return $pending_task->getId();
        }
        else {
          return $this->assign_task($task_type, $assigned_to, $assigned_to, date("Y-m-d"), date("Y-m-d"), $application_id);
        }
      }
      else
      {
        return false;
      }
    }

    /**
    *
    * Get a list of available tasks accessible to a reviewer
    *
    **/
    public function can_pick_task($application_id, $reviewer_id)
    {
      $q = Doctrine_Query::create()
           ->from('CfUser a')
           ->where('a.nid = ?', $reviewer_id);
      $reviewer = $q->fetchOne();

      $q = Doctrine_Query::create()
          ->from("FormEntry a")
          ->leftJoin("a.Task b")
          ->leftJoin("b.Owner c")
          ->where("a.id = ?", $application_id)
          ->andWhere("c.strdepartment = ?", $reviewer->getStrdepartment())
          ->andWhere("b.task_stage = a.approved")
          ->andWhere("b.status = 1")
          ->orderBy("a.id DESC");

      if($q->count())
      {
          $is_free = false;
      }
      else
      {
          $is_free = true;
      }

      return $is_free;
    }

    /**
    *
    * Get a list of available tasks accessible to a reviewer
    *
    **/
    public function available_tasks($reviewer_id, $page)
    {
        //Get a list of all stages with automatic assignments
        $q = Doctrine_Query::create()
            ->from("SubMenuTasks a");
        $task_stages = $q->execute();

        //Filter out stages that we don't have permissions for
        $allowed_stages = "";

        $count = 0;
        foreach($task_stages as $task_stage)
        {
          if($this->reviewer_has_credential($reviewer_id, 'accesssubmenu'.$task_stage->getSubMenuId()))
          {
            $count++;
            if($count == 1)
            {
              $allowed_stages .= "a.approved = ".$task_stage->getSubMenuId();
            }
            else
            {
              $allowed_stages .= " OR a.approved = ".$task_stage->getSubMenuId();
            }
          }
        }

        //Display applications without any tasks
        $q = "";

        if($allowed_stages <> "")
        {
          $q = Doctrine_Query::create()
               ->from('CfUser a')
               ->where('a.nid = ?', $reviewer_id);
          $reviewer = $q->fetchOne();

          $q = Doctrine_Query::create()
              ->from("FormEntry a")
              ->leftJoin("a.Task b")
              ->leftJoin("b.Owner c")
              ->where($allowed_stages)
              ->andWhere("b.owner_user_id IS NULL OR c.strdepartment <> ?", $reviewer->getStrdepartment())
              ->orderBy("a.id DESC");
        }
        else {
          $q = Doctrine_Query::create()
              ->from("FormEntry a")
              ->where("a.approved = -1")
              ->orderBy("a.id DESC");
        }

        $pending_page = 1;

        if($page)
        {
          $pending_page = $page;
        }

        $pending_pager = new sfDoctrinePager('Task', 10);
        $pending_pager->setQuery($q);
        $pending_pager->setPage($pending_page);
        $pending_pager->init();

        return $pending_pager;
    }

    /**
    *
    * Get a list of tasks assigned to a reviewer
    *
    **/
    public function assigned_tasks($reviewer_id, $page)
    {
        //Get a list of all stages with automatic assignments
        $q = Doctrine_Query::create()
            ->from("SubMenuTasks a");
        $task_stages = $q->execute();

        //Filter out stages that we don't have permissions for
        $allowed_stages = "";

        $count = 0;
        foreach($task_stages as $task_stage)
        {
          if($this->reviewer_has_credential($reviewer_id, 'accesssubmenu'.$task_stage->getSubMenuId()))
          {
            $count++;
            if($count == 1)
            {
              $allowed_stages .= "a.approved = ".$task_stage->getSubMenuId();
            }
            else
            {
              $allowed_stages .= " OR a.approved = ".$task_stage->getSubMenuId();
            }
          }
        }

        //Display applications without any tasks
        $q = "";

        if($allowed_stages <> "")
        {
          $q = Doctrine_Query::create()
               ->from('CfUser a')
               ->where('a.nid = ?', $reviewer_id);
          $reviewer = $q->fetchOne();

          $q = Doctrine_Query::create()
              ->from("FormEntry a")
              ->leftJoin("a.Task b")
              ->leftJoin("b.Owner c")
              ->where($allowed_stages)
              ->andWhere("b.owner_user_id IS NULL OR c.strdepartment <> ?", $reviewer->getStrdepartment())
              ->orderBy("a.id DESC");
        }
        else {
          $q = Doctrine_Query::create()
              ->from("FormEntry a")
              ->where("a.approved = -1")
              ->orderBy("a.id DESC");
        }

        $q = Doctrine_Query::create()
            ->from("Task a")
            ->leftJoin("a.Owner b")
            ->leftJoin("a.Application c")
            ->leftJoin("a.Creator d")
            ->where("a.owner_user_id = ? and a.status = ? and c.id = a.application_id", array($reviewer_id, 1))
            ->orWhere("a.creator_user_id = ? and a.status = ? and c.id = a.application_id", array($reviewer_id, 2))
            ->orderBy("a.id DESC");

        $assigned_page = 1;

        if($page)
        {
          $assigned_page = $page;
        }

        $assigned_pager = new sfDoctrinePager('Task', 10);
        $assigned_pager->setQuery($q);
        $assigned_pager->setPage($assigned_page);
        $assigned_pager->init();

        return $assigned_pager;
    }

    /**
    *
    * Try to cancel a task. Only the person that assigned it is allowed to do this
    *
    **/
    public function cancel_task($task_id, $review_id)
    {

    }

    /**
    *
    * Show a transfer form. Only the person that assigned it is allowed to do this
    *
    **/
    public function transfer_form($task_id, $review_id)
    {

    }

    /**
    *
    * Try to transfer a task. Only the person that assigned it is allowed to do this
    *
    **/
    public function transfer_task($task_id, $assigned_by, $assigned_to)
    {

    }

    /**
    *
    * Start commenting/invoicing on a task. Only the person assigned to a task is allowed to do this
    *
    **/
    public function start_task($task_id, $reviewer_id)
    {

    }

    /**
    *
    * Save a task as a draft. Only the person assigned to a task is allowed to do this
    *
    **/
    public function draft_task($task_id, $reviewer_id)
    {

    }

    /**
    *
    * Save the current work on a task. Only the person assigned to a task is allowed to do this
    *
    **/
    public function save_task($task_id, $reviewer_id)
    {

    }

    /**
    *
    * Resume commenting/invoicing on a task. Only the person assigned to a task is allowed to do this
    *
    **/
    public function resume_task($task_id, $reviewer_id)
    {

    }

    /**
    *
    * Complete a task. Only the person that assigned the task is allowed to do this
    *
    **/
    public function complete_task($task_id, $reviewer_id)
    {

    }

    /**
    *
    * Complete a task. Only the person that assigned the task is allowed to do this
    *
    **/
  	public function reviewer_has_credential($user_id, $credential)
  	{
  		$q = Doctrine_Query::create()
  			->from('MfGuardUserGroup a')
  			->leftJoin('a.MfGuardGroup b')
  			->leftJoin('b.MfGuardGroupPermission c') //Left Join group permissions
  			->leftJoin('c.MfGuardPermission d') //Left Join permissions
  			->where('a.user_id = ?', $user_id)
  			->andWhere('d.name = ?', $credential);
  		$usergroups = $q->execute();
  		if(sizeof($usergroups) > 0)
  		{
  			return TRUE;
  		}
  		else
  		{
  			return FALSE;
  		}
  	}

    //Get all services that current user is allowed to access
    public static function any_pending_tasks($application_id)
    {
        $q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.application_id = ?", $application_id)
            ->andWhere("a.status = 1 OR a.status = 2")
            ->orderBy("a.id DESC");

        if($q->count())
        {
           return true;
        }
        else 
        {
          return false;
        }
    }
}
