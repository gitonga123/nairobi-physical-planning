<?php
    require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();
    
    $db_connection = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
    
    function GetDaysDiff($startdate, $enddate)
    {
	  $date1 = new DateTime($startdate);
	  $date2 = new DateTime($enddate);

	  $diff = $date2->diff($date1)->format("%a");
	  return $diff;
    }
    
    echo "/**** STARTING REVERSE ARCHIVING CRONJOB ***/\n";

    $foundexpired = false;
    
    $sql = "SELECT id FROM form_entry_archive";
    $results = mysql_query($sql, $db_connection);
    
    while($row = mysql_fetch_assoc($results))
    {
        $q = Doctrine_Query::create()
			->from("FormEntryArchive a")
			->where("a.id = ?", $row['id']);
        $archived_application = $q->fetchOne();
        
		$foundarchived = true;
		
		$q = Doctrine_Query::create()
			->from("FormEntry a")
			->where("a.id = ?", $archived_application->getId());
		if($q->count())
		{
			echo "-- skipping reverse archiving ".$archived_application->getApplicationId()."\n";
			continue;
		}
		else
		{
			echo "-- reverse archiving ".$archived_application->getApplicationId()."\n";
		}
		
		//Move Application
		$entry = new FormEntry();
		$entry->setId($archived_application->getId());
		$entry->setFormId($archived_application->getFormId());
		$entry->setEntryId($archived_application->getEntryId());
		$entry->setApproved($archived_application->getApproved());
		$entry->setApplicationId($archived_application->getApplicationId());
		$entry->setUserId($archived_application->getUserId());
		$entry->setParentSubmission($archived_application->getParentSubmission());
		$entry->setDeclined($archived_application->getDeclined());
		$entry->setDateOfSubmission($archived_application->getDateOfSubmission());
		$entry->setDateOfResponse($archived_application->getDateOfResponse());
		$entry->setDateOfIssue($archived_application->getDateOfIssue());
		$entry->setObservation($archived_application->getObservation());
		$entry->save();
		
		//Move Invoice
		$archive_invoices = $archived_application->getMfInvoiceArchive();
		foreach($archive_invoices as $archive_invoice)
		{
                        $q = Doctrine_Query::create()
			->from("MfInvoice a")
			->where("a.id = ?", $archive_invoice->getId());
                        if($q->count())
                        {
                                echo "-- skipping reverse archiving invoice for ".$archived_application->getApplicationId()."\n";
                                continue;
                        }
                        else
                        {
                                echo "-- reverse archiving invoice for ".$archived_application->getApplicationId()."\n";
                        }
                        
			$invoice = new MfInvoice();
			$invoice->setId($archive_invoice->getId());
			$invoice->setAppId($archive_invoice->getAppId());
			$invoice->setInvoiceNumber($archive_invoice->getInvoiceNumber());
			$invoice->setTemplateId($archive_invoice->getTemplateId());
			$invoice->setPaid($archive_invoice->getPaid());
			$invoice->setCreatedAt($archive_invoice->getCreatedAt());
			$invoice->setUpdatedAt($archive_invoice->getUpdatedAt());
			$invoice->setDueDate($archive_invoice->getDueDate());
			$invoice->setExpiresAt($archive_invoice->getExpiresAt());
			$invoice->setPayerId($archive_invoice->getPayerId());
			$invoice->setPayerName($archive_invoice->getPayerName());
			$invoice->setDocRefNumber($archive_invoice->getDocRefNumber());
			$invoice->setCurrency($archive_invoice->getCurrency());
			$invoice->setServiceCode($archive_invoice->getServiceCode());
			$invoice->setTotalAmount($archive_invoice->getTotalAmount());
			$invoice->save();
			
			$archive_invoice_details = $archive_invoice->getMfInvoiceDetailArchive();
			foreach($archive_invoice_details as $archive_invoice_detail)
			{
                                $q = Doctrine_Query::create()
                                ->from("MfInvoiceDetail a")
                                ->where("a.id = ?", $archive_invoice_detail->getId());
                                if($q->count())
                                {
                                        echo "-- skipping reverse archiving invoice fee ".$archived_application->getApplicationId()."\n";
                                        continue;
                                }
                                else
                                {
                                        echo "-- reverse archiving invoice fee for ".$archived_application->getApplicationId()."\n";
                                }
                                
				$invoice_detail = new MfInvoiceDetail();
				$invoice_detail->setId($archive_invoice_detail->getId());
				$invoice_detail->setInvoiceId($archive_invoice_detail->getInvoiceId());
				$invoice_detail->setDescription($archive_invoice_detail->getDescription());
				$invoice_detail->setAmount($archive_invoice_detail->getAmount());
				$invoice_detail->setCreatedAt($archive_invoice_detail->getCreatedAt());
				$invoice_detail->setUpdatedAt($archive_invoice_detail->getUpdatedAt());
				$invoice_detail->save();
				
				//Delete original detail
				$archive_invoice_detail->delete();
			}
			
			//Delete original invoice
			$archive_invoice->delete();
		}
		
		//Move Permit
		$archived_permits = $archived_application->getSavedPermits();
		foreach($archived_permits as $archived_permit)
		{
			$permit = new SavedPermit();
			$permit->setId($archived_permit->getId());
			$permit->setTypeId($archived_permit->getTypeId());
			$permit->setApplicationId($archived_permit->getApplicationId());
			$permit->setDateOfIssue($archived_permit->getDateOfIssue());
			$permit->setDateOfExpiry($archived_permit->getDateOfExpiry());
			$permit->setRemoteResult($archived_permit->getRemoteResult());
			$permit->setCreatedBy($archived_permit->getCreatedBy());
			$permit->setLastUpdated($archived_permit->getLastUpdated());
			$permit->setRemoteUpdateUuid($archived_permit->getRemoteUpdateUuid());
			$permit->save();
			
			//Delete original permit
			$archived_permit->delete();
		}
		
		//Delete original application
		$archived_application->delete();
		
		echo " - Done with ".$archived_application->getApplicationId().".";
	}

?>