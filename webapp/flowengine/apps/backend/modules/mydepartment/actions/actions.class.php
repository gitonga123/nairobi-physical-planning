<?php
/**
 * Users actions.
 *
 * Reviewers Management Service.
 *
 * @package    backend
 * @subpackage users
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class mydepartmentActions extends sfActions
{
    /**
     * Executes 'Viewuser' action
     *
     * Displays full reviewer details
     *
     * @param sfRequest $request A request object
     */
    public function executeViewuser(sfWebRequest $request)
    {
       $this->page = $request->getParameter('page', 0);
       $this->spage = $request->getParameter('spage', 0);
       $this->aspage = $request->getParameter('aspage', 0);
       $this->apppage = $request->getParameter('apppage', 0);
       $this->taskpage = $request->getParameter('taskpage', 0);
       $this->page_applications = $request->getParameter('pageapp', 1);
       $this->page_tasks = $request->getParameter('pagetask', 1);
       $this->userid = $request->getParameter('userid');

       if($request->getParameter('tab'))
       {
         $_GET['tab'] = $request->getParameter('tab');
       }

       if($request->getPostParameter("fromdate"))
       {
          $this->fromdate = $request->getPostParameter("fromdate");
          $this->fromtime = $request->getPostParameter("fromtime");
          $this->todate = $request->getPostParameter("todate");
          $this->totime = $request->getPostParameter("totime");
       }

       $this->filter_date = $request->getPostParameter("filterdate", date("Y-m-d"));

        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $this->userid);
        $reviewer = $q->fetchOne();

        $this->forward404Unless($reviewer = Doctrine_Core::getTable('CfUser')->find(array($request->getParameter('userid'))), sprintf('The selected reviewer of id (%s) does not exist.', $request->getParameter('userid')));

        $this->reviewer = $reviewer;
    }

    /**
     * Executes 'Index' action
     *
     * Displays list of reviewer departments
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $this->getUser()->getAttribute("userid"));
        $logged_reviewer = $q->fetchOne();

        $this->filter = "";

        if($request->getParameter('filter'))
        {
            $this->filter = $request->getParameter('filter');
        }

        if($request->getPostParameter('search'))
        {
        $this->filter = $request->getPostParameter('search');
        }

        if($this->filter)
        {
            if($request->getParameter('filterstatus') != "")
            {
                $this->filterstatus = $request->getParameter('filterstatus');

                $q = Doctrine_Query::create()
                    ->from('CfUser a')
                    ->where('a.bdeleted = ?', $this->filterstatus)
                    ->andWhere('a.strdepartment = ?', $logged_reviewer->getStrdepartment())
                    ->andWhere('a.strfirstname LIKE ? OR a.stremail  = ? OR a.struserid = ?', array($this->filter . "%", $this->filter, $this->filter))
                    ->orderBy('a.strfirstname ASC');
            }
            else {
                $q = Doctrine_Query::create()
                    ->from('CfUser a')
                    ->where('a.bdeleted = 0')
                    ->andWhere('a.strdepartment = ?', $logged_reviewer->getStrdepartment())
                    ->andWhere('a.strfirstname LIKE ? OR a.stremail  = ? OR a.struserid = ?', array($this->filter . "%", $this->filter, $this->filter))
                    ->orderBy('a.strfirstname ASC');

                $this->filterstatus = "";
            }
        }
        else
        {
            if($request->getParameter('filterstatus') != "")
            {
                $this->filterstatus = $request->getParameter('filterstatus');
                $q = Doctrine_Query::create()
                    ->from('CfUser a')
                    ->where('a.bdeleted = ?', $this->filterstatus)
                    ->andWhere('a.strdepartment = ?', $logged_reviewer->getStrdepartment())
                    ->orderBy('a.strfirstname ASC');
            }
            else {
                $q = Doctrine_Query::create()
                    ->from('CfUser a')
                    ->where('a.bdeleted = 0')
                    ->andWhere('a.strdepartment = ?', $logged_reviewer->getStrdepartment())
                    ->orderBy('a.strfirstname ASC');

                $this->filterstatus = "";
            }
        }
        $this->pager = new sfDoctrinePager('CfUser', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();

        if($request->getPostParameter("filter_date"))
        {
            $this->getUser()->setAttribute("filter_date", date('Y-m-d', strtotime($request->getPostParameter("filter_date"))));
        }

        if($this->getUser()->getAttribute("filter_date"))
        {
            $this->filter_date = date('Y-m-d', strtotime($this->getUser()->getAttribute("filter_date")));
        }
        else 
        {
            $this->filter_date = date("Y-m-d");
        }
    }

}
