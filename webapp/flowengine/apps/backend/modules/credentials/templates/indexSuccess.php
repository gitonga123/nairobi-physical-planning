<?php
use_helper("I18N");

if($sf_user->mfHasCredential("manageroles"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Roles'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                <a class="btn btn-primary" id="newpage" href="/backend.php/credentials/new" ><?php echo __('+ Add Role'); ?></a>
        </div>


        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="30%"><?php echo __('Title'); ?></th>
                    <th width="50%"><?php echo __('Description'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($roles as $role): ?>
                    <tr id="row_<?php echo $role->getId() ?>">
                        <td><?php echo $role->getId(); ?></td>
                        <td><?php echo $role->getName();  ?></td>
                        <td><?php echo $role->getDescription();  ?></td>
                        <td align="center">
                            <a id="editpage<?php echo $role->getId(); ?>" href="/backend.php/credentials/edit/id/<?php echo $role->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $role->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/credentials/delete/id/<?php echo $role->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
