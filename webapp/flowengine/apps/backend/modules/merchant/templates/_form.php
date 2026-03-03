<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php
use_helper("I18N");
?>
<div class="contentpanel">
<form class="form-bordered" action="<?php echo url_for('/plan/merchant/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId()."&filter=".$filter : '?filter='.$filter )) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>  autocomplete="off" data-ajax="false">

<div class="panel panel-dark">
<div class="panel-heading">
<h3 class="panel-title"><?php echo __('Payment Merchant Details'); ?></h3>
</div>


<div class="panel-body panel-body-nopadding">

   <?php echo $form['_csrf_token']->render(); ?> 
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
      <?php echo $form->renderGlobalErrors() ?>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Name'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['name']->renderError() ?>
          <?php echo $form['name'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Description'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Website Link'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['link']->renderError() ?>
          <?php echo $form['link'] ?>
        </div>
      </div>
       <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Currency'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['currency_id']->renderError() ?>
          <?php echo $form['currency_id'] ?>
        </div>
      </div>

     <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Status'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['status']->renderError() ?>
          <?php echo $form['status'] ?>
        </div>
      </div>
     
	   <div class="panel-footer">
               <a href="<?php echo url_for('/plan/merchant/index') ?>" <button class="btn btn-danger mr10"><?php echo __('Back to List'); ?></button> </a>
                        <button type="submit" class="btn btn-primary" value="submitbuttonvalue"><?php echo __('Save'); ?></button>
	  </div>
</form>
</div>
