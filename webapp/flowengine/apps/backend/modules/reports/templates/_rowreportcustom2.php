<?php
/**
 * rowreportcustom1 partial.
 *
 * Custom built-in report partial developed for Kigali Construction Permit Management System
 *
 * @package    backend
 * @subpackage reports
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 
 
	$q = Doctrine_Query::create()
		->from('FormEntry a')
		->where('a.form_id = ? OR a.form_id = ?', array($application_form, 23))
		->andWhere('a.entry_id = ?', $row['id'])
		->andWhere('a.approved <> ?', '0')
		->andWhere('a.parent_submission = 0');
	$application = $q->fetchOne();
	if($application)
	{
		$count++;
		$q = Doctrine_Query::create()
		  ->from('SfGuardUserProfile a')
		  ->where('a.user_id = ?', $application->getUserId());
		$user = $q->fetchOne();
										
		?>
		<tr id="row_<?php echo $application->getId() ?>">
						  
						<td><?php echo $count; ?></td>
						<td><?php 
						if($row['element_2'] != "")
						{
						        if($row['element_5_1'] != "")
						        {
						                echo $row['element_2']." / ".$row['element_5_1']." ".$row['element_5_2'];
						        }
						        else
						        {
						                echo $row['element_2'];
						        }
						}
						else
						{
						        echo $row['element_5_1']." ".$row['element_5_2'];
						}
						
						 ?></td>
						 <td>
						 <?php
						 echo $user->getMobile();
						 ?>
						 </td>
						 <td>
						 <?php
						        echo $row['date_created'];
						 ?>
						 </td>
						 <td>
						 <?php
						        //date of response
						 ?>
						 </td>
						 <td>
						 <?php
						        //date cp issued
						 ?>
						 </td>
						 <td>
						 <?php
						        //number of days taken
						 ?>
						 </td>
						 <td>
						 <?php
						        //observation
						 ?>
						 </td>
					</tr>
		<?php	
	}
?>
