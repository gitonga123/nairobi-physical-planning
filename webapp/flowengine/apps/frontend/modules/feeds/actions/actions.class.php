<?php
/**
 * Feed actions.
 *
 * Handles RSS Feeds
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa
 */

class feedsActions extends sfActions
{
  /**
  * Gets list of all available application forms/services (Doesn't group them by form_groups)
  **/
  public function executeServices(sfWebRequest $request)
  {
    $this->setLayout(false);

    $q = Doctrine_Query::create()
       ->from("ApForms a")
       ->where("a.form_type = 1")
       ->andWhere("a.form_active = 1")
       ->orderBy("a.form_name ASC");
    $available_forms = $q->execute();

    $organisation_name = "";
    $organisation_description = "";

    $q = Doctrine_Query::create()
       ->from("ApSettings a")
       ->where("a.id = 1")
       ->orderBy("a.id DESC");
    $apsettings = $q->fetchOne();
    if($apsettings)
    {
        $organisation_name = $apsettings->getOrganisationName();
        $organisation_description = $apsettings->getOrganisationDescription();
    }

    // The XML structure
    $data = '<?xml version="1.0" encoding="UTF-8" ?>';
    $data .= '<rss version="2.0">';
    $data .= '<channel>';
    $data .= '<title>'.$organisation_name.' : '.$organisation_description.'</title>';
    $data .= '<link>http://'.$_SERVER['HTTP_HOST'].'</link>';
    $data .= '<description>List of available services</description>';
    foreach ($available_forms as $available_form) {
    $data .= '<item>';
    $data .= '<title>'.$available_form->getFormName().'</title>';
    $data .= '<link>http://'.$_SERVER['HTTP_HOST'].'/index.php/forms/view?id='.$available_form->getFormId().'</link>';
    $data .= '<description>'.$available_form->getFormDescription().'</description>';
    $data .= '</item>';
    }
    $data .= '</channel>';
    $data .= '</rss> ';

    echo $data;
    $this->getResponse()->setContentType('text/xml');
    exit;
  }


  /**
  * Gets details about a submitted application
  **/
  public function executeGetapplication(sfWebRequest $request)
  {
    $this->setLayout(false);

    $q = Doctrine_Query::create()
       ->from("FormEntry a")
       ->where("a.application_id = ?", $request->getParameter('id'));
    $application = $q->fetchOne();

    $data = "";

    if($application)
    {

      $q = Doctrine_Query::create()
         ->from("SfGuardUserProfile a")
         ->where("a.user_id = ?", $application->getUserId());
      $user_profile = $q->fetchOne();

      if($user_profile)
      {
        $q = Doctrine_Query::create()
           ->from("SfGuardUser a")
           ->where("a.id = ?", $application->getUserId());
        $user = $q->fetchOne();

        // The XML structure
        $data = '<?xml version="1.0" encoding="utf-8"?>';
        $data .= '<item>';
        $data .= '<applicationno>'.$application->getApplicationId().'</applicationno>';
        $data .= '<submittedon>'.$application->getDateOfSubmission().'</submittedon>';
        $data .= '<idnumber>'.$user->getUsername().'</idnumber>';
        $data .= '<email>'.$user_profile->getEmail().'</email>';
        $data .= '<fullname>'.$user_profile->getFullname().'</fullname>';
        $data .= '<stage>'.$application->getStatusName().'</stage>';
        if($application->getDateOfResponse())
        {
          $data .= '<status>Approved</status>';
        }
        else
        {
          $data .= '<status>Not Approved</status>';
        }
        $data .= '<approvedon>'.$application->getDateOfResponse().'</approvedon>';
        $data .= '</item>';
      }
      else
      {
        // The XML structure
          $data = '<?xml version="1.0" encoding="UTF-8" ?>';
          $data .= '<rss version="2.0">';
          $data .= '<item>';
          $data .= '<applicationno>'.$application->getApplicationId().'</applicationo>';
          $data .= '<submittedon>'.$application->getDateOfSubmission().'</submittedon>';
          $data .= '<submittedby>Unknown</submittedby>';
          $data .= '<stage>'.$application->getStatusName().'</stage>';
          if($application->getDateOfResponse())
          {
            $data .= '<status>Approved</status>';
          }
          else
          {
            $data .= '<status>Not Approved</status>';
          }
          $data .= '<approvedon>'.$application->getDateOfResponse().'</approvedon>';
          $data .= '</item>';
      }

    }
    else
    {
      // The XML structure
        $data = '<?xml version="1.0" encoding="UTF-8" ?>';
        $data .= '<response>Application does not exist</response>';
    }

    header('Content-type: text/xml');
    echo $data;
    $this->getResponse()->setContentType('text/xml');
    exit;
  }


  /**
  * Gets list of all users applications
  **/
  public function executeGetuserapplications(sfWebRequest $request)
  {
    $this->setLayout(false);

    $q = Doctrine_Query::create()
       ->from("SfGuardUser a")
       ->where("a.username = ?", $request->getParameter('idnumber'));
    $user = $q->fetchOne();

    if($user) //Check if user exists, else display user does not exist xml
    {
      $q = Doctrine_Query::create()
         ->from("SfGuardUserProfile a")
         ->where("a.user_id = ?", $user->getId());
      $user_profile = $q->fetchOne();

      $q = Doctrine_Query::create()
         ->from("FormEntry a")
         ->where("a.user_id = ?", $user->getId());
      $applications = $q->execute();

      $data = "";

      if(sizeof($applications) > 0) //Check if there are any applications, else display no applications found xml
      {

          // The XML structure
          $data = '<?xml version="1.0" encoding="UTF-8" ?>';
          $data .= '<applications>';
          foreach($applications as $application) //Display all applications submitted by specified user
          {
            $data .= '<item>';
            $data .= '<applicationno>'.$application->getApplicationId().'</applicationno>';
            $data .= '<submittedon>'.$application->getDateOfSubmission().'</submittedon>';
            $data .= '<idnumber>'.$user->getUsername().'</idnumber>';
            $data .= '<email>'.$user_profile->getEmail().'</email>';
            $data .= '<fullname>'.$user_profile->getFullname().'</fullname>';
            $data .= '<stage>'.$application->getStatusName().'</stage>';
            if($application->getDateOfResponse())
            {
              $data .= '<status>Approved</status>';
            }
            else
            {
              $data .= '<status>Not Approved</status>';
            }
            $data .= '<approvedon>'.$application->getDateOfResponse().'</approvedon>';
            $data .= '</item>';
          }
          $data .= '</applications>';

      }
      else
      {
        // The XML structure
          $data = '<?xml version="1.0" encoding="UTF-8" ?>';
          $data .= '<response>No applications found</response>';
      }
    }
    else
    {
      // The XML structure
      $data = '<?xml version="1.0" encoding="UTF-8" ?>';
      $data .= '<response>User does not exist</response>';
    }

    header('Content-type: text/xml');
    echo $data;
    $this->getResponse()->setContentType('text/xml');
    exit;
  }
}
