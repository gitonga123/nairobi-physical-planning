<?php
/**
 * Sharedapplication actions.
 *
 * Displays shared applications submitted by the client
 *
 * @package    frontend
 * @subpackage sharedapplication
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class sharedapplicationActions extends sfActions
{
        /**
	 * Executes 'Index' action
	 *
	 * Displays list of all of the currently logged in client's shared applications
	 *
	 * @param sfRequest $request A request object
	 */
    public function executeIndex(sfWebRequest $request)
    {
            $this->page = $request->getParameter('page', 1);
		$this->setLayout("layoutdash");
    }

        /**
	 * Executes 'Unshare' action
	 *
	 * Stop sharing selected application with another client
	 *
	 * @param sfRequest $request A request object
	 */
        public function executeUnshare(sfWebRequest $request)
        {
                  $q = Doctrine_Query::create()
                        ->from('FormEntryShares a')
                        ->where('a.Formentryid = ?', $request->getParameter("id"))
                        ->andWhere('a.senderid = ?', $this->getUser()->getGuardUser()->getId());
                  $shareapplication = $q->fetchOne();
                  $shareapplication->delete();
                  
                  $this->redirect("/plan/sharedapplication/index");
        }

  /**
	 * Executes 'Unshare' action
	 *
	 * Stop sharing selected application with me
	 *
	 * @param sfRequest $request A request object
	 */
    public function executeUnshareme(sfWebRequest $request)
    {
              $q = Doctrine_Query::create()
                    ->from('FormEntryShares a')
                    ->where('a.Formentryid = ?', $request->getParameter("id"))
                    ->andWhere('a.receiverid = ?', $this->getUser()->getGuardUser()->getId());
              $shareapplication = $q->fetchOne();
              $shareapplication->delete();
              
              $this->redirect("/plan/sharedapplication/index");
    }

  /**
   * Executes 'View' action
   *
   * View Shared Application
   *
   * @param sfRequest $request A request object
   */
    public function executeView(sfWebRequest $request)
    {
		$q = Doctrine_Query::create()
			->select('a.formentryid')
		   ->from("FormEntryShares a")
		   ->where("a.receiverid = ? OR a.senderid = ?", array($this->getUser()->getGuardUser()->getId(),$this->getUser()->getGuardUser()->getId()));        
	   $this->forward404Unless($q->count(),"Application not shared with the user!");
             $q = Doctrine_Query::create()
                    ->from('FormEntry a')
                    ->where('a.id = ?', $request->getParameter("id"));
             $this->application = $q->fetchOne();
		$this->setLayout("layoutdash");
    } 
    /**
     *  OTB patch
     * Save message from users
     */
    public function executeSaveMessage(sfWebRequest $request){
          $q = Doctrine_Query::create()
                    ->from('FormEntry a')
                    ->where('a.id = ?', $request->getParameter("id"));
             $this->application = $q->fetchOne();  
          //set layout
           $this->setLayout("layoutdash");
           
         //OTB patch - Add code for saving message in shared application view
                if($request->getPostParameter("txtmessage"))
                {
                        //save user message
                        $message = new Communications();
                        $message->setArchitectId($this->getUser()->getGuardUser()->getId());
                        $message->setMessageread("0");
                        $message->setContent($request->getPostParameter("txtmessage"));
                        $message->setApplicationId($this->application->getId());
                        $message->setActionTimestamp(date('Y-m-d'));
                        $message->save();
                        //Log user activity
                        $activity = new Activity();
                        $activity->setUserId($this->getUser()->getGuardUser()->getId());
                        $activity->setFormEntryId($this->application->getId());
                        $activity->setAction("User sent a message");
                        $activity->setActionTimestamp(date('Y-m-d'));
                        $activity->save();
                       
                }
                else {
                    //do nothing
                }
                $this->redirect("/plan/sharedapplication/view/id/".$this->application->getId());
              // 
		
    }
	public function executeEdit(sfWebRequest $request)
	{
		$this->setLayout("layoutdash");
	}
}
