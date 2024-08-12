<div class="card flex-fill">
	<div class="card-header text-end">
		<?php
		if ($application->getApproved() == 0 || $application->getDeclined() == 1) {
		?>
			<a href="/index.php//application/edit/id/<?php echo $application->getId(); ?>" class="btn btn-primary pull-right text-end"><span class="fa fa-edit"></span> <?php echo __("Edit &amp; Resubmit"); ?></a>
		<?php
		} else {
		?>
			<a href="/index.php//application/viewentrypdf/id/<?php echo $application->getId(); ?>" class="btn btn-dark text-end btn-sm"><span class="fa fa-download"></span> <?php echo __("Download Application Details"); ?></a>
		<?php
		}
		?>
		<?php
		$otbhelper = new OTBHelper();
		if ($otbhelper->isSharedStage($application->getApproved())) :
		?>
			<!-- OTB Start Patch >> Share Button -->
			<a title='<?php echo __('Share Application'); ?>' href="<?php echo public_path('index.php/application/share/id/' . $application->getId()) ?>" class="btn btn-primary dropdown-toggle waves-effect"><?php echo __("Share"); ?> </a>
			<!-- OTB End Patch >> Share Button -->
		<?php endif; ?>
	</div>



	<div class="card-body">
		<div class="table-responsive">
			<table class="table">
				<tbody>
					<?php
					$toggle = false;

					$application_json = html_entity_decode($application->getFormData());

					$application_data = json_decode($application_json, true);

					foreach ($application_data as $row) {
						if ($row['element_type'] == "page_break") {
							//skip
						} elseif ($row['element_type'] == "section" && !is_null($row['label'])) {
							$row_markup = "";
							$row_markup .= "<tr {$row_style}>\n";
							$row_markup .= "<td colspan='2'><h3>{$row['label']}</h3></td>\n";
							$row_markup .= "</tr>\n";
							echo $row_markup;
						} elseif ($row['element_type'] == 'signature') {
							if ($row['element_size'] == 'small') {
								$canvas_height = 70;
								$line_margin_top = 50;
							} else if ($row['element_size'] == 'medium') {
								$canvas_height = 130;
								$line_margin_top = 95;
							} else {
								$canvas_height = 260;
								$line_margin_top = 200;
							}
							$signature_markup = <<<EOT
						<div id="mf_sigpad_{$row['element_id']}" class="mf_sig_wrapper {$row['element_size']}">
							<canvas class="mf_canvas_pad" width="309" height="{$canvas_height}"></canvas>
						</div>
						<script type="text/javascript">
							$(function(){
								var sigpad_options_{$row['element_id']} = {
									drawOnly : true,
									displayOnly: true,
									bgColour: '#fff',
									penColour: '#000',
									output: '#element_{$row['element_id']}',
									lineTop: {$line_margin_top},
									lineMargin: 10,
									validateFields: false
								};
								var sigpad_data_{$row['element_id']} = {$row['value']};
								$('#mf_sigpad_{$row['element_id']}').signaturePad(sigpad_options_{$row['element_id']}).regenerate(sigpad_data_{$row['element_id']});
							});
						</script>
EOT;

							$row_markup .= "<tr>\n";
							$row_markup .= "<td><strong>{$row['label']}</strong></td>\n";
							$row_markup .= "<td width=\"60%\">{$signature_markup}</td>\n";
							$row_markup .= "</tr>\n";

							echo $row_markup;
						} else {
							$row_markup = "";
							$row_markup .= "<tr {$row_style}>\n";
							$row_markup .= "<td><strong>{$row['label']}</strong></td>\n";
							$row_markup .= "<td>" . nl2br($row['value']) . "</td>\n";
							$row_markup .= "</tr>\n";

							echo $row_markup;
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript" src="/form_builder/js/signaturepad/jquery.signaturepad.min.js"></script>