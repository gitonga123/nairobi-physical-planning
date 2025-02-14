<?php

/**
 * Frusers actions.
 *
 * Allows the client to manage their account information
 *
 * @package    frontend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class frusersActions extends sfActions
{
	/**
	 * Executes 'Adddetails' action
	 *
	 * Allows client to add missing additional details due to registration errors
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeAdddetails(sfWebRequest $request)
	{
		$this->formid = $request->getParameter('formid');
		$this->entryid = $request->getParameter('entryid');
		$this->setLayout("layoutdash");
	}

	public function executeCategory(sfWebRequest $request)
	{
		$this->setLayout("layout");
	}

	/**
	 * Executes 'Editadditional' action
	 *
	 * Allows client to edit additional details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEditadditional(sfWebRequest $request)
	{
		$this->formid = $request->getParameter('formid');
		$this->entryid = $request->getParameter('entryid');
		$this->setLayout("layoutmentordashsubmit");
	}

	/**
	 * Executes 'Edit' action
	 *
	 * Allows client to edit basic details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEdit(sfWebRequest $request)
	{
		$this->forward404Unless($sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id'))), sprintf('Object sf_guard_user does not exist (%s).', $request->getParameter('id')));
		$this->form = new sfGuardUserForm($sf_guard_user);
		$this->setLayout("layoutmentordashsubmit");
	}

	/**
	 * Executes 'Update' action
	 *
	 * Updates client's basic details
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeUpdate(sfWebRequest $request)
	{
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id'))), sprintf('Object sf_guard_user does not exist (%s).', $request->getParameter('id')));
		$this->form = new sfGuardUserForm($sf_guard_user);

		$this->processForm($request, $this->form);

		$this->setTemplate('edit');
		$this->setLayout("layoutdash");
	}

	/**
	 * Executes 'processForm' action
	 *
	 * Called by Update action to validate form before updating basic details
	 *
	 * @param sfRequest $request A request object
	 */
	protected function processForm(sfWebRequest $request, sfForm $form)
	{
		$sf_guard_user = Doctrine_Core::getTable('sfGuardUser')->find(array($request->getParameter('id')));
		if ($sf_guard_user) {
			$user_profile = $sf_guard_user->getProfile();
			$user_profile->setFullname($request->getPostParameter("sfApplyApply2[fullname]"));
			$user_profile->setEmail($request->getPostParameter("sfApplyApply2[email]"));
			$user_profile->save();

			$sf_guard_user->setUsername($request->getPostParameter("sfApplyApply2[username]"));
			$sf_guard_user->setIsActive($request->getPostParameter("sfApplyApply2[active]"));
			$sf_guard_user->setIsSuperAdmin($request->getPostParameter("sfApplyApply2[validated]"));
			if ($request->getPostParameter("sfApplyApply2[password]")) {
				$sf_guard_user->setPassword($request->getPostParameter("sfApplyApply2[password]"));
			}

			$sf_guard_user->save();

			$audit = new Audit();
			$audit->saveAudit("", "<a href=\"/plan/frusers/show/id/" . $sf_guard_user->getId() . "\">Updated a user account</a>");

			$this->redirect('/backend.php/settings');
		}
	}
}
