<?php

/**
 * _latest_applications template.
 *
 * Displays a list of the latest applications
 *
 * @package    frontend
 * @subpackage lastest_applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper("I18N");
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo __("My Businesses"); ?>
            <a href="/index.php//profile/create" class="btn btn-primary btn-sm pull-right" style="margin-top: -4px; color: #FFFFFF;"><?php echo __("Add Business"); ?></a>
        </h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover table-special table-striped">
                <thead>
                <th width="40%"><?php echo __("Business Name"); ?></th>
                <th><?php echo __("Verified?"); ?></th>
                <th></th>
                </thead>
                <tbody>
                <?php foreach ($my_businesses->getResults() as $my_business): ?>
                    <tr>
                        <td style="word-wrap:break-word; width: 250px;">
                            <?php
                                $status = "";

                                if($my_business->getDeleted())
                                {
                                    $status = " <span class='label label-danger'>".__("Not Active")."</span>";
                                }

                                echo $my_business->getTitle();
                            ?>
                        </td>
                        <td>
                            <?php 
                                if($my_business->getDeleted())
                                {
                                    echo " <span class='label label-danger'>".__("Not Active")."</span>";
                                }
                                else 
                                {
                                    echo " <span class='label label-success'>".__("Active")."</span>";
                                }
                            ?>
                        </td>
                        <td>
                            <a class="btn btn-xs btn-default"  title='<?php echo __('View Profile'); ?>' href='/index.php//profile/view/id/<?php echo $my_business->getId(); ?>'><?php echo __("View"); ?> </a>
                        </td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
                <tfoot>
                   <tr>
                       <th colspan="12">
                               <?php if ($my_businesses->haveToPaginate()): ?>
                                   - <?php echo __("page"); ?> <strong><?php echo $my_businesses->getPage() ?>/<?php echo $my_businesses->getLastPage() ?></strong>
                               <?php endif; ?></p>

                           <?php if ($my_businesses->haveToPaginate()): ?>
                               <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                   <li><a href="/index.php//dashboard/index/apage/1">
                                           <i class="fa fa-angle-left"></i>
                                       </a></li>

                                   <li> <a href="/index.php//dashboard/index/apage/<?php echo $my_businesses->getPreviousPage() ?>">
                                           <i class="fa fa-angle-left"></i>
                                       </a></li>

                                   <?php foreach ($my_businesses->getLinks() as $page): ?>
                                       <?php if ($page == $my_businesses->getPage()): ?>
                                           <li class="active"><a href=""><?php echo $page ?></a>
                                       <?php else: ?>
                                           <li><a href="/index.php//dashboard/index/apage/<?php echo $page ?>"><?php echo $page ?></a></li>
                                       <?php endif; ?>
                                   <?php endforeach; ?>

                                   <li> <a href="/index.php//dashboard/index/apage/<?php echo $my_businesses->getNextPage() ?>">
                                           <i class="fa fa-angle-right"></i>
                                       </a></li>

                                   <li> <a href="/index.php//dashboard/index/apage/<?php echo $my_businesses->getLastPage() ?>">
                                           <i class="fa fa-angle-right"></i>
                                       </a></li>
                               </ul>
                           <?php endif; ?>

                       </th>
                   </tr>
                   </tfoot>
            </table>
       </div>
    </div>
</div>
