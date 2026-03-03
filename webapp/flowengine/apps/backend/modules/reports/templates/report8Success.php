<?php
/**
 * report8 template.
 *
 * Report of all inspection carried out by a specific reviewer
 *
 * @package    backend
 * @subpackage reports
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 
/**
*
* Function to get all the dates between a period of time
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

?> 

<div class="g12" style="padding-left: 3px;">
			<form style="margin-bottom: 0px;">
			<label style='height: 30px; margin-top: 0px;'>
			<div style='float: left; font-size: 20px; font-weight: 700;'>Inspections Reports</div>
             <div style="float: right;">
                <a href="/plan/reports/printreport8/form/<?php echo $_POST['reviewer']; ?>/startdate/<?php echo $fromdate; ?>/enddate/<?php echo $todate; ?>"><u>Export to Excel</u></a>
             </div>
            </label>
			<fieldset style="margin: 0px;">

		
 <div class="g12">
			
			
	<?php
	
	
	$days = GetDays($fromdate, $todate); 

	$tableHeaders = "";
	$tableRows = "";
	
	$tableRows[] = "Inspection";
	
	//If < 15 (days)
	if(sizeof($days) <= 15)
	{
		foreach($days as $day)
		{
		   if(strlen($day) > 0)
		   {
				$tableHeaders[] = "<th>".$day."</th>";
				$q = Doctrine_Query::create()
					->from('Task a');
					 
					if($reviewer != "0")
					{
							 $q->where('a.owner_user_id = ?', $reviewer);
					}
			
				$q->andWhere('a.type = ?', '6');
				$q->andWhere('a.start_date = ?', $day);
				$tasks = $q->execute();
				$tableRows[] = "<td>".sizeof($tasks)."</td>";
			}
		}
    }
	else if(sizeof($days) > 15 && sizeof($days) <= 40)//If < 40 (weeks)
	{
		$dayofweek = 1;
		$total = 0;
		$header = "";
		foreach($days as $day)
		{
		  if(strlen($day) > 0)
		  {
		    if($dayofweek == 1)
			{
				$header = $day;
			}
			if($dayofweek == 7 || (sizeof($days) == $total))
			{
				$q = Doctrine_Query::create()
					->from('Task a');
					 
					if($reviewer != "0")
					{
							 $q->where('a.owner_user_id = ?', $reviewer);
					}
			
				$q->andWhere('a.type = ?', '6');
				$q->andWhere('a.start_date BETWEEN ? AND ?', array($header,$day));
				$tasks = $q->execute();
				$header = $header." to ".$day;
				$tableHeaders[] = "<th>".$header."</th>";
				$tableRows[] = "<td>".sizeof($tasks)."</td>";
				$dayofweek = 0;
			}
			$total++;
			$dayofweek++;
		   }
		}
	}
	else if(sizeof($days) > 40 && sizeof($days) <= 365)//If < 12 (months)
	{
		$dayofmonth = 1;
		$total = 0;
		$header = "";
		foreach($days as $day)
		{
		  if(strlen($day) > 0)
		  {
		    if($dayofmonth == 1)
			{
				$header = $day;
			}
			if($dayofmonth == 30 || (sizeof($days) == $total))
			{
				$dayquery = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created BETWEEN '".$header."' AND '".$day."'";
				$q = Doctrine_Query::create()
					->from('Task a');
					 
					if($reviewer != "0")
					{
							 $q->where('a.owner_user_id = ?', $reviewer);
					}
			
				$q->andWhere('a.type = ?', '6');
				$q->andWhere('a.start_date BETWEEN ? AND ?', array($header,$day));
				$tasks = $q->execute();
				$header = $header." to ".$day;
				$tableHeaders[] = "<th>".$header."</th>";
				$tableRows[] = "<td>".sizeof($tasks)."</td>";
				$dayofmonth = 0;
			}
			$total++;
			$dayofmonth++;
		   }
		}
	}
	else if(sizeof($days) > 365)//If > 12 (years)
	{
		$dayofyear = 1;
		$total = 0;
		$header = "";
		foreach($days as $day)
		{
		  if(strlen($day) > 0)
		  {
		    if($dayofyear == 1)
			{
				$header = $day;
			}
			if($dayofyear == 365 || (sizeof($days) == $total))
			{
				$dayquery = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created BETWEEN '".$header."' AND '".$day."'";
				$q = Doctrine_Query::create()
					->from('Task a');
					 
					if($reviewer != "0")
					{
							 $q->where('a.owner_user_id = ?', $reviewer);
					}
			
				$q->andWhere('a.type = ?', '6');
				$q->andWhere('a.start_date BETWEEN ? AND ?', array($header,$day));
				$tasks = $q->execute();
				$header = $header." to ".$day;
				$tableHeaders[] = "<th>".$header."</th>";
				$tableRows[] = "<td>".sizeof($tasks)."</td>";
				$dayofyear = 0;
			}
			$total++;
			$dayofyear++;
		   }
		}
	}
		
	?>
				
			<table class="chart">
			<thead>
				<tr>
				    <th></th>
					<?php
					foreach($tableHeaders as $tableheader)
					{
						echo $tableheader;
					}
					?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<?php
					foreach($tableRows as $row)
					{
						echo $row;
					}
					?>
				</tr>
			</tbody>
		</table>
		
			
            <table>
				<thead>
					<tr>
					<th>Task Type</th><th>Application</th><th>Task Started On</th><th>Task Ended On</th><th>Task Assigned By</th><th>Task Status</th><th style="background:none;">Action</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach($tasks as $task)
				{
					$q = Doctrine_Query::create()
					 ->from('FormEntry a')
					 ->where('a.id = ?', $task->getApplicationId())
				     ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0","","0"));
					$application = $q->fetchOne();
					?>
					<tr>
					<td style="background-color: <?php
						if($task->getType() == "1")
						{
							echo "#f0a8a8";
						}
						else if($task->getType() == "2")
						{
							echo "#a2e8a2";
						}
						else if($task->getType() == "6")
						{
							echo "#e8e8a2";
						}
						else if($task->getType() == "3")
						{
							echo "#a8f0f0";
						}
						else if($task->getType() == "4")
						{
							echo "#f0a8f0";
						}
						else if($task->getType() == "5")
						{
							echo "#aea8f0";
						}
						else
						{
							echo "#cfccf0";
						}
				?>"><?php 
						if($task->getType() == "1")
						{
							echo "Review";
						}
						if($task->getType() == "2")
						{
							echo "Commenting";
						}
						if($task->getType() == "6")
						{
							echo "Inspection";
						}
						if($task->getType() == "3")
						{
							echo "Invoicing";
						}
						if($task->getType() == "4")
						{
							echo "Scanning";
						}
						if($task->getType() == "5")
						{
							echo "Collection";
						}
						?></td><td><?php echo $application->getApplicationId(); ?></td><td><?php echo $task->getStartDate(); ?></td><td><?php echo $task->getEndDate(); ?></td><td><?php
							$q = Doctrine_Query::create()
						     ->from('CfUser a')
							 ->where('a.nid = ?', $task->getCreatorUserId());
					    $reviewer = $q->fetchOne();
						if($reviewer)
						{
							echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
						}
						else
						{
							echo "-";
						}
						?></td><td><?php
							if($task->getStatus() == "1")
							{
								echo "Pending";
							}
							else if($task->getStatus() == "2")
							{
								echo "Completing";
							}
							else if($task->getStatus() == "25")
							{
								echo "Completed";
							}
							else if($task->getStatus() == "5")
							{
								echo "Cancelling";
							}
							else if($task->getStatus() == "55")
							{
								echo "Cancelled";
							}
							else if($task->getStatus() == "3")
							{
								echo "PostPoned";
							}
							else if($task->getStatus() == "4")
							{
								echo "Transferring";
							}
							else if($task->getStatus() == "45")
							{
								echo "Transferred";
							}
						?></td><td style="background:none;"><a title='View Task' href='/plan/tasks/view/id/<?php echo $task->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a></td>
						</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			
			
			</fieldset>
			</form>
	</div>
	