<?php
/**
 * reportcustom2 template.
 *
 * Custom built-in report developed for Kigali Construction Permit Management System
 *
 * @package    backend
 * @subpackage reports
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 
/**
*
* Function to get all the dates between a period
*
* @param String $sStartDate Starting date to begin fetching dates from
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



$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);

?> 

<div class="g12" style="padding-left: 3px; ">
	<form style="margin-bottom: 0px;">
		<label style='height: 30px; margin-top: 0px;'>
		<div style='float: left; font-size: 20px; font-weight: 700;'>Report Two: <?php 
		 {
			 echo "Report";
		 }
		
		 ?></div>
         <div style="float: right;">
            <a href="/plan/reports/printcustom2/form/<?php echo $application_form; ?>/startdate/<?php echo $startdate; ?>/enddate/<?php echo $enddate; ?>"><u>Export to Excel</u></a>
         </div>
         </label>
			<table>
				<thead>
					<tr>
						<th>No</th>
						<th>Name of the project/developer</th>
						<th>Contact no</th>
						<th>Date of submission</th>
						<th>Date of response</th>
						<th>Date of CP issued</th>
						<th>Number of days taken</th>
						<th>Observation</th>
					</tr>
				</thead>
				<tbody>
				
				<?php
				    
				    /**
					* Fetch all applications linked to the filtered 'type of application' and the 'start date'
					*/
						
					$days = GetDays($startdate, $enddate); 
					
					//Iterate through all days and fetch applications submitted on each of those dates.
					$count = 0;
					foreach($days as $day)
					{
				                if($day == "")
				                {
				                        continue;
				                }
						$query = "SELECT a.* FROM ap_form_".$application_form."  a LEFT JOIN form_entry b ON a.id = b.entry_id WHERE a.date_created LIKE '%".$day."%' AND b.form_id = ".$application_form." AND b.approved <> 0 AND b.approved <> 897 AND b.parent_submission = 0";
						
						$results = mysql_query($query,$dbconn);

						if(mysql_num_rows($results) == 0 && $application_form == 60)
						{
							$query = "SELECT a.* FROM ap_form_23  a LEFT JOIN form_entry b ON a.id = b.entry_id WHERE a.date_created LIKE '%".$day."%' AND b.form_id = 23 AND b.approved <> 0 AND b.approved <> 897 AND b.parent_submission = 0";
					
					        $results = mysql_query($query,$dbconn);
							while($row = mysql_fetch_assoc($results))
							{
								include_partial('rowreportcustom2', array('row' => $row, 'application_form' => 23,'count'=>$count));
						        $count++;
							}
						}
						else
						{
							while($row = mysql_fetch_assoc($results))
							{
								include_partial('rowreportcustom2', array('row' => $row, 'application_form' => $application_form,'count'=>$count));
						        $count++;
							}
						}
					}
				?>
				</tbody>
			</table>
		</fieldset>
	</form>
</div>
