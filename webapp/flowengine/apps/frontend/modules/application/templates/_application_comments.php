<?php

/**
 * _viewcomments.php partial.
 *
 * Display comments submitted by reviewers #may eventually decide to merge this with the _viewreviewers if i manage to seperate individual reviewer comments
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

?>

<div class="card-header">
    <h3 class="card-title mb-0"><?php echo __("Reviewers -  Comments"); ?></h3>
</div>
<div class="card-body">
    <?php
    // Show comments submitted by individual reviewers #Categorized
    include_partial('application/application_reviewer_comments', array('application' => $application));
    ?>
</div>