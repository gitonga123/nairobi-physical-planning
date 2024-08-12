<?php
/**
 * index actions.
 *
 * Displays Web Page Content from the Content Management System
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class indexActions extends sfActions
{
    /**
     * Executes 'Index' action
     *
     * Displays a web page from the CMS based on id
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        //If default language has not been set then pick english as the default
        if(empty($this->getUser()->getCulture()) || $this->getUser()->getCulture() == 'localhost')
        {
            $this->getUser()->setCulture('en_US');
        }

        //If user is logged in then redirect to dashboard
        if($this->getUser()->isAuthenticated())
        {
            return $this->redirect("plan/dashboard");
        }

        //Fetch page by id or fetch homepage
        $this->page = Doctrine_Core::getTable('Content')->find(array($request->getParameter('id', 1)));

        //If page does not exist then redirect to 404
        if(empty($this->page))
        {
            return $this->redirect("plan/errors/notfound");
        }
        $this->setLayout('layouthomentor') ;
    }

     /**
     * Executes 'Setlocale' action
     *
     * Changes the language for current user (logged in and not logged in)
     *
     * @param sfRequest $request A request object
     */
    public function executeSetlocale(sfWebRequest $request)
    {
        //Set the locale
        $this->getUser()->setCulture($request->getParameter("code"));

        $_SESSION['locale'] = $request->getParameter("code");

        //Redirect back to referring page
        $this->redirect($request->getReferer());
    }
}
