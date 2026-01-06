<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Downloads'); ?></h3>
    </div>

    <div class="panel-body padding-1">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-special m-b-0">
                    <thead>
                    <tr>
                        <th width="60px">#</th>
                        <th><?php echo __("Download"); ?></th>
                        <th><?php echo __("Date of issue"); ?></th>
                        <th><?php echo __("Date of Expiry"); ?></th>
                        <th><?php echo __("Signed By"); ?></th>
                        <th><?php echo __("Actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    $q = Doctrine_Query::create()
                        ->from("SavedPermit a")
                        ->where("a.application_id = ?", $application->getId())
                        ->andWhere("a.permit_status <> 3")
                        ->andWhere('a.expiry_trigger <> 1');
                    $permits = $q->execute();

                    if ($q->count()) {
                        foreach ($permits as $permit) {
                            $q = Doctrine_Query::create()
                                ->from('Permits a')
                                ->where('a.id = ?', $permit->getTypeId());
                            $permittype = $q->fetchOne();

                            if ($q->count() == 0 || ($permittype->getPartType() == 2 && $permit->getDocumentKey() == "")) {
                                continue;
                            }

                            $permit_status = "";

                            if ($permit->getExpiryTrigger() != 0) {
                                $permit_status = "- EXPIRED -";
                            }

                            $is_signed = $permit->isSigned();
                            $is_signable = $permit->isSignable();
                            $can_sign_this_permit = $sf_user->mfHasCredential('can-sign-permit-' . $permit->getTypeId());
                            $redirect_to = "/backend.php/applications/view/id/" . $application->getId();
                            $signer = ($signer = $permit->getSignedBy()) ? (($signer = Doctrine_Core::getTable('CfUser')->findOneBy('nid', $signer)) ? $signer->getStrfirstname() . ' ' . $signer->getStrlastname() : '-') : '-';
                            ?>
                            <tr>
                                <td><?php echo $permit->getId(); ?></td>
                                <td><?php echo $permittype->getTitle() . ' - ' . $application->getApplicationId(); ?></td>
                                <td><?php echo $permit->getDateOfIssue() ?></td>
                                <td><?php echo $permit->getDateOfExpiry() ?></td>
                                <td><?php echo $signer ?></td>
                                <td>
                                    <a target="_blank" class="btn btn-xs"
                                       href="/backend.php/permits/view/id/<?php echo $permit->getId(); ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <?php if ($can_sign_this_permit and !$is_signed): ?>
                                        <?php if (Functions::isDocumentInSigningSession($permit->getUnSignedFilePath())): ?>
                                            <a class="btn btn-danger"
                                               href="/backend.php/signingsessions/remove?document=<?php echo $permit->getUnSignedFilePath(); ?>&redirect_to=<?php echo $redirect_to ?>">
                                                <i class="fa fa-minus"></i> Remove from Signing list
                                            </a>
                                        <?php else: ?>
                                            <a href="/backend.php/signingsessions/add?document=<?php echo $permit->getUnSignedFilePath(); ?>&id=<?php echo $permit->getId() ?>&type=SavedPermit&slug=<?php echo $permit->getTaskSlug(); ?>&name=<?php echo $permit->getTemplate()->getTitle(); ?>&application_id=<?php echo $permit->getApplicationId(); ?>&redirect_to=<?php echo $redirect_to ?>"
                                               class="btn btn-info">
                                                <i class="fa fa-plus"></i> Add to Signing list
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
