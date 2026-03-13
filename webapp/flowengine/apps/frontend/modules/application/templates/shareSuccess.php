<?php
/**
 * shareSuccess.php template.
 *
 * Allows the client to share an application with another client
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
require($prefix_folder.'includes/init.php');

require($prefix_folder.'../../../config/form_builder_config.php');
require($prefix_folder.'includes/db-core.php');
require($prefix_folder.'includes/helper-functions.php');
require($prefix_folder.'includes/check-session.php');

require($prefix_folder.'includes/entry-functions.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/users-functions.php');


$form_id  = $application->getFormId();
$entry_id = $application->getEntryId();


										
?>



   <div class="pageheader">
       <h2> <i class="fa fa-share-square-o"></i><?php echo __('Share') ?>  <?php if($application->getApproved() == "0")
					{
						echo __("DRAFT-").$application->getId();
					}
					else
					{
						echo $application->getApplicationId(); 
					} ?> (<?php
		 if($application->getApproved() == "0")
		 {
			echo "Draft";
		 }
		 else
		 {
			$q = Doctrine_Query::create()
				 ->from('SubMenus a')
				 ->where('a.id = ?', $application->getApproved());
			$submenu = $q->fetchOne();
			if($submenu)
			{
				echo $submenu->getTitle();
			}
		}
		?>) 
</h2>
      <div class="breadcrumb-wrapper">
        
        <ol class="breadcrumb">
          <li><a href="#"><?php echo __('Applications') ?></a></li>
          <li class="active"><?php
						$q = Doctrine_Query::create()
							 ->from('ApForms a')
							 ->where('a.form_id = ?', $application->getFormId());
						$form = $q->fetchOne();
						$formtype = $form->getFormName();
						echo $formtype;
						?></li>
          <li class="active"><?php
		if($application->getApproved() == "0")
		 {
		 }
		 else
		 {
			$q = Doctrine_Query::create()
				 ->from('SubMenus a')
				 ->where('a.id = ?', $application->getApproved());
			$submenu = $q->fetchOne();
		}
	
	
	$editable = false;
	if($submenu && $submenu->getAllowEdit() == "1")
	{
		$editable = true;
	}
?></li>

        </ol>
      </div>
    </div>
    










<div class="contentpanel">

    <div class="row">

       
            <div class="panel panel-default">
              <div class="panel-heading">
             <div class="panel-title">
       
             <h4><?php echo __('Share This Application With Other People') ?> </h4>

             </div> 
           </div> 
              <div class="panel-body-nopadding pt10">
				<?php if($sf_user->hasFlash('shared_error')): ?>
				<div class="alert alert-warning">
					<?php $shared_user=$sf_user->getFlash('shared_error'); ?>
					<p><?php echo $application->getApplicationId() ?> has already been shared with <?php echo $shared_user[0]['fullname'] ?>!</p>
					<p>If this is the user you wanted to share with, you can proceed to move the application for the user to access their required form(s). <a href="<?php echo url_for('/plan/application/sharemove?architect='.$shared_user[0]['user_id'].'&id='.$application->getId()) ?>" class="btn btn-primary">Move Application</a></p>
				</div>
				<?php endif; ?>
         <form class="form-horizontal form-bordered" action="<?php echo public_path('plan/application/share/id/'.$application->getId()); ?>" method="post">
            
            <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Enter the email of a registered user and click find') ?></label>
               <div class="col-sm-6">
               <input required name="filter" id="filter" alt="filter" class="form-control" type="text" size="20" value="<?php if($filter && $filter != ""){ echo $filter; }else{ ?> <?php echo __('Email of registered user') ?>...<?php } ?>"  onblur="if(this.value=='') this.value='Email of registered user...';" onfocus="if(this.value=='Email of registered user...') this.value='';" onClick="$(this).val(' ');" />
               </div>
            </div>
            
     
        </div><!-- panel-body-nopadding pt10 -->
              <div class="panel-footer">
                <button class="btn btn-primary"><?php echo __('Submit') ?></button>
              </div><!-- panel-footer -->
            </div><!-- panel-default -->
          </form>







     
<?php
$q = "";
if($filter && $filter != "")
{
	error_log('------filter-----'.$filter);
$q = Doctrine_Query::create()
         ->from("SfGuardUserProfile a")
         ->where("a.email LIKE ? OR a.fullname LIKE ?", array($filter."%",$filter."%"))
         ->orderBy("a.email ASC");
$pager = new sfDoctrinePager('SfGuardUserProfile', 10);
$pager->setQuery($q);
$pager->setPage($page);
$pager->init();
$counter = 1;
?>




 <div class="panel panel-default">
       
           <div class="panel-heading">
       
             <h4 class="panel-title"><?php echo __('Select a user with whom you want to share your application') ?></h4>

           </div> 

       <div class="panel-body-nopadding pt10">
       
       

<div class="table-responsive">
<table class="table table-bordered mb0">
  <thead>
  <th width="2%">#</th>
  <th><?php echo __('Name') ?></th>
  <th  width="2%"><?php echo __('Share') ?></th>
  </thead>
<tbody>
<?php
foreach($pager->getResults() as $architect)
{
  $user = Doctrine_Core::getTable("SfGuardUser")->find(array($architect->getUserId()));
  if($user)
  {
	  //if($user->getIsActive() == "1" && $user->getIsSuperAdmin() == "1")
	  {
  		echo "<tr><td>".$counter."</td><td>".$architect->getFullname()."</td>";
		?>
		<td align="center">
           <a title='<?php echo __('Share Application')?>' href='<?php echo public_path('plan/application/share/id/'.$application->getId().'/architect/'.$architect->getUserId()); ?>'>
           <span class="badge badge-primary"><i class="fa fa-share"></i></span>
           </a>
		</td>
    <?php
		echo "</tr>";
  		$counter++;
	  }
  }
}
}
else
{
//Nothing
}
?>
</tbody>
</table>
</div><!--Responsive-table-->
  
</div>
</div>  
  
  
  
  
   </div> 
    </div> 
  
  



