<?php
class audit
{
	public function __construct()
	{

	}

	public static function audit($entryid, $action)
	{
		try{
			$audit = new AuditTrail();

			if(sfContext::getInstance()->getUser()->isAuthenticated())
			{
				$audit->setUserId(sfContext::getInstance()->getUser()->getAttribute('userid', 0));
			}
			else 
			{
				$audit->setUserId(0);
			}

			if($entryid != "")
			{
				$audit->setFormEntryId($entryid);
			}

			$client_ip = "";

			if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
				$client_ip =  $_SERVER["HTTP_X_FORWARDED_FOR"];
			}else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
				$client_ip = $_SERVER["REMOTE_ADDR"];
			}else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
				$client_ip = $_SERVER["HTTP_CLIENT_IP"];
			}

			$audit->setAction($action." <br> ".$_SERVER['HTTP_REFERER']);
			$audit->setActionTimestamp(date('Y-m-d g:i:s'));
			$audit->setIpaddress($client_ip);
			$audit->setHttpAgent($_SERVER['HTTP_USER_AGENT']);
			$audit->setUserLocation("-");
			$audit->save();
		}
		catch(Exception $ex)
		{
			error_log("Audit Error: ".$ex);
		}
	}

	public function saveAudit($entryid, $action)
	{
		try{
			$audit = new AuditTrail();

			if(sfContext::getInstance()->getUser()->isAuthenticated())
			{
				$audit->setUserId(sfContext::getInstance()->getUser()->getAttribute('userid', 0));
			}
			else 
			{
				$audit->setUserId(0);
			}

			if($entryid != "")
			{
				$audit->setFormEntryId($entryid);
			}
			$audit->setAction("<a href='http://".$_SERVER['HTTP_REFERER']."'>".$action."</a>");
			$audit->setActionTimestamp(date('Y-m-d g:i:s'));
			$audit->setIpaddress($this->getClientIP());
			$audit->setHttpAgent($_SERVER['HTTP_USER_AGENT']);
			$audit->setUserLocation("-");
			$audit->save();
		}
		catch(Exception $ex)
		{
			error_log("Audit Error: ".$ex);
		}
	}

	public function saveFullAudit($action,$id,$table,$before,$after,$appid = 0)
	{
		try{
			$audit = new AuditTrail();
			
			if(sfContext::getInstance()->getUser()->isAuthenticated())
			{
				$audit->setUserId(sfContext::getInstance()->getUser()->getAttribute('userid', 0));
			}
			else 
			{
				$audit->setUserId(0);
			}

			if($appid != "")
			{
				$audit->setFormEntryId($appid);
			}
			$audit->setAction($action);
			$audit->setActionTimestamp(date('Y-m-d g:i:s'));
			$audit->setObjectId($id);
			$audit->setObjectName($table);
			$audit->setBeforeValues($before);
			$audit->setAfterValues($after);
			$audit->setIpaddress($this->getClientIP());
			$audit->setHttpAgent($_SERVER['HTTP_USER_AGENT']);
 			$audit->setUserLocation("-");
			$audit->save();
		}catch(Exception $ex)
		{
			error_log("Audit Error: ".$ex);
		}
	}

	public function saveClientAudit($entryid, $action)
	{
		try{
			$audit = new AuditTrail();
			$audit->setUserId(0);
			$audit->setFormEntryId($entryid);
			$audit->setAction($action);
			$audit->setActionTimestamp(date('Y-m-d g:i:s'));
			$audit->setIpaddress($this->getClientIP());
			$audit->setHttpAgent($_SERVER['HTTP_USER_AGENT']);
      		$audit->setUserLocation("-");
			$audit->save();
		}
		catch(Exception $ex)
		{
			error_log("Audit Error: ".$ex->getMessage());
		}
	}

	public function getClientIP(){

   if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
          return  $_SERVER["HTTP_X_FORWARDED_FOR"];
      }else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
          return $_SERVER["REMOTE_ADDR"];
      }else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
          return $_SERVER["HTTP_CLIENT_IP"];
      }

      return '';

  }
}
?>
