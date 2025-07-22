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
use_helper("I18N");
$application = $invoice->getFormEntry();
?>

<div class="contentpanel">
    <div class="row">


        <div class="col-sm-12">

            <div class="panel panel-default">

                <div class="panel-body padding-20 panel-bordered" style="border-top:0;">

                    <?php
                    $invoice_manager = new InvoiceManager();

                    try {
                        $html = $invoice_manager->generate_invoice_template($invoice->getId(), false);
                    } catch (Exception $ex) {
                        error_log("Debug-t: Invoice Parse Error: " . $ex);

                        $html = $invoice_manager->generate_invoice_template_old_parser($invoice->getId(), false);
                    }

                    echo $html;
                    ?>

                    <div class="text-right btn-invoice"
                        style="padding-right: 10px; padding-top: 10px; padding-bottom: 10px;">
                        <?php
                        if ($invoice->getDocumentKey()) {
                            ?>
                            <a class="btn btn-white" id="printinvoice" href="<?php echo $invoice->getDocumentKey(); ?>"><i
                                    class="fa fa-print mr5"></i> <?php echo __('Download Invoice'); ?></a>
                            <?php
                        } else {
                            ?>
                            <a class="btn btn-white" id="printinvoice"
                                href="/plan/invoices/print/id/<?php echo $invoice->getId(); ?>"><i
                                    class="fa fa-print mr5"></i> <?php echo __("Print Invoice"); ?></a>
                            <?php
                        }

                        if ($invoice->getPaid() <> 2 && $invoice->getPaid() <> 3 && $sf_user->mfHasCredential('approvepaymentoverride2') && $sf_user->mfHasCredential('code_access_rights2')) {
                            ?>
                            <a onClick="if(confirm('Are you sure you want to confirm this invoice?')){ return true; }else{ return false; }"
                                class="btn btn-white" id="makepayment"
                                href="/plan/invoices/view/id/<?php echo $invoice->getId(); ?>/confirm/<?php echo md5($invoice->getId()); ?>"><i
                                    class="fa fa-print mr5"></i> <?php echo __('Confirm Payment'); ?></a>
                            <?php
                        }

                        if ($invoice->getPaid() == 1 && $sf_user->mfHasCredential('code_access_rights')) {
                            ?>
                            <a class="btn btn-success" id="makepayment"
                                href="/plan/invoices/checkpaymentstatus/id/<?php echo $invoice->getId(); ?>/bill_ref/<?php echo $invoice->getFormEntry()->getFormId() . "" . $invoice->getFormEntry()->getEntryId() . "" . $invoice->getId() ?>"><i
                                    class="fa fa-check mr5"></i> <?php echo __('Check Payment Status'); ?></a>
                            <?php
                        }

                        if (($invoice->getPaid() <> 3 || $invoice->getPaid() <> 2) && $sf_user->mfHasCredential('code_access_rights')) {
                            ?>
                            <!-- <a class="btn btn-white" id="makepayment" onClick="if(confirm('Are you sure you want to cancel this invoice?')){ return true; }else{ return false; }" href="/plan/invoices/view/id/<?php // echo $invoice->getId(); 
                                ?>/cancel/<?php  // echo md5($invoice->getId()); 
                                    ?>"><i class="fa fa-print mr5"></i> <?php // echo __('Cancel Payment'); 
                                        ?></a> -->
                            <?php
                        }

                        if ($invoice->getPaid() <> 2 && $sf_user->mfHasCredential('approvepaymentbyreference')) {
                            ?>
                            <a class="btn btn-white" id="makepayment" href="#" data-toggle="modal"
                                data-target="#referenceModal"><i class="fa fa-print mr5"></i>
                                <?php echo __('Attach Reference'); ?></a>
                            <?php
                        }
                        ?>
                    </div>

                    <?php foreach ($invoice->getUploadReceipt() as $receipt): ?>
                        <?php if ($receipt->getFormId() && $receipt->getEntryId()): ?>
                            <a target='_blank'
                                href="<?php echo url_for('/plan/invoices/viewreceipt?form_id=' . $receipt->getFormId() . "&id=" . $receipt->getEntryId()) ?>">View
                                Receipt</a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

            </div>

        </div>

    </div>
</div>