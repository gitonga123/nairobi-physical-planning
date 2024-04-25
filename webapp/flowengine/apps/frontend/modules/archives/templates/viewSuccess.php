<?php
/**
 * viewSuccess.php template.
 *
 * Displays full application details
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

//Only the owner of the application should have access to the application
if($sf_user->getGuardUser()->getId() == $application->getUserId())
{
    $form_id  = $application->getFormId();
    $entry_id = $application->getEntryId();
?>

<div class="pageheader">
       <h2><i class="fa fa-edit"></i><?php echo __('My Applications'); ?></h2>
       <div class="breadcrumb-wrapper">
            <ol class="breadcrumb">

               <li><a href="#"><?php echo __('Applications'); ?></a></li>
               <li class="active"><?php
                    $q = Doctrine_Query::create()
                         ->from('ApForms a')
                         ->where('a.form_id = ?', $application->getFormId());
                    $form = $q->fetchOne();

                    $sql = "SELECT * FROM ext_translations WHERE field_id = '".$application->getFormId()."' AND field_name = 'form_name' AND table_class = 'ap_forms' AND locale = '".$sf_user->getCulture()."'";

                    $rows = mysql_query($sql, $dbconn);
                    if($row = mysql_fetch_assoc($rows))
                    {
                        echo $row['trl_content'];
                    }
                    else
                    {
                      if($form)
                      {
                        echo $form->getFormName();
                      }
                    }
                    ?>
                </li>
            </ol>
     </div>
</div>



<div class="contentpanel">

<div class="col-sm-12">
   <div class="pull-right mb10">
    <?php
        $action_count = 0;

        //Show links to generated permits
        $q = Doctrine_Query::create()
           ->from("SavedPermitArchive a")
           ->where("a.application_id = ?", $application->getId())
           ->andWhere("a.permit_status <> 3");
        $permits = $q->execute();

        foreach($permits as $permit)
        {
            if($application->getApproved() == "0")
            {
                $permit->delete();
            }

          $q = Doctrine_Query::create()
              ->from('Permits a')
              ->where('a.id = ?', $permit->getTypeId());
          $permittype = $q->fetchOne();
          if($permittype->getPartType() != 3)
          {
              ?>
              <a class="btn btn-primary" onClick="window.location = '/index.php/archivedpermits/view/id/<?php echo $permit->getId(); ?>';"><?php echo __('View'); ?> <?php echo $permittype->getTitle()." (".$permit->getDateOfIssue().")"; ?></a>
              <?php
                  $action_count++;
           }
        }


        if($application->getApproved() != "0")
        {
            $q = Doctrine_Query::create()
                 ->from('SubMenus a')
                 ->where('a.id = ?', $application->getApproved());
            $submenu = $q->fetchOne();
        }

?>
         </div>
         <hr class="mb10 nt0">
    </div>

    <div class="col-sm-3">
        <div class="blog-item pt20 pb5">
          <?php
              $q = Doctrine_Query::create()
                   ->from('SfGuardUser a')
                   ->where('a.id = ?', $application->getUserId());
              $user = $q->fetchOne();

              if(sfConfig::get('app_sso_secret'))
              {
                $account_type = "";

                if($user->getProfile()->getRegisteras() == 1)
                {
                  $account_type = "citizen";
                }
                elseif($user->getProfile()->getRegisteras() == 3)
                {
                  $account_type = "alien";
                }
                elseif($user->getProfile()->getRegisteras() == 4)
                {
                  $account_type = "visitor";
                }

                ?>
                <div class="form-group" align="center">
                  <img src="https://account.ecitizen.go.ke/profile-picture/<?php echo $user->getUsername(); ?>?t=<?php echo $account_type; ?>" class="thumbnail img-responsive mb20" alt="" />
                </div>
                <?php
              }

            //Check time for loading of library scripts
            $summary_timer_start = microtime(TRUE);
            ?>
            <h5 class="subtitle mt20 ml20"><?php echo __('Application Summary'); ?></h5>

              <ul class="profile-social-list">
                <li><a href=""><?php
                        $q = Doctrine_Query::create()
                            ->from("SubMenus a")
                            ->where("a.id = ?", $application->getApproved());
                        $stage = $q->fetchOne();
                        if($stage)
                        {
                            $q = Doctrine_Query::create()
                                ->from("Menus a")
                                ->where("a.id = ?", $stage->getMenuId());
                            $parentstage = $q->fetchOne();
                    ?><?php echo $parentstage->getTitle(); ?></a></li><?php
                        }

                        $q = Doctrine_Query::create()
                            ->from("SfGuardUserProfile a")
                            ->where("a.user_id = ?", $application->getUserId());
                        $architect = $q->fetchOne();
                    ?>
                <li><?php echo __('Submitted by'); ?> <a href=""> <?php echo $architect->getFullname(); ?></a></li>
                <li><?php echo __('Date of Submission'); ?> <a><?php echo str_replace(" ", " @ ", $application->getDateOfSubmission()); ?></a></li>
                <?php
                if($application->getDateOfResponse())
                {
                ?>
                <li><?php echo __('Date of Approval'); ?> <a><?php echo str_replace(" ", " @ ", $application->getDateOfResponse()); ?></a></li>
                <?php
                }
                ?>
               <?php
                function GetDaysSince($sStartDate, $sEndDate){
                    $start_ts = strtotime($sStartDate);
                    $end_ts = strtotime($sEndDate);
                    $diff = $end_ts - $start_ts;
                    return round($diff / 86400);
                }

                $days = 0;
                if($application->getDateOfResponse())
                {
                    $days = GetDaysSince($application->getDateOfSubmission(), $application->getDateOfResponse());
                }
               else {
                   $days = GetDaysSince($application->getDateOfSubmission(), date("Y-m-d H:i:s"));
               }

                $days_color = "";

                if($days < 10){
                        $days_color = "success";
                }
                elseif($days >= 10 && $days < 20){
                        $days_color = "primary";
                }
                elseif($days >= 20 && $days < 30){
                        $days_color = "warning";
                }
                elseif($days >= 30){
                        $days_color = "danger";
                }
               ?>
                <li><?php echo __('Days in progress'); ?> <span

                class="badge badge-<?php echo $days_color; ?>"

                ><strong>
                <?php
                echo $days;
                ?>
                <?php echo __('days'); ?>
                </strong>
                </span>
                </li>
              </ul>


              <ul class="profile-social-list">
                <li><?php echo __('Application Status'); ?><a href=""> <?php
                    if($stage)
                    {
                        echo $stage->getTitle();
                    }
                    else
                    {
                        echo "Draft";
                    }
                 ?> </a></li>

                <?php
                $q = Doctrine_Query::create()
                    ->from("SubMenus a")
                    ->where("a.id = ?", $application->getApproved());
                $current = $q->fetchOne();

                $current_stage_no = 0;

                if($current)
                {
                    $parent_menu = $current->getMenuId();

                    $q = Doctrine_Query::create()
                        ->from("SubMenus a")
                        ->where("a.menu_id = ?", $parent_menu)
                        ->orderBy("a.order_no ASC");
                    $stages = $q->execute();

                    $countstages = 0;
                    foreach($stages as $stage)
                    {
                        $countstages++;
                        if($stage->getId() == $current->getId())
                        {
                            $current_stage_no = $countstages;
                        }
                    }
                }

                if($countstages == 0)
                {
                     $percentage = 0;
                }
                else
                {
                    $percentage = ($current_stage_no/$countstages) * 100;
                }
                ?>
              </ul>
           </div><!-- blog-item -->

            </div><!-- col-sm-3 -->



            <div class="col-sm-9">
            <?php
             if($_SESSION['payment_updated'])
             {
                $_SESSION['payment_updated'] = false;
            ?>
            <div class="alert alert-success">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <strong><?php echo __('You have succesfully paid for your invoice. Your payment will be confirmed in a few minutes'); ?></strong>.
            </div>
            <?php
             }
            ?>

            <?php
            if($sf_user->hasFlash('notice'))
            {
                ?>
                <div class="alert alert-success">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <strong><?php echo $sf_user->getFlash('notice'); ?></strong>.
                </div>
            <?php
            }
            ?>

            <div class="panel panel-dark widget-btns">
            <div class="panel-heading">
               <h3 class="panel-title"> <?php  echo $application->getApplicationId(); ?>
               <?php

               $q = Doctrine_Query::create()
                 ->from("ApForms a")
                 ->where("a.form_id = ?", $application->getFormId());
               $form = $q->fetchOne();
               if($form)
               {
                  echo "<span><p class=\"text-muted\">Service Code: ".$form->getFormCode()."</p></span>";
               }

               ?><span><?php
                 if($application->getApproved() == "0")
                 {
                     //Draft
                 }
                 else
                 {
                    $q = Doctrine_Query::create()
                         ->from('SubMenus a')
                         ->where('a.id = ?', $application->getApproved());
                    $submenu = $q->fetchOne();
                    if($submenu)
                    {
                        echo "</h3>";
                        echo "<p class=\"text-muted\">".$submenu->getTitle()."</p>";
                    }
                }
                ?>

                <div class="panel-btns">
                   <div class="pull-right">
                  </div>
                </div>
            </div>

            <div>
     <?php
                                if($application->getDeclined() == "1"){
                                ?>
                                <div class="col-sm-12" style="background-color: #d9534f; padding:10px; border-bottom:2px solid #fff;">

                                <div class="col-sm-8"><h4 style="color:#fff;"> Your application has been declined. View the reasons in the comments tab.</h4></div>
                                 <div class="col-sm-4" style="text-align:right;">
                                    <a class="btn btn-default mt5" href="/index.php/application/edit?application_id=<?php echo $application->getId(); ?>"><?php echo __('Edit and Submit'); ?></a>
                                 </div>

                                </div>
                                <?php
                                }
                                if($application->getDeclined() == "2"){
                                ?>
                                <div class="col-sm-12" style="background-color: #d9534f; padding:10px;">
                                <h4 style="color:#fff;"> Your application has been declined. View the reasons in the comments tab.</h4>
                                </div>
                                <?php
                                }
?>

          </div>
             <div class="panel-body panel-body-nopadding">

                        <ul class="nav nav-tabs nav-justified">
                            <li <?php if($open){}else{ ?>class="active"<?php } ?>><a href="#tabs1" data-toggle="tab"><?php echo __('Details'); ?></a></li>
                        </ul>



                    <div class="tab-content tab-content-nopadding">

                        <div id="tabs1" class="tab-pane <?php if($open){}else{ ?>active<?php } ?>">
                            <form class="form-bordered">
                                <?php
                                //Display control buttons that manipulate the application
                                include_partial('viewdetails', array('application' => $application, 'choosen_form' => $choosen_form));
                                ?>
                            </form>
                        </div>

                    </div> <!--Panel-body-->

            </div><!--Panel-body-->

            </div><!--Panel-dark-->


    </div><!-- /Content panel-->
    <?php
}
else
{
    echo "<div class='contentpanel'><h3>Sorry! You are trying to view a permit that doesn't belong to you</h3></div>";
}
?>