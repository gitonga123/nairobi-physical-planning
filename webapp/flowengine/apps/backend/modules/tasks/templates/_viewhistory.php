<?php
/**
 * _viewcomments.php partial.
 *
 * Display comments submitted by reviewers #may eventually decide to merge this with the _viewreviewers if i manage to seperate individual reviewer comments
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

//get form id and entry id
$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();
?>
<div class="panel-group" id="accordion">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#audit">
          Audit Logs
        </a>
      </h4>
    </div>
    <div id="audit" class="panel-collapse collapse in">
      <div class="panel-body">
        <div style="margin: 10px;">

          <button class="btn btn-primary pull-right" type="button" style="margin-top: -15px;" onClick="window.location='/plan/applications/view/id/<?php echo $application->getId(); ?>/current_tab/history'">
          Reset
          </button>
          <button class="btn btn-primary pull-right" type="button" style="margin-top: -15px; margin-right: 5px;" data-toggle="modal" data-target="#auditModal">
          Filter
          </button>

          <div class="mb30"></div>

          <p>This is a security log of activity on this application.</p>
          <?php

          if($fromdate)
          {
            $q = Doctrine_Query::create()
                ->from("AuditTrail a")
                ->where("a.form_entry_id = ?", $application->getId())
                ->andWhere("a.action_timestamp BETWEEN ? AND ?", array($fromdate." ".$fromtime, $todate." ".$totime))
                ->orderBy("a.id DESC");
          }
          else
          {
            $q = Doctrine_Query::create()
                ->from("AuditTrail a")
                ->where("a.form_entry_id = ?", $application->getId())
                ->orderBy("a.id DESC");
            }

            $apppager = new sfDoctrinePager('AuditTrail', 10);
            $apppager->setQuery($q);
            $apppager->setPage($apppage);
            $apppager->init();
          ?>
          <table class="table table-bordered">
            <thead>
            <tr><th>Client IP</th><th>Reviewer</th>	<th>Activity</th>	<th>Date and time</th> <th>Device</th></tr>
            </thead>
            <tbody>
            <?php foreach($apppager->getResults() as $audit): ?>
              <tr>
                <td><?php echo $audit->getIpaddress(); ?></td>
                <td><?php
                  $q = Doctrine_Query::create()
                    ->from("CfUser a")
                    ->where("a.nid = ?", $audit->getUserId());
                  $reviewer = $q->fetchOne();
                  if($reviewer)
                  {
                    echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
                  }
                ?></td>
                <td><?php echo ucfirst($audit->getAction()); ?></td>
                <td><?php echo $audit->getActionTimestamp(); ?></td>
                <td><?php echo $audit->getHttpAgent(); ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php if ($apppager->haveToPaginate()): ?>
        <div align="center">
          <ul class="pagination pagination-sm mb0 mt0">
            <li>
              <a href="/plan/applications/view/id/<?php echo $application->getId(); ?>/current_tab/history/apppage/1<?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
              <i class="fa fa-angle-left"></i>
              </a>
            </li>

            <li> 
              <a href="/plan/applications/view/id/<?php echo $application->getId(); ?>/current_tab/history/apppage/<?php echo $apppager->getPreviousPage() ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
              <i class="fa fa-angle-left"></i>
              </a>
            </li>

            <?php foreach ($apppager->getLinks() as $page): ?>
            <?php if ($page == $apppager->getPage()): ?>
                <li class="active"><a href=""><?php echo $page ?></a>
            <?php else: ?>
              <li><a href="/plan/applications/view/id/<?php echo $application->getId(); ?>/current_tab/history/apppage/<?php echo $page ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>"><?php echo $page ?></a></li>
            <?php endif; ?>
            <?php endforeach; ?>

            <li> <a href="/plan/applications/view/id/<?php echo $application->getId(); ?>/current_tab/history/apppage/<?php echo $apppager->getNextPage() ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
            <i class="fa fa-angle-right"></i>
            </a></li>

            <li> <a href="/plan/applications/view/id/<?php echo $application->getId(); ?>/current_tab/history/apppage/<?php echo $apppager->getLastPage() ?><?php if($fromdate){ ?>/fromdate/<?php echo $fromdate ?>/todate/<?php echo $todate ?>/fromtime/<?php echo $fromtime ?>/totime/<?php echo $totime ?><?php } ?>">
            <i class="fa fa-angle-right"></i>
            </a></li>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<!--OTB Start: Movement History -->
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#movement">
          Movement log
        </a>
      </h4>
    </div>
    <div id="movement" class="panel-collapse collapse">
      <div class="panel-body">
        <p>This is a log of movement of this application.</p>
        <?php
          $q = Doctrine_Query::create()
            ->from("ApplicationReference a")
            ->where("a.application_id = ?", $application->getId())
            ->orderBy("a.id DESC");
          $move_history = $q->execute();
        ?>
        <table class="table table-bordered">
          <thead>
            <tr><th>Movement</th><th>Client/Reviewer</th>	<th>Date</th> </tr>
          </thead>
          <tbody>
            <?php foreach($move_history as $ref): ?>
            <tr>
              <td><?php
                if($ref->getApprovedBy()){
                    $q = Doctrine_Query::create()
                    ->from("CfUser a")
                    ->where("a.nid = ?", $ref->getApprovedBy());
                    $reviewer = $q->fetchOne();
                    if($reviewer)
                    {
                    echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
                    }
                  }else{
                    $q = Doctrine_Query::create()
                    ->from("SfGuardUserProfile a")
                    ->where("a.user_id = ?", $application->getUserId());
                    $applicant = $q->fetchOne();
                    if($applicant)
                    {
                    echo $applicant->getFullname()." (Applicant)";
                    }		  
                  }
                ?>
              </td>		
              <td>
              <?php $q = Doctrine_Query::create()
                  ->from("SubMenus a")
                  ->where("a.id = ?", $ref->getStageId())
                  ->orderBy("a.id DESC");
                $stage = $q->fetchOne();
                if($stage){
                echo "Moved to ".$stage->getTitle(); 
                }
              ?>
              </td>
              <td><?php echo $ref->getStartDate(); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <!--OTB End: Movement History -->
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#numbers">
          Previous Application No.
        </a>
      </h4>
    </div>
    <div id="numbers" class="panel-collapse collapse">
      <div class="panel-body">
        <?php 
          $q=Doctrine_Query::create()
            ->from('ApplicationNumberHistory h')
            ->where('h.form_entry_id = ?',$application->getId());
          $previous_nos=$q->execute();
        ?>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Application no</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($previous_nos as $previous_no): ?>
            <tr>
              <td><?php echo $previous_no->getApplicationNumber() ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
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
        <h4 class="modal-title" id="myModalLabel">Filter Activity Log</h4>
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
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
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
