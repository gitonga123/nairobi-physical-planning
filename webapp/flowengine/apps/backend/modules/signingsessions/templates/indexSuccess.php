<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed Signing Sessions Config");

if ($sf_user->mfHasCredential("signingsessions")) {
    ?>
    <div class="contentpanel panel-email">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Signing Sessions Config'); ?>
                    <a class="btn btn-primary pull-right" id="newpage" style="color: white"
                       href="/plan/signingsessions/create">
                        <?php echo __('Add Session Config'); ?>
                    </a>
                </h3>
                <br/>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table dt-on-steroids mb0" id="table3">
                        <thead>
                        <tr>
                            <th class="no-sort">#</th>
                            <th width="40%"><?php echo __('User'); ?></th>
                            <th width="10%"><?php echo __('Already Signed'); ?></th>
                            <th width="10%"><?php echo __('Remaining Signings'); ?></th>
                            <th width="10%"><?php echo __('Total Allowed p.a'); ?></th>
                            <th width="10%"><?php echo __('Action'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($configs as $config): ?>
                            <tr id="row_<?php echo $config['id'] ?>">
                                <td><?php echo $config['id'] ?></td>
                                <td><?php echo $config['name'] ?></td>
                                <td style="text-align: right"><?php echo $config['used_signatures'] ?></td>
                                <td style="text-align: right"><?php echo $config['total_allowed_pa'] - $config['used_signatures'] ?></td>
                                <td style="text-align: right"><?php echo $config['total_allowed_pa'] ?></td>
                                <td style="text-align: center">
                                    <a href="/plan/signingsessions/create/id/<?php echo $config['id']?>">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div><!--panel-body-->
    </div>

    <script language='javascript'>
        jQuery('#table3').dataTable({
            "sPaginationType": "full_numbers",

            // Using aoColumnDefs
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": ['no-sort']}
            ]
        });
    </script>

    <?php
} else {
    include_partial("applications/accessdenied");
}
?>
