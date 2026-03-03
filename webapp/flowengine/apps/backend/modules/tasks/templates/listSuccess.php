<?php
/**
 * list template.
 *
 * Display a list of assigned and completed tasks
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

?>
<div class="pageheader">
<h2><i class="fa fa-envelope"></i> <?php echo __("Tasks"); ?> <span><?php echo __("List of tasks assigned to reviewers"); ?></span></h2>
<div class="breadcrumb-wrapper">
    <span class="label"><?php echo __("You are here"); ?>:</span>
    <ol class="breadcrumb">
    <li><a href="/plan/dashboard"><?php echo __("Home"); ?></a></li>
    <li class="active"><?php echo __("Tasks"); ?></li>
    </ol>
</div>
</div>

<div class="contentpanel">


<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __("Tasks"); ?></h3>
    <?php echo __("Manage tasks below"); ?>
  </div>


  <div class="panel-body p-0">

    <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#all_tasks" data-toggle="tab"><?php echo __("Available Tasks"); ?> <span class="label label-primary">0</span></a></li>
                            <li><a href="#my_tasks" data-toggle="tab"><?php echo __("My Tasks"); ?> <span class="label label-primary">0</span></a></li>
                        </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="all_tasks">

                          <div class="panel panel-default">
                          <div class="panel-heading">
                            <h3 class="panel-title"><?php echo __("List of tasks available for you to work on"); ?></h3>
                          </div>
                            <?php include_partial('task_available', array('service' => $service, 'stage' => $stage, 'page' => $page)) ?>
                          </div>

                        </div>
                        <div class="tab-pane fade" id="my_tasks">
                          <p><?php echo __("List of tasks assigned to you to work on"); ?></p>

                          <?php include_partial('task_inbox', array('tasks' => $tasks)) ?>
                        </div>
                    </div>
                </div>
            </div>


  </div>

</div>

</div>


</div><!-- mainpanel -->
