<?php
    require_once(dirname(__FILE__).'/../../permitflow_src/config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();
    
    $invoice_manager = new InvoiceManager();
    
    $db_connection = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
    
    function GetDaysDiff($startdate, $enddate)
    {
	  $date1 = new DateTime($startdate);
	  $date2 = new DateTime($enddate);

	  $diff = $date2->diff($date1)->format("%a");
	  return $diff;
    }
    
    echo "/**** STARTING EXPIRATION CRONJOB ***/\n";

    $foundexpired = false;
    
    $q = Doctrine_Query::create()
        ->from("SubMenus a")
        ->where("a.id = ?", $_SERVER['argv'][1]);
    $submenu = $q->fetchOne();
   
    echo "/** STAGE: ".$submenu->getTitle()." **/\n";

    mysql_select_db(sfConfig::get('app_mysql_db'),$db_connection);

    $sql = "SELECT * FROM form_entry WHERE approved = ".$submenu->getId();
    $results = mysql_query($sql, $db_connection);

    echo "/* TOTAL APPLICATIONS TO CHECK: ".mysql_num_rows($results)." */\n";
    while($application = mysql_fetch_assoc($results))
    {
        $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->where("a.id = ?", $application['id']);
        $application = $q->fetchOne();

        echo "- Checking: ".$application['application_id']." */\n";
        $q = Doctrine_Query::create()
            ->from("ApplicationReference a")
            ->where("a.application_id = ?", $application->getId())
            ->andWhere("a.stage_id = ?", $submenu->getId())
            ->orderBy("a.id DESC");
        $reference = $q->fetchOne();
        if($reference)
        {
            if(GetDaysDiff($reference->getStartDate(), date('Y-m-d H:m:s')) > $submenu->getMaxDuration())
            {
                echo "-- ".$application->getApplicationId()." has expired */\n";
                $foundexpired = true;

                if($submenu->getStageExpiredMovement()) {
                    $sql = "UPDATE form_entry SET approved = ".$submenu->getStageExpiredMovement()." WHERE id = ".$application->getId();
                    mysql_query($sql, $db_connection) or die("Error ON: ".$sql);
                    
                    //$invoice_manager->update_invoices($application->getId());
                }
            }
        }
        else
        {
            if(GetDaysDiff($application->getDateOfSubmission(), date('Y-m-d H:m:s')) > $submenu->getMaxDuration())
            {
                echo "-- ".$application->getApplicationId()." has expired */\n";
                $foundexpired = true;

                if($submenu->getStageExpiredMovement()) {
                    $sql = "UPDATE form_entry SET approved = ".$submenu->getStageExpiredMovement()." WHERE id = ".$application->getId();
                    mysql_query($sql, $db_connection) or die("Error ON: ".$sql);
                    
                    //$invoice_manager->update_invoices($application->getId());
                }
            }
        }

        echo "-- Checking need for archiving */\n";

        //If the application has been sent to an archiving stage then just archive the application instead of waiting for a cronjob
        $q = Doctrine_Query::create()
            ->from("SubMenus a")
            ->where("a.id = ?", $application->getApproved());
        $stage = $q->fetchOne();
        if($stage->getStageType() == 7)
        {
            $foundarchived = true;
            echo "-- archiving ".$application->getApplicationId()."\n";

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

            echo " - Done with ".$application->getApplicationId().".";
        }
    }
    
    if($foundexpired)
    {
        error_log("Debug-e: Found expired applications");
    }
    else
    {
        error_log("Debug-e: Did not find expired applications");
    }

?>