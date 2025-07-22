<?php
use_helper("I18N");
?>
<?php
if($sf_user->mfHasCredential("managememberdatabase"))
{
  $_SESSION['current_module'] = "membersdatabase";
  $_SESSION['current_action'] = "association";
  $_SESSION['current_id'] = "";
?>
<div class="panel panel-dark">
<div class="panel-heading">
			<h3 class="panel-title"><?php echo __('Validation Record'); ?></h3>
				<div class="pull-right">
            <a class="btn btn-primary-alt settings-margin42" id="newplot" href="<?php echo public_path(); ?>plan/membersdatabase/new"><?php echo __('New Validation Record'); ?></a>

            <script language="javascript">
            jQuery(document).ready(function(){
              $( "#newplot" ).click(function() {
                  $("#contentload").load("<?php echo public_path(); ?>plan/membersdatabase/new");
              });
            });
            </script>
</div>
</div>
		
 
<div class="panel panel-body panel-body-nopadding ">

<div class="table-responsive">
<table class="table dt-on-steroids mb0" id="table3">
    <thead>
   <tr>
      <th class="no-sort" style="width: 10px;"><input type='checkbox' name='batchall' onclick="boxes = document.getElementsByTagName('input'); for(var index = 0; index < boxes.length; index++) { box = boxes[index]; if (box.type == 'checkbox' && box.name == 'batch') { if(this.checked == true){ box.checked = false; }else{ box.checked = true; } } } "></th>
      
      <th width="60">#</th>
      <th class="no-sort"><?php echo __('Membership No'); ?></th>
      <th><?php echo __('Full Name'); ?></th>
      <th class="no-sort" width="17%"><?php echo __('Actions'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
		$count = 1;
	?>
    <?php foreach ($records as $record): ?>
    <tr id="row_<?php echo $record->getId() ?>">
	  <td><input type='checkbox' name='batch' id='batch_<?php echo $record->getId() ?>' value='<?php echo $record->getId() ?>'></td>
      <td><?php echo $count++; ?></td>
      <td><?php echo $record->getMembersNo() ?></td>
      <td><?php echo $record->getFullName() ?></td>
      <td>
						<a id="editrecord<?php echo $record->getId(); ?>" href="<?php echo public_path(); ?>plan/membersdatabase/edit/id/<?php echo $record->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
						<a id="deleterecord<?php echo $record->getId(); ?>" href="<?php echo public_path(); ?>plan/membersdatabase/delete/id/<?php echo $record->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i class="fa fa-trash-o"></i></span></a>
						<a id="validaterecord<?php echo $record->getId(); ?>" href="<?php echo public_path(); ?>plan/membersdatabase/validate/id/<?php echo $record->getId(); ?>" title="<?php echo __('Validate'); ?>"><span class="badge badge-primary"><i class="fa fa-check"></i></span></a>

            <script language="javascript">
            jQuery(document).ready(function(){
              $( "#editrecord<?php echo $record->getId(); ?>" ).click(function() {
                  $("#contentload").load("<?php echo public_path(); ?>plan/membersdatabase/edit/id/<?php echo $record->getId(); ?>");
              });
              $( "#deleterecord<?php echo $record->getId(); ?>" ).click(function() {
                  if(confirm('Are you sure you want to delete this record?')){
                    $("#contentload").load("<?php echo public_path(); ?>plan/membersdatabase/delete/id/<?php echo $record->getId(); ?>");
                  }
                  else
                  {
                    return false;
                  }
              });
              $( "#validaterecord<?php echo $record->getId(); ?>" ).click(function() {
                  if(confirm('Are you sure you want to validate this record for submission of applications?')){
                    $("#contentload").load("<?php echo public_path(); ?>plan/membersdatabase/validate/id/<?php echo $record->getId(); ?>");
                  }
                  else
                  {
                    return false;
                  }
              });
            });
            </script>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
	<tfoot>
   <tr><td colspan='8' style='text-align: left;'>
   <select id='batch_action' name='batch_action' onChange="if(this.value != ''){if(confirm('Are you sure?')){ batch('plot', this.options[this.selectedIndex].text, this.value); document.getElementById('default').selected='selected'; }}">
   <option id='default' value=''><?php echo __('Choose an action'); ?>..</option>
   <option value='delete'><?php echo __('Set As Deleted'); ?></option>
   </select>
   </td></tr>
   </tfoot>
</table>
</div>
</div>
<script>
  jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",
     
      // Using aoColumnDefs
      "aoColumnDefs": [
      	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
    	]
    });
    
</script><?php
}
else
{
  include_partial("settings/accessdenied");
}
?>