<?php
use_helper('I18N');

$membersManager = new MembersManager();
$membership = $membersManager->checkIfUserAccountIsActivated(null, null, $sf_user->getGuardUser()->getId());

$membership_details = $membersManager->getMembersDatabaseDetails(null, null, $sf_user->getGuardUser()->getId());
$user = $sf_user->getGuarduser();

// $user_category = $sf_user->getGuardUser()->getProfile()->getRegisteras();
$user_category = 6;
if ($membership) :
?>
	<div class="col-md-7 col-lg-8 col-xl-9">
		<div class="row">
			<div class="col-12">
				<!-- Tab Menu -->
				<nav class="user-tabs">
					<ul class="nav nav-tabs nav-tabs-bottom nav-justified">
						<?php
						$count = 0;
						foreach ($groups as $group) { ?>
							<li>
								<a class="nav-link active" href="# <?php echo $group->getGroupName(); ?>" data-bs-toggle="tab"> Uasin Gishu -> <?php echo $group->getGroupName(); ?></a>
							</li>
						<?php } ?>
					</ul>
				</nav>
				<!-- /Tab Menu -->
				<!-- Tab Content -->
				<div class="tab-content">
					<!-- Active Content -->
					<div role="tabpanel" id="activeservice" class="tab-pane fade show active">
						<div class="row">
							<?php
							if ($membership_details && $membership && $user_category == 6) { ?>
								<div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
									<i class="bi bi-exclamation-triangle-fill me-2"></i>
									<div>
										<strong>Notice — Professional Submission Required</strong>
										<p class="mb-0">
											This system only accepts applications submitted by qualified and registered professionals
											(e.g., architects, engineers, physical planners).
											As a normal applicant, please either engage a qualified professional or visit the
											<strong>Uasin Gishu County offices</strong> for guidance on submitting your application.
										</p>
									</div>
								</div>
							<?php } else if (sfConfig::get('app_enable_categories') == "yes") {
								$q = Doctrine_Query::create()
									->from('ApForms a')
									->leftJoin('a.sfGuardUserCategoriesForms s')
									->andWhere('a.form_type = 1')
									->andWhere('a.form_active = 1')
									->andWhere('a.form_group = ?', $group->getGroupId())
									->where('s.categoryid = ?', $user_category)
									->orderBy('a.form_name ASC');
							} else {


								// Original
								$q = Doctrine_Query::create()
									->from('ApForms a')
									->leftJoin('a.sfGuardUserCategoriesForms s')
									->andWhere('a.form_type = 1')
									->andWhere('a.form_active = 1')
									->andWhere('a.form_group = ?', $group->getGroupId())
									->where('s.categoryid = ?', $user_category)
									->orderBy('a.form_name DESC');
							}
							$forms = $q->execute();
							foreach ($forms as $form) {
							?>
								<!-- here -->
								<div class="col-12 col-md-6 col-xl-4 d-flex">
									<div class="course-box blog grid-blog">
										<div class="course-content">
											<span class="course-title"><?php echo $form->getFormName() ?></span>
											<p><?php echo $form->getFormDescription() ?></p>
											<div class="row">
												<div class="col">
													<a href="/plan/forms/view?id=<?php echo $form->getFormId(); ?>" class="btn btn-success"><i class="far fa-edit"></i> Apply </a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- end here -->
							<?php
							}
							?>
						</div>
					</div>
					<!-- /Active Content -->
				</div>
				<!-- /Tab Content -->
			</div>
		</div>
	</div>
<?php
else :
	$q = Doctrine_Query::create()
		->from('sfGuardUserCategories a')
		->where('a.id = ?', $user_category);
	$actual_category = $q->fetchOne();

?>
	<div class="col-md-7 col-lg-8 col-xl-9">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="card-header card-title">
							<?php
							if (!$membership && !$membership_details) {
								$headingText  = __('PLEASE ADD YOUR PROFESSIONAL MEMBERSHIP DETAILS');
								$headingClass = 'text-danger';
							} elseif (!$membership && strlen($membership_details->id)) {
								$headingText  = __('MEMBERSHIP DETAILS SUBMITTED – AWAITING VERIFICATION');
								$headingClass = 'text-dark strong';
							} else {
								$headingText  = __('ACCOUNT VERIFIED');
								$headingClass = 'text-success';
							}
							?>
							<h4 class="<?php echo $headingClass; ?>">
								<?php echo $headingText; ?>
							</h4>
						</div>
						<div class="card-body card-text">
							<?php
							$q = Doctrine_Query::create()
								->from('mfUserProfile a')
								->where('a.user_id = ?', $user->getId());
							$profile = $q->fetchOne();
							if ($membership_details && $profile && $profile->getFormId() && $profile->getEntryId()) {
							?>
								<div class="form-row pull-right">
									<div class="col-sm-12">
										<a class="btn btn-outline-primary" href="/plan/frusers/editadditional/formid/<?php echo $profile->getFormId(); ?>/entryid/<?php echo $profile->getEntryId(); ?>"> <?php echo __('Edit Additional Details'); ?></a>
									</div>
								</div>
								<?php
								include_partial('frusers/listinfo', array('form_id' => $profile->getFormId(), 'entry_id' => $profile->getEntryId()));
								?>
							<?php
							} else {
								$form_id = 15;
								if ($user_category) {
									$q = Doctrine_Query::create()
										->from("SfGuardUserCategories a")
										->where("a.id = ?", $user_category);
									$category = $q->fetchOne();
									if ($category) {
										$form_id = $category->getFormId();
									}
								}
							?>
								<div class="form-row pull-right">
									<div class="col-sm-12">
										<?php
										include_partial('register_details', array('form_id' => $form_id));
										?>
									</div>
								</div>
							<?php
							}
							?>
							<!-- <?php // if (strlen($membership_details->id)) : 
									?>
								<div class="alert alert-warning d-flex align-items-center" role="alert">
									<i class="bi bi-exclamation-triangle-fill me-2"></i>
									<div>
										<strong>Account approval pending.</strong>
										Your membership details have been received and are currently under review. You will be notified once approval is complete.
									</div>
								</div>
							<?php // endif; 
							?> -->

							<?php if ($membership_details && $membership && $userCategory == 6) :
							?>
								<div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
									<i class="bi bi-exclamation-triangle-fill me-2"></i>
									<div>
										<strong>Notice — Professional Submission Required</strong>
										<p class="mb-0">
											This system only accepts applications submitted by qualified and registered professionals
											(e.g., architects, engineers, physical planners).
											As a normal applicant, please either engage a qualified professional or visit the
											<strong>Uasin Gishu County offices</strong> for guidance on submitting your application.
										</p>
									</div>
								</div>
							<?php
							// --- Pending verification alert ---
							elseif (strlen($membership_details->id)) :
							?>
								<div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
									<i class="bi bi-exclamation-triangle-fill me-2"></i>
									<div>
										<strong>Account approval pending.</strong>
										Your membership details have been received and are currently under review. You will be notified once approval is complete.
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
endif;
?>