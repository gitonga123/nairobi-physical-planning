<?php

/**
 * viewSuccess.php template.
 *
 * Displays a full permit details
 *
 * @package    frontend
 * @subpackage permits
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

if ($permit->getFormEntry()->getUserId() == $sf_user->getGuardUser()->getId()) {
?>
    <div class="col-sm-10">
        <?php
        $document_key = $permit->getDocumentKey();

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $permit->getTypeId());
        $permit_template = $q->fetchOne();
        ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $permit_template->getTitle(); ?></h3>
            </div>
            <div class="panel-body">

                <?php
                $templateparser = new TemplateParser();

                $permit_manager = new PermitManager();
                $html = $permit_manager->generate_permit_template($permit->getId(), false);

                # check if file exists as signed
                $_id = (new PermitManager())->permit_file_name($permit);
                $permit_id = $permit->getId();
                Functions::saveUnsignedPermit($permit);
                $file_path = "app/permits/signed/$_id";
                $file_path_unsigned = "app/permits/unsigned/$_id";
                $signed_copy_exists = file_exists($file_path);
                $is_signed = (new PermitManager())->is_signed($permit_id);

                $file_url = $base_url_ . '/' . ($is_signed ? $file_path : $file_path_unsigned);
                // echo $html;
                //                echo $file_url;
                ?>

                <div class="text-right btn-invoice" style="padding-right: 10px;">
                    <br>
                    <?php if (empty($document_key)) :
                        # check if signed file is available
                        if ($permit_manager->is_signed($permit->getId()) and Functions::isSignable($permit->getTypeId())) :
                            include_partial('render_pdf', ['url' => $file_url]) ?>
                            <a class="btn btn-primary" id="printinvoice" type="button" href="<?php echo $file_url ?>" download>
                                <i class="fa fa-print mr5"></i> <?php echo __("Print Service"); ?>
                            </a>
                        <?php else : ?>
                            <?php if (!Functions::isSignable($permit->getTypeId())) :
                                include_partial('render_pdf', ['url' => $file_url]);
                            ?>

                                <a class="btn btn-primary" id="printinvoice" type="button" href="<?php echo $file_url ?>" download>
                                    <i class="fa fa-print mr5"></i> <?php echo __("Print"); ?>
                                </a>
                            <?php else : ?>
                                <div class="row">
                                    <div class="col-md-12 alert alert-info" style="text-align: center">
                                        Document not signed or activated yet
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif;
                    endif;
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

    </div>

    </div>

<?php
} else {
    echo __("<h3>Sorry! You are trying to view a permit that doesn't belong to you</h3>");
}
?>