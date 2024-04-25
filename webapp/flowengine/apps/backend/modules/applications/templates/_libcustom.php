<?php
	/**
	 * _libcustom.php partial.
	 *
	 * Contains custom functions unrelated to external libraries
	 *
	 * @package    backend
	 * @subpackage applications
	 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
	 */

    /**
	*
	* Function to get all the days between a period of time
	*
	* @param String $sStartDate Start date to begin fetching dates from
	* @param String $sEndDate End date where to stop fetching dates from
	*
	* @return String[]
	*/
	function GetDays($sStartDate, $sEndDate){
		$aDays[] = $start_date;
		$start_date  = $sStartDate;
		$end_date = $sEndDate;
		$current_date = $start_date;
		while(strtotime($current_date) <= strtotime($end_date))
		{
			$aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
			$current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
		}
	 	return $aDays;
	}

	/**
	* Executes 'UpdateIdentifier' action
	*
	* Check for application number/identifier change
	*
	* @param Application $request Submitted Application
	*/
	function UpdateIdentifier($application)
	{
		//Check for application number/identifier change
		$q = Doctrine_Query::create()
		  ->from('AppChange a')
		  ->where('a.stage_id = ? AND a.form_id = ?', array($application->getApproved(),$application->getFormId()));
		$appchange = $q->fetchOne();

		if(!empty($appchange))
		{
			$app_identifier = $appchange->getAppIdentifier();
			$identifier_type = $appchange->getIdentifierType();
			$identifier_start = $appchange->getIdentifierStart();
			$query = "SELECT * FROM form_entry WHERE application_id LIKE '%INV-%' AND id = ".$application->getId()."";
			$max_results = do_query($query);

			if(mysql_num_rows($max_results) > 0)
			{
				//If this application already has the identifier then skip
				$pos = strpos($application->getFormId(),$app_identifier);
				//string needle found in haystack

				if($identifier_type == "0") //Pick First Letter of Field, Increment
				{
					$app_identifier = parse($application->getFormId(), $application->getEntryId(), $app_identifier, $identifier_type);
					$new_app_id = $app_identifier;

					//Get the last form entry record
					$query = "SELECT * FROM form_entry WHERE application_id LIKE '%".$app_identifier."%' ORDER BY application_id DESC LIMIT 1";
					$max_results = do_query($query);

					if(mysql_num_rows($max_results) > 0)
					{
						$last_row = mysql_fetch_assoc($max_results);
						$last_id = $last_row['application_id'];
						$new_app_id = ++$last_id;
					}
					else
					{
						$new_app_id = $new_app_id.$identifier_start;
					}
				}

					if($identifier_type == "1") //Pick Whole Field, Increment
					{
						$app_identifier = parseFull($formentry->getFormId(), $formentry->getEntryId(), $app_identifier);
						$new_app_id = $app_identifier.$identifier_start;
						$new_app_id = ++ $new_app_id;
					}

					if($identifier_type == "2") //Pick First Letter of Field, Don't Increment
					{
						$app_identifier = parse($formentry->getFormId(), $formentry->getEntryId(), $app_identifier, $identifier_type);
						$new_app_id = $app_identifier.$identifier_start;
					}

					if($identifier_type == "3") //Pick Whole Field, Don't Increment
					{

						$app_identifier = parseFull($formentry->getFormId(), $formentry->getEntryId(), $app_identifier);
						$new_app_id = $app_identifier;
					}

					$application->setApplicationId($new_app_id);
					$application->save();
				}
		}
	}
?>
