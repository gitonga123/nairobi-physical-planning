<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OTBHelper
 *
 * @author boniboy
 */
class OTBHelper
{

    /** Compare values set for application queuing
     * This function checks if value set for queuing is equal to menus value otherwise the system picks
     * the set value on submenu.
     * used to implement override of applicaton queuing set in menus for backend application listing
     */
    public function getAppQueuing($submenu_queuing, $menu_queuing)
    {
        if ($submenu_queuing == "default") {
            //user prefers to use menu setting for queuing applications - so return menu queuing as the value
            return $menu_queuing;
        } else {
            //user has set a value so return the value
            return $submenu_queuing; //override menu
        }
    }

    /** Get currency ISO code */
    public function getCurrencyISOCode($currency_id)
    {
        error_log("Currency ISO code >>> " . $currency_id);
        $q = Doctrine_Query::create()
            ->from('Currencies c')
            ->where('c.id = ? ', $currency_id);
        $query_r = $q->execute();
        $iso_code = null;
        ///
        // error_log(print_r($query_r,True));
        foreach ($query_r as $r) {
            $iso_code = $r->getCode();
        }
        //error_log("Currency >>> ".$iso_code) ;
        return $iso_code;
    }

    /** function to return an array of countries available */
    public function getCountries()
    {

        $sql = "SELECT name FROM countries ";
        //we are protected by using manager
        $sql_res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($sql);
        //
        $choices = array();

        //
        foreach ($sql_res as $c) {
            //create array;
            $choices[$c['name']] = $c['name'];
        }
        return $choices;
    }

    /** get jambopay merchant * */
    public function getJamboPayMerchant()
    {
        $sql = "SELECT id,name FROM merchant where name='jambopay' ";
        //we are protected by using manager
        $sql_res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($sql);
        //
        $choices = array();

        //
        foreach ($sql_res as $c) {
            //create array;
            $choices[$c['id']] = $c['name'];
        }
        return $choices;
    }

    /** get jambopay merchant by name 
     * merchant_id as parameter
     * * */
    public function getJambopayMerchantByName($merchant_id)
    {
        $sql = "SELECT name FROM merchant where id='$merchant_id' ";
        //we are protected by using manager
        $sql_res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($sql);
        //

        $name = null;
        foreach ($sql_res as $r) {
            $name = $r['name'];
        }
        //
        return $name;
    }

    /** Get merchant currency */
    public function getMerchantCurrency($name)
    {
        $q = Doctrine_Query::create()
            ->from('Merchant m')
            ->where('m.name = ? ', $name)
            ->limit(1);
        $r_exec = $q->execute();
        //
        $currency = 'Uknown';
        //
        foreach ($r_exec as $r) {
            $currency = $r->getCurrencyId();
        }

        return $currency;
    }

    /** Get Ap Form Merchant Type */
    public function getApFormMerchant($form_id)
    {
        $q = Doctrine_Query::create()
            ->from('ApForms a')
            ->where('a.form_id = ? ', $form_id)
            ->limit(1);
        $r_exec = $q->execute();
        //
        $merchant_type = 'Undefined';
        //
        foreach ($r_exec as $r) {
            $merchant_type = $r->getPaymentMerchantType();
        }
        //
        return $merchant_type;
    }

    public function hasCfUserAccount($email, $username)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser u')
            ->where('u.stremail =? or u.struserid =?', [$email, $username]);

