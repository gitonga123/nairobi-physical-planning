<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __($profile->getForm()->getFormName().': '.$service->getTitle().' -> Report'); ?></h3>
        </div>

        <div class="panel-heading text-right">
              <a class="btn btn-primary" id="export" href="/backend.php/profiles/printreport" ><?php echo __('Export'); ?></a>
              <a class="btn btn-primary" id="newpage" href="/backend.php/reports/list" ><?php echo __('Back to Reports'); ?></a>
        </div>
        <div class="panel-body">

            <div class="table-responsive">
                <table class="table dt-on-steroids mb0" id="table3">
                    <thead>
                    <tr>
                        <th class="no-sort">#</th>
                        <th class="b-b-0"><?php echo $profile->getForm()->getFormName(); ?></th>
                        <th class="b-b-0"><?php echo __('Application No.'); ?></th>
                        <th class="b-b-0"><?php echo __('Created By'); ?></th>
                        <th class="b-b-0"><?php echo __('Phone Number'); ?></th>
                        <th class="b-b-0"><?php echo __('Status'); ?></th>
                        <th class="b-b-0"><?php echo __('Action'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;

                        foreach($pager->getResults() as $application)
                        {
                            $count++;

                            $user = $application->getSfGuardUser();
                            $profile = $application->getSfGuardUserProfile();

                            echo "<tr>";
                            ?>
                            <td><?php echo $application->getId(); ?></td>
                            <td><?php echo strtoupper($application->getMfUserProfile()->getTitle()); ?></td>
                            <td><?php echo $application->getApplicationId(); ?></td>
                            <td><?php echo $application->getSfGuardUserProfile()->getFullname()." (".$user->getUsername().")"; ?></td>
                            <td><?php echo $profile->getMobile(); ?></td>
                            <td>
                            <?php 
                                if($application->needsRenewal())
                                {
                                    echo "Expired";
                                }
                                else 
                                {
                                    echo "Renewed";
                                }
                            ?>
                            </td>
                            <td>
                                <a title="<?php echo __('View Service'); ?>" href="/backend.php/applications/view/id/<?php echo $application->getId(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                            </td>
                            <?php
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="12">
                            <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('profiles'); ?>

                                <?php if ($pager->haveToPaginate()): ?>
                                    - <?php echo __('page'); ?> <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                                <?php endif; ?></p>


                            <?php if ($pager->haveToPaginate()): ?>
                                <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                    <li><a href="/backend.php/profiles/report/filter/service/page/1">
                                            <i class="fa fa-angle-left"></i>
                                        </a></li>

                                    <li><a href="/backend.php/profiles/report/filter/service/page/<?php echo $pager->getPreviousPage() ?>">
                                            <i class="fa fa-angle-left"></i>
                                        </a></li>

                                    <?php foreach ($pager->getLinks() as $page): ?>
                                        <?php if ($page == $pager->getPage()): ?>
                                            <li class="active"><a href=""><?php echo $page ?></li></a>
                                        <?php else: ?>
                                            <li><a href="/backend.php/profiles/report/filter/service/page/<?php echo $page ?>"><?php echo $page ?></a></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <li><a href="/backend.php/profiles/report/filter/service/page/<?php echo $pager->getNextPage() ?>">
                                            <i class="fa fa-angle-right"></i>
                                        </a></li>

                                    <li><a href="/backend.php/profiles/report/filter/service/page/<?php echo $pager->getLastPage() ?>">
                                            <i class="fa fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>