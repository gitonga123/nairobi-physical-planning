<?php 

class generatePenaltiesTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = "permitflow";
        $this->name = "generate-penalties";
        $this->briefDescription    = 'Add penalties';
 
        $this->detailedDescription = <<<EOF
The [permitflow:generate-penalties|INFO] task checks for pending invoices and add penalties fees.
 
  [./symfony permitflow:generate-penalties|INFO]

  Check for penalties based on date of response or date of issue [--filter|COMMENT] = form_id.
  Else if no filter then, Check for penalties based on pending invoice.
EOF;

        $this->addArgument('filter', sfCommandArgument::OPTIONAL, 'Check for penalties on application id', null);
    }

    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('permitflow', 'Checking application with pending invoices');
        
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase("doctrine")->getConnection();

        $invoice_manager = new InvoiceManager();
        $application_manager = new ApplicationManager();

        $success = 0;
        $failed = 0;
		

		//OTB Add
		$current_year=date('Y');
		$q=Doctrine_Query::create()
			->from('FormEntry f')
			->innerJoin('f.MfInvoice m')
			->where('m.paid = ? and f.approved <> ? and f.deleted_status =?', array(1,0,0));
		if($arguments['filter']){
			$q->andWhere('f.id = ?',$arguments['filter']);
		}
		$applications=$q->execute();
		//loop through applications with pending invoice
        foreach($applications as $application)
        {
            $this->logSection('permitflow', 'Checking application: '.$application->getId());
			//
			//Get submitted date
			$year_of_submission=date('Y',strtotime($application->getDateOfSubmission()));
			$year_of_response=0;
			if(strlen($application->getDateOfResponse())){
				$year_of_response=date('Y',strtotime($application->getDateOfResponse()));
			}
			//Add check for migrated data
			/*$migrated=$application_manager->check_if_migrated($application->getEntryId());
			if($migrated){
				//if migrated check date of response
				$migrated_form='';
				switch($application->getFormId()){
					case 939:
						$migrated_form=7283;
						break;
					case 7283:
						$migrated_form=939;
						break;
				}
				if($migrated_form){
					//get migrated app
					$q=Doctrine_Query::create()
						->from('FormEntry f')
						->where('f.form_id = ? and f.entry_id = ?', array($migrated_form,$application->getEntryId()));
					$migrated_app=$q->fetchOne();
					
					//if date of response > than the current use that one
					if(strlen($migrated_app->getDateOfResponse())){
						$migrated_response_year=date('Y',strtotime($migrated_app->getDateOfResponse()));
						if($migrated_response_year > $year_of_response){
							$year_of_response=$migrated_response_year;
						}
					}
				}
			}*/
			error_log('--------Year of response-----'.$year_of_response.'----------Year of submission-------'.$year_of_submission);
			//if year of response = current year don't issue
			if($year_of_response == 0 || $year_of_response != date('Y')){
				//issue penalties
				//get pending penalties issue by county or system
				$penalties_issued=0;
				//get penalties fee id
				$q=Doctrine_Query::create()
					->from('Fee f')
					->where('f.description like ?','Penalties');
				$fee=$q->fetchOne();
				
				foreach($application->getMfInvoice() as $invoice){
					foreach($invoice->getMfInvoiceDetail() as $detail){
						//search for any issued penalties						
						if(is_numeric($detail->getDescription()) && $detail->getDescription() == $fee->getId()){
							$penalties_issued+= $detail->getAmount();
						}
					}
				}
				//Calculate
				if($year_of_response == 0){
					//total penalties
					$penalties_total=0;
					//get penalties from the end of that year
					while($year_of_submission < date('Y')){
						error_log('-----Year of submission to penalized---'.$year_of_submission);
						$days_since_expiry = Functions::get_days_since($year_of_submission."-12-31", $year_of_submission.'-1-1');
						$months_since_expiry = Functions::get_months_since($year_of_submission."-12-31", $year_of_submission.'-1-1');
						
						//2017 penalties issued from June - IFC consultant
						if($year_of_submission == 2017){
							//shed off the months/days 
							$days_since_expiry=($days_since_expiry-(30*2));
							$months_since_expiry=($months_since_expiry-2);
						}
						//List of penalties that apply to current permit
						$q = Doctrine_Query::create()
							->from("PenaltyTemplate a")
							->where("(a.trigger_period <= ".$days_since_expiry." AND a.trigger_type = 1) OR (a.trigger_period <= ".$months_since_expiry." AND a.trigger_type = 2)");
						$penalties = $q->execute();
						
						foreach($penalties as $penalty)
						{
							$this->logSection('permitflow', 'YES! Applying penalty '.$penalty->getId().' to '.$application->getId());
							$penalties_total+=$invoice_manager->apply_penalty($application->getId(), $penalty->getId());

						}
						$year_of_submission++;
					}
					//cal current years penalties
					if($year_of_submission == date('Y')){
						error_log('-----Year of submission to penalized after incremental/ year was same---'.$year_of_submission);
						$days_since_expiry = Functions::get_days_since(date('Y-m-d'), $year_of_submission.'-1-1');
						$months_since_expiry = Functions::get_months_since(date('Y-m-d'), $year_of_submission.'-1-1');
						
						//List of penalties that apply to current permit
						$q = Doctrine_Query::create()
							->from("PenaltyTemplate a")
							->where("(a.trigger_period <= ".$days_since_expiry." AND a.trigger_type = 1) OR (a.trigger_period <= ".$months_since_expiry." AND a.trigger_type = 2)");
						$penalties = $q->execute();
						
						foreach($penalties as $penalty)
						{
							$this->logSection('permitflow', 'YES! Applying penalty '.$penalty->getId().' to '.$application->getId());
							$penalties_total+=$invoice_manager->apply_penalty($application->getId(), $penalty->getId());

						}
					}
					//invoice 
					error_log('-----Penalties total----'.$penalties_total.'-------Penalties issued----'.$penalties_issued);
					if($penalties_total && $penalties_total > $penalties_issued){
						try{
							$invoice_manager->create_invoice_from_task($application->getId(),array($fee->getId()),array(($penalties_total-$penalties_issued)));
							//Move
							$stage=$application_manager->get_submission_stage($application->getFormId(),$application->getEntryId());
							error_log('-----Stage to be moved-----'.$stage);
							$application->setApproved($stage);
							$application->save();
						}catch(Exception $e){
							$this->logSection('permitflow', 'Penalty error - '.$application->getId().': '.$ex);
							$failed++;
						}
					}
					
				}elseif($year_of_response){
					error_log('-------Year of response found------'.$year_of_response);
					//total penalties
					$penalties_total=0;
					//since that year was issued get penalties for the next year(current year)
					++$year_of_response;
					error_log('------Year of response ---incremented---'.$year_of_response);
					while($year_of_response < date('Y')){
						error_log('--------Year to be penalized---'.$year_of_response);
						$days_since_expiry = Functions::get_days_since($year_of_response."-12-31", $year_of_response.'-1-1');
						$months_since_expiry = Functions::get_months_since($year_of_response."-12-31", $year_of_response.'-1-1');
						
						//2017 penalties issued from June - IFC consultant
						if($year_of_response == 2017){
							//shed off the months/days 
							$days_since_expiry=($days_since_expiry-(30*2));
							$months_since_expiry=($months_since_expiry-2);
						}
						//List of penalties that apply to current permit
						$q = Doctrine_Query::create()
							->from("PenaltyTemplate a")
							->where("(a.trigger_period <= ".$days_since_expiry." AND a.trigger_type = 1) OR (a.trigger_period <= ".$months_since_expiry." AND a.trigger_type = 2)");
						$penalties = $q->execute();
						
						foreach($penalties as $penalty)
						{
							$this->logSection('permitflow', 'YES! Applying penalty '.$penalty->getId().' to '.$application->getId());
							$penalties_total+=$invoice_manager->apply_penalty($application->getId(), $penalty->getId());

						}
						$year_of_response++;
					}
					error_log('------Year of response ---incremented in loop--'.$year_of_response);
					//cal current years penalties
					if($year_of_response == date('Y')){
						$days_since_expiry = Functions::get_days_since(date('Y-m-d'), $year_of_response.'-1-1');
						$months_since_expiry = Functions::get_months_since(date('Y-m-d'), $year_of_response.'-1-1');
						
						//List of penalties that apply to current permit
						$q = Doctrine_Query::create()
							->from("PenaltyTemplate a")
							->where("(a.trigger_period <= ".$days_since_expiry." AND a.trigger_type = 1) OR (a.trigger_period <= ".$months_since_expiry." AND a.trigger_type = 2)");
						$penalties = $q->execute();
						
						foreach($penalties as $penalty)
						{
							$this->logSection('permitflow', 'YES! Applying penalty '.$penalty->getId().' to '.$application->getId());
							$penalties_total+=$invoice_manager->apply_penalty($application->getId(), $penalty->getId());

						}
					}
					//invoice 
					if($penalties_total && $penalties_total > $penalties_issued){
						try{
							$invoice_manager->create_invoice_from_task($application->getId(),array($fee->getId()),array(($penalties_total-$penalties_issued)));
							//Move
							$stage=$application_manager->get_submission_stage($application->getFormId(),$application->getEntryId());
							error_log('-----Stage to be moved-----'.$stage);
							$application->setApproved($stage);
							$application->save();
							
						}catch(Exception $e){
							$this->logSection('permitflow', 'Penalty error - '.$application->getId().': '.$ex);
							$failed++;
						}
					}

				}
			}
		}

        $this->logSection('permitflow', 'Completed generate-penalties task with '.$success.' successful and '.$failed.' failed.');
    }
}