<?php
/**
 *
 * Class for managing Member Associations
 *
 */

class MembersManager {

    public function __construct()
    {
        $this->suffix = empty($_SERVER['HTTPS']) ? "http://" : "https://";
    }

	//Start OTB Patch - Check for Membership
	public function sendMemberVerificationMail($reg_no,$usercategory,$memberships)
	{
		//Save validation
		$validation_array=array();
		$members_email=array();
		$members_name=array();
		$user_id = $_SESSION['symfony/user/sfUser/attributes']['sfGuardSecurityUser']['user_id'] ? $_SESSION['symfony/user/sfUser/attributes']['sfGuardSecurityUser']['user_id'] : $_SESSION['new_userid'];
		$members_db=Doctrine_Core::getTable('MembersDatabase')->createQuery('m')->where('m.form_id = ? AND m.user_id = ?',array($usercategory['member_database'],$user_id))->fetchArray();
		//error_log('----Membership ---');
		//error_log(print_r($memberships,true));
		foreach($memberships as $membership){
			//Check if the validator exist in MembersDatabase incase of resending
			$found_array=array('found' => false,'validation' => null);
			foreach($members_db as $m){
				if($m['entry_id'] == $membership['id']){
					$found_array['found']=true;
					$found_array['validation']=$m['validate'];
				}
			}
			if(!$found_array['found']){
				//Set Validator
				$validate=rand(1000000,9999999);
				$validation_array[]=$validate;
				$members_email[]=$membership['element_'.$usercategory['member_database_member_email_field']];
				$members_name[]=$membership['element_'.$usercategory['member_database_member_name_field']];
				$membership_db=new MembersDatabase();
				$membership_db->setFormId($usercategory['member_database']);
				$membership_db->setEntryId($membership['id']);
				$membership_db->setUserId($user_id);
				$membership_db->setValidate($validate);
				$membership_db->save();
				//Doctrine_Core::getTable('MembersDatabase')->find($membership['id'])->setValidate($validate)->save();
			}else{
				//Set values
				$validation_array[]=$found_array['validation'];
				$members_email[]=$membership['element_'.$usercategory['member_database_member_email_field']];
				$members_name[]=$membership['element_'.$usercategory['member_database_member_name_field']];
			}
		}
		$user_prof=Doctrine_Core::getTable('SfGuardUserProfile')->findByUserId($user_id);
		foreach($validation_array as $k => $v){
		 $to = trim($members_email[$k]);
		 $subject = $usercategory['member_association_name']." Verification";
		 $message = str_replace("{member_full_name}", $members_name[$k], $usercategory['member_email_verification_message']);
		 $message = str_replace("{user_full_name}", $user_prof[0]['fullname'], $message);
		 $message = str_replace("{association}", $usercategory['member_association_name'], $message);
		 $message = str_replace("{membership_no}", $reg_no, $message);
		 $validation_link = "<a href='".$this->suffix.$_SERVER['HTTP_HOST']."/index.php/membersdatabase/confirm/validate/".$v."'>".$this->suffix.$_SERVER['HTTP_HOST']."/index.php/membersdatabase/confirm/validate/".$v."</a>";
		 $message = str_replace("{validation_link}", $validation_link, $message);
		 
		 //error_log("userid ##".$user_id."   ".$user_prof[0]['fullname'].print_R($usercategory, true)."email message ###".$message);

		  $headers = "";
		  $headers .= "Reply-To: ".sfConfig::get('app_organisation_name')." <".sfConfig::get('app_organisation_email').">\r\n";
		  $headers .= "Return-Path: ".sfConfig::get('app_organisation_name')." <".sfConfig::get('app_organisation_email').">\r\n";
		  $headers .= "From: ".sfConfig::get('app_organisation_name')." <".sfConfig::get('app_organisation_email').">\r\n";
		  $headers .= "Organization: ".sfConfig::get('app_organisation_name')."\r\n";
		  $headers .= "MIME-Version: 1.0\r\n";
		  $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		  $headers .= "X-Priority: 3\r\n";
		  $headers .= "X-Mailer: PHP". phpversion() ."\r\n";
		  //error_log('--TO '.$to.' Subject-- '.$subject.'---message-- '.$message);
		  $notification=new mailnotifications();
			$notification->sendemail('',$to,$subject,$message);

		  //mail($to,$subject,$message,$headers);
		}
	}
	
	public function MembershipIsValidated($user_id){
		$q = Doctrine_Query::create()
		   ->from("SfGuardUserProfile a")
		   ->where("a.user_id = ?", $user_id);
		$loggedInSfUserProfile = $q->fetchOne();

		if($loggedInSfUserProfile and $loggedInSfUserProfile->getRegisteras()){
			$q = Doctrine_Query::create()
			   ->from("SfGuardUserCategories a")
			   ->where("a.id = ?", $loggedInSfUserProfile->getRegisteras());
			$UserCategory = $q->fetchOne();
			$UserCategoryAssoc = $q->fetchArray()[0];
		}
		
		$q = Doctrine_Query::create()
		   ->from("MfUserProfile a")
		   ->where("a.user_id = ?", $user_id);
		$loggedInMfUserProfile = $q->fetchOne();

		if($loggedInMfUserProfile and $UserCategory and $UserCategory->getMemberNoElementId()){
			$details_query = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT element_".$UserCategory->getMemberNoElementId()." FROM ap_form_".$loggedInMfUserProfile->getFormId()." WHERE id = ".$loggedInMfUserProfile->getEntryId());
		}
		$member_no='';
		if ($details_query){
			$member_no = trim($details_query[0]['element_'.$UserCategory->getMemberNoElementId()]);
			$query_member_db = "SELECT ";
			$query_member_db.="id,element_".$UserCategory->getMemberDatabaseMemberNoField()." , element_".$UserCategory->getMemberDatabaseMemberEmailField();
			$query_member_db.=" FROM ap_form_".$UserCategory->getMemberDatabase();
			$query_member_db.=" WHERE element_".$UserCategory->getMemberDatabaseMemberNoField()." = '$member_no'";
			$memberships=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query_member_db);
			$non_validated=array();
			foreach($memberships as $member){
				//Check if the entry id exist in table members_database
				$q = Doctrine_Query::create()
				   ->from("MembersDatabase a")
				   ->where("a.form_id = ? AND a.entry_id = ? AND a.user_id = ?", array($UserCategory->getMemberDatabase(),$member['id'],$user_id));
				$member_validation=$q->fetchArray();
				if(count($member_validation)){
					//Entries not validated
					$non_validated[]=$member_validation[0]['id'];
				}
			}
		}
		if($loggedInMfUserProfile && count($non_validated) == 0 or !$UserCategory->getMemberNoElementId()){
			return array('member_no'=>$member_no, 'validated'=>true, 'category'=>$UserCategoryAssoc,'database' => $memberships);
		}else{
			return array('member_no'=>$member_no, 'validated'=>false, 'category'=>$UserCategoryAssoc,'database' => $memberships,'entries' => $non_validated);
		}
	}
	//End OTB Patch - Check for Membership
}
