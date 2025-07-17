<?php

/**
 * invoicetemplates actions.
 *
 * @package    backend
 * @subpackage invoicetemplates
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class invoicetemplatesActions extends sfActions
{

  public function executeIndex(sfWebRequest $request)
  {
      //Audit 
      Audit::audit("", "Accessed invoice template settings");

      $wizard_manager = new WizardManager();

      if($wizard_manager->is_first_run())
      {
        $this->redirect("/plan/dashboard");
      }
      
      if($request->getParameter("filter"))
      {
        //Save filter to session
        $this->getUser()->setAttribute('filter', $request->getParameter("filter"));

        $q = Doctrine_Query::create()
          ->from("Menus a")
          ->where("a.id = ?", $request->getParameter("filter", $this->getUser()->getAttribute("filter")));
        $service = $q->fetchOne();

        if($service)
        {
          $this->getUser()->setAttribute('service_type', $service->getServiceType());
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

        if($stages_query == "")
        {
          $q = Doctrine_Query::create()
            ->from("Invoicetemplates a");
          $this->templates = $q->execute();
        }
        else 
        {
          $q = Doctrine_Query::create()
            ->from("Invoicetemplates a")
            ->where($stages_query);
          $this->templates = $q->execute();
        }
      }
      else 
      {
        $q = Doctrine_Query::create()
          ->from("SubMenus a")
          ->where("a.menu_id = ?", $this->getUser()->getAttribute('filter'));
        $stages = $q->execute();

        $stages_array = array();

        foreach($stages as $stage)
        {
            $stages_array[] = "a.applicationstage = ".$stage->getId();
        }

        $stages_query = implode(" OR ", $stages_array);

        if($stages_query == "")
        {
          $q = Doctrine_Query::create()
            ->from("Invoicetemplates a");
          $this->templates = $q->execute();
        }
        else 
        {
          $q = Doctrine_Query::create()
            ->from("Invoicetemplates a")
            ->where($stages_query);
          $this->templates = $q->execute();
        }
      }

      $this->setLayout("layout-settings");
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new InvoicetemplatesForm();

    $this->setLayout("layout-settings");
  }

  public function executeCreate(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Created new invoice template");

    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new InvoicetemplatesForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($invoicetemplates = Doctrine_Core::getTable('invoicetemplates')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    
    $this->form = new InvoicetemplatesForm($invoicetemplates);

    $this->setLayout("layout-settings");
  }

  public function executeUpdate(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Updated invoice template");

    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($invoicetemplates = Doctrine_Core::getTable('invoicetemplates')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    $this->form = new InvoicetemplatesForm($invoicetemplates);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Deleted invoice template");

    $this->forward404Unless($invoicetemplates = Doctrine_Core::getTable('invoicetemplates')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
    $invoicetemplates->delete();

    $this->redirect('/plan/invoicetemplates/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $invoicetemplates = $form->save();
    }
    
    $this->redirect('/plan/invoicetemplates/index');
  }
  public function executeList(sfWebRequest $request)
  {
      //Audit 
      Audit::audit("", "Accessed invoice template settings");

      $wizard_manager = new WizardManager();

      if($wizard_manager->is_first_run())
      {
        $this->redirect("/plan/dashboard");
      }
      
      $q = Doctrine_Query::create()
        ->from("Invoicetemplates a");
      $this->templates = $q->execute();

      $this->setLayout("layout-settings");
  }
}
