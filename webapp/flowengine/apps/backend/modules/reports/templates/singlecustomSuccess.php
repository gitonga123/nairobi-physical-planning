<?php
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
			<div style='float: left; font-size: 20px; font-weight: 700;'><?php echo $report->getTitle(); ?></div></label>
			<fieldset style="margin: 0px;">
			<section>
				<div style="float: right; width: 98%;" align='right'>
				<button onClick="if(document.getElementById('filter').style.display == 'none'){ document.getElementById('filter').style.display = 'block'; }else{ document.getElementById('filter').style.display = 'none'; }">Filter</button>
				</div>
			</section>
			</fieldset>
			</form>

<div name='filter' id='filter' class="g12" style="display: none;">
	<form method="post" action="/plan/reports/report1" autocomplete="off" data-ajax="false">
			 <fieldset>
				<label>Filters</label>
				<section>
				<label>Type Of Application</label>
				<div>
				<select id='application_form' name='application_form' onChange="ajaxSearchform(this.value);">
			    <option value="0">All</option>
				<?php
				
					$q = Doctrine_Query::create()
					  ->from('ApForms a')
					  ->where('a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ?',array('6','7','15','16','17'))
					  ->orderBy('a.form_name ASC');
					$forms = $q->execute();
					
					foreach($forms as $form)
					{
						echo "<option value='".$form->getFormId()."'>".$form->getFormName()."</option>";
					}
				?>>
			</select>
			</div>
			</section>
				<section>
					<label>From Date</label>
					<div>
					<input type='text' name='from_date' id='from_date' class="date">
					</div>
				</section>
				<section>
					<label>To Date</label>
					<div>
					<input type='text' name='to_date' id='to_date' class="date"> 
					</div>
				</section>
				 <div id='ajaxsearchform' name='ajaxsearchform' >
				 <div >
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
$days = GetDays($fromdate, $todate); 

$tableHeaders = "";
$tableRows = "";

$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);

if($_POST['application_form'] != "0")
{

$query = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created BETWEEN '".$fromdate."' AND '".$todate."'";
				$q = Doctrine_Query::create()
				  ->from('ApFormElements a')
				  ->where('a.form_id = ?', $_POST['application_form'])
				  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
				  ->orderBy('a.element_position ASC');
				$fields = $q->execute();
				
				foreach($fields as $field)
				{
					if($_POST['element_'.$field->getElementId()] != "")
					{
						$query = $query." AND ";
						$query = $query."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
						
					}
				}
$results = mysql_query($query,$dbconn);

echo "&nbsp; &nbsp;".mysql_num_rows($results)." entries found. <br>";


	//Use Days in Table Headers
	
	$q = Doctrine_Query::create()
		 ->from('ApForms a')
		 ->where('a.form_id = ?', $_POST['application_form']);
	$form = $q->fetchOne();
	if($form)
	{
		$tableRows[] = "<th>".$form->getFormName()."</th>";
	}
	//If < 15 (days)
	if(sizeof($days) <= 15)
	{
		foreach($days as $day)
		{
		   if(strlen($day) > 0)
		   {
				$tableHeaders[] = "<th>".$day."</th>";
					$dayquery = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created LIKE '%".$day."%' ";
					$q = Doctrine_Query::create()
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $_POST['application_form'])
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
					$dayapps = mysql_query($dayquery,$dbconn);
				$tableRows[] = "<td>".mysql_num_rows($dayapps)."</td>";
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
				$dayquery = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created BETWEEN '".$header."' AND '".$day."' ";
				$q = Doctrine_Query::create()
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $_POST['application_form'])
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
				$dayapps = mysql_query($dayquery,$dbconn);
				$header = $header." to ".$day;
				$tableHeaders[] = "<th>".$header."</th>";
				$tableRows[] = "<td>".mysql_num_rows($dayapps)."</td>";
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
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $_POST['application_form'])
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
				$dayapps = mysql_query($dayquery,$dbconn);
				$header = $header." to ".$day;
				$tableHeaders[] = "<th>".$header."</th>";
				$tableRows[] = "<td>".mysql_num_rows($dayapps)."</td>";
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
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $_POST['application_form'])
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
				$dayapps = mysql_query($dayquery,$dbconn);
				$header = $header." to ".$day;
				$tableHeaders[] = "<th>".$header."</th>";
				$tableRows[] = "<td>".mysql_num_rows($dayapps)."</td>";
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
			
			<table class="datatable">
				<thead>
					<tr><th>Type</th><th>No</th><th>Submitted On</th><th>Submitted By</th><th>Status</th><th style="background: none;">Actions</th>
					</tr>
				</thead>
				<tbody>
				
		    <?php
			$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
			mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
			
			if($_POST['application_form'] != "0")
			{
			
				$query = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created BETWEEN '".$fromdate."' AND '".$todate."'";
				
				$q = Doctrine_Query::create()
				  ->from('ApFormElements a')
				  ->where('a.form_id = ?', $_POST['application_form'])
				  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
				  ->orderBy('a.element_position ASC');
				$fields = $q->execute();
				
				foreach($fields as $field)
				{
					if($_POST['element_'.$field->getElementId()] != "")
					{
						$query = $query." AND ";
						$query = $query."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
						
					}
				}
				
			}
			
			$results = mysql_query($query,$dbconn);
			
			?>
			<br>
			<?php
			while($row = mysql_fetch_assoc($results))
			{
				$q = Doctrine_Query::create()
				  ->from('FormEntry a')
				  ->where('a.form_id = ?', $_POST['application_form'])
				  ->andWhere('a.entry_id = ?', $row['id']);
				$application = $q->fetchOne();
				if($application)
				{
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
							echo $row['date_created'];
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
						<a title='View Application' href='/plan/reports/singleview/applicationid/<?php echo $application->getId(); ?>/reportid/<?php echo $report->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a>
						</td>
					</tr>
				<?php
				}
			}
			?>
			</tbody>
			</table>
			
			</p>

	</div>
	
 
