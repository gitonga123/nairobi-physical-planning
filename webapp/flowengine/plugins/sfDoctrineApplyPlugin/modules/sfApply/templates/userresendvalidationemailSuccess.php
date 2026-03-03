<?php use_helper('I18N')?>
<?php slot('sf_apply_login')?>
<?php end_slot()?>
<section id="headline">
	<div class="container">
		<div class="row">
			<div class="col-sm-8 col-sm-offset-2">
				<h3><?php echo __("Resend Account Validation Email"); ?></h3>
			</div>
		</div>
	</div>
</section>
<!-- BEGIN CONTAINER -->
<div class="container margin-bottom-40">
	<div class="row signinpanel">
		<div class="eleven columns offset-by-five login-signup-page mb40">
			<?php if (isset($_SESSION['resend_resp'])): ?>
			<div class="alert alert-info">
				<p>Validation email has been sent! Kindly check the email you registed with.</p>
			</div>
			<?php endif;?>
			<?php if (isset($_SESSION['resent_resp_valid'])): ?>
			<div class="alert alert-warning">
				<p>Your account is already active! Kindly Login.</p>
			</div>
			<?php endif;?>
			<form method="POST" action="<?php echo url_for('@resendRequest') ?>" name="sf_apply_reset_request" id="sf_apply_reset_request" autocomplete="off" data-ajax="false">
				<h4 class="nomargin"><?php echo __('Validation Email'); ?></h4>
				<p class="mt5 mb20"><?php echo __('This will resend the validation email used to verify your email account. Kindly check the "SPAM/JUNK" folder in your email account that you registered with.'); ?></p>
				<?php echo $form->renderHiddenFields() ?>
				<?php echo $form->renderGlobalErrors() ?>
				<div class="form-group">
					<?php echo $form['username_or_email']->renderLabel() ?>
					<?php $form['username_or_email']->renderError()?>
					<?php echo $form['username_or_email']->render(array('class' => 'form-control', 'placeholder' => 'Email or Username')) ?>
				</div>
				<button type="submit" name='submit' class="btn btn-primary btn-block"><?php echo __('Resend Email'); ?></button>
				<div class="text-center dont-have">Email Verified? <a href="/plan/login">Login</a></div>
			</form>
		</div>
	</div>
</div>