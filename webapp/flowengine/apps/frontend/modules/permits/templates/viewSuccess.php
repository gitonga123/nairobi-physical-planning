<?php

/**
 * viewSuccess.php template.
 *
 * Displays a full Permit Details
 *
 * @package    frontend
 * @subpackage permits
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="col-md-8 col-lg-9 col-xl-10">

    <?php if ($permit->getFormEntry()->getUserId() == $sf_user->getGuardUser()->getId()) {
    ?>

        <?php
        $document_key = $permit->getDocumentKey();

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $permit->getTypeId());
        $permit_template = $q->fetchOne();
        ?>

        <div class="card flex-fill">
            <div class="card-header text-center">
                <h3 class="card-title mb-0"><?php echo $permit_template->getTitle(); ?></h3>
            </div>
            <div class="card-body">

                <?php
                $templateparser = new TemplateParser();

                $permit_manager = new PermitManager();
                $html = $permit_manager->generate_permit_template($permit->getId(), false);

                echo $html;
                ?>

                <div class="card-footer text-end">
                    <br>
                    <?php
                    if (empty($document_key)) {
                    ?>
                        <button class="btn btn-primary" id="printinvoice" type="button" onClick="window.location='/index.php//permits/print/id/<?php echo $permit->getId(); ?>';">
                            <i class="fa fa-print mr5"></i> <?php echo __("Print Service"); ?>
                        </button>
                    <?php
                    }
                    if (empty($document_key) && $permit_template->getPartType() == 2) {
                    ?>
                        <button class="btn btn-primary" id="printinvoice" type="button" onClick="window.location='/index.php//permits/attach/id/<?php echo $permit->getId(); ?>';">
                            <i class="fa fa-print mr5"></i> <?php echo __("Attach Signed Copy"); ?>
                        </button>
                        <?php
                    } elseif (!empty($document_key) && $permit_template->getPartType() == 2) {

                        //Client should only reattach in a corrections stage
                        if ($application->getDeclined()) {
                        ?>
                            <button class="btn btn-primary" id="printinvoice" type="button" onClick="window.location='/index.php//permits/attach/id/<?php echo $permit->getId(); ?>';">
                                <i class="fa fa-print mr5"></i> <?php echo __("Re-attach Signed Copy"); ?>
                            </button>
                        <?php
                        }
                        ?>

                        <button class="btn btn-primary" id="printinvoice" type="button" onClick="window.location='/<?php echo $document_key; ?>';"><i class="fa fa-print mr5"></i> <?php echo __("Print Signed Copy"); ?>
                        </button>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>


    <?php
    } else {
        echo __("<h3>Sorry! You are trying to view a permit that doesn't belong to you</h3>");
    }
    ?>
</div>