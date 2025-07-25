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
    <p>		
        <button onClick="window.location = '/plan/reports/report13export/fromdate/<?php echo $fromdate; ?>/todate/<?php echo $todate; ?>';">Export to excel</button>
            <table>
                    <thead>
                            <tr>
                            <th>#</th>
                            <th>Application</th>
                            <th>Date of Submission</th>
                            <th>Name of Project</th>
                            <th>Submitted By</th>
                            <th>TIN</th>
                            <th>Plot No</th>
                            <th>Registered Usage</th>
                            <th>Plot Size</th>
                            <th>Last name or Company name</th>
                            <th>Identity card number</th>
                            <th>Calculation or market value (Estimated Construction Cost)</th>
                            <th>Total m2 of all floors in the building</th>
                            <th>Year of building construction (or last renovation)</th>
                            <th>Market value residential fixed asset including plot, building and improvements</th>
                            <th>Market value commercial/industrial/quarrying purposes fixed asset including plot, building and improvements</th>
                            <th>Tax due</th>
                            <th>Total market value of fixed asset rounded up to full 1000 Rwf</th>
                            <th>Multiply the value in key 34 with tax rate</th>
                            <th style="background: none;">Actions</th>
                            </tr>
                    </thead>
                    <tbody>
			<br>
			<?php
                        $count = 0;
                        
                        $dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
                        mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
                        
                        $sql = "SELECT * FROM ap_form_60 WHERE date_created BETWEEN '".$fromdate."' AND '".$todate."'";
                        $results = mysql_query($sql, $dbconn);
                        
			while($row = mysql_fetch_assoc($results))
                        {
                            $q = Doctrine_Query::create()
                               ->from("FormEntry a")
                               ->where("a.form_id = ? AND a.entry_id = ?", array("60",$row['id']))
                               ->andWhere("a.approved <> 0 AND a.approved <> 860 AND a.parent_submission = 0");
                            $application = $q->fetchOne();
                        
                            if($application)
                            {
                                $count++;
                                
                                    $q = Doctrine_Query::create()
                                       ->from("SfGuardUserProfile a")
                                       ->where("a.user_id = ?", $application->getUserId());
                                    $architect = $q->fetchOne();
                                    
                                    ?>
                                    <tr id="row_<?php //echo $task->getId() ?>">
                                            <td><?php echo $count; ?></td>        
                                            <td><?php echo $application->getApplicationId(); ?></td>
                                            <td><?php echo $row['date_created']; ?></td>
                                            <td><?php echo $row['element_2']; ?></td>
                                            <td><?php echo $architect->getFullname(); ?></td>
                                            <td><?php echo $row['element_80']; ?></td>
                                            <td><?php echo $row['element_14']; ?></td>
                                            <td><?php echo $row['element_15']; ?></td>
                                            <td><?php echo $row['element_17']; ?></td>
                                            <td><?php echo $row['element_5_1']." ".$row['element_5_2']; ?></td>
                                            <td><?php echo $row['element_6']; ?></td>
                                            <td></td>
                                            <td><?php echo $row['element_22']; ?></td>
                                            <?php
                                                $one = "";
                                                $two = "";
                                                $three = "";
                                                $four = "";
                                                $five = "";
                                                $six = "";
                                                
                                                $q = Doctrine_Query::create()
                                                   ->from("FormEntryLinks a")
                                                   ->where("a.formentryid = ?", $application->getId());
                                                $links = $q->execute();
                                                foreach($links as $link)
                                                {
                                                    $sql = "SELECT * FROM ap_form_".$link->getFormId()." WHERE id = ".$link->getEntryId();
                                                    $results2 = mysql_query($sql, $dbconn);
                                                    while($row2 = mysql_fetch_assoc($results2))
                                                    {
                                                        $one = $row2['element_3'];
                                                        $two = $row2['element_2'];
                                                        $three = $row2['element_4'];
                                                        $four = $row2['element_10'];
                                                        $five = $row2['element_8'];
                                                        $six = $row2['element_9'];
                                                    }
                                                }
                                            ?>
                                            <td><?php echo $one; ?></td>
                                            <td><?php echo $two; ?></td>
                                            <td><?php echo $three; ?></td>
                                            <td><?php echo $four; ?></td>
                                            <td><?php echo $five; ?></td>
                                            <td><?php echo $six; ?></td>
                                            <td class="c">
                                                <a title='View Task' href='/plan/application/view/id/<?php echo $application->getId(); ?>'><img src='/asset_styles/images/icons/dark/create_write.png'></a>
                                            </td>
                                    </tr>
                                    <?php
                            }
			}
			?>
			</tbody>
			</table>
			
			</p>

	</div>

</div>