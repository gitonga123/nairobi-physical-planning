<?php
/**
 * profileSuccess.php template.
 *
 * Edit your account settings
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper('I18N');
?>
<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __('My Account'); ?> <span><?php echo __('Edit your account details'); ?></span></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __('You are here'); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("backend.php"); ?>"><?php echo __('Home'); ?></a></li>
            <li class="active"><?php echo __('My Account'); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
    <div class="row">

        <?php include_partial('profile_form', array('form' => $form)) ?>

    </div>
</div>