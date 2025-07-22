<?php
/**
 * sharedSuccess.php template.
 *
 * Shows success message if application is shared successfully
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>

<div class="content">

<!--ul class="breadcrumb">
        <li><a href="#"><?php echo __('Applications') ?></a> <span class="divider">/</span></li>
        <li class="active"><?php echo __('Share Successful') ?></li>
        <li></li>
</ul-->    <!-- Docs nav-->


<div class="row">

<?php //include_partial('index/sidemenu', array('' => '')) ?>

<div class="span9 padded-20">


<div class="alert alert-success" style="text-align:center;">
<?php echo __('You have successfully shared the application') ?>.
</div>


</div><!-- /.span9 -->
						
</div><!-- /.row -->
			



