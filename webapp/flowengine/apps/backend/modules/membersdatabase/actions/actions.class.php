<?php

class membersdatabaseActions extends sfActions
{

  public function executeIndex(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
       ->from('MembersDatabase a')
	   ->orderBy('a.id DESC');
     $this->records = $q->execute();
	 
	$this->setLayout("layout-settings");
  }

  public function executeAssociation(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
       ->from('MembersDatabase a')
	   ->where('a.user_category_id = ?',$request->getParameter('filter'))
	   ->orderBy('a.id DESC');
     $this->records = $q->execute();
	 
	$this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new MembersDatabaseForm();
	$this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new MembersDatabaseForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($record = Doctrine_Core::getTable('MembersDatabase')->find(array($request->getParameter('id'))), sprintf('Object record does not exist (%s).', $request->getParameter('id')));
    $this->form = new MembersDatabaseForm($record);
	  $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($record = Doctrine_Core::getTable('MembersDatabase')->find(array($request->getParameter('id'))), sprintf('Object record does not exist (%s).', $request->getParameter('id')));
    $this->form = new MembersDatabaseForm($record);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {

    $this->forward404Unless($record = Doctrine_Core::getTable('MembersDatabase')->find(array($request->getParameter('id'))), sprintf('Object record does not exist (%s).', $request->getParameter('id')));

    $audit = new Audit();
    $audit->saveAudit("", "deleted record of id ".$record->getId());

    $record->delete();

    $this->redirect('/backend.php/membersdatabase');
  }

  public function executeValidate(sfWebRequest $request)
  {

    $this->forward404Unless($record = Doctrine_Core::getTable('MembersDatabase')->find(array($request->getParameter('id'))), sprintf('Object record does not exist (%s).', $request->getParameter('id')));

    $audit = new Audit();
    $audit->saveAudit("", "validate MembersDatabase number ".$record->getMembersNo());

    $record->setValidate(null)->save();

    $this->redirect('/backend.php/membersdatabase');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $record = $form->save();

      $audit = new Audit();
      $audit->saveAudit("", "<a href=\"/backend.php/membersdatabase/edit?id=".$record->getId()."&language=en\">updated a user association validation record (MembersDatabase)</a>");

      $this->redirect('/backend.php/membersdatabase');
    }
  }
}
