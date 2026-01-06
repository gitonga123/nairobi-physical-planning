<?php
use_helper("I18N");
?>
<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>


<form id="stageform" class="form-bordered" action="<?php echo url_for('/backend.php/stages/' . ($form->getObject()->isNew() ? 'create' : 'update') . (!$form->getObject()->isNew() ? '/id/' . $form->getObject()->getId() : '')) ?>/filter/<?php echo $filter; ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?> autocomplete="off" data-ajax="false">
	<div class="panel panel-default">

		<div class="panel-heading">
			<h3 class="panel-title"><?php echo ($form->getObject()->isNew() ? __('New Stage') : __($form->getObject()->getMenus()->getTitle() . ' -&gt; ' . __('Edit Stage'))); ?></h3>
		</div>
		<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
			<button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
			<strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this stage'); ?></a>.
		</div>
		<div class="panel-body padding-0">


			<?php echo $form->renderGlobalErrors() ?>


			<?php echo $form->renderHiddenFields() ?>

			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Title'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['title']->renderError() ?>
					<?php
					$translation = new translation();
					$title = "";
					if (!$form->getObject()->isNew()) {
						$title = $form->getObject()->getTitle();
						if ($translation->getTranslation('submenus', 'title', $form->getObject()->getId())) {
							$title = $translation->getTranslation('submenus', 'title', $form->getObject()->getId());
						}
					}
					?>
					<input class="form-control" type='text' name='sub_menus[title]' id='sub_menus_title' required="required" value="<?php if (!$form->getObject()->isNew()) {
																																		echo $title;
																																	} ?>">
				</div>
			</div>

			<div id="nameresult" name="nameresult"></div>

			<script language="javascript">
				$('document').ready(function() {
					$('#sub_menus_title').keyup(function() {
						$.ajax({
							type: "POST",
							url: "/backend.php/stages/checkname/filter/<?php echo $filter; ?>",
							data: {
								'name': $('input:text[id=sub_menus_title]').val()
							},
							dataType: "text",
							success: function(msg) {
								//Receiving the result of search here
								$("#nameresult").html(msg);
							}
						});
					});
				});
			</script>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Workflow/Service') ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['menu_id']->renderError() ?>
					<?php echo $form['menu_id']->render(array('class' => 'form-control')) ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Maximum duration of time an application is allowed in this stage (Days)'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['max_duration']->renderError() ?>
					<?php echo $form['max_duration']->render(array('class' => 'form-control')) ?>
				</div>
			</div>
			<?php
			$notification = null;
			$stage_type = null;
			$stage_property = null;
			$stage_type_movement = null;
			$stage_type_notification = null;
			$stage_expired_movement = null;
			$change_application_number = null;
			$application_id = null;
			$application_id_start = null;

			if (!$form->getObject()->isNew()) {
				$q = Doctrine_Query::create()
					->from("Notifications a")
					->where("a.submenu_id = ?", $form->getObject()->getId());
				$notification = $q->fetchOne();

				$stage_type = $form->getObject()->getStageType();
				$stage_property = $form->getObject()->getStageProperty();
				$stage_type_movement = $form->getObject()->getStageTypeMovement();
				$stage_type_movement_pass = $form->getObject()->getStageTypeMovement();
				$stage_type_movement_fail = $form->getObject()->getStageTypeMovementFail();
				$stage_type_notification = $form->getObject()->getStageTypeNotification();
				$stage_expired_movement = $form->getObject()->getStageExpiredMovement();
				$change_application_number = $form->getObject()->getChangeIdentifier();
				$application_id = $form->getObject()->getNewIdentifier();
				$application_id_start = $form->getObject()->getNewIdentifierStart();
			}

			//If applications expire, what to do

			//Stage type properties
			?>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Send expired application to another stage'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['stage_expired_movement']->renderError() ?>
					<?php echo $form['stage_expired_movement']->render(array('class' => 'form-control')) ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Send expired application as declined'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['stage_expired_movement_decline']->renderError() ?>
					<?php echo $form['stage_expired_movement_decline']->render(array('class' => 'form-control')) ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Change Application Number'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['change_identifier']->renderError() ?>
					<?php echo $form['change_identifier']->render(array('class' => 'form-control', 'onChange' => "if(this.value == '1'){ document.getElementById('application_number_area').style.display = 'block'; }else{ document.getElementById('application_number_area').style.display = 'none'; }")) ?>
				</div>
			</div>
			<div class="form-group" id="application_number_area" name="application_number_area" style="display: <?php echo ($change_application_number == 1) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label">Application Number Properties</i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('New Application Number depends on a condition been met?'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['change_identifier_condition']->renderError() ?>
							<?php echo $form['change_identifier_condition']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_identifier_condition').change(function() {
								var condition = $(this).val();
								if (condition == 1) {
									//Show fields
									$('#sub_menus_change_field_form').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value').parent().parent().prev().css('display', 'block');
									$('#sub_menus_conditional_identifier').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_1').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_1').parent().parent().prev().css('display', 'block');
									$('#sub_menus_conditional_identifier_1').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_2').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_2').parent().parent().prev().css('display', 'block');
									$('#sub_menus_conditional_identifier_2').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_3').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_3').parent().parent().prev().css('display', 'block');
									$('#sub_menus_conditional_identifier_3').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_4').parent().parent().css('display', 'block');
									$('#sub_menus_change_field_element_value_4').parent().parent().prev().css('display', 'block');
									$('#sub_menus_conditional_identifier_4').parent().parent().css('display', 'block');
									$('#well_condition').css('display', 'block');
								} else {
									$('#sub_menus_change_field_form').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value').parent().parent().prev().css('display', 'none');
									$('#sub_menus_conditional_identifier').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_1').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_1').parent().parent().prev().css('display', 'none');
									$('#sub_menus_conditional_identifier_1').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_2').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_2').parent().parent().prev().css('display', 'none');
									$('#sub_menus_conditional_identifier_2').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_3').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_3').parent().parent().prev().css('display', 'none');
									$('#sub_menus_conditional_identifier_3').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_4').parent().parent().css('display', 'none');
									$('#sub_menus_change_field_element_value_4').parent().parent().prev().css('display', 'none');
									$('#sub_menus_conditional_identifier_4').parent().parent().css('display', 'none');
									$('#well_condition').css('display', 'none');
								}
							});
							<?php if (!$form->getObject()->isNew()) : ?>
								$('#sub_menus_change_identifier_condition').trigger('change');
							<?php endif; ?>
						});
					</script>
					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Default New Application Number Identifier e.g. CPF-'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['new_identifier']->renderError() ?>
							<?php echo $form['new_identifier']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_form']->renderLabel('Form to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_form']->renderError() ?>
							<?php echo $form['change_field_form']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_field_form').change(function() {
								var form = $(this).val();
								$.ajax({
									url: '<?php echo url_for('/backend.php/usercategories/updatememeberfields') ?>',
									data: {
										form: form
									},
									type: 'post',
									dataType: 'json',
								}).done(function(resp) {
									$('#sub_menus_change_field_element').children().remove();
									$('#sub_menus_change_field_element_value').children().remove();
									$('#sub_menus_change_field_element_value_1').children().remove();
									$('#sub_menus_change_field_element_value_2').children().remove();
									$('#sub_menus_change_field_element_value_3').children().remove();
									$('#sub_menus_change_field_element_value_4').children().remove();
									$.each(resp.all, function(i, x) {
										$('#sub_menus_change_field_element').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element').val('<?php echo $form->getObject()->getChangeFieldElement() ?>');
									<?php endif; ?>
									$('#sub_menus_change_field_element').trigger('change');
								}).fail(function(xhr, status, errorThrown) {
									//alert( "Sorry, there was a problem!" );
									console.log("Error: " + errorThrown);
									console.log("Status: " + status);
									console.dir(xhr);
								});
							}).trigger('change');
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_element']->renderLabel('Form field to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_element']->renderError() ?>
							<?php echo $form['change_field_element']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$('#sub_menus_change_field_element').change(function() {
							var field = $(this).val();
							var form = $('#sub_menus_change_field_form').val();
							$.ajax({
								url: '<?php echo url_for('/backend.php/usercategories/elementvalues') ?>',
								data: {
									form: form,
									element: field
								},
								type: 'post',
								dataType: 'json',
							}).done(function(resp) {
								$('#element_val').children().remove();
								$('#element_val_1').children().remove();
								$('#element_val_2').children().remove();
								$('#element_val_3').children().remove();
								$('#element_val_4').children().remove();
								//console.log('--------'+resp.elements.length);
								if (resp.elements.length > 1) {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val').append('<select class="form-control" id="sub_menus_change_field_element_value" name="sub_menus[change_field_element_value]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value').val(<?php echo $form->getObject()->getChangeFieldElementValue() ?>);
									<?php endif; ?>
									$('#element_val_1').append('<select class="form-control" id="sub_menus_change_field_element_value_1" name="sub_menus[change_field_element_value_1]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1').val(<?php echo $form->getObject()->getChangeFieldElementValue_1() ?>);
									<?php endif; ?>
									$('#element_val_2').append('<select class="form-control" id="sub_menus_change_field_element_value_2" name="sub_menus[change_field_element_value_2]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2').val(<?php echo $form->getObject()->getChangeFieldElementValue_2() ?>);
									<?php endif; ?>
									$('#element_val_3').append('<select class="form-control" id="sub_menus_change_field_element_value_3" name="sub_menus[change_field_element_value_3]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3').val(<?php echo $form->getObject()->getChangeFieldElementValue_3() ?>);
									<?php endif; ?>
									$('#element_val_4').append('<select class="form-control" id="sub_menus_change_field_element_value_4" name="sub_menus[change_field_element_value_4]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4').val(<?php echo $form->getObject()->getChangeFieldElementValue_4() ?>);
									<?php endif; ?>
								} else {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value" name="sub_menus[change_field_element_value]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value').val('<?php echo $form->getObject()->getChangeFieldElementValue() ?>');
									<?php endif; ?>
									$('#element_val_1').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_1" name="sub_menus[change_field_element_value_1]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1').val('<?php echo $form->getObject()->getChangeFieldElementValue_1() ?>');
									<?php endif; ?>
									$('#element_val_2').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_2" name="sub_menus[change_field_element_value_2]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2').val('<?php echo $form->getObject()->getChangeFieldElementValue_2() ?>');
									<?php endif; ?>
									$('#element_val_3').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_3" name="sub_menus[change_field_element_value_3]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3').val('<?php echo $form->getObject()->getChangeFieldElementValue_3() ?>');
									<?php endif; ?>
									$('#element_val_4').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_4" name="sub_menus[change_field_element_value_4]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4').val('<?php echo $form->getObject()->getChangeFieldElementValue_4() ?>');
									<?php endif; ?>
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					</script>
					<div class="well" id="well_condition" style="display:none">
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 1') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val">
								<?php echo $form['change_field_element_value']->renderError() ?>
								<?php echo $form['change_field_element_value']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier']->renderLabel(null, array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier']->renderError() ?>
								<?php echo $form['conditional_identifier']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 2') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_1']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_1">
								<?php echo $form['change_field_element_value_1']->renderError() ?>
								<?php echo $form['change_field_element_value_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_1']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_1']->renderError() ?>
								<?php echo $form['conditional_identifier_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 3') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_2']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_2">
								<?php echo $form['change_field_element_value_2']->renderError() ?>
								<?php echo $form['change_field_element_value_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_2']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_2']->renderError() ?>
								<?php echo $form['conditional_identifier_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 4') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_3']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_3']->renderError() ?>
								<?php echo $form['change_field_element_value_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_3']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_3']->renderError() ?>
								<?php echo $form['conditional_identifier_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 5') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_4']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_4']->renderError() ?>
								<?php echo $form['change_field_element_value_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_4']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_4']->renderError() ?>
								<?php echo $form['conditional_identifier_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<button class="btn btn-info" id="add_form_condition">Add form</button>
						</div>
					</div>
					<script>
						$(function() {
							$('#add_form_condition').click(function(e) {
								$(this).parent().parent().remove();
								$('#sub_menus_change_field_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_1_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_1_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_2_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_2_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_3_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_3_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_4_form_1').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_4_form_1').parent().parent().css('display', 'block');
								$('#well_condition_form_1').css('display', 'block');
								return false;
							});
							<?php if (!$form->getObject()->isNew() && $form->getObject()->getChangeFieldForm_1()) : ?>
								$('#add_form_condition').trigger('click');
								$('#sub_menus_change_field_form_1').trigger('change');
							<?php endif; ?>
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_form_1']->renderLabel('Form to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_form_1']->renderError() ?>
							<?php echo $form['change_field_form_1']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_field_form_1').change(function() {
								var form = $(this).val();
								$.ajax({
									url: '<?php echo url_for('/backend.php/usercategories/updatememeberfields') ?>',
									data: {
										form: form
									},
									type: 'post',
									dataType: 'json',
								}).done(function(resp) {
									$('#sub_menus_change_field_element_form_1').children().remove();
									$('#sub_menus_change_field_element_value_form_1').children().remove();
									$('#sub_menus_change_field_element_value_1_form_1').children().remove();
									$('#sub_menus_change_field_element_value_2_form_1').children().remove();
									$('#sub_menus_change_field_element_value_3_form_1').children().remove();
									$('#sub_menus_change_field_element_value_4_form_1').children().remove();
									$.each(resp.all, function(i, x) {
										$('#sub_menus_change_field_element_form_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_form_1').val('<?php echo $form->getObject()->getChangeFieldElementForm_1() ?>');
									<?php endif; ?>
									$('#sub_menus_change_field_element_form_1').trigger('change');
								}).fail(function(xhr, status, errorThrown) {
									//alert( "Sorry, there was a problem!" );
									console.log("Error: " + errorThrown);
									console.log("Status: " + status);
									console.dir(xhr);
								});
							}).trigger('change');
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_element_form_1']->renderLabel('Form field to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_element_form_1']->renderError() ?>
							<?php echo $form['change_field_element_form_1']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$('#sub_menus_change_field_element_form_1').change(function() {
							var field = $(this).val();
							var form = $('#sub_menus_change_field_form_1').val();
							$.ajax({
								url: '<?php echo url_for('/backend.php/usercategories/elementvalues') ?>',
								data: {
									form: form,
									element: field
								},
								type: 'post',
								dataType: 'json',
							}).done(function(resp) {
								$('#element_val_form_1').children().remove();
								$('#element_val_1_form_1').children().remove();
								$('#element_val_2_form_1').children().remove();
								$('#element_val_3_form_1').children().remove();
								$('#element_val_4_form_1').children().remove();
								//console.log('--------'+resp.elements.length);
								if (resp.elements.length > 1) {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_1').append('<select class="form-control" id="sub_menus_change_field_element_value_form_1" name="sub_menus[change_field_element_value_form_1]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_form_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_1').val(<?php echo $form->getObject()->getChangeFieldElementValueForm_1() ?>);
									<?php endif; ?>
									$('#element_val_1_form_1').append('<select class="form-control" id="sub_menus_change_field_element_value_1_form_1" name="sub_menus[change_field_element_value_1_form_1]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_1_form_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_1').val(<?php echo $form->getObject()->getChangeFieldElementValue_1Form_1() ?>);
									<?php endif; ?>
									$('#element_val_2_form_1').append('<select class="form-control" id="sub_menus_change_field_element_value_2_form_1" name="sub_menus[change_field_element_value_2_form_1]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_2_form_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_1').val(<?php echo $form->getObject()->getChangeFieldElementValue_2Form_1() ?>);
									<?php endif; ?>
									$('#element_val_3_form_1').append('<select class="form-control" id="sub_menus_change_field_element_value_3_form_1" name="sub_menus[change_field_element_value_3_form_1]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_3_form_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_1').val(<?php echo $form->getObject()->getChangeFieldElementValue_3Form_1() ?>);
									<?php endif; ?>
									$('#element_val_4_form_1').append('<select class="form-control" id="sub_menus_change_field_element_value_4_form_1" name="sub_menus[change_field_element_value_4_form_1]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_4_form_1').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_1').val(<?php echo $form->getObject()->getChangeFieldElementValue_4Form_1() ?>);
									<?php endif; ?>
								} else {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_1').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_form_1" name="sub_menus[change_field_element_value_form_1]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_1').val('<?php echo $form->getObject()->getChangeFieldElementValueForm_1() ?>');
									<?php endif; ?>
									$('#element_val_1_form_1').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_1_form_1" name="sub_menus[change_field_element_value_1_form_1]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_1').val('<?php echo $form->getObject()->getChangeFieldElementValue_1Form_1() ?>');
									<?php endif; ?>
									$('#element_val_2_form_1').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_2_form_1" name="sub_menus[change_field_element_value_2_form_1]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_1').val('<?php echo $form->getObject()->getChangeFieldElementValue_2Form_1() ?>');
									<?php endif; ?>
									$('#element_val_3_form_1').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_3_form_1" name="sub_menus[change_field_element_value_3_form_1]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_1').val('<?php echo $form->getObject()->getChangeFieldElementValue_3Form_1() ?>');
									<?php endif; ?>
									$('#element_val_4_form_1').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_4_form_1" name="sub_menus[change_field_element_value_4_form_1]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_1').val('<?php echo $form->getObject()->getChangeFieldElementValue_4Form_1() ?>');
									<?php endif; ?>
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					</script>
					<div class="well" id="well_condition_form_1" style="display:none">
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 1') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_form_1']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val">
								<?php echo $form['change_field_element_value_form_1']->renderError() ?>
								<?php echo $form['change_field_element_value_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_form_1']->renderLabel(null, array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_form_1']->renderError() ?>
								<?php echo $form['conditional_identifier_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 2') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_1_form_1']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_1">
								<?php echo $form['change_field_element_value_1_form_1']->renderError() ?>
								<?php echo $form['change_field_element_value_1_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_1_form_1']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_1_form_1']->renderError() ?>
								<?php echo $form['conditional_identifier_1_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 3') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_2_form_1']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_2">
								<?php echo $form['change_field_element_value_2_form_1']->renderError() ?>
								<?php echo $form['change_field_element_value_2_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_2_form_1']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_2_form_1']->renderError() ?>
								<?php echo $form['conditional_identifier_2_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 4') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_3_form_1']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_3_form_1']->renderError() ?>
								<?php echo $form['change_field_element_value_3_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_3_form_1']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_3_form_1']->renderError() ?>
								<?php echo $form['conditional_identifier_3_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 5') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_4_form_1']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_4_form_1']->renderError() ?>
								<?php echo $form['change_field_element_value_4_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_4_form_1']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_4_form_1']->renderError() ?>
								<?php echo $form['conditional_identifier_4_form_1']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<button class="btn btn-info" id="add_form_condition_1">Add form</button>
						</div>
					</div>
					<script>
						$(function() {
							$('#add_form_condition_1').click(function(e) {
								$(this).parent().parent().remove();
								$('#sub_menus_change_field_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_1_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_1_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_2_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_2_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_3_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_3_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_4_form_2').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_4_form_2').parent().parent().css('display', 'block');
								$('#well_condition_form_2').css('display', 'block');
								return false;
							});
							<?php if (!$form->getObject()->isNew() && $form->getObject()->getChangeFieldForm_2()) : ?>
								$('#add_form_condition_1').trigger('click');
								$('#sub_menus_change_field_form_2').trigger('change');
							<?php endif; ?>
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_form_2']->renderLabel('Form to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_form_2']->renderError() ?>
							<?php echo $form['change_field_form_2']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_field_form_2').change(function() {
								var form = $(this).val();
								$.ajax({
									url: '<?php echo url_for('/backend.php/usercategories/updatememeberfields') ?>',
									data: {
										form: form
									},
									type: 'post',
									dataType: 'json',
								}).done(function(resp) {
									$('#sub_menus_change_field_element_form_2').children().remove();
									$('#sub_menus_change_field_element_value_form_2').children().remove();
									$('#sub_menus_change_field_element_value_1_form_2').children().remove();
									$('#sub_menus_change_field_element_value_2_form_2').children().remove();
									$('#sub_menus_change_field_element_value_3_form_2').children().remove();
									$('#sub_menus_change_field_element_value_4_form_2').children().remove();
									$.each(resp.all, function(i, x) {
										$('#sub_menus_change_field_element_form_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_form_2').val('<?php echo $form->getObject()->getChangeFieldElementForm_2() ?>');
									<?php endif; ?>
									$('#sub_menus_change_field_element_form_2').trigger('change');
								}).fail(function(xhr, status, errorThrown) {
									//alert( "Sorry, there was a problem!" );
									console.log("Error: " + errorThrown);
									console.log("Status: " + status);
									console.dir(xhr);
								});
							}).trigger('change');
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_element_form_2']->renderLabel('Form field to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_element_form_2']->renderError() ?>
							<?php echo $form['change_field_element_form_2']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$('#sub_menus_change_field_element_form_2').change(function() {
							var field = $(this).val();
							var form = $('#sub_menus_change_field_form_2').val();
							$.ajax({
								url: '<?php echo url_for('/backend.php/usercategories/elementvalues') ?>',
								data: {
									form: form,
									element: field
								},
								type: 'post',
								dataType: 'json',
							}).done(function(resp) {
								$('#element_val_form_2').children().remove();
								$('#element_val_1_form_2').children().remove();
								$('#element_val_2_form_2').children().remove();
								$('#element_val_3_form_2').children().remove();
								$('#element_val_4_form_2').children().remove();
								//console.log('--------'+resp.elements.length);
								if (resp.elements.length > 1) {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_2').append('<select class="form-control" id="sub_menus_change_field_element_value_form_2" name="sub_menus[change_field_element_value_form_2]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_form_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_2').val(<?php echo $form->getObject()->getChangeFieldElementValueForm_2() ?>);
									<?php endif; ?>
									$('#element_val_1_form_2').append('<select class="form-control" id="sub_menus_change_field_element_value_1_form_2" name="sub_menus[change_field_element_value_1_form_2]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_1_form_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_2').val(<?php echo $form->getObject()->getChangeFieldElementValue_1Form_2() ?>);
									<?php endif; ?>
									$('#element_val_2_form_2').append('<select class="form-control" id="sub_menus_change_field_element_value_2_form_2" name="sub_menus[change_field_element_value_2_form_2]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_2_form_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_2').val(<?php echo $form->getObject()->getChangeFieldElementValue_2Form_2() ?>);
									<?php endif; ?>
									$('#element_val_3_form_2').append('<select class="form-control" id="sub_menus_change_field_element_value_3_form_2" name="sub_menus[change_field_element_value_3_form_2]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_3_form_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_2').val(<?php echo $form->getObject()->getChangeFieldElementValue_3Form_2() ?>);
									<?php endif; ?>
									$('#element_val_4_form_2').append('<select class="form-control" id="sub_menus_change_field_element_value_4_form_2" name="sub_menus[change_field_element_value_4_form_2]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_4_form_2').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_2').val(<?php echo $form->getObject()->getChangeFieldElementValue_4Form_2() ?>);
									<?php endif; ?>
								} else {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_2').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_form_2" name="sub_menus[change_field_element_value_form_2]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_2').val('<?php echo $form->getObject()->getChangeFieldElementValueForm_2() ?>');
									<?php endif; ?>
									$('#element_val_1_form_2').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_1_form_2" name="sub_menus[change_field_element_value_1_form_2]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_2').val('<?php echo $form->getObject()->getChangeFieldElementValue_1Form_2() ?>');
									<?php endif; ?>
									$('#element_val_2_form_2').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_2_form_2" name="sub_menus[change_field_element_value_2_form_2]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_2').val('<?php echo $form->getObject()->getChangeFieldElementValue_2Form_2() ?>');
									<?php endif; ?>
									$('#element_val_3_form_2').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_3_form_2" name="sub_menus[change_field_element_value_3_form_2]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_2').val('<?php echo $form->getObject()->getChangeFieldElementValue_3Form_2() ?>');
									<?php endif; ?>
									$('#element_val_4_form_2').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_4_form_2" name="sub_menus[change_field_element_value_4_form_2]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_2').val('<?php echo $form->getObject()->getChangeFieldElementValue_4Form_2() ?>');
									<?php endif; ?>
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					</script>
					<div class="well" id="well_condition_form_2" style="display:none">
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 1') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_form_2']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val">
								<?php echo $form['change_field_element_value_form_2']->renderError() ?>
								<?php echo $form['change_field_element_value_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_form_2']->renderLabel(null, array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_form_2']->renderError() ?>
								<?php echo $form['conditional_identifier_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 2') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_1_form_2']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_1">
								<?php echo $form['change_field_element_value_1_form_2']->renderError() ?>
								<?php echo $form['change_field_element_value_1_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_1_form_2']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_1_form_2']->renderError() ?>
								<?php echo $form['conditional_identifier_1_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 3') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_2_form_2']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_2">
								<?php echo $form['change_field_element_value_2_form_2']->renderError() ?>
								<?php echo $form['change_field_element_value_2_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_2_form_2']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_2_form_2']->renderError() ?>
								<?php echo $form['conditional_identifier_2_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 4') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_3_form_2']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_3_form_2']->renderError() ?>
								<?php echo $form['change_field_element_value_3_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_3_form_2']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_3_form_2']->renderError() ?>
								<?php echo $form['conditional_identifier_3_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 5') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_4_form_2']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_4_form_2']->renderError() ?>
								<?php echo $form['change_field_element_value_4_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_4_form_2']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_4_form_2']->renderError() ?>
								<?php echo $form['conditional_identifier_4_form_2']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<button class="btn btn-info" id="add_form_condition_2">Add form</button>
						</div>
					</div>
					<script>
						$(function() {
							$('#add_form_condition_2').click(function(e) {
								$(this).parent().parent().remove();
								$('#sub_menus_change_field_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_1_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_1_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_2_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_2_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_3_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_3_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_4_form_3').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_4_form_3').parent().parent().css('display', 'block');
								$('#well_condition_form_3').css('display', 'block');
								return false;
							});
							<?php if (!$form->getObject()->isNew() && $form->getObject()->getChangeFieldForm_3()) : ?>
								$('#add_form_condition_2').trigger('click');
								$('#sub_menus_change_field_form_3').trigger('change');
							<?php endif; ?>
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_form_3']->renderLabel('Form to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_form_3']->renderError() ?>
							<?php echo $form['change_field_form_3']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_field_form_3').change(function() {
								var form = $(this).val();
								$.ajax({
									url: '<?php echo url_for('/backend.php/usercategories/updatememeberfields') ?>',
									data: {
										form: form
									},
									type: 'post',
									dataType: 'json',
								}).done(function(resp) {
									$('#sub_menus_change_field_element_form_3').children().remove();
									$('#sub_menus_change_field_element_value_form_3').children().remove();
									$('#sub_menus_change_field_element_value_1_form_3').children().remove();
									$('#sub_menus_change_field_element_value_2_form_3').children().remove();
									$('#sub_menus_change_field_element_value_3_form_3').children().remove();
									$('#sub_menus_change_field_element_value_4_form_3').children().remove();
									$.each(resp.all, function(i, x) {
										$('#sub_menus_change_field_element_form_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_form_3').val('<?php echo $form->getObject()->getChangeFieldElementForm_3() ?>');
									<?php endif; ?>
									$('#sub_menus_change_field_element_form_3').trigger('change');
								}).fail(function(xhr, status, errorThrown) {
									//alert( "Sorry, there was a problem!" );
									console.log("Error: " + errorThrown);
									console.log("Status: " + status);
									console.dir(xhr);
								});
							}).trigger('change');
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_element_form_3']->renderLabel('Form field to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_element_form_3']->renderError() ?>
							<?php echo $form['change_field_element_form_3']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$('#sub_menus_change_field_element_form_3').change(function() {
							var field = $(this).val();
							var form = $('#sub_menus_change_field_form_3').val();
							$.ajax({
								url: '<?php echo url_for('/backend.php/usercategories/elementvalues') ?>',
								data: {
									form: form,
									element: field
								},
								type: 'post',
								dataType: 'json',
							}).done(function(resp) {
								$('#element_val_form_3').children().remove();
								$('#element_val_1_form_3').children().remove();
								$('#element_val_2_form_3').children().remove();
								$('#element_val_3_form_3').children().remove();
								$('#element_val_4_form_3').children().remove();
								//console.log('--------'+resp.elements.length);
								if (resp.elements.length > 1) {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_3').append('<select class="form-control" id="sub_menus_change_field_element_value_form_3" name="sub_menus[change_field_element_value_form_3]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_form_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_3').val(<?php echo $form->getObject()->getChangeFieldElementValueForm_3() ?>);
									<?php endif; ?>
									$('#element_val_1_form_3').append('<select class="form-control" id="sub_menus_change_field_element_value_1_form_3" name="sub_menus[change_field_element_value_1_form_3]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_1_form_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_3').val(<?php echo $form->getObject()->getChangeFieldElementValue_1Form_3() ?>);
									<?php endif; ?>
									$('#element_val_2_form_3').append('<select class="form-control" id="sub_menus_change_field_element_value_2_form_3" name="sub_menus[change_field_element_value_2_form_3]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_2_form_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_3').val(<?php echo $form->getObject()->getChangeFieldElementValue_2Form_3() ?>);
									<?php endif; ?>
									$('#element_val_3_form_3').append('<select class="form-control" id="sub_menus_change_field_element_value_3_form_3" name="sub_menus[change_field_element_value_3_form_3]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_3_form_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_3').val(<?php echo $form->getObject()->getChangeFieldElementValue_3Form_3() ?>);
									<?php endif; ?>
									$('#element_val_4_form_3').append('<select class="form-control" id="sub_menus_change_field_element_value_4_form_3" name="sub_menus[change_field_element_value_4_form_3]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_4_form_3').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_3').val(<?php echo $form->getObject()->getChangeFieldElementValue_4Form_3() ?>);
									<?php endif; ?>
								} else {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_3').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_form_3" name="sub_menus[change_field_element_value_form_3]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_3').val('<?php echo $form->getObject()->getChangeFieldElementValueForm_3() ?>');
									<?php endif; ?>
									$('#element_val_1_form_3').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_1_form_3" name="sub_menus[change_field_element_value_1_form_3]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_3').val('<?php echo $form->getObject()->getChangeFieldElementValue_1Form_3() ?>');
									<?php endif; ?>
									$('#element_val_2_form_3').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_2_form_3" name="sub_menus[change_field_element_value_2_form_3]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_3').val('<?php echo $form->getObject()->getChangeFieldElementValue_2Form_3() ?>');
									<?php endif; ?>
									$('#element_val_3_form_3').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_3_form_3" name="sub_menus[change_field_element_value_3_form_3]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_3').val('<?php echo $form->getObject()->getChangeFieldElementValue_3Form_3() ?>');
									<?php endif; ?>
									$('#element_val_4_form_3').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_4_form_3" name="sub_menus[change_field_element_value_4_form_3]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_3').val('<?php echo $form->getObject()->getChangeFieldElementValue_4Form_3() ?>');
									<?php endif; ?>
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					</script>
					<div class="well" id="well_condition_form_3" style="display:none">
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 1') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_form_3']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val">
								<?php echo $form['change_field_element_value_form_3']->renderError() ?>
								<?php echo $form['change_field_element_value_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_form_3']->renderLabel(null, array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_form_3']->renderError() ?>
								<?php echo $form['conditional_identifier_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 2') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_1_form_3']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_1">
								<?php echo $form['change_field_element_value_1_form_3']->renderError() ?>
								<?php echo $form['change_field_element_value_1_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_1_form_3']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_1_form_3']->renderError() ?>
								<?php echo $form['conditional_identifier_1_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 3') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_2_form_3']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_2">
								<?php echo $form['change_field_element_value_2_form_3']->renderError() ?>
								<?php echo $form['change_field_element_value_2_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_2_form_3']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_2_form_3']->renderError() ?>
								<?php echo $form['conditional_identifier_2_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 4') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_3_form_3']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_3_form_3']->renderError() ?>
								<?php echo $form['change_field_element_value_3_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_3_form_3']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_3_form_3']->renderError() ?>
								<?php echo $form['conditional_identifier_3_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 5') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_4_form_3']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_4_form_3']->renderError() ?>
								<?php echo $form['change_field_element_value_4_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_4_form_3']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_4_form_3']->renderError() ?>
								<?php echo $form['conditional_identifier_4_form_3']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<button class="btn btn-info" id="add_form_condition_3">Add form</button>
						</div>
					</div>
					<script>
						$(function() {
							$('#add_form_condition_3').click(function(e) {
								$(this).parent().parent().remove();
								$('#sub_menus_change_field_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_1_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_1_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_2_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_2_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_3_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_3_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_4_form_4').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_4_form_4').parent().parent().css('display', 'block');
								$('#well_condition_form_4').css('display', 'block');
								return false;
							});
							<?php if (!$form->getObject()->isNew() && $form->getObject()->getChangeFieldForm_4()) : ?>
								$('#add_form_condition_3').trigger('click');
								$('#sub_menus_change_field_form_4').trigger('change');
							<?php endif; ?>
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_form_4']->renderLabel('Form to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_form_4']->renderError() ?>
							<?php echo $form['change_field_form_4']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_field_form_4').change(function() {
								var form = $(this).val();
								$.ajax({
									url: '<?php echo url_for('/backend.php/usercategories/updatememeberfields') ?>',
									data: {
										form: form
									},
									type: 'post',
									dataType: 'json',
								}).done(function(resp) {
									$('#sub_menus_change_field_element_form_4').children().remove();
									$('#sub_menus_change_field_element_value_form_4').children().remove();
									$('#sub_menus_change_field_element_value_1_form_4').children().remove();
									$('#sub_menus_change_field_element_value_2_form_4').children().remove();
									$('#sub_menus_change_field_element_value_3_form_4').children().remove();
									$('#sub_menus_change_field_element_value_4_form_4').children().remove();
									$.each(resp.all, function(i, x) {
										$('#sub_menus_change_field_element_form_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_form_4').val('<?php echo $form->getObject()->getChangeFieldElementForm_4() ?>');
									<?php endif; ?>
									$('#sub_menus_change_field_element_form_4').trigger('change');
								}).fail(function(xhr, status, errorThrown) {
									//alert( "Sorry, there was a problem!" );
									console.log("Error: " + errorThrown);
									console.log("Status: " + status);
									console.dir(xhr);
								});
							}).trigger('change');
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_element_form_4']->renderLabel('Form field to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_element_form_4']->renderError() ?>
							<?php echo $form['change_field_element_form_4']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$('#sub_menus_change_field_element_form_4').change(function() {
							var field = $(this).val();
							var form = $('#sub_menus_change_field_form_4').val();
							$.ajax({
								url: '<?php echo url_for('/backend.php/usercategories/elementvalues') ?>',
								data: {
									form: form,
									element: field
								},
								type: 'post',
								dataType: 'json',
							}).done(function(resp) {
								$('#element_val_form_4').children().remove();
								$('#element_val_1_form_4').children().remove();
								$('#element_val_2_form_4').children().remove();
								$('#element_val_3_form_4').children().remove();
								$('#element_val_4_form_4').children().remove();
								//console.log('--------'+resp.elements.length);
								if (resp.elements.length > 1) {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_4').append('<select class="form-control" id="sub_menus_change_field_element_value_form_4" name="sub_menus[change_field_element_value_form_4]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_form_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_4').val(<?php echo $form->getObject()->getChangeFieldElementValueForm_4() ?>);
									<?php endif; ?>
									$('#element_val_1_form_4').append('<select class="form-control" id="sub_menus_change_field_element_value_1_form_4" name="sub_menus[change_field_element_value_1_form_4]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_1_form_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_4').val(<?php echo $form->getObject()->getChangeFieldElementValue_1Form_4() ?>);
									<?php endif; ?>
									$('#element_val_2_form_4').append('<select class="form-control" id="sub_menus_change_field_element_value_2_form_4" name="sub_menus[change_field_element_value_2_form_4]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_2_form_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_4').val(<?php echo $form->getObject()->getChangeFieldElementValue_2Form_4() ?>);
									<?php endif; ?>
									$('#element_val_3_form_4').append('<select class="form-control" id="sub_menus_change_field_element_value_3_form_4" name="sub_menus[change_field_element_value_3_form_4]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_3_form_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_4').val(<?php echo $form->getObject()->getChangeFieldElementValue_3Form_4() ?>);
									<?php endif; ?>
									$('#element_val_4_form_4').append('<select class="form-control" id="sub_menus_change_field_element_value_4_form_4" name="sub_menus[change_field_element_value_4_form_4]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_4_form_4').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_4').val(<?php echo $form->getObject()->getChangeFieldElementValue_4Form_4() ?>);
									<?php endif; ?>
								} else {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_4').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_form_4" name="sub_menus[change_field_element_value_form_4]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_4').val('<?php echo $form->getObject()->getChangeFieldElementValueForm_4() ?>');
									<?php endif; ?>
									$('#element_val_1_form_4').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_1_form_4" name="sub_menus[change_field_element_value_1_form_4]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_4').val('<?php echo $form->getObject()->getChangeFieldElementValue_1Form_4() ?>');
									<?php endif; ?>
									$('#element_val_2_form_4').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_2_form_4" name="sub_menus[change_field_element_value_2_form_4]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_4').val('<?php echo $form->getObject()->getChangeFieldElementValue_2Form_4() ?>');
									<?php endif; ?>
									$('#element_val_3_form_4').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_3_form_4" name="sub_menus[change_field_element_value_3_form_4]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_4').val('<?php echo $form->getObject()->getChangeFieldElementValue_3Form_4() ?>');
									<?php endif; ?>
									$('#element_val_4_form_4').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_4_form_4" name="sub_menus[change_field_element_value_4_form_4]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_4').val('<?php echo $form->getObject()->getChangeFieldElementValue_4Form_4() ?>');
									<?php endif; ?>
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					</script>
					<div class="well" id="well_condition_form_4" style="display:none">
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 1') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_form_4']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val">
								<?php echo $form['change_field_element_value_form_4']->renderError() ?>
								<?php echo $form['change_field_element_value_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_form_4']->renderLabel(null, array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_form_4']->renderError() ?>
								<?php echo $form['conditional_identifier_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 2') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_1_form_4']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_1">
								<?php echo $form['change_field_element_value_1_form_4']->renderError() ?>
								<?php echo $form['change_field_element_value_1_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_1_form_4']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_1_form_4']->renderError() ?>
								<?php echo $form['conditional_identifier_1_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 3') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_2_form_4']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_2">
								<?php echo $form['change_field_element_value_2_form_4']->renderError() ?>
								<?php echo $form['change_field_element_value_2_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_2_form_4']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_2_form_4']->renderError() ?>
								<?php echo $form['conditional_identifier_2_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 4') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_3_form_4']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_3_form_4']->renderError() ?>
								<?php echo $form['change_field_element_value_3_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_3_form_4']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_3_form_4']->renderError() ?>
								<?php echo $form['conditional_identifier_3_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 5') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_4_form_4']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_4_form_4']->renderError() ?>
								<?php echo $form['change_field_element_value_4_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_4_form_4']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_4_form_4']->renderError() ?>
								<?php echo $form['conditional_identifier_4_form_4']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<button class="btn btn-info" id="add_form_condition_4">Add form</button>
						</div>
					</div>
					<script>
						$(function() {
							$('#add_form_condition_4').click(function(e) {
								$(this).parent().parent().remove();
								$('#sub_menus_change_field_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_1_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_1_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_2_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_2_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_3_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_3_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_change_field_element_value_4_form_5').parent().parent().css('display', 'block');
								$('#sub_menus_conditional_identifier_4_form_5').parent().parent().css('display', 'block');
								$('#well_condition_form_5').css('display', 'block');
								return false;
							});
							<?php if (!$form->getObject()->isNew() && $form->getObject()->getChangeFieldForm_5()) : ?>
								$('#add_form_condition_4').trigger('click');
								$('#sub_menus_change_field_form_5').trigger('change');
							<?php endif; ?>
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_form_5']->renderLabel('Form to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_form_5']->renderError() ?>
							<?php echo $form['change_field_form_5']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$(function() {
							$('#sub_menus_change_field_form_5').change(function() {
								var form = $(this).val();
								$.ajax({
									url: '<?php echo url_for('/backend.php/usercategories/updatememeberfields') ?>',
									data: {
										form: form
									},
									type: 'post',
									dataType: 'json',
								}).done(function(resp) {
									$('#sub_menus_change_field_element_form_5').children().remove();
									$('#sub_menus_change_field_element_value_form_5').children().remove();
									$('#sub_menus_change_field_element_value_1_form_5').children().remove();
									$('#sub_menus_change_field_element_value_2_form_5').children().remove();
									$('#sub_menus_change_field_element_value_3_form_5').children().remove();
									$('#sub_menus_change_field_element_value_4_form_5').children().remove();
									$.each(resp.all, function(i, x) {
										$('#sub_menus_change_field_element_form_5').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_form_5').val('<?php echo $form->getObject()->getChangeFieldElementForm_5() ?>');
									<?php endif; ?>
									$('#sub_menus_change_field_element_form_5').trigger('change');
								}).fail(function(xhr, status, errorThrown) {
									//alert( "Sorry, there was a problem!" );
									console.log("Error: " + errorThrown);
									console.log("Status: " + status);
									console.dir(xhr);
								});
							}).trigger('change');
						});
					</script>
					<div class="form-group" style="display:none">
						<?php echo $form['change_field_element_form_5']->renderLabel('Form field to use to apply condition:', array('class' => "col-sm-4")) ?>
						<div class="col-sm-8">
							<?php echo $form['change_field_element_form_5']->renderError() ?>
							<?php echo $form['change_field_element_form_5']->render(array('class' => 'form-control')) ?>
						</div>
					</div>
					<script>
						$('#sub_menus_change_field_element_form_5').change(function() {
							var field = $(this).val();
							var form = $('#sub_menus_change_field_form_5').val();
							$.ajax({
								url: '<?php echo url_for('/backend.php/usercategories/elementvalues') ?>',
								data: {
									form: form,
									element: field
								},
								type: 'post',
								dataType: 'json',
							}).done(function(resp) {
								$('#element_val_form_5').children().remove();
								$('#element_val_1_form_5').children().remove();
								$('#element_val_2_form_5').children().remove();
								$('#element_val_3_form_5').children().remove();
								$('#element_val_4_form_5').children().remove();
								//console.log('--------'+resp.elements.length);
								if (resp.elements.length > 1) {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_5').append('<select class="form-control" id="sub_menus_change_field_element_value_form_5" name="sub_menus[change_field_element_value_form_5]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_form_5').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_5').val(<?php echo $form->getObject()->getChangeFieldElementValueForm_5() ?>);
									<?php endif; ?>
									$('#element_val_1_form_5').append('<select class="form-control" id="sub_menus_change_field_element_value_1_form_5" name="sub_menus[change_field_element_value_1_form_5]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_1_form_5').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_5').val(<?php echo $form->getObject()->getChangeFieldElementValue_1Form_5() ?>);
									<?php endif; ?>
									$('#element_val_2_form_5').append('<select class="form-control" id="sub_menus_change_field_element_value_2_form_5" name="sub_menus[change_field_element_value_2_form_5]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_2_form_5').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_5').val(<?php echo $form->getObject()->getChangeFieldElementValue_2Form_4() ?>);
									<?php endif; ?>
									$('#element_val_3_form_5').append('<select class="form-control" id="sub_menus_change_field_element_value_3_form_5" name="sub_menus[change_field_element_value_3_form_5]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_3_form_5').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_5').val(<?php echo $form->getObject()->getChangeFieldElementValue_3Form_5() ?>);
									<?php endif; ?>
									$('#element_val_4_form_5').append('<select class="form-control" id="sub_menus_change_field_element_value_4_form_5" name="sub_menus[change_field_element_value_4_form_5]"></select>');
									$.each(resp.elements, function(i, x) {
										$('#sub_menus_change_field_element_value_4_form_5').append('<option value="' + i + '">' + x + '</option>');
									});
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_5').val(<?php echo $form->getObject()->getChangeFieldElementValue_4Form_5() ?>);
									<?php endif; ?>
								} else {
									//$('#sub_menus_change_field_element_value').remove();
									$('#element_val_form_5').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_form_5" name="sub_menus[change_field_element_value_form_5]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_form_5').val('<?php echo $form->getObject()->getChangeFieldElementValueForm_5() ?>');
									<?php endif; ?>
									$('#element_val_1_form_5').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_1_form_5" name="sub_menus[change_field_element_value_1_form_5]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_1_form_5').val('<?php echo $form->getObject()->getChangeFieldElementValue_1Form_5() ?>');
									<?php endif; ?>
									$('#element_val_2_form_5').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_2_form_5" name="sub_menus[change_field_element_value_2_form_5]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_2_form_5').val('<?php echo $form->getObject()->getChangeFieldElementValue_2Form_5() ?>');
									<?php endif; ?>
									$('#element_val_3_form_5').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_3_form_5" name="sub_menus[change_field_element_value_3_form_5]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_3_form_5').val('<?php echo $form->getObject()->getChangeFieldElementValue_3Form_5() ?>');
									<?php endif; ?>
									$('#element_val_4_form_5').append('<input type="text" class="form-control" id="sub_menus_change_field_element_value_4_form_5" name="sub_menus[change_field_element_value_4_form_5]"/>');
									<?php if (!$form->getObject()->isNew()) : ?>
										$('#sub_menus_change_field_element_value_4_form_5').val('<?php echo $form->getObject()->getChangeFieldElementValue_4Form_5() ?>');
									<?php endif; ?>
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					</script>
					<div class="well" id="well_condition_form_5" style="display:none">
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 1') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_form_5']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val">
								<?php echo $form['change_field_element_value_form_5']->renderError() ?>
								<?php echo $form['change_field_element_value_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_form_5']->renderLabel(null, array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_form_5']->renderError() ?>
								<?php echo $form['conditional_identifier_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 2') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_1_form_5']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_1">
								<?php echo $form['change_field_element_value_1_form_5']->renderError() ?>
								<?php echo $form['change_field_element_value_1_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_1_form_5']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_1_form_5']->renderError() ?>
								<?php echo $form['conditional_identifier_1_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 3') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_2_form_5']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_2">
								<?php echo $form['change_field_element_value_2_form_5']->renderError() ?>
								<?php echo $form['change_field_element_value_2_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_2_form_5']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_2_form_5']->renderError() ?>
								<?php echo $form['conditional_identifier_2_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 4') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_3_form_5']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_3_form_5']->renderError() ?>
								<?php echo $form['change_field_element_value_3_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_3_form_5']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_3_form_5']->renderError() ?>
								<?php echo $form['conditional_identifier_3_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="alert alert-info" style="display:none"><?php echo __('Condition 5') ?></div>
						<div class="form-group" style="display:none">
							<?php echo $form['change_field_element_value_4_form_5']->renderLabel('Form field value to use to apply condition:', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8" id="element_val_3">
								<?php echo $form['change_field_element_value_4_form_5']->renderError() ?>
								<?php echo $form['change_field_element_value_4_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group" style="display:none">
							<?php echo $form['conditional_identifier_4_form_5']->renderLabel('Conditional identifier', array('class' => "col-sm-4")) ?>
							<div class="col-sm-8">
								<?php echo $form['conditional_identifier_4_form_5']->renderError() ?>
								<?php echo $form['conditional_identifier_4_form_5']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Application Number Starting Point e.g. AAA0001'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['new_identifier_start']->renderError() ?>
							<?php echo $form['new_identifier_start']->render(array('class' => 'form-control')) ?>
						</div>
					</div>

				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Application Queuing'); ?></i>
					<?php echo __('(Default behavior is to use workflow settings)') ?>
				</label>
				<div class="col-sm-8">
					<?php echo $form['app_queuing']->render(array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Distribute Tasks Equally'); ?></i>
					<?php echo __('(New tasks to assigned to reviewers with less tasks)') ?>
				</label>
				<div class="col-sm-8">
					<?php echo $form['distribute_equally']->render(array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Type of Stage'); ?></i></label>
				<div class="col-sm-8">
					<select id="stage_type" name="sub_menus[stage_type]" class="form-control" onChange="if(this.value == '2'){ document.getElementById('assessment_area').style.display = 'block'; document.getElementById('invoicing_area').style.display = 'none'; document.getElementById('corrections_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'none'; document.getElementById('renewals_area').style.display = 'none';document.getElementById('expired_area').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('invoicing_area').style.display = 'block'; document.getElementById('assessment_area').style.display = 'none'; document.getElementById('corrections_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'none'; document.getElementById('renewals_area').style.display = 'none';document.getElementById('expired_area').style.display = 'none'; }else if(this.value == '5'){ document.getElementById('corrections_area').style.display = 'block'; document.getElementById('assessment_area').style.display = 'none'; document.getElementById('invoicing_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'none'; document.getElementById('renewals_area').style.display = 'none';document.getElementById('expired_area').style.display = 'none'; }else if(this.value == '8'){ document.getElementById('corrections_area').style.display = 'none'; document.getElementById('assessment_area').style.display = 'none'; document.getElementById('invoicing_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'block'; document.getElementById('renewals_area').style.display = 'none';document.getElementById('expired_area').style.display = 'none'; }else if(this.value == '10'){ document.getElementById('corrections_area').style.display = 'none'; document.getElementById('assessment_area').style.display = 'none'; document.getElementById('invoicing_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'none'; document.getElementById('renewals_area').style.display = 'block';document.getElementById('expired_area').style.display = 'none'; }else if(this.value == '12'){
								document.getElementById('corrections_area').style.display = 'none'; document.getElementById('assessment_area').style.display = 'none'; document.getElementById('invoicing_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'none'; document.getElementById('renewals_area').style.display = 'none';document.getElementById('expired_area').style.display = 'block';								
								}else{ document.getElementById('assessment_area').style.display = 'none'; document.getElementById('invoicing_area').style.display = 'none'; document.getElementById('corrections_area').style.display = 'none'; document.getElementById('dispatch_area').style.display = 'none'; document.getElementById('renewals_area').style.display = 'none';document.getElementById('expired_area').style.display = 'none'; }">
						<option value='1' <?php echo ($stage_type == 1) ? 'selected="selected"' : '' ?>><?php echo __('Default'); ?></option>
						<option value='8' <?php echo ($stage_type == 8) ? 'selected="selected"' : '' ?>><?php echo __('Dispatch'); ?></option>
						<option value='2' <?php echo ($stage_type == 2) ? 'selected="selected"' : '' ?>><?php echo __('Assessment'); ?></option>
						<option value='3' <?php echo ($stage_type == 3) ? 'selected="selected"' : '' ?>><?php echo __('Payment'); ?></option>
						<option value='4' <?php echo ($stage_type == 4) ? 'selected="selected"' : '' ?>><?php echo __('Approved'); ?></option>
						<option value='5' <?php echo ($stage_type == 5) ? 'selected="selected"' : '' ?>><?php echo __('Corrections'); ?></option>
						<option value='6' <?php echo ($stage_type == 6) ? 'selected="selected"' : '' ?>><?php echo __('Rejected'); ?></option>
						<option value='7' <?php echo ($stage_type == 7) ? 'selected="selected"' : '' ?>><?php echo __('Archived'); ?></option>
						<option value='10' <?php echo ($stage_type == 10) ? 'selected="selected"' : '' ?>><?php echo __('Renewal'); ?></option>
						<option value='11' <?php echo ($stage_type == 11) ? 'selected="selected"' : '' ?>><?php echo __('Agenda'); ?></option>
						<option value='12' <?php echo ($stage_type == 12) ? 'selected="selected"' : '' ?>><?php echo __('Expired'); ?></option>
					</select>
				</div>
			</div>
			<div class="form-group" id="dispatch_area" name="dispatch_area" style="display: <?php echo ($stage_type == 8) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label">Dispatch Properties</i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('What to do when all tasks have been dispatched to reviewers?'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_property']->renderError() ?>
							<select id="dispatch_properties" name="dispatch_properties" class="form-control" onChange="if(this.value == '2'){ document.getElementById('dispatch_properties_stage').style.display = 'block'; document.getElementById('dispatch_properties_notification').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('dispatch_properties_notification').style.display = 'block'; document.getElementById('dispatch_properties_stage').style.display = 'none'; }else{ document.getElementById('dispatch_properties_stage').style.display = 'none'; document.getElementById('dispatch_properties_notification').style.display = 'none';  }">
								<option value='1' <?php echo ($stage_property == 1) ? 'selected="selected"' : '' ?>><?php echo __('Do Nothing'); ?></option>
								<option value='2' <?php echo ($stage_property == 2) ? 'selected="selected"' : '' ?>><?php echo __('Move to Another Stage'); ?></option>
								<option value='3' <?php echo ($stage_property == 3) ? 'selected="selected"' : '' ?>><?php echo __('Send a Notification'); ?></option>
							</select>
						</div>
					</div>

					<div class="form-group" id="dispatch_properties_stage" name="dispatch_properties_stage" style="display: <?php echo ($stage_property == 2) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Select a stage'); ?></i></label>
						<div class="col-sm-8">
							<select id="dispatch_next_stage" name="dispatch_next_stage" class="form-control">
								<?php
								$q = Doctrine_Query::create()
									->from('Menus a')
									->orderBy('a.order_no ASC');
								$stagegroups = $q->execute();
								foreach ($stagegroups as $stagegroup) {
									$q = Doctrine_Query::create()
										->from('SubMenus a')
										->where('a.menu_id = ?', $stagegroup->getId())
										->andWhere('a.deleted = ?', '0')
										->orderBy('a.order_no ASC');
									$stages = $q->execute();

									echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

									foreach ($stages as $stage) {
										$selected = "";

										if ($stage_type_movement == $stage->getId()) {
											$selected = "selected";
										}

										echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
									}

									echo "</optgroup>";
								}

								?>
							</select>
						</div>
					</div>

					<div class="form-group" id="dispatch_properties_notification" name="dispatch_properties_notification" style="display: <?php echo ($stage_property == 3) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Notification to be sent to reviewers'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_notification']->renderError() ?>
							<textarea id="dispatch_notification" name="dispatch_notification" class='form-control'><?php echo $stage_type_notification; ?></textarea>
						</div>
					</div>

				</div>
			</div>
			<div class="form-group" id="assessment_area" name="assessment_area" style="display: <?php echo ($stage_type == 2) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Assessment Properties'); ?></i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('What to do when all tasks are complete?'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_property']->renderError() ?>
							<select id="assessment_properties" name="assessment_properties" class="form-control" onChange="if(this.value == '2'){ document.getElementById('assessment_properties_stage').style.display = 'block'; document.getElementById('assessment_properties_notification').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('assessment_properties_notification').style.display = 'block'; document.getElementById('assessment_properties_stage').style.display = 'none'; }else{ document.getElementById('assessment_properties_stage').style.display = 'none'; document.getElementById('assessment_properties_notification').style.display = 'none';  }">
								<option value='1' <?php echo ($stage_property == 1) ? 'selected="selected"' : '' ?>><?php echo __('Do Nothing'); ?></option>
								<option value='2' <?php echo ($stage_property == 2) ? 'selected="selected"' : '' ?>><?php echo __('Move to Another Stage'); ?></option>
								<option value='3' <?php echo ($stage_property == 3) ? 'selected="selected"' : '' ?>><?php echo __('Send a Notification'); ?></option>
							</select>
						</div>
					</div>

					<div class="form-group" id="assessment_properties_stage" name="assessment_properties_stage" style="display: <?php echo ($stage_property == 2) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Select a stage'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_movement']->renderError() ?>
							<select id="assessment_next_stage" name="assessment_next_stage" class="form-control">
								<?php
								$q = Doctrine_Query::create()
									->from('Menus a')
									->orderBy('a.order_no ASC');
								$stagegroups = $q->execute();
								foreach ($stagegroups as $stagegroup) {
									$q = Doctrine_Query::create()
										->from('SubMenus a')
										->where('a.menu_id = ?', $stagegroup->getId())
										->andWhere('a.deleted = ?', '0')
										->orderBy('a.order_no ASC');
									$stages = $q->execute();

									echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

									foreach ($stages as $stage) {
										$selected = "";

										if ($stage_type_movement == $stage->getId()) {
											$selected = "selected";
										}

										echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
									}

									echo "</optgroup>";
								}

								?>
							</select>
						</div>
					</div>

					<div class="form-group" id="assessment_properties_notification" name="assessment_properties_notification" style="display: <?php echo ($stage_property == 3) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Notification to be sent to reviewers'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_notification']->renderError() ?>
							<textarea id="assessment_notification" name="assessment_notification" class='form-control'><?php echo $stage_type_notification; ?></textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-12"><i class="bold-label"><?php echo __('Select any reviewers you want automatically assigned to an application'); ?></i></label>
					</div>
					<div class="form-group" id="assessment_reviewers" name="assessment_reviewers">
						<div class="col-sm-12">
							<select name='allowed_reviewers[]' id='allowed_reviewers' multiple>
								<?php
								$selected = "";
								$q = Doctrine_Query::create()
									->from("CfUser a")
									->where("a.bdeleted = 0")
									->orderBy("a.strfirstname ASC");
								$reviewers = $q->execute();
								foreach ($reviewers as $reviewer) {
									$selected = "";

									if (!$form->getObject()->isNew()) {
										$q = Doctrine_Query::create()
											->from("WorkflowReviewers a")
											->where("a.workflow_id = ?", $form->getObject()->getId())
											->andWhere("a.reviewer_id = ?", $reviewer->getNid());
										$workflow_reviewer = $q->fetchOne();

										if ($workflow_reviewer) {
											$selected = "selected='selected'";
										}
									}

									echo "<option value='" . $reviewer->getNid() . "' " . $selected . ">" . ucfirst($reviewer->getStrfirstname()) . " " . ucfirst($reviewer->getStrlastname()) . " (" . $reviewer->getStrdepartment() . ")</option>";
								}
								?>
							</select>

							<script>
								jQuery(document).ready(function() {
									var demo2 = $('[id="allowed_reviewers"]').bootstrapDualListbox();
								});
							</script>
						</div>
					</div>

				</div>
			</div>
			<div class="form-group" id="invoicing_area" name="invoicing_area" style="display: <?php echo ($stage_type == 3) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label">Invoicing Properties</i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('What to do when invoices are paid?'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_property']->renderError() ?>
							<select id="invoicing_properties" name="invoicing_properties" class="form-control" onChange="if(this.value == '2'){ document.getElementById('invoicing_properties_stage').style.display = 'block'; document.getElementById('invoicing_properties_notification').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('invoicing_properties_notification').style.display = 'block'; document.getElementById('invoicing_properties_stage').style.display = 'none'; }else{ document.getElementById('invoicing_properties_stage').style.display = 'none'; document.getElementById('invoicing_properties_notification').style.display = 'none';  }">
								<option value='1' <?php echo ($stage_property == 1) ? 'selected="selected"' : '' ?>><?php echo __('Do Nothing'); ?></option>
								<option value='2' <?php echo ($stage_property == 2) ? 'selected="selected"' : '' ?>><?php echo __('Move to Another Stage'); ?></option>
								<option value='3' <?php echo ($stage_property == 3) ? 'selected="selected"' : '' ?>><?php echo __('Send a Notification'); ?></option>
							</select>
						</div>
					</div>

					<div class="form-group" id="invoicing_properties_stage" name="invoicing_properties_stage" style="display: <?php echo ($stage_property == 2) ? 'block' : 'none' ?>;">
						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select the stage for successful payments'); ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_type_movement']->renderError() ?>
								<select id="invoicing_next_stage_pass" name="invoicing_next_stage_pass" class="form-control">
									<?php
									$q = Doctrine_Query::create()
										->from('Menus a')
										->orderBy('a.order_no ASC');
									$stagegroups = $q->execute();
									foreach ($stagegroups as $stagegroup) {
										$q = Doctrine_Query::create()
											->from('SubMenus a')
											->where('a.menu_id = ?', $stagegroup->getId())
											->andWhere('a.deleted = ?', '0')
											->orderBy('a.order_no ASC');
										$stages = $q->execute();

										echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

										foreach ($stages as $stage) {
											$selected = "";

											if ($stage_type_movement_pass == $stage->getId()) {
												$selected = "selected";
											}

											echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
										}

										echo "</optgroup>";
									}

									?>
								</select>
							</div>

						</div>

						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select the stage for failed payments'); ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_type_movement_fail']->renderError() ?>
								<select id="invoicing_next_stage_fail" name="invoicing_next_stage_fail" class="form-control">
									<?php
									$q = Doctrine_Query::create()
										->from('Menus a')
										->orderBy('a.order_no ASC');
									$stagegroups = $q->execute();
									foreach ($stagegroups as $stagegroup) {
										$q = Doctrine_Query::create()
											->from('SubMenus a')
											->where('a.menu_id = ?', $stagegroup->getId())
											->andWhere('a.deleted = ?', '0')
											->orderBy('a.order_no ASC');
										$stages = $q->execute();

										echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

										foreach ($stages as $stage) {
											$selected = "";

											if ($stage_type_movement_fail == $stage->getId()) {
												$selected = "selected";
											}

											echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
										}

										echo "</optgroup>";
									}

									?>
								</select>
							</div>
						</div>
						<!--OTB Start - Combine cash and electronic payments -->
						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select the stage for manual payment confirmation (if any)'); ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_payment_confirmation']->renderError() ?>
								<?php echo $form['stage_payment_confirmation']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<!--OTB End - Combine cash and electronic payments -->
					</div>

					<div class="form-group" id="invoicing_properties_notification" name="invoicing_properties_notification" style="display: <?php echo ($stage_property == 3) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Notification to be sent to reviewers'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_notification']->renderError() ?>
							<textarea id="invoicing_notification" name="invoicing_notification" class='form-control'><?php echo $stage_type_notification; ?></textarea>
						</div>
					</div>

				</div>
			</div>
			<div class="form-group" id="corrections_area" name="corrections_area" style="display: <?php echo ($stage_type == 5) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label">Corrections Properties</i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('What to do when user makes corrections?'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_property']->renderError() ?>
							<select id="correction_properties" name="correction_properties" class="form-control" onChange="if(this.value == '2'){ document.getElementById('correction_properties_stage').style.display = 'block'; document.getElementById('correction_properties_notification').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('correction_properties_notification').style.display = 'block'; document.getElementById('correction_properties_stage').style.display = 'none'; }else{ document.getElementById('correction_properties_stage').style.display = 'none'; document.getElementById('correction_properties_notification').style.display = 'none';  }">
								<option value='1' <?php echo ($stage_property == 1) ? 'selected="selected"' : '' ?>><?php echo __('Do Nothing'); ?></option>
								<option value='2' <?php echo ($stage_property == 2) ? 'selected="selected"' : '' ?>><?php echo __('Move to Another Stage'); ?></option>
								<option value='3' <?php echo ($stage_property == 3) ? 'selected="selected"' : '' ?>><?php echo __('Send a Notification'); ?></option>
							</select>
						</div>
					</div>

					<div class="form-group" id="correction_properties_stage" name="correction_properties_stage" style="display: <?php echo ($stage_property == 2) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Select a stage'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_movement']->renderError() ?>
							<select id="correction_next_stage" name="correction_next_stage" class="form-control">
								<?php
								$q = Doctrine_Query::create()
									->from('Menus a')
									->orderBy('a.order_no ASC');
								$stagegroups = $q->execute();
								foreach ($stagegroups as $stagegroup) {
									$q = Doctrine_Query::create()
										->from('SubMenus a')
										->where('a.menu_id = ?', $stagegroup->getId())
										->andWhere('a.deleted = ?', '0')
										->orderBy('a.order_no ASC');
									$stages = $q->execute();

									echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

									foreach ($stages as $stage) {
										$selected = "";

										if ($stage_type_movement == $stage->getId()) {
											$selected = "selected";
										}

										echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
									}

									echo "</optgroup>";
								}

								?>
							</select>
						</div>
					</div>

					<div class="form-group" id="correction_properties_notification" name="correction_properties_notification" style="display: <?php echo ($stage_property == 3) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Notification to be sent to reviewers'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_notification']->renderError() ?>
							<textarea id="correction_notification" name="correction_notification" class='form-control'><?php echo $stage_type_notification; ?></textarea>
						</div>
					</div>

				</div>
			</div>
			<div class="form-group" id="renewals_area" name="renewals_area" style="display: <?php echo ($stage_type == 10) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label">Renewals Properties</i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('What to do when invoices are paid?'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_property']->renderError() ?>
							<select id="renewals_properties" name="renewals_properties" class="form-control" onChange="if(this.value == '2'){ document.getElementById('renewals_properties_stage').style.display = 'block'; document.getElementById('renewals_properties_notification').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('renewals_properties_notification').style.display = 'block'; document.getElementById('renewals_properties_stage').style.display = 'none'; }else{ document.getElementById('renewals_properties_stage').style.display = 'none'; document.getElementById('renewals_properties_notification').style.display = 'none';  }">
								<option value='1' <?php echo ($stage_property == 1) ? 'selected="selected"' : '' ?>><?php echo __('Do Nothing'); ?></option>
								<option value='2' <?php echo ($stage_property == 2) ? 'selected="selected"' : '' ?>><?php echo __('Move to Another Stage'); ?></option>
								<option value='3' <?php echo ($stage_property == 3) ? 'selected="selected"' : '' ?>><?php echo __('Send a Notification'); ?></option>
							</select>
						</div>
					</div>

					<div class="form-group" id="renewals_properties_stage" name="renewals_properties_stage" style="display: <?php echo ($stage_property == 2) ? 'block' : 'none' ?>;">
						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select the stage for successful payments'); ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_type_movement']->renderError() ?>
								<select id="renewals_next_stage_pass" name="renewals_next_stage_pass" class="form-control">
									<?php
									$q = Doctrine_Query::create()
										->from('Menus a')
										->orderBy('a.order_no ASC');
									$stagegroups = $q->execute();
									foreach ($stagegroups as $stagegroup) {
										$q = Doctrine_Query::create()
											->from('SubMenus a')
											->where('a.menu_id = ?', $stagegroup->getId())
											->andWhere('a.deleted = ?', '0')
											->orderBy('a.order_no ASC');
										$stages = $q->execute();

										echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

										foreach ($stages as $stage) {
											$selected = "";

											if ($stage_type_movement_pass == $stage->getId()) {
												$selected = "selected";
											}

											echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
										}

										echo "</optgroup>";
									}

									?>
								</select>
							</div>

						</div>

						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select the stage for failed payments'); ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_type_movement_fail']->renderError() ?>
								<select id="renewals_next_stage_fail" name="renewals_next_stage_fail" class="form-control">
									<?php
									$q = Doctrine_Query::create()
										->from('Menus a')
										->orderBy('a.order_no ASC');
									$stagegroups = $q->execute();
									foreach ($stagegroups as $stagegroup) {
										$q = Doctrine_Query::create()
											->from('SubMenus a')
											->where('a.menu_id = ?', $stagegroup->getId())
											->andWhere('a.deleted = ?', '0')
											->orderBy('a.order_no ASC');
										$stages = $q->execute();

										echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

										foreach ($stages as $stage) {
											$selected = "";

											if ($stage_type_movement_fail == $stage->getId()) {
												$selected = "selected";
											}

											echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
										}

										echo "</optgroup>";
									}

									?>
								</select>
							</div>
						</div>
					</div>

					<div class="form-group" id="renewals_properties_notification" name="renewals_properties_notification" style="display: <?php echo ($stage_property == 3) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Notification to be sent to reviewers'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_notification']->renderError() ?>
							<textarea id="renewals_notification" name="renewals_notification" class='form-control'><?php echo $stage_type_notification; ?></textarea>
						</div>
					</div>

				</div>
			</div>
			<div class="form-group" id="expired_area" style="display: <?php echo ($stage_type == 12) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Expired properties'); ?></i></label>
				<div class="col-sm-8">
					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Expired stage action') ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_property']->renderError() ?>
							<select id="expired_properties" name="expired_properties" class="form-control" onChange="if(this.value == '2'){ document.getElementById('expired_properties_stage').style.display = 'block'; document.getElementById('expired_properties_notification').style.display = 'none'; }else if(this.value == '3'){ document.getElementById('expired_properties_notification').style.display = 'block'; document.getElementById('expired_properties_stage').style.display = 'none'; }else{ document.getElementById('expired_properties_stage').style.display = 'none'; document.getElementById('expired_properties_notification').style.display = 'none';  }">
								<option value='1' <?php echo ($stage_property == 1) ? 'selected="selected"' : '' ?>><?php echo __('Do Nothing'); ?></option>
								<option value='2' <?php echo ($stage_property == 2) ? 'selected="selected"' : '' ?>><?php echo __('Update Invoice & Move to Another Stage'); ?></option>
								<option value='3' <?php echo ($stage_property == 3) ? 'selected="selected"' : '' ?>><?php echo __('Send a Notification'); ?></option>
							</select>
						</div>
					</div>
					<div class="form-group" id="expired_properties_stage" style="display: <?php if ($stage_property == 2) : ?>block<?php else : ?>none<?php endif; ?>">
						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select an invoice action') ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_expired_invoice_action']->renderError() ?>
								<?php echo $form['stage_expired_invoice_action']->render(array('class' => 'form-control')) ?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4"><i class="bold-label"><?php echo __('Select Stage to move') ?></i></label>
							<div class="col-sm-8">
								<?php echo $form['stage_type_movement']->renderError() ?>
								<select id="expired_next_stage" name="expired_next_stage" class="form-control">
									<?php
									$q = Doctrine_Query::create()
										->from('Menus a')
										->orderBy('a.order_no ASC');
									$stagegroups = $q->execute();
									foreach ($stagegroups as $stagegroup) {
										$q = Doctrine_Query::create()
											->from('SubMenus a')
											->where('a.menu_id = ?', $stagegroup->getId())
											->andWhere('a.deleted = ?', '0')
											->orderBy('a.order_no ASC');
										$stages = $q->execute();

										echo "<optgroup label='" . $stagegroup->getTitle() . "'>";

										foreach ($stages as $stage) {
											$selected = "";

											if ($stage_type_movement == $stage->getId()) {
												$selected = "selected";
											}

											echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . "</option>";
										}

										echo "</optgroup>";
									}

									?>
								</select>
							</div>
						</div>
					</div>
					<div class="form-group" id="expired_properties_notification" name="expired_properties_notification" style="display: <?php echo ($stage_property == 3) ? 'block' : 'none' ?>;">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Notification to be sent to reviewers'); ?></i></label>
						<div class="col-sm-8">
							<?php echo $form['stage_type_notification']->renderError() ?>
							<textarea id="expired_notification" name="expired_notification" class='form-control'><?php echo $stage_type_notification; ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Allow editing of applications by reviewers with access to this stage'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['allow_edit']->renderError() ?>
					<?php echo $form['allow_edit']->render(array('class' => 'form-control')) ?>
				</div>
			</div>
			<!-- OTB ADD shared automatic move -->
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Allow shared applications move'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['shared_stage_move']->renderError() ?>
					<?php echo $form['shared_stage_move']->render(array('class' => 'form-control', 'onChange' => 'if($(this).val() == 1){$("#shared_stage").show();}else{$("#shared_stage").hide();}')) ?>
				</div>
			</div>
			<div class="form-group" id="shared_stage" <?php if ($form->getObject()->getSharedStageMove() == 0) {
															echo 'style="display:none"';
														} ?>>
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Shared applications move stage'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['shared_stage']->renderError() ?>
					<?php echo $form['shared_stage']->render(array('class' => 'form-control')) ?>
				</div>
			</div>
			<!-- OTB END -->
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Send notification to user when application enters this stage?'); ?></i></label>
				<div class="col-sm-8">
					<select id="send_notification" name="send_notification" class="form-control" onChange="if(this.value == '1'){ document.getElementById('notification_area').style.display = 'block'; }else{ document.getElementById('notification_area').style.display = 'none'; }">
						<option value='0'>No</option>
						<option value='1' <?php echo ($notification) ? 'selected="selected"' : '' ?>>Yes</option>
					</select>
				</div>
			</div>
			<div class="form-group" id="notification_area" name="notification_area" style="display: <?php echo ($notification) ? 'block' : 'none' ?>;">
				<label class="col-sm-4"><i class="bold-label">Notification</i></label>
				<div class="col-sm-8">

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Mail Subject'); ?></i></label>
						<div class="col-sm-8">
							<input type="text" id="mail_subject" name="mail_subject" class='form-control' value="<?php echo ($notification) ? $notification->getTitle() : '' ?>">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Mail Content'); ?></i></label>
						<div class="col-sm-8">
							<textarea id="mail_content" name="mail_content" class='form-control'><?php echo ($notification) ? $notification->getContent() : '' ?></textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('SMS Content'); ?></i></label>
						<div class="col-sm-8">
							<textarea id="sms_content" name="sms_content" class='form-control'><?php echo ($notification) ? $notification->getSms() : '' ?></textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-4"><i class="bold-label"><?php echo __('Send Options'); ?></i></label>
						<div class="col-sm-8">
							<select id="send_options" name="send_options" class="form-control">
								<option value="1">Send notification automatically</option>
								<option value="0" <?php echo ($notification && !$notification->getAutosend()) ? 'selected="selected"' : '' ?>>Allow reviewer to edit notification before sending</option>
							</select>
						</div>
					</div>


					<div class="form-group">
						<div class="col-sm-12 alignright">
							<button type="button" class="btn btn-primary" data-target="#fieldsModal" data-toggle="modal">View available user/form fields</button>
						</div>
					</div>


					<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="fieldsModal" class="modal fade" style="display: none;">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
									<h4 id="myModalLabel" class="modal-title">View available user/form fields</h4>
								</div>
								<div class="modal-body">
									<div class="form-group"> <?php
																//Get User Information (anything starting with sf_ )
																//sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
																?>

										<table class="table dt-on-steroids mb0">
											<thead>
												<tr>
													<th width="50%"><?php echo __('User Details'); ?></th>
													<th><?php echo __('Tag'); ?></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo __('Username'); ?></td>
													<td{sf_username}< /td>
												</tr>
												<tr>
													<td><?php echo __('Email'); ?></td>
													<td>{sf_email}</td>
												</tr>
												<tr>
													<td><?php echo __('Full Name'); ?></td>
													<td>{sf_fullname}</td>
												</tr>
												<?php
												$q = Doctrine_Query::create()
													->from('apFormElements a')
													->where('a.form_id = ?', '15');

												$elements = $q->execute();

												foreach ($elements as $element) {
													$childs = $element->getElementTotalChild();
													if ($childs == 0) {
														echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "}</td></tr>";
													} else {
														if ($element->getElementType() == "select") {
															echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "}</td></tr>";
														} else {
															for ($x = 0; $x < ($childs + 1); $x++) {
																echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "_" . ($x + 1) . "}</td></tr>";
															}
														}
													}
												}
												?>
											</tbody>
										</table>
										<?php
										foreach ($forms as $formd) {
										?>
											<div id='form_<?php echo $formd->getFormId(); ?>' name='form_<?php echo $formd->getFormId(); ?>' style='display: none;'>
												<table class="table dt-on-steroids mb0">
													<thead>
														<tr>
															<th width="50%"><?php echo __('Application Details'); ?></th>
															<th>Tag</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td><?php echo __('Plan Registration Number'); ?></td>
															<td>{ap_application_id}</td>
														</tr>
														<tr>
															<td><?php echo __('Created At'); ?></td>
															<td>{fm_created_at}</td>
														</tr>
														<tr>
															<td><?php echo __('Approved At'); ?></td>
															<td>{fm_updated_at}</td>
														</tr>
														<?php

														$q = Doctrine_Query::create()
															->from('apFormElements a')
															->where('a.form_id = ?', $formd->getFormId());

														$elements = $q->execute();

														foreach ($elements as $element) {
															$childs = $element->getElementTotalChild();
															if ($childs == 0) {
																echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "}</td></tr>";
															} else {
																if ($element->getElementType() == "select") {
																	echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "}</td></tr>";
																} else {
																	for ($x = 0; $x < ($childs + 1); $x++) {
																		echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "_" . ($x + 1) . "}</td></tr>";
																	}
																}
															}
														}
														?>
													</tbody>
												</table>
											</div>
										<?php
										}
										?>


										<table class="table dt-on-steroids mb0">
											<thead>
												<tr>
													<th width="50%"><?php echo __('Conditions Of Approval'); ?></th>
													<th>Tag</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo __('Conditions Of Approval'); ?></td>
													<td>{ca_conditions}</td>
												</tr>
											</tbody>
										</table>

										<table class="table dt-on-steroids mb0">
											<thead>
												<tr>
													<th width="50%"><?php echo __('Invoice Details'); ?></th>
													<th><?php echo __('Tag'); ?></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo __('Total'); ?></td>
													<td>{in_total}</td>
												</tr>
											</tbody>
										</table>

										<table class="table dt-on-steroids mb0">
											<thead>
												<tr>
													<th width="50%"><?php echo __('Other Details'); ?></th>
													<th><?php echo __('Tag'); ?></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo __('Current Date'); ?></td>
													<td>{current_date}</td>
												</tr>
												<tr>
													<td><?php echo __('Comments and Reasons for Decline'); ?></td>
													<td>{ap_comments}</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<div class="modal-footer">
									<button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
								</div>
							</div><!-- modal-content -->
						</div><!-- modal-dialog -->
					</div>
					<hr>

				</div>
			</div>

		</div>

		<div class="panel-footer">
			<button class="btn btn-danger mr10"><?php echo __('Reset'); ?></button><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
		</div>

	</div>
	</div>

	</div>

</form>

<script>
	jQuery(document).ready(function() {

		$("#addgroup").click(function() {
			$("#groups").append("<div class='form-group' class='formgroup'><label class='col-sm-4'>Name</label><div class='col-sm-8'> <input type='text' name='name[]' class='form-control' /></div><label class='col-sm-4'>Description</label><div class='col-sm-8'><textarea name='description[]' class='form-control' /></textarea></div><a style='float: right; margin-top: 10px;' href='#' class='panel-close'>&times;</a></div>");
		});

		// Date Picker
		jQuery('#datepicker1').datepicker();
		jQuery('#datepicker2').datepicker();


	});
</script>