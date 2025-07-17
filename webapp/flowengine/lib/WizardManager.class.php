<?php
/**
 *
 * Wizard manager classes determines whether there is a need to display the wizard to the systems administrator based
 *  on the current system's configuration e.g. first run after installation
 *
 * Created by PhpStorm.
 * User: Thomas
 * Date: 31/12/2014
 * Time: 00:20
 */

class WizardManager {

    //Public constructor for the wizard manager class
    public function __construct()
    {

    }

    //Determines whether the system is not configured and there is need to display wizard pages
    public function is_first_run()
    {
        $settings = Functions::site_settings();

        return $settings->getFirstRun();
    }

    //Determines whether the system's security settings have been configured
    public function is_security_configured()
    {
        $q = Doctrine_Query::create()
        ->from('MfGuardGroup a');
        $groups = $q->count();

        $q = Doctrine_Query::create()
            ->from('MfGuardGroupPermission a');
        $permissions = $q->count();

        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.bdeleted = 0')
            ->andWhere('a.nid <> 1');
        $reviewers = $q->count();

        if($reviewers && $permissions && $groups)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //Determines whether the system's workflow settings have been configured
    public function is_workflow_configured()
    {
        $q = Doctrine_Query::create()
            ->from('Department a');
        $departments = $q->count();

        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.strdepartment <> ?', '')
            ->andWhere('a.bdeleted = 0')
            ->andWhere('a.nid <> 1');
        $assigned_reviewers = $q->count();

        $q = Doctrine_Query::create()
            ->from('Menus a');
        $workflows = $q->count();

        $q = Doctrine_Query::create()
            ->from('SubMenus a');
        $stages = $q->count();

        $q = Doctrine_Query::create()
            ->from('SubMenuButtons a');
        $actions = $q->count();

        if($departments && $assigned_reviewers && ($workflows && $stages) && $actions)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //Determines whether the system's inputs and outputs settings have been configured
    public function is_service_configured()
    {
        $q = Doctrine_Query::create()
            ->from('ApForms a')
            ->andWhere('a.form_active = 1 AND a.form_type = 1');
        $forms = $q->count();

        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a');
        $invoices = $q->count();

        $q = Doctrine_Query::create()
            ->from('Permits a');
        $permits = $q->count();
		

        if($permits && $invoices && $forms)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //Get the current step the wizard should resume from
    public function postsetup_resume_step()
    {
      if(sfContext::getInstance()->getUser()->getAttribute('post_resume') >= 1)
      {
        return sfContext::getInstance()->getUser()->getAttribute('post_resume');
      }
      else
      {
        return 1;
      }
    }

    //Get the current step the wizard should resume from
    public function resume_step()
    {
        if(!$this->is_security_configured())
        {
            $q = Doctrine_Query::create()
                ->from('MfGuardGroup a');
            $groups = $q->count();

            if($groups == 0)
            {
                return 0;
            }

            $q = Doctrine_Query::create()
                ->from('MfGuardGroupPermission a');
            $permissions = $q->count();

            if($permissions == 0)
            {
                return 1;
            }

            $q = Doctrine_Query::create()
                ->from('CfUser a')
                ->where('a.bdeleted = 0')
                ->andWhere('a.nid <> 1');
            $reviewers = $q->count();

            if($reviewers == 0)
            {
                return 2;
            }
        }
        elseif(!$this->is_workflow_configured())
        {
            $q = Doctrine_Query::create()
                ->from('Department a');
            $departments = $q->count();

            if($departments == 0)
            {
                return 3;
            }

            $q = Doctrine_Query::create()
                ->from('CfUser a')
                ->where('a.strdepartment <> ?', '')
                ->andWhere('a.bdeleted = 0')
                ->andWhere('a.nid <> 1');
            $assigned_reviewers = $q->count();

            if($assigned_reviewers == 0)
            {
                return 4;
            }

            $q = Doctrine_Query::create()
                ->from('Menus a');
            $workflows = $q->count();

            $q = Doctrine_Query::create()
                ->from('SubMenus a');
            $stages = $q->count();

            if($workflows == 0 || $stages == 0)
            {
                return 5;
            }

            $q = Doctrine_Query::create()
                ->from('SubMenuButtons a');
            $actions = $q->count();

            if($actions == 0)
            {
                return 6;
            }
        }
        elseif(!$this->is_service_configured())
        {
            $q = Doctrine_Query::create()
                ->from('ApForms a')
                ->andWhere('a.form_active = 1 AND a.form_type = 1');
            $forms = $q->count();

            if($forms == 0)
            {
                return 7;
            }

            $q = Doctrine_Query::create()
                ->from('Invoicetemplates a');
            $invoices = $q->count();

            if($invoices == 0)
            {
                return 8;
            }

            $q = Doctrine_Query::create()
                ->from('Permits a');
            $permits = $q->count();

            if($permits == 0)
            {
                return 9;
            }
        }
        elseif(!$this->is_other_configured())
        {
			$q = Doctrine_Query::create()
				->from('Currencies c');
			$currencies = $q->count();

            if($currencies == 0)
            {
                return 10;
            }

			$q = Doctrine_Query::create()
				->from('Merchant m');
			$merchants = $q->count();

            if($merchants == 0)
            {
                return 11;
            }

			$q = Doctrine_Query::create()
				->from('FeeCategory f');
			$fee_categories = $q->count();

            if($fee_categories == 0)
            {
                return 12;
            }
			
			$q = Doctrine_Query::create()
				->from('Fee f');
			$fees = $q->count();
			
            if($fees == 0)
            {
                return 13;
            }
			
			$q = Doctrine_Query::create()
				->from('Agency f');
			$agencies = $q->count();
			
            if($agencies == 0)
            {
                return 14;
            }
        }

        return 0;
    }
	//OTB ADD 
    public function is_other_configured()
    {

        $q = Doctrine_Query::create()
            ->from('Currencies c');
        $currencies = $q->count();

        $q = Doctrine_Query::create()
            ->from('Merchant m');
        $merchants = $q->count();
		
        $q = Doctrine_Query::create()
            ->from('FeeCategory f');
        $fee_categories = $q->count();
		
        $q = Doctrine_Query::create()
            ->from('Fee f');
        $fees = $q->count();
		
        $q = Doctrine_Query::create()
            ->from('Agency a');
        $agencies = $q->count();

        if($currencies && $merchants && $fee_categories && $fees && $agencies)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
