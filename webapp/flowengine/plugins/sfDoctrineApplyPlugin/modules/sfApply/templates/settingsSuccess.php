<?php
$user = $sf_user->getGuarduser();
$membersManager = new MembersManager();
$membership = $membersManager->MembershipIsValidated($user->getId());
use_helper('I18N');
?>
<div class="col-md-7 col-lg-8 col-xl-9">
	<div class="card">
		<?php
		if (!$membership && !$membership['validated'] && !$membership['member_no']) :
		?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Warning!</strong> Please Update Your <a href="#" class="alert-link">Additional Details to Submit Applications in the System</a>.
				<button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
		<?php endif; ?>
		<div class="card-body">
			<div class="card-body">
				<ul class="nav nav-tabs nav-tabs-bottom">
					<li class="nav-item"><a class="nav-link active" href="#bottom-tab1" data-bs-toggle="tab"><?php echo __('Basic Details'); ?></a></li>
					<li class="nav-item"><a class="nav-link" href="#bottom-tab2" data-bs-toggle="tab"><?php echo __('Edit Additional Details'); ?></a></li>
					<li class="nav-item"><a class="nav-link" href="#bottom-tab3" data-bs-toggle="tab"><?php echo __('My Activity'); ?></a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane show active" id="bottom-tab1">
						<form>
							<div class="row form-row">
								<div class="col-12 col-md-6">
									<div class="form-group">
										<label><?php echo __('Full Name'); ?></label>
										<input type="text" class="form-control" value="<?php echo $user->getProfile()->getFullname(); ?>" disabled>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group">
										<label><?php echo __('Email Address'); ?></label>
										<input type="text" class="form-control" value="<?php echo $user->getProfile()->getEmail(); ?>" disabled>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group">
										<label><?php echo __('Username'); ?></label>
										<input type="text" class="form-control" value="<?php echo $user->getUsername(); ?>" disabled>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group">
										<label><?php echo __('Last Login'); ?></label>
										<div class="cal-icon">
											<?php if ($user->getLastLogin()) {
												echo "<input type='text' class='form-control' value={$user->getLastLogin()} disabled>";
											} else {
												echo "<input type='text' class='form-control datetimepicker' value='' disabled>";
											} ?>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-md-2"><?php echo __('Is Active?'); ?></label>
										<div class="col-md-10">
											<div class="checkbox">
												<label>
													<input type="checkbox" name="checkbox" <?php if ($user->getIsActive() == "1") {
																								echo __("checked");
																							} else {
																								echo __("");
																							} ?> disabled> Yes
												</label>
											</div>
											<div class="checkbox">
												<label>
													<input type="checkbox" name="checkbox" <?php if ($user->getIsActive() == "1") {
																								echo __("");
																							} else {
																								echo __("checked");
																							} ?> disabled> No
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-md-2"><?php echo __('Email is Validated?'); ?></label>
										<div class="col-md-10">
											<div class="checkbox">
												<label>
													<input type="checkbox" name="checkbox" <?php if ($user->getIsSuperAdmin() == "1") {
																								echo __("checked");
																							} else {
																								echo __("");
																							} ?> disabled> Yes
												</label>
											</div>
											<div class="checkbox">
												<label>
													<input type="checkbox" name="checkbox" <?php if ($user->getIsSuperAdmin() == "1") {
																								echo __("");
																							} else {
																								echo __("checked");
																							} ?> disabled> No
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="tab-pane" id="bottom-tab2">
						<?php
						$q = Doctrine_Query::create()
							->from('mfUserProfile a')
							->where('a.user_id = ?', $user->getId());
						$profile = $q->fetchOne();
						if ($profile) {
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
									<a class="btn btn-outline-primary" href="/plan/mfRegister/registerDetails2?id=<?php echo $form_id; ?>"> <?php echo __('Add Additional Details'); ?></a>
								</div>
							</div>
						<?php
						}
						?>
					</div>
					<div class="tab-pane" id="bottom-tab3">
						<div class="table-responsive">
							<table class="datatable table table-stripped">
								<thead>
									<tr>
										<th><?php echo __('ID'); ?></th>
										<th><?php echo __('Date/Time'); ?></th>
										<th><?php echo __('Action'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$q = Doctrine_Query::create()
										->from('Activity a')
										->where('a.user_id = ?', $user->getId());
									$activities = $q->execute();
									$count = 1;
									foreach ($activities as $activity) {
									?>
										<tr class="gradeX">
											<td><?php echo $count++; ?></td>
											td><?php
												echo $activity->getActionTimestamp();
												?></td>
											<td><?php echo $activity->getAction(); ?></td>
										<?php
									}
										?>
										</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>