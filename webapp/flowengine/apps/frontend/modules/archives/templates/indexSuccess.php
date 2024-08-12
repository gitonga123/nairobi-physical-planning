<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of all of the currently logged in client's applications
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

/**
 * Executes 'GetDays' function
 *
 * Gets the all the dates between two periods of time
 *
 * @param sfRequest $sEndDate A request object
 * @param sfRequest $sStartDate A request object
 * @return string
 */

use_helper("I18N");

function GetDaysSince($sStartDate, $sEndDate){
    $start_ts = strtotime($sStartDate);
    $end_ts = strtotime($sEndDate);
    $diff = $end_ts - $start_ts;
    return round($diff / 86400);
}
?>


   <div class="pageheader">
       <h2><i class="fa fa-edit"></i><?php echo __('Archives'); ?></h2>
      <div class="breadcrumb-wrapper">

        <ol class="breadcrumb">
          <li><a href=""><?php echo __('Archives'); ?></a></li>
          <li class="active"><?php echo __('This page list all the archived applications you have submitted'); ?></li>
        </ol>
      </div>
    </div>



<div class="contentpanel">

    <div class="row">

        <div class="row">

           <div class="col-sm-112 col-lg-12">
               <?php if ($pager->getResults()): ?>
               <div class="table-responsive">
                   <table class="table mb0" id="table2">
                       <thead>
                       <th  class="no-sort"></th>
                       <th  class="no-sort"></th>
                       <th><?php echo __("Application Form"); ?></th>
                       <th><?php echo __("Application No"); ?></th>
                       <th><?php echo __("Submission Date"); ?></th>
                       <th><?php echo __("Application Status"); ?></th>
                       <th class="no-sort" width="10%"><?php echo __("Actions"); ?></th>
                       </thead>
                       <tbody>
                       <?php foreach ($pager->getResults() as $application): ?>
                           <?php
                           $days =  GetDaysSince($application->getDateOfSubmission(), date("Y-m-d H:i:s"));
                           ?>
                           <?php include_partial('list', array('application' => $application, 'days' => $days)) ?>
                       <?php endforeach; ?>
                       </tbody>
                       <tfoot>
                       <tr>
                           <th colspan="12">
                               <p class="table-showing pull-left"><strong><?php echo $pager->getNbResults(); ?></strong> applications in this stage

                                   <?php if ($pager->haveToPaginate()): ?>
                                       - page <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                                   <?php endif; ?></p>

                               <?php if ($pager->haveToPaginate()): ?>
                                   <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                       <li><a href="/plan/archives/index<?php if($stage): ?>/subgroup/<?php echo $stage; ?><?php endif; ?>/page/1">
                                               <i class="fa fa-angle-left"></i>
                                           </a></li>

                                       <li> <a href="/plan/archives/index<?php if($stage): ?>/subgroup/<?php echo $stage; ?><?php endif; ?>/page/<?php echo $pager->getPreviousPage() ?>">
                                               <i class="fa fa-angle-left"></i>
                                           </a></li>

                                       <?php foreach ($pager->getLinks() as $page): ?>
                                           <?php if ($page == $pager->getPage()): ?>
                                               <li class="active"><a href=""><?php echo $page ?></a>
                                           <?php else: ?>
                                               <li><a href="/plan/archives/index<?php if($stage): ?>/subgroup/<?php echo $stage; ?><?php endif; ?>/page/<?php echo $page ?>"><?php echo $page ?></a></li>
                                           <?php endif; ?>
                                       <?php endforeach; ?>

                                       <li> <a href="/plan/archives/index<?php if($stage): ?>/subgroup/<?php echo $stage; ?><?php endif; ?>/page/<?php echo $pager->getNextPage() ?>">
                                               <i class="fa fa-angle-right"></i>
                                           </a></li>

                                       <li> <a href="/plan/archives/index<?php if($stage): ?>/subgroup/<?php echo $stage; ?><?php endif; ?>/page/<?php echo $pager->getLastPage() ?>">
                                               <i class="fa fa-angle-right"></i>
                                           </a></li>
                                   </ul>
                               <?php endif; ?>

                           </th>
                       </tr>
                       </tfoot>
                   </table>
                   <?php else: ?>
                       <div class="table-responsive">
                           <table class="table dt-on-steroids mb0">
                               <tbody>
                               <tr><td>
                                       No Records Found
                                   </td></tr>
                               </tbody>
                           </table>
                       </div>
                   <?php endif; ?>
           </div><!-- table-responsive -->


                    </div><!-- panel-body-nopadding pt10 -->

            </div><!-- col-sm-9 -->
        </div><!-- row -->

             </div><!--panel-row-->
      </div><!--contentpanel-->
