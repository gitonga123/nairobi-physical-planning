<?php
/**
 * Application actions.
 *
 * Displays applications submitted by the client
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class archivesActions extends sfActions
{
       /**
	 * Executes 'Index' action
	 *
	 * Displays list of all of the currently logged in client's applications
	 *
	 * @param sfRequest $request A request object
	 */
        public function executeIndex(sfWebRequest $request)
        {
                $this->page = $request->getParameter('page', 1);
				$this->setLayout("layoutdash");

				if($request->getParameter("subgroup"))
				{
					$q = Doctrine_Query::create()
						->from('SubMenus a')
						->where("a.id = ?", $request->getParameter("subgroup"));
					$submenu = $q->fetchOne();
					$_SESSION['group'] = $submenu->getMenuId();
					$_SESSION['subgroup'] = $request->getParameter("subgroup");

                    $this->stage = $submenu->getId();

					if($request->getParameter("form"))
					{
						$q = Doctrine_Query::create()
						   ->from("FormEntryArchive a")
						   ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
						   ->andWhere("a.form_id = ?", $request->getParameter("form"))
						   ->andWhere("a.approved = ?", $request->getParameter("subgroup"))
						   ->orderBy("a.application_id DESC");
					}
					else
					{
						$q = Doctrine_Query::create()
						   ->from("FormEntryArchive a")
						   ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
						   ->andWhere("a.approved = ?", $request->getParameter("subgroup"))
						   ->orderBy("a.application_id DESC");
					}
				}
				else
				{
					if($request->getParameter("form"))
					{
						$q = Doctrine_Query::create()
						   ->from("FormEntryArchive a")
						   ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
						   ->andWhere("a.form_id = ?", $request->getParameter("form"))
						   ->andWhere('a.approved <> 0 AND a.approved <> ?', '')
						   ->orderBy("a.application_id DESC");

					}
					else
					{
						if($request->getParameter("drafts"))
						{
							$q = Doctrine_Query::create()
							   ->from("FormEntryArchive a")
							   ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
							   ->andWhere('a.approved = 0')
							   ->orderBy("a.application_id DESC");

							$_SESSION['group'] = 0;
							$_SESSION['subgroup'] =0;
						}
						else
						{
							$q = Doctrine_Query::create()
							   ->from("FormEntryArchive a")
							   ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
							   ->andWhere('a.approved <> 0 AND a.approved <> ?', '')
							   ->orderBy("a.application_id DESC");

							$_SESSION['group'] = 0;
							$_SESSION['subgroup'] =0;
						}
					}
				}

            $this->pager = new sfDoctrinePager('FormEntryArchive', 10);
            $this->pager->setQuery($q);
            $this->pager->setPage($request->getParameter('page', 1));
            $this->pager->init();
        }

        /**
	 * Executes 'View' action
	 *
	 * Displays full application details
	 *
	 * @param sfRequest $request A request object
	 */
        public function executeView(sfWebRequest $request)
        {
            $q = Doctrine_Query::create()
                ->from('FormEntryArchive a')
                ->where('a.id = ?', $request->getParameter("id"))
                ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
            $this->application = $q->fetchOne();

			$this->setLayout("layoutdash");
        }

}
