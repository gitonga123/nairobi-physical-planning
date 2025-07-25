<?php

/**
 * showSuccess.php template.
 *
 * Displays full client details
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

?>
<div class="pageheader">
	<h2><i class="fa fa-envelope"></i> <?php echo __('Users'); ?></h2>
	<div class="breadcrumb-wrapper">
		<span class="label"><?php echo __('You are here'); ?>:</span>
		<ol class="breadcrumb">
			<li><a href="/plan"><?php echo __('Home'); ?></a></li>
			<li><a href="/plan/frusers/index"><?php echo __('Users'); ?></a></li>
		</ol>
	</div>
</div>

<div class="contentpanel">
	<div class="row">



		<div class="col-sm-2">

			<?php
			if (sfConfig::get('app_sso')) {
				$account_type = "";

				if ($user->getProfile()->getRegisteras() == 1) {
					$account_type = "citizen";
				} elseif ($user->getProfile()->getRegisteras() == 3) {
					$account_type = "alien";
				} elseif ($user->getProfile()->getRegisteras() == 4) {
					$account_type = "visitor";
				}

			?>
				<img src="https://account.ecitizen.go.ke/profile-picture/<?php echo $user->getUsername(); ?>?t=<?php echo $account_type; ?>" class="thumbnail img-responsive mb20" alt="" />
			<?php
			} else {
			?>
				<?php if ($user->getProfile()->getProfilePic()) { ?>
				<img src="/assets_frontend/images/<?php echo $user->getProfile()->getProfilePic(); ?>" class="thumbnail img-responsive mb20" alt="" />
				<?php } else {?>
					<img src="/assets_frontend/images/avatar.png" class="thumbnail img-responsive mb20" alt="" />
				<?php }?>
			<?php
			}
			?>

		</div>


		<div class="col-sm-10" style="min-height:340px;">
			<div class="panel panel-default">
				<ul class="nav nav-tabs ">
					<li class="active"><a href="#ptab1" data-toggle="tab"><?php echo __('Basic Details'); ?></a></li>
					<?php
					if (!sfConfig::get('app_sso')) {
					?>
						<li><a href="#ptab2" data-toggle="tab"><?php echo __('Additional Details'); ?></a></li>
					<?php
					}
					?>
				</ul>
				<div class="tab-content tab-content-nopadding">
					<div class="tab-pane active" id="ptab1">
						<form class="form-bordered form-horizontal border-top-1">
							<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Full Name'); ?></i></label>
								<div class="col-sm-10"><?php echo $user->getProfile()->getFullname(); ?><a class='btn btn-default pull-right' href="/plan/frusers/edit/id/<?php echo $user->getId(); ?>"><i class="fa fa-pencil"></i> <span class="hidden-xs"><?php echo __('Edit Basic Details'); ?></span></a></div>
							</div>
							<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Email Address'); ?></i></label>
								<div class="col-sm-10"><?php echo $user->getProfile()->getEmail(); ?></div>
							</div>
							<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Username'); ?></i></label>
								<div class="col-sm-10"><?php echo $user->getUsername(); ?></div>
							</div>
							<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Phone Number'); ?></i></label>
								<div class="col-sm-10"><?php echo $user->getProfile()->getMobile(); ?></div>
							</div>
							<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Last Login'); ?></i></label>
								<div class="col-sm-10"><?php if ($user->getLastLogin()) {
															echo $user->getLastLogin();
														} else {
															echo "<i>Never logged in</i>";
														} ?></div>
							</div>
							<?php
							if (!sfConfig::get('app_sso')) {
							?>
								<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Active'); ?></i></label>
									<div class="col-sm-10"><?php if ($user->getIsActive() == "1") {
																echo "Yes";
															} else {
																echo "No";
															} ?></div>
								</div>
								<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Validated Email'); ?></i></label>
									<div class="col-sm-10"><?php if ($user->getIsSuperAdmin() == "1") {
																echo "Yes";
															} else {
																echo "No";
															} ?></div>
								</div>
							<?php
							}
							?>
							<div class="form-group"><label class="col-sm-2 control-label"><i class="bold-label-2"><?php echo __('Registered As'); ?></i></label>
								<div class="col-sm-10"><?php

														$q = Doctrine_Query::create()
															->from("SfGuardUserCategories a")
															->where("a.id = ?", $user->getProfile()->getRegisteras());
														$category = $q->fetchOne();
														if (!empty($category)) {
															echo $category->getName();
														} else {
															echo __("No Category Selected");
														}

														?></div>
							</div>
						</form>
					</div>
					<?php

					$q = Doctrine_Query::create()
						->from('mfUserProfile a')
						->where('a.user_id = ?', $user->getId());
					$profile = $q->fetchOne();

					?>
					<div class="tab-pane" id="ptab2">
						<form class="form-bordered form-horizontal border-top-1">
							<?php
							if ($profile && !sfConfig::get('app_sso')) {
							?>
								<div class="form-group">
									<div class="col-sm-12"><a class='btn btn-default pull-right' href="/plan/frusers/editadditional/formid/<?php echo $profile->getFormId() ?>/entryid/<?php echo $profile->getEntryId(); ?>">&nbsp; <?php echo __('Edit Additional Details'); ?></a></div>
								</div>
							<?php
								include_partial('frusers/listinfo', array('form_id' => $profile->getFormId(), 'entry_id' => $profile->getEntryId()));
							} else {
								echo "
	<div class=\"form-group\">
      <div class=\"col-sm-12\"><i class=\"bold-label pt20 pb20 pr20 pl20\">No additional information has been added by the user.</i></div></div>";
							}
							?>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 mt20">
			<div class="panel panel-default">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#ptab3" data-toggle="tab"><?php echo __('Applications'); ?></a></li>
					<li><a href="#ptab6" data-toggle="tab"><?php echo __('Service History'); ?></a></li>
					<?php if (Functions::client_can_add_businesses()) : ?>
						<li><a href="#ptabb" data-toggle="tab"><?php echo __('Businesses'); ?></a></li>
					<?php endif; ?>
					<li><a href="#ptab5" data-toggle="tab"><?php echo __('Archives'); ?></a></li>
					<li><a href="#ptab4" data-toggle="tab"><?php echo __('Activity'); ?></a></li>

				</ul>
				<div class="tab-content tab-content-nopadding">
					<div class="tab-pane active" id="ptab3">
						<form class="form">
							<div class="table-responsive">
								<table class="table table-striped table-hover mb0 radius-bl radius-br border-top-1" id="table3">
									<thead>
										<tr>
											<th>#</th>
											<th><?php echo __('Type'); ?></th>
											<th><?php echo __('No'); ?></th>
											<th><?php echo __('Submitted On'); ?></th>
											<th><?php echo __('Status'); ?></th>
											<th class="aligncenter"><?php echo __('Actions'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$q = Doctrine_Query::create()
											->from('FormEntry a')
											->where('a.user_id = ?', $user->getId())
											->andWhere("a.parent_submission = 0");
										$applications = $q->execute();
										$count = 1;
										foreach ($applications as $application) {
										?>
											<tr>
												<td><?php echo $count++; ?></td>
												<td><?php
													$q = Doctrine_Query::create()
														->from('ApForms a')
														->where('a.form_id = ?', $application->getFormId());
													$form = $q->fetchOne();
													if ($form) {
														echo $form->getFormName();
													} else {
														echo "-";
													}
													?></td>
												<td><?php echo $application->getApplicationId(); ?></td>
												<td><?php
													echo $application->getDateOfSubmission();
													?></td>
												<td class="c">
													<?php
													$q = Doctrine_Query::create()
														->from('SubMenus a')
														->where('a.id = ?', $application->getApproved());
													$submenu = $q->fetchOne();
													if ($submenu) {
														echo $submenu->getTitle();
													} else {
														echo __("Saved Draft");
													}
													?>
												</td>
												<td class="aligncenter">
													<a title='<?php echo __('View Application'); ?>' href='/plan/applications/view/id/<?php echo $application->getId(); ?>'><span class="label label-primary"><i class="fa fa-search-plus"></i></span></a>
													<?php
													if ($application->getApproved() == 0) {
													?>
														<a onClick="if(confirm('Are you sure you want to delete this draft?')){ return true; }else{ return false; }" title='<?php echo __('Delete Draft'); ?>' href='/plan/frusers/show/id/<?php echo $application->getUserId(); ?>/remove/<?php echo $application->getId(); ?>'><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
													<?php
													}
													?>
												</td>
											</tr>
										<?php
										}
										?>
										<tr>
											<td colspan="6" class="radius-bl radius-br">
												<a href="/plan/frusers/recover/id/<?php echo $user->getId(); ?>" class="btn btn-primary pull-right" id="recoverapplication"><?php echo __('Recover An Application'); ?></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</form>
					</div>
					<div class="tab-pane" id="ptab6">
						<form class="form">
							<div class="table-responsive">
								<table class="table table-striped table-hover mb0 radius-bl radius-br border-top-1">
									<thead>
										<tr>
											<th>#</th>
											<th><?php echo __('Type'); ?></th>
											<th><?php echo __('No'); ?></th>
											<th><?php echo __('Generated On'); ?></th>
											<th><?php echo __('Expires On'); ?></th>
											<th><?php echo __('Status'); ?></th>
											<th class="aligncenter"><?php echo __('Actions'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$q = Doctrine_Query::create()
											->from('SavedPermit b')
											->leftJoin('b.FormEntry a')
											->where('a.user_id = ?', $user->getId())
											->andWhere("a.parent_submission = 0")
											->orderBy("b.id DESC");
										$permits = $q->execute();
										$count = 1;
										foreach ($permits as $permit) {
											$application = $permit->getFormEntry();
										?>
											<tr class="gradeA">
												<td><?php echo $count++; ?></td>
												<td><?php
													$q = Doctrine_Query::create()
														->from('ApForms a')
														->where('a.form_id = ?', $application->getFormId());
													$form = $q->fetchOne();
													if ($form) {
														echo $form->getFormName();
													} else {
														echo "-";
													}
													?></td>
												<td><?php echo $application->getApplicationId(); ?></td>
												<td>
													<?php
													echo $permit->getDateOfIssue();
													?>
												</td>
												<td>
													<?php
													echo $permit->getDateOfExpiry();
													?>
												</td>
												<td class="c">
													<?php
													if ($permit->getExpiryTrigger() == 1) {
														echo "Expired";
													} else {
														echo "Active";
													}
													?>
												</td>
												<td class="aligncenter">
													<a title='<?php echo __('View Application'); ?>' href='/plan/permits/view/id/<?php echo $permit->getId(); ?>'><span class="label label-primary"><i class="fa fa-search-plus"></i></span></a>
												</td>
											</tr>
										<?php
										}
										?>
										<tr>
											<td colspan="6" class="radius-bl radius-br">
												<a href="/plan/frusers/recover/id/<?php echo $user->getId(); ?>" class="btn btn-primary pull-right" id="recoverapplication"><?php echo __('Recover An Application'); ?></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</form>
					</div>
					<?php if (Functions::client_can_add_businesses()) : ?>
						<div class="tab-pane" id="ptabb">
							<form class="form">
								<div class="table-responsive">
									<table class="table table-striped table-hover mb0 radius-bl radius-br border-top-1">
										<thead>
											<th class="b-b-0">#</th>
											<th class="b-b-0">Business</th>
											<th class="b-b-0"><?php echo __('Created By'); ?></th>
											<th class="b-b-0"><?php echo __('Created On'); ?></th>
											<th class="b-b-0"><?php echo __('Status'); ?></th>
											<th class="b-b-0 aligncenter"><?php echo __('Action'); ?></th>
										</thead>
										<tbody>
											<?php
											foreach ($businesses->getResults() as $business) {
											?>
												<tr>
													<td><?php echo $business->getId() ?></td>
													<td><?php echo strtoupper($business->getTitle()) ?></td>
													<td><?php echo $business->getUser()->getProfile()->getFullname(); ?></td>
													<td><?php echo $business->getCreatedAt() ?></td>
													<td><?php echo ($business->getDeleted()) ? "<span class='label label-danger'>Not Active</span>" : "<span class='label label-success'>Active</span>"; ?></td>
													<td>
														<a title="<?php echo __('View Business'); ?>" href="/plan/profiles/view/id/<?php echo $business->getId(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
													</td>
												</tr>
											<?php
											}
											?>
										</tbody>
										<tfoot>
											<tr>
												<th colspan="12">
													<p class="table-showing pull-left"><strong><?php echo count($businesses) ?></strong> <?php echo __('profiles'); ?>

														<?php if ($businesses->haveToPaginate()) : ?>
															- <?php echo __('page'); ?> <strong><?php echo $businesses->getPage() ?>/<?php echo $businesses->getLastPage() ?></strong>
														<?php endif; ?></p>


													<?php if ($businesses->haveToPaginate()) : ?>
														<ul class="pagination pagination-sm mb0 mt0 pull-right">
															<li><a href="/plan/frusers/show/page/1">
																	<i class="fa fa-angle-left"></i>
																</a></li>

															<li><a href="/plan/frusers/show/page/<?php echo $businesses->getPreviousPage() ?>">
																	<i class="fa fa-angle-left"></i>
																</a></li>

															<?php foreach ($businesses->getLinks() as $page) : ?>
																<?php if ($page == $businesses->getPage()) : ?>
																	<li class="active"><a href=""><?php echo $page ?></li></a>
																<?php else : ?>
																	<li><a href="/plan/frusers/show/page/<?php echo $page ?>"><?php echo $page ?></a></li>
																<?php endif; ?>
															<?php endforeach; ?>

															<li><a href="/plan/frusers/show/page/<?php echo $businesses->getNextPage() ?>">
																	<i class="fa fa-angle-right"></i>
																</a></li>

															<li><a href="/plan/frusers/show/<?php echo $businesses->getLastPage() ?>">
																	<i class="fa fa-angle-right"></i>
																</a>
															</li>
														</ul>
													<?php endif; ?>
												</th>
											</tr>
										</tfoot>
									</table>
								</div>
							</form>
						</div>
					<?php endif; ?>
					<div class="tab-pane" id="ptab5">
						<form class="form">
							<div class="table-responsive">
								<table class="table table-striped table-hover mb0 radius-bl radius-br border-top-1">
									<thead>
										<tr>
											<th>#</th>
											<th><?php echo __('Type'); ?></th>
											<th><?php echo __('No'); ?></th>
											<th><?php echo __('Submitted On'); ?></th>
											<th><?php echo __('Status'); ?></th>
											<th class="aligncenter"><?php echo __('Actions'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$q = Doctrine_Query::create()
											->from('FormEntryArchive a')
											->where('a.user_id = ?', $user->getId())
											->andWhere("a.parent_submission = 0");
										$applications = $q->execute();
										$count = 1;
										foreach ($applications as $application) {

											$dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
											mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);
											$query = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = '" . $application->getEntryId() . "'";
											$result = mysql_query($query, $dbconn);

											$application_form = mysql_fetch_assoc($result);
										?>
											<tr class="gradeA">
												<td><?php echo $count++; ?></td>
												<td><?php
													$q = Doctrine_Query::create()
														->from('ApForms a')
														->where('a.form_id = ?', $application->getFormId());
													$form = $q->fetchOne();
													if ($form) {
														echo $form->getFormName();
													} else {
														echo "-";
													}
													?></td>
												<td><?php echo $application->getApplicationId(); ?></td>
												<td><?php
													echo $application_form['date_created'];
													?></td>
												<td class="c">
													<?php
													$q = Doctrine_Query::create()
														->from('SubMenus a')
														->where('a.id = ?', $application->getApproved());
													$submenu = $q->fetchOne();
													if ($submenu) {
														echo $submenu->getTitle();
													} else {
														echo __("Saved Draft");
													}
													?>
												</td>
												<td class="aligncenter">
													<a title='<?php echo __('View Application'); ?>' href='/plan/applications/viewarchive/id/<?php echo $application->getId(); ?>'><span class="label label-primary"><i class="fa fa-search-plus"></i></span></a>
													<a title='<?php echo __('Unarchive Application'); ?>' href='/plan/applications/reversearchive/id/<?php echo $application->getId(); ?>'><span class="label label-primary"><i class="fa fa-arrow-left"></i></span></a>
												</td>
											</tr>
										<?php
										}
										?>
										<tr>
											<td colspan="6" class="radius-bl radius-br">
												<a href="/plan/frusers/recover/id/<?php echo $user->getId(); ?>" class="btn btn-primary pull-right" id="recoverapplication"><?php echo __('Recover An Application'); ?></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</form>
					</div>
					<div class="tab-pane" id="ptab4">
						<form class="form">
							<div class="table-responsive">
								<table class="table table-striped table-hover mb0 radius-bl radius-br border-top-1">
									<thead>
										<tr>
											<th style="background: none;">#</th>
											<th><?php echo __('Date'); ?></th>
											<th class="no-sort"><?php echo __('Actions'); ?></th>
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
												<td><?php
													echo $activity->getActionTimestamp();
													?></td>
												<td><?php echo $activity->getAction(); ?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>