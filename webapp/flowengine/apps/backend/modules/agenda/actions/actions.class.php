<?php

/**
 * agenda actions.
 *
 * @package    MasterCPMIS
 * @subpackage agenda
 * @author     Webmasters Africa <thomas.juma@webmastersafrica.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agendaActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
    $this->agenda_columnss = Doctrine_Core::getTable('AgendaColumns')
      ->createQuery('a')
      ->execute();
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->agenda_columns = Doctrine_Core::getTable('AgendaColumns')->find(array($request->getParameter('id')));
    $this->forward404Unless($this->agenda_columns);
	//Get form entries
	$q="SELECT form_entry.application_id, form_entry.id as form_entry_id, form_entry.date_of_submission, ap_form_".$this->agenda_columns->getFormId().".* , GROUP_CONCAT(mf_invoice.invoice_number ORDER BY mf_invoice.id DESC) as invoice_number , GROUP_CONCAT(mf_invoice.total_amount ORDER BY mf_invoice.id) as total_amount, GROUP_CONCAT((CASE WHEN mf_invoice.paid = 2 THEN 'PAID' WHEN mf_invoice.paid = 3 THEN 'CANCELLED' WHEN mf_invoice.paid = 15 THEN 'PENDING CONFIRMATION' WHEN mf_invoice.paid = 1 THEN 'PENDING' ELSE NULL END)) as paid, sf_guard_user_profile.fullname, sf_guard_user_categories.name as registered_as, sf_guard_user_categories.member_no_element_id, sf_guard_user_categories.member_address, mf_user_profile.form_id as members_form, mf_user_profile.entry_id as members_form_entry";
	$q.=" FROM form_entry";
	$q.=" LEFT JOIN ap_form_".$this->agenda_columns->getFormId()." ON ap_form_".$this->agenda_columns->getFormId().".id = form_entry.entry_id";
	$q.=" LEFT JOIN mf_invoice ON form_entry.id = mf_invoice.app_id";
	$q.=" LEFT JOIN sf_guard_user_profile ON form_entry.user_id = sf_guard_user_profile.user_id";
	$q.=" LEFT JOIN sf_guard_user_categories ON sf_guard_user_profile.registeras = sf_guard_user_categories.id";
	$q.=" LEFT JOIN mf_user_profile ON form_entry.user_id = mf_user_profile.user_id";
	//$q.=" LEFT JOIN approval_condition ON approval_condition.entry_id = form_entry.id";
	//$q.=" LEFT JOIN conditions_of_approval ON conditions_of_approval.id = approval_condition.condition_id";
 	$q.=" WHERE form_entry.form_id = ".$this->agenda_columns->getFormId()." AND form_entry.approved = ".$this->agenda_columns->getStage()." AND form_entry.deleted_status = 0 AND form_entry.parent_submission = 0 GROUP BY form_entry.id";
	error_log('--------QUERY---------------');
	error_log('----- \n'.$q.'\n ------');
	error_log('-----------------------');
	//$this->applications=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
	$this->applications=Doctrine_Manager::getInstance()->getCurrentConnection()->execute($q);
	//Element column name
	$all_elements=Doctrine_Core::getTable('ApFormElements')->getAllFormFieldsIncludeEmail($this->agenda_columns->getFormId());
	$app_colms=Doctrine_Core::getTable('FormEntry')->getColumnsAgenda();
	$this->array_element_name=array_merge($app_colms,$all_elements);
	$this->items_positions=json_decode($this->agenda_columns->getPosition());
	//Summary 
	//Land size element -- will be improved
	//check element attribute
	//plotsize
	$q=Doctrine_Query::create()
		->from('ApFormElements e')
		->where('e.form_id = ? and e.element_status =? and e.element_plotsize =? and e.element_type LIKE ?',[$this->agenda_columns->getFormId(),1,1,'number']);
	$plotsize=$q->fetchOne();
	$ha='';
	if($plotsize){
		error_log('---------plot size ------'.$plotsize->getElementId().'----'.$plotsize->getElementTitle());
		$ha=$plotsize->getElementId();
	}
	//element_permittedgroundcoverage
	$q=Doctrine_Query::create()
		->from('ApFormElements e')
		->where('e.form_id = ? and e.element_status =? and e.element_permittedgroundcoverage =? and e.element_type LIKE ?',[$this->agenda_columns->getFormId(),1,1,'number']);
	$permittedgroundcoverage=$q->fetchOne();
	$groundcoverage='';
	if($permittedgroundcoverage){
		$groundcoverage=$permittedgroundcoverage->getElementId();
	}
	//element_projectcost
	$q=Doctrine_Query::create()
		->from('ApFormElements e')
		->where('e.form_id = ? and e.element_status =? and e.element_projectcost =? and e.element_type LIKE ?',[$this->agenda_columns->getFormId(),1,1,'number']);
	$projectcost=$q->fetchOne();
	$cost='';
	if($projectcost){
		$cost=$projectcost->getElementId();
	}
	error_log('------ha-----'.$ha);
	$q="SELECT";
	$query_arr=[];
	if(strlen($ha)){
		$query_arr[]=" SUM(ap_form_".$this->agenda_columns->getFormId().".element_".$ha.") as total_ha";
	}
	if(strlen($groundcoverage)){
		$query_arr[]=" SUM(ap_form_".$this->agenda_columns->getFormId().".element_".$groundcoverage.") as total_groundcoverage";
	}
	if(strlen($cost)){
		$query_arr[]=" SUM(ap_form_".$this->agenda_columns->getFormId().".element_".$cost.") as total_cost";
	}
	if(count($query_arr)){
		$q.=implode(",",$query_arr);
	}
	$q.=" COUNT(form_entry.id) as total_apps FROM ap_form_".$this->agenda_columns->getFormId()." LEFT JOIN form_entry ON form_entry.entry_id = ap_form_".$this->agenda_columns->getFormId().".id WHERE form_entry.form_id = ".$this->agenda_columns->getFormId()." AND form_entry.approved = ".$this->agenda_columns->getStage()." AND form_entry.deleted_status = 0 AND form_entry.parent_submission = 0";
	$this->summary_array=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
	$this->setLayout('layout_agenda');
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
    $this->form = new AgendaColumnsForm();
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
	  //error_reporting(E_ALL);
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new AgendaColumnsForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
    $this->forward404Unless($agenda_columns = Doctrine_Core::getTable('AgendaColumns')->find(array($request->getParameter('id'))), sprintf('Object agenda_columns does not exist (%s).', $request->getParameter('id')));
    $this->form = new AgendaColumnsForm($agenda_columns);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
	  //error_reporting(E_ALL);
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($agenda_columns = Doctrine_Core::getTable('AgendaColumns')->find(array($request->getParameter('id'))), sprintf('Object agenda_columns does not exist (%s).', $request->getParameter('id')));
    $this->form = new AgendaColumnsForm($agenda_columns);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
    //$request->checkCSRFProtection();

    $this->forward404Unless($agenda_columns = Doctrine_Core::getTable('AgendaColumns')->find(array($request->getParameter('id'))), sprintf('Object agenda_columns does not exist (%s).', $request->getParameter('id')));
    $agenda_columns->delete();

    $this->redirect('/plan/agenda/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
		$values=$form->getValues();
		$element_id=json_encode($values['element_id']);
		$entry_column=json_encode($values['entry_column']);
      $agenda_columns = $form->save();
		$agenda_columns->setElementId($element_id);
		$agenda_columns->setEntryColumn($entry_column);
		$agenda_columns->save();
      $this->redirect('/plan/agenda/index');
    }
  }
  public function executeAgendaapparray(sfWebRequest $request)
  {
	echo json_encode(Doctrine_Core::getTable('FormEntry')->getColumnsAgenda());
	exit;
  }
  public function executeShowbystage(sfWebRequest $request)
  {
    //$this->setLayout("layout-settings");
	$stage_id=$request->getParameter('stage');
	$agenda_column=Doctrine_Core::getTable('AgendaColumns')->createQuery('a')->where('a.stage = ?',$stage_id)->execute();
	$this->forward404Unless($agenda_column,'No agenda found for stage id '.$stage_id.'!');
	$this->agenda_column=$agenda_column;
	$this->stage_id=$stage_id;
	//FOR PIS
	/*$q="SELECT (CASE WHEN form_entry.form_id = 8004 THEN 'Amalgamation' WHEN form_entry.form_id = 1852 THEN 'Subdivision' WHEN form_entry.form_id = 9198 THEN 'Extension of Use' WHEN form_entry.form_id = 1238 THEN 'Change of Use' END) as application, avg(DATEDIFF(CURDATE(),CAST(form_entry.date_of_submission as DATE))) as avg_days, SUM((CASE WHEN form_entry.form_id = 8004 THEN ap_form_8004.element_66 WHEN form_entry.form_id = 1852 THEN ap_form_1852.element_62 WHEN form_entry.form_id = 9198 THEN ap_form_9198.element_58 WHEN form_entry.form_id = 1238 THEN ap_form_1238.element_59 END)) as total_ha, COUNT(form_entry.id) as plans, SUM(mf_invoice.total_amount) as total_amount FROM form_entry LEFT JOIN ap_form_1238 ON ap_form_1238.id = form_entry.entry_id LEFT JOIN ap_form_8004 ON ap_form_8004.id = form_entry.entry_id LEFT JOIN ap_form_1852 ON ap_form_1852.id = form_entry.entry_id LEFT JOIN ap_form_9198 ON ap_form_9198.id = form_entry.entry_id LEFT JOIN mf_invoice ON mf_invoice.app_id = form_entry.id WHERE form_entry.approved = ".$stage_id." AND form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 GROUP BY form_entry.form_id";
	$this->summary_applications=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);*/
  }
  public function executeSummaryexcel (sfWebRequest $request)
  {
	$stage_id=$request->getParameter('stage');
	$q="SELECT (CASE WHEN form_entry.form_id = 8004 THEN 'Amalgamation' WHEN form_entry.form_id = 1852 THEN 'Subdivision' WHEN form_entry.form_id = 9198 THEN 'Extension of Use' WHEN form_entry.form_id = 1238 THEN 'Change of Use' END) as application, avg(DATEDIFF(CURDATE(),CAST(form_entry.date_of_submission as DATE))) as avg_days, SUM((CASE WHEN form_entry.form_id = 8004 THEN ap_form_8004.element_66 WHEN form_entry.form_id = 1852 THEN ap_form_1852.element_62 WHEN form_entry.form_id = 9198 THEN ap_form_9198.element_58 WHEN form_entry.form_id = 1238 THEN ap_form_1238.element_59 END)) as total_ha, COUNT(form_entry.id) as plans, SUM(mf_invoice.total_amount) as total_amount FROM form_entry LEFT JOIN ap_form_1238 ON ap_form_1238.id = form_entry.entry_id LEFT JOIN ap_form_8004 ON ap_form_8004.id = form_entry.entry_id LEFT JOIN ap_form_1852 ON ap_form_1852.id = form_entry.entry_id LEFT JOIN ap_form_9198 ON ap_form_9198.id = form_entry.entry_id LEFT JOIN mf_invoice ON mf_invoice.app_id = form_entry.id WHERE form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 AND form_entry.approved = ".$stage_id." GROUP BY form_entry.form_id";
	$summary_applications=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
	
	$colms=array('Development Category','Average Days Taken','Plot Size','No. of Items','Submission Fees');
	$data=array();
	$total_days=0;
	$total_ha=0;
	$total_plans=0;
	$total_fees=0;
	foreach($summary_applications as $summary){
		$inner_arr=array();
		$inner_arr[]=$summary['application'];
		$inner_arr[]=$summary['avg_days']; 
		$total_days+=$summary['avg_days'];
		$inner_arr[]=$summary['total_ha']; 
		$total_ha+=$summary['total_ha'];
		$inner_arr[] = $summary['plans']; 
		$total_plans+=$summary['plans'];
		$inner_arr[] = number_format($summary['total_amount'],0); 
		$total_fees+=$summary['total_amount'];
		$data[]=$inner_arr;
	}
	$data[]=array('Total',$total_days,$total_ha,$total_plans,number_format($total_fees,0));
	//use function to output excel
	$this->ReportGeneratorAgenda($colms,$data);
  }  
  public function executeAgendaexcel (sfWebRequest $request)
  {
    $agenda_columns = Doctrine_Core::getTable('AgendaColumns')->find(array($request->getParameter('id')));
    $this->forward404Unless($agenda_columns);
	//Get form entries
	$q="SELECT form_entry.application_id, form_entry.id as form_entry_id, form_entry.date_of_submission, ap_form_".$agenda_columns->getFormId().".* , GROUP_CONCAT(mf_invoice.invoice_number ORDER BY mf_invoice.id DESC) as invoice_number , GROUP_CONCAT(mf_invoice.total_amount ORDER BY mf_invoice.id) as total_amount, GROUP_CONCAT((CASE WHEN mf_invoice.paid = 2 THEN 'PAID' WHEN mf_invoice.paid = 3 THEN 'CANCELLED' WHEN mf_invoice.paid = 15 THEN 'PENDING CONFIRMATION' WHEN mf_invoice.paid = 1 THEN 'PENDING' ELSE NULL END)) as paid, sf_guard_user_profile.fullname, sf_guard_user_categories.name as registered_as, sf_guard_user_categories.member_no_element_id, sf_guard_user_categories.member_address, mf_user_profile.form_id as members_form, mf_user_profile.entry_id as members_form_entry";
	$q.=" FROM form_entry";
	$q.=" LEFT JOIN ap_form_".$agenda_columns->getFormId()." ON ap_form_".$agenda_columns->getFormId().".id = form_entry.entry_id";
	$q.=" LEFT JOIN mf_invoice ON form_entry.id = mf_invoice.app_id";
	$q.=" LEFT JOIN sf_guard_user_profile ON form_entry.user_id = sf_guard_user_profile.user_id";
	$q.=" LEFT JOIN sf_guard_user_categories ON sf_guard_user_profile.registeras = sf_guard_user_categories.id";
	$q.=" LEFT JOIN mf_user_profile ON form_entry.user_id = mf_user_profile.user_id";
	//$q.=" LEFT JOIN approval_condition ON approval_condition.entry_id = form_entry.id";
	//$q.=" LEFT JOIN conditions_of_approval ON conditions_of_approval.id = approval_condition.condition_id";
 	$q.=" WHERE form_entry.form_id = ".$agenda_columns->getFormId()." AND form_entry.approved = ".$agenda_columns->getStage()." AND form_entry.deleted_status = 0 AND form_entry.parent_submission = 0 GROUP BY form_entry.id";
	error_log('------QUERY---------');
	error_log($q);
	error_log('---------------');
	$applications=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
	
	//Element column name
	$all_elements=Doctrine_Core::getTable('ApFormElements')->getAllFormFieldsIncludeEmail($agenda_columns->getFormId());
	$app_colms=Doctrine_Core::getTable('FormEntry')->getColumnsAgenda();
	$array_element_name=array_merge($app_colms,$all_elements);
	$items_positions=json_decode($agenda_columns->getPosition());
	//Create Array to pass to generator method
	$colms=array();
	foreach($items_positions as $pos){
		$colms[]=$array_element_name[$pos];
	}
	$data=array();
	foreach($applications as $app){
		$inner_arr=array();
		foreach($items_positions as $p){
			switch($p){ 
				case "membership_no":
					if($app['member_no_element_id'] and $app['members_form'] and $app['members_form_entry']){
						$q="SELECT element_".$app['member_no_element_id'];
						$q.=" FROM ap_form_".$app['members_form'];
						$q.=" WHERE id = ".$app['members_form_entry'];
						$membeship=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
						$inner_arr[]=$membeship[0]['element_'.$app['member_no_element_id']];
					}else{
						$inner_arr[]="N/A";
					}
					break;
			case "membership_address":
				if($app['member_no_element_id'] and $app['members_form'] and $app['members_form_entry']){
					$q="SELECT *";
					$q.=" FROM ap_form_".$app['members_form'];
					$q.=" WHERE id = ".$app['members_form_entry'];
					$membeship=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
					
					if(array_key_exists('element_'.$app['member_address'],$membeship[0])){
						$inner_arr[]=$membeship[0]['element_'.$app['member_address']];
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
							$inner_arr[]=$output_string;
						}else{
							$inner_arr[]= 'N/A';
						}
					}
				}else{
					$inner_arr[]='N/A';
				}
				break;
				case "conditions":
					$q="SELECT conditions_of_approval.short_name,conditions_of_approval.description";
					$q.=" FROM approval_condition";
					$q.=" LEFT JOIN conditions_of_approval ON conditions_of_approval.id = approval_condition.condition_id";
					$q.=" WHERE approval_condition.entry_id = ".$app['form_entry_id'];
					$conditions=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
					$condition_str='';
					foreach($conditions as $k => $c){
						if($k == 0):
							if($agenda_columns->getFormId() == 14):
								$condition_str.=$c['short_name'];
							else:
								$condition_str.='('.Doctrine_Core::getTable('FormEntry')->integerToRoman(1).')';
								//$condition_str.=$c['short_name'].'-'.$c['description'];
							endif;
						else:
							if($agenda_columns->getFormId() == 14):
								$condition_str.=' ,'.$c['short_name'];
							else:
								if($k == (count($conditions)-1)):
									$condition_str.=' - ('.Doctrine_Core::getTable('FormEntry')->integerToRoman(($k+1)).')';
								endif;
							endif;
						endif;
					}
					if(strlen($condition_str)){
						$inner_arr[]=$condition_str;
					}else{
						$inner_arr[]= 'N/A';
					}
				break;
				case 'days':
					//Calculate days 
					$inner_arr[]= Doctrine_Core::getTable('FormEntry')->getDaysinCirculation($app['date_of_submission'],$app['form_entry_id']);
					break;
				default:
					$element_str=explode('_',$p); 
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
								$opti_str='';
								for($i=1;$i<count($element_option_array);$i++){
									$ele_val=$app['element_'.$element_str[1].'_'.$i];
									if($ele_val == 1){
										$opti_str.=$element_option_array[$i];
									}
								}
								$inner_arr[]=$opti_str;
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
								$inner_arr[]= $element_option_array[$app[$p]].''.$elem_desc_mx;
							}
						}else{
							if(array_key_exists($p,$app)){
								$inner_arr[]= $app[$p];
							}else{
								error_log('-----key doesn\'t exist----');
								$output_string='';
								//Loop & check element existance
								foreach(range(1,10) as $e){
									$element=$p.'_'.$e;
									if(strlen($app[$element])){
										if($agenda_columns->getFormId() != 14):
										$q="SELECT COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".sfConfig::get('app_mysql_db')."' AND TABLE_NAME = 'ap_form_".$agenda_columns->getFormId()."' AND COLUMN_NAME = '".$element."'";
										$comments=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
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
									}
								}
								if(strlen($output_string)){
									$inner_arr[]= trim($output_string);
								}else{
									$inner_arr[]= $app[$p];
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
							$inner_arr[]= trim($membeship_no.' - '.$app[$p]);
						}else{
							//Explode
							$paid_arr=explode(',',$app['paid']);
							$needle=array_search('PAID',$paid_arr);
							switch($p){
								case 'paid':
									if($needle !== false){
										$inner_arr[] = $paid_arr[$needle];
									}else{
										$inner_arr[] = $paid_arr[0];
									}
									break;
								case 'invoice_number':
									$invoices=explode(',',$app['invoice_number']);
									if($needle !== false){
										$inner_arr[] = $invoices[$needle];
									}else{
										$inner_arr[] = $invoices[0];
									}
									break;
								case 'total_amount':
									$amt_invoices=explode(',',$app['total_amount']);
									if($needle !== false){
										$inner_arr[] = $amt_invoices[$needle];
									}else{
										$inner_arr[] = $amt_invoices[0];
									}
									break;
								default:
									$inner_arr[] = $app[$p];
							}
						}
						
					}
			}
		}		
		$data[]=$inner_arr;
	}
	//use function to output excel
	Outputsheet::ReportGenerator("Agenda Report -".date("Y-m-d"), $colms,$data);

  }
	protected function ReportGeneratorAgenda($columns, $records) {
		date_default_timezone_set('Africa/Nairobi');

		if (PHP_SAPI == 'cli')
			die('This example should only be run from a Web Browser');

		/** Include PHPExcel */
		//require_once dirname(__FILE__) . '/../../../../../lib/vendor/phpexcel/Classes/PHPExcel.php';

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator(sfConfig::get('app_organisation_name'))
				->setTitle("Agenda report");
		// Add some data
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$alpha_count = "B";
		foreach ($columns as $key => $value) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_count)->setAutoSize(true);
			$alpha_count++;
		}

		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', 'No');
		$alpha_count = "B";
		foreach ($columns as $key => $value) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . '4', $value);
			$alpha_count++;
		}

		$alpha_count--;

		$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A4:' . ($alpha_count) . '4')->getFill()->getStartColor()->setARGB('46449a');

		$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

		$alpha_count = "B";
		foreach ($columns as $key => $value) {
			$objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			$alpha_count++;
		}

		$alpha_count--;

		$objPHPExcel->getActiveSheet()->getStyle('A1:' . ($alpha_count) . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1:' . ($alpha_count) . '1')->getFill()->getStartColor()->setARGB('504dc5');
		$objPHPExcel->getActiveSheet()->getStyle('A2:' . ($alpha_count) . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A2:' . ($alpha_count) . '2')->getFill()->getStartColor()->setARGB('504dc5');
		$objPHPExcel->getActiveSheet()->getStyle('A3:' . ($alpha_count) . '3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A3:' . ($alpha_count) . '3')->getFill()->getStartColor()->setARGB('504dc5');
		
		$q=Doctrine_Query::create()
			->from('ApSettings s');
		$setting=$q->fetchOne();
		if($setting && strlen($setting->getAdminImageUrl())){
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName('Logo');
			$objDrawing->setDescription('Logo');
			$objDrawing->setPath('./'.$setting->getUploadDir().'/'.$setting->getAdminImageUrl());
			$objDrawing->setHeight(60);
			$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
		}

		$alpha_count = "B";
		foreach ($columns as $key => $value) {
			$objPHPExcel->getActiveSheet()->getStyle($alpha_count . '4')->getFont()->setBold(true);
			$alpha_count++;
		}

		/**
		 * Fetch all applications linked to the filtered 'type of application' and the 'start date'
		 */
		$count = 5;

		// Miscellaneous glyphs, UTF-8
		$alpha_count = "B";

		foreach ($records as $record_columns) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $count, $count - 4);
			$alpha_count = "B";
			foreach ($record_columns as $key => $value) {
				//error_log("The Value >>> ".$value) ;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha_count . $count, $value);
				$alpha_count++;
			}
			$count++;
		}


		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('Report');


		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);


		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="report1.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
	public function executeAgendaarrays(sfWebRequest $request)
	{
		$form_id=$request->getParameter('form');
		$form_elements = Doctrine_Core::getTable('ApFormElements')->getAllFormFieldsIncludeEmail($form_id);
		$app_elements = Doctrine_Core::getTable('FormEntry')->getColumnsAgenda();
		$merged_elements=array_merge($form_elements,$app_elements);
		echo json_encode($merged_elements);
		exit;
	}
	public function executeAgendasummary(sfWebRequest $request)
	{
		$stage = $request->getParameter('stage');
		//Search agenda column for forms that will have an agenda on that stage
		$agendas=Doctrine_Core::getTable('AgendaColumns')->findByStage($stage);
		$forms=array();
		foreach($agendas as $a){
			$forms[]=$a['form_id'];
		}
		foreach($forms as $k => $f){
			$apps=Doctrine_Core::getTable('FormEntry')->createQuery('e')->select('e.entry_id')->where('e.form_id = ? AND e.approved =? AND e.parent_submission = ? AND e.deleted_status = ?',array($f,$stage,0,0))->fetchArray();
			$entries_.''.$k=array();
			$form_entry_.''.$k=array();
			foreach($apps as $app){
				$entries_.''.$k[]=$app['entry_id'];
				$form_entry_.''.$k[]=$app['id'];
			}
		}
		//create query 
		foreach($forms as $k => $f){
			$apps=Doctrine_Core::getTable('FormEntry')->createQuery('e')->select('e.entry_id')->where('e.form_id = ? AND e.approved =? AND e.parent_submission = ? AND e.deleted_status = ?',array($f,$stage,0,0))->fetchArray();
			error_log(print_r($apps,true));
			$form="ap_form_".$f;
			$sum_ha='';
			$invoice=0;
			switch($f){
				case 8004:
					$sum_ha='element_66';
					$invoice=2;
					break;
				case 1852:
					$sum_ha='element_62';
					$invoice=3;
					break;
				case 9198:
					$sum_ha='element_58';
					$invoice=5;
					break;
				case 1238:
					$sum_ha='element_59';
					$invoice=6;
					break;
			}
			/*foreach($apps as $a){
				$q="SELECT COUNT($form.id) as apps, SUM($form.$sum_ha) as size, SUM(mf_invoice.total_amount) as total FROM $form LEFT JOIN mf_invoice ON mf_invoice.app_id = $a['id'] WHERE mf_invoice.paid = 2 AND template_id = $invoice AND WHERE $form.id IN ($entry_str)";
			}
			echo $q;
			echo '<br/>';
			echo '<pre>';
			print_r(Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q));
			echo '</pre>';*/
		}
		exit;
	}
	public function executeExcelsummary(sfWebRequest $request)
	{

		$agenda_columns = Doctrine_Core::getTable('AgendaColumns')->find(array($request->getParameter('id')));
		$this->forward404Unless($agenda_columns);
		//plotsize
		$q=Doctrine_Query::create()
			->from('ApFormElements e')
			->where('e.form_id = ? and e.element_status =? and e.element_plotsize =? and e.element_type LIKE ?',[$agenda_columns->getFormId(),1,1,'number']);
		$plotsize=$q->fetchOne();
		$ha='';
		if($plotsize){
			error_log('---------plot size ------'.$plotsize->getElementId().'----'.$plotsize->getElementTitle());
			$ha=$plotsize->getElementId();
		}
		//element_permittedgroundcoverage
		$q=Doctrine_Query::create()
			->from('ApFormElements e')
			->where('e.form_id = ? and e.element_status =? and e.element_permittedgroundcoverage =? and e.element_type LIKE ?',[$agenda_columns->getFormId(),1,1,'number']);
		$permittedgroundcoverage=$q->fetchOne();
		$groundcoverage='';
		if($permittedgroundcoverage){
			$groundcoverage=$permittedgroundcoverage->getElementId();
		}
		//element_projectcost
		$q=Doctrine_Query::create()
			->from('ApFormElements e')
			->where('e.form_id = ? and e.element_status =? and e.element_projectcost =? and e.element_type LIKE ?',[$agenda_columns->getFormId(),1,1,'number']);
		$projectcost=$q->fetchOne();
		$cost='';
		if($projectcost){
			$cost=$projectcost->getElementId();
		}
		error_log('------ha-----'.$ha);
		$q="SELECT";
		$query_arr=[];
		if(strlen($ha)){
			$query_arr[]=" SUM(ap_form_".$agenda_columns->getFormId().".element_".$ha.") as total_ha";
		}
		if(strlen($groundcoverage)){
			$query_arr[]=" SUM(ap_form_".$agenda_columns->getFormId().".element_".$groundcoverage.") as total_groundcoverage";
		}
		if(strlen($cost)){
			$query_arr[]=" SUM(ap_form_".$agenda_columns->getFormId().".element_".$cost.") as total_cost";
		}
		if(count($query_arr)){
			$q.=implode(",",$query_arr);
		}
		$q.=" COUNT(form_entry.id) as total_apps FROM ap_form_".$agenda_columns->getFormId()." LEFT JOIN form_entry ON form_entry.entry_id = ap_form_".$agenda_columns->getFormId().".id WHERE form_entry.form_id = ".$agenda_columns->getFormId()." AND form_entry.approved = ".$agenda_columns->getStage()." AND form_entry.deleted_status = 0 AND form_entry.parent_submission = 0";
		$summary_array=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$colms=[];
		$data=array();
		foreach($summary_array as $summary){
			$data_=array();
			foreach($summary as $k => $s){
				$data_[]= $k.' : '.$s;
			}
			$data[]=$data_;
		}
		//$this->ReportGeneratorAgenda($colms,$data);
		Outputsheet::ReportGenerator("Agenda Report -".date("Y-m-d"), $colms,$data);
	}
	public function executeFinancialsummary(sfWebRequest $request)
	{
		$year=$request->getParameter('year');
		$start=date('c',strtotime('01-01-'.$year));
		$end=date('c',strtotime('31-12-'.$year));
		$q="SELECT SUM(mf_invoice.total_amount), MONTH(mf_invoice.updated_at) as month FROM mf_invoice LEFT JOIN form_entry ON form_entry.id = mf_invoice.app_id WHERE form_entry.form_id = 1238 AND form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 AND mf_invoice.paid = 2 AND mf_invoice.updated_at >= '{$start}' AND mf_invoice.updated_at <= '{$end}' GROUP BY MONTH(mf_invoice.updated_at)";
		$change_of_use=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$q="SELECT SUM(mf_invoice.total_amount), MONTH(mf_invoice.updated_at) as month FROM mf_invoice LEFT JOIN form_entry ON form_entry.id = mf_invoice.app_id WHERE form_entry.form_id = 1852 AND form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 AND mf_invoice.paid = 2 AND mf_invoice.updated_at >= '{$start}' AND mf_invoice.updated_at <= '{$end}' GROUP BY MONTH(mf_invoice.updated_at)";
		$sub_division=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$q="SELECT SUM(mf_invoice.total_amount), MONTH(mf_invoice.updated_at) as month FROM mf_invoice LEFT JOIN form_entry ON form_entry.id = mf_invoice.app_id WHERE form_entry.form_id = 8004 AND form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 AND mf_invoice.paid = 2 AND mf_invoice.updated_at >= '{$start}' AND mf_invoice.updated_at <= '{$end}' GROUP BY MONTH(mf_invoice.updated_at)";
		$almagamation=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$q="SELECT SUM(mf_invoice.total_amount), MONTH(mf_invoice.updated_at) as month FROM mf_invoice LEFT JOIN form_entry ON form_entry.id = mf_invoice.app_id WHERE form_entry.form_id = 9198 AND form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 AND mf_invoice.paid = 2 AND mf_invoice.updated_at >= '{$start}' AND mf_invoice.updated_at <= '{$end}' GROUP BY MONTH(mf_invoice.updated_at)";
		$extension_of_use=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$colms=array('Category','Jan '.$year,'Feb '.$year,'Mar '.$year,'Apr '.$year,'May '.$year,'Jun '.$year,'Jul '.$year,'Aug '.$year,'Sep '.$year,'Oct '.$year,'Nov '.$year,'Dec '.$year,'Total');
		$data=array();
		$_data=array();
		$_data[]="Change of Use";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($change_of_use as $c){
			error_log('--CU-month--'.$c['month']);
			$_data[$c['month']]=$c['SUM(mf_invoice.total_amount)'];
			$total+=$c['SUM(mf_invoice.total_amount)'];
		}
		$_data[]=$total;
		error_log(print_r($_data,true));
		$data[]=$_data;
		$_data=array();
		$_data[]="Subdivision";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($sub_division as $s){
			error_log('--SD-month--'.$s['month']);
			$_data[$s['month']]=$s['SUM(mf_invoice.total_amount)'];
			$total+=$s['SUM(mf_invoice.total_amount)'];
		}
		$_data[]=$total;
		$data[]=$_data;
		error_log(print_r($_data,true));
		$_data=array();
		$_data[]="Amalgamation";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($almagamation as $s){
			error_log('--Am-month--'.$c['month']);
			$_data[$s['month']]=$s['SUM(mf_invoice.total_amount)'];
			$total+=$s['SUM(mf_invoice.total_amount)'];
		}
		$_data[]=$total;
		error_log(print_r($_data,true));
		$data[]=$_data;
		$_data=array();
		$_data[]="Extension of Use";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($extension_of_use as $s){
			error_log('--EU-month--'.$c['month']);
			$_data[$s['month']]=$s['SUM(mf_invoice.total_amount)'];
			$total+=$s['SUM(mf_invoice.total_amount)'];
		}
		$_data[]=$total;
		//error_log(print_r($_data,true));
		$data[]=$_data;
		//$this->ReportGeneratorAgenda($colms,$data);
		Outputsheet::ReportGenerator("Agenda Report -".date("Y-m-d"), $colms,$data);

	}
	public function executeFinancialsummarydc(sfWebRequest $request)
	{
		$year=$request->getParameter('year');
		$start=date('c',strtotime('01-01-'.$year));
		$end=date('c',strtotime('31-12-'.$year));
		$q="SELECT SUM(mf_invoice.total_amount), MONTH(mf_invoice.updated_at) as month FROM mf_invoice LEFT JOIN form_entry ON form_entry.id = mf_invoice.app_id WHERE form_entry.form_id = 14 AND form_entry.parent_submission = 0 AND form_entry.deleted_status = 0 AND mf_invoice.paid = 2 AND mf_invoice.updated_at >= '{$start}' AND mf_invoice.updated_at <= '{$end}' GROUP BY MONTH(mf_invoice.updated_at)";
		$dc=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$colms=array('Category','Jan '.$year,'Feb '.$year,'Mar '.$year,'Apr '.$year,'May '.$year,'Jun '.$year,'Jul '.$year,'Aug '.$year,'Sep '.$year,'Oct '.$year,'Nov '.$year,'Dec '.$year,'Total');
		$data=array();
		$_data=array();
		$_data[]="Development Application";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		error_log(print_r($dc,true));
		$total=0;
		foreach($dc as $c){
			$_data[$c['month']]=$c['SUM(mf_invoice.total_amount)'];
			$total+=$c['SUM(mf_invoice.total_amount)'];
		}
		$_data[]=$total;
		//error_log(print_r($_data,true));
		$data[]=$_data;
		//$this->ReportGeneratorAgenda($colms,$data);
		Outputsheet::ReportGenerator("Agenda Report -".date("Y-m-d"), $colms,$data);

	}
	public function executePlanspermission(sfWebRequest $request)
	{
		$year=$request->getParameter('year');
		$start=date('c',strtotime('01-01-'.$year));
		$end=date('c',strtotime('31-12-'.$year));
		$q="SELECT COUNT(id), MONTH(date_of_submission) as month FROM form_entry WHERE form_id = 1238 AND deleted_status = 0 AND date_of_submission >= '{$start}' AND date_of_submission <= '{$end}' AND parent_submission = 0 GROUP BY MONTH(date_of_submission)";
		$change_of_use=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$q="SELECT COUNT(id), MONTH(date_of_submission) as month FROM form_entry WHERE form_id = 1852 AND deleted_status = 0 AND date_of_submission >= '{$start}' AND date_of_submission <= '{$end}' AND parent_submission = 0 GROUP BY MONTH(date_of_submission)";
		$sub_division=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$q="SELECT COUNT(id), MONTH(date_of_submission) as month FROM form_entry WHERE form_id = 8004 AND deleted_status = 0 AND date_of_submission >= '{$start}' AND date_of_submission <= '{$end}' AND parent_submission = 0 GROUP BY MONTH(date_of_submission)";
		$almagamation=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$q="SELECT COUNT(id), MONTH(date_of_submission) as month FROM form_entry WHERE form_id = 9198 AND deleted_status = 0 AND date_of_submission >= '{$start}' AND date_of_submission <= '{$end}' AND parent_submission = 0 GROUP BY MONTH(date_of_submission)";
		$extension_of_use=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$colms=array('Category','Jan '.$year,'Feb '.$year,'Mar '.$year,'Apr '.$year,'May '.$year,'Jun '.$year,'Jul '.$year,'Aug '.$year,'Sep '.$year,'Oct '.$year,'Nov '.$year,'Dec '.$year,'Total');
		$data=array();
		$_data=array();
		$_data[]="Change of Use";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($change_of_use as $c){
			error_log('--CU-month--'.$c['month']);
			$_data[$c['month']]=$c['COUNT(id)'];
			$total+=$c['COUNT(id)'];
		}
		$_data[]=$total;
		error_log(print_r($_data,true));
		$data[]=$_data;
		$_data=array();
		$_data[]="Subdivision";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($sub_division as $s){
			error_log('--SD-month--'.$s['month']);
			$_data[$s['month']]=$s['COUNT(id)'];
			$total+=$s['COUNT(id)'];
		}
		$_data[]=$total;
		$data[]=$_data;
		error_log(print_r($_data,true));
		$_data=array();
		$_data[]="Amalgamation";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($almagamation as $s){
			error_log('--Am-month--'.$s['month']);
			$_data[$s['month']]=$s['COUNT(id)'];
			$total+=$s['COUNT(id)'];
		}
		$_data[]=$total;
		error_log(print_r($_data,true));
		$data[]=$_data;
		$_data=array();
		$_data[]="Extension of Use";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		$total=0;
		foreach($extension_of_use as $s){
			error_log('--EU-month--'.$s['month']);
			$_data[$s['month']]=$s['COUNT(id)'];
			$total+=$s['COUNT(id)'];
		}
		$_data[]=$total;
		//error_log(print_r($_data,true));
		$data[]=$_data;
		//$this->ReportGeneratorAgenda($colms,$data);
		Outputsheet::ReportGenerator("Agenda Report -".date("Y-m-d"), $colms,$data);

	}
	public function executePlansapplication(sfWebRequest $request)
	{
		$year=$request->getParameter('year');
		$start=date('c',strtotime('01-01-'.$year));
		$end=date('c',strtotime('31-12-'.$year));
		$q="SELECT COUNT(id), MONTH(date_of_submission) as month FROM form_entry WHERE form_id = 14 AND deleted_status = 0 AND date_of_submission >= '{$start}' AND date_of_submission <= '{$end}' AND parent_submission = 0 GROUP BY MONTH(date_of_submission)";
		$dc=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
		$colms=array('Category','Jan '.$year,'Feb '.$year,'Mar '.$year,'Apr '.$year,'May '.$year,'Jun '.$year,'Jul '.$year,'Aug '.$year,'Sep '.$year,'Oct '.$year,'Nov '.$year,'Dec '.$year,'Total');
		$data=array();
		$_data=array();
		$_data[]="Development Application";
		for($i=1;$i<13;$i++){
			$_data[]='';
		}
		error_log(print_r($dc,true));
		$total=0;
		foreach($dc as $c){
			$_data[$c['month']]=$c['COUNT(id)'];
			$total+=$c['COUNT(id)'];
		}
		$_data[]=$total;
		//error_log(print_r($_data,true));
		$data[]=$_data;
		//$this->ReportGeneratorAgenda($colms,$data);
		Outputsheet::ReportGenerator("Agenda Report -".date("Y-m-d"), $colms,$data);

	}
}
