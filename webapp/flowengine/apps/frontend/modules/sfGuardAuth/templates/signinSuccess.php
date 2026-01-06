<?php
/**
 * indexSuccess.php template.
 *
 * Displays login form for clients
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage sfGuardAuth
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
?>
<!-- mentoring theme -->
<form method="post" action="<?php echo url_for('@sf_guard_signin') ?>">
	<?php echo $form->renderHiddenFields() ?>
	<?php if ($form->hasGlobalErrors()): ?>
	<div class="alert alert-danger">
		<?php echo $form->renderGlobalErrors() ?>
	</div>
	<?php endif;?>
	<div class="form-group">
		<label class="form-control-label">Your username or Email</label>
		<?php echo $form['username']->render(array('class' => 'form-control uname', "id" => "signin_username")) ?>
		<?php if ($form['username']->hasError()): ?>
		<div class="alert alert-error">
			<?php echo $form['username']->renderError() ?>
		</div>
		<?php endif;?>
	</div>
	<div class="form-group">
		<label class="form-control-label">Your Password</label>
		<div class="pass-group">
			<?php echo $form['password']->render(array('class' => 'form-control pass-input', "id" => "signin_password")) ?>
			<!--<input type="password" class="form-control pass-input"> -->
			<span class="fas fa-eye toggle-password"></span>
		</div>
	</div>
	<div class="text-end">
		<a class="forgot-link" href="/index.php/reset-request">Forgot Password ?</a> <!-- /index.php/reset-request -->
	</div>
	<button class="btn btn-primary login-btn" type="submit">Login</button>
	<div class="text-center dont-have">Don’t have an account? <a href="/index.php/apply">Register</a></div>
</form>
<?php if ($form['username']->hasError()): ?>
<div class="login-or">
	<span class="or-line"></span>
	<span class="span-or">or</span>
</div>
<div class="text-center">
	<a class="btn btn-warning btn-block" href="<?php echo public_path('index.php/resend'); ?>"><?php echo __("Resend Validation Email"); ?></a>
</div>
<?php endif;?>
<!-- end -->