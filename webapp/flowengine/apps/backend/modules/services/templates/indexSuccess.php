<?php
use_helper("I18N");
if ($sf_user->mfHasCredential("code_access_rights")): ?>
	<div class="contentpanel">
		<div class="panel panel-default" id="services">

			<div class="panel-heading">
				<h3 class="panel-title"><?php echo __('Services'); ?></h3>
			</div>
			<?php
			if ($sf_user->mfHasCredential('access_security')) {
				?>
				<div class="panel-heading text-right">
					<a class="btn btn-primary" id="newpage" href="/plan/services/new"><?php echo __('+ Add Service'); ?></a>
				</div>
			<?php } ?>

			<div class="panel-body p-b-0">
				<?php
				$c_count = 0;
				foreach ($categories as $category) {
					$c_count++;
					?>
					<div class="list-group">
						<div class="list-group-item">
							<?php echo $c_count; ?>: <?php echo $category->getTitle(); ?>
						</div>
						<?php
						$count = 0;
						foreach ($category->getMenus() as $service) {
							$count++;
							?>
							<div class="list-group">
								<div class="list-group-item">
									<?php echo $count; ?>: <?php echo $service->getTitle(); ?>
								</div>
								<div class="list-group-item">
									<?php
									if ($service->getServiceType() == 1) {
										?>
										<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
											href="/plan/forms/index/filter/<?php echo $service->getId(); ?>"><span
												class="fa fa-edit"></span> <?php echo __('Forms'); ?></a>
										<?php
									} else {
										?>
										<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
											href="/plan/services/fees/id/<?php echo $service->getId(); ?>"><span
												class="fa fa-money"></span> <?php echo __('Fees'); ?></a>
										<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
											href="/plan/services/morefees/id/<?php echo $service->getId(); ?>"><span
												class="fa fa-money"></span> <?php echo __('Other Fees'); ?></a>
										<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
											href="/plan/services/otherfees/id/<?php echo $service->getId(); ?>"><span
												class="fa fa-money"></span> <?php echo __('Charges'); ?></a>
										<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
											href="/plan/services/multiplier/id/<?php echo $service->getId(); ?>"><span
												class="fa fa-money"></span> <?php echo __('Multipliers'); ?></a>
										<?php
									}
									?>
									<a class="btn btn-primary btn-sm btn-workflow" style="margin-right: 10px;"
										href="/plan/stages/index/filter/<?php echo $service->getId(); ?>"><span
											class="fa fa-random"></span> <?php echo __('Workflow'); ?></a>
									<a class="btn btn-primary btn-sm btn-outputs" style="margin-right: 10px;"
										href="/plan/invoicetemplates/index/filter/<?php echo $service->getId(); ?>"><span
											class="fa fa-print"></span> <?php echo __('Invoices'); ?></a>
									<a class="btn btn-primary btn-sm btn-outputs" style="margin-right: 10px;"
										href="/plan/permittemplates/index/filter/<?php echo $service->getId(); ?>"><span
											class="fa fa-print"></span> <?php echo __('Permits'); ?></a>

									<?php
									if ($service->getServiceType() == 2) {
										?>
										<a class="btn btn-primary btn-sm btn-outputs" style="margin-right: 10px;"
											href="/plan/penalties/index/filter/<?php echo $service->getId(); ?>"><span
												class="fa fa-print"></span> <?php echo __('Penalties'); ?></a>
										<?php
									}
									if ($sf_user->mfHasCredential('access_security')) {
										?>

										<div class="btn-group pull-right" role="group" aria-label="...">
											<a title="Edit Service" class="btn btn-default btn-sm"
												href="/plan/services/edit/id/<?php echo $service->getId(); ?>"><span
													class="fa fa-edit"></span></a>
											<a title="Duplicate Service" class="btn btn-default btn-sm"
												href="/plan/services/duplicate/id/<?php echo $service->getId(); ?>"><span
													class="fa fa-copy"></span></a>
											<a onClick="if(confirm('Are you sure?')){ return true; }else{ return false; }"
												class="btn btn-default btn-sm"
												href="/plan/services/delete/id/<?php echo $service->getId(); ?>"><span
													class="fa fa-trash-o"></span></a>
										</div>

										<?php
									}
									?>

								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
<?php endif; ?>