        return $q->fetchOne();
    }

    public function findDepartmentByName($department)
    {

        $q = Doctrine_Query::create()
            ->from("Department d")
            ->where("d.department_name LIKE '%{$department}%'")
            ->orderBy('d.id desc');

        $department_found = $q->fetchOne();

        if (!$department_found) {
            $q = Doctrine_Query::create()
                ->from('Department d')
                ->orderBy('d.id desc');

            $department_found = $q->fetchOne();
        }

        return $department_found;
    }

    public function findGroupByName($group, $force_check = false)
    {
        error_log("Group we are ---> {$group}");

        $cache = new sfFileCache([
            'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
        ]);

        $found_cache_group = $cache->get("found_group_{$group}");



        error_log("Found group is --->", json_decode($found_cache_group));

        // if ($found_cache_group) {
        //     $group_found = json_decode($found_cache_group);

        //     return $group_found;
        // }

        $q = Doctrine_Query::create()
            ->from('mfGuardUserGroup a')
            ->leftJoin('a.MfGuardGroup m2')
            ->where("m2.name LIKE' %{$group}%'")
            ->orderBy('m2.id desc');

        $group_found = $q->fetchOne();

        if (!$group_found) {
            $group = 'reviewer';

            $q = Doctrine_Query::create()
                ->from('mfGuardUserGroup a')
                ->leftJoin('a.MfGuardGroup m2')
                ->where("m2.name LIKE '%{$group}%'")
                ->orderBy('m2.id desc');

            $group_found = $q->fetchOne();
        }

        error_log("Group found or now --->");

        if ($group_found) {
            error_log("Group found ---->");
            $cache->set("found_group_{$group}", json_encode($group_found), 3600);
        }

        return $group_found;
    }

    public function createCfUser($data)
    {
        $reviewer = new CfUser();
        $reviewer->setStrLastName($data['last_name']);
        $reviewer->setStrfirstname($data['first_name']);
        $reviewer->setStremail($data['email']);
        $reviewer->setStruserid($data['username']);
        $reviewer->setStrpassword(password_hash($data['password'], PASSWORD_BCRYPT));

        $reviewer->setStrphoneMain1($data['phone_number']);
        $reviewer->setStrdepartment($data['department']);
        $reviewer->save();

        $audit = new Audit();
        $audit->saveAudit("", "<a href=\"/plan/users/edituser?userid=" . $reviewer->getNid() . "&language=en\">added a new user</a>");

        return $reviewer;
    }

    public function assignCfUserToGroup($reviewer_id, $groups)
    {
        try {
            foreach ($groups as $group) {
                error_log("Error groups ----> {$group->getGroupId()}");
                $q = Doctrine_Query::Create()
                    ->from('mfGuardUserGroup a')
                    ->where('a.user_id = ? and a.group_id = ?', [$reviewer_id, $group->getGroupId()]);
                $user_group_exists = $q->count();
                if ($user_group_exists == 0) {
                    $usergroup = new MfGuardUserGroup();
                    $usergroup->setUserId($reviewer_id);
                    $usergroup->setGroupId($group->getGroupId());
                    $usergroup->save();
                }
            }

            return true;
        } catch (\Exception $error) {
            error_log("Error encountered assigning users to groups --->{$error->getMessage()}");

            return false;
        }
    }

    public function assignUserToAgency($reviewer_id)
    {
        $q = Doctrine_Query::create()
            ->from('Agency a')
            ->orderBy('a.name ASC');
        $agencies = $q->execute();

        foreach ($agencies as $agency) {

            $q = Doctrine_Query::create()
                ->from('AgencyUser a')
                ->where('a.user_id = ? and a.agency_id = ?', [$reviewer_id, $agency->getId()]);

            $agency_user = $q->fetchOne();
            if (!$agency_user) {
                $agency_new = new AgencyUser();
                $agency_new->setUserId($reviewer_id);
                $agency_new->setAgencyId($agency->getId());

                $agency_new->save();
            }
        }
    }

    /**
     * Function used to set sub-county on user application submission from frontend
     * parameters - sf_user and application_id
     */
    public function setSubCounty($county_name, $application_id)
    {
        //OTB patch - We get the set variable county_name 
        //Then we update the subcounty value for this application. 
        //We the update FormEntry table with for subcounty. We will improve on this to work for draft applications to
        $q_form_update = Doctrine_Query::create()
            ->UPDATE('FormEntry')
            ->SET('subcounty', '?', $county_name)
            ->WHERE('id = ?', $application_id);
        $q_form_update->execute();
    }

    /**
     * Get logged reviewer subcounty value - returns county code
     */
    public function getSubcounty($user_id)
    {

        ///

        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ? ', $user_id);
        $results = $q->execute();
        //
        $subcounty = null;
        // 
        foreach ($results as $r) {
            $subcounty = $r->getSubcounty();
        }

        return $subcounty;
    }

    /**
     * Get subcounty by name
     * Parameter = subcounty_code
     * Important Note: This should be improved to use relations
     */
    public function getSubcountyByName($subcounty_code)
    {
        if ($subcounty_code) {
            $get_county_name = Doctrine_Query::create()
                ->from('Counties c')
                ->where('c.county_code = ? ', $subcounty_code)
                ->limit(1);
            $res_county = $get_county_name->fetchOne();
            //
            return $res_county->getCountyName();

        } else {
            return "Uknown";
        }
    }

    /**
     * Get subcounty by id
     * Parameter = subcounty_name
     * Important Note: This should be improved to use relations
     */
    public function getSubcountyById($name)
    {

        $get_county_id = Doctrine_Query::create()
            ->from('Counties c')
            ->where('c.county_code = ? ', $name)
            ->limit(1);
        $res_county = $get_county_id->fetchOne();
        if ($res_county) {
            return $res_county->getId();
        } else {
            return false;
        }
        //

    }

    /**
     * Get sub-county number
     */
    public function getSubcountyNumber($subcounty_code)
    {
        try {
            $get_county_name = Doctrine_Query::create()
                ->from('Counties c')
                ->where('c.county_code = ? ', $subcounty_code)
                ->limit(1);
            $res_county = $get_county_name->fetchOne();
            //
            return $res_county->getNumber();
        } catch (Exception $ex) {
            error_log("getSubcountyNumber() Error " . $ex->getMessage());
        }
    }

    /**
     * Get sub-county code
     */
    public function getSubcountycode($name)
    {
        try {
            $get_county_name = Doctrine_Query::create()
                ->from('Counties c')
                ->where('c.county_code = ? ', $name)
                ->limit(1);
            $res_county = $get_county_name->fetchOne();
            //
            // error_log("County to select ".$name);
            //
            return $res_county->getCountyCode();
        } catch (Exception $ex) {
            error_log("getSubcountycode() Error " . $ex->getMessage());
        }
    }

    /** Update Generated number and append subcounty code at the start of the string */
    public function updateAppNumber($application_id, $subcounty_code)
    {
        //get the submission identification (Application) number of the form entry 
        $app_q = Doctrine_Query::create()
            ->from('FormEntry f')
            ->WHERE('id = ?', $application_id)
            ->limit(1);
        $res = $app_q->fetchOne();
        //
        $old_number = $res->getApplicationId();
        //we append a string prefix for the application number
        $new_number = "INV-" . $subcounty_code . "-" . $old_number;
        //update number
        $update_number = Doctrine_Query::create()
            ->UPDATE('FormEntry')
            ->SET('application_id', '?', $new_number)
            ->WHERE('id = ?', $application_id);
        $update_number->execute();
        //
        return $new_number;
    }

    /**
     * Change application number after payment from INV
     * This funtion removes INV- from an application number
     * 
     */
    public function changeAppNumber($app_number)
    {
        //change application number for applications with prefix INV - This prevents change of numbers from resubmissions of 
        //application sent back after circulations
        if (strpos($app_number, "INV-") !== false) {
            $new_no = str_replace("INV-", "", $app_number);
            return $new_no;
        } else {
            //just return the same name
            return $app_number;
        }
    }

    /** OTB patch - Return an array of existing counties
     *  */
    public function getCounties()
    {
        //query
        $q = Doctrine_Query::create()
            ->from('Counties c');
        //execute
        $counties = $q->execute();
        //return results
        return $counties;
    }

    /**
     * Get Application Sub-county value
     */
    public function getApplicationSubcounty($app_id)
    {
        try {
            //error_log("App id ".$app_id) ;
            $query = Doctrine_Query::create()
                ->from("FormEntry f")
                ->where("f.id = ? ", $app_id);
            //
            $query_res = $query->fetchOne();
            //
            return $this->getSubcountycode($query_res->getSubcounty());
        } catch (Exception $ex) {
            error_log("getApplicationSubcounty() Error " . $ex->getMessage());
        }
    }

    /**
     * Get Elements the user should edit on the frontend
     * after submissions
     */
    public function getEditFields($app_id)
    {
        $edit_fields = null;
        //
        $q = Doctrine_Query::create()
            ->from("EntryDecline a")
            ->where("a.entry_id = ?", $app_id)
            ->andWhere("a.resolved = 0");
        $comments = $q->execute();
        foreach ($comments as $comment) {
            $edit_fields = json_decode($comment->getEditFields(), TRUE);
        }
        //return an array of fields
        return $edit_fields;
    }

    /** Function to get an invoice for an application with settings as invoicing
     * 
     */
    public function getInvoiceFromApplication($application_id)
    {
        try {
            $q = Doctrine_Query::create()
                ->from("MfInvoice m")
                ->where("m.app_id = ? ", $application_id)
                ->addwhere("m.paid = ? ", 1);
            $invoice_details = $q->fetchOne();
            //
            //$invoice_id = $invoice_details->getId() ;
            //pass the invoice details to function resposible for sending information to county pro system
            // $this->sendInvoiceToCountyPro($invoice_details, $application_id);
        } catch (Exception $ex) {
            error_log("getInvoiceFromApplication() Error " . $ex->getMessage());
        }
    }

    /**
     * function to get invoice from application and call save function responsible for saving 
     * details that will be sent to countypro
     */
    public function getInvoiceFromApplicationAndSave($application_id)
    {
        try {
            $q = Doctrine_Query::create()
                ->from("MfInvoice m")
                ->where("m.app_id = ? ", $application_id)
                ->addwhere("m.paid = ? ", 1);
            $invoice_details = $q->fetchOne();
            //
            //just save the invoice 
            // $this->saveInfoToSendToCountyPro($invoice_details, $application_id); //we create a table that we will do an 
            //insert and save data that we can retrieve and send later. This is to avoid delays with countypro processing. We want to allow 
            //reviewers continue with processing other applications without having to wait untill the system sends the invoice.
        } catch (Exception $ex) {
            error_log("getInvoiceFromApplication() Error " . $ex->getMessage());
        }
    }

    /**
     * Get user_id or the user who submitted a particular application 
     */
    public function getApplicationOwner($app_id)
    {
        try {
            $q = Doctrine_Query::create()
                ->from("FormEntry f")
                ->where("f.id = ? ", $app_id);
            $q_res = $q->fetchOne();
            //
            return $q_res->getUserId();
        } catch (Exception $ex) {
            error_log("getApplicationOwner() Error " . $ex->getMessage());
        }
    }

    /**
     * Get Application owner details
     * 
     */
    public function getApplicationOwnerDetails($owner_id)
    {
        try {
            error_log("User Id >>> " . $owner_id);
            $q = Doctrine_Query::create()
                ->from("SfGuardUserProfile u")
                ->where("u.user_id = ? ", $owner_id);
            $q_res = $q->fetchOne();
            return $q_res;
        } catch (Exception $ex) {
            error_log("getApplicationOwnerDetails() Error " . $ex->getMessage());
        }
    }

    /**
     * Get Reviewer User Details
     * 
     */
    public function getReviewerDetails($user_id)
    {
        try {
            $q = Doctrine_Query::create()
                ->from("CfUser u")
                ->where("u.nid = ? ", $user_id);
            $q_res = $q->execute();
            return $q_res;
        } catch (Exception $ex) {
            error_log("getReviewerDetails() Error " . $ex->getMessage());
        }
    }

    /**
     * get Application details
     */
    public function getApplicationDetails($app_id)
    {
        try {
            $q = Doctrine_Query::create()
                ->from('FormEntry f')
                ->where('f.id = ? ', $app_id)
                ->limit(1);
            $res = $q->fetchOne();
            return $res;
        } catch (Exception $ex) {
            error_log("getApplicationDetails() Error " . $ex->getMessage());
        }

    }
    /**
     * Helper for calculating days. Borrowed from thomas work
     */
    public function GetDays($sStartDate, $sEndDate)
    {
        $aDays[] = $sStartDate;
        $start_date = $sStartDate;
        $end_date = $sEndDate;
        $current_date = $start_date;
        while (strtotime($current_date) <= strtotime($end_date)) {
            $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
            $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
        }

        // error_log(print_r($aDays, true));
        return $aDays;
    }

    /**
     * Get days an application has stayed in a particular stage
     */
    public function getAppStageStayedDays($stage_id, $app_id)
    {
        $q = Doctrine_Query::create()
            ->from('ApplicationReference b')
            ->where('b.stage_id = ?', $stage_id)
            ->andWhere('b.application_id = ?', $app_id);
        $application_reference = $q->fetchOne();
        //
        if ($application_reference) {
            // $days = sizeOf($this->GetDays($application_reference->getStartDate(), date('Y-m-d')));
            //
            $current_date = time();
            $start_date = strtotime($application_reference->getStartDate());
            $mydays = $current_date - $start_date;
            $days = floor($mydays / 86400);
            // $dys = round($mydays / 86400, 0);
            //  error_log("getAppStageStayedDays() My days ".  floor($mydays/86400));
            return $days;
        } else {
            //calculate days stayed
            $q = Doctrine_Query::create()
                ->from('FormEntry e')
                ->where('e.id =?', $app_id);
            $app = $q->fetchOne();
            if ($app) {
                //calculate
                $submitted_date = strtotime($app->getDateOfSubmission());
                $time_diff = time() - $submitted_date;
                return round($time_diff / 86400);
            } else {
                return 0;
            }
        }

    }

    /**
     * Get stage set max duration an application is allowed to stay
     */
    public function getStageMaxDays($stage_id)
    {
        $q = Doctrine_Query::create()
            ->from('SubMenus a')
            ->where('a.id = ?', $stage_id);
        $stage = $q->fetchOne();
        //
        return $stage->getMaxDuration();
    }

    /**
     * get stage name using stage_id
     */
    public function getStageName($stage_id)
    {
        $q = Doctrine_Query::create()
            ->from('SubMenus a')
            ->where('a.id = ?', $stage_id);
        $stage = $q->fetchOne();
        //
        return $stage->getTitle();
    }

    /**
     * Insert or update form details in ap_form_field_preload_settings table
     */
    public function updateOrInsertLogic($form_id, $base_element_id, $affected_element_id, $data_source_table_name, $data_source_option_column_name_value, $data_source_option_column_name_label)
    {

        //first do a select and determine if its an update
        $select_query = "select * from ap_forms_field_preload_settings where form_id = '$form_id' ";
        $select_query_res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($select_query);
        //
        if (count($select_query_res) > 0) {
            //update existing
            $statement_up = "UPDATE ap_forms_field_preload_settings SET  base_element_id = '$base_element_id', "
                . "affected_element_id = '$affected_element_id' , table_name = '$data_source_table_name', table_optional_value = '$data_source_option_column_name_value',table_optional_label = '$data_source_option_column_name_label' ";
            $this->myDBWrapper($statement_up);
        } else {
            //insert new
            $statement_in = "INSERT INTO ap_forms_field_preload_settings (form_id,base_element_id,affected_element_id,table_name,table_optional_value,table_optional_label) "
                . "VALUES($form_id,$base_element_id,$affected_element_id,'$data_source_table_name','$data_source_option_column_name_value','$data_source_option_column_name_label')";
            $this->myDBWrapper($statement_in);
            error_log("Query executed >>>> " . $statement_in);

        }
    }

    /**
     * Get Element Base field affected used for our custom form logic to pre-load a field data
     */
    public function getFormLogicBaseField($form_id)
    {

        $base_field_id = null;
        $base_field_title = "None";
        $statement = "SELECT base_element_id FROM ap_forms_field_preload_settings WHERE form_id = $form_id ";
        //
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        foreach ($res as $r) {
            $base_field_id = $r['base_element_id'];
        }
        if ($base_field_id) {
            //select element title
            $statement2 = "SELECT element_title FROM ap_form_elements WHERE element_id = $base_field_id AND form_id = $form_id";
            $res2 = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement2);
            foreach ($res2 as $r) {
                $base_field_title = $r['element_title'];
            }
        }

        return $base_field_title;

    }

    /**
     * Get Element Affected field info
     */
    public function getFormLogicAffectedField($form_id)
    {

        $affected_field_id = null;
        $affected_field_title = "None";
        $statement = "SELECT affected_element_id FROM ap_forms_field_preload_settings WHERE form_id = $form_id ";
        //
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        foreach ($res as $r) {
            $affected_field_id = $r['affected_element_id'];
        }
        if ($affected_field_id) {
            //select element title
            $statement2 = "SELECT element_title FROM ap_form_elements WHERE element_id = $affected_field_id AND form_id = $form_id";
            $res2 = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement2);
            foreach ($res2 as $r) {
                $affected_field_title = $r['element_title'];
            }
        }
        return $affected_field_title;

    }
    /**
     * Get table info that stores data for pre-loading fields data (affected fields data source)
     */
    public function getFormLogicTableInfo($form_id)
    {
        $statement = "SELECT table_name as name,table_optional_value as value,table_optional_label as label FROM ap_forms_field_preload_settings WHERE form_id = $form_id ";
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        return $res;

    }

    /**
     * return a list of zones for a specified sub-county
     */
    public function getZones($subcounty_id)
    {

        $statement = "SELECT id,name FROM zone WHERE sub_county = $subcounty_id";
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        return $res;
    }
    /**
     * return permitteduser for a selected zone
     * this should return permitted plot ratio and permitted ground coverage
     */
    public function getPermittedUser($zone_id)
    {
        $statement = "SELECT id,name FROM permitted_user WHERE zone = $zone_id";
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        return $res;
    }
    /**
     * return permitted plot ratio for a certain use for a zone
     */
    public function getPermittedPlotRatio($permitted_user_id)
    {
        $statement = "SELECT plot_ratio FROM permitted_user WHERE id = $permitted_user_id";
        error_log("Debug >>>>>" . $statement);
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        return $res;
    }

    /**
     * return permitted plot ratio for a certain use for a zone
     */
    public function getPermittedGroundCoverage($permitted_user_id)
    {
        $statement = "SELECT ground_coverange as ground_coverage FROM permitted_user WHERE id = $permitted_user_id";
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        return $res;
    }

    /**
     * get details from table ap_forms_field_preload_settings
     */
    public function getOnchangeDropdownFieldsSettings($form_id)
    {
        $statement = "SELECT form_id as form, base_element_id as base_field,affected_element_id as affected_field,table_name as table_name,"
            . "table_optional_value as value, table_optional_label as label FROM ap_forms_field_preload_settings WHERE form_id = $form_id ";
        $results = array();
        $values = array();
        //
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        foreach ($res as $r) {
            $values['form_id'] = $r['form'];
            $values['base_field_id'] = $r['base_field'];
            $values['affected_field_id'] = $r['affected_field'];
            $values['table_name'] = $r['table_name'];
            $values['option_value'] = $r['value'];
            $values['option_label'] = $r['label'];
            //error_log("BaseField >>>> ". $r['base_field']);
            //
            array_push($results, $values);
        }
        return $results;
    }
    /**
     * get details from table ap_forms_field_preload_settings difference is that this
     * function uses form_id and base_field_id
     */
    public function getOnchangeDropdownFieldsSettings2($form_id, $base_field)
    {
        $statement = "SELECT form_id as form, base_element_id as base_field,affected_element_id as affected_field,table_name as table_name,"
            . "table_optional_value as value, table_optional_label as label FROM ap_forms_field_preload_settings WHERE form_id = $form_id ";
        $results = array();
        $values = array();
        //
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        foreach ($res as $r) {
            $values['form_id'] = $r['form'];
            $values['base_field_id'] = $r['base_field'];
            $values['affected_field_id'] = $r['affected_field'];
            $values['table_name'] = $r['table_name'];
            $values['option_value'] = $r['value'];
            $values['option_label'] = $r['label'];
            //error_log("BaseField >>>> ". $r['base_field']);
            //
            array_push($results, $values);
        }
        return $results;
    }
    /***
     * get data to display on affected field element
     */
    public function getPreloadInfoData($tabletoselectfrom, $option_value, $option_label, $fk_id, $affected_element_id)
    {
        //make fk column configurable
        $statement = "SELECT $option_value,$option_label FROM $tabletoselectfrom WHERE zone = $fk_id ";
        $results = array();
        $values = array();
        //
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        foreach ($res as $r) {
            $values['value'] = $r["" . $option_value . ""];
            $values['label'] = $r["" . $option_label . ""];
            $values['affected_element_id'] = $affected_element_id;
            array_push($results, $values);
        }
        return $results;
    }

    /**
     * OTB function for database connection
     */
    public function myDBWrapper($query, $type = 0)
    {

        try {
            // Get Connection of Database
            $connection = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
            // Make Statement
            $statement = $connection->prepare($query);
            // Execute Query
            $res = $statement->execute();
            //
            return $res;
        } catch (Exception $ex) {
            error_log("Ooops!! Something is wrong " . $ex->getMessage());
            error_log("Debug Query is " . $query);
            return false;
        }

    }
    /**
     * Function to convert a month number to month name
     */
    public function convertMonthNumberToMonthName($num)
    {
        $monthNum = $num;
        $monthName = "Uknown";
        if ($monthNum) {
            $dateObj = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('F');
        } else {
            //do nothing
            error_log("convertMonthNumberToMonthName() failed. Month Number supplied is " . $monthNum);
        }
        return $monthName;
    }

    /**
     *  Kibes Idea
     * @param type $number
     * @return boolean
     * 
     */
    public function convert_number_to_words($number)
    {

        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $this->uppercaseString($string); //Return string capitalized for all first letters in each string
    }
    /**
     * function to convert each character of each word in a string
     * http://php.net/manual/en/function.ucwords.php
     */
    public function uppercaseString($string)
    {

        $word = ucwords(strtolower($string));
        return $word;
    }

    /**
     * function to add thousands separator
     */
    public function formatFigures($number)
    {
        $formatted = number_format($number);
        return $formatted;
    }

    /**
     * check if an application is already shared to avoid double entry in form_entry_shares
     * 
     */
    public function checkifAppShared($receiverid, $app_id)
    {
        $q = Doctrine_Query::create()
            ->from("FormEntryShares f")
            ->where("f.receiverid = ? ", $receiverid)
            ->addWhere("f.formentryid = ? ", $app_id)
            ->limit(1);
        $res = $q->execute();

        if (count($res) > 0) {
            return "shared";
        } else {
            return "not_shared";
        }

    }
    /**
     * Method to return job role from a user category of a registered frontend user
     */
    public function getRole($user_id)
    {
        error_log("User Id " . $user_id);
        $statement = "SELECT r.role from sf_guard_user_profile p LEFT JOIN sf_guard_user_categories r ON p.registeras = r.id WHERE p.user_id = $user_id ";
        $role = 'others';
        $res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($statement);
        foreach ($res as $r) {
            $role = $r['role'];
        }
        return $role;
        //            try{
//            $q = Doctrine_Query::create()
//                    ->from('SfGuardUserProfile p')
//                    ->leftJoin('SfGuardUserCategories c')
//                    ->where('p.user_id = ?',$user_id) 
//                     ->limit(1);
//            $res = $q->execute() ;
//            return $res ;
//                    
//            }  catch (Exception $ex){
//                
//            }
    }

    /**
     * check if linked form is already submitted
     */
    public function checkLinkedFormIsSubmitted($app_id)
    {
        $q = Doctrine_Query::create()
            ->from("FormEntryLinks a")
            ->where("a.formentryid = ?", $app_id);
        $links = $q->execute();
        //
        $submitted = false;
        foreach ($links as $link) {
            $q = Doctrine_Query::create()
                ->from("ApForms a")
                ->where("a.form_id = ?", $link->getFormId());
            $linkedform = $q->fetchOne();
            if ($linkedform) {
                $submitted = true;
            } else {
                //do nothing
            }
        }
        return $submitted;
    }
    /**
     * Convert date to human readable mode
     * 
     */
    public function convertDateToHumanFriendlyMode($date_string)
    {
        $date = date_create($date_string);
        return date_format($date, 'l jS \of F Y');
    }
    /**
     * Convert date for invoice
     */
    public function convertDateForInvoice($date_string)
    {
        $date = date_create($date_string);
        return date_format($date, 'jS F Y');
    }
    /**
     * get backend user details
     */
    public function getBackendUserDetails($user_id)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser c')
            ->where('nid = ? ', $user_id);
        $res = $q->fetchOne();

        return $res;

    }
    /**
     * Get Submenu by ID
     */
    public function getStageDetails($stage_id)
    {
        $q = Doctrine_Query::create()
            ->from('Submenus s')
            ->where('s.id = ? ', $stage_id)
            ->limit(1);
        $res = $q->execute();
        return $res;

    }
    /**
     * Get invoices templates
     */
    public function getInvoiceTemplates()
    {
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates t');
        $res = $q->execute();
        //
        $invoice_temps = array();
        $invoice_temps['0'] = 'NUll';
        foreach ($res as $value) {

            $invoice_temps[$value->getId()] = $value->getTitle();
        }
        return $invoice_temps;

    }
    /**
     * Check if user has pending invoicing tasks for selected application
     * 
     */
    public function hasPendingInvoicingTask($user_id, $app_id)
    {
        $user_id = $user_id;
        $app_id = $app_id;
        $type = 3; //invoicing task
        //
        $q = Doctrine_Query::create()
            ->from('Task t')
            ->where('t.owner_user_id = ? ', $user_id)
            ->addWhere('t.application_id = ?', $app_id)
            ->andWhere('t.type = ? ', $type);
        $results = $q->execute();
        //
        if (count($results)) {
            return 'yes';
        } else {
            return 'no';
        }
    }
    /**
     * Get Logged user Task id
     */
    public function getUserTaskId($user, $app)
    {
        $user_id = $user;
        $app_id = $app;
        error_log("App >>>> " . $app_id);
        error_log("User >>>> " . $user_id);
        $type = 3; //invoicing task
        //
        $q = Doctrine_Query::create()
            ->from('Task t')
            ->where('t.owner_user_id = ? ', $user_id)
            ->addWhere('t.application_id = ?', $app_id)
            ->andWhere('t.type = ? ', $type);
        $results = $q->execute();
        //
        $task_id = null;
        foreach ($results as $r) {
            $task_id = $r->getId();
        }
        return $task_id;
    }

    /* OTB - Start Form builder useful functions */
    public function getFieldLabel($form_id, $field_id)
    {
        $q = Doctrine_Query::create()
            ->from('ApFormElements a')
            ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $field_id))
            ->limit(1);
        $element = $q->fetchOne();
        return $element->getElementTitle();
    }

    public function getFormElements($form_id)
    {
        $q = Doctrine_Query::create()
            ->from('ApFormElements a')
            ->where('a.form_id = ?', array($form_id));
        return $q->execute();
    }

    public function get_fields_html_markup($form_id, $element_ids)
    {
        //$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/cp_machform/";
        $prefix_folder = "vendor/form_builder/";
        require($prefix_folder . 'includes/view-functions.php');
        require($prefix_folder . 'includes/helper-functions.php');
        require($prefix_folder . 'includes/db-core.php');
        require($prefix_folder . '../../../config/form_builder_config.php');
        $dbh = mf_connect_db();

        $form_html = "";
        foreach ($element_ids as $estimation_element_id) {
            //Get Options
            $params = array($form_id, $estimation_element_id);
            $query = "SELECT
					aeo_id,
							element_id,
							option_id,
							`position`,
							`option`,
                            `option_text`,
							option_is_default
						FROM
							" . MF_TABLE_PREFIX . "element_options
					   where
							form_id = ? and live=1 and element_id = ?
					order by
							element_id asc,`option` asc";

            $sth = mf_do_query($query, $params, $dbh);
            while ($row = mf_do_fetch_result($sth)) {
                $element_id = $row['element_id'];
                $option_id = $row['option_id'];
                $options_lookup[$element_id][$option_id]['aeo_id'] = $row['aeo_id'];
                $options_lookup[$element_id][$option_id]['position'] = $row['position'];
                $options_lookup[$element_id][$option_id]['option'] = $row['option_text'];



                $sql = "SELECT * FROM ext_translations WHERE field_id = ? AND option_id = ? AND field_name = 'option' AND table_class = 'ap_element_options' AND locale = ?";
                $params = array($row['aeo_id'], $option_id, $locale);
                $translation_sth = mf_do_query($sql, $params, $dbh);
                $translation_row = mf_do_fetch_result($translation_sth);
                if ($translation_row) {
                    $options_lookup[$element_id][$option_id]['option'] = $translation_row['trl_content'];
                }

                $options_lookup[$element_id][$option_id]['option_is_default'] = $row['option_is_default'];

                if (isset($element_prices_array[$element_id][$option_id])) {
                    $options_lookup[$element_id][$option_id]['price_definition'] = $element_prices_array[$element_id][$option_id];
                }
            }
            //End Get Options

            $params = array($form_id, $estimation_element_id);
            $query = "SELECT *
						FROM ap_form_elements
					   WHERE
							form_id = ? and element_status='1' and element_type <> 'page_break' and element_id = ?
					ORDER BY
							element_position asc";

            $sth = mf_do_query($query, $params, $dbh);
            while ($row = mf_do_fetch_result($sth)) {
                $element_data = new stdClass();
                //lookup element options first
                if (!empty($options_lookup[$element_id])) {
                    $element_options = array();
                    $i = 0;
                    foreach ($options_lookup[$element_id] as $option_id => $data) {
                        $element_options[$i] = new stdClass();
                        $element_options[$i]->id = $option_id;
                        $element_options[$i]->option = $data['option'];

                        $sql = "SELECT * FROM ext_translations WHERE field_id = ? AND option_id = ? AND field_name = 'option' AND table_class = 'ap_element_options' AND locale = ?";
                        $params = array($data['aeo_id'], $element_options[$i]->id, $locale);
                        $translation_sth = mf_do_query($sql, $params, $dbh);
                        $translation_row = mf_do_fetch_result($translation_sth);
                        if ($translation_row) {
                            $element_options[$i]->option = $translation_row['trl_content'];
                        }

                        $element_options[$i]->is_default = $data['option_is_default'];
                        $element_options[$i]->is_db_live = 1;

                        if (isset($data['price_definition'])) {
                            $element_options[$i]->price_definition = $data['price_definition'];
                        }

                        $i++;
                    }
                }
                if (!empty($element_options)) {
                    $element_data->options = $element_options;
                } else {
                    $element_data->options = '';
                }
                $element_data->title = nl2br($row['element_title']);
                $element_data->guidelines = $row['element_guidelines'];
                $element_data->size = $row['element_size'];
                $element_data->is_required = $row['element_is_required'];
                $element_data->is_unique = $row['element_is_unique'];
                $element_data->is_private = $row['element_is_private'];
                $element_data->type = $row['element_type'];
                $element_data->position = $row['element_position'];
                $element_data->id = $row['element_id'];
                $element_data->is_db_live = 1;
                $element_data->form_id = $form_id;
                $element_data->choice_has_other = (int) $row['element_choice_has_other'];
                $element_data->choice_other_label = $row['element_choice_other_label'];
                $element_data->choice_columns = (int) $row['element_choice_columns'];
                $element_data->time_showsecond = (int) $row['element_time_showsecond'];
                $element_data->time_24hour = (int) $row['element_time_24hour'];
                $element_data->address_hideline2 = (int) $row['element_address_hideline2'];
                $element_data->address_us_only = (int) $row['element_address_us_only'];
                $element_data->date_enable_range = (int) $row['element_date_enable_range'];
                $element_data->date_range_min = $row['element_date_range_min'];
                $element_data->date_range_max = $row['element_date_range_max'];
                $element_data->date_enable_selection_limit = (int) $row['element_date_enable_selection_limit'];
                $element_data->date_selection_max = (int) $row['element_date_selection_max'];
                $element_data->date_disable_past_future = (int) $row['element_date_disable_past_future'];
                $element_data->date_past_future = $row['element_date_past_future'];
                $element_data->date_disable_weekend = (int) $row['element_date_disable_weekend'];
                $element_data->date_disable_specific = (int) $row['element_date_disable_specific'];
                $element_data->date_disabled_list = $row['element_date_disabled_list'];
                $element_data->file_enable_type_limit = (int) $row['element_file_enable_type_limit'];
                $element_data->file_block_or_allow = $row['element_file_block_or_allow'];
                $element_data->file_type_list = $row['element_file_type_list'];
                $element_data->file_as_attachment = (int) $row['element_file_as_attachment'];
                $element_data->file_enable_advance = (int) $row['element_file_enable_advance'];
                $element_data->table_name = $row['element_table_name'];
                $element_data->field_value = $row['element_field_value'];
                $element_data->field_error_message = $row['element_field_error_message'];
                $element_data->field_name = $row['element_field_name'];
                $element_data->option_query = $row['element_option_query'];
                $element_data->existing_form = $row['element_existing_form'];
                $element_data->existing_stage = $row['element_existing_stage'];
                $element_data->file_auto_upload = (int) $row['element_file_auto_upload'];
                $element_data->file_enable_multi_upload = (int) $row['element_file_enable_multi_upload'];
                $element_data->file_max_selection = (int) $row['element_file_max_selection'];
                $element_data->file_enable_size_limit = (int) $row['element_file_enable_size_limit'];
                $element_data->file_size_max = (int) $row['element_file_size_max'];
                $element_data->matrix_allow_multiselect = (int) $row['element_matrix_allow_multiselect'];
                $element_data->matrix_parent_id = (int) $row['element_matrix_parent_id'];
                $element_data->upload_dir = $mf_settings['upload_dir'];
                $element_data->range_min = $row['element_range_min'];
                $element_data->range_max = $row['element_range_max'];
                $element_data->range_limit_by = $row['element_range_limit_by'];
                $element_data->jsondef = $row['element_jsondef'];
                $element_data->css_class = $row['element_css_class'];
                $element_data->machform_path = $machform_path;
                $element_data->machform_data_path = $machform_data_path;
                $element_data->section_display_in_email = (int) $row['element_section_display_in_email'];
                $element_data->section_enable_scroll = (int) $row['element_section_enable_scroll'];
                $element_data->select_options = (int) $row['element_select_options'];
                $form_html .= call_user_func('mf_display_' . $element_data->type, $element_data);
            }
        }
        return $form_html;
    }

    /* OTB - End Form builder useful functions */
    //OTB Start - Password change from old system - also used for password expire
    // validate a user password using old md5
    public function checkPassOldMethod($user_id, $password)
    {

        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $user_id)
            ->andWhere('a.bdeleted = ?', 0);
        $available_user = $q->fetchOne();
        //
        if ($available_user) {
            //$hash = $available_user->getStrpassword();
            $strMd5Password = md5($password);
            if ($strMd5Password == $available_user->getStrpassword()) {
                //valid password
                return true;
            } else {
                //invalid password
                return false;
            }
        }
    }
    /**
     * check if user has changed password
     */
    public function hasUserChangedPassword($user_id)
    {
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $user_id)
            ->andWhere('a.bdeleted = ?', 0);
        $available_user = $q->fetchOne();
        if ($available_user->getPassChange() == 0) {
            return false;
        } else {
            return true;
        }
    }
    //OTB Start - Password change from old system - also used for password expire

    //OTB check if stage is a shared stage
    public function isSharedStage($sub_menu)
    {
        $stage = Doctrine_Core::getTable('SubMenus')->find($sub_menu);
        if ($stage) {
            if (!is_null($stage->getSharedStageMove()) && $stage->getSharedStageMove() && $stage->getSharedStage()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**

    * Get Application details using form_id and entryid

    */

    public function getApplicationDetailsUsingCompositeDetails($form_id, $entryid)
    {

        $q = Doctrine_Query::create()

            ->from('FormEntry f')

            ->where('f.form_id = ? ', $form_id)

            ->andWhere('f.entry_id = ? ', $entryid);

        $res = $q->fetchOne();

        return $res;



    }

    /**

     * Get applications menu id using submenu id

     */

    public function getMenuId($submenu_id)
    {

        error_log("Submenu to search for " . $submenu_id);

        $q = Doctrine_Query::create()

            ->from('SubMenus sm')

            ->where('sm.id = ? ', $submenu_id);

        //

        $res = $q->fetchOne();

        return $res;

    }

    /**

     * Get all stages with stage type approved for a selected menu

     */

    public function getStageWithSetStageType($stage_type = 4)
    {

        //error_log("Stage Type >>> ".$stage_type);

        //error_log("Menu >>>>>> ".$menu_id);

        $q = Doctrine_Query::create()
            ->select('s.id')
            ->from('SubMenus s')

            ->where('s.stage_type = ?', $stage_type);

        //->andWhere('s.menu_id = ?', $menu_id) ;

        $res = $q->fetchArray();
        $arr_approved_submenu_ids = array();
        foreach ($res as $r) {
            $arr_approved_submenu_ids[] = $r['id'];
        }

        return $arr_approved_submenu_ids;



    }

    /**

     * Check if an application was approved

     * This is done by checking if the application has a entry of submenu supplied which is of stage type 4 = approved

     */

    public function checkApplicationApproved($submenu_ids, $app_id)
    {
        error_log(print_r($submenu_ids, true));

        $q = Doctrine_Query::create()

            ->from('ApplicationReference ref')

            ->whereIn('ref.stage_id', $submenu_ids)

            ->andWhere('ref.application_id = ?', $app_id);

        //

        $res = $q->execute();

        if (count($res)) {

            return true; //approved

        } else {

            return false; //not approved

        }

    }

    //Return JSON decode errors
    public function json_decode_error_list($error)
    {
        switch ($error) {
            case JSON_ERROR_NONE:
                return ('JSON-DECODE - No errors');
                break;
            case JSON_ERROR_DEPTH:
                return ('JSON-DECODE - Maximum stack depth exceeded');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return ('JSON-DECODE - Underflow or the modes mismatch');
                break;
            case JSON_ERROR_CTRL_CHAR:
                return ('JSON-DECODE - Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                return ('JSON-DECODE - Syntax error, malformed JSON');
                break;
            case JSON_ERROR_UTF8:
                return ('JSON-DECODE - Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            default:
                return ('JSON-DECODE - Unknown error');
                break;
        }
    }
    public function check_payment_jambo_pay($token, $billing_reference_number)
    {
        $url = sfConfig::get('app_api_jambo_url') . 'api/v1/bill/status/';

        $stream = new Stream();

        $query_response = $stream->sendRequest([
            'url' => $url,
            'method' => 'POST',
            'ssl' => 'none',
            'contentType' => 'json',
            'headers' => array(
                "Authorization" => "JWT " . $token,
            ),
            'data' => [
                'bill_number' => $billing_reference_number
            ]
        ]);

        if ($query_response->status == 200 || $query_response->status == 201) {
            $content = $query_response->content;
            error_log("Payment confirmation is ---->");
            error_log(print_r($content, true));
            if (strtolower($content['status']) == 'paid') {
                return ['success' => true, 'receipt' => $content['receipt_numbers']];
            }
            return ['success' => false];
        } else {
            return ['success' => false];
        }
    }

    public function updateInvoiceToPaid($billing_reference_number, $invoice_id, $receipt)
    {
        $q = Doctrine_Query::create()
            ->from("ApFormPayments a")
            ->where("a.payment_id = ?", $billing_reference_number)
            ->where("a.invoice_id = ?", $invoice_id)
            ->orderBy('a.afp_id desc');
        $transaction = $q->fetchOne();

        $transaction->setPaymentStatus('paid');
        $transaction->setStatus(2);

        $transaction->save();


        $q = Doctrine_Query::create()
            ->from('MfInvoice m')
            ->where('m.id = ?', $transaction->getInvoiceId());

        $invoice = $q->fetchOne();

        $invoice->setPaid(2);
        $invoice->setReceiptNumber(json_encode($receipt));

        $invoice->save();

        return true;
    }

}
