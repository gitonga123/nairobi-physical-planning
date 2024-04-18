<?php

/**
 * feessactions.
 *
 * @package    backend
 * @subpackage invoiceapiaccounts
 * @author     Thomas Juma
 * @version    2.5: 2017-01-24
 */
class feesActions extends sfActions {
	/**
	 * Executes 'index' function
	 *
	 * Display a list of existing objects
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request) {
		$wizard_manager = new WizardManager();

		if ($wizard_manager->is_first_run()) {
			$this->redirect("/backend.php/dashboard");
		}
		//Audit
		Audit::audit("", "Accessed fee settings");

		//Get list of all objects
		$q = Doctrine_Query::create()
			->from('Fee a')
			->orderBy('a.description ASC');
		$this->fees = $q->execute();

		$this->setLayout("layout-settings");
	}

	/**
	 * Executes 'new' function
	 *
	 * Create a new object
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeNew(sfWebRequest $request) {
		$this->form = new FeeForm();

		$this->setLayout("layout-settings");
	}

	/**
	 * Executes 'create' function
	 *
	 * Save a new object
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeCreate(sfWebRequest $request) {
		//Audit
		Audit::audit("", "Added new fee");

		$this->forward404Unless($request->isMethod(sfRequest::POST));

		$this->form = new FeeForm();

		$this->processForm($request, $this->form);

		$this->setTemplate('new');
	}

	/**
	 * Executes 'edit' function
	 *
	 * Edit an existing object
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEdit(sfWebRequest $request) {
		$this->forward404Unless($account = Doctrine_Core::getTable('Fee')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

		$this->form = new FeeForm($account);
		$this->setSfFormChoiceWidgetOptionsFromApFormElements($account->getInvoiceid(), 'base_field'); //OTB Patch - Dynamic Condition Fields selection For Implementing Finance Bills
		$this->setLayout("layout-settings");
	}

	/**
	 * Executes 'update' action
	 *
	 * Update an existing object
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeUpdate(sfWebRequest $request) {
		//Audit
		Audit::audit("", "Updated existing fee");

		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($fee = Doctrine_Core::getTable('Fee')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

		$this->form = new FeeForm($fee);

		$this->processForm($request, $this->form);

		$this->setTemplate('edit');
	}

	/**
	 * Executes 'processForm' function
	 *
	 * Validate the form and save the object
	 *
	 * @param sfRequest $request A request object
	 */
	protected function processForm(sfWebRequest $request, sfForm $form) {
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		
		if ($form->isValid()) {
			$fee = $form->save();

			$this->redirect('/backend.php/fees/index');
		}
	}

