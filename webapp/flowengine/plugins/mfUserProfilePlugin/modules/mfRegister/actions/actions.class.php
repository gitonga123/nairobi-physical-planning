<?php
class mfRegisterActions extends sfActions
{
  public function executeRegisterDetails(sfWebRequest $request)
  {
    
    $this->formid = $request->getParameter("formid");
	  $this->userid = $request->getParameter("userid");
    $this->userid = $this->getUser()->getAttribute('new_user_id');
    $this->setLayout("layoutmentorregister");

    $q = Doctrine_Query::create()
       ->from("SfGuardUser a")
       ->where("a.id = ?", $this->userid);
    $current_user = $q->fetchOne();

    if($current_user)
    {
      $current_user->setIsActive(true);
      $current_user->save();
    }
  }
  public function executeRegisterDetails2(sfWebRequest $request)
  {
    $this->formid = $request->getParameter("formid");
	  $this->userid = $request->getParameter("userid");
    $this->setLayout("layoutmentorregister");
  }
  public function executeNotification(sfWebRequest $request)
  {
    $this->setLayout("layoutmentorregister");
  }
}
?>
