<?php

/**
 * viewSuccess.php template.
 *
 * Displays the full message
 *
 * @package    frontend
 * @subpackage messages
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<?php
$q = Doctrine_Query::create()
    ->from('FormEntry a')
    ->where('a.id = ?', $communication->getApplicationId());
$application = $q->fetchOne();
?>
<div class="col-md-8 col-lg-9 col-xl-10">
    <div class="card flex-fill">
        <div class="card-header">
            <h3 class="card-title"><?php echo __('Application No'); ?> <?php echo $application->getApplicationId(); ?></h3>
            <div class="panel-btns">
                <div class="pull-right">
                    <div class="top-btn">
                        <a class="btn btn-primary" href="/index.php//application/view/id/<?php echo $communication->getApplicationId(); ?>"><?php echo __('View Application'); ?></a>
                    </div>
                </div>
            </div>
        </div><!-- panel-heading -->

        <div class="card-body">
            <?php
            $q = Doctrine_Query::create()
                ->from('Communications a')
                ->where('a.application_id = ?', $communication->getApplicationId());
            $messages = $q->execute();

            foreach ($messages as $message) {

                if ($message->getArchitectId() != "") {
                    $q = Doctrine_Query::create()
                        ->from('SfGuardUser a')
                        ->where('a.id = ?', $message->getArchitectId());
                    $architect = $q->fetchOne();

                    $fullname = $architect->getProfile()->getFullname();
                } else if ($message->getReviewerId() != "") {
                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.nid = ?', $message->getReviewerId());
                    $reviewer = $q->fetchOne();

                    $fullname = $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname();
                }
            ?>
                <div class="media">
                    <div class="media-body">
                        <span class="media-meta pull-right"><?php echo $message->getActionTimestamp(); ?></span>
                        <h4 class="text-primary"><?php echo $fullname; ?></h4>
                        <!--small class="text-muted"><?php echo __('From'); ?>: hisemail@hisemail.com</small-->
                    </div>
                </div><!-- media -->

                <?php
                echo $message->getContent();
                ?>
                <div class="mb20"></div>

            <?php
            }
            ?>
            <form id="reply_form" name="reply_form" method="post" action="/index.php//messages/view/id/<?php echo $communication->getId(); ?>" autocomplete="off" data-ajax="false">

                <textarea name="reply" id="wysiwyg" placeholder="<?php echo __('Enter text here'); ?>..." class="form-control" rows="10" data-autogrow="true"></textarea>

            </form>

            <div class="panel-footer">
                <button type='submit' class="btn btn-primary"><?php echo __('Send'); ?> </button>
                </div-->
            </div><!-- panel-body -->
        </div><!-- panel -->
    </div>
</div>