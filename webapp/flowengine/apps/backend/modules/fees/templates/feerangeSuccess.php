<?php
use_helper("I18N");
?>
<div class="panel panel-default">
<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this record'); ?></a>.
</div>
<div class="panel-heading">
<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Detail'):__('Edit Detail')); ?></h3>
</div>

<?php if($sf_user->hasFlash('save_notice')): ?>
	<div><?php echo $sf_user->getFlash('save_notice') ?></div>
<?php endif; ?>
<form id="bform" class="form-bordered form-horizontal" action="<?php echo url_for('/plan/fees/'.($form->getObject()->isNew() ? 'newfeerange' : 'updatefeerange').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false">
    <div class="panel-body panel-body-nopadding">

          <?php echo $form->renderGlobalErrors() ?>
          <?php if(isset($form['_csrf_token'])): ?>
          <?php echo $form['_csrf_token']->render(); ?>
          <?php endif; ?>


		<input type="hidden" name="fee_range[fee_id]" value="<?php echo $filter; ?>" id="fee_range_fee_id" /><!--Automatically save fee_id of opened fee-->

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Name'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['name']->renderError() ?>
              <?php echo $form['name'] ?>
            </div>
          </div>

          <!--<div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Range Min'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['range_1']->renderError() ?>
              <?php echo $form['range_1'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Range Max'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['range_2']->renderError() ?>
              <?php echo $form['range_2'] ?>
            </div>
          </div>-->
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Result Type'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['value_type']->renderError() ?>
              <?php echo $form['value_type'] ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Return Result if'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['condition_set_operator']->renderError() ?>
              <?php echo $form['condition_set_operator'] ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Result'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['result_value']->renderError() ?>
              <?php echo $form['result_value'] ?>
            </div>
          </div>
          <div class="form-group">
				<h3><label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Conditions'); ?></i></label></h3>
		  </div>
	<!--OTB Start Patch - For Implementing Finance Bills. Load fee conditions area -->
	<div class="form-group">
	        <div class="col-sm-12" id="loadrangeconditions" name="loadrangeconditions">

	        </div>
	        <?php
	          $feerangeid = 0;
			  if(!$form->getObject()->isNew())
			  {
			  		$feerangeid = $form->getObject()->getId();
			  }
	        ?>
			<script language="javascript">
			 jQuery(document).ready(function(){
		        $("#loadrangeconditions").load("<?php echo url_for('/plan/fees/rangeconditions/filter/'.$feerangeid) ?>");
				 /*$('#fee_invoiceid').change(function(){
					var value = this.value ;
					 $.ajax({
						url: '<?php echo url_for('/plan/fees/changebasefield/invoicetemplate_id/'); ?>'+value,
						cache: false,
						type: 'POST',
						data : $('#bform').serialize(),
						success: function(json) {
							$('#fee_base_field').empty().append(json);
						}
					});
				});*/
		     });
			 </script>
	      </div>
	  <!--OTB End Patch - For Implementing Finance Bills. Load fee conditions area -->
      </div><!--panel-body-->
<div class="panel-footer" align="right">
            <a id="backbuttonname" name="backbuttonname" class="btn btn-success"><?php echo __('Back'); ?></a> <button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
       </div>
</div>
<script language="javascript">
 jQuery(document).ready(function(){
	$("#submitbuttonname").click(function() {
		 $.ajax({
			url: '<?php echo url_for('/plan/fees/'.($form->getObject()->isNew() ? 'newfeerange' : 'updatefeerange').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>',
			cache: false,
			type: 'POST',
			data : $('#bform').serialize(),
			success: function(json) {
				$('#alertdiv').attr("style", "display: block;");
		        $("#loadranges").load("<?php echo url_for('/plan/fees/feerangeindex/filter/'.$filter) ?>");
			}
		});
		return false;
	 });

	  $( "#backbuttonname" ).click(function() {
		        $("#loadranges").load("<?php echo url_for('/plan/fees/feerangeindex/filter/'.$filter) ?>");
	  });

	});
</script>

</form>
</div>