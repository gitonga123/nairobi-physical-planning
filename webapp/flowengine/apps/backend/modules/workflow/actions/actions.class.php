<?php

/**
 * workflow actions.
 *
 * @package    permit
 * @subpackage workflow
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class workflowActions extends sfActions
{
  public function executeIndexCategory(sfWebRequest $request)
  {
		$q=Doctrine_Query::create()
            ->from("WorkflowCategory c")
            ->orderBy("c.order_id ASC");
		$this->services = $q->execute();
		$this->setLayout('layout-settings');
  }
  public function executeNewCategory(sfWebRequest $request)
  {
	  $this->form=new WorkflowCategoryForm();
	  $this->getUser()->getAttributeHolder()->remove('delete_cat_notice');
		$this->setLayout('layout-settings');
  }
  public function executeEditCategory(sfWebRequest $request)
  {
    $this->forward404Unless($workflow = Doctrine_Core::getTable('WorkflowCategory')->find(array($request->getParameter('id'))), sprintf('Object does not exist (%s).', $request->getParameter('id')));
    $this->form = new WorkflowCategoryForm($workflow);
	$this->setTemplate('newCategory');
		$this->setLayout('layout-settings');
  }
  public function executeDeleteCategory(sfWebRequest $request)
  {
	$work_cat_id=$request->getParameter('id');
    $this->forward404Unless($workflow = Doctrine_Core::getTable('WorkflowCategory')->find(array($work_cat_id)), sprintf('Object does not exist (%s).', $request->getParameter('id')));
    $workflow->delete();
	$menues=Doctrine_Core::getTable('Menus')->findByCategoryId($work_cat_id);
	foreach($menues as $menu){
		Doctrine_Core::getTable('Menus')->find($menu['id'])->setCategoryId(0)->save();
	}
	$this->getUser()->setAttribute('delete_cat_notice','Category id '.$work_cat_id.' has been deleted!');
    $this->redirect('/backend.php/workflow/indexCategory');
  }
  public function executePostCategory(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
	$id=$request->getParameter('id');
	$msg='created';
	if(strlen($id)){
		$workflow = Doctrine_Core::getTable('WorkflowCategory')->find(array($request->getParameter('id')));
		$this->form = new WorkflowCategoryForm($workflow);
		$msg='updated';
	}else{
		$this->form = new WorkflowCategoryForm();
	}
    $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
    if ($this->form->isValid())
    {
		$category=$this->form->save();
		$services=$request->getParameter('service');
		//error_log(var_dump($services));
		foreach($services as $service){
			Doctrine_Core::getTable('Menus')->find($service)->setCategoryId($category->getId())->save();
		}
		$this->getUser()->setAttribute('delete_cat_notice','Category '.$category->getTitle().' has been '.$msg.'!');
		$this->redirect('/backend.php/workflow/indexCategory');
	}
	$this->setTemplate('newCategory');
		$this->setLayout('layout-settings');
  }
}
