<?php
use_helper("I18N");
?>
<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this record'); ?></a>.
</div>

<form id="plotform" class="form-bordered" action="<?php echo url_for('/plan/plot/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>

<div class="panel panel-dark">
<div class="panel-heading">
<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Plot'):__('Edit Plot')); ?></h3>
</div>

<div class="panel-body panel-body-nopadding">


	<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
      <?php echo $form['_csrf_token']->render(); ?>

      <?php echo $form->renderGlobalErrors() ?>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Plot No'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_no']->renderError() ?>
          <?php echo $form['plot_no'] ?>
        </div>
      </div>
	  
		<div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Plot Location'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_location']->renderError() ?>
          <?php echo $form['plot_location'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Plot Type'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_type']->renderError() ?>
          <select id="plot_plot_type" class="form-control" name="plot[plot_type]">
            <option>L.R No</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Plot Status'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_status']->renderError() ?>
          <select id="plot_plot_status" name="plot[plot_status]">
          <option value='Available'><?php echo __('Available'); ?></option>
          <option value='Pending-Application'><?php echo __('Pending Application'); ?></option>
          <option value='Contentious'><?php echo __('Contentious'); ?></option>
          <option value='Black-Listed'><?php echo __('Black Listed'); ?></option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Plot Size'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_size']->renderError() ?>
          <?php echo $form['plot_size'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Latitude'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_lat']->renderError() ?>
          <?php echo $form['plot_lat'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Longitude'); ?></i></label>
       <div class="col-sm-8 rogue-input">
          <?php echo $form['plot_long']->renderError() ?>
          <?php echo $form['plot_long'] ?>
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
			url: '<?php echo url_for('/plan/plot/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>',
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