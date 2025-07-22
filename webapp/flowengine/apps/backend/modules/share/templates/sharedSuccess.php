<?php
/**
 * sharedSuccess.php template.
 *
 * Shows success message if application is shared successfully
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com), OTBAfrica
 */
use_helper('I18N');
$otbhelper = new OTBHelper() ;
?>

<div class="pageheader">
    <h2><i class="fa fa-home"></i><?php echo __('Sharing'); ?></h2>
    <div class="breadcrumb-wrapper">
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path('plan') ?>"><?php echo __('Home'); ?></a></li>
            <li class="active"><?php echo __('Shared applications'); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
   
        <div class="panel-body panel-body-nopadding">
            <?php if ($sf_user->getFlash('shared')) : ?>
                <div class="alert alert-success">
                    <?php echo __("Successfuly shared this application.") ?>
                </div>
            <?php endif; ?>
            <?php if ($sf_user->getFlash('share_error_exists')) : ?>
                <div class="alert alert-warning alert-dismissable">
                    <?php echo __("Sorry cannot share this application. This application is already shared with selected user. Please try a different user!") ?>
                    <a class="btn btn-primary" href="<?php echo public_path('plan/share/share/id/'.$id) ?>">
                        <?php echo __("Back") ?>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($sf_user->getFlash('uknown_share_error')) : ?>
                <div class="alert alert-danger">
                    <?php echo __("Oops! Uknown error occured while sharing this application. Please try again later") ?>
                    <a class="btn btn-primary" href="<?php echo public_path('plan/share/share/id/'.$id) ?>">
                        <?php echo __("Back") ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>


    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">

                <h4><?php echo __('List of applications shared') ?> </h4>

            </div> 
        </div> 
        <div class="panel-body-nopadding pt10">
            <div class="table-responsive">
                <table id="shared_list" class="table table-bordered mb0">
                    <thead>
                    <th><?php echo __('Application No') ?></th>
                    <th><?php echo __('Submitted by') ?></th>
                    <th><?php echo __('Shared with') ?></th>
                    <th><?php echo __('Shared by') ?></th>
                    <th><?php echo __('Date') ?></th>
                    <th><?php echo __('Status') ?></th>
                    <th><?php echo __('Actions') ?></th>
                    </thead>
                    <tbody>
                        <?php foreach($results as $res) :?>
                        <tr>
                            <td>
                                <?php 
                                  $app_details = $otbhelper->getApplicationDetails($res->getFormentryid()) ; 
                                  echo $app_details->getApplicationId() ;
                                ?>
                                
                            </td>
                            <td><?php 
                                $owner =  $otbhelper->getApplicationOwnerDetails($res->getSenderid()) ;
                                if($owner){
                                      echo $owner->getFullname() ;
                                }
                              
                                //echo $res->getSenderid();
                                ?></td>
                            <td>
                                <?php // we use the same function to get details of the user we shared with
                                $shared_with =  $otbhelper->getApplicationOwnerDetails($res->getReceiverid()) ;
                                if($shared_with){
                                    echo $shared_with->getFullname() ;
                                }
                                //echo $shared_with->getFullname() ;
                               // echo $res->getReceiverid();
                                ?>
                            </td>
                            <td>
                                <?php //Note we use the same function to get details of the user who shared 
                                $shared_by =  $otbhelper->getReviewerDetails($res->getSharedBy()) ;
                                   //for applications shared by frontend users
                                    if(count($shared_by) > 0){
                                        foreach($shared_by as $user1){
                                               echo  $user1->getStrfirstname()." ".$user1->getStrlastname() ;
                                              }
                                    } //for applications shared by backend users
                                    else {
                                         
                                            if($res->getSharedBy() != null){
                                                 $user2 = $otbhelper->getApplicationOwnerDetails($res->getSenderid()) ;

                                            }else {
                                                 $user2 = $otbhelper->getApplicationOwnerDetails($res->getSharedBy()) ; 
                                            }
                                        ///
                                            if($user2){
                                                 echo $user2->getFullname();
                                            }
                                       
                                        
                                    }
                                
                                
                               // echo $shared_by->getStrfirstname()." ".$shared_by->getStrlastname() ;
                                ?>
                            </td>
                            <td>
                                <?php echo $res->getCreatedAt(); ?>
                            </td>
                            <td>
                                <?php if($res->getStatus() == "active"):  ?>  
                                <label class="success"> <font color="green"> <?php echo __("Active") ?> </font> </label>
                                <?php endif;  ?>
                                <?php if($res->getStatus() == "inactive"):  ?>  
                                <label class="warning"> <font color="red"> <?php echo __("Cancelled") ?> </font> </label>
                                <?php endif;  ?>
                                
                            </td>
                            <td>
                                <?php if($res->getStatus() == "active"):  ?>  
                                <a class="btn btn-danger" href="<?php echo public_path('plan/share/cancel/id/'.$res->getId()) ?>">
                                    <?php echo __("Cancel") ?>
                                </a>
                                 <?php endif;  ?>
                                <?php if($res->getStatus() == "inactive"):  ?>  
                                <a class="btn btn-success" href="<?php echo public_path('plan/share/activate/id/'.$res->getId()) ?>">
                                    <?php echo __("Activate") ?>
                                </a>
                                 <?php endif;  ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
	</div>
    <div>
            <script type="text/javascript">
              jQuery('#shared_list').dataTable({
                                        "sPaginationType": "full_numbers",

                                        // Using aoColumnDefs
                                        "aoColumnDefs": [
                                          { "bSortable": false, "aTargets": [ 'no-sort' ] }
                                          ]
                                      }); 
            </script>
    </div>
