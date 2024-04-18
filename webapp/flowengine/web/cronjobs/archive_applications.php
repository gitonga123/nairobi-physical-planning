<?php
    require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

    $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.stage_type = ? ", 7);
    $submenus = $q->execute();
    foreach($submenus as $submenu)
    {
        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.approved = ?", $submenu->getId())
            ->limit(1000);
        $applications = $q->execute();
        
        echo "*** Found ".sizeof($applications)." in stage_id = ".$submenu->getId()." records pending archving ***";
        
        $count = 0;
		
        foreach($applications as $application)
        {
            $foundarchived = true;
            echo "Cloning ".$application->getApplicationId();
            
			//Move Application
            $archive_entry = new FormEntryArchive();
            $archive_entry->setId($application->getId());
            $archive_entry->setFormId($application->getFormId());
            $archive_entry->setEntryId($application->getEntryId());
            $archive_entry->setApproved($application->getApproved());
            $archive_entry->setApplicationId($application->getApplicationId());
            $archive_entry->setUserId($application->getUserId());
            $archive_entry->setParentSubmission($application->getParentSubmission());
            $archive_entry->setDeclined($application->getDeclined());
            $archive_entry->setDateOfSubmission($application->getDateOfSubmission());
            $archive_entry->setDateOfResponse($application->getDateOfResponse());
            $archive_entry->setDateOfIssue($application->getDateOfIssue());
            $archive_entry->setObservation($application->getObservation());
            $archive_entry->save();
            
            echo " - Done with ".$application->getApplicationId().".";
            
			//Move Invoice
            $invoices = $application->getMfInvoice();
            foreach($invoices as $invoice)
            {
                $archive_invoice = new MfInvoiceArchive();
                $archive_invoice->setId($invoice->getId());
                $archive_invoice->setAppId($invoice->getAppId());
                $archive_invoice->setInvoiceNumber($invoice->getInvoiceNumber());
                $archive_invoice->setTemplateId($invoice->getTemplateId());
                $archive_invoice->setPaid($invoice->getPaid());
                $archive_invoice->setCreatedAt($invoice->getCreatedAt());
                $archive_invoice->setUpdatedAt($invoice->getUpdatedAt());
                $archive_invoice->setDueDate($invoice->getDueDate());
                $archive_invoice->setExpiresAt($invoice->getExpiresAt());
                $archive_invoice->setPayerId($invoice->getPayerId());
                $archive_invoice->setPayerName($invoice->getPayerName());
                $archive_invoice->setDocRefNumber($invoice->getDocRefNumber());
                $archive_invoice->setCurrency($invoice->getCurrency());
                $archive_invoice->setServiceCode($invoice->getServiceCode());
                $archive_invoice->setTotalAmount($invoice->getTotalAmount());
                $archive_invoice->save();
                
                $invoice_details = $invoice->getMfInvoiceDetail();
                foreach($invoice_details as $detail)
                {
                    $archive_invoicedetail = new MfInvoiceDetailArchive();
                    $archive_invoicedetail->setId($detail->getId());
                    $archive_invoicedetail->setInvoiceId($detail->getInvoiceId());
                    $archive_invoicedetail->setDescription($detail->getDescription());
                    $archive_invoicedetail->setAmount($detail->getAmount());
                    $archive_invoicedetail->setCreatedAt($detail->getCreatedAt());
                    $archive_invoicedetail->setUpdatedAt($detail->getUpdatedAt());
                    $archive_invoicedetail->save();
                    
                    //Delete original detail
                    $detail->delete();
                }
                
                //Delete original invoice
                $invoice->delete();
            }
			
			//Move Permit
            $permits = $application->getSavedPermits();
            foreach($permits as $permit)
            {
                $archived_permit = new SavedPermitArchive();
                $archived_permit->setId($permit->getId());
                $archived_permit->setTypeId($permit->getTypeId());
                $archived_permit->setApplicationId($permit->getApplicationId());
                $archived_permit->setDateOfIssue($permit->getDateOfIssue());
                $archived_permit->setDateOfExpiry($permit->getDateOfExpiry());
                $archived_permit->setRemoteResult($permit->getRemoteResult());
                $archived_permit->setCreatedBy($permit->getCreatedBy());
                $archived_permit->setLastUpdated($permit->getLastUpdated());
                $archived_permit->setRemoteUpdateUuid($permit->getRemoteUpdateUuid());
                $archived_permit->save();
                
                //Delete original permit
                $permit->delete();
            }
            
            //Delete original application
            $application->delete();
		}
        
        echo "*** - Done archiving ".sizeof($applications)." records ***";
    }
    if($foundarchived)
    {
        error_log("Debug-e: Archived applications");
    }
    else
    {
        error_log("Debug-e: Did not archive applications");
    }

?>