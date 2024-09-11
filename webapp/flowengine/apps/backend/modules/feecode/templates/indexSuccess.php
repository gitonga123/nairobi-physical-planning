<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managefees"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Fee Codes'); ?></h3>
        </div>


        <div class="panel-heading text-right">
            <a class="btn btn-primary" id="newpage" href="/plan/feecode/new" ><?php echo __('+ Add Fee Code'); ?></a>
        </div>

        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th ><?php echo __('Service id'); ?></th>
                    <th ><?php echo __('Service'); ?></th>
                    <th ><?php echo __('Fixed?'); ?></th>
                    <th ><?php echo __('Amount'); ?></th>
                    <th ><?php echo __('Zone'); ?></th>
                    <th class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($feecodes as $feecode): ?>
                    <tr id="row_<?php echo $feecode->getId() ?>">
                        <td><?php echo $feecode->getId(); ?></td>
                        <td><?php echo $feecode->getServiceId(); ?></td>
                        <td><?php echo $feecode->getServiceName();  ?></td>
                        <td><?php echo $feecode->getFixed();  ?></td>
                        <td><?php echo $feecode->getAmount();  ?></td>
                        <td><?php echo $feecode->getZone();  ?></td>
                        <td align="center">
                            <a id="feecodeedit<?php echo $feecode->getId(); ?>" href="/plan/feecode/edit/id/<?php echo $feecode->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="feecodedelete<?php echo $feecode->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/feecode/delete/id/<?php echo $feecode->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
