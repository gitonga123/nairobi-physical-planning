<?php 

class generatePenaltyTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "generate-penalty";
        $this->briefDescription    = 'Check for expired permits and penalties for one application';
 
        $this->detailedDescription = <<<EOF
The [permitflow:generate-penalty|INFO] task checks for expired permits and generates any penalties that may have been configured
if there have been no renewals. But this one only checks one application
 
  [./symfony permitflow:generate-penalties|INFO]

  Application number to check [--filter|COMMENT] = application_no.
EOF;

        $this->addArgument('filter', sfCommandArgument::REQUIRED, 'Application number to check', null);
        $this->addArgument('template', sfCommandArgument::REQUIRED, 'Permit template id', null);
        $this->addArgument('clear', sfCommandArgument::OPTIONAL, 'Clear all penalties', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking for expired permits and penalties for one application');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $invoice_manager = new InvoiceManager();

        $success = 0;
        $failed = 0;

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.application_id = ?", $arguments['filter']);
        $application = $q->fetchOne();

        if($arguments['clear'] == "true")
        {
            $this->logSection('permitflow', 'Clearing penalties first...');

            $expired_permit = null;

            $q = Doctrine_Query::create()
                ->from("SavedPermit a")
                ->leftJoin("a.FormEntry b")
                ->leftJoin("b.SavedPermit c")
                ->where("a.type_id = ?", $arguments['template'])
                ->andWhere("b.id = ?", $application->getId())
                ->andWhere("a.expiry_trigger = 1")
                ->andWhere("c.expiry_trigger <> 0");

            if($q->count() > 0)
            {
                $expired_permit = $q->fetchOne();

                $q = Doctrine_Query::create()
                    ->from("Penalty a")
                    ->where("a.permit_id = ?", $expired_permit->getId())
                    ->andWhere("a.paid = 0");
                $penalties = $q->execute();

                foreach($penalties as $penalty)
                {
                    $q = Doctrine_Query::create()
                       ->from("MfInvoice a")
                       ->where("a.id = ?", $penalty->getInvoiceId());
                    $invoices = $q->execute();

                    foreach($invoices as $invoice)
                    {
                        $this->logSection('permitflow', 'Clearing penalties invoice...');
                        $invoice->delete();
                    }

                    $penalty->delete();
                }
                
            }
        }

        if($application)
        {
            $expired_permit = null;

            $q = Doctrine_Query::create()
                ->from("SavedPermit a")
                ->leftJoin("a.FormEntry b")
                ->leftJoin("b.SavedPermit c")
                ->where("a.type_id = ?", $arguments['template'])
                ->andWhere("b.id = ?", $application->getId())
                ->andWhere("a.expiry_trigger = 1")
                ->andWhere("c.expiry_trigger <> 0");

            if($q->count() == 0)
            {
                $expired_permit = new SavedPermit();
                $expired_permit->setTypeId($arguments['template']);
                $expired_permit->setApplicationId($application->getId());
                $expired_permit->setExpiryTrigger(1);
                $expired_permit->setDateOfExpiry($application->getDateOfSubmission());
                $expired_permit->setDateOfIssue($application->getDateOfSubmission());
                $expired_permit->save();
            }
            else 
            {
                $expired_permit = $q->fetchOne();
            }

            //Check for penalties
            $days_since_expiry = Functions::get_days_since(Date("Y-m-d"), $expired_permit->getDateOfExpiry());
            $months_since_expiry = Functions::get_months_since(Date("Y-m-d"), $expired_permit->getDateOfExpiry());

            //List of penalties that apply to current permit
            $q = Doctrine_Query::create()
                ->from("PenaltyTemplate a")
                ->where("a.template_id = ?", $arguments['template'])
                ->andWhere("(a.trigger_period <= ".$days_since_expiry." AND a.trigger_type = 1) OR (a.trigger_period <= ".$months_since_expiry." AND a.trigger_type = 2)");
            $penalties = $q->execute();

            //Check if each penalty was already applied, if no, then generate penalty invoice
            foreach($penalties as $penalty)
            {
                $this->logSection('permitflow', 'Checking if penalty '.$penalty->getId().' applies to permit '.$expired_permit->getId());

                $q = Doctrine_Query::create()
                    ->from("Penalty a")
                    ->where("a.template_id = ?", $penalty->getId())
                    ->andWhere("a.permit_id = ?", $expired_permit->getId());
                if($q->count() == 0)
                {
                    try 
                    {
                        $this->logSection('permitflow', 'YES! Applying penalty '.$penalty->getId().' to '.$expired_permit->getId());

                        //4. Generate penalty or update existing penalty invoice with new penalty
                        $invoice_manager->apply_penalty($application->getId(), $expired_permit->getId(), $penalty->getId());
                        $success++;
                    }catch(Exception $ex)
                    {
                        $this->logSection('permitflow', 'Penalty error on Permit - '.$expired_permit->getId().': '.$ex);
                        $failed++;
                    }
                }
                else 
                {
                    $this->logSection('permitflow', 'NOPE!');
                }
            }
        }
        else
        {
            $this->logSection('permitflow', 'No application found matching: '.$arguments['filter']);
        }
        
    }
}