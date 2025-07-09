<?php

class SubcountyActions extends sfActions
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
            ->from('Subcounty s')
            ->orderBy('s.id ASC');
        $this->subcounties = $q->execute();

        $this->setLayout("layout-settings");
    }

}