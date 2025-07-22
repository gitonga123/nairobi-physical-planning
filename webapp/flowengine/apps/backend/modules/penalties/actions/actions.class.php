<?php

/**
 * penalties actions.
 *
 * @package    backend
 * @subpackage penalties
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class penaltiesActions extends sfActions
{

  public function executeIndex(sfWebRequest $request)
  {
      $service = null;

      if($request->getParameter("filter"))
      {
        $service = $request->getParameter("filter");
        $this->getUser()->setAttribute("filter", $service);
      }
      else 
      {
        $service = $this->getUser()->getAttribute("filter");
      }

      $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.menu_id = ?", $request->getParameter("filter", $this->getUser()->getAttribute("filter")));
      $stages = $q->execute();

      $stages_array = array();

      foreach($stages as $stage)
      {
          $stages_array[] = "a.applicationstage = ".$stage->getId();
      }

      $stages_query = implode(" OR ", $stages_array);

      $permit_templates = null;

      if($stages_query == "")
      {
        $q = Doctrine_Query::create()
          ->from("Permits a");
        $permit_templates = $q->execute();
      }
      else 
      {
        $q = Doctrine_Query::create()
          ->from("Permits a")
          ->where($stages_query);
        $permit_templates = $q->execute();
      }
      $permits = array();

      foreach($permit_templates as $permit)
      {
          $permits[] = "a.template_id = ".$permit->getId();
      }

      if(sizeof($permits) > 0)
      {
        $permits_query = implode(" OR ", $permits);
        
        $q = Doctrine_Query::create()
          ->from("PenaltyTemplate a")
          ->where($permits_query);
        $this->templates = $q->execute();
      }
      else 
      {
        $q = Doctrine_Query::create()
          ->from("PenaltyTemplate a");
        $this->templates = $q->execute();
      }

      $this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new PenaltyTemplateForm();

    $this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new PenaltyTemplateForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($penalties = Doctrine_Core::getTable('PenaltyTemplate')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new PenaltyTemplateForm($penalties);

    $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($penalties = Doctrine_Core::getTable('PenaltyTemplate')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    $this->form = new PenaltyTemplateForm($penalties);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $this->forward404Unless($penalties = Doctrine_Core::getTable('PenaltyTemplate')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    $penalties->delete();

    $this->redirect('/plan/penalties/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $penalties = $form->save();
      $this->redirect('/plan/penalties/index');
    }
  }
}
