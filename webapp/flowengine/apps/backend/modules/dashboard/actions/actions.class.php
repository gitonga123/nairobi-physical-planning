<?php

/**
 * Dashboard actions.
 *
 * Displays a summary of all application related information for the reviewers
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class dashboardActions extends sfActions
{
    /**
     * Executes 'Index' action
     *
     * Displays a summary of all application related information for the reviewers
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        //If this is the first run after installation then display the wizard, else display the dashboard
        $wizard_manager = new WizardManager();

        if ($wizard_manager->is_first_run()) {
            $this->first_run = true;
            $this->wizard_manager = $wizard_manager;

            //Wizard Queries
            $q = Doctrine_Query::create()
                ->from("MfGuardGroup a")
                ->orderBy("a.id ASC");
            $this->groups = $q->execute();

            $q = Doctrine_Query::create()
                ->from("MfGuardPermission a")
                ->orderBy("a.name ASC");
            $this->permissions = $q->execute();

            $q = Doctrine_Query::create()
                ->from("Department a")
                ->orderBy("a.department_name ASC");
            $this->departments = $q->execute();

            $q = Doctrine_Query::create()
                ->from("Menus a")
                ->orderBy("a.id DESC");
            $this->workflow = $q->fetchOne();
        } else {
            $this->first_run = false;
            $current_reviewer = Functions::current_user();

            if ($current_reviewer == null) {
                $this->redirect("/backend.php/login/logout");
            }

            $this->logged_user = $current_reviewer;

            //Get all stages the current reviewer is allowed to access and stages must have allowed task
            $allowed_stages = Functions::get_allowed_stages();

            $allowed_stages_params = implode(" OR b.approved = ", $allowed_stages);

            $q = null;
            // count list of active users 
            $q_applicants = Doctrine_Query::create()
                ->from("sfGuardUser a")
                ->where("a.is_active = ? ", 1);
            $this->total_count = $q_applicants->count();
            //My Tasks 
            $account_params = array_map(function ($m) {
                return $m['group_id'];
            }, Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT DISTINCT group_id FROM mf_guard_user_group WHERE user_id = " . $current_reviewer->getNid()));
            $glue = " OR group_id = ? ";
            $k = implode($glue, array_map(function () {
                return '';
            }, $account_params)) . $glue;
            array_unshift($account_params, $current_reviewer->getNid());

            $q = Doctrine_Query::create()
                ->from("Task a")
                ->leftJoin('a.Application b')
                ->where("a.owner_user_id = ? " . $k, $account_params)
                ->andWhere("a.task_stage = b.approved")
                ->andWhere("a.status = ?", 1)
                ->orderBy('a.id DESC');

            if ($q->count() == 0) {
                $q = Doctrine_Query::create()
                    ->from("Task a")
                    ->where("status IN (1,2) AND (owner_user_id = " . $current_reviewer->getNid() . " OR group_id IN (SELECT GROUP_CONCAT(m.group_id SEPARATOR ',') FROM MfGuardUserGroup m WHERE user_id = " . $current_reviewer->getNid() . "))")
                    ->orderBy('a.id DESC');

                $this->my_tasks_stats = $q->count();
            } else {
                $this->my_tasks_stats = $q->count();
            }
            $app_list = [];

            //Completed Tasks (Today)
            $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.owner_user_id = ?", $current_reviewer->getNid())
                ->andWhere("a.status = ?", 25)
                ->andWhere("a.end_date LIKE ?", date("Y-m-d") . "%")
                ->orderBy('a.id DESC');
            $this->completed_tasks_stats = $q->count();

            //New Messages
            $q = Doctrine_Query::create()
                ->from('Communications a')
                ->leftJoin('a.FormEntry b')
                ->where("b.approved = " . $allowed_stages_params)
                ->andWhere('a.architect_id <> ?', "")
                ->andWhere('a.messageread = ?', 0)
                ->orderBy("a.id DESC");
            $this->new_messages_stats = $q->count();

            $this->filter = "";
            if ($request->getParameter("filter") && $request->getParameter("filter") != 0) {
                $this->filter = "/filter/" . $request->getParameter("filter");
            } else {
                $this->filter = "/filter/" . $allowed_stages[0];
            }

            if ($request->getParameter("current") == "available" || empty($request->getParameter("current"))) {

                $this->current_tab = "available";
                $this->stage_id = 0;
                $this->stage_type = 0;
            } else {
                if ($request->getParameter("current") == "inspections") {
                    $allowed_inspection_stages_params = null;

                    $department_id = $current_reviewer->getStrdepartment();

                    if (!is_numeric($department_id)) {
                        $q = Doctrine_Query::create()
                            ->from("Department a")
                            ->where("a.department_name = ?", $department_id);
                        $department_id = $q->fetchOne()->getId();
                    }

                    $q = Doctrine_Query::create()
                        ->from("ServiceInspections a")
                        ->where("a.department_id = ?", $department_id);
                    $allowed_inspections = $q->execute();

                    foreach ($allowed_inspections as $allowed_inspection) {
                        //if($this->getUser()->mfHasCredential("accesssubmenu".$allowed_inspection->getStageId()))
                        {
                            $allowed_inspection_stages[] = $allowed_inspection->getStageId();
                        }
                    }

                    $allowed_inspection_stages_params = implode(" OR a.approved = ", $allowed_inspection_stages);

                    if (sizeof($allowed_inspection_stages) > 0) {
                        //Get all applications in those stages that are not flagged as assessment in progress 
                        $this->q = Doctrine_Query::create()
                            ->from("FormEntry a")
                            ->leftJoin("a.Inspections b")
                            ->where("a.approved = " . $allowed_inspection_stages_params)
                            ->andWhere("b.department_id <> ?", $department_id)
                            ->orderBy('a.id DESC');
                    } else {
                        //Get all applications in those stages that are not flagged as assessment in progress 
                        $this->q = Doctrine_Query::create()
                            ->from("FormEntry a")
                            ->andWhere("a.approved = -1")
                            ->orderBy('a.id DESC');
                    }
                    $this->current_paginator = new sfDoctrinePager('FormEntry', 20);

                    $this->current_tab = "inspections";
                } elseif ($request->getParameter("current") == "queued") {
                    $this->q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("status IN (1,2) AND (owner_user_id = " . $current_reviewer->getNid() . " OR group_id IN (SELECT GROUP_CONCAT(m.group_id SEPARATOR ',') FROM MfGuardUserGroup m WHERE user_id = " . $current_reviewer->getNid() . "))")
                        ->orderBy('a.id DESC');
                    $this->current_paginator = new sfDoctrinePager('Task', 5);

                    $this->current_tab = "queued";
                } elseif ($request->getParameter("current") == "completed") {
                    $this->q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("a.owner_user_id = ?", $current_reviewer->getNid())
                        ->andWhere("a.status = ?", 25)
                        ->orderBy('a.id DESC');
                    $this->current_paginator = new sfDoctrinePager('Task', 5);

                    $this->current_tab = "completed";
                } elseif ($request->getParameter("current") == "messages") {
                    $this->q = Doctrine_Query::create()
                        ->from('Communications a')
                        ->leftJoin('a.FormEntry b')
                        ->where("b.approved = " . $allowed_stages_params)
                        ->andWhere('a.architect_id <> ?', "")
                        ->andWhere('a.messageread = ?', 0)
                        ->orderBy("a.id DESC");
                    $this->current_paginator = new sfDoctrinePager('Communications', 10);

                    $this->current_tab = "messages";
                }

                $this->page = $request->getParameter('page', 1);

                $this->current_paginator->setQuery($this->q);
                $this->current_paginator->setPage($request->getParameter('page', 1));
                $this->current_paginator->init();
            }

            if ($request->isXmlHttpRequest() || $request->getParameter('draw')) {
                //COLUMNS
                $columns = array('a.id', 'a.application_id', 's.title', 'a.date_of_submission', 'p.fullname');
                $q = $this->_entiresQuery($columns, $request, $app_list);
                $q_extra = $this->_entiresQuery(null, $request, $app_list);
                $result = array(
                    "draw" => intval($request->getParameter('draw')),
                    "recordsTotal" => $q_extra ? $q_extra->count() : "",
                    "recordsFiltered" => $q->count(),
                    "data" => []
                );
                if (!empty($request->getParameter('order'))) {
                    //ORDER
                    $q->orderBy($columns[$request->getParameter('order')[0]['column']] . ' ' . $request->getParameter('order')[0]['dir']);
                }
                //For pagination
                $q->offset($request->getParameter('start'));
                $q->limit($request->getParameter('length'));
                // error_log('query ------------' . $q->getSqlQuery());
                error_log('Results ------------' . $q->count());
                $applications = $q->execute();
                error_log("execution results count " . count($applications));

                $helper = new OTBHelper();
                foreach ($applications as $application) {
                    $days_in_stage = $helper->getAppStageStayedDays($application->getApproved(), $application->getId());
                    $cl_date_highlight = '';
                    $max_days = $helper->getStageMaxDays($application->getApproved());
                    $diff_max_day = $max_days - $days_in_stage;
                    if ($max_days > 0) {
                        if ($diff_max_day < 0) {
                            $cl_date_highlight = 'danger';
                        } elseif ($diff_max_day <= 10 && $diff_max_day > 0) {
                            $cl_date_highlight = 'warning';
                        } else {
                            $cl_date_highlight = 'info';
                        }
                    }
                    $data = new stdClass;
                    $data->id = $application->getId();
                    $data->application_id = $application->getApplicationId();
                    $data->stage = $application->getStatusName();
                    $data->date_submitted = date('jS M Y H:i:s', strtotime($application->getDateOfSubmission()));
                    $data->applicant = !is_null($application->getSfGuardUser()->getProfile()) ? $application->getSfGuardUser()->getProfile()->getFullname() : "";
                    $data->date_highlight = $cl_date_highlight;
                    $data->days_in_stage = $days_in_stage;
                    $data->service = $application->getStage()->getMenus()->getTitle();
                    $result['data'][] = $data;
                }

                $this->getResponse()->setContent(json_encode($result));
                return sfView::NONE;
            }
        }
        // set custom layout
        //$this->setLayout('layout-admin-dashboard') ;
    }
    /**
     * application settings 
     */
    public function executeApplications(sfWebRequest $request)
    {
    }
    /**
     * Executes 'Profile' action
     *
     * Edit your account settings
     *
     * @param sfRequest $request A request object
     */
    public function executeProfile(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $this->getUser()->getAttribute("userid"));
        $this->reviewer = $q->fetchOne();

        $this->currentpage = $request->getParameter('currentpage', 1);
        $this->completedpage = $request->getParameter('completepage', 1);
        $this->cancelpage = $request->getParameter('cancelpage', 1);
        $this->auditpage = $request->getParameter('auditpage', 1);

        //Update user record if post params are found
        if ($request->getPostParameter("first_name")) {
            //Audit 
            Audit::audit("", "Updated user details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrfirstname($request->getPostParameter("first_name"));
            $this->reviewer->setStrlastname($request->getPostParameter("last_name"));
            $this->reviewer->setStruserid($request->getPostParameter("id_number"));
            $this->reviewer->setStremail($request->getPostParameter("email"));
            $this->reviewer->setStrphoneMain1($request->getPostParameter("phone_number"));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("new_password") && ($request->getPostParameter("new_password") == $request->getPostParameter("confirm_password"))) {
            //Audit 
            Audit::audit("", "Updated password details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrpassword(password_hash($request->getPostParameter("new_password"), PASSWORD_BCRYPT));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("country")) {
            //Audit 
            Audit::audit("", "Updated other details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrcountry($request->getPostParameter("country"));
            $this->reviewer->setStrcity($request->getPostParameter("city"));
            $this->reviewer->setUserdefined1Value($request->getPostParameter("designation"));
            $this->reviewer->setUserdefined2Value($request->getPostParameter("mannumber"));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("department")) {
            //Audit 
            Audit::audit("", "Updated department details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrdepartment($request->getPostParameter("department"));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("groups")) {
            //Audit 
            Audit::audit("", "Updated group details for reviewer #" . $this->reviewer->getNid());

            $q = Doctrine_Query::Create()
                ->from('mfGuardUserGroup a')
                ->where('a.user_id = ?', $this->reviewer->getNid());
            $usergroups = $q->execute();
            if ($usergroups) {
                foreach ($usergroups as $usergroup) {
                    $usergroup->delete();
                }
            }

            $groups = $request->getPostParameter("groups");
            foreach ($groups as $group) {
                $usergroup = new MfGuardUserGroup();
                $usergroup->setUserId($this->reviewer->getNid());
                $usergroup->setGroupId($group);
                $usergroup->save();
            }
        }

        //Current tasks
        $this->q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $this->reviewer->getNid())
            ->andWhere("a.status = ? OR a.status = ?", array(1, 2));
        $this->current_paginator = new sfDoctrinePager('Task', 5);
        $this->current_paginator->setQuery($this->q);
        $this->current_paginator->setPage($this->currentpage);
        $this->current_paginator->init();

        //Completed tasks
        $this->q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $this->reviewer->getNid())
            ->andWhere("a.status = ?", 25);
        $this->completed_paginator = new sfDoctrinePager('Task', 5);
        $this->completed_paginator->setQuery($this->q);
        $this->completed_paginator->setPage($this->completedpage);
        $this->completed_paginator->init();

        //Cancelled tasks
        $this->q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $this->reviewer->getNid())
            ->andWhere("a.status = ?", 35);
        $this->cancel_paginator = new sfDoctrinePager('Task', 5);
        $this->cancel_paginator->setQuery($this->q);
        $this->cancel_paginator->setPage($this->cancelpage);
        $this->cancel_paginator->init();

        //Audit log
        $this->q = Doctrine_Query::create()
            ->from("AuditTrail a")
            ->where("a.user_id = ?", $this->reviewer->getNid())
            ->orderBy("a.id DESC");
        $this->audit_paginator = new sfDoctrinePager('AuditTrail', 5);
        $this->audit_paginator->setQuery($this->q);
        $this->audit_paginator->setPage($this->auditpage);
        $this->audit_paginator->init();
    }

    /**
     * Executes 'Saveprofile' action
     *
     * Save your account settings
     *
     * @param sfRequest $request A request object
     */
    public function executeSaveprofile(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $this->getUser()->getAttribute("userid"));
        $logged_reviewer = $q->fetchOne();

        $this->form = new CfUserForm($logged_reviewer);

        $this->processForm($request, $this->form, $logged_reviewer);

        $this->setTemplate('editprofile');
    }

    protected function processForm(sfWebRequest $request, sfForm $form, CfUser $user)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $params = $request->getPostParameter("cf_user");

            $user->setStrfirstname($params['strfirstname']);
            $user->setStrlastname($params['strlastname']);
            $user->setStruserid($params['struserid']);
            $user->setStremail($params['stremail']);
            $user->setStrphoneMobile($params['strphone_mobile']);
            $user->setStrcountry($params['strcountry']);
            $user->setStrcity($params['strcity']);
            $user->setUserdefined1Value($params['struserdefined1_value']);
            $user->setUserdefined2Value($params['struserdefined2_value']);
            $user->save();

            $this->redirect('/backend.php/dashboard');
        }
    }
    private function _entiresQuery($cols = null, $request = null, $app_list = [])
    {
        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->leftJoin("a.SfGuardUser u")
            ->leftJoin("u.Profile p")
            ->leftJoin("a.Stage s")
            ->where('a.deleted_status = ?', 0);
        $allowed_stages = Functions::get_allowed_stages();
        if (empty($allowed_stages)) {
            $q->andWhereIn('s.id', [0]);
            return $q;
        }
        error_log("App list is below ---->");
        error_log(print_r($app_list, true));
        if ($request->getParameter("current") == "available" || empty($request->getParameter("current"))) {
            error_log("We are in the current ---->");
            if (sizeof($app_list) > 0) {
                error_log("App list is greater than 1 ---->" . count($app_list));
                $q->whereIn('a.id', $app_list);
            } else if ($request->getParameter("filter") && $request->getParameter("filter") != 0) {
                error_log("Filter items is ---->");
                error_log($request->getParameter("filter"));
                error_log(print_r($allowed_stages, true));
                $q->where("a.approved = ? and a.deleted_status = ? and a.parent_submission =?", [$request->getParameter("filter"), 0, 0])
                    ->andWhereIn('s.id', $allowed_stages);
            } else {
                error_log("Allowed stages ---->");
                error_log(print_r($allowed_stages));
                if (sizeof($allowed_stages) > 0) {
                    $q->where("a.approved = ? and a.deleted_status = ? and a.parent_submission =?", [$allowed_stages[0], 0, 0]);
                }
            }
        }

        error_log("\n\n");
        if (null === $cols || empty($cols)) return $q;

        $search = $request->getParameter('search')['value'];

        if ("" === $search || empty($search)) return $q;
        $sql = [];
        $params = [];


        foreach ($cols as $i => $col) {
            $sql[] = $col . " LIKE ?";
            $params[] = '%' . $search . '%';
        }
        $q->addWhere("(" . implode(" OR ", $sql) . ")", $params);
        return $q;
    }
}
