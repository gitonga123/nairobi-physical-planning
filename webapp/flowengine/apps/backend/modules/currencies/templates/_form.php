<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php
use_helper("I18N");
?>
<div class="contentpanel">
<form class="form-bordered" action="<?php echo url_for('/backend.php/currencies/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId()."&filter=".$filter : '?filter='.$filter )) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>  autocomplete="off" data-ajax="false">

<div class="panel panel-dark">
<div class="panel-heading">
<h3 class="panel-title"><?php echo __('Currency Details'); ?></h3>
</div>


<div class="panel-body panel-body-nopadding">

   <?php echo $form['_csrf_token']->render(); ?> 
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
      <?php echo $form->renderGlobalErrors() ?>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('County / Name'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['state']->renderError() ?>
          <?php echo $form['state'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Name'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['name']->renderError() ?>
          <?php echo $form['name'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('ISO Code'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['code']->renderError() ?>
          <?php echo $form['code'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Symbol'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['symbol']->renderError() ?>
          <?php echo $form['symbol'] ?>
        </div>
      </div>
	   <div class="panel-footer">
               <a href="<?php echo url_for('/backend.php/currencies/index') ?>" <button class="btn btn-danger mr10"><?php echo __('Back to List'); ?></button> </a>
                        <button type="submit" class="btn btn-primary" value="submitbuttonvalue"><?php echo __('Save'); ?></button>
	  </div>
</form>
</div>
