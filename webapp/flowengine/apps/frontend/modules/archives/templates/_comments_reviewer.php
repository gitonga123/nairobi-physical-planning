<?php
/**
 * _comments_reviewer template.
 *
 * Shows comments from each reviewer
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper("I18N");
?>
<div class="panel-group panel-group mb0" id="accordion_inner">
<?php

$taskitems = null;

$q = Doctrine_Query::create()
    ->from("Task a")
    ->where("a.application_id = ?", $application->getId());
$tasks = $q->execute();
foreach($tasks as $task)
{

    $q = Doctrine_Query::create()
       ->from("TaskForms a")
       ->where("a.task_id = ?", $task->getId());
    $taskforms = $q->execute();

    if(sizeof($taskforms) <= 0)
    {

        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $task->getOwnerUserId());
        $reviewer = $q->fetchOne();
        ?>
        <div class="panel panel-default">
        <div class="panel-heading panel-heading-noradius">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion_inner" href="#comments<?php echo $task->getId(); ?>">
                <?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname(); ?> - <?php echo $reviewer->getStrdepartment(); ?><p>(<?php echo __("Last updated on"); ?> <?php echo $task->getEndDate(); ?>)</p></a></h4>
                
              </div>
              <div id="comments<?php echo $task->getId(); ?>" class="panel-collapse collapse in">
                <div class="panel-body">
                    <?php echo $task->getStatusName(); ?>
                    <?php
                        if($task->getStatusName() == "Completed" && $task->getTypeName() == "Invoicing")
                        {
                            echo __("Check billing tab for new invoices");
                        }
                    ?>
                </div>
              </div>
        </div>
        <?php
    }

    foreach($taskforms as $taskform)
    {
        $taskitems[] = $task->getId();

        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $task->getOwnerUserId());
        $reviewer = $q->fetchOne();
        ?>
	 <div class="panel panel-default">
		<div class="panel-heading panel-heading-noradius">
        	<h4 class="panel-title">
            	<a data-toggle="collapse" data-parent="#accordion_inner" href="#comments<?php echo $task->getId().$taskform->getEntryId(); ?>">
            	<?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname(); ?> - <?php echo $reviewer->getStrdepartment(); ?><p>(Last updated on <?php echo $task->getEndDate(); ?>)</p></a></h4>
                
              </div>
              </div>
            </div>
        <?php
    }

}

//get form id and entry id
$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();

//Iterate through each department, check for assigned reviewers
$q = Doctrine_Query::create()
		->from('Department a');
$departments = $q->execute();
$count_deps = 0;
foreach($departments as $department)
{
	//Iterate through each reviewer, check if they have tasks assigned to this application
	$q = Doctrine_Query::create()
	   ->from('CfUser a')
	   ->where('a.strdepartment = ?', $department->getDepartmentName());
	$reviewers = $q->execute();
	$count_tasks = 0;
	foreach($reviewers as $reviewer)
	{
		$q = Doctrine_Query::create()
		   ->from('Task a')
		   ->where('a.owner_user_id = ?', $reviewer->getNid())
		   ->andWhere('a.application_id = ?', $application->getId());
		$count_tasks = $count_tasks + sizeof($q->execute());
	}

	if($count_tasks <= 0)	//If this department doesn't have tasks, don't display it.
	{
		continue;
	}
	
	$count_deps++;

	$q = Doctrine_Query::create()
		->from('CfUser a')
		->where('a.strdepartment = ?', $department->getDepartmentName());
	$reviewers = $q->execute();
	foreach($reviewers as $reviewer)
	{
		$q = Doctrine_Query::create()
		   ->from('Task a')
		   ->where('a.owner_user_id = ?', $reviewer->getNid())
		   ->andWhere('a.application_id = ?', $application->getId());
		$tasks = $q->execute();
		foreach($tasks as $task)
		{
            //if(($task->getStatusName() != "Pending" || $task->getStatusName() != "Completed") && in_array($task->getId(),$taskitems))
			if(($task->getStatusName() != "Pending" || $task->getStatusName() != "Completed"))
            {
                break;
            }

			if($task->getIsLeader() == "1")
			{
				$reviewertype = "Lead Reviewer";
				$style = "color: #4CD014;";
			}
			else
			{
				$reviewertype = "Support Reviewer";
				$style = "color: #F43535;";
			}
			
			$tasklink = "/plan/tasks/view/id/".$task->getId();
			?>
             <div class="panel panel-default">
		<div class="panel-heading panel-heading-noradius">
        	<h4 class="panel-title">
            	<a data-toggle="collapse" data-parent="#accordion_inner" href="#comments<?php echo $task->getId().$taskform->getEntryId(); ?>">
            	<?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname(); ?> <i>(Last updated on <?php echo $task->getEndDate(); ?>)</i>
                </a>
                </h4>
              </div>
              <div id="comments<?php echo $task->getId().$taskform->getEntryId(); ?>" class="panel-collapse collapse">
                <div class="panel-body">
                        <?php
				//If this task is complete, show the comments
				if($task->getStatusName() == "Completed")
				{
					include_partial('comments_reviewer_comments', array('application' => $application, 'form_id' => $form_id, 'entry_id' =>  $entry_id, 'task' => $task));
				}
				else if($task->getStatusName() == "Pending" && $task->getCreatorUserId() == $sf_user->getGuardUser()->getId())
				{
					include_partial('comments_reviewer_comments', array('application' => $application, 'form_id' => $form_id, 'entry_id' =>  $entry_id, 'task' => $task));
				}
				else
				{
					echo $task->getStatusName();
				}
			?>
            </div>
              </div>
             </div>
			<?php
	
		}
	
	}
}
?>
</div>
<?php
//If no reviewers have been assigned to work on this application
if($count_deps == 0)
{
	?>
           <table class="table mb0">
			<tbody>
			<tr>
			<td><i class="bold-label"><?php echo __("No records found"); ?></i></td>
			</tr>
			</tbody>
			</table>
   
	<?php
}
?>
