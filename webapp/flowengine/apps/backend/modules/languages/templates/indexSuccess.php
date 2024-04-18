<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managelanguages"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Languages'); ?></h3>
        </div>

        <div class="panel-heading text-right">
           <a class="btn btn-primary" id="newpage" href="/backend.php/languages/new" ><?php echo __('+ Add Language'); ?></a>
           <a class="btn btn-primary m-l10" id="newlanguage" href="<?php echo public_path('backend.php/languages/translate') ?>"><?php echo __('Translate Labels'); ?></a>
        </div>


        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="50%"><?php echo __('Title'); ?></th>
                    <th width="30%"><?php echo __('Locale'); ?></th>
                    <th width="10%"><?php echo __('Is Default'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($languages as $language): ?>
                    <tr id="row_<?php echo $language->getId() ?>">
                        <td><?php echo $language->getId(); ?></td>
                        <td><?php echo $language->getLocalTitle();  ?></td>
                        <td><?php echo $language->getLocaleIdentifier();  ?></td>
                        <td>
                        <?php
                        if($language->getIsDefault() == "1")
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
                            <a id="editpage<?php echo $language->getId(); ?>" href="/backend.php/languages/edit/id/<?php echo $language->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $language->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/languages/delete/id/<?php echo $language->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
