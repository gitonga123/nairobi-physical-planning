
     <!-- BEGIN PAGE CONTAINER -->
    <div class="page-container">
        <!-- BEGIN CONTAINER -->
        <div class="container mb40">
          <div class="row signuppanel">
			<div class="col-md-4 col-sm-2">
			</div>
			<div class="col-md-4 col-sm-2">
            <div class="twelve columns offset-by-four">
				<?php if($form->hasGlobalErrors()): ?>
				<div class="alert">
				<p><?php echo $form->renderGlobalErrors() ?></p>
				</div>
				<?php endif; ?>
                <form method="post" id="registration_form" name="registration_form" action="/index.php//apply" method="post" enctype="multipart/form-data" autocomplete="off" data-ajax="false"  onsubmit="javascript:return validateall();">
                    <h3 class="nomargin"><?php echo __('Please create an account'); ?></h3>
                    <p class="mt5 mb20"><?php echo __('Already a member?'); ?> <a href="/index.php//login"><strong><?php echo __('Sign In'); ?></strong></a></p>

                    <?php
					?>

                   <div class="mb10">
                    <label class="control-label"><?php echo __('Full Name'); ?></label>
					<?php echo $form['fullname']->render(array('class' => 'validate[required,custom[onlyLetterSp],minSize[3]] form-control', 'required' => 'required')) ?>
					<?php if($form['fullname']->hasError()): ?>
					<div class="alert alert-warning">
					<?php echo $form['fullname']->renderError() ?>
					</div>
					<?php endif; ?>
                    </div>

                    <div class="mb10">
                    <label class="control-label"><?php echo __('Username'); ?></label>
					<?php echo $form['username']->render(array('class' => 'validate[required,custom[onlyLetterNumber],minSize[3]] form-control', 'required' => 'required')) ?>
					<?php if($form['username']->hasError()): ?>
					<div class="alert alert-warning">
					<?php echo $form['username']->renderError() ?>
					</div>
					<?php endif; ?>
				    <div id='checkusername' name='checkusername'></div>
                    </div>
                    
                  <div id="usernameresult" name="usernameresult"></div>

                  <script language="javascript">
                    $('document').ready(function(){
                      $('#sfApplyApply_username').keyup(function(){
                        $.ajax({
                                  type: "POST",
                                  url: "/index.php//index/checkuser",
                                  data: {
                                      'name' : $('input:text[id=sfApplyApply_username]').val()
                                  },
                                  dataType: "text",
                                  success: function(msg){
                                        //Receiving the result of search here
                                        $("#usernameresult").html(msg);
                                  }
                              });
                          });
                    });
                  </script>
                    

                    <div class="mb10">
                        <label class="control-label"><?php echo __('Enter Email'); ?></label>
						<?php echo $form['email']->render(array('class' => 'validate[required,custom[email]] form-control', 'required' => 'required')) ?>
						<?php if($form['email']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['email']->renderError() ?>
						</div>
						<?php endif; ?>
					   <div id='checkemail' name='checkemail'></div>
                    </div>

					<div id="emailresult" name="emailresult"></div>

					<script language="javascript">
					  $('document').ready(function(){
						$('#sfApplyApply_email').keyup(function(){
						  $.ajax({
									type: "POST",
									url: "/index.php//index/checkemail",
									data: {
										'email' : $('input:text[id=sfApplyApply_email]').val()
									},
									dataType: "text",
									success: function(msg){
										  //Receiving the result of search here
										  $("#emailresult").html(msg);
									}
								});
							});
					  });
					</script>

                    <div class="mb10">
                       <label class="control-label"><?php echo __('Confirm Email'); ?></label>
						<?php echo $form['email2']->render(array('class' => 'validate[required,equals[email-1]] form-control', 'required' => 'required')) ?>
						<?php if($form['email2']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['email2']->renderError() ?>
						</div>
						<?php endif; ?>
					  <div id='confirmemail' name='confirmemail'></div>
                    </div>
                    
                    <div id="confirmemailresult" name="confirmemailresult"></div>

                    <script language="javascript">
                      $('document').ready(function(){
                        $('#sfApplyApply_email').keyup(function(){
                          if($('#sfApplyApply_email').val() == $('#sfApplyApply_email2').val() && $('#sfApplyApply_email').val() != "")
                          {
                            $('#confirmemailresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Emails match!</strong></div>');
                            $('#submit_app').prop('disabled',false);
                          }
                          else
                          {
                            $('#confirmemailresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Emails don\'t match!</strong> Try again.</div>');
                            $('#submit_app').prop('disabled',true);
                          }
                        });
                        $('#sfApplyApply_email2').keyup(function(){
                          if($('#sfApplyApply_email').val() == $('#sfApplyApply_email2').val() && $('#sfApplyApply_email').val() != "")
                          {
                            $('#confirmemailresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Emails match!</strong></div>');
                            $('#submit_app').prop('disabled',false);
                          }
                          else
                          {
                            $('#confirmemailresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Emails don\'t match!</strong> Try again.</div>');
                            $('#submit_app').prop('disabled',true);
                          }
                        });
                      });
                    </script>

                    <div class="mb10">
                        <label class="control-label"><?php echo __('New Password'); ?></label>
						<?php echo $form['password']->render(array('class' => 'password validate[required] form-control', 'required' => 'required', 'data-min' => '6', 'title' => 'Minimum of Six Characters')) ?>
						<?php if($form['password']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['password']->renderError() ?>
						</div>
						<?php endif; ?>
				    	<div id='checkpassword' name='checkpassword'></div>
                    </div>

                    <div class="mb10">
                        <label class="control-label"><?php echo __('Confirm Password'); ?></label>
						<?php echo $form['password2']->render(array('class' => 'password validate[required] form-control', 'required' => 'required', 'data-min' => '6', 'title' => 'Minimum of Six Characters', 'onKeyup' => 'confirmpassword()' )) ?>
						<?php if($form['password2']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['password2']->renderError() ?>
						</div>
						<?php endif; ?>
						<div id='confirmpassword' name='confirmpassword'></div>
                    </div>
                    
                    <div id="passwordresult" name="passwordresult"></div>

                    <script language="javascript">
                      $('document').ready(function(){
                        $('#sfApplyApply_password').keyup(function(){
                          if($('#sfApplyApply_password').val() == $('#sfApplyApply_password2').val() && $('#sfApplyApply_password').val() != "")
                          {
                            $('#passwordresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords match!</strong></div>');
                            $('#submit_app').prop('disabled',false);
                          }
                          else
                          {
                            $('#passwordresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords don\'t match!</strong> Try again.</div>');
                            $('#submit_app').prop('disabled',true);
                          }
                        });
                        $('#sfApplyApply_password2').keyup(function(){
                          if($('#sfApplyApply_password').val() == $('#sfApplyApply_password2').val() && $('#sfApplyApply_password').val() != "")
                          {
                            $('#passwordresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords match!</strong></div>');
                            $('#submit_app').prop('disabled',false);
                          }
                          else
                          {
                            $('#passwordresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords don\'t match!</strong> Try again.</div>');
                            $('#submit_app').prop('disabled',true);
                          }
                        });
                      });
                    </script>

                       <div class="mb10">
                        <label class="control-label"><?php echo __('Mobile Phone (e.g 256700123456 or 0700123456)'); ?></label>
						<?php echo $form['mobile']->render(array('class' => 'validate[required,custom[phone],minSize[10],maxSize[12]] form-control', 'required' => 'required', 'data-min' => '6', 'title' => 'Minimum of Six Characters')) ?>
						<?php if($form['mobile']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['mobile']->renderError() ?>
						</div>
						<?php endif; ?>
                    </div>

                       <div class="mb10">
                        <label class="control-label"><?php echo __('Register As'); ?></label>
						<?php echo $form['registeras']->render(array('class' => 'form-control', 'required' => 'required')) ?>
						<?php if($form['registeras']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['registeras']->renderError() ?>
						</div>
						<?php endif; ?>
                    </div>


					<div class="mb10">
                        <label class="control-label"><?php echo __('Avatar'); ?></label>
						<?php echo $form['profile_pic']->render(array('class' => 'form-control')) ?>
						<?php if($form['profile_pic']->hasError()): ?>
						<div class="alert alert-warning">
						<?php echo $form['profile_pic']->renderError() ?>
						</div>
						<?php endif; ?>
                    </div>
					<div class="mb10">
                        <label class="control-label"><?php echo __('Terms and Conditions'); ?></label>
						<div>
							<?php echo __('By clicking on "Save and Continue" below, you are agreeing to the'); ?> 
							<a href="" target="_blank"><?php echo __('Terms of services'); ?></a> <?php echo __('and the'); ?> <a href="" target="_blank"><?php echo __('Privacy Policy'); ?></a>.
						</div>
                    </div>
					<?php
						echo $form->renderHiddenFields();
					?>

                    <button class="btn btn-success btn-block" type="submit" name="submit_app" id="submit_app" value="submitbuttonvalue"><?php echo __('Save and Continue'); ?></button>
                </form>
            </div><!-- col-sm-6 -->
			</div>
			<div class="col-md-4 col-sm-2">
			</div>
          </div><!-- row -->
        </div>
        <!-- END CONTAINER -->

  </div>
    <!-- END PAGE CONTAINER -->
