<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">
      <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('More Fees'); ?></h3>
          
      </div>


    <form action="#" method="post">

        <div class="panel-heading text-right">
                <a href="/backend.php/services" class="btn btn-primary"><?php echo __("Back to Services"); ?></a>
                <a class="btn btn-primary" id="newfee" href="/backend.php/services/newmorefees/serviceid/<?php echo $service->getId(); ?>" > <?php echo __('+ Add Fee'); ?></a>
        </div>

        <div class="panel-body">

            <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort" width="5%">#</th>
                    <th width="60%"><?php echo __('Fee Title'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($fees as $fee): ?>
                    <tr id="row_<?php echo $fee->getId() ?>">
                        <td><?php echo $fee->getId(); ?></td>
                        <td><?php echo $fee->getFeeTitle();  ?></td>
                        <td align="center">
                            <a id="editfee<?php echo $fee->getId(); ?>" href="/backend.php/services/editmorefees/serviceid/<?php echo $service->getId(); ?>/id/<?php echo $fee->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletefee<?php echo $fee->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/services/deletemorefees/serviceid/<?php echo $service->getId(); ?>/id/<?php echo $fee->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

        </div>

        </form>
    </div>
</div>
