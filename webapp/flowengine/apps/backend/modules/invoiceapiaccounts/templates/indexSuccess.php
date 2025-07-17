<?php
use_helper("I18N");

if($sf_user->mfHasCredential("manageinvoices"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Invoice API Accounts'); ?></h3>
        </div>


        <div class="panel-heading text-right">
            <a class="btn btn-primary" id="newpage" href="/plan/invoiceapiaccounts/new" ><?php echo __('New Account'); ?></a>
        </div>
        <div class="panel-body">

        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="40%"><?php echo __('Name'); ?></th>
                    <th width="40%"><?php echo __('Branch'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($accounts as $account): ?>
                    <tr id="row_<?php echo $account->getId() ?>">
                        <td><?php echo $account->getId(); ?></td>
                        <td><?php echo $account->getMdaName();  ?></td>
                        <td><?php echo $account->getMdaBranch();  ?></td>
                        <td align="center">
                            <a id="editpage<?php echo $account->getId(); ?>" href="/plan/invoiceapiaccounts/edit/id/<?php echo $account->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="deletepage<?php echo $account->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/invoiceapiaccounts/delete/id/<?php echo $account->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
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
