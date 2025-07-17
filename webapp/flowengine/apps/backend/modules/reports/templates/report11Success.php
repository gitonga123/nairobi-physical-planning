<?php
/**
 * report11 template.
 *
 * Report of the data integrity of all archived Construction Permit Requests.
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


$q = Doctrine_Query::create()
	->from('SubMenus a')
	->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
	->orderBy('a.order_no ASC');
$stages = $q->execute();

?>

<div class="g12" style="padding-left: 3px;">
			<form style="margin-bottom: 0px;" method="post" action="/plan/reports/printreport11" autocomplete="off" data-ajax="false">
			<label style='height: 30px; margin-top: 0px;'>
			<div style='float: left; font-size: 20px; font-weight: 700;'>Checksums Reports</div>
            <div style="float: right;">
            <?php
            	foreach($stages as $stage)
				{
					if($_POST['pending_stage'][$stage->getId()] == 'true')
					{
						echo "<input type='hidden' name='pending_stage[".$stage->getId()."]' value='true'>";
						$filstages[] = $stage->getId();
						if($count == 0)
						{
							$filtags = $filtags."a.approved = ? ";
						}
						else
						{
							$filtags = $filtags."OR a.approved = ? ";
						}
					$count++;
					}
				}
				?>
                <button class="submit" style="margin-top: -5px;">Export to Excel</button>
            </div>
            </label>
			<fieldset style="margin: 0px;">

 <div class="g12">
			
						
			<table>
				<thead>
					<tr>
			         <th>Type</th><th>No</th><th>Submitted By</th><th>Checksum</th><th>Status</th><th style="background: none;">Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$filstages = "";
				
				$filtags = "";
				
				$count = 0;
				foreach($stages as $stage)
				{
					if($_POST['pending_stage'][$stage->getId()] == 'true')
					{
						echo "<input type='hidden' name='pending_stage[".$stage->getId()."]' value='true'>";
						$filstages[] = $stage->getId();
						if($count == 0)
						{
							$filtags = $filtags."a.approved = ? ";
						}
						else
						{
							$filtags = $filtags."OR a.approved = ? ";
						}
					$count++;
					}
				}
				
						$q = Doctrine_Query::create()
						  ->from('FormEntry a')
						  ->where($filtags, $filstages)
				          ->andWhere("a.approved <> ? AND a.approved <> ? AND a.parent_submission = ?", array("0","","0"));
						$applications = $q->execute();
				
						foreach($applications as $application)
						{
						
							$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
							mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
							$query = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = '".$application->getEntryId()."'";
							$result = mysql_query($query,$dbconn);

							$application_form = mysql_fetch_assoc($result);
				?>
					
						<tr id="row_<?php echo $application->getId() ?>">
						<td><?php 
						$q = Doctrine_Query::create()
						     ->from('ApForms a')
							 ->where('a.form_id = ?', $application->getFormId());
					    $form = $q->fetchOne();
						if($form)
						{
							echo $form->getFormName();
						}
						else
						{
							echo "-";
						}
						?></td>
						<td><?php echo $application->getApplicationId(); ?></td>
						<td class="c">
						<?php
							$q = Doctrine_Query::create()
						     ->from('sfGuardUserProfile a')
							 ->where('a.user_id = ?', $application->getUserId());
					    $userprofile = $q->fetchOne();
						if($userprofile)
						{
							echo $userprofile->getFullname();
						}
						else
						{
							echo "-";
						}
						?>
						</td>
						<td><?php
							$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
							mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
							
						
							//Get checksum
							$sql = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ".$application->getEntryId();
							$ck_result = mysql_query($sql);
							$ck_row = mysql_fetch_assoc($ck_result);							
							
							$ck_string = "";
							
							$q = Doctrine_Query::create()
								->from('ApFormElements a')
								->where('a.form_id = ?', $application->getFormId());
							$elements = $q->execute();
							
							foreach($elements as $element)
							{
								$ck_string = $ck_string.$ck_row['element_'.$element->getElementId()];
							}
							
							$q = Doctrine_Query::create()
							   ->from('Checksum a')
							   ->where('a.entry_id = ?', $application->getId());
							$checksums = $q->execute();
							$str_checksum = "";
							foreach($checksums as $checksum)
							{
								$str_checksum = $checksum->getChecksum();
							}
						
							if(md5($ck_string) == $str_checksum)
							{
								echo "<img src='/assets_backend/images/flag_green.gif'> Secure";
							}
							else
							{
								echo "<img src='/assets_backend/images/flag_red.gif'> Invalid";
							}
						?></td>
						<td class="c">
						<?php
							 $q = Doctrine_Query::create()
								->from('SubMenus a')
								->where('a.id = ?', $application->getApproved());
							$submenu = $q->fetchOne();
							echo $submenu->getTitle();
						?>
						</td>
						<td class="c">
						<a title='View Application' href='/plan/applications/viewreference/id/<?php echo $application->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a>
						</td>
					</tr>
				<?php
						}
				?>
				</tbody>
			</table>

			
			</fieldset>
			</form>
	</div>