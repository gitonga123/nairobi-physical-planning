<?php

/**
 *
 * Submissions class will handle all functions regarding the creation and modifications of applications
 *
 * Created by PhpStorm.
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 12:26 AM
 */

class ApplicationManager
{

    public $invoice_manager = null;
    public $permit_manager = null;

    //Constructor for submissions class
    public function __construct()
    {
        $this->invoice_manager = new InvoiceManager();
        $this->permit_manager = new PermitManager();
    }

    //Get a specific field from a record
    public function get_field_data($form_id, $entry_id, $element_id)
    {
        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = ?";
        $params = array($entry_id);
        $sth = mf_do_query($sql, $params, $dbh);

        $apform = mf_do_fetch_result($sth);

        return $apform["element_" . $element_id];
    }

    //Create a new application from a form submission
    public function create_application($form_id, $entry_id, $user_id, $save_as_draft)
    {
        $submission = new FormEntry();
        $submission->setDeclined('0');
        $submission->setUserId($user_id);
        $submission->setFormId($form_id);
        $submission->setEntryId($entry_id);
        $audit = new Audit();
        error_log('-------entry_id------' . $entry_id);
        if ($save_as_draft) {
            error_log('-------------DRAFT SAVE--------');
            $submission->setApplicationId("Draft-" . date("Y-m-d H:i:s"));
            $submission->setApproved("0");
            $submission->setDateOfSubmission(date("Y-m-d H:i:s"));
            $audit->saveClientAudit($submission->getId(), "Draft application submitted");
        } else {
            $submission->setApplicationId($this->generate_application_number($form_id));
            $submission->setApproved($this->get_submission_stage($form_id, $entry_id));
            $submission->setDateOfSubmission(date("Y-m-d H:i:s"));

            $audit->saveClientAudit($submission->getId(), "Submitted application");
        }

        //OTB ADD
        //Check merchant enable and merchant identifier
        $q = Doctrine_Query::create()
            ->from('ApForms f')
            ->where('f.form_id = ?', $form_id);
        $form = $q->fetchOne();
        //check if set
        if ($form->getPaymentEnableMerchant() && strlen($form->getPaymentMerchantIdentifier())) {
            //set incremented merchant identifier
            //$submission->setMerchantIdentifier($this->generate_merchant_number($form_id));
        }

        //Save business id if current_profile is set
        if (sfContext::getInstance()->getUser()->getAttribute("current_profile")) {
            $submission->setBusinessId(sfContext::getInstance()->getUser()->getAttribute("current_profile"));
        }

        $submission->save();

        $this->update_services($submission->getId());
        //otb add send ifc file(s)
        //$this->sendIfc($submission);

        // register plan to zizi
        // $api = new ApiCalls();
        // $api->registerPlan($form_id, $submission);
        return $submission;
    }

    //Create a new application from a form submission
    public function create_business_application($service_id, $form_id, $entry_id, $user_id, $business_id, $business_title)
    {
        $submission = new FormEntry();
        $submission->setDeclined('0');
        $submission->setServiceId($service_id);
        $submission->setUserId($user_id);
        $submission->setBusinessId($business_id);
        $submission->setFormId($form_id);
        $submission->setEntryId($entry_id);

        $submission->setApplicationId($this->generate_cyclic_number($service_id));

        $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $form_id);
        $form = $q->fetchOne();

        //Check workflow override logic
        if ($form->getLogicWorkflowEnable()) {
            $submission->setApproved($this->get_submission_stage($form_id, $entry_id));
        } else {
            $submission->setApproved($this->get_service_stage($service_id));
        }

        $submission->setDateOfSubmission(date("Y-m-d H:i:s"));

        $audit = new Audit();
        $audit->saveClientAudit($submission->getId(), "Submitted application");

        $submission->save();

