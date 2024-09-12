<?php use_helper('I18N') ?>
<div class="pageheader">
  <h2><?php echo __("Agenda") ?></h2>
</div>
<div class="contentpanel">
   <div class="panel panel-dark">
	   <div class="panel-heading">
            <h3 class="panel-title"><?php echo __("Agenda").' '.$agenda_columns->getFormname() ?></h3>
			<div class="pull-right">
			   <a href="<?php echo url_for('/plan/dashboard/index') ?>" class="btn btn-info-alt settings-margin42"><?php echo __('Back to Dashboard') ?></a>
			   <a href="<?php echo url_for('/plan/agenda/agendaexcel?id='.$agenda_columns->getId().'&form='.urlencode($agenda_columns->getFormname())) ?>" class="btn btn-primary-alt settings-margin42"><?php echo __('Excel') ?></a>
			</div>
		</div>
       <div class="panel-body panel-body-nopadding">
			<table class="table">
				<thead>
					<tr>
					<?php foreach($items_positions as $pos): ?>
					<th><?php echo $array_element_name[$pos] ?></th>
					<?php endforeach; ?>
					<th>Action</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$q = Doctrine_Query::create()
						->from('SubMenuButtons a')
						->where('a.sub_menu_id = ?', $agenda_columns->getStage());
				$submenubuttons = $q->execute();
				?>
				<?php foreach($applications as $app): ?>
				<tr>
					<?php foreach($items_positions as $p): ?>
						<?php switch($p){ 
							case "membership_no":
								if($app['member_no_element_id'] and $app['members_form'] and $app['members_form_entry']){
									$q="SELECT element_".$app['member_no_element_id'];
									$q.=" FROM ap_form_".$app['members_form'];
									$q.=" WHERE id = ".$app['members_form_entry'];
									$membeship=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
									echo '<td>'.$membeship[0]['element_'.$app['member_no_element_id']].'</td>';
								}else{
									echo '<td>N/A</td>';
								}
								break;
							case "membership_address":
								if($app['member_no_element_id'] and $app['members_form'] and $app['members_form_entry']){
									$q="SELECT *";
									$q.=" FROM ap_form_".$app['members_form'];
									$q.=" WHERE id = ".$app['members_form_entry'];
									$membeship=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
									
									if(array_key_exists('element_'.$app['member_address'],$membeship[0])){
										echo '<td>'.$membeship[0]['element_'.$app['member_address']].'</td>';
									}else{
										error_log('-----key doesn\'t exist----');
										$output_string='';
										//Loop & check element existance
										foreach(range(1,10) as $e){
											//form to show particular details 
											$element='element_'.$app['member_address'].'_'.$e;
											error_log('------'.$element.'-------');
											if(strlen($membeship[0][$element])){
												if($agenda_columns->getFormId() != 14):
												error_log('----Found----'.$element.'-----');
												$q="SELECT COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".sfConfig::get('app_mysql_db')."' AND TABLE_NAME = 'ap_form_".$app['members_form']."' AND COLUMN_NAME = '".$element."'";
												error_log('-----Query Comments----');
												error_log($q);
												error_log('-----------');
												$comments=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
												error_log(print_r($comments,true));
												if($e == 1){
													$output_string.=$comments[0]['COLUMN_COMMENT'].': '.$membeship[0][$element];
												}else{
													$output_string.=' , '.$comments[0]['COLUMN_COMMENT'].': '.$membeship[0][$element];
												}
												else:
												if($e == 1){
													$output_string.=$membeship[0][$element];
												}else{
													$output_string.=' , '.$membeship[0][$element];
												}
												endif;
											}else{
												error_log('-----Not Found-----');
											}
										}
										error_log('-----Message--'.$output_string);
										if(strlen($output_string)){
											echo '<td>'.$output_string.'</td>';
										}else{
											echo '<td>N/A</td>';
										}
									}
								}else{
									echo '<td>N/A</td>';
								}
								break;
							case "conditions":
								$q="SELECT conditions_of_approval.short_name,conditions_of_approval.description";
								$q.=" FROM approval_condition";
								$q.=" LEFT JOIN conditions_of_approval ON conditions_of_approval.id = approval_condition.condition_id";
								$q.=" WHERE approval_condition.entry_id = ".$app['form_entry_id'];
								$conditions=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
								$condition_str='';
								//if($agenda_columns->getFormId() != 14):
									//echo '<td><ul>';
								//else:
									echo '<td>';
								//endif;
								foreach($conditions as $k => $c){
									if($agenda_columns->getFormId() != 14):
										//echo '<li>'.$c['short_name'].'-'.substr($c['description'],0,20).'....'.'</li>';
										if($k == 0):
											$condition_str.='('.Doctrine_Core::getTable('FormEntry')->integerToRoman(1).')';
										endif;
										if($k == (count($conditions)-1)):
											$condition_str.=' - ('.Doctrine_Core::getTable('FormEntry')->integerToRoman(($k+1)).')';
										endif;
									else:
										if($k == 0):
											$condition_str.=$c['short_name'];
										else:
											$condition_str.=' ,'.$c['short_name'];
										endif;
									endif;
								}
								//if($agenda_columns->getFormId() != 14):
									//echo '</ul></td>';
								//else:
									echo $condition_str.'</td>';
								//endif;
								break;
							case 'days':
								//Calculate days 
								echo '<td>'.Doctrine_Core::getTable('FormEntry')->getDaysinCirculation($app['date_of_submission'],$app['form_entry_id']).'</td>';
								break;
							default:
								$element_str=explode('_',$p); 
								echo '<td>';
								//echo '<pre>';
								//print_r($element_str);
								//echo '</pre>';
								if($element_str[0] == 'element'){
									//Check if element has values in ap_element_options
									$element_option_array=Doctrine_Core::getTable('ApElementOptions')->getElementOptions($agenda_columns->getFormId(),$element_str[1]);
									//echo '<pre>';
									//print_r($element_option_array);
									//echo '</pre>';
									if(count($element_option_array)>1){
										$element_type=Doctrine_Core::getTable('ApFormElements')->getElementsType($agenda_columns->getFormId(),$element_str[1]);
										if($element_type == 'checkbox'){
											//Get the elements values
											echo '<ul>';
											for($i=1;$i<count($element_option_array);$i++){
												$ele_val=$app['element_'.$element_str[1].'_'.$i];
												if($ele_val == 1){
													echo '<li>'.$element_option_array[$i].'</li>';
												}
											}
											echo '</ul>';
										}else{
											//var for mixed use description
											$elem_desc_mx='';
											if(strcasecmp(trim($element_option_array[$app[$p]]),'Mixed use') == 0){
												//Get element of mixed description
												$q1=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT element_id FROM ap_form_elements WHERE form_id = ".$agenda_columns->getFormId()." AND element_title LIKE '%mixed use%' AND element_status = 1 LIMIT 1");
												if(count($q1)){
													$elem_desc_mx=' ( '.trim($app['element_'.$q1[0]['element_id']]).' ) ';
												}
											}
											echo $element_option_array[$app[$p]].''.$elem_desc_mx;
										}
									}else{
										if(array_key_exists($p,$app)){
											echo $app[$p];
										}else{
											error_log('-----key doesn\'t exist----');
											$output_string='';
											//Loop & check element existance
											foreach(range(1,10) as $e){
												//form to show particular details 
												$element=$p.'_'.$e;
												error_log('------'.$element.'-------');
												if(strlen($app[$element])){
													if($agenda_columns->getFormId() != 14):
													error_log('----Found----'.$element.'-----');
													$q="SELECT COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".sfConfig::get('app_mysql_db')."' AND TABLE_NAME = 'ap_form_".$agenda_columns->getFormId()."' AND COLUMN_NAME = '".$element."'";
													error_log('-----Query Comments----');
													error_log($q);
													error_log('-----------');
													$comments=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
													error_log(print_r($comments,true));
													if($e == 1){
														$output_string.=$comments[0]['COLUMN_COMMENT'].': '.$app[$element];
													}else{
														$output_string.=' , '.$comments[0]['COLUMN_COMMENT'].': '.$app[$element];
													}
													else:
													if($e == 1){
														$output_string.=$app[$element];
													}else{
														$output_string.=' , '.$app[$element];
													}
													endif;
												}else{
													error_log('-----Not Found-----');
												}
											}
											error_log('-----Message--'.$output_string);
											if(strlen($output_string)){
												echo $output_string;
											}else{
												echo $app[$p];
											}
										}
									}
								}else{
									if($p == 'fullname'){
										$membeship_no='';
										if($app['member_no_element_id'] and $app['members_form'] and $app['members_form_entry']){
											$q="SELECT element_".$app['member_no_element_id'];
											$q.=" FROM ap_form_".$app['members_form'];
											$q.=" WHERE id = ".$app['members_form_entry'];
											$membeship=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
											$membeship_no=$membeship[0]['element_'.$app['member_no_element_id']];
										}else{
											$membeship_no='';
										}
										echo $membeship_no.' - '.$app[$p];
									}else{
										//Explode
										$paid_arr=explode(',',$app['paid']);
										$needle=array_search('PAID',$paid_arr);
										switch($p){
											case 'paid':
												if($needle !== false){
													echo $paid_arr[$needle];
												}else{
													echo $paid_arr[0];
												}
												break;
											case 'invoice_number':
												$invoices=explode(',',$app['invoice_number']);
												if($needle !== false){
													echo $invoices[$needle];
												}else{
													echo $invoices[0];
												}
												break;
											case 'total_amount':
												$amt_invoices=explode(',',$app['total_amount']);
												if($needle !== false){
													echo $amt_invoices[$needle];
												}else{
													echo $amt_invoices[0];
												}
												break;
											default:
												echo $app[$p];
										}
									}
								}
								echo '</td>';
						}
						?>
						
					<?php endforeach; ?>
					<td>
						<div class="btn-group">
						<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="#">Action<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<?php if($sf_user->mfHasCredential('editapplication')): ?>
							<li><a href="<?php echo url_for('/plan/applications/edit?form_id='.$agenda_columns->getFormId().'&id='.$app['id']) ?>" target="_blank">Edit</a></li>
							<?php endif; ?>
							<li>
								<a target="_blank"  href="<?php echo public_path('plan/applications/view/id/'.$app['form_entry_id']) ?>"> <?php echo __('View Details') ?> </a>
							</li>  
							<?php if ($sf_user->mfHasCredential("approve_applications")): ?>
								<?php
								foreach ($submenubuttons as $submenubutton) {
									?> 
									<?php
									$q = Doctrine_Query::create()
											->from('Buttons a')
											->where('a.id = ?', $submenubutton->getButtonId());
									$buttons = $q->execute();
									?>
									<?php
									foreach ($buttons as $button) {
										if ($sf_user->mfHasCredential("accessbutton" . $button->getId())) {
											?>

											<li>
												<a target="_blank"  onClick="if (confirm('Are you sure?')) {
															window.location = '<?php echo $button->getLink(); ?>&entryid=<?php echo $app['form_entry_id'] ?>&form_id=<?php echo $agenda_columns->getFormId() ?>';
												} else {
													return false;
												}"><?php echo $button->getTitle(); ?></a>
											</li>




											<?php
										}
									}
									?>


								<?php } ?>
							<?php endif; ?>
							
						</ul>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- Summary table --->
   <div class="panel panel-dark">
	   <div class="panel-heading">
            <h3 class="panel-title"><?php echo __("Summary of").' '.$agenda_columns->getFormname() ?></h3>
			<div class="pull-right">
			   <a href="<?php echo url_for('/plan/dashboard/index') ?>" class="btn btn-info-alt settings-margin42"><?php echo __('Back to Dashboard') ?></a>
			   <a href="<?php echo url_for('/plan/agenda/excelsummary?id='.$agenda_columns->getId().'&form='.urlencode($agenda_columns->getFormname())) ?>" class="btn btn-primary-alt settings-margin42"><?php echo __('Excel') ?></a>
			</div>
		</div>
       <div class="panel-body panel-body-nopadding">
			<table class="table">
				<tbody>
					<?php foreach($summary_array as $summary): ?>
						<tr>
						<?php foreach($summary as $k => $s): ?>
								<td><?php echo $k.' : '.$s?></td>
						<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>