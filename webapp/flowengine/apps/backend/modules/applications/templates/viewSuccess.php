<?php

/**
 * view template.
 *
 * Display a task, its comments sheets/invoices and application details relating to it
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="pageheader">
	<h2><i class="fa fa-th-list"></i> <?php echo __("Tasks"); ?> <span><?php echo $application->getApplicationId(); ?></span></h2>
	<div class="breadcrumb-wrapper">
		<span class="label"><?php echo __("You are here"); ?>:</span>
		<ol class="breadcrumb">
			<li><a href="/plan/tasks/list"><?php echo __("Applications"); ?></a></li>
			<li class="active"><?php echo __("View Details"); ?></li>
		</ol>
	</div>
</div>

<div class="contentpanel">
	<?php if ($has_document_to_sign and $can_sign_permit) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"> Service Details</h3>
			</div>
			<div class="panel-body">
				<h3>You need to sign all (<?php echo count($q_permit_result) ?>) permits before proceeding</h3>

				<?php include_partial(
					'downloads',
					[
						'application' => $application,
						'can_sign_permit' => $can_sign_permit,
						'permit_manager' => $permit_manager
					]
				) ?>
			</div>
		</div>
	<?php else : ?>
		<div class="panel panel-default">
			<?php if (isset($error)) : ?>
				<div class="alert alert-danger" id="alertdiv" name="alertdiv">
					<button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">
						&times;
					</button>
					<strong><?php echo __('Info'); ?>!</strong> <?php echo $error; ?></a>.
				</div>
			<?php endif; ?>


			<div class="panel-heading">
				<h3 class="panel-title"> Service Details</h3>
			</div>
			<!--OTB Start - Allow authorized users to send any stage (Previously possible in old system)-->
			<?php
			$invoice_manager = new InvoiceManager();

			//$existing_unpaid_invoice = $invoice_manager->has_unpaid_invoice($application->getId());

			//error_log("Override stage permission >>>> ".$sf_user->mfHasCredential('can_override_stage').", >> unpaid invoice permission >>> ".$existing_unpaid_invoice." --- Cyclic function---".$sf_user->mfHasCredential('can_run_cyclic'));
			//Stage
			$q = Doctrine_Query::create()
				->from('SubMenus a')
				->where('a.id = ?', $application->getApproved());
			$current_stage = $q->fetchOne();
			if ($application->getApproved() != 0 && $current_stage->getStageType() != 3) //Only show if settings access and not a invoice stage
			{
			?>
				<div class="btn-group mr5 pull-left">
					<?php if ($sf_user->mfHasCredential('can_override_stage')) : ?>
						<button type="button" class="btn btn-primary"><?php echo __('Choose Action'); ?></button>
						<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<?php
							if ($application->getParentSubmission() == "0") {
								$rejected = false;

								if ($application->getDeclined()) {
									$rejected = true;
								}

								$q = Doctrine_Query::create()
									->from('SubMenuButtons a')
									->where('a.sub_menu_id = ?', $application->getApproved());
								$submenubuttons = $q->execute();
								//OTB ADD
								foreach ($submenubuttons as $submenubutton) {
									$q = Doctrine_Query::create()
										->from('Buttons a')
										->where('a.id = ?', $submenubutton->getButtonId());
									$buttons = $q->execute();

									foreach ($buttons as $button) {
										if ($sf_user->mfHasCredential("accessbutton" . $button->getId())) {
											echo "<li><a onClick=\"if(confirm('Are you sure?')){ window.location='" . $button->getLink() . "&form_entry_id=" . $application->getId() . "'; }else{ return false; }\">" . $button->getTitle() . "</a></li>";
										}
									}
								}
							}
							?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="btn-group mr5 pull-left">
					<?php if ($sf_user->mfHasCredential('can_override_stage_move_to')) : ?>
						<button type="button" class="btn btn-warning">Move</button>
						<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<?php
							//Get list of all stages, starting with parent stages

							$q = Doctrine_Query::create()
								->from('Menus a')
								->where('a.id = ?', $current_stage->getMenuId())
								->orderBy('a.order_no ASC');
							$stagegroups = $q->execute();
							foreach ($stagegroups as $stagegroup) {
								//Filter only stages that the current user has privileges for
								if ($sf_user->mfHasCredential('accessmenu' . $stagegroup->getId())) {

									$q = Doctrine_Query::create()
										->from('SubMenus a')
										->where('a.menu_id = ? AND a.deleted = 0', $stagegroup->getId())
										->orderBy('a.order_no ASC');
									$stages = $q->execute();

									$stagecontent = "";

									//Get of list of all child stages under this parent stage
									foreach ($stages as $stage) {
										//Filter only stages that the current user has privileges for
										if ($sf_user->mfHasCredential('accesssubmenu' . $stage->getId())) {
											$selected = "";
											//Highlighted currently filtered stage as selected
											if ($_GET['filter'] != "" && $_GET['filter'] == $stage->getId()) {
												$selected = "selected";
											}
							?>
											<li><a onClick="if(confirm('Are you sure you want to move it?')){ window.location = '/plan/applications/moveapp/id/<?php echo $application->getId(); ?>/stage/<?php echo $stage->getId(); ?>'; }"><?php echo $stage->getTitle(); ?></a></li>
							<?php
										}
									}

									//This prevents parent stages from showing if they don't have any child stages
									/*if($stagecontent != "")
				{
					echo "<optgroup label='".$stagegroup->getTitle()."'>";
						
					echo $stagecontent;
						
					echo "</optgroup>";
				}*/
								}
							}
							?>
						</ul>
					<?php endif; ?>
					<?php if ($sf_user->mfHasCredential('can_run_cyclic') && Functions::client_can_add_businesses()) : ?>
						<a href="<?php echo url_for('/plan/applications/cyclicondemand/id/' . $application->getId()) ?>" class="btn btn-info">Cyclic Billing</a>
						<a href="<?php echo url_for('/plan/applications/cyclicondemandfuture/id/' . $application->getId()) ?>" class="btn btn-info">Cyclic Billing (+1 Year)</a>
						<a href="<?php echo url_for('/plan/applications/movetoworkflow/id/' . $application->getId()) ?>" class="btn btn-warning" onClick="confirm('Are you sure?')">Move Workflows</a>
					<?php endif; ?>
				</div>
			<?php } ?>
			<!--OTB End - Allow authorized users to send any stage (Previously possible in old system)-->

			<?php
			//OTB HIDE IF NO TASK SET
			$q = Doctrine_Query::create()
				->from("SubMenuTasks a")
				->where("a.sub_menu_id = ?", $application->getApproved());
			$sub_menu_tasks = $q->count();
			if ($sub_menu_tasks) {
				$q = Doctrine_Query::create()
					->from("Task a")
					->where("a.status = 1")
					->andWhere("a.application_id = ? and a.owner_user_id = ?", array($application->getId(), $sf_user->getAttribute('userid')));
				//Show if no pending assessment
				if ($application->getAssessmentInprogress() == 0 && $q->count() == 0) {
			?>
					<div class="panel-heading text-right">
						<a class="btn btn-primary" href="/plan/tasks/pick/id/<?php echo $application->getId(); ?>"><?php echo __('Start Task') ?></a>
					</div>
					<?php
				} else {
					$task = $q->fetchOne();

					if ($task) {
					?>
						<div class="panel-heading text-right">
							<a class="btn btn-primary" href="/plan/tasks/view/id/<?php echo $task->getId(); ?>"><?php echo __('View Task') ?></a>
						</div>
			<?php
					}
				}
			}
			?>

			<?php
			//Check if stage if shared will move app 
			$otbhelper = new OTBHelper();
			if ($sf_user->mfHasCredential("backend_share_application") && $otbhelper->isSharedStage($application->getApproved())) :
			?>
				<div class="panel-heading text-right">
					<a title="<?php echo __('Share Application'); ?>" class="btn btn-warning" onClick="window.location='/plan/share/share/id/<?php echo $application->getId(); ?>';"><span class="glyphicon glyphicon-share"></span><?php echo __('Share Application') ?></a>
				</div>

			<?php endif; ?>

			<br />
			<div class="panel-body">

				<?php
				//Displays the user panel
				include_partial('tasks/task_user_info', array('application' => $application));
				?>
				<?php include_partial(
					'signableattachments',
					['application' => $application]
				) ?>


				<?php
				if ($application->getMfInvoice()) {
				?>
					<form class="form-bordered">
						<?php
						//Displays any information attached to this application
						include_partial('tasks/application_billing', array('application' => $application));
						?>
					</form>
				<?php
				}
				?>

				<?php include_partial(
					'downloads',
					[
						'application' => $application,
						'can_sign_permit' => $can_sign_permit,
						'permit_manager' => $permit_manager
					]
				)
				?>


				<div class="panel panel-default">
					<div class="panel-heading">
						<h5 class="bug-key-title"><?php echo $application->getApplicationId(); ?> - <?php echo $application->getStatusName(); ?></h5>
						<div class="panel-title">
							<?php
							$q = Doctrine_Query::create()
								->from("ApForms a")
								->where("a.form_id = ?", $application->getFormId())
								->limit(1);
							$form = $q->fetchOne();
							if ($form) {
								echo $form->getFormName();
							}
							?>
						</div>

						<div class="btn-group pull-right" style="margin-top: -38px;">
							<?php if ($application->getStage()->getAllowEdit() && $sf_user->mfHasCredential("accesssubmenu" . $application->getApproved())) { ?>
								<a href="/plan/applications/edit/id/<?php echo $application->getId(); ?>" class="btn btn-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Application");  ?></a>
								<?php
								$q = Doctrine_Query::create()
									->from("FormEntryLinks a")
									->where("a.formentryid = ? AND a.entry_id <> ?", array($application->getId(), 0));
								$links = $q->execute();

								foreach ($links as $link) {
									//CHECK IF FORM IS ACCESSED BY frontend users(applicants)
									$q = Doctrine_Query::create()
										->from("SfGuardUserCategoriesForms a")
										->where("a.formid = ?", $link->getFormId());
									$sharedform = $q->fetchOne();

									if ($sharedform) {
										$q = Doctrine_Query::create()
											->from('ApForms f')
											->where('f.form_id = ?', $link->getFormId());
										$shared_form = $q->fetchOne();
								?>
										<a title="<?php echo __('Edit'); ?> <?php echo $shared_form->getFormName(); ?>" class="btn btn-primary" onClick="window.location='/plan/applications/editentries?form_id=<?php echo $link->getFormId() ?>&id=<?php echo $link->getEntryId(); ?>&form_entry_id=<?php echo $application->getId() ?>';"><span class="glyphicon glyphicon-edit"></span> <?php echo __('Edit Application'); ?></a>
							<?php

									}
								}
							}
							?>
						</div>
					</div>
					<div class="panel-body padding-0">
						<div class="panel with-nav-tabs panel-default">
							<div class="panel-heading">
								<ul class="nav nav-tabs">
									<li <?php if ($current_tab == "application") {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=application"><span class="fa fa-bars"></span> <?php echo __('Application Details'); ?></a></li>
									<li <?php if ($current_tab == "application_link") {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=application_link"><span class="fa fa-bars"></span> <?php echo __('Application Additional Details'); ?></a></li>
									<li <?php if ($current_tab == "revisions") {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=revisions"><span class="fa fa-bars"></span> <?php echo __('Previous Revisions'); ?></a></li>
									<li <?php if ($current_tab == "reviews") {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=reviews"><span class="fa fa-eye"></span> <?php echo __('Review History'); ?></a></li>
									<li <?php if ($current_tab == "messages") {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=messages"><span class="fa fa-comments"></span> <?php echo __('Messages'); ?></a></li>
									<li <?php if ($current_tab == "memo") {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=memo"><span class="fa fa-comments-o"></span> <?php echo __('Memo  '); ?><span class="badge"><?php echo $internal_memo; ?></span></a></li>
									<li <?php if ($current_tab == "history" && $sf_user->mfHasCredential("access_application_history")) {
											echo "class='active'";
										} ?>><a href="/plan/applications/view?id=<?php echo $application->getId(); ?>&current_tab=history"><span class="fa fa-bars"></span> <?php echo __('Application History'); ?></a></li>
								</ul>
							</div>
							<div class="panel-body">
								<div class="tab-content">
									<?php if ($current_tab == "application") { ?>
										<div class="tab-pane fade in active" id="ptab1">
											<form class="form-bordered">
												<?php include_partial('tasks/application_details', array('application' => $application)); ?>
											</form>
										</div>
									<?php } ?>
									<?php if ($current_tab == "application_link") {
										include_partial('tasks/application_links', array('application' => $application));
									} ?>
									<!-- OTB ADD --->
									<?php if ($current_tab == "reviews") : ?>
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
													include_partial('tasks/application_comments', array('application' => $application));
													?>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading panel-heading-noradius">
												<h4 class="panel-title">
													<a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentsDeclines">
														<?php echo __("Previous Reasons for Decline"); ?>
													</a>
												</h4>
											</div>
											<div id="commentsDeclines" class="panel-collapse collapse">
												<div class="panel-body">
													<?php
													include_partial('tasks/application_declines', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' => $application->getEntryId()));
													?>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading panel-heading-noradius">
												<h4 class="panel-title">
													<a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#commentsConditions"><?php echo __("Conditions of Approval"); ?></a>
												</h4>
											</div>
											<div id="commentsConditions" class="panel-collapse collapse">
												<div class="panel-body">
													<?php
													//Check if this application has been previously declined before
													include_partial('tasks/comments_conditions', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' =>  $application->getEntryId()));
													?>
												</div>
											</div>
										</div>
									<?php endif; ?>
									<?php if ($current_tab == "messages") { ?>
										<div class="tab-pane pt20 active" id="ptab6">
											<form class="form-bordered">
												<?php
												//Displays a message trail between the client and the reviewers
												include_partial('tasks/application_messages', array('application' => $application)); //Check time for loading of library scripts
												?>
											</form>
										</div>
									<?php } ?>
									<?php if ($current_tab == "memo") { ?>
										<div class="tab-pane pt20 active" id="ptab7">
											<form class="form-bordered">
												<?php
												//Displays a message trail between reviewers
												include_partial('tasks/application_memos', array('application' => $application)); //Check time for loading of library scripts
												?>
											</form>
										</div>
									<?php } ?>
									<?php if ($current_tab == "history") : ?>
										<div class="panel panel-default">
											<div class="panel-heading">
												<h3 class="panel-title"><?php echo __('Application History'); ?></h3>
											</div>
											<div class="panel-body padding-0">
												<?php
												//Displays any information attached to this application
												include_partial('tasks/viewhistory', array('application' => $application, 'fromdate' => $fromdate, 'fromtime' => $fromtime, 'todate' => $todate, 'totime' => $totime, 'apppage' => $apppage));
												?>
											</div>
										</div>
									<?php endif; ?>
									<?php if ($current_tab == "revisions") : ?>
										<div class="panel panel-default">
											<div class="panel-heading">
												<h3 class="panel-title"><?php echo __('Previous Revisions'); ?></h3>
											</div>
											<div class="panel-body padding-0">
												<?php
												//Displays any information attached to this application
												include_partial('tasks/viewrevisions', array('revisions' => $revisions));
												?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<!--End panel with tabs-->
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>