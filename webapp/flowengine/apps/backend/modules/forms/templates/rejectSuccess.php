<?php
use_helper("I18N");
?>
<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __("Application"); ?> <span><?php echo __("Decline"); ?></span></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __("You are here"); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("plan");
            ; ?>plan"><?php echo __("Home"); ?></a></li>
            <li class="active"><?php echo __("Applications"); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __("Reject Application"); ?></h3>
            <p><?php echo __("Send to rejected applications"); ?></p>
            <div class="pull-right">

            </div>
        </div>

        <div class="panel-body padding-0">
            <div class="col-md-12">
                <form action="<?php echo url_for('/backend.php/forms/confirmreject') ?>" method="POST" autocomplete="off"
                    data-ajax="false">
                    <fieldset>
                        <input type="hidden" name="id" value="<?php echo $application->getId(); ?>">
                        <?php if ($moveto) { ?>
                            <input type="hidden" name="moveto" value="<?php echo $moveto; ?>">
                        <?php } ?>
                        <label>
                            <h4><?php echo __("Reason"); ?>:</h4>
                        </label>
                        <div>
                            <textarea class="form-control" name="reason" cols="50" rows="10" required></textarea>
                        </div>
                    </fieldset>
                    <fieldset>
                        <fieldset>
                            <section>
                                <div>
                                    <button id="sendComments" class="btn btn-default mt10" type="submit"
                                        class="submit"><?php echo __("Send Comments"); ?></button>
                                </div>
                            </section>
                        </fieldset>
                </form>
                <br>
            </div>
        </div>