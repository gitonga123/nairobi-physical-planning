<?php

/**
 * Dashboard actions.
 *
 * Displays a summary of all application related activity.
 *
 * @package    frontend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class dashboardActions extends sfActions
{
	/**
	 * Executes 'Index' action
	 *
	 * Displays the client's dashboard
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
		//If the user is not authenticated then redirect to index page
		if (!$this->getUser()->isAuthenticated()) {
			$this->redirect('/plan/');
		}

		//If no user is authenticated then signout. Backend session and Frontend session mix ups
		if ($this->getUser()->getGuardUser() == null) {
			header("Location: /plan//signon/logout");
		}
		// All permits
		$q = Doctrine_Query::create()
			->from('SavedPermit a')
			->leftJoin('a.FormEntry b')
			->where('b.user_id = ?', $this->getUser()->getGuardUser()->getId())
			->andWhere('a.permit_status <> 3')
			->orderBy('a.id DESC');
		$this->saved_permits = $q->execute();
		// All applications
		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->leftJoin("a.Form f")
			->leftJoin("a.Stage s")
			->where("a.user_id = ? and a.deleted_status = ? and a.parent_submission =?", [$this->getUser()->getGuardUser()->getId(), 0, 0])
			->orderBy('a.id desc');

		$this->all_applications = $q->execute();

		//Display list of latest applications
		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->leftJoin("a.Form f")
			->leftJoin("a.Stage s")
			->where("a.user_id = ? and a.deleted_status = ? and a.parent_submission =?", [$this->getUser()->getGuardUser()->getId(), 0, 0])
			->orderBy('a.id desc')
			->limit(100);

		$this->latest_applications = $q->execute();

		$q = Doctrine_Query::create()
			->from("MfInvoice i")
			->leftJoin("i.FormEntry e")
			->leftJoin("e.Form f")
			->leftJoin("e.Stage s")
			->where("i.paid = ? and e.user_id = ? and e.deleted_status = ? and e.parent_submission =?", [1, $this->getUser()->getGuardUser()->getId(), 0, 0])
			->orderBy('i.id desc');
		$this->all_invoices = $q->execute();

		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
			->andWhere("a.approved <> ?", 0)
			->andWhere('a.parent_submission = ? and a.deleted_status = ?', [0, 0])
			->andWhere("a.declined = 1")
			->orderBy("a.id DESC")
			->limit(2);
		$this->corrections_applications = $q->execute();

		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->leftJoin("a.MfInvoice b")
			->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
			->andWhere("a.approved <> ?", 0)
			->andWhere('a.parent_submission = ? and a.deleted_status = ?', [0, 0])
			->andWhere("b.paid = 1")
			->orderBy("a.id DESC");
		$this->renewal_applications = $q->execute();

		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->where("a.circulation_id = ?", $this->getUser()->getGuardUser()->getId())
			->limit(2);
		$this->transferring_applications = $q->execute();
		$this->setLayout("layoutmentordash");
	}
	public function executeApplicationslist(sfWebRequest $request)
	{

		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->leftJoin("a.Form f")
			->leftJoin("a.Stage s")
			->where("a.user_id = ? and a.deleted_status = ? and a.parent_submission =?", [$this->getUser()->getGuardUser()->getId(), 0, 0])
			->orderBy('a.id desc');

		$this->all_applications = $q->execute();
		$this->setLayout("layoutmentordash");
	}
	public function executeCorrectionsList(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
			->andWhere("a.approved <> ?", 0)
			->andWhere('a.parent_submission = ? and a.deleted_status = ?', [0, 0])
			->andWhere("a.declined = 1")
			->orderBy("a.id DESC");
		$this->corrections_applications = $q->execute();
		$this->setLayout("layoutmentordash");
	}
	public function executePlotinformation(sfWebRequest $request)
	{
		// If there is a parameter to indicate that we are to work with response
		$plot = preg_replace('/[^\d]/S', '', $request->getParameter('q'));
		$message = "The plot number has not been specified.";
		$color = "#f29b11";

		// If there is no plot information
		if ('' !== $plot) {
			// Run the db query to check if the record is active or not
			$row = Doctrine_Query::create()->getConnection()->execute(
				"SELECT * FROM plot a WHERE a.plot_no = :plot",
				array('plot' => $plot)
			)->fetch(\PDO::FETCH_ASSOC);

			// If the plot does not exist
			if (false === $row) {
				$message = "This plot does not exist.";
			}

			// If the plot is black listed
			else if ('Black-Listed' == $row['plot_status']) {
				$message = "This plot has been black-listed.";
				$color = "#e86051";
			}

			// All is well
			else {
				$message = "This is a valid plot number.";
				$color = "#29ba9b";
			}
		}

		// Set the content
		// $this->getResponse()->setContent("<span style='color:{$color}'>{$message}</span>");
		$this->getResponse()->setContent(json_encode($row));


		// Disable page rendering
		return sfView::NONE;
	}
	public function executeInvoiceslist(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from("MfInvoice i")
			->leftJoin("i.FormEntry e")
			->leftJoin("e.Form f")
			->leftJoin("e.Stage s")
			->where("i.paid = ? and e.user_id = ? and e.deleted_status = ? and e.parent_submission =?", [1, $this->getUser()->getGuardUser()->getId(), 0, 0])
			->orderBy('i.id desc');
		$this->all_invoices = $q->execute();
		$this->setLayout("layoutmentordash");
	}	
	public function executePaidinvoices(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
			->from("MfInvoice i")
			->leftJoin("i.FormEntry e")
			->leftJoin("e.Form f")
			->leftJoin("e.Stage s")
			->where("i.paid = ? and e.user_id = ? and e.deleted_status = ? and e.parent_submission =?", [2, $this->getUser()->getGuardUser()->getId(), 0, 0])
			->orderBy('i.id desc');
		$this->all_invoices = $q->execute();
		$this->setLayout("layoutmentordash");
	}
	private function _applicationsQuery($cols = null, $request = null)
	{
		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->leftJoin("a.Form f")
			->leftJoin("a.Stage s")
			->where("a.user_id = ? and a.deleted_status = ? and a.parent_submission =?", [$this->getUser()->getGuardUser()->getId(), 0, 0]);
		if (null === $cols) return $q;

		$search = $request->getParameter('search')['value'];

		if ("" === $search) return $q;
		$sql = [];
		$params = [];

		foreach ($cols as $i => $col) {
			$sql[] = $col . " LIKE ?";
			$params[] = '%' . $search . '%';
		}
		//error_log('-----------SQL----------');
		//error_log(print_r($sql,true));
		//error_log('-------------PARAMS---------');
		//error_log(print_r($params,true));
		//error_log('-------SQL------'.$q->getSqlQuery());

		$q->addWhere("(" . implode(" OR ", $sql) . ")", $params);
		return $q;
	}
	private function _invoicesQuery($cols = null, $request = null)
	{
		$q = Doctrine_Query::create()
			->from("MfInvoice i")
			->leftJoin("i.FormEntry e")
			->leftJoin("e.Form f")
			->leftJoin("e.Stage s")
			->where("e.user_id = ? and e.deleted_status = ? and e.parent_submission =?", [$this->getUser()->getGuardUser()->getId(), 0, 0]);
		if (null === $cols) return $q;

		$search = $request->getParameter('search')['value'];

		if ("" === $search) return $q;
		$sql = [];
		$params = [];

		foreach ($cols as $i => $col) {
			$sql[] = $col . " LIKE ?";
			$params[] = '%' . $search . '%';
		}
		//error_log('-----------SQL----------');
		//error_log(print_r($sql,true));
		//error_log('-------------PARAMS---------');
		//error_log(print_r($params,true));
		//error_log('-------SQL------'.$q->getSqlQuery());

		$q->addWhere("(" . implode(" OR ", $sql) . ")", $params);
		return $q;
	}
	public function executeDownloadpermits(sfWebRequest $request)
	{
		$date = date('h_i_s__d_m_y');
		$file_name = "permits_$date.csv";

		$columns = ['Name', 'Mobile', 'Email', 'Application', 'Type', 'Date Of Issue', 'Status'];
		$this->filterPermits($request); # sets the data
		$data = $this->permits;

		$file = fopen($file_name, 'w');
		# fill the columns
		fputcsv($file, $columns);
		foreach ($data as $row) {
			fputcsv(
				$file,
				[
					$row['fullname'],
					$row['mobile'] ? ((strpos($row['mobile'], '254') === 0) ? $row['mobile'] : "254" . substr($row['mobile'], 1)) : '',
					$row['email'],
					$row['a_id'],
					$row['title'],
					$row['date_of_issue'],
					$row['permit_status'] == 1 ? 'Approved' : 'Cancelled',
				]
			);
		}

		fclose($file);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_name));
		readfile($file_name);
		exit();
	}


	function filterPermits(sfWebRequest $request)
	{
		# should filter by period
		# search by word

		$date_of_issue_gte = $request->getGetParameter('date_of_issue_gte');
		$date_of_issue_lte = $request->getGetParameter('date_of_issue_lte');
		$status = $request->getGetParameter('status');

		$query =
			"SELECT s.*, s.id as permit_id, p.title, f.application_id as a_id, up.email, up.fullname, up.mobile FROM "
			. "saved_permit s LEFT JOIN permits p ON s.type_id = p.id"
			. " JOIN form_entry f ON s.application_id = f.id "
			. " JOIN sf_guard_user u ON f.user_id = u.id "
			. " JOIN sf_guard_user_profile up ON u.id = up.user_id ";

		if ($date_of_issue_gte and $date_of_issue_lte) {
			$query .= "WHERE s.date_of_issue BETWEEN '$date_of_issue_gte 00:00:00'  AND '$date_of_issue_lte 00:00:00' ";
		}

		if ($status > 0) { # assumes that -1 means all
			$query .= " AND s.permit_status = $status";
		}

		$query .= " ORDER BY s.date_of_issue DESC";

		$q = Doctrine_Manager::getInstance()
			->getCurrentConnection()->fetchAssoc($query);

		$this->permits = $q;
	}


	public function executeChecknextaction(sfWebRequest $request)
	{
		$application = $request->getParameter('id');

		$this->application = Doctrine_Query::create()
			->from('FormEntry f')
			->where('f.id = ?', $application)
			->fetchOne();
		$table = [];
		if ($this->application) {
			$user_registered_as = Doctrine_Query::create()
				->from('sfGuardUserProfile u')
				->where('u.user_id = ?', $this->getUser()->getGuardUser()->getId());
			$user_registered_as_res =   $user_registered_as->fetchOne();

			$this->forms_link = [];
			//if we have something
			if ($user_registered_as_res) {
				$q = Doctrine_Query::create()
					->select('f.formid')
					->from('SfGuardUserCategoriesForms f')
					->where('f.categoryid = ? and f.formid <> ?', array($user_registered_as_res->getRegisteras(), $this->application->getFormId()));
				$cat_forms = $q->fetchArray();

				$q = Doctrine_Query::create()
					->from('ApForms f')
					->where('f.form_stage =? and f.form_active =? and f.form_type =?', array($this->application->getApproved(), 1, 1))
					->andWhereIn('f.form_id', array_column($cat_forms, 'formid'));
				$this->forms_link = $q->execute();
				foreach ($this->forms_link as $form_link) {
					$table['form_id'] = $form_link->getFormId();
					$table['application'] = $application;
				}
			}
		}
		return $this->renderText(json_encode($table));
	}
}
