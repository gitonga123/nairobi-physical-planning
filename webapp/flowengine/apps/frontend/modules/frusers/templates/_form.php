 
        <form  class="form-bordered form-horizontal" id="registration_form" action="/index.php/frusers/update/id/<?php echo $form->getObject()->getId();  ?>" method="post" enctype="multipart/form-data"   autocomplete="off" data-ajax="false">
		   <?php echo $form->renderGlobalErrors(); ?>
            <?php
            if($sf_user->hasFlash("notice"))
            {
                ?>
                <div id="notification">
                    <div class="status warning" style="background: url('/assets_backend/images/bg_fade_yellow_med.png') repeat-x scroll center top #FEEB9C;  border: 3px solid #BF9900; border-radius: 10px 10px 10px 10px; clear: both; margin-bottom: 20px; overflow: auto; padding: 8px 10px 5px; text-shadow: 1px 1px 1px #FFFFFF;">
                        <p class="closestatus" style="color: #FFFFFF; float: right; margin-left: 10px; text-align: center;"><a title="Close" href="#" style="border-radius: 5px 5px 5px 5px; color: #FFFFFF; display: block; height: 10px; line-height: 0.6em; padding: 5px; position: relative; text-decoration: none; text-shadow: none; top: -2px; width: 10px;background: none repeat scroll 0 0 #BF9900;" onclick="document.getElementById('notification').innerHTML = '';">x</a></p>
                        <p><img alt="Warning" src="/assets_backend/images/icons/icon_warning.png"> &nbsp;<span style="color: #BF9900; font-weight: 700;font-family: Arial,Helvetica,sans-serif;font-size: 13px;">Attention! <?php echo $sf_user->getFlash("notice"); ?></p>
                    </div>
                </div>
                <?php
            }
            ?>
            <?php if(isset($form['_csrf_token'])): ?>
            <?php echo $form['_csrf_token']->render(); ?>
            <?php endif; ?>
            
            
            <div class="panel-body panel-body-nopadding">
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_full_name'); ?></i></label>
                    <div class="col-sm-9">
                    <input type="text" class="form-control" name="sfApplyApply2[fullname]" id="sfApplyApply2_fullname" value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getFullname(); } ?>"/>
                  </div><!-- col-sm-8 -->
                </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_username'); ?></i></label>
                    <div class="col-sm-9">
                    <input type="text" class="form-control" name="sfApplyApply2[username]" id="sfApplyApply2_username"   value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getUsername(); } ?>"/>
                  </div><!-- col-sm-8 -->
                </div>
              
              
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_password'); ?></i></label>
                    <div class="col-sm-9">
					<input type="password" class="form-control" name="sfApplyApply2[password]" id="sfApplyApply2_password"/>
                  </div><!-- col-sm-8 -->
                </div>
                
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_email_address'); ?></i></label>
                    <div class="col-sm-9">
                    <input type="email" class="form-control" name="sfApplyApply2[email]" id="sfApplyApply2_email"  value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getEmail(); } ?>"/>
                  </div><!-- col-sm-6 -->
                </div>
    
				
                        <input type='hidden' name="sfApplyApply2[active]" id="sfApplyApply2_active" value='<?php echo $form->getObject()->getIsActive(); ?>'>
		
                        <input type='hidden' name="sfApplyApply2[validated]" id="sfApplyApply2_validated" value='<?php echo $form->getObject()->getIsSuperAdmin(); ?>'>
						
			</div><!--panel-body-->
			
			<div class="panel-footer">
				<button class="btn btn-primary" onClick="window.location='/index.php/settings';"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_back'); ?></button>
				<button class="submit btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_submit'); ?></button>
			</div>
        
		
            </fieldset>
        </form>

