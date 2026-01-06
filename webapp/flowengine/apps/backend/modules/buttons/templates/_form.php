<?php
use_helper("I18N");
?>

<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form id="actionform" class="form-bordered"
	action="<?php echo url_for('/backend.php/buttons/' . ($form->getObject()->isNew() ? 'create' : 'update') . (!$form->getObject()->isNew() ? '?id=' . $form->getObject()->getId() : '')) ?>"
	method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo ($form->getObject()->isNew() ? __('New Action') : __('Edit Action')); ?></h3>
		</div>

		<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
			<button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';"
				aria-hidden="true">&times;</button>
			<strong><?php echo __('Well done'); ?>!</strong>
			<?php echo __('You successfully updated this action'); ?></a>.
		</div>
		<div class="panel-body padding-0">

			<?php if (!$form->getObject()->isNew()): ?>
				<input type="hidden" name="sf_method" value="put" />
			<?php endif; ?>
			<?php if (isset($form['_csrf_token'])): ?>
				<?php echo $form['_csrf_token']->render(); ?>
			<?php endif; ?>
			<?php echo $form->renderGlobalErrors() ?>


			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Title'); ?></i></label>
				<div class="col-sm-8 rogue-input">
					<?php echo $form['title']->renderError() ?>
					<?php echo $form['title'] ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Link'); ?></i></label>
				<div class="col-sm-8 rogue-input">
					<?php echo $form['link']->renderError() ?>
					<?php echo $form['link'] ?> <br />
					<hr />
					<?php echo __('OR Choose an action from below'); ?>: <br />
					<select name="link_action" id="link_action">
						<?php
						//Generate Invoice
						//Add Invoice
						//Start Circulation
						//Decline
						//Move with reason
						//Move without reason
						//View Permit
						?>
						<option value="0"><?php echo __('None'); ?></option>
						<option value="/backend.php/forms/move?"><?php echo __('Move to another stage'); ?></option>
						<option value="/backend.php/forms/decline?"><?php echo __('Back to Client'); ?></option>
						<option value="/backend.php/forms/reject?"><?php echo __('Reject'); ?></option>
						<option value="/backend.php/forms/approve?"><?php echo __('Approve'); ?></option>
					</select>


					<?php echo __('Select Submenu if above action requires moving along workflow'); ?>: <br />
					<select id='link_moveto' name='link_moveto'>
						<option value="" disabled><?php echo __('None'); ?></option>
						<?php
						$q = Doctrine_Query::create()
							->from('Menus a')
							->orderBy('a.order_no ASC');
						$stagegroups = $q->execute();
						foreach ($stagegroups as $stagegroup) {
							echo "<option value='' disabled>" . $stagegroup->getTitle() . "</option>";
							$q = Doctrine_Query::create()
								->from('SubMenus a')
								->where('a.menu_id = ?', $stagegroup->getId())
								->andWhere('a.deleted = ?', '0')
								->orderBy('a.order_no ASC');
							$stages = $q->execute();

							foreach ($stages as $stage) {
								$selected = "";

								if ($application_status != "" && $application_status == $stage->getId()) {
									$selected = "selected";
								}

								echo "<option value='moveto=" . $stage->getId() . "' " . $selected . ">&nbsp;&nbsp;&nbsp;&nbsp;" . $stage->getTitle() . "</option>";
							}
						}

						?>
					</select>
				</div>
			</div>
			<div class="form-group" style="display: none;">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('List of Submenus'); ?></i></label>
				<?php ?>
				<div class="col-sm-8 rogue-input">
					<?php echo $form['submenus_list']->renderError() ?>
					<?php echo $form['submenus_list'] ?>
				</div>

				<?php ?>
			</div>

			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Allowed Groups'); ?></i></label>
				<div class="col-sm-8">
					<select name='allowed_groups[]' id='allowed_groups' multiple>
						<?php
						$selected = "";
						$q = Doctrine_Query::create()
							->from("MfGuardGroup a")
							->orderBy("a.name ASC");
						$groups = $q->execute();
						foreach ($groups as $group) {
							$selected = "";
							$grouppermissions = $group->getPermissions();
							foreach ($grouppermissions as $grouppermission) {
								if (!$form->getObject()->isNew()) {
									$q = Doctrine_Query::create()
										->from("MfGuardPermission a")
										->where("a.name = ?", "accessbutton" . $form->getObject()->getId());
									$permission = $q->fetchOne();
									if ($permission && ($permission->getId() == $grouppermission->getId())) {
										$selected = "selected";
									}
								}
							}
							echo "<option value='" . $group->getId() . "' " . $selected . ">" . $group->getName() . "</option>";
						}
						?>
					</select>
				</div>
			</div>


		</div>

	</div><!--panel-body-->

	<div class="panel-footer">
		<button class="btn btn-danger mr10"><?php echo __('Reset'); ?></button><button type="submit"
			class="btn btn-primary" name="submitbuttonname" id="submitbuttonname"
			value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
	</div>

	</fieldset>
	</div>
</form>
<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script language="javascript">
	jQuery(document).ready(function () {

		var demo1 = $('[id="allowed_groups"]').bootstrapDualListbox();

		$("#submitbuttonname").click(function () {
			$.ajax({
				url: '<?php echo url_for('/backend.php/buttons/' . ($form->getObject()->isNew() ? 'create' : 'update') . (!$form->getObject()->isNew() ? '?id=' . $form->getObject()->getId() : '')) ?>',
				cache: false,
				type: 'POST',
				data: $('#actionform').serialize(),
				success: function (json) {
					$('#alertdiv').attr("style", "display: block;");
					$("html, body").animate({ scrollTop: 0 }, "slow");
					$("#loadinner").load("/backend.php/buttons/index/filter/<?php echo $filter; ?>");
				}
			});
			return false;
		});
	});
</script>