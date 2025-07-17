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
<div class="panel-group mb0" id="accordion">

    <div id="commentsReviewers" class="panel-collapse collapse in">
        <div class="panel-body padding-0">
            <?php
            //Show comments submitted by individual reviewers #Categorized
            include_partial('tasks/application_reviewer_comments', array('application' => $application, 'form_id' => $form_id, 'entry_id' =>  $entry_id));
            ?>
        </div>
    </div>

</div>
