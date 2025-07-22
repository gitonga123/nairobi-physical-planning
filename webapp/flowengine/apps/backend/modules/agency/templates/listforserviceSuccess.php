<?php

 use_helper("I18N");
?>

<div class="contentpanel">
	<div class="row">

     <div class="col-sm-12">
	     <div class="panel panel-dark">
	     <div class="panel-heading">
	     <h3 class="panel-title">
	     <?php echo __('Agencies') ?>
	     </h3>
	     </div>
	     <div class="panel-body panel-body-nopadding">

     <?php
     if($sf_user->hasFlash("notice"))
     {
        ?>
         <div class="alert alert-success">
             <button type="button" class="close" aria-hidden="true">&times;</button>
             <strong><?php echo __('Success'); ?>! </strong><?php echo $sf_user->getFlash("notice"); ?>
         </div>
        <?php
     }

     if($sf_user->hasFlash("error"))
     {
         ?>
         <div class="alert alert-danger">
             <button type="button" class="close" aria-hidden="true">&times;</button>
             <strong><?php echo __('Error'); ?>! </strong><?php echo $sf_user->getFlash("error"); ?>
         </div>
        <?php
     }
     ?>

	        <div id="agency_settings">
	        	<form id="agencyform" class="form-horizontal form-bordered">

                <div class="alert alert-success" id="agencyalertdiv" name="agencyalertdiv" style="display: none;">
				  <button type="button" class="close" onClick="document.getElementById('agencyalertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
				  <strong><?php echo __('Well done'); ?></strong> <?php echo __('You successfully linked agencies to this service'); ?>.
				</div>

                <div class="alert alert-warning" id="agencyerrordiv" name="agencyerrordiv" style="display: none;">
				  <button type="button" class="close" onClick="document.getElementById('agencyerrordiv').style.display = 'none';" aria-hidden="true">&times;</button>
				  <?php echo __('An errored occured while trying to link agencies'); ?>.
				</div>

	            <input type="hidden" name="menuid" value="<?php echo $service->getId(); ?>">

	            <div class="form-group">
	              <label class="col-sm-3"><?php echo __('Select agencies that will access this workflow') ?>:</label>
	              <div class="col-sm-6">
	                <select id="agencies" name="agencies[]" multiple class="form-control" required="required">
						<?php
						foreach($agencies as $agency)
						{
							$selected = "";
							$q = Doctrine_Query::create()
								 ->from('AgencyMenu a')
								 ->where('a.menu_id = ?',  $service->getId())
								 ->andWhere('a.agency_id = ?', $agency->getId());
							$menuagencies = $q->execute();

							if(sizeof($menuagencies) > 0)
							{
								$selected = "selected";
							}

							?>
							<option value='<?php echo $agency->getId(); ?>' <?php echo $selected; ?>><?php echo $agency->getName(); ?></option>
							<?php
						}
						?>
					</select>
					<script language="javascript">
					 jQuery(document).ready(function(){

					 	var demo1 = $('[id="agencies"]').bootstrapDualListbox();

					 });
					 </script>
	              </div>
	            </div>

	            <br>
	             <div class="form-group">
	              <div class="col-sm-6">
				  <a href="/plan/services/index" class="btn btn-success"><?php echo __('Back'); ?></a>
				  <button class="btn btn-primary" id="agency_submitbutton" name="agency_submitbutton" type="submit">Save</button></div>
	            </div>
	            <br>

	         </form>

	         <script language="javascript">
 				jQuery(document).ready(function(){
 				   $("#agency_submitbutton").click(function(e) {
 				   	 e.preventDefault()
					 $.ajax({
						url: '<?php echo public_path(); ?>plan/agency/updateagency',
						cache: false,
						type: 'POST',
						data : $('#agencyform').serialize(),
						success: function(json) {
							$('#agencyalertdiv').attr("style", "display: block;");
							$("html, body").animate({ scrollTop: 0 }, "slow");
						},
						error: function(){
							$('#agencyerrordiv').attr("style", "display: block;");
							$("html, body").animate({ scrollTop: 0 }, "slow");
						}
					});
				  });
			  });
	         </script>
	        </div><!-- Agency Settings -->
		</div>
	</div>
</div>
</div>
</div>


