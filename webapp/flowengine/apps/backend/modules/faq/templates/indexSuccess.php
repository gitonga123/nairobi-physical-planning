<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managefaqs"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Frequently Asked Questions'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                <a class="btn btn-primary" id="newpage" href="/backend.php/faq/new" ><?php echo __('+ Add FAQ'); ?></a>
        </div>
        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="50%"><?php echo __('Question'); ?></th>
                    <th width="30%"><?php echo __('Answer'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Published'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($faqs as $faq): ?>
                    <tr id="row_<?php echo $faq->getId() ?>">
                        <td><?php echo $faq->getId(); ?></td>
                        <td><?php echo $faq->getQuestion();  ?></td>
                        <td><?php echo $faq->getAnswer();  ?></td>
                        <td>
                        <?php
                        if($faq->getPublished() == "1")
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
                            <a id="editpage<?php echo $faq->getId(); ?>" href="/backend.php/faq/edit/id/<?php echo $faq->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $faq->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/backend.php/faq/delete/id/<?php echo $faq->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
