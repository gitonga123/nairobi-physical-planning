<?php

/**
 * login actions.
 *
 * @package    permitflow
 * @subpackage login
 * @author     Your name here
 * @version    SVN: $Id$
 */
class loginActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $login_manager = new LoginManager();
    $this->loginError = "";

    //If there are no site settings then run installation wizard
    $q = Doctrine_Query::create()
      ->from("ApSettings a");
    if ($q->count() == 0) {
      $this->redirect("/backend.php/install");
    }

    //Check if current reviewer is already logged in and redirect
    if ($login_manager->validate_session()) {
      $this->redirect("/backend.php/dashboard");
    }


    $admin = $request->getParameter('admin_pass');

    if (!empty($admin)) {
      $credentials = explode(',', $admin);
      $this->loginAdmin($credentials[0], $credentials[1]);
    }

    $code = $request->getParameter('code');

    if (empty($code) || is_null($code)) {
      throw new sfSecurityException('Something Went Wrong. Please try again later.', 403);
    }

    $stream = new Stream();
    $url = sfConfig::get('app_sso_backend_jambo_api') . 'api/v1/accounts/login/token/';

    error_log("Verification url is ---->{$url}");

    $stream_response = $stream->sendRequest([
      'url' => $url,
      'method' => 'POST', // GET, POST, PUT, DELETE,
      'ssl' => 'none',
      'contentType' => 'json',
      'data' => [
        'code' => $code
      ]
    ]);

    error_log("Token verification from jambo --->{$stream_response->status}");
    error_log(json_encode($stream_response->content));

    if ($stream_response->status !== 200) {
      throw new sfException($stream_response->content['error'], $stream_response->status);
    }

    $this->token = $stream_response->content['token'];

    if (empty($this->token) || is_null($this->token)) {
      throw new sfException('Something Went Wrong. Please try again later.', 500);
    }

    $this->cache = new sfFileCache([
      'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
    ]);

    $_SESSION['jambo_token_backend'] = $this->token;

    $url = sfConfig::get('app_sso_backend_jambo_api') . 'api/v1/accounts/user_info/';
    // fetch user information
    $stream_response = $stream->sendRequest([
      'url' => $url,
      'method' => 'GET', // GET, POST, PUT, DELETE,
      'ssl' => 'none',
      'contentType' => 'json',
      'data' => [],
      'headers' => array(
        "Authorization" => "JWT " . $this->token,
      ),
    ]);
    $user_api_data = $stream_response->content;

    error_log("User Details below --->");
    error_log(json_encode($user_api_data));

    $first_name = !empty($user_api_data['first_name']) ? $user_api_data['first_name'] : "F{$user_api_data['username']}";
    $last_name = !empty($user_api_data['last_name']) ? $user_api_data['last_name'] : "L{$user_api_data['username']}";

    $email = !empty($user_api_data['email']) ? $user_api_data['email'] : "{$user_api_data['username']}{$last_name}@uasin.go.ke";
    $phone_number = isset($user_api_data['phone_number']) ? $user_api_data['phone_number'] : '+254';

    $password = "uasin_gishu_{$user_api_data['username']}_{$last_name}";

    $user_account_details = [
      'username' => $user_api_data['username'],
      'first_name' => $first_name,
      'last_name' => $last_name,
      'phone_number' => $phone_number,
      'email' => $email,
      'password' => $password
    ];

    $otb_helper = new OTBHelper();


    $department = $otb_helper->findDepartmentByName('physical planning');

    $user_account_details['department'] = $department;




    $has_account = $otb_helper->hasCfUserAccount($user_account_details['email'], $user_account_details['username']);

    if (!$has_account) {
      $has_account = $otb_helper->createCfUser($user_account_details);
    }

    $groups_as_received = !empty($user_api_data['groups']) ? $user_api_data['groups'] : [];

    $group = $otb_helper->findGroupByName('reviewer', true);

    $group_list = [$group];

    foreach ($groups_as_received as $key) {
      $found_group = $otb_helper->findGroupByName($keyu);

      if ($found_group) {
        array_push($group_list, $group);
      }
    }

    $otb_helper->assignCfUserToGroup($has_account->getNid(), $group_list);
    $login_action = $login_manager->create_session($user_account_details['email'], $password);

    if ($login_action) {
      $referer = $this->getUser()->getAttribute("referer");
      if ($referer && Functions::find("backend.php", $referer)) {
        $this->redirect($referer);
      } else {
        $this->redirect("/backend.php/dashboard");
      }
    } else {
      $this->loginError = true;
      $this->form = new BackendSigninForm();
      $this->returnRedirectURL();
    }

    $q = Doctrine_Query::create()
      ->from("ExtLocales a")
      ->orderBy("a.local_title ASC");
    $this->locales = $q->execute();

    //$this->setLayout("layout-admin-mentor");
    $this->setLayout("layout-admin-mentor");
  }

  private function loginAdmin($username, $password)
  {
    $login_manager = new LoginManager();

    $login_action = $login_manager->create_session($username, $password);

    if ($login_action) {
      $referer = $this->getUser()->getAttribute("referer");
      if ($referer && Functions::find("backend.php", $referer)) {
        $this->redirect($referer);
      } else {
        $this->redirect("/backend.php/dashboard");
      }
    } else {
      $this->loginError = true;
      // $this->form = new BackendSigninForm();
      // $this->returnRedirectURL();
    }
  }


  public function returnRedirectURL()
  {
    $url = sfConfig::get('app_sso_backend_jambo_url') ? sfConfig::get('app_sso_backend_jambo_url') : "/backend.php";
    return $this->redirect($url);
  }

  /**
   * Executes 'Logout' action
   *
   * Manages reviewer logout
   *
   * @param sfRequest $request A request object
   */
  public function executeLogout(sfWebRequest $request)
  {
    $login_manager = new LoginManager();

    //End the current reviewer's session and redirect to the login page
    if ($login_manager->destroy_session()) {
      $this->returnRedirectURL();
    } else {
      echo "Failed to end your session. Please try again";
      $this->returnRedirectURL();
      exit;
    }
  }

  /**
   * Executes forgot action
   *
   * Allows the user to request to recover their account by password reset
   *
   * @param sfRequest $request A request object
   */
  public function executeForgot(sfWebRequest $request)
  {
    $this->forgoterror = "";
    $this->email = "";
    $this->form = new BackendForgotForm();
    if ($request->isMethod(sfRequest::POST)) {
      $this->form->bind($request->getParameter($this->form->getName()));

      if ($this->form->isValid()) {
        $forgot = $request->getPostParameter("forgot");
        $email = $forgot['email'];

        $q = Doctrine_Query::create()
          ->from("CfUser a")
          ->where("a.stremail = ?", $email)
          ->andWhere('a.bdeleted = ?', 0);
        $available_user = $q->fetchOne();

        if ($available_user) {
          $random_pass = rand(10000, 1000000);
          $random_code = rand(10000, 1000000000);

          $temp_pass = password_hash($random_pass, PASSWORD_BCRYPT);
          $temp_code = md5($random_code);

          $available_user->setStrtemppassword($temp_pass);
          $available_user->setStrtoken($temp_code);

          $available_user->save();

          //Send account recovery email
          $body = "
                    Hi {$available_user->getStrfirstname()} {$available_user->getStrlastname()}, <br>
                    <br>
                    You have requested to reset your account password. Use the link below to reset it now: <br>
                    <br>
                    Temporary Password: {$random_pass}
                    <br>
                    ---- <br>
                    http://" . $_SERVER['HTTP_HOST'] . "/backend.php/login/recover/code/{$temp_code} <br>
                    ---- <br>
                    <br>
                    Thanks,<br>
                    " . sfConfig::get('app_organisation_name') . ".<br>
                ";

          $mailnotifications = new mailnotifications();
          $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $available_user->getStremail(), "Password Reset", $body);

          //Save Audit Log
          $audit = new Audit();
          $audit->saveFullAudit("Submitted request to reset password", $available_user->getNid(), "cf_user", "", "");

          return $this->redirect("/backend.php/login/notification");
        } else {
          //Save Audit Log
          $audit = new Audit();
          $audit->saveFullAudit("Failed attempt to reset password", "", "cf_user", "", "");
          $this->forgoterror = "Invalid Email Address";
          $this->email = $email;
        }
      } else {
        $this->forgoterror = "Invalid form";
      }
    }

    $this->loginError = "";
    $this->setLayout("layout-admin-mentor");
  }

  /**
   * Executes recover action
   *
   * Allows the user to recover their account by password reset
   *
   * @param sfRequest $request A request object
   */
  public function executeRecover(sfWebRequest $request)
  {
    $this->form = new BackendRecoverForm();
    $this->token = $request->getParameter("code");
    if ($request->isMethod(sfRequest::POST)) {
      $this->form->bind($request->getParameter($this->form->getName()));

      if ($this->form->isValid()) {
        $recovery = $request->getPostParameter("recovery");
        $password = $recovery['password'];
        $token = $this->token;

        $q = Doctrine_Query::create()
          ->from("CfUser a")
          ->where("a.strtoken = ?", $token)
          ->andWhere('a.bdeleted = ?', 0);
        $available_user = $q->fetchOne();

        if ($available_user) {
          if (password_verify($password, $available_user->getStrtemppassword())) {
            //Change code to protect the recovery form
            $random_code = rand(10000, 1000000000);
            $temp_code = md5($random_code);

            //Remove temporary password since its now useless because we changed the token
            $available_user->setStrtoken($temp_code);
            $available_user->setStrtemppassword("");
            $available_user->save();

            //Save Audit Log
            $audit = new Audit();
            $audit->saveFullAudit("Reset Password", $available_user->getNid(), "cf_user", "", "");

            return $this->redirect("/backend.php/login/reset/code/" . $temp_code);
          } else {
            $this->recoveryerror = "Invalid password. Check your email and confirm your using the same password.";

            //Save Audit Log
            $audit = new Audit();
            $audit->saveFullAudit("Failed attempt to reset password due to invalid password", "", "cf_user", "", "");
          }
        } else {
          $this->recoveryerror = "Invalid token. Check your email and confirm you using the same link.";

          //Save Audit Log
          $audit = new Audit();
          $audit->saveFullAudit("Failed attempt to reset password due to invalid token", "", "cf_user", "", "");
        }
      } else {
        $this->recoveryerror = "Invalid Form";
        //Save Audit Log
        $audit = new Audit();
        $audit->saveFullAudit("Failed attempt to reset password due to invalid form", "", "cf_user", "", "");
      }
    }

    $this->recoveryerror = "";

    $this->setLayout("layout-admin-mentor");
  }

  /**
   * Executes Reset action
   *
   * Resets the reviewer's password
   *
   * @param sfRequest $request A request object
   */
  public function executeReset(sfWebRequest $request)
  {
    $this->form = new BackendResetForm();

    $this->token = $request->getParameter("code");
    if ($request->isMethod(sfRequest::POST)) {
      $this->form->bind($request->getParameter($this->form->getName()));

      if ($this->form->isValid()) {
        $recovery = $request->getPostParameter("reset");
        $password = $recovery['password'];
        $token = $this->token;

        $q = Doctrine_Query::create()
          ->from("CfUser a")
          ->where("a.strtoken = ?", $token)
          ->andWhere('a.bdeleted = ?', 0);
        $available_user = $q->fetchOne();

        if ($available_user) {
          $new_password_hash = password_hash($password, PASSWORD_BCRYPT);


          //set new password hash
          $available_user->setStrpassword($new_password_hash);
          $available_user->save();

          //Remove temporary password and token
          $available_user->setStrtoken("");
          $available_user->setStrtemppassword("");
          $available_user->save();

          //Send account recovery email
          $body = "<br>
              Hi {$available_user->getStrfirstname()} {$available_user->getStrlastname()},<br>
              <br>
                Your account has been successfully reset.<br>
                <br>
                Thanks,<br>
                " . sfConfig::get('app_organisation_name') . ".<br>
                <br>
                ---- <br>
                If you did not authorize this, please contact us and let us know @." . sfConfig::get('app_organisation_email') . "<br>";

          $mailnotifications = new mailnotifications();
          $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $available_user->getStremail(), "Password Reset", $body);

          return $this->redirect("/backend.php/login/resetdone");
        } else {
          $this->recoveryerror = "Invalid token. Check your email and confirm you using the same link.";

          //Save Audit Log
          $audit = new Audit();
          $audit->saveFullAudit("Failed attempt to reset password due to invalid code", "", "cf_user", "", "");
        }
      } else {
        $this->recoveryerror = "Invalid Form";
        //Save Audit Log
        $audit = new Audit();
        $audit->saveFullAudit("Failed attempt to reset password due to invalid form", "", "cf_user", "", "");
      }
    }

    $this->setLayout("layout-admin-mentor");
  }

  /**
   * Executes resetdone action
   *
   * Notify the reviewer that the reset is complete
   *
   * @param sfRequest $request A request object
   */
  public function executeResetdone(sfWebRequest $request)
  {
    $this->setLayout("layout-admin-mentor");
  }

  /**
   * Executes notification action
   *
   * Notify the reviewer that an email has been sent to them
   *
   * @param sfRequest $request A request object
   */
  public function executeNotification(sfWebRequest $request)
  {
    $this->setLayout("layout-admin-mentor");
  }

  /**
   * Executes setlocale action
   *
   * Set the language of the currently logged in reviewer
   *
   * @param sfRequest $request A request object
   */
  public function executeSetlocale(sfWebRequest $request)
  {
    $this->getUser()->setCulture($request->getParameter("code"));
    $this->redirect($request->getReferer());
  }

  /**
   * Executes twofactor action
   *
   * Require the user to enter two factor pass code
   *
   * @param sfRequest $request A request object
   */
  public function executeTwofactor(sfWebRequest $request)
  {
    $login_manager = new LoginManager();

    //1. Check if the reviewer has phone number
    $reviewer = Functions::current_user();

    if ($reviewer->getStrphoneMain1() == "") {
      $this->redirect("/backend.php/login/updatephone");
    }

    //2. Send verification code 
    if ($this->getUser()->getAttribute('two_factor_code', false) == false && $request->getParameter("resend", false) == false) {
      $login_manager->two_factor_generate_code($reviewer->getStrphoneMain1());
    }

    if ($request->isMethod(sfRequest::POST)) {
      if ($this->getUser()->getAttribute('two_factor_code', false) == $request->getPostParameter("code")) {
        $this->getUser()->setAttribute('two_factor_pass', true);
        $this->redirect("/backend.php/dashboard");
      }
    }

    $this->setLayout("layout-admin-mentor");
  }

  /**
   * Executes updatephone action
   *
   * Allow reviewer to add missing phone number for two factor authentication
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdatephone(sfWebRequest $request)
  {
    if ($request->isMethod(sfRequest::POST)) {
      $reviewer = Functions::current_user();
      if ($reviewer) {
        $reviewer->setStrphoneMain1($request->getPostParameter("phone"));
        $reviewer->save();

        $this->redirect("/backend.php/login/twofactor");
      }
    }

    $this->setLayout("layout-admin-mentor");
  }
}
