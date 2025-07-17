<?php

/**
 * Permits actions.
 *
 * Displays all permits issued to currently logged in client
 *
 * @package    frontend
 * @subpackage sharedapplication
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class permitsActions extends sfActions
{
  /**
   * Executes 'Index' action
   *
   * Displays list of all of the currently logged in client's permits
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('SavedPermit a')
      ->leftJoin('a.FormEntry b')
      ->where('b.user_id = ?', $this->getUser()->getGuardUser()->getId())
      ->andWhere('a.permit_status <> 3')
      ->orderBy('a.id DESC');

    $this->pager = new sfDoctrinePager('SavedPermit', 50);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();
    $this->getResponse()->setTitle( Functions::site_settings()->getOrganisationName()."| Permits");

    $this->setLayout("layoutmentordash");
  }

  /**
   * Executes 'Print' action
   *
   * Displays permit (PDF)
   *
   * @param sfRequest $request A request object
   */
  public function executePrint(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('SavedPermit a')
      ->leftJoin('a.FormEntry b')
      ->where('a.id = ?', $request->getParameter("id"));
    $permit = $q->fetchOne();

    if ($permit) {
      $permit_manager = new PermitManager();
      $permit_manager->save_to_pdf($permit->getId());
    }

    exit;
  }

  /**
   * Executes 'View' action
   *
   * Displays permit (Non-PDF)
   *
   * @param sfRequest $request A request object
   */
  public function executeView(sfWebRequest $request)
  {

    $q = Doctrine_Query::create()
      ->from('SavedPermit a')
      ->leftJoin('a.FormEntry b')
      ->where('a.id = ?', $request->getParameter("id"));
    $this->permit = $q->fetchOne();


    if ($this->permit->getFormEntry()->getApproved() == 0) {
      $application_id = $this->permit->getFormEntry()->getId();

      $this->permit->delete();
      $this->redirect("/plan/application/view/id/" . $application_id);
    }

    $this->application = $this->permit->getFormEntry();

    $this->done = $request->getParameter("done", 0);


    $this->getResponse()->setTitle( Functions::site_settings()->getOrganisationName()."| Permit - ".$this->permit->getPermitId());

    $this->setLayout("layoutmentordash");
  }



  /**
   * Executes 'Attach' action
   *
   * Displays permit (Non-PDF)
   *
   * @param sfRequest $request A request object
   */
  public function executeCreate(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()
      ->from('SavedPermit a')
      ->leftJoin('a.FormEntry b')
      ->where('a.id = ?', $request->getParameter("id"));
    $this->permit = $q->fetchOne();

    $this->form = new SignedPermitForm();

    $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
    if ($this->form->isValid()) {
      $file = $this->form->getValue('permit');
      $filename = 'attachment_' . sha1(md5(date("Y-m-d H:i:s")) . $file->getOriginalName());
      $extension = $file->getExtension($file->getOriginalExtension());
      $file->save($filename . $extension);

      $this->permit->setDocumentKey("asset_signed/" . $filename . $extension);
      $this->permit->save();
      $this->redirect("/plan/permits/view/id/" . $this->permit->getId());
    } else {
      $this->forward('permits', 'attach');
    }

    $this->setLayout("layoutdash");
  }

  /**
   * Executes 'Attach' action
   *
   * Displays permit (Non-PDF)
   *
   * @param sfRequest $request A request object
   */
  public function executeAttach(sfWebRequest $request)
  {

    $q = Doctrine_Query::create()
      ->from('SavedPermit a')
      ->leftJoin('a.FormEntry b')
      ->where('a.id = ?', $request->getParameter("id"));
    $this->permit = $q->fetchOne();

    $this->form = new SignedPermitForm();

    $this->application = $this->permit->getFormEntry();

    $this->setLayout("layoutdash");
  }

  /**
   *
   * Execute 'Openrequest' action
   *
   * Allows external systems to request for a permit without having an account
   *
   **/
  public function executeOpenrequest(sfWebRequest $request)
  {
    $permit_manager = new PermitManager();

    $reference = $request->getParameter("reference");

    if ($request->getParameter("typeid")) {
      //Check if specific permit template is allowed to be accessed publicly
      if ($permit_manager->has_public_permissions($request->getParameter("typeid"))) {
        if ($_GET['print']) {
          $permit_manager->generate_public_pdf($request->getParameter("typeid"), $reference);
        } else {
          $this->template = $permit_manager->generate_public_html($request->getParameter("typeid"), $reference);
        }
      } else {
        error_log("Debug-t: " . $request->getParameter('typeid') . " not found in public_permits in settings.yml");
        echo "Unauthorized - No permissions for specified template";
      }
    } else {
      //If no template is explicitly referenced then assume there is a default template in the settings
      $template_id = $permit_manager->get_public_template();

      if ($template_id) {
        if ($_GET['print']) {
          $permit_manager->generate_public_pdf($template_id, $reference);
        } else {
          $this->template = $permit_manager->generate_public_html($template_id, $reference);
        }
      } else {
        error_log("Debug-t: No config for public_permits in settings.yml");
        echo "<h3Unauthorized - Missing permissions for adhoc access";
      }
    }

    if ($_GET['print']) {
      $this->setLayout(false);
      exit;
    }
  }
  function executeDownloadsignedpermit(sfRequest $request)
  {
    $permit_id = $request->getParameter('id');
    $permit = Doctrine_Query::create()->from("SavedPermit a")->where('a.id = ?', $permit_id)->fetchOne();
    $file_name = (new PermitManager())->permit_file_name($permit);
    $file_name = "app/permits/signed/$file_name";

    if (file_exists($file_name)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file_name));
      readfile($file_name);
      exit();
    } else
      return $this->redirect('/plan/permits/view/id/' . $permit_id);
  }
}
