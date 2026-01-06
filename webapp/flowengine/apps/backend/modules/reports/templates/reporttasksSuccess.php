<?php
function GetDays($sStartDate, $sEndDate){  
    $aDays[] = $start_date;
	$start_date  = $sStartDate;
	$end_date = $sEndDate;
	$current_date = $start_date;
	while(strtotime($current_date) <= strtotime($end_date))
	{
		$aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
		$current_date = gmdate("Y-m-d", strtotime("+2 day", strtotime($current_date)));
	}

  
  return $aDays;  
} 

?> 
 <div class="g12" style="padding-left: 10px;">
     <form>
         <label>Reviewers Report 
             <div style="float: right;">
                <a href="/backend.php/reports/printreporttasks/reviewer/<?php echo $_POST['task_reviewer']; ?>/status/<?php echo $_POST['task_status']; ?>/startdate/<?php echo $fromdate; ?>/enddate/<?php echo $todate; ?>"><u>Export to Excel</u></a>
             </div>
         </label>
     </form>
    <p>
			
<?php 
$days = GetDays($fromdate, $todate); 

$tasks = null;


if($filterreviewer == "0")
{
    $q = Doctrine_Query::create()
        ->from('CfUser a')
        ->where('a.nid = ?', $sf_user->getAttribute('userid'));
    $logged_in_reviewer = $q->fetchOne();
    $department = $logged_in_reviewer->getStrdepartment();
    if($filterstatus == "0")
    {
         $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.strdepartment LIKE ?","%".$department."%")
            ->orderBy("a.strfirstname ASC");
         $reviewers = $q->execute();
         foreach($reviewers as $reviewer)
         {
             $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.owner_user_id = ?",$reviewer->getNid())
                ->andWhere("a.start_date BETWEEN ? AND ?", array($fromdate,$todate));
             $reviewertasks = $q->execute();
             foreach($reviewertasks as $reviewertask)
             {
                 $tasks[] = $reviewertask;
             }
         }
    }
    else
    {
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.strdepartment LIKE ?","%".$department."%")
            ->orderBy("a.strfirstname ASC");
         $reviewers = $q->execute();
         foreach($reviewers as $reviewer)
         {
             $q = Doctrine_Query::create()
                ->from("Task a")
                ->where("a.owner_user_id = ?",$reviewer->getNid())
                ->andWhere("a.status = ?", $filterstatus)
                ->andWhere("a.start_date BETWEEN ? AND ?", array($fromdate,$todate));
             $reviewertasks = $q->execute();
             foreach($reviewertasks as $reviewertask)
             {
                 $tasks[] = $reviewertask;
             }
         }
    }
}
else
{
    $q = Doctrine_Query::create()
        ->from('CfUser a')
        ->where('a.nid = ?', $filterreviewer);
    $reviewer = $q->fetchOne();
    if($filterstatus == "0")
    {
        $q = Doctrine_Query::create()
           ->from("Task a")
           ->where("a.owner_user_id = ?",$reviewer->getNid())
           ->andWhere("a.start_date BETWEEN ? AND ?", array($fromdate,$todate));
        $reviewertasks = $q->execute();
        foreach($reviewertasks as $reviewertask)
        {
            $tasks[] = $reviewertask;
        }
    }
    else
    {
        $q = Doctrine_Query::create()
           ->from("Task a")
           ->where("a.owner_user_id = ?",$reviewer->getNid())
           ->andWhere("a.status = ?", $filterstatus)
           ->andWhere("a.start_date BETWEEN ? AND ?", array($fromdate,$todate));
        $reviewertasks = $q->execute();
        foreach($reviewertasks as $reviewertask)
        {
            $tasks[] = $reviewertask;
        }
    }
}

echo "&nbsp; &nbsp;".sizeof($tasks)." entries found. <br>";
?>
			
			
            <table class="datatable">
                    <thead>
                            <tr>
                                <th>#</th>
                                <th>Reviewer</th>
                                <th>Application</th>
                                <th>Task</th>
                                <th>Started On</th>
                                <th>Completed On</th>
                                <th>Status</th>
                                <th style="background: none;">Actions</th>
                            </tr>
                    </thead>
                    <tbody>
			<br>
			<?php
                        $count = 0;
			foreach($tasks as $task)
            {
                            $count++;
				?>
				<tr id="row_<?php echo $task->getId() ?>">
					<td><?php echo $count; ?></td>	  
                                        <td>
                                            <?php 
                                            $q = Doctrine_Query::create()
                                               ->from("CfUser a")
                                               ->where("a.nid = ?", $task->getOwnerUserId());
                                            $reviewer = $q->fetchOne();
                                            echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $q = Doctrine_Query::create()
                                               ->from("FormEntry a")
                                               ->where("a.id = ?", $task->getApplicationId());
                                            $application = $q->fetchOne();
                                            echo "<a href='".public_path("plan/applications/view/id/".$application->getId())."'>".$application->getApplicationId()."</a>";
                                            ?>
                                        </td>
                                        <td>
                                        <?php
                                            if($task->getType() == "1")
                                            {
                                                    echo "Review";
                                            }
                                            if($task->getType() == "2")
                                            {
                                                    echo "Assessment";
                                            }
                                            if($task->getType() == "6")
                                            {
                                                    echo "Inspection";
                                            }
                                            if($task->getType() == "3")
                                            {
                                                    echo "Invoicing";
                                            }
                                            if($task->getType() == "4")
                                            {
                                                    echo "Scanning";
                                            }
                                            if($task->getType() == "5")
                                            {
                                                    echo "Collection";
                                            }
                                        ?>
                                        </td>
                                        <td>
                                        <?php
                                            echo $task->getStartDate();
                                        ?>
                                        </td>
                                        <td>
                                        <?php
                                            echo $task->getEndDate();
                                        ?>
                                        </td>
                                        <td>
                                        <?php
                                            if($task->getStatus() == "1")
                                            {
                                                    echo "Pending";
                                            }
                                            else if($task->getStatus() == "2")
                                            {
                                                    echo "Completing";
                                            }
                                            else if($task->getStatus() == "25")
                                            {
                                                    echo "Completed";
                                            }
                                            else if($task->getStatus() == "5")
                                            {
                                                    echo "Cancelling";
                                            }
                                            else if($task->getStatus() == "55")
                                            {
                                                    echo "Cancelled";
                                            }
                                            else if($task->getStatus() == "3")
                                            {
                                                    echo "PostPoned";
                                            }
                                            else if($task->getStatus() == "4")
                                            {
                                                    echo "Transferring";
                                            }
                                            else if($task->getStatus() == "45")
                                            {
                                                    echo "Transferred";
                                            }
                                        ?>
                                        </td>
                                        <td class="c">
                                        <a title='View Task' href='/backend.php/tasks/view/id/<?php echo $task->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a>
                                        </td>
                                </tr>
				<?php
				}
			?>
			</tbody>
			</table>
			
			</p>

	</div>

</div>