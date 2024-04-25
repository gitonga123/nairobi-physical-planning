<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed conditions settings");
?>
<?php
if($sf_user->mfHasCredential("manageconditions"))
{
?>
<div class="panel panel-dark">
<div class="panel-heading">
	<h3 class="panel-title"><?php echo __('Conditions'); ?></h3>
</div>
<div class="panel-heading">
	<a class="btn btn-primary settings-margin42" id="newcondition" href="<?php echo url_for('/backend.php/conditionsmng/new/filter/'.$filter) ?>"><?php echo __('New Condition'); ?></a>
</div>


<div class="panel panel-body panel-body-nopadding ">
<div class="table-responsive">
<table class="table dt-on-steroids mb0" id="table3">
    <thead>
   <tr>
      <th width="60">#</th>
      <th><?php echo __('Short name'); ?></th>
      <th><?php echo __('Description'); ?></th>
      <th width="7%"><?php echo __('Actions'); ?></th>
    </tr>
     </thead>
    <tbody>
 <?php
	$count = 1;
 ?>
 <?php foreach ($conditions as $conditions_of_approval): ?>
    <tr id="row_<?php echo $conditions_of_approval->getId() ?>">
	    <td><?php echo $count++; ?></td>
      <td><?php echo $conditions_of_approval->getShortName() ?></td>
      <td><?php echo $conditions_of_approval->getDescription() ?></td>
      <td>
     	  <a id="editcondition<?php echo $conditions_of_approval->getId() ?>" href="<?php echo url_for('/backend.php/conditionsmng/edit/id/'.$conditions_of_approval->getId().'/filter/'.$filter); ?>" title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
      	<a id="deletecondition<?php echo $conditions_of_approval->getId() ?>" href="<?php echo url_for('/backend.php/conditionsmng/delete/id/'.$conditions_of_approval->getId().'/filter/'.$filter); ?>" title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i class="fa fa-trash-o"></i></span></a>


        <script language="javascript">
        /*$(document).ready(function(){
          $( "#editcondition<?php echo $conditions_of_approval->getId() ?>" ).click(function() {
              $("#loadinner").load("<?php echo url_for('/backend.php/conditionsmng/edit/id/'.$conditions_of_approval->getId().'/filter/'.$filter); ?>");
          });
          $( "#deletecondition<?php echo $conditions_of_approval->getId() ?>" ).click(function() {
              if(confirm('Are you sure you want to delete this group?')){
                $("#loadinner").load("<?php echo url_for('/backend.php/conditionsmng/delete/id/'.$conditions_of_approval->getId().'/filter/'.$filter); ?>");
              }
              else
              {
                return false;
              }
          });
        });*/
        </script>


  </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
	<tfoot>
   <tr><td colspan='8' style='text-align: left;'>
   <select id='batch_action' name='batch_action' onChange="if(this.value != ''){if(confirm('Are you sure?')){ batch('conditionsmng', this.options[this.selectedIndex].text, this.value); document.getElementById('default').selected='selected'; }}">
   <option id='default' value=''><?php echo __('Choose an action'); ?>..</option>
   <option value='delete'><?php echo __('Set As Deleted'); ?></option>
   </select>
   </td></tr>
   </tfoot>
</table>
<div class="panel-footer">
	<a class="btn btn-primary settings-margin42" href="<?php echo url_for('/backend.php/permittemplates/index/filter/'.$filter) ?>"><?php echo __('Back to Permits'); ?></a>
</div>
<script>
  $('#table3').dataTable({
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
