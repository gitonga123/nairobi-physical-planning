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
  <h2><i class="fa fa-user"></i> <?php echo __('Profile'); ?><span><?php echo __('View reviewer details'); ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('My Account'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
	<div class="row">

     <div class="col-sm-12">

     <?php
     if($sf_user->hasFlash("notice"))
     {
        ?>
         <div class="alert alert-success">
             <button type="button" class="close" aria-hidden="true">&times;</button>
             <strong><?php echo __('Success'); ?>! </strong><?php echo $sf_user->getFlash("notice"); ?>
         </div>
        <?php
     }

     if($sf_user->hasFlash("error"))
     {
         ?>
         <div class="alert alert-danger">
             <button type="button" class="close" aria-hidden="true">&times;</button>
             <strong><?php echo __('Error'); ?>! </strong><?php echo $sf_user->getFlash("error"); ?>
         </div>
        <?php
     }
     ?>

	     <div class="user-profile">
	     <div class="panel panel-default">
	     <div class="panel-heading">
	     <h3 class="panel-title">
	     <?php echo $reviewer->getStrfirstname(); ?> <?php echo $reviewer->getStrlastname(); ?>
	     </h3>
	     </div>
	     <div class="panel-body padding-0">

			 	<table class="table table-striped table-hover table-special">
					<thead>
					<tr>
							<th ><?php echo __("#"); ?></th>
							<th ><?php echo __("Service"); ?></th>
							<th ><?php echo __("Application No"); ?></th>
							<th ><?php echo __("Completed On"); ?></th>
							<th ><?php echo __("Status"); ?></th>
							<th ></th>
					</tr>
					</thead>
					<tbody>
						<?php 
						$q = Doctrine_Query::create()
								->from("Task a")
								->where("a.owner_user_id = ?", $reviewer->getNid())
								->andWhere("a.status = 25")
								->andWhere("a.end_date LIKE ?", "%".$filter_date."%");
						$tasks = $q->execute();
						?>
						<?php foreach ($tasks as $task): ?>
						<tr>
								<td><i class="fa fa-tasks tooltips" data-toggle="tooltip" title="Bug"></i></td>
                <td><a href="/plan/tasks/view/id/<?php echo $task->getId(); ?>"><?php echo $task->getApplication()->getForm()->getFormName(); ?></a></td>
                <td><a href="/plan/tasks/view/id/<?php echo $task->getId(); ?>"><?php echo $task->getApplication()->getApplicationId(); ?></a></td>
                <td><?php echo $task->getEndDate(); ?></td>
				        <td><?php echo $task->getStatusName(); ?></td>
                <td>
                    <a  title='<?php echo __('View Task'); ?>' href='<?php echo public_path("plan/tasks/view/id/".$task->getId()); ?>'> <span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                </td>
						</tr>
						<?php endforeach; ?>
					</tbody>
					</table>
	         
	     </div><!-- end-panel-body -->
	     </div> <!-- end-panel -->
	       </div> <!-- end-user-profile -->
	     </div><!-- col-sm-12 -->
    </div>
</div>
