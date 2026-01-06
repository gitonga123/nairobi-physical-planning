<?php
use_helper("I18N");

if($sf_user->mfHasCredential("manageinvoices"))
{
?>
<div class="contentpanel">
  <div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Penalties'); ?></h3>
    </div>

    <div class="panel-heading text-right">
            <a class="btn btn-primary" id="newtemplate" href="/backend.php/penalties/new"><?php echo __('+ Add Penalty'); ?></a>
            <a class="btn btn-primary m-l-10" id="back" href="/backend.php/services/index"><?php echo __('Back to Services'); ?></a>
    </div>


  <div class="panel-body">
    <div class="table-responsive">
      <table class="table dt-on-steroids mb0" id="table3">
        <thead>
          <tr>
            <th width="60">#</th>
            <th width="30%"><?php echo __('Title'); ?></th>
            <th class="no-sort"><?php echo __('Permit'); ?></th>
            <th class="no-sort"><?php echo __('Trigger Type'); ?></th>
            <th class="no-sort"><?php echo __('Trigger Period'); ?></th>
            <th class="no-sort"><?php echo __('Amount'); ?></th>
            <th class="no-sort" width="7%"><?php echo __('Actions'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $count = 1;
        ?>
          <?php foreach ($templates as $template): ?>
          <tr id="row_<?php echo $template->getId() ?>">
            <td><?php echo $count++; ?></td>
            <td><?php echo $template->getDescription() ?></td>
            <td><?php echo $template->getTemplate()->getTitle(); ?></td>
            <td><?php echo ($template->getTriggerType() == 1)?"Days":"Months";?></td>
            <td><?php echo $template->getTriggerPeriod(); ?></td>
            <td><?php echo $template->getPenaltyAmount(); ?></td>
            <td>
              <a id="edittemplate<?php echo $template->getId(); ?>" href="/backend.php/penalties/edit/id/<?php echo $template->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
              <a id="deletetemplate<?php echo $template->getId(); ?>" onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/penalties/delete/id/<?php echo $template->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
jQuery(document).ready(function(){
  jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",

      // Using aoColumnDefs
      "aoColumnDefs": [
      	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
    	]
  });
});
</script>
<?php
}
else
{
  include_partial("accessdenied");
}
?>
