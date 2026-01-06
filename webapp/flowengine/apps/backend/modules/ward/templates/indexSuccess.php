<?php
use_helper("I18N");

if ($sf_user->mfHasCredential("managefees")) {
    ?>
    <div class="contentpanel">
        <div class="panel panel-default">

            <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Wards'); ?></h3>
            </div>


            <div class="panel-heading text-right">
                <a class="btn btn-warning" id="update_ward"
                    href="<?php echo url_for('/backend.php/ward/updatewards') ?>"><?php echo __('Update Wards'); ?></a>
                <script>
                    $(document).ready(function () {
                        $('#update_ward').click(function (e) {
                            $.ajax({
                                url: "<?php echo url_for('/backend.php/ward/updatewards') ?>",
                                type: "GET",
                                dataType: "json",
                            }).done(function (resp) {
                                console.log(JSON.stringify(resp));
                            }).fail(function (xhr, status, errorThrown) {
                                //alert( "Sorry, there was a problem!" );
                                console.log("Error: " + errorThrown);
                                console.log("Status: " + status);
                                console.dir(xhr);
                            });
                            return false;
                        });
                        $('#preloader').ajaxStart(function () {
                            $(this).show();
                        }).ajaxStop(function () {
                            $(this).hide();
                            location.reload(true);
                        });
                    });
                </script>
            </div>

            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table dt-on-steroids mb0" id="table3">
                        <thead>
                            <tr>
                                <th class="no-sort">#</th>
                                <th><?php echo __('UUID'); ?></th>
                                <th><?php echo __('Ward'); ?></th>
                                <th><?php echo __('Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wards as $ward): ?>
                                <tr id="row_<?php echo $ward->getId() ?>">
                                    <td><?php echo $ward->getId(); ?></td>
                                    <td><?php echo $ward->getUuid(); ?></td>
                                    <td><?php echo $ward->getName(); ?></td>
                                    <td align="center">
                                        <a id="wardedit<?php echo $ward->getId(); ?>"
                                            href="/backend.php/ward/view/id/<?php echo $ward->getId(); ?>"
                                            title="<?php echo __('View'); ?>"><span class="label label-primary"><i
                                                    class="fa fa-eye"></i></span></a>
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
                { "bSortable": false, "aTargets": ['no-sort'] }
            ]
        });
    </script>

    <?php
} else {
    include_partial("settings/accessdenied");
}
?>
u