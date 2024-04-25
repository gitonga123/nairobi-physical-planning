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

//get form id and entry id
$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();
?>
<div class="panel-group mb0" id="accordion">

  <div class="panel panel-default">
    <div class="panel-heading panel-heading-noradius">
          <h4 class="panel-title">
              <a data-toggle="collapse" class="collapsed"  data-parent="#accordion" href="#commentsDeclines">
              <?php echo __("Previous Reasons for Decline"); ?>
                </a>
                </h4>
              </div>
              <div id="commentsDeclines" class="panel-collapse">
                <div class="panel-body">
        <?php
        //Check if this application has been previously declined before
        include_partial('comments_declines', array('application' => $application, 'form_id' => $form_id, 'entry_id' =>  $entry_id));
        ?>
        </div>
              </div>
            </div>

  <div class="panel panel-default">
    <div class="panel-heading panel-heading-noradius">
          <h4 class="panel-title">
              <a data-toggle="collapse" class="collapsed"  data-parent="#accordion" href="#commentsConditions">
              <?php echo __("Conditions of Approval"); ?>
                </a>
                </h4>
              </div>
              <div id="commentsConditions" class="panel-collapse collapse">
                <div class="panel-body">
        <?php
        //Check if this application has been previously declined before
        include_partial('comments_conditions', array('application' => $application, 'form_id' => $form_id, 'entry_id' =>  $entry_id));
        ?>
        </div>
              </div>
            </div>
  <div class="panel panel-default">
    <div class="panel-heading  panel-heading-noradius">
          <h4 class="panel-title">
              <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentsSummary">
              <?php echo __("Comments Summary"); ?>
                </a>
                </h4>
              </div>
              <div id="commentsSummary" class="panel-collapse collapse">
                <div class="panel-body panel-body-nopadding">
        <?php
        //Summarize all comments and show all negative comments that may make this application become declined
        include_partial('comments_summary', array('application' => $application, 'form_id' => $form_id, 'entry_id' =>  $entry_id));
        ?>
        </div>
              </div>
            </div>
</div>
