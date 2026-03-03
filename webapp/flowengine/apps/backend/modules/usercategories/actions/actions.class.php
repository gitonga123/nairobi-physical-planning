<?php

/**
 * usercategories actions.
 *
 * @package    permit
 * @subpackage banner
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class usercategoriesActions extends sfActions
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
    //Get list of all objects
    $q = Doctrine_Query::create()
        ->from('sfGuardUserCategories a')
        ->orderBy('a.id ASC');
    $this->categories = $q->execute();

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
    $this->form = new sfGuardUserCategoriesForm();

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

    $this->form = new sfGuardUserCategoriesForm();

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
    $this->forward404Unless($category = Doctrine_Core::getTable('sfGuardUserCategories')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new sfGuardUserCategoriesForm($category);

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
    $this->forward404Unless($category = Doctrine_Core::getTable('sfGuardUserCategories')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $this->form = new sfGuardUserCategoriesForm($category);

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
      $category = $form->save();

      $q = Doctrine_Query::create()
	      ->from("SfGuardUserCategoriesForms a")
		    ->where("a.categoryid = ?", $category->getId());
      $links = $q->execute();
      foreach($links as $link)
      {
        $link->delete();
      }

      $forms = $request->getPostParameter("islinkedto");

      foreach($forms as $apform)
      {
        $linkto = new SfGuardUserCategoriesForms();
        $linkto->setCategoryid($category->getId());
        $linkto->setFormid($apform);

        if($request->getPostParameter("link_form_".$apform))
        {
          $linkto->setIslinkedto($request->getPostParameter("link_form_".$apform));
          $linkto->setIslinkedtitle($request->getPostParameter("link_title_".$apform));
        }

        $linkto->save();

      }

      $this->redirect('/plan/usercategories/index');
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
    $this->forward404Unless($category = Doctrine_Core::getTable('sfGuardUserCategories')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

    $category->delete();

    $this->redirect('/plan/usercategories/index');
  }
//OTB Start - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
	public function executeChangefield(sfWebRequest $request)
	{

		if ($request->getParameter('form_id')){
			$element_type = $request->getParameter('email') ? "email" : false;
		  	$elements = Doctrine_Core::getTable('ApFormElements')->getAllFields($request->getParameter('form_id'), $element_type);
			$new_options='';
			foreach($elements as $key => $value){
				$new_options .= "<option value='".$key."'>".$value."</option>";
			}
		  echo $new_options;
	  }
	  exit();
	}
	//Update member fields
	public function executeUpdatememeberfields(sfWebRequest $request)
	{
		$form_id=$request->getParameter('form');
		if ($form_id){
			//Get all field for member no
		  $elements_all = Doctrine_Core::getTable('ApFormElements')->getAllFields($form_id);
		  $elements_email = Doctrine_Core::getTable('ApFormElements')->getAllFields($form_id,'email');
		  echo json_encode(array('all' => $elements_all,'email' => $elements_email));
	
		}
	  exit();
	}
	public function executeUpdatememeberfieldsagenda(sfWebRequest $request)
	{
		$form_id=$request->getParameter('form');
		if ($form_id){
			//Get all field for member no
		  $elements_all = Doctrine_Core::getTable('ApFormElements')->getAllFormFieldsIncludeEmail($form_id);
		  echo json_encode(array('all' => $elements_all));
	
		}
	  exit();
	}
	public function executeElementvalues(sfWebRequest $request)
	{
		$form_id=$request->getParameter('form');
		$element_id=$request->getParameter('element');
		echo json_encode(array('elements' => Doctrine_Core::getTable('ApElementOptions')->getElementOptions($form_id,$element_id)));
		exit;
	}
	protected function setSfFormChoiceWidgetOptionsFromApFormElements($apform_id, $widget_name, $element_type = false)
	{
		$widget = $this->form->getWidget($widget_name);
		$widget->setOptions(array('choices' => Doctrine_Core::getTable('ApFormElements')->getAllFields($apform_id, $element_type)));
	}
//OTB End - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
}
