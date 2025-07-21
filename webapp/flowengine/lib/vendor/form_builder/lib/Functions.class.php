<?php

class Functions
{

    //Format numbers for statistics nicely by rounding off
    public static function bd_nice_number($n)
    {
        // first strip any formatting;
        $n = (0 + str_replace(",", "", $n));

        // is this a number?
        if (!is_numeric($n))
            return false;

        // now filter it;
        if ($n > 1000000000000)
            return round(($n / 1000000000000), 1) . ' T';
        else if ($n > 1000000000)
            return round(($n / 1000000000), 1) . ' B';
        else if ($n > 1000000)
            return round(($n / 1000000), 1) . ' M';

        return number_format($n);
    }

    //Get the currently logged in reviewer
    public static function current_user()
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', sfContext::getInstance()->getUser()->getAttribute('userid'));
        return $q->fetchOne();
    }

    //Get the site settings
    public static function site_settings()
    {
        $q = Doctrine_Query::create()
            ->from("ApSettings a");
        return $q->fetchOne();
    }

    //Get number of days since a given date
    public static function get_days_since($sStartDate, $sEndDate)
    {
        $start_ts = new DateTime($sStartDate);
        $end_ts = new DateTime($sEndDate);
        $diff = $start_ts->diff($end_ts)->d;
        return $diff;
    }

    //Get number of months since a given date
    public static function get_months_since($sStartDate, $sEndDate)
    {
        $start_ts = new DateTime($sStartDate);
        $end_ts = new DateTime($sEndDate);
        $diff = $start_ts->diff($end_ts)->m;
        return $diff;
    }

    //Get all services that current user is allowed to access
    public static function get_allowed_services()
    {
        //OTB ADD Agency
        $agency = new AgencyManager();
        $menus = $agency->getAllowedServices(sfContext::getInstance()->getUser()->getAttribute('userid'));
        $allowed_services = array();

        $q = Doctrine_Query::create()
            ->from('Menus a')
            ->whereIn('a.id', $menus)
            ->orderBy('a.order_no ASC');
        $services = $q->execute();
        foreach ($services as $service) {
            if (sfContext::getInstance()->getUser()->mfHasCredential('accessmenu' . $service->getId())) {
                $allowed_services[] = $service;
            }
        }

        return $allowed_services;
    }
    //Get all services from workflow categories
    public static function get_allowed_category_services()
    {
        //OTB ADD Agency
        $agency = new AgencyManager();
        $menus = $agency->getAllowedServices(sfContext::getInstance()->getUser()->getAttribute('userid'));
        $categories = array();

        $q = Doctrine_Query::create()
            ->from('WorkflowCategory c')
            ->leftJoin('c.Menus a')
            ->whereIn('a.id', $menus)
            ->orderBy('a.order_no ASC');
        $categories = $q->execute();

        return $categories;
    }

    //Get all allowed stages the user is allowed to access
    public static function get_allowed_stage_models($workflow_id)
    {
        $allowed_stages = array();
        //OTB ADD Agency
        $agency = new AgencyManager();
        $agency_stages = $agency->getAllowedStages(sfContext::getInstance()->getUser()->getAttribute('userid'));
        $q = Doctrine_Query::create()
            ->from('SubMenus a')
            ->where('a.menu_id = ?', $workflow_id)
            ->andWhereIn('a.id', $agency_stages)
            ->andWhere('a.deleted = 0')
            ->orderBy('a.order_no ASC');
        $stages = $q->execute();

        foreach ($stages as $stage) {
            if (sfContext::getInstance()->getUser()->mfHasCredential('accesssubmenu' . $stage->getId())) {
                $allowed_stages[] = $stage;
            }
        }

        return $allowed_stages;
    }

    //Get the first stage of a service
    public static function get_allowed_service_first_stage($service_id)
    {
        //OTB ADD Agency
        $agency = new AgencyManager();
        $agency_stages = $agency->getAllowedStages(sfContext::getInstance()->getUser()->getAttribute('userid'));
        $q = Doctrine_Query::create()
            ->from('SubMenus a')
            ->where('a.menu_id = ?', $service_id)
            ->andWhereIn('a.id', $agency_stages)
            ->andWhere('a.deleted = 0')
            ->orderBy('a.order_no ASC');
        $stages = $q->execute();
        foreach ($stages as $stage) {
            if (sfContext::getInstance()->getUser()->mfHasCredential('accesssubmenu' . $stage->getId())) {
                return $stage;
            }
        }

        //If no stages are accessible then return false
        return false;
    }

    //Get all allowed stages the user is allowed to access
    public static function get_allowed_stages()
    {
        //OTB ADD Agency
        $agency = new AgencyManager();
        $menus = $agency->getAllowedServices(sfContext::getInstance()->getUser()->getAttribute('userid'));
        $allowed_stages = array();

        $q = Doctrine_Query::create()
            ->from('Menus a')
            ->whereIn('a.id', $menus)
            ->orderBy('a.title ASC');
        $services = $q->execute();
        foreach ($services as $service) {
            if (sfContext::getInstance()->getUser()->mfHasCredential('accessmenu' . $service->getId())) {
                //OTB ADD Agency
                $agency_stages = $agency->getAllowedStages(sfContext::getInstance()->getUser()->getAttribute('userid'));
                $q = Doctrine_Query::create()
                    ->from('SubMenus a')
                    ->where('a.menu_id = ?', $service->getId())
                    ->andWhereIn('a.id', $agency_stages)
                    ->andWhere('a.deleted = 0')
                    ->orderBy('a.order_no ASC');
                $stages = $q->execute();
                foreach ($stages as $stage) {
                    if (sfContext::getInstance()->getUser()->mfHasCredential('accesssubmenu' . $stage->getId())) {
                        $allowed_stages[] = $stage->getId();
                    }
                }
            }
        }

        return $allowed_stages;
    }

    //Get list of available languages
    public static function get_languages()
    {
        $q = Doctrine_Query::create()
            ->from('ExtLocales a')
            ->orderBy("a.local_title ASC");
        return $q->execute();
    }

    //Check if logged in client can create business profiles
    public static function client_can_add_businesses()
    {
        //OTB needed if SBP
        /*$user = sfContext::getInstance()->getUser()->getGuardUser();

        $q = Doctrine_Query::create()
            ->from('SfGuardUserCategories a')
            ->where("a.id = ?", $user->getProfile()->getRegisteras());
        $category = $q->fetchOne();

        if($category && $category->getFormid() == 0)
        {*/
        return false;
        /*}
        else 
        {
            return true;
        }*/
    }

    //Return the category of the client
    public static function get_client_category()
    {
        $user = sfContext::getInstance()->getUser()->getGuardUser();

        $q = Doctrine_Query::create()
            ->from('SfGuardUserCategories a')
            ->where("a.id = ?", $user->getProfile()->getRegisteras());
        $category = $q->fetchOne();

        return $category;

    }

    //Check if the client has a profile
    public static function client_has_profile()
    {
        $user = sfContext::getInstance()->getUser()->getGuardUser();

        $q = Doctrine_Query::create()
            ->from('MfUserProfile a')
            ->where("a.user_id = ?", $user->getId());
        if ($q->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    //Get the profile
    public static function get_client_profile($id)
    {
        $q = Doctrine_Query::create()
            ->from('MfUserProfile a')
            ->where("a.id = ?", $id);
        if ($q->count() > 0) {
            return $q->fetchOne();
        } else {
            return false;
        }
    }

    //Get current profile
    public static function get_current_profile()
    {
        $q = Doctrine_Query::create()
            ->from('MfUserProfile a')
            ->where("a.id = ?", sfContext::getInstance()->getUser()->getAttribute("current_profile"));
        if ($q->count() > 0) {
            return $q->fetchOne();
        } else {
            return false;
        }
    }

    //Find a substring in a string
    public function find($needle, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function has_accessible_forms()
    {
        $count = 0;

        $q = Doctrine_Query::create()
            ->from('FormGroups a')
            ->orderBy('a.group_name ASC');
        $groups = $q->execute();

        foreach ($groups as $group) {
            $q = Doctrine_Query::create()
                ->from('ApForms a')
                ->where('a.form_group = ?', $group->getGroupId())
                ->andWhere('a.form_type = 1')
                ->andWhere('a.form_active = 1')
                ->orderBy('a.form_name ASC');

            foreach ($q->execute() as $form) {
                //Check if enable_categories is set, if it is then filter application forms
                if (sfConfig::get('app_enable_categories') == "yes") {
                    $q = Doctrine_Query::create()
                        ->from('sfGuardUserCategoriesForms a')
                        ->where('a.categoryid = ?', sfContext::getInstance()->getUser()->getGuardUser()->getProfile()->getRegisteras())
                        ->andWhere('a.formid = ?', $form->getFormId());
                    $category = $q->count();

                    if ($category == 0) {
                        continue;
                    } else {
                        $count++;
                    }
                } else {
                    //If form category permissions is disabled and then just display the category
                    $count++;
                }
            }
        }

        if ($count > 0) {
            return true;
        } else {
            return false;
        }

    }
    public static function saveUnsignedPermit($permit)
    {
        $permit_manager = new PermitManager();
        $file_name = $permit_manager->permit_file_name($permit);

        $file_path = "app/permits/unsigned/$file_name";

        if (file_exists($file_path)) {

        } else {
            $output = $permit_manager->get_pdf_output($permit->getId());
            $file = fopen("app/permits/unsigned/$file_name", 'w');
            fwrite($file, $output);
            fclose($file);
        }

        return $file_path;
    }

    public static function isSignable($permitTypeId): bool
    {
        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where('a.allows_signing = ?', "1")
            ->andwhere("a.id = ?", $permitTypeId)->fetchArray();
        return count($q);
    }

    public static function getAbsoluteFilePath($query)
    {
        $query = base64_decode($query);
        $params = [];
        array_map(function ($k) use (&$params) {
            $k = explode('=', $k);
            return $params[$k[0]] = $k[1];
        }, explode('&', $query));

        //        form_id=9772&id=32&el=element_11&hash=163635e65838a486bce91b7fb5d5df19
        $el = $params['el'];
        $form_id = $params['form_id'];
        $id = $params['id'];

        $q = "SELECT $el FROM ap_form_$form_id WHERE id = $id";
        $file_name = Doctrine_Manager::getInstance()->getCurrentConnection()
            ->fetchAssoc($q)[0][$el];

        return "asset_data/form_$form_id/files/$file_name";
    }

    public static function canSignAttachmentField($form_id, $element_id): bool
    {
        if (!$element_id)
            return false;
        $user_id = self::current_user()->getNid();

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();

        $r = $conn->fetchAssoc("SELECT group_id FROM mf_guard_user_group m WHERE user_id = $user_id");
        $group_ids = implode("", array_map(function ($k) {
            return ' OR group_id = ' . $k['group_id'];
        }, $r));
        $r = $conn->fetchAssoc("SELECT id FROM signable_attachments_fields WHERE form_id = $form_id"
            . " AND element_id = $element_id AND (user_id = $user_id $group_ids)");

        ////////


        $k = <<<EOL
SELECT id FROM signable_attachments_fields
    WHERE form_id = $form_id AND element_id = $element_id (group_id IN (SELECT group_id FROM mf_guard_user_group mgup WHERE user_id = $user_id)
    OR user_id = $user_id) 
EOL;


        return count($r) > 0;
    }

    /**
     * Fields that are configured for me to sign for this form
     *
     * @param $form_id
     * @return array
     * @throws Doctrine_Connection_Exception
     */
    public static function fields_i_can_sign_for_this_form($form_id)
    {
        if ($me = self::current_user()) {
            $me = $me->getNid();
            $q = <<<EOL
SELECT element_id, element_title FROM ap_form_elements
WHERE form_id = $form_id
  AND element_id IN (SELECT element_id
                     FROM signable_attachments_fields
                     WHERE form_id = $form_id
                       AND (group_id IN (SELECT group_id FROM mf_guard_user_group mgup WHERE user_id = $me) OR
                            user_id = $me))
EOL;

            $q = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
            return array_map(function ($k) use ($me) {
                return $k['element_id'];
            }, $q);
        }

        return [];
    }

    /**
     * Confirms if application has any attachments
     * that can be signed are yet to be signed
     *
     * @param $application
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     */
    public static function check_need_for_creating_signingTask($application)
    {
        # find any fields for this application that need signing
        # find out who (groups and users) can complete this task
        # create tasks for each of them
        # Note as a callback, if all attachments get signed, mark these tasks as completed
        $application_id = $application->getId();
        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        $q = "SELECT id FROM sub_menus WHERE id = " . $application->getApproved() . " and stage_type = 4";

        if (count($current_stage = $conn->fetchAssoc($q)) > 0):
            # find any signable attachments for this application
            $q = "SELECT * FROM signable_attachments_fields WHERE form_id = $form_id";
            $signable_fields = $conn->fetchAssoc($q);

            foreach ($signable_fields as $signable_field):
                $q = "SELECT element_title FROM ap_form_elements WHERE form_id = $form_id AND element_id = " . $signable_field['element_id'];
                $element_title = $conn->fetchAssoc($q)[0]['element_title'];
                $el = 'element_' . $signable_field['element_id'];
                $file_name = $conn->fetchAssoc("SELECT $el FROM ap_form_$form_id WHERE id = $entry_id")[0][$el];
                $file_name_signed = str_replace('.pdf', '--signed.pdf', $file_name);
                $is_signed = file_exists("asset_data/form_$form_id/files/$file_name_signed");

                if (!$is_signed) {
                    # ensure that this task is not a duplicate
                    $slug = $application_id . '-' . $signable_field['element_id'];
                    if (
                        Doctrine_Query::create()
                            ->from('Task a')
                            ->where('a.application_id = ?', $application_id)
                            ->andWhere('a.type = 10')
                            ->andWhere('a.task_application_slug = ?', $slug)
                            ->fetchOne()
                    )
                        continue;

                    $task = new Task();
                    $task->setType(10);
                    $task->setTaskApplicationSlug($slug);

                    $task->setOwnerUserId($signable_field['user_id']);
                    $task->setGroupId($signable_field['group_id']);

                    $task->setDuration("0");
                    $task->setStartDate(date('Y-m-d H:i:s'));
                    $task->setEndDate((new Datetime('+3 months'))->format('Y-m-d'));
                    $task->setPriority(3);
                    $task->setIsLeader("1");
                    $task->setActive("1");
                    $task->setStatus("1");
                    $task->setLastUpdate(date('Y-m-d'));
                    $task->setDateCreated(date('Y-m-d'));
                    $task->setRemarks("<b>$element_title</b> requires signing for " . $application->getApplicationId());
                    $task->setApplicationId($application_id);
                    $task->save();
                }
            endforeach;
        endif;
    }

    public static function files_to_sign_in_form($application = null, $application_id = null, $form_id = null, $entry_id = null)
    {
        if (!$application and $application_id and !$form_id and $entry_id)
            throw new Exception('Provide some direction using an application_id or form_id and entry_id');

        if (!$application and $application_id) {
            $application = Doctrine_Core::getTable('FormEntry')->find($application_id);
        }

        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();
        $application_id = $application_id ?: $application->getId();

        $me = self::current_user()->getNid();
        $q = <<<EOL
SELECT element_id, element_title FROM ap_form_elements
WHERE form_id = $form_id
  AND element_id IN (SELECT element_id
                     FROM signable_attachments_fields
                     WHERE form_id = $form_id
                       AND (group_id IN (SELECT group_id FROM mf_guard_user_group mgup WHERE user_id = $me) OR
                            user_id = $me))
EOL;

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        if ($result = $conn->fetchAssoc($q)) {
            $field_name_map = [];
            array_map(function ($c) use (&$field_name_map) {
                return $field_name_map['element_' . $c['element_id']] = $c['element_title'];
            }, $result);

            $columns = array_map(function ($column) {
                return $column['element_id'];
            }, $result);

            $columns_in_query = ' element_' . implode(", element_", $columns);
            $files_query = $conn->fetchAssoc("SELECT $columns_in_query FROM ap_form_$form_id WHERE id = $entry_id")[0];

            $files = array_map(function ($k) use ($field_name_map, $files_query, $application_id, $form_id) {
                $f = $files_query[$k];
                $file_path = $f ? "asset_data/form_$form_id/files/" . $f : null;
                $signed_path = str_replace('.pdf', '--signed.pdf', $file_path);
                return [
                    'file_name' => $field_name_map[$k],
                    'local_file' => $file_path,
                    'local_file_signed' => $signed_path,
                    'is_signed' => file_exists($signed_path),
                    'slug' => $application_id . '-' . (explode('_', $k)[1])
                ];
            }, array_keys($files_query));

            return $files;
        } else {
            return null;
        }
    }


    public static function create_signing_task_if_needed($saved_permit)
    {
        # confirm that this permit does not have a task yet
        # if no:
        #   find all groups that are allowed to do the signing
        #       for each group:
        #           create task
        # else:
        #   skip

        if ($saved_permit->isSigned()) {
            error_log("permit " . $saved_permit->getPermitId() . ' is already signed');
            return;
        }

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();


        $application = $saved_permit->getApplication();
        $slug = $saved_permit->getTaskSlug();

        $permit_type = $saved_permit->type_id;
        $permit_type_name = Doctrine_Query::create()
            ->from('Permits a')
            ->where('id = ? ', $permit_type)
            ->fetchOne()->getTitle();


        if (
            $groups = $conn->fetchAssoc(
                <<<EOL
SELECT mgg.id, mgg.name, permissions.description as permission
    FROM mf_guard_group_permission mggp LEFT JOIN mf_guard_group mgg on mggp.group_id = mgg.id
    LEFT JOIN (SELECT id, description FROM mf_guard_permission WHERE name = 'can-sign-permit-$permit_type') permissions on permissions.id = mggp.permission_id
    WHERE mggp.permission_id = permissions.id;
EOL
            )
        ) {

            foreach ($groups as $group) {
                $group = $group['id'];

                if (
                    $task = Doctrine_Query::create()
                        ->from('Task a')
                        ->where('a.application_id = ?', $saved_permit->getApplicationId())
                        ->andWhere("a.task_application_slug = ?", $slug)
                        ->andWhere('a.type = ?', 10)
                        ->andWhere('a.group_id = ? and status = ?', [$group, 1])
                        ->fetchOne()
                ) {
                    error_log('skip creating signing task for group ');
                    continue;
                }

                $date_format = 'Y-m-d H:i:s';
                $start_date = date($date_format);
                $end_date = date($date_format, strtotime('2 month'));

                $task = new Task();
                $task->setType(10);
                //            $task->setCreatorUserId($assigned_by);
//            $task->setOwnerUserId($assigned_to);
                $task->setGroupId($group);
                $task->setDuration("0");
                $task->setStartDate($start_date);
                $task->setEndDate($end_date);
                $task->setPriority('3');
                $task->setIsLeader("0");
                $task->setActive("1");
                $task->setStatus("1");
                $task->setTaskApplicationSlug($slug);
                $task->setLastUpdate(date('Y-m-d'));
                $task->setDateCreated(date('Y-m-d'));
                $task->setRemarks("Sign $permit_type_name for " . $application->getApplicationId());
                $task->setApplicationId($saved_permit->getApplicationId());
                $task->setTaskStage($application->getApproved());
                $task->save();
            }
        } // could not find groups -> leave
    }

    public static function lastSigningSession()
    {
        $me = Functions::current_user()->getNid();
        $fetch = "SELECT * FROM user_signing_sessions WHERE user_id = $me AND status = 1";
        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();

        if ($result = $conn->fetchAssoc($fetch)) {
            return $result[0];
        } else {
            $now = date('Y-m-d h:i:s');
            $conn->execute("INSERT INTO user_signing_sessions (user_id, documents, started_at, status) VALUES ($me, '[]', '$now', 1)");
            return $conn->fetchAssoc($fetch)[0];
        }
    }

    /**
     * confirm if document is part of current signing sessions
     *
     * @param string $url
     * @return bool
     */
    public static function isDocumentInSigningSession(string $url): bool
    {
        $latest_signing_session = self::lastSigningSession();
        return !empty(array_filter(json_decode($latest_signing_session['documents']), function ($doc) use ($url) {
            return $doc->url == $url;
        }));
    }


    /**
     * Once a tasks / application has been assigned to a reviewer once,
     * it remains their task until the last of its stages has been handled
     *
     * to solve the issue with the CBI having to keep assigning assessments to
     * building inspectors for the same application
     *
     * @param $application_id
     * @param null $application
     * @throws Doctrine_Connection_Exception
     */
    public static function assign_tasks_from_history($application_id, $application = null)
    {
        if (!$application)
            $application = Doctrine_Core::getTable('FormEntry')->find($application_id);

        if (!$application)
            return;

        if (!$application_id)
            $application_id = $application->getId();



        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();

        $current_stage = $application->getApproved();
        #confirm that this is happening in the 'Building Inspection and Occupation' workflow
        if (!$conn->fetchOne("SELECT id FROM sub_menus WHERE menu_id = 12 AND id = $current_stage"))
            return;

        # confirm if this application has ever been assigned to a given user
        # - i.e. the user who created it is not the user who owns it.
        # if so,
        #   - then:
        #       - find out if it has already completed the Construction workflow # some status above 120
        #       - then create a task for the user and send a mail notification to them
        # else:
        #   let it be

        $q = <<<EOL
        SELECT * FROM task WHERE application_id = $application_id AND creator_user_id <> owner_user_id ORDER BY id DESC LIMIT 1    
EOL;


        if ($last_task_for_this_application = $conn->fetchAssoc($q)) {
            $last_task_for_this_application = $last_task_for_this_application[0];

            $me = $last_task_for_this_application['owner_user_id'];

            # to avoid recreation of the same task on the same stage
            $task_slug = "reassigned-task-at-stage-" . $application->getApproved() . "-to-$me";


            if ($conn->fetchOne("SELECT id from task WHERE task_application_slug = '$task_slug'")) {
                error_log('this is already assigned to ' . $me);
                return;
            }

            $task = new Task();
            $task->setType(2);

            $task->setCreatorUserId($me);
            $task->setOwnerUserId($me);
            $task->setSheetId(0);
            $task->setDuration("0");

            $date_format = 'Y-m-d H:i:s';
            $now = date($date_format);
            $two_weeks_from_now = date($date_format, strtotime('+2 weeks'));
            $task->setStartDate($now);
            $task->setEndDate($two_weeks_from_now);

            $task->setPriority(3);
            $task->setIsLeader("0");
            $task->setActive("1");
            $task->setStatus("1");
            $task->setLastUpdate(date('Y-m-d'));
            $task->setDateCreated(date('Y-m-d'));
            $task->setRemarks("Task assigned to you relative to previous assignment on task <a href=\"/plan/tasks/view/id/" . $last_task_for_this_application['id'] . "\">here</a>");
            $task->setApplicationId($application);
            $task->setTaskApplicationSlug($task_slug);
            $task->save();

            //Audit
            Audit::audit($application, "Re-Assigned task to reviewer #" . $me);

            $q = Doctrine_Query::create()
                ->from("CfUser a")
                ->where("a.nid = ?", $me);
            $reviewer = $q->fetchOne();
            $app_id = $application->getApplicationId();

            $audit = new Audit();
            $audit->saveFullAudit("<a href=\"/plan/tasks/view/id/" . $task->getId() . "\">Assigned " . $task->getTypeName() . " task on " . $app_id . " to " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . "</a>", $task->getId(), "task", "", "Pending", $application);

            $review_name = $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();
            $host = sfConfig::get('app_sso_jambo_url');
            $task_id = $task->getId();
            $body = <<<EOL
                    Hi $review_name,<br>
                    <br>
                    You have been assigned a new task on $app_id:<br>
                    <br><br>
                   
                    <br>
                    <br>
                    Click here to view the task: <br>
                    ------- <br>
                    <a href="https://$host/plan/tasks/view/id/$task_id">Link to $app_id task</a><br>
                    ------- <br>
                    <br>
EOL;

            $mailnotifications = new mailnotifications();
            $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "New Task", $body);

            if ($reviewer->getStrphoneMain1() && strlen($reviewer->getStrphoneMain1()) > 5) {
                $body = <<<EOL
                    Hi $review_name,

                    You have been assigned a new task on $app_id.

                    Click here to view the task:
                    https://$host/plan/tasks/view/id/$task_id
                    EOL;
                $mailnotifications->sendsms($reviewer->getStrphoneMain1(), $body);
            }
        }
    }
}

?>