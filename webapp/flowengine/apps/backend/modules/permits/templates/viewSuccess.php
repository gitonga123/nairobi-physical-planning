<?php

/**
 * viewSuccess.php template.
 *
 * Displays a full invoice
 *
 * @package    frontend
 * @subpackage invoices
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
if ($permit || $application) {
    # check if file exists as signed

    $_id = (new PermitManager())->permit_file_name($permit);
    $permit_id = $permit->getId();
    Functions::saveUnsignedPermit($permit);
    $file_path = "app/permits/signed/$_id";
    $file_path_unsigned = "app/permits/unsigned/$_id";
    $signed_copy_exists = file_exists($file_path);
    $is_signed = (new PermitManager())->is_signed($permit_id);
?>
    <div class="pageheader">
        <h2><i class="fa fa-envelope"></i> Permit <span>View permit details</span></h2>
        <div class="breadcrumb-wrapper">
            <span class="label">You are here:</span>
            <ol class="breadcrumb">
                <li><a href="<?php echo public_path('plan') ?>">Home</a></li>
                <li><a href="<?php echo public_path('plan/permits/index') ?>">Permits</a></li>
                <li class="active"><?php echo $application->getApplicationId(); ?></li>
            </ol>
        </div>
    </div>

    <div class="contentpanel">
        <div class="row">

            <div class="col-sm-12">
                <?php
                //Displays the user panel
                include_partial('tasks/task_user_info', array('application' => $application));
                ?>
            </div>

        </div>

        <div class="row">

            <div class="col-sm-12">

                <div class="panel panel-default">

                    <div class="panel-heading">
                        <h5 class="bug-key-title">
                            <a href="/backend.php/applications/view?id=<?php echo $application->getId(); ?>">
                                <?php echo $application->getApplicationId(); ?>
                            </a>
                        </h5>
                        <div class="panel-title"><?php echo $permit->getTemplate()->getTitle(); ?></div>
                    </div>
                    <div class="panel-body padding-20 panel-bordered" style="border-top:0;">
                        <?php
                        $document_key = $permit->getDocumentKey();

                        $permit_manager = new PermitManager();
                        $html = $permit_manager->generate_permit_template($permit->getId(), false);

                        echo $html;
                        ?>

                        <div class="text-right btn-invoice" style="padding-right: 10px; margin-top: 10px;">
                            <?php
                            if (!empty($document_key)) {
                            ?>
                                <button class="btn btn-primary" id="printinvoice" type="button" onClick="window.location='/uploads/<?php echo $document_key; ?>';"><i class="fa fa-print mr5"></i> Download Service</button>

                            <?php
                            } else {
                            ?>
                                <button class="btn btn-primary" id="printinvoice" type="button" onClick="window.location='/backend.php/applications/viewpermit/id/<?php echo $permit->getId(); ?>';"><i class="fa fa-print mr5"></i> Download Permit</button>
                            <?php
                            }

                            if ($sf_user->mfHasCredential('cancel_permits') && $permit->getPermitStatus() != "3") {
                            ?>
                                <button class="btn btn-primary" id="cancelpermit" type="button" onClick="window.location='/backend.php/permits/cancelpermit/id/<?php echo $permit->getId(); ?>';"><i class="fa fa-print mr5"></i> Cancel Permit</button>
                            <?php
                            }

                            if ($sf_user->mfHasCredential('cancel_permits') && $permit->getPermitStatus() == "3") {
                            ?>
                                <button class="btn btn-primary" id="cancelpermit" type="button" onClick="window.location='/backend.php/permits/uncancelpermit/id/<?php echo $permit->getId(); ?>';"><i class="fa fa-print mr5"></i> UnCancel Permit</button>
                            <?php
                            }

                            //If permit template has a remote url, add a button for update remote database
                            $q = Doctrine_Query::create()
                                ->from("Permits a")
                                ->where("a.id = ?", $permit->getTypeId())
                                ->andWhere("a.remote_url <> ?", "");
                            $permit_template = $q->fetchOne();

                            if ($permit_template && $permit->getExpiryTrigger() != 1) {
                            ?>
                                <button class="btn btn-primary" id="cancelpermit" type="button" onClick="window.location='/backend.php/permits/updatesingleremote/id/<?php echo $permit->getId(); ?>';"><i class="fa fa-print mr5"></i> Update Remote Database</button>
                            <?php
                            }
                            ?>

                            <br>

                            <br>
                        </div>

                    </div>

                </div>

            </div><!-- /.row -->



        </div><!-- /.marketing -->
    </div>


<?php } ?>