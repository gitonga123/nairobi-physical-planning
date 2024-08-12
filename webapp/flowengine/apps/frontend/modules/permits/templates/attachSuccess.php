<?php
/**
 * viewSuccess.php template.
 *
 * Displays a full invoice
 *
 * @package    frontend
 * @subpackage invoices
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper("I18N");
 ?>
 <div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __("Application"); ?> <span><?php echo __("Attach scanned copy"); ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __("You are here"); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan/"><?php echo __("Home"); ?></a></li>
      <li class="active"><?php echo $application->getApplicationId(); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
    <div class="row">


 <div class="col-sm-12">

<ul id="myTab" class="nav nav-tabs" style="margin-top:20px; margin-right:20px;">
                        <li class="active"><a href="#tabs-1" data-toggle="tab"><?php echo __("Application Details"); ?></a></li>
</ul>
        <div id="myTabContent" class="tab-content" style=" margin-right:20px;">
                    <div class="tab-pane fade in active" id="tabs-1">
                        <?php
                        if(empty($document_key)) //Can't attach if there is already a signed permit
                        {
                            ?>
                        <form id="bannerform" class="form-bordered form-horizontal" action="/plan//permits/create/id/<?php echo $permit->getId(); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false">
                            <div class="panel-body panel-body-nopadding">

                                  <?php echo $form->renderGlobalErrors() ?>
                                  <?php if(isset($form['_csrf_token'])): ?>
                                  <?php echo $form['_csrf_token']->render(); ?>
                                  <?php endif; ?>

                                <div class="form-group">
                                    <label class="col-sm-12"><i class="bold-label"><a href='/plan//permits/print/id/<?php echo $permit->getId(); ?>'><?php echo __('Click here to print your application. Sign it and attach it below to continue.'); ?></a></i></label>
                                </div>

                                  <div class="form-group">
                                    <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Attach signed copy'); ?></i></label>
                                     <div class="col-sm-8">
                                      <?php echo $form['permit']->renderError() ?>
                                      <?php echo $form['permit'] ?>
                                    </div>
                                  </div>


                              </div><!--panel-body-->

                              <div class="panel-footer"><button id="submitbuttonname" type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
                          </div>
                        </form>
                            <?php
                        }
                        ?>
                    </div>


</div>

</div><!-- /.row -->



</div><!-- /.marketing -->
