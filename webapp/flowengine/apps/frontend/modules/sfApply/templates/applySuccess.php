<!-- Register Form -->
<form amethod="post" id="registration_form" action="plan/apply" method="post" enctype="multipart/form-data">
<?php
                    echo $form->renderHiddenFields();
                  ?>
                   <?php if($form->hasGlobalErrors()): ?>
                  <div class="alert">
                  <p><?php echo $form->renderGlobalErrors() ?></p>
                  </div>
                  <?php endif; ?>
                  <?php if($form['fullname']->hasError()): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                      <strong>Warning!</strong> <?php echo $form['fullname']->renderError() ?>.
                      <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                  <?php endif; ?>

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label class="form-control-label"><?php echo ('Full Name'); ?> (Eg. John Doe)</label>
                        <?php echo $form['fullname']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
                          
											</div>
										</div>

                    <div class="col-lg-6">
											<div class="form-group">
												<label class="form-control-label"><?php echo ('Register As'); ?></label>
                        <?php echo $form['registeras']->render(array('class' => 'form-control form-select custom-select', 'required' => 'required')) ?>

                                <?php if($form['registeras']->hasError()): ?>
                                <div class="alert alert-warning">
                                <?php echo $form['registeras']->renderError() ?>
                                </div>
                               <?php endif; ?>
											</div>
										</div>
										
									</div>

                  <div class="row">    
                      <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label"><?php echo ('Username'); ?></label>
                            <?php echo $form['username']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
                              <?php if($form['username']->hasError()): ?>
                              <div class="alert alert-warning">
                              <?php echo $form['username']->renderError() ?>
                              </div>
                              <?php endif; ?>
                              <!-- user --> 
                               
                              <!-- end --> 
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label">Phone Number</label>
                             <?php echo $form['mobile']->render(array('class' => 'validate[required,custom[phone],minSize[10],maxSize[12]] form-control', 'required' => 'required', 'data-min' => '6', 'title' => 'Minimum of Six Characters')) ?>
                              <?php if($form['mobile']->hasError()): ?>
                              <div class="alert alert-warning">
                              <?php echo $form['mobile']->renderError() ?>
                              </div>
                              <?php endif; ?>
                          </div>
                        </div>
                  </div>

                  <div class="row">    
                      <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label"><?php echo ('Email'); ?></label>
                            <?php echo $form['email']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
                              <?php if($form['email']->hasError()): ?>
                              <div class="alert alert-warning">
                              <?php echo $form['email']->renderError() ?>
                              </div>
                              <?php endif; ?>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label">Confirm Email </label>
                            <?php echo $form['email2']->render(array('class' => 'validate[required,equals[email-1]] form-control', 'required' => 'required')) ?>
                            <?php if($form['email2']->hasError()): ?>
                            <div class="alert alert-warning">
                            <?php echo $form['email2']->renderError() ?>
                            </div>
                            <?php endif; ?>
                          </div>
                        </div>
                  </div>

                  <div class="row">    
                      <div class="col-lg-6">
                          <div class="form-group">
                          
                            <label class="form-control-label"><?php echo ('Password'); ?></label>
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
                            <label class="form-control-label">Confirm Password </label>
                            <?php echo $form['password2']->render(array('class' => 'password validate[required] form-control', 'required' => 'required', 'data-min' => '6', 'title' => 'Minimum of Six Characters', 'onKeyup' => 'confirmpassword()' )) ?>
                            <?php if($form['password2']->hasError()): ?>
                            <div class="alert alert-warning">
                            <?php echo $form['password2']->renderError() ?>
                            </div>
                            <?php endif; ?>
                          </div>
                        </div>
                  </div>

                  
                  
              
								
									<div class="form-group">
										<div class="form-check form-check-xs custom-checkbox">
											<input type="checkbox" class="form-check-input required" name="agreeCheckboxUser" id="agree_checkbox_user">
											<label class="form-check-label" for="agree_checkbox_user">I agree to Uasin Gishu County Revenue Collections System</label> <a tabindex="-1" href="javascript:void(0);">Privacy Policy</a> &amp; <a tabindex="-1" href="javascript:void(0);"> Terms.</a>
										</div>
									</div>
									<button class="btn btn-primary login-btn" type="submit">Sign Up</button>
									<div class="account-footer text-center mt-3">
										Already have an account? <a class="forgot-link mb-0" href="plan/login">Login</a>
									</div>
								</form>
								<!-- /Register Form -->
