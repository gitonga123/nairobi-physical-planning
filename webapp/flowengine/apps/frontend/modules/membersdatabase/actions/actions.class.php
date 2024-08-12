<?php
class membersdatabaseActions extends sfActions
{
	#OTB Start: Resend Boraqs Verification Email
	public function executeResendboraq(sfWebRequest $request)
	{
		$membersManager = new MembersManager();
		$member = $membersManager->MembershipIsValidated($this->getUser()->getGuardUser()->getId());
		$membersManager->sendMemberVerificationMail($member['member_no'], $member['category'],$member['database']);
		$this->getUser()->setAttribute('boraqs_reset','Member Verification email(s) sent!');
		$this->redirect('/plan//forms/groups/');
	}
	#OTB End: Resend Boraqs Verification Email

  public function executeConfirm(sfWebRequest $request)
  {
    $validate = $this->request->getParameter('validate');
	$memberships=Doctrine_Core::getTable('MembersDatabase')->findByValidate($validate);
	if(count($memberships) == 0){
		$this->valid=0;
	}else{
		$this->valid=1;
		foreach($memberships as $membership){
			Doctrine_Core::getTable('MembersDatabase')->findById($membership['id'])->delete();
			// check the single use column
			$categories = Doctrine_Core::getTable('SfGuardUserCategories')->findByFormId($membership['form_id']);

			foreach ($categories as $category) {
				$element = $category->getMemberDatabaseMemberOneSingleUse();
				if ($element) {
					$query = "UPDATE ap_form_".$membership['form_id'] . " SET element_".$element."_1 = 1". " WHERE id = ".$membership['entry_id'];

					$conn = Doctrine_Manager::getInstance()->getCurrentConnection();
					$conn->execute($query);
				}
			}
		}
	}
	if($this->getUser()->isAuthenticated()){
		if($this->valid){
			$this->getUser()->setFlash('notice', 'Membership has been verified');

			$this->redirect('@dashboard');
		}else{
			$this->getUser()->setFlash('notice','Verification error. Might be the Membership has already been verified.');
			$this->redirect('@dashboard');
		}
	} else {
		$this->redirect('/plan//dashboard',200);
	}

	$this->setLayout("layoutmentordash");
	
  }

}
