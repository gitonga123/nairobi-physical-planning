<?php

/**
 * Professionals actions.
 *
 * View application details
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class professionalsActions extends sfActions
{

	/*End  patch*/
	/**
	 * Executes 'View' action
	 *
	 * Display application details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
		$this->form_id = 96247;
		$q = Doctrine_Query::create()
			->select('a.form_id,a.form_name')
			->from("ApForms a")
			->where('a.form_id = ?', $this->form_id)
			->limit(1);
		$this->apform = $q->fetchArray();

		$q = Doctrine_Query::create()
			->select('a.element_title, a.element_id, a.element_type, eo.*')
			->from('ApFormElements a')
			->leftJoin(
				'a.Options eo WITH eo.element_id = a.element_id AND eo.form_id = a.form_id'
			)
			->where('a.form_id = ?', $this->form_id)
			->andWhere('a.element_status = ?', 1);

		$this->form_elements = $q->fetchArray();

		//Entries
		$this->entries = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT * FROM ap_form_" . $this->form_id . " WHERE status = 1");
		$this->setLayout('layout');
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

	public function executeApprove(sfWebRequest $request)
	{
		$form_id = $request->getParameter('form');
		$entry_id = $request->getParameter('entry');

		$q = Doctrine_Query::create()
			->from('MembersDatabase a')
			->where('a.entry_id = ? and a.form_id = ?', [$entry_id, $form_id])
			->orderBy('a.id DESC');
		$membership_database = $q->fetchOne();

		if (!$membership_database) {
			$this->getUser()->setFlash("error", "Failed! User associated with this record couldn't be found.");
			$this->redirect('/plan/professionals/index/');
		}

		$membership_database->setValidate('');

		$membership_database->save();

		$user_prof = Doctrine_Core::getTable('SfGuardUserProfile')->findOneByUserId($membership_database->getUserId());

		$message = "Hello {$user_prof['fullname']}, your membership has been approved. You may now log in and submit applications. Thank you.";

		$notification = new mailnotifications();

		$notification->sendsms($user_prof->getMobile(), $message);

		$this->getUser()->setFlash("notice", "User Account Approved.");
		$this->redirect('/plan/professionals/index/');
	}

	public function executeDeactivate(sfWebRequest $request)
	{
		$form_id = $request->getParameter('form');
		$entry_id = $request->getParameter('entry');

		$q = Doctrine_Query::create()
			->from('MembersDatabase a')
			->where('a.entry_id = ? and a.form_id = ?', [$entry_id, $form_id])
			->orderBy('a.id DESC');
		$membership_database = $q->fetchOne();


		$validate = rand(1000000, 9999999);
		$membership_database->setValidate($validate);
		$membership_database->save();

		$this->getUser()->setFlash("notice", "User Account has been deactivated.");
		$this->redirect('/plan/professionals/index/');
	}

	public function executeView(sfWebRequest $request)
	{
		$this->form_id = $request->getParameter('form');
		$this->entry_id = $request->getParameter('entry');

		$applicationManager = new ApplicationManager();

		$this->view_data = $applicationManager->get_json_by_form($this->form_id, $this->entry_id);

		$this->application_data = json_decode($this->view_data, true);

		$this->setLayout('layout');
	}
}
