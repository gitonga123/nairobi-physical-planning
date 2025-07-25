<?php
/**
 * viewinspection template.
 *
 * Inspection History Report for an application
 *
 * @package    backend
 * @subpackage reports
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

//Check if backend user is logged in, if so log them out
if($sf_user->isAuthenticated()){
    if($sf_user->getGuardUser()->getId() != '1')
    {
        $sf_user->signout();
		header('Location: '.public_path('plan/login/index'));
		exit;
    }
}
?>
	
<div class="g12">
			<form>

			<label><?php echo $application->getApplicationId(); ?> (<?php

				$q = Doctrine_Query::create()

					 ->from('SubMenus a')

					 ->where('a.id = ?', $application->getApproved());

				$submenu = $q->fetchOne();

				
                if($submenu)
				{
					echo $submenu->getTitle();
				}
				else
				{
					echo "None";
				}

				

			?>)  - <?php

									$q = Doctrine_Query::create()

											 ->from('EntryDecline a')

											 ->where('a.entry_id = ?', $application->getId());

									$declines = $q->execute();

									$submission = sizeof($declines);

									$submission++;

									if($submission == 1)

									{

										echo "First";

									}

									if($submission == 2)

									{

										echo "Second";

									}

									if($submission == 3)

									{

										echo "Third";

									}

									if($submission == 4)

									{

										echo "Fourth";

									}

									if($submission == 5)

									{

										echo "Fifth";

									}

									if($submission == 6)

									{

										echo "Sixth";

									}

									if($submission == 7)

									{

										echo "Seventh";

									}

									if($submission == 8)

									{

										echo "Eighth";

									}

									if($submission == 9)

									{

										echo "Ninth";

									}

									if($submission == 10)

									{

										echo "Tenth";

									}

									?> Submission
									
									<div style="float: right;">
									<a href="#" onClick="window.print();"><img src='/assets_backend/images/pdf.gif'></a>
									</div>
									
									</label>
									</form>
			<div class="tab">
					<ul>
						<li><a href="#tabs-7">Inspections</a></li>
					</ul>
					<div id="tabs-7">
						<table>
							<thead>
								<tr>
									<th>Task Type</th><th>Application</th><th>Task Sent On</th><th>Task Sent By</th><th>Task Status</th><th style="background:none;">Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
							
							        $q = Doctrine_Query::create()
											->from('Task a');
										$q->where('a.application_id = ?', $application->getId());
										$q->andWhere('a.type = ?', '6');
									$tasks = $q->execute();
							
									foreach($tasks as $task)
									{
										$q = Doctrine_Query::create()
										 ->from('FormEntry a')
										 ->where('a.id = ?', $task->getApplicationId());
										$tapplication = $q->fetchOne();
									
							?>
								<tr>
									<td style="background-color: <?php
									if($task->getType() == "1")
									{
										echo "#f0a8a8";
									}
									else if($task->getType() == "2")
									{
										echo "#a2e8a2";
									}
									else if($task->getType() == "6")
									{
										echo "#e8e8a2";
									}
									else if($task->getType() == "3")
									{
										echo "#a8f0f0";
									}
									else if($task->getType() == "4")
									{
										echo "#f0a8f0";
									}
									else if($task->getType() == "5")
									{
										echo "#aea8f0";
									}
									else
									{
										echo "#cfccf0";
									}
							?>"><?php 
									if($task->getType() == "1")
									{
										echo "Review";
									}
									if($task->getType() == "2")
									{
										echo "Commenting";
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
									?></td>
									<td><?php echo $tapplication->getApplicationId(); ?></td>
									<td><?php
										echo $task->getDateCreated();
									?></td>
									<td class="c">
									<?php
										$q = Doctrine_Query::create()
										 ->from('CfUser a')
										 ->where('a.nid = ?', $task->getCreatorUserId());
									$reviewer = $q->fetchOne();
									if($reviewer)
									{
										echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
									}
									else
									{
										echo "-";
									}
									?>
									</td>
									<td class="c">
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
										<a title='View Task' href='/plan/tasks/view/id/<?php echo $task->getId(); ?>'><img src='/assets_backend/images/icons/dark/create_write.png'></a>
										<?php
										/**
										if($task->getStatus() == "1"){
										?>
											<a title='Complete Task' href='/plan/tasks/complete/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/tick.png'> </a>
											<a title='Transfer Task' href='/plan/tasks/transfer/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/shuffle.png'> </a>
											<a title='Postpone Task' href='/plan/tasks/postpone/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/timer.png'> </a>
											<a title='Cancel Task' href='/plan/tasks/cancel/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/cross.png'> </a>
										<?php
										}
										else if($task->getStatus() == "2")
										{
										?>
											<a title='Confirm Completion' href='/plan/tasks/complete/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/tick.png'> </a>
											<a title='Return To Reviewer' href='/plan/tasks/return/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/arrow_left.png'> </a>
										<?php
										}
										else if($task->getStatus() == "25")
										{
										?>
											
										<?php
										}
										else if($task->getStatus() == "5")
										{
										?>
											<a title='Confirm Cancellation' href='/plan/tasks/cancel/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/tick.png'> </a>
											<a title='Return To Reviewer' href='/plan/tasks/return/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/arrow_left'> </a>
										<?php
										}
										else if($task->getStatus() == "55")
										{
										?>
										
										<?php
										}
										else if($task->getStatus() == "3")
										{
										?>
											<a title='Resume Task' href='/plan/tasks/return/id/<?php echo $task->getId(); ?>'><img src='/assets_backend/images/icons/dark/arrow_left.png'>  </a>
											<a title='Transfer Task' href='/plan/tasks/transfer/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/shuffle.png'> </a>
											<a title='Cancel Task' href='/plan/tasks/cancel/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/cross.png'> </a>
										<?php
										}
										else if($task->getStatus() == "4")
										{
										?>
											<a title='Confirm Transfer' href='/plan/tasks/transfer/id/<?php echo $task->getId(); ?>'><img src='/assets_backend/images/icons/dark/shuffle.png'> </a>
											<a title='Cancel Task' href='/plan/tasks/cancel/id/<?php echo $task->getId(); ?>'> <img src='/assets_backend/images/icons/dark/cross.png'></a>
										<?php
										}
										else if($task->getStatus() == "45")
										{
										?>
										
										<?php
										}
										***/
										?>
										</td>
								</tr>
							<?php
									}
							?>
							</tbody>
						</table>
					</div>
					
				</div>
</div>