<?php

use_helper("I18N");
$applicationM = new ApplicationManager();
?>

<div class="col-md-8 col-lg-9 col-xl-10">
    <div class="card flex-fill">
        <div class="card-header">
            <h3 class="card-title mb-0"><?php echo __("All your applications"); ?></h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable_applications table table-stripped">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Application No</th>
                            <th>Submission Date</th>
                            <th>Owner's Name</th>
                            <th>Plot Details.</th>
                            <th>Stage</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_applications as $application): ?>
                            <tr>
                                <?php $owner_plot = $applicationM->getExtraApplicationInfo($application->getFormId(), $application->getEntryId());
                                ?>
                                <td> <?php echo $application->getForm()->getFormName() ?> </td>
                                <td> <a class="link-primary"
                                        href="/index.php/application/view/id/<?php echo $application->getId(); ?>"><?php echo $application->getApplicationId() ?>
                                    </a></td>
                                <td><?php echo date('d-m-Y H:i:s', strtotime($application->getDateOfSubmission())) ?></td>
                                <td class="text-start">
                                    <?php echo $owner_plot[1]; ?>
                                </td>
                                <td class="text-start"><?php echo $owner_plot[0]; ?></td>
                                <td class="text-start">
                                    <span class="<?php echo $application->getDeclined() ? "text-danger" : "text-dark" ?>">
                                        <?php if ($application->getStage()) { ?>
                                            <?php echo $application->getStage()->getTitle() ?>
                                        <?php } else { ?>
                                            <?php echo "Unassigned" ?>
                                        <?php } ?>
                                    </span>
                                </td>
                                <td class="text-start">
                                    <a href="/index.php/application/view/id/<?php echo $application->getId() ?>"
                                        class="btn btn-outline-info btn-sm"> View</a>
                                    <?php if ($application->getDeclined()): ?>
                                       | <a href="/index.php/application/edit/id/<?php echo $application->getId() ?>"
                                            class="btn btn-outline-danger btn-sm"> <i class="far fa-edit"></i> Edit &
                                            Resubmit</a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>