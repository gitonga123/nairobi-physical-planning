<?php
/**
 * application_details_link partial.
 *
 */
$dbh = mf_connect_db();

$form_id  = $link->getFormId();
$entry_id = $link->getEntryId();
$application_manager = new ApplicationManager();

?>
<div class="table-responsive">
    <table class="table m-b-0 m-t-20">
        <tbody>
        <?php

           $application_data = mf_get_entry_details($dbh,$form_id,$entry_id);
           foreach($application_data as $row)
           {
               if($row['element_type'] == "page_break")
               {
                    //skip
               }
               elseif($row['element_type'] == "section")
               {
                    $row_markup = "";
                    $row_markup .= "<tr {$row_style}>\n";
                    $row_markup .= "<td colspan='2'><h3>{$row['label']}</h3></td>\n";
                    $row_markup .= "</tr>\n";

                    echo $row_markup;
				}elseif($row['element_type'] == 'signature') {
					if($row['element_size'] == 'small'){
						$canvas_height = 70;
						$line_margin_top = 50;
					}else if($row['element_size'] == 'medium'){
						$canvas_height = 130;
						$line_margin_top = 95;
					}else{
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
				}else{
               
                    $row_markup = "";
                    $row_markup .= "<tr {$row_style}>\n";
                    $row_markup .= "<td><strong>{$row['label']}</strong></td>\n";
                    $row_markup .= "<td>".nl2br($row['value'])."</td>\n";
                    $row_markup .= "</tr>\n";

                    echo $row_markup;
               }
           }
        ?>
        </tbody>
    </table>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="ifc_viewer" aria-labelledby="ifc_viewer" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modal-content">
	</div>
  </div>
</div>
<script type="text/javascript" src="/form_builder/js/signaturepad/jquery.signaturepad.min.js"></script>
<script>
$(function(){
	$('.ifc-button').click(function(e){
		$('#modal-content').load("<?php echo url_for('/plan/applications/viewifc') ?>",{application: $(this).data('application')});
	});
});
</script>