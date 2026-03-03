<!-- Tasks Table -->	
                <table class="list-table">
                    <thead>
                        <tr>
                            <th width="20">#</th>
                            <th>Application Stage</th>
                            <th>PRN</th>
                            <th>Date of Application</th>
                            <th>Submitted By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$count = 0;
						if ($pager->haveToPaginate())
						{
							$count  = ($pager->getPage() - 1) * 10;
						}
						foreach($pager->getResults() as $application)
						{
							$count++;
						$status = '';
						$color = '';
						
					$q = Doctrine_Query::create()
					   ->from('SubMenus a')
					   ->where('a.id = ?', $application->getApproved());
					$submenu = $q->fetchOne();
									$status = $submenu->getTitle();
									$color = "grey";
								
							$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
							mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
							if(isset($datefrom) && isset($dateto))
							{
								$query = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ".$application->getEntryId()." AND date_created BETWEEN '".$datefrom."' AND '".$dateto."'";
							}
							else
							{
								$query = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ".$application->getEntryId();
							}
							$results = mysql_query($query,$dbconn);
							if(mysql_num_rows($results) <= 0)
							{
								continue;
							}
							$ftcrow = mysql_fetch_assoc($results);
						?>
                        <tr style="cursor: move;" id="row-<?php echo $application->getId() ?>">
                            <td><?php echo $count; ?></td>
                            <td><span class="button submit medium <?php echo $color; ?>-back fixed-width-100 ui-corner-all" style="padding: 10px; width: 200px;"><?php echo $status; ?></span></td>
                            <td><?php if($application->getApplicationId() == "0"){echo "--";}else{echo $application->getApplicationId();} ?></td>
                            <td><?php 
							
							
							echo $ftcrow['date_created'];
							
							?>
                            </td>
                            <td>
                            <?php
							$q = Doctrine_Query::create()
							   ->from('sfGuardUser b')
							   ->where('b.id = ?', $application->getUserId());
							$theuser = $q->fetchOne();
							?>
                            <a href='/plan/frusers/show?id=<?php echo $theuser->getId(); ?>'><?php echo $theuser->getProfile()->getFullname(); ?></a>
                            </td>
                            <td><a href="/plan/forms/viewentry?form_id=<?php echo $application->getFormId(); ?>&id=<?php echo $application->getEntryId(); ?>" class="view-record">View</a></td>
                        </tr>
                        <?php
						}
						?>
                    </tbody>
                </table>