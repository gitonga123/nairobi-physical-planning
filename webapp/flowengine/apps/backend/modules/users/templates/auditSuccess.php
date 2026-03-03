<?php
/**
 * viewuserSuccess.php template.
 *
 * Displays full reviewer details
 *
 * @package    backend
 * @subpackage users
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper("I18N");
?>
<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<div class="pageheader">
  <h2><i class="fa fa-user"></i> <?php echo __('Profile'); ?><span><?php echo __("View reviewer details"); ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('My Account'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">


  <div class="panel panel-default">

    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();  ?></h3> <?php echo __("Reviewer Informaiton"); ?>
    </div>

    <div class="panel-heading text-right">
        <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>" class="btn btn-default"><span class="fa fa-arrow-left"></span>  <?php echo __("Back to Profile"); ?></a>
    </div>

    <div class="panel-body">
        <div class="media media-pro">
            <div class="media-left">
                <a href="#">
                    <?php 
                    if($reviewer->getProfilePic())
                    {
                    ?>
                    <img src="/profiles/<?php echo $reviewer->getProfilePic(); ?>" class="thumbnail" alt="">
                    <?php 
                    }
                    else 
                    {
                    ?>
                    <img src="/assets_backend/images/avatar.jpeg" class="thumbnail" alt="">
                    <?php 
                    }
                    ?>
                </a>
            </div>
            <div class="media-body">
                <!--User Details-->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("User Details"); ?></h3>
                    </div>
                    <div class="panel-body padding-0">
                        <table class="table table-vertical m-b-0">
                            <thead>
                                <tr>
                                    <th class="hidden-sm"><?php echo __("Name"); ?></th>
                                    <th class="hidden-sm"><?php echo __("ID Number"); ?></th>
                                    <th class="hidden-sm"><?php echo __("Mobile Number"); ?></th>
                                    <th class="hidden-sm"><?php echo __("Email"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();  ?></td>
                                    <td><?php echo $reviewer->getStruserid(); ?></td>
                                    <td><?php echo $reviewer->getStrphoneMain1(); ?></td>
                                    <td><?php echo $reviewer->getStremail(); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--End User Details-->
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                  <?php echo __("Audit Log"); ?></h3>

                  <button class="btn btn-primary pull-right" style="margin-top: -26px;" onClick="window.location='/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>'">
                   <?php echo __("Reset"); ?>
                   </button>
                   <button class="btn btn-primary pull-right" style="margin-top: -26px; margin-right: 5px;" data-toggle="modal" data-target="#auditModal">
                   <?php echo __("Filter"); ?>
                   </button>
            </div>

            <!-- panel-heading-->
            <div class="panel-body padding-0">

                <table class="table table-bordered">
                    <thead>
                    <tr><th><?php echo __("Client IP"); ?></th>	<th><?php echo __("Activity"); ?></th>	<th><?php echo __("Date and time"); ?></th> <th><?php echo __("Device"); ?></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach($pager->getResults() as $audit){ ?>
                        <tr>
                        <td><?php echo $audit->getIpaddress(); ?></td>
                        <td><?php echo html_entity_decode($audit->getAction()); ?></td>
                        <td><?php echo $audit->getActionTimestamp(); ?></td>
                        <td><?php echo $audit->getHttpAgent(); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    </table>
                    <?php if ($pager->haveToPaginate()): ?>
                    <div align="center">
                    <ul class="pagination pagination-sm mb0 mt0">
                        <li><a href="/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>/page/1<?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
                        <i class="fa fa-angle-left"></i>
                        </a></li>

                        <li> <a href="/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>/page/<?php echo $pager->getPreviousPage() ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
                        <i class="fa fa-angle-left"></i>
                        </a></li>

                        <?php foreach ($pager->getLinks() as $page): ?>
                            <?php if ($page == $pager->getPage()): ?>
                                <li class="active"><a href=""><?php echo $page ?></a>
                            <?php else: ?>
                            <li><a href="/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>/page/<?php echo $page ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>"><?php echo $page ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <li> <a href="/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>/page/<?php echo $pager->getNextPage() ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
                            <i class="fa fa-angle-right"></i>
                        </a></li>

                        <li> <a href="/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>/page/<?php echo $pager->getLastPage() ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
                            <i class="fa fa-angle-right"></i>
                        </a></li>
                        </ul>
                        </div>
                        <br>
                        <br>
                    <?php endif; ?>

            </div>
            <!-- panel-body -->
        </div>

    </div>

</div>

<!-- Modal -->
<div class="modal fade" id="auditModal" tabindex="-1" role="dialog" aria-labelledby="auditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?php echo __("Filter Activity Log"); ?></h4>
      </div>
      <div class="modal-body">
          <label>From</label>
          <div class="input-group">
            <input type="text" id="from-multiple" name="fromdate" placeholder="mm/dd/yyyy" class="form-control">
            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
          </div>
          <div class="input-group mb15">
            <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
            <div class="bootstrap-timepicker"><input id="timepicker1" name="fromtime" type="text" class="form-control"/></div>
          </div>
          <label>To</label>
          <div class="input-group">
            <input type="text" id="to-multiple" name="todate" placeholder="mm/dd/yyyy" class="form-control">
            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
          </div>
          <div class="input-group mb15">
            <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
            <div class="bootstrap-timepicker"><input id="timepicker2" name="totime" type="text" class="form-control"/></div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
        <button type="submit" class="btn btn-primary"><?php echo __("Save changes"); ?></button>
      </div>
	  </form>
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
</div><!-- modal -->


<script>
jQuery(document).ready(function(){

  jQuery('#from-multiple').datepicker({
    numberOfMonths: 3,
    showButtonPanel: true
  });

  jQuery('#to-multiple').datepicker({
    numberOfMonths: 3,
    showButtonPanel: true
  });

  // Time Picker
  jQuery('#timepicker1').timepicker({showMeridian: false});
  jQuery('#timepicker2').timepicker({showMeridian: false});


});
</script>
