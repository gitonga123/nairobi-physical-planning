<?php use_helper('I18N') ?>
<?php slot('sf_apply_login') ?>
<?php end_slot() ?>

<!-- Reset Form -->
<form method="post" name="sf_apply_reset_request" id="sf_apply_reset_request" action="<?php echo public_path('plan/reset-request') ?>" method="post" enctype="multipart/form-data">
					<div class="row">
                  <?php
                    echo $form->renderHiddenFields();
                  ?>
                  <?php if($form->hasGlobalErrors()): ?>
                  <div class="alert">
                  <p><?php echo $form->renderGlobalErrors() ?></p>
                  </div>
                  <?php endif; ?>

				  <h4 class="nomargin"><?php echo __('Lost Password Recovery'); ?></h4>
            <p class="mt5 mb20"><?php echo __('If you have forgotten your username or password, you can request to have your username emailed to you and to reset your password. When you fill in your registered email address, you will be sent instructions on how to reset your password.'); ?></p>


                  <div class="col-lg-12">
                     <div class="form-group">
                        <label class="form-control-label"><?php echo ('Username / Email Address'); ?></label>
                          <?php echo $form['username_or_email']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
                          <?php if($form['username_or_email']->hasError()): ?>
                          <div class="alert alert-warning">
                          <?php echo $form['username_or_email']->renderError() ?>
                          </div>
                          <?php endif; ?>
                      </div>
                  </div>

								
									<button class="btn btn-primary login-btn" type="submit"><?php echo ('Reset My Password'); ?></button>
									<div class="account-footer text-center mt-3">
										Already have an account? <a class="forgot-link mb-0" href="/plan/login"><?php echo ('Login'); ?></a>
									</div>
</form>								