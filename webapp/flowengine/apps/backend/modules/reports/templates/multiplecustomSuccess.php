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

function find($needle, $haystack)
{
	$pos = strpos($haystack, $needle);
	if($pos === false)
	{
		return false;
	}
	else
	{
		return true;
	}
}

?>
<div class="g12" style="padding-left: 3px;">
			<h1><?php echo $report->getTitle(); ?> <div style='float: right;'><button onClick="window.location='/plan/reports/exportcustom/id/<?php echo $report->getId(); ?>/form/<?php echo $_POST['application_form'] ?>/from/<?php echo $_POST['from_date']; ?>/to/<?php echo $_POST['to_date']; ?>/filter/<?php echo $_POST['filter']; ?>';">Export</button></div></h1>
			<div  style='width: 100%;'>
			
			<?php
				$q = Doctrine_Query::create()
				 ->from('ReportFields a')
				 ->where('a.report_id = ?', $report->getId());
			   $fields = $q->execute();

			   $parser = new templateparser();
			   
			   
				$q = Doctrine_Query::create()
					->from('apFormElements a')
					->where('a.form_id = ?', $report->getFormId());
				$formelements = $q->execute();
			?>

			<table>
				<thead>
					<tr>
					<td>#</td>
					<?php
					foreach($fields as $field)
					{
						?>
						<th style="background: none;" nowrap><?php if($field->getCustomheader()){ echo $field->getCustomheader(); }else{ echo $parser->parseHeaders($_POST['application_form'], $field->getElement()); }?></th>
						<?php
					}
					?>
					</tr>
				</thead>
				<tbody>
				
		    <?php
			$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
			mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
			
			$query = "SELECT * FROM ap_form_".$_POST['application_form']." WHERE date_created BETWEEN '".$_POST['from_date']."' AND '".$_POST['to_date']."'";
			
			$results = mysql_query($query,$dbconn);
			
			?>
			<br>
			<?php
			$counter = 1;
			while($row = mysql_fetch_assoc($results))
			{
                if($_POST['filter'] != "" && $_POST['filter'] != "0")
                {
                    $q = Doctrine_Query::create()
                        ->from('FormEntry a')
                        ->where('a.form_id = ?', $_POST['application_form'])
                        ->andWhere('a.entry_id = ?', $row['id'])
                        ->andWhere('a.approved = ?', $_POST['filter']);
                    $application = $q->fetchOne();
                }
                else
                {
                    $q = Doctrine_Query::create()
                        ->from('FormEntry a')
                        ->where('a.form_id = ?', $_POST['application_form'])
                        ->andWhere('a.entry_id = ?', $row['id'])
                        ->andWhere('a.approved <> ? AND a.approved <> ?', array('897','0'));
                    $application = $q->fetchOne();
                }
				if($application)
				{
				?>
				<tr id="app_<?php echo $application->getId() ?>">
				<td><?php echo $counter++; ?></td>
                <?php
                foreach($fields as $field)
                {
                 ?>
                    <td>
                    <?php
                        if(find("{fm_element", $field->getElement()))
                        {
                        	$element = str_replace("{fm_", "", $field->getElement());
                        	$element = str_replace("}", "", $element);
                        	
                        	$q = Doctrine_Query::create()
								->from('apFormElements a')
								->where('a.form_id = ?', $report->getFormId())
								->andWhere('a.element_id = ?', str_replace("element_", "", $element))
								->andWhere('a.element_total_child > 0');
							$formelements = $q->execute();
							
							if(sizeof($formelements) > 0)
							{
								$q = Doctrine_Query::create()
								   ->from('ApElementOptions a')
								   ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($report->getFormId(),str_replace("element_", "", $element),$row[$element]));
								$option = $q->fetchOne();
					
								if($option)
								{
									echo $option->getOption();
								}
							}
                        	else
                        	{
                        		echo $row[$element];
                        	}
                        }
                        else if(find("{ap_application_status}", $field->getElement()))
                        {
                        	echo $application->getStatusName();
                        }
                        else if(find("{ap_date_of_submission}", $field->getElement()))
                        {
                        	echo $application->getDateOfSubmission();
                        }
                        else if(find("{ap_date_of_approval}", $field->getElement()))
                        {
                        	echo $application->getDateOfIssue();
                        }
                        else if(find("{ap_application_id}", $field->getElement()))
                        {
                        	echo $application->getApplicationId();
                        }
                        else
                        {
                        	echo $field->getElement();
                        }
                    ?>
                    </td>
                <?php
                }
                ?>
				</tr>
				<?php
				}
			}
			?>
			</tbody>
			</table>
			
			</div>
			

	</div>
	
 
