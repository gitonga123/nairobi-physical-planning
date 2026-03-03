<?php
use_helper("I18N");
?>

<div class="panel-heading">
<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Agency'):__('Edit Agency')); ?></h3>
</div>
<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>


<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this agency'); ?></a>.
</div>

<div class="panel-body panel-body-nopadding">
<form id="departmentform" class="form-bordered form-horizontal"  action="<?php echo url_for('/plan/agency/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>  autocomplete="off" data-ajax="false">
    <div class="panel-body panel-body-nopadding">

          <?php echo $form->renderGlobalErrors() ?>
          <?php if(isset($form['_csrf_token'])): ?>
          <?php echo $form['_csrf_token']->render(); ?>
          <?php endif; ?>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Name'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['name']->renderError() ?>
              <?php echo $form['name'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Address'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['address']->renderError() ?>
              <?php echo $form['address'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Logo'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['logo']->renderError() ?>
              <?php echo $form['logo'] ?>
			  <?php
				  $siteconfig = Doctrine_Core::getTable('ApSettings')->find(array(1));
				  if(!$form->getObject()->isNew() && $form->getObject()->getLogo())
				  {
					  ?>
					  <img src="/<?php echo $siteconfig->getUploadDir()."/".$form->getObject()->getLogo(); ?>">
					  <?php
				  }
			  ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Tag Line'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['tag_line']->renderError() ?>
              <?php echo $form['tag_line'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Parent Agency'); ?></i></label>
             <div class="col-sm-8">
              <?php echo $form['parent_agency']->renderError() ?>
              <?php echo $form['parent_agency'] ?>
            </div>
          </div>

             <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Reviewers'); ?></i></label>
                    <div class="col-sm-8">
                    <?php  ?>
                      <?php echo $form['users_list']->renderError() ?>
                      <?php echo $form['users_list'] ?>
                      <?php  ?>
                      
                    </div>
                  </div>

             <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Workflows'); ?></i></label>
                    <div class="col-sm-8">
                    <?php  ?>
                      <?php echo $form['menus_list']->renderError() ?>
                      <?php echo $form['menus_list'] ?>
                      <?php  ?>
                      
                    </div>
                  </div>

             <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Departments'); ?></i></label>
                    <div class="col-sm-8">
                    <?php  ?>
                      <?php echo $form['departments_list']->renderError() ?>
                      <?php echo $form['departments_list'] ?>
                      <?php  ?>
                      
                    </div>
                  </div>

      </div><!--panel-body-->

	  <div class="panel-footer">
			<button class="btn btn-success"><?php echo __('Back'); ?></button>
			<button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
	  </div>
</div>

</form>
<script>
jQuery(document).ready(function(){
  
  // CKEditor
  var list1 = jQuery('select[name="agency[users_list][]"]').bootstrapDualListbox();
  var list2 = jQuery('select[name="agency[menus_list][]"]').bootstrapDualListbox();
  var list2 = jQuery('select[name="agency[departments_list][]"]').bootstrapDualListbox();

});
</script>