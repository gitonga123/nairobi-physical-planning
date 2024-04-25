<?php use_helper('I18N') ?>
<?php slot('sf_apply_login') ?>
<?php end_slot() ?>

  <section id="headline">
    <div class="container">
      <h3><?php echo __("Password Recovery"); ?></h3>
    </div>
</section>
        <div class="container margin-bottom-40">
           <div class="row signinpanel">

            <div class="eleven columns offset-by-five login-signup-page mb40">

            <form method="POST" action="<?php echo public_path('index.php/reset-request') ?>" name="sf_apply_reset_request" id="sf_apply_reset_request" autocomplete="off" data-ajax="false">
            <h4 class="nomargin"><?php echo __('Lost Password Recovery'); ?></h4>
            <p class="mt5 mb20"><?php echo __('If you have forgotten your username or password, you can request to have your username emailed to you and to reset your password. When you fill in your registered email address, you will be sent instructions on how to reset your password.'); ?></p>

            <?php echo $form->renderHiddenFields() ?>
			<div class="form-group">

			<?php echo $form['username_or_email']->renderLabel() ?>
			<?php echo $form['username_or_email']->renderError() ?>
			<?php echo $form['username_or_email']->render(array('class' => 'form-control','id' => 'sfApplyResetRequest_username_or_email')) ?>
			</div>
			<div class="form-group">
			<button type="submit" name='submit' class="btn btn-primary btn-block"><?php echo __('Reset My Password'); ?></button>
			</div>
			</form>

			</div>
			</div>
		</div>


