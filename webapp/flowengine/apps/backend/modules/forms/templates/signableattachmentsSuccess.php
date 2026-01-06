<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Signable Attachments'.($form_name ? ' <i>For '.$form_name.'</i>' : '')); ?></h3>
            <small>List of fields that can be made signable</small>
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table class="table dt-on-steroids mb0" id="table3">
                    <thead>
                    <tr>
                        <?php if (!$formId): ?>
                            <th><?php echo __('Service'); ?></th>
                            <th><?php echo __('Form'); ?></th>
                        <?php endif; ?>
                        <th><?php echo __('Document'); ?></th>
<!--                        <th class="no-sort">--><?php //echo __('Is Signable'); ?><!--</th>-->
                        <th class="no-sort" width="7%"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($elements as $e): ?>
                        <tr id="row_<?php echo 'id' ?>">
                            <?php if (!$formId): ?>
                                <td><?php echo $e['service'] ?></td>
                                <td><?php echo $e['form_name'] ?></td>
                            <?php endif; ?>
                            <td><?php echo $e['title'] ?></td>
<!--                            <td>--><?php //echo $e['sample_relation_id'] ? 'YES' : 'NO' ?><!--</td>-->
                            <td>
                                <a class="btn btn-primary" id="newtemplate"
                                   href="/backend.php/forms/addsignableattachment/form/<?php echo $e['form_id'] ?>/element/<?php echo $e['id'] ?>">
                                    <i class="fa fa-pencil"></i> UPDATE
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function () {
            jQuery('#table3').dataTable({
                "sPaginationType": "full_numbers",

                // Using aoColumnDefs
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": ['no-sort']}
                ]
            });
        });
    </script>
</div>
