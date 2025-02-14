<?php
/**
 * viewSuccess.php template.
 *
 * Displays business profile
 *
 * @package    backend
 * @subpackage profiles
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

$application_manager = new ApplicationManager();
$entry_details = $application_manager->get_entry_details($inspection->getFormId(), $inspection->getEntryId());
?>
<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Users'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan"><?php echo __('Home'); ?></a></li>
      <li><a href="/backend.php/frusers/index"><?php echo __('Users'); ?></a></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
    <div class="row">

		<div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $business->getTitle(); ?></h3>

            <div class="pull-right" style="margin-top: -30px;">
                <a class="btn btn-primary " id="newpage" href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>" ><?php echo __('Back to Profile'); ?></a>
				
            </div>
        </div>

		<div class="panel-body" style="margin: 0px; padding: 0px;">
            <div class="table-responsive">
                <table class="table table-card-box m-b-0">
                    <tbody>
                    <?php 
                            $toggle = false;
                            
                            foreach ($entry_details as $data){ 
                                if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
                                    continue;
                                }

                                if($toggle){
                                    $toggle = false;
                                    $row_style = 'class="alt"';
                                }else{
                                    $toggle = true;
                                    $row_style = '';
                                }

                                $row_markup = '';
                                $element_id = $data['element_id'];

                                if($data['element_type'] == 'section' || $data['element_type'] == 'textarea') {
                                    if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
                                        $section_separator = '<br/>';
                                    }else{
                                        $section_separator = '';
                                    }

                                    $section_break_content = '<span class="mf_section_title"><strong>'.nl2br($data['label']).'</strong></span>'.$section_separator.'<span class="mf_section_content">'.nl2br($data['value']).'</span>';

                                    $row_markup .= "<tr {$row_style}>\n";
                                    $row_markup .= "<td width=\"100%\" colspan=\"2\">{$section_break_content}</td>\n";
                                    $row_markup .= "</tr>\n";
                                }else if($data['element_type'] == 'signature') {
                                    if($data['element_size'] == 'small'){
                                        $canvas_height = 70;
                                        $line_margin_top = 50;
                                    }else if($data['element_size'] == 'medium'){
                                        $canvas_height = 130;
                                        $line_margin_top = 95;
                                    }else{
                                        $canvas_height = 260;
                                        $line_margin_top = 200;
                                    }

                $signature_markup = <<<EOT
                <div id="mf_sigpad_{$element_id}" class="mf_sig_wrapper {$data['element_size']}">
                    <canvas class="mf_canvas_pad" width="309" height="{$canvas_height}"></canvas>
                </div>
                <script type="text/javascript">
                    $(function(){
                        var sigpad_options_{$element_id} = {
                            drawOnly : true,
                            displayOnly: true,
                            bgColour: '#fff',
                            penColour: '#000',
                            output: '#element_{$element_id}',
                            lineTop: {$line_margin_top},
                            lineMargin: 10,
                            validateFields: false
                        };
                        var sigpad_data_{$element_id} = {$data['value']};
                        $('#mf_sigpad_{$element_id}').signaturePad(sigpad_options_{$element_id}).regenerate(sigpad_data_{$element_id});
                    });
                </script>
EOT;

                                    $row_markup .= "<tr>\n";
                                    $row_markup .= "<td><strong>{$data['label']}</strong></td>\n";
                                    $row_markup .= "<td width=\"60%\">{$signature_markup}</td>\n";
                                    $row_markup .= "</tr>\n";
                                }else{
                                    $row_markup .= "<tr {$row_style}>\n";
                                    $row_markup .= "<td><strong>{$data['label']}</strong></td>\n";
                                    $row_markup .= "<td>".nl2br($data['value'])."</td>\n";
                                    $row_markup .= "</tr>\n";
                                }

                                echo $row_markup;
                            } 
                    ?>  	
                    </tbody>
                </table>
            </div>
        </div>
    
    </div>
</div>