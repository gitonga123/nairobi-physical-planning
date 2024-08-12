<?php $applicationM = new ApplicationManager(); ?>
<div class="col-md-8 col-lg-9 col-xl-10">

    <div class="row">
        <?php include_partial(
            'notifications',
            array(
                'corrections_applications' => $corrections_applications,
                'renewal_applications' => $renewal_applications,
                'transferring_applications' => $transferring_applications
            )
        ) ?>
        <div class="col-md-12 col-lg-4 dash-board-list blue">
            <a href="/index.php//dashboard/applicationslist">
                <div class="dash-widget">
                    <div class="circle-bar">
                        <div class="icon-col">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h3><?php echo count($all_applications) ?> </h3>
                        <h6>Applications Submitted</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-12 col-lg-4 dash-board-list yellow">
            <a href="/index.php//dashboard/invoiceslist">
                <div class="dash-widget">
                    <div class="circle-bar">
                        <div class="icon-col">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h3><?php echo count($all_invoices) ?></h3>
                        <h6>New Invoices</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-12 col-lg-4 dash-board-list pink">
            <a href="/index.php//permits">
                <div class="dash-widget">
                    <div class="circle-bar">
                        <div class="icon-col">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h3><?php echo count($saved_permits) ?></h3>
                        <h6>Permits Issued</h6>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="row justify-content-between">
                <div class="col-auto">
                    <h4 class="mb-4">Latest Applications</h4>
                </div>
                <div class="col-auto">
                    <a href="/index.php//forms/groups" class="btn btn-success btn-sm"><i class="far fa-edit" aria-hidden="true"></i> Submit New Application</a>
                </div>
            </div>
            <div class="card flex-fill">
                <div class="card-body">
                    <div class="table-responsive-lg table-responsive-md">
                        <table class="datatable_applications table table-stripped">
                            <thead>
                                <tr>
                                    <th class="text-start">Service Name</th>
                                    <th class="text-start">Application No</th>
                                    <th class="text-start">Submission Date</th>
                                    <td class="text-start">Owner's Name</td>
                                    <td class="text-start">Plot Details.</td>
                                    <th class="text-start">Stage</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latest_applications as $application): ?>
                                    <tr>
                                        <?php $owner_plot = $applicationM->getExtraApplicationInfo($application->getFormId(), $application->getEntryId());
                                        ?>
                                        <td class="text-start"> <?php echo $application->getForm()->getFormName() ?> </td>
                                        <td> <a class="link-primary"
                                                href="/index.php//application/view/id/<?php echo $application->getId(); ?>"><?php echo $application->getApplicationId() ?>
                                            </a></td>
                                        <td class="text-start">
                                            <?php echo date('d-m-Y H:i:s', strtotime($application->getDateOfSubmission())) ?>
                                        </td>
                                        <td class="text-start">
                                            <?php echo $owner_plot[1]; ?>
                                        </td>
                                        <td class="text-start"><?php echo $owner_plot[0]; ?></td>
                                        <td class="text-start">
                                            <span
                                                class="<?php echo $application->getDeclined() ? "text-danger" : "text-dark" ?>">
                                                <?php if ($application->getStage()) { ?>
                                                    <?php echo $application->getStage()->getTitle() ?>
                                                <?php } else { ?>
                                                    <?php echo "Unassigned" ?>
                                                <?php } ?>
                                            </span>
                                        </td>
                                        <td class="text-start"><a
                                                href="/index.php//application/view/id/<?php echo $application->getId() ?>"
                                                class="btn btn-outline-info btn-sm"> View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>