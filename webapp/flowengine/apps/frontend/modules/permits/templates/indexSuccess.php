<?php

use_helper("I18N");
$applicationM = new ApplicationManager();
?>
<div class="col-md-8 col-lg-9 col-xl-10">
    <div class="card flex-fill">
        <div class="card-header">
            <h3 class="card-title mb-0"><?php echo __("Permits And Licenses"); ?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable_permits table table-stripped">

                    <thead>
                        <tr>
                            <th><?php echo __("Application Reference"); ?></th>
                            <th><?php echo __("Service"); ?></th>
                            <th><?php echo __("Owner's Name"); ?></th>
                            <th><?php echo __("Plot Details."); ?></th>
                            <th><?php echo __("Date of Issue"); ?></th>
                            <th><?php echo __("Date of Expiry"); ?></th>
                            <th><?php echo __("Status"); ?></th>
                            <th></th><?php echo __("Actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->getResults() as $permit) : ?>
                            <tr>
                                <?php $owner_plot = $applicationM->getExtraApplicationInfo($permit->getFormEntry()->getFormId(), $permit->getFormEntry()->getEntryId());
                                $expired = "VALID";
                                if (!empty($permit->getDateOfExpiry())) {
                                    $db_date_event = str_replace('/', '-', $permit->getDateOfExpiry());

                                    $db_date_event = strtotime($db_date_event);

                                    if (time() > $db_date_event) {
                                        $expired = "EXPIRED";
                                    }
                                }

                                if ($permit->getExpiryTrigger() != 0) {
                                    $expired = "EXPIRED";
                                }
                                ?>
                                <td><a href="/plan/application/view/id/<?php echo $permit->getFormEntry()->getId(); ?>" class="link-primary"><?php echo $permit->getFormEntry()->getApplicationId(); ?></a></td>
                                <td><?php echo $permit->getTemplate()->getTitle(); ?></td>
                                <td>
                                    <?php echo $owner_plot[1]; ?>
                                </td>
                                <td><?php echo $owner_plot[0]; ?></td>
                                <td><?php echo date('jS M Y H:i:s', strtotime($permit->getDateOfIssue())); ?></td>
                                <td><?php echo !empty($permit->getDateOfExpiry()) ? date('jS M Y', strtotime($permit->getDateOfExpiry())) : ""; ?></td>
                                <td><?php echo  $expired; ?></td>
                                <td>
                                    <a class="btn btn-outline-info btn-sm" title='<?php echo __('View Permit'); ?>' href='/plan/permits/view/id/<?php echo $permit->getId(); ?>'><?php echo __("View"); ?> </a>

                                    <?php if ($expired == "VALID") { ?>
                                        <button class="btn btn-sm btn-success" id="printinvoice" type="button" onClick="window.location='/plan/permits/print/id/<?php echo $permit->getId(); ?>';">
                                            <i class="fa fa-print mr5"></i> <?php echo __("Download"); ?>
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>