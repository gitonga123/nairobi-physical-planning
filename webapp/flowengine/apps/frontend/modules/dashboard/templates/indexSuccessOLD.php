<?php

/**
 * indexSuccess.php template.
 *
 * Displays client dashboard
 *
 * @package    frontend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

//$apsettings = Functions::site_settings();
?>

<!--Show a list of notifications for the client-->
<?php include_partial('notifications', array('corrections_applications' => $corrections_applications, 'renewal_applications' => $renewal_applications, 'transferring_applications' => $transferring_applications)) ?>

<!--Show a list of the latest businesses-->
<?php
if (Functions::client_can_add_businesses()) {
   // include_partial('my_businesses', array('my_businesses' => $my_businesses));
}
?>

<!--Show a list of the latest applications-->
<?php include_partial('latest_applications', array('latest_applications' => $latest_applications)) ?>

<!--Show a list of the latest applications-->
<?php include_partial('latest_bills', array('latest_invoices' => $latest_invoices)) ?>