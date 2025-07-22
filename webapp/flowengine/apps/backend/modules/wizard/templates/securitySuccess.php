<?php
  use_helper("I18N");
?>
<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>

<div class="pageheader">
  <h2><i class="fa fa-home"></i> <?php echo __('Security Setup Wizard'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('Security'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
  <div class="row">
   <div class="col-md-12">

<!-- BASIC WIZARD -->
<div id="progressWizard" class="basic-wizard bordered-wizard">

<ul class="nav nav-pills nav-justified">
  <li <?php if($step == 1){ ?>class="active"<?php } ?>><a href="#ptab1" <?php if($step == 1){ ?>data-toggle="tab"<?php } ?>><span><?php echo __('Step'); ?> 1:</span> <?php echo __('Groups'); ?></a></li>
  <li <?php if($step == 2){ ?>class="active"<?php } ?>><a href="#ptab2" <?php if($step == 2){ ?>data-toggle="tab"<?php } ?>><span><?php echo __('Step'); ?> 2:</span> <?php echo __('Roles'); ?></a></li>
  <li <?php if($step == 3){ ?>class="active"<?php } ?>><a href="#ptab3" <?php if($step == 3){ ?>data-toggle="tab"<?php } ?>><span><?php echo __('Step'); ?> 3:</span> <?php echo __('Reviewers'); ?></a></li>
  <li <?php if($step == 4){ ?>class="active"<?php } ?>><a href="#ptab4" <?php if($step == 4){ ?>data-toggle="tab"<?php } ?>><span><?php echo __('Step'); ?> 4:</span> <?php echo __('Done'); ?></a></li>
</ul>
  
<div class="tab-content tab-content-nopadding">
  
  <div class="pt20 tab-pane <?php if($step == 1){ ?>active<?php } ?>" id="ptab1">
    <form class="form-horizontal form-bordered" method="post" action="/plan/wizard/security">
    <input type="hidden" name="step" value="1" />
    <div id="groups">
          <?php
          $q = Doctrine_Query::create()
             ->from("MfGuardGroup a");
          $departments = $q->execute();
          if(sizeof($departments) <= 0)
          {
          ?>
          	<div class="form-group">
            	<label class="col-sm-2 control-label"><?php echo __('Name'); ?></label>
            	<div class="col-sm-8">
              <input type="text" name="name[]" readonly="readonly" class="form-control" value="System Administrators" required/>
            </div>
          	</div>  
            <div class="form-group"> 
            	<label class="col-sm-2 control-label"><?php echo __('Description'); ?></label>
            	<div class="col-sm-8">
              <textarea name="description[]" readonly="readonly"  class="form-control" required/>System administrators who will manage the system</textarea>
            </div>
          	</div>
          <?php 
          }
          ?>
     </div>
     <div class="form-group">  
      <div class="col-sm-2"> 
      	<button type="button" class="btn btn-primary pull-right" id="addgroup"><?php echo __('Add Group'); ?></button>
      </div>
     </div>
     <div class="panel-footer">
      <button type="submit" class="btn btn-success pull-right" id="submitgroups"><?php echo __('Next'); ?></button>
     </div>
     <script language="javascript">
        jQuery(document).ready(function(){
          $( "#addgroup" ).click(function() {
              $("#groups").append("<div class='form-group'><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Name'); ?></label><span class='col-sm-8'> <input type='text' name='name[]' onKeyUp='checkgroup(this.value);'  class='form-control' /></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Description'); ?></label><span class='col-sm-8'><textarea name='description[]' class='form-control' /></textarea></span></div><span class='col-sm-10'><a href='#' class='panel-close pull-right' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
          });
        });
      </script>
    </form>
  </div>
  

  
  <div class="pt20 tab-pane <?php if($step == 2){ ?>active<?php } ?>" id="ptab2">
    <form class="form-horizontal form-bordered" method="post" action="/plan/wizard/security">
    <input type="hidden" name="step" value="2" />
      <?php
	  $q = Doctrine_Query::create()
	     ->from("MfGuardGroup a")
		   ->orderBy("a.id ASC");
    $groups = $q->execute();

	  
	  $q = Doctrine_Query::create()
	  	 ->from("MfGuardPermission a")
		   ->orderBy("a.name ASC");
    $permissions = $q->execute();
	  
    $count = 0;
	  foreach($groups as $group)
	  {
      if(sizeof($group->getUsers()) > 0)
      {
        continue;
      }
      $count++;
	  ?>
      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo $group->getName(); ?></label>
        <div class="col-sm-8">
          <select multiple name="group<?php echo $group->getId(); ?>[]" id="group_<?php echo $group->getId(); ?>_roles">
            <option value=""><?php echo __('Choose One'); ?></option>
            <?php 
      			foreach($permissions as $permission)
      			{
                $selected = "";
                if($count == 1)
                {
                  $selected = "selected";
                }
      			?>
                  <option value="<?php echo $permission->getId() ?>" <?php echo $selected; ?>><?php echo $permission->getDescription(); ?></option>
                  <?php
      			}
      			?>
          </select>
        </div>
      </div>
       <script language="javascript">
       jQuery(document).ready(function(){

        var demo<?php echo $group->getId(); ?> = $('[id="group_<?php echo $group->getId(); ?>_roles"]').bootstrapDualListbox();

      });
       </script>
       <?php
	  }
	  ?>
	  <div class="panel-footer">
      	<button type="submit" class="btn btn-success" id="submitpermissions" style="float: right;"><?php echo __('Next'); ?></button>
      </div>
    </form>
  </div>
  <div class="pt20 tab-pane <?php if($step == 3){ ?>active<?php } ?>" id="ptab3">
    <form class="form-horizontal form-bordered" method="post" action="/plan/wizard/security">
    <input type="hidden" name="step" value="3" />
    <div id="users">
    </div>
    <div class="form-group">
     <div class="col-sm-2">  
      <button type="button" class="btn btn-primary pull-right" id="adduser"><?php echo __('Add Reviewer'); ?></button>
     </div>
     </div>
     <div class="panel-footer">  
      <button type="submit" class="btn btn-success pull-right" id="submitusers"><?php echo __('Next'); ?></button>
     </div> 
      <script language="javascript">
        jQuery(document).ready(function(){
          $( "#adduser" ).click(function() {
              $("#users").append("<div class='form-group'><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Username'); ?></label><span class='col-sm-8'><input type='text' name='username[]' onKeyUp='checkuser(this.value);' class='form-control' placeholder='<?php echo __('Username'); ?>' required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Name'); ?></label><span class='col-sm-8'><input type='text' name='firstname[]' class='form-control mb10' placeholder='<?php echo __('First Name'); ?>' required='required'/><input type='text' name='lastname[]' class='form-control' placeholder='<?php echo __('Last Name'); ?>' required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Password'); ?></label><span class='col-sm-8'><input type='password' name='userpassword[]' class='form-control'  required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Email'); ?></label><span class='col-sm-8'><input type='text' name='useremail[]' onKeyUp='checkemail(this.value);' class='form-control'  required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Group'); ?></label><span class='col-sm-8'><select class='form-control' name='group[]' required='required'><option value=''><?php echo __('Choose One'); ?></option><?php 
			    $q = Doctrine_Query::create()
				 ->from("MfGuardGroup a")
				 ->orderBy("a.name ASC");
			    $groups = $q->execute();
				
                foreach($groups as $group)
                {
                ?><option value='<?php echo $group->getId() ?>'><?php echo $group->getName(); ?></option><?php
                }
                ?></select></span></div><span class='col-sm-10'><a href='#' class='panel-close pull-right' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
          });
        });
      </script>
    </form>
  </div>
  
  <div class="tab-pane <?php if($step == 4){ ?>active<?php } ?>" id="ptab4">
  <div class='panel panel-default'>
   <div class='panel-body'>  
	   <div class="alert alert-success">
			<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
				<strong><?php echo __('Done'); ?>!</strong>
				<?php echo __('Changes have already taken effect. Return to dashboard to continue with other configuration wizards'); ?>
		</div>

   </div>
   <div class='panel-footer'>  
      <button class="btn btn-primary pull-right" onClick="window.location='/plan';"><?php echo __('Back to Dashboard'); ?></button>
   </div>   
  </div>    
  </div>
  
</div><!-- tab-content -->

</div><!-- #basicWizard -->

</div>
</div>
</div>



<script>

function checkgroup(group)
{
  var xmlhttp;
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      if(xmlhttp.responseText == "fail")
      {
        alert("The name you entered already exists");
        document.getElementById("submitgroups").disabled = true;
      }
      else
      {
        document.getElementById("submitgroups").disabled = false;
      }
    }
  }
  xmlhttp.open("POST","/plan/groups/checknamemin",true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xmlhttp.send("name=" + group);
}

function checkuser(username)
{
  var xmlhttp;
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      if(xmlhttp.responseText == "fail")
      {
        alert("The username you entered already exists");
        document.getElementById("submitusers").disabled = true;
      }
      else
      {
        document.getElementById("submitusers").disabled = false;
      }
    }
  }
  xmlhttp.open("POST","/plan/users/checkusermin",true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xmlhttp.send("username=" + username);
}

function checkemail(email)
{
  var xmlhttp;
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      if(xmlhttp.responseText == "fail")
      {
        alert("The email you entered already exists");
        document.getElementById("submitusers").disabled = true;
      }
      else
      {
        document.getElementById("submitusers").disabled = false;
      }
    }
  }
  xmlhttp.open("POST","/plan/users/checkemailmin",true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xmlhttp.send("email=" + email);
}

jQuery(document).ready(function(){

  // Basic Wizard
  jQuery('#basicWizard').bootstrapWizard();
  
  // Progress Wizard
  $('#progressWizard').bootstrapWizard({
    'nextSelector': '.next',
    'previousSelector': '.previous',
    onNext: function(tab, navigation, index) {
      var $total = navigation.find('li').length;
      var $current = index+1;
      var $percent = ($current/$total) * 100;
      jQuery('#progressWizard').find('.progress-bar').css('width', $percent+'%');
    },
    onPrevious: function(tab, navigation, index) {
      var $total = navigation.find('li').length;
      var $current = index+1;
      var $percent = ($current/$total) * 100;
      jQuery('#progressWizard').find('.progress-bar').css('width', $percent+'%');
    },
    onTabShow: function(tab, navigation, index) {
      var $total = navigation.find('li').length;
      var $current = index+1;
      var $percent = ($current/$total) * 100;
      jQuery('#progressWizard').find('.progress-bar').css('width', $percent+'%');
    }
  });
  
  // Disabled Tab Click Wizard
  jQuery('#disabledTabWizard').bootstrapWizard({
    tabClass: 'nav nav-pills nav-justified nav-disabled-click',
    onTabClick: function(tab, navigation, index) {
      return false;
    }
  });
  
  // With Form Validation Wizard
  var $validator = jQuery("#firstForm").validate({
    highlight: function(element) {
      jQuery(element).closest('.form-group').removeClass('has-success').addClass('has-error');
    },
    success: function(element) {
      jQuery(element).closest('.form-group').removeClass('has-error');
    }
  });
  
  jQuery('#validationWizard').bootstrapWizard({
    tabClass: 'nav nav-pills nav-justified nav-disabled-click',
    onTabClick: function(tab, navigation, index) {
      return false;
    },
    onNext: function(tab, navigation, index) {
      var $valid = jQuery('#firstForm').valid();
      if(!$valid) {
        
        $validator.focusInvalid();
        return false;
      }
    }
  });	  
  
  
});
</script>
