<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of all notifications related to currently logged in user
 *
 * @package    backend
 * @subpackage messages
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>
<?php use_helper('I18N', 'Date') ?>


<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> <?php echo __('Notifications'); ?></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __('You are here'); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="/plan"><?php echo __('Home'); ?></a></li>
            <li class="active"><?php echo __('Notifications'); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel panel-email">

    <div class="row">

        <div class="col-sm-12 col-lg-12">

            <div class="panel panel-default">
                <div class="panel-body panel-body-nopadding">
                    <h5 class="subtitle mb5 mt10 ml10"><?php echo __('Inbox'); ?></h5>
                    <p class="text-muted ml10"><?php echo __('Showing'); ?> <?php echo $pager->count(); ?> <?php echo __('notifications'); ?></p>

                    <?php if($pager->count() > 0): ?>

                        <div class="table-responsive">
                            <table class="table table-email">
                                <tbody>
                                <?php foreach ($pager->getResults() as $notification): ?>
                                    <?php
                                        $q = Doctrine_Query::create()
                                           ->from("FormEntry a")
                                           ->where("a.id = ?", $notification->getApplicationId());
                                        $application = $q->fetchOne();
                                    ?>
                                    <tr
                                        <?php
                                        if($notification->getConfirmedReceipt() == "0")
                                        {
                                            ?>
                                            class="unread"
                                        <?php
                                        }
                                        ?>
                                        >
                                        <td>
                                            <div class="ckbox ckbox-success">
                                                <input type="checkbox" id="checkbox1">
                                                <label for="checkbox1"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="" class="star"><i class="glyphicon glyphicon-star"></i></a>
                                        </td>
                                        <td>
                                            <div class="media">
                                                <div class="media-body">
                                                    <span class="media-meta pull-right"><?php echo $notification->getSentOn(); ?></span>
                                                    <a href="<?php echo public_path("plan/notifications/viewnotification/id/".$notification->getId()); ?>">
                                                        <h4 class="text-primary"><?php echo ($application)?$application->getApplicationId():""; ?></h4>
                                                    </a>
                                                    <small class="text-muted"></small>
                                                    <p class="email-summary">
                                                        <a href="<?php echo public_path("plan/notifications/viewnotification/id/".$notification->getId()); ?>">
                                                            <?php echo strip_tags(html_entity_decode($notification->getNotification())); ?>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                endforeach;
                                ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="12">
                                        <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('Notifications'); ?>

                                            <?php if ($pager->haveToPaginate()): ?>
                                                - page <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                                            <?php endif; ?></p>




                                        <?php if ($pager->haveToPaginate()): ?>
                                            <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                <li><a href="/plan/applications/notifications/page/1">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <li><a href="/plan/applications/notifications/page/<?php echo $pager->getPreviousPage() ?>">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <?php foreach ($pager->getLinks() as $page): ?>
                                                    <?php if ($page == $pager->getPage()): ?>
                                                        <li class="active"><a href=""><?php echo $page ?></li></a>
                                                    <?php else: ?>
                                                        <li><a href="/plan/applications/notifications/page/<?php echo $page ?>"><?php echo $page ?></a></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <li><a href="/plan/applications/notifications/page/<?php echo $pager->getNextPage() ?>">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a></li>

                                                <li><a href="/plan/applications/notifications/page/<?php echo $pager->getLastPage() ?>">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                                </tfoot>
                            </table>
                        </div><!-- table-responsive -->

                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table dt-on-steroids mb0">
                                <tbody>
                                <tr><td>
                                        <?php echo __('No Records Found'); ?>
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div><!-- panel-body -->
            </div><!-- panel -->

        </div><!-- col-sm-9 -->

    </div><!-- row -->

</div>

</div><!-- mainpanel -->
