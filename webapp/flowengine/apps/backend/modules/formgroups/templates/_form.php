<?php
	use_helper("I18N");
?>
<form  action="/plan/formgroups/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getGroupId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Form Category'):__('Edit Form Category'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading text-right">
          <a class="btn btn-primary" id="newpage" href="/plan/formgroups/index" ><?php echo __('Back to List'); ?></a>
    </div>


    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Category Name'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['group_name']->renderError() ?>
            <?php echo $form['group_name'] ?>
          </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Category Description'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['group_description']->renderError() ?>
            <?php echo $form['group_description'] ?>
          </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Application Forms'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['group_forms']->renderError() ?>
            <?php echo $form['group_forms'] ?>
          </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
	jQuery(document).ready(function(){
		var list1 = jQuery('select[name="form_groups[group_forms][]"]').bootstrapDualListbox();
	});
</script>
