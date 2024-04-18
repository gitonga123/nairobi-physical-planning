<div class="table-responsive">
    <table class="table dt-on-steroids mb0" id="table3">
    <thead>
    <tr>
        <th class="no-sort"><?php echo __("#"); ?></th>
        <th width="600px"><?php echo __("Application"); ?></th>
        <th><?php echo __("Status"); ?></th>
        <th class="no-sort"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($tasks as $task): ?>
    <tr>
        <td><?php echo $task->getId(); ?></td>
        <td><a href="/backend.php/tasks/view?id=<?php echo $task->getId(); ?>"><?php echo $task->getApplication()->getApplicationId(); ?></a></td>
        <td><?php echo $task->getStatusName(); ?></td>
        <td>
            <a  title='<?php echo __('View Task'); ?>' href='<?php echo public_path("backend.php/tasks/view?id=".$task->getId()); ?>'> <span class="label label-primary"><i class="fa fa-eye"></i></span></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
</div><!-- table-responsive -->

<script language='javascript'>
  jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",

      // Using aoColumnDefs
      "aoColumnDefs": [
      	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
    	]
    });
</script>