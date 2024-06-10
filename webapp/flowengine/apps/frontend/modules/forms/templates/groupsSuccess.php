<?php
use_helper('I18N');

$membersManager = new MembersManager();
$membership = $membersManager->MembershipIsValidated($sf_user->getGuardUser()->getId());

$user = $sf_user->getGuarduser();
if ($membership && $membership['validated'] && $membership['member_no']) :
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
								<a class="nav-link active" href="# <?php echo $group->getGroupName(); ?>" data-bs-toggle="tab"> UASIN GISHU -> <?php echo $group->getGroupName(); ?></a>
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
							if (sfConfig::get('app_enable_categories') == "yes") {
								$q = Doctrine_Query::create()
									->from('ApForms a')
									->leftJoin('a.sfGuardUserCategoriesForms s')
									->andWhere('a.form_type = 1')
									->andWhere('a.form_active = 1')
									->andWhere('a.form_group = ?', $group->getGroupId())
									->where('s.categoryid = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras())
									->orderBy('a.form_name ASC');
							} else {
								$q = Doctrine_Query::create()
									->from('ApForms a')
									->andWhere('a.form_type = 1')
									->andWhere('a.form_active = 1')
									->andWhere('a.form_group = ?', $group->getGroupId())
									->orderBy('a.form_name ASC');
							}
							$forms = $q->execute();
							foreach ($forms as $form) {
								var_dump($sf_user->getGuardUser()->getProfile()->getId());
								var_dump($sf_user->getGuardUser()->getProfile()->getRegisteras());
								// var_dump($form->getFormId());
								// if (sfConfig::get('app_enable_categories') == "yes") {
								// 	$q = Doctrine_Query::create()
								// 		->from('sfGuardUserCategoriesForms a')
								// 		->where('a.categoryid = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras())
								// 		->andWhere('a.formid = ?', $form->getFormId());
								// 	$category = $q->count();
								// 	if ($category == 0) {
								// 		continue;
								// 	}
								// }
							?>
								<!-- here -->
								<div class="col-12 col-md-6 col-xl-4 d-flex">
									<div class="course-box blog grid-blog">
										<div class="course-content">
											<span class="course-title"><?php echo $form->getFormName() ?></span>
											<p><?php echo $form->getFormDescription() ?></p>
											<div class="row">
												<div class="col">
													<a href="/index.php/forms/view?id=<?php echo $form->getFormId(); ?>" class="btn btn-primary"><i class="far fa-edit"></i> Apply </a>
												</div>
												<div class="col text-end">
													<a href="javascript:void(0);" class="btn btn-secondary">
														<i class="fas fa-envelope"></i> Help
													</a>
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
		->where('a.id = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras());
	$actual_category = $q->fetchOne();

?>
	<div class="col-md-7 col-lg-8 col-xl-9">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="card-header card-title">
							<h4 class="text-danger"><?php echo __('PLEASE UPDATE YOUR MEMBERSHIP DETAILS'); ?></b></h4>
						</div>
						<div class="card-body card-text">
							<?php
							$q = Doctrine_Query::create()
								->from('mfUserProfile a')
								->where('a.user_id = ?', $user->getId());
							$profile = $q->fetchOne();
							if ($profile && $profile->getFormId() && $profile->getEntryId()) {
							?>
								<div class="form-row pull-right">
									<div class="col-sm-12">
										<a class="btn btn-outline-primary" href="/index.php/frusers/editadditional/formid/<?php echo $profile->getFormId(); ?>/entryid/<?php echo $profile->getEntryId(); ?>"> <?php echo __('Edit Additional Details'); ?></a>
									</div>
								</div>
								<?php
								include_partial('frusers/listinfo', array('form_id' => $profile->getFormId(), 'entry_id' => $profile->getEntryId()));
								?>
							<?php
							} else {
								$form_id = 15;
								if ($user->getProfile()->getRegisteras()) {
									$q = Doctrine_Query::create()
										->from("SfGuardUserCategories a")
										->where("a.id = ?", $user->getProfile()->getRegisteras());
									$category = $q->fetchOne();
									if ($category) {
										$form_id = $category->getFormId();
									}
								}
							?>
								<div class="form-row pull-right">
									<div class="col-sm-12">
										<a class="btn btn-outline-primary" href="/index.php/mfRegister/registerDetails2?id=<?php echo $form_id; ?>"> <?php echo __('Add Additional Details'); ?></a>
									</div>
								</div>
							<?php
							}
							?>
							<?php if (strlen($membership['member_no'])) : ?>
								<p><?php echo __('If you have still not received a verification email in your inbox, kindly click the button below.'); ?></p>
								<?php if (strlen($sf_user->getAttribute('boraqs_reset', ''))) : ?>
									<div class="alert alert-warning">
										<p><?php echo $sf_user->getAttribute('boraqs_reset') ?></p>
									</div>
								<?php endif; ?>
								<?php $sf_user->getAttributeHolder()->remove('boraqs_reset'); ?>
								<p><a href="<?php echo '/index.php/membersdatabase/resendboraq' ?>" class="btn btn-warning"><?php echo __('Resend Verification Email'); ?></a></p>
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