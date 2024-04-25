<?php
/**
 * _list.php partial.
 *
 * Displays list of applications
 *
 * @package    frontend
 * @subpackage sharedapplication
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N') ;
?>
<div class="col-lg-10">

    <div class="card-box"> 
    
        <h4 class="text-dark  header-title m-t-0"><?php echo __('Recent Shared Applications') ?></h4>
        <p class="text-muted m-b-25 font-13">
           <?php echo __('Below are the applications shared with you recently') ?>
        </p>
         <div class="table-responsive">
                             <table class="table">
	<thead>
		<tr>		 
        <th><?php echo sfConfig::get('app_'.$_SESSION['locale'].'_plan_no'); ?></th>
        <th> <?php echo __('Date Received') ?></th>
        <th><?php echo __('Submitted by') ?></th>
        <!--<th><?php //echo sfConfig::get('app_'.$_SESSION['locale'].'_statistics'); ?></th> -->
        <th> <?php echo __('Bill') ?> </th>
        <th> <?php echo __('Approval Stage') ?></th>
        
        <th width="80" align="center" class="no-sort"><?php echo __('Actions') ?></th>
		</tr>
	</thead>
	<tbody>
			<?php
			foreach($sharedapplications as $sharedapplication)
			{
				$q = Doctrine_Query::create()
					 ->from('FormEntry a')
					 ->where('a.id = ?', $sharedapplication->getFormentryid());
				$application = $q->fetchOne();
				
				if(!empty($filter))
				{
					if($filter != $application->getApproved())
					{
						continue;
					}
				}
				
				$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
				mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
				$query = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = '".$application->getEntryId()."'";
				$result = mysql_query($query,$dbconn);

				$application_form = mysql_fetch_assoc($result);
			?>
				<tr>
					
					<td>
						<a class="table-item-title" title='View Application' href='<?php echo public_path(); ?>index.php/sharedapplication/view/id/<?php echo $application->getId(); ?>'>
						<?php echo $application->getApplicationId(); ?>
						</a>
                     </td>
                     <td>   
						<?php echo $application_form['date_created']; ?>
                     </td>   
					 <td>
                                  <?php
                                                        $q = Doctrine_Query::create()
                                                           ->from("SfGuardUserProfile a")
                                                           ->where("a.user_id = ?", $application->getUserId());
                                                        $profile = $q->fetchOne();
                                                        echo $profile->getFullname()." Email:  (".$profile->getEmail().")";
                                                        ?>
					</td>
					<!--<td class="aligned">
						<?php
							/*$q = Doctrine_Query::create()
							->from("Task a")
							->where("a.application_id = ?", $application->getId())
							->andWhere("a.type <> ?", 3);
							$tasks = $q->execute();
							
							$q = Doctrine_Query::create()
							->from("Task a")
							->where("a.application_id = ?", $application->getId())
							->andWhere("a.status <> ?", 1)
							->andWhere("a.status <> ?", 2)
							->andWhere("a.status <> ?", 3)
							->andWhere("a.status <> ?", 4)
							->andWhere("a.status <> ?", 5)
							->andWhere("a.type <> ?", 3);
							$completedtasks = $q->execute(); */
							
							
						?>
						    <div class="pull-left mr20 mb0">
                       <span class="badge badge-success" style="width:40px;">
                          <strong><?php //echo round((sizeof($completedtasks)/sizeof($tasks))*100); ?>% </strong> 
                      </span>
                      </div> 
                      
                      
                   <div class="progress mb0">
                     <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo (sizeof($completedtasks)/sizeof($tasks))*100; ?>%;">
                     <span class="sr-only"><?php //echo round((sizeof($completedtasks)/sizeof($tasks))*100); ?>%</span>
                    </div>
                   </div>
					</td>
                                        
                                        -->
                                        
                                             <td>
                        <?php
                        //Check billing status of the application
                        $q = Doctrine_Query::create()
                            ->from("MfInvoice a")
                            ->where("a.app_id = ?", $application->getId())
                            ->andWhere("a.paid = 1 OR a.paid = 15");
                        $unpaid_invoices = $q->count();
                        if($unpaid_invoices > 0)
                        {
                            ?>
                            <span class="label label-danger"><?php echo __("Not Paid"); ?></span>
                            <?php
                        }
                        else
                        {
                            $q = Doctrine_Query::create()
                                ->from("MfInvoice a")
                                ->where("a.app_id = ?", $application->getId());
                            $invoices = $q->count();
                            if($invoices == 0)
                            {
                                ?>
                                <span class="label label-default"><?php echo __("No Bill"); ?></span>
                                <?php
                            }
                            else
                            {
                                ?>
                                <span class="label label-success"><?php echo __("Paid"); ?></span>
                                <?php
                            }
                        }
                        ?>
                    </td>
                                       
                                        <td>
                                            <span class="label label-primary">
                                                <?php echo $application->getStatusName(); ?>
                                            </span>
                                        </td>
					<td align="center">
						<a  title='View Application' href='<?php echo public_path(); ?>index.php/sharedapplication/view/id/<?php echo $application->getId(); ?>'><span class="badge badge-primary"><i class="fa fa-eye"></i></span></a>
<?php
			if($sf_user->getGuardUser()->getId() == $sharedapplication->getSharedBy())
			{
			?>
			<a  title='UnShare Application' href='<?php echo public_path(); ?>index.php/sharedapplication/unshare/id/<?php echo $application->getId(); ?>'><span class="badge badge-primary"><i class="fa fa-share"></i></span></a>
			</td>
<?php
			}
			else
			{
			?>
<td  class="aligned">
			<!-- OTB patch - Allow only user who shared this application <a  title='UnShare Application' href='<?php //echo public_path(); ?>index.php/sharedapplication/unshareme/id/<?php //echo $application->getId(); ?>'><span class="badge badge-primary"><i class="fa fa-share"></i></span></a>-->
			</td>
<?php
			}
			?>
		</tr>
	<?php
			}
			if(sizeof($sharedapplications) == 0)
			{
				?>
				     <table class="table mb0">
                    <tbody>
                    <tr>
                    <td>
                    <i class="bold-label">No records found</i>
                    </td>
                    </tr>
                    </tbody>
                    </table>
				<?php
			}
	?>
	</tbody>
</table>
</div><!--Responsive-table-->
    
    
    </div> 



</div>
