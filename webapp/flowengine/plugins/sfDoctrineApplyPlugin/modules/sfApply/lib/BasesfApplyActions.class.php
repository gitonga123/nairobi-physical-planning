<?php

/**
 * sfApply actions.
 *
 * @package    5seven5
 * @subpackage sfApply
 * @author     Tom Boutell, tom@punkave.com
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class BasesfApplyActions extends sfActions
{

  public function executeApply(sfRequest $request)
  {
    $this->setLayout('layoutmentorregister');
    $this->form = $this->newForm('sfApplyApplyForm');
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('sfApplyApply'), $request->getFiles($this->form->getName()));
      if ($this->form->isValid()) {
        $guid = "n" . self::createGuid();
        $this->form->setValidate($guid);
        $this->form->save();
        try {
          $profile = $this->form->getObject();

          $this->sendVerificationMail($profile);
          //Redirect to additional user details form afterwards
          $this->getUser()->setAttribute('new_user_id', $profile->getUserId());


          $q = Doctrine_Query::create()
            ->from("SfGuardUserCategories a")
            ->where("a.id = ?", $profile->getRegisteras());
          $cat = $q->fetchOne();


          $this->redirect('/plan/mfRegister/registerDetails?formid=' . $cat->getFormid());
        } catch (Exception $e) {
          //$mailer->disconnect();
          $profile = $this->form->getObject();
          $user = $profile->getUser();
          //$user->delete();
          // You could re-throw $e here if you want to
          // make it available for debugging purposes
          //$this->getUser()->setFlash("notice","Could not send verification email. Please try again.");
        }
      } else {
        #$this->redirect('/index.php');
        #$this->getUser()->setFlash("notice","Error. Invalid Security Token.");
      }
    }
  }


  public function executeApply2(sfRequest $request)
  {
    $this->setLayout("layout");
    $this->form = $this->newForm('sfApplyApplyForm2');
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('sfApplyApply2'));
      if ($this->form) {
        try {
          $guid = "n" . self::createGuid();
          $this->form->setValidate($guid);
          $this->form->save();

          $profile = $this->form->getObject();

          //$this->sendVerificationMail($profile);
          //Redirect to additional user details form afterwards
          $apply = $_POST['sfApplyApply2'];
          $user = $profile->getProfilita();
          $user->setIsActive(1);
          $user->setIsSuperAdmin(1);
          $user->save();
          //$this->getUser()->setAttribute('new_user_id',$user->getId());
          $this->redirect('/plan/frusers/show/id/' . $user->getId());
          exit;
        } catch (Exception $e) {
          error_log($e);
          //$mailer->disconnect();
          //$profile = $this->form->getObject();
          //$user = $profile->getUser();

          $error = "";

          foreach ($this->form->getErrorSchema()->getErrors() as $e) {
            $error .= $e->__toString() . ". ";
          }
          $this->getUser()->setFlash("notice", "Could not save the user. Try again." . html_entity_decode($error));
          $this->redirect('/plan/frusers/new');
          //$user->delete();
          // You could re-throw $e here if you want to
          // make it available for debugging purposes
          //$this->getUser()->setFlash("notice","Could not send verification email. Please try again.");
        }
      } else {
        $this->getUser()->setFlash("notice", "Error. Invalid Security Token.");
      }
    }
  }

  // Hate Zend_Mail? Override me
  protected function mail($options)
  {
    $required = array('subject', 'parameters', 'email', 'fullname', 'html', 'text');
    foreach ($required as $option) {
      if (!isset($options[$option])) {
        throw new sfException("Required option $option not supplied to sfApply::mail");
      }
    }
    $transport = $this->getMailTransport();
    $message = new Zend_Mail();
    $message->setSubject($options['subject']);

    // Render message parts
    $message->setBodyHtml($this->getPartial($options['html'], $options['parameters']), 'text/html');
    $message->setBodyText($this->getPartial($options['text'], $options['parameters']), 'text/plain');
    $address = $this->getFromAddress();
    $message->setFrom($address['email'], $address['fullname']);
    $message->addTo($options['email'], $options['fullname']);
    $message->send($transport);
  }

  // apply uses this. Password reset also uses it in the case of a user who
  // was never verified to begin with

  protected function sendVerificationMail($profile)
  {
    /*
    $this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_apply_subject',
        sfContext::getInstance()->getI18N()->__("Please verify your account on %1%", array('%1%' => $this->getRequest()->getHost()))),
      'fullname' => $profile->getFullname(),
      'email' => $profile->getEmail(),
      'parameters' => array('fullname' => $profile->getFullname(), 'validate' => $profile->getValidate()),
      'text' => 'sfApply/sendValidateNewText',
      'html' => 'sfApply/sendValidateNew'));

      */

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

    //mail($to,$subject,$message,$headers);

    //$mail = $this->getMailer()->compose();
    //$mail->setSubject($subject);
    //$mail->setTo($to);
    //$mail->setFrom("info@kcps.gov.rw");

    //$mail->setBody($message, 'text/html');

    //$this->getMailer()->send($mail);

  }

  public function executeResetRequest(sfRequest $request)
  {
    $this->setLayout("layoutmentorlogin");
    $user = $this->getUser();
    if ($user->isAuthenticated()) {
      $guardUser = $this->getUser()->getGuardUser();
      $this->forward404Unless($guardUser);
      return $this->resetRequestBody($guardUser);
    } else {
      $this->form = $this->newForm('sfApplyResetRequestForm');
      if ($request->isMethod('post')) {
        $this->form->bind($request->getParameter('sfApplyResetRequest'));
        if ($this->form->isValid()) {
          // The form matches unverified users, but retrieveByUsername does not, so
          // use an explicit query. We'll special-case the unverified users in
          // resetRequestBody

          $username_or_email = $this->form->getValue('username_or_email');
          if (strpos($username_or_email, '@') !== false) {
            $q = Doctrine_Query::create()
              ->from('sfGuardUser u')
              ->innerJoin('u.Profile p')
              ->where('p.email =?', $username_or_email);
            $user = $q->fetchOne();
          } else {
            $q = Doctrine_Query::create()
              ->from('sfGuardUser u')
              ->where('u.username =?', $username_or_email);
            $user = $q->fetchOne();
          }
          return $this->resetRequestBody($user);
        }
      }
    }
  }

  public function resetRequestBody($user)
  {
    if (!$user) {
      return 'NoSuchUser';
    }
    $this->forward404Unless($user);
    $profile = $user->getProfile();

    if (!$user->getIsActive()) {
      $type = $this->getValidationType($profile->getValidate());
      if ($type === 'New') {
        try {
          $this->sendVerificationMail($profile);
        } catch (Exception $e) {
          return 'UnverifiedMailerError';
        }
        return 'Unverified';
      } elseif ($type === 'Reset') {
        // They lost their first password reset email. That's OK. let them try again
      } else {
        return 'Locked';
      }
    }
    $profile->setValidate('r' . self::createGuid());
    $profile->save();
    try {
      /*$this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_reset_subject',
          sfContext::getInstance()->getI18N()->__("Please verify your password reset request on %1%", array('%1%' => $this->getRequest()->getHost()))),
        'fullname' => $profile->getFullname(),
        'email' => $profile->getEmail(),
        'parameters' => array('fullname' => $profile->getFullname(), 'validate' => $profile->getValidate(), 'username' => $user->getUsername()),
        'text' => 'sfApply/sendValidateResetText',
        'html' => 'sfApply/sendValidateReset')); */
      if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $is_http = "https";
      } else {
        $is_http = "http";
      }

      error_log("Checking if its http or https ---->" . $is_http);
      error_log("<a href='{$is_http}://" . $this->getRequest()->getHost() . "/plan/sfApply/confirm?validate=" . $profile->getValidate() . "&username=" . $user->getUsername());
      $to = $profile->getEmail();
      $subject = "Password Reset";
      $message = "Please click the following link to reset your password: <a href='{$is_http}://" . $this->getRequest()->getHost() . "/plan/sfApply/confirm?validate=" . $profile->getValidate() . "&username=" . $user->getUsername() . "'>Password Reset</a>";
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
      //mail($to,$subject,$message,$headers);
      //$mail = $this->getMailer()->compose();
      //$mail->setSubject($subject);
      //$mail->setTo($to);
      //$mail->setFrom("info@kcps.gov.rw");

      //$mail->setBody($message, 'text/html');

      //$this->getMailer()->send($mail);

    } catch (Exception $e) {
      return 'MailerError';
    }
    return 'After';
  }

  protected function getFromAddress()
  {
    $from = sfConfig::get('app_sfApplyPlugin_from', false);
    if (!$from) {
      throw new Exception('app_sfApplyPlugin_from is not set');
    }
    // i18n the full name
    return array('email' => $from['email'], 'fullname' => sfContext::getInstance()->getI18N()->__($from['fullname']));
  }

  public function executeConfirm(sfRequest $request)
  {
    $this->setLayout("layoutmentorlogin");

    $validate = $this->request->getParameter('validate');
    // 0.6.3: oops, this was in sfGuardUserProfilePeer in my application
    // and therefore never got shipped with the plugin until I built
    // a second site and spotted it!

    // Note that this only works if you set foreignAlias and
    // foreignType correctly
    $sfGuardUser = Doctrine_Query::create()->from("sfGuardUser u")->innerJoin("u.Profile p with p.validate = ?", $validate)->fetchOne();

    if (!$sfGuardUser) {
      return "Invalid";
    }

    $type = self::getValidationType($validate);
    if (!strlen($validate)) {
      return "Invalid";
    }


    $profile = $sfGuardUser->getProfile();
    $profile->setValidate(null);
    $profile->save();
    if ($type == 'New') {
      $sfGuardUser->setIsSuperAdmin(true);
      $sfGuardUser->setIsActive(true);
      $sfGuardUser->save();
      //$this->getUser()->signIn($sfGuardUser);
    }
    if ($type == 'Reset') {
      $this->getUser()->setAttribute('sfApplyReset', $sfGuardUser->getId());
      return $this->redirect('sfApply/reset');
    }
  }

  public function executeReset(sfRequest $request)
  {
    $this->setLayout("layoutmentorlogin");
    $this->form = $this->newForm('sfApplyResetForm');
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('sfApplyReset'));
      if ($this->form->isValid()) {
        $this->id = $this->getUser()->getAttribute('sfApplyReset', false);
        $this->forward404Unless($this->id);
        $this->sfGuardUser = Doctrine_Query::create()->from('sfGuardUser u')->where('u.id = ?', $this->id)->fetchOne();
        $this->forward404Unless($this->sfGuardUser);
        $sfGuardUser = $this->sfGuardUser;
        $sfGuardUser->setPassword($this->form->getValue('password'));
        $sfGuardUser->save();
        $this->getUser()->signIn($sfGuardUser);
        $this->getUser()->setAttribute('sfApplyReset', null);
        return 'After';
      }
    }
  }

  public function executeResetCancel()
  {
    $this->setLayout("layoutmentorlogin");
    $this->getUser()->setAttribute('sfApplyReset', null);
    return $this->redirect(sfConfig::get('app_sfApplyPlugin_after', '@homepage'));
  }

  public function executeSettings(sfRequest $request)
  {
    $user = $this->getUser();
    if ($user->isAuthenticated()) {
      if (sfConfig::get('app_sso_secret', null)) {
        $this->redirect(sfConfig::get('app_sso_jambo_web_url') . "/#/profile");
        exit;
      }
      // sfApplySettingsForm inherits from sfApplyApplyForm, which
      // inherits from sfGuardUserProfile. That minimizes the amount
      // of duplication of effort. If you want, you can use a different
      // form class. I suggest inheriting from sfApplySettingsForm and
      // making further changes after calling parent::configure() from
      // your own configure() method.

      $profile = $this->getUser()->getProfile();
      $this->form = $this->newForm('sfApplySettingsForm', $profile);
      if ($request->isMethod('post')) {
        $this->form->bind($request->getParameter('sfApplySettings'));
        if ($this->form->isValid()) {
          $this->form->save();
          return $this->redirect('@homepage');
        }
      }
      $this->setLayout("layoutmentordash");
    } else {
      return $this->redirect('@homepage');
    }

    $this->setLayout("layoutmentordash");
  }

  static private function createGuid()
  {
    $guid = "";
    // This was 16 before, which produced a string twice as
    // long as desired. I could change the schema instead
    // to accommodate a validation code twice as big, but
    // that is completely unnecessary and would break
    // the code of anyone upgrading from the 1.0 version.
    // Ridiculously unpasteable validation URLs are a
    // pet peeve of mine anyway.
    for ($i = 0; ($i < 8); $i++) {
      $guid .= sprintf("%02x", mt_rand(0, 255));
    }
    return $guid;
  }

  static private function getValidationType($validate)
  {
    $t = substr($validate, 0, 1);
    if ($t == 'n') {
      return 'New';
    } elseif ($t == 'r') {
      return 'Reset';
    } else {
      return sfView::NONE;
    }
  }

  // There's a lot here. Symfony could benefit from a standard convenience
  // class with a method like this one.
  protected function getMailTransport()
  {
    // sfDoctrineApplyPlugin 1.1 uses Zend_Mail instead of SwiftMailer. SwiftMailer 3.0 is
    // unsupported at this point, and rather than upgrade to 4.0, we're choosing to go with
    // a framework that we already use for search. Fewer dependencies = better

    // Example:
    //
    // all:
    //   sfApply:
    //     mail_transport_class: Zend_Mail_Transport_Smtp
    //     mail_transport_host: smtp.example.com
    //     mail_transport_options:
    //       ssl: tls # Enable SSL
    //
    // See also:
    //
    // http://framework.zend.com/manual/en/zend.mail.smtp-secure.html
    // http://micrub.info/2008/09/22/sending-email-with-zend_mail-using-gmail-smtp-services/
    //
    // Or just use the default mailer by not configuring these options at all.

    $this->registerZend();

    $class = sfConfig::get('app_sfApplyPlugin_mail_transport_class', null);
    $host = sfConfig::get('app_sfApplyPlugin_mail_transport_host', null);
    $options = sfConfig::get('app_sfApplyPlugin_mail_transport_options', null);
    if (($class === null) && ($options === null) && ($host === null)) {
      // This actually works - Zend_Mail will accept null and use the default transport
      return null;
    }
    $transport = new $class($host, $options);
    return $transport;
  }

  static private $zendLoaded = false;

  public function registerZend()
  {
    if (self::$zendLoaded) {
      return;
    }

    # Zend 1.8.0 and thereafter

    include_once('Zend/Loader/Autoloader.php');
    $loader = Zend_Loader_Autoloader::getInstance();
    // Zend should NOT be the fallback autoloader, that gets in Symfony's way and generates warnings in 1.3
    $loader->setFallbackAutoloader(false);
    $loader->suppressNotFoundWarnings(false);

    self::$zendLoaded = true;
  }

  // A convenience method to instantiate a form of the
  // specified class... unless the user has specified a
  // replacement class in app.yml. Sweet, no?
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
  //OTB ADD - Function to resend validation email and incase user had requested for password reset and account isn't active we update the validate column and resend
  public function executeUserresendvalidationemail(sfRequest $request)
  {
    $this->form = $this->newForm('sfApplyResetRequestForm');
    unset($_SESSION['resend_resp']);
    unset($_SESSION['resent_resp_valid']);
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('sfApplyResetRequest'));
      if ($this->form->isValid()) {
        $username_or_email = $this->form->getValue('username_or_email');
        error_log('-----username_or_email----' . $username_or_email);
        if (strpos($username_or_email, '@') !== false) {
          $user = Doctrine_Core::getTable('sfGuardUser')->createQuery('u')->innerJoin('u.Profile p')->where('p.email = ?', $username_or_email)->fetchOne();
        } else {
          $user = Doctrine_Core::getTable('sfGuardUser')->createQuery('u')->where('username = ?', $username_or_email)->fetchOne();
        }
        $this->forward404Unless($user);
        $profile = $user->getProfile();
        $type = $this->getValidationType($profile->getValidate());

        if ($type == 'New') {
          //Send the email again
          $this->sendVerificationMail($profile);
          $_SESSION['resend_resp'] = true;
        } elseif ($type == 'Reset' && !$user->getIsSuperAdmin()) {
          //Update the user account
          $guid = "n" . self::createGuid();
          $profile->setValidate($guid)->save();
          $this->sendVerificationMail($profile);
          $_SESSION['resend_resp'] = true;
        } else {
          if (!$user->getIsSuperAdmin()) {
            $guid = "n" . self::createGuid();
            $profile->setValidate($guid)->save();
            $this->sendVerificationMail($profile);
            $_SESSION['resend_resp'] = true;
          } else {
            $_SESSION['resent_resp_valid'] = true;
          }
        }
      }
    }

    $this->setLayout("layoutmentorlogin");
  }
  //OTB END
}
