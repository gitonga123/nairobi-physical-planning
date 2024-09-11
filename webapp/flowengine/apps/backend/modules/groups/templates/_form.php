<?php
	use_helper("I18N");
?>
<form  action="/plan/groups/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Group'):__('Edit Group'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading text-right">
          <a class="btn btn-primary" id="newpage" href="/plan/groups/index" ><?php echo __('Back to List'); ?></a>
    </div>

    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Name'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['name']->renderError() ?>
            <?php echo $form['name'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Description'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
        </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Reviewers'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['users_list']->renderError() ?>
          <?php echo $form['users_list'] ?>
        </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Permissions'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['permissions_list']->renderError() ?>
          <?php echo $form['permissions_list'] ?>
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
		var list1 = jQuery('select[name="mf_guard_group[users_list][]"]').bootstrapDualListbox();
		var list2 = jQuery('select[name="mf_guard_group[permissions_list][]"]').bootstrapDualListbox();
	});
</script>
