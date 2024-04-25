<?php

/**
 * indexSuccess.php partial.
 *
 * Displays Settings landing page
 *
 * @package    backend
 * @subpackage Settings
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper("I18N");

if ($sf_user->mfHasCredential("code_access_rights")): ?>

<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __('Settings'); ?> <span><?php echo __('Access various system configurations'); ?></span></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __('You are here'); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("backend.php"); ?>"><?php echo __('Home'); ?></a></li>
            <li class="active"><?php echo __('Settings'); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel panel-email">
    <div class="notfoundpanel">
        <h2><?php echo __('System Settings'); ?></h2>
        <h4><?php echo __('Click on the menus on the left to access various system configurations'); ?></h4>
        <h4><?php echo __('If you are experiencing any trouble, please contact your system administrator'); ?></h4>
    </div><!-- notfoundpanel -->
</div>

<?php endif ?>