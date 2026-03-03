<?php
/**
 * Frusers actions.
 *
 * Client Management Service
 *
* @package    backend
 * @subpackage feedback
 * @author     OTB Africa / Boniface Irungu (boniface@otbafrica.com)
 */
class feedbackActions extends sfActions
{
  
  
    /**
     * Executes 'Index' action
     *
     * Displays list of all registered clients
     *
     * @param sfRequest $request A request object
     */
          
    public function executeIndex(sfWebRequest $request)
    {
        $query = "SELECT * FROM ap_form_44495 ORDER BY date_created DESC LIMIT 15 " ;
        //
        $this->query_res = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
        $this->setLayout('layout');
    }
      
          /**
     * Executes 'View' action
     *
     * Displays full user message
     *
     * @param sfRequest $request A request object
     */
    public function executeView(sfWebRequest $request)
    {
        //Assumption table ap_form_7 is our feedback table
        $query = "SELECT * FROM ap_form_44495 WHERE id = ".$request->getParameter('id');
       //not the best option...but it works !!!
        $this->message = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
       //then update message status to 2 meaning read
       //0 = not read
        $query_update = "UPDATE ap_form_44495 SET status = 2 WHERE id = ".$request->getParameter('id');
       //not the best option...but it works !!!
         $conn =  Doctrine_Manager::getInstance()->getCurrentConnection();
       //
         $prepare = $conn->prepare($query_update);
         $prepare->execute();
       
         $this->setLayout('layout');
    }
}
