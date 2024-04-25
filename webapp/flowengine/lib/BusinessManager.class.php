<?php
/**
 *
 * Handles creation of cyclic invoices for businesses
 *
 * Created by PhpStorm.
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 12:26 AM
 */

class BusinessManager {

    public $application_manager = null;
    public $invoice_manager = null;

    //Constructor for submissions class
    public function __construct()
    {
        $this->application_manager = new ApplicationManager();
        $this->invoice_manager = new InvoiceManager();
    }

    //Generate any configured cyclic bills for a specific business id
    public function generate_cyclic_bills($business_id)
    {
        $q = Doctrine_Query::create()
           ->from("MfUserProfile a")
           ->where("a.id = ?", $business_id);
        $business_profile = $q->fetchOne();

        $q = Doctrine_Query::create()
           ->from("Menus a")
           ->where("a.service_form = ?", $business_profile->getFormId())
           ->andWhere("a.service_type = 2")
           ->andWhere("a.service_fee_field <> 0");
        $services = $q->execute(); 

        foreach($services as $service)
        {
            //1. Check if business already has pending invoices for this service 
            $q = Doctrine_Query::create()
               ->from("MfInvoice a")
               ->leftJoin("a.FormEntry b")
               ->where("a.paid = 1")
               ->andWhere("b.service_id = ?", $service->getId())
               ->andWhere("b.form_id = ?", $business_profile->getFormId())
               ->andWhere("b.business_id = ?", $business_id);
            if($q->count() > 0)
            {
				//Pending invoices
                //error_log("Cyclic-b: No bills to generate for business id: ".$business_id.", under service id: ".$service->getId());
            }
            else 
            {
            //2. If no pending invoice then:
                //2.1 Create application if non exists
                $q = Doctrine_Query::create()
                    ->from("FormEntry a")
                    ->andWhere("a.service_id = ?", $service->getId())
                    ->andWhere("a.form_id = ?", $business_profile->getFormId())
                    ->andWhere("a.business_id = ?", $business_id);
                $submission = $q->fetchOne();

                if($submission == null)
                {
                    //error_log("Cyclic-b: Creating application for business id: ".$business_id.", under service id: ".$service->getId());
                    $submission = $this->application_manager->create_business_application($service->getId(), $business_profile->getFormId(), $business_profile->getEntryId(), $business_profile->getUserId(), $business_id, $business_profile->getTitle());
                }

                //Only generate invoice if there is no permit or if there is an expired permit
                $q = Doctrine_Query::create()
                   ->from("SavedPermit a")
                   ->where("a.application_id = ?", $submission->getId());
                $permits_count = $q->count();

                 $q = Doctrine_Query::create()
                   ->from("SavedPermit a")
                   ->where("a.application_id = ?", $submission->getId())
                   ->andWhere("a.permit_status <> 3")
				   ->andWhere("a.Template.expiry_trigger <> ?",0)//OTB Patch, also check that permit template is set to expire
                   ->andWhere("a.expiry_trigger = ?", 0);
                $renewed_permits = $q->count();

                if($permits_count == 0 || $renewed_permits == 0)
                {
                    //2.2 Generate invoice
                    //error_log("Cyclic-b: Creating invoices for business id: ".$business_id.", under service id: ".$service->getId());
                    $this->invoice_manager->generate_cyclic_invoices($submission->getId(), $service->getId());
                }
            }
        }
    }

    //Clear cyclic bills
    public function clear_cyclic_bills($business_id)
    {
        $q = Doctrine_Query::create()
           ->from("MfUserProfile a")
           ->where("a.id = ?", $business_id);
        $business_profile = $q->fetchOne();

        $q = Doctrine_Query::create()
           ->from("Menus a")
           ->where("a.service_form = ?", $business_profile->getFormId())
           ->andWhere("a.service_type = 2")
           ->andWhere("a.service_fee_field <> 0");
        $services = $q->execute(); 

        foreach($services as $service)
        {
            //1. Check if business already has pending invoices for this service 
            $q = Doctrine_Query::create()
               ->from("MfInvoice a")
               ->leftJoin("a.FormEntry b")
               ->where("a.paid = 1")
               ->andWhere("b.service_id = ?", $service->getId())
               ->andWhere("b.form_id = ?", $business_profile->getFormId())
               ->andWhere("b.business_id = ?", $business_id);
            $invoices = $q->execute();

            foreach($invoices as $invoice)
            {
                error_log("Cyclic-b: Clearing invoices for business id: ".$business_id.", under service id: ".$service->getId());
                if($invoice->getPaid() == 1)
                {
                    $invoice->delete();
                }
            }
        }
    }