	/**
	 * Executes 'delete' action
	 *
	 * Delete the object
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeDelete(sfWebRequest $request) {
		//Audit
		Audit::audit("", "Deleted existing fee");

		$this->forward404Unless($fee = Doctrine_Core::getTable('Fee')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

		$fee->delete();

		$this->redirect('/backend.php/fees/index');
	}

	public function executeGetfee(sfWebRequest $request) {
		$string = $request->getPostParameter("code");

		$code = "";

		$tok = strtok($string, ":");

		while ($tok !== false) {
			$code = $tok;
			break;
		}

		$q = Doctrine_Query::create()
			->from("Fee a")
			->where("a.fee_code = ?", $code)
			->orWhere("a.id = ?", $code);
		$fee = $q->fetchOne();
		if ($fee) {
			echo $fee->getAmount();
		}
		exit;
	}
	//OTB Start Patch - For Implementing Finance Bills
	public function executeFeerangeindex(sfWebRequest $request) {
		$this->filter = $request->getParameter("filter");
		$q_range = Doctrine_Query::create()
			->from('FeeRange f')
			->where('f.fee_id = ?', $this->filter);
		$this->fee_ranges = $q_range->execute();
	}
	public function executeFeerange(sfWebRequest $request) {
		$this->filter = $request->getParameter("filter");
		if ($request->getParameter('id')) {
			$range = Doctrine_Core::getTable('FeeRange')->find(array($request->getParameter('id')));
			$this->form = new FeeRangeForm($range);
		} else {
			$this->form = new FeeRangeForm();
		}
		$fee = Doctrine_Core::getTable('fee')->find(array($this->filter));
		//$this->setSfFormChoiceWidgetOptionsFromApFormElements($fee->getInvoicetemplate()->getApplicationform(), 'condition_field');//OTB Patch - Dynamic Condition Fields selection For Implementing Finance Bills
	}
	public function executeNewfeerange(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST));
		$this->form = new FeeRangeForm();
		$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
		if ($this->form->isValid()) {
			$this->form->save();
		}
		$this->getUser()->setFlash('save_notice', sprintf('Fee range has been saved'));
		$this->redirect('/backend.php/fees/feerangeindex');
		$this->setTemplate('feerange');
	}
	public function executeUpdatefeerange(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($range = Doctrine_Core::getTable('FeeRange')->find($request->getParameter('id')));
		$form = new FeeRangeForm($range);
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		if ($form->isValid()) {
			$form->save();
		}
		$this->getUser()->setFlash('save_notice', sprintf('Fee range has been updated'));
		$this->redirect('/backend.php/fees/feerangeindex');
		$this->setTemplate('feerange');
	}
	public function executeDeleterange(sfWebRequest $request) {
		$this->forward404Unless($fee_range = Doctrine_Core::getTable('FeeRange')->find(array($request->getParameter('id'))), sprintf('Object fee_range does not exist (%s).', $request->getParameter('id')));

		$audit = new Audit();
		$audit->saveAudit("", "deleted fee_range of id " . $fee_range->getId());

		$fee_id = $fee_range->getFeeId();

		$fee_range->delete();

		$this->redirect('/backend.php/fees/feerangeindex/filter/' . $fee_id);
	}

	public function executeChangebasefield(sfWebRequest $request) {
		$invoicetemplate = Doctrine_Core::getTable('invoicetemplates')->find(array($request->getParameter('invoicetemplate_id')));
		if ($invoicetemplate) {
			$elements = Doctrine_Core::getTable('ApFormElements')->getAllFields($invoicetemplate->getApplicationform());
			error_log(print_r($elements, true));
			echo json_encode($elements);
			/*$new_options='';
				  foreach($elements as $key => $value){
					$new_options .= "<option value='".$key."'>".$value."</option>";
				  }
			*/
		}
		exit();
	}
	protected function setSfFormChoiceWidgetOptionsFromApFormElements($apform_id, $widget_name) {
		$widget = $this->form->getWidget($widget_name);
		$widget->setOptions(array('choices' => Doctrine_Core::getTable('ApFormElements')->getAllFields($apform_id)));
		//error_log($apform_id." ## configure this form values ### ".print_R($widget->getChoices(), true));
	}

	//Conditions
	public function executeRangeconditions(sfWebRequest $request) {
		$this->filter = $request->getParameter("filter");
		$q_range = Doctrine_Query::create()
			->from('FeeRangeCondition f')
			->where('f.fee_range_id = ?', $this->filter);
		$this->fee_range_conditions = $q_range->execute();
	}
	public function executeRangeconditionform(sfWebRequest $request) {
		$this->filter = $request->getParameter("filter");
		error_log("the filter ya range condition form #### " . $this->filter);
		if ($request->getParameter('id')) {
			$rangecondition = Doctrine_Core::getTable('FeeRangeCondition')->find(array($request->getParameter('id')));
			$this->form = new FeeRangeConditionForm($rangecondition);
		} else {
			$this->form = new FeeRangeConditionForm();
		}
		$fee_range = Doctrine_Core::getTable('feerange')->find(array($this->filter));
		$fee = Doctrine_Core::getTable('fee')->find(array($fee_range->getFeeId()));
		$this->setSfFormChoiceWidgetOptionsFromApFormElements($fee->getInvoicetemplates()->getApplicationform(), 'condition_field'); //OTB Patch - Dynamic Condition Fields selection For Implementing Finance Bills
	}
	public function executeNewfeerangecondition(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST));
		$this->form = new FeeRangeConditionForm();
		$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
		if ($this->form->isValid()) {
			$this->form->save();
		}
		$this->getUser()->setFlash('save_notice', sprintf('Fee range has been saved'));
		$this->redirect('/backend.php/fees/rangeconditions');
		$this->setTemplate('feerangecondition');
	}
	public function executeUpdatefeerangecondition(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($rangecondition = Doctrine_Core::getTable('FeeRangeCondition')->find($request->getParameter('id')));
		$form = new FeeRangeConditionForm($rangecondition);
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		error_log("is valid ###### " . $form->isValid());
		if ($form->isValid()) {
			$form->save();
		}
		$this->getUser()->setFlash('save_notice', sprintf('Fee range condition has been updated'));
		$this->redirect('/backend.php/fees/rangeconditions');
		$this->setTemplate('rangeconditions');
	}
	public function executeDeleterangecondition(sfWebRequest $request) {
		$this->forward404Unless($fee_range_condition = Doctrine_Core::getTable('FeeRangeCondition')->find(array($request->getParameter('id'))), sprintf('Object fee_range_condition does not exist (%s).', $request->getParameter('id')));

		$audit = new Audit();
		$audit->saveAudit("", "deleted fee_range_condition of id " . $fee_range_condition->getId());

		$fee_range_id = $fee_range_condition->getFeeRangeId();

		$fee_range_condition->delete();

		$this->redirect('/backend.php/fees/rangeconditions/filter/' . $fee_range_id);
	}
	//OTB End Patch - For Implementing Finance Bills
	public function executeGetfeecode(sfWebRequest $request) {
		$service_id = $request->getParameter('service_id');
		$q = Doctrine_Query::create()
			->from('FeeCode c')
			->where('c.service_id =?', $service_id);
		$fee_code = $q->fetchOne();
		$fee_code_arr = array("fixed" => $fee_code->getFixed(), "amount" => $fee_code->getAmount());
		$this->getResponse()->setHttpHeader('content-type', 'application/json');
		return $this->renderText(json_encode($fee_code_arr));
	}
	//OTB dublicate fee
	public function executeFeedublicate(sfWebRequest $request) {
		$fee_id = $request->getParameter('id');
		//get fee
		$q = Doctrine_Query::create()
			->from('Fee f')
			->where('f.id =?', $fee_id);
		$fee = $q->fetchOne();
		//create fee
		$new_fee = new Fee();
		$new_fee->fee_code = $fee->getFeeCode();
		$new_fee->description = $fee->getDescription() . '_copy';
		$new_fee->amount = $fee->getAmount();
		$new_fee->invoiceid = $fee->getInvoiceid();
		$new_fee->fee_category = $fee->getFeeCategory();
		$new_fee->base_field = $fee->getBaseField();
		$new_fee->fee_type = $fee->getFeeType();
		$new_fee->percentage = $fee->getPercentage();
		$new_fee->minimum_fee = $fee->getMinimumFee();
		$new_fee->save();
		//get fee range
		$q = Doctrine_Query::create()
			->from('FeeRange r')
			->where('r.fee_id =?', $fee->getId());
		$fee_ranges = $q->execute();
		//loop
		foreach ($fee_ranges as $fee_range) {
			//create fee range
			$new_fee_range = new FeeRange();
			$new_fee_range->fee_id = $new_fee->getId();
			$new_fee_range->name = $fee_range->getName();
			$new_fee_range->range_1 = $fee_range->getRange_1();
			$new_fee_range->range_2 = $fee_range->getRange_2();
			$new_fee_range->result_value = $fee_range->getResultValue();
			$new_fee_range->condition_field = $fee_range->getConditionField();
			$new_fee_range->condition_operator = $fee_range->getConditionOperator();
			$new_fee_range->condition_value = $fee_range->getConditionValue();
			$new_fee_range->created_by = $fee_range->getCreatedBy();
			$new_fee_range->value_type = $fee_range->getValueType();
			$new_fee_range->condition_set_operator = $fee_range->getConditionSetOperator();
			$new_fee_range->save();
			//create fee range condition
			//get fee range conditions
			$q = Doctrine_Query::create()
				->from('FeeRangeCondition c')
				->where('c.fee_range_id =?', $fee_range->getId());
			$fee_range_conditions = $q->execute();
			//loop
			foreach ($fee_range_conditions as $fee_range_condition) {
				//create fee range condition
				$new_fee_range_condition = new FeeRangeCondition();
				$new_fee_range_condition->fee_range_id = $new_fee_range->getId();
				$new_fee_range_condition->condition_field = $fee_range_condition->getConditionField();
				$new_fee_range_condition->condition_operator = $fee_range_condition->getConditionOperator();
				$new_fee_range_condition->condition_value = $fee_range_condition->getConditionValue();
				$new_fee_range_condition->created_by = $fee_range_condition->getCreatedBy();
				$new_fee_range_condition->save();
			}
		}
		$this->getUser()->setFlash('notice', sprintf('Fee: %s has been successfully dublicated!', $fee->getDescription()));
		$this->redirect('/backend.php/fees/index');
	}
}
