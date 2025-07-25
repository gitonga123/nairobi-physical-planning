<?php
	use_helper("I18N");
?>
<form  action="/plan/feecode/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered bform">

<?php echo $form->renderHiddenFields() ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Fee Code'):__('Edit Fee Code'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading">
          <a class="btn btn-primary" id="newpage" href="/plan/feecode/index" ><?php echo __('Back to List'); ?></a>
    </div>


    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Service id'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['service_id']->renderError() ?>
            <?php echo $form['service_id'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Service'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['service_name']->renderError() ?>
          <?php echo $form['service_name'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Is fixed?'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['fixed']->renderError() ?>
          <?php echo $form['fixed'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Amount'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['amount']->renderError() ?>
          <?php echo $form['amount'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Zone'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['zone']->renderError() ?>
          <?php echo $form['zone'] ?>
        </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>
