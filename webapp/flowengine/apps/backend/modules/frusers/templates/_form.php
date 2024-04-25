<?php
/**
 * _form.php template.
 *
 * Shows form for creating a new client account (basic details)
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>


<div class="panel panel-default">


        <div class="panel-heading">
				<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New User Details'):__('Edit Basic Details')); ?>
        </div>

         <div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
           <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
           <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this user'); ?>.
         </div>

        <div class="alert alert-danger" id="errordiv" name="errordiv" style="display: none;">
          <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
          <strong><?php echo __('Error'); ?>!</strong>
            <div id="errorresponse" name="errorresponse"></div>
        </div>

         <div class="panel-body padding-0">
         <form id="registration_form" action="<?php if($form->getObject()->isNew()){ ?>/backend.php/apply2 <?php }else{ ?>/backend.php/frusers/update/id/<?php echo $form->getObject()->getId(); } ?>" method="post" enctype="multipart/form-data" autocomplete="off" data-ajax="false">
				 <?php echo $form->renderGlobalErrors(); ?>
            <?php
            if($sf_user->hasFlash("notice"))
            {
                ?>
                 <div class="alert alert-danger" id="errordiv" name="errordiv">
                     <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
                     <strong><?php echo __('Error'); ?>!</strong><?php echo $sf_user->getFlash("notice"); ?>
                 </div>
                <?php
            }
            ?>
            <?php if(isset($form['_csrf_token'])): ?>
            <?php echo $form['_csrf_token']->render(); ?>
            <?php endif; ?>

            <div class="form-bordered form-horizontal">
                <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><i class="bold-label"><?php echo __('Full Name'); ?></i></label>
                <div class="col-sm-8">
                <input type="text" class="form-control" name="sfApplyApply2[fullname]" id="sfApplyApply2_fullname" value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getFullname(); } ?>" required="required"/>
                </div>
                 </div>

                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('User ID'); ?></i></label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="sfApplyApply2[username]" id="sfApplyApply2_username"   value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getUsername(); } ?>" required="required"/>
                        </div>
                    </div>



                  <div id="usernameresult" name="usernameresult"></div>

                  <script language="javascript">
                    $('document').ready(function(){
                      $('#sfApplyApply2_username').keyup(function(){
                        $.ajax({
                                  type: "POST",
                                  url: "/backend.php/frusers/checkuser",
                                  data: {
                                      'name' : $('input:text[id=sfApplyApply2_username]').val()
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

              <div class="form-group">
                      <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Email'); ?></i></label>
                      <div class="col-sm-8">
                      <input type="text" class="form-control" name="sfApplyApply2[email]" id="sfApplyApply2_email"     value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getEmail(); } ?>" required="required"/>
                      </div>
                  </div>



                <div id="emailresult" name="emailresult"></div>

                <script language="javascript">
                  $('document').ready(function(){
                    $('#sfApplyApply2_email').keyup(function(){
                      $.ajax({
                                type: "POST",
                                url: "/backend.php/frusers/checkemail",
                                data: {
                                    'email' : $('input:text[id=sfApplyApply2_email]').val()
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



                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Password'); ?></i></label>
                        <div class="col-sm-8">
                        <input type="password" class="form-control" name="sfApplyApply2[password]" id="sfApplyApply2_password"  required="required"/>
                        </div>
                    </div>



                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Confirm Password'); ?></i></label>
                        <div class="col-sm-8">
                        <input type="password" class="form-control" name="sfApplyApply2[confirmpassword]" id="sfApplyApply2_confirmpassword"  required="required"/>
                        </div>
                    </div>


                    <div id="passwordresult" name="passwordresult"></div>

                    <script language="javascript">
                      $('document').ready(function(){
                        $('#sfApplyApply2_password').keyup(function(){
                          if($('#sfApplyApply2_password').val() == $('#sfApplyApply2_confirmpassword').val() && $('#sfApplyApply2_password').val() != "")
                          {
                            $('#passwordresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords match!</strong></div>');
                          }
                          else
                          {
                            $('#passwordresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords don\'t match!</strong> Try again.</div>');
                          }
                        });
                        $('#sfApplyApply2_confirmpassword').keyup(function(){
                          if($('#sfApplyApply2_password').val() == $('#sfApplyApply2_confirmpassword').val() && $('#sfApplyApply2_password').val() != "")
                          {
                            $('#passwordresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords match!</strong></div>');
                          }
                          else
                          {
                            $('#passwordresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords don\'t match!</strong> Try again.</div>');
                          }
                        });
                      });
                    </script>

                  <div class="form-group">

                        <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Phone Number'); ?></i></label>
                        <div class="col-sm-8">
                        <input type="mobile" class="form-control" name="sfApplyApply2[mobile]" id="sfApplyApply2_mobile"     value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getMobile(); } ?>"/>
                          </div>
                    </div>

				 <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Registered As'); ?></i></label>
                        <div class="col-sm-8">
                        <select name="sfApplyApply2[registeras]" id="sfApplyApply2_registeras" style="width:20%;">
                            <?php
                            $q = Doctrine_Query::create()
                               ->from("SfGuardUserCategories a")
                               ->orderBy("a.name ASC");
                            $cats = $q->execute();
                            foreach($cats as $cat){
                            ?>
							<option value="<?php echo $cat->getId(); ?>" <?php if(!$form->getObject()->isNew()){ if($form->getObject()->getProfile()->getRegisteras() == $cat->getId()){ echo "selected"; } } ?>><?php echo $cat->getName(); ?></option>
							<?php
						    }
						    ?>
						</select>
                        </div>

                    </div>

              </div><!--formbordered-->
          </div><!--panel-body-->

        <div class="panel-footer">
		<button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
		</div>


        </form>
  </div>
