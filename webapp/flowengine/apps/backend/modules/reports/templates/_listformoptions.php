<?php

/**
* Re-usable partial for producing the options for a select combo-box related to application forms
*
* $groups Array of FormGroups
*/
if(sizeof($groups) > 0)
{
	foreach($groups as $group)
	{
		echo "<optgroup label='".$group->getGroupName()."'>";
		$forms = "";
		
		$q = Doctrine_Query::create()
		  ->from('ApForms a')
		  ->leftJoin('a.ApFormGroups b')
		  ->where('a.form_id = b.form_id')
		  ->andWhere('b.group_id = ?', $group->getGroupId());
		$forms = $q->execute();
		
		foreach($forms as $form)
		{
			if($reduce == "minimum")
			{
				if($form->getFormId() != 60 && $form->getFormId() != 47 && $form->getFormId() != 48 && $form->getFormId() != 49)
				{
					continue;
				}
			}
			$selected = "";
			
			if($application_form != "" && $application_form == $form->getFormId())
			{
				$selected = "selected";
				$_GET['form'] = $application_form;
			}
			
			echo "<option value='".$form->getFormId()."' ".$selected.">".$form->getFormDescription()."</option>";
		}
		
		echo "</optgroup>";
	}
}
else
{			
	echo "<optgroup label='Application Forms'>";
	
	$q = Doctrine_Query::create()
	  ->from('ApForms a')
	  ->where('a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ?',array('6','7','15','16','17'))
	  ->orderBy('a.form_id ASC');
	$forms = $q->execute();
	
	foreach($forms as $form)
	{
		
		$selected = "";
		
		if($application_form != "" && $application_form == $form->getFormId())
		{
			$selected = "selected";
			$_GET['form'] = $application_form;
		}
		
		if($selectedform != "" && $selectedform == $form->getFormId())
		{
			$selected = "selected";
			$_GET['form'] = $selectedform;
		}
		
		
		
		echo "<option value='".$form->getFormId()."' ".$selected.">".$form->getFormDescription()."</option>";
		
	}
	
	echo "</optgroup>";
}
?>
