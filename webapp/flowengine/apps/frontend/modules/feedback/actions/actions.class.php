<?php
/**
 * Feedback actions.
 *
 * Displays feedback content for clients
 *
 * @package    frontend
 * @subpackage none
 * @author     OTB Africa / Boniface Irungu (boniface@otbafica.com)
 */
class feedbackActions extends sfActions
{
          
  /**
	 * Executes 'Feedback' action
	 *
	 * Displays feedback form for clients
	 *
	 * @param sfRequest $request A request object
	 */
	  public function executeIndex(sfWebRequest $request)
	  {
		  $this->notifier = new notifications($this->getMailer());
		  $this->formid = 44495; //improve on this, its automatically created at time of database creation
		  $this->setLayout("layoutmentordash");
	  }
}
