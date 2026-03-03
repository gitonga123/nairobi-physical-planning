<?php
	use_helper("I18N");
?>
<form  action="/plan/penalties/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Penalty'):__('Edit Penalty'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>


		<div class="panel-heading text-right">
          <a class="btn btn-primary" id="newpage" href="/plan/penalties/index" ><?php echo __('Back to List'); ?></a>
    </div>

    <div class="panel-body">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Penalty'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['description']->renderError() ?>
            <?php echo $form['description'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Permit Type'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['template_id']->renderError() ?>
          <?php echo $form['template_id'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Penalty Trigger'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['trigger_type']->renderError() ?>
          <?php echo $form['trigger_type'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Trigger Time (When penalty starts)'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['trigger_period']->renderError() ?>
          <?php echo $form['trigger_period'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Type Of Penalty'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['penalty_type']->renderError() ?>
          <?php echo $form['penalty_type'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Penalty Amount'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['penalty_amount']->renderError() ?>
          <?php echo $form['penalty_amount'] ?>
        </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>
