<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managefees") && $sf_user->mfHasCredential("code_access_rights"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Fees'); ?></h3>
        </div>


        <div class="panel-heading text-right">
            <a class="btn btn-primary" id="newpage" href="/plan/fees/new" ><?php echo __('+ Add Fee'); ?></a>
        </div>

        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="40%"><?php echo __('Description'); ?></th>
                    <th width="40%"><?php echo __('Amount'); ?></th>
                    <th width="40%"><?php echo __('Code'); ?></th>
                    <th width="40%"><?php echo __('Category'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($fees as $fee): ?>
                    <tr id="row_<?php echo $fee->getId() ?>">
                        <td><?php echo $fee->getId(); ?></td>
                        <td><?php echo $fee->getDescription();  ?></td>
                        <td><?php echo $fee->getAmount();  ?></td>
                        <td><?php echo $fee->getFeeCode();  ?></td>
                        <td><?php echo $fee->getFeeCategory();  ?></td>
                        <td align="center">
                            <a id="editpage<?php echo $fee->getId(); ?>" href="/plan/fees/edit/id/<?php echo $fee->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="editpage<?php echo $fee->getId(); ?>" href="/plan/fees/feedublicate/id/<?php echo $fee->getId(); ?>" title="<?php echo __('Dublicate'); ?>"><span class="label label-warning"><i class="fa fa-files-o" aria-hidden="true"></i></span></a>
                            <a id="deletepage<?php echo $fee->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/fees/delete/id/<?php echo $fee->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
