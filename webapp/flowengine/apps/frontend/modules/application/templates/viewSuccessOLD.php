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
?>

    <div class="col-sm-12">

        <div class="panel with-nav-tabs panel-default">
            <div class="panel-heading panel-n-tab-heading">
                <h3 class="panel-title"><?php echo $application->getApplicationId(); ?></h3><small><?php echo $application->getForm()->getFormName(); ?></small>
				<p><?php echo $application->getStage()->getMenus()->getTitle() ?> > <?php echo $application->getStage()->getTitle() ?></p>
				<p style="float:right">
				<!--OTB linked-->
				<?php
				foreach($forms_link as $form_link):
				?>
					<a class="btn btn-primary" style="border: 2px solid red;" href="/plan/forms/view?id=<?php echo $form_link->getFormId() ?>&linkto=<?php echo $application->getId() ?>"><?php echo __('Apply for')." ".$form_link->getFormName() ?></a>
				<?php
				endforeach;
				?>
				</p>
            </div>
            <div class="panel-heading">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab1default" data-toggle="tab"><span class="fa fa-edit"></span> <?php echo __("Details"); ?></a></li>
                    <li><a href="#tab3default" data-toggle="tab"><span class="fa fa-download"></span> <?php echo __("Downloads"); ?></a></li>
                    <li><a href="#tab6default" data-toggle="tab"><span class="fa fa-download"></span> <?php echo __("Previous Revisions"); ?></a></li>
                    <li><a href="#tab7default" data-toggle="tab"><span class="fa fa-download"></span> <?php echo __("Application Additional Details"); ?></a></li>
                    <li><a href="#tab5default" data-toggle="tab"><span class="fa fa-download"></span> <?php echo __("Payments"); ?></a></li>
                    <li><a href="#reviewtab" data-toggle="tab"><span class="fa fa-eye"></span> <?php echo __("Reviews"); ?></a></li>
                    <li class="pull-right"><a href="#tab4default" data-toggle="tab"><span class="fa fa-exclamation-circle"></span> <?php echo __("Messages"); ?></a></li>
                </ul>
            </div>
            <div class="panel-body p-0">
                <div class="tab-content">
                    <div class="tab-pane fade in active form-horizontal" id="tab1default">
                        <?php
                        //Display form details
                        include_partial('viewdetails', array('application' => $application));
                        ?>
                    </div>
                    <div class="tab-pane fade in form-horizontal" id="tab3default">
                        <?php
                        //Display downloads
                        include_partial('viewdownloads', array('application' => $application));
                        ?>
                    </div>
                    <div class="tab-pane fade in form-horizontal" id="tab6default">
                        <?php
                        //Display form details
                        include_partial('viewrevisions', array('revisions' => $revisions));
                        ?>
                    </div>
                    <div class="tab-pane fade in form-horizontal" id="tab7default">
                        <?php
                            $q = Doctrine_Query::create()
                            ->from("FormEntryLinks a")
                            ->where("a.formentryid = ? AND a.entry_id <> ?", array($application->getId(),0));
                            $links = $q->execute();
                            $count = 0;
                            foreach($links as $link) {
                            $count++;
                            $q = Doctrine_Query::create()
                                ->from("ApForms a")
                                ->where("a.form_id = ?", $link->getFormId())
                                ->limit(1);
                            $linkedform = $q->fetchOne();
                            if ($linkedform) {
                                //Display control buttons that manipulate the application
                            ?>
                            <div class="panel panel-default">
                            <div class="panel-heading panel-heading-noradius">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#link_<?php echo $count ?>">
                                        <?php echo $linkedform->getFormName() ?>
                                    </a>
                                </h4>
                            </div>
                            <div id="link_<?php echo $count ?>" class="panel-collapse collapse">
                                <div class="panel-body">
                                <?php
                                    //Displays any information attached to this application
                                    include_partial('viewformlinks', array('link' => $link));
                                ?>
                                </div>
                            </div>
                            </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="tab-pane fade in form-horizontal" id="tab5default">
                        <?php
                        include_partial('viewpayments', array('application' => $application));
                        ?>
                    </div>
                    <div class="tab-pane fade in form-horizontal" id="tab4default">
                        <?php
                        //Display downloads
                        include_partial('viewmessages', array('application' => $application));
                        ?>
                    </div>
                    <div class="tab-pane fade in form-horizontal" id="reviewtab">
						<div class="panel panel-default">
						  <div class="panel-heading panel-heading-noradius">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentReviews">
									<?php echo __('Review History'); ?>
								</a>
							</h4>
						  </div>
						  <div id="commentReviews" class="panel-collapse collapse">
							<div class="panel-body">
							  <?php
								//Displays any information attached to this application
								include_partial('application_comments', array('application' => $application));
							  ?>
							</div>
						  </div>
						</div>
						
						<div class="panel panel-default">
							<div class="panel-heading panel-heading-noradius">
								<h4 class="panel-title">
									<a data-toggle="collapse" class="collapsed" data-parent="#accordion"
										href="#commentsDeclines">
										<?php echo __("Previous Reasons for Decline"); ?>
									</a>
								</h4>
							</div>
							<div id="commentsDeclines" class="panel-collapse collapse">
								<div class="panel-body">
									<?php
									include_partial('application_declines', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' => $application->getEntryId()));
									?>
								</div>
							</div>
						</div>
						
						<div class="panel panel-default">
							<div class="panel-heading panel-heading-noradius">
							  <h4 class="panel-title">
								<a data-toggle="collapse" class="collapsed"  data-parent="#accordion" href="#commentsConditions"><?php echo __("Conditions of Approval"); ?></a>
							  </h4>
							</div>
							<div id="commentsConditions" class="panel-collapse collapse">
							  <div class="panel-body">
								<?php
								//Check if this application has been previously declined before
								include_partial('comments_conditions', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' =>  $application->getEntryId()));
								?>
							  </div>
							</div>
						</div>	
                    </div>
                </div>
            </div>
        </div>

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
                    <h4 class="modal-title" id="myModalLabel"><?php echo __("Your application has been received"); ?>.</h4>
                </div>
                <div class="modal-body">
                    <p>
                    <?php
                    //Display success message if available on the form
                    $q = Doctrine_Query::create()
                        ->from("ApForms a")
                        ->where("a.form_id = ?", $application->getFormId());
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
                        echo __("Your application has been submitted");
                    }
                    ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                </div>
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div><!-- modal -->
<?php
}
?>
