<div class="panel panel-default">
      <div class="panel-heading">
        <div class="panel-title">
            <?php echo __("Task Details"); ?>
        </div>
      </div><!-- panel-heading-->
      <div class="panel-body padding-0">
      
        <table class="table table-special">
            <thead>
                <tr>
                    <th><?php echo __("Application"); ?> #</th>
                    <th><?php echo __("Task sent on"); ?></th>
                    <th><?php echo __("Status"); ?></th>
                    <th><?php echo __("Stage"); ?></th>
                    <th><?php echo __("Assigned To"); ?></th>
                    <th><?php echo __("Remarks"); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="<?php echo url_for('/backend.php/applications/view?id='.$application->getId()) ?>"><?php echo $application->getApplicationId(); ?></a></td>
                    <td><?php echo date('d F Y', strtotime($task->getDateCreated())); ?></td>
                    <td><span class="label label-default"><?php echo $task->getStatusName(); ?></span></td>
                    <td><span class="label label-default"><?php echo $application->getServiceName()." &gt; ".$application->getStatusName(); ?></span></td>
                    <td><?php echo $task->getOwner()->getStrfirstname()." ".$task->getOwner()->getStrlastname(); ?></td>
                    <td><?php echo html_entity_decode($task->getRemarks()) ?></td>
                </tr>
            </tbody>
        </table>

    </div><!-- panel-body -->
</div>