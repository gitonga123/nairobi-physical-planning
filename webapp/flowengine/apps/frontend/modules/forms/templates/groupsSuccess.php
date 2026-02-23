<?php
use_helper('I18N');

$membersManager = new MembersManager();
$membership = $membersManager->MembershipIsValidated($sf_user->getGuardUser()->getId());
?>
<div class="col-md-7 col-lg-8 col-xl-9">

	<!-- FILTERS -->
	<div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
		<select id="groupFilter" class="form-select" style="width:auto;">
			<option value="all">All Services</option>
			<?php foreach ($groups as $group): ?>
				<option value="group-<?php echo $group->getGroupId(); ?>"><?php echo $group->getGroupName(); ?></option>
			<?php endforeach; ?>
		</select>

		<input type="text" id="formSearch" class="form-control" placeholder="Search forms by name or description..." style="flex:1;">
	</div>

	<!-- GROUP SECTIONS -->
	<?php foreach ($groups as $group): ?>
		<section id="group-<?php echo $group->getGroupId(); ?>" class="group-section mb-5 border rounded">
			<div class="group-header bg-default text-white p-3 rounded-top border-bottom border-primary">
				<h4 class="mb-0"><?php echo $group->getGroupName(); ?></h4>
			</div>
			<div class="group-body p-4">

				<?php
				// Fetch forms for this group
				$q = Doctrine_Query::create()
					->from('ApForms a')
					->andWhere('a.form_type = 1')
					->andWhere('a.form_active = 1')
					->andWhere('a.form_group = ?', $group->getGroupId())
					->orderBy('a.form_name ASC');
				$forms = $q->execute();

				$filtered_forms = [];
				foreach ($forms as $form) {
					if (sfConfig::get('app_enable_categories') == "yes") {
						$q2 = Doctrine_Query::create()
							->from('sfGuardUserCategoriesForms a')
							->where('a.categoryid = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras())
							->andWhere('a.formid = ?', $form->getFormId());
						if ($q2->count() > 0) {
							$filtered_forms[] = $form;
						}
					} else {
						$filtered_forms[] = $form;
					}
				}
				?>

				<?php if ($membership && $membership['validated']): ?>
					<?php if (count($filtered_forms) > 0): ?>
						<div class="row g-4">
							<?php foreach ($filtered_forms as $form): ?>
								<div class="col-12 col-md-6 col-lg-4">
									<div class="form-card p-3 border rounded h-100 shadow-sm"
										data-form-name="<?php echo strtolower($form->getFormName()); ?>"
										data-form-desc="<?php echo strtolower($form->getFormDescription()); ?>">
										<h5 class="form-title mb-2"><?php echo $form->getFormName(); ?></h5>
										<p class="form-description mb-3"><?php echo $form->getFormDescription(); ?></p>
										<a href="/index.php/forms/view?id=<?php echo $form->getFormId(); ?>" class="btn btn-primary">
											<i class="far fa-edit"></i> Apply
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="alert alert-info mt-3">
							<?php echo __('No available forms for this group.'); ?>
						</div>
					<?php endif; ?>
				<?php else: ?>
					<?php
					$q = Doctrine_Query::create()
						->from('sfGuardUserCategories a')
						->where('a.id = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras());
					$actual_category = $q->fetchOne();
					?>
					<div class="alert alert-danger mt-3">
						<h5><i class="fa fa-exclamation-triangle"></i> <?php echo __('ATTENTION'); ?> - <?php echo strtoupper($actual_category->getName()); ?></h5>
						<p><?php echo __('You cannot submit forms because you have not provided your membership details.'); ?></p>
						<p><?php echo __('To update your membership details:'); ?></p>
						<ol>
							<li><?php echo __('Click your Name at the top right.'); ?></li>
							<li><?php echo __('Go to'); ?> <a href="/index.php/settings"><?php echo __('Account Settings'); ?></a>.</li>
							<li><?php echo __('Click "Edit Additional Details".'); ?></li>
							<li><?php echo __('Add details and submit.'); ?></li>
						</ol>
						<?php if (strlen($membership['member_no'])): ?>
							<p><?php echo __('Did not receive verification email? Click below:'); ?></p>
							<a href="/index.php/membersdatabase/resendboraq" class="btn btn-warning"><?php echo __('Resend Verification Email'); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			</div>
		</section>
	<?php endforeach; ?>
</div>

<!-- STYLES -->
<style>
	.group-section {
		transition: transform 0.2s, box-shadow 0.2s;
		background-color: #fff;
	}

	.group-section:hover {
		transform: translateY(-3px);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
	}

	.group-header h4 {
		font-weight: 600;
		margin: 0;
	}

	.form-card {
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.form-card:hover {
		transform: translateY(-5px);
		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
	}

	.form-title {
		font-weight: 600;
	}

	.form-description {
		font-size: 0.9rem;
		color: #555;
	}
</style>

<!-- SEARCH SCRIPT -->
<script>
	const groupFilter = document.getElementById('groupFilter');
	const formSearch = document.getElementById('formSearch');

	function filterForms() {
		const query = formSearch.value.toLowerCase();
		const selectedGroup = groupFilter.value;

		document.querySelectorAll('.group-section').forEach(section => {
			let showSection = false;
			const sectionId = section.id;

			// Check dropdown filter
			if (selectedGroup !== 'all' && sectionId !== selectedGroup) {
				section.style.display = 'none';
				return;
			}

			// Check cards inside section
			section.querySelectorAll('.form-card').forEach(card => {
				const name = card.getAttribute('data-form-name');
				const desc = card.getAttribute('data-form-desc');
				if (name.includes(query) || desc.includes(query)) {
					card.parentElement.style.display = '';
					showSection = true;
				} else {
					card.parentElement.style.display = 'none';
				}
			});

			// Hide section if no cards match
			section.style.display = showSection ? '' : 'none';
		});
	}

	// Event listeners
	groupFilter.addEventListener('change', filterForms);
	formSearch.addEventListener('input', filterForms);
</script>