<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managedepartments"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Departments'); ?></h3>
        </div>

        <div class="panel-heading text-right">
              <a class="btn btn-primary" id="newpage" href="/plan/department/new" ><?php echo __('+ Add  Department'); ?></a>
        </div>
        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="80%"><?php echo __('Title'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($departments as $department): ?>
                    <tr id="row_<?php echo $department->getId() ?>">
                        <td><?php echo $department->getId(); ?></td>
                        <td><?php echo $department->getDepartmentName();  ?></td>
                        <td align="center">
                            <a id="editpage<?php echo $department->getId(); ?>" href="/plan/department/edit/id/<?php echo $department->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $department->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/department/delete/id/<?php echo $department->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>
</div>

<script language='javascript'>
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
