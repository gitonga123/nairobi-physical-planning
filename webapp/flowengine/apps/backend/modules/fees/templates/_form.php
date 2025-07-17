<?php
use_helper("I18N");
?>
<form action="/plan/fees/<?php echo ($form->getObject()->isNew() ? 'create' : 'update') . (!$form->getObject()->isNew() ? '?id=' . $form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?> autocomplete="off" data-ajax="false" class="form-bordered bform">
	<?php echo $form->renderHiddenFields() ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo ($form->getObject()->isNew() ? __('New Fee') : __('Edit Fee')); ?></h3>
			<?php echo $form->renderGlobalErrors() ?>
		</div>
		<div class="panel-heading">
			<a class="btn btn-primary" id="newpage" href="/plan/fees/index"><?php echo __('Back to List'); ?></a>
		</div>
		<div class="panel-body padding-0">
			<div class="form-group">
				<label class="col-sm-4 control-label"><?php echo __('Description'); ?></label><br>
				<div class="col-sm-12">
					<?php echo $form['description']->renderError() ?>
					<?php echo $form['description'] ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label"><?php echo __('Fee Code'); ?></label><br>
				<div class="col-sm-12">
					<?php echo $form['fee_code']->renderError() ?>
					<?php echo $form['fee_code'] ?>
				</div>
				<script>
					$(document).ready(function() {
						$('#fee_fee_code').addClass('form-control');
						$('#fee_fee_code').change(function(e) {
							$.ajax({
								url: "<?php echo url_for('/plan/fees/getfeecode') ?>",
								data: {
									service_id: $(this).val()
								},
								type: "GET",
								dataType: "json",
							}).done(function(resp) {
								$('#fee_amount').val(resp.amount);
								if (Number(resp.fixed)) {
									$('#fee_fee_type').val('fixed').trigger('change');
								}
							}).fail(function(xhr, status, errorThrown) {
								//alert( "Sorry, there was a problem!" );
								console.log("Error: " + errorThrown);
								console.log("Status: " + status);
								console.dir(xhr);
							});
						});
					});
				</script>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label"><?php echo __('Fee Category'); ?></label><br>
				<div class="col-sm-12">
					<?php echo $form['fee_category']->renderError() ?>
					<?php echo $form['fee_category'] ?>
				</div>
			</div>
			
			<div class="form-group">
				<label class="col-sm-4 control-label"><?php echo __('Fixed to a specific Invoice?'); ?></label><br>
				<div class="col-sm-12">
					<?php echo $form['invoiceid']->renderError() ?>
					<?php echo $form['invoiceid'] ?>
				</div>
			</div>
			<!--OTB Start Patch - For Implementing Finance Bills -->
			<div class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Fee Type'); ?></i></label>
				<div class="col-sm-8 rogue-input">
					<?php echo $form['fee_type']->renderError() ?>
					<?php echo $form['fee_type'] ?>
				</div>
				<script language="javascript">
					jQuery(document).ready(function() {
						//default is fixed so first hide percentage and range areas
						get_fields_to_show($('#fee_fee_type').val());
						$('#fee_fee_type').change(function() {
							var value = this.value;
							get_fields_to_show(value);
						})

						function get_fields_to_show(value) {
							if (value == "percentage") {
								$('#fixed_amount_div').show();
								$("#loadranges").hide();
								//$('#percentage_div').show();
								$('#base_field').show();
							} else if (value == "fixed") {
								$('#fixed_amount_div').show();
								$("#loadranges").hide();
								//$('#percentage_div').hide();
								$('#base_field').hide();
							} else if (value == "range" || value == "range_percentage") {
								$('#fixed_amount_div').hide();
								//$('#percentage_div').hide();
								$('#base_field').show();
								$("#loadranges").show();
							} else if (value == "formula") {
								$('#fixed_amount_div').show();
								$("#loadranges").hide();
								$('#base_field').show();
							}
						}
					});
				</script>
			</div>
			<!--<div id="percentage_div" class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Percentage(%)'); ?></i></label>
				<div class="col-sm-8">
					<?php echo $form['percentage']->renderError() ?>
					<?php echo $form['percentage'] ?>
				</div>
			</div>-->
			<div id="base_field" class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Base field'); ?></i></label>
				<div class="col-sm-8 rogue-input">
					<?php echo $form['base_field']->renderError() ?>
					<?php echo $form['base_field'] ?>
				</div>
			</div>
			<div id="base_field" class="form-group">
				<label class="col-sm-4"><i class="bold-label"><?php echo __('Amount'); ?></i></label>
				<div class="col-sm-8 rogue-input">
					<?php echo $form['amount']->renderError() ?>
					<?php echo $form['amount'] ?>
				</div>
			</div>
			<!--OTB End Patch - For Implementing Finance Bills -->
			<!--OTB Start Patch - For Implementing Finance Bills. Load fee structures area -->
			<div class="form-group">
				<div class="col-sm-12" id="loadranges" name="loadranges">
				</div>
				<?php
				$feeid = 0;
				if (!$form->getObject()->isNew()) {
					$feeid = $form->getObject()->getId();
				}
				?>
				<script language="javascript">
					jQuery(document).ready(function() {
						$("#loadranges").load("<?php echo url_for('/plan/fees/feerangeindex/filter/' . $feeid) ?>");
						$('#fee_invoiceid').change(function() {
							var value = this.value;
							$.ajax({
								url: '<?php echo url_for('/plan/fees/changebasefield/invoicetemplate_id/'); ?>' + value,
								cache: false,
								type: 'POST',
								//data : $('#bform').serialize(),
								success: function(json) {
									$('#fee_base_field').empty();
									$.each(JSON.parse(json), function(k, v) {
										<?php if (!$form->getObject()->isNew()) : ?>
											if (k == <?php echo $form->getObject()->getBaseField() ?>) {
												$('#fee_base_field').append("<option value='" + k + "' selected>" + v + "</option>");
											} else {
												$('#fee_base_field').append("<option value='" + k + "'>" + v + "</option>");
											}
										<?php else : ?>
											$('#fee_base_field').append("<option value='" + k + "'>" + v + "</option>");
										<?php endif; ?>
									});
								}
							});
						}).trigger("change");
					});
				</script>
			</div>
			<!--OTB End Patch - For Implementing Finance Bills. Load fee structures area -->
		</div><!-- panel-body -->
		<div class="panel-footer">
			<button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
		</div>
	</div><!-- panel-default -->
</form>