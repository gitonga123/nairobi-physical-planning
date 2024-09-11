<?php

/**
 * Applications actions.
 *
 * View application details
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class applicationsActions extends sfActions
{
	/**Start  patch
	 * Executes 'Move' action -  patch
	 *
	 * Moves an application directly to any stage
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeMoveapp(sfWebRequest $request)
	{
		$stage = $request->getParameter("stage");
		$task_id = $request->getParameter("taskid");
		if ($stage) {
			$q = Doctrine_Query::create()
				->from('FormEntry a')
				->where('a.id = ?', $request->getParameter('id'));
			$application = $q->fetchOne();
			// ADD Check stage
			$sub_menu = Doctrine_Core::getTable('SubMenus')->find($stage);
			$this->forward404Unless($sub_menu, sprintf('Stage id %s does not exist!', $stage));
			$this->forward404Unless($application, sprintf('Application id %s does not exist!', $application));
			if ($sub_menu) {
				switch ($sub_menu->getStageType()) {
					//correction
					case 5:
						$this->redirect('/backend.php/forms/decline?moveto=' . $stage . '&form_entry_id=' . $application->getId());
						break;
					case 6:
						$this->redirect('/backend.php/forms/reject?moveto=' . $stage . '&form_entry_id=' . $application->getId());
						break;
					case 11:
						//Check conditions
						$q = Doctrine_Query::create()
							->from('ApprovalCondition a')
							->where('a.entry_id = ?', $application->getId());
						$count_conditions = $q->count();
						error_log('----Conditions--' . $count_conditions);
						if ($count_conditions <= 0) {
							$this->getUser()->setFlash('notice', 'Application has no Conditions of approval set! Agenda stage requires Conditions to be set.');
							$this->redirect("/backend.php/applications/view/id/" . $application->getId() . "/current_tab/reviews");
						}
						$application->setApproved($stage);
						break;
					case 3:
						//Check if the most recent invoice is set
						$q = Doctrine_Query::create()
							->from('MfInvoice i')
							->where('i.app_id = ? and (i.paid = ? OR i.paid = ?)', array($application->getId(), 1, 15));
						//Check the no of pending invoices
						if ($q->count() == 0) {
							$this->getUser()->setFlash('notice', 'Application has no pending Invoice(s)! Invoice is required.');
							$this->redirect("/backend.php/applications/view/id/" . $application->getId() . "/current_tab/billing");
						}
						$application->setApproved($stage);
						continue;
					default:
						$application->setDeclined(0);
						$application->setApproved($stage);
				}
			}
			$application->save();
			// Patch - Instead of checking is the permit is generated in the view application always check when moving the app - this is to avoid getting the wrong created by user
			$permit_manager = new PermitManager();
			if ($permit_manager->needs_permit_for_current_stage($application->getId())) {

				$permit_manager->create_permit($application->getId());
			}

			/*if($task_id){// Patch - Null ref check
												$task_manager = new TasksManager();
												$task_manager->cancelTask($task_id) ;
												//Need to execute cancel tasks if an application moves
												Audit::audit("", "Moved application #".$request->getParameter('id')." To stage ".$request->getParameter("stage"));
												}*/
		}

		$this->redirect("/backend.php/applications/view/id/" . $application->getId());
	}
	/*End  patch*/
	/**
	 * Executes 'View' action
	 *
	 * Display application details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeView(sfWebRequest $request)
	{
		// Patch - Multiagency fucntionality, application viewing check
		$agency_manager = new AgencyManager();
		$this->forward404Unless($this->application = Doctrine_Core::getTable('FormEntry')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
		if ($this->application->getApproved() != 0) {
			$this->forward404Unless($agency_manager->checkAgencyApplicationAccess($this->getUser()->getAttribute('userid'), $request->getParameter('id')), 'No agency access for user ' . $this->getUser()->getAttribute('userid'));
		}

		if ($this->application->getDeletedStatus() == 1) {
			$this->forward404Unless($this->application->getDeletedStatus() != 1, 'Application is Archived/Deleted' . $this->getUser()->getAttribute('userid'));
		}

		$q = Doctrine_Query::create()
			->from("EntryDecline a")
			->where("a.entry_id = ?", $this->application->getId());
		$this->declined = $q->count();
		$application_manager = new ApplicationManager();
		$permit_manager = new PermitManager();
		$invoice_manager = new InvoiceManager();

		$invoice_manager->update_invoices($request->getParameter('id'));

		//Check JSON. Generate if empty
		$application_manager->check_json($request->getParameter('id'));

		// check for memo count;
		$q = Doctrine_Query::create()
			->from('Communication a')
			->where('a.application_id = ?', $this->application->getId())
			->andWhere('a.isread = ?', 0);
		$this->internal_memo = $q->count();

		//If assessment inprogress and there are no pending tasks then mark as no assessment in progress
		if ($this->application->getAssessmentInprogress()) {
			if (!TasksManager::any_pending_tasks($this->application->getId())) {
				$this->application->setAssessmentInprogress(0);
				$this->application->save();
			}
		}

		//If reference number is posted then attempt to recover application
		if ($request->getParameter("reference_number")) {
			$refs = explode("/", $request->getParameter("reference_number"));

			$this->application->setEntryId($refs[1]);
			$this->application->save();
		}

		//Check if there any permits required for this stage
		if ($permit_manager->needs_permit_for_current_stage($this->application->getId())) {
			$permit_manager->create_permit($this->application->getId());
		}

		//Audit 
		Audit::audit($this->application->getId(), "Viewed application");
		$this->current_tab = $request->getParameter("current_tab", "application");
		$this->apppage = $request->getParameter("apppage");
		//get revison
		$q = Doctrine_Query::create()
			->from('FormEntry a')
			->where('a.parent_submission = ?', $this->application->getId())
			->orderBy('a.id ASC');
		$this->revisions = $q->execute();
		// Added for showing permit needs signing
		$q_permit = "SELECT a.id as permit_it, a.type_id, a.application_id, a.document_key, p.allows_signing "
			. " FROM saved_permit a LEFT JOIN permits p ON a.type_id = p.id"
			. " WHERE a.application_id = " . $this->application->getId()
			. " AND  a.permit_status <> 3 "
			. " AND  a.expiry_trigger <> 1 "
			. " AND  p.parttype <> 2 "
			. " AND  a.document_key IS NULL ";

		$this->q_permit_result = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q_permit);
		$this->can_sign_permit = $this->getUser()->mfHasCredential('can-sign-permit-' . $this->q_permit_result['type_id']);

		$unsigned = 0;
		$this->has_document_to_sign = count(array_filter($this->q_permit_result, function ($k) use (&$permit_manager, &$unsigned) {
			$is_signed = $permit_manager->is_signed($k['permit_it']);
			$is_signable = $k['allows_signing'] == 1;
			if (!$is_signed and $is_signable)
				$unsigned++;
			return !$is_signed and $is_signable;
		})) != 0;

		if (!$this->can_sign_permit and $this->has_document_to_sign) {
			$this->error = $unsigned . ' document(s) require signing';
		}

		$this->can_activate_permit = $this->canActivatePermit($this->application->getId());

		Functions::check_need_for_creating_signingTask($this->application);
	}

	/**
	 * Executes 'Edit' action
	 *
	 * Edit application details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEdit(sfWebRequest $request)
	{
		$this->forward404Unless($this->application = Doctrine_Core::getTable('FormEntry')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

		//Audit 
		Audit::audit($this->application->getId(), "Edited application");

		$this->current_tab = $request->getParameter("current_tab", "application");
	}

	/**
	 * Executes 'Viewinvoice' action
	 *
	 * Display invoice details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeViewinvoice(sfWebRequest $request)
	{
		$this->forward404Unless($this->invoice = Doctrine_Core::getTable('MfInvoice')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
	}

	/**
	 * Executes 'Printinvoice' action
	 *
	 * Print invoice details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executePrintinvoice(sfWebRequest $request)
	{
		$invoice_manager = new InvoiceManager();
		$invoice_manager->save_to_pdf($request->getParameter("id"));

		exit;
	}

	/**
	 * Executes 'Search' action
	 *
	 * Display application search results
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeSearch(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from('ApForms a')
			->where('a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ? AND a.form_id <> ?', array('6', '7', '15', '16', '17'))
			->orderBy('a.form_id ASC');
		$this->forms = $q->execute();

		$this->getUser()->setAttribute('task_id', false);
	}


	/**
	 * Executes 'Viewpermit' action
	 *
	 * Displays the generated permit that is attached to an application
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeViewpermit(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from('SavedPermit a')
			->where('a.id = ?', $request->getParameter('id'));
		$savedpermit = $q->fetchOne();

		if ($savedpermit) {
			$permit_manager = new PermitManager();
			$permit_manager->save_to_pdf($savedpermit->getId());
		} else {
			echo "Invalid Permit Link";
		}

		exit;
	}

	/**
	 * Executes 'Formsearch' action
	 *
	 * Fetch search filters for selected application form
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeFormsearch(sfWebRequest $request)
	{
		$this->setLayout(false);
	}

	/**
	 * Executes 'Confirmpayment' action
	 *
	 * Confirms an invoice as paid
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeConfirmpayment(sfWebRequest $request)
	{
		$invoice_id = $request->getParameter("id");

		if ($this->getUser()->mfHasCredential('approvepaymentoverride')) {
			$q = Doctrine_Query::create()
				->from("MfInvoice a")
				->where("a.id = ?", $invoice_id);
			$invoice = $q->fetchOne();

			$invoice->setPaid(2);
			$invoice->setUpdatedAt(date("Y-m-d"));
			$invoice->save();

			//Audit 
			Audit::audit($invoice->getAppId(), "Confirmed invoice #" . $invoice->getId());

			//Save audit of invoice confirmation   
			$audit = new Audit();
			$audit->saveAudit("", "Confirmed payment for invoice #" . $invoice_id);

			$this->redirect($this->getUser()->getAttribute('resume_url'));
		} else {
			$this->redirect($this->getUser()->getAttribute('resume_url'));
		}
	}

	/**
	 * Executes 'Cancelpayment' action
	 *
	 * Cancel an invoice as failed
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeCancelpayment(sfWebRequest $request)
	{
		$invoice_id = $request->getParameter("id");

		if ($this->getUser()->mfHasCredential('approvepaymentoverride')) {
			$q = Doctrine_Query::create()
				->from("MfInvoice a")
				->where("a.id = ?", $invoice_id);
			$invoice = $q->fetchOne();

			$invoice->setPaid(3);
			$invoice->setUpdatedAt(date("Y-m-d"));
			$invoice->save();

			//Audit 
			Audit::audit($invoice->getAppId(), "Cancelled invoice #" . $invoice->getId());

			//Save audit of invoice confirmation   
			$audit = new Audit();
			$audit->saveAudit("", "Cancelled payment for invoice #" . $invoice_id);

			// Add
			//Cancel on merchant
			//check if merchant enabled
			if ($invoice->getFormEntry()->getForm() && $invoice->getFormEntry()->getForm()->getPaymentEnableMerchant()) {
				//cancel
				$api = new ApiCalls();
				$api->cancelInvoice($invoice);
			}

			$this->redirect($this->getUser()->getAttribute('resume_url'));
		} else {
			$this->redirect($this->getUser()->getAttribute('resume_url'));
		}
	}

	/**
	 * Executes 'Uncancelpayment' action
	 *
	 * Cancel an invoice as failed
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeUncancelpayment(sfWebRequest $request)
	{
		$invoice_id = $request->getParameter("id");

		if ($this->getUser()->mfHasCredential('approvepaymentoverride')) {
			$q = Doctrine_Query::create()
				->from("MfInvoice a")
				->where("a.id = ?", $invoice_id);
			$invoice = $q->fetchOne();

			$invoice->setPaid(1);
			$invoice->setUpdatedAt(date("Y-m-d"));
			$invoice->save();
			//Post invoice
			$api = new ApiCalls();
			$api->postInvoice($invoice->getFormEntry(), $invoice);
			//Audit 
			Audit::audit($invoice->getAppId(), "Uncancelled invoice #" . $invoice->getId());

			//Save audit of invoice confirmation   
			$audit = new Audit();
			$audit->saveAudit("", "Uncancelled payment for invoice #" . $invoice_id);

			$this->redirect($this->getUser()->getAttribute('resume_url'));
		} else {
			$this->redirect($this->getUser()->getAttribute('resume_url'));
		}
	}

	/**
	 * Executes 'Transfer' action
	 *
	 * Change Ownership of an Application
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeTransfer(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from('FormEntry a')
			->where('a.id = ?', $request->getParameter('id'));
		$this->application = $q->fetchOne();

		if ($request->getPostParameter("email")) {
			$email = $request->getPostParameter("email");

			$q = Doctrine_Query::create()
				->from("SfGuardUserProfile a")
				->where("a.email = ?", $email);

			$new_user = $q->fetchOne();

			if ($new_user) {
				$this->application->setUserId($new_user->getUserId());
				$this->application->save();

				if ($this->getUser()->getAttribute("task_id")) {
					$this->redirect("/backend.php/tasks/view/id/" . $this->getUser()->getAttribute("task_id"));
				} else {
					$this->redirect("/backend.php/applications/view/id/" . $this->application->getId());
				}
			} else {
				$this->getUser()->setFlash('error', 'The user you specified does not exist');
				$this->redirect("/backend.php/applications/transfer/id/" . $this->application->getId());
			}
		}
	}

	/**
	 * Executes 'Paynow' action
	 *
	 * Displays a dynamically generated application form
	 *
	 * @param sfRequest $request A request object
	 */
	public function executePaynow(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from("MfInvoice a")
			->where("a.id = ?", $request->getParameter("id"));
		$this->invoice = $q->fetchOne();

		$this->application = $this->invoice->getFormEntry();
	}
	/**
	 * ADD cyclic on demand
	 */
	public function executeCyclicondemand(sfWebRequest $request)
	{
		$this->forward404Unless($application = Doctrine_Core::getTable('FormEntry')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

		//Check saved permit for expired permit
		$q = Doctrine_Query::create()
			->from("SavedPermit a")
			->where("a.date_of_expiry <= ? and a.application_id = ? and a.permit_status <> 3 and a.Template.expiry_trigger <> ?", array(date("Y-m-d H:m:s"), $application->getId(), 0));
		$expired_permits = $q->execute();
		if ($expired_permits) {
			foreach ($expired_permits as $permit) {
				error_log('--------Permit-----' . $permit->getId());
				$permit->setExpiryTrigger(1);
				$permit->save();
			}
			//Run cyclic on that application
			//Check if business id is set
			if ($application->getBusinessId()) {
				error_log('-----Business id-----' . $application->getBusinessId());
				//Run cyclic 
				$invoice_manager = new InvoiceManager();
				$invoice_manager->generate_cyclic_invoices($application->getId(), $application->getServiceId(), false);
				$this->getUser()->setFlash('notice', 'Cyclic billing done for application : ' . $application->getApplicationId());
				//move
				$q = Doctrine_Query::create()
					->from('SubMenus a')
					->where('a.id = ?', $application->getApproved());
				$stage = $q->fetchOne();

				if ($stage) {
					if ($stage->getStageExpiredMovement()) {
						//Move application to another stage
						$application->setApproved($stage->getStageExpiredMovement());
						$application->save();
					}
				}
			} else {
				//Check menu for existence of service renewal for form
				$q = Doctrine_Query::create()
					->from('Menus m')
					->where('m.service_form = ? and m.service_type = ? and service_fee_field <> ?', array($application->getFormId(), 2, 0));
				$service = $q->fetchOne();
				if ($service) {
					$invoice_manager = new InvoiceManager();
					$invoice_manager->generate_cyclic_invoices($application->getId(), $service->getId(), false);
					$this->getUser()->setFlash('notice', 'Cyclic billing done for application : ' . $application->getApplicationId());
					//move
					$q = Doctrine_Query::create()
						->from('SubMenus a')
						->where('a.id = ?', $application->getApproved());
					$stage = $q->fetchOne();

					if ($stage) {
						if ($stage->getStageExpiredMovement()) {
							//Move application to another stage
							$application->setApproved($stage->getStageExpiredMovement());
							$application->save();
						}
					}
				} else {
					$this->getUser()->setFlash('notice', 'Cyclic billing could not be executed for application : ' . $application->getApplicationId() . '!');
				}
			}
		} else {
			$this->getUser()->setFlash('notice', 'Application : ' . $application->getApplicationId() . ' does not have an expired permit!');
		}
		$this->redirect('/backend.php/applications/view/id/' . $application->getId());
	}
	public function executeMovetoworkflow(sfWebRequest $request)
	{
		$form_entry_id = $request->getParameter('id');
		$app = Doctrine_Core::getTable('FormEntry')->find($form_entry_id);
		$application_manager = new ApplicationManager();
		$stage = $application_manager->get_submission_stage($app->getFormId(), $app->getEntryId());
		//Update form entry
		$app->setApproved($stage);
		$app->save();

		$this->getUser()->setFlash('notice', 'Application has been moved!');

		//Save audit of invoice confirmation   
		$audit = new Audit();
		$audit::audit($app->getId(), "Workflow logic move");
		$this->redirect('/backend.php/applications/view/id/' . $app->getId());
	}
	/**
	 * ADD cyclic on demand +1 year
	 */
	public function executeCyclicondemandfuture(sfWebRequest $request)
	{
		$this->forward404Unless($application = Doctrine_Core::getTable('FormEntry')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));



		//Check saved permit for expired permit
		$q = Doctrine_Query::create()
			->from("SavedPermit a")
			->where("a.date_of_expiry <= ? and a.application_id = ? and a.permit_status <> 3 and a.Template.expiry_trigger <> ?", array((date("Y") + 1) . "-1-1", $application->getId(), 0));
		$expired_permits = $q->execute();
		if ($expired_permits) {
			foreach ($expired_permits as $permit) {
				error_log('--------Permit-----' . $permit->getId());
				$permit->setExpiryTrigger(1);
				$permit->save();
			}
			//Run cyclic on that application
			//Check if business id is set
			if ($application->getBusinessId()) {
				error_log('-----Business id-----' . $application->getBusinessId());
				//Run cyclic 
				$invoice_manager = new InvoiceManager();
				$invoice_manager->generate_cyclic_invoices($application->getId(), $application->getServiceId(), false, true);
				$this->getUser()->setFlash('notice', 'Cyclic billing +1 year done for application : ' . $application->getApplicationId());
				//move
				$q = Doctrine_Query::create()
					->from('SubMenus a')
					->where('a.id = ?', $application->getApproved());
				$stage = $q->fetchOne();

				if ($stage) {
					if ($stage->getStageExpiredMovement()) {
						//Move application to another stage
						$application->setApproved($stage->getStageExpiredMovement());
						$application->save();
					}
				}
			} else {
				//Check menu for existence of service renewal for form
				$q = Doctrine_Query::create()
					->from('Menus m')
					->where('m.service_form = ? and m.service_type = ? and service_fee_field <> ?', array($application->getFormId(), 2, 0));
				$service = $q->fetchOne();
				if ($service) {
					$invoice_manager = new InvoiceManager();
					$invoice_manager->generate_cyclic_invoices($application->getId(), $service->getId(), false, true);
					$this->getUser()->setFlash('notice', 'Cyclic billing +1 year done for application : ' . $application->getApplicationId());
					//move
					$q = Doctrine_Query::create()
						->from('SubMenus a')
						->where('a.id = ?', $application->getApproved());
					$stage = $q->fetchOne();

					if ($stage) {
						if ($stage->getStageExpiredMovement()) {
							//Move application to another stage
							$application->setApproved($stage->getStageExpiredMovement());
							$application->save();
						}
					}
				} else {
					$this->getUser()->setFlash('notice', 'Cyclic billing could not be executed for application : ' . $application->getApplicationId() . '!');
				}
			}
		} else {
			$this->getUser()->setFlash('notice', 'Application : ' . $application->getApplicationId() . ' does not have an expired permit!');
		}
		$this->redirect('/backend.php/applications/view/id/' . $application->getId());
	}
	// to be able to submit new form
	public function executeNew(sfWebRequest $request)
	{
		$this->form_id = $request->getParameter('form_id');
		$this->setLayout("layout-settings");
	}
	public function executeShowmemberships(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from("ApForms a")
			->where('a.form_type = ?', 4)
			->orderBy("a.form_name ASC");
		$this->apforms = $q->execute();
		$this->setLayout("layout-settings");
	}
	public function executeShowentries(sfWebRequest $request)
	{
		$form_id = $request->getParameter('form_id');
		//Form name
		$q = Doctrine_Query::create()
			->select('a.form_id,a.form_name')
			->from("ApForms a")
			->where('a.form_id = ?', $form_id)
			->limit(1);
		$this->apform = $q->fetchArray();
		//Elements
		$q = Doctrine_Query::create()
			->select('a.element_title,a.element_id')
			->from("ApFormElements a")
			->where('a.form_id = ? AND a.element_status = ?', array($form_id, 1));
		$this->form_elements = $q->fetchArray();
		//Entries
		$this->entries = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT * FROM ap_form_" . $form_id . " WHERE status = 1");
		$this->setLayout("layout-settings");
	}
	public function executeEditentries(sfWebRequest $request)
	{

		$this->form_id = $request->getParameter('form_id');
		$this->entry_id = $request->getParameter('id');
		$this->form_entry_id = $request->getParameter('form_entry_id');

		//Audit 
		Audit::audit('', "Edited membership form: " . $this->form_id . " entry id: " . $this->entry_id);
	}
	// ADD - Add condition
	public function executeConditionlinkapp(sfWebRequest $request)
	{
		//error_log('-----Method------'.$request->getMethod());
		$condition_form = new ConditionsOfApproval();
		$condition_form->entry_id = $request->getParameter('id');
		//Save depart
		$q = Doctrine_Query::create()
			->from("CfUser a")
			->where("a.nid = ?", $this->getUser()->getAttribute('userid'));
		$reviewer = $q->fetchOne();
		//Depart id
		$depart = Doctrine_Core::getTable('Department')->findByDepartmentName($reviewer->getStrdepartment());
		$condition_form->department_id = $depart[0]['id'];
		$this->form = new ConditionsOfApprovalForm($condition_form);

		if ($request->getMethod() == 'POST') {
			//Check if form is valid
			$this->form->bind($request->getParameter('conditions_of_approval'));
			if ($this->form->isValid()) {
				$condition = $this->form->save();
				//Create entry on ApprovalCondition
				$approval_cond = new ApprovalCondition();
				$approval_cond->entry_id = $condition->getEntryId();
				$approval_cond->condition_id = $condition->getId();
				$approval_cond->save();
				//Redirect
				$this->getUser()->setFlash('notice', 'Condition added!');
				$this->redirect("/backend.php/applications/view/id/" . $condition->getEntryId() . "/current_tab/reviews");
			}
		}
	}
	// END
	/**
	 * Executes 'Togglecondition' action
	 */
	public function executeTogglecondition(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from('ApprovalCondition a')
			->where('a.entry_id = ?', $request->getParameter("appid"))
			->andWhere('a.condition_id = ?', $request->getParameter("id"));
		$condition = $q->fetchOne();
		if (empty($condition)) {
			$condition = new ApprovalCondition();
			$condition->setEntryId($request->getParameter("appid"));
			$condition->setConditionId($request->getParameter("id"));
			$condition->save();
		} else {
			$condition->delete();
		}
		exit;
	}
	// ADD IFC callback
	public function executeIfccallback(sfWebRequest $request)
	{
		$this->forward404Unless($request->isMethod('POST'), 'Can\'t update none post for ifc');
		error_log('-------REQUEST CALL BACK-----');
		//error_log(print_r($request,true));
		error_log('-------Application-------' . $request->getParameter('application'));
		error_log('----------Mess-------' . $request->getParameter('message'));
		error_log('---------Status---------' . $request->getParameter('status')); //if == 'ok'
		//save to table ifc_file
		if ($request->getParameter('application')) {
			$ifc_file = new IfcFile();
			$ifc_file->setApplication($request->getParameter('application'));
			$ifc_file->setMessage($request->getParameter('message'));
			$ifc_file->setStatus($request->getParameter('status'));
			$ifc_file->save();
		}
		exit;
	}
	// ADD
	public function executeIfc(sfWebRequest $request)
	{
		//if($this->getUser()->isAuthenticated()){
		$time = time();
		$url = sfConfig::get('app_ifc_server') . "/login/browse?" . http_build_query(array('username' => sfConfig::get('app_ifc_user'), 'stamp' => $time, 'token' => hash('sha256', sfConfig::get('app_ifc_pwd') . $time)));
		error_log('------Auth----' . $url);
		$zip = new \ZipArchive();
		$this->redirect($url);
		// phpinfo();
		//}
		exit;
		//sfView::none;
	}
	public function executeViewifc(sfWebRequest $request)
	{
		$this->application = $request->getParameter('application');
	}
	/**
	 * Executes 'Viewjson' action
	 *
	 * Displays a single application and all of its review history
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeViewjson(sfWebRequest $request)
	{
		$this->executeView($request);
		$this->setLayout(false);
	}
	public function executeSend(sfWebRequest $request)
	{
		$send = new Send('localhost', 5672, 'guest', 'guest');
		$send->createQueue('hello');
		echo $send->createMsg('Hello World!');
		exit();
	}

	public function executeReceive(sfWebRequest $request)
	{
		$receiver = new Receiver('localhost', 5672, 'guest', 'guest');
		echo $receiver->queueDeclare('hello');
		echo $receiver->consume();
		exit();
	}
	public function executeMailtest(sfWebRequest $request)
	{
		$notification = new mailnotifications();
		$notification->sendemail('', 'george@africa.com', 'Test', 'Test of mail sending');
		exit();
	}

	public function executeSMStest(sfWebRequest $request)
	{
		$notification = new mailnotifications();
		$notification->sendsms('0710594298', 'Hi 0710594298, Testing 1,2,3');
		exit();
	}
	########### Sasalog :: end addition by James

	# get a list of permits that are not activated
	# for each of them check if this user is within the groups allowed to generate the permit
	# if they are, then, confirm that they actually moved it to the generation stage
	# and if they did, then proceed and give a list that they can activate
	function canActivatePermit($application_id): bool
	{
		$conn = Doctrine_Manager::getInstance()->getCurrentConnection();
		$me = $this->getUser()->getAttribute('userid');

		$q = "SELECT id, type_id FROM saved_permit WHERE application_id = $application_id "
			. " AND is_activated = 0 AND signed_by = $me";
		$found_not_activated_commits = $conn->fetchAssoc($q);
		return count($found_not_activated_commits) > 0;
	}


	public function executeActivatepermits(sfWebRequest $request)
	{
		$application_id = $request->getParameter('id');
		// if an application has permits,
		// mark them as activated
		$conn = Doctrine_Manager::getInstance()->getCurrentConnection();
		$q = "UPDATE saved_permit SET is_activated = 1 WHERE application_id = " . $application_id;
		$conn->execute($q);

		$this->redirect('/backend.php/applications/view/id/' . $application_id);
	}
	public function executeSearchdetail(sfWebRequest $request)
	{
		$this->application = Doctrine_Query::create()
			->from('FormEntry a')
			->where('id = ? ', $request->getParameter('id'))
			->fetchOne();
	}

	public function executeSearchprofile(sfWebRequest $request)
	{
		$search_word = $request->getParameter('searchWord');
		$result = [];
		if ($me = Functions::current_user()) {
			$me = $me->getNid();
			$q = <<<EOL
SELECT CONCAT(c.strfirstname,' ',c.strlastname, ' | ', c.strphone_mobile,' | ', c.stremail) as text, c.stremail,c.strphone_mobile,  c.nid as id  FROM cf_user c
    WHERE (c.stremail LIKE '%$search_word%' OR c.strlastname LIKE '%$search_word%' OR c.strphone_mobile LIKE '%$search_word%' OR strlastname LIKE '%$search_word%')
    AND nid <> $me
LIMIT 10
EOL;
			$result = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc($q);
		}

		$this->getResponse()->setHttpHeader('content-type', 'application/json');
		return $this->renderText(json_encode($result));
	}
}
