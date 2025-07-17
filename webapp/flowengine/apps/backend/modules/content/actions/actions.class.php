<?php

/**
 * contentactions.
 *
 * @package    backend
 * @subpackage content
 * @author     Thomas Juma
 * @version    2.5: 2017-01-24
 */
class contentActions extends sfActions
{
  /**
	 * Executes 'index' function
	 *
	 * Display a list of existing objects
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeIndex(sfWebRequest $request)
  {
    //Toggle the published status of a web page
    if($request->getParameter("ptoggle"))
    {
        $content = Doctrine_Core::getTable('Content')->find(array($request->getParameter('ptoggle')));
        if($content->getPublished() == "1")
        {
          $content->setPublished("0");
        }
        else
        {
          $content->setPublished("1");
        }

        $content->save();
    }

    //Get list of all web pages
    $q = Doctrine_Query::create()
        ->from('Content a')
        ->orderBy('a.menu_index ASC');
    $this->contents = $q->execute();

    $this->setLayout("layout-settings");
  }

  /**
	 * Executes 'new' function
	 *
	 * Create a new object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeNew(sfWebRequest $request)
  {
    $this->form = new ContentForm();

	  $this->setLayout("layout-settings");
  }

  /**
	 * Executes 'create' function
	 *
	 * Save a new object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new ContentForm();

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
  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($content = Doctrine_Core::getTable('Content')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new ContentForm($content);

    $this->setLayout("layout-settings");
  }

  /**
	 * Executes 'update' action
	 *
	 * Update an existing object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($content = Doctrine_Core::getTable('Content')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $this->form = new ContentForm($content);

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
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $content = $form->save();

      $this->redirect('/plan/content/index');
    }
  }

  /**
	 * Executes 'delete' action
	 *
	 * Delete the object
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeDelete(sfWebRequest $request)
  {
    $this->forward404Unless($content = Doctrine_Core::getTable('Content')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $content->delete();

    $this->redirect('/plan/content/index');
  }

  /**
	 * Executes 'Orderup' action
	 *
	 * Move the order of a web page up by one spot
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeOrderup(sfWebRequest $request)
  {
    $this->forward404Unless($content = Doctrine_Core::getTable('Content')->find(array($request->getParameter('id'))), sprintf('Object menus does not exist (%s).', $request->getParameter('id')));

    $q = Doctrine_Query::create()
         ->from("Content a")
         ->where("a.menu_index < ?", $content->getMenuIndex())
         ->orderBy('a.menu_index DESC');
    $previous_content = $q->fetchOne();

    if($previous_content)
    {
      $current_order = $content->getMenuIndex();
      $previous_order = $previous_content->getMenuIndex();

      $previous_content->setMenuIndex(-1); //temporary set page order to prevent conflict
      $previous_content->save();

      $content->setMenuIndex($previous_order);
      $content->save();

      $previous_content->setMenuIndex($current_order);
      $previous_content->save();
    }

    return $this->redirect("/plan/content/index");
  }

  /**
	 * Executes 'Orderdown' action
	 *
	 * Move the order of a web page down by one spot
	 *
	 * @param sfRequest $request A request object
	 */
  public function executeOrderdown(sfWebRequest $request)
  {
    $this->forward404Unless($content = Doctrine_Core::getTable('Content')->find(array($request->getParameter('id'))), sprintf('Object menus does not exist (%s).', $request->getParameter('id')));

    $q = Doctrine_Query::create()
         ->from("Content a")
         ->where("a.menu_index > ?", $content->getMenuIndex())
         ->orderBy('a.menu_index ASC');
    $previous_content = $q->fetchOne();

    if($previous_content)
    {
      $current_order = $content->getMenuIndex();
      $previous_order = $previous_content->getMenuIndex();

      $previous_content->setMenuIndex(-1); //temporary set page order to prevent conflict
      $previous_content->save();

      $content->setMenuIndex($previous_order);
      $content->save();

      $previous_content->setMenuIndex($current_order);
      $previous_content->save();
    }

    return $this->redirect("/plan/content/index");
  }

}
