<?php

class WardActions extends sfActions
{
    /**
     * Executes 'index' function
     *
     * Display a list of existing objects
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {

        //Get list of all objects
        $q = Doctrine_Query::create()
            ->from('Ward w')
            ->orderBy('w.id ASC');
        $this->wards = $q->execute();

        $this->setLayout("layout-settings");
    }

    public function executeUpdatewards(sfWebRequest $request)
    {
       
        return $this->renderText(json_encode(array('status' => 'success')));
    }
}