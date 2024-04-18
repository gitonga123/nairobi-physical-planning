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
  use_helper('I18N');
?>
<div class="panel panel-dark mb0">
          <div class="panel-heading">
              <?php /*?><div class="panel-btns">
                <select name="app_type"> 
                <option>Filter by Application Type</option></select>
              </div><!-- panel-btns --><?php */?>
              <h3 class="panel-title"><?php echo __('Shared Applications'); ?></h3>
              <p class="text-muted"><?php echo __('View and manage all shared applications'); ?>...</p>
            </div>
          <div class="panel-body panel-body-nopadding">  
                        <div class="table-responsive">
                            <table class="table mb0" id="table2">
	<thead>
		<tr>		 
        <th><?php echo __('Application') ?></th>
        <th><?php echo __('Submitted on'); ?></th>
        <th><?php echo __('Submitted by'); ?></th>
        <th><?php echo __('Shared with'); ?></th><!--OTB Patch Start - Shared with-->
        <th><?php echo __('Assessment') ?></th>
		<th width="80" align="center" class="no-sort"><?php echo __('actions'); ?></th>
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
				
				$query = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = '".$application->getEntryId()."'";
				$application_form=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
			?>
				<tr>
					
					<td>
						<a class="table-item-title" title='View Application' href='<?php echo public_path('index.php/sharedapplication/view/id/'.$application->getId()); ?>'>
						<?php echo $application->getApplicationId(); ?>
						</a>
                     </td>
                     <td>   
						<?php echo date('d F Y',strtotime($application_form[0]['date_created'])); ?>
                     </td>
		<!--OTB Patch Start - Old system feature-->
					 <td>
                                  <?php
                                                        $q = Doctrine_Query::create()
                                                           ->from("SfGuardUserProfile a")
                                                           ->where("a.user_id = ?", $application->getUserId());
                                                        $profile = $q->fetchOne();
                                                        echo $profile->getFullname()." Email:  (".$profile->getEmail().")";
                                                        ?>
					</td>
		<!--OTB Patch Start - Shared with-->
					 <td>
                                  <?php
                                                        $q = Doctrine_Query::create()
                                                           ->from("SfGuardUserProfile a")
                                                           ->where("a.user_id = ?", $sharedapplication->getReceiverid());
                                                        $profile = $q->fetchOne();
                                                        echo $profile->getFullname()." Email:  (".$profile->getEmail().")";
                                                        ?>
					</td>
		<!--OTB Patch End - Shared with-->
					<td class="aligned">
						<?php
							if(!is_null($sharedapplication->getStatus())):
							$q = Doctrine_Query::create()
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
							$completedtasks = $q->execute();
							
							
						?>
						    <div class="pull-left mr20 mb0">
                       <span class="badge badge-success" style="width:40px;">
                          <strong><?php echo round((sizeof($completedtasks)/sizeof($tasks))*100); ?>% </strong> 
                      </span>
                      </div> 
                      <?php else: ?>
						    <div class="pull-left mr20 mb0">
                       <span class="badge badge-warning" style="width:40px;">
                          <strong>0% </strong> 
                      </span>
                      </div> 
                      <?php endif; ?>
                   <!--div class="progress mb0">
                     <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo (sizeof($completedtasks)/sizeof($tasks))*100; ?>%;">
                     <span class="sr-only"><?php echo round((sizeof($completedtasks)/sizeof($tasks))*100); ?>%</span>
                    </div>
                   </div-->
					</td>
					<td align="center">
						<a  title='View Application' href='<?php echo public_path('index.php/sharedapplication/view/id/'.$application->getId()); ?>'><span class="badge badge-primary"><i class="fa fa-eye"></i></span></a>
<?php
			if($sf_user->getGuardUser()->getId() == $sharedapplication->getSenderid())
			{
			?>
			<a  title='UnShare Application' href='<?php echo public_path('index.php/sharedapplication/unshare/id/'.$application->getId()); ?>'><span class="badge badge-primary"><i class="fa fa-share"></i></span></a>
<?php
			}
			else
			{
			?>
			<a  title='UnShare Application' href='<?php echo public_path('index.php/sharedapplication/unshareme/id/'.$application->getId()); ?>'><span class="badge badge-primary"><i class="fa fa-share"></i></span></a>
<?php
			}
			
			?>
			</td>

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
                    <i class="bold-label"><?php echo __('No records found'); ?></i>
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
<script>
$(function(){
	$('#table2').DataTable({"order":[[1,'desc']]});
});
</script>