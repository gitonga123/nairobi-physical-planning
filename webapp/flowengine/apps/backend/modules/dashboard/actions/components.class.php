<?php

/**
 * Dashboard components.
 *
 * Contains code snippets that can be inserted into the layout
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class dashboardComponents extends sfComponents
{
  /**
   * Executes 'Sidemenu' component
   *
   * Displays the sidemenu located on the left of the screen
   *
   */
  public function executeSidemenu(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');

    $this->logged_reviewer = Functions::current_user();

    $this->services = Functions::get_allowed_services();

    $q =  Doctrine_Query::create()
      ->from('MembersDatabase a')
      ->where('a.validate > 0');
    $this->unverified_professionals = $q->count();
  }

  /**
   * Executes 'Sidemenu' component
   *
   * Displays the sidemenu located on the left of the screen
   *
   */
  public function executeSettingssidemenu(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');

    $this->logged_reviewer = Functions::current_user();
  }


  /**
   * Executes 'Header' component
   *
   * Displays the header component
   *
   */
  public function executeHeader(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');

    $this->logged_reviewer = Functions::current_user();

    $q = Doctrine_Query::create()
      ->from("ExtLocales a")
      ->orderBy("a.local_title ASC");
    $this->languages = $q->execute();
  }
  /**
   * Custom mentor header
   */
  public function executeHeadermentor(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');

    $this->logged_reviewer = Functions::current_user();

    $q = Doctrine_Query::create()
      ->from("ExtLocales a")
      ->orderBy("a.local_title ASC");
    $this->languages = $q->execute();
  }
  /**
   * Custom mentor sidebar
   */
  public function executeSidebarmentor(sfWebRequest $request) {}



  /**
   * Executes 'Header' component
   *
   * Displays the header component
   *
   */
  public function executeSettingsheader(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');

    $this->logged_reviewer = Functions::current_user();

    $this->site_settings = Functions::site_settings();

    $q = Doctrine_Query::create()
      ->from("ExtLocales a")
      ->orderBy("a.local_title ASC");
    $this->languages = $q->execute();
  }

  /**
   * Executes 'Stylesheets' component
   *
   * Displays the stylesheets component
   *
   */
  public function executeStylesheets(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');
  }

  /**
   * Executes 'Javascripts' action
   *
   * Displays the javascripts component
   *
   */
  public function executeJavascripts(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');
  }

  /**
   * Executes 'Checksession' action
   *
   * Logout user if session info is not correct
   *
   */
  public function executeChecksession(sfWebRequest $request)
  {
    $this->module = $request->getParameter('module');
    $this->action = $request->getParameter('action');
  }
}
