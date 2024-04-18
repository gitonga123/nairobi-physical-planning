<?php use_helper('I18N'); ?>
<div class="contentpanel">
<div class="panel panel-dark">
<div class="panel-heading">
  <h3 class="panel-title"><?php echo __("Decline Application"); ?></h3>
  <p><?php echo __("Edit fields"); ?></p>
 </div>
<div class="panel-body panel-body-nopadding">
    <div class="col-md-12">
	<form id="fields" method="POST" >
		<input type="hidden" name="entry_decline_id" value="<?php echo $_GET['decline_id'] ?>"/>
		<fieldset>
			<div>
				<?php
				  foreach($fields as $field)
				  {
					  $checked=false;
					foreach($decline_fields as $f){
						if($f == $field->getElementId()){
							$checked=true;
						}
					}
		
					if($field->getElementType() == "section")
					{
					  ?>
					  <h4><?php  echo $field->getElementTitle(); ?></h4>
					  <?php
					}
					else {
						echo is_array($field->getElementId(),$decline_fields);
						//OTB patch add support for of translations 
						//Test for existance of translation
						 $translation = new Translation();
						 
						 if($translation->getFieldTranslation('ap_form_elements','element_title',$entry->getFormId(),$field->getElementId()) && $translation->getFieldTranslation('ap_form_elements','element_title',$entry->getFormId(),$field->getElementId()) != "") {
						  ?>  						  
					  <div class="checkbox block"><label><input type="checkbox" name="edit_fields[]" value="<?php echo $field->getElementId(); ?>" <?php echo $checked?'checked':'' ?>> <?php echo $translation->getFieldTranslation('ap_form_elements','element_title',$entry->getFormId(),$field->getElementId()); ?></label></div>
					  <?php
						 }
						 else { ?>
							 <div class="checkbox block"><label><input type="checkbox" name="edit_fields[]" value="<?php echo $field->getElementId(); ?>" <?php echo $checked?'checked':'' ?>> <?php echo $field->getElementTitle(); ?></label></div>
						<?php }
					   }
				  }
				  ?>
			 </div>
		</fieldset>
		<fieldset>
			<section>
			  <div>
				<button id="field_submit" class="btn btn-default mt10" type="submit" class="submit"><?php echo __("Submit"); ?></button>
			  </div>
			</section>
		</fieldset>
	</form>
	</div>
</div>