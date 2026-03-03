<?php
/**
 * index components.
 *
 * Contains code snippets that can be inserted into the layout
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class indexComponents extends sfComponents
{
	/**
	 * Executes 'Checksession' component
	 *
	 * Checks whether the user is logged in correctly
	 *
	 */
	public function executeChecksession()
	{

	}

	/**
	 * Executes 'Stylesheets' component
	 *
	 * Displays stylesheets on the layout
	 *
	 */
	public function executeStylesheets()
	{

	}

	/**
	 * Executes 'Javascripts' component
	 *
	 * Displays javascripts on the layout
	 *
	 */
	public function executeJavascripts()
	{

	}

	/**
	 * Executes 'Stylesheets' component
	 *
	 * Displays stylesheets on the layout
	 *
	 */
	public function executeStylesheetsdash()
	{

	}

	/**
	 * Executes 'Javascripts' component
	 *
	 * Displays javascripts on the layout
	 *
	 */
	public function executeJavascriptsdash()
	{

	}

	/**
	 * Executes 'Header' component
	 *
	 * Displays header
	 *
	 */
	public function executeHeader()
	{

	}

	/**
	 * Executes 'Headerdash' component
	 *
	 * Displays header
	 *
	 */
	public function executeHeaderdash()
	{
		$q = Doctrine_Query::create()
            ->from('Communications a')
            ->leftJoin('a.FormEntry b')
            ->where('a.reviewer_id <> ?', "")
            ->andWhere('b.user_id = ?', $this->getUser()->getGuardUser()->getId())
			->andWhere('a.messageread = 0')
            ->orderBy('a.id DESC');
		$this->messages = $q->count();
	}

	/**
	 * Executes 'Headerprofile' component
	 *
	 * Displays header
	 *
	 */
	public function executeHeaderprofile()
	{

	}

	/**
	 * Executes 'Sidemenu' component
	 *
	 * Displays sidemenu
	 *
	 */
	public function executeSidemenu()
	{

	}

	/**
	 * Executes 'Footer' component
	 *
	 * Displays footer
	 *
	 */
	public function executeFooter()
	{

	}

	/**
	 * Executes 'Banner' component
	 *
	 * Displays banner
	 *
	 */
  	public function executeBanner()
	{
		$q = Doctrine_Query::create()
			->from('Banner a')
			->orderBy('a.id ASC');
		$this->banners = $q->execute();
	}

	/**
	 * Executes 'Menu' component
	 *
	 * Displays menu
	 *
	 */
	public function executeMenu()
	{
		if($this->getUser()->isAuthenticated())
		{
			$q = Doctrine_Query::create()
				  ->from('Content a')
				  ->where('a.published = ?', 1)
				  ->andWhere('a.visibility = 1 AND a.visibility = 0')
				  ->orderBy('a.menu_index ASC');
			$this->pages = $q->execute();

		}
		else
		{
			$q = Doctrine_Query::create()
				  ->from('Content a')
				  ->where('a.published = ? and a.parent_id = ?', array(1,0))
				  ->andWhere('a.visibility = 1')
				  ->orderBy('a.menu_index ASC');
			$this->pages = $q->execute();
		}
	}

}
