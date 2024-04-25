<?php
use_helper("I18N");
?>
<div class="pageheader">
   <h2><i class="fa fa-home"></i> <?php echo __("Application"); ?> <span><?php echo __("Decline"); ?></span></h2>
   <div class="breadcrumb-wrapper">
     <span class="label"><?php echo __("You are here"); ?>:</span>
     <ol class="breadcrumb">
       <li><a href="<?php echo public_path(); ?>backend.php"><?php echo __("Home"); ?></a></li>
       <li class="active"><?php echo __("Applications"); ?></li>
     </ol>
   </div>
 </div>

<div class="contentpanel">
<div class="row">
  <div class="panel panel-dark">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __("Decline Application"); ?></h3>
    <p><?php echo __("Send back for corrections"); ?></p>
  </div>
  <div class="panel-body panel-body-nopadding">
    <div class="col-md-12">
       <br>
       <h4><?php echo __("Send Application Back To User"); ?></h4>
       <br>
        <?php
        if($decline)
        {
           echo __("The application has been sent to the user for re-submission");
        }
        else
        {
          ?>
          <form action="/backend.php/forms/decline" method="POST"  autocomplete="off" data-ajax="false">
            <fieldset>
              <input type="hidden" name="entryid" value="<?php echo $entry->getId(); ?>">
              <input type="hidden" name="moveto" value="<?php echo $moveto; ?>">
              <label>
              <?php echo __("Reason"); ?>:
              </label>
              <div><textarea class="form-control" name="reason" cols="50" rows="10" required></textarea></div>
              </fieldset>
			  <fieldset>
				<input type="hidden" name="entryid" value="<?php echo $entry->getId(); ?>">
				<label>
				<h4><?php echo __("Select the fields you want the client to edit"); ?>:</h4>
				<h5><?php echo __('Leaving the boxes unselected will allow the client to edit all fields') ?></h5>
				</label>
				  <div>
					<?php
					  foreach($fields as $field)
					  {
						if($field->getElementType() == "section")
						{
						  ?>
						  <h4><?php  echo $field->getElementTitle(); ?></h4>
						  <?php
						}
						else {
							//OTB patch add support for of translations 
							//Test for existance of translation
							 $translation = new Translation();
							 
							 if($translation->getFieldTranslation('ap_form_elements','element_title',$entry->getFormId(),$field->getElementId()) && $translation->getFieldTranslation('ap_form_elements','element_title',$entry->getFormId(),$field->getElementId()) != "") {
							  ?>            
						  <div class="checkbox block"><label><input type="checkbox" name="edit_fields[]" value="<?php echo $field->getElementId(); ?>"> <?php echo $translation->getFieldTranslation('ap_form_elements','element_title',$entry->getFormId(),$field->getElementId()); ?></label></div>
						  <?php
							 }
							 else { ?>
								 <div class="checkbox block"><label><input type="checkbox" name="edit_fields[]" value="<?php echo $field->getElementId(); ?>"> <?php echo $field->getElementTitle(); ?></label></div>
							<?php }
						   }
					  }
					?>
				  </div>
			  </fieldset>
              <fieldset>
              <section>
                <div>
                  <button class="btn btn-default" type="submit" class="submit"><?php echo __("Send Comments"); ?></button>
                </div>
              </section>
            </fieldset>
          </form>
          <?php
        }
        ?>
        </div>
  		</div>
	</div>
</div>
</div>
