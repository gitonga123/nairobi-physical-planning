<?php

/**
 * conditionsmng actions.
 *
 * @package    permit
 * @subpackage conditionsmng
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class conditionsmngActions extends sfActions
{
  
  public function executeBatch(sfWebRequest $request)
  {
		if($request->getPostParameter('delete'))
		{
			$item = Doctrine_Core::getTable('ConditionsOfApproval')->find(array($request->getPostParameter('delete')));
			if($item)
			{
				$item->delete();
			}
		}
    }
  public function executeIndex(sfWebRequest $request)
  {
	  
	  $q = Doctrine_Query::create()
       ->from('ConditionsOfApproval a')
       ->andWhere('a.permit_id = ?', $request->getParameter('filter'))
	   ->orderBy('a.short_name ASC');
     $this->conditions = $q->execute();
	 
	 $this->filter = $request->getParameter("filter");
	$this->setLayout('layout-settings');
  }

  public function executeNew(sfWebRequest $request)
  {
	$conditions=new ConditionsOfApproval();
	$conditions->setPermitId($request->getParameter("filter"));
    $this->form = new ConditionsOfApprovalForm($conditions);
   $this->filter = $request->getParameter("filter");
	$this->setLayout('layout-settings');
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new ConditionsOfApprovalForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($conditions_of_approval = Doctrine_Core::getTable('ConditionsOfApproval')->find(array($request->getParameter('id'))), sprintf('Object conditions_of_approval does not exist (%s).', $request->getParameter('id')));
    $this->form = new ConditionsOfApprovalForm($conditions_of_approval);
   $this->filter = $request->getParameter("filter");
	$this->setLayout('layout-settings');
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($conditions_of_approval = Doctrine_Core::getTable('ConditionsOfApproval')->find(array($request->getParameter('id'))), sprintf('Object conditions_of_approval does not exist (%s).', $request->getParameter('id')));
    $this->form = new ConditionsOfApprovalForm($conditions_of_approval);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {

    $this->forward404Unless($conditions_of_approval = Doctrine_Core::getTable('ConditionsOfApproval')->find(array($request->getParameter('id'))), sprintf('Object conditions_of_approval does not exist (%s).', $request->getParameter('id')));

    $audit = new Audit();
    $audit->saveAudit("", "deleted condition of approval of id ".$conditions_of_approval->getId());


    $conditions_of_approval->delete();

    $this->redirect('/plan/conditionsmng/index/filter/'.$conditions_of_approval->getPermitId());
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $conditions_of_approval = $form->save();

      $audit = new Audit();
      $audit->saveAudit("", "<a href=\"/plan/conditionsmng/edit?id=".$conditions_of_approval->getId()."&language=en\">updated a condition of approval</a>");

      $this->redirect('/plan/conditionsmng/index/filter/'.$conditions_of_approval->getPermitId());
    }
  }
}
