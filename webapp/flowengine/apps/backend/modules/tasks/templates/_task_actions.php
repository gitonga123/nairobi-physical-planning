<?php

/**
 * _actionspanel partial.
 *
 * Display actions related to the task and application
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<a href="/plan/applications/view/id/<?php echo $application->getId() ?>" class="btn btn-warning">Back to application</a>
<div class="panel-heading text-right">

    <!-- Buttons to view comment sheets and invoice forms -->
    <?php
    // Action to a task to an application
    $q = Doctrine_Query::create()
        ->from('SubMenus a')
        ->where('a.id = ?', $application->getApproved());
    $stage = $q->fetchOne();
    error_log('-----Application---' . $stage->getStageType());

    if ($task->getStatus() == 1) {
        $q = Doctrine_Query::create()
            ->from("TaskForms a")
            ->where("a.task_id = ?", $task->getId());
        $taskforms = $q->execute();
        if (sizeof($taskforms) == 0) {
            if (!empty($form)) {
    ?>
                <a class="btn btn-primary" data-toggle="modal" data-target="#assessmentModal"><i class="fa fa-comments mr5"></i> <?php echo $task->getTypeName(); ?></a>
                <?php
            } else {
                if ($stage->getStageType() != 8) {
                ?>
                    <a class="btn btn-primary" data-toggle="modal" data-target="#assessmentModal"><i class="fa fa-comments mr5"></i> <?php echo $task->getTypeName();  ?></a>
                <?php
                } else { ?>
                    <div class="alert alert-info">
                        <strong>Assign!</strong> Please Assign Task(s) to Other Reviewers.
                    </div>
            <?php }
            }
        } else {
            ?>
            <a class="btn btn-primary" data-toggle="modal" data-target="#assessmentModal"><i class="fa fa-comments mr5"></i> <?php echo $task->getTypeName(); ?></a>
        <?php
        }
    }

    if ($sf_user->mfHasCredential("assigntask") && ($stage->getStageType() == 8 || $stage->getStageType() == 2) && $sf_user->mfHasCredential("accesssubmenu" . $stage->getId())) {
        ?>
        <a class="btn btn-success" data-toggle="modal" data-target="#myModal"><?php echo __('Assign Application'); ?></a>
    <?php
    }


    if (($task->getOwnerUserId() == $task->getCreatorUserId() && $task->getStatus() == 1) || ($sf_user->mfHasCredential("has_hod_access") && $task->getStatus() == 1)) {
    ?>
        <a class="btn btn-danger" href="/plan/tasks/cancel/id/<?php echo $task->getId(); ?>/redirect/dashboard"><?php echo __('Cancel Task'); ?></a>
    <?php
    }
    ?>
</div>