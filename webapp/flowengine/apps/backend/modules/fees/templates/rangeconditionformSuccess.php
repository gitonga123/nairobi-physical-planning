<?php
use_helper("I18N");
?>
<div class="panel panel-default">
<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this record'); ?></a>.
</div>
<div class="panel-heading">
<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Condition'):__('Edit Condition')); ?></h3>
</div>

<?php if($sf_user->hasFlash('save_notice')): ?>
	<div><?php echo $sf_user->getFlash('save_notice') ?></div>
<?php endif; ?>
<form id="bconditionform" class="form-bordered form-horizontal" action="<?php echo url_for('/plan/fees/'.($form->getObject()->isNew() ? 'newfeerangecondition' : 'updatefeerangecondition').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false">
    <div class="panel-body panel-body-nopadding">

          <?php echo $form->renderGlobalErrors() ?>

          <?php error_log("the csrf token s this #### ".$form['_csrf_token']); ?>

          <?php if(isset($form['_csrf_token'])): ?>
          <?php echo $form['_csrf_token']->render(); ?>
          <?php endif; ?>


		<input type="hidden" name="fee_range_condition[fee_range_id]" value="<?php echo $filter; ?>" id="fee_range_condition_fee_range_id" /><!--Automatically save fee_range_id of opened fee_range-->
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Condition Field'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['condition_field']->renderError() ?>
              <?php echo $form['condition_field'] ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Condition Operator'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['condition_operator']->renderError() ?>
              <?php echo $form['condition_operator'] ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Condition Value'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['condition_value']->renderError() ?>
              <?php echo $form['condition_value'] ?>
            </div>
          </div>
      </div><!--panel-body-->
<div class="panel-footer" align="right">
            <a id="backbuttonname" name="backbuttonname" class="btn btn-success"><?php echo __('Back'); ?></a> <button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
       </div>
</div>
<script language="javascript">
 jQuery(document).ready(function(){
	$("#submitbuttonname").click(function() {
		 $.ajax({
			url: '<?php echo url_for('/plan/fees/'.($form->getObject()->isNew() ? 'newfeerangecondition' : 'updatefeerangecondition').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>',
			cache: false,
			type: 'POST',
			data : $('#bconditionform').serialize(),
			success: function(json) {
				$('#alertdiv').attr("style", "display: block;");
		        $("#loadrangeconditions").load("<?php echo url_for('/plan/fees/rangeconditions/filter/'.$filter) ?>");
			}
		});
		return false;
	 });

	  $( "#backbuttonname" ).click(function() {
		        $("#loadrangeconditions").load("<?php echo url_for('/plan/fees/rangeconditions/filter/'.$filter) ?>");
	  });

	});
</script>

</form>
</div>