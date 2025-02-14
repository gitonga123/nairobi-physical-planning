<?php
class agencyActions extends sfActions
{

  public function executeIndex(sfWebRequest $request)
  {
    $wizard_manager = new WizardManager();

    if ($wizard_manager->is_first_run()) {
      $this->redirect("/backend.php/dashboard");
    }
	$q = Doctrine_Core::getTable('agency')
      ->createQuery('a');

	if($request->getParameter("filter"))
	{
		  $this->filter = $request->getParameter("filter");
		  //Get agency workflows
			$workflow_agencies = Doctrine_Query::create()
				 ->from('AgencyMenu a')
				 ->where('a.menu_id = ?',  $this->filter)
			     ->execute();
			$workflow_agency_ids = array(0);
			foreach($workflow_agencies as $workflow_agency){
					array_push($workflow_agency_ids, $workflow_agency->getAgencyId());
			}
			$q->whereIn('a.id', $workflow_agency_ids);
	}

    $this->agencies = $q->execute();

      $this->setLayout("layout-settings");
  }

  public function executeListforservice(sfWebRequest $request)
  {
	$this->menu_filter = $request->getParameter("filter");
	$q = Doctrine_Query::create()
		 ->from('Agency a')
		 ->orderBy('a.name ASC');
	$this->agencies = $q->execute();

	$q = Doctrine_Query::create()
		 ->from('Menus a')
		 ->where('a.id = ?', $this->menu_filter)
		 ->orderBy('a.title ASC');
	$this->service = $q->fetchOne();

      $this->setLayout("layout-settings");
  }
	public function executeUpdateagency(sfWebRequest $request)
	{
	   $menuid = $request->getPostParameter('menuid');
	   $q = Doctrine_Query::create()
		  ->from("Menus a")
		->where("a.id = ?", $menuid);
	   $service = $q->fetchOne();

	   if($service)
	   {
			$q = Doctrine_Query::Create()
			   ->from('AgencyMenu a')
			   ->where('a.menu_id = ?', $service->getId());
			$service_agencies = $q->execute();
			if($service_agencies)
			{
			   foreach($service_agencies as $service_agency)
			   {
				  $service_agency->delete();
			   }
			}

		   if($_POST['agencies'])
		   {

			   $agencies = $_POST['agencies'];
			   foreach($agencies as $agency)
			   {
				   $service_agency = new AgencyMenu();
				   $service_agency->setMenuId($service->getId());
				   $service_agency->setAgencyId($agency);
				   $service_agency->save();
			   }
		   }
		  echo "STATUS: SUCCESS";
	   }
	   else
	   {
		  echo "STATUS: FAILED";
	   }
	   exit;
	}

  public function executeNew(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
    $this->form = new AgencyForm();
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new AgencyForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->setLayout("layout-settings");
    $this->forward404Unless($agency = Doctrine_Core::getTable('agency')->find(array($request->getParameter('id'))), sprintf('Object agency does not exist (%s).', $request->getParameter('id')));
    $this->form = new AgencyForm($agency);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($agency = Doctrine_Core::getTable('agency')->find(array($request->getParameter('id'))), sprintf('Object agency does not exist (%s).', $request->getParameter('id')));
    $this->form = new AgencyForm($agency);

    $this->processForm($request, $this->form);

    $this->redirect('/backend.php/agency/index');
  }

  public function executeDelete(sfWebRequest $request)
  {

    $this->forward404Unless($agency = Doctrine_Core::getTable('agency')->find(array($request->getParameter('id'))), sprintf('Object agency does not exist (%s).', $request->getParameter('id')));
    $agency->delete();

    $this->redirect('/backend.php/agency/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
	  
       $agency = $form->save();
      $wizard_manager = new WizardManager();

      if($wizard_manager->is_first_run())
      {
        $site_settings = Functions::site_settings();
        $site_settings->setFirstRun(0);
        $site_settings->save();

        $this->redirect('/backend.php/logout');
      }

      $this->redirect('/backend.php/agency/index');
    }
  }
}
