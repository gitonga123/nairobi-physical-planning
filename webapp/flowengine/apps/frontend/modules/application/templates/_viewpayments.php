<?php

/**
 * _viewinvoices.php partial.
 *
 * Displays any invoices attached to an application
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="card-body">
    <div class="responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo __("Date Of Issue"); ?></th>
                    <th><?php echo __("Amount"); ?></th>
                    <th><?php echo __("Status"); ?></th>
                    <th><?php echo __("Action"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                //Iterate through any invoices attached to this application
                $invcount = 0;
                $invtotal = 0;

                $inv_currency = "";

                $invoice_manager = new InvoiceManager();

                $q = Doctrine_Query::create()
                    ->from("MfInvoice a")
                    ->where("a.app_id = ?", $application->getId())
                    ->andWhere("a.paid <> 3")
                    ->orderBy("a.id DESC");
                $invoices = $q->execute();

                foreach ($invoices as $invoice) {
                    $invcount++;

                    $inv_currency = $invoice->getCurrency();
                    ?>
                    <tr>
                        <td><?php echo $invoice->getId(); ?></td>
                        <td><?php echo $invoice->getCreatedAt(); ?></td>
                        <td><?php echo $invoice->getCurrency(); ?>. <?php echo $invoice->getTotalAmount(); ?></td>
                        <td><?php echo $invoice->getStatus(); ?></td>
                        <td>
                            <a class="btn btn-outline-info btn-sm" id="printinvoice"
                                href="/plan/invoices/view/id/<?php echo $invoice->getId(); ?>"><i class="fa fa-eye mr5"></i>
                                <?php echo __("View Invoice"); ?></a> |
                            <a class="btn btn-dark btn-sm" id="printinvoice"
                                href="/plan/invoices/printinvoice/id/<?php echo $invoice->getId(); ?>"><i
                                    class="fa fa-print mr5"></i> <?php echo __("Print Invoice"); ?></a>

                            <?php if ($invoice->getPaid() == 1 || $invoice->getPaid() == 15) { ?>
                                | <a class="btn btn-primary btn-sm" id="makeinvoice"
                                    href="/plan/forms/payment?id=<?php echo $application->getFormId(); ?>&app_id=<?php echo $application->getEntryId(); ?>&invoice=<?php echo $invoice->getId(); ?>">
                                    <i class="fa fa-credit-card mr5"></i> <?php echo __("Pay now"); ?>
                                </a>
                            <?php } ?>

                            <?php

                            $list_print_urls = [];

                            if ($invoice->getPaid() == 2 && !empty($invoice->getReceiptNumber())) {
                                $receipt_data = $invoice->getReceiptNumber();

                                $from_string_ids = trim($receipt_data);

                                $receipt_ids = json_decode($from_string_ids, true);

                                if (is_array($receipt_ids) && !empty($receipt_ids)) {
                                    $api_url = sfConfig::get('app_api_jambo_url');

                                    foreach ($receipt_ids as $key => $receipt_number) {
                                        $my_string = "{$api_url}api/v1/print/receipt/{$receipt_number}/Physical_Planning";
                                        array_push($list_print_urls, $my_string);
                                    }
                                }

                            }
                            if (count($list_print_urls) > 0) {
                                ?>
                                <a title="Download Receipt" href="<?php echo $list_print_urls[0] ?>" class="btn btn-primary"><i
                                        class="fas fa-file-download"></i>
                                    <?php echo __(" Receipt - 1");
                                    ?>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                    if ($invoice->getPaid() == 2) {
                        $invtotal = $invtotal + $invoice->getTotalAmount();
                    }
                }
                ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td colspan="3">
                        <h3><?php echo __("Total Paid"); ?> <?php echo $inv_currency . " " . $invtotal; ?></h3>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>