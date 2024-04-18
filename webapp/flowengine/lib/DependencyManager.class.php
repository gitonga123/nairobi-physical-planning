<?php
/**
 *
 * Dependency class will handle form dependencyies for all drafts
 *
 */

class DependencyManager {

  private $dbconn;

  //Constructor for dependency class
  public function __construct()
  {
    $this->dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'),$this->dbconn);
  }

  public function has_dependency($form_id, $record_id)
  {
    $sql = "SELECT * FROM form_dependents WHERE form_id = ".$form_id;
    $dependents = mysql_query($sql, $this->dbconn);

    while($dependent = mysql_fetch_assoc($dependents))
    {
      $sql = "SELECT * FROM ap_form_".$form_id." WHERE id = ".$record_id;
      $form_data = mysql_fetch_assoc(mysql_query($sql, $this->dbconn));

      if($form_data['element_'.$dependent["element_id"]] == $dependent["element_value"])
      {
        $q = Doctrine_Query::create()
           ->from("ApForms a")
           ->where("a.form_id = ?", $dependent["dependent_form_id"]);
         $form = $q->fetchOne();

         if($form)
         {
           return $dependent["dependent_form_id"];
         }
      }
    }

    return false;
  }

  public function dependency_met($form_id, $record_id)
  {
    $q = Doctrine_Query::create()
       ->from("FormEntry a")
       ->where("a.form_id = ? and a.entry_id = ?", array($form_id, $record_id));
    $application = $q->fetchOne();

    $user = Doctrine_Core::getTable('SfGuardUser')->find(array($application->getUserId()));

    $fullname = $user->getProfile()->getFullname();
    $idnumber = $user->getUsername();
    $email = $user->getEmailAddress();

    //One of email or phonenumber is required
    $phonenumber = $user->getProfile()->getMobile();

    $token_info = array(
      "Name" => $fullname,
      "Phone" => $phonenumber,
      "Email" => $email,
      "NationalID" => $idnumber
    );

    $cart_manager = new CartManager($token_info, $application->getUserId());

    $form_id = $this->has_dependency($form_id, $record_id);
    if($form_id)
    {
      $q = Doctrine_Query::create()
         ->from("FormEntry a")
         ->where("a.form_id = ?", $form_id)
         ->andWhere("a.user_id = ?", $application->getUserId())
         ->orderBy("a.id DESC");

      if($q->count() > 0)
      {
        $dependency_application = $q->fetchOne();

        if($cart_manager->is_in_cart($dependency_application->getId()))
        {
          return true;
        }
        else
        {
          return false;
        }
      }
    }
    else
    {
      return false;
    }
  }

}
