<?php
use_helper("I18N");
?>
<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this record'); ?></a>.
</div>

<form id="plotform" class="form-bordered" action="<?php echo url_for('/backend.php/membersdatabase/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>

<div class="panel panel-dark">
<div class="panel-heading">
<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Record'):__('Edit Record')); ?></h3>
</div>

<div class="panel-body panel-body-nopadding">


	<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
      <?php echo $form['_csrf_token']->render(); ?>

      <?php echo $form->renderGlobalErrors() ?>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('User Category'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['user_category_id']->renderError() ?>
          <?php echo $form['user_category_id'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Member Number'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['members_no']->renderError() ?>
          <?php echo $form['members_no'] ?>
        </div>
      </div>
	  
		<div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Full Name'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['full_name']->renderError() ?>
          <?php echo $form['full_name'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Email'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['email']->renderError() ?>
          <?php echo $form['email'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Address'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['address']->renderError() ?>
          <?php echo $form['address'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Town'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['town']->renderError() ?>
          <?php echo $form['town'] ?>
        </div>
      </div>
	  </div><!--Panel-body-->

       <div class="panel-footer">
			<button  class='btn btn-danger mr10'><?php echo __('Reset'); ?></button><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
	  </div>
	  </fieldset>
</form>

<script language="javascript">
 jQuery(document).ready(function(){
	$("#submitbuttonname").click(function() {
		 $.ajax({
			url: '<?php echo url_for('/backend.php/membersdatabase/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>',
			cache: false,
			type: 'POST',
			data : $('#plotform').serialize(),
			success: function(json) {
				$('#alertdiv').attr("style", "display: block;");
				$("html, body").animate({ scrollTop: 0 }, "slow");
			}
		});
		return false;
	 });
	});
</script>
