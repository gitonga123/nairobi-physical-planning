<?php
use_helper("I18N");

if ($sf_user->mfHasCredential("managestages")) {
	?>
	<div class="contentpanel">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php if ($service) {
					echo $service->getTitle(); ?> 	<?php } ?></h3>
				<?php if ($service) {
					echo $service->getTitle(); ?> 	<?php } ?> 	<?php echo __('workflow'); ?>
				<?php echo __('stages'); ?>
			</div>

			<?php
			if ($sf_user->mfHasCredential('access_security')) {
				?>
				<div class="panel-heading text-right">
					<a class="btn btn-primary" id="newstage"
						href="/plan/stages/new/filter/<?php echo $filter; ?>"><?php echo __('+ Add New Stage'); ?></a>
				</div>
			<?php } ?>

			<div class="panel-body p-b-0">
				<?php
				$list_of_stages = array();

				foreach ($stages as $stage) {
					$list_of_stages[$stage->getOrderNo()] = $stage->getTitle();
				}

				$count = 0;
				foreach ($stages as $stage) {
					$count++;
					?>
					<div class="list-group">
						<div class="list-group-item">
							<?php echo $stage->getTitle(); ?>
						</div>
						<div class="list-group-item">

							<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
								href="/plan/stages/actions/id/<?php echo $stage->getId(); ?>"><span class="fa fa-edit"></span>
								Actions</a>
							<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
								href="/plan/stages/groups/id/<?php echo $stage->getId(); ?>"><span class="fa fa-edit"></span>
								Group Access</a>
							<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
								href="/plan/stages/tasks/id/<?php echo $stage->getId(); ?>"><span class="fa fa-edit"></span>
								Allowed Tasks</a>
							<a class="btn btn-primary btn-sm btn-form" style="margin-right: 10px;"
								href="/plan/stages/inspections/id/<?php echo $stage->getId(); ?>"><span
									class="fa fa-edit"></span> Inspections</a>

							<?php if ($sf_user->mfHasCredential('access_security')) {
								?>
								<div class="btn-group pull-right m-l-10" role="group" aria-label="...">

									<a class="btn btn-default btn-sm"
										href="/plan/stages/edit/id/<?php echo $stage->getId(); ?>/filter/<?php echo $filter; ?>"><span
											class="fa fa-edit"></span></a>
									<a class="btn btn-default btn-sm" href="/plan/stages/delete/id/<?php echo $stage->getId(); ?>"
										onClick="if(confirm('Are you sure?')){ return true; }else{ return false; }"><span
											class="fa fa fa-trash-o"></span></a>
								</div>

								<select class='form-control pull-right' style="width: 200px;"
									onChange="window.location='/plan/stages/index/move/<?php echo $stage->getId(); ?>/to/' + this.value;">
									<option>Change order...</option>
									<option value="1">- Top -</option>
									<?php
									foreach ($list_of_stages as $key => $value) {
										echo "<optgroup label='" . $value . "'>";
										echo "<option value='" . ($key + 1) . "'>Here</option>";
										echo "</optgroup>";
									}
									?>
								</select>
							<?php } ?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div><!--panel-body-->
	</div><!--panel-default-->
	<?php
} else {
	include_partial("accessdenied");
}
?>