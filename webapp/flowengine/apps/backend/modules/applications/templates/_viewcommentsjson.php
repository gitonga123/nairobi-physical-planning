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
		<div class="panel-heading  panel-heading-noradius">
        	<h4 class="panel-title">
            	<a data-toggle="collapse" data-parent="#accordion" href="#commentsReviewers">
            	<?php echo __("Reviewer Comments"); ?>
                </a>
                </h4>
              </div>
              <div id="commentsReviewers" class="panel-collapse collapse in">
                <div class="panel-body panel-body-nopadding">
        				<?php
        				//Show comments submitted by individual reviewers #Categorized
						include_partial('tasks/application_comments', array('application' => $application));
        				?>
        				</div>
              </div>
            </div>

  <div class="panel panel-default">
    <div class="panel-heading panel-heading-noradius">
          <h4 class="panel-title">
              <a data-toggle="collapse" class="collapsed"  data-parent="#accordion" href="#commentsDeclines">
              <?php echo __("Previous Reasons for Decline"); ?>
                </a>
                </h4>
              </div>
              <div id="commentsDeclines" class="panel-collapse collapse">
                <div class="panel-body">
        <?php
        //Check if this application has been previously declined before
        include_partial('tasks/application_declines', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' => $application->getEntryId()))
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
					include_partial('tasks/comments_conditions', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' =>  $application->getEntryId()));
        ?>
        </div>
              </div>
            </div>
</div>
