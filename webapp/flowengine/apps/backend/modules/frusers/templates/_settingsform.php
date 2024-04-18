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
<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
  <strong>Well done!</strong> You successfully updated this user</a>.
</div>

        <div class="panel panel-default">
        
        
        <div class="panel-heading">
				<h3 class="panel-title"><?php echo ($form->getObject()->isNew()?'New Client Details':'Edit Basic Details'); ?>
         </div> 
         
         <div class="panel-body padding-0"> 
         <form id="registration_form" action="<?php if($form->getObject()->isNew()){ echo public_path('backend.php/apply2') ?> <?php }else{ echo public_path('backend.php/frusers/update/id/'.$form->getObject()->getId()); } ?>" method="post" enctype="multipart/form-data" autocomplete="off" data-ajax="false">     
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
            
            <div class="form-bordered form-horizontal">
                <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><i class="bold-label">Full Name</i></label>
                <div class="col-sm-8">
                <input type="text" class="form-control" name="sfApplyApply2[fullname]" id="sfApplyApply2_fullname" value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getFullname(); } ?>"/>
                </div>
                 </div>

                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label">Username</i></label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="sfApplyApply2[username]" id="sfApplyApply2_username"   value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getUsername(); } ?>"/>
                        </div>
                    </div>

                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label">Password</i></label>
                        <div class="col-sm-8">
                        <input type="password" class="form-control" name="sfApplyApply2[password]" id="sfApplyApply2_password" />
                        </div>
                    </div>

                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label">Email</i></label>
                        <div class="col-sm-8">
                        <input type="email" class="form-control" name="sfApplyApply2[email]" id="sfApplyApply2_email"     value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getEmail(); } ?>"/>                    
                        </div>
                    </div>
				
                  <div class="form-group">
                    
                        <label class="col-sm-2 control-label"><i class="bold-label">Phone Number</i></label>
                        <div class="col-sm-8">
                        <input type="mobile" class="form-control" name="sfApplyApply2[mobile]" id="sfApplyApply2_mobile"     value="<?php if(!$form->getObject()->isNew()){ echo $form->getObject()->getProfile()->getMobile(); } ?>"/>
                          </div>
                    </div>
                
				 <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label">Register As</i></label>
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
				
                <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label">Is Active?</i></label>
                        <div class="col-sm-8">
                        <select name="sfApplyApply2[active]" id="sfApplyApply2_active" style="width:10%;">
							<option value="1" <?php if(!$form->getObject()->isNew()){ if($form->getObject()->getIsActive() == "1"){ echo "selected"; } } ?>>Yes</option>
							<option value="0" <?php if(!$form->getObject()->isNew()){ if($form->getObject()->getIsActive() == "0"){ echo "selected"; } } ?>>No</option>
						</select>
						
                    </div>
                </div>
				
				 <div class="form-group">
                        <label class="col-sm-2 control-label"><i class="bold-label">Has Valid Email?</i></label>
                        <div class="col-sm-8">
                        <select name="sfApplyApply2[validated]" id="sfApplyApply2_validated" style="width:10%;">
							<option value="1" <?php if(!$form->getObject()->isNew()){ if($form->getObject()->getIsSuperAdmin() == "1"){ echo "selected"; } } ?>>Yes</option>
							<option value="0" <?php if(!$form->getObject()->isNew()){ if($form->getObject()->getIsSuperAdmin() == "0"){ echo "selected"; } } ?>>No</option>
						</select>
						</div>
                    </div>
              </div><!--formbordered-->
          </div><!--panel-body-->
        
        <div class="panel-footer">
		<button class="btn btn-danger mr10">Reset</button><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue">Submit</button>
		</div>
		

        </form>
  </div>



<script language="javascript">
 jQuery(document).ready(function(){
	$("#submitbuttonname").click(function() {
		 $.ajax({
			url: '<?php if($form->getObject()->isNew()){ echo public_path('backend.php/apply2') ?> <?php }else{ echo public_path('backend.php/frusers/update/id/'.$form->getObject()->getId()); } ?>',
			cache: false,
			type: 'POST',
			data : $('#registration_form').serialize(),
			success: function(json) {
				$('#alertdiv').attr("style", "display: block;");
				$("html, body").animate({ scrollTop: 0 }, "slow");
			}
		});
		return false;
	 });
	});
</script>