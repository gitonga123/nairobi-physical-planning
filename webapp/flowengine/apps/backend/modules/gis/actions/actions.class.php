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
        $this->setLayout('layout');
    }
}