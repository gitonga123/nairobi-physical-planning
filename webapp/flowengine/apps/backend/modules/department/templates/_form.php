<?php
	use_helper("I18N");
?>
<form  action="/plan/department/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Department'):__('Edit Department'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading text-right">
          <a class="btn btn-primary" id="newpage" href="/plan/department/index" ><?php echo __('Back to List'); ?></a>
    </div>


    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Department Name'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['department_name']->renderError() ?>
            <?php echo $form['department_name'] ?>
          </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Head of Department'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['department_head']->renderError() ?>
            <?php echo $form['department_head'] ?>
          </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Department Reviewers'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['department_reviewers']->renderError() ?>
            <?php echo $form['department_reviewers'] ?>
          </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
      <?php if(!$form->getObject()->isNew()){ ?><a href="/plan/department/delete/id/<?php echo $form->getObject()->getId(); ?>" class="btn btn-danger"><?php echo __('Delete'); ?></a><?php } ?>
    </div>
 </div><!-- panel-default -->
</form>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
	jQuery(document).ready(function(){
		var list1 = jQuery('select[name="department[department_reviewers][]"]').bootstrapDualListbox();
	});
</script>
