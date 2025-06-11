<?php

/**
 * reports actions.
 *
 * Generates in-built reports on various types of applications submitted by clients and the history of their review process.
 * Also includes components for creating and generating custom reports.
 *
 * @package    backend
 * @subpackage reports
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class reportsActions extends sfActions
{
  /**
   * Get
   *
   * @param sfRequest $request A request object
   */
  public function executeGetdropdownfields(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $request->getParameter('formid'))
      ->andWhere('a.element_status = ?', 1)
      ->andWhere('a.element_type LIKE ?', '%select%')
      ->orderBy('a.element_title ASC');
    $elements = $q->execute();

    echo "<select name='form_dropdown_fields' id='form_dropdown_fields' class='form-control'>";
    echo "<option>Choose a dropdown field...</option>";
    foreach ($elements as $element) {
      echo "<option value='" . $element->getElementId() . "'>" . $element->getElementTitle() . "</option>";
    }
    echo "</select>";
    echo '<script language="javascript">
      jQuery(document).ready(function(){
          jQuery("#form_dropdown_fields" ).change(function() {
              var selecteditem = this.value;
              $.ajax({url:"/plan/reports/getdropdownvaluefields?formid=' . $request->getParameter('formid') . '&elementid=" + selecteditem,success:function(result){
                $("#ajaxdropdownvaluefields").html(result);
              }});
          });
      });
    </script>';
    exit;
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
    foreach ($options as $option) {
      echo "<option value='" . $option->getOptionId() . "'>" . $option->getOptionText() . "</option>";
    }
    echo "</select>";
    exit;
  }

  public function executeGetdatefields(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $request->getParameter('formid'))
      ->andWhere('a.element_status = ?', 1)
      ->andWhere('a.element_type LIKE ?', '%date%')
      ->orderBy('a.element_title ASC');
    $elements = $q->execute();

    echo "<select name='form_fields' id='form_fields'>";
    foreach ($elements as $element) {
      echo "<option value='" . $element->getElementId() . "'>" . $element->getElementTitle() . "</option>";
    }
    echo "</select>";
    exit;
  }

  public function executeGettimefields(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $request->getParameter('formid'))
      ->andWhere('a.element_status = ?', 1)
      ->andWhere('a.element_type LIKE ?', '%time%')
      ->orderBy('a.element_title ASC');
    $elements = $q->execute();

    echo "<select name='form_time_fields' id='form_time_fields'>";
    foreach ($elements as $element) {
      echo "<option value='" . $element->getElementId() . "'>" . $element->getElementTitle() . "</option>";
    }
    echo "</select>";
    exit;
  }

  public function executeSetdate(sfWebRequest $request)
  {
    $applicationid = $request->getParameter('applicationid');
    $elementid = $request->getParameter('elementid');
    $timeelementid = $request->getParameter('timeelementid');
    $date = $request->getParameter('date');
    $time = $request->getParameter('time');

    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.application_id = ?', $applicationid);
    $application = $q->fetchOne();
    if ($application) {
      $sql = "UPDATE ap_form_" . $application->getFormId() . " SET element_" . $elementid . " = '" . $date . "' WHERE id = " . $application->getEntryId();
      $sth = mf_do_query($sql, array(), $dbh);

      if ($sth === false) {
        echo "Could not update date for application";
      } else {
        echo "Successfull updated date application.";
      }

      $sql = "UPDATE ap_form_" . $application->getFormId() . " SET element_" . $timeelementid . " = '" . $time . "' WHERE id = " . $application->getEntryId();
      $sth = mf_do_query($sql, array(), $dbh);

      if ($sth === false) {
        echo "Could not update time for application";
      } else {
        echo "Successfull updated time application.";
      }
    }
    exit;
  }

  public function executeGetformentryid(sfWebRequest $request)
  {
    $form_id = $request->getParameter('applicationid');
    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.application_id = ?', $form_id);
    $application = $q->fetchOne();
    if ($application) {
      echo $application->getId();
      exit;
    } else {
      echo "0";
      exit;
    }
  }


  /**
   * Executes 'List' action
   *
   * Displays a list of all available reports.
   * Each report item in the list consists of a form containing filters that are used to generate the report.
   *
   * @param sfRequest $request A request object
   */
  public function executeList(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('FormGroups a');
    $this->groups = $q->execute();

    $this->filter = $request->getParameter("filter", false);
  }

  public function executeTimetablereport(sfWebRequest $request)
  {
    if ($request->getPostParameter('application_form')) {
      $this->form_id = $request->getPostParameter('application_form');
      $this->getUser()->setAttribute('application_form', $this->form_id);
      $this->stage_id = $request->getPostParameter('application_status');
      $this->getUser()->setAttribute('application_status', $this->stage_id);
      $this->element_id = $request->getPostParameter('form_fields');
      $this->getUser()->setAttribute('form_fields', $this->element_id);
      $this->time_element_id = $request->getPostParameter('form_time_fields');
      $this->getUser()->setAttribute('form_time_fields', $this->time_element_id);
    } else {
      $this->form_id = $this->getUser()->getAttribute('application_form');
      $this->stage_id = $this->getUser()->getAttribute('application_status');
      $this->element_id = $this->getUser()->getAttribute('form_fields');
      $this->time_element_id = $this->getUser()->getAttribute('form_time_fields');
    }
  }
  /** 
   * @param sfRequest $request A request object
   */
  public function executeEmbed(sfWebRequest $request)
  {
    // we just return the view embedded

  }

  /**
   * Executes 'Report1' action
   *
   * Report of all applications that have been submitted within a specified time period and their status
   *
   * @param sfRequest $request A request object
   */
  public function executeReport1(sfWebRequest $request)
  {
    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $applicationform = $request->getParameter("application_form");
    $startdate = $request->getPostParameter('from_dateblt1');
    $enddate = $request->getPostParameter('to_date');

    $this->getUser()->setAttribute('applicationform', $applicationform);
    $this->getUser()->setAttribute('startdate', $startdate);
    $this->getUser()->setAttribute('enddate', $enddate);

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    function GetDays($sStartDate, $sEndDate)
    {
      $aDays[] = $start_date;
      $start_date = $sStartDate;
      $end_date = $sEndDate;
      $current_date = $start_date;
      while (strtotime($current_date) <= strtotime($end_date)) {
        $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
        $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
      }


      return $aDays;
    }

    $query = "SELECT a.id as id, a.date_created as date_created FROM ap_form_" . $applicationform . "  a LEFT JOIN form_entry b ON a.id = b.entry_id WHERE a.date_created BETWEEN '" . $startdate . "' AND '" . $enddate . "' AND b.form_id = " . $applicationform . " AND b.approved <> 0 AND b.parent_submission = 0";

    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $applicationform)
      ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section', 'file'))
      ->andWhere('a.element_status = 1')
      ->orderBy('a.element_position ASC');
    $fields = $q->execute();

    foreach ($fields as $field) {
      if ($dropdownfield) {
        $query = $query . " AND ";
        $query = $query . "a.element_" . $dropdownfield . " = " . $dropdownoption;
      }
    }

    $results = mf_do_query($query, array(), $dbh);

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    foreach ($fields as $field) {
      $columns[] = $field->getElementTitle();
    }

    $records = "";

    while ($row = mf_do_fetch_result($results)) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.form_id = ?', $applicationform)
        ->andWhere('a.entry_id = ?', $row['id'])
        ->andWhere('a.approved <> ?', '0')
        ->andWhere('a.parent_submission = ?', '0');
      $application = $q->fetchOne();
      if ($application) {
        $query = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = '" . $application->getEntryId() . "'";
        $sth = mf_do_query($query, array(), $dbh);
        $apform = mf_do_fetch_result($sth);

        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormCode();
          $record_columns[] = $form->getFormName();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $record_columns[] = $application->getDateOfSubmission();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();

        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.id = ?', $application->getUserId());
        $user = $q->fetchOne();

        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        } else {
          $record_columns[] = "-";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();

        foreach ($fields as $field) {
          if ($field->getElementType() == "select") {
            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.option_id = ?', $apform["element_" . $field->getElementId()])
              ->andWhere('a.form_id = ?', $application->getFormId());
            $option_value = $q->fetchOne();
            if ($option_value) {
              $record_columns[] = $option_value->getOptionText();
            } else {
              $record_columns[] = "-";
            }
          } elseif ($field->getElementType() == "checkbox" || $field->getElementType() == "radio") {
            $choices = "";

            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.form_id = ?', $application->getFormId());
            $options = $q->execute();
            foreach ($options as $option) {
              if ($apform["element_" . $field->getElementId() . "_" . $option->getOptionId()]) {
                $choices .= $option->getOptionText() . ", ";
              }
            }

            $record_columns[] = $choices;
          } else {
            $record_columns[] = $apform["element_" . $field->getElementId()];
          }
        }

        $records[] = $record_columns;
      }
    }

    $this->columns = $columns;
    $this->records = $records;
  }

  /**
   * Executes 'Report2' action
   *
   * Report of all applications that have been approved within a specific time period and their status.
   *
   * @param sfRequest $request A request object
   */
  public function executeReport2(sfWebRequest $request)
  {
    $applicationform = $request->getPostParameter("application_form");
    $startdate = $request->getPostParameter("from_date1");
    $enddate = $request->getPostParameter("to_date1");

    $this->getUser()->setAttribute('applicationform', $applicationform);
    $this->getUser()->setAttribute('startdate', $startdate);
    $this->getUser()->setAttribute('enddate', $enddate);

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    $q = Doctrine_Query::create()
      ->from("SavedPermit a")
      ->leftJoin("a.FormEntry b")
      ->where("a.date_of_issue BETWEEN ? AND ?", array($startdate, $enddate))
      ->andWhere("b.form_id = ?", $applicationform);
    $permits = $q->execute();

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Permit No";
    $columns[] = "Permit Issued On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    $records = "";

    foreach ($permits as $permit) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.id = ?', $permit->getApplicationId());
      $application = $q->fetchOne();
      if ($application) {
        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormCode();
          $record_columns[] = $form->getFormName();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $record_columns[] = $application->getDateOfSubmission();
        $record_columns[] = $permit->getPermitId();
        $record_columns[] = $permit->getDateOfIssue();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.id = ?', $application->getUserId());
        $user = $q->fetchOne();
        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        } else {
          $record_columns[] = "-";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();
        $records[] = $record_columns;
      }
    }

    $this->columns = $columns;
    $this->records = $records;
  }

  /**
   * Executes 'Report3' action
   *
   * Report of all applications that are pending at a particular stage of the workflow.
   *
   * @param sfRequest $request A request object
   */
  public function executeReport3(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stages = $_POST['pending_stage'];
    $this->getUser()->setAttribute("pending_stages", $pending_stages);

    foreach ($stages as $stage) {
      if ($pending_stages[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = array();
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Phone Number";
    $columns[] = "Status";
    $records = array();

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();

    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = array();
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();
      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        $record_columns[] = $userprofile->getMobile();
      } else {
        $record_columns[] = "-";
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();
      $records[] = $record_columns;
    }

    $this->columns = $columns;
    $this->records = $records;
  }



  /**
   * Executes 'Report4' action
   *
   * Report of all applications that are pending at a particular stage of the workflow.
   *
   * @param sfRequest $request A request object
   */
  public function executeReport4(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    function GetDays($sStartDate, $sEndDate)
    {
      $aDays[] = $start_date;
      $start_date = $sStartDate;
      $end_date = $sEndDate;
      $current_date = $start_date;
      while (strtotime($current_date) <= strtotime($end_date)) {
        $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
        $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
      }


      return $aDays;
    }

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stages = $_POST['pending_stage'];
    $this->getUser()->setAttribute("pending_stages", $pending_stages);

    foreach ($stages as $stage) {
      if ($pending_stages[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Duration";
    $columns[] = "Status";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();

    foreach ($applications as $application) {
      $maximum_duration = 0;
      //get maximum duration of current stage
      $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.id = ?", $application->getApproved());
      $current_stage = $q->fetchOne();
      if ($current_stage) {
        $maximum_duration = $current_stage->getMaxDuration();
      }

      $days = GetDays($application->getDateOfSubmission(), date("Y-m-d"));

      if ($days >= $maximum_duration && $maximum_duration != 0) {
        //show
      } else {
        //don't show
        continue;
      }

      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('ApplicationReference b')
        ->where('b.stage_id = ?', $application->getApproved())
        ->andWhere('b.application_id = ?', $application->getId());
      $application_reference2 = $q->fetchOne();


      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $stage = $q->fetchOne();

      if ($application_reference2) {
        $days = sizeOf(GetDays($application_reference2->getStartDate(), date('Y-m-d')));
        if ($days > $stage->getMaxDuration()) {
          $record_columns[] = $days . " days taken";
        } else {
          $record_columns[] = $days;
        }
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();
      $records[] = $record_columns;
    }

    $this->columns = $columns;
    $this->records = $records;
  }

  /**
   * Executes 'Viewinspections' action
   *
   * Inspection History Report for an application.
   *
   * @param sfRequest $request A request object
   */
  public function executeViewinspections(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.id = ?', $request->getParameter('id'))
      ->andWhere('a.parent_submission = 0');
    $this->application = $q->fetchOne();
  }


  /**
   * Executes 'Viewinspections' action
   *
   * Shows report of supervisors that has approved/rejected application through its lifecycle.
   *
   * @param sfRequest $request A request object
   */
  public function executeViewreference(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.id = ?', $request->getParameter('id'))
      ->andWhere('a.parent_submission = 0');
    $this->application = $q->fetchOne();
  }


  /**
   * Executes 'Viewnotifications' action
   *
   * Shows report of notifications sent for an application.
   *
   * @param sfRequest $request A request object
   */
  public function executeViewnotifications(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.id = ?', $request->getParameter('id'))
      ->andWhere('a.parent_submission = 0');
    $this->application = $q->fetchOne();
  }

  /**
   * Executes 'Report5' action
   *
   * Report of all applications pending action from the requestor (developer/architect).
   *
   * @param sfRequest $request A request object
   */
  public function executeReport5(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stages = $_POST['pending_stage'];
    $this->getUser()->setAttribute('pending_stages', $pending_stages);

    foreach ($stages as $stage) {
      if ($pending_stages[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Phone Number";
    $columns[] = "Status";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->leftJoin('a.MfInvoice b')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"))
      ->andWhere("a.declined = 1 OR b.paid <> 2");
    $applications = $q->execute();


    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        $record_columns[] = $userprofile->getMobile();
      } else {
        $record_columns[] = "-";
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();
      $records[] = $record_columns;
    }

    $this->columns = $columns;
    $this->records = $records;
  }

  /**
   * Executes 'Report6' action
   *
   * Report of all applications that are pending at a particular stage of the workflow
   *
   * @param sfRequest $request A request object
   */
  public function executeReport6(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stage = $_POST['pending_stage'];
    $this->getUser()->setAttribute('pending_stages', $pending_stage);

    foreach ($stages as $stage) {
      if ($pending_stage[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Stage";
    $columns[] = "Sent to this stage on";
    $columns[] = "Sent to this stage by";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();


    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();

      $q = Doctrine_Query::create()
        ->from('ApplicationReference b')
        ->where('b.stage_id = ?', $submenu->getId())
        ->andWhere('b.application_id = ?', $application->getId())
        ->orderBy('b.id DESC');
      $application_reference = $q->fetchOne();

      if ($application_reference) {
        $record_columns[] = $application_reference->getStartDate();
        $q = Doctrine_Query::create()
          ->from('CfUser a')
          ->where('a.nid = ?', $application_reference->getApprovedBy());
        $reviewer = $q->fetchOne();
        if ($reviewer) {
          $record_columns[] = $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();
        } else {
          $record_columns[] = "-Client-";
        }
      } else {
        $record_columns[] = "-";
        $record_columns[] = "-";
      }

      $records[] = $record_columns;
    }

    $this->columns = $columns;
    $this->records = $records;
  }


  /**
   * Executes 'Report7' action
   *
   * Report of all notifications for an application that have been sent
   *
   * @param sfRequest $request A request object
   */
  public function executeReport7(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stage = $_POST['pending_stage'];
    $this->getUser()->setAttribute('pending_stages', $pending_stage);

    foreach ($stages as $stage) {
      if ($pending_stage[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Notifications";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();


    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $notificationtext = "";

      $q = Doctrine_Query::create()
        ->from('NotificationHistory a')
        ->where('a.application_id = ?', $application->getId());
      $notifications = $q->execute();

      $count = 0;
      foreach ($notifications as $notification) {
        $count++;
        $notificationtext .= $count . "." . $notification->getNotification();
      }

      $record_columns[] = $notificationtext;

      $records[] = $record_columns;
    }

    $this->columns = $columns;
    $this->records = $records;
  }

  /**
   * Executes 'Report8' action
   *
   * Report of all inspection carried out by a specific reviewer
   *
   * @param sfRequest $request A request object
   */
  public function executeReport8(sfWebRequest $request)
  {
    if ($request->isMethod(sfRequest::POST)) {
      $this->reviewer = $request->getPostParameter('reviewer');

      $q = Doctrine_Query::create()
        ->from('Task a');

      if ($this->reviewer != "0") {
        $q->where('a.owner_user_id = ?', $this->reviewer);
      }

      $q->andWhere('a.type = ?', '6');
      $q->andWhere('a.start_date BETWEEN ? AND ?', array($request->getPostParameter('from_date8'), $request->getPostParameter('to_date8')));
      $this->tasks = $q->execute();

      $this->fromdate = $request->getPostParameter('from_date8');
      $this->todate = $request->getPostParameter('to_date8');
    }
  }


  /**
   * Executes 'Report9' action
   *
   * Inspection History of an Application
   *
   * @param sfRequest $request A request object
   */
  public function executeReport9(sfWebRequest $request)
  {
  }

  /**
   * Executes 'Report10' action
   *
   * Report of all activities with a certain period of time.
   *
   * @param sfRequest $request A request object
   */
  public function executeReport10(sfWebRequest $request)
  {
    if ($request->getPostParameter("reviewer")) {
      $this->fromdate = $request->getPostParameter('from_date10');
      $this->todate = $request->getPostParameter('to_date10');
      $this->reviewer = $request->getPostParameter("reviewer");

      //Change start date to be inclusive of filtered dates
      $this->fromdate = date('Y-m-d', strtotime($this->fromdate . ' -1 day'));
      $this->todate = date('Y-m-d', strtotime($this->todate . ' +1 day'));

      $this->getUser()->setAttribute("from_date", $this->fromdate);
      $this->getUser()->setAttribute("to_date", $this->todate);
      $this->getUser()->setAttribute("reviewer", $request->getPostParameter("reviewer"));
    } else {
      $this->fromdate = $this->getUser()->getAttribute("from_date");
      $this->todate = $this->getUser()->getAttribute("to_date");
      $this->reviewer = $this->getUser()->getAttribute("reviewer");
    }

    $q = Doctrine_Query::create()
      ->from('AuditTrail a')
      ->where('a.action_timestamp BETWEEN ? AND ?', array($this->fromdate, $this->todate))
      ->andWhere('a.user_id = ?', $this->reviewer)
      ->orderBy('a.id DESC');

    $this->pager = new sfDoctrinePager('AuditTrail', 10);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();
  }


  /**
   * Executes 'Report11' action
   *
   * Report of the data integrity of all archived Construction Permit Requests.
   *
   * @param sfRequest $request A request object
   */
  public function executeReport11(sfWebRequest $request)
  {
    if ($request->isMethod(sfRequest::POST)) {
      if ($request->getPostParameter("filter1") == "-") {
        $q = Doctrine_Query::create()
          ->from('FormEntry a')
          ->andWhere('a.parent_submission = 0')
          ->orderBy('a.id DESC');
      } else {
        $q = Doctrine_Query::create()
          ->from('FormEntry a')
          ->where('a.approved = ?', $request->getPostParameter("filter1"))
          ->andWhere('a.parent_submission = 0')
          ->orderBy('a.id DESC');
      }
      $this->pager = new sfDoctrinePager('FormEntry', 10);
      $this->pager->setQuery($q);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->init();


      $this->filteruser = $request->getPostParameter("user");
    } else {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->orderBy('a.id DESC');
      $this->pager = new sfDoctrinePager('FormEntry', 10);
      $this->pager->setQuery($q);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->init();
    }
  }


  /**
   * Executes 'Report12' action
   *
   * Report of the income from confirmed payments
   *
   * @param sfRequest $request A request object
   */
  public function executeReport12(sfWebRequest $request)
  {
    $this->fromdate = $request->getPostParameter('from_date12');
    $this->todate = $request->getPostParameter('to_date12');
  }

  /**
   * Executes 'Report13' action
   *
   * Tax report for application submitted within a certain time period.
   * Kigali, One Stop Center
   *
   * @param sfRequest $request A request object
   */
  public function executeReport13(sfWebRequest $request)
  {
    $this->fromdate = $request->getPostParameter('from_date');
    $this->todate = $request->getPostParameter('to_date');
  }

  /**
   * Executes 'Report14' action
   *
   * Tax report for application submitted within a certain time period.
   * Gasabo, One Stop Center
   *
   * @param sfRequest $request A request object
   */
  public function executeReport14(sfWebRequest $request)
  {
    $this->fromdate = $request->getPostParameter('from_date');
    $this->todate = $request->getPostParameter('to_date');
  }

  /**
   * Executes 'Report15' action
   *
   * Tax report for application submitted within a certain time period.
   * Kucikiro, One Stop Center
   *
   * @param sfRequest $request A request object
   */
  public function executeReport15(sfWebRequest $request)
  {
    $this->fromdate = $request->getPostParameter('from_date');
    $this->todate = $request->getPostParameter('to_date');
  }

  /**
   * Executes 'Report16' action
   *
   * Tax report for application submitted within a certain time period.
   * Nyarugenge, One Stop Center
   *
   * @param sfRequest $request A request object
   */
  public function executeReport16(sfWebRequest $request)
  {
    $this->fromdate = $request->getPostParameter('from_date');
    $this->todate = $request->getPostParameter('to_date');
  }

  /**
   * Executes 'Report13export' action
   *
   * Report of the income from confirmed payments
   *
   * @param sfRequest $request A request object
   */
  public function executeReport13export(sfWebRequest $request)
  {
    $fromdate = $request->getParameter('fromdate');
    $todate = $request->getParameter('todate');

    date_default_timezone_set('Africa/Kigali');
    $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);


    if (PHP_SAPI == 'cli')
      die('This example should only be run from a Web Browser');

    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/../../../../../lib/vendor/phpexcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("One Stop Center, City Of Kigali")
      ->setTitle("Tax Report, One Stop Center");

    // Add some data
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A4', 'No')
      ->setCellValue('B4', 'Application')
      ->setCellValue('C4', 'Date of submission')
      ->setCellValue('D4', 'Name of project')
      ->setCellValue('E4', 'Submitted by')
      ->setCellValue('F4', 'TIN')
      ->setCellValue('G4', 'Plot No')
      ->setCellValue('H4', 'Registered Usage')
      ->setCellValue('I4', 'Plot Size')
      ->setCellValue('J4', 'Last name or Company name')
      ->setCellValue('K4', 'Identity card number')
      ->setCellValue('L4', 'Calculation or market value (Estimated Construction Cost)')
      ->setCellValue('M4', 'Total m2 of all floors in the building')
      ->setCellValue('N4', 'Year of building construction (or last renovation)')
      ->setCellValue('O4', 'Market value residential fixed asset including plot, building and improvements')
      ->setCellValue('P4', 'Market value commercial/industrial/quarrying purposes fixed asset including plot, building and improvements')
      ->setCellValue('Q4', 'Tax due')
      ->setCellValue('R4', 'Total market value of fixed asset rounded up to full 1000 Rwf')
      ->setCellValue('S4', 'Multiply the value in key 34 with tax rate');
    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A4:S4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A4:S4')->getFill()->getStartColor()->setARGB('46449a');

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('H4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('I4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('J4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('K4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('L4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('M4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('N4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('O4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('P4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('Q4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('R4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('S4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

    $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFill()->getStartColor()->setARGB('504dc5');
    $objPHPExcel->getActiveSheet()->getStyle('A2:S2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A2:S2')->getFill()->getStartColor()->setARGB('504dc5');
    $objPHPExcel->getActiveSheet()->getStyle('A3:S3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A3:S3')->getFill()->getStartColor()->setARGB('504dc5');

    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setName('Logo');
    $objDrawing->setDescription('Logo');
    $objDrawing->setPath('./assets_backend/images/logo.png');
    $objDrawing->setHeight(60);
    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    $objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('H4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('I4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('J4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('K4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('L4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('M4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('N4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('O4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('P4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('Q4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('R4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('S4')->getFont()->setBold(true);

    $count = 4;

    $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);

    $sql = "SELECT * FROM form_entry WHERE approved <> 0 AND approved <> 860 AND parent_submission = 0 AND date_of_submission BETWEEN '" . $fromdate . "' AND '" . $todate . "'";
    $results = mysql_query($sql, $dbconn);

    while ($row2 = mysql_fetch_assoc($results)) {
      $q = Doctrine_Query::create()
        ->from("FormEntry a")
        ->where("a.id = ?", $row2['id'])
        ->andWhere("a.approved <> 0 AND a.approved <> 860 AND a.parent_submission = 0");
      $application = $q->fetchOne();

      $sql = "SELECT * FROM ap_form_60 WHERE id = '" . $application->getEntryId() . "'";
      $results = mysql_query($sql, $dbconn);
      $row = mysql_fetch_assoc($results);

      if ($application) {
        $count++;

        $q = Doctrine_Query::create()
          ->from("SfGuardUserProfile a")
          ->where("a.user_id = ?", $application->getUserId());
        $architect = $q->fetchOne();

        $one = "";
        $two = "";
        $three = "";
        $four = "";
        $five = "";
        $six = "";

        $q = Doctrine_Query::create()
          ->from("FormEntryLinks a")
          ->where("a.formentryid = ?", $application->getId());
        $links = $q->execute();
        foreach ($links as $link) {
          $sql = "SELECT * FROM ap_form_" . $link->getFormId() . " WHERE id = " . $link->getEntryId();
          $results2 = mysql_query($sql, $dbconn);
          while ($row2 = mysql_fetch_assoc($results2)) {
            $one = $row2['element_3'];
            $two = $row2['element_2'];
            $three = $row2['element_4'];
            $four = $row2['element_10'];
            $five = $row2['element_8'];
            $six = $row2['element_9'];
          }
        }

        $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('A' . $count, $count - 4)
          ->setCellValue('B' . $count, $application->getApplicationId())
          ->setCellValue('C' . $count, $application->getDateOfSubmission())
          ->setCellValue('D' . $count, $row['element_2'])
          ->setCellValue('E' . $count, $architect->getFullname())
          ->setCellValue('F' . $count, $row['element_80'])
          ->setCellValue('G' . $count, $row['element_14'])
          ->setCellValue('H' . $count, $row['element_15'])
          ->setCellValue('I' . $count, $row['element_17'])
          ->setCellValue('J' . $count, $row['element_5_1'] . " " . $row['element_5_2'])
          ->setCellValue('K' . $count, $row['element_6'])
          ->setCellValue('L' . $count, "")
          ->setCellValue('M' . $count, $row['element_22'])
          ->setCellValue('N' . $count, $one)
          ->setCellValue('O' . $count, $two)
          ->setCellValue('P' . $count, $three)
          ->setCellValue('Q' . $count, $four)
          ->setCellValue('R' . $count, $five)
          ->setCellValue('S' . $count, $six);
      }
    }

    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle('Report 2');


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="report2.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
  }


  public function executeReport17(sfWebRequest $request)
  {
    $payment_mda = $request->getPostParameter('payment_mda');
    $payment_psp = $request->getPostParameter('payment_psp');
    $form_id = $request->getPostParameter('application_form');
    $fromdate = $request->getPostParameter('fromdate');
    $todate = $request->getPostParameter('todate');

    $this->q = Doctrine_Query::create()
      ->from('MfInvoice a')
      ->leftJoin('a.FormEntry b')
      ->where('b.form_id = ?', $form_id)
      ->andWhere('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($todate)) . "+1 day"))))
      ->orderBy('a.id DESC');

    $qt = Doctrine_Query::create()
      ->select('SUM(b.amount) as total')
      ->from('MfInvoice a')
      ->leftJoin('a.FormEntry c')
      ->leftJoin('a.MfInvoiceDetail b')
      ->where('c.form_id = ?', $form_id)
      ->andWhere('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($todate)) . "+1 day"))))
      ->andWhere('b.description LIKE ? or b.description LIKE ?', array("%Total%", "%submission fee%"))
      ->andWhere('a.paid = 2')
      ->orderBy('a.id DESC');
    $this->total = $qt->fetchOne();

    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $columns = "";
    $columns[] = "Invoice Number";
    $columns[] = "Payer's Name/Citizen";
    $columns[] = "Payment reference number (Provided by PSP) and to appear in bank statement";
    $columns[] = "Amount (KES)";

    $records = "";

    $invoices = $this->q->execute();

    foreach ($invoices as $invoice) {
      $record_columns = "";
      $application = $invoice->getFormEntry();

      if (empty($application)) {
        continue;
      }

      $query = "select * from " . MF_TABLE_PREFIX . "form_payments where form_id = ? and record_id = ?";
      $params = array($application->getFormId(), $application->getEntryId());
      $sth = mf_do_query($query, $params, $dbh);
      $count = 0;
      $row = mf_do_fetch_result($sth);

      $record_columns[] = $invoice->getInvoiceNumber();
      $record_columns[] = $invoice->getPayerName();

      if ($row) {
        $record_columns[] = $row['payment_id'];
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $invoice->getTotalAmount();

      $records[] = $record_columns;
    }

    if ($this->total) {
      $record_columns = "";
      $record_columns[] = "";
      $record_columns[] = "";
      $record_columns[] = "Total";
      $record_columns[] = $this->total->getTotal();

      $records[] = $record_columns;
    }

    $this->ReportGeneratorKRA1("Detailed Transactions Report" . date("Y-m-d"), $columns, $records, $form_id);
    exit;
  }

  public function executeReport18(sfWebRequest $request)
  {
    $payment_mda = $request->getPostParameter('payment_mda');
    $payment_psp = $request->getPostParameter('payment_psp');
    $form_id = $request->getPostParameter('application_form');
    $fromdate = $request->getPostParameter('fromdate');
    $todate = $request->getPostParameter('todate');

    $this->q = Doctrine_Query::create()
      ->from('MfInvoice a')
      ->leftJoin('a.FormEntry b')
      ->where('b.form_id = ?', $form_id)
      ->andWhere('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($todate)) . "+1 day"))))
      ->orderBy('a.id DESC');

    $qt = Doctrine_Query::create()
      ->select('SUM(b.amount) as total')
      ->from('MfInvoice a')
      ->leftJoin('a.FormEntry c')
      ->leftJoin('a.MfInvoiceDetail b')
      ->where('c.form_id = ?', $form_id)
      ->andWhere('a.created_at BETWEEN ? AND ?', array(date('Y-m-d', strtotime(date("Y-m-d", strtotime($fromdate)) . "-1 day")), date('Y-m-d', strtotime(date("Y-m-d", strtotime($todate)) . "+1 day"))))
      ->andWhere('b.description LIKE ? or b.description LIKE ?', array("%Total%", "%submission fee%"))
      ->andWhere('a.paid = 2')
      ->orderBy('a.id DESC');
    $this->total = $qt->fetchOne();

    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Service Description";
    $columns[] = "MDA Bank";
    $columns[] = "MDA Account";
    $columns[] = "Amount (KES)";

    $records = "";

    $invoices = $this->q->execute();

    foreach ($invoices as $invoice) {
      $record_columns = "";
      $application = $invoice->getFormEntry();

      if (empty($application)) {
        continue;
      }

      $query = "select * from " . MF_TABLE_PREFIX . "form_payments where form_id = ? and record_id = ?";
      $params = array($application->getFormId(), $application->getEntryId());
      $sth = mf_do_query($query, $params, $dbh);
      $count = 0;
      $row = mf_do_fetch_result($sth);

      $q = Doctrine_Query::create()
        ->from("ApForms a")
        ->where("a.form_id = ?", $application->getFormId());
      $form = $q->fetchOne();

      $record_columns[] = $form->getFormCode();
      $record_columns[] = $form->getFormName();
      $record_columns[] = sfConfig::get('app_mda_bank');
      $record_columns[] = sfConfig::get('app_mda_account_' . $form->getFormId());
      $record_columns[] = $invoice->getTotalAmount();

      $records[] = $record_columns;
    }

    if ($this->total) {
      $record_columns = "";
      $record_columns[] = "";
      $record_columns[] = "";
      $record_columns[] = "";
      $record_columns[] = "Total";
      $record_columns[] = $this->total->getTotal();

      $records[] = $record_columns;
    }

    $this->ReportGeneratorKRA2("Summary Collection and Remittance Report" . date("Y-m-d"), $columns, $records, $form_id);
    exit;
  }


  /**      Custom Report Generator ( or Report settings under System Settings)  start here      **/

  /**
   * Executes batch action
   *
   * Deletes a group of selected reports
   *
   * @param sfRequest $request A request object
   */
  public function executeBatch(sfWebRequest $request)
  {
    if ($request->getPostParameter('delete')) {
      $item = Doctrine_Core::getTable('Reports')->find(array($request->getPostParameter('delete')));
      if ($item) {
        $item->delete();
      }
    }
  }

  /**
   * Executes index action
   *
   * Shows a list of custom generated reports
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    if ($this->getUser()->getAttribute('form_filter', $request->getParameter("filter"))) {
      if ($request->getParameter("filter")) {
        //Save filter to session
        $this->getUser()->setAttribute('form_filter', $request->getParameter("filter"));
      }

      $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.menu_id = ?", $this->getUser()->getAttribute('form_filter'));
      $stages = $q->execute();

      $stages_array = array();
      $comment_stages_array = array();

      foreach ($stages as $stage) {
        $stages_array[] = "a.form_stage = " . $stage->getId();
        $comment_stages_array[] = "a.form_department_stage = " . $stage->getId();
      }

      $stages_query = implode(" OR ", $stages_array);
      $comment_stages_query = implode(" OR ", $comment_stages_array);

      $q = Doctrine_Query::create()
        ->from("ApForms a")
        ->where('a.form_type = 1 or a.form_type = 2')
        ->andWhere('a.form_active = 1')
        ->andWhere($stages_query . " OR " . $comment_stages_query);
      $this->reports = $q->execute();

      $q = Doctrine_Query::create()
        ->from("Menus a")
        ->where("a.id = ?", $this->getUser()->getAttribute('form_filter'));
      $this->service = $q->fetchOne();
    } else {
      $q = Doctrine_Query::create()
        ->from("ApForms a")
        ->where('a.form_type = 1 or a.form_type = 2')
        ->andWhere('a.form_active = 1');
      $this->reports = $q->execute();
    }

    $this->setLayout("layout-settings");
  }


  /**
   * Executes New action
   *
   * Displays form for creating a new custom report
   *
   * @param sfRequest $request A request object
   */
  public function executeNew(sfWebRequest $request)
  {
    if ($request->getParameter("formid") != "") {
      $this->formid = $request->getParameter("formid");
      $this->selectedform = $request->getParameter("formid");
      $this->application_form = $request->getParameter("formid");
      $this->type = $request->getParameter("type");
      $this->title = $request->getParameter("title");
    }
    $this->setLayout(false);
  }



  /**
   * Executes Edit action
   *
   * Displays form for editing an existing custom report
   *
   * @param sfRequest $request A request object
   */
  public function executeEdit(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter('id'));
    $this->report = $q->fetchOne();
    $this->formid = $this->report->getFormId();
    $this->selectedform = $this->report->getFormId();
    $this->application_form = $this->report->getFormId();
    $this->type = $this->report->getType();
    $this->title = $this->report->getTitle();
    if ($request->getParameter("formid") != "") {
      $this->formid = $request->getParameter("formid");
      $this->type = $request->getParameter("type");
      $this->title = $request->getParameter("title");
    }
    $this->setLayout(false);
  }



  /**
   * Executes create action
   *
   * Processes POST data submitted when user is creating a new custom report
   *
   * @param sfRequest $request A request object
   */
  public function executeCreate(sfWebRequest $request)
  {
    if ($request->isMethod(sfRequest::POST)) {
      $report = new Reports();
      $report->setType($request->getPostParameter("rpttype"));
      $report->setFormId($request->getPostParameter("application_form"));
      $report->setTitle($request->getPostParameter("title"));
      $report->setContent($request->getPostParameter("rptcontent"));
      $report->save();

      if ($report->getId() != "") {
        if ($request->getPostParameter("application_filter") != "0") {
          $filter = new ReportFilters();
          $filter->setReportId($report->getId());
          $filter->setElementId($request->getPostParameter("application_actions"));
          $filter->setValue($request->getPostParameter("application_filter"));
          $filter->save();
        }

        $fields = $request->getPostParameter("fields");
        foreach ($fields as $field) {
          $reportfield = new ReportFields();
          $reportfield->setReportId($report->getId());
          $reportfield->setElement($field);
          $reportfield->save();
        }
      }
    }

    $this->redirect("/plan/settings/forms?load=reports");
  }



  /**
   * Executes update action
   *
   * Processes POST data submitted when user is editing an existing custom report
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdate(sfWebRequest $request)
  {
    if ($request->isMethod(sfRequest::POST)) {
      $q = Doctrine_Query::create()
        ->from('Reports a')
        ->where('a.id = ?', $request->getParameter('id'));
      $report = $q->fetchOne();
      $report->setType($request->getPostParameter("rpttype"));
      $report->setFormId($request->getPostParameter("application_form"));
      $report->setTitle($request->getPostParameter("title"));
      $report->setContent($request->getPostParameter("rptcontent"));
      $report->save();

      if ($report->getId() != "") {
        if ($request->getPostParameter("application_filter") != "0") {
          $q = Doctrine_Query::create()
            ->from('ReportFilters a')
            ->where('a.report_id = ?', $request->getParameter('id'));
          $filters = $q->execute();
          foreach ($filters as $filter) {
            $filter->delete();
          }

          $filter = new ReportFilters();
          $filter->setReportId($report->getId());
          $filter->setElementId($request->getPostParameter("application_actions"));
          $filter->setValue($request->getPostParameter("application_filter"));
          $filter->save();
        }

        $q = Doctrine_Query::create()
          ->from('ReportFields a')
          ->where('a.report_id = ?', $request->getParameter('id'));
        $fields = $q->execute();
        foreach ($fields as $field) {
          $field->delete();
        }

        $fields = $request->getPostParameter("fields");
        $headers = $request->getPostParameter("headers");
        $count = 0;
        foreach ($fields as $field) {
          $reportfield = new ReportFields();
          $reportfield->setReportId($report->getId());
          $reportfield->setElement($field);
          $reportfield->setCustomheader($headers[$count]);
          $reportfield->save();
          $count++;
        }
      }
    }

    $this->redirect("/plan/settings/forms?load=reports");
  }


  /**
   * Executes delete action
   *
   * Deletes an existing custom report
   *
   * @param sfRequest $request A request object
   */
  public function executeDelete(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter("id"));
    $report = $q->fetchOne();

    $report->delete();

    $this->redirect("/plan/settings/forms?load=reports");
  }



  /**
   * Executes singleview action
   *
   * Custom report viewer that displays report for a single record
   *
   * @param sfRequest $request A request object
   */
  public function executeSingleview(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter("reportid"));
    $this->report = $q->fetchOne();

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.id = ?', $request->getParameter("applicationid"))
      ->andWhere('a.parent_submission = 0');
    $this->application = $q->fetchOne();
  }



  /**
   * Executes singleview action
   *
   * Prints single view custom reports
   *
   * @param sfRequest $request A request object
   */
  public function executePrintsingleview(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter("reportid"));
    $this->report = $q->fetchOne();

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.id = ?', $request->getParameter("applicationid"))
      ->andWhere('a.parent_submission = 0');
    $this->application = $q->fetchOne();

    $this->setLayout(false);
  }



  /**
   * Executes singlecustom action
   *
   * Custom report viewer that displays report for a single record
   *
   * @param sfRequest $request A request object
   */
  public function executeSinglecustom(sfWebRequest $request)
  {

    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter("id"));
    $this->report = $q->fetchOne();

    if ($request->isMethod(sfRequest::POST)) {
      $this->fromdate = $request->getPostParameter('from_date');
      $this->todate = $request->getPostParameter('to_date');
    }
  }



  /**
   * Executes multiplecustom action
   *
   * Custom report viewer that displays report for multiple records
   *
   * @param sfRequest $request A request object
   */
  public function executeMultiplecustom(sfWebRequest $request)
  {

    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter("id"));
    $this->report = $q->fetchOne();

    if ($request->isMethod(sfRequest::POST)) {
      $this->fromdate = $request->getPostParameter('from_date');
      $this->todate = $request->getPostParameter('to_date');
    }

    $this->setLayout("layout");
  }



  /**
   * Executes 'Exportcustom' action
   *
   * Export multicustom report to excel
   *
   * @param sfRequest $request A request object
   */
  public function executeExportcustom(sfWebRequest $request)
  {
    date_default_timezone_set('Africa/Kigali');
    $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);

    $q = Doctrine_Query::create()
      ->from('Reports a')
      ->where('a.id = ?', $request->getParameter("id"));
    $report = $q->fetchOne();

    $q = Doctrine_Query::create()
      ->from('ReportFields a')
      ->where('a.report_id = ?', $report->getId());
    $fields = $q->execute();

    function find($needle, $haystack)
    {
      $pos = strpos($haystack, $needle);
      if ($pos === false) {
        return false;
      } else {
        return true;
      }
    }

    $q = Doctrine_Query::create()
      ->from('apFormElements a')
      ->where('a.form_id = ?', $report->getFormId());
    $formelements = $q->execute();

    /**
     *
     * Function to get all the dates between a period
     *
     * @param String $sStartDate Starting date to begin fetching dates from
     *
     * @return String[]
     */
    function GetDays($sStartDate, $sEndDate)
    {
      $aDays[] = $start_date;
      $start_date = $sStartDate;
      $end_date = $sEndDate;
      $current_date = $start_date;
      while (strtotime($current_date) <= strtotime($end_date)) {
        $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
        $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
      }


      return $aDays;
    }

    if (PHP_SAPI == 'cli')
      die('This example should only be run from a Web Browser');

    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/../../../../../lib/vendor/phpexcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("One Stop Center, City Of Kigali")
      ->setTitle("Report 1");

    // Add some data

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

    $column = 'A';
    foreach ($fields as $field) {
      $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
      $column++;
    }


    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue($column . 'A4', '#');

    $parser = new templateparser();

    $column = 'B';
    foreach ($fields as $field) {
      if ($field->getCustomheader() == "") {
        $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue($column . '4', $parser->parseHeaders($request->getParameter("form"), $field->getElement()));
      } else {
        $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue($column . '4', $field->getCustomheader());
      }
      $column++;
    }


    $lastcolumn = $column--;

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A4:' . $lastcolumn . '4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A4:' . $lastcolumn . '4')->getFill()->getStartColor()->setARGB('46449a');


    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

    $column = 'B';
    foreach ($fields as $field) {
      $objPHPExcel->getActiveSheet()->getStyle($column . '4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
      $column++;
    }

    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastcolumn . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastcolumn . '1')->getFill()->getStartColor()->setARGB('504dc5');
    $objPHPExcel->getActiveSheet()->getStyle('A2:' . $lastcolumn . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A2:' . $lastcolumn . '2')->getFill()->getStartColor()->setARGB('504dc5');
    $objPHPExcel->getActiveSheet()->getStyle('A3:' . $lastcolumn . '3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A3:' . $lastcolumn . '3')->getFill()->getStartColor()->setARGB('504dc5');

    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setName('Logo');
    $objDrawing->setDescription('Logo');
    $objDrawing->setPath('./assets_backend/images/logo.png');
    $objDrawing->setHeight(60);
    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    $column = 'B';
    foreach ($fields as $field) {
      $objPHPExcel->getActiveSheet()->getStyle($column . '4')->getFont()->setBold(true);
      $column++;
    }


    $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);

    $query = "SELECT * FROM ap_form_" . $request->getParameter("form") . " WHERE date_created BETWEEN '" . $request->getParameter("from") . "' AND '" . $request->getParameter("to") . "'";
    $results = mysql_query($query, $dbconn);

    $count = 4;


    while ($row = mysql_fetch_assoc($results)) {

      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.form_id = ?', $request->getParameter("form"))
        ->andWhere('a.entry_id = ?', $row['id'])
        ->andWhere('a.approved <> ? AND a.approved <> ?', array('897', '0'));
      $application = $q->fetchOne();

      if ($application) {
        $count++;
        $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('A' . $count, $count - 4);
        $column = "B";
        foreach ($fields as $field) {
          if (find("{fm_element", $field->getElement())) {
            $element = str_replace("{fm_", "", $field->getElement());
            $element = str_replace("}", "", $element);

            $q = Doctrine_Query::create()
              ->from('apFormElements a')
              ->where('a.form_id = ?', $report->getFormId())
              ->andWhere('a.element_id = ?', str_replace("element_", "", $element))
              ->andWhere('a.element_total_child > 0');
            $formelements = $q->execute();

            if (sizeof($formelements) > 0) {
              $q = Doctrine_Query::create()
                ->from('ApElementOptions a')
                ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($report->getFormId(), str_replace("element_", "", $element), $row[$element]));
              $option = $q->fetchOne();

              if ($option) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $option->getOptionText());
              }
            } else {
              $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $row[$element]);
            }
          } else if (find("{ap_application_status}", $field->getElement())) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $application->getStatusName());
          } else if (find("{ap_date_of_submission}", $field->getElement())) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $application->getDateOfSubmission());
          } else if (find("{ap_date_of_approval}", $field->getElement())) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $application->getDateOfIssue());
          } else if (find("{ap_application_id}", $field->getElement())) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $application->getApplicationId());
          } else {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $count, $field->getElement());
          }
          $column++;
        }
      }
    }


    // Miscellaneous glyphs, UTF-8

    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle("Export");


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="report1.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
  }


  public function executeReporttasks(sfWebRequest $request)
  {
    if ($request->isMethod(sfRequest::POST)) {
      $this->filterreviewer = $request->getPostParameter('task_reviewer');
      $this->filterstatus = $request->getPostParameter('task_status');
      $this->fromdate = $request->getPostParameter('from_date');
      $this->todate = $request->getPostParameter('to_date');
    }
  }

  /**
   * Executes 'ReportGenerator' action
   *
   * Reusable excel generator
   *
   * @param sfRequest $request A request object
   */
  public function ReportGenerator($reportname, $columns, $records)
  {
    date_default_timezone_set('Africa/Nairobi');

    if (PHP_SAPI == 'cli')
      die('This example should only be run from a Web Browser');

    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/../../../../../lib/vendor/phpexcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("eCitizen")->setTitle($reportname);

    // Add some data
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getColumnDimension($alpha_count)->setAutoSize(true);
      $alpha_count++;
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', 'No');
    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . '4', $value);
      $alpha_count++;
    }

    $alpha_count--;

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->getStartColor()->setARGB('46449a');

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
      $alpha_count++;
    }

    $alpha_count--;

    $objPHPExcel->getActiveSheet()->getStyle('A1:' . ($alpha_count) . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:' . ($alpha_count) . '1')->getFill()->getStartColor()->setARGB('504dc5');
    $objPHPExcel->getActiveSheet()->getStyle('A2:' . ($alpha_count) . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A2:' . ($alpha_count) . '2')->getFill()->getStartColor()->setARGB('504dc5');
    $objPHPExcel->getActiveSheet()->getStyle('A3:' . ($alpha_count) . '3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A3:' . ($alpha_count) . '3')->getFill()->getStartColor()->setARGB('504dc5');

    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setName('Logo');
    $objDrawing->setDescription('Logo');
    $objDrawing->setPath('./assets_backend/images/logo.png');
    $objDrawing->setHeight(60);
    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->setBold(true);
      $alpha_count++;
    }

    /**
     * Fetch all applications linked to the filtered 'type of application' and the 'start date'
     */
    $count = 5;

    // Miscellaneous glyphs, UTF-8
    $alpha_count = "B";

    foreach ($records as $record_columns) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $count, $count - 4);
      $alpha_count = "B";
      foreach ($record_columns as $key => $value) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . $count, $value);
        $alpha_count++;
      }
      $count++;
    }


    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle($reportname);


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $reportname . '.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
  }


  /**
   * Executes 'ReportGenerator' action
   *
   * Reusable excel generator
   *
   * @param sfRequest $request A request object
   */
  public function ReportGeneratorKRA1($reportname, $columns, $records, $form_id)
  {
    date_default_timezone_set('Africa/Nairobi');

    if (PHP_SAPI == 'cli')
      die('This example should only be run from a Web Browser');

    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/../../../../../lib/vendor/phpexcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("eCitizen")
      ->setTitle('Report');

    // Add some data
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getColumnDimension($alpha_count)->setAutoSize(true);
      $alpha_count++;
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', 'No');
    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . '4', $value);
      $alpha_count++;
    }

    $alpha_count--;

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->getStartColor()->setARGB('46449a');

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
      $alpha_count++;
    }

    $alpha_count--;

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', 'MDA');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', sfConfig::get('app_mda_branch'));

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', 'Report Type: ');

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', 'Detail Transactions Report');


    $q = Doctrine_Query::create()
      ->from('ApForms a')
      ->where('a.form_id = ?', $form_id);
    $form = $q->fetchOne();

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', 'Service Description: ');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3', $form->getFormName());

    //$objPHPExcel->getActiveSheet()->getStyle('A1:'.($alpha_count).'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    //$objPHPExcel->getActiveSheet()->getStyle('A1:'.($alpha_count).'1')->getFill()->getStartColor()->setARGB('504dc5');
    //$objPHPExcel->getActiveSheet()->getStyle('A2:'.($alpha_count).'2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    //$objPHPExcel->getActiveSheet()->getStyle('A2:'.($alpha_count).'2')->getFill()->getStartColor()->setARGB('504dc5');
    //$objPHPExcel->getActiveSheet()->getStyle('A3:'.($alpha_count).'3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    //$objPHPExcel->getActiveSheet()->getStyle('A3:'.($alpha_count).'3')->getFill()->getStartColor()->setARGB('504dc5');


    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->setBold(true);
      $alpha_count++;
    }

    /**
     * Fetch all applications linked to the filtered 'type of application' and the 'start date'
     */
    $count = 5;

    // Miscellaneous glyphs, UTF-8
    $alpha_count = "B";

    foreach ($records as $record_columns) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $count, $count - 4);
      $alpha_count = "B";
      foreach ($record_columns as $key => $value) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . $count, $value);
        $alpha_count++;
      }
      $count++;
    }


    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle('Report');


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $reportname . '.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
  }


  /**
   * Executes 'ReportGenerator' action
   *
   * Reusable excel generator
   *
   * @param sfRequest $request A request object
   */
  public function ReportGeneratorKRA2($reportname, $columns, $records, $form_id)
  {
    date_default_timezone_set('Africa/Nairobi');

    if (PHP_SAPI == 'cli')
      die('This example should only be run from a Web Browser');

    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/../../../../../lib/vendor/phpexcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("eCitizen")
      ->setTitle('Report');

    // Add some data
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getColumnDimension($alpha_count)->setAutoSize(true);
      $alpha_count++;
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', 'No');
    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . '4', $value);
      $alpha_count++;
    }

    $alpha_count--;

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->getStartColor()->setARGB('46449a');

    $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
      $alpha_count++;
    }

    $alpha_count--;

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', 'MDA:');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', sfConfig::get('app_mda_branch'));

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', 'Report Type: ');

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', 'Summary Collection and Remittance Report');

    $q = Doctrine_Query::create()
      ->from('ApForms a')
      ->where('a.form_id = ?', $form_id);
    $form = $q->fetchOne();

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', 'Service Description: ');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3', $form->getFormName());

    //$objPHPExcel->getActiveSheet()->getStyle('A1:'.($alpha_count).'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    //$objPHPExcel->getActiveSheet()->getStyle('A1:'.($alpha_count).'1')->getFill()->getStartColor()->setARGB('504dc5');
    //$objPHPExcel->getActiveSheet()->getStyle('A2:'.($alpha_count).'2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    //$objPHPExcel->getActiveSheet()->getStyle('A2:'.($alpha_count).'2')->getFill()->getStartColor()->setARGB('504dc5');
    //$objPHPExcel->getActiveSheet()->getStyle('A3:'.($alpha_count).'3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    //$objPHPExcel->getActiveSheet()->getStyle('A3:'.($alpha_count).'3')->getFill()->getStartColor()->setARGB('504dc5');


    $alpha_count = "B";
    foreach ($columns as $key => $value) {
      $objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->setBold(true);
      $alpha_count++;
    }

    /**
     * Fetch all applications linked to the filtered 'type of application' and the 'start date'
     */
    $count = 5;

    // Miscellaneous glyphs, UTF-8
    $alpha_count = "B";

    foreach ($records as $record_columns) {
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $count, $count - 4);
      $alpha_count = "B";
      foreach ($record_columns as $key => $value) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . $count, $value);
        $alpha_count++;
      }
      $count++;
    }


    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle('Report');


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $reportname . '.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
  }

  public function executeReportfilter(sfWebRequest $request)
  {
    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $applicationform = $request->getParameter("application_form_filter");
    $startdate = $request->getPostParameter('from_date_filter');
    $enddate = $request->getPostParameter('to_date_filter');
    $dropdownfield = $request->getPostParameter('form_dropdown_fields');
    $dropdownoption = $request->getPostParameter('form_dropdown_value_fields');

    $this->getUser()->setAttribute('application_form', $applicationform);
    $this->getUser()->setAttribute('start_date', $startdate);
    $this->getUser()->setAttribute('end_date', $enddate);
    $this->getUser()->setAttribute('dropdown_field', $dropdownfield);
    $this->getUser()->setAttribute('dropdown_option', $dropdownoption);

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    $query = "SELECT a.id as id, a.date_created as date_created FROM ap_form_" . $applicationform . "  a LEFT JOIN form_entry b ON a.id = b.entry_id WHERE a.date_created BETWEEN '" . $startdate . "' AND '" . $enddate . "' AND b.form_id = " . $applicationform . " AND b.approved <> 0 AND b.parent_submission = 0";

    $query = $query . " AND ";
    $query = $query . "a.element_" . $dropdownfield . " = " . $dropdownoption;

    $sth = mf_do_query($query, array(), $dbh);

    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $applicationform)
      ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section', 'file'))
      ->andWhere('a.element_status = 1')
      ->orderBy('a.element_position ASC');
    $fields = $q->execute();

    $columns = array();
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    foreach ($fields as $field) {
      $columns[] = $field->getElementTitle();
    }

    $records = array();

    while ($row = mf_do_fetch_result($sth)) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.form_id = ?', $applicationform)
        ->andWhere('a.entry_id = ?', $row['id'])
        ->andWhere('a.approved <> ?', '0')
        ->andWhere('a.parent_submission = ?', '0');
      $application = $q->fetchOne();
      if ($application) {
        $query = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = '" . $application->getEntryId() . "'";
        $sth1 = mf_do_query($query, array(), $dbh);
        $apform = mf_do_fetch_result($sth1);

        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormCode();
          $record_columns[] = $form->getFormName();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $record_columns[] = $application->getDateOfSubmission();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.id = ?', $application->getUserId());
        $user = $q->fetchOne();
        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        } else {
          $record_columns[] = "-";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();

        foreach ($fields as $field) {
          if ($field->getElementType() == "select") {
            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.option_id = ?', $apform["element_" . $field->getElementId()])
              ->andWhere('a.form_id = ?', $application->getFormId());
            $option_value = $q->fetchOne();
            if ($option_value) {
              $record_columns[] = $option_value->getOptionText();
            } else {
              $record_columns[] = "-";
            }
          } elseif ($field->getElementType() == "checkbox" || $field->getElementType() == "radio") {
            $choices = "";

            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.form_id = ?', $application->getFormId());
            $options = $q->execute();
            foreach ($options as $option) {
              if ($apform["element_" . $field->getElementId() . "_" . $option->getOptionId()]) {
                $choices .= $option->getOptionText() . ", ";
              }
            }

            $record_columns[] = $choices;
          } else {
            $record_columns[] = $apform["element_" . $field->getElementId()];
          }
        }

        $records[] = $record_columns;
      }
    }

    $this->columns = $columns;
    $this->records = $records;
  }

  public function executePrintreportfilter(sfWebRequest $request)
  {
    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $applicationform = $this->getUser()->getAttribute('application_form');
    $startdate = $this->getUser()->getAttribute('start_date');
    $enddate = $this->getUser()->getAttribute('end_date');
    $dropdownfield = $this->getUser()->getAttribute('dropdown_field');
    $dropdownoption = $this->getUser()->getAttribute('dropdown_option');

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    $query = "SELECT a.id as id, a.date_created as date_created FROM ap_form_" . $applicationform . "  a LEFT JOIN form_entry b ON a.id = b.entry_id WHERE a.date_created BETWEEN '" . $startdate . "' AND '" . $enddate . "' AND b.form_id = " . $applicationform . " AND b.approved <> 0 AND b.parent_submission = 0";

    $query = $query . " AND ";
    $query = $query . "a.element_" . $dropdownfield . " = " . $dropdownoption;

    $sth = mf_do_query($query, array(), $dbh);

    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $applicationform)
      ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section', 'file'))
      ->andWhere('a.element_status = 1')
      ->orderBy('a.element_position ASC');
    $fields = $q->execute();

    $columns = array();
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    foreach ($fields as $field) {
      $columns[] = $field->getElementTitle();
    }

    $records = array();

    while ($row = mf_do_fetch_result($sth)) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.form_id = ?', $applicationform)
        ->andWhere('a.entry_id = ?', $row['id'])
        ->andWhere('a.approved <> ?', '0')
        ->andWhere('a.parent_submission = ?', '0');
      $application = $q->fetchOne();
      if ($application) {
        $query = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = '" . $application->getEntryId() . "'";
        $sth1 = mf_do_query($query, array(), $dbh);
        $apform = mf_do_fetch_result($sth1);

        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormCode();
          $record_columns[] = $form->getFormName();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $record_columns[] = $application->getDateOfSubmission();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.id = ?', $application->getUserId());
        $user = $q->fetchOne();
        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        } else {
          $record_columns[] = "-";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();

        foreach ($fields as $field) {
          if ($field->getElementType() == "select") {
            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.option_id = ?', $apform["element_" . $field->getElementId()])
              ->andWhere('a.form_id = ?', $application->getFormId());
            $option_value = $q->fetchOne();
            if ($option_value) {
              $record_columns[] = $option_value->getOptionText();
            } else {
              $record_columns[] = "-";
            }
          } elseif ($field->getElementType() == "checkbox" || $field->getElementType() == "radio") {
            $choices = "";

            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.form_id = ?', $application->getFormId());
            $options = $q->execute();
            foreach ($options as $option) {
              if ($apform["element_" . $field->getElementId() . "_" . $option->getOptionId()]) {
                $choices .= $option->getOptionText() . ", ";
              }
            }

            $record_columns[] = $choices;
          } else {
            $record_columns[] = $apform["element_" . $field->getElementId()];
          }
        }

        $records[] = $record_columns;
      }
    }

    $this->ReportGenerator("Filter-By-Dropdown", $columns, $records);
  }


  public function executePrintreport1(sfWebRequest $request)
  {
    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require_once($prefix_folder . 'includes/init.php');

    require_once($prefix_folder . '../../../config/form_builder_config.php');
    require_once($prefix_folder . 'includes/db-core.php');
    require_once($prefix_folder . 'includes/helper-functions.php');
    require_once($prefix_folder . 'includes/check-session.php');

    require_once($prefix_folder . 'includes/language.php');
    require_once($prefix_folder . 'includes/entry-functions.php');
    require_once($prefix_folder . 'includes/post-functions.php');
    require_once($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();
    $mf_settings = mf_get_settings($dbh);

    $applicationform = $this->getUser()->getAttribute('applicationform');
    $startdate = $this->getUser()->getAttribute('startdate');
    $enddate = $this->getUser()->getAttribute('enddate');

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    function GetDays($sStartDate, $sEndDate)
    {
      $aDays[] = $start_date;
      $start_date = $sStartDate;
      $end_date = $sEndDate;
      $current_date = $start_date;
      while (strtotime($current_date) <= strtotime($end_date)) {
        $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
        $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
      }


      return $aDays;
    }

    $query = "SELECT a.id as id, a.date_created as date_created FROM ap_form_" . $applicationform . "  a LEFT JOIN form_entry b ON a.id = b.entry_id WHERE a.date_created BETWEEN '" . $startdate . "' AND '" . $enddate . "' AND b.form_id = " . $applicationform . " AND b.approved <> 0 AND b.parent_submission = 0";

    $q = Doctrine_Query::create()
      ->from('ApFormElements a')
      ->where('a.form_id = ?', $applicationform)
      ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section', 'file'))
      ->andWhere('a.element_status = 1')
      ->orderBy('a.element_position ASC');
    $fields = $q->execute();

    foreach ($fields as $field) {
      if ($dropdownfield) {
        $query = $query . " AND ";
        $query = $query . "a.element_" . $dropdownfield . " = " . $dropdownoption;
      }
    }

    $results = mf_do_query($query, array(), $dbh);

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    foreach ($fields as $field) {
      $columns[] = $field->getElementTitle();
    }

    $records = "";

    while ($row = mf_do_fetch_result($results)) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.form_id = ?', $applicationform)
        ->andWhere('a.entry_id = ?', $row['id'])
        ->andWhere('a.approved <> ?', '0')
        ->andWhere('a.parent_submission = ?', '0');
      $application = $q->fetchOne();
      if ($application) {
        $query = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = '" . $application->getEntryId() . "'";
        $sth = mf_do_query($query, array(), $dbh);
        $apform = mf_do_fetch_result($sth);

        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormCode();
          $record_columns[] = $form->getFormName();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $record_columns[] = $application->getDateOfSubmission();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();

        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.id = ?', $application->getUserId());
        $user = $q->fetchOne();

        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        } else {
          $record_columns[] = "-";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();

        foreach ($fields as $field) {
          if ($field->getElementType() == "select") {
            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.option_id = ?', $apform["element_" . $field->getElementId()])
              ->andWhere('a.form_id = ?', $application->getFormId());
            $option_value = $q->fetchOne();
            if ($option_value) {
              $record_columns[] = $option_value->getOptionText();
            } else {
              $record_columns[] = "-";
            }
          } elseif ($field->getElementType() == "checkbox" || $field->getElementType() == "radio") {
            $choices = "";

            $q = Doctrine_Query::create()
              ->from('ApElementOptions a')
              ->where('a.element_id = ?', $field->getElementId())
              ->andWhere('a.form_id = ?', $application->getFormId());
            $options = $q->execute();
            foreach ($options as $option) {
              if ($apform["element_" . $field->getElementId() . "_" . $option->getOptionId()]) {
                $choices .= $option->getOptionText() . ", ";
              }
            }

            $record_columns[] = $choices;
          } else {
            $record_columns[] = $apform["element_" . $field->getElementId()];
          }
        }

        $records[] = $record_columns;
      }
    }

    $this->ReportGenerator("Submissions-Report", $columns, $records);
  }


  public function executePrintreporttasks(sfWebRequest $request)
  {
    $filterreviewer = $request->getParameter("reviewer");
    $filterstatus = $request->getParameter("status");
    $startdate = $request->getParameter("startdate");
    $enddate = $request->getParameter("enddate");

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    function GetDays($sStartDate, $sEndDate)
    {
      $aDays[] = $start_date;
      $start_date = $sStartDate;
      $end_date = $sEndDate;
      $current_date = $start_date;
      while (strtotime($current_date) <= strtotime($end_date)) {
        $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
        $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
      }


      return $aDays;
    }

    $days = GetDays($startdate, $enddate);

    $tasks = null;


    if ($filterreviewer == "0") {
      $q = Doctrine_Query::create()
        ->from('CfUser a')
        ->where('a.nid = ?', $this->getUser()->getAttribute('userid'));
      $logged_in_reviewer = $q->fetchOne();
      $department = $logged_in_reviewer->getStrdepartment();
      if ($filterstatus == "0") {
        $q = Doctrine_Query::create()
          ->from("CfUser a")
          ->where("a.strdepartment LIKE ?", "%" . $department . "%")
          ->orderBy("a.strfirstname ASC");
        $reviewers = $q->execute();
        foreach ($reviewers as $reviewer) {
          $q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $reviewer->getNid())
            ->andWhere("a.start_date BETWEEN ? AND ?", array($startdate, $enddate));
          $reviewertasks = $q->execute();
          foreach ($reviewertasks as $reviewertask) {
            $tasks[] = $reviewertask;
          }
        }
      } else {
        $q = Doctrine_Query::create()
          ->from("CfUser a")
          ->where("a.strdepartment LIKE ?", "%" . $department . "%")
          ->orderBy("a.strfirstname ASC");
        $reviewers = $q->execute();
        foreach ($reviewers as $reviewer) {
          $q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $reviewer->getNid())
            ->andWhere("a.status = ?", $filterstatus)
            ->andWhere("a.start_date BETWEEN ? AND ?", array($startdate, $enddate));
          $reviewertasks = $q->execute();
          foreach ($reviewertasks as $reviewertask) {
            $tasks[] = $reviewertask;
          }
        }
      }
    } else {
      $q = Doctrine_Query::create()
        ->from('CfUser a')
        ->where('a.nid = ?', $filterreviewer);
      $reviewer = $q->fetchOne();
      if ($filterstatus == "0") {
        $q = Doctrine_Query::create()
          ->from("Task a")
          ->where("a.owner_user_id = ?", $reviewer->getNid())
          ->andWhere("a.start_date BETWEEN ? AND ?", array($startdate, $enddate));
        $reviewertasks = $q->execute();
        foreach ($reviewertasks as $reviewertask) {
          $tasks[] = $reviewertask;
        }
      } else {
        $q = Doctrine_Query::create()
          ->from("Task a")
          ->where("a.owner_user_id = ?", $reviewer->getNid())
          ->andWhere("a.status = ?", $filterstatus)
          ->andWhere("a.start_date BETWEEN ? AND ?", array($startdate, $enddate));
        $reviewertasks = $q->execute();
        foreach ($reviewertasks as $reviewertask) {
          $tasks[] = $reviewertask;
        }
      }
    }

    $columns = "";
    $columns[] = "#";
    $columns[] = "Reviewer";
    $columns[] = "Application";
    $columns[] = "Task";
    $columns[] = "Started On";
    $columns[] = "Completed On";
    $columns[] = "Status";
    $records = "";

    $count = 0;
    foreach ($tasks as $task) {
      $count++;
      $record_columns = "";
      $record_columns[] = $count;
      $q = Doctrine_Query::create()
        ->from("CfUser a")
        ->where("a.nid = ?", $task->getOwnerUserId());
      $reviewer = $q->fetchOne();
      $record_columns[] = $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();

      $q = Doctrine_Query::create()
        ->from("FormEntry a")
        ->where("a.id = ?", $task->getApplicationId());
      $application = $q->fetchOne();
      $record_columns[] = $application->getApplicationId();
      $record_columns[] = $task->getTypeName();
      $record_columns[] = $task->getStartDate();
      $record_columns[] = $task->getEndDate();
      $record_columns[] = $task->getStatusName();
      $records[] = $record_columns;
    }


    $this->ReportGenerator("Reviewers-Report", $columns, $records);
  }



  public function executePrintreport2(sfWebRequest $request)
  {
    $applicationform = $this->getUser()->getAttribute('applicationform');
    $startdate = $this->getUser()->getAttribute('startdate');
    $enddate = $this->getUser()->getAttribute('enddate');

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    $q = Doctrine_Query::create()
      ->from("SavedPermit a")
      ->leftJoin("a.FormEntry b")
      ->where("a.date_of_issue BETWEEN ? AND ?", array($startdate, $enddate))
      ->andWhere("b.form_id = ?", $applicationform);
    $permits = $q->execute();

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Permit No";
    $columns[] = "Permit Issued On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    $records = "";

    foreach ($permits as $permit) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.id = ?', $permit->getApplicationId());
      $application = $q->fetchOne();
      if ($application) {
        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormCode();
          $record_columns[] = $form->getFormName();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $record_columns[] = $application->getDateOfSubmission();
        $record_columns[] = $permit->getPermitId();
        $record_columns[] = $permit->getDateOfIssue();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.id = ?', $application->getUserId());
        $user = $q->fetchOne();
        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        } else {
          $record_columns[] = "-";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();
        $records[] = $record_columns;
      }
    }

    $this->ReportGenerator("Approvals-Report", $columns, $records);
  }

  public function executePrintreport3(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stages = $this->getUser()->getAttribute("pending_stages");

    foreach ($stages as $stage) {
      if ($pending_stages[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = array();
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Status";
    $records = array();

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();

    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = array();
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();
      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();
      $records[] = $record_columns;
    }

    $this->ReportGenerator("Pending-Stages-Report", $columns, $records);
  }

  public function executePrintreport4(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    function GetDays($sStartDate, $sEndDate)
    {
      $aDays[] = $start_date;
      $start_date = $sStartDate;
      $end_date = $sEndDate;
      $current_date = $start_date;
      while (strtotime($current_date) <= strtotime($end_date)) {
        $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
        $current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
      }


      return $aDays;
    }

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stages = $this->getUser()->getAttribute("pending_stages");

    foreach ($stages as $stage) {
      if ($pending_stages[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    if ($filtags == "") {
      $filtags = $filtags . "a.approved = ? ";
      $filstages[] = "-1";
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Duration";
    $columns[] = "Status";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();

    foreach ($applications as $application) {
      $maximum_duration = 0;
      //get maximum duration of current stage
      $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.id = ?", $application->getApproved());
      $current_stage = $q->fetchOne();
      if ($current_stage) {
        $maximum_duration = $current_stage->getMaxDuration();
      }

      $days = GetDays($application->getDateOfSubmission(), date("Y-m-d"));

      if ($days >= $maximum_duration && $maximum_duration != 0) {
        //show
      } else {
        //don't show
        continue;
      }

      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('ApplicationReference b')
        ->where('b.stage_id = ?', $application->getApproved())
        ->andWhere('b.application_id = ?', $application->getId());
      $application_reference2 = $q->fetchOne();


      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $stage = $q->fetchOne();

      if ($application_reference2) {
        $days = sizeOf(GetDays($application_reference2->getStartDate(), date('Y-m-d')));
        if ($days > $stage->getMaxDuration()) {
          $record_columns[] = $days . " days taken";
        } else {
          $record_columns[] = $days;
        }
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();
      $records[] = $record_columns;
    }

    $this->ReportGenerator("Overdue-Report", $columns, $records);
  }

  public function executePrintreport5(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stages = $this->getUser()->getAttribute('pending_stages');

    foreach ($stages as $stage) {
      if ($pending_stages[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Phone Number";
    $columns[] = "Status";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->leftJoin('a.MfInvoice b')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"))
      ->andWhere("a.declined = 1 OR b.paid <> 2");
    $applications = $q->execute();


    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
        $record_columns[] = $userprofile->getMobile();
      } else {
        $record_columns[] = "-";
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();
      $records[] = $record_columns;
    }


    $this->ReportGenerator("Report 5", $columns, $records);
  }

  public function executePrintreport6(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stage = $this->getUser()->getAttribute('pending_stages');

    foreach ($stages as $stage) {
      if ($pending_stage[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Stage";
    $columns[] = "Sent to this stage on";
    $columns[] = "Sent to this stage by";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();


    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
      $submenu = $q->fetchOne();
      $record_columns[] = $submenu->getTitle();

      $q = Doctrine_Query::create()
        ->from('ApplicationReference b')
        ->where('b.stage_id = ?', $submenu->getId())
        ->andWhere('b.application_id = ?', $application->getId())
        ->orderBy('b.id DESC');
      $application_reference = $q->fetchOne();

      if ($application_reference) {
        $record_columns[] = $application_reference->getStartDate();
        $q = Doctrine_Query::create()
          ->from('CfUser a')
          ->where('a.nid = ?', $application_reference->getApprovedBy());
        $reviewer = $q->fetchOne();
        if ($reviewer) {
          $record_columns[] = $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();
        } else {
          $record_columns[] = "-Client-";
        }
      } else {
        $record_columns[] = "-";
        $record_columns[] = "-";
      }

      $records[] = $record_columns;
    }


    $this->ReportGenerator("Report 6", $columns, $records);
  }

  public function executePrintreport7(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    $pending_stage = $this->getUser()->getAttribute('pending_stages');

    foreach ($stages as $stage) {
      if ($pending_stage[$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Service Code";
    $columns[] = "Form Name";
    $columns[] = "Application No";
    $columns[] = "Submitted On";
    $columns[] = "Submitted By";
    $columns[] = "Notifications";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();


    foreach ($applications as $application) {
      $application_form = $application_manager->get_entry_details($application->getFormId(), $application->getEntryId());

      $record_columns = "";
      $q = Doctrine_Query::create()
        ->from('ApForms a')
        ->where('a.form_id = ?', $application->getFormId());
      $form = $q->fetchOne();
      if ($form) {
        $record_columns[] = $form->getFormCode();
        $record_columns[] = $form->getFormName();
      } else {
        $record_columns[] = "-";
      }

      $record_columns[] = $application->getApplicationId();

      $record_columns[] = $application->getDateOfSubmission();

      $q = Doctrine_Query::create()
        ->from('sfGuardUserProfile a')
        ->where('a.user_id = ?', $application->getUserId());
      $userprofile = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->where('a.id = ?', $application->getUserId());
      $user = $q->fetchOne();
      if ($userprofile) {
        $record_columns[] = $userprofile->getFullname() . " (" . $user->getUsername() . ")";
      } else {
        $record_columns[] = "-";
      }

      $notificationtext = "";

      $q = Doctrine_Query::create()
        ->from('NotificationHistory a')
        ->where('a.application_id = ?', $application->getId());
      $notifications = $q->execute();

      $count = 0;
      foreach ($notifications as $notification) {
        $count++;
        $notificationtext .= $count . "." . $notification->getNotification();
      }

      $record_columns[] = $notificationtext;

      $records[] = $record_columns;
    }


    $this->ReportGenerator("Report 6", $columns, $records);
  }

  public function executePrintreport8(sfWebRequest $request)
  {
    $reviewer = $request->getParameter("status");
    $startdate = $request->getParameter("startdate");
    $enddate = $request->getParameter("enddate");

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($startdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($enddate . ' +1 day'));

    $q = Doctrine_Query::create()
      ->from('Task a');

    if ($reviewer != "0") {
      $q->where('a.owner_user_id = ?', $reviewer);
    }

    $q->andWhere('a.type = ?', '6');
    $q->andWhere('a.start_date BETWEEN ? AND ?', array($startdate, $enddate));
    $tasks = $q->execute();

    $columns = "";
    $columns[] = "Task Type";
    $columns[] = "Applicaiton";
    $columns[] = "Task Started On";
    $columns[] = "Task Ended On";
    $columns[] = "Task Assigned By";
    $columns[] = "Task Status";
    $records = "";

    foreach ($tasks as $task) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.id = ?', $task->getApplicationId())
        ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
      $application = $q->fetchOne();
      if ($application) {
        $record_columns = "";
        $record_columns[] = $task->getTypeName();
        $record_columns[] = $application->getApplicationId();
        $record_columns[] = $task->getStartDate();
        $record_columns[] = $task->getEndDate();
        $q = Doctrine_Query::create()
          ->from('CfUser a')
          ->where('a.nid = ?', $task->getCreatorUserId());
        $reviewer = $q->fetchOne();
        if ($reviewer) {
          $record_columns[] = $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();
        } else {
          $record_columns[] = "-";
        }
        $record_columns[] = $task->getStatusName();
        $records[] = $record_columns;
      }
    }

    $this->ReportGenerator("Report 8", $columns, $records);
  }

  public function executePrintreport10(sfWebRequest $request)
  {
    if ($request->getPostParameter("reviewer")) {
      $fromdate = $this->getUser()->setAttribute("from_date", $request->getPostParameter('from_date10'));
      $todate = $this->getUser()->setAttribute("to_date", $request->getPostParameter('to_date10'));
      $reviewer = $this->getUser()->setAttribute("reviewer", $request->getPostParameter("reviewer"));
    } else {
      $fromdate = $this->getUser()->getAttribute("from_date");
      $todate = $this->getUser()->getAttribute("to_date");
      $reviewer = $this->getUser()->getAttribute("reviewer");
    }

    //Change start date to be inclusive of filtered dates
    $startdate = date('Y-m-d', strtotime($fromdate . ' -1 day'));
    $enddate = date('Y-m-d', strtotime($todate . ' +1 day'));

    $q = Doctrine_Query::create()
      ->from('AuditTrail a')
      ->where('a.action_timestamp BETWEEN ? AND ?', array($startdate, $enddate))
      ->andWhere("a.user_id = ?", $reviewer)
      ->orderBy('a.id DESC');
    $audits = $q->execute();

    $columns = "";
    $columns[] = "Reviewer";
    $columns[] = "Date/Time";
    $columns[] = "Action";
    $records = "";


    foreach ($audits as $audit) {
      $q = Doctrine_Query::create()
        ->from('cfUser a')
        ->where('a.nid = ?', $audit->getUserId());
      $thisuser = $q->fetchOne();
      if ($thisuser) {
        $record_columns[] = $thisuser->getStrlastname() . " " . $thisuser->getStrfirstname();
        $record_columns[] = $audit->getActionTimestamp();
        $record_columns[] = html_entity_decode($audit->getAction());
        $records[] = $record_columns;
      }
    }

    $this->ReportGenerator("Report 10", $columns, $records);
  }

  public function executePrintreport11(sfWebRequest $request)
  {
    $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);

    $q = Doctrine_Query::create()
      ->from('SubMenus a')
      ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
      ->orderBy('a.order_no ASC');
    $stages = $q->execute();

    $filstages = "";

    $filtags = "";

    $count = 0;

    foreach ($stages as $stage) {
      if ($_POST['pending_stage'][$stage->getId()]) {
        $filstages[] = $stage->getId();
        if ($count == 0) {
          $filtags = $filtags . "a.approved = ? ";
        } else {
          $filtags = $filtags . "OR a.approved = ? ";
        }
        $count++;
      }
    }

    $columns = "";
    $columns[] = "Type";
    $columns[] = "No";
    $columns[] = "Submitted By";
    $columns[] = "Checksum";
    $columns[] = "Status";
    $records = "";

    $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where($filtags, $filstages)
      ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0", "", "0"));
    $applications = $q->execute();


    foreach ($applications as $application) {
      if ($application) {
        $record_columns = "";
        $q = Doctrine_Query::create()
          ->from('ApForms a')
          ->where('a.form_id = ?', $application->getFormId());
        $form = $q->fetchOne();
        if ($form) {
          $record_columns[] = $form->getFormDescription();
        } else {
          $record_columns[] = "-";
        }

        $record_columns[] = $application->getApplicationId();

        $q = Doctrine_Query::create()
          ->from('sfGuardUserProfile a')
          ->where('a.user_id = ?', $application->getUserId());
        $userprofile = $q->fetchOne();
        if ($userprofile) {
          $record_columns[] = $userprofile->getFullname();
        } else {
          $record_columns[] = "-";
        }

        //Get checksum
        $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = " . $application->getEntryId();
        $ck_result = mysql_query($sql);
        $ck_row = mysql_fetch_assoc($ck_result);

        $ck_string = "";

        $q = Doctrine_Query::create()
          ->from('ApFormElements a')
          ->where('a.form_id = ?', $application->getFormId());
        $elements = $q->execute();

        foreach ($elements as $element) {
          $ck_string = $ck_string . $ck_row['element_' . $element->getElementId()];
        }

        $q = Doctrine_Query::create()
          ->from('Checksum a')
          ->where('a.entry_id = ?', $application->getId());
        $checksums = $q->execute();
        $str_checksum = "";
        foreach ($checksums as $checksum) {
          $str_checksum = $checksum->getChecksum();
        }

        if (md5($ck_string) == $str_checksum) {
          $record_columns[] = "Secure";
        } else {
          $record_columns[] = "Invalid";
        }

        $q = Doctrine_Query::create()
          ->from('SubMenus a')
          ->where('a.id = ?', $application->getApproved());
        $submenu = $q->fetchOne();
        $record_columns[] = $submenu->getTitle();
        $records[] = $record_columns;
      }
    }

    $this->ReportGenerator("Report 11", $columns, $records);
  }

  /**
   * Executes 'Filterdropdown' action
   *
   * Filter a dropdown based on selected option
   *
   * @param sfRequest $request A request object
   */
  public function executeFilterdropdown(sfWebRequest $request)
  {
    $form_id = $request->getParameter("form_id");
    $element_id = $request->getParameter("element_id");
    $link_id = $request->getParameter("link_id");
    $option_id = $request->getParameter("option_id");

    $q = Doctrine_Query::create()
      ->from("ApDropdownFilters a")
      ->where("a.form_id = ? AND a.element_id = ? AND a.link_id = ? AND a.option_id = ?", array($form_id, $element_id, $link_id, $option_id));
    $filters = $q->execute();

    $filter_options = array();

    foreach ($filters as $filter) {
      $filter_options[] = "a.option_id = " . $filter->getLioptionId();
    }

    $filter_options_query = implode(" OR ", $filter_options);

    $options = array();

    if ($filter_options_query) {
      $q = Doctrine_Query::create()
        ->from("ApElementOptions a")
        ->where("a.form_id = ?", $form_id)
        ->andWhere("a.element_id = ?", $link_id)
        ->andWhere($filter_options_query)
        ->orderBy("a.position ASC");
      $options = $q->execute();
    }

    $filter_js = "";

    $q = Doctrine_Query::create()
      ->from("ApDropdownFilters a")
      ->where("a.form_id = ? AND a.element_id = ?", array($form_id, $link_id));

    if ($q->count() > 0) {
      $filter = $q->fetchOne();

      $filter_js = "onChange='filter_dropdown(" . $form_id . ", " . $link_id . ", " . $filter->getLinkId() . ", this.value);'";
    }

    echo "<select class='element select' id='element_" . $link_id . "' name='extra_filters[]' " . $filter_js . ">";
    echo "<option></option>";
    foreach ($options as $option) {
      echo "<option value='" . $option->getOptionId() . "'>" . $option->getOptionText() . "</option>";
    }
    echo "</select>";
    exit;
  }

  // OTB Metabase login method
  public function executeLogin(sfWebRequest $request)
  {
    $user = $this->validateRequest($request);
    if ($user === sfView::NONE)
      return sfView::NONE;

    $time = time();
    $link = sprintf(
      "http://%s/auth/login?key=%s&timestamp=%s&session_id=%s",
      sfConfig::get('app_report_url'),
      hash('sha256', sfConfig::get('app_api_key') . $time),
      $time,
      session_id()
    );
    return $this->redirect($link);
  }

  public function executeUser(sfWebRequest $request)
  {
    $user = $this->validateRequest($request);
    if ($user === sfView::NONE)
      return sfView::NONE;

    return $this->json(array(
      'username' => 'admin' == $user->getStruserid() ? 'anon' : $user->getStruserid(),
      'first_name' => 'admin' == $user->getStruserid() ? 'Anonymous' : $user->getStrfirstname(),
      'last_name' => 'admin' == $user->getStruserid() ? 'User' : $user->getStrlastname(),
      'dash_id' => 1,
      'email' => 'admin' == $user->getStruserid() ? 'anon@otbafrica.com' : $user->getStremail()
    ));
  }

  public function executeGroup(sfWebRequest $request)
  {
    $user = $this->validateRequest($request);
    if ($user === sfView::NONE)
      return sfView::NONE;

    $groups = array();
    foreach ($user->getMfGuardUserGroup() as $group)
      $groups[] = $group->getGroupId();

    return $this->json($groups);
  }

  public function executeViewdashboard(sfWebRequest $request)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $this->iframe = "{$is_http}://" . sfConfig::get('app_report_url') . '/public/dashboard/' . $request->getParameter('dashboard');
  }

  public function executeRevenue(sfWebRequest $request)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $this->iframe = "{$is_http}://" . sfConfig::get('app_report_url') . '/public/dashboard/' . $request->getParameter('dashboard');
  }
  public function executeProfessionals(sfWebRequest $request)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $this->iframe = "{$is_http}://" . sfConfig::get('app_report_url') . '/public/dashboard/' . $request->getParameter('dashboard');
  }
  public function executeManagementR(sfWebRequest $request)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $this->iframe = "{$is_http}://" . sfConfig::get('app_report_url') . '/public/dashboard/' . $request->getParameter('dashboard');
  }
  public function executeSubcountyReports(sfWebRequest $request)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $this->iframe = "{$is_http}://" . sfConfig::get('app_report_url') . '/public/dashboard/' . $request->getParameter('dashboard');
  }

  public function executePending(sfWebRequest $request)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $this->iframe = "{$is_http}://" . sfConfig::get('app_report_url') . '/public/dashboard/' . $request->getParameter('dashboard');
  }
  public function executeQuestion(sfWebRequest $request)
  {
    $this->iframe = "http://" . sfConfig::get('app_report_url') . '/question/new';
  }

  private function validateRequest(sfWebRequest $request)
  {
    $timestamp = $request->getParameter('timestamp');
    $key = sfConfig::get('app_api_key');

    if (!($request->getParameter('key') == hash('sha256', $key . $timestamp))) {
      return $this->json(array(
        'success' => false,
        'error' => "User not authenticated.",
      ), 403);
    }

    $user = Functions::current_user();
    if (false === $user) {
      return $this->json(array(
        'success' => false,
        'error' => "User not found."
      ), 404);
    }

    return $user;
  }

  private function json($content, $status = 200)
  {
    $this->getResponse()->setHttpHeader('Content-Type', 'application/json');
    $this->getResponse()->setContent(json_encode($content));
    $this->getResponse()->setStatusCode($status);
    return sfView::NONE;
  }
  //OTB END 
}
