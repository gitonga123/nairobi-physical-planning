<?php

/**
 * settings actions.
 *
 * @package    permitflow
 * @subpackage settings
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
class settingsActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    //Audit 
    Audit::audit("", "Accessed system settings");

    $this->setLayout("layout-settings");
  }
}
