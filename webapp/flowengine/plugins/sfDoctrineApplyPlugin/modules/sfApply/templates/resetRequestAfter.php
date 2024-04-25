<?php use_helper('I18N') ?>
<?php slot('sf_apply_login') ?>
<?php end_slot() ?>

<div class="container">
<div class="row">
<div class="twelve columns offset-by-three">
          <div class="panel panel-default">
            <div class="panel-heading">
			<h4 class="panel-title"><?php echo __('Check Your E-Mail'); ?></h4>
			</div><!-- panel-heading -->
            <div class="panel-body">
              <div class="alert alert-info">
<?php echo __('For security reasons, a confirmation message has been sent to 
the email address associated with this account. Please check your
email for that message. You will need to click on a link provided
in that email in order to change your password. If you do not see
the message, be sure to check your "spam" and "bulk" email folders.'); ?>
<br>
<br>
</div>
            </div>
          </div><!-- panel -->
	
</div>
</div>
</div>


  

