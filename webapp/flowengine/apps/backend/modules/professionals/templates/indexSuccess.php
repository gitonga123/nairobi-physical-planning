<?php
use_helper("I18N");

if ($sf_user->mfHasCredential("can_verify_professionals_details")) {
    $_SESSION['current_module'] = "professionals";
    $_SESSION['current_action'] = "index";
    $_SESSION['current_id'] = "";

    $membersManager = new MembersManager();
?>
    <?php
    /**
     * indexSuccess.php template.
     *
     * Displays list of all registered clients
     *
     * @package    backend
     * @subpackage frusers
     * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
     */
    ?>
    <div class="pageheader">
        <h2><i class="fa fa-home"></i><?php echo __('Users'); ?><span>Professionals List</span></h2>
        <div class="breadcrumb-wrapper">
            <span class="label"><?php echo __('You are here'); ?>:</span>
            <ol class="breadcrumb">
                <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
                <li class="active"><?php echo __('Profressionals'); ?></li>
            </ol>
        </div>
    </div>

    <div class="contentpanel">

        <div class="panel panel-default">
            <div class="panel-body">
                <?php if (count($entries) > 0) : ?>
                    <table class="table b-b-0">
                        <thead>
                            <tr>

                                <form method="post" action="/backend.php/professionals/index/filter/<?php echo $filter; ?><?php if ($filterstatus != "") {
                                                                                                                                echo "/filterstatus/" . $filterstatus;
                                                                                                                            } ?>">
                                    <th class="b-b-0" style="width:50%;">
                                        <input name="search" value="<?php echo $filter; ?>" placeholder="<?php echo __('Search'); ?>" type="text" class="form-control p10">
                                    </th>

                                    <?php
                                    if (!sfConfig::get('app_sso_secret')) {
                                    ?>
                                        <th class="b-b-0 radius-tr">
                                            <select size="1" name="filter_status" aria-controls="table2" class="select2 form-select" onChange="window.location='/backend.php/professionals/index/filterstatus/' + this.value;">
                                                <option value="1"><?php echo __('Select Status'); ?></option>
                                                <option value="1" <?php if ($filterstatus == "1") {
                                                                        echo "selected='selected'";
                                                                    } ?>><?php echo __('Active'); ?>
                                                </option>
                                                <option value="0" <?php if ($filterstatus == "0") {
                                                                        echo "selected='selected'";
                                                                    } ?>><?php echo __('Inactive'); ?>
                                                </option>
                                            </select>
                                        </th>
                                    <?php
                                    }
                                    ?>
                                </form>
                            </tr>
                        </thead>
                    </table>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-special" id="table_professionals">
                            <thead>
                                <tr class="main-tr">
                                    <th class="b-b-0">#</th>
                                    <?php foreach ($form_elements as $element): ?>
                                        <th><?php echo $element['element_title'] ?></th>
                                    <?php endforeach; ?>
                                    <th><?php echo __('Status'); ?></th>
                                    <th class="aligncenter no-sort b-b-0"><?php echo __('Action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($entries as $entry) {
                                    $count++;
                                ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <?php foreach ($form_elements as $e): ?>
                                            <td>
                                                <?php if ($e['element_type'] == 'select') {
                                                    $item_id = $entry['element_' . $e['element_id']];
                                                    $element_options = $e['Options']->getRawValue();
                                                    $match = array_values(array_filter($element_options, function ($row) use ($item_id) {
                                                        return (int)$row['option_id'] === (int)$item_id;
                                                    }));
                                                    $option_text = $match ? $match[0]['option_text'] : null;
                                                    echo $option_text;
                                                } else { ?>
                                                    <?php if (strlen($entry['element_' . $e['element_id']])): ?>
                                                        <?php echo $entry['element_' . $e['element_id']]; ?>
                                                    <?php else: ?>
                                                        <?php for ($i = 1; $i <= 20; $i++): ?>
                                                            <?php echo $entry['element_' . $e['element_id'] . '_' . $i]; ?>
                                                        <?php endfor; ?>
                                                    <?php endif; ?>
                                                <?php } ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <?php
                                        $isActivated = $membersManager->checkIfUserAccountIsActivated($form_id, $entry['id']);
                                        $badgeClass = $isActivated ? 'label label-success' : 'label label-warning';
                                        $badgeText  = $isActivated ? 'ACTIVE' : 'INACTIVE';
                                        ?>
                                        <td><span class="<?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span></td>
                                        <td class="aligncenter">
                                            <?php if (!$isActivated) { ?>
                                                <a title="Approve User" href="/backend.php/professionals/approve/form/<?php echo $form_id; ?>/entry/<?php echo $entry['id'] ?>"><span class="label label-success"><i class="fa fa-check-circle-o"></i></span></a>
                                            <?php } ?>
                                            <?php if ($isActivated) { ?>
                                                <a title="Deactivate User" href="/backend.php/professionals/deactivate/form/<?php echo $form_id; ?>/entry/<?php echo $entry['id'] ?>"><span class="label label-danger"><i class="fa fa-times"></i></span></a>
                                            <?php } ?>
                                            <a title="View User" href="/backend.php/professionals/view/form/<?php echo $form_id; ?>/entry/<?php echo $entry['id'] ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div><!-- table-responsive -->
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table dt-on-steroids mb0">
                            <tbody>
                                <tr>
                                    <td class="text-center aligncenter">
                                        <?php echo __('No Records Found'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>


    </div>

    </div><!-- panel -->


    </div>


<?php
} else {
    include_partial("accessdenied");
}
?>

<script>
    jQuery(document).ready(function() {
        jQuery('#table_professionals').dataTable({
            "sPaginationType": "full_numbers",

            // Using aoColumnDefs
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": ['no-sort']
            }]
        });
    });
</script>