<?php

/**
 * _comments_reviewer template.
 *
 * Shows comments from each reviewer
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>

<div id="accordion">
    <?php
    $taskitems = null;
    $q = Doctrine_Query::create()
        ->from('Task a')
        ->where('a.application_id = ?', $application->getId())
        ->andWhereNotIn('a.owner_user_id', [111, 105]);
    $tasks = $q->execute();
    foreach ($tasks as $task) {
        if ($task->getType() == 3) {
            $q = Doctrine_Query::create()
                ->from('CfUser a')
                ->where('a.nid = ?', $task->getOwnerUserId());
            $reviewer = $q->fetchOne();
            ?>
            <div class="card border-light">
                <div class="card-header bg-light text-dark" id="headingOne">
                    <h5 class="mb-0 card-title text-dark">
                        <button class="btn btn-link text-dark" data-toggle="collapse"
                            data-target="#collapse<?php echo $task->getId(); ?>" aria-expanded="true"
                            aria-controls="collapse<?php echo $task->getId(); ?>">
                            <?php echo $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname(); ?> -
                            <?php echo $reviewer->getDepartment(); ?>
                            <span class="small"></span>(<?php if ($task->getEndDate()) { ?>Last updated on
                                <?php echo $task->getEndDate();
                            } else { ?>Created on
                                <?php echo $task->getDateCreated();
                            } ?>)</span>
                        </button>
                    </h5>
                </div>

                <div id="collapse<?php echo $task->getId(); ?>" class="collapse show"
                    aria-labelledby="heading<?php echo $task->getId(); ?>" data-parent="#accordion">
                    <div class="card-body">
                        <?php echo $task->getStatusName(); ?>
                        <?php
                        if ($task->getStatusName() == "Completed" && $task->getTypeName() == "Invoicing") {
                            echo __("Check billing tab for new invoices");
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            if ($task->getType() != 10) {
                $q = Doctrine_Query::create()
                    ->from('CfUser a')
                    ->where('a.nid = ?', $task->getOwnerUserId());
                $reviewer = $q->fetchOne();

                $q = Doctrine_Query::create()
                    ->from('TaskForms a')
                    ->where('a.task_id = ?', $task->getId());
                $taskform = $q->fetchOne();
                ?>
            <?php } ?>
            <div class="card border-light">
                <div class="card-header bg-light text-dark" id="heading<?php echo $task->getId(); ?>">
                    <h5 class="mb-0 text-dark">
                        <button class="btn btn-link text-dark" data-toggle="collapse"
                            data-target="#collapse<?php echo $task->getId(); ?>" aria-expanded="true"
                            aria-controls="collapse<?php echo $task->getId(); ?>">
                            <?php echo $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname(); ?> -
                            <?php echo $reviewer->getDepartment(); ?>
                            <span class="small"></span>(<?php if ($task->getEndDate()) { ?>Last updated on
                                <?php echo $task->getEndDate();
                            } else { ?>Created on
                                <?php echo $task->getDateCreated();
                            } ?>)</span>
                        </button>
                    </h5>
                </div>

                <div id="collapse<?php echo $task->getId(); ?>" class="collapse show"
                    aria-labelledby="heading<?php echo $task->getId(); ?>" data-parent="#accordion">
                    <div class="card-body">
                        <?php
                        // If this task is complete, show the comments
                        if ($task->getStatusName() == "Completed" && $taskform != null) {
                            include_partial('application/application_comments_details', array('application' => $application, 'form_id' => $taskform->getFormId(), 'entry_id' => $taskform->getEntryId(), 'task' => $task));
                        } else {
                            echo $task->getStatusName();
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>