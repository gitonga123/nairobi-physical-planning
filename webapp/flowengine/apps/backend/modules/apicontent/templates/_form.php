<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php
use_helper("I18N");
?>
<div class="contentpanel">
<form class="form-bordered" action="<?php echo url_for('/backend.php/apicontent/'.($form->getObject()->isNew() ? 'create' : 'update/id/'.$form->getObject()->getId())) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>  autocomplete="off" data-ajax="false">

<div class="panel panel-dark">
<div class="panel-heading">
<h3 class="panel-title"><?php echo __('API content'); ?></h3>
</div>


<div class="panel-body panel-body-nopadding">

   <?php echo $form->renderHiddenFields(); ?>
      <?php echo $form->renderGlobalErrors() ?>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Form'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['form_id']->renderError() ?>
          <?php echo $form['form_id'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('API use'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['api_use']->renderError() ?>
          <?php echo $form['api_use'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Merchant'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['merchant_id']->renderError() ?>
          <?php echo $form['merchant_id'] ?>
        </div>
      </div>
       <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Request URL'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['request_url']->renderError() ?>
          <?php echo $form['request_url'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('API use diff'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['api_use_diff']->renderError() ?>
          <?php echo $form['api_use_diff'] ?>
        </div>
      </div>
     <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Content'); ?></i></label>
        <div class="col-sm-8 rogue-input">
          <?php echo $form['content']->renderError() ?>
          <?php echo $form['content']->render(['style' => 'height:20%']) ?>
        </div>
      </div>
     
	   <div class="panel-footer">
               <a href="<?php echo url_for('/backend.php/apicontent/index') ?>">
                <button class="btn btn-danger mr10"><?php echo __('Back to List'); ?></button> 
              </a>
              <button type="submit" class="btn btn-primary" value="submitbuttonvalue"><?php echo __('Save'); ?></button>
	  </div>
</form>
</div>
