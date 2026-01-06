<?php
use_helper("I18N");

$site_settings = Functions::site_settings();

if($sf_user->mfHasCredential("managewebpages"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Banners'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                <a class="btn btn-primary" id="newpage" href="/backend.php/banner/new" ><?php echo __('+ Add Banner'); ?></a>
        </div>
        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="50%"><?php echo __('Title'); ?></th>
                    <th width="30%"><?php echo __('Image'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($banners as $banner): ?>
                    <tr id="row_<?php echo $banner->getId() ?>">
                        <td><?php echo $banner->getId(); ?></td>
                        <td><?php echo $banner->getTitle();  ?></td>
                        <td><img src="<?php echo '/'.$site_settings->getUploadDir().'/'.$banner->getImage();  ?>" width="200px"></td>
                        <td align="center">
                            <a id="editpage<?php echo $banner->getId(); ?>" href="/backend.php/banner/edit/id/<?php echo $banner->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $banner->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/banner/delete/id/<?php echo $banner->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
