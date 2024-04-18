<?php
	use_helper("I18N");
?>
<form  action="/backend.php/zones/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered bform">

<?php echo $form->renderHiddenFields() ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Fee Zone'):__('Edit Fee Zone'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading">
          <a class="btn btn-primary" id="" href="/backend.php/zones/index" ><?php echo __('Back to List'); ?></a>
    </div>


    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Zone id'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['zone_id']->renderError() ?>
            <?php echo $form['zone_id'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Name'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['name']->renderError() ?>
            <?php echo $form['name'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Sub county'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['sub_county']->renderError() ?>
          <?php echo $form['sub_county'] ?>
        </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>
