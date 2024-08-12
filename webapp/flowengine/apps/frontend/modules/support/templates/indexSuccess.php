    <?php
    /**
     * indexSuccess.php template.
     *
     * Displays list of all messages related to currently logged in user
     *
     * @package    backend
     * @subpackage messages
     * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
     */
    use_helper('I18N', 'Date')
    ?>
    <div class="col-md-8 col-lg-9 col-xl-10">
        <div class="card flex-fill">
            <div class="card-header">
                <h3 class="card-title mb-0"><?php echo __("Support"); ?></h3>
                <p class="text-muted ml10"><?php echo __('Showing'); ?> <?php echo $pager->count(); ?> <?php echo __('messages'); ?></p>
            </div>
            <div class="card-body">
                <?php if ($pager->count() > 0) : ?>

                    <div class="table-responsive">
                        <table class="table table-email">
                            <tbody>
                                <?php foreach ($pager->getResults() as $message) : ?>
                                    <tr <?php
                                        if ($message->getMessageread() == "0") {
                                        ?> class="unread" <?php
                                                        }
                                                            ?>>
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
                                                    <span class="media-meta pull-right"><?php echo $message->getActionTimestamp(); ?></span>
                                                    <?php
                                                    $q = Doctrine_Query::create()
                                                        ->from("CfUser a")
                                                        ->where("a.nid = ?", $message->getReviewerId());
                                                    $sender = $q->fetchOne();
                                                    if ($sender) {
                                                    ?>
                                                        <a href="/plan//support/view/id/<?php echo $message->getId(); ?>/messages/read">
                                                            <h4 class="text-primary"><?php echo $sender->getStrfirstname() . " " . $sender->getStrlastname(); ?></h4>
                                                        </a>
                                                        <small class="text-muted"></small>
                                                        <p class="email-summary">
                                                            <a href="/plan//support/view/id/<?php echo $message->getId(); ?>/messages/read">
                                                                <?php
                                                                $words = explode(" ", html_entity_decode($message->getContent()));
                                                                echo strip_tags(implode(" ", array_splice($words, 0, 10)), '<p><a>') . "....";
                                                                ?>
                                                            </a>
                                                        </p>
                                                    <?php
                                                    }
                                                    ?>
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
                                        <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('Messages'); ?>

                                            <?php if ($pager->haveToPaginate()) : ?>
                                                - page <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                                            <?php endif; ?></p>




                                        <?php if ($pager->haveToPaginate()) : ?>
                                            <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                <li><a href="/backend.php/applications/messages/page/1">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <li><a href="/backend.php/applications/messages/page/<?php echo $pager->getPreviousPage() ?>">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <?php foreach ($pager->getLinks() as $page) : ?>
                                                    <?php if ($page == $pager->getPage()) : ?>
                                                        <li class="active"><a href=""><?php echo $page ?></li></a>
                                                    <?php else : ?>
                                                        <li><a href="/backend.php/applications/messages/page/<?php echo $page ?>"><?php echo $page ?></a></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <li><a href="/backend.php/applications/messages/page/<?php echo $pager->getNextPage() ?>">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a></li>

                                                <li><a href="/backend.php/applications/messages/page/<?php echo $pager->getLastPage() ?>">
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

                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table dt-on-steroids mb0">
                            <tbody>
                                <tr>
                                    <td>
                                        <?php echo __('No Records Found'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- panel-body -->