<?php
/**
 * editSuccess.php template.
 *
 * Allows client to edit basic details
 *
 * @package    frontend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>

    <div class="pageheader">
      <h2><i class="fa fa-home"></i>Edit Basic Details</h2>
      <div class="breadcrumb-wrapper">
        
        <ol class="breadcrumb">
          <li><a href="#"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_account_settings'); ?></a></li>
          <li class="active"><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_edit_basic_details'); ?></li>
        </ol>
      </div>
    </div>
    
    
<div class="contentpanel">
<div class="row"> 
 <div class="panel panel-dark">
        <div class="panel-heading">
          <h4 class="panel-title">Basic Details</h4>
          <p>Edit your basic details</p>
        </div>
        
 <?php include_partial('form', array('form' => $form)) ?>
 
 </div><!--panel-->

</div><!--panel-row-->
</div><!--contentpanel-->          
               




