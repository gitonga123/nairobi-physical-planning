<div class="card">

    <div class="card-body p-0">

        <table class="table m-b-0">
            <tbody>
                <?php
                $q = Doctrine_Query::create()
                    ->from("SavedPermit a")
                    ->where("a.application_id = ?", $application->getId())
                    ->andWhere("a.permit_status <> 3")
                    ->andWhere('a.expiry_trigger <> 1');

                if ($q->count() > 0) {
                    $downloads = $q->execute();

                    foreach ($downloads as $download) {
                        $q = Doctrine_Query::create()
                            ->from('Permits a')
                            ->where('a.id = ?', $download->getTypeId());
                        $permittype = $q->fetchOne();

                        if ($permittype->getPartType() == 2 && $download->getDocumentKey() == "") {
                            continue;
                        }

                        $permit_status = "";

                        if ($download->getExpiryTrigger() != 0) {
                            $permit_status = "- EXPIRED -";
                        }
                ?>
                        <tr>
                            <td style="padding: 15px;" class="text-primary pe-auto">

                                <a class="text-primary link_primary pe-auto" target="_blank" onClick="window.location = '/index.php/permits/view/id/<?php echo $download->getId(); ?>';"><i class="fas fa-download"></i> <?php echo $permittype->getTitle() . " (" . $download->getDateOfIssue() . ") " . $permit_status; ?></a>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4" class="aligned">
                            <h4><?php echo __('No Downloads'); ?></h4>

                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>

    </div>

</div>