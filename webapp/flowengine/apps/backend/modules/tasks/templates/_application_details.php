<?php
/**
 * application_details partial.
 *
 * Displays application details
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

$application_manager = new ApplicationManager();

if($application->getEntryId() == 0 || $application->getEntryId() == "")
{
    ?>
    <form method="get" action="/plan/applications/view?id=<?php echo $application->getId(); ?>">
        <div class="panel-body padding-0" style="border-top:none;">
            <div class="form-group">
                <label class="col-sm-8"><?php echo __('This application had an issue during submission. Please enter bill reference to recover application details or contact system administrator for assistance'); ?></label>
            </div>
            <div class="form-group">
                <div class="col-sm-8">
                    <input class="form-control" type='text' name='reference_number' id='reference_number' style="width:80%;">
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-primary" type="submit"><?php echo __('Recover'); ?></button>
        </div>
    </form>
    <?php
}
else 
{
?>
<div class="table-responsive">
    <table class="table m-b-0 m-t-20">
        <tbody>
        <?php
           $application_json = html_entity_decode($application->getFormData());

           $application_data = json_decode($application_json, true);
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
<?php 
}
?>

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