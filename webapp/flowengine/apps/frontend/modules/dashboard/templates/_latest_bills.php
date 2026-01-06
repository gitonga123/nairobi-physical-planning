<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __("Recent Bills"); ?></h3>
        <small><?php echo __("The following bills require your action"); ?></small>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover table-special table-striped" id="bills">
                <thead>
                    <tr>
                        <th><?php echo __("#"); ?></th>
                        <th><?php echo __("Invoice no"); ?></th>
                        <th><?php echo __("Issue date"); ?></th>
                        <th><?php echo __("Due date"); ?></th>
                        <th><?php echo __("Amount"); ?></th>
                        <th><?php echo __("Application id"); ?></th>
                        <th><?php echo __("Form name"); ?></th>
                        <th><?php echo __("Status"); ?></th>
                        <th><?php echo __("Actions"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest_invoices as $invoice): ?>
                    <tr>
                        <td><?php echo $invoice->getId() ?></td>
                        <td><?php echo $invoice->getInvoiceNumber() ?></td>
                        <td><?php echo date('jS M Y H:i:s',strtotime($invoice->getCreatedAt())) ?></td>
                        <td><?php echo date('jS M Y H:i:s',strtotime($invoice->getDueDate())) ?></td>
                        <td><?php echo $invoice->getCurrency()." ".$invoice->getTotalAmount(); ?></td>
                        <td><?php echo $invoice->getFormEntry()->getApplicationId() ?></td>
                        <td><?php echo $invoice->getFormEntry()->getForm()->getFormName() ?></td>
                        <td><?php echo $invoice->getStatus() ?></td>
                        <td>
                            <a class="btn btn-xs btn-default"  title='<?php echo __('View Invoice'); ?>' href='/index.php/invoices/view/id/<?php echo $invoice->getId(); ?>'><?php echo __("View"); ?> </a>

                            <?php if($invoice->getPaid() == 1 || $invoice->getPaid() == 15){ ?>
                            <!--a href="/index.php/forms/payment?id=<?php echo $invoice->getFormEntry()->getFormId(); ?>&app_id=<?php echo $invoice->getFormEntry()->getEntryId(); ?>&invoice=<?php echo $invoice->getId(); ?>" class="btn btn-primary btn-xs">
                            <?php echo __("Pay now"); ?>
                            </a-->
                            <?php } ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

