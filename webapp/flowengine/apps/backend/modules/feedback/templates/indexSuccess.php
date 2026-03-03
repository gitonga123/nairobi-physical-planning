<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of all messages related to currently logged in reviewer
 *
 * @package    backend
 * @subpackage messages
 * @author     OTB Africa 
 * error_reporting(1);
 */
?>
<?php use_helper('I18N', 'Date') ?>
<?php
    if($sf_user->mfHasCredential("accessfeedback"))
{
  $_SESSION['current_module'] = "feedback";
  $_SESSION['current_action'] = "index";
  $_SESSION['current_id'] = "";
?>
    <div class="pageheader">
      <h2><i class="fa fa-envelope"></i> <?php echo __('FeedBack Messages') ?> <span><?php echo __('Message List') ?></span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __('You are here') ?>:</span>
        <ol class="breadcrumb">
          <li><a href="<?php echo public_path('plan'); ?>"><?php echo __('Home') ?></a></li>
          <li class="active"><?php echo __('Feedback Messages') ?></li>
        </ol>
      </div>
    </div>
    
    <div class="contentpanel panel-email">

        <div class="row">
            <div class="col-sm-3 col-lg-2">
                <ul class="nav nav-pills nav-stacked nav-email">
                    <li class="active">
                    <a href="<?php echo public_path('plan/feedback/index'); ?>">
                    	
                        <span class="badge pull-right"><?php echo sizeof($query_res); ?></span>
                        <i class="glyphicon glyphicon-inbox"></i> <?php echo __('Feedback Inbox') ?>
                    </a>
                    </li>
                    
                </ul>
                
                <div class="mb30"></div>
            </div><!-- col-sm-3 -->
            
            <div class="col-sm-9 col-lg-10">
                
                <div class="panel panel-default">
                    <div class="panel-body panel-body-nopadding">
                                                <h5 class="subtitle mb5 mt10 ml10"><?php echo __('Inbox') ?></h5>
                        <p class="text-muted ml10"><?php echo __('Showing') ?> <?php echo sizeof($query_res); ?> <?php echo __('messages') ?></p>
                        
                        <div class="table-responsive">
                            <table class="table table-email" id="table3">
                              <tbody>
                              <?php foreach ($query_res as $message): ?>
                                <tr>
                                  <td>
                                    <div class="ckbox ckbox-success">
                                        <input type="checkbox" id="checkbox1">
                                        <label for="checkbox1"></label>
                                    </div>
                                  </td>
                                  <td>
                                      <?php if($message['status'] == 1) //new messages
                                                  { ?>
                                      <a href="<?php echo public_path('plan/feedback/view/id/'. $message['id']); ?>" class="star"><i class="glyphicon glyphicon-star"></i><b style="color: green"> <?php echo __('New') ?> </b></a>
                                     <?php } else {?>
                                    <a href="<?php echo public_path('plan/feedback/view/id/'. $message['id']); ?>" class="star"> <i class="glyphicon glyphicon-star-empty"></i> </a>
                                     <?php
                                                  } ?>
                                  </td> 
                                  <td>
                                    <div class="media">
                                        <div class="media-body">
                                            <span class="media-meta pull-right"><?php
                                             $datetime = new DateTime($message['date_created']) ;
                                             echo $datetime->format('l jS \of F Y h:i:s A');
                                             ?></span>
                                           <a class href="<?php echo public_path('plan/feedback/view/id/'. $message['id']); ?>"> <h4 class="text-primary"> <?php echo $message['element_1_1']." ".$message['element_1_2'] ."(".$message['element_2'].")"?> </h4></a>
                                            <small class="text-muted"></small>
                                            <p class="email-summary"> 
                                                <?php $words = explode(" ",$message['element_3']);  ?>
                                                
                                                <?php if($message['status'] == 1) //new messages
                                                  { ?>
                                                
                                                     <?php echo implode(" ",array_splice($words,0,5))."...."; ?>
                                                  
                                                  <?php } else {?>
                                                
                                                     <?php echo implode(" ",array_splice($words,0,5))."...."; ?>
                                                
                                            <?php
                                                  }
                                            
                                          
					  
                                            ?> 
                                                
                                                <a class href="<?php echo public_path('plan/feedback/view/id/'. $message['id']); ?>"><i class="md md-add-alarm"></i>
                                                <span> <?php echo __('Read More'); ?></span></a>
                                            </p>
                                        </div>
                                    </div>
                                  </td>
                                </tr>
                                <?php
                                endforeach;
                                ?>
								</tbody>
                            </table>
                        </div><!-- table-responsive -->
                        
                    </div><!-- panel-body -->
                </div><!-- panel -->
                
            </div><!-- col-sm-9 -->
            
        </div><!-- row -->
    
    </div>
    
  </div><!-- mainpanel -->
<?php } else {
    
     include_partial("settings/accessdenied");
}
?>

