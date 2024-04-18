<?php
	use_helper("I18N");
?>
<form  action="/backend.php/languages/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Language'):__('Edit Language'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading text-right">
          <a class="btn btn-primary" id="newpage" href="/backend.php/languages/index" ><?php echo __('Back to List'); ?></a>
    </div>

    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Title'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['local_title']->renderError() ?>
            <?php echo $form['local_title'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Description'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['locale_description']->renderError() ?>
          <?php echo $form['locale_description'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Locale'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['locale_identifier']->renderError() ?>
          <?php echo $form['locale_identifier'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Is Default?'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['is_default']->renderError() ?>
          <?php echo $form['is_default'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Left Align or Right Aligned?'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['text_align']->renderError() ?>
          <?php echo $form['text_align'] ?>
        </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>
