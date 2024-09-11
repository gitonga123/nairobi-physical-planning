<?php
use_helper("I18N");
?>
<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this record'); ?></a>.
</div>

<form id="conditionform" class="form-bordered" action="<?php echo url_for('/plan/conditionsmng/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>  autocomplete="off" data-ajax="false">

<div class="panel panel-dark">
<div class="panel-heading">
<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Condition'):__('Edit Condition')); ?></h3>
</div>


<div class="panel-body panel-body-nopadding">

<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
	<?php endif; ?>
	<?php if(isset($form['_csrf_token'])): ?>
	<?php echo $form['_csrf_token']->render(); ?>
	<?php endif; ?>
	<?php echo $form->renderGlobalErrors() ?>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Permit'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['permit_id']->renderError() ?>
          <?php echo $form['permit_id'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Department'); ?></i></label>
        <div class="col-sm-8 rogue-input">
           <?php echo $form['department_id']->renderError() ?>
          <?php echo $form['department_id'] ?>
        </div>
      </div> 
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Short name'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['short_name']->renderError() ?>
          <?php echo $form['short_name'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Description'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
        </div>
     </div>
     </div>
<div class="panel-footer" align="right">
            <button class="btn btn-danger mr10"><?php echo __('Reset'); ?></button><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
       </div>
</div>
</form>
<script language="javascript">
 /*jQuery(document).ready(function(){
	$("#submitbuttonname").click(function() {
		 $.ajax({
			url: '<?php echo url_for('/plan/conditionsmng/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId().'&filter='.$filter : '?filter='.$filter)) ?>',
			cache: false,
			type: 'POST',
			data : $('#conditionform').serialize(),
			success: function(json) {
				$('#alertdiv').attr("style", "display: block;");
        $("#loadinner").load("<?php url_for('/plan/conditionsmng/index/filter/'.$filter); ?>");
			}
		});
		return false;
	 });
	});*/
</script>