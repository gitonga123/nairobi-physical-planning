<?php use_helper('I18N') ?>
<?php slot('sf_apply_login') ?>
<?php end_slot() ?>

<br/>
<br/>
<br/>
<div class="panel">
			<legend><?php echo __('Forgot Your Password?'); ?></legend>
			
<form  class="unspaced" method="POST" action="<?php echo url_for("sfApply/reset") ?>" name="sf_apply_reset_form" id="sf_apply_reset_form">

<?php echo __('Thanks for confirming your email address. You may now change your
password using the form below.'); ?>
</p>
<ul style="list-style: none; margin:0;">
<?php echo $form ?>
<li>
<input type="submit" class="btn btn-info" value="<?php echo __("Reset My Password") ?>">
<?php echo __("or") ?> 
<?php echo link_to(__('Cancel'), 'sfApply/resetCancel') ?>
</li>
</ul>
</form>
		  
		  
          
          
          
		  
</div>