        return $submission;
    }

    //Push an application from drafts to a live workflow
    public function publish_draft($application_id)
    {
        $submission = $this->get_application_by_id($application_id);

        if ($submission->getBusinessId()) {
            $submission->setApplicationId($this->generate_cyclic_number($submission->getServiceId()));
        } else {
            $submission->setApplicationId($this->generate_application_number($submission->getFormId()));
        }

        if ($submission->getBusinessId()) {
            $submission->setApproved($this->get_service_stage($submission->getServiceId()));
        } else {
            $submission->setApproved($this->get_submission_stage($submission->getFormId(), $submission->getEntryId()));
        }
        $submission->setDateOfSubmission(date("Y-m-d H:i:s"));
        $submission->save();

        $audit = new Audit();
        $audit->saveClientAudit($submission->getId(), "Submitted application");

        return $submission;
    }

    //Push an application from drafts to a live workflow
    public function resubmit_application($application_id)
    {
        error_log('------Application id----' . $application_id);
        $submission = $this->get_application_by_id($application_id);
        error_log("------SUBMISSION-----" . $submission->getApplicationId());
        //OTB don't replace given no after resubmission
        //$submission->setApplicationId($this->generate_application_number($submission->getFormId()));
        $submission->setApproved($this->get_resubmission_stage($submission->getFormId(), $submission->getEntryId()));
        $submission->setDeclined("0");
        //OTB don't replace inital data of submission
        //$submission->setDateOfSubmission(date("Y-m-d H:i:s"));
        $submission->save();

        $this->update_services($submission->getId());

        return $submission;
    }

    public function getExtraApplicationInfo($form_id, $entry_id)
    {
        $details = ["", ""];
        if (empty($form_id) || empty($entry_id)) {
            return $details;
        }

        $sql_block = "SELECT element_id from ap_form_elements where form_id = {$form_id} and element_block_no = 1";
        $sql_block_element = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_block);

        if (!empty($sql_block_element)) {
            $sql_block_details = "SELECT element_{$sql_block_element} from ap_form_{$form_id} WHERE id = {$entry_id}";
            $details[0] = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_block_details);
        }


        error_log("Details is here -----> 1" . $details[0]);

        error_log("Time for testing ---< 2");

        $sql_plot = "SELECT element_id from ap_form_elements where form_id = {$form_id} and element_plot_no = 1";
        $sql_plot_element = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_plot);

        if (!empty($sql_plot_element)) {
            $sql_plot_details = "SELECT element_{$sql_plot_element} from ap_form_{$form_id} WHERE id = {$entry_id}";
            $plot_no = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_plot_details);

            if (!empty($details[0])) {
                $details[0] = "$details[0] {$plot_no}";
            } else {
                $details[0] = $plot_no;
            }
        }

        $sql_owner = "SELECT element_id from ap_form_elements where form_id = {$form_id} and element_ownertype = 1";
        $sql_owner_element = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_owner);
        if (!empty($sql_owner_element)) {
            $sql_owner_details = "SELECT element_{$sql_owner_element} from ap_form_{$form_id} where id = {$entry_id}";
            $details[1] = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_owner_details);
        }
        return $details;
    }

    public function getSubCountyNameFromApplication($form_id, $entry_id)
    {
        if (empty($form_id) || empty($entry_id)) {
            return '';
        }
        $sql_subcounty = "SELECT element_id from ap_form_elements where form_id = {$form_id} and element_subcounty = 1";
        $sql_subcounty_element = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_subcounty);

        if (!empty($sql_subcounty_element)) {
            $sql_subcounty_option_element = "SELECT element_{$sql_subcounty_element} from ap_form_{$form_id} WHERE id = {$entry_id}";
            $sql_subcounty_option_id = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql_subcounty_option_element);

            if (!empty($sql_subcounty_option_id)) {
                $subcounty_name_query = "select option_text from ap_element_options where form_id = {$form_id} and element_id = {$sql_subcounty_element} and option_id = {$sql_subcounty_option_id}";
                return Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($subcounty_name_query);
            }
            return '';
        }

        return '';
    }

    //Check if an application already exists for a given form entry
    public function application_exists($form_id, $entry_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.entry_id = ?', $entry_id)
            ->andWhere('a.form_id = ?', $form_id)
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) //Already submitted then tell client its already submitted
        {
            return true;
        } else {
            return false;
        }
    }

    //Returns an existing application
    public function get_application($form_id, $entry_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.entry_id = ?', $entry_id)
            ->andWhere('a.form_id = ?', $form_id)
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) //Already submitted then tell client its already submitted
        {
            return $existing_app;
        } else {
            return false;
        }
    }

    //Returns an existing application
    public function get_application_by_id($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id)
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) //Already submitted then tell client its already submitted
        {
            return $existing_app;
        } else {
            return false;
        }
    }

    //Check if an application is a draft
    public function is_draft($form_id, $entry_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.entry_id = ?', $entry_id)
            ->andWhere('a.form_id = ?', $form_id)
            ->andWhere("a.approved = 0")
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) {
            return true;
        } else {
            return false;
        }
    }

    //Check if an application is a resubmission
    public function is_resubmission($form_id, $entry_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.entry_id = ?', $entry_id)
            ->andWhere('a.form_id = ?', $form_id)
            ->andWhere("a.approved = 0 AND a.parent_submission <> 0")
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) {
            return true;
        } else {
            return false;
        }
    }

    //Check if the form already has an existing draft application
    public function has_draft($form_id, $user_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.user_id = ?', $user_id)
            ->andWhere('a.form_id = ?', $form_id)
            ->andWhere("a.approved = 0")
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if ($existing_app) {
            return true;
        } else {
            return false;
        }
    }

    //Generate a new application number for a new application
    public function generate_application_number($form_id)
    {
        //$lockingManager = new Doctrine_Locking_Manager_Pessimistic();

        $q = Doctrine_Query::create()
            ->from("ApNumberGenerator a")
            ->where("a.form_id = ?", $form_id);
        $form_numbers = $q->fetchOne();

        if ($form_numbers) {
            $application_number = $form_numbers->getApplicationNumber();
            error_log('-------App no from ap_number_generator 102---' . $application_number);
            try {
                //LOCK ap_number_generator
                //$gotLock = $lockingManager->getLock($form_numbers, 'jwage');
                Doctrine_Manager::getInstance()->getCurrentConnection()->execute("LOCK TABLES ap_number_generator WRITE");

                //Update the value
                $application_number++;
                //Update ap_number_generator
                $form_numbers->setApplicationNumber($application_number);
                $form_numbers->save();

                //UNLOCK ap_number_generator
                //$lockingManager->releaseLock($form_numbers, 'jwage');
                Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UNLOCK TABLES");
            } catch (Exception $dle) {
                echo $dle->getMessage(); // handle the error
                exit;
            }
            error_log('------APp no generated----202' . $application_number);
            return $application_number;
        } else {
            error_log('-------No ap_number_generator found-----');
            $application_number = 0;

            $q = Doctrine_Query::create()
                ->from("ApForms a")
                ->where("a.form_id = ?", $form_id);
            $form = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.approved <> 0 AND a.declined <> 1 AND a.parent_submission = 0')
                ->andWhere('a.form_id = ?', $form_id)
                ->orderBy("a.application_id DESC")
                ->limit(1);
            $last_app = $q->fetchOne();

            if ($last_app) {
                $new_app_id = $last_app->getApplicationId();
                $new_app_id = ++$new_app_id;
                $application_number = $new_app_id;
            } else {
                $application_number = $form->getFormIdn();
            }

            $form_numbers = new ApNumberGenerator();
            $form_numbers->setFormId($form_id);
            $form_numbers->setApplicationNumber($application_number);
            $form_numbers->save();
            error_log('------APp no generated----203' . $application_number);
            return $application_number;
        }
    }

    //Generate a new application number for cyclic services
    public function generate_cyclic_number($service_id)
    {
        //$lockingManager = new Doctrine_Locking_Manager_Pessimistic();

        $q = Doctrine_Query::create()
            ->from("ApNumberGenerator a")
            ->where("a.service_id = ?", $service_id);
        $service_numbers = $q->fetchOne();

        if ($service_numbers) {
            $application_number = $service_numbers->getApplicationNumber();
            error_log('------Application no-----' . $application_number);
            try {
                //LOCK ap_number_generator
                //$gotLock = $lockingManager->getLock($form_numbers, 'jwage');
                Doctrine_Manager::getInstance()->getCurrentConnection()->execute("LOCK TABLES ap_number_generator WRITE");
                //Update the value
                $application_number++;

                $service_numbers->setApplicationNumber($application_number);
                $service_numbers->save();

                //UNLOCK ap_number_generator
                //$lockingManager->releaseLock($form_numbers, 'jwage');
                Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UNLOCK TABLES");
            } catch (Exception $dle) {
                echo $dle->getMessage(); // handle the error
                exit;
            }

            return $application_number;
        } else {
            $application_number = 0;
            error_log('-----No ap_number_generator------');
            $q = Doctrine_Query::create()
                ->from("Menus a")
                ->where("a.id = ?", $service_id);
            $service = $q->fetchOne();

            $application_number = $service->getServiceNumber();

            //$form_id = 10000 + $service_id;

            $form_numbers = new ApNumberGenerator();
            $form_numbers->setFormId($form_id);
            $form_numbers->setServiceId($service_id);
            $form_numbers->setApplicationNumber($application_number);
            $form_numbers->save();

            return $application_number;
        }
    }

    //Get the stage of submission for a form entry (Depends on whether logic for workflow is set or not)
    public function get_submission_stage($form_id, $entry_id)
    {
        //Use the settings from ap_forms
        $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $form_id);
        $form = $q->fetchOne();

        //Check workflow override logic
        if ($form->getLogicWorkflowEnable()) {
            //error_log("Logic-Workflow #1: Enabled");
            $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
            require_once($prefix_folder . 'includes/init.php');

            require_once($prefix_folder . '../../../config/form_builder_config.php');
            require_once($prefix_folder . 'includes/db-core.php');
            require_once($prefix_folder . 'includes/helper-functions.php');

            $dbh = mf_connect_db();
            $mf_settings = mf_get_settings($dbh);

            //Logic field
            $sql = "SELECT * FROM ap_workflow_logic_elements WHERE form_id = ?";
            $params = array($form_id);
            $sth = mf_do_query($sql, $params, $dbh);
            $element_values = mf_do_fetch_result($sth);

            $element_id = $element_values['element_id'];

            //error_log("Logic-Workflow #2: Element ".$element_id);

            //Get element value
            $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = ?";
            $params = array($entry_id);
            $sth = mf_do_query($sql, $params, $dbh);
            $entry_values = mf_do_fetch_result($sth);

            $option_id = $entry_values['element_' . $element_id];

            //error_log("Logic-Workflow #3: Option ID ".$option_id);

            $sql = "SELECT * FROM ap_element_options WHERE form_id = ? AND element_id = ? AND option_id = ?";
            $params = array($form_id, $element_id, $option_id);
            $sth = mf_do_query($sql, $params, $dbh);
            $option_values = mf_do_fetch_result($sth);

            $option_value = $option_values['option_text'];

            //error_log("Logic-Workflow #4: Option Value ".$option_value);

            $sql = "SELECT * FROM ap_workflow_logic_conditions WHERE form_id = ? AND target_element_id = ? AND rule_keyword = ?";
            $params = array($form_id, $element_id, $option_value);
            $sth = mf_do_query($sql, $params, $dbh);
            $stage_values = mf_do_fetch_result($sth);

            //error_log("Logic-Workflow #5: SQL: ".$sql);

            if ($stage_values) {
                //error_log("Logic-Workflow #2: Found Override Stage");
                $stage_value = $stage_values['element_name'];

                return $stage_value;
            } else {
                //error_log("Logic-Workflow #2: No Override Stage");
                return $form->getFormStage();
            }
        } else {
            return $form->getFormStage();
        }
    }

    //Get the stage of submission for a form entry
    public function get_service_stage($service_id)
    {
        $q = Doctrine_Query::create()
            ->from('Menus a')
            ->where('a.id = ?', $service_id);
        $service = $q->fetchOne();

        if ($service) {
            $q = Doctrine_Query::create()
                ->from('SubMenus a')
                ->where('a.menu_id = ?', $service_id)
                ->orderBy("a.order_no ASC");
            $stage = $q->fetchOne();

            if ($stage) {
                return $stage->getId();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    //Get the stage of submission for a form entry (Depends on whether logic for workflow is set or not)
    public function get_resubmission_stage($form_id, $entry_id)
    {
        $submission = $this->get_application($form_id, $entry_id);

        $q = Doctrine_Query::create()
            ->from('SubMenus a')
            ->where('a.id = ?', $submission->getApproved())
            ->limit(1);
        $current_stage = $q->fetchOne();
        //corrections
        if ($current_stage && $current_stage->getStageType() == 5 && $current_stage->getStageProperty() == 2) {
            return $current_stage->getStageTypeMovement();
        } elseif ($current_stage && ($current_stage->getStageType() == 5 || $current_stage->getStageType() == 12) && $current_stage->getStageProperty() == 3) {
            //Send notification to reviewers
            $notification = $current_stage->getStageTypeNotification();

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
                    ->andWhere('d.name = ?', "accesssubmenu" . $submission->getApproved());
                $usergroups = $q->execute();
                if (sizeof($usergroups) > 0) {
                    $body = "
                            Hi " . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . ",<br>
                            <br>
                            " . $notification . "

                            <br>
                            Click here to view the application: <br>
                            ------- <br>
                            <a href='http://" . $_SERVER['HTTP_HOST'] . "/plan/applications/view/id/" . $submission->getId() . "'>Link to " . $submission->getApplicationId() . "</a><br>
                            ------- <br>

                            <br>
                            ";

                    $mailnotifications = new mailnotifications();
                    $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $reviewer->getStremail(), "Corrected Application", $body);
                }
            }
        } elseif ($current_stage && $current_stage->getStageType() == 12 && $current_stage->getStageProperty() == 2) {
            error_log('----------Expired action------');
            //OTB Use Expired stage settings to reset expired invoice accordingly - originated from Kisumu requirements
            $this->invoice_manager->update_expired_invoices($submission->getId(), $current_stage->getStageExpiredInvoiceAction());
            return $current_stage->getStageTypeMovement();
        } else {
            //Use the settings from ap_forms
            $q = Doctrine_Query::create()
                ->from("ApForms a")
                ->where("a.form_id = ?", $form_id);
            $form = $q->fetchOne();

            //Check workflow override logic
            if ($form->getLogicWorkflowEnable()) {
                $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
                require_once($prefix_folder . 'includes/init.php');

                require_once($prefix_folder . '../../../config/form_builder_config.php');
                require_once($prefix_folder . 'includes/db-core.php');
                require_once($prefix_folder . 'includes/helper-functions.php');

                $dbh = mf_connect_db();
                $mf_settings = mf_get_settings($dbh);

                //Logic field
                $sql = "SELECT * FROM ap_workflow_logic_elements WHERE form_id = ?";
                $params = array($form_id);
                $sth = mf_do_query($sql, $params, $dbh);
                $element_values = mf_do_fetch_result($sth);

                $element_id = $element_values['element_id'];

                //Get element value
                $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = ?";
                $params = array($entry_id);
                $sth = mf_do_query($sql, $params, $dbh);
                $entry_values = mf_do_fetch_result($sth);

                $option_id = $entry_values['element_' . $element_id];

                $sql = "SELECT * FROM ap_element_options WHERE form_id = ? AND element_id = ? AND option_id = ?";
                $params = array($form_id, $element_id, $option_id);
                $sth = mf_do_query($sql, $params, $dbh);
                $option_values = mf_do_fetch_result($sth);

                $option_value = $option_values['option_text'];

                $sql = "SELECT * FROM ap_workflow_logic_conditions WHERE form_id = ? AND target_element_id = ? AND rule_keyword = ?";
                $params = array($form_id, $element_id, $option_value);
                $sth = mf_do_query($sql, $params, $dbh);
                $stage_values = mf_do_fetch_result($sth);

                if ($stage_values) {
                    $stage_value = $stage_values['element_name'];

                    return $stage_value;
                } else {
                    return $form->getFormStage();
                }
            } else {
                return $form->getFormStage();
            }
        }
    }

    //Check if an application requires the generation of permits
    public function update_services($application_id)
    {
        //Only auto generate permit if there is permit template configured for the current stage and there is nothing owed
        if ($this->permit_manager->needs_permit_for_current_stage($application_id)) {
            $this->permit_manager->create_permit($application_id);
        }

        //If application form has just been paid for from the frontend then redirect to the generated permit
        // This improves userability as the client doesn't need to search for where the permit link is
        if ($_SESSION['just_submitted'] && empty(sfContext::getInstance()->getUser()->getAttribute('userid'))) {
            //Redirect to permits page if available (more user friendly that way)
            if ($this->permit_manager->has_permit($application_id)) {
                //redirect to permit page
                $q = Doctrine_Query::create()
                    ->from("SavedPermit a")
                    ->leftJoin("a.FormEntry b")
                    ->where("b.id = ?", $application_id)
                    ->andWhere("a.permit_status <> 3")
                    ->limit(1);
                $permit = $q->fetchOne();

                if ($permit) {
                    echo "<script language='javascript'>window.parent.location.href = '/plan/permits/view/id/" . $permit->getId() . "/done/1';</script>";
                    exit;
                }
            }
            //OTB causes infinity loading since on submission doesn't find permit and reloads the same action over and over
            //Redirect to applications page if permit is not available
            //echo "<script language='javascript'>window.parent.location.href = '/plan/application/view/id/" . $application_id."/done/1';</script>";
            //exit;
        }
    }

    //Check if application requires generation of invoices
    public function update_invoices($application_id)
    {
        //Check if there is already an invoice generated for this application
        if ($this->invoice_manager->has_unpaid_invoice($application_id)) {
            //Try changing the invoice status to pending since the user is trying to
            // add a payment for an application
            $invoice = $this->invoice_manager->get_unpaid_invoice($application_id);
            if ($invoice) {
                $invoice->setPaid(1);
                $invoice->save();
            }
        } else {
            if (!$this->invoice_manager->has_paid_invoice($application_id)) {
                //If no invoice then generate an invoice for this application since this function is
                // only called when there is a payment on submission
                $invoice = $this->invoice_manager->create_invoice_from_submission($application_id);
            }
        }
    }

    //Determines whether the form can be autosubmitted
    public function can_autosubmit_form($form_id)
    {
        //The following conditions must be met
        //1. The form must have only 1 active element
        //2. The element must have a default value which is a tag that returns a value e.g. {sf_username}
        //3. The elemnt must have settings for fetching a remote value from a remote database

        // First condition, check number of elements
        $q = Doctrine_Query::create()
            ->from("ApFormElements a")
            ->where("a.form_id = ?", $form_id)
            ->andWhere("a.element_status = 1");
        $elements = $q->count();

        if ($elements > 1) {
            return false;
        } elseif ($elements == 0) {
            echo "This form doesn't have any fields. It is therefore not capable of being submitted.";
            exit;
        }

        // Second condition, check the default value
        $q = Doctrine_Query::create()
            ->from("ApFormElements a")
            ->where("a.form_id = ?", $form_id)
            ->andWhere("a.element_status = 1")
            ->limit(1);
        $element = $q->fetchOne();

        if ($element && $element->getElementDefaultValue() == "") {
            return false;
        }

        // Third condition, can fetch remote data
        $q = Doctrine_Query::create()
            ->from("ApFormElements a")
            ->where("a.form_id = ?", $form_id)
            ->andWhere("a.element_status = 1")
            ->limit(1);
        $element = $q->fetchOne();

        if ($element && $element->getElementOptionQuery() == "") {
            return false;
        }

        return true;
    }

    //Attempts to autosubmit a form
    public function autosubmit_form($form_id, $user_id)
    {
        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $template_parser = new templateparser();

        //Fetch default value and parse it
        $q = Doctrine_Query::create()
            ->from("ApFormElements a")
            ->where("a.form_id = ?", $form_id)
            ->andWhere("a.element_status = 1")
            ->limit(1);
        $element = $q->fetchOne();

        $default_value = $element->getElementDefaultValue();

        $entry_data = $template_parser->parseUser($user_id, $default_value);

        //First check if data is in remote database
        $remote_url = $element->getElementOptionQuery(); //http://remote
        $criteria = $element->getElementFieldName(); //can be 'records', 'norecords' or 'value'
        $result_value = $element->getElementFieldValue(); //If criteria is 'value'
        $remote_username = $element->getElementRemoteUsername(); //Remote username
        $remote_password = $element->getElementRemotePassword(); //Remote password

        $ch = curl_init();

        $pos = strpos($remote_url, '$value');

        if ($pos === false) {
            //dont' do anything
        } else {
            $remote_url = str_replace('$value', curl_escape($ch, $entry_data), $remote_url);
        }

        $pos = strpos($remote_url, '{fm_element_' . $element['element_id'] . '}');

        curl_setopt($ch, CURLOPT_URL, $remote_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!empty($remote_username) && !empty($remote_password)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "$remote_username:$remote_password");
        }

        $results = curl_exec($ch);

        curl_close($ch);

        if (empty($error)) {
            $values = json_decode($results);
            if ($criteria == "records") {
                //If count is = 0, fail
                if ($values->{'count'} == 0) {
                    $error = "<div class='contentpanel'><div class='panel panel-default'><div class='panel-heading'><h4 class='panel-title'>Your application cannot be submitted at this time:</h4></div> <div class='panel-body'><div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>No records found on server</div></div></div></div>";
                    echo $error;
                } else {
                    //Create review page entry details
                    //The form should have a review page otherwise it would just go straight to the payment settings or applications page
                    $q = Doctrine_Query::create()
                        ->from("ApForms a")
                        ->where("a.form_id = ?", $form_id)
                        ->limit(1);
                    $form = $q->fetchOne();

                    if ($form->getFormReview()) {
                        //Enter data into review table and redirect
                        $sql = "INSERT INTO ap_form_" . $form_id . "_review (date_created, element_" . $element->getElementId() . ") VALUES('" . date('Y-m-d') . "','" . $entry_data . "')";
                        $params = array();
                        $sth = mf_do_query($sql, $params, $dbh);

                        $_SESSION['review_id'] = (int) $dbh->lastInsertId();

                        //Redirect to review page
                        header("Location: /plan/forms/confirm?id={$form_id}");
                        exit;
                    } else {
                        echo "<div class='contentpanel'><div class='panel panel-default'><div class='panel-heading'><h4 class='panel-title'>Your application cannot be submitted at this time:</h4></div> <div class='panel-body'><div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>You must enable review page for this form if you want to use automatic submission</div></div></div></div>";
                    }
                }
            } else if ($criteria == "norecords") {
                //If count is greater than 0, then pass
                if ($values->{'count'} > 0) {
                    $error = "<div class='contentpanel'><div class='panel panel-default'><div class='panel-heading'><h4 class='panel-title'>Your application cannot be submitted at this time:</h4></div> <div class='panel-body'><div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>Existing records found on server</div></div></div></div>";
                    echo $error;
                }
            } else if ($criteria == "value") {
                //If count is greater than 0, then pass
                if ($result_value != $results) {
                    $error = "<div class='contentpanel'><div class='panel panel-default'><div class='panel-heading'><h4 class='panel-title'>Your application cannot be submitted at this time:</h4></div> <div class='panel-body'><div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>No matching records found on server</div></div></div></div>";
                    echo $error;
                } else {
                    //Create review page entry details
                    //The form should have a review page otherwise it would just go straight to the payment settings or applications page
                    $q = Doctrine_Query::create()
                        ->from("ApForms a")
                        ->where("a.form_id = ?", $form_id)
                        ->limit(1);
                    $form = $q->fetchOne();

                    if ($form->getFormReview()) {
                        //Enter data into review table and redirect
                        $sql = "INSERT INTO ap_form_" . $form_id . "_review (date_created, element_" . $element->getElementId() . ") VALUES('" . date('Y-m-d') . "','" . $entry_data . "')";
                        $params = array();
                        $sth = mf_do_query($sql, $params, $dbh);

                        $_SESSION['review_id'] = (int) $dbh->lastInsertId();

                        //Redirect to review page
                        header("Location: /plan/forms/confirm?id={$form_id}");
                        exit;
                    } else {
                        echo "<div class='contentpanel'><div class='panel panel-default'><div class='panel-heading'><h4 class='panel-title'>Your application cannot be submitted at this time:</h4></div> <div class='panel-body'><div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>You must enable review page for this form if you want to use automatic submission</div></div></div></div>";
                    }
                }
            }
        } else {
            $error = "<div class='contentpanel'></div><div class='panel panel-default'><div class='panel-heading'><h4 class='panel-title'>Your application cannot be submitted at this time:</h4></div> <div class='panel-body'><div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>Could not connect to remote database. Try again later.</div></div></div></div>";
            echo $error;
        }
    }

    //Function that checks if an application form has reached its limit for number of entries permitted
    public function form_entry_limit($form_id, $user_id)
    {
        $form = Doctrine_Core::getTable('ApForms')->find($form_id);

        if ($form->getFormUniqueIp()) {
            //Check for applications with pending invoices and permits that haven't expired
            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->leftJoin("a.mfInvoice b")
                ->where("a.user_id = ?", $user_id)
                ->andWhere("a.form_id = ?", $form_id)
                ->andWhere('a.parent_submission = 0')
                ->andWhere('a.approved <> 0')
                ->andWhere('b.paid = 15 OR b.paid = 1')
                ->andWhere('a.declined <> 1')
                ->orderBy("a.id DESC");
            $applications_with_pending_invoices = $q->count();

            if ($applications_with_pending_invoices > 0) {
                return true;
            } else {
                //Check for permits that haven't expired
                $q = Doctrine_Query::create()
                    ->from("SavedPermit a")
                    ->leftJoin("a.FormEntry b")
                    ->where("b.form_id = ?", $form_id)
                    ->andWhere("b.user_id = ?", $user_id)
                    ->andWhere('b.declined <> 1')
                    ->andWhere("a.date_of_expiry > CURRENT_DATE() or a.date_of_expiry = ?", '');
                $applications_with_valid_permits = $q->count();


                if ($applications_with_valid_permits > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    //Insert audit trail for current application
    public function insert_audit($id, $application_id, $stage_id, $stage_name)
    {
        //Save an application specific log
        try {
            $q = Doctrine_Query::create()
                ->from("ApplicationReference a")
                ->where("a.application_id = ?", $id)
                ->andWhere("a.stage_id = ?", $stage_id);

            if ($q->count() == 0 && $id) {
                // Update last reference as ended
                $q = Doctrine_Query::create()
                    ->from("ApplicationReference a")
                    ->where("a.application_id = ?", $id)
                    ->orderBy("a.id DESC");
                $last_reference = $q->fetchOne();

                if ($last_reference) {
                    $last_reference->setEndDate(date('Y-m-d H:i:s'));
                    $last_reference->save();
                }


                if (sfContext::getInstance()->getUser()->getAttribute('userid', 0)) {
                    $appref = new ApplicationReference();
                    $appref->setStageId($stage_id);
                    $appref->setApplicationId($id);
                    $appref->setApprovedBy(sfContext::getInstance()->getUser()->getAttribute('userid', 0));
                    $appref->setStartDate(date('Y-m-d H:i:s'));
                    $appref->setEndDate("");
                    $appref->save();
                } else {
                    $appref = new ApplicationReference();
                    $appref->setStageId($stage_id);
                    $appref->setApplicationId($id);
                    $appref->setApprovedBy(0);
                    $appref->setStartDate(date('Y-m-d H:i:s'));
                    $appref->setEndDate("");
                    $appref->save();
                }
            }
        } catch (Exception $ex) {
            error_log("Application Reference Log Failed " . $ex->getMessage());
        }

        //Save a general log as well
        if ($stage_name) {
            //Save Audit Log
            $audit = new Audit();
            $audit->saveFullAudit("Moved " . $application_id . " to " . $stage_name, $id, "form_entry", "", $stage_name);
        }
    }

    //Send notifications for current stage of the application
    public function queue_notifications($application_id, $form_id, $entry_id, $user_id, $stage_id)
    {
        $q = Doctrine_Query::create()
            ->from("ApplicationReference a")
            ->where("a.application_id = ?", $application_id)
            ->orderBy("a.id DESC")
            ->limit(1);

        $movement_history = $q->fetchOne();

        if ($movement_history) {
            if ($movement_history->getIsSmsSent() == 1 && $stage_id == $movement_history->getStageId()) {
                return;
            }
        } else {
            error_log("No movement history found for application_id: $application_id");
        }


        $q = Doctrine_Query::create()
            ->from('Notifications b')
            ->where('b.submenu_id = ?', $stage_id)
            ->limit(1);
        $notification = $q->fetchOne();

        if ($notification && $notification->getAutosend()) {
            $template_parser = new Templateparser();
            $body = trim($notification->getContent());
            $body = $template_parser->parseApplication($application_id, $body);
            $subject = trim($notification->getTitle());
            $subject = $template_parser->parseApplication($application_id, $subject);
            $sms = trim($notification->getSms());
            $sms = $template_parser->parseApplication($application_id, $sms);

            $q = Doctrine_Query::create()
                ->from('sfGuardUserProfile b')
                ->where('b.user_id = ?', $user_id)
                ->limit(1);
            $userprofile = $q->fetchOne();
            //OTB Start Notification to others - get form elements and data for contacts to be copied
            $q = Doctrine_Query::create()
                ->from('ApFormElements b')
                ->where('b.form_id = ?', $form_id)
                ->andWhereIn('b.element_type', array('email', 'simple_phone', 'phone'))
                ->andWhere('b.element_notify_contact = ?', 1);
            $elements = $q->execute();

            $email_query = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT * FROM ap_form_" . $form_id . " WHERE id=" . $entry_id);
            $copy_emails = array();
            $copy_phone_numbers = array();
            foreach ($email_query as $eq) {
                foreach ($elements as $element) {
                    if ($element->getElementType() == "email") {
                        array_push($copy_emails, $eq['element_' . $element->getElementId()]);
                    } else if ($element->getElementType() == "simple_phone" || $element->getElementType() == "phone") {
                        array_push($copy_phone_numbers, $eq['element_' . $element->getElementId()]);
                    }
                }
            }
            //OTB End Notification to others - get form elements and data for contacts to be copied
            if ($userprofile) {
                array_push($copy_emails, $userprofile->getEmail());
                array_push($copy_phone_numbers, $userprofile->getMobile());
            }


            $notifier = new mailnotifications();
            if (strlen($body) > 0 || strlen($subject) > 0) {
                foreach ($copy_emails as $email) {
                    error_log('--------email--------' . $email);
                    $notifier->sendemail('', $email, $subject, $body);
                }
            }
            if (strlen($sms) > 0) {
                foreach ($copy_phone_numbers as $no) {
                    error_log('-------SMS-------' . $no);
                    $notifier->sendsms($no, $sms);
                }
            }

            error_log("Movement history send sms recorded.");
            if ($movement_history) {
                $movement_history->setIsSmsSent(1);
                $movement_history->save();
            }
        }
    }

    //Send notifications for current stage of the application
    public function queue_notifications_custom($id, $form_id, $entry_id, $user_id, $subject, $body)
    {
        $sms = $subject;

        $q = Doctrine_Query::create()
            ->from('sfGuardUserProfile b')
            ->where('b.user_id = ?', $user_id)
            ->limit(1);
        $userprofile = $q->fetchOne();

        if ($q->count() > 0) {
            $notifier = new mailnotifications();

            $notifier->queueemail(sfConfig::get('app_organisation_email'), $userprofile->getEmail(), $body, $body, $user_id, $id);

            if ($sms) {
                $notifier->queuesms($userprofile->getMobile(), $sms, $user_id, $id);
            }
        }
    }

    //Execute triggers for current stage if any
    public function execute_triggers($application)
    {
        $q = Doctrine_Query::create()
            ->from('SubMenus a')
            ->where('a.id = ?', $application->getApproved())
            ->limit(1);

        $current_stage = $q->fetchOne();

        if ($current_stage) {
            //1. If stage has default reviewers, assign them
            $q = Doctrine_Query::create()
                ->from("WorkflowReviewers a")
                ->where("a.workflow_id = ?", $application->getApproved());
            $workflow_reviewers = $q->execute();

            foreach ($workflow_reviewers as $reviewer) {
                //If already assigned then skip
                $q = Doctrine_Query::create()
                    ->from("Task a")
                    ->where("a.status = 1")
                    ->andWHere("a.owner_user_id = ?", $reviewer->getReviewerId());

                if ($q->count() > 0) {
                    continue;
                }

                $task = new Task();
                $task->setType(2);
                $task->setCreatorUserId($reviewer->getReviewerId());
                $task->setOwnerUserId($reviewer->getReviewerId());
                $task->setDuration("0");
                $task->setStartDate(date('Y-m-d'));
                $task->setEndDate("");
                $task->setPriority('3');
                $task->setIsLeader("0");
                $task->setActive("1");
                $task->setStatus("1");
                $task->setLastUpdate(date('Y-m-d'));
                $task->setDateCreated(date('Y-m-d'));
                $task->setRemarks("");
                $task->setTaskStage($application->getApproved());
                $task->setApplicationId($application->getId());
                $task->save();
            }

            //2. Check if all invoices are paid and application needs movement
            $invoice_manager = new InvoiceManager();

            if (!$invoice_manager->has_unpaid_invoice($application->getId()) && $invoice_manager->invoice_count($application->getId()) > 0) {
                if ($current_stage->getStageType() == 3) {
                    if ($current_stage->getStageProperty() == 2) {
                        //Move application to another stage
                        $next_stage = $current_stage->getStageTypeMovement();
                        $application->setApproved($next_stage);
                        //$application->save();
                    }
                }
            }

            //3. If all tasks are complete then move to the next stage
            if ($current_stage->getStageType() == 2) {
                if ($current_stage->getStageProperty() == 2) {
                    $q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("a.application_id = ?", $application->getId());
                    $total_tasks = $q->count();

                    $q = Doctrine_Query::create()
                        ->from("Task a")
                        ->where("a.status = ?", 1)
                        ->andWhere("a.application_id = ?", $application->getId());
                    $total_pending_tasks = $q->count();

                    $q = Doctrine_Query::create()
                        ->from("ServiceInspections a")
                        ->where("a.stage_id = ?", $current_stage->getId());
                    $inspections_required = $q->execute();

                    $all_inspections_done = true;

                    foreach ($inspections_required as $required_inspection) {
                        $done = false;

                        $q = Doctrine_Query::create()
                            ->from("Inspections a")
                            ->where("a.stage_id = ?", $current_stage->getId())
                            ->andWhere("a.application_id = ?", $application->getId())
                            ->andWhere("a.department_id = ?", $required_inspection->getDepartmentId());
                        $inspections = $q->execute();

                        foreach ($inspections as $inspection) {
                            $q = Doctrine_Query::create()
                                ->from("Task a")
                                ->where("a.id = ?", $inspection->getTaskId());
                            $task = $q->fetchOne();

                            if ($task->getStatus() != 1) {
                                $done = true;
                            }
                        }

                        if ($done == false) {
                            $all_inspections_done = false;
                        }
                    }

                    if ($all_inspections_done && $total_pending_tasks == 0 && $total_tasks > 0) {
                        //Move application to another stage
                        $next_stage = $current_stage->getStageTypeMovement();
                        $application->setApproved($next_stage);
                        //$application->save();
                    }
                }
            }
            //OTB Start - Add trigger for changing application number
            //4. If stage has change identifier set as true, change identifier and start as required
            if ($current_stage->getChangeIdentifier() == 1) {
                if ($current_stage->getChangeIdentifierCondition() && $application->getFormId()) {
                    if ($application->getFormId() == $current_stage->getChangeFieldForm()) {
                        //Check if condition has been met
                        $query = "SELECT id, element_" . $current_stage->getChangeFieldElement();
                        $query .= " from ap_form_" . $current_stage->getChangeFieldForm();
                        $query .= " WHERE id = " . $application->getEntryId();
                        $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        //Check if element has options or is a text
                        $element = $entry_details[0]["element_" . $current_stage->getChangeFieldElement()];
                        if (is_numeric($element)) {
                            //Get value
                            $value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElement(), $element);
                            $condition_value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElement(), $current_stage->getChangeFieldElementValue());
                            if (!is_null($current_stage->getChangeFieldElementValue_1())) {
                                $condition_value_1 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElement(), $current_stage->getChangeFieldElementValue_1());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_2())) {
                                $condition_value_2 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElement(), $current_stage->getChangeFieldElementValue_2());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_3())) {
                                $condition_value_3 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElement(), $current_stage->getChangeFieldElementValue_3());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_4())) {
                                $condition_value_4 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElement(), $current_stage->getChangeFieldElementValue_4());
                            }
                            error_log('---value----' . $value . '----condition_value-----' . $condition_value . '-----condition_value_1----' . $condition_value_1 . '------condition_value_2----' . $condition_value_2 . '-------condition_value_3----' . $condition_value_3 . '-----condition_value_4-----' . $condition_value_4);
                            //Compare the two
                            if (strcmp($value, $condition_value) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier();
                            } elseif (isset($condition_value_1) && strcmp($value, $condition_value_1) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_1();
                            } elseif (isset($condition_value_2) && strcmp($value, $condition_value_2) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_2();
                            } elseif (isset($condition_value_3) && strcmp($value, $condition_value_3) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_3();
                            } elseif (isset($condition_value_4) && strcmp($value, $condition_value_4) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_4();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        } else {
                            //element value is a string
                            if (strcasecmp($element, $current_stage->getChangeFieldElementValue()) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        }
                    } elseif ($application->getFormId() == $current_stage->getChangeFieldForm_1()) {
                        //Check if condition has been met
                        $query = "SELECT id, element_" . $current_stage->getChangeFieldElementForm_1();
                        $query .= " from ap_form_" . $current_stage->getChangeFieldForm_1();
                        $query .= " WHERE id = " . $application->getEntryId();
                        $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        //Check if element has options or is a text
                        $element = $entry_details[0]["element_" . $current_stage->getChangeFieldElementForm_1()];
                        if (is_numeric($element)) {
                            //Get value
                            $value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_1(), $element);
                            $condition_value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_1(), $current_stage->getChangeFieldElementValueForm_1());
                            if (!is_null($current_stage->getChangeFieldElementValue_1Form_1())) {
                                $condition_value_1 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_1(), $current_stage->getChangeFieldElementValue_1Form_1());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_2Form_1())) {
                                $condition_value_2 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_1(), $current_stage->getChangeFieldElementValue_2Form_1());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_3Form_1())) {
                                $condition_value_3 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_1(), $current_stage->getChangeFieldElementValue_3Form_1());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_4Form_1())) {
                                $condition_value_4 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_1(), $current_stage->getChangeFieldElementValue_4Form_1());
                            }
                            error_log('---value----' . $value . '----condition_value-----' . $condition_value . '-----condition_value_1----' . $condition_value_1 . '------condition_value_2----' . $condition_value_2 . '-------condition_value_3----' . $condition_value_3 . '-----condition_value_4-----' . $condition_value_4);
                            //Compare the two
                            if (strcmp($value, $condition_value) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_1();
                            } elseif (isset($condition_value_1) && strcmp($value, $condition_value_1) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_1Form_1();
                            } elseif (isset($condition_value_2) && strcmp($value, $condition_value_2) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_2Form_1();
                            } elseif (isset($condition_value_3) && strcmp($value, $condition_value_3) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_3Form_1();
                            } elseif (isset($condition_value_4) && strcmp($value, $condition_value_4) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_4Form_1();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        } else {
                            //element value is a string
                            if (strcasecmp($element, $current_stage->getChangeFieldElementValueForm_1()) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_1();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        }
                    } elseif ($application->getFormId() == $current_stage->getChangeFieldForm_2()) {
                        //Check if condition has been met
                        $query = "SELECT id, element_" . $current_stage->getChangeFieldElementForm_2();
                        $query .= " from ap_form_" . $current_stage->getChangeFieldForm_2();
                        $query .= " WHERE id = " . $application->getEntryId();
                        $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        //Check if element has options or is a text
                        $element = $entry_details[0]["element_" . $current_stage->getChangeFieldElementForm_2()];
                        if (is_numeric($element)) {
                            //Get value
                            $value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_2(), $element);
                            $condition_value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_2(), $current_stage->getChangeFieldElementValueForm_2());
                            if (!is_null($current_stage->getChangeFieldElementValue_1Form_2())) {
                                $condition_value_1 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_2(), $current_stage->getChangeFieldElementValue_1Form_2());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_2Form_2())) {
                                $condition_value_2 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_2(), $current_stage->getChangeFieldElementValue_2Form_2());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_3Form_2())) {
                                $condition_value_3 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_2(), $current_stage->getChangeFieldElementValue_3Form_2());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_4Form_2())) {
                                $condition_value_4 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_2(), $current_stage->getChangeFieldElementValue_4Form_2());
                            }
                            error_log('---value----' . $value . '----condition_value-----' . $condition_value . '-----condition_value_1----' . $condition_value_1 . '------condition_value_2----' . $condition_value_2 . '-------condition_value_3----' . $condition_value_3 . '-----condition_value_4-----' . $condition_value_4);
                            //Compare the two
                            if (strcmp($value, $condition_value) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_2();
                            } elseif (isset($condition_value_1) && strcmp($value, $condition_value_1) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_1Form_2();
                            } elseif (isset($condition_value_2) && strcmp($value, $condition_value_2) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_2Form_2();
                            } elseif (isset($condition_value_3) && strcmp($value, $condition_value_3) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_3Form_2();
                            } elseif (isset($condition_value_4) && strcmp($value, $condition_value_4) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_4Form_2();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        } else {
                            //element value is a string
                            if (strcasecmp($element, $current_stage->getChangeFieldElementValueForm_2()) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_2();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        }
                    } elseif ($application->getFormId() == $current_stage->getChangeFieldForm_3()) {
                        //Check if condition has been met
                        $query = "SELECT id, element_" . $current_stage->getChangeFieldElementForm_3();
                        $query .= " from ap_form_" . $current_stage->getChangeFieldForm_3();
                        $query .= " WHERE id = " . $application->getEntryId();
                        $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        //Check if element has options or is a text
                        $element = $entry_details[0]["element_" . $current_stage->getChangeFieldElementForm_3()];
                        if (is_numeric($element)) {
                            //Get value
                            $value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_3(), $element);
                            $condition_value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_3(), $current_stage->getChangeFieldElementValueForm_3());
                            if (!is_null($current_stage->getChangeFieldElementValue_1Form_3())) {
                                $condition_value_1 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_3(), $current_stage->getChangeFieldElementValue_1Form_3());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_2Form_3())) {
                                $condition_value_2 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_3(), $current_stage->getChangeFieldElementValue_2Form_3());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_3Form_3())) {
                                $condition_value_3 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_3(), $current_stage->getChangeFieldElementValue_3Form_3());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_4Form_3())) {
                                $condition_value_4 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_3(), $current_stage->getChangeFieldElementValue_4Form_3());
                            }
                            error_log('---value----' . $value . '----condition_value-----' . $condition_value . '-----condition_value_1----' . $condition_value_1 . '------condition_value_2----' . $condition_value_2 . '-------condition_value_3----' . $condition_value_3 . '-----condition_value_4-----' . $condition_value_4);
                            //Compare the two
                            if (strcmp($value, $condition_value) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_3();
                            } elseif (isset($condition_value_1) && strcmp($value, $condition_value_1) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_1Form_3();
                            } elseif (isset($condition_value_2) && strcmp($value, $condition_value_2) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_2Form_3();
                            } elseif (isset($condition_value_3) && strcmp($value, $condition_value_3) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_3Form_3();
                            } elseif (isset($condition_value_4) && strcmp($value, $condition_value_4) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_4Form_3();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        } else {
                            //element value is a string
                            if (strcasecmp($element, $current_stage->getChangeFieldElementValueForm_3()) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_3();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        }
                    } elseif ($application->getFormId() == $current_stage->getChangeFieldForm_4()) {
                        //Check if condition has been met
                        $query = "SELECT id, element_" . $current_stage->getChangeFieldElementForm_4();
                        $query .= " from ap_form_" . $current_stage->getChangeFieldForm_4();
                        $query .= " WHERE id = " . $application->getEntryId();
                        $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        //Check if element has options or is a text
                        $element = $entry_details[0]["element_" . $current_stage->getChangeFieldElementForm_4()];
                        if (is_numeric($element)) {
                            //Get value
                            $value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_4(), $element);
                            $condition_value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_4(), $current_stage->getChangeFieldElementValueForm_4());
                            if (!is_null($current_stage->getChangeFieldElementValue_1Form_4())) {
                                $condition_value_1 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_4(), $current_stage->getChangeFieldElementValue_1Form_4());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_2Form_4())) {
                                $condition_value_2 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_4(), $current_stage->getChangeFieldElementValue_2Form_4());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_3Form_4())) {
                                $condition_value_3 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_4(), $current_stage->getChangeFieldElementValue_3Form_4());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_4Form_4())) {
                                $condition_value_4 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_4(), $current_stage->getChangeFieldElementValue_4Form_4());
                            }
                            error_log('---value----' . $value . '----condition_value-----' . $condition_value . '-----condition_value_1----' . $condition_value_1 . '------condition_value_2----' . $condition_value_2 . '-------condition_value_3----' . $condition_value_3 . '-----condition_value_4-----' . $condition_value_4);
                            //Compare the two
                            if (strcmp($value, $condition_value) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_4();
                            } elseif (isset($condition_value_1) && strcmp($value, $condition_value_1) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_1Form_4();
                            } elseif (isset($condition_value_2) && strcmp($value, $condition_value_2) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_2Form_4();
                            } elseif (isset($condition_value_3) && strcmp($value, $condition_value_3) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_3Form_4();
                            } elseif (isset($condition_value_4) && strcmp($value, $condition_value_4) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_4Form_4();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        } else {
                            //element value is a string
                            if (strcasecmp($element, $current_stage->getChangeFieldElementValueForm_4()) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_4();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        }
                    } elseif ($application->getFormId() == $current_stage->getChangeFieldForm_5()) {
                        //Check if condition has been met
                        $query = "SELECT id, element_" . $current_stage->getChangeFieldElementForm_5();
                        $query .= " from ap_form_" . $current_stage->getChangeFieldForm_5();
                        $query .= " WHERE id = " . $application->getEntryId();
                        $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        //Check if element has options or is a text
                        $element = $entry_details[0]["element_" . $current_stage->getChangeFieldElementForm_5()];
                        if (is_numeric($element)) {
                            //Get value
                            $value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_5(), $element);
                            $condition_value = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_5(), $current_stage->getChangeFieldElementValueForm_5());
                            if (!is_null($current_stage->getChangeFieldElementValue_1Form_5())) {
                                $condition_value_1 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_5(), $current_stage->getChangeFieldElementValue_1Form_5());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_2Form_5())) {
                                $condition_value_2 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_5(), $current_stage->getChangeFieldElementValue_2Form_5());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_3Form_5())) {
                                $condition_value_3 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_5(), $current_stage->getChangeFieldElementValue_3Form_5());
                            }
                            if (!is_null($current_stage->getChangeFieldElementValue_4Form_5())) {
                                $condition_value_4 = Doctrine_Core::getTable('ApElementOptions')->getElementOptionsValue($application->getFormId(), $current_stage->getChangeFieldElementForm_5(), $current_stage->getChangeFieldElementValue_4Form_5());
                            }
                            error_log('---value----' . $value . '----condition_value-----' . $condition_value . '-----condition_value_1----' . $condition_value_1 . '------condition_value_2----' . $condition_value_2 . '-------condition_value_3----' . $condition_value_3 . '-----condition_value_4-----' . $condition_value_4);
                            //Compare the two
                            if (strcmp($value, $condition_value) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_5();
                            } elseif (isset($condition_value_1) && strcmp($value, $condition_value_1) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_1Form_5();
                            } elseif (isset($condition_value_2) && strcmp($value, $condition_value_2) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_2Form_5();
                            } elseif (isset($condition_value_3) && strcmp($value, $condition_value_3) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_3Form_5();
                            } elseif (isset($condition_value_4) && strcmp($value, $condition_value_4) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifier_4Form_5();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        } else {
                            //element value is a string
                            if (strcasecmp($element, $current_stage->getChangeFieldElementValueForm_5()) == 0) {
                                $app_identifier = $current_stage->getConditionalIdentifierForm_5();
                            } else {
                                //Return default
                                $app_identifier = $current_stage->getNewIdentifier();
                            }
                        }
                    } else {
                        //default
                        $app_identifier = $current_stage->getNewIdentifier();
                    }
                } else {
                    $app_identifier = $current_stage->getNewIdentifier();
                }
                error_log('--APP identifier--' . $app_identifier . '----');
                $new_app_id = str_replace("{Y}", date("Y"), $app_identifier); //OTB Replace, Y in identifier with current year
                //OTB ADD Only change no. if current app_id doesn't match the identifier
                if (strpos($application->getApplicationId(), $new_app_id) === false):
                    $starting_number = $current_stage->getNewIdentifierStart();
                    error_log('--APP identifier Year replaced--' . $new_app_id . '----');
                    $q = Doctrine_Query::create()
                        ->from("FormEntry a")
                        ->where("a.form_id = ? AND a.application_id LIKE ?", array($application->getFormId(), "%" . $new_app_id . "%"))
                        ->orderBy("a.application_id DESC");
                    $other_submissions = $q->count();

                    if ($other_submissions > 0 && $starting_number) {
                        $last_entry = $q->fetchOne();
                        $new_app_id = $this->change_identifier_number_generator($new_app_id, $last_entry);
                        //$new_app_id++;
                    } else if ($starting_number) {
                        $new_app_id = $new_app_id . $starting_number;
                    } else //if starting number is null, then only replace the identifier (prefix) of the app number. (separator must be included in prefix)
                    {
                        $arr = explode(substr($current_stage->getNewIdentifier(), -1), $application->getApplicationId(), 2);
                        $new_app_id = $new_app_id . $arr[1];
                    }
                    error_log('--APP identifier final--' . $new_app_id . '----');
                    //Save old app no and set
                    if ($application->getId()) {
                        Doctrine_Manager::getInstance()->getCurrentConnection()->execute("Insert into application_number_history (form_entry_id,application_number) Values ('" . $application->getId() . "','" . $application->getApplicationId() . "')");
                    }
                    $application->setApplicationId($new_app_id);
                    //$application->save();
                endif;
            }
            //OTB End - Add trigger for changing application number

        }
    }

    public function get_entry_details($form_id, $entry_id)
    {
        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');
        require_once($prefix_folder . 'includes/check-session.php');

        require_once($prefix_folder . 'includes/language.php');
        require_once($prefix_folder . 'includes/entry-functions.php');
        require_once($prefix_folder . 'includes/post-functions.php');
        require_once($prefix_folder . 'includes/users-functions.php');

        $nav = trim($_GET['nav']);

        if (empty($form_id) || empty($entry_id)) {
            die("Invalid Request");
        }

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        //get entry details for particular entry_id
        $param['checkbox_image'] = '/form_builder/images/icons/59_blue_16.png';
        $param['show_image_preview'] = true;

        $entry_details = mf_get_entry_details($dbh, $form_id, $entry_id, $param);

        return $entry_details;
    }

    public function check_and_update_json($application_id)
    {
        $application_data_json = $this->get_json($application_id);

        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');
        require_once($prefix_folder . 'includes/check-session.php');

        require_once($prefix_folder . 'includes/language.php');
        require_once($prefix_folder . 'includes/entry-functions.php');
        require_once($prefix_folder . 'includes/post-functions.php');
        require_once($prefix_folder . 'includes/users-functions.php');

        $nav = trim($_GET['nav']);

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $query = "UPDATE form_entry SET form_data = ? WHERE id = ?";

        $params = array($application_data_json, $application_id);

        $sth = mf_do_query($query, $params, $dbh);
    }

    public function check_json($application_id)
    {
        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.id = ?", $application_id);
        $application = $q->fetchOne();
        //OTB Add json to be checked if current - specially for edited apps
        $application_data_json = $this->get_json($application_id);
        if ($application->getFormData() == null || strcmp($application->getFormData(), $application_data_json) !== 0) {
            $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
            require_once($prefix_folder . 'includes/init.php');

            require_once($prefix_folder . '../../../config/form_builder_config.php');
            require_once($prefix_folder . 'includes/db-core.php');
            require_once($prefix_folder . 'includes/helper-functions.php');
            require_once($prefix_folder . 'includes/check-session.php');

            require_once($prefix_folder . 'includes/language.php');
            require_once($prefix_folder . 'includes/entry-functions.php');
            require_once($prefix_folder . 'includes/post-functions.php');
            require_once($prefix_folder . 'includes/users-functions.php');

            $nav = trim($_GET['nav']);

            $dbh = mf_connect_db();
            $mf_settings = mf_get_settings($dbh);


            $query = "UPDATE form_entry SET form_data = ? WHERE id = ?";

            $params = array($application_data_json, $application->getId());

            $sth = mf_do_query($query, $params, $dbh);
        }
    }

    public function get_json($application_id)
    {
        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.id = ?", $application_id);
        $application = $q->fetchOne();

        $entry_details = $this->get_entry_details($application->getFormId(), $application->getEntryId());

        $application_data = array();

        foreach ($entry_details as $entry_detail) {
            $application_data[] = $entry_detail;
        }

        $application_data_json = json_encode($application_data);

        return $application_data_json;
    }

    public function get_json_by_form($form_id, $entry_id)
    {
        $entry_details = $this->get_entry_details($form_id, $entry_id);

        $application_data = array();

        foreach ($entry_details as $entry_detail) {
            $application_data[] = $entry_detail;
        }

        $application_data_json = json_encode($application_data);

        return $application_data_json;
    }
    //OTB ADD GET SERVICE Fee
    public function get_service_fee_desc($service_id, $application_id)
    {
        $q = Doctrine_Query::create()
            ->from('Menus a')
            ->where('a.id = ?', $service_id);
        $service = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id);
        $application = $q->fetchOne();
        $application_manager = new ApplicationManager();
        $field_data = $application_manager->get_field_data($application->getFormId(), $application->getEntryId(), $service->getServiceFeeField());
        //error_log('---------Field data------'.$field_data.'-----Form----'.$service->getServiceForm().'---------Fee field---'.$service->getServiceFeeField());
        //First check the main service fee configured
        $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $service->getServiceForm())
            ->andWhere("a.element_id = ?", $service->getServiceFeeField())
            ->andWhere("a.option_id = ?", $field_data)
            ->andWhere("a.live = 1")
            ->orderBy("a.option_text ASC");
        $element_option = $q->fetchOne();

        if ($element_option) {
            //error_log('---------Activity ----'.$element_option->getOptionText());
            //FEE FOR BUSINESS ACTIVITY -- 
            $q = Doctrine_Query::create()
                ->from("ServiceFees a")
                ->where("a.service_id = ?", $service->getId())
                ->andWhere("a.field_id = ?", $service->getServiceFeeField())
                ->andWhere("a.option_id = ?", $element_option->getAeoId());
            $option_fee = $q->fetchOne();

            //If a fee has been configured for the data the user submitted then add it to the invoice
            $main_fee_text = '';
            $main_fee_amount = '';
            if ($option_fee) {
                //error_log("Cyclic-b #2: Found main fee of ".$top_amount." for ".$application_id.": ".$element_option->getOptionText());
                $main_fee_text = $element_option->getOptionText();
                $main_fee_amount = $option_fee->getTotalAmount();
            }
            return ['fee_desc' => $main_fee_text, 'fee_amt' => $main_fee_amount];
        }
    }
    //Generate a new merchant number for a new application
    public function generate_merchant_number($form_id)
    {
        //$lockingManager = new Doctrine_Locking_Manager_Pessimistic();

        $q = Doctrine_Query::create()
            ->from("ApMerchantGenerator a")
            ->where("a.form_id = ?", $form_id);
        $merchant_numbers = $q->fetchOne();

        if ($merchant_numbers) {
            $merchant_identifier = $merchant_numbers->getMerchantIdentifier();
            error_log('-------App no from ap_number_generator 101---' . $merchant_identifier);
            try {
                //LOCK ap_number_generator
                //$gotLock = $lockingManager->getLock($form_numbers, 'jwage');
                Doctrine_Manager::getInstance()->getCurrentConnection()->execute("LOCK TABLES ap_merchant_generator WRITE");

                //Update the value
                $merchant_identifier++;
                //Update ap_number_generator
                $merchant_numbers->setMerchantIdentifier($merchant_identifier);
                $merchant_numbers->save();

                //UNLOCK ap_number_generator
                //$lockingManager->releaseLock($form_numbers, 'jwage');
                Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UNLOCK TABLES");
            } catch (Exception $dle) {
                echo $dle->getMessage(); // handle the error
                exit;
            }
            error_log('------APp no generated----204' . $merchant_identifier);
            return $merchant_identifier;
        } else {
            error_log('-------No ap_merchant_generator found-----');
            $merchant_identifier = 0;

            $q = Doctrine_Query::create()
                ->from("ApForms a")
                ->where("a.form_id = ?", $form_id);
            $form = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.approved <> 0 AND a.declined <> 1 AND a.parent_submission = 0 AND a.merchant_identifier <> NULL')
                ->andWhere('a.form_id = ?', $form_id)
                ->orderBy("a.merchant_identifier DESC")
                ->limit(1);
            $last_app = $q->fetchOne();

            if ($last_app) {
                $new_app_id = $last_app->getMerchantIdentifier();
                $new_app_id = ++$new_app_id;
                $merchant_identifier = $new_app_id;
            } else {
                $merchant_identifier = $form->getPaymentMerchantIdentifier();
            }

            $merchant_numbers = new ApMerchantGenerator();
            $merchant_numbers->setFormId($form_id);
            $merchant_numbers->setMerchantIdentifier($merchant_identifier);
            $merchant_numbers->save();
            error_log('------APp no generated----205' . $merchant_identifier);
            return $merchant_identifier;
        }
    }
    //OTB ADD ChangeIdentifierNumberGenerator
    public function change_identifier_number_generator($identifier, $form_entry)
    {
        //Form entry object
        $last_app_no = $form_entry->getApplicationId();
        //Check if value is saved on table change_identifier_number_generator
        //LOCK
        Doctrine_Manager::getInstance()->getCurrentConnection()->execute("LOCK TABLES change_identifier_number_generator WRITE");
        $q = "SELECT * FROM change_identifier_number_generator WHERE form_id = " . $form_entry->getFormId() . " AND identifier LIKE '" . $identifier . "%' LIMIT 1";
        $last_identifier = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
        if (count($last_identifier) == 0) {
            $last_app_no++;
            //Save last value to the table
            Doctrine_Manager::getInstance()->getCurrentConnection()->execute("Insert into change_identifier_number_generator (form_id,identifier,application_no) Values (" . $form_entry->getFormId() . ",'" . $identifier . "','" . $last_app_no . "')");
            //UNLOCK
            Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UNLOCK TABLES");
            //Return new app no
            return $last_app_no;
        } else {
            //Calculate and save new app no
            $last_app_no = $last_identifier[0]['application_no'];
            $last_app_no++;
            //save on table
            Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UPDATE change_identifier_number_generator SET application_no = '" . $last_app_no . "' WHERE id = " . $last_identifier[0]['id']);
            //UNLOCK
            Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UNLOCK TABLES");
            //Return new app no
            return $last_app_no;
        }
    }
    //OTB END
    //Link a new form submission to an existing application
    public function create_linked_application($main_application, $form_id, $entry_id, $user_id)
    {
        if ($this->linked_application_exists($main_application, $form_id, $entry_id)) {
            $q = Doctrine_Query::create()
                ->from('FormEntryLinks l')
                ->where('l.formentryid = ? and l.form_id = ? and l.entry_id = ? and l.user_id = ?', [$main_application, $form_id, $entry_id]);
            $linked_app = $q->fetchOne();
            if ($linked_app) {
                return $linked_app;
            }
        } else {
            $form_link = new FormEntryLinks();
            $form_link->setFormentryid($main_application);
            $form_link->setFormId($form_id);
            $form_link->setEntryId($entry_id);
            $form_link->setUserId($user_id);
            $form_link->setDateOfSubmission(date("Y-m-d H:i:s"));
            $form_link->save();
            $this->get_application_by_id($main_application)->setApproved($this->get_submission_stage($form_id, $entry_id))->save(); //OTB Patch - Get The main application so we can set the form stage as per worklflow condition logic
        }

        return $form_link;
    }
    //Check if the form submission is already linked to an application
    public function linked_application_exists($main_application, $form_id, $entry_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntryLinks a')
            ->where('a.entry_id = ?', $entry_id)
            ->andWhere('a.form_id = ?', $form_id)
            ->andWhere('a.formentryid = ?', $main_application)
            ->limit(1);
        $existing_link = $q->fetchOne();
        if ($existing_link) //Already submitted then tell client its already submitted
        {
            return true;
        } else {
            return false;
        }
    }
    //OTB ADD
    public function revision_save($form_id, $entry_id)
    {
        $new_id = $entry_id;
        $q = Doctrine_Query::create()
            ->from('FormEntry e')
            ->where('e.form_id =? and e.entry_id =?', array($form_id, $entry_id));
        $submission = $q->fetchOne();
        if ($submission && intval($submission->getParentSubmission()) == 0 && intval($submission->getDeclined()) == 1) {
            //resolve EntryDecline
            //Doctrine_Manager::getInstance()->getCurrentConnection()->execute("Update entry_decline set resolved = 1 where entry_id = " . $submission->getId());

            error_log('------DECLINED---PARENT SUBMISSION ---0-----');
            $new_id = $this->DuplicateMySQLRecord("ap_form_" . $submission->getFormId(), "id", $submission->getEntryId());
            error_log('--------ID--------' . $new_id);
            //save revision
            $new_entry = new FormEntry();
            $new_entry->setFormId($submission->getFormId());
            $new_entry->setEntryId($submission->getEntryId());
            $new_entry->setApproved($submission->getApproved());
            $new_entry->setApplicationId($submission->getApplicationId());
            $new_entry->setUserId($submission->getUserId());
            $new_entry->setParentSubmission($submission->getId());
            $new_entry->setDeclined(1);
            $new_entry->setDateOfSubmission($submission->getDateOfSubmission());
            $new_entry->setDateOfResponse($submission->getDateOfResponse());
            $new_entry->setDateOfIssue($submission->getDateOfIssue());
            $new_entry->setObservation($submission->getObservation());
            $new_entry->save();
            //new entry
            //Doctrine_Manager::getInstance()->getCurrentConnection()->execute("Update form_entry set entry_id = " . $new_id . ", previous_submission = " . $new_entry->getId() . ", date_of_submission = '" . date('Y-m-d H:i:s') . "' where id = " . $submission->getId());


            $submission->setEntryId($new_id);

            $submission->setPreviousSubmission($new_entry->getId());
            //Update date to save when editted
            $submission->setDateOfResponse(date('Y-m-d H:i:s'));
            $submission->save();
        }
        return $new_id;
    }
    public function DuplicateMySQLRecord($table, $id_field, $id)
    {

        // load the original record into an array
        $sql = "SELECT * FROM {$table} WHERE {$id_field}={$id}";
        $original_record = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($sql);
        error_log('-------------Original record--------');
        error_log(print_r($original_record, true));
        // insert the new record and get the new auto_increment id
        $sql_new = "INSERT INTO {$table} (`{$id_field}`) VALUES (NULL)";
        Doctrine_Manager::getInstance()->getCurrentConnection()->execute($sql_new);
        $new_id = Doctrine_Manager::getInstance()->getCurrentConnection()->lastInsertId();
        error_log('-------------New insert--------');
        error_log('---------------new id-------' . $new_id);
        // generate the query to update the new record with the previous values
        $query = "UPDATE {$table} SET ";
        foreach ($original_record[0] as $key => $value) {
            error_log('-----------Key-----' . $key . '--------value------' . $value);
            if ($key != $id_field && $value != "") {
                $query .= '`' . $key . '` = "' . str_replace('"', '\"', $value) . '", ';
            }
        }
        error_log('----------UPDATE Query--------' . $query);
        $query = substr($query, 0, strlen($query) - 2); // lop off the extra trailing comma

        $query .= " WHERE {$id_field}={$new_id}";
        /*mysql_query($query,$db_connection) or die(mysql_error());*/
        Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

        // return the new id
        return $new_id;
    }
    //OTB ADD send ifc file(s)
    public function sendIfc($submission)
    {
        $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
        require_once($prefix_folder . 'includes/init.php');

        require_once($prefix_folder . '../../../config/form_builder_config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        if ($submission) {
            //get if form has ifc file
            $q = Doctrine_Query::create()
                ->select('e.element_id, e.element_title')
                ->from('ApFormElements e')
                ->where('e.element_file_ifc = ? and e.form_id = ? and e.element_status = ?', [1, $submission->getFormId(), 1]);
            $ifc_elements = $q->execute();
            if ($ifc_elements) {
                foreach ($ifc_elements as $ifc_element) {
                    $element_id = $ifc_element->getElementId();
                    //check if anything has been uploaded
                    $q = "SELECT id, element_" . $element_id;
                    $q .= " from ap_form_" . $submission->getFormId();
                    $q .= " WHERE id = " . $submission->getEntryId();
                    $entry_details = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
                    $ifc_val = $entry_details[0]["element_" . $element_id];
                    if (strlen($ifc_val)) {
                        //val found
                        //if multiple upload
                        $ifc_files = explode('|', $ifc_val);
                        foreach ($ifc_files as $ifc_file) {
                            error_log('--------IFC FILE-----' . $ifc_file);
                            error_log('--------IFC server-----' . sfConfig::get('app_ifc_server'));
                            error_log('--------IFC user-----' . sfConfig::get('app_ifc_user'));
                            error_log('--------IFC token-----' . sfConfig::get('app_ifc_tkn'));
                            $abs_url = "http" . mf_get_ssl_suffix() . "://" . $_SERVER['HTTP_HOST'];
                            error_log('------VAR EXPORT------');
                            error_log(
                                var_export(
                                    array(
                                        'api' => sfConfig::get('app_ifc_server'),
                                        'user' => sfConfig::get('app_ifc_user'),
                                        'token' => sfConfig::get('app_ifc_tkn')
                                    ),
                                    true
                                )
                            );
                            $fm = new FileManager((object) array(
                                'api' => sfConfig::get('app_ifc_server'),
                                'user' => sfConfig::get('app_ifc_user'),
                                'token' => sfConfig::get('app_ifc_tkn')
                            ), (object) ['ifcAPI' => null]);
                            $filesend = $fm->sendFile(
                                $ifc_file,
                                $_SERVER['DOCUMENT_ROOT'] . '/' . $mf_settings['upload_dir'] . $ifc_file,
                                $abs_url . '/plan/applications/ifccallback'
                            );
                            error_log('--------FILE SEND-----');
                            error_log(print_r($filesend, true));
                            error_log('---------SEND ERROR---------');
                            error_log(print_r($fm->getLastError(), true));
                        }
                    }
                }
            }
        } else {
            error_log('---------Not submission object!------');
        }
    }

    public function moveApplication(FormEntry $application, $stageId)
    {
        try {
            $q = Doctrine_Query::create()
                ->update('FormEntry a')
                ->where('a.id = ?', $application->getId())
                ->set(array('a.approved' => $stageId));
            $q->execute();

            $this->execute_triggers($application);
            return true;
        } catch (\Exception $er) {
            error_log($er->getMessage());
            return false;
        }
    }
}
