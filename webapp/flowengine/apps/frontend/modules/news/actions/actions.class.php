<?php
/**
 * News actions.
 *
 * Displays published news articles
 *
 * @package    frontend
 * @subpackage news
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 
class newsActions extends sfActions
{
	/**
	* Executes 'Index' action
	*
	* Displays list of published news articles
	*
	* @param sfRequest $request A request object
	*/
	public function executeIndex(sfWebRequest $request)
	{
		$q = Doctrine_Query::create()
		   ->from('News a')
		   ->where('a.published = ?', 1)
		   ->orderBy('a.id DESC');
		 $this->pager = new sfDoctrinePager('News', 5);
		 $this->pager->setQuery($q);
		 $this->pager->setPage($request->getParameter('page', 1));
		 $this->pager->init();
	}
  
	/**
	* Executes 'Article' action
	*
	* Displays full news article
	*
	* @param sfRequest $request A request object
	*/
	public function executeArticle(sfWebRequest $request)
	{
		 $q = Doctrine_Query::create()
		   ->from('News a')
		   ->where('a.id = ?', $request->getParameter("id"));
		 $this->news = $q->fetchOne();

		//If page does not exist then redirect to 404
		if(empty($this->news))
		{
			return $this->redirect("plan/errors/notfound");
		}
	}
}
