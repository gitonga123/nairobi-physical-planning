<?php
use_helper("I18N");

if ($sf_user->mfHasCredential("managefees")) {
    ?>
    <div class="contentpanel">
        <div class="panel panel-default">

            <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Sub Counties'); ?></h3>
            </div>


            <div class="panel-heading text-right">
                <a class="btn btn-warning" id="update_subcounty"
                    href="<?php echo url_for('/plan/subcounty/updatesubcounties') ?>"><?php echo __('Update Sub Counties'); ?></a>
                <script>
                    $(document).ready(function () {
                        $('#update_subcounty').click(function (e) {
                            $.ajax({
                                url: "<?php echo url_for('/plan/subcounty/updatesubcounties') ?>",
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
                                <th><?php echo __('id'); ?></th>
                                <th><?php echo __('UUID'); ?></th>
                                <th><?php echo __('Sub County'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subcounties as $subcounty): ?>
                                <tr id="row_<?php echo $subcounty->getId() ?>">
                                    <td><?php echo $subcounty->getUuid(); ?></td>
                                    <td><?php echo $subcounty->getName(); ?></td>
                                    <td align="center">
                                        <a id="subcountyedit<?php echo $subcounty->getId(); ?>"
                                            href="/plan/subcounty/view/id/<?php echo $subcounty->getId(); ?>"
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