    //Check if business and application are similar
    public function are_linked($business, $application, $element_id)
    {
        $prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);
        
        $sql = "SELECT * FROM ap_form_".$business->getFormId()." WHERE id = ".$business->getEntryId();
        $params = array();
        $busness_sth = mf_do_query($sql,$params,$dbh);
        $business_row = mf_do_fetch_result($busness_sth);

        $sql = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ".$application->getEntryId();
        $params = array();
        $application_sth = mf_do_query($sql,$params,$dbh);
        $application_row = mf_do_fetch_result($application_sth);

        $match = true;

        if($business_row[$element_id] != $application_row[$element_id])
        {
            error_log("Found false match on : ".$business_row[$business_column]." / ".$application_row[$business_column]);
            $match = false;
        }

        return $match;
    }

    public function get_entry_details($form_id, $entry_id)
    {
        $prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');
        require_once($prefix_folder.'includes/check-session.php');

        require_once($prefix_folder.'includes/language.php');
        require_once($prefix_folder.'includes/entry-functions.php');
        require_once($prefix_folder.'includes/post-functions.php');
        require_once($prefix_folder.'includes/users-functions.php');

        $nav = trim($_GET['nav']);

        if(empty($form_id) || empty($entry_id)){
            die("Invalid Request");
        }

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        //get entry details for particular entry_id
        $param['checkbox_image'] = '/form_builder/images/icons/59_blue_16.png';
        $param['show_image_preview'] = true;

        $entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$param);

        return $entry_details;
    }

    public function check_and_update_json($business_id)
    {
        $business_data_json = $this->get_json($business_id);

        $prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');
        require_once($prefix_folder.'includes/check-session.php');

        require_once($prefix_folder.'includes/language.php');
        require_once($prefix_folder.'includes/entry-functions.php');
        require_once($prefix_folder.'includes/post-functions.php');
        require_once($prefix_folder.'includes/users-functions.php');

        $nav = trim($_GET['nav']);

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);
        
        $query  = "UPDATE mf_user_profile SET form_data = ? WHERE id = ?";

        $params = array($business_data_json, $business_id);

        $sth = mf_do_query($query,$params,$dbh);

    }

    public function check_json($business_id)
    {
        $q = Doctrine_Query::create()
           ->from("MfUserProfile a")
           ->where("a.id = ?", $business_id);
        $profile = $q->fetchOne();
        
        if($profile->getFormData() == null)
        {
            $prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
            require_once($prefix_folder.'includes/init.php');

            require_once($prefix_folder.'../../../config/form_builder_config.php');
            require_once($prefix_folder.'includes/db-core.php');
            require_once($prefix_folder.'includes/helper-functions.php');
            require_once($prefix_folder.'includes/check-session.php');

            require_once($prefix_folder.'includes/language.php');
            require_once($prefix_folder.'includes/entry-functions.php');
            require_once($prefix_folder.'includes/post-functions.php');
            require_once($prefix_folder.'includes/users-functions.php');

            $nav = trim($_GET['nav']);

            $dbh = mf_connect_db();
            $mf_settings = mf_get_settings($dbh);

            $business_data_json = $this->get_json($business_id);

            $query  = "UPDATE mf_user_profile SET form_data = ? WHERE id = ?";

            $params = array($business_data_json, $profile->getId());

            $sth = mf_do_query($query,$params,$dbh);
        }

    }

    public function get_json($business_id)
    {
        $q = Doctrine_Query::create()
           ->from("MfUserProfile a")
           ->where("a.id = ?", $business_id);
        $profile = $q->fetchOne();

        $entry_details = $this->get_entry_details($profile->getFormId(), $profile->getEntryId());

        $business_data = array();

        foreach($entry_details as $entry_detail)
        {
            $business_data[] = $entry_detail;
        }

        $business_data_json = json_encode($business_data);
        
        return $business_data_json;
    }

}