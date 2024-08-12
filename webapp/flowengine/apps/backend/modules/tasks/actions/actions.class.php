<?php

/**
 * Tasks actions.
 *
 * Tasks Management Service.
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class tasksActions extends sfActions
{
    /**
     * Executes 'List' action
     *
     * Displays list of tasks assigned to current user
     *
     * @param sfRequest $request A request object
     */
    public function executeList(sfWebRequest $request)
    {
        //Get list of pending tasks assigned to current user
        $q = Doctrine_Query::create()
            ->from("Task a")
            ->leftJoin("a.Owner b")
            ->leftJoin("a.Application c")
            ->leftJoin("a.Creator d")
            ->where("a.owner_user_id = ? and a.status = ? and c.id = a.application_id", array($this->getUser()->getAttribute("userid"), 1))
            ->orWhere("a.creator_user_id = ? and a.status = ? and c.id = a.application_id", array($this->getUser()->getAttribute("userid"), 2))
            ->orderBy("a.id DESC");
        $this->tasks = $q->execute();
        $this->service = $request->getParameter("service", 0);
        $this->stage = $request->getParameter('stage', 0);
        $this->page = $request->getParameter('page', 1);

        $this->getUser()->setAttribute('service', $this->service);
    }

    /**
     * Executes 'Pick' action
     *
     * Reviewers can assign tasks to themselves
     *
     * @param sfRequest $request A request object
     */
    public function executePick(sfWebRequest $request)
    {
        //OTB ADD
        $agency = new AgencyManager();
        $this->forward404Unless($agency->checkAgencyApplicationAccess($this->getUser()->getAttribute("userid"), $request->getParameter('id')));
        $tasks_manager = new TasksManager();

        $task_id = $tasks_manager->pick_task($request->getParameter('type'), $this->getUser()->getAttribute("userid"), $request->getParameter('id'));

        if ($task_id) {
            //Audit 
            Audit::audit($request->getParameter('id'), "Picked a task");

            $this->redirect("/backend.php/tasks/view?id=" . $task_id);
        } else {
            //If there exists a pending task for this user and application then redirect to that task
            $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.application_id = ?", $request->getParameter('id'))
                ->andWhere("a.owner_user_id = ?", $this->getUser()->getAttribute("userid"))
                ->andWhere("a.status <> ? AND a.status <> ?", array(25, 55));

            if ($q->count() > 0) {
                $task = $q->fetchOne();

                //Audit
                Audit::audit($request->getParameter('id'), "Picked a task");

                $this->redirect("/backend.php/tasks/view?id=" . $task->getId());
            }

            $this->redirect("/backend.php/tasks/list");
        }
    }

    /**
     * Executes 'Pickinspection' action
     *
     * Reviewers can assign tasks to themselves
     *
     * @param sfRequest $request A request object
     */
    public function executePickinspection(sfWebRequest $request)
    {
        //OTB ADD
        $agency = new AgencyManager();
        $this->forward404Unless($agency->checkAgencyApplicationAccess($this->getUser()->getAttribute("userid"), $request->getParameter('id')));
        $tasks_manager = new TasksManager();

        $task_id = $tasks_manager->pick_task($request->getParameter('type'), $this->getUser()->getAttribute("userid"), $request->getParameter('id'));

        if ($task_id) {
            //Audit 
            Audit::audit($request->getParameter('id'), "Picked an inspection");

            //Create Inspections entry
            $q = Doctrine_Query::create()
                ->from("Inspections a")
                ->where("a.task_id = ?", $task_id);

            if ($q->count() == 0) {
                $q = Doctrine_Query::create()
                    ->from("Task a")
                    ->where("a.id = ?", $task_id);
                $task = $q->fetchOne();
                $application = $task->getApplication();

                $current_reviewer = Functions::current_user();

                $department_id = $current_reviewer->getStrdepartment();

                if (!is_numeric($department_id)) {
                    $q = Doctrine_Query::create()
                        ->from("Department a")
                        ->where("a.department_name = ?", $department_id);
                    $department_id = $q->fetchOne()->getId();
                }

                $inspection = new Inspections();
                $inspection->setApplicationId($application->getId());
                $inspection->setStageId($application->getApproved());
                $inspection->setDepartmentId($department_id);
                $inspection->setReviewerId($current_reviewer->getNid());
                $inspection->setTaskId($task->getId());
                $inspection->save();
            }

            $this->redirect("/backend.php/tasks/view?id=" . $task_id);
        } else {
            //If there exists a pending task for this user and application then redirect to that task
            $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.application_id = ?", $request->getParameter('id'))
                ->andWhere("a.owner_user_id = ?", $this->getUser()->getAttribute("userid"))
                ->andWhere("a.status <> ? AND a.status <> ?", array(25, 55));

            if ($q->count() > 0) {
                $task = $q->fetchOne();

                //Audit
                Audit::audit($request->getParameter('id'), "Picked an inspection");

                //Create Inspections entry
                $q = Doctrine_Query::create()
                    ->from("Inspections a")
                    ->where("a.task_id = ?", $task->getId());

                if ($q->count() == 0) {
                    $q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("a.id = ?", $task->getId());
                    $task = $q->fetchOne();
                    $application = $task->getApplication();

                    $current_reviewer = Functions::current_user();

                    $department_id = $current_reviewer->getStrdepartment();

                    if (!is_numeric($department_id)) {
                        $q = Doctrine_Query::create()
                            ->from("Department a")
                            ->where("a.department_name = ?", $department_id);
                        $department_id = $q->fetchOne()->getId();
                    }

                    $inspection = new Inspections();
                    $inspection->setApplicationId($application->getId());
                    $inspection->setStageId($application->getApproved());
                    $inspection->setDepartmentId($department_id);
                    $inspection->setReviewerId($current_reviewer->getNid());
                    $inspection->setTaskId($task->getId());
                    $inspection->save();
                }

                $this->redirect("/backend.php/tasks/view?id=" . $task->getId());
            }

            $this->redirect("/backend.php/tasks/list");
        }
    }

    /**
     * Executes 'View' action
     *
     * View a task
     *
     * @param sfRequest $request A request object
     */
    public function executeView(sfWebRequest $request)
    {
        $this->comment_sheet_posted = false;
        $this->action_needed = false;
        error_log('------TASK VIEW-------');
        error_log(var_export($_POST, true));
        if ($request->getPostParameter('form_id') || $request->getParameter("done")) {
            $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.id = ?", $this->getUser()->getAttribute("task_id"));
            $this->task = $q->fetchOne();

            $this->comment_sheet_posted = true;
        } else {
            $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.id = ?", $request->getParameter('id'));
            $this->task = $q->fetchOne();

            if ($this->task) {
                $this->getUser()->setAttribute('task_id', $this->task->getId());
            } else {
                $q = Doctrine_Query::create()
                    ->from("Task a")
                    ->where("a.id = ?", $this->getUser()->getAttribute("task_id"));
                $this->task = $q->fetchOne();
            }
            $this->comment_sheet_posted = false;
        }

        if (empty($this->task)) {
            $this->redirect("/backend.php/dashboard");
        }

        //Audit
        Audit::audit("", "Viewed a task <a href='/backend.php/tasks/view/id/" . $this->task->getId() . "'></a>");


        //Check JSON. Generate if empty
        $application_manager = new ApplicationManager();
        $application_manager->check_json($this->task->getApplicationId());

        $this->application = $this->task->getApplication();
        //query form values
        $query = "SELECT * FROM ap_form_" . $this->application->getFormId() . " WHERE id = '" . $this->application->getEntryId() . "'";

        $this->application_form = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll();
        //If the application has not been marked as assessment in progress and task is pending then mark it
        if ($this->task->getStatus() == 1 && $this->application->getAssessmentInprogress() == 0) {
            $this->application->setAssessmentInprogress(1);
            $this->application->save();
        }

        //If the task has no type then check the stage settings and set it 
        // else set to default type which is assessemnt
        // if($this->task->getType() == "" || $this->task->getType() == NULL)
        //Constantly update the task type on refresh
        if ($this->task->getStatus() == 1 && $this->task->getType() < 1) {
            $allowed_tasks = $this->application->getStage()->getSubMenuTasks();
            foreach ($allowed_tasks as $task) {
                $this->task->setType($task->getTaskId());
                $this->task->save();
                continue;
            }
        }


        $this->current_tab = $request->getParameter("current_tab", "application");
    }

    /**
     * Executes 'New' action
     *
     * Allows current reviewer to assign a new task to other reviewers or to themselves
     *
     * @param sfRequest $request A request object
     */
    public function executeNew(sfWebRequest $request)
    {
        if ($request->getParameter("application")) {
            $this->appid = $request->getParameter("application");
        }

        if ($request->getParameter("submenu")) {
            $submenu = $request->getParameter("submenu");

            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->where("a.approved = ?", $submenu)
                ->orderBy("a.application_id DESC");
            $this->applications = $q->execute();
        }
    }

    /**
     * Executes 'Save' action
     *
     * Saves new task details to the database
     *
     * @param sfRequest $request A request object
     */
    public function executeSave(sfWebRequest $request)
    {
        $this->success = false;
        if ($request->getPostParameter("workflow_type") == 0) {
            if ($request->getPostParameter("reviewers") || $request->getPostParameter("workflow")) {
                if ($request->getPostParameter("workflow") == "none") {
                    $reviewers = $request->getPostParameter("reviewers");
                    $supportreviewers = $request->getPostParameter("supporters");
                    $otherreviewers = $request->getPostParameter("otherreviewers");
                } else {
                    $q = Doctrine_Query::create()
                        ->from('WorkflowReviewers a')
                        ->where('a.workflow_id = ?', $request->getPostParameter("workflow"))
                        ->orderBy('a.id ASC');
                    $workflowreviewers = $q->execute();
                    foreach ($workflowreviewers as $workflowreviewer) {
                        $reviewers[] = $workflowreviewer->getReviewerId();
                    }
                }

                if ($request->getPostParameter("application")) {
                    $count = 0;
                    $previous_task_id = 0;
                    foreach ($reviewers as $reviewer) {
                        $application = $request->getPostParameter("application");
                        if ($count == 0) {
                            $task = new Task();
                            $task->setType($request->getPostParameter("task_type"));
                            $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                            $task->setOwnerUserId($reviewer);
                            $task->setSheetId($request->getPostParameter("task_sheet"));
                            $task->setDuration("0");
                            $task->setStartDate($request->getPostParameter("start_date"));
                            $task->setEndDate($request->getPostParameter("end_date"));
                            $task->setPriority($request->getPostParameter("priority"));
                            $task->setIsLeader("1");
                            $task->setActive("1");
                            $task->setStatus("1");
                            $task->setLastUpdate(date('Y-m-d'));
                            $task->setDateCreated(date('Y-m-d'));
                            $task->setRemarks($request->getPostParameter("description"));
                            $task->setApplicationId($application);
                            $task->save();

                            //Audit 
                            Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                            $this->task = $task;
                            $previous_task_id = $task->getId();
                            $this->success = true;

                            //if application is in submissions, move to circulations
                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $fullApplication = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.nid = ?', $reviewer);
                            $treviewer = $q->fetchOne();

                            $audit = new Audit();
                            $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                        } else {
                            $task = new Task();
                            $task->setType($request->getPostParameter("task_type"));
                            $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                            $task->setOwnerUserId($reviewer);
                            $task->setSheetId($request->getPostParameter("task_sheet"));
                            $task->setDuration("0");
                            $task->setStartDate($request->getPostParameter("start_date"));
                            $task->setEndDate($request->getPostParameter("end_date"));
                            $task->setPriority($request->getPostParameter("priority"));
                            $task->setIsLeader("1");
                            if ($request->getPostParameter("workflow_type") == "1") {
                                $task->setActive("0");
                            } else {
                                $task->setActive("1");
                            }
                            $task->setStatus("1");
                            $task->setLastUpdate(date('Y-m-d'));
                            $task->setDateCreated(date('Y-m-d'));
                            $task->setRemarks($request->getPostParameter("description"));
                            $task->setApplicationId($application);
                            $task->save();

                            //Audit 
                            Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                            $this->task = $task;

                            $taskqueue = new TaskQueue();
                            $taskqueue->setCurrentTaskId($previous_task_id);
                            $taskqueue->setNextTaskId($task->getId());
                            $taskqueue->save();

                            $previous_task_id = $task->getId();
                            $this->success = true;

                            //if application is in submissions, move to circulations
                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $fullApplication = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.nid = ?', $reviewer);
                            $treviewer = $q->fetchOne();

                            $audit = new Audit();
                            $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                        }


                        $count++;

                        $q = Doctrine_Query::create()
                            ->from('FormEntry a')
                            ->where('a.id = ?', $application);
                        $this->application = $q->fetchOne();

                        $q = Doctrine_Query::create()
                            ->from("CfUser a")
                            ->where("a.nid = ?", $reviewer);
                        $reviewer = $q->fetchOne();

                        $body = "
                                    Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                                    <br>
                                    You have been assigned a new task on " . $this->application->getApplicationId() . ":<br>
                                    <br><br>
                                    &ldquo; " . $request->getPostParameter("description") . " &rdquo;
                                    <br>
                                    <br>
                                    Click here to view the task: <br>
                                    ------- <br>
                                    <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/tasks/view/id/" . $this->task->getId() . "'>Link to " . $this->application->getApplicationId() . " task</a><br>
                                    ------- <br>

                                    <br>
                                    ";

                        $mailnotifications = new mailnotifications();
                        $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);
                    }
                    foreach ($otherreviewers as $reviewer) {
                        $application = $request->getPostParameter("application");

                        if ($count == 0) {
                            $task = new Task();
                            $task->setType($request->getPostParameter("task_type"));
                            $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                            $task->setOwnerUserId($reviewer);
                            $task->setSheetId($request->getPostParameter("task_sheet"));
                            $task->setDuration("0");
                            $task->setStartDate($request->getPostParameter("start_date"));
                            $task->setEndDate($request->getPostParameter("end_date"));
                            $task->setPriority($request->getPostParameter("priority"));
                            $task->setIsLeader("0");
                            $task->setActive("1");
                            $task->setStatus("1");
                            $task->setLastUpdate(date('Y-m-d'));
                            $task->setDateCreated(date('Y-m-d'));
                            $task->setRemarks($request->getPostParameter("description"));
                            $task->setApplicationId($application);
                            $task->save();

                            //Audit 
                            Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                            $this->task = $task;
                            $previous_task_id = $task->getId();
                            $this->success = true;

                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $fullApplication = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.nid = ?', $reviewer);
                            $treviewer = $q->fetchOne();

                            $audit = new Audit();
                            $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                        } else {
                            $task = new Task();
                            $task->setType($request->getPostParameter("task_type"));
                            $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                            $task->setOwnerUserId($reviewer);
                            $task->setSheetId($request->getPostParameter("task_sheet"));
                            $task->setDuration("0");
                            $task->setStartDate($request->getPostParameter("start_date"));
                            $task->setEndDate($request->getPostParameter("end_date"));
                            $task->setPriority($request->getPostParameter("priority"));
                            $task->setIsLeader("0");
                            if ($request->getPostParameter("workflow_type") == "1") {
                                $task->setActive("0");
                            } else {
                                $task->setActive("1");
                            }
                            $task->setStatus("1");
                            $task->setLastUpdate(date('Y-m-d'));
                            $task->setDateCreated(date('Y-m-d'));
                            $task->setRemarks($request->getPostParameter("description"));
                            $task->setApplicationId($application);
                            $task->save();

                            //Audit 
                            Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                            $this->task = $task;

                            $taskqueue = new TaskQueue();
                            $taskqueue->setCurrentTaskId($previous_task_id);
                            $taskqueue->setNextTaskId($task->getId());
                            $taskqueue->save();

                            $previous_task_id = $task->getId();
                            $this->success = true;


                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $fullApplication = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.nid = ?', $reviewer);
                            $treviewer = $q->fetchOne();

                            $audit = new Audit();
                            $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                        }

                        $q = Doctrine_Query::create()
                            ->from('FormEntry a')
                            ->where('a.id = ?', $application);
                        $this->application = $q->fetchOne();

                        $q = Doctrine_Query::create()
                            ->from("CfUser a")
                            ->where("a.nid = ?", $reviewer);
                        $reviewer = $q->fetchOne();

                        $body = "
                                    Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                                    <br>
                                    You have been assigned a new task on " . $this->application->getApplicationId() . ":<br>
                                    <br><br>
                                    &ldquo; " . $request->getPostParameter("description") . " &rdquo;
                                    <br>
                                    <br>
                                    Click here to view the task: <br>
                                    ------- <br>
                                    <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/tasks/view/id/" . $this->task->getId() . "'>Link to " . $this->application->getApplicationId() . " task</a><br>
                                    ------- <br>

                                    <br>
                                    ";

                        $mailnotifications = new mailnotifications();
                        $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);
                        $count++;
                    }

                    foreach ($supportreviewers as $reviewer) {
                        $application = $request->getPostParameter("application");
                        if ($count == 0) {
                            $task = new Task();
                            $task->setType($request->getPostParameter("task_type"));
                            $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                            $task->setOwnerUserId($reviewer);
                            $task->setSheetId($request->getPostParameter("task_sheet"));
                            $task->setDuration("0");
                            $task->setStartDate($request->getPostParameter("start_date"));
                            $task->setEndDate($request->getPostParameter("end_date"));
                            $task->setPriority($request->getPostParameter("priority"));
                            $task->setIsLeader("0");
                            $task->setActive("1");
                            $task->setStatus("1");
                            $task->setLastUpdate(date('Y-m-d'));
                            $task->setDateCreated(date('Y-m-d'));
                            $task->setRemarks($request->getPostParameter("description"));
                            $task->setApplicationId($application);
                            $task->save();

                            //Audit 
                            Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                            $this->task = $task;
                            $previous_task_id = $task->getId();
                            $this->success = true;


                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $fullApplication = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.nid = ?', $reviewer);
                            $treviewer = $q->fetchOne();

                            $audit = new Audit();
                            $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                        } else {
                            $task = new Task();
                            $task->setType($request->getPostParameter("task_type"));
                            $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                            $task->setOwnerUserId($reviewer);
                            $task->setSheetId($request->getPostParameter("task_sheet"));
                            $task->setDuration("0");
                            $task->setStartDate($request->getPostParameter("start_date"));
                            $task->setEndDate($request->getPostParameter("end_date"));
                            $task->setPriority($request->getPostParameter("priority"));
                            $task->setIsLeader("0");
                            if ($request->getPostParameter("workflow_type") == "1") {
                                $task->setActive("0");
                            } else {
                                $task->setActive("1");
                            }
                            $task->setStatus("1");
                            $task->setLastUpdate(date('Y-m-d'));
                            $task->setDateCreated(date('Y-m-d'));
                            $task->setRemarks($request->getPostParameter("description"));
                            $task->setApplicationId($application);
                            $task->save();

                            //Audit 
                            Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                            $this->task = $task;

                            $taskqueue = new TaskQueue();
                            $taskqueue->setCurrentTaskId($previous_task_id);
                            $taskqueue->setNextTaskId($task->getId());
                            $taskqueue->save();

                            $previous_task_id = $task->getId();
                            $this->success = true;


                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $fullApplication = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.nid = ?', $reviewer);
                            $treviewer = $q->fetchOne();

                            $audit = new Audit();
                            $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                        }
                        $q = Doctrine_Query::create()
                            ->from('FormEntry a')
                            ->where('a.id = ?', $application);
                        $this->application = $q->fetchOne();

                        $q = Doctrine_Query::create()
                            ->from("CfUser a")
                            ->where("a.nid = ?", $reviewer);
                        $reviewer = $q->fetchOne();

                        $body = "
                                        Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                                        <br>
                                        You have been assigned a new task on " . $this->application->getApplicationId() . ":<br>
                                        <br><br>
                                        &ldquo; " . $request->getPostParameter("description") . " &rdquo;
                                        <br>
                                        <br>
                                        Click here to view the task: <br>
                                        ------- <br>
                                        <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/tasks/view/id/" . $this->task->getId() . "'>Link to " . $this->application->getApplicationId() . " task</a><br>
                                        ------- <br>

                                        <br>
                                        ";

                        $mailnotifications = new mailnotifications();
                        $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);
                        $count++;
                    }

                    if ($request->getPostParameter("task_type") == 2) {
                        $application = $request->getPostParameter("application");
                        $q = Doctrine_Query::create()
                            ->from('FormEntry a')
                            ->where('a.id = ?', $application);
                        $appentry = $q->fetchOne();
                        if ($appentry && $appentry->getApproved() == "852") {
                            $appentry->setApproved("853");
                            $appentry->save();
                        } else if ($appentry && $appentry->getApproved() == "861") {
                            $appentry->setApproved("862");
                            $appentry->save();
                        } else if ($appentry && $appentry->getApproved() == "902") {
                            $appentry->setApproved("903");
                            $appentry->save();
                        } else if ($appentry && $appentry->getApproved() == "912") {
                            $appentry->setApproved("913");
                            $appentry->save();
                        } else if ($appentry && $appentry->getApproved() == "922") {
                            $appentry->setApproved("923");
                            $appentry->save();
                        }
                    }


                    $application = $request->getPostParameter("application");
                    $q = Doctrine_Query::create()
                        ->from('FormEntry a')
                        ->where('a.id = ?', $application);
                    $appentry = $q->fetchOne();
                    if ($appentry && $appentry->getApproved() == "869") {
                        $appentry->setApproved("864");
                        $appentry->save();
                    } else if ($appentry && $appentry->getApproved() == "865") {
                        $appentry->setApproved("867");
                        $appentry->save();
                    }
                } else {
                    $applications = $request->getPostParameter('applications');
                    foreach ($applications as $application) {
                        $count = 0;
                        $previous_task_id = 0;
                        foreach ($reviewers as $reviewer) {
                            if ($count == 0) {
                                $task = new Task();
                                $task->setType($request->getPostParameter("task_type"));
                                $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                                $task->setOwnerUserId($reviewer);
                                $task->setSheetId($request->getPostParameter("task_sheet"));
                                $task->setDuration("0");
                                $task->setStartDate($request->getPostParameter("start_date"));
                                $task->setEndDate($request->getPostParameter("end_date"));
                                $task->setPriority($request->getPostParameter("priority"));
                                $task->setIsLeader("1");
                                $task->setActive("1");
                                $task->setStatus("1");
                                $task->setLastUpdate(date('Y-m-d'));
                                $task->setDateCreated(date('Y-m-d'));
                                $task->setRemarks($request->getPostParameter("description"));
                                $task->setApplicationId($application);
                                $task->save();

                                //Audit 
                                Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                                $this->task = $task;
                                $previous_task_id = $task->getId();
                                $this->success = true;

                                //if application is in submissions, move to circulations
                                $q = Doctrine_Query::create()
                                    ->from('FormEntry a')
                                    ->where('a.id = ?', $application);
                                $fullApplication = $q->fetchOne();

                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.nid = ?', $reviewer);
                                $treviewer = $q->fetchOne();

                                $audit = new Audit();
                                $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                            } else {
                                $task = new Task();
                                $task->setType($request->getPostParameter("task_type"));
                                $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                                $task->setOwnerUserId($reviewer);
                                $task->setSheetId($request->getPostParameter("task_sheet"));
                                $task->setDuration("0");
                                $task->setStartDate($request->getPostParameter("start_date"));
                                $task->setEndDate($request->getPostParameter("end_date"));
                                $task->setPriority($request->getPostParameter("priority"));
                                $task->setIsLeader("1");
                                if ($request->getPostParameter("workflow_type") == "1") {
                                    $task->setActive("0");
                                } else {
                                    $task->setActive("1");
                                }
                                $task->setStatus("1");
                                $task->setLastUpdate(date('Y-m-d'));
                                $task->setDateCreated(date('Y-m-d'));
                                $task->setRemarks($request->getPostParameter("description"));
                                $task->setApplicationId($application);
                                $task->save();

                                //Audit 
                                Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                                $this->task = $task;

                                $taskqueue = new TaskQueue();
                                $taskqueue->setCurrentTaskId($previous_task_id);
                                $taskqueue->setNextTaskId($task->getId());
                                $taskqueue->save();

                                $previous_task_id = $task->getId();
                                $this->success = true;

                                //if application is in submissions, move to circulations
                                $q = Doctrine_Query::create()
                                    ->from('FormEntry a')
                                    ->where('a.id = ?', $application);
                                $fullApplication = $q->fetchOne();

                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.nid = ?', $reviewer);
                                $treviewer = $q->fetchOne();

                                $audit = new Audit();
                                $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                            }


                            $count++;

                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $this->application = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from("CfUser a")
                                ->where("a.nid = ?", $reviewer);
                            $reviewer = $q->fetchOne();

                            $body = "
                                    Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                                    <br>
                                    You have been assigned a new task on " . $this->application->getApplicationId() . ":<br>
                                    <br><br>
                                    &ldquo; " . $request->getPostParameter("description") . " &rdquo;
                                    <br>
                                    <br>
                                    Click here to view the task: <br>
                                    ------- <br>
                                    <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/tasks/view/id/" . $this->task->getId() . "'>Link to " . $this->application->getApplicationId() . " task</a><br>
                                    ------- <br>

                                    <br>
                                    ";

                            $mailnotifications = new mailnotifications();
                            $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);
                        }
                        foreach ($otherreviewers as $reviewer) {

                            if ($count == 0) {
                                $task = new Task();
                                $task->setType($request->getPostParameter("task_type"));
                                $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                                $task->setOwnerUserId($reviewer);
                                $task->setSheetId($request->getPostParameter("task_sheet"));
                                $task->setDuration("0");
                                $task->setStartDate($request->getPostParameter("start_date"));
                                $task->setEndDate($request->getPostParameter("end_date"));
                                $task->setPriority($request->getPostParameter("priority"));
                                $task->setIsLeader("0");
                                $task->setActive("1");
                                $task->setStatus("1");
                                $task->setLastUpdate(date('Y-m-d'));
                                $task->setDateCreated(date('Y-m-d'));
                                $task->setRemarks($request->getPostParameter("description"));
                                $task->setApplicationId($application);
                                $task->save();

                                //Audit 
                                Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                                $this->task = $task;
                                $previous_task_id = $task->getId();
                                $this->success = true;

                                $q = Doctrine_Query::create()
                                    ->from('FormEntry a')
                                    ->where('a.id = ?', $application);
                                $fullApplication = $q->fetchOne();

                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.nid = ?', $reviewer);
                                $treviewer = $q->fetchOne();

                                $audit = new Audit();
                                $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                            } else {
                                $task = new Task();
                                $task->setType($request->getPostParameter("task_type"));
                                $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                                $task->setOwnerUserId($reviewer);
                                $task->setSheetId($request->getPostParameter("task_sheet"));
                                $task->setDuration("0");
                                $task->setStartDate($request->getPostParameter("start_date"));
                                $task->setEndDate($request->getPostParameter("end_date"));
                                $task->setPriority($request->getPostParameter("priority"));
                                $task->setIsLeader("0");
                                if ($request->getPostParameter("workflow_type") == "1") {
                                    $task->setActive("0");
                                } else {
                                    $task->setActive("1");
                                }
                                $task->setStatus("1");
                                $task->setLastUpdate(date('Y-m-d'));
                                $task->setDateCreated(date('Y-m-d'));
                                $task->setRemarks($request->getPostParameter("description"));
                                $task->setApplicationId($application);
                                $task->save();

                                //Audit 
                                Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                                $this->task = $task;

                                $taskqueue = new TaskQueue();
                                $taskqueue->setCurrentTaskId($previous_task_id);
                                $taskqueue->setNextTaskId($task->getId());
                                $taskqueue->save();

                                $previous_task_id = $task->getId();
                                $this->success = true;


                                $q = Doctrine_Query::create()
                                    ->from('FormEntry a')
                                    ->where('a.id = ?', $application);
                                $fullApplication = $q->fetchOne();

                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.nid = ?', $reviewer);
                                $treviewer = $q->fetchOne();

                                $audit = new Audit();
                                $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                            }

                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $this->application = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from("CfUser a")
                                ->where("a.nid = ?", $reviewer);
                            $reviewer = $q->fetchOne();

                            $body = "
                                    Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                                    <br>
                                    You have been assigned a new task on " . $this->application->getApplicationId() . ":<br>
                                    <br><br>
                                    &ldquo; " . $request->getPostParameter("description") . " &rdquo;
                                    <br>
                                    <br>
                                    Click here to view the task: <br>
                                    ------- <br>
                                    <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/tasks/view/id/" . $this->task->getId() . "'>Link to " . $this->application->getApplicationId() . " task</a><br>
                                    ------- <br>

                                    <br>
                                    ";

                            $mailnotifications = new mailnotifications();
                            $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);
                            $count++;
                        }

                        foreach ($supportreviewers as $reviewer) {
                            if ($count == 0) {
                                $task = new Task();
                                $task->setType($request->getPostParameter("task_type"));
                                $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                                $task->setOwnerUserId($reviewer);
                                $task->setSheetId($request->getPostParameter("task_sheet"));
                                $task->setDuration("0");
                                $task->setStartDate($request->getPostParameter("start_date"));
                                $task->setEndDate($request->getPostParameter("end_date"));
                                $task->setPriority($request->getPostParameter("priority"));
                                $task->setIsLeader("0");
                                $task->setActive("1");
                                $task->setStatus("1");
                                $task->setLastUpdate(date('Y-m-d'));
                                $task->setDateCreated(date('Y-m-d'));
                                $task->setRemarks($request->getPostParameter("description"));
                                $task->setApplicationId($application);
                                $task->save();

                                //Audit 
                                Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                                $this->task = $task;
                                $previous_task_id = $task->getId();
                                $this->success = true;


                                $q = Doctrine_Query::create()
                                    ->from('FormEntry a')
                                    ->where('a.id = ?', $application);
                                $fullApplication = $q->fetchOne();

                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.nid = ?', $reviewer);
                                $treviewer = $q->fetchOne();

                                $audit = new Audit();
                                $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                            } else {
                                $task = new Task();
                                $task->setType($request->getPostParameter("task_type"));
                                $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                                $task->setOwnerUserId($reviewer);
                                $task->setSheetId($request->getPostParameter("task_sheet"));
                                $task->setDuration("0");
                                $task->setStartDate($request->getPostParameter("start_date"));
                                $task->setEndDate($request->getPostParameter("end_date"));
                                $task->setPriority($request->getPostParameter("priority"));
                                $task->setIsLeader("0");
                                if ($request->getPostParameter("workflow_type") == "1") {
                                    $task->setActive("0");
                                } else {
                                    $task->setActive("1");
                                }
                                $task->setStatus("1");
                                $task->setLastUpdate(date('Y-m-d'));
                                $task->setDateCreated(date('Y-m-d'));
                                $task->setRemarks($request->getPostParameter("description"));
                                $task->setApplicationId($application);
                                $task->save();

                                //Audit 
                                Audit::audit($application, "Assigned task to reviewer #" . $reviewer);

                                $this->task = $task;

                                $taskqueue = new TaskQueue();
                                $taskqueue->setCurrentTaskId($previous_task_id);
                                $taskqueue->setNextTaskId($task->getId());
                                $taskqueue->save();

                                $previous_task_id = $task->getId();
                                $this->success = true;


                                $q = Doctrine_Query::create()
                                    ->from('FormEntry a')
                                    ->where('a.id = ?', $application);
                                $fullApplication = $q->fetchOne();

                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.nid = ?', $reviewer);
                                $treviewer = $q->fetchOne();

                                $audit = new Audit();
                                $audit->saveFullAudit("<a href='/backend.php/tasks/view/id/" . $task->getId() . "'>Assigned " . $task->getTypeName() . " task on " . $fullApplication->getApplicationId() . " to " . $treviewer->getStrfirstname() . " " . $treviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);
                            }
                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $this->application = $q->fetchOne();

                            $q = Doctrine_Query::create()
                                ->from("CfUser a")
                                ->where("a.nid = ?", $reviewer);
                            $reviewer = $q->fetchOne();

                            $body = "
                                        Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                                        <br>
                                        You have been assigned a new task on " . $this->application->getApplicationId() . ":<br>
                                        <br><br>
                                        &ldquo; " . $request->getPostParameter("description") . " &rdquo;
                                        <br>
                                        <br>
                                        Click here to view the task: <br>
                                        ------- <br>
                                        <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/tasks/view/id/" . $this->task->getId() . "'>Link to " . $this->application->getApplicationId() . " task</a><br>
                                        ------- <br>

                                        <br>
                                        ";

                            $mailnotifications = new mailnotifications();
                            $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);
                            $count++;
                        }

                        if ($request->getPostParameter("task_type") == 2) {
                            $q = Doctrine_Query::create()
                                ->from('FormEntry a')
                                ->where('a.id = ?', $application);
                            $appentry = $q->fetchOne();
                            if ($appentry && $appentry->getApproved() == "852") {
                                $appentry->setApproved("853");
                                $appentry->save();
                            } else if ($appentry && $appentry->getApproved() == "861") {
                                $appentry->setApproved("862");
                                $appentry->save();
                            } else if ($appentry && $appentry->getApproved() == "902") {
                                $appentry->setApproved("903");
                                $appentry->save();
                            } else if ($appentry && $appentry->getApproved() == "912") {
                                $appentry->setApproved("913");
                                $appentry->save();
                            } else if ($appentry && $appentry->getApproved() == "922") {
                                $appentry->setApproved("923");
                                $appentry->save();
                            }
                        }

                        $q = Doctrine_Query::create()
                            ->from('FormEntry a')
                            ->where('a.id = ?', $application);
                        $appentry = $q->fetchOne();
                        if ($appentry && $appentry->getApproved() == "869") {
                            $appentry->setApproved("864");
                            $appentry->save();
                        } else if ($appentry && $appentry->getApproved() == "865") {
                            $appentry->setApproved("867");
                            $appentry->save();
                        }
                    }
                }
            }
        } else {
            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.id = ?', $request->getPostParameter("application"));
            $application = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $application->getApproved());
            $child_stage = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.menu_id = ?", $child_stage->getMenuId())
                ->orderBy("a.order_no ASC");
            $stages = $q->execute();

            foreach ($stages as $stage) {
                $reviewers = $request->getPostParameter("reviewers_" . $stage->getId());

                $count = 0;
                $previous_task_id = 0;
                foreach ($reviewers as $reviewer) {
                    $task = new Task();
                    $task->setType($request->getPostParameter("task_type"));
                    $task->setCreatorUserId($this->getUser()->getAttribute('userid'));
                    $task->setOwnerUserId($reviewer);
                    $task->setSheetId($request->getPostParameter("task_sheet"));
                    $task->setDuration("0");
                    $task->setStartDate($request->getPostParameter("start_date"));
                    $task->setEndDate($request->getPostParameter("end_date"));
                    $task->setPriority($request->getPostParameter("priority"));
                    $task->setIsLeader("0");
                    $task->setActive("1");
                    $task->setStatus("1");
                    $task->setLastUpdate(date('Y-m-d'));
                    $task->setDateCreated(date('Y-m-d'));
                    $task->setRemarks($request->getPostParameter("description"));
                    $task->setApplicationId($application->getId());
                    $task->setTaskStage($stage->getId());
                    $task->save();

                    //Audit 
                    Audit::audit($application->getId(), "Assigned task to reviewer #" . $reviewer);

                    $this->task = $task;
                    $this->success = true;
                }
            }
        }

        //Check the current stage for automatic triggers
        // if this is a dispatch stage, the application can either move to the next stage or send notifications
        if ($request->getPostParameter("application")) {
            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.id = ?', $request->getPostParameter("application"));
            $application = $q->fetchOne();

            if ($application) {
                $q = Doctrine_Query::create()
                    ->from('SubMenus a')
                    ->where('a.id = ?', $application->getApproved());
                $stage = $q->fetchOne();

                if ($stage && ($stage->getStageType() == 8 || $stage->getStageType() == 9)) {
                    if ($stage->getStageProperty() == 2) {
                        //Move application to another stage
                        $next_stage = $stage->getStageTypeMovement();
                        $application->setApproved($next_stage);
                        $application->save();
                    } elseif ($stage->getStageProperty() == 3) {
                        //Send notification to reviewers
                        $notification = $stage->getStageTypeNotification();

                        $q = Doctrine_Query::create()
                            ->from('CfUser a')
                            ->where('a.bdeleted = 0');
                        $reviewers = $q->execute();
                        foreach ($reviewers as $reviewer) {
                            $q = Doctrine_Query::create()
                                ->from('mfGuardUserGroup a')
                                ->leftJoin('a.Group b')
                                ->leftJoin('b.mfGuardGroupPermission c') //Left Join group permissions
                                ->leftJoin('c.Permission d') //Left Join permissions
                                ->where('a.user_id = ?', $reviewer->getNid())
                                ->andWhere('d.name = ?', "accesssubmenu" . $application->getApproved());
                            $usergroups = $q->execute();
                            if (sizeof($usergroups) > 0) {
                                $body = "
	                        Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
	                        <br>
	                        " . $notification . "

	                        <br>
	                        Click here to view the application: <br>
	                        ------- <br>
	                        <a href='http://" . $_SERVER['HTTP_HOST'] . "/backend.php/applications/view/id/" . $application->getId() . "'>Link to " . $application->getApplicationId() . "</a><br>
	                        ------- <br>

	                        <br>
	                        ";

                                $mailnotifications = new mailnotifications();
                                $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "Paid Invoice", $body);
                            }
                        }
                    }
                }
            }


            if ($this->success) {
                if ($request->getParameter("redirect")) {
                    //Mark initial task as complete and redirect
                    $q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("a.id = ?", $request->getParameter("redirect"));
                    $task = $q->fetchOne();

                    if ($task) {
                        $task->setStatus(25);
                        $task->save();
                    }

                    $this->redirect("/backend.php/tasks/view/id/" . $request->getParameter("redirect"));
                } else {
                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.nid = ?', $this->getUser()->getAttribute('userid'));
                    $reviewer = $q->fetchOne();
                    $this->redirect("/backend.php/applications/view/id/" . $request->getPostParameter("application"));
                }
            }
        } else {
            if ($this->success) {
                if ($request->getParameter("redirect")) {
                    //Mark initial task as complete and redirect
                    $q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("a.id = ?", $request->getParameter("redirect"));
                    $task = $q->fetchOne();

                    if ($task) {
                        $task->setStatus(25);
                        $task->save();
                    }

                    $this->redirect("/backend.php/tasks/view/id/" . $request->getParameter("redirect"));
                } else {
                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.nid = ?', $this->getUser()->getAttribute('userid'));
                    $reviewer = $q->fetchOne();
                    $this->redirect("/backend.php/tasks/department/filter/" . $reviewer->getStrdepartment());
                }
            }
        }
    }

    /**
     * Executes 'Saveinvoice' action
     *
     * Saves invoice details to the database
     *
     * @param sfRequest $request A request object
     */
    public function executeSaveinvoice(sfWebRequest $request)
    {
        $filename = $request->getPostParameter("filevalue");
        $originalfilename = $filename['0'];

        $client_ip = $_SERVER["REMOTE_ADDR"];

        if ($client_ip == "::1" || $client_ip == "") {
            $client_ip = "127.0.0.1";
        }

        $filename = $client_ip . $filename[0];

        $q = Doctrine_Query::create()
            ->from('Task a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->task = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $this->task->getApplicationId());
        $this->application = $q->fetchOne();

        $descriptions = $request->getPostParameter("feetitle");

        $updated_descriptions = array();

        foreach ($descriptions as $description) {
            if ($description != __("Choose Fee")) {
                $updated_descriptions[] = $description;
            }
        }

        $descriptions = $updated_descriptions;

        $amounts = $request->getPostParameter("feevalue");

        $invoice_manager = new InvoiceManager();

        $invoice = $invoice_manager->create_invoice_from_task($this->application->getId(), $descriptions, $amounts, true);

        if (empty($invoice)) {
            $q = Doctrine_Query::create()
                ->from("MfInvoice a")
                ->where("a.app_id = ?", $this->application->getId())
                ->orderBy("a.id DESC");
            $invoice = $q->fetchOne();
        }

        //Audit 
        Audit::audit($this->application->getId(), "Generated a new invoice #" . $invoice->getId());

        $this->task->setStatus('25');

        $this->task->setEndDate(date('Y-m-d'));
        $this->task->save();

        $application = $invoice_manager->get_application_by_id($this->task->getApplicationId());

        $q = Doctrine_Query::create()
            ->from('Task a')
            ->where('a.application_id = ?', $this->task->getApplicationId())
            ->andWhere('a.task_stage = ?', $application->getApproved())
            ->andWhere('a.status = 1 OR a.status = 2 OR a.status = 3 OR a.status = 4 OR a.status = 5');
        $tasks = $q->execute();

        //Commented to decide whether after invoicing the application should stick in the same stage or move. Commented to reduce possible client complaints due to mistakes
        if (sizeof($tasks) == 0) {
            //If there no more tasks, check stage of assessment settings or move application to the next stage by default
            $q = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.id = ?", $application->getApproved());
            $stage = $q->fetchOne();

            if ($stage && $stage->getStageType() == 2) {
                if ($stage->getStageProperty() == 2) {
                    //Move application to another stage
                    $next_stage = $stage->getStageTypeMovement();
                    $application->setApproved($next_stage);
                    $application->save();
                }
            }
        }

        if ($request->getPostParameter("submit")) {
            $this->redirect($request->getPostParameter("submit"));
        } else {
            $this->redirect('/backend.php/tasks/view/id/' . $this->task->getId());
        }
    }

    /**
     * Executes 'Cancel' action
     *
     * Cancel a task
     *
     * @param sfRequest $request A request object
     */
    public function executeCancel(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('Task a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->task = $q->fetchOne();

        if ($this->getUser()->mfHasCredential("has_hod_access") || $this->task->getCreatorUserId() && $this->task->getStatus() == 1) {
            //Audit 
            Audit::audit("", "Cancelled task #" . $request->getParameter("id"));

            $this->task->setStatus(55);
            $this->task->save();

            $application = $this->task->getApplication();
            $application->setAssessmentInprogress(0);
            $application->save();

            if ($request->getParameter("redirect")) {
                $this->redirect("/backend.php/dashboard");
            } else {
                $this->redirect("/backend.php/users/viewuser/userid/" . $this->task->getOwnerUserId());
            }
        }
    }
    /**
     * OTB ADD Toggle decline called via ajax
     * 
     */
    public function executeToggledecline(sfWebRequest $request)
    {
        $entry_id = $request->getParameter("id");
        error_log("Entry decline ID >>>>" . $entry_id);
        $resolved = $request->getParameter("resolved");
        error_log("Resolved Id >>>>" . $resolved);
        $q = Doctrine_Query::create()
            ->update("EntryDecline d")
            ->set("d.resolved ", "?", $resolved)
            ->where("d.id = ? ", $entry_id);
        $q->execute();
        exit();
    }
    //OTB ADD
    public function executeMessaging(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry f')
            ->where('f.id = ?', $request->getParameter('id'));
        $application = $q->fetchOne();

        $this->forward404Unless($request->isMethod('POST') && $application, sprintf('Either not a post or application %s doesn\'t exist', $request->getParameter('id')));
        $q = Doctrine_Query::create()
            ->from("SfGuardUserProfile a")
            ->where("a.user_id = ?", $application->getUserId());
        $user_profile = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $this->getUser()->getAttribute("userid"));
        $reviewer = $q->fetchOne();

        if ($request->getPostParameter("txtmessage")) {
            //Audit
            Audit::audit($application->getId(), "Sent a message");

            //If the user is reply then mark the messages as read
            $q = Doctrine_Query::create()
                ->from("Communications a")
                ->Where('a.messageread = ?', '0')
                ->andWhere('a.application_id = ?', $application->getId());
            $messages = $q->execute();
            foreach ($messages as $message) {
                if ($message->getReviewerId() == "") {
                    $message->setMessageread("1");
                    $message->save();
                }
            }

            $message = new Communications();
            $message->setReviewerId($this->getUser()->getAttribute("userid"));
            $message->setMessageread("0");
            $message->setContent(trim($request->getPostParameter("txtmessage")));
            $message->setApplicationId($application->getId());
            $message->setActionTimestamp(date('c'));
            $message->save();


            $body = "
            Hi " . $user_profile->getFullname() . ",<br>
            <br>
            You have received a new message on " . $application->getApplicationId() . " from " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . " (" . $reviewer->getStrdepartment() . "):<br>
            <br><br>
            -------
            <br>
            " . trim($request->getPostParameter("txtmessage")) . "
            <br>
            <br>
            Click here to view the application: <br>
            ------- <br>
            <a href='http://" . $_SERVER['HTTP_HOST'] . "/plan/application/view/id/" . $application->getId() . "'>Link to " . $application->getApplicationId() . "</a><br>
            ------- <br>

            <br>
            ";

            $mailnotifications = new mailnotifications();
            $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $user_profile->getEmail(), "New Message", $body);
            echo json_encode(array('success' => true, 'message' => array('name' => $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname(), 'content' => trim($request->getPostParameter("txtmessage")), 'time' => $message->getActionTimestamp())));
        } elseif ($request->getPostParameter("txtmemo")) {
            //Audit
            Audit::audit($application->getId(), "Sent a memo");
            $message = new Communication();
            $message->setSender($this->getUser()->getAttribute("userid"));
            $message->setIsread("0");
            $message->setMessage($request->getPostParameter("txtmemo"));
            $message->setApplicationId($application->getId());
            $message->setCreatedOn(date('c'));
            $message->save();
            echo json_encode(array('success' => true, 'message' => array('name' => $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname(), 'content' => trim($request->getPostParameter("txtmemo")), 'time' => $message->getCreatedOn())));
        } else {
            echo json_encode(array('success' => false));
        }

        exit;
    }


    public function executeMarkAsRead(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry f')
            ->where('f.id = ?', $request->getParameter('id'));
        $application = $q->fetchOne();

        $this->forward404Unless($request->isMethod('GET') && $application, sprintf('Either not a post or application %s doesn\'t exist', $request->getParameter('id')));

        $q = Doctrine_Query::create()
            ->from("Communications a")
            ->Where('a.messageread = ?', 0)
            ->andWhere('a.application_id = ?', $application->getId());
        $messages = $q->execute();
        Audit::audit($application->getId(), "Mark Message as read");
        foreach ($messages as $message) {
            if (empty($message->getReviewerId()) || is_null($message->getReviewerId())) {
                $message->setMessageread("1");
                $message->save();
            }
        }

        return $this->renderText(json_encode(array('success' => true, 'message' => "Updated successfully")));
    }
}
