<?php
	use_helper("I18N");
?>
<form  action="/backend.php/jsonreports/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Report'):__('Edit Report'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>

      <div class="pull-right">
          <a class="btn btn-primary" id="newpage" href="/backend.php/jsonreports/index" ><?php echo __('Back to List'); ?></a>
      </div>
    </div>
    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Title'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['title']->renderError() ?>
            <?php echo $form['title'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Application Form'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['form_id']->renderError() ?>
          <?php echo $form['form_id'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Report Type'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['type']->renderError() ?>
          <?php echo $form['type'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Content'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['content']->renderError() ?>
          <?php echo $form['content'] ?>
        </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>