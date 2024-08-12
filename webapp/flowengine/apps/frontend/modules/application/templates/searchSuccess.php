<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use_helper("I18N");

function GetDaysSince($sStartDate, $sEndDate){
    $start_ts = strtotime($sStartDate);
    $end_ts = strtotime($sEndDate);
    $diff = $end_ts - $start_ts;
    return round($diff / 86400);
}
?>
<?php if($sf_user->isAuthenticated()) { ?>	
<!-- Page-Title -->
<div class="row">
    <div class="col-xs-6 col-sm-6">
        <h4 class="page-title"><?php echo __('Search Results') ?></h4>
    </div>
    
</div>
<!-- Page-Title -->

<div class="row">
    <div class="col-lg-12">
          <?php
                      // $days =  GetDaysSince($application->getDateOfSubmission(), date("Y-m-d H:i:s"));
                       ?>
        
        <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
					  <?php echo __('Results of Search by Application Number'); ?>
				  </a>
                </h4>
              </div>
            <div class="panel-body panel-body-nopadding">
                <div class="table table-responsive">
                    <?php if(count($results) > 0 ) { ?>
                    <table class="table dt-on-steroids mb0" id="table3">
                    <thead>
                            <tr>
                            <th width="215px;"><?php echo __('Type'); ?></th>
                            <th width="86px"><?php echo __('Application No'); ?></th>
                            <th width="135px"><?php echo __('Submitted On'); ?></th>
                            <th width="106px"><?php echo __('Bill'); ?></th>
                            <th width="86px"><?php echo __('Approval'); ?></th>
                            <th width="38px"><?php echo __('Actions'); ?></th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $app): ?>
                        <tr>
                           <?php 
                            //Dirty but works!!
                           $q = Doctrine_Query::create()
                                            ->from('ApForms a')
                                            ->where('a.form_id = ?', $app->getFormId());
                                   $form = $q->fetchOne(); ?>
                            <td>
                                <?php
                                if($form)
                                {
                                        echo $form->getFormName() ." ".$form->getFormDescription();
                                }
                                else
                                {
                                  echo __('Unknown');
                                }
                                ?></td>
                            <td> <a href="<?php echo public_path('/index.php//application/view/id/'.$app->getId()) ?>"><?php echo $app->getApplicationId(); ?></a></td>
                            <td><?php echo $app->getDateOfSubmission() ?></td>
                            <td><?php
          $q = Doctrine_Query::create()
             ->from("MfInvoice a")
             ->where("a.app_id = ?", $app->getId())
             ->andWhere("a.paid = 1 OR a.paid = 15");
          $unpaid_invoices = $q->count();

          if($unpaid_invoices > 0)
          {
            ?>
            <span class="label label-danger"><?php echo __('Not Paid') ?></span>
            <?php
          }
          else
          {
              $q = Doctrine_Query::create()
                  ->from("MfInvoice a")
                  ->where("a.app_id = ?", $app->getId());
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
          ?></td>
                            <td class="c" width="180px">
                                <span class="label label-primary">
                                    <?php
                                        $q = Doctrine_Query::create()
                                               ->from('SubMenus a')
                                               ->where('a.id = ?', $app->getApproved());
                                       $submenu = $q->fetchOne();

                                       if($submenu)
                                       {
                                               echo $submenu->getTitle();
                                       }
                                       else
                                       {
                                                echo __('Unknown');
                                       }
                               ?>
                                </span></td>
                                <td>
                                <a  title='<?php echo __('View Application'); ?>' href='<?php echo public_path('/index.php//application/view/id/'.$app->getId()) ?>' class="btn"><?php echo __('View') ?></a>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                    <?php } else { ?>
                       <div class="alert alert-warning"> 
                            <button type="button" class="close" data-dismiss="alert"> </button>
                            <b> <?php echo __("Sorry Found Zero matches. Please try again !!"); ?> </b>
                        </div>
                    <?php } ?>
                   
        </div>
            </div>
                
        </div>
        
    </div>
</div>
<?php }  else { ?>
   <?php 
     //redirect user to login first  
     echo __("Access Error.Please Login") ;
   ?>
<?php } ?>
