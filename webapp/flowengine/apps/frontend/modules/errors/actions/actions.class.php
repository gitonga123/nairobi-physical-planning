<?php

/**
 * errors actions.
 *
 * @package    permit
 * @subpackage errors
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class errorsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeNotallowed(sfWebRequest $request)
  {
     $this->setLayout(false);
  }
  
  public function executeNotfound(sfWebRequest $request)
  {
     $this->setLayout(false);
  }
}
