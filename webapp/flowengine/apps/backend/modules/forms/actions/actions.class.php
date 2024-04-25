<?php

/**
 * forms actions.
 *
 * @package    permitflow
 * @subpackage forms
 * @author     Your name here
 * @version    SVN: $Id$
 */
class formsActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed form settings. List of forms.");

    $wizard_manager = new WizardManager();

    if ($wizard_manager->is_first_run()) {
      $this->redirect("/backend.php/dashboard");
    }

    if ($request->getParameter("filter")) {
      //Save filter to session
      $this->getUser()->setAttribute('form_filter', $request->getParameter("filter"));
    }

    if ($request->getParameter("filter") == "all") {
      $this->skip_filter = true;
    } else {
      $this->skip_filter = false;
    }

    $q = Doctrine_Query::create()
      ->from("SubMenus a")
      ->where("a.menu_id = ?", $this->getUser()->getAttribute('form_filter'));
    $stages = $q->execute();

    $stages_array = array();
    $comment_stages_array = array();

    foreach ($stages as $stage) {
      $stages_array[] = "form_stage = " . $stage->getId();
      $comment_stages_array[] = "form_department_stage = " . $stage->getId();
    }

    $stages_query = implode(" OR ", $stages_array);
    $comment_stages_query = implode(" OR ", $comment_stages_array);

    $this->allowed_application_stages = $stages_query;
    $this->allowed_comment_stages = $comment_stages_query;

    $this->setLayout("layout-settings");
  }

  /**
   * Executes ajaxform action
   *
   * Display form builder
   *
   * @param sfRequest $request A request object
   */
  public function executeForm(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed edit form");

    $this->setLayout("layout-settings");
  }

  /**
   * Executes manageentries action
   *
   * Display entries
   *
   * @param sfRequest $request A request object
   */
  public function executeManageentries(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed form entries");

    $this->setLayout("layout-settings");
  }

  /**
   * Executes addfield action
   *
   * Add a new field
   *
   * @param sfRequest $request A request object
   */
  public function executeAddfield(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes addmatrixrow action
   *
   * Add a matrix row
   *
   * @param sfRequest $request A request object
   */
  public function executeAddmatrixrow(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes deletedraftfield action
   *
   * Delete a draft field
   *
   * @param sfRequest $request A request object
   */
  public function executeDeletedraftfield(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes deleteform action
   *
   * Delete form
   *
   * @param sfRequest $request A request object
   */
  public function executeDeleteform(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Deleted form");

    $this->setLayout(false);
  }

  /**
   * Executes deletelivefield action
   *
   * Delete live field
   *
   * @param sfRequest $request A request object
   */
  public function executeDeletelivefield(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes deletematrixrow action
   *
   * Delete matrix row
   *
   * @param sfRequest $request A request object
   */
  public function executeDeletematrixrow(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes duplicateform action
   *
   * Duplicate form
   *
   * @param sfRequest $request A request object
   */
  public function executeDuplicateform(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Duplicated form");

    $this->setLayout(false);
  }

  /**
   * Executes edittheme action
   *
   * Edit form theme
   *
   * @param sfRequest $request A request object
   */
  public function executeEdittheme(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes embedcode action
   *
   * Display embed code
   *
   * @param sfRequest $request A request object
   */
  public function executeEmbedcode(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes logicsettings action
   *
   * Logic settings
   *
   * @param sfRequest $request A request object
   */
  public function executeLogicsettings(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed form logic settings");

    $this->setLayout("layout-settings");
  }

  /**
   * Executes savelogicsettings action
   *
   * Logic settings
   *
   * @param sfRequest $request A request object
   */
  public function executeSavelogicsettings(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Updated form logic settings");

    $this->setLayout(false);
  }

  /**
   * Executes addwidget action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeAddwidget(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes editwidget action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeEditwidget(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed form widget settings");

    $this->setLayout("layout-settings");
  }

  /**
   * Executes duplicatewidget action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeDuplicatewidget(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Duplicated form widget");

    $this->setLayout(false);
  }

  /**
   * Executes deletewidget action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeDeletewidget(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Deleted form widget");

    $this->setLayout(false);
  }

  /**
   * Executes widget action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeWidget(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes editwidget action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeWidgetcode(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes savewidgetsettings action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeSavewidgetsettings(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Updated form widget settings");

    $this->setLayout(false);
  }

  /**
   * Executes savewidgetsposition action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeSavewidgetsposition(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes managereport action
   *
   * Custom Reports
   *
   * @param sfRequest $request A request object
   */
  public function executeManagereport(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes notificationsettings action
   *
   * Notification settings
   *
   * @param sfRequest $request A request object
   */
  public function executeNotificationsettings(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes paymentsettings action
   *
   * Payment settings
   *
   * @param sfRequest $request A request object
   */
  public function executePaymentsettings(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed form payment settings");

    $this->setLayout("layout-settings");
  }

  /**
   * Executes savepaymentsettings action
   *
   * Payment settings
   *
   * @param sfRequest $request A request object
   */
  public function executeSavepaymentsettings(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Updated form payment settings");

    $this->setLayout(false);
  }

  /**
   * Executes saveform action
   *
   * Save form
   *
   * @param sfRequest $request A request object
   */
  public function executeSaveform(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Updated form settings");

    $this->setLayout(false);
  }

  /**
   * Executes savetags action
   *
   * Save tags
   *
   * @param sfRequest $request A request object
   */
  public function executeSavetags(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes Stringtotime action
   *
   * String to time
   *
   * @param sfRequest $request A request object
   */
  public function executeStringtotime(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes Synchfields action
   *
   * Synch field settings
   *
   * @param sfRequest $request A request object
   */
  public function executeSynchfields(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes toggleform action
   *
   * Toggle form
   *
   * @param sfRequest $request A request object
   */
  public function executeToggleform(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes view action
   *
   * Display form
   *
   * @param sfRequest $request A request object
   */
  public function executeView(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes confirm action
   *
   * Display form
   *
   * @param sfRequest $request A request object
   */
  public function executeConfirm(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes payment action
   *
   * Display form
   *
   * @param sfRequest $request A request object
   */
  public function executePayment(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes viewentry action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeViewentry(sfWebRequest $request)
  {
    //Temporary action code. Due to upgrade to 2.5. 2.4 actions still point here
    if ($request->getParameter("moveto")) {
      $q = Doctrine_Query::create()
        ->from("Task a")
        ->where("a.id = ?", $request->getParameter("id"));
      $task = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.id = ?", $request->getParameter("moveto"));
      $stage = $q->fetchOne();

      $stage_title = "";
      //OTB ADD - DON't save approved for no existent stage
      if ($stage) {
        $stage_title = $stage->getTitle();


        $application = $task->getApplication();
        $application->setApproved($request->getParameter("moveto"));
        $application->save();

        //Audit 
        Audit::audit($application->getId(), "Moved application to " . $stage_title);
      }
      $this->redirect("/backend.php/tasks/view/id/" . $task->getId());
    }

    $this->setLayout("layout-settings");
  }

  /**
   * Executes editentry action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeEditentry(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Edit form entry");

    $this->setLayout("layout-settings");
  }

  /**
   * Executes emailentry action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeEmailentry(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes viewentrypdf action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeViewentrypdf(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes resetentrynumber action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeResetentrynumber(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
  }

  /**
   * Executes savefilter action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeSavefilter(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes savefilterusers action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeSavefilterusers(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes clearfilter action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeClearfilter(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes clearfilterusers action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeClearfilterusers(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes changeentrystatus action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeChangeentrystatus(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes savecolumnpreference action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeSavecolumnpreference(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes exportentries action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeExportentries(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes deleteentries action
   *
   * Display entry
   *
   * @param sfRequest $request A request object
   */
  public function executeDeleteentries(sfWebRequest $request)
  {
    $this->setLayout(false);
  }

  /**
   * Executes ping action
   *
   * Keep form edit session alive
   *
   * @param sfRequest $request A request object
   */
  public function executePing(sfWebRequest $request)
  {
    $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
    require($prefix_folder . 'includes/init.php');

    require($prefix_folder . 'includes/db-core.php');
    require($prefix_folder . 'includes/helper-functions.php');
    require($prefix_folder . 'includes/check-session.php');
    require($prefix_folder . 'includes/users-functions.php');

    $dbh = mf_connect_db();

    $form_id = (int) $_POST['form_id'];

    if (empty($form_id)) {
      die("Parameter required.");
    }

    //check permission, is the user allowed to access this page?
    if (empty($_SESSION['mf_user_privileges']['priv_administer'])) {
      $user_perms = mf_get_user_permissions($dbh, $form_id, $_SESSION['mf_user_id']);

      //this page need edit_form permission
      if (empty($user_perms['edit_form'])) {
        die("Access Denied. You don't have permission to edit this form.");
      }
    }

    //delete previous record on ap_form_locks table
    $query = "delete from " . MF_TABLE_PREFIX . "form_locks where form_id=?";
    $params = array($form_id);
    mf_do_query($query, $params, $dbh);

    //insert new record
    $current_timestamp = date("Y-m-d H:i:s");

    $query = "insert into " . MF_TABLE_PREFIX . "form_locks(form_id,user_id,lock_date) values(?,?,?)";
    $params = array($form_id, $_SESSION['mf_user_id'], $current_timestamp);
    mf_do_query($query, $params, $dbh);

    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

    echo '{"status" : "ok"}';

    $this->setLayout(false);
    exit;
  }

  /**
   * Executes service action
   *
   * Display advanced service form settings
   *
   * @param sfRequest $request A request object
   */
  public function executeService(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from("ApForms a")
      ->where("a.form_id = ?", $request->getParameter("id"));
    $this->ap_form = $q->fetchOne();

    $this->form_id = $this->ap_form->getFormId();

    $this->form = new BackendServiceSettingsForm();

    $this->setLayout(false);
  }

  /**
   * Executes update action
   *
   * Update form service settings
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdate(sfWebRequest $request)
  {
    $settings = $request->getPostParameter("service_settings");

    $q = Doctrine_Query::create()
      ->from("ApForms a")
      ->where("a.form_id = ?", $request->getParameter("id"));
    $ap_form = $q->fetchOne();

    $ap_form->setFormType($settings["form_type"]);
    $ap_form->setFormStage($settings["form_stage"]);
    $ap_form->setFormIdn($settings["form_idn"]);
    $ap_form->setFormCode($settings["form_code"]);
    $ap_form->save();

    $this->redirect("/backend.php/forms/index");
  }

  /**
   * Executes Move action
   *
   * Move an application
   *
   * @param sfRequest $request A request object
   */
  public function executeMove(sfWebRequest $request)
  {
    $application_manager = new ApplicationManager();
    if ($request->getParameter('id')) {
      $q = Doctrine_Query::create()
        ->from("Task a")
        ->where("a.id = ?", $request->getParameter("id"));
      $task = $q->fetchOne();

      if ($request->getParameter("moveto")) {
        $application = $task->getApplication();
        $next_stage = $request->getParameter("moveto");
        error_log("Check next Stage ---->");
        error_log($next_stage);
        if (intval($next_stage) === 1) {
          $stage_to_send =  $application_manager->get_submission_stage_nakuru($application->getFormId(), $application->getEntryId());
          if ($stage_to_send) {
            $next_stage = $stage_to_send;
          }
          $application->setApproved($next_stage);
        } else {
          $application->setApproved($next_stage);
        }
        $application->save();

        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.id = ?", $request->getParameter("moveto"));
        $stage = $q->fetchOne();

        $stage_title = "";

        if ($stage) {
          $stage_title = $stage->getTitle();
        }
        //Audit 
        Audit::audit($application->getId(), "Moved application to " . $stage_title);
      }

      $this->redirect("/backend.php/tasks/view/id/" . $task->getId());
    }
    //OTB ADD
    if ($request->getParameter('form_entry_id')) {
      $this->forward404Unless($application = Doctrine_Core::getTable('FormEntry')->find($request->getParameter('form_entry_id')));
      $next_stage = $request->getParameter("moveto");

      error_log("Next Stage is this --->");
      error_log($next_stage);

      if ($request->getParameter("moveto")) {
        if (intval($next_stage) === 1) {
          $stage_to_send =  $application_manager->get_submission_stage_nakuru($application->getFormId(), $application->getEntryId());
          if ($stage_to_send) {
            $next_stage = $stage_to_send;
          }
          $application->setApproved($next_stage);
        } else {
          $application->setApproved($next_stage);
        }
        $application->save();
        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.id = ?", $request->getParameter("moveto"));
        $stage = $q->fetchOne();

        $stage_title = "";

        if ($stage) {
          $stage_title = $stage->getTitle();
        }

        //Audit 
        Audit::audit($application->getId(), "Moved application to " . $stage_title);
      }
      $this->redirect("/backend.php/applications/view/id/" . $application->getId());
    }
  }

  /**
   * Executes Approve action
   *
   * Approve an application
   *
   * @param sfRequest $request A request object
   */
  public function executeApprove(sfWebRequest $request)
  {
    if ($request->getParameter("id")) {
      $q = Doctrine_Query::create()
        ->from("Task a")
        ->where("a.id = ?", $request->getParameter("id"));
      $task = $q->fetchOne();

      $application = $task->getApplication();
      if ($request->getParameter("moveto")) {
        $application->setApproved($request->getParameter("moveto"));
        $application->save();

        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.id = ?", $request->getParameter("moveto"));
        $stage = $q->fetchOne();

        $stage_title = "";

        if ($stage) {
          $stage_title = $stage->getTitle();
        }

        //Audit 
        Audit::audit($application->getId(), "Moved application to " . $stage_title);
      }

      $permit_manager = new PermitManager();
      $permit_manager->create_permit($application->getId());

      $this->redirect("/backend.php/tasks/view/id/" . $task->getId());
    }
    if ($request->getParameter('form_entry_id')) {
      $this->forward404Unless($application = Doctrine_Core::getTable('FormEntry')->find($request->getParameter('form_entry_id')));
      if ($request->getParameter("moveto")) {
        $application->setApproved($request->getParameter("moveto"));
        $application->save();

        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.id = ?", $request->getParameter("moveto"));
        $stage = $q->fetchOne();

        $stage_title = "";

        if ($stage) {
          $stage_title = $stage->getTitle();
        }

        //Audit 
        Audit::audit($application->getId(), "Moved application to " . $stage_title);
      }

      $permit_manager = new PermitManager();
      $permit_manager->create_permit($application->getId());

      $this->redirect("/backend.php/applications/view/id/" . $application->getId());
    }
  }

  /**
   * Executes Decline action
   *
   * Decline an application
   *
   * @param sfRequest $request A request object
   */
  public function executeDecline(sfWebRequest $request)
  {
    if ($request->getParameter("id")) {
      $q = Doctrine_Query::create()
        ->from("Task a")
        ->where("a.id = ?", $request->getParameter("id"));
      $this->application = $q->fetchOne()->getApplication();
    }
    if ($request->getParameter("form_entry_id")) {
      $q = Doctrine_Query::create()
        ->from("FormEntry e")
        ->where('e.id = ?', $request->getParameter('form_entry_id'));
      $this->application = $q->fetchOne();
    }
    $this->forward404Unless($this->application, 'Application does not exist!');
    //Check move to
    $sub_menus = Doctrine_Core::getTable('SubMenus')->find($request->getParameter("moveto"));
    if ($request->getParameter("moveto") && $sub_menus) {
      $this->moveto = $request->getParameter("moveto");
    } else {
      $this->moveto = false;
    }
  }

  /**
   * Executes Confirmdecline action
   *
   * Confirm decline of an application
   *
   * @param sfRequest $request A request object
   */
  public function executeConfirmdecline(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod('POST'), 'Method is not a post!');
    $q = Doctrine_Query::create()
      ->from("FormEntry a")
      ->where("a.id = ?", $request->getPostParameter("id"));
    $application = $q->fetchOne();

    $this->forward404Unless($application, sprintf('Application %s does not exit!', $request->getParameter("id")));

    if ($request->getPostParameter("moveto")) {
      $application->setApproved($request->getPostParameter("moveto"));

      $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.id = ?", $request->getParameter("moveto"));
      $stage = $q->fetchOne();

      $stage_title = "";

      if ($stage) {
        $stage_title = $stage->getTitle();
      }

      //Audit 
      Audit::audit($application->getId(), "Declined application to " . $stage_title);
    }
    $application->setDeclined(1);
    $application->save();

    $decline = new EntryDecline();
    $decline->setEntryId($application->getId());
    $decline->setDescription(trim($request->getPostParameter("reason")));
    $decline->setEditFields(json_encode($request->getPostParameter('edit_fields')));
    $decline->setDeclinedBy($this->getUser()->getAttribute('userid', 0));
    $decline->setCreatedAt(date("Y-m-d H:m:s"));
    $decline->setUpdatedAt(date("Y-m-d H:m:s"));
    $decline->save();

    $this->redirect("/backend.php/applications/view/id/" . $application->getId());
  }

  /**
   * Executes Reject action
   *
   * Reject an application
   *
   * @param sfRequest $request A request object
   */
  public function executeReject(sfWebRequest $request)
  {
    if ($request->getParameter("id")) {
      $q = Doctrine_Query::create()
        ->from("Task a")
        ->where("a.id = ?", $request->getParameter("id"));
      $this->task = $q->fetchOne();

      $this->application = $this->task->getApplication();
    }
    if ($request->getParameter("form_entry_id")) {
      $q = Doctrine_Query::create()
        ->from("FormEntry e")
        ->where('e.id = ?', $request->getParameter('form_entry_id'));
      $this->application = $q->fetchOne();
    }
    $this->forward404Unless($this->application, 'Application does not exist!');

    if ($request->getParameter("moveto")) {
      $this->moveto = $request->getParameter("moveto");
    } else {
      $this->moveto = false;
    }
  }

  /**
   * Executes Confirmreject action
   *
   * Confirm rejection of an application
   *
   * @param sfRequest $request A request object
   */
  public function executeConfirmreject(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from("FormEntry e")
      ->where("e.id = ?", $request->getPostParameter("id"));
    $application = $q->fetchOne();

    if ($request->getPostParameter("moveto")) {
      //Check stage
      $sub_menu = Doctrine_Core::getTable('SubMenus')->find($request->getPostParameter("moveto"));
      $this->forward404Unless($sub_menu, sprintf('Stage %s does not exist!', $request->getPostParameter("moveto")));
      $this->forward404Unless($application, sprintf('Application %s does not exist!', $request->getPostParameter("id")));

      $application->setApproved($request->getPostParameter("moveto"));

      //Audit 
      Audit::audit($application->getId(), "Declined application to " . $application->getTitle());
    }
    $application->setDeclined(2);
    $application->save();

    $rejection = new EntryDecline();
    $rejection->setEntryId($application->getId());
    $rejection->setDescription($request->getPostParameter("reason"));
    $rejection->setCreatedAt(date("Y-m-d H:m:s"));
    $rejection->setUpdatedAt(date("Y-m-d H:m:s"));
    $rejection->save();

    $this->redirect("/backend.php/applications/view/id/" . $application->getId());
  }

  /**
   * Executes 'Bulkoptions' action
   *
   * Allows for editing of a dynamic form
   *
   * @param sfRequest $request A request object
   */
  public function executeBulkoptions(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from("ApForms a")
      ->where('a.form_id = ?', $request->getParameter("id"));
    $this->form = $q->fetchOne();

    $this->setLayout("layout-settings");
  }

  /**
   * Executes 'Bulkfilters' action
   *
   * Allows for editing of a dynamic form
   *
   * @param sfRequest $request A request object
   */
  public function executeBulkfilters(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from("ApForms a")
      ->where('a.form_id = ?', $request->getParameter("id"));
    $this->form = $q->fetchOne();

    $this->setLayout("layout-settings");
  }


  /**
   * Executes 'Bulkoptionupdate' action
   *
   * Update an option
   *
   * @param sfRequest $request A request object
   */
  public function executeBulkoptionupdate(sfWebRequest $request)
  {
    if ($this->getUser()->mfHasCredential("manageforms")) {
      $q = Doctrine_Query::create()
        ->from("ApElementOptions a")
        ->where('a.aeo_id = ?', $request->getPostParameter("aeo_id"));
      $option = $q->fetchOne();

      if ($option) {
        $option->setOptionText($request->getPostParameter("value"));
        $option->save();

        $audit = new Audit();
        $audit->saveAudit("", "Updated Option - " . $option->getOptionText() . " - to - " . $request->getPostParameter("value") . " -");
      }

      echo "Updated " . $request->getPostParameter("value");
      exit;
    }
  }

  /**
   * Executes 'Bulkfilterupdate' action
   *
   * Update a filter
   *
   * @param sfRequest $request A request object
   */
  public function executeBulkfilterupdate(sfWebRequest $request)
  {
    $form_id = $request->getPostParameter('form_id');
    $element_id = $request->getPostParameter('element_id');
    $link_id = $request->getPostParameter('link_id');
    $option_id = $request->getPostParameter('option_id');
    $lioption_id = $request->getPostParameter('lioption_id');

    $q = Doctrine_Query::create()
      ->from("ApDropdownFilters a")
      ->where('a.form_id = ? AND a.element_id = ? AND a.link_id = ? AND a.option_id = ? AND a.lioption_id = ?', array($form_id, $element_id, $link_id, $option_id, $lioption_id));
    $filter = $q->fetchOne();

    if ($filter) {
      $filter->delete();
      echo "Deleted";
    } else {
      $filter = new ApDropdownFilters();
      $filter->setFormId($form_id);
      $filter->setElementId($element_id);
      $filter->setLinkId($link_id);
      $filter->setOptionId($option_id);
      $filter->setLioptionId($lioption_id);
      $filter->save();

      echo "Inserted";
    }

    exit;
  }

  /**
   * Executes 'Bulkoptionpublish' action
   *
   * Update an option to live
   *
   * @param sfRequest $request A request object
   */
  public function executeBulkoptionpublish(sfWebRequest $request)
  {
    if ($this->getUser()->mfHasCredential("manageforms")) {
      $q = Doctrine_Query::create()
        ->from("ApElementOptions a")
        ->where('a.aeo_id = ?', $request->getPostParameter("aeo_id"));
      $option = $q->fetchOne();

      if ($option) {
        $option->setLive(1);
        $option->save();

        $audit = new Audit();
        $audit->saveAudit("", "Updated Option - " . $option->getOptionText() . " - to Live!");
      }

      exit;
    }
  }

  /**
   * Executes 'Bulkoptionunpublish' action
   *
   * Update an option to not live
   *
   * @param sfRequest $request A request object
   */
  public function executeBulkoptionunpublish(sfWebRequest $request)
  {
    if ($this->getUser()->mfHasCredential("manageforms")) {
      $q = Doctrine_Query::create()
        ->from("ApElementOptions a")
        ->where('a.aeo_id = ?', $request->getPostParameter("aeo_id"));
      $option = $q->fetchOne();

      if ($option) {
        $option->setLive(0);
        $option->save();

        $audit = new Audit();
        $audit->saveAudit("", "Updated Option - " . $option->getOptionText() . " - to Not Live!");
      }

      exit;
    }
  }

  /**
   * Executes 'Translate' action
   *
   * Allows for editing of language labels
   *
   * @param sfRequest $request A request object
   */
  public function executeTranslate(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from("ApForms a")
      ->where('a.form_id = ?', $request->getParameter("id"));
    $this->form = $q->fetchOne();

    $this->setLayout("layout-settings");
  }

  /**
   * Executes 'Savetranslate' action
   *
   * Allows for editing of a dynamic form
   *
   * @param sfRequest $request A request object
   */
  public function executeSavetranslate(sfWebRequest $request)
  {
    $form_id = $request->getParameter("id");
    $filter = $request->getParameter("service");

    $translator = new translation();

    $q = Doctrine_Query::create()
      ->from("ExtLocales a")
      ->orderBy("a.local_title ASC");
    $languages = $q->execute();

    //Form Name

    foreach ($languages as $language) {
      $translator->setTranslationManual("ap_forms", "form_name", $form_id, $request->getPostParameter("locale_" . $language->getLocaleIdentifier() . "_form_name_" . $language->getLocaleIdentifier()), $language->getLocaleIdentifier());
    }

    //Form Description

    foreach ($languages as $language) {
      $translator->setTranslationManual("ap_forms", "form_description", $form_id, $request->getPostParameter("locale_" . $language->getLocaleIdentifier() . "_form_description_" . $language->getLocaleIdentifier()), $language->getLocaleIdentifier());
    }

    //Form Success Message

    foreach ($languages as $language) {
      $translator->setTranslationManual("ap_forms", "form_success_message", $form_id, $request->getPostParameter("locale_" . $language->getLocaleIdentifier() . "_form_success_message_" . $language->getLocaleIdentifier()), $language->getLocaleIdentifier());
    }

    //Save Element Translations

    $q = Doctrine_Query::create()
      ->from("ApFormElements a")
      ->where("a.form_id = ?", $form_id)
      ->orderBy("a.element_position ASC");
    $elements = $q->execute();

    foreach ($elements as $element) {
      foreach ($languages as $language) {
        //Set the language names
        $lang_values = $request->getPostParameter("locale_" . $language->getLocaleIdentifier());

        $translator->setOptionTranslationManual("ap_form_elements", "element_title", $form_id, $element->getElementId(), $lang_values[$element->getElementId()], $language->getLocaleIdentifier());

        //Set the element guidelines
        $guideline_values = $request->getPostParameter("locale_guideline_" . $language->getLocaleIdentifier());

        $translator->setOptionTranslationManual("ap_form_elements", "element_guidelines", $form_id, $element->getElementId(), $guideline_values[$element->getElementId()], $language->getLocaleIdentifier());

        //Set the option values
        $option_values = $request->getPostParameter("locale_option_" . $element->getElementId() . "_" . $language->getLocaleIdentifier());

        if ($option_values) {
          $q = Doctrine_Query::create()
            ->from("ApElementOptions a")
            ->where("a.form_id = ?", $form_id)
            ->andWhere("a.element_id = ?", $element->getElementId())
            ->andWhere("a.live = 1");
          $options = $q->execute();

          foreach ($options as $option) {
            $translator->setOptionTranslationManual("ap_element_options", "option_text", $option->getAeoId(), $option->getOptionId(), $option_values[$option->getAeoId()], $language->getLocaleIdentifier());
          }
        }
      }
    }

    $q = Doctrine_Query::create()
      ->from("ApFieldLogicConditions a")
      ->where("a.form_id = ?", $form_id);
    $conditions = $q->execute();

    foreach ($conditions as $condition) {
      foreach ($languages as $language) {

        $lang_value = $request->getPostParameter("condition_locale_" . $language->getLocaleIdentifier() . "_" . $condition->getAlcId());

        $translator->setTranslationManual("field_logic_conditions", "rule_keyword", $condition->getAlcId(), $lang_value, $language->getLocaleIdentifier());
      }
    }

    $this->redirect("/backend.php/forms/translate?id=" . $form_id . "&filter=" . $filter);
  }

  public function executeGetcurrency(sfWebRequest $request)
  {
    $merchant = $request->getParameter('merchant');
    //Get currecy for merchant
    $q = Doctrine_Query::create()
      ->select('m.id,c.code,c.name')
      ->from('Merchant m')
      ->innerJoin('m.Currency c')
      ->where('m.name LIKE ?', $merchant);
    $merchants = $q->fetchArray();

    $symbol = '';
    $currecy = '';
    foreach ($merchants as $m) {
      //error_log(print_r($m,true));
      $symbol = $m['Currency'][0]['code'];
      $currency = $m['Currency'][0]['name'];
    }
    //error_log('---------symbol-----'.$symbol.'--------currecy-----'.$currecy);
    echo json_encode(array('currency' => $currency, 'symbol' => $symbol));
    exit;
  }
  public function executeDecline2(sfWebRequest $request)
  {
    $this->setLayout('layout');

    if ($request->isMethod('POST') && $request->getPostParameter("reason")) {
      $entry = Doctrine_Core::getTable('FormEntry')->find(array($request->getPostParameter("entryid")));
      if ($entry) {
        $decline = new EntryDecline();
        $decline->setEntryId($entry->getId());
        $decline->setDescription($request->getPostParameter("reason"));
        $decline->setDeclinedBy($this->getUser()->getAttribute('userid'));
        $decline->save();
        $this->decline = $decline;


        $entry->setApproved($request->getPostParameter('moveto'));
        $entry->setDeclined("1");
        $entry->save();

        /*$q = Doctrine_Query::create()
					 ->from('ApplicationReference a')
					 ->where('a.application_id = ?', $entry->getId())
					 ->andWhere('a.end_date = ?', "");
				$oldappref = $q->fetchOne();*/

        //Save Audit
        $audit = new Audit();
        $audit->saveAudit($entry->getId(), "rejected_request");

        //Complete all of current user's tasks
        $q = Doctrine_Query::create()
          ->from("Task a")
          ->where("a.owner_user_id = ?", $this->getUser()->getAttribute('userid'))
          ->andWhere("a.application_id = ?", $entry->getId())
          ->andWhere("a.status = ?", 1);
        $tasks = $q->execute();

        foreach ($tasks as $task) {
          $task->setStatus(25);
          $task->save();
        }
      }
    } else {
      $entry = Doctrine_Core::getTable('FormEntry')->find(array($_GET['entryid']));
      $former_state = $entry->getApproved();
      if ($entry) {
        $q = Doctrine_Query::create()
          ->from("ApFormElements a")
          ->where("a.form_id = ?", $entry->getFormId())
          ->andWhere("a.element_status = ?", 1)
          ->orderBy("a.element_position ASC");
        $this->fields = $q->execute();

        $this->entry = $entry;
        //OTB check if moveto is a valid one
        $sub_menu = Doctrine_Core::getTable('SubMenus')->find($request->getGetParameter("moveto"));
        $this->forward404Unless($sub_menu, sprintf('Stage id %s does not exist!', $sub_menu));
        $this->moveto = $request->getGetParameter("moveto");
      }
    }
  }
  function executeSignableattachments(sfWebRequest $request)
  {
    $conn = Doctrine_Manager::getInstance()->getCurrentConnection();

    $this->formId = $request->getParameter('id');
    $q = "SELECT a.element_id as id, a.element_title as title, s.id as sample_relation_id , ap.form_id"
      . ", ap.form_name, m.title as service"
      . " FROM ap_form_elements a LEFT JOIN signable_attachments_fields s ON a.element_id = s.element_id "
      . " LEFT JOIN ap_forms ap ON ap.form_id = a.form_id "
      . " LEFT JOIN menus m ON m.service_form = ap.form_id "
      . " WHERE a.element_status = 1"
      . ($this->formId ? " AND a.form_id = " . $this->formId : '')
      . " AND a.element_type = 'file'"
      . " ORDER BY a.element_position ASC ";

    $this->form_name = $request->getParameter('id') ?
      $conn->fetchAssoc("SELECT form_name FROM ap_forms WHERE form_id = " . $request->getParameter('id') . ' LIMIT 1')[0]['form_name'] : null;

    $already_added = [];
    $this->elements = array_filter($conn->fetchAssoc($q), function ($k) use (&$already_added) {
      if (!in_array($k['title'], $already_added)) {
        array_push($already_added, $k['title']);
        return true;
      }

      return false;
    });
  }


  function executeAddsignableattachment(sfWebRequest $request)
  {
    $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    $form_id = $request->getParameter('form');
    $element_id = $request->getParameter('element');
    $this->element = $conn->fetchAssoc("SELECT element_title as title FROM ap_form_elements WHERE element_id = " . $element_id . ' AND form_id = ' . $form_id . ' LIMIT 1')[0];

    $q = "SELECT CONCAT(strfirstname, ' ', strlastname) as fullname, nid as id FROM cf_user k ";
    $this->mf_users = $conn->fetchAssoc($q);
    $this->mf_groups = $conn->fetchAssoc("SELECT * FROM mf_guard_group");


    if ($request->getMethod() == 'POST') {
      $params = $request->getPostParameters() +
        [
          'form_id' => $request->getParameter('form'),
          'element_id' => $request->getParameter('element')
        ];

      # delete all first
      $conn->execute("DELETE FROM signable_attachments_fields WHERE form_id = $form_id AND element_id = $element_id");

      # add all
      $creator_id = $this->getUser()->getAttributeHolder()->get('userid');

      foreach ($params['user_ids'] as $user_id) {
        $q = "INSERT INTO signable_attachments_fields (form_id,element_id,user_id,created_by) "
          . " VALUES (" . $params['form_id'] . "," . $params['element_id'] . "," . $user_id . "," . $creator_id . " )";
        $conn->execute($q);
      }

      foreach ($params['group_ids'] as $group_id) {
        $q = "INSERT INTO signable_attachments_fields (form_id,element_id,group_id,created_by) "
          . " VALUES (" . $params['form_id'] . "," . $params['element_id'] . "," . $group_id . "," . $creator_id . " )";
        $conn->execute($q);
      }


      $this->redirect("/backend.php/forms/signableattachments?id=$form_id");
    }


    $selected_all = $conn->fetchAssoc("SELECT * FROM signable_attachments_fields WHERE form_id = $form_id AND element_id = $element_id");
    $this->selected_group_ids = array_map(function ($j) {
      return $j['group_id'];
    }, array_filter($selected_all, function ($k) {
      return $k['group_id'] != null;
    }));

    $this->selected_user_ids = array_map(function ($j) {
      return $j['user_id'];
    }, array_filter($selected_all, function ($k) {
      return $k['user_id'] != null;
    }));
  }
}
