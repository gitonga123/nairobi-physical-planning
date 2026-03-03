<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managereports"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('JSON Reports'); ?></h3>

            <div class="pull-right">
                <a class="btn btn-primary" id="newpage" href="/plan/jsonreports/new" ><?php echo __('New Report'); ?></a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="50%"><?php echo __('Title'); ?></th>
                    <th width="30%"><?php echo __('Report Type'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($reports as $report): ?>
                    <tr id="row_<?php echo $report->getId() ?>">
                        <td><?php echo $report->getId(); ?></td>
                        <td><?php echo $report->getTitle();  ?></td>
                        <td>
                        <?php 
                            if($report->getType() == 1)
                            {
                                echo "Multiple Applications Summary";
                            }
                            else 
                            {
                                echo "Single Application Summary";
                            }
                        ?>
                        </td>
                        <td align="center">
                            <a id="editpage<?php echo $report->getId(); ?>" href="/plan/jsonreports/edit/id/<?php echo $report->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $report->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/jsonreports/delete/id/<?php echo $report->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