<?php
}
else
{

$q = Doctrine_Query::create()
  ->from('ApForms a')
  ->where('a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ?',array('6','7','15','16','17'))
  ->orderBy('a.form_name ASC');
$appforms = $q->execute();

$resultsize = 0;

foreach($appforms as $form)
{
	$query = "SELECT * FROM ap_form_".$form->getFormId()." WHERE date_created BETWEEN '".$fromdate."' AND '".$todate."'";
	$results = mysql_query($query,$dbconn);
	$resultsize = $resultsize + mysql_num_rows($results);
}


echo "&nbsp; &nbsp;".$resultsize." entries found. <br>";


	//Use Days in Table Headers
	
	$form_count =0;
	
	foreach($appforms as $form)
	{
		$form_count++;
	
		
		$q = Doctrine_Query::create()
			 ->from('ApForms a')
			 ->where('a.form_id = ?', $form->getFormId());
		$form = $q->fetchOne();
		if($form)
		{
			$tableRows[$form->getFormId()][] = "<th>".$form->getFormName()."</th>";
		}
		//If < 15 (days)
		if(sizeof($days) <= 15)
		{
			foreach($days as $day)
			{
			   if(strlen($day) > 0)
			   {
					if($form_count == 1)
					{
					$tableHeaders[] = "<th>".$day."</th>";
					}
						$dayquery = "SELECT * FROM ap_form_".$form->getFormId()." WHERE date_created LIKE '%".$day."%' ";
						$q = Doctrine_Query::create()
						  ->from('ApFormElements a')
						  ->where('a.form_id = ?', $form->getFormId())
						  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
						  ->orderBy('a.element_position ASC');
						$fields = $q->execute();
						
						foreach($fields as $field)
						{
							if($_POST['element_'.$field->getElementId()] != "")
							{
								$dayquery = $dayquery." AND ";
								$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
								
							}
						}
						$dayapps = mysql_query($dayquery,$dbconn);
					$tableRows[$form->getFormId()][] = "<td>".mysql_num_rows($dayapps)."</td>";
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
					$dayquery = "SELECT * FROM ap_form_".$form->getFormId()." WHERE date_created BETWEEN '".$header."' AND '".$day."'";
					$q = Doctrine_Query::create()
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $form->getFormId())
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
					$dayapps = mysql_query($dayquery,$dbconn);
					$header = $header." to ".$day;
					if($form_count == 1)
					{
					$tableHeaders[] = "<th>".$header."</th>";
					}
					$tableRows[$form->getFormId()][] = "<td>".mysql_num_rows($dayapps)."</td>";
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
					$dayquery = "SELECT * FROM ap_form_".$form->getFormId()." WHERE date_created BETWEEN '".$header."' AND '".$day."'";
					$q = Doctrine_Query::create()
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $form->getFormId())
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
					$dayapps = mysql_query($dayquery,$dbconn);
					$header = $header." to ".$day;
					if($form_count == 1)
					{
					$tableHeaders[] = "<th>".$header."</th>";
					}
					$tableRows[$form->getFormId()][] = "<td>".mysql_num_rows($dayapps)."</td>";
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
					$dayquery = "SELECT * FROM ap_form_".$form->getFormId()." WHERE date_created BETWEEN '".$header."' AND '".$day."'";
					$q = Doctrine_Query::create()
					  ->from('ApFormElements a')
					  ->where('a.form_id = ?', $form->getFormId())
					  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
					  ->orderBy('a.element_position ASC');
					$fields = $q->execute();
					
					foreach($fields as $field)
					{
						if($_POST['element_'.$field->getElementId()] != "")
						{
							$dayquery = $dayquery." AND ";
							$dayquery = $dayquery."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
							
						}
					}
					$dayapps = mysql_query($dayquery,$dbconn);
					$header = $header." to ".$day;
					if($form_count == 1)
					{
					$tableHeaders[] = "<th>".$header."</th>";
					}
					$tableRows[$form->getFormId()][] = "<td>".mysql_num_rows($dayapps)."</td>";
					$dayofyear = 0;
				}
				$total++;
				$dayofyear++;
			   }
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
			    <?php 
				foreach($appforms as $form)
				{
				?>
				<tr>
					<?php
					foreach($tableRows[$form->getFormId()] as $row)
					{
						echo $row;
					}
					?>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
			
			<table class="datatable">
				<thead>
					<tr><th>Type</th><th>No</th><th>Submitted On</th><th>Submitted By</th><th>Status</th><th style="background: none;">Actions</th>
					</tr>
				</thead>
				<tbody>
				
		    <?php
			$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
			mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
			
	foreach($appforms as $form)
	{
			$query = "SELECT * FROM ap_form_".$form->getFormId()." WHERE date_created BETWEEN '".$fromdate."' AND '".$todate."' ";
				
			$q = Doctrine_Query::create()
			  ->from('ApFormElements a')
			  ->where('a.form_id = ?', $form->getFormId())
			  ->andWhere('a.element_type <> ? AND a.element_type <> ?', array('section','file'))
			  ->orderBy('a.element_position ASC');
			$fields = $q->execute();
			
			foreach($fields as $field)
			{
				if($_POST['element_'.$field->getElementId()] != "")
				{
					$query = $query." AND ";
				    $query = $query."element_".$field->getElementId()." = ".$_POST['element_'.$field->getElementId()];
					
				}
			}
			
			
			$results = mysql_query($query,$dbconn);
			
			?>
			<br>
			<?php
			while($row = mysql_fetch_assoc($results))
			{
				$q = Doctrine_Query::create()
				  ->from('FormEntry a')
				  ->where('a.form_id = ?', $form->getFormId())
				  ->andWhere('a.entry_id = ?', $row['id'])
				  ->andWhere('a.approved <> ?', '0');
				$application = $q->fetchOne();
				if($application)
				{
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
							echo $row['date_created'];
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
						<a title='View Application' href='/plan/reports/singleview/applicationid/<?php echo $application->getId(); ?>/reportid/<?php echo $report->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a>
						</td>
					</tr>
				<?php
				}
			}
	}
			?>
			</tbody>
			</table>
			
			</p>

	</div>
	
 
<?php
}
?>