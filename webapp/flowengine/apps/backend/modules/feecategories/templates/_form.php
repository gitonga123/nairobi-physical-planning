<?php
	use_helper("I18N");
?>
<form  action="/plan/feecategories/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Fee Category'):__('Edit Fee Category'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading">
      <a class="btn btn-primary" id="newpage" href="/plan/feecategories/index" ><?php echo __('Back to List'); ?></a>
    </div>


    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Title'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['title']->renderError() ?>
            <?php echo $form['title'] ?>
          </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>
