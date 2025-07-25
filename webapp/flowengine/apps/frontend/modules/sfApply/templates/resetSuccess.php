<?php use_helper('I18N') ?>
<?php slot('sf_apply_login') ?>
<?php end_slot() ?>

<!-- Reset Form -->
<form method="post" name="sf_apply_reset_request" id="sf_apply_reset_request" action="<?php echo url_for("sfApply/reset") ?>" method="post" enctype="multipart/form-data">
					<div class="row">
                  <?php
                    echo $form->renderHiddenFields();
                  ?>
                  <?php if($form->hasGlobalErrors()): ?>
                  <div class="alert">
                  <p><?php echo $form->renderGlobalErrors() ?></p>
                  </div>
                  <?php endif; ?>

				  <h4 class="nomargin"><?php echo __('Password Recovery'); ?></h4>
				  <?php echo __('<p class="nomargin account-subtitle">Thanks for confirming your email address. You may now change your password using the form below.</p>'); ?>
                  <div class="col-lg-6">
                     <div class="form-group">
                        <label class="form-control-label"><?php echo ('New Password'); ?></label>
                          <?php echo $form['password']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
                          <?php if($form['password']->hasError()): ?>
                          <div class="alert alert-warning">
                          <?php echo $form['password']->renderError() ?>
                          </div>
                          <?php endif; ?>
                      </div>
                  </div>

				  <div class="col-lg-6">
                     <div class="form-group">
                        <label class="form-control-label"><?php echo ('Confirm Password'); ?></label>
                          <?php echo $form['password2']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
                          <?php if($form['password2']->hasError()): ?>
                          <div class="alert alert-warning">
                          <?php echo $form['password2']->renderError() ?>
                          </div>
                          <?php endif; ?>
                      </div>
                  </div>

								
									<button class="btn btn-primary login-btn" type="submit"><?php echo ('Update My Password'); ?></button>
									<div class="account-footer text-center mt-3">
										Back to Login <a class="forgot-link mb-0" href="/plan/login"><?php echo ('Login'); ?></a>
									</div>
</form>								