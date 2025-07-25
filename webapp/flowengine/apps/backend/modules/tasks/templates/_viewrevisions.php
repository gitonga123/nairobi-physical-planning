<?php
$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
require($prefix_folder.'includes/init.php');

header("p3p: CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");

require($prefix_folder.'../../../config/form_builder_config.php');
#require($prefix_folder.'includes/db-core.php');
require_once($prefix_folder.'includes/helper-functions.php');

require_once($prefix_folder.'includes/entry-functions.php');

$dbh = mf_connect_db();

//get entry details for particular entry_id
$param['checkbox_image'] = '/assets_unified/images/59_blue_16.png';

?>



<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <?php foreach($revisions as $revision): ?>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading<?php echo $revision->getId() ?>">
        <h4 class="panel-title">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $revision->getId() ?>" aria-expanded="true" aria-controls="collapse<?php echo $revision->getId() ?>">
            <?php echo $revision->getApplicationId() ?> (<?php echo date('Y-m-d H:i:s',strtotime($revision->getDateOfSubmission())) ?>)
            </a>
        </h4>
        </div>
        <div id="collapse<?php echo $revision->getId() ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $revision->getId() ?>">
            <div class="panel-body">
                <table class="table m-b-0">
                <tbody>
                    <?php
                        $toggle = false;

                        $application_data = mf_get_entry_details($dbh, $revision->getFormId(), $revision->getEntryId(), $param, $sf_user->getCulture());
                                    

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
        </div>
    </div>
    <?php endforeach; ?>
</div>
<script type="text/javascript" src="/form_builder/js/signaturepad/jquery.signaturepad.min.js"></script>
