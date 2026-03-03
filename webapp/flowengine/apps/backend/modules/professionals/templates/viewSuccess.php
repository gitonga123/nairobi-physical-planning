<?php

/**
 * view template.
 *
 * Display a task, its comments sheets/invoices and application details relating to it
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

$membersManager = new MembersManager();
?>
<div class="contentpanel">
    <div class="panel panel-default">
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" id="alertdiv" name="alertdiv">
                <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">
                    &times;
                </button>
                <strong><?php echo __('Info'); ?>!</strong> <?php echo $error; ?></a>.
            </div>
        <?php endif; ?>

        <div class="panel-heading">
            <h3 class="panel-title"> Professional Details</h3>
        </div>
        <?php
        $isActivated = $membersManager->checkIfUserAccountIsActivated($form_id, $entry_id);

        $badgeClass = $isActivated ? 'label label-success' : 'label label-warning';
        $badgeText  = $isActivated ? 'ACTIVE' : 'INACTIVE';
        ?>

        <div class="panel-heading text-right">
            <?php if (!$isActivated): ?>
                <a class="btn btn-success"
                    title="Approve User"
                    href="/plan/professionals/approve/form/<?php echo $form_id; ?>/entry/<?php echo $entry_id; ?>">
                    <?php echo __('Approve Account'); ?>
                </a>
            <?php else: ?>
                <a class="btn btn-danger"
                    title="Deactivate User"
                    href="/plan/professionals/deactivate/form/<?php echo $form_id; ?>/entry/<?php echo $entry_id; ?>">
                    <?php echo __('Deactivate Account'); ?>
                </a>
            <?php endif; ?>
            <a class="btn btn-primary"
                title="Back List"
                href="/plan/professionals/index">
                <?php echo __('Back to list'); ?>
            </a>
        </div>


        <div class="panel-body">
            <div class="table table-striped table-responsive">
                <table class="table m-b-0 m-t-20">
                    <tbody>
                        <?php
                        foreach ($application_data as $row) {
                            if ($row['element_type'] == "page_break") {
                                //skip
                            } elseif ($row['element_type'] == "section") {
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

                                // Decode entities first
                                $decoded_value = html_entity_decode($row['value']);

                                // Optional: sanitize to allow only safe HTML tags
                                $allowed_tags = '<a><b><i><u><strong><em><br><p><ul><ol><li>';
                                $sanitized_value = strip_tags($decoded_value, $allowed_tags);

                                // Convert newlines to <br>
                                $sanitized_value = nl2br($sanitized_value);

                                // Build the row
                                $row_markup = "<tr {$row_style}>\n";
                                $row_markup .= "<td><strong>" . htmlspecialchars($row['label']) . "</strong></td>\n";
                                $row_markup .= "<td>{$sanitized_value}</td>\n";
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
</div>