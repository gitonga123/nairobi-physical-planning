<table class="table table-striped table-hover table-special">
    <thead>
    <tr>
        <th >#</th>
        <th ><?php echo __("Service"); ?></th>
        <th ><?php echo __("Status"); ?></th>
        <th ><?php echo __("Submitted On"); ?></th>
        <th ><?php echo __("Submitted By"); ?></th>
        <th ></th>
    </tr>
    </thead>
    <tbody>
        <?php
        foreach ($current_paginator->getResults() as $task)
        {
            $application = $task->getApplication();
            ?>
            <tr>
                <td><?php echo $application->getId(); ?></td>
                <td style="word-wrap:break-word; width: 250px;">
                    <?php echo $application->getTitle(); ?>
                    <h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
                    <p><?php echo date('d F Y H:i:s', strtotime($application->getDateOfSubmission())); ?></p>
                </td>
                <td style="vertical-align:middle">
                <?php
                    echo $application->getStatusName(). '<br/><br/><small>' . html_entity_decode($task->getRemarks()) . '</small>';
                ?>
                </td>
                <td style="vertical-align:middle">
                    <?php echo $application->getDateOfSubmission(); ?>
                </td>
                <td style="vertical-align:middle">
                    <?php 
                      $q = Doctrine_Query::create()
                         ->from("SfGuardUserProfile a")
                         ->where("a.user_id = ?", $application->getUserId());
                      $profile = $q->fetchOne(); 
                      if($profile)
                      {
                         echo $profile->getFullname();
                      } 
                    ?>
                </td>
                <td style="vertical-align:middle">
                    <a class='btn btn-default btn-xs' title='<?php echo __('View Task'); ?>' href='<?php echo public_path("plan/tasks/view/id/".$task->getId()); ?>'><span class="fa fa-eye"></span></a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="12">
                <p class="table-showing pull-left"><strong><?php echo count($current_paginator) ?></strong> <?php echo __('Tasks'); ?>

                    <?php if ($current_paginator->haveToPaginate()): ?>
                        - <strong><?php echo $current_paginator->getPage() ?>/<?php echo $current_paginator->getLastPage() ?></strong>
                    <?php endif; ?></p>

                <?php if ($current_paginator->haveToPaginate()): ?>
                    <ul class="pagination pagination-sm mb0 mt0 pull-right">
                        <li><a href="/plan/dashboard/index/current/queued/page/1">
                                <i class="fa fa-angle-left"></i>
                            </a></li>

                        <li> <a href="/plan/dashboard/index/current/queued/page/<?php echo $current_paginator->getPreviousPage() ?>">
                                <i class="fa fa-angle-left"></i>
                            </a></li>

                        <?php foreach ($current_paginator->getLinks() as $page): ?>
                            <?php if ($page == $current_paginator->getPage()): ?>
                                <li class="active"><a href=""><?php echo $page ?></a>
                            <?php else: ?>
                                <li><a href="/plan/dashboard/index/current/queued/page/<?php echo $page ?>"><?php echo $page ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <li> <a href="/plan/dashboard/index/current/queued/page/<?php echo $current_paginator->getNextPage() ?>">
                                <i class="fa fa-angle-right"></i>
                            </a></li>

                        <li> <a href="/plan/dashboard/index/current/queued/page/<?php echo $current_paginator->getLastPage() ?>">
                                <i class="fa fa-angle-right"></i>
                            </a></li>
                    </ul>
                <?php endif; ?>
            </th>
        </tr>
    </tfoot>
</table>
