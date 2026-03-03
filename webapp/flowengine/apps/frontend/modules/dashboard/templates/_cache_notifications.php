<?php

/**
 * _notifications template.
 *
 * Displays a list of the latest notifications
 *
 * @package    frontend
 * @subpackage notifications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
$number_of_invoices = $newCacheContent['invoices'] - $cacheContent['invoices'];
if ($number_of_invoices > 0) {
?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">

        <strong><?php echo ("Success!"); ?></strong> <?php echo ("You have {$number_of_invoices} new Invoices"); ?>
        <strong> <a class="link success" href="/plan/dashboard/invoiceslist"><?php echo ("Click here to see the list."); ?></a></strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php
} ?>