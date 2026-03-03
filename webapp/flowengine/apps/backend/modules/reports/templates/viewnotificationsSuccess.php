<?php
/**
 * viewnotifications template.
 *
 * Shows report of notifications sent for an application.
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
		header('Location: '.public_path().'plan/login/index');
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
						<li><a href="#tabs-7">Notifications</a></li>
						
					</ul>
					<div id="tabs-7">
					
					<table>
					<thead>
					<th>Notifications</th><th>Sent On</th><th>Receipt Confirmed?</th>
					</thead>
					<tbody>
					<?php
						$q = Doctrine_Query::create()
							 ->from('NotificationHistory a')
							 ->where('a.application_id = ?', $application->getId())
							 ->orderBy('a.id ASC');
						$refs = $q->execute();
						
						foreach($refs as $ref)
						{
							
							$q = Doctrine_Query::create()
								 ->from('SfGuardUserProfile a')
								 ->where('a.user_id = ?', $ref->getUserId());
							$receiver = $q->fetchOne();
							
							$receiver_name = "-";
							
							if($receiver)
							{
								$receiver_name = $receiver->getFullname();
							}
							
							if($ref->getConfirmedReceipt() == "1"){
								$receipt = "Yes";
							}else{
								$receipt = "No";
							}
							
							echo "<tr><td>".$ref->getNotification()."</td><td>".$ref->getSentOn()."</td><td>".$receipt."</td></tr>";
						}
						
					?>
					</tbody>
					</table>
					
					</div>
					
					
				</div>
</div>