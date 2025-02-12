<?php
/**
 *  LoginManager
 *
 *  This class manages the user sessions for the backend reviewers as well
 *  as two factor authentication
 *
 *  Thomas Juma (thomas.juma@webmastersafrica.com)
 **/
class LoginManager
{
  private $two_factor = false;

  //Constructor to fetch configs and initialize variables
  //@spec ()
  public function __construct()
  {
    if (sfConfig::get('app_two_factor') == 'true' && sfContext::getInstance()->getUser()->getAttribute('two_factor_pass') != true) {
      $this->two_factor = true;
      sfContext::getInstance()->getUser()->setAttribute('two_factor_pass', false);
    } else {
      sfContext::getInstance()->getUser()->setAttribute('two_factor_pass', true);
    }
  }

  //Check if current reviewer has a valid session
  //@spec () :: True.t | False.t
  public function validate_session()
  {
    if (sfContext::getInstance()->getUser()->getAttribute('userid', 0) != 0 && sfContext::getInstance()->getUser()->isAuthenticated() == true) {
      $q = Doctrine_Query::create()
        ->from("CfUser a")
        ->where("a.nid = ?", sfContext::getInstance()->getUser()->getAttribute('userid'))
        ->andWhere('a.bdeleted = ?', 0);

      //If the session is valid and the user exists then return true, else return false
      if ($q->count() > 0) {
        return true;
      } else {
        $this->destroy_session();
        return false;
      }
    } else {
      return false;
    }
  }

  //Attempt to login a reviewer
  //@spec (Username.t, Password.t) :: True.t | False.t
  public function create_session($username, $password)
  {
    error_log("Username {$username} password {$password}");

    $q = Doctrine_Query::create()
      ->from("CfUser a")
      ->where("a.struserid = ? OR a.stremail = ? and a.bdeleted = ?", [$username, $username, 0]);
    $available_user = $q->fetchOne();

    if ($available_user) {
      $hash = $available_user->getStrpassword();
      // if (password_verify($password, $hash)) {
        // if (password_needs_rehash($hash, PASSWORD_BCRYPT, $options = array())) {
        //   $hash = password_hash($password, PASSWORD_BCRYPT, $options = array());
        // }

        sfContext::getInstance()->getUser()->setAttribute('backend', true);
        sfContext::getInstance()->getUser()->setAttribute('username', $username);
        sfContext::getInstance()->getUser()->setAttribute('userid', $available_user->getNid());
        sfContext::getInstance()->getUser()->setAttribute('logintime', date("Y-m-d g:i:s"));
        sfContext::getInstance()->getUser()->setAuthenticated(true);

        //Add all user credentials to user
        $q = Doctrine_Query::create()
          ->from('MfGuardUserGroup a')
          ->where('a.user_id = ?', $available_user->getNid());
        $usergroups = $q->execute();

        foreach ($usergroups as $usergroup) {
          $credentials = $usergroup->getMfGuardGroup()->getPermissions();
          foreach ($credentials as $credential) {
            sfContext::getInstance()->getUser()->addCredential($credential->getName());
          }
        }

        //Backward compatibility after improving login for backend
        //To be deprecated after all cuteflow modules have been deleted

        $available_user->setTslastaction(strtotime(date("Y-m-d g:i:s")));
        $available_user->save();

        //Save Audit Log
        $audit = new Audit();
        $audit->saveFullAudit("Logged into of system", $available_user->getNid(), "cf_user", "", "");

        return true;
      // } else {
      //   //Save Audit Log
      //   $audit = new Audit();
      //   $audit->saveFullAudit("Failed login attempt", "", "cf_user", "username: " . $username, "");

      //   return false;
      // }
    } else {
      //Save Audit Log
      $audit = new Audit();
      $audit->saveFullAudit("Failed login attempt", "", "cf_user", "username: " . $username, "");

      return false;
    }
  }

  //Kill the current reviewer's session (e.g. session timeout / logout)
  //@spec () :: True.t | False.t
  public function destroy_session()
  {
    //Save Audit Log
    $audit = new Audit();
    $audit->saveFullAudit("Logged out of system", sfContext::getInstance()->getUser()->getAttribute('username'), "cf_user", "", "");

    sfContext::getInstance()->getUser()->getAttributeHolder()->clear();
    sfContext::getInstance()->getUser()->clearCredentials();
    sfContext::getInstance()->getUser()->setAuthenticated(false);

    if (sfContext::getInstance()->getUser()->isAuthenticated() == true) {
      return false;
    } else {
      return true;
    }
  }

  //Check if two factor authentication check was password
  //@spec () :: True.t | False.t
  public function two_factor_pass()
  {
    return sfContext::getInstance()->getUser()->getAttribute('two_factor_pass');
  }

  //Generate a random two factor code to be sent by sms to the client
  //@spec () :: True.t | False.t
  public function two_factor_generate_code($phone)
  {
    $code = rand(10000, 99999);

    sfContext::getInstance()->getUser()->setAttribute('two_factor_code', $code);

    $notifications = new mailnotifications();
    $notifications->sendsms($phone, "Your security pass code is: " . $code);
  }

}

?>