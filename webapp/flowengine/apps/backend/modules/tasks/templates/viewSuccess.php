<?php

/**
 * view template.
 *
 * Display a task, its comments sheets/invoices and application details relating to it
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="pageheader">
  <h2><i class="fa fa-th-list"></i> <?php echo __("Tasks"); ?> <span><?php echo $application->getApplicationId(); ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __("You are here"); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan/tasks/list"><?php echo __("Tasks"); ?></a></li>
      <li class="active"><?php echo __("View Task"); ?></li>
    </ol>
  </div>
</div>

<!-- Modal -->
<div id="assessmentModal" class="modal fade <?php echo $task->getType() == 3 && $task->getStatus() == 1 ? "task modal-md" : "task_normal modal-md" ?>" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">
          <?php
          if ($task->getStatus() == 25) {
            echo __("Please choose an action:");
          } else {
            if ($task->getType() == 3) {
              echo __("Create an invoice:");
            } else {
              echo __("Submit Comments:");
            }
          }
          ?>
        </h4>
      </div>
      <div class="modal-body">
        <?php
        //Displays the tasks panel
        include_partial('task_panel', array('application' => $application, 'task' => $task, 'application_form' => $application_form));
        ?>
      </div>
    </div>

  </div>
</div>

<div class="contentpanel">

  <div class="panel panel-default">

    <div class="panel-heading">
      <h3 class="panel-title"><?php echo $application->getApplicationId(); ?></h3>
      <?php
      $q = Doctrine_Query::create()
        ->from("ApForms a")
        ->where("a.form_id = ?", $application->getFormId())
        ->limit(1);
      $form = $q->fetchOne();
      if ($form) {
        echo $form->getFormName();
      }
      ?>
    </div>

    <?php
    //Displays the actions panel
    include_partial('task_actions', array('application' => $application, 'task' => $task));
    ?>

    <div class="panel-body">

      <?php
      //Displays the user panel
      include_partial('task_user_info', array('application' => $application, 'task' => $task));
      ?>


      <?php
      //Displays the user panel
      include_partial('task_details', array('application' => $application, 'task' => $task));
      ?>

      <?php
      if ($application->getMfInvoice()) {
        //Displays any information attached to this application
        include_partial('application_billing', array('application' => $application, 'task' => $task));
      }
      ?>
      <?php if (isset($application)) : ?>
        <?php include_partial('applications/signableattachments', ['application' => $application]) ?>
      <?php endif; ?>
      <?php
      //Displays any information attached to this application
      include_partial('application_downloads', array('application' => $application));
      ?>

      <div class="panel panel-default">

        <div class="panel-heading">
          <h5 class="bug-key-title"><?php echo __("Application Details"); ?></h5>
          <div class="panel-title">
            <?php echo __("Details of the application"); ?>
          </div>

          <div class="btn-group pull-right" style="margin-top: -38px;">
            <?php if ($application->getStage()->getAllowEdit() && $sf_user->mfHasCredential("accesssubmenu" . $application->getApproved())) { ?>
              <a href="/plan/applications/edit/id/<?php echo $application->getId(); ?>" class="btn btn-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Application");  ?></a>
            <?php } ?>
          </div>
        </div>

        <div class="panel-body padding-0">

          <div class="panel with-nav-tabs panel-default">
            <div class="panel-heading">
              <ul class="nav nav-tabs">
                <li <?php if ($current_tab == "application") {
                      echo "class='active'";
                    } ?>><a href="/plan/tasks/view?id=<?php echo $task->getId(); ?>&current_tab=application"><span class="fa fa-bars"></span> <?php echo __('Application Details'); ?></a></li>
                <li <?php if ($current_tab == "reviews") {
                      echo "class='active'";
                    } ?>><a href="/plan/tasks/view?id=<?php echo $task->getId(); ?>&current_tab=reviews"><span class="fa fa-eye"></span> <?php echo __('Review History'); ?></a></li>
                <li <?php if ($current_tab == "messages") {
                      echo "class='active'";
                    } ?>><a href="/plan/tasks/view?id=<?php echo $task->getId(); ?>&current_tab=messages"><span class="fa fa-comments"></span> <?php echo __('Messages'); ?></a></li>
                <li <?php if ($current_tab == "memo") {
                      echo "class='active'";
                    } ?>><a href="/plan/tasks/view?id=<?php echo $task->getId(); ?>&current_tab=memo"><span class="fa fa-comments-o"></span> <?php echo __('Memo'); ?></a></li>
              </ul>
            </div>
            <div class="panel-body p-0">
              <div class="tab-content tab-content-nopadding">
                <?php if ($current_tab == "application") { ?>
                  <div class="tab-pane fade in active" id="ptab1">
                    <form class="form-bordered">
                      <?php include_partial('tasks/application_details', array('application' => $application)); ?>
                    </form>
                  </div>
                <?php } ?>
                <!-- OTB ADD --->
                <?php if ($current_tab == "reviews") : ?>
                  <div class="panel panel-default">
                    <div class="panel-heading panel-heading-noradius">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentReviews">
                          <?php echo __('Review History'); ?>
                        </a>
                      </h4>
                    </div>
                    <div id="commentReviews" class="panel-collapse collapse">
                      <div class="panel-body">
                        <?php
                        //Displays any information attached to this application
                        include_partial('tasks/application_comments', array('application' => $application));
                        ?>
                      </div>
                    </div>
                  </div>

                  <?php if ($declined > 0) : ?>
                    <div class="panel panel-default">
                      <div class="panel-heading panel-heading-noradius">
                        <h4 class="panel-title">
                          <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentsDeclines">
                            <?php echo __("Previous Reasons for Decline"); ?>
                          </a>
                        </h4>
                      </div>
                      <div id="commentsDeclines" class="panel-collapse collapse">
                        <div class="panel-body">
                          <?php
                          include_partial('tasks/application_declines', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' => $application->getEntryId()));
                          ?>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>

                  <div class="panel panel-default">
                    <div class="panel-heading panel-heading-noradius">
                      <h4 class="panel-title">
                        <a style="<?php echo $pstyle ?>" data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentsConditions"><?php echo __("Conditions of Approval"); ?></a>
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
                <?php endif; ?>
                <?php if ($current_tab == "messages") { ?>
                  <div class="tab-pane pt20 active" id="ptab6">
                    <form class="form-bordered">0

                      <?php
                      //Displays a message trail between the client and the reviewers
                      include_partial('tasks/application_messages', array('application' => $application)); //Check time for loading of library scripts
                      ?>
                    </form>
                  </div>
                <?php } ?>
                <?php if ($current_tab == "memo") { ?>
                  <div class="tab-pane pt20 active" id="ptab7">
                    <form class="form-bordered">
                      <?php
                      //Displays a message trail between reviewers
                      include_partial('tasks/application_memos', array('application' => $application)); //Check time for loading of library scripts
                      ?>
                    </form>
                  </div>
                <?php } ?>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Modal -->
  <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 800px;">
      <div class="modal-content">
        <form class="form" action="<?php echo public_path('backend.php/tasks/save/redirect/' . $task->getId()) ?>" method="post" autocomplete="off">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo __('New Task'); ?></h4>
          </div>
          <div class="modal-body modal-body-nopadding" id="newtask">
            <?php echo __("Content goes here"); ?>...
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
            <button type="submit" class="btn btn-primary"><?php echo __('Send Task'); ?></button>
          </div>
        </form>
      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->

  <input type="hidden" name="warning" id="warning" value="1">

  <script language="javascript">
    $("#newtask").load("<?php echo public_path('backend.php/tasks/new/application/' . $application->getId()) ?>");

    <?php
    //Display modal if comment sheet is posted and has errors or if task is completed and actions are needed
    if ($task->getStatus() == 25) {
      $stage = $task->getApplication()->getApproved();

      if ($sf_user->mfHasCredential("accesssubmenu" . $stage) && $task->getTaskStage() == $stage) {
        $action_needed = true;
      }
    }

    if ($comment_sheet_posted || $action_needed) {
    ?>
      $('#assessmentModal').modal({
        backdrop: 'static',
        keyboard: false
      }, 'show');
    <?php
    }
    ?>

    <?php
    if ($decline_warning == true) {
    ?> alert("<?php echo __('You still have an unresolved reason for decline. Please mark it as resolved'); ?>");
    <?php
    }

    if ($action_count > 0 && $task->getStatus() != 1) {
    ?>
      window.onbeforeunload = function(e) {
        if (document.getElementById('warning').value == 1) {
          var message = "<?php echo __('You haven\'t clicked on any action yet. Are you sure you want to exit?'); ?>",
            e = e || window.event;
          // For IE and Firefox
          if (e) {
            e.returnValue = message;
          }

          // For Safari
          return message;
        }
      };
    <?php
    }
    ?>
  </script>

  <style>
    .task .modal-dialog {
      width: 75%;
      margin: 0 auto;
      height: 90%;
    }

    .task .modal-content {
      height: 100%;
     
    }

    .task_normal .modal-dialog {
      width: 50%;
      margin: 0 auto;
      height: 80%;
    }
  </style>