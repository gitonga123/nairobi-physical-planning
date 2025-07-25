<?php

/**
 * gis actions.
 *
 * @package    gis
 * @subpackage gis
 * @author     Boniface Irungu
 * @version    SVN: $Id: actions.class.php 23810 2024-11-12 11:07:44Z Kris.Wallsmith $
 * 
 */
class GisActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $otb_helper = new OTBHelper();
        $this->application_types = $otb_helper->getPermitTypes($this->getUser()->getAttribute('userid'));

        $this->setLayout('layout');
    }
}