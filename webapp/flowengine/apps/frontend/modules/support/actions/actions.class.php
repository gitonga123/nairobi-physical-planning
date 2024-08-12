<?php

/**
 * Messages actions.
 *
 * Displays messages sent between currently logged in client and reviewers
 *
 * @package    frontend
 * @subpackage messages
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class supportActions extends sfActions
{
    /**
     * Executes 'Index' action
     *
     * Displays list of all messages to the currently logged in client
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('Communications a')
            ->leftJoin('a.FormEntry b')
            ->where('a.reviewer_id <> ?', "")
            ->andWhere('b.user_id = ?', $this->getUser()->getGuardUser()->getId())
            ->orderBy('a.id DESC');
        $this->pager = new sfDoctrinePager('Communications', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Sent' action
     *
     * Displays list of all sent messages from the currently logged in client
     *
     * @param sfRequest $request A request object
     */
    public function executeSent(sfWebRequest $request)
    {

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.user_id = ?', $this->getUser()->getGuardUser()->getId())
            ->orderBy('a.id DESC');
        $allapplications = $q->execute();


        foreach ($allapplications as $application) {

            $q = Doctrine_Query::create()
                ->from('Communications a')
                ->where('a.application_id = ?', $application->getId())
                ->orderBy('a.id DESC');
            $communications = $q->execute();
            foreach ($communications as $communication) {
                if ($communication->getArchitectId() != "") {
                    $messages[] = $communication;
                }
            }
        }

        $this->messages = $messages;

        if ($request->getParameter("show")) {
            $this->show = $request->getParameter("show");
        }

        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'New' action
     *
     * Displays form for submitting a new message
     *
     * @param sfRequest $request A request object
     */
    public function executeNew(sfWebRequest $request)
    {
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Send' action
     *
     * Submit message details to the reviewers
     *
     * @param sfRequest $request A request object
     */
    public function executeSend(sfWebRequest $request)
    {
        $message = new Communications();
        $message->setArchitectId($this->getUser()->getGuardUser()->getId());
        $message->setMessageread("0");
        $message->setContent($request->getPostParameter("appmessage"));
        $message->setApplicationId($request->getPostParameter("application"));
        $message->setActionTimestamp(date('Y-m-d'));
        $message->save();

        $this->redirect('/plan//messages');
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'View' action
     *
     * Displays the full message
     *
     * @param sfRequest $request A request object
     */
    public function executeView(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('Communications a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->communication = $q->fetchOne();
        $this->communication->setMessageread("1");
        $this->communication->save();

        if ($request->getPostParameter("reply")) {
            $message = new Communications();
            $message->setArchitectId($this->getUser()->getGuardUser()->getId());
            $message->setMessageread("1");
            $message->setContent($request->getPostParameter("reply"));
            $message->setApplicationId($this->communication->getApplicationId());
            $message->setActionTimestamp(date('Y-m-d'));
            $message->save();
        }
        $this->setLayout("layoutmentordash");
    }
}
