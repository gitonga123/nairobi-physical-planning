<?php
/**
 * viewSuccess.php template.
 *
 * Displays a full invoice
 *
 * @package    frontend
 * @subpackage invoices
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

if($permit->getPdfPath() == "")
{
    $permit_manager = new PermitManager();

    $filename = $permit_manager->save_archive_to_pdf_locally($permit->getId());
    $permit->setPdfPath($filename);
    $permit->save();
}
 ?>
 <div class="pageheader">
  <h2><i class="fa fa-envelope"></i> Permit <span>View permit details</span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label">You are here:</span>
    <ol class="breadcrumb">
      <li><a href="/plan">Home</a></li>
      <li><a href="/plan/permits/index">Permits</a></li>
      <li class="active"><?php echo $application->getApplicationId(); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
<?php
    if($permit->getFormEntry()->getUserId() == $sf_user->getGuardUser()->getId()) {
       
        $q = Doctrine_Query::create()
        ->from("ApSettings a");
        
        $settings = $q->fetchOne();
        ?>
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
              ?>
                <h5 class="subtitle mt20 ml20">Application Summary</h5>


                <ul class="profile-social-list">
                    <li><a href=""><?php
                            $q = Doctrine_Query::create()
                                ->from("SubMenus a")
                                ->where("a.id = ?", $application->getApproved());
                            $stage = $q->fetchOne();
                            if ($stage)
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
                    <li>Submitted by <a href=""> <?php echo $architect->getFullname(); ?></a></li>
                    <li>Date of Submission
                        <a><?php echo str_replace(" ", " @ ", $application->getDateOfSubmission()); ?></a></li>
                    <?php
                    if ($application->getDateOfResponse()) {
                        ?>
                        <li>Date of Approval
                            <a><?php echo str_replace(" ", " @ ", $application->getDateOfResponse()); ?></a></li>
                    <?php
                    }
                    ?>
                    <?php
                    function GetDaysSince($sStartDate, $sEndDate)
                    {
                        $start_ts = strtotime($sStartDate);
                        $end_ts = strtotime($sEndDate);
                        $diff = $end_ts - $start_ts;
                        return round($diff / 86400);
                    }

                    $days = GetDaysSince($application->getDateOfSubmission(), date("Y-m-d H:i:s"));

                    $days_color = "";

                    if ($days < 10) {
                        $days_color = "success";
                    } elseif ($days >= 10 && $days < 20) {
                        $days_color = "primary";
                    } elseif ($days >= 20 && $days < 30) {
                        $days_color = "warning";
                    } elseif ($days >= 30) {
                        $days_color = "danger";
                    }
                    ?>
                    <li>Days in progress <span

                            class="badge badge-<?php echo $days_color; ?>"

                            ><strong>
                                <?php
                                echo $days . " Days";
                                ?>
                            </strong>
                </span>
                    </li>
                </ul>

                <ul class="profile-social-list">
                    <li>Application Status<a href=""> <?php
                            if ($stage) {
                                echo $stage->getTitle();
                            } else {
                                echo "Draft";
                            }
                            ?> </a></li>

                    <?php
                    $q = Doctrine_Query::create()
                        ->from("SubMenus a")
                        ->where("a.id = ?", $application->getApproved());
                    $current = $q->fetchOne();

                    $current_stage_no = 0;

                    if ($current) {
                        $parent_menu = $current->getMenuId();

                        $q = Doctrine_Query::create()
                            ->from("SubMenus a")
                            ->where("a.menu_id = ?", $parent_menu)
                            ->orderBy("a.order_no ASC");
                        $stages = $q->execute();

                        $countstages = 0;
                        foreach ($stages as $stage) {
                            $countstages++;
                            if ($stage->getId() == $current->getId()) {
                                $current_stage_no = $countstages;
                            }
                        }
                    }

                    if ($countstages == 0) {
                        $percentage = 0;
                    } else {
                        $percentage = ($current_stage_no / $countstages) * 100;
                    }
                    ?>
                </ul>

            </div>
            <!-- blog-item -->

        </div><!-- col-sm-3 -->
        <div class="col-sm-9">
            <?php
            $document_key = $permit->getDocumentKey();
            ?>

            <ul id="myTab" class="nav nav-tabs">
                <li class="active"><a href="#tabs-1" data-toggle="tab">
                        <?php
                        if ($document_key) {
                            ?>
                            Application Details
                        <?php
                        } else {
                            ?>
                            Service Details
                        <?php
                        }
                        ?>
                    </a></li>
					<li class="pull-right" style="padding-top: 5px; padding-right: 10px;"><button class="btn btn-white" id="printinvoice" type="button" onClick="window.location='<?php echo $settings->getUploadDirWeb().$permit->getPdfPath(); ?>';"><i class="fa fa-print mr5"></i> Print</button></li>
            </ul>
            <div id="myTabContent" class="tab-content" style=" margin-right:20px;">
                <div class="tab-pane fade in active" id="tabs-1">
                    <?php
                    $q = Doctrine_Query::create()
                        ->from("Permits a")
                        ->where("a.id = ?", $permit->getTypeId());
                    $permit_template = $q->fetchOne();
                    ?>
                    
                    <div class="text-right btn-invoice" style="padding-right: 10px;">
                        <br>
                        <?php
                        if (empty($document_key) && $permit_template->getPartType() == 2) {
                            ?>
                            <button class="btn btn-white" id="printinvoice" type="button"
                                    onClick="window.location='/plan/permits/attach/id/<?php echo $permit->getId(); ?>';">
                                <i class="fa fa-print mr5"></i> Attach Signed Copy
                            </button>
                        <?php
                        } elseif (!empty($document_key) && $permit_template->getPartType() == 2) {
                            ?>
                            <button class="btn btn-white" id="printinvoice" type="button"
                                    onClick="window.location='/plan/permits/attach/id/<?php echo $permit->getId(); ?>';">
                                <i class="fa fa-print mr5"></i> Re-attach Signed Copy
                            </button>

                            <button class="btn btn-white" id="printinvoice" type="button"
                                    onClick="window.location='/<?php echo $document_key; ?>';"><i
                                    class="fa fa-print mr5"></i> Print Signed Copy
                            </button>
                        <?php
                        }
                        ?>
                    </div>
                    
                    <br>
                    
                    <div align="center">
                        <?php
                        $pdf_path = $permit->getPdfPath();
                        
                        if($settings->getUploadDir() == "/mnt/gv0/ntsa-prod/uploads")
                        {
                            $pdf_path = "/uploads/".$pdf_path;
                        }
                        else
                        {
                            $pdf_path = $settings->getUploadDir()."/".$pdf_path;
                        }
                        
                        if(substr($pdf_path, 1) != "/")
                        {
                            $pdf_path  = "/".$pdf_path;
                        }
                        ?>
                        <iframe src = "/ViewerJS/#..<?php echo $pdf_path; ?>" width='1024' height='900' allowfullscreen webkitallowfullscreen></iframe>
                     </div>
                </div>


            </div>

        </div><!-- /.row -->
        <!-- /.marketing -->
    <?php
    }
    else
    {
        echo "<h3>Sorry! You are trying to view a permit that doesn't belong to you</h3>";
    }
    ?>
</div>


<?php
if($done == 1) {
    ?>
    <!-- Modal -->
    <div class="modal fade" id="submissionsModal" tabindex="-1" role="dialog"
         aria-labelledby="submissionsModalLabel"
         aria-hidden="true" style="margin-top: 15%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Your application has been received.</h4>
                </div>
                <div class="modal-body">
                    <p>
                        <?php
                        //Display success message if available on the form
                        $q = Doctrine_Query::create()
                            ->from("ApForms a")
                            ->where("a.form_id = ?", $permit->getFormEntry()->getFormId());
                        $apform = $q->fetchOne();
                        if ($apform && $apform->getFormSuccessMessage())
                        {
                        ?>

                    <div class="alert alert-success">
                        <?php echo $apform->getFormSuccessMessage(); ?>
                    </div>
                    <?php
                    }
                    else {
                        echo "Your service is ready. Click on print service to download your service.";
                    }
                    ?>
                    </p>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div><!-- modal -->

<?php
}
?>
