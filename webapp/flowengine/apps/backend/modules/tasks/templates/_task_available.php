<?php
/**
 * _tasks_available.php partial.
 *
 * Displays list of tasks available for current user to pick from
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 ?>
    <?php
    //Display list of workflows if there are no filters set
    if($service == 0) {
        $q = Doctrine_Query::create()
            ->from('Menus a')
            ->orderBy('a.title ASC');
        $workflows = $q->execute();
        foreach ($workflows as $workflow) {
            if ($sf_user->mfHasCredential('accessmenu' . $workflow->getId())) {
                $q = Doctrine_Query::create()
                    ->from('SubMenus a')
                    ->where("a.menu_id = ?", $workflow->getId())
                    ->andWhere("a.deleted = 0")
                    ->orderBy('a.order_no ASC');
                $stages = $q->execute();
                foreach ($stages as $stage) {
                    if ($sf_user->mfHasCredential('accesssubmenu' . $stage->getId())) {
                        ?>
                        <h4><a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>"><i
                                class="fa fa-caret-right"></i> <?php echo $workflow->getTitle(); ?></a></h4>
                        <?php
                        break;
                    }
                }
            }
        }
    }
    else {
        //Display a list of stages if no filters set for stage
        if($stage == 0){
            $application_manager = new ApplicationManager();

            $q = Doctrine_Query::create()
                ->from('Menus a')
                ->where('a.id = ?', $service)
                ->orderBy('a.order_no ASC');
            $workflow = $q->fetchOne();

            echo "<div class='panel-heading'><small> <a href='/backend.php/tasks/list'>".__("Workflows")."</a> &gt; " . $workflow->getTitle() . "</small></div> ";

            $q = Doctrine_Query::create()
                ->from('SubMenus a')
                ->where("a.menu_id = ?", $service)
                ->andWhere("a.deleted <> 1")
                ->orderBy('a.order_no ASC');
            $stages = $q->execute();



            foreach ($stages as $stage) {
                if ($sf_user->mfHasCredential('accesssubmenu' . $stage->getId())) {
                    ?>
                    <div class="panel-heading"><a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>/stage/<?php echo $stage->getId(); ?>">
                        <?php
                            $q = Doctrine_Query::create()
                                ->from("FormEntry a")
                                ->where("a.parent_submission = ?", 0)
                                ->andWhere("a.deleted_status = ?", 0)
                                ->andWhere("a.assessment_inprogress = ?", 0)
                                ->andwhere("a.approved = ?", $stage->getId());
                            $applications = $q->count();
                        ?>
                        <span class="badge pull-right"><?php echo $applications; ?></span>
                        <i class="fa fa-caret-right"></i>
                        <?php echo $stage->getTitle(); ?></a> </div>
                    <?php
                }
            }
        }
        else {
            //Display list of applications in a stage
            $q = Doctrine_Query::create()
                ->from('Menus a')
                ->where('a.id = ?', $service)
                ->orderBy('a.order_no ASC');
            $workflow = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from('SubMenus a')
                ->where('a.id = ?', $stage)
                ->orderBy('a.order_no ASC');
            $stage = $q->fetchOne();

            echo "<h3 class='panel-title'> <a href='/backend.php/tasks'>".__("Workflows")."</a> &gt; <a href='/backend.php/tasks/list/service/".$workflow->getId()."'>"  . $workflow->getTitle() . "</a> &gt; " . $stage->getTitle() . "</h3>";

            //Use application manager to filter logiced applications
            $application_manager = new ApplicationManager();

            $q = Doctrine_Query::create()
                ->from("FormEntry a")
                ->where("a.parent_submission = ?", 0)
                ->andWhere("a.deleted_status = ?", 0)
                ->andWhere("a.assessment_inprogress = ?", 0)
                ->andwhere("a.approved = ?", $stage->getId())
                ->orderBy("a.date_of_submission ASC");

            $pager = new sfDoctrinePager('FormEntry', 10);
            $pager->setQuery($q);
            $pager->setPage($page);
            $pager->init();

            ?>


            <form action="/backend.php/tasks/batchpick" method="post">
            <table class="table table-striped table-hover table-special">
                <thead>
                <tr>
                    <th >#</th>
                    <th ><?php echo __("Application No"); ?></th>
                    <th ><?php echo __("Submitted On"); ?></th>
                    <th ></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pager->getResults() as $application)
                {
                    ?>
                    <tr>
                        <td><input type="checkbox" name="batch_pick[]" id="batch_<?php echo $application->getId(); ?>" value="<?php echo $application->getId(); ?>"></td>
                        <td><a href="/backend.php/tasks/pick/id/<?php echo $application->getId(); ?>"><?php echo $application->getApplicationId(); ?></a></td>
                        <td><?php echo $application->getDateOfSubmission(); ?></td>
                        <td>
                                <a  title='<?php echo __('Pick Task'); ?>' href='<?php echo public_path("backend.php/tasks/pick/id/".$application->getId()); ?>'> <span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="12">
                        <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('Tasks'); ?>

                            <?php if ($pager->haveToPaginate()): ?>
                                - <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                            <?php endif; ?></p>

                        <?php if ($pager->haveToPaginate()): ?>
                            <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                <li><a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>/stage/<?php echo $stage->getId(); ?>/page/1">
                                        <i class="fa fa-angle-left"></i>
                                    </a></li>

                                <li> <a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>/stage/<?php echo $stage->getId(); ?>/page/<?php echo $pager->getPreviousPage() ?>">
                                        <i class="fa fa-angle-left"></i>
                                    </a></li>

                                <?php foreach ($pager->getLinks() as $page): ?>
                                    <?php if ($page == $pager->getPage()): ?>
                                        <li class="active"><a href=""><?php echo $page ?></a>
                                    <?php else: ?>
                                        <li><a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>/stage/<?php echo $stage->getId(); ?>/page/<?php echo $page ?>"><?php echo $page ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <li> <a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>/stage/<?php echo $stage->getId(); ?>/page/<?php echo $pager->getNextPage() ?>">
                                        <i class="fa fa-angle-right"></i>
                                    </a></li>

                                <li> <a href="/backend.php/tasks/list/service/<?php echo $workflow->getId(); ?>/stage/<?php echo $stage->getId(); ?>/page/<?php echo $pager->getLastPage() ?>">
                                        <i class="fa fa-angle-right"></i>
                                    </a></li>
                            </ul>
                        <?php endif; ?>
                    </th>
                </tr>
                <tr>
                    <td colspan="7"><button type="submit" class="btn btn-primary">Pick Tasks</button></td>
                </tr>
                </tfoot>
            </table>
            </form>
            <?php

        }
    }
    ?>
