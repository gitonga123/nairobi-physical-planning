<?php 

class expiredPermitsTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "check-expired-permits";
        $this->briefDescription    = 'Check for expired permits';
 
        $this->detailedDescription = <<<EOF
The [permitflow:check-expired-permits|INFO] task checks for expired permits and execute any triggers that 
have been configured.
 
  [./symfony permitflow:check-expired-permits|INFO]

  Specify the service to which to check for expired permits [--service|COMMENT] argument.
EOF;

        $this->addArgument('service', sfCommandArgument::REQUIRED, 'Service in which to check for expired permits', null);
		$this->addArgument('appid', sfCommandArgument::OPTIONAL, 'application id to check', null);
		$this->addArgument('cyclic_service_config', sfCommandArgument::OPTIONAL, 'service configurations to use for cyclic invoices', null);    
	}

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for expired permits');
        
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $invoice_manager = new InvoiceManager();

        $success = 0;
        $failed = 0;

        //Only search permits that have triggers on expiry
        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.expiry_trigger <> ? and a.applicationform = ?", array(0,$arguments['service']));
        $permits = $q->execute();

        $trigger_templates = array();
        $trigger_types = array();

        foreach($permits as $permit)
        {
            $trigger_templates[] = $permit->getId();
            $trigger_types[$permit->getId()] = $permit->getExpiryTrigger();
        }
        $q = Doctrine_Query::create()
        ->from("SavedPermit a")
        ->where("a.date_of_expiry <= ?", date("Y-m-d H:m:s"))
        ->andWhere("a.type_id = 0".implode(" OR a.type_id = ", $trigger_templates));
		//OTB Patch - If a specific application has been run with this task, then filter for that application only
		  if ($arguments['appid']){
		   $q->andWhere("a.application_id = ?",$arguments['appid']);
		  }
		  		  
        $q->andWhere("a.expiry_trigger = 0");

        $expired_permits = $q->execute();

        foreach($expired_permits as $expired_permit)
        {
            $expired_permit->setExpiryTrigger(1);
            $expired_permit->save();
            
            try
            {
                $application = $expired_permit->getFormEntry();

                if($trigger_types[$expired_permit->getTypeId()] == 1) //New Invoice
                {
					//OTB ADD - CHECK IF service id is set
                    if($application->getBusinessId() && $application->getServiceId())
                    {
                        error_log("Permit-Expiry: Cyclic: ".$application->getId());
                        $invoice_manager->generate_cyclic_invoices($application->getId(), $application->getServiceId(),false);
                    }
                    else 
                    {
                        error_log("Permit-Expiry: Normal Renewal: ".$application->getId());
                        //$invoice_manager->generate_renewal_invoice($expired_permit->getApplicationId());
						//OTB Patch - if specific cyclic configurations have been set by user run, then apply cyclic invoices using thos service configs
						  if ($arguments['cyclic_service_config']){
						   $invoice_manager->generate_cyclic_invoices($application->getId(), $arguments['cyclic_service_config'],false);  
						  }else{
							//OTB ADD CHECK IF SERVICE IS SET FOR THE FORM
							$q=Doctrine_Query::create()
								->from('Menus m')
								->where('m.service_form = ? and m.service_type = ?',array($application->getFormId(),2));
							$service=$q->fetchOne();
							if($service && $service->getServiceFeeField()){
								//EXECUTE Cyclic
							   $invoice_manager->generate_cyclic_invoices($application->getId(), $service->getId(),false);  
							}else{
								//OTB -- since county is doing data entry
							   //$invoice_manager->generate_renewal_invoice($expired_permit->getApplicationId());
							}
						  }						
						
						
						
                    }
                }
                elseif($trigger_types[$expired_permit->getTypeId()] == 2) //Move
                {
                    $q = Doctrine_Query::create()
                        ->from('SubMenus a')
                        ->where('a.id = ?', $application->getApproved());
                    $stage = $q->fetchOne();

                    if($stage)
                    {
                        if($stage->getStageExpiredMovement())
                        {
                            //Move application to another stage
                            $application->setApproved($stage->getStageExpiredMovement());
                            $application->save();
                        }
                    }
                }
                elseif($trigger_types[$expired_permit->getTypeId()] == 3) //Move and New Invoice
                {
                    if($application->getBusinessId() && $application->getServiceId())
                    {
                        error_log("Permit-Expiry: Cyclic: ".$application->getId());
                        $invoice_manager->generate_cyclic_invoices($application->getId(), $application->getServiceId(),false);
                    }
                    else 
                    {
                        error_log("Permit-Expiry: Normal Renewal: ".$application->getId());
                        //$invoice_manager->generate_renewal_invoice($expired_permit->getApplicationId());
						//OTB Patch - if specific cyclic configurations have been set by user run, then apply cyclic invoices using thos service configs
						  if ($arguments['cyclic_service_config']){
						   $invoice_manager->generate_cyclic_invoices($application->getId(), $arguments['cyclic_service_config'],false);  
						  }else{
							//OTB ADD CHECK IF SERVICE IS SET FOR THE FORM
							$q=Doctrine_Query::create()
								->from('Menus m')
								->where('m.service_form = ? and m.service_type = ?',array($application->getFormId(),2));
							$service=$q->fetchOne();
							if($service && $service->getServiceFeeField()){
								//EXECUTE Cyclic
							   $invoice_manager->generate_cyclic_invoices($application->getId(), $service->getId(),false);  
							}else{
								//OTB -- since county is doing data entry
							   //$invoice_manager->generate_renewal_invoice($expired_permit->getApplicationId());
							}
						  }						
						
						
                    }
					
                    $q = Doctrine_Query::create()
                        ->from('SubMenus a')
                        ->where('a.id = ?', $application->getApproved());
                    $stage = $q->fetchOne();

                    if($stage)
                    {
                        if($stage->getStageExpiredMovement())
                        {
                            //Move application to another stage
							error_log('--------Stage to be moved----'.$stage->getStageExpiredMovement());
                            $application->setApproved($stage->getStageExpiredMovement());
                            $application->save();
                        }
                    }

                }

                $success++;
            }catch(Exception $ex)
            {
                $failed++;
                $this->logSection('permitflow', 'Check expired permits failed on ID: '.$expired_permit->getId().' due to '.$ex);
            }
        }

        $this->logSection('permitflow', 'Completed check-expired-permits task with '.$success.' successful and '.$failed.' failed.');
    }
}