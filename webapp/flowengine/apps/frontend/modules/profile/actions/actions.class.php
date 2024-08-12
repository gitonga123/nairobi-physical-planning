<?php

/**
 * profile actions.
 *
 * @package    permitflow
 * @subpackage profile
 * @author     Your name here
 * @version    SVN: $Id$
 */
class profileActions extends sfActions
{
  /**
   * Executes create action
   *
   * @param sfRequest $request A request object
   */
  public function executeCreate(sfWebRequest $request)
  {
    $this->category = Functions::get_client_category();

    $this->setLayout("layoutdash");
  }

  /**
   * Executes confirm action
   *
   * @param sfRequest $request A request object
   */
  public function executeConfirm(sfWebRequest $request)
  {
    $this->setLayout("layoutdash");
  }

  /**
   * Executes payment action
   *
   * @param sfRequest $request A request object
   */
  public function executePayment(sfWebRequest $request)
  {
    $this->category = Functions::get_client_category();

    $this->setLayout("layoutdash");
  }

   /**
   * View the client's profile
   *
   * @param sfRequest $request A request object
   */
  public function executeView(sfWebRequest $request)
  {
    $this->profile = Functions::get_client_profile($request->getParameter("id"));

    if($this->profile == null)
    {
      $this->redirect("plan/profile/create");
    }

    //Update any pending profile activation payment
    $q = Doctrine_Query::create()
        ->from("ApFormPayments a")
        ->where("a.payment_id = ?", $this->profile->getFormId()."/".$this->profile->getEntryId()."/".$this->profile->getId())
        ->andWhere("a.payment_status = ?", "pending")
        ->orderBy("a.afp_id DESC")
        ->limit(1);

    if($q->count())
    {
        $invoice_manager = new InvoiceManager();
        $result = $invoice_manager->basic_remote_reconcile($this->profile->getFormId()."/".$this->profile->getEntryId()."/".$this->profile->getId());

        //If response is paid, then mark invoice as paid
        if($result == "paid")
        {
            $this->profile->setDeleted(0);
            $this->profile->save();
        }
    }

    $this->getUser()->setAttribute("current_profile", $request->getParameter("id"));

    $this->current_user = $this->getUser()->getGuardUser();

    $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->andWhere("a.business_id = ?", $this->profile->getId())
           ->orderBy("a.id DESC");

		$this->latest_services = new sfDoctrinePager('FormEntry', 5);
		$this->latest_services->setQuery($q);
		$this->latest_services->setPage($request->getParameter('apage', 1));
		$this->latest_services->init();

    $q = Doctrine_Query::create()
           ->from("MfInvoice a")
           ->leftJoin("a.FormEntry b")
           ->andWhere("b.business_id = ?", $this->profile->getId())   
           ->andWhere("a.paid <> 3")
           ->orderBy("a.created_at DESC");

		$this->latest_invoices = new sfDoctrinePager('MfInvoice', 5);
		$this->latest_invoices->setQuery($q);
		$this->latest_invoices->setPage($request->getParameter('mpage', 1));
		$this->latest_invoices->init();

    $q = Doctrine_Query::create()
        ->from("MfUserProfileShare a")
        ->where("a.profile_id = ?", $this->profile->getId())
        ->andWhere("a.deleted = 0");
    $this->users = $q->execute();

    $q = Doctrine_Query::create()
        ->from("MfUserProfileInspection a")
        ->where("a.profile_id = ?", $this->profile->getId())
        ->andWhere("a.deleted = 0");
    $this->inspections = $q->execute();
    
    $this->setLayout("layoutprofile");
  }

  /**
   * Edit the client's profile
   *
   * @param sfRequest $request A request object
   */
  public function executeEdit(sfWebRequest $request)
  {
    $this->profile = Functions::get_client_profile($request->getParameter("id"));
    
    $this->setLayout("layoutdash");
  }

  /**
   * Add another user to the profile
   *
   * @param sfRequest $request A request object
   */
  public function executeAdduser(sfWebRequest $request)
  {
    $this->profile = Functions::get_client_profile($request->getParameter("id"));

    $this->setLayout("layoutdash");
  }

  /**
   * Save another user to the profile
   *
   * @param sfRequest $request A request object
   */
  public function executeSaveuser(sfWebRequest $request)
  {
    $id_number = $request->getPostParameter("id_number");

    $profile_id = $this->getUser()->getAttribute("current_profile");

    $q = Doctrine_Query::create()
       ->from("SfGuardUser a")
       ->where("a.username = ?", $id_number);
    
    if($q->count() > 0)
    {
      $user = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from("MfUserProfileShare a")
        ->where("a.user_id = ? AND a.profile_id = ?", array($user->getId(), $profile_id))
        ->andWhere("a.deleted = 0");

      if($q->count() == 0)
      {
        $profile_share = new MfUserProfileShare();
        $profile_share->setProfileId($profile_id);
        $profile_share->setUserId($user->getId());
        $profile_share->setCreatedAt(date("Y-m-d"));
        $profile_share->setUpdatedAt(date("Y-m-d"));
        $profile_share->setDeleted(0);
        $profile_share->save();
      }

      $this->redirect("plan/profile/view/id/".$profile_id);

    }
    else 
    {
      $this->redirect("plan/profile/adduser/id/".$profile_id);
    }
  }

  /**
   * Delete a user from the profile
   *
   * @param sfRequest $request A request object
   */
  public function executeRemoveuser(sfWebRequest $request)
  {
    $profile_id = $this->getUser()->getAttribute("current_profile");

    $q = Doctrine_Query::create()
       ->from("SfGuardUser a")
       ->where("a.id = ?", $request->getParameter("id"));
    
    if($q->count() > 0)
    {
      $user = $q->fetchOne();

      $q = Doctrine_Query::create()
        ->from("MfUserProfileShare a")
        ->where("a.user_id = ? AND a.profile_id = ?", array($user->getId(), $profile_id))
        ->andWhere("a.deleted = 0");

      if($q->count() > 0)
      {
        $profile_share = $q->fetchOne();

        $profile_share->setDeleted(1);
        $profile_share->save();
      } 

      $this->redirect("plan/profile/view/id/".$profile_id);
    }

    $this->redirect("plan/profile/view/id/".$profile_id);
  }

   /**
   * View inspection sheet
   *
   * @param sfRequest $request A request object
   */
  public function executeInspection(sfWebRequest $request)
  { 
    $q = Doctrine_Query::create()
       ->from("MfUserProfileInspection a")
       ->where("a.id = ?", $request->getParameter("id"));
    $this->inspection = $q->fetchOne();

    $this->profile = Functions::get_client_profile($this->getUser()->getAttribute("current_profile"));

    $this->setLayout("layoutprofile");
  }
}
