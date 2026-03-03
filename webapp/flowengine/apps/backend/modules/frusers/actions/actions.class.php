<?php

/**
 * Frusers actions.
 *
 * Client Management Service
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class frusersActions extends sfActions
{
  /**
   * Executes 'Recover' action
   *
   * Helps recover an application for a user
   *
   * @param sfRequest $request A request object
   */
  public function executeRecover(sfWebRequest $request)
  {
    // store id in session
    $step = $request->getPostParameter("step");

    if ($request->getParameter("id")) {
      $this->user_id = $request->getParameter("id");
    }

    if ($step == "") {
      $step = 1;
    }

    if ($step == 1) {
      //The reviewer will type the reference number here
      $this->step = 1;
    } elseif ($step == 2) {
      //The results of the recovery process will be displayed

      $reference_number = $request->getPostParameter("reference_number");

      //Extract the form id and entry id from the reference number
      $reference_array = explode("/", $reference_number);

      $form_id = $reference_array[0];
      $entry_id = $reference_array[1];

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

      $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = " . $entry_id;
      $sth = mf_do_query($sql, array(), $dbh);

      if ($sth === false) {
        $this->found_entry = false;
      } else {
        $this->found_entry = true;
      }

      if ($this->found_entry) {
        //Check if an entry already exists, if none then send to template and attempt to generate the application and invoice
        $q = Doctrine_Query::create()
          ->from("FormEntry a")
          ->where("a.form_id = ?", $form_id)
          ->andWhere("a.entry_id = ?", $entry_id);
        $application = $q->fetchOne();

        if ($application) {
          $this->existing_application = true;
          $this->existing_application_id = $application->getApplicationId();
          $this->user_id = $request->getParameter("id");
          $this->step = 1;
        } else {
          $this->existing_application = false;
          $this->form_id = $form_id;
          $this->entry_id = $entry_id;
          $this->user_id = $request->getParameter("id");
          $this->step = 2;
        }
      } else {
        $this->found_entry_error = true;
        $this->user_id = $request->getParameter("id");
        $this->step = 1;
      }
    }
  }

  /**
   * Executes 'Checkuser' action
   *
   * Ajax used to check existence of username
   *
   * @param sfRequest $request A request object
   */
  public function executeCheckuser(sfWebRequest $request)
  {
    // add new user
    $q = Doctrine_Query::create()
      ->from("sfGuardUser a")
      ->where('a.username = ?', $request->getPostParameter('name'));
    $existinguser = $q->execute();
    if (sizeof($existinguser) > 0) {
      echo '<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Username is already in use!</strong></div><script language="javascript">document.getElementById("submitbuttonname").disabled = true;</script>';
      exit;
    } else {
      echo '<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Username is available!</strong></div><script language="javascript">document.getElementById("submitbuttonname").disabled = false;</script>';
      exit;
    }
  }
  /**
   * Executes 'Checkemail' action
   *
   * Ajax used to check existence of email
   *
   * @param sfRequest $request A request object
   */
  public function executeCheckemail(sfWebRequest $request)
  {
    // add new user
    $q = Doctrine_Query::create()
      ->from("sfGuardUserProfile a")
      ->where('a.email = ?', $request->getPostParameter('email'));
    $existinguser = $q->execute();
    if (sizeof($existinguser) > 0) {
      echo '<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Email is already in use!</strong></div><script language="javascript">document.getElementById("submitbuttonname").disabled = true;</script>';
      exit;
    } else {
      echo '<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Email is available!</strong></div><script language="javascript">document.getElementById("submitbuttonname").disabled = false;</script>';
      exit;
    }
  }

  public function executeBatch(sfWebRequest $request)
  {
    if ($request->getPostParameter('delete')) {
      $item = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getPostParameter('delete')));
      if ($item) {
        $q = Doctrine_Query::create()
          ->from('FormEntry a')
          ->where('a.user_id = ?', $item->getId());
        $applications = $q->execute();
        if (sizeof($applications) <= 0) {
          $item->delete();
        }
      }
    }
    if ($request->getPostParameter('activate')) {
      $item = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getPostParameter('activate')));
      if ($item) {
        $item->setIsActive('1');
        $item->save();
      }
    }
    if ($request->getPostParameter('deactivate')) {
      $item = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getPostParameter('deactivate')));
      if ($item) {
        $item->setIsActive('0');
        $item->save();
      }
    }
  }


  /**
   * Executes 'Sendnotification' action
   *
   * Allows sending of notifications to selected clients
   *
   * @param sfRequest $request A request object
   */
  public function executeSendnotification(sfWebRequest $request) {}

  /**
   * Executes 'Notificationmail' action
   *
   * Sends mail from 'Sendnotification' template form
   *
   * @param sfRequest $request A request object
   */
  public function executeNotificationmail(sfWebRequest $request)
  {
    $notificationmng = new mailnotifications();


    if ($request->getPostParameter("emails")) {
      $emails = $request->getPostParameter("emails");
      foreach ($emails as $email) {
        $notificationmng->sendemail("One Stop Center", $email, $request->getPostParameter("subject"), $request->getPostParameter("mail"));
      }
    }


    $this->redirect('/plan/frusers/index');
  }


  /**
   * Executes 'Activate' action
   *
   * Activate user accounts
   *
   * @param sfRequest $request A request object
   */
  public function executeActivate(sfWebRequest $request)
  {
    $user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($user) {
      $user->setIsActive("1");
      $user->setIsSuperAdmin("1");
      $user->save();
      $audit = new Audit();
      $audit->saveAudit(0, "Activated a user: " . $user->getUsername());
    }

    $this->getUser()->setFlash('notice', 'Successfully activated a user');
    $this->redirect($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());
  }

  /**
   * Executes 'Deactivate' action
   *
   * Deactivate user accounts
   *
   * @param sfRequest $request A request object
   */
  public function executeDeactivate(sfWebRequest $request)
  {
    $user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($user) {
      $user->setIsActive("0");
      $user->setIsSuperAdmin("0");
      $user->save();
      $audit = new Audit();
      $audit->saveAudit(0, "Deactivated a user: " . $user->getUsername());
    }

    $this->getUser()->setFlash('notice', 'Successfully deactivated a user');
    $this->redirect($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());
  }




  /**
   * Executes 'Activate' action
   *
   * Activate user accounts
   *
   * @param sfRequest $request A request object
   */
  public function executeValidate(sfWebRequest $request)
  {
    $user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($user) {
      $user->setIsSuperAdmin("1");
      $user->save();
      $audit = new Audit();
      $audit->saveAudit(0, "Activated a user: " . $user->getUsername());
    }

    $this->getUser()->setFlash('notice', 'Successfully validated a user');
    $this->redirect($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());
  }

  /**
   * Executes 'Deactivate' action
   *
   * Deactivate user accounts
   *
   * @param sfRequest $request A request object
   */
  public function executeUnvalidate(sfWebRequest $request)
  {
    $user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($user) {
      $user->setIsSuperAdmin("0");
      $user->save();
      $audit = new Audit();
      $audit->saveAudit(0, "Deactivated a user: " . $user->getUsername());
    }

    $this->getUser()->setFlash('notice', 'Successfully unvalidated a user');
    $this->redirect($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());
  }

  /**
   * Executes 'Index' action
   *
   * Displays list of all registered clients
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    if ($request->getParameter("done")) {
      $this->done = 1;
    }

    $this->filter = "";
    $this->filterstatus = 1;
    
    if ($request->getPostParameter("search") || $request->getParameter('filter')) {
      if ($request->getPostParameter("search")) {
        $this->filter = $request->getPostParameter("search");
      } else {
        $this->filter = $request->getParameter("filter");
      }

      if ($request->getParameter('filterstatus') != "") {
        $this->filterstatus = $request->getParameter('filterstatus');
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->leftJoin('a.Profile b')
          ->where('a.id = b.user_id')
          ->andWhere('b.fullname LIKE ? OR a.username LIKE ? OR b.email  LIKE ?', array('%' . $this->filter . '%', '%' . $this->filter . '%', '%' . $this->filter . '%'))
          ->andWhere('a.is_active = ?', $request->getParameter('filterstatus'))
          ->orderBy('a.created_at DESC');
        $this->pager = new sfDoctrinePager('sfGuardUser', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
      } else {
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->leftJoin('a.Profile b')
          ->where('a.id = b.user_id')
          ->andWhere('b.fullname LIKE ? OR a.username LIKE ? OR b.email  LIKE ?', array('%' . $this->filter . '%', '%' . $this->filter . '%', '%' . $this->filter . '%'))
          ->orderBy('b.fullname ASC');
        $this->pager = new sfDoctrinePager('sfGuardUser', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
      }
    } else {
      if ($request->getParameter('filterstatus') != "") {
        $this->filterstatus = $request->getParameter('filterstatus');
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->leftJoin('a.Profile b')
          ->where('a.id = b.user_id')
          ->andWhere('a.is_active = ?', $request->getParameter('filterstatus'))
          ->orderBy('a.created_at DESC');
        $this->pager = new sfDoctrinePager('sfGuardUser', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
      } else {
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->leftJoin('a.Profile b')
          ->andWhere('a.is_active = ?', $this->filterstatus)
          ->orderBy('b.fullname ASC');
        $this->pager = new sfDoctrinePager('sfGuardUser', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
      }
    }

    $this->setLayout('layout');
  }



  /**
   * Executes 'Index' action
   *
   * Displays list of all registered clients
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingsindex(sfWebRequest $request)
  {
    if ($request->getParameter("done")) {
      $this->done = 1;
    }

    if ($request->getParameter("act") == "0") {
      $q = Doctrine_Query::create()
        ->from('sfGuardUser');
      $users = $q->execute();
      foreach ($users as $user) {
        $user->setIsActive("0");
        $user->save();
        $audit = new Audit();
        $audit->saveAudit(0, "<a href=\"/plan/frusers/show?id=" . $user->getId() . "&language=en\">deactivated a user</a>");
      }
    } else if ($request->getParameter("act") == "1") {
      $q = Doctrine_Query::create()
        ->from('sfGuardUser');
      $users = $q->execute();
      foreach ($users as $user) {
        $user->setIsActive("1");
        $user->save();
        $audit = new Audit();
        $audit->saveAudit(0, "<a href=\"/plan/frusers/show?id=" . $user->getId() . "&language=en\">activated a user</a>");
      }
    }


    if ($request->getParameter("atoggle")) {
      $content = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('atoggle')));
      if ($content->getIsActive() == "1") {
        $content->setIsActive("0");
        $to = $content->getProfile()->getEmail();
        $subject = "Account Deactivation";
        $message = "Your account is now deactivated. You will not be able to login to the system.";
        $from = "info@webmastersafrica.net";
        $headers = "From:" . "AdminCP";
        $notification = new mailnotifications();
        try {
          $notification->sendemail('', $to, $subject, $message);
          //mail($to,$subject,$message,$headers);
        } catch (Exception $ex) {
          echo "Could not send mail";
        }

        $audit = new Audit();
        $audit->saveAudit(0, "<a href=\"/plan/frusers/show?id=" . $content->getId() . "&language=en\">deactivated a user</a>");
      } else {
        $content->setIsActive("1");
        $to = $content->getProfile()->getEmail();
        $subject = "Account Activation";
        $message = "Your account is now active. You may login to the system.";
        $from = "info@webmastersafrica.net";
        $headers = "From:" . "AdminCP";
        try {
          mail($to, $subject, $message, $headers);
        } catch (Exception $ex) {
          echo "Could not send mail";
        }
        $audit = new Audit();
        $audit->saveAudit(0, "<a href=\"/plan/frusers/show?id=" . $content->getId() . "&language=en\">activated a user</a>");
      }
      $content->save();
    }

    if ($request->getParameter("stoggle")) {
      $content = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('stoggle')));
      if ($content->getIsSuperAdmin() == "1") {
        $content->setIsSuperAdmin("0");
        $content->save();
        $audit = new Audit();
        $audit->saveAudit(0, "<a href=\"/plan/frusers/show?id=" . $content->getId() . "&language=en\">unvalidated a user account</a>");
      } else {
        $content->setIsSuperAdmin("1");
        $content->save();
        $audit = new Audit();
        $audit->saveAudit(0, "<a href=\"/plan/frusers/show?id=" . $content->getId() . "&language=en\">validated a user account</a>");
      }
    }

    $page = $request->getParameter('page');

    if ($request->getParameter("filter")) {
      if ($request->getParameter("filter") == "active") {
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.is_active = ?', 1)
          ->orderBy('a.id DESC');
        $pager = new sfDoctrinePager('ReviewerComments', 10);
        $pager->setQuery($q);
        $pager->setPage($request->getParameter('page', $page));
        $pager->init();

        $this->pager = $pager;
      } else {
        $q = Doctrine_Query::create()
          ->from('sfGuardUser a')
          ->where('a.is_active = ?', 0)
          ->orderBy('a.id DESC');
        $pager = new sfDoctrinePager('ReviewerComments', 10);
        $pager->setQuery($q);
        $pager->setPage($request->getParameter('page', $page));
        $pager->init();

        $this->pager = $pager;
      }
    } else {
      $q = Doctrine_Query::create()
        ->from('sfGuardUser a')
        ->orderBy('a.id DESC');
      $pager = new sfDoctrinePager('sfGuardUser', 10);
      $pager->setQuery($q);
      $pager->setPage($page);
      $pager->init();

      $this->pager = $pager;
    }

    $this->setLayout(false);
  }

  /**
   * Executes 'Show' action
   *
   * Displays full client details
   *
   * @param sfRequest $request A request object
   */
  public function executeShow(sfWebRequest $request)
  {
    $this->user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));

    if ($request->getParameter("remove")) {
      $q = Doctrine_Query::create()
        ->from("FormEntry a")
        ->where("a.id = ?", $request->getParameter("remove"))
        ->andWhere("a.user_id = ?", $request->getParameter("id"))
        ->andWhere("a.approved = 0")
        ->orderBy('a.id DESC');
      $application = $q->fetchOne();

      if ($application) {
        $application->delete();
      }
    }

    //Get list of shared businesses user is allowed to access
    $q = Doctrine_Query::create()
      ->from("MfUserProfileShare a")
      ->where("a.user_id = ?", $this->user->getId());
    $shared_businesses = $q->execute();

    $array_shared_businesses = array();

    $array_shared_businesses[] = 0;

    foreach ($shared_businesses as $business) {
      $array_shared_businesses[] = $business->getProfileId();
    }

    $q = null;

    if (sizeof($shared_businesses) > 0) {
      $list_allowed_businesses = implode(" OR a.id = ", $array_shared_businesses);

      //Display list of latest applications
      $q = Doctrine_Query::create()
        ->from("MfUserProfile a")
        ->where("a.user_id = ?", $this->user->getId())
        ->orWhere("a.id = " . $list_allowed_businesses)
        ->orderBy("a.created_at DESC");
    } else {
      //Display list of latest applications
      $q = Doctrine_Query::create()
        ->from("MfUserProfile a")
        ->where("a.user_id = ?", $this->user->getId())
        ->orderBy("a.created_at DESC");
    }

    $this->businesses = new sfDoctrinePager('MfUserProfile', 5);
    $this->businesses->setQuery($q);
    $this->businesses->setPage($request->getParameter('bpage', 1));
    $this->businesses->init();

    $this->forward404Unless($this->user);
    $this->setLayout('layout');
  }


  /**
   * Executes 'newForm' action
   *
   * Creates new client form object
   *
   * @param sfRequest $request A request object
   */
  protected function newForm($className, $object = null)
  {
    $key = "app_sfApplyPlugin_$className" . "_class";
    $class = sfConfig::get(
      $key,
      $className
    );
    if ($object !== null) {
      return new $class($object);
    }
    return new $class;
  }

  /**
   * Executes 'New' action
   *
   * Allows creation of a new client account
   *
   * @param sfRequest $request A request object
   */
  public function executeNew(sfWebRequest $request)
  {
    $this->form = $this->newForm('sfApplyApplyForm2');
    $this->setLayout('layout');
  }



  /**
   * Executes 'New' action
   *
   * Allows creation of a new client account
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingsnew(sfWebRequest $request)
  {
    $this->form = $this->newForm('sfApplyApplyForm2');
    $this->setLayout(false);
  }


  /**
   * Executes 'Adddetails' action
   *
   * Allows adding of additional details to a newly created client account
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingsadddetails(sfWebRequest $request)
  {
    $this->formid = $request->getParameter("formid");
    $this->userid = $request->getParameter("userid");
    $this->setLayout(false);
  }


  /**
   * Executes 'Adddetails' action
   *
   * Allows adding of additional details to a newly created client account
   *
   * @param sfRequest $request A request object
   */
  public function executeAdddetails(sfWebRequest $request)
  {
    $this->formid = $request->getParameter("formid");
    $this->userid = $request->getParameter("userid");
  }


  /**
   * Executes 'Delete' action
   *
   * Deletes an existing client's account
   *
   * @param sfRequest $request A request object
   */
  public function executeDelete(sfWebRequest $request)
  {
    $user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($user) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.user_id = ?', $user->getId());
      $applications = $q->execute();
      if (sizeof($applications) == 0) {
        $q = Doctrine_Query::create()
          ->from("SfGuardUserProfile a")
          ->where("a.user_id = ?", $user->getId());
        $profile = $q->fetchOne();
        if ($profile) {
          $profile->delete();
        }

        $q = Doctrine_Query::create()
          ->from("MfUserProfile a")
          ->where("a.user_id = ?", $user->getId());
        $profile = $q->fetchOne();
        if ($profile) {
          $profile->delete();
        }
        $user->delete();
      } else {
        echo "Cannot delete user with existing applications";
        exit;
      }
    }
    return true;
  }


  /**
   * Executes 'Delete' action
   *
   * Deletes an existing client's account
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingsdelete(sfWebRequest $request)
  {
    $user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($user) {
      $q = Doctrine_Query::create()
        ->from('FormEntry a')
        ->where('a.user_id = ?', $user->getId());
      $applications = $q->execute();
      if (sizeof($applications) == 0) {
        $q = Doctrine_Query::create()
          ->from("SfGuardUserProfile a")
          ->where("a.user_id = ?", $user->getId());
        $profile = $q->fetchOne();
        if ($profile) {
          $profile->delete();
        }

        $q = Doctrine_Query::create()
          ->from("MfUserProfile a")
          ->where("a.user_id = ?", $user->getId());
        $profile = $q->fetchOne();
        if ($profile) {
          $profile->delete();
        }
        $user->delete();
      }
    }
    $this->redirect('/plan/frusers/show/id/' . $request->getParameter('id'));
  }


  /**
   * Executes 'Create' action
   *
   * Saves new client information to database
   *
   * @param sfRequest $request A request object
   */
  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));
    if ($request->isMethod('post')) {
      $sf_guard_user = new SfGuardUser();
      $sf_guard_user->setUsername($request->getPostParameter("username"));
      $sf_guard_user->setPassword($request->getPostParameter("password"));
      $sf_guard_user->setIsActive("0");
      $sf_guard_user->setIsSuperAdmin("0");
      $sf_guard_user->save();

      $profile = new SfGuardUserProfile();
      $profile->setUserId($sf_guard_user->getId());
      $profile->setRegisteras($request->getPostParameter("registeras"));
      $profile->setEmail($request->getPostParameter("email"));
      $profile->setFullname($request->getPostParameter("full_name"));
      $profile->save();

      try {

        $this->sendVerificationMail($profile);
        //Redirect to additional user details form afterwards
        $user = $profile->getUser();
        $this->getUser()->setAttribute('new_user_id', $user->getId());
        $this->redirect('/plan/frusers/adddetails?formid=15');
      } catch (Exception $e) {
        //$mailer->disconnect();
        $profile = $this->form->getObject();
        $user = $profile->getUser();
        //$user->delete();
        // You could re-throw $e here if you want to
        // make it available for debugging purposes
        $this->getUser()->setFlash("notice", "Could not send verification email. Please try again.");
      }
    }
  }


  /**
   * Executes 'Create' action
   *
   * Saves new client information to database
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingscreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));
    if ($request->isMethod('post')) {
      $sf_guard_user = new SfGuardUser();
      $sf_guard_user->setUsername($request->getPostParameter("username"));
      $sf_guard_user->setPassword($request->getPostParameter("password"));
      $sf_guard_user->setIsActive("0");
      $sf_guard_user->setIsSuperAdmin("0");
      $sf_guard_user->save();

      $profile = new SfGuardUserProfile();
      $profile->setUserId($sf_guard_user->getId());
      $profile->setEmail($request->getPostParameter("email"));
      $profile->setRegisteras($request->getPostParameter("registeras"));
      $profile->setFullname($request->getPostParameter("full_name"));
      $profile->save();

      try {

        $this->sendVerificationMail($profile);
        //Redirect to additional user details form afterwards
        $user = $profile->getUser();
        $this->getUser()->setAttribute('new_user_id', $user->getId());
        $this->redirect('/plan/frusers/settingsadddetails?formid=15');
      } catch (Exception $e) {
        //$mailer->disconnect();
        $profile = $this->form->getObject();
        $user = $profile->getUser();
        //$user->delete();
        // You could re-throw $e here if you want to
        // make it available for debugging purposes
        $this->getUser()->setFlash("notice", "Could not send verification email. Please try again.");
      }
    }
  }

  /**
   * Executes 'Editadditional' action
   *
   * Allows editing of additional client details
   *
   * @param sfRequest $request A request object
   */
  public function executeEditadditional(sfWebRequest $request)
  {
    $this->formid = $request->getParameter('formid');
    $this->entryid = $request->getParameter('entryid');
  }

  /**
   * Executes 'Edit' action
   *
   * Allows editing of basic client details
   *
   * @param sfRequest $request A request object
   */
  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id'))), sprintf('Object sf_guard_user does not exist (%s).', $request->getParameter('id')));
    $this->form = new sfGuardUserForm($sf_guard_user);
    $this->setLayout('layout');
  }

  /**
   * Executes 'Edit' action
   *
   * Allows editing of basic client details
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingsedit(sfWebRequest $request)
  {
    $this->forward404Unless($sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id'))), sprintf('Object sf_guard_user does not exist (%s).', $request->getParameter('id')));
    $this->form = new sfGuardUserForm($sf_guard_user);
    $this->setLayout(false);
  }

  /**
   * Executes 'Update' action
   *
   * Saves updated client details to database
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id'))), sprintf('Object sf_guard_user does not exist (%s).', $request->getParameter('id')));
    $this->form = new sfGuardUserForm($sf_guard_user);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  /**
   * Executes 'ProcessForm' action
   *
   * Saves client details to database (Used by Create and Update actions)
   *
   * @param sfRequest $request A request object
   */
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($sf_guard_user) {
      $user_profile = $sf_guard_user->getProfile();
      $user_profile->setFullname($request->getPostParameter("sfApplyApply2[fullname]"));
      $user_profile->setEmail($request->getPostParameter("sfApplyApply2[email]"));
      $user_profile->setMobile($request->getPostParameter("sfApplyApply2[mobile]"));
      $user_profile->setRegisteras($request->getPostParameter("sfApplyApply2[registeras]"));
      $user_profile->save();

      $sf_guard_user->setUsername($request->getPostParameter("sfApplyApply2[username]"));
      $sf_guard_user->setIsActive($request->getPostParameter("sfApplyApply2[active]"));
      $sf_guard_user->setIsSuperAdmin($request->getPostParameter("sfApplyApply2[validated]"));
      if ($request->getPostParameter("sfApplyApply2[password]")) {
        $sf_guard_user->setPassword($request->getPostParameter("sfApplyApply2[password]"));
      }

      $sf_guard_user->save();

      $audit = new Audit();
      $audit->saveAudit(0, "<a href=\"/plan/frusers/show/id/" . $sf_guard_user->getId() . "\">Updated a user account</a>");
    }
  }


  /**
   * Executes 'Update' action
   *
   * Saves updated client details to database
   *
   * @param sfRequest $request A request object
   */
  public function executeSettingsupdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id'))), sprintf('Object sf_guard_user does not exist (%s).', $request->getParameter('id')));
    $this->form = new sfGuardUserForm($sf_guard_user);

    $this->processSettingsForm($request, $this->form);

    $this->setTemplate('edit');
  }


  /**
   * Executes 'ProcessForm' action
   *
   * Saves client details to database (Used by Create and Update actions)
   *
   * @param sfRequest $request A request object
   */
  protected function processSettingsForm(sfWebRequest $request, sfForm $form)
  {
    $sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
    if ($sf_guard_user) {
      $user_profile = $sf_guard_user->getProfile();
      $user_profile->setFullname($request->getPostParameter("sfApplyApply2[fullname]"));
      $user_profile->setEmail($request->getPostParameter("sfApplyApply2[email]"));
      $user_profile->setMobile($request->getPostParameter("sfApplyApply2[mobile]"));
      $user_profile->setRegisteras($request->getPostParameter("sfApplyApply2[registeras]"));
      $user_profile->save();

      $sf_guard_user->setUsername($request->getPostParameter("sfApplyApply2[username]"));
      $sf_guard_user->setIsActive($request->getPostParameter("sfApplyApply2[active]"));
      $sf_guard_user->setIsSuperAdmin($request->getPostParameter("sfApplyApply2[validated]"));
      if ($request->getPostParameter("sfApplyApply2[password]")) {
        $sf_guard_user->setPassword($request->getPostParameter("sfApplyApply2[password]"));
      }

      $sf_guard_user->save();

      $audit = new Audit();
      $audit->saveAudit(0, "<a href=\"/plan/frusers/show/id/" . $sf_guard_user->getId() . "\">Updated a user account</a>");

      $this->redirect('/plan/settings/security?load=registeredmembers');
    }
  }

  protected function sendVerificationMail($profile)
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      $is_http = "https";
    } else {
      $is_http = "http";
    }

    $to = $profile->getEmail();
    $subject = "Account Verification";
    $message = "Please verify your account on " . "{$is_http}://" . $this->getRequest()->getHost() . "/plan/sfApply/confirm/validate/" . $profile->getValidate();
    $headers = "";
    $headers .= "Reply-To: " . sfConfig::get('app_organisation_name') . " <" . sfConfig::get('app_organisation_email') . ">\r\n";
    $headers .= "Return-Path: " . sfConfig::get('app_organisation_name') . " <" . sfConfig::get('app_organisation_email') . ">\r\n";
    $headers .= "From: " . sfConfig::get('app_organisation_name') . " <" . sfConfig::get('app_organisation_email') . ">\r\n";
    $headers .= "Organization: " . sfConfig::get('app_organisation_name') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP" . phpversion() . "\r\n";
    $notification = new mailnotifications();
    $notification->sendemail('', $to, $subject, $message);
  }
}
