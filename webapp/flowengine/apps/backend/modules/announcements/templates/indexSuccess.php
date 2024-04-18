<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managefaqs"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Announcements'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                <a class="btn btn-primary" id="newpage" href="/backend.php/announcements/new" ><?php echo __('+ Add Announcement'); ?></a>
        </div>
        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="60%"><?php echo __('Content'); ?></th>
                    <th width="10%"><?php echo __('Start Date'); ?></th>
                    <th width="10%"><?php echo __('End Date'); ?></th>
                    <th width="10%" class="no-sort"><?php echo __('Frontend?'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($announcements as $announcement): ?>
                    <tr id="row_<?php echo $announcement->getId() ?>">
                        <td><?php echo $announcement->getId(); ?></td>
                        <td><?php echo $announcement->getContent();  ?></td>
                        <td><?php echo $announcement->getStartDate();  ?></td>
                        <td><?php echo $announcement->getEndDate();  ?></td>
                        <td>
                        <?php
                        if($announcement->getFrontend() == "1")
                        {
                            echo "<span class='badge-round badge-success'><span class='fa fa-check'></span></span>";
                        }
                        else
                        {
                            echo "<span class='badge-round badge-danger'><span class='fa fa-times'></span></span>";
                        }

                        ?>
                        </td>
                        <td align="center">
                            <a id="editpage<?php echo $announcement->getId(); ?>" href="/backend.php/announcements/edit/id/<?php echo $announcement->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $announcement->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/announcements/delete/id/<?php echo $announcement->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
