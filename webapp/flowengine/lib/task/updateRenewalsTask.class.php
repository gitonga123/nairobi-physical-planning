<?php 

class updateRenewalsTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "update-renewals";
        $this->briefDescription    = 'Create renewals permits a business profile if they already exist in the normal workflows';
 
        $this->detailedDescription = <<<EOF
The [permitflow:update-renewals|INFO] will automatically generate business renewal permits and cancel the invoices and penalties if the services
had already been renewed in a different workflow. This normally happens in a transition phase when moving from a workflow model to a business model.
 
  [./symfony permitflow:update-renewals|INFO]
 
Specify the business renewals service [--business_service|COMMENT] argument.
Specify the permit template id [--new_permit_template|COMMENT] argument.
Specify the existing renewals permit template id [--old_permit_template|COMMENT] argument
EOF;
 
        $this->addArgument('business_service', sfCommandArgument::REQUIRED, 'Specify the business renewals service', null);
        $this->addArgument('new_permit_template', sfCommandArgument::REQUIRED, 'Specify the permit template id', null);
        $this->addArgument('old_permit_template', sfCommandArgument::REQUIRED, 'Specify the existing renewals permit template id', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Fetching list of permits to update....');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $permits_manager = new PermitManager();

        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.type_id = ?", $arguments['old_permit_template'])
            ->andWhere('a.permit_status <> 3')
            ->andWhere('a.expiry_trigger = 0');

        $success = 0;
        $failed = 0;

        foreach($q->execute() as $permit)
        {
            try{
                $user_id = $permit->getFormEntry()->getSfGuardUser()->getId();

                $q_apps = Doctrine_Query::create()
                   ->from("FormEntry a")
                   ->where("a.service_id = ?", $arguments['business_service'])
                   ->andWhere("a.user_id = ?", $user_id)
                   ->andWhere("a.approved <> 0");

                foreach($q_apps->execute() as $application)
                {
                    //Check if there are any existing renewals in the new workflow
                    $q_permits = Doctrine_Query::create()
                        ->from("SavedPermit a")
                        ->leftJoin("a.FormEntry b")
                        ->where("a.type_id = ?", $arguments['new_permit_template'])
                        ->andWhere('a.permit_status <> 3')
                        ->andWhere('a.expiry_trigger = 0')
                        ->andWhere('b.service_id = ?', $arguments['business_service'])
                        ->andWhere("b.user_id = ?", $user_id);

                    if($q_permits->count() == 0)
                    {
                        $this->logSection('permitflow', 'Creating Renewal permit for Application ID: '.$application->getId());

                        //Generate the renewal permit
                        $permits_manager->create_permit_with_template($application->getId(), $arguments['new_permit_template']);

                        //Delete all pending invoices since the application was already renewed
                        $q_invoices = Doctrine_Query::create()
                            ->from("MfInvoice a")
                            ->where("a.paid  = ?", 0)
                            ->andWhere('a.app_id = ?', $application->getId());
                        
                        foreach($q_invoices->execute() as $invoice)
                        {
                            $invoice_details = $invoice->getMfInvoiceDetail();
                            foreach($invoice_details as $invoice_detail)
                            {
                                $invoice_detail->delete();
                            }
                            $invoice->delete();
                        }

                        //Move application to the next stage if invoice triggers existed
                        $q = Doctrine_Query::create()
                            ->from("SubMenus a")
                            ->where("a.id = ?", $application->getApproved());
                        $stage = $q->fetchOne();

                        if ($stage && $stage->getStageType() == 3) {
                            if($stage->getStageProperty() == 2)
                            {
                                //Move application to another stage
                                $next_stage = $stage->getStageTypeMovement();
                                $application->setApproved($next_stage);
                                $application->save();
                            }
                        }
                    }
                }

                $success++;
            }catch(Exception $ex)
            {
                $failed++;
                $this->logSection('permitflow', 'Renewal update failed on ID: '.$permit->getId().' due to '.$ex);
            }
        }

        $this->logSection('permitflow', 'Completed update-renewals task with '.$success.' successful and '.$failed.' failed.');
    }
}