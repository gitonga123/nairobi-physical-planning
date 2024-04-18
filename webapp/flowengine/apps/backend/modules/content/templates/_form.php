<?php
	use_helper("I18N");
?>
<form  action="/backend.php/content/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New WebPage'):__('Edit WebPage'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading text-right">
          <a class="btn btn-primary" id="newpage" href="/backend.php/content/index" ><?php echo __('Back to List'); ?></a>
    </div>

    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Title'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['menu_title']->renderError() ?>
            <?php echo $form['menu_title'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Content'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['top_article']->renderError() ?>
          <?php echo $form['top_article'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Status'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['published']->renderError() ?>
          <?php echo $form['published'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Parent'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['parent_id']->renderError() ?>
          <?php echo $form['parent_id'] ?>
        </div>
    </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>

<script src="/assets_backend/js/ckeditor/ckeditor.js"></script>
<script src="/assets_backend/js/ckeditor/adapters/jquery.js"></script>

<script>
jQuery(document).ready(function(){

  // CKEditor
  jQuery('#content_top_article').ckeditor();

});
</script>
