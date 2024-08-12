<?php

use_helper("I18N");
?>
<div class="col-md-8 col-lg-9 col-xl-10">
    <div class="card flex-fill">
        <div class="card-header">
            <h3 class="card-title mb-0"><?php echo __("Paid Invoices"); ?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable_invoices table table-stripped">

                    <thead>
                        <tr>

                            <th><?php echo __("Invoice no"); ?></th>
                            <th><?php echo __("Due date"); ?></th>
                            <th><?php echo __("Amount"); ?></th>
                            <th><?php echo __("Application id"); ?></th>
                            <th><?php echo __("Status"); ?></th>
                            <th><?php echo __("Actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_invoices as $invoice) : ?>
                            <tr>

                                <td><?php echo $invoice->getInvoiceNumber() ?></td>

                                <td><?php echo date('jS M Y H:i:s', strtotime($invoice->getDueDate())) ?></td>
                                <td><?php echo $invoice->getCurrency() . " " . $invoice->getTotalAmount(); ?></td>
                                <td><?php echo $invoice->getFormEntry()->getApplicationId() ?></td>
                                <td><?php echo $invoice->getStatus() ?></td>
                                <td>
                                    <a class="btn btn-outline-info btn-sm" title='<?php echo __('View Invoice'); ?>' href='/plan/invoices/view/id/<?php echo $invoice->getId(); ?>'><?php echo __("View"); ?> </a>

                                    <?php if ($invoice->getPaid() == 1 || $invoice->getPaid() == 15) { ?>
                                        <a href="/plan/forms/payment?id=<?php echo $invoice->getFormEntry()->getFormId(); ?>&app_id=<?php echo $invoice->getFormEntry()->getEntryId(); ?>&invoice=<?php echo $invoice->getId(); ?>" class="btn btn-primary btn-sm"><i class="fas fa-money-bill"></i>
                                            <?php echo __(" Pay now");
                                            ?>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>