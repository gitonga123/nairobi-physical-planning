<?php

/**
 * profiles actions.
 *
 * @package    permitflow
 * @subpackage profiles
 * @author     Your name here
 * @version    SVN: $Id$
 */

class profilesActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
      $q_pager = null;

      $this->search = null;

      if($request->getPostParameter('search'))
      {
        $this->search = $request->getPostParameter('search');

        $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');
        require_once($prefix_folder.'includes/check-session.php');

        require_once($prefix_folder.'includes/language.php');
        require_once($prefix_folder.'includes/entry-functions.php');
        require_once($prefix_folder.'includes/post-functions.php');
        require_once($prefix_folder.'includes/users-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        //Get element field used as title
        $q = Doctrine_Query::create()
		   ->from("ApColumnPreferences a")
		   ->where("a.form_id = ?", $request->getParameter('filter'));
		
        $column_settings = $q->fetchOne();
        $element_name = $column_settings->getElementName();

        //Search for records
        $sql = "SELECT * FROM ap_form_".$request->getParameter('filter')." WHERE ".$element_name." LIKE '%".$request->getPostParameter('search')."%'";
        $params = array();
        $sth = mf_do_query($sql,$params,$dbh);

        $results = array();

        while($row = mf_do_fetch_result($sth))
        {
            $results[] = $row['id'];
        }

        $results_string = "a.entry_id = ".implode(" OR a.entry_id = ", $results);;

        //Get all mf_user_profiles matching the entry ids
        $q_pager = Doctrine_Query::create()
                ->from('MfUserProfile a')
                ->where("a.form_id = ?", $request->getParameter('filter'))
                ->andWhere($results_string)
                ->orderBy('a.id DESC');
      }
      elseif($request->getParameter('dropdown'))
      {
        $this->filter_dropdown = $request->getParameter('dropdown');
        $this->filter_element = $request->getParameter('element');

        $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');
        require_once($prefix_folder.'includes/check-session.php');

        require_once($prefix_folder.'includes/language.php');
        require_once($prefix_folder.'includes/entry-functions.php');
        require_once($prefix_folder.'includes/post-functions.php');
        require_once($prefix_folder.'includes/users-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        //Get element field used as title
        $element_name = "element_".$this->filter_element;

        //Search for records
        $sql = "SELECT * FROM ap_form_".$request->getParameter('filter')." WHERE ".$element_name." = ".$this->filter_dropdown;
        $params = array();
        $sth = mf_do_query($sql,$params,$dbh);

        $results = array();

        while($row = mf_do_fetch_result($sth))
        {
            $results[] = $row['id'];
        }

        $results_string = "a.entry_id = ".implode(" OR a.entry_id = ", $results);;

        //Get all mf_user_profiles matching the entry ids
        $q_pager = Doctrine_Query::create()
                ->from('MfUserProfile a')
                ->where("a.form_id = ?", $request->getParameter('filter'))
                ->andWhere($results_string)
                ->orderBy('a.id DESC');
      }
      else 
      {
        if($request->getParameter('filterstatus') != "")
        {
            $this->filterstatus = $request->getParameter('filterstatus');
            $q_pager = Doctrine_Query::create()
                ->from('MfUserProfile a')
                ->where("a.form_id = ?", $request->getParameter('filter'))
                ->andWhere('a.deleted = ?', $this->filterstatus)
                ->orderBy('a.id DESC');
        }
        else {
            $q_pager = Doctrine_Query::create()
                ->from('MfUserProfile a')
                ->where("a.form_id = ?", $request->getParameter('filter'))
                ->orderBy('a.id DESC');
        }
      }


        $this->filter = $request->getParameter('filter');

        $this->dropdown = $request->getParameter('dropdown');

        $q_profile = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $request->getParameter('filter'));
        $this->profile_form = $q_profile->fetchOne();

        $this->pager = new sfDoctrinePager('MfUserProfile', 10);
        $this->pager->setQuery($q_pager);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
  }

  public function executeGetdropdownvaluefields(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('ApElementOptions a')
	  ->where('a.form_id = ?', $request->getParameter('formid'))
      ->andWhere('a.element_id = ?', $request->getParameter('elementid'))
      ->andWhere('a.live = 1')
      ->orderBy('a.position ASC');
    $options = $q->execute();

    echo "<select name='form_dropdown_value_fields' id='form_dropdown_value_fields' class='form-control'>";
    echo "<option>Filter by an option..</option>";
    foreach($options as $option)
    {
       echo "<option value='".$option->getOptionId()."'>".$option->getOptionText()."</option>";
    }
    echo "</select>";
    echo '<script language="javascript">
      jQuery(document).ready(function(){
          jQuery("#form_dropdown_value_fields" ).change(function() {
              var selecteditem = this.value;
              window.location = "/backend.php/profiles/index/filter/'.$request->getParameter('formid').'/dropdown/" + selecteditem + "/element/'.$request->getParameter('elementid').'";
          });
      });
    </script>';
    exit;
  }

  /**
   * Executes view action
   *
   * @param sfRequest $request A request object
   */
  public function executeView(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
        ->from('MfUserProfile a')
        ->where('a.id = ?', $request->getParameter("id"));
    $this->business = $q->fetchOne();

    //Check JSON. Generate if empty
    $business_manager = new BusinessManager();
    $business_manager->check_json($this->business->getId());

    if($request->getParameter("confirm") && $request->getParameter("confirm") == md5($this->business->getFormId()."/".$this->business->getEntryId()."/".$this->business->getId()))
    {
        $q = Doctrine_Query::create()
            ->from("ApFormPayments a")
            ->where("a.payment_id = ?", $this->business->getFormId()."/".$this->business->getEntryId()."/".$this->business->getId())
            ->andWhere("a.payment_status = ?", "pending")
            ->orderBy("a.afp_id DESC")
            ->limit(1);
        $payment = $q->fetchOne();

        if($payment)
        {
            $payment->setPaymentStatus("paid");
            $payment->save();

            $this->business->setDeleted(0);
            $this->business->save();
        }
    }

    //Update any pending profile activation payment
    /**$q = Doctrine_Query::create()
        ->from("ApFormPayments a")
        ->where("a.payment_id = ?", $this->business->getFormId()."/".$this->business->getEntryId()."/".$this->business->getId())
        ->andWhere("a.payment_status = ?", "pending")
        ->orderBy("a.afp_id DESC")
        ->limit(1);

    if($q->count())
    {
        $invoice_manager = new InvoiceManager();
        $result = $invoice_manager->basic_remote_reconcile($this->business->getFormId()."/".$this->business->getEntryId()."/".$this->business->getId());

        //If response is paid, then mark invoice as paid
        if($result == "paid")
        {
            $this->business->setDeleted(0);
            $this->business->save();
        }
    }**/

    $this->getUser()->setAttribute("current_profile", $request->getParameter("id"));

    $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->andWhere("a.business_id = ?", $this->business->getId())
           ->orderBy("a.id DESC");

    $this->latest_services = new sfDoctrinePager('FormEntry', 5);
    $this->latest_services->setQuery($q);
    $this->latest_services->setPage($request->getParameter('apage', 1));
    $this->latest_services->init();

    $q = Doctrine_Query::create()
           ->from("MfInvoice a")
           ->leftJoin("a.FormEntry b")
           ->andWhere("b.business_id = ?", $this->business->getId())
           ->orderBy("a.created_at DESC");

    $this->latest_invoices = new sfDoctrinePager('MfInvoice', 5);
    $this->latest_invoices->setQuery($q);
    $this->latest_invoices->setPage($request->getParameter('mpage', 1));
    $this->latest_invoices->init();

    $q = Doctrine_Query::create()
        ->from("MfUserProfileShare a")
        ->where("a.profile_id = ?", $this->business->getId())
        ->andWhere("a.deleted = 0");
    $this->users = $q->execute();

    $q = Doctrine_Query::create()
        ->from("MfUserProfileInspection a")
        ->where("a.profile_id = ?", $this->business->getId())
        ->andWhere("a.deleted = 0");
    $this->inspections = $q->execute();
  }

  /**
   * Executes transter action
   *
   * @param sfRequest $request A request object
   */
  public function executeTransfer(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
        ->from('MfUserProfile a')
        ->where('a.id = ?', $request->getParameter("id"));
    $this->business = $q->fetchOne();

    if($request->getPostParameter("email"))
    {
        $q = Doctrine_Query::create()
           ->from("SfGuardUser a")
           ->where("a.email_address = ?", $request->getPostParameter("email"));
        $user = $q->fetchOne();

        if($user)
        {
            $this->business->setUserId($user->getId());
            $this->business->save();

            //Also set all the application of the business to the new user
            $q = Doctrine_Query::create()
               ->from("FormEntry a")
               ->where("a.business_id = ?", $this->business->getId());
            $applications = $q->execute();

            foreach($applications as $application)
            {
                $application->setUserId($user->getId());
                $application->save();
            }

            $this->redirect("/backend.php/profiles/view/id/".$this->business->getId());
        }
    }
  }

  /**
   * Executes activate action
   *
   * @param sfRequest $request A request object
   */
  public function executeActivate(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
        ->from('MfUserProfile a')
        ->where('a.id = ?', $request->getParameter("id"));
    $this->business = $q->fetchOne();

    $this->business->setDeleted(0);
    $this->business->save();

    $this->redirect("/backend.php/profiles/view/id/".$this->business->getId());
  }

  /**
   * Executes deactivate action
   *
   * @param sfRequest $request A request object
   */
  public function executeDeactivate(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
        ->from('MfUserProfile a')
        ->where('a.id = ?', $request->getParameter("id"));
    $this->business = $q->fetchOne();

    $this->business->setDeleted(1);
    $this->business->save();

    $this->redirect("/backend.php/profiles/view/id/".$this->business->getId());
  }

  /**
   * Executes inspect action
   *
   * @param sfRequest $request A request object
   */
  public function executeInspect(sfWebRequest $request)
  {
        if(!$this->getUser()->mfHasCredential("can_inspect"))
        {
            exit;
        }

        $q = Doctrine_Query::create()
            ->from('MfUserProfile a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->business = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('ApForms a')
            ->where('a.form_type = 3')
            ->andWhere('a.form_active = 1')
            ->orderBy('a.form_id DESC');

        $this->inspection_count = $q->count();

        $this->inspection_sheet = $q->fetchOne();
  }

  /**
   * Executes confirm action
   *
   * @param sfRequest $request A request object
   */
  public function executeConfirm(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
        ->from('MfUserProfile a')
        ->where('a.id = ?', $this->getUser()->getAttribute("current_profile"));
    $this->business = $q->fetchOne();

    $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_type = 3')
        ->andWhere('a.form_active = 1');

    $this->inspection_count = $q->count();

    $this->inspection_sheet = $q->fetchOne();
  }

  /**
   * Executes viewinspection action
   *
   * @param sfRequest $request A request object
   */
  public function executeViewinspection(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
        ->from('MfUserProfile a')
        ->where('a.id = ?', $this->getUser()->getAttribute("current_profile"));
    $this->business = $q->fetchOne();

    $q = Doctrine_Query::create()
        ->from('MfUserProfileInspection a')
        ->where('a.id = ?', $request->getParameter("id"));

    $this->inspection = $q->fetchOne();
  }

  /**
   * Executes report action
   *
   * @param sfRequest $request A request object
   */
  public function executeReport(sfWebRequest $request)
  {
    $service = $request->getPostParameter("application_service", $this->getUser()->getAttribute('application_service'));
    $status = $request->getPostParameter("application_status", $this->getUser()->getAttribute('application_status'));
    $profile_id = $request->getPostParameter("profile_id", $this->getUser()->getAttribute('profile_id'));

    $this->getUser()->setAttribute('application_service', $service);
    $this->getUser()->setAttribute('application_status', $status);
    $this->getUser()->setAttribute('profile_id', $profile_id);

    $q = null;
    
    if($status == 1) //Renewed Applications
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->leftJoin('a.SavedPermits b')
            ->where("a.service_id = ?", $service)
            ->andWhere('a.parent_submission = ?', 0)
            ->andWhere('a.approved <> 0')
            ->andWhere('b.expiry_trigger = 0')
            ->orderBy('a.id DESC');
    }
    elseif($status == 2) //Expired Applications
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->leftJoin('a.SavedPermits b')
            ->where("a.service_id = ?", $service)
            ->andWhere('a.parent_submission = ?', 0)
            ->andWhere('a.approved <> 0')
            ->andWhere('b.expiry_trigger = 1')
            ->orderBy('a.id DESC');
    }
    else { //All Applications
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where("a.service_id = ?", $service)
            ->andWhere('a.parent_submission = ?', 0)
            ->andWhere('a.approved <> 0')
            ->orderBy('a.id DESC');
    }

    $this->forward404Unless($this->service = Doctrine_Core::getTable('Menus')->find(array($service)), sprintf('Object services does not exist (%s).', $service));
    $this->forward404Unless($this->profile = Doctrine_Core::getTable('SfGuardUserCategories')->find(array($profile_id)), sprintf('Object profiles does not exist (%s).', $profile_id));

    $this->pager = new sfDoctrinePager('FormEntry', 10);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();
  }

  /**
   * Executes printreport action
   *
   * @param sfRequest $request A request object
   */
  public function executeFinance(sfWebRequest $request)
  {
    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
	require_once($prefix_folder.'includes/init.php');

	require_once($prefix_folder.'../../../config/form_builder_config.php');
	require_once($prefix_folder.'includes/db-core.php');
	require_once($prefix_folder.'includes/helper-functions.php');
	require_once($prefix_folder.'includes/check-session.php');

	require_once($prefix_folder.'includes/language.php');
	require_once($prefix_folder.'includes/entry-functions.php');
	require_once($prefix_folder.'includes/post-functions.php');
	require_once($prefix_folder.'includes/users-functions.php');

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

    $filter_fee = $request->getParameter('fee_filters');

    $q = Doctrine_Query::create()
            ->from("Menus a")
            ->where("a.id = ?", $request->getPostParameter('service_id'));
    $service = $q->fetchOne();

    $form_id = $service->getServiceForm();

    $q = null;

    if($filter_fee == "main_fee")
    {
        $filter_fee = $request->getParameter('fee_filters_other');

        $other_fees = array();

        $q1 = Doctrine_Query::create()
            ->from("MoreFees a")
            ->where("a.service_id = ?", $request->getParameter('service_id'))
            ->orderBy("a.fee_title ASC");

        foreach($q1->execute() as $fee)
        {
            $other_fees[] = $fee->getFeeTitle();
        }

        $q2 = Doctrine_Query::create()
            ->from("ServiceOtherFees a")
            ->where("a.service_id = ?", $request->getParameter('service_id'));

        foreach($q2->execute() as $fee)
        {
            $other_fees[] = $fee->getServiceCode();
        }

        $other_fees_string = "a.description <> '".implode("' AND a.description <> '", $other_fees)."'";

        $fields_names = $request->getPostParameter("field_names");
        $extra_filters = $request->getPostParameter("extra_filters");

        if(sizeof($fields_names) > 0)
        {

            $fields = array();
            $fields_string = "";

            $count = 0;
            foreach($fields_names as $field_name)
            {
                if($extra_filters[$count])
                {
                    $fields[] = "d.element_".$field_name." = ".$extra_filters[$count];
                }
                $count++;
            }

            if(sizeof($fields) > 0)
            {
                $fields_string = implode(" AND ", $fields);
            }

            if($fields_string)
            {
                $sql = "SELECT b.id as id, a.id as detail_id FROM mf_invoice_detail a 
                        INNER JOIN mf_invoice b ON a.invoice_id = b.id 
                        INNER JOIN form_entry c ON b.app_id = c.id 
                        INNER JOIN ap_form_".$form_id." d ON c.entry_id = d.id
                    WHERE ".$other_fees_string."
                    AND ".$fields_string."
                    AND c.service_id = ".$request->getPostParameter('service_id')."
                    AND b.paid = 2";
            }
            else 
            {
                $sql = "SELECT b.id as id, a.id as detail_id FROM mf_invoice_detail a 
                        INNER JOIN mf_invoice b ON a.invoice_id = b.id 
                        INNER JOIN form_entry c ON b.app_id = c.id 
                    WHERE ".$other_fees_string."
                    AND c.service_id = ".$request->getPostParameter('service_id')."
                    AND b.paid = 2";
            }
        }
        else 
        {
            $sql = "SELECT b.id as id, a.id as detail_id FROM mf_invoice_detail a 
                        INNER JOIN mf_invoice b ON a.invoice_id = b.id 
                        INNER JOIN form_entry c ON b.app_id = c.id 
                    WHERE ".$other_fees_string."
                    AND c.service_id = ".$request->getPostParameter('service_id')."
                    AND b.paid = 2";
        }

        $sth = mf_do_query($sql,array(),$dbh);

    }
    else 
    {
        $fields_names = $request->getPostParameter("field_names");
        $extra_filters = $request->getPostParameter("extra_filters");

        if(sizeof($fields_names) > 0)
        {
            $fields = array();
            $fields_string = "";

            $count = 0;
            foreach($fields_names as $field_name)
            {
                if($extra_filters[$count])
                {
                    $fields[] = "d.element_".$field_name." = ".$extra_filters[$count];
                }
                $count++;
            }

            if(sizeof($fields) > 0)
            {
                $fields_string = implode(" AND ", $fields);
            }

            if($fields_string)
            {
                $sql = "SELECT b.id as id, a.id as detail_id FROM mf_invoice_detail a 
                            INNER JOIN mf_invoice b ON a.invoice_id = b.id 
                            INNER JOIN form_entry c ON b.app_id = c.id 
                            INNER JOIN ap_form_".$form_id." d ON c.entry_id = d.id
                        WHERE a.description = '".$filter_fee."'
                        AND ".$fields_string."
                        AND c.service_id = ".$request->getPostParameter('service_id')."
                        AND b.paid = 2";
            }
            else
            {
                $sql = "SELECT b.id as id, a.id as detail_id FROM mf_invoice_detail a 
                        INNER JOIN mf_invoice b ON a.invoice_id = b.id 
                        INNER JOIN form_entry c ON b.app_id = c.id 
                    WHERE a.description = '".$filter_fee."'
                    AND c.service_id = ".$request->getPostParameter('service_id')."
                    AND b.paid = 2";
            }
        }
        else 
        {
            $sql = "SELECT b.id as id, a.id as detail_id FROM mf_invoice_detail a 
                        INNER JOIN mf_invoice b ON a.invoice_id = b.id 
                        INNER JOIN form_entry c ON b.app_id = c.id 
                    WHERE a.description = '".$filter_fee."'
                    AND c.service_id = ".$request->getPostParameter('service_id')."
                    AND b.paid = 2";
        }

        $sth = mf_do_query($sql,array(),$dbh);
    }

    $columns = array();
    $columns[] = "#";
    $columns[] = "Business";
    $columns[] = "Application No";
    $columns[] = "Reference No";
    $columns[] = "Created By";
    $columns[] = "Phone";
    $columns[] = "Fee Title";
    $columns[] = "Amount";

    $records = array();

    $grand_total = 0;

    while($row = mf_do_fetch_result($sth))
    {
        $q = Doctrine_Query::create()
           ->from("MfInvoice a")
           ->where("a.id = ?", $row["id"]);
        $invoice = $q->fetchOne();
        
        $q = Doctrine_Query::create()
           ->from("MfInvoiceDetail a")
           ->where("a.id = ?", $row["detail_id"]);
        $invoice_detail = $q->fetchOne();

        $application = $invoice->getFormEntry();

        $user = $application->getSfGuardUser();
        $profile = $application->getSfGuardUserProfile();
        
        $record_columns = array();
        $record_columns[] = $application->getId();
        $record_columns[] = strtoupper($application->getMfUserProfile()->getTitle());
        $record_columns[] = $application->getApplicationId();
        $record_columns[] = $application->getFormId()."/".$application->getEntryId()."/".$invoice->getId();
        $record_columns[] = $application->getSfGuardUserProfile()->getFullname()." (".$user->getUsername().")";
        $record_columns[] = $profile->getMobile();
        $record_columns[] = $filter_fee;
        $record_columns[] = $invoice_detail->getAmount();

        $grand_total = $grand_total + $invoice_detail->getAmount();

        $records[] = $record_columns;
    }

    $record_columns = array();
    $record_columns[] = "";
    $record_columns[] = "";
    $record_columns[] = "";
    $record_columns[] = "";
    $record_columns[] = "";
    $record_columns[] = "";
    $record_columns[] = "Total";
    $record_columns[] = $grand_total ;

    $records[] = $record_columns;

    Outputsheet::ReportGenerator("Finance-Report", $columns, $records);
  }

  /**
   * Executes printreport action
   *
   * @param sfRequest $request A request object
   */
  public function executePrintreport(sfWebRequest $request)
  {
    $service = $this->getUser()->getAttribute('application_service');
    $status = $this->getUser()->getAttribute('application_status');
    $profile_id = $this->getUser()->getAttribute('profile_id');

    $q = null;
    
    if($status == 1) //Renewed Applications
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->leftJoin('a.SavedPermits b')
            ->where("a.service_id = ?", $service)
            ->andWhere('a.parent_submission = ?', 0)
            ->andWhere('a.approved <> 0')
            ->andWhere('b.expiry_trigger <> 1')
            ->orderBy('a.id DESC');
    }
    elseif($status == 2) //Expired Applications
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->leftJoin('a.SavedPermits b')
            ->where("a.service_id = ?", $service)
            ->andWhere('a.parent_submission = ?', 0)
            ->andWhere('a.approved <> 0')
            ->andWhere('b.expiry_trigger <> 0')
            ->orderBy('a.id DESC');
    }
    else { //All Applications
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where("a.service_id = ?", $service)
            ->andWhere('a.parent_submission = ?', 0)
            ->andWhere('a.approved <> 0')
            ->orderBy('a.id DESC');
    }

    $columns = array();
    $columns[] = "#";
    $columns[] = "Business";
    $columns[] = "Application No";
    $columns[] = "Created By";
    $columns[] = "Status";

    $records = array();

    foreach($q->execute() as $application)
    {
        $user = $application->getSfGuardUser();
        $profile = $application->getSfGuardUserProfile();
        
        $record_columns = array();
        $record_columns[] = $application->getId();
        $record_columns[] = strtoupper($application->getMfUserProfile()->getTitle());
        $record_columns[] = $application->getApplicationId();
        $record_columns[] = $application->getSfGuardUserProfile()->getFullname()." (".$user->getUsername().")";
        $record_columns[] = $profile->getMobile();

        if($application->needsRenewal())
        {
            $record_columns[] = "Expired";
        }
        else 
        {
            $record_columns[] = "Renewed";
        }

        $records[] = $record_columns;
    }

    Outputsheet::ReportGenerator("Businesses-Report", $columns, $records);
  }
}
