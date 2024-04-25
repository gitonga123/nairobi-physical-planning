<?php
/**
 * Permits actions.
 *
 * Displays all permits issued to currently logged in client
 *
 * @package    frontend
 * @subpackage sharedapplication
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class archivedpermitsActions extends sfActions
{
    

     /**
	 * Executes 'View' action
	 *
	 * Displays permit (Non-PDF)
	 *
	 * @param sfRequest $request A request object
	 */
    public function executeView(sfWebRequest $request)
    {

        $q = Doctrine_Query::create()
            ->from('SavedPermitArchive a')
            ->leftJoin('a.FormEntry b')
          ->where('a.id = ?', $request->getParameter("id"));
        $this->permit = $q->fetchOne();

        $this->application = $this->permit->getFormEntry();

        $this->done = $request->getParameter("done", 0);

        $this->setLayout("layoutdash");
    }
      
}
