<?php
use_helper("I18N");
if($sf_user->mfHasCredential("managecurrencies"))
{
?>
<?php use_helper('I18N', 'Date') ?>

<div class="panel panel-dark">
<div class="panel-heading">
			<h3 class="panel-title"><?php echo __('Currencies'); ?></h3>
</div>
<div class="panel-heading">
	<a class="btn btn-primary settings-margin42" id="newfee" href="<?php echo url_for('/plan/currencies/new') ?>"><?php echo __('New Currency'); ?></a>     
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
     
     <th class="no-sort" width="7%"><?php echo __('State / Country'); ?></th>
      <th class="no-sort" width="7%"><?php echo __('Name'); ?></th>
     <th class="no-sort" width="7%"><?php echo __('ISO Code'); ?></th>
      
      <th class="no-sort" width="7%"><?php echo __('Actions'); ?></th>
    </tr>
  </thead>
    <tbody>
    <?php foreach ($currenciess as $c): ?>
    <tr id="row_<?php echo $c->getId() ?>">
		
                <td><a href="<?php echo url_for('/plan/currencies/edit?id='.$c->getId()) ?>"><?php echo $c->getState() ?></a></td>
		<td><a href="<?php echo url_for('/plan/currencies/edit?id='.$c->getId()) ?>"><?php echo $c->getName() ?></a></td>
		<td><a href="<?php echo url_for('/plan/currencies/edit?id='.$c->getId()) ?>"><?php echo $c->getCode() ?></a></td>
		
    <td>					
	  <a id="editfee<?php echo $c->getId() ?>" href="<?php echo url_for('/plan/currencies/edit?id='.$c->getId()) ?>" alt="Edit" title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
     
      <a id="deletefee<?php echo $c->getId() ?>" href="<?php echo url_for('/plan/currencies/delete?id='.$c->getId()) ?>" alt="Delete" title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i class="fa fa-trash-o"></i></span></a>
	 

           
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
