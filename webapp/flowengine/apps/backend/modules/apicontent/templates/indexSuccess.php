<?php
use_helper("I18N");
if($sf_user->mfHasCredential("managecurrencies"))
{
?>
<?php use_helper('I18N', 'Date') ;
 $otbhelper = new OTBHelper() ;
?>

<div class="panel panel-dark">
<div class="panel-heading">
			<h3 class="panel-title"><?php echo __('API content'); ?></h3>
</div>
<div class="panel-heading">
	<a class="btn btn-primary settings-margin42" id="newfee" href="<?php echo url_for('/plan/apicontent/new') ?>"><?php echo __('New Content'); ?></a>
</div>
 
<div class="panel panel-body panel-body-nopadding ">
    <?php if($sf_user->hasFlash('Success')): ?>
    <div class="alert alert-success"> 
        <button type="button" class="close" data-dismiss="alert"> * </button>
        <b> <?php echo $sf_user->getFlash('Success') ?> </b>
    </div>
    <?php endif; ?>
<div class="table-responsive">
<table class="table dt-on-steroids mb0" id="table3">
    <thead>
   <tr>
     
     <th class="no-sort" width="7%"><?php echo __('#'); ?></th>
      <th class="no-sort" width="7%"><?php echo __('Form'); ?></th>
     <th class="no-sort" width="7%"><?php echo __('API use'); ?></th>
     <th class="no-sort" width="7%"><?php echo __('Merchant'); ?></th>  
     <th class="no-sort" width="7%"><?php echo __('API use diff'); ?></th>  
      <th class="no-sort" width="7%"><?php echo __('Actions'); ?></th>
    </tr>
  </thead>
    <tbody>
    <?php foreach ($apiContent as $c): ?>
    <tr id="row_<?php echo $c->getId() ?>">
		
      <td><?php echo $c->getId() ?></td>
		  <td><?php echo $c->getApForms()->getFormName() ?></td>
		  <td><?php echo $c->getApiusedesc() ?></td>
      <td><?php echo $c->getMerchant() ?></td>
      <td><?php echo $c->getApiUseDiff() ?></td>
      <td>					
	      <a id="editmer<?php echo $c->getId() ?>" href="<?php echo url_for('/plan/apicontent/edit?id='.$c->getId()) ?>" alt="Edit" title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
     
        <a id="deletemer<?php echo $c->getId() ?>" href="<?php echo url_for('/plan/apicontent/delete?id='.$c->getId()) ?>" alt="Delete" title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i class="fa fa-trash-o"></i></span></a>    
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<a name="end"></a>

</div><!--panel-body-->
</div><!--panel-dark-->

<script>
  jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",
     
      // Using aoColumnDefs
      "aoColumnDefs": [
      	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
    	]
    });
    
</script>
<?php
}
else
{
  include_partial("settings/accessdenied");
}
?>
