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
		->where('a.form_id = ?', array($application_form))
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
		
		$column["count"] = $count;
		$column["project/developer"] = "";
		$column["contactno"] = "";
		$column["plotno"] = "";
		$column["locality"] = "";
		
		$column["usage"] = "";
		$column["costusd"] = "";
		$column["costrwf"] = "";
		$column["floors"] = "";
		$column["request"] = "";
		
		$column["datesubmission"] = $application->getDateOfSubmission();
		$column["dateapproval"] = $application->getDateOfResponse();
		$column["dateissuing"] = $application->getDateOfIssue();
		$column["gfarea"] = "";
		$column["plotsize"] = "";
		
		if($application_form == 60 || $application_form == 23) //One Stop Center Kigali
		{
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,81))
			   ->andWhere("a.option_id = ?", $row['element_81']);
			$district = $q->fetchOne();
			$district != null ? $district = $district->getOption()."/" : $district = "";
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,82))
			   ->andWhere("a.option_id = ?", $row['element_82']);
			$sector = $q->fetchOne();
			$sector != null ? $sector = $sector->getOption()."/" : $sector = "";
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,187))
			   ->andWhere("a.option_id = ?", $row['element_187']);
			$cell = $q->fetchOne();
			$cell != null ? $cell = $cell->getOption()."/" : $cell = "";
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,188))
			   ->andWhere("a.option_id = ?", $row['element_188']);
			$village = $q->fetchOne();
			$village != null ? $village = $village->getOption()."/" : $village = "";
			
			if($application_form == 23)
			{
				$q = Doctrine_Query::create()
				   ->from("ApElementOptions a")
				   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,11))
				   ->andWhere("a.option_id = ?", $row['element_11']);
				$cell = $q->fetchOne();
				$cell != null ? $cell = $cell->getOption()."/" : $cell = $row['element_11']."/";
				
				$q = Doctrine_Query::create()
				   ->from("ApElementOptions a")
				   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,12))
				   ->andWhere("a.option_id = ?", $row['element_12']);
				$village = $q->fetchOne();
				$village != null ? $village = $village->getOption() : $village = $row['element_12'];
			}
			
			$locality = $district.$sector.$cell.$village;
			
			if($row['element_2'] != "")
			{
					if($row['element_5_1'] != "")
					{
							$column["project/developer"] = $row['element_2']." / ".$row['element_5_1']." ".$row['element_5_2'];
					}
					else
					{
							$column["project/developer"] = $row['element_2'];
					}
			}
			else
			{
					$column["project/developer"] = $row['element_5_1']." ".$row['element_5_2'];
			}
			
			if($row['element_83'] == "" || $row['element_83'] == "+250" || $row['element_83'] == "0")
			{
				$column["contactno"] = $user->getMobile();
			}
			else
			{	
				$column["contactno"] = $row['element_83'];
			}
			$column["plotno"] = $row['element_14'];
			$column["locality"] = $locality;
			
			$column["usage"] = $row['element_15'];
			$column["costusd"] = $row['element_78'];
			$column["costrwf"] = $row['element_33'];
			$column["floors"] = $row['element_21'];
			$column["request"] = "";
			
			$column["gfarea"] = $row['element_22'];
			$column["plotsize"] = $row['element_17'];
		}
		else if($application_form == 47) //Gasabo
		{
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,53))
			   ->andWhere("a.option_id = ?", $row['element_53']);
			$district = $q->fetchOne();
			$district != null ? $district = $district->getOption() : $district = "";
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,10))
			   ->andWhere("a.option_id = ?", $row['element_10']);
			$sector = $q->fetchOne();
			$sector != null ? $sector = $sector->getOption() : $sector = $row['element_10'];
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,11))
			   ->andWhere("a.option_id = ?", $row['element_11']);
			$cell = $q->fetchOne();
			$cell != null ? $cell = $cell->getOption() : $cell = $row['element_11'];
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,12))
			   ->andWhere("a.option_id = ?", $row['element_12']);
			$village = $q->fetchOne();
			$village != null ? $village = $village->getOption() : $village = $row['element_12'];
			
			$locality = $district."/".$sector."/".$cell."/".$village;
			
			if($row['element_2'] != "")
			{
					if($row['element_5_1'] != "")
					{
							$column["project/developer"] = $row['element_2']." / ".$row['element_5_1']." ".$row['element_5_2'];
					}
					else
					{
							$column["project/developer"] = $row['element_2'];
					}
			}
			else
			{
					$column["project/developer"] = $row['element_5_1']." ".$row['element_5_2'];
			}
			
			if($row['element_55'] == "" || $row['element_55'] == "+250")
			{
				$column["contactno"] = $user->getMobile();
			}
			else
			{	
				$column["contactno"] = $row['element_55'];
			}
			
			$column["plotno"] = $row['element_14'];
			$column["locality"] = $locality;
			
			$column["usage"] = $row['element_15'];
			$column["costusd"] = "";
			$column["costrwf"] = $row['element_33'];
			$column["floors"] = $row['element_21'];
			$column["request"] = "";
			
			$column["gfarea"] = $row['element_22'];
			$column["plotsize"] = $row['element_17'];
		}
		else if($application_form == 49) //Kucikiro
		{
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,53))
			   ->andWhere("a.option_id = ?", $row['element_53']);
			$district = $q->fetchOne();
			$district != null ? $district = $district->getOption() : $district = "";
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,10))
			   ->andWhere("a.option_id = ?", $row['element_10']);
			$sector = $q->fetchOne();
			$sector != null ? $sector = $sector->getOption() : $sector = $row['element_10'];
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,11))
			   ->andWhere("a.option_id = ?", $row['element_11']);
			$cell = $q->fetchOne();
			$cell != null ? $cell = $cell->getOption() : $cell = $row['element_11'];
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,12))
			   ->andWhere("a.option_id = ?", $row['element_12']);
			$village = $q->fetchOne();
			$village != null ? $village = $village->getOption() : $village = $row['element_12'];
			
			$locality = $district."/".$sector."/".$cell."/".$village;
			
			if($row['element_2'] != "")
			{
					if($row['element_5_1'] != "")
					{
							$column["project/developer"] = $row['element_2']." / ".$row['element_5_1']." ".$row['element_5_2'];
					}
					else
					{
							$column["project/developer"] = $row['element_2'];
					}
			}
			else
			{
					$column["project/developer"] = $row['element_5_1']." ".$row['element_5_2'];
			}
			
			if($row['element_55'] == "" || $row['element_55'] == "+250")
			{
				$column["contactno"] = $user->getMobile();
			}
			else
			{	
				$column["contactno"] = $row['element_55'];
			}
			
			$column["plotno"] = $row['element_14'];
			$column["locality"] = $locality;
			
			$column["usage"] = $row['element_15'];
			$column["costusd"] = "";
			$column["costrwf"] = $row['element_33'];
			$column["floors"] = $row['element_21'];
			$column["request"] = "";
			
			$column["gfarea"] = $row['element_22'];
			$column["plotsize"] = $row['element_17'];
		}
		else if($application_form == 48) //Nyarugenge
		{
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,53))
			   ->andWhere("a.option_id = ?", $row['element_53']);
			$district = $q->fetchOne();
			$district != null ? $district = $district->getOption() : $district = "";
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,10))
			   ->andWhere("a.option_id = ?", $row['element_10']);
			$sector = $q->fetchOne();
			$sector != null ? $sector = $sector->getOption() : $sector = $row['element_10'];
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,11))
			   ->andWhere("a.option_id = ?", $row['element_11']);
			$cell = $q->fetchOne();
			$cell != null ? $cell = $cell->getOption() : $cell = $row['element_11'];
			
			$q = Doctrine_Query::create()
			   ->from("ApElementOptions a")
			   ->where("a.form_id = ? AND a.element_id = ?", array($application_form,12))
			   ->andWhere("a.option_id = ?", $row['element_12']);
			$village = $q->fetchOne();
			$village != null ? $village = $village->getOption() : $village = $row['element_12'];
			
			$locality = $district."/".$sector."/".$cell."/".$village;
			
			if($row['element_2'] != "")
			{
					if($row['element_5_1'] != "")
					{
							$column["project/developer"] = $row['element_2']." / ".$row['element_5_1']." ".$row['element_5_2'];
					}
					else
					{
							$column["project/developer"] = $row['element_2'];
					}
			}
			else
			{
					$column["project/developer"] = $row['element_5_1']." ".$row['element_5_2'];
			}
			
			if($row['element_55'] == "" || $row['element_55'] == "+250")
			{
				$column["contactno"] = $user->getMobile();
			}
			else
			{	
				$column["contactno"] = $row['element_55'];
			}
			
			$column["plotno"] = $row['element_14'];
			$column["locality"] = $locality;
			
			$column["usage"] = $row['element_15'];
			$column["costusd"] = "";
			$column["costrwf"] = $row['element_33'];
			$column["floors"] = $row['element_21'];
			$column["request"] = "";
			
			$column["gfarea"] = $row['element_22'];
			$column["plotsize"] = $row['element_17'];
		}
										
		?>
		<tr id="row_<?php echo $application->getId() ?>">
			<td><?php echo $column["count"];  ?></td>
			<td><?php echo $column["project/developer"]; ?></td>
			<td><?php echo $column["contactno"]; ?></td>
			<td><?php echo $column["plotno"]; ?></td>
			<td><?php echo $column["locality"]; ?></td>
			<td><?php echo $column["usage"]; ?></td>
			<td><?php echo $column["costusd"]; ?></td>
			<td><?php echo $column["costrwf"]; ?></td>
			<td><?php echo $column["floors"]; ?></td>
			<td><?php //request ?></td>
			<td><?php echo $column["datesubmission"]; ?></td>
			<td><?php echo $column["dateapproval"]; ?></td>
			<td><?php echo $column["dateissuing"]; ?></td>
			<td><?php echo $column["gfarea"]; ?></td>
			<td><?php echo $column["plotsize"]; ?></td>
		</tr>
		<?php	
	}
?>
