<?php
/**
 * Notifications actions.
 *
 * Confirms receipt of alerts/notifications sent via email
 *
 * @package    frontend
 * @subpackage notifications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class notificationsActions extends sfActions
{

    /**
     * Executes 'Index' action
     *
     * Show a list of notifications
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from("NotificationHistory a")
            ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
            ->andWhere("a.application_id <> ?",'')
            ->andWhere("a.confirmed_receipt = ?", 0)
            ->orderBy("a.id DESC");
        $this->pager = new sfDoctrinePager('NotificationHistory', 10);
        $this->pager->setQuery($q);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->init();
        $this->setLayout("layoutdash");
    }

    /**
	 * Executes 'Confirm' action
	 *
	 * Displays success message when a client successfully confirms receipt of alert/notification via email
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeConfirm(sfWebRequest $request)
      {
         if($request->getParameter("id"))
         {
            $q = Doctrine_Query::create()
               ->from('NotificationHistory a')
               ->where('a.id = ?', $request->getParameter('id'));
            $notifs = $q->execute();
            foreach($notifs as $notif)
            {
               $notif->setConfirmedReceipt('1');
               $notif->save();
                $this->redirect("/index.php//dashboard");
            }
         }
		$this->setLayout("layoutdash");
     }

    /**
     * Executes 'viewNotificaiton' action
     *
     * Mark a notification as read and redirect to the link
     *
     * @param sfRequest $request A request object
     */
    public function executeViewnotification(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from("NotificationHistory a")
            ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
            ->andWhere("a.id = ?", $request->getParameter('id'))
            ->orderBy("a.id DESC");
        $alert = $q->fetchOne();
        if($alert)
        {
            $alert->setConfirmedReceipt(1);
            $alert->save();

            if($alert->getApplicationId()) {
                $this->redirect("/index.php//application/view/id/" . $alert->getApplicationId());
            }
            else
            {
                $this->redirect("/index.php//notifications/index");
            }
        }
        else
        {
            $this->redirect("/index.php//notifications/index");
        }
    }
}
