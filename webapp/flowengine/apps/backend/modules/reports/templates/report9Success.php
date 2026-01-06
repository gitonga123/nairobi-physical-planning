<?php
/**
 * report9 template.
 *
 * Inspection History of an Application
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
			<div style='float: left; font-size: 20px; font-weight: 700;'>Inspection History Reports</div>
            </label>
			<fieldset style="margin: 0px;">
			<section>
				<div style="float: right; width: 98%;" align='right'>
				<button onClick="if(document.getElementById('filter').style.display == 'none'){ document.getElementById('filter').style.display = 'block'; }else{ document.getElementById('filter').style.display = 'none'; }">Filter</button>
				</div>
			</section>
			</fieldset>
			</form>

<div name='filter' id='filter' class="g12" style="display: none;">
	<form method="post" action="/backend.php/reports/report9" autocomplete="off" data-ajax="false">
			 <fieldset>
				<label>Filters</label>
				<section>
					<label>Status</label>
					<div style='height: 250px; overflow-y: auto;'>
						<?php
							$q = Doctrine_Query::create()
							  ->from('SubMenus a')
							  ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
							  ->orderBy('a.order_no ASC');
							$stages = $q->execute();
							
							foreach($stages as $stage)
							{
								echo "<input type='checkbox' name='pending_stage' id='pending_stage' value='".$stage->getId()."'>".$stage->getTitle()."<br>";
							}
						?>
					</div>
				</section>
				</fieldset>
				<fieldset>
						<section>
							<div><button class="reset">Reset</button><button class="submit" name="submitbuttonname" value="submitbuttonvalue">Submit</button></div>
						</section>
				</fieldset>
			</form>
	</div>
		
 <div class="g12">
			<p>
			<?php
			$q = Doctrine_Query::create()
			  ->from('SubMenus a')
			  ->where('a.id <> 0 AND a.id <> 650 AND a.id <> 750 AND a.id <> 850')
			  ->orderBy('a.order_no ASC');
			$stages = $q->execute();
			
			?>
			
						
			<table class="datatable">
				<thead>
					<tr>
			         <th>Type</th><th>No</th><th>Submitted On</th><th>Submitted By</th><th>Status</th><th style="background: none;">Actions</th>
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
							if(!$sf_user->mfHasCredential("accesssubmenu".$application->getApproved()))
							{
								continue;
							}
						
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
						<td><?php
							echo $application_form['date_created'];
						?></td>
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
						<a title='View Application' href='/backend.php/reports/viewinspections/id/<?php echo $application->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a>
						</td>
					</tr>
				<?php
						}
				?>
				</tbody>
			</table>

			
			</p>
	</div>