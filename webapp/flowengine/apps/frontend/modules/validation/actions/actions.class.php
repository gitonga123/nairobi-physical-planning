<?php
/**
 * Validation actions.
 *
 * Handles some form validation on the registration form
 *
 * @package    frontend
 * @subpackage validation
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class validationActions extends sfActions
{
         /**
	 * Executes 'Checkusername' action
	 *
	 * Validates whether an account with a similar username exists
	 *
	 * @param sfRequest $request A request object
	 */
          public function executeCheckusername(sfWebRequest $request)
          {
	        $this->setLayout(false);
                $this->username = $request->getPostParameter("username"); 
          }
  
        /**
	 * Executes 'Checkemail' action
	 *
	 * Validates whether an account with a similar email exists
	 *
	 * @param sfRequest $request A request object
	 */
          public function executeCheckemail(sfWebRequest $request)
          {
	        $this->setLayout(false);
                $this->email = $request->getPostParameter("email"); 
          }
}
