<?php
/**
 * edituserSuccess.php template.
 *
 * Edit reviewer details
 *
 * @package    backend
 * @subpackage users
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>

<div class="pageheader">
<h2><i class="fa fa-envelope"></i><?php echo __('Reviewers'); ?></h2>
<div class="breadcrumb-wrapper">
<span class="label"><?php echo __('You are here'); ?>:</span>
<ol class="breadcrumb">
<li>
<a href=""><?php echo __('Home'); ?></a>
</li>
<li>
<a href=""><?php echo __('Reviewers'); ?></a>
</li>
<li class="active"><?php echo __('Password Reset'); ?></li>
</ol>
</div>
</div>

<div class="contentpanel">
<div class="row">

<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
    Password Reset
</h3>
</div>       
			
	<div class="panel-body padding-0">


		<div id="basicWizard" class="basic-wizard">


			<div class="tab-content tab-content-nopadding">


				<div class="tab-pane active" id="tabs-1">

					<?php
					if($success)
					{
					?>
					<div class="alert alert-success" id="alertdiv" name="alertdiv">
					  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
					  <strong><?php echo __('Well done'); ?></strong> <?php echo __("An email has been sent to the reviewer's account for them to reset their password"); ?></a>.
					</div>    
					<?php
					}
					else
					{
					?>
					<div class="alert alert-danger" id="alertdiv" name="alertdiv">
					  <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
					  <strong><?php echo __('Sorry'); ?></strong> <?php echo __("Could not reset the reviewer's account password"); ?></a>.
					</div>      
					<?php
					}
					?>
				</div>

			</div>    

		</div><!--tabs-2-->

	</div><!--basicWizard-->

</div><!--Panel-body-->

</div><!--panel-default-->





</div>
</div>
