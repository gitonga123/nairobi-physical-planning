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
    if($task->getType() == 3)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $task->getOwnerUserId());
        $reviewer = $q->fetchOne();
        ?>
        <div class="panel panel-default">
            <div class="panel-heading panel-heading-noradius">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion_inner" href="/plan/tasks/view/id/<?php echo $task->getId(); ?>">
                <?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname(); ?> - <?php echo $reviewer->getDepartment(); ?><p>(<?php if($task->getEndDate()){ ?>Last updated on <?php echo $task->getEndDate(); }else{ ?>Created on <?php echo $task->getDateCreated(); }?>)</p></a></h4>

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
    else 
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $task->getOwnerUserId());
        $reviewer = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('TaskForms a')
            ->where('a.task_id = ?', $task->getId());
        $taskform = $q->fetchOne();
        ?>
        <?php if($reviewer): ?>
		 <div class="panel panel-default">
			<div class="panel-heading panel-heading-noradius">
	        	<h4 class="panel-title">
	            	<a href="/plan/tasks/view/id/<?php echo $task->getId(); ?>">
	            	<?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname(); ?> - <?php echo $reviewer->getDepartment(); ?><br>
                    <p>(<?php if($task->getEndDate()){ ?>Last updated on <?php echo $task->getEndDate(); }else{ ?>Created on <?php echo $task->getDateCreated(); }?>)</p></a></h4>
		            <div class="panel-body" style="background-color: #FFF;">
	                <?php
						//If this task is complete, show the comments
						if($task->getStatusName() == "Completed" && $taskform != null )
						{
							include_partial('tasks/application_comments_details', array('application' => $application, 'form_id' => $taskform->getFormId(), 'entry_id' =>  $taskform->getEntryId(), 'task' => $task));
						}
						else
						{
							echo $task->getStatusName();
						}
					?>
	            </div>
	        </div>
	     </div>
        <?php endif; ?>
        <?php
    }

}

?>
</div>