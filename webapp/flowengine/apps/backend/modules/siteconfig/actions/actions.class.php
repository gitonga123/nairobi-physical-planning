<?php
/**
 * Settings actions.
 *
 * System settings dashboard with links to other settings
 *
 * @package    backend
 * @subpackage settings
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class siteconfigActions extends sfActions
{
   /**
   * Executes 'Index' action
   *
   * Displays system settings dashboard with links to various settings
   *
   * @param sfRequest $request A request object
   */
    public function executeIndex(sfWebRequest $request)
    {
        $this->success = "";

        if(!$this->getUser()->mfHasCredential('access_settings'))
        {
            $this->redirect('/plan/errors/notallowed');
        }

        $this->forward404Unless($siteconfig = Doctrine_Core::getTable('ApSettings')->find(array(1)), sprintf('Object ap_setting does not exist (%s).', 1));

        $this->form = new ApSettingsForm($siteconfig);

        $this->setLayout("layout-settings");
    }

    public function executeUpdate(sfWebRequest $request)
    {
      $this->forward404Unless($siteconfig = Doctrine_Core::getTable('ApSettings')->find(array(1)), sprintf('Object ap_setting does not exist (%s).', 1));

      $this->form = new ApSettingsForm($siteconfig);

      if($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT))
      {
          $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
          if ($this->form->isValid())
          {
             $siteconfig = $this->form->save();

             $siteconfig->setFirstRun(0);
             $siteconfig->save();

             $this->redirect("/plan/siteconfig/index");
          }
          else
          {
             $this->success = false;
             $this->redirect("/plan/siteconfig/index");
          }
      }
    }
}
