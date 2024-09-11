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
        <a href="/plan/users/audit/id/<?php echo $reviewer->getNid(); ?>" class="btn btn-default"><span class="fa fa-history"></span>  <?php echo __("Audit Log"); ?></a>
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
                                    <?php 
                                    if($sf_user->mfHasCredential("access_reviewers"))
                                    {
                                    ?>
                                    <th class="hidden-sm text-right"><?php echo __("Action"); ?></th>
                                    <?php 
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();  ?></td>
                                    <td><?php echo $reviewer->getStruserid(); ?></td>
                                    <td><?php echo $reviewer->getStrphoneMain1(); ?></td>
                                    <td><?php echo $reviewer->getStremail(); ?></td>
                                    <!--Add permitsions to this  column-->
                                    <td class="text-right">
                                      <?php 
                                       if($sf_user->mfHasCredential("access_reviewers"))
                                       {
                                      ?>
                                      <a href="" class="btn btn-xs btn-info" data-toggle="modal" data-target="#passwordModal"><span class="fa fa-lock"></span> <?php echo __("Reset Password"); ?></a>
                                      <a href="" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#userModal"><span class="fa fa-edit"></span> </a>
                                      <?php 
                                       }
                                      ?>
                                    </td>
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
                  <?php echo __("Other Details"); ?></h3>
            </div>

            <?php 
            if($sf_user->mfHasCredential("access_reviewers"))
            {
            ?>
            <div class="panel-heading text-right">
              <a href="" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#otherModal"><span class="fa fa-edit"></span></a>
            </div>
            <?php
            }
            ?>

            <!-- panel-heading-->
            <div class="panel-body padding-0">


                <table class="table table-special">
                      <tr>
                          <td><?php echo __("Country"); ?></td>
                          <td><?php echo $reviewer->getStrcountry(); ?></td>
                          <td></td>
                      </tr>
                        <tr>
                            <td><?php echo __("City"); ?></td>
                            <td><?php echo $reviewer->getStrcity(); ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><?php echo __("Designation"); ?></td>
                            <td><?php echo $reviewer->getUserdefined1Value(); ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><?php echo __("Man Number"); ?></td>
                            <td><?php echo $reviewer->getUserdefined2Value(); ?></td>
                            <td></td>
                        </tr>
                </table>
            </div>
            <!-- panel-body -->
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                  <?php echo __("Access"); ?></h3>
                  <?php echo __("Roles and permissions management"); ?>
            </div>
            <!-- panel-heading-->
            <div class="panel-body padding-0">


                <table class="table table-special">
                    <thead>
                        <tr>
                            <th><?php echo __("Department"); ?></th>
                            <th><?php echo __("User group (s)"); ?></th>
                            <?php 
                            if($sf_user->mfHasCredential("access_reviewers"))
                            {
                            ?>
                            <th><?php echo __("Actions"); ?></th>
                            <?php 
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $reviewer->getDepartment(); ?>
							</td>
                            <td>
                                <?php 
                                $groups = $reviewer->getGroups();
                                foreach($groups as $group)
                                {
                                    echo $group->getName().",";
                                }
                                ?>
                            </td>

                            </td>
                            <td>
                                <?php 
                                if($sf_user->mfHasCredential("access_reviewers"))
                                {
                                ?>
                                <a href="" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#accessModal"><span class="fa fa-edit"></span> </a>
                                <?php 
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- panel-body -->
        </div>




        <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab1default" data-toggle="tab"><span class="fa fa-tasks"></span> <?php echo __("Current tasks"); ?></a></li>
                            <li><a href="#tab2default" data-toggle="tab"><span class="fa fa-bars"></span> <?php echo __("Completed Tasks"); ?></a></li>
                            <li><a href="#tab3default" data-toggle="tab"><span class="fa fa-times"></span> <?php echo __("Canceled Tasks"); ?></a></li>
                            <li><a href="#tab4default" data-toggle="tab"><span class="fa fa-history"></span> <?php echo __("Audit log"); ?></a></li>
                        </ul>
                </div>
                <div class="panel-body padding-0">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab1default">

                          <table class="table table-striped table-hover table-special m-t-20 m-b-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo __("Service"); ?></th>
                                <th><?php echo __("Status"); ?></th>
                                <th><?php echo __("Submitted On"); ?></th>
                                <th><?php echo __("Submitted By"); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                                foreach($current_paginator->getResults() as $task){ 
                                    $application = $task->getApplication();
                            ?>
                              <tr>
                                <td><?php echo $task->getId(); ?></td>
                                <td style="word-wrap:break-word; width: 250px;">
                                    <?php echo $application->getTitle(); ?>
                                    <h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
                                    <p><?php echo date('d F Y H:m:s', strtotime($application->getDateOfSubmission())); ?></p>
                                </td>
                                <td>
                                <?php
                                    echo $application->getStatusName();
                                ?>
                                </td>
                                <td style="vertical-align:middle">
                                    <?php echo $application->getDateOfSubmission(); ?>
                                </td>
                                <td style="vertical-align:middle">
                                    <?php echo $application->getSfGuardUserProfile()->getFullname(); ?>
                                </td>
                                <td>
                                <a class='btn btn-default btn-xs' title='<?php echo __('View Task'); ?>' href='<?php echo public_path("backend.php/tasks/view/id/".$task->getId()); ?>'><span class="fa fa-eye"></span></a>
                                <?php 
                                if($sf_user->mfHasCredential("has_hod_access"))
                                {
                                ?>
                                <a title="Cancel Task" class="btn btn-danger btn-xs" href="<?php echo public_path("backend.php/tasks/cancel/id/".$task->getId()); ?>"><span class="fa fa-times"></span></a>  
                                <?php 
                                }
                                ?>            
                                </td>
                            </tr>
                            <?php } ?>        
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="12">
                                        <p class="table-showing pull-left"><strong><?php echo count($current_paginator) ?></strong> <?php echo __('Tasks'); ?>

                                            <?php if ($current_paginator->haveToPaginate()): ?>
                                                - <strong><?php echo $current_paginator->getPage() ?>/<?php echo $current_paginator->getLastPage() ?></strong>
                                            <?php endif; ?></p>

                                        <?php if ($current_paginator->haveToPaginate()): ?>
                                            <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/currentpage/1#end">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/currentpage/<?php echo $current_paginator->getPreviousPage() ?>#end">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <?php foreach ($current_paginator->getLinks() as $page): ?>
                                                    <?php if ($page == $current_paginator->getPage()): ?>
                                                        <li class="active"><a href=""><?php echo $page ?></a>
                                                    <?php else: ?>
                                                        <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/currentpage/<?php echo $page ?>#end"><?php echo $page ?></a></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/currentpage/<?php echo $current_paginator->getNextPage() ?>#end">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a></li>

                                                <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/currentpage/<?php echo $current_paginator->getLastPage() ?>#end">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a></li>
                                            </ul>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>

                        </div>
                        <div class="tab-pane fade" id="tab2default">
                            <table class="table table-striped table-hover table-special m-t-20 m-b-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Service"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Submitted On"); ?></th>
                                    <th><?php echo __("Submitted By"); ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php 
                                    foreach($completed_paginator->getResults() as $task){ 
                                        $application = $task->getApplication();
                                ?>
                                <tr>
                                    <td><?php echo $task->getId(); ?></td>
                                    <td style="word-wrap:break-word; width: 250px;">
                                        <?php echo $application->getTitle(); ?>
                                        <h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
                                        <p><?php echo date('d F Y H:m:s', strtotime($application->getDateOfSubmission())); ?></p>
                                    </td>
                                    <td>
                                    <?php
                                        echo $application->getStatusName();
                                    ?>
                                    </td>
                                    <td style="vertical-align:middle">
                                        <?php echo $application->getDateOfSubmission(); ?>
                                    </td>
                                    <td style="vertical-align:middle">
                                        <?php echo $application->getSfGuardUserProfile()->getFullname(); ?>
                                    </td>
                                    <td>
                                    <a class='btn btn-default btn-xs' title='<?php echo __('View Task'); ?>' href='<?php echo public_path("backend.php/tasks/view/id/".$task->getId()); ?>'><span class="fa fa-eye"></span></a>
                                    <?php 
                                    if($sf_user->mfHasCredential("has_hod_access"))
                                    {
                                    ?>
                                    <a title="Cancel Task" class="btn btn-danger btn-xs" href="<?php echo public_path("backend.php/tasks/cancel/id/".$task->getId()); ?>"><span class="fa fa-times"></span></a>  
                                    <?php 
                                    }
                                    ?>            
                                    </td>
                                </tr>
                                <?php } ?>        
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="12">
                                            <p class="table-showing pull-left"><strong><?php echo count($completed_paginator) ?></strong> <?php echo __('Tasks'); ?>

                                                <?php if ($completed_paginator->haveToPaginate()): ?>
                                                    - <strong><?php echo $completed_paginator->getPage() ?>/<?php echo $completed_paginator->getLastPage() ?></strong>
                                                <?php endif; ?></p>

                                            <?php if ($completed_paginator->haveToPaginate()): ?>
                                                <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                    <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/completepage/1#end">
                                                            <i class="fa fa-angle-left"></i>
                                                        </a></li>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/completepage/<?php echo $completed_paginator->getPreviousPage() ?>#end">
                                                            <i class="fa fa-angle-left"></i>
                                                        </a></li>

                                                    <?php foreach ($completed_paginator->getLinks() as $page): ?>
                                                        <?php if ($page == $completed_paginator->getPage()): ?>
                                                            <li class="active"><a href=""><?php echo $page ?></a>
                                                        <?php else: ?>
                                                            <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/completepage/<?php echo $page ?>#end"><?php echo $page ?></a></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/completepage/<?php echo $completed_paginator->getNextPage() ?>#end">
                                                            <i class="fa fa-angle-right"></i>
                                                        </a></li>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/completepage/<?php echo $completed_paginator->getLastPage() ?>#end">
                                                            <i class="fa fa-angle-right"></i>
                                                        </a></li>
                                                </ul>
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="tab3default">
                            <table class="table table-striped table-hover table-special m-t-20 m-b-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Service"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Submitted On"); ?></th>
                                    <th><?php echo __("Submitted By"); ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php 
                                    foreach($cancel_paginator->getResults() as $task){ 
                                        $application = $task->getApplication();
                                ?>
                                <tr>
                                    <td><?php echo $task->getId(); ?></td>
                                    <td style="word-wrap:break-word; width: 250px;">
                                        <?php echo $application->getTitle(); ?>
                                        <h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
                                        <p><?php echo date('d F Y H:m:s', strtotime($application->getDateOfSubmission())); ?></p>
                                    </td>
                                    <td>
                                    <?php
                                        echo $application->getStatusName();
                                    ?>
                                    </td>
                                    <td style="vertical-align:middle">
                                        <?php echo $application->getDateOfSubmission(); ?>
                                    </td>
                                    <td style="vertical-align:middle">
                                        <?php echo $application->getSfGuardUserProfile()->getFullname(); ?>
                                    </td>
                                    <td>
                                    <a class='btn btn-default btn-xs' title='<?php echo __('View Task'); ?>' href='<?php echo public_path("backend.php/tasks/view/id/".$task->getId()); ?>'><span class="fa fa-eye"></span></a>
                                    <?php 
                                    if($sf_user->mfHasCredential("has_hod_access"))
                                    {
                                    ?>
                                    <a title="Cancel Task" class="btn btn-danger btn-xs" href="<?php echo public_path("backend.php/tasks/cancel/id/".$task->getId()); ?>"><span class="fa fa-times"></span></a>  
                                    <?php 
                                    }
                                    ?>            
                                    </td>
                                </tr>
                                <?php } ?>        
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="12">
                                            <p class="table-showing pull-left"><strong><?php echo count($cancel_paginator) ?></strong> <?php echo __('Tasks'); ?>

                                                <?php if ($cancel_paginator->haveToPaginate()): ?>
                                                    - <strong><?php echo $cancel_paginator->getPage() ?>/<?php echo $cancel_paginator->getLastPage() ?></strong>
                                                <?php endif; ?></p>

                                            <?php if ($cancel_paginator->haveToPaginate()): ?>
                                                <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                    <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/cancelpage/1#end">
                                                            <i class="fa fa-angle-left"></i>
                                                        </a></li>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/cancelpage/<?php echo $cancel_paginator->getPreviousPage() ?>#end">
                                                            <i class="fa fa-angle-left"></i>
                                                        </a></li>

                                                    <?php foreach ($cancel_paginator->getLinks() as $page): ?>
                                                        <?php if ($page == $cancel_paginator->getPage()): ?>
                                                            <li class="active"><a href=""><?php echo $page ?></a>
                                                        <?php else: ?>
                                                            <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/cancelpage/<?php echo $page ?>#end"><?php echo $page ?></a></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/cancelpage/<?php echo $cancel_paginator->getNextPage() ?>#end">
                                                            <i class="fa fa-angle-right"></i>
                                                        </a></li>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/cancelpage/<?php echo $cancel_paginator->getLastPage() ?>#end">
                                                            <i class="fa fa-angle-right"></i>
                                                        </a></li>
                                                </ul>
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="tab4default">
                            <table class="table table-striped table-hover table-special m-t-20 m-b-0">
                                <thead>
                                <tr><th><?php echo __("Client IP"); ?></th>	<th><?php echo __("Activity"); ?></th>	<th><?php echo __("Date and time"); ?></th> <th><?php echo __("Device"); ?></th></tr>
                                </thead>
                                <tbody>
                                <?php 
                                    foreach($audit_paginator->getResults() as $audit){ 
                                ?>
                                    <tr>
                                    <td><?php echo $audit->getIpaddress(); ?></td>
                                    <td><?php echo html_entity_decode($audit->getAction()); ?></td>
                                    <td><?php echo $audit->getActionTimestamp(); ?></td>
                                    <td><?php echo $audit->getHttpAgent(); ?></td>
                                    </tr>
                                <?php 
                                    }
                                ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="12">
                                            <p class="table-showing pull-left"><strong><?php echo count($audit_paginator) ?></strong> <?php echo __('Audit logs'); ?>

                                                <?php if ($audit_paginator->haveToPaginate()): ?>
                                                    - <strong><?php echo $audit_paginator->getPage() ?>/<?php echo $audit_paginator->getLastPage() ?></strong>
                                                <?php endif; ?></p>

                                            <?php if ($audit_paginator->haveToPaginate()): ?>
                                                <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                    <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/auditpage/1#end">
                                                            <i class="fa fa-angle-left"></i>
                                                        </a></li>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/auditpage/<?php echo $audit_paginator->getPreviousPage() ?>#end">
                                                            <i class="fa fa-angle-left"></i>
                                                        </a></li>

                                                    <?php foreach ($audit_paginator->getLinks() as $page): ?>
                                                        <?php if ($page == $audit_paginator->getPage()): ?>
                                                            <li class="active"><a href=""><?php echo $page ?></a>
                                                        <?php else: ?>
                                                            <li><a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/auditpage/<?php echo $page ?>#end"><?php echo $page ?></a></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/auditpage/<?php echo $audit_paginator->getNextPage() ?>#end">
                                                            <i class="fa fa-angle-right"></i>
                                                        </a></li>

                                                    <li> <a href="/plan/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>/auditpage/<?php echo $audit_paginator->getLastPage() ?>#end">
                                                            <i class="fa fa-angle-right"></i>
                                                        </a></li>
                                                </ul>
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <a name="end"></a>
                    </div>
                </div>
            </div>













    </div>

</div>


<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="auditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?php echo __("User Details"); ?></h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-4">First Name</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="first_name" class="form-control" value="<?php echo $reviewer->getStrfirstname(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Last Name</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="last_name" class="form-control" value="<?php echo $reviewer->getStrlastname(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">ID Number</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="id_number" class="form-control" value="<?php echo $reviewer->getStruserid(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Email</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="email" class="form-control" value="<?php echo $reviewer->getStremail(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Mobile Number</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="phone_number" class="form-control" value="<?php echo $reviewer->getStrphoneMain1(); ?>">
            </div>
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


<!-- Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="auditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?php echo __("Filter Activity Log"); ?></h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-4">New Password</label>
            <div class="col-sm-8">
                <input type="password" id="from-multiple" name="new_password" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Confirm Password</label>
            <div class="col-sm-8">
                <input type="password" id="from-multiple" name="confirm_password" class="form-control">
            </div>
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

<!-- Modal -->
<div class="modal fade" id="otherModal" tabindex="-1" role="dialog" aria-labelledby="auditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?php echo __("Edit other details"); ?></h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-4">Country</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="country" class="form-control" value="<?php echo $reviewer->getStrcountry(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">City</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="city" class="form-control" value="<?php echo $reviewer->getStrcity(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Designation</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="designation" class="form-control" value="<?php echo $reviewer->getUserdefined1Value(); ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Man Number</label>
            <div class="col-sm-8">
                <input type="text" id="from-multiple" name="mannumber" class="form-control" value="<?php echo $reviewer->getUserdefined2Value(); ?>">
            </div>
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

<!-- Modal -->
<div class="modal fade" id="accessModal" tabindex="-1" role="dialog" aria-labelledby="auditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?php echo __("Filter Activity Log"); ?></h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-4">Department</label>
            <div class="col-sm-8">
                <select name="department" class="form-control">
                <?php 
                $q = Doctrine_Query::create()
                    ->from("Department a")
                    ->orderBy("a.department_name ASC");
                $departments = $q->execute();

                foreach($departments as $department)
                {
                    $selected = "";

                    if($department->getId() == $reviewer->getStrdepartment())
                    {
                        $selected = "selected='selected'";
                    }
                    echo "<option value='".$department->getId()."' ".$selected.">".$department->getDepartmentName()."</option>";
                }
                ?>
                </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4">Groups</label>
            <div class="col-sm-8">
                <select id="groups" name="groups[]" multiple class="form-control" required="required">
						<?php
						$q = Doctrine_Query::create()
							 ->from('MfGuardGroup a')
							 ->orderBy('a.name ASC');
						$groups = $q->execute();
						foreach($groups as $group)
						{
							$selected = "";
							$q = Doctrine_Query::create()
								 ->from('MfGuardUserGroup a')
								 ->where('a.user_id = ?',  $reviewer->getNid())
								 ->andWhere('a.group_id = ?', $group->getId());
							$usergroup = $q->execute();

							if(sizeof($usergroup) > 0)
							{
								$selected = "selected";
							}

							?>
							<option value='<?php echo $group->getId(); ?>' <?php echo $selected; ?>><?php echo $group->getName(); ?></option>
							<?php
						}
						?>
					</select>
					<script language="javascript">
					 jQuery(document).ready(function(){

					 	var demo1 = $('[id="groups"]').bootstrapDualListbox();

					 });
					 </script>
            </div>
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
