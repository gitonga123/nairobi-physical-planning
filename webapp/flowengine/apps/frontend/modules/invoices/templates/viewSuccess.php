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

//Get site config properties
$q = Doctrine_Query::create()
    ->from("ApSettings a")
    ->where("a.id = 1")
    ->orderBy("a.id DESC");
$apsettings = $q->fetchOne();

$invoice_manager = new InvoiceManager();

$invoice_manager->update_invoices($application->getId());

?>
<div class="col-md-8 col-lg-9 col-xl-10">
    <div class="card flex-fill">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title mb-0"><?php echo __("Invoice Details"); ?></h3>
                <a class="card-title btn btn-dark btn-sm text-end" target="_blank"
                    href="/plan/application/view/id/<?php echo $invoice->getAppId(); ?>"><?php echo __("View Application"); ?></a>
            </div>
        </div>
        <div class="card-body">
            <?php
            if ($invoice->getFormEntry()->getUserId() == $sf_user->getGuardUser()->getId()) { ?>
                <ul class="nav nav-tabs nav-tabs-top">
                    <!-- <li class="nav-item"><a class="nav-link active" href="#solid-justified-tab1" data-bs-toggle="tab"><?php echo __('Invoice Details'); ?></a></li> -->
                    <!-- <li class="nav-item"><a class="nav-link" href="#solid-justified-tab2" data-bs-toggle="tab"><?php echo __('Payment Details'); ?></a></li> -->
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane show active" id="solid-justified-tab1">
                        <br>
                        <?php
                        $templateparser = new TemplateParser();
                        $invoice_manager = new InvoiceManager();

                        try {
                            $html = $invoice_manager->generate_invoice_template($invoice->getId(), false);
                        } catch (Exception $ex) {
                            error_log("Debug-t: Invoice Parse Error: " . $ex);

                            $html = $invoice_manager->generate_invoice_template_old_parser($invoice->getId(), false);
                        }

                        echo $html;
                        ?>

                        <div class="text-end btn-invoice mt-3" style="padding-right: 10px;">
                            <?php if ($invoice->getDocumentKey()) { ?>
                                <button class="btn btn-primary btn-sm" id="printinvoice" type="button"
                                    onClick="window.location='<?php echo $invoice->getDocumentKey(); ?>';">
                                    <i class="fa fa-print me-2"></i> <?php echo __('Download Invoice'); ?>
                                </button>
                            <?php } else { ?>
                                <button class="btn btn-outline-dark" id="printinvoice" type="button"
                                    onClick="window.location='/plan/invoices/printinvoice/id/<?php echo $invoice->getId(); ?>';">
                                    <i class="fa fa-print me-2"></i> <?php echo __('Print Invoice'); ?>
                                </button>
                            <?php } ?>
                            <?php if ($invoice->getPaid() == 2 && !empty($invoice->getReceiptNumber())) {
                                $receipt_data = $invoice->getReceiptNumber();  // Get the raw value
                        
                                echo "<pre>Raw Receipt Number: " . print_r($receipt_data, true) . "</pre>";

                                // Try to decode only if it’s a JSON string
                                $receipt_id = json_decode($receipt_data, true);

                                // If json_decode fails, use the raw value
                                if (json_last_error() === JSON_ERROR_NONE && is_array($receipt_id)) {
                                    $receipt_number1 = !empty($receipt_id) ? $receipt_id[0] : null;
                                } else {
                                    $receipt_number1 = $receipt_data; // Fallback if it's not JSON
                                }

                                echo "<pre>Decoded Receipt ID: " . print_r($receipt_id, true) . "</pre>";
                                echo "<pre>Extracted Receipt Number: " . print_r($receipt_number1, true) . "</pre>";

                                if (!empty($receipt_number1)) {
                                    $api_url = sfConfig::get('app_api_jambo_url');
                                    echo "Count --->" . count($receipt_id);
                                    foreach ($receipt_id as $index => $receipt_number) {
                                        echo "{$index}";
                                        echo '<a title="Download Receipt ' . ($index + 1) . '" href="' . $api_url . '/api/v1/print/receipt/' . $receipt_number . '/Physical_Planning/" class="btn btn-primary" style="margin-right: 10px;">
                                                <i class="fas fa-file-download"></i> ' . __("Receipt ") . ($index + 1) . '
                                              </a>';
                                    }
                                } else {
                                    echo "<p style='color:red;'>Error: No valid receipt number found.</p>";
                                }
                            } ?>
                            <?php
                            $expired = false;
                            $db_date_event = str_replace('/', '-', $invoice->getExpiresAt());
                            $db_date_event = strtotime($db_date_event);

                            if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2")) {
                                $expired = true;
                            }

                            if ($expired) {
                                echo "Expired. No Payments Possible";
                            } else {
                                if ($invoice->getPaid() == 1) { ?>
                                    <button class="btn btn-primary" id="makepayment" type="button"
                                        onClick="window.location='/plan/forms/payment?id=<?php echo $invoice->getFormEntry()->getFormId(); ?>&app_id=<?php echo $invoice->getFormEntry()->getEntryId(); ?>&invoice=<?php echo $invoice->getId(); ?>';">
                                        <i class="fas fa-money-bill"></i> <?php echo __('Make Payment'); ?>
                                    </button>
                                <?php }
                            }

                            if ($invoice->getPaid() != 1 && $invoice->getPaid() != 2) { ?>
                                <button class="btn btn-primary" id="makepayment" type="button"
                                    onClick="window.location='/plan/invoices/pay/id/<?php echo $invoice->getId(); ?>';">
                                    <i class="fa fa-plus me-2"></i> <?php echo __('Add Payment'); ?>
                                </button>
                            <?php } ?>
                        </div>

                    </div>
                    <div class="tab-pane" id="solid-justified-tab2">
                        <?php

                        function get_ordinal($number)
                        {
                            $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
                            if (($number % 100) >= 11 && ($number % 100) <= 13)
                                $abbreviation = $number . 'th';
                            else
                                $abbreviation = $number . $ends[$number % 10];
                            return $abbreviation;
                        }

                        $paid = false;


                        $receipts = $invoice->getUploadReceipt();

                        if (sizeof($receipts) > 0) {
                            $count = 0;
                            foreach ($receipts as $receipt) {
                                $count++;
                                ?>
                                <h4><a href="#"><?php echo __('Receipt'); ?>             <?php echo $count; ?></a></h4>
                                <div>
                                    <p style="font-size: 12px; font-family:Times New Roman,'Georgia',Serif;">
                                        <?php echo "<a target='_blank' href='/plan/invoices/viewreceipt?form_id=" . $receipt->getFormId() . "&id=" . $receipt->getEntryId() . "'>(" . __("View Receipt") . ")</a>"; ?>
                                    </p>
                                </div>
                                <?php

                            }
                        }

                        if (sizeof($receipts) == 0 && $paid == false) {
                            ?>
                            <h4><a href="#"><?php echo __('Receipts'); ?></a></h4>
                            <div>
                                <p style="font-size: 12px; font-family:Times New Roman,'Georgia',Serif;">

                                <div class="table-responsive">
                                    <table class="table table-bordered mb0">
                                        <tbody>
                                            <tr>
                                                <td colspan="4" class="aligned">
                                                    <h4><?php echo __('No Payments Made'); ?></h4>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--Responsive-table-->
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php } else {
                echo __("<h3>Sorry! You are trying to view an invoice that doesn't belong to you</h3>");
            } ?>
        </div>
    </div>
</div>