<?php

/**
 * share actions.
 *
 * @package    symfony
 * @subpackage share
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class shareActions extends sfActions {

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {
        // $this->forward('default', 'module');
        print "List of Shared Apps";
        exit();
    }

    /**
     * Executes 'Share' action
     *
     * Allows the client to share selected application with another client
     *
     * @param sfRequest $request A request object
     */
    public function executeShare(sfWebRequest $request) {

        $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.id = ?', $request->getParameter("id"));
        $this->application = $q->fetchOne();
		//Check if stage is set for share
        $otbhelper = new OTBHelper() ;
		$this->forward404Unless($this->getUser()->mfHasCredential("backend_share_application") && $otbhelper->isSharedStage($this->application->getApproved()),'Can\'t share application! Application needs to be on a allowed share stage');
        if ($request->getParameter("filter")) {
            $this->filter = trim($request->getParameter("filter"));
        }

        if ($request->getParameter("page")) {
            $this->page = $request->getParameter("page");
        }

        if ($request->getParameter("architect") && $request->getParameter("architect") != "") 
        {
            /**
             * OTB patch - We get the application owner id - The person who submitted not the logged in user like in 
             * the frontend share module
             */
            $owner_id = $otbhelper->getApplicationOwner($request->getParameter("id")) ;
			$user_share=Doctrine_Core::getTable('sfGuardUserProfile')->findByUserId($request->getParameter("architect"));

            //call function that checks if the appliaction is already shared with selected user
            if($otbhelper->checkifAppShared($request->getParameter("architect"), $request->getParameter("id"))== "shared"){
                //true
               // error_log("True >>>>>>>>") ;
                //$this->getUser()->setFlash('share_error_exists', "share error exists") ;
				sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
				$this->getUser()->setFlash('shared_error',$user_share);
				$this->redirect("/backend.php/share/share/id/".$request->getParameter('id')."");
            }
            else if($otbhelper->checkifAppShared($request->getParameter("architect"), $request->getParameter("id"))== "not_shared") {
                //false
                $share = new FormEntryShares();
                $share->setSenderid($owner_id);
                $share->setReceiverid($request->getParameter("architect"));
                $share->setFormentryid($request->getParameter("id"));
                 //our own custom shared by
                $share->setSharedBy($this->getUser()->getAttribute('userid')) ;
                $share->setUpdatedBy($this->getUser()->getAttribute('userid')) ;
                //date
                $time = new DateTime() ;                
                $share->setCreatedAt($time->format("Y-m-d h:i:s")) ;
                $share->setUpdatedAt($time->format("Y-m-d h:i:s")) ;
                //backend user with permission may deny frontend user access to a shared application
                $share->setStatus("active") ;
                
                $share->save();
				//Check if sub_menus is set to automatically send to stage
				if($this->application->getStage()->getSharedStageMove() && $otbhelper->isSharedStage($this->application->getApproved())){
					//move applicatiion
					$this->application->setApproved($this->application->getStage()->getSharedStage());
					$this->application->save();
				}
				//Notification
				$notify=new mailnotifications();
				$body="<p>Application ".$this->application->getApplicationId()." has been shared with you (".$user_share[0]['fullname']." (".$user_share[0]['email'].")).</p><p>Kindly login to ".sfConfig::get('app_organisation_name')." ".sfConfig::get('app_organisation_description')." to proceed";
				//error_log('--------------BODY------'.$body);
				$notify->sendemail('',$user_share[0]['email'],'Shared Application',$body);
                $this->getUser()->setFlash('shared', "shared") ;
            }
            else {
                //uknown
                $this->getUser()->setFlash('uknown_share_error', "unknown") ;
            }
           /* */
            $this->redirect("/backend.php/share/shared/id/".$request->getParameter('id')."");
        }
        $this->setLayout("layout");
    }

    /**
     * Executes 'Shared' action
     *
     * Shows success message if application is shared successfully
     *
     * @param sfRequest $request A request object
     */
    public function executeShared(sfWebRequest $request) {
        $this->id = $request->getParameter('id') ;
        $q = Doctrine_Query::create()
                ->from('FormEntryShares f')
                ->where('f.shared_by = ?',$this->getUser()->getAttribute('userid'))
                ->orderBy('f.created_at DESC')
                ->limit(1000) ;
        $this->results = $q->execute();
        $this->setLayout("layout");
    }
    /**
     * Unshare or deactivate a shared application if it was shared by mistake
     */
    public function executeCancel(sfWebRequest $request) {
       $id = $request->getParameter('id') ; // record id to cancel
       $q = Doctrine_Query::create()
               ->update('FormEntryShares f')
               ->set('f.status','?','inactive') // set to inactive
               ->where('f.id = ? ', $id) ;
       $res = $q->execute(); 
       //
        $this->redirect("/backend.php/share/shared");
    }
    /**
     * Activate applicatiion shared
     */
    public function executeActivate(sfWebRequest $request){
        $id = $request->getParameter('id') ; // record id to cancel
       $q = Doctrine_Query::create()
               ->update('FormEntryShares f')
               ->set('f.status','?','active') // set to active
               ->where('f.id = ? ', $id) ;
       $res = $q->execute(); 
       //
        $this->redirect("/backend.php/share/shared");
    }
	//OTB ADD
	public function executeSharemove(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
		->from('FormEntry a')
		   ->where('a.id = ?', $request->getParameter("id"));
		$this->application = $q->fetchOne();
		//Check if stage is set for share
		$otbhelper = new OTBHelper() ;
		$this->forward404Unless($otbhelper->isSharedStage($this->application->getApproved()),'Can\'t share application! Application needs to be on a allowed share stage');
        if($otbhelper->checkifAppShared($request->getParameter("architect"), $request->getParameter("id"))== "shared"){
			if($this->application->getStage()->getSharedStageMove() && $otbhelper->isSharedStage($this->application->getApproved())){
				//move application
				$this->application->setApproved($this->application->getStage()->getSharedStage());
				$this->application->save();
			}
			$this->redirect("/backend.php/share/shared");
		}else{
			$this->getUser()->setFlash('shared_error',htmlentities('<p>Application wasn\'t shared with the user!</p>'));
			$this->redirect("/backend.php/share/share");
		}
		
	}
	//OTB END
}
