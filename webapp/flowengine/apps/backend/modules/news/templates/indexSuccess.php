<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managenews"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('News'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                <a class="btn btn-primary" id="newpage" href="/plan/news/new" ><?php echo __('+ Add News'); ?></a>
        </div>
        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="80%"><?php echo __('Title'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Published'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($news_articles as $news): ?>
                    <tr id="row_<?php echo $news->getId() ?>">
                        <td><?php echo $news->getId(); ?></td>
                        <td><?php echo $news->getTitle();  ?></td>
                        <td>
                        <?php
                        if($news->getPublished() == "1")
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
                            <a id="editpage<?php echo $news->getId(); ?>" href="/plan/news/edit/id/<?php echo $news->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $news->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/news/delete/id/<?php echo $news->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
