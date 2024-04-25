<?php
/**
 * newSuccess.php template.
 *
 * Allows creation of a new client account
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="pageheader">
<h2><i class="fa fa-envelope"></i><?php echo __('Users'); ?></h2>
<div class="breadcrumb-wrapper">
<span class="label"><?php echo __('You are here'); ?>:</span>
<ol class="breadcrumb">
<li>
<a href="/backend.php"><?php echo __('Home'); ?></a>
</li>
<li>
<a href="/backend.php/applications/list/get/your"><?php echo __('Users'); ?></a>
</li>
<li class="active"><?php echo __('New User'); ?></li>
</ol>
</div>
</div>

<div class="contentpanel">
<div class="row">
<?php include_partial('form', array('form' => $form)) ?>
</div>
</div>
