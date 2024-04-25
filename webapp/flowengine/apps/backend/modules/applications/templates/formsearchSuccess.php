<?php

$formid = $_POST['formid'];
?>
<div   style='height: 250px; overflow-y: auto;'>
<fieldset>
<?php
  $q = Doctrine_Query::create()
	  ->from('ApFormElements a')
	  ->where('a.form_id = ?', $formid)
	  ->andWhere('a.element_type <> ? AND a.element_type <> ? AND a.element_type <> ? AND a.element_type <> ?', array('section','file','section','checkbox'))
	  ->andWhere('a.element_status = ?', 1)
	  ->orderBy('a.element_position ASC');
	$fields = $q->execute();
	foreach($fields as $field)
	{
		?>
		<section>
		<label><?php echo $field->getElementTitle(); ?></label>
		<div>
		<?php
		if($field->getElementType() == "select")
		{
			$q = Doctrine_Query::create()
			  ->from('ApElementOptions a')
			  ->where('a.element_id = ?', $field->getElementId())
			  ->andWhere('a.form_id = ?', $formid)
			  ->andWhere('a.live = ?', 1)
			  ->orderBy('a.position ASC');
			$options = $q->execute();
			echo "<select name='element_".$field->getElementId()."' id='element_".$field->getElementId()."'>";
			echo "<option value=''></option>";
			foreach($options as $option)
			{
				echo "<option value='".$option->getOptionId()."'>".$option->getOptionText()."</option>";
			}
			echo "</select>";
		}
		else if($field->getElementType() == "simple_name_wmiddle")
		{
		?>
			<input type='text' name='element_<?php echo $field->getElementId(); ?>_1' id='element_<?php echo $field->getElementId(); ?>_1'>
			<input type='text' name='element_<?php echo $field->getElementId(); ?>_2' id='element_<?php echo $field->getElementId(); ?>_2'>
			<input type='text' name='element_<?php echo $field->getElementId(); ?>_3' id='element_<?php echo $field->getElementId(); ?>_3'>
		<?php
		}
		else
		{
		?>
			<input type='text' name='element_<?php echo $field->getElementId(); ?>' id='element_<?php echo $field->getElementId(); ?>'>
		<?php
		}
		?>
		</div>
		</section>
		<?php
	}
?>
</fieldset>
</div>
