<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managecategories"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('User Categories'); ?></h3>
        </div>


        <div class="panel-heading">
                <a class="btn btn-primary" id="newpage" href="/plan/usercategories/new" ><?php echo __('New Category'); ?></a>
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
                    <?php foreach($categories as $category): ?>
                    <tr id="row_<?php echo $category->getId() ?>">
                        <td><?php echo $category->getId(); ?></td>
                        <td><?php echo $category->getName();  ?></td>
                        <td><?php echo $category->getDescription();  ?></td>
                        <td align="center">
                            <a id="editpage<?php echo $category->getId(); ?>" href="/plan/usercategories/edit/id/<?php echo $category->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $category->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/usercategories/delete/id/<?php echo $category->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div>
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
