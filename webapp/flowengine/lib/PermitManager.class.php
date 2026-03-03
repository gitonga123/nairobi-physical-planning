<?php
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Dompdf\Dompdf;
/**
 *
 * Permit class that manages the creation and modification of permits
 *
 * Created by PhpStorm.
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 2:15 AM
 */

class PermitManager {

    //Public construction for the permit manager class
    public function __construct()
    {

    }

    //output permit to html
    public function generate_permit_template($permit_id, $pdf=false)
    {
        $templateparser = new TemplateParser();

        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $savedpermit = $q->fetchOne();

        $application = $savedpermit->getFormEntry();

        $html = "<html>
        <body>
        ";

        $cancelled = false;
        $expired = false;

        if($savedpermit->getPermitStatus() == 3)
        {
            $cancelled = true;
        }

        $db_date_event = str_replace('/', '-', $savedpermit->getDateOfExpiry());

        $db_date_event = strtotime($db_date_event);

        if (time() > $db_date_event && !$cancelled)
        {
            $expired = true;
        }

        if($cancelled)
        {
            $html = $html. "<style type='text/css'>
          .watermark {
            color: #680000;
            font-size: 50pt;
            -webkit-transform: rotate(-45deg);
            -moz-transform: rotate(-45deg);
            position: absolute;
            width: 100%;
            height: 200px;
            margin: 0;
            left:100px;
            top:400px;
            z-index: 1;
          }
        </style>";

            $html = $html."<div class='watermark'>Cancelled</div>";
        }

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $savedpermit->getTypeId())
            ->limit(1);
        $permit_template = $q->fetchOne();

        $permit_content = "";

        if($permit_template)
        {
            error_log('------------permit_template exists------------');
            if($permit_template->getContent()) {
              if(sfConfig::get('app_old_parser'))
              {
                $permit_content = $templateparser->parsePermitOld($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
              }
              else
              {
                $permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
              }
            }
            else
            {
                $permit_content = "<h3>This permit template is blank. Please contact system administrator.</h3>";
            }
        }
        else
        {
            error_log('------------permit_template don\'t exists------------');
            $q = Doctrine_Query::create()
                ->from("Permits a")
                ->where("a.applicationform = ?", $application->getFormId())
                ->limit(1);
            $permit_template = $q->fetchOne();

            if($permit_template)
            {
                $permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
            }
        }

        if($pdf)
        {
            $ssl_suffix = "s";

            if (empty($_SERVER['HTTPS'])) {
                $ssl_suffix = "";
            }

            //replace src=" for images with src="http://localhost
            $permit_content = str_replace('src="', 'src="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'], $permit_content);
            $permit_content = str_replace('background:url(', 'background:url(http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'], $permit_content);
            //$permit_content = str_replace('src="/', 'src="', $permit_content);
        }

        $html .= $permit_content;

        if($cancelled)
        {
            $html = "This permit has been cancelled";
        }

        $html .= "
        </body>
        </html>";

        if(empty($savedpermit->getRemoteUpdateUuid()))
        {
            $permit_manager = new PermitManager();
            $uuid = $permit_manager->generate_uuid();
            error_log("UUID Log: ".$uuid);

            $savedpermit->setRemoteUpdateUuid($uuid);
            $savedpermit->save();
        }
        $hidden_signature_holder = '<p style="color:white; margin-top: .5rem; margin-bottom: .5rem"> [sig|req|signer1] </p>';
        $html = str_ireplace('#SIGNATURE#', $hidden_signature_holder, $html);
        return html_entity_decode($html);
    }

    public function generate_permit_qr_template($permit_id, $pdf=false)
    {
        $templateparser = new TemplateParser();

        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $savedpermit = $q->fetchOne();

        $application = $savedpermit->getFormEntry();

        $html = "<html>
        <body>
        ";

        $cancelled = false;
        $expired = false;

        if($savedpermit->getPermitStatus() == 3)
        {
            $cancelled = true;
        }

        $db_date_event = str_replace('/', '-', $savedpermit->getDateOfExpiry());

        $db_date_event = strtotime($db_date_event);

        if (time() > $db_date_event && !$cancelled)
        {
            $expired = true;
        }

        if($cancelled)
        {
            $html = $html. "<style type='text/css'>
          .watermark {
            color: #680000;
            font-size: 50pt;
            -webkit-transform: rotate(-45deg);
            -moz-transform: rotate(-45deg);
            position: absolute;
            width: 100%;
            height: 200px;
            margin: 0;
            left:100px;
            top:400px;
            z-index: 1;
          }
        </style>";

            $html = $html."<div class='watermark'>Cancelled</div>";
        }

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $savedpermit->getTypeId())
            ->limit(1);
        $permit_template = $q->fetchOne();

        $permit_content = "";

        if($permit_template)
        {
            if($permit_template->getQrContent()) {
                try {
                    $permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getQrContent());
                }
                catch(Exception $ex)
                {
                    error_log("Debug-p: Could not use new template parser: ".$ex);
                    $permit_content = $templateparser->parsePermitOld($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getQrContent());
                }
            }
            else if($permit_template->getContent()) {
                try {
                    $permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
                }
                catch(Exception $ex)
                {
                    error_log("Debug-p: Could not use new template parser: ".$ex);
                    $permit_content = $templateparser->parsePermitOld($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
                }
            }

            else
            {
                $permit_content = "<h3>This permit template is blank. Please contact system administrator.</h3>";
            }
        }
        else
        {
            $q = Doctrine_Query::create()
                ->from("Permits a")
                ->where("a.applicationform = ?", $application->getFormId())
                ->limit(1);
            $permit_template = $q->fetchOne();

            if($permit_template)
            {
		if($permit_template->getQrContent()){
                	$permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getQrContent());
		}else{
                	$permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
		}
            }
        }

        if($pdf == false)
        {
            $ssl_suffix = "s";

            if (empty($_SERVER['HTTPS'])) {
                $ssl_suffix = "";
            }

            //replace src=" for images with src="http://localhost
            $permit_content = str_replace('<img src="', '<img src="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].'/', $permit_content);
        }

        $html .= $permit_content;

        if($cancelled)
        {
            $html = "This permit has been cancelled";
        }

        $html .= "
        </body>
        </html>";

        if(empty($savedpermit->getRemoteUpdateUuid()))
        {
            $permit_manager = new PermitManager();
            $uuid = $permit_manager->generate_uuid();
            error_log("UUID Log: ".$uuid);

            $db_connection = mysqli_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'),sfConfig::get('app_mysql_db'));

            $sql = "UPDATE saved_permit SET remote_update_uuid = '".$uuid."' WHERE id = ".$savedpermit->getId();
            $result = mysqli_query($db_connection, $sql);
        }

        return html_entity_decode($html);
    }

    //output permit to html
    public function generate_archive_permit_template($permit_id, $pdf=false)
    {
        $templateparser = new TemplateParser();

        $q = Doctrine_Query::create()
            ->from('SavedPermitArchive a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $savedpermit = $q->fetchOne();

        $application = $savedpermit->getApplication();

        $html = "<html>
        <body>
        ";

        $cancelled = false;
        $expired = false;

        if($savedpermit->getPermitStatus() == 3)
        {
            $cancelled = true;
        }

        $db_date_event = str_replace('/', '-', $savedpermit->getDateOfExpiry());

        $db_date_event = strtotime($db_date_event);

        if (time() > $db_date_event && !$cancelled)
        {
            $expired = true;
        }

        if($cancelled)
        {
            $html = $html. "<style type='text/css'>
          .watermark {
            color: #680000;
            font-size: 50pt;
            -webkit-transform: rotate(-45deg);
            -moz-transform: rotate(-45deg);
            position: absolute;
            width: 100%;
            height: 200px;
            margin: 0;
            left:100px;
            top:400px;
            z-index: 1;
          }
        </style>";

            $html = $html."<div class='watermark'>Cancelled</div>";
        }

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $savedpermit->getTypeId())
            ->limit(1);
        $permit_template = $q->fetchOne();

        $permit_content = "";

        if($permit_template)
        {
            if($permit_template->getContent()) {
                try {
                    $permit_content = $templateparser->parseArchivePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
                }
                catch(Exception $ex)
                {
                    error_log("Debug-p: Could not use new template parser: ".$ex);
                    $permit_content = $ex;
                }
            }
            else
            {
                $permit_content = "<h3>This permit template is blank. Please contact system administrator.</h3>";
            }
        }
        else
        {
            $q = Doctrine_Query::create()
                ->from("Permits a")
                ->where("a.applicationform = ?", $application->getFormId())
                ->limit(1);
            $permit_template = $q->fetchOne();

            if($permit_template)
            {
                $permit_content = $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $permit_template->getContent());
            }
        }

        if($pdf == false)
        {
            $ssl_suffix = "s";

            if (empty($_SERVER['HTTPS'])) {
                $ssl_suffix = "";
            }

            //replace src=" for images with src="http://localhost
            $permit_content = str_replace('<img src="', '<img src="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].'/', $permit_content);
        }

        $html .= $permit_content;

        if($cancelled)
        {
            $html = "This permit has been cancelled";
        }

        $html .= "
        </body>
        </html>";

        return html_entity_decode($html);
    }

    //output invoice to pdf
    public function save_to_pdf($permit_id)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $permit = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $permit->getTypeId())
            ->limit(1);
        $template = $q->fetchOne();

        $html = $this->generate_permit_template($permit_id, true);

        $dompdf = new Dompdf();
		$dompdf->set_option('isRemoteEnabled', TRUE);
        $dompdf->loadHtml($html);
        //Define the PDF page settings
        if($template->getPageType() == "A5")
        {
            if($template->getPageOrientation() == "landscape")
            {
                $dompdf->setPaper("A5", "landscape");
            }
            else
            {
                $dompdf->setPaper("A5", "potrait");
            }
        }
        else
        {
            if($template->getPageOrientation() == "landscape")
            {
                $dompdf->setPaper("A4", "landscape");
            }
            else
            {
                $dompdf->setPaper("A4", "potrait");
            }
        }

        $dompdf->render();
        $dompdf->stream($permit->getFormEntry()->getApplicationId().".pdf");
    }

     //output invoice to pdf
    public function save_archive_to_pdf($permit_id)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermitArchive a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $permit = $q->fetchOne();

        $html = $this->generate_archive_permit_template($permit_id, true);

        #require_once(dirname(__FILE__)."/vendor/dompdf/dompdf_config.inc.php");

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream($permit->getApplication()->getApplicationId().".pdf");
    }

    //output invoice to pdf but save as file locally and return link
    public function save_to_pdf_locally($permit_id)
    {
        return $filename;
    }

    //output invoice to pdf but save as file locally and return link
    public function save_archive_to_pdf_locally($permit_id)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermitArchive a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $permit = $q->fetchOne();

        $html = $this->generate_archive_permit_template($permit_id, true);

        #require_once(dirname(__FILE__)."/vendor/dompdf/dompdf_config.inc.php");

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $output = $dompdf->output();

        $filename = md5($permit->getFormEntry()->getApplicationId()."-".date("Y-m-d g:i:s"));

        $q = Doctrine_Query::create()
            ->from("ApSettings a");

        $settings = $q->fetchOne();
        if($settings) {
            try {
                if (substr($settings->getUploadDir(), 1) == "/") {
                    $file_to_save = $settings . '/' . $filename;
                    error_log("Debug-p: ".$file_to_save);
                    file_put_contents($file_to_save, $output);
                } else {
                    $file_to_save = $settings->getUploadDir() . '/' . $filename;
                    error_log("Debug-p: ".$file_to_save);
                    file_put_contents($file_to_save, $output);
                }
            }catch(Exception $ex)
            {
                error_log("Debug-p: ".$ex);
            }
        }

        return $filename.".pdf";
    }

    //Generate a new permit
    public function create_permit($application_id)
    {
        $submission = $this->get_application_by_id($application_id);
        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.applicationstage = ?",$submission->getApproved()) 
			->andWhere("a.applicationform = ?", $submission->getFormId());
        $permit_templates = $q->execute();
        foreach ($permit_templates as $permit_template) {
            //OTB ADD
            $q=Doctrine_Query::create()
                ->from("ApprovalCondition c")
                ->leftJoin("c.Condition a")
                ->where("c.entry_id =? and a.permit_id = ?",array($submission->getId(),$permit_template->getId()));
            $conditions=$q->count();
            if(($permit_template->getCheckConditions() && $conditions) || !$permit_template->getCheckConditions()){
                $q = Doctrine_Query::create()
                    ->from("SavedPermit a")
                    ->where("a.application_id = ?", $submission->getId())
                    ->andWhere("a.type_id = ?", $permit_template->getId())
                    ->andWhere("a.expiry_trigger = 0")
                    ->andWhere("a.permit_status <> 3");
                //$saved_permits = $q->execute();
                if ($q->count() > 0){
                    continue;
                }
                $date_of_issue = date("Y-m-d H:i:s");
                $submission->setDateOfResponse(date("Y-m-d H:i:s"));
                $submission->setDateOfIssue($date_of_issue);
                $submission->save();

                $new_permit = new SavedPermit();
                $new_permit->setTypeId($permit_template->getId());
                $new_permit->setApplicationId($submission->getId());
                
                $new_permit->setDateOfIssue($date_of_issue);
                
                if ($permit_template->getMaxDuration() > 0) {
                    $date = strtotime("+" . $permit_template->getMaxDuration() . " day");
                    $new_permit->setDateOfExpiry(date('Y-m-d', $date));
                }

                $new_permit->setCreatedBy(0);
                $new_permit->setLastUpdated($date_of_issue);

                $uuid = $this->generate_uuid();

                $new_permit->setRemoteUpdateUuid($uuid);
                $new_permit->save();
            }else{
                //return flash
                sfContext::getInstance()->getUser()->setFlash('notice','Permit not created! No condition set for application :'.$submission->getApplicationId().'! Permit :'.$permit_template->getTitle());
            }
        }
    }

    //Generate a new permit
    public function create_permit_with_template($application_id, $template_id)
    {
        $submission = $this->get_application_by_id($application_id);

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $template_id);
        $permit_templates = $q->execute();

        foreach ($permit_templates as $permit_template) {
            $q = Doctrine_Query::create()
                ->from("SavedPermit a")
                ->where("a.application_id = ?", $submission->getId())
                ->andWhere("a.type_id = ?", $permit_template->getId())
                ->andWhere("a.expiry_trigger = 0")
                ->andWhere("a.permit_status <> 3");
            $saved_permits = $q->execute();

            if ($q->count() > 0) {
                //No permits found so we need to generate a permit
                continue;
            }

            $date_of_issue = date("Y-m-d H:i:s");

            $submission->setDateOfResponse(date("Y-m-d H:i:s"));
            $submission->setDateOfIssue($date_of_issue);
            $submission->save();

            $new_permit = new SavedPermit();
            $new_permit->setTypeId($permit_template->getId());
            $new_permit->setApplicationId($submission->getId());
            $new_permit->setDateOfIssue($date_of_issue);

            if ($permit_template->getMaxDuration() > 0) {
                $date = strtotime("+" . $permit_template->getMaxDuration() . " day");
                $new_permit->setDateOfExpiry(date('Y-m-d', $date));
            }
            else 
            {
               $new_permit->setDateOfExpiry(""); 
            }

            $new_permit->setCreatedBy(0);
            $new_permit->setLastUpdated($date_of_issue);

            $uuid = $this->generate_uuid();

            $new_permit->setRemoteUpdateUuid($uuid);
            $new_permit->save();
        }
    }

    //Returns an existing application
    public function get_application_by_id($application_id)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id)
            ->orderBy("a.application_id DESC")
            ->limit(1);
        $existing_app = $q->fetchOne();
        if($existing_app)//Already submitted then tell client its already submitted
        {
            return $existing_app;
        }
        else
        {
            return false;
        }
    }

    //Check if the application has a permit
    public function has_permit($application_id)
    {
        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.application_id = ?", $application_id)
            ->andWhere("a.permit_status <> 3")
            ->limit(1);
        $saved_permit = $q->fetchOne();

        if($saved_permit)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //Check if the application has a permit for the current stage in the workflow
    public function needs_permit_for_current_stage($application_id)
    {
        $submission = $this->get_application_by_id($application_id);

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.applicationstage = ?", $submission->getApproved())
            ->andWhere("a.applicationform = ?", $submission->getFormId());
        $permit_templates = $q->execute();

        if($q->count()) {
			//OTB ADD
			$invoice_manager=new InvoiceManager();
			$unpaid_invoice=$invoice_manager->has_unpaid_invoice($application_id);
			$application_manager = new ApplicationManager();
			#$unpaid_invoice_migrated=false;
			#$migrated=$application_manager->check_if_migrated($submission->getEntryId());
			/*if($migrated){
				//Get migrated
				switch($submission->getFormId()){
					case 939:
						$migrated_application=Doctrine_Query::create()->from('FormEntry e')->where('e.form_id = ? and e.entry_id = ?', array(7283,$submission->getEntryId()))->fetchOne();
						break;
					case 7283:
						$migrated_application=Doctrine_Query::create()->from('FormEntry e')->where('e.form_id = ? and e.entry_id = ?', array(939,$submission->getEntryId()))->fetchOne();
						break;
				}
				$unpaid_invoice_migrated=$invoice_manager->has_unpaid_invoice($migrated_application->getId());
			}*/
            foreach($permit_templates as $template)
            {
              $q = Doctrine_Query::create()
                  ->from("SavedPermit a")
                  ->where("a.application_id = ?", $application_id)
                  ->andWhere("a.type_id = ?", $template->getId())
                  ->andWhere("a.expiry_trigger = 0")
                  ->andWhere("a.permit_status <> 3");
              //$saved_permits = $q->execute();
			  //OTB ADD Check if migrated & no pending
              if (($q->count() == 0 && !$unpaid_invoice)) {
                  //No permits found so we need to generate a permit
                  return true;
              }
          }

          return false;
        } else {
            //return true coz there is no need generate a permit on stage that doesn't require a permit
            return false;
        }
    }

    //Check if a permit has public access
    public function has_public_permissions($template_id)
    {
        $public_permits_settings = sfConfig::get('app_public_permits');
        $public_permits = explode(",", $public_permits_settings);

        if(in_array($template_id, $public_permits))
        {
            return true;
        }
        else
        {
            if($template_id == sfConfig::get('app_public_permits'))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    //Check if a permit has public access
    public function get_public_template($template_id)
    {
        $public_permits_settings = sfConfig::get('app_public_permits');
        return $public_permits_settings;
    }

    //Generate an adhoc (not-attached to any account) PDF if a permit has public access
    public function generate_public_pdf($template_id, $reference)
    {
        if($reference == '')
        {
            echo "<h3>No records found for your reference number</h3>";
            exit;
        }

        error_log("Debug-t: Attempting to generate permit for ".$template_id." and reference ".$reference);

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $template_id)
            ->limit(1);
        $permit_template = $q->fetchOne();

        if ($permit_template) {
            error_log("Debug-t: Found template");

            $template = $permit_template->getContent();

            //If form has remote settings, try and parse remote content
            if($permit_template->getRemoteUrl())
            {
                error_log("Debug-t: Found remote fetch");

                $results = null;

                $templateparser = new templateparser();

                $remote_url = $permit_template->getRemoteUrl();
                $remote_fields = $permit_template->getRemoteField();
                $remote_username = $permit_template->getRemoteUsername();
                $remote_password = $permit_template->getRemotePassword();

                $remote_url = str_replace('$reference',$reference, $remote_url);

                error_log("Debug-t: Remote URL: ".$remote_url);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $remote_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                if(!empty($remote_username) && !empty($remote_password))
                {
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_USERPWD, "$remote_username:$remote_password");
                }
                curl_setopt($ch, CURLOPT_ENCODING ,"");

                $results = curl_exec($ch);

                if($error = curl_error($ch))
                {
                    $error = "curl_error:" . $error . "<br />";
                    error_log("Debug-t: ".$error);
                }
                else
                {
                    error_log("Debug-t: Results found ".$results);

                    if($results == '{"count":0,"records":[]}')
                    {
                        echo "<h3>No records found for your reference number</h3>";
                        exit;
                    }
                    else
                    {
                        $template = $templateparser->parsePublicPermit($template, $reference, $results);
                    }
                }
            }

            $dompdf = new Dompdf();
            $dompdf->loadHtml($template);
            $dompdf->render();
            $dompdf->stream($reference);
        }
        else
        {
            error_log("Debug-t: No template found");
            echo "<h3>Unauthorized - No template found</h3>";
            exit;
        }
    }

    //Generate an adhoc (not-attached to any account) HTML if a permit has public access
    public function generate_public_html($template_id, $reference)
    {
        if($reference == '')
        {
            echo "<h3>No records found for your reference number</h3>";
            exit;
        }

        error_log("Debug-t: Attempting to generate permit for ".$template_id." and reference ".$reference);

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $template_id)
            ->limit(1);
        $permit_template = $q->fetchOne();

        if ($permit_template) {
            error_log("Debug-t: Found template");

            $template = $permit_template->getContent();

            //If form has remote settings, try and parse remote content
            if($permit_template->getRemoteUrl())
            {
                error_log("Debug-t: Found remote fetch");

                $results = null;

                $templateparser = new templateparser();

                $remote_url = $permit_template->getRemoteUrl();
                $remote_fields = $permit_template->getRemoteField();
                $remote_username = $permit_template->getRemoteUsername();
                $remote_password = $permit_template->getRemotePassword();

                $remote_url = str_replace('$reference',$reference, $remote_url);

                error_log("Debug-t: Remote URL: ".$remote_url);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $remote_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                if(!empty($remote_username) && !empty($remote_password))
                {
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_USERPWD, "$remote_username:$remote_password");
                }
                curl_setopt($ch, CURLOPT_ENCODING ,"");

                $results = curl_exec($ch);

                if($error = curl_error($ch))
                {
                    $error = "curl_error:" . $error . "<br />";
                    error_log("Debug-t: ".$error);
                }
                else
                {
                    error_log("Debug-t: Results found ".$results);

                    if($results == '{"count":0,"records":[]}')
                    {
                        echo "<h3>No records found for your reference number</h3>";
                        exit;
                    }
                    else
                    {
			$template = $templateparser->parsePublicPermit($template, $reference, $results);

			//Temporary fix to remove expiry date from permits that don't have an expiry date
			$template = str_replace('until
<strong></strong>
.','',$template);
                    }
                }
            }

            $ssl_suffix = "s";

            if (empty($_SERVER['HTTPS'])) {
                $ssl_suffix = "";
            }

            $template = str_replace('<img src="', '<img src="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].'/', $template);

            $template = $template.'<button class="btn btn-primary" onClick="window.location=\'http://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI].'&print=1\';">Print PDF</button>';

            return $template;
        }
        else
        {
            error_log("Debug-t: No template found");
            echo "<h3>Unauthorized - No template found</h3>";
            exit;
        }
    }

    //Get remote data and save to the permit
    public function get_remote_result($permit_id)
    {
        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.id = ?", $permit_id)
            ->limit(1);
        $saved_permit = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.id = ?", $saved_permit->getFormEntry()->getId())
            ->limit(1);
        $application = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("ApFormElements a")
            ->where("a.form_id = ?", $application->getFormId())
            ->andWhere("a.element_option_query <> ?", "");
        $form_elements = $q->execute();

        $prefix_folder = dirname(__FILE__)."/vendor/form_builder/";
        require_once($prefix_folder.'includes/init.php');

        require_once($prefix_folder.'../../../config/form_builder_config.php');
        require_once($prefix_folder.'includes/db-core.php');
        require_once($prefix_folder.'includes/helper-functions.php');

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        foreach($form_elements as $element)
        {
            $sql = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ?";
            $params = array($application->getEntryId());
            $sth = mf_do_query($sql,$params,$dbh);

            $entry_data = mf_do_fetch_result($sth);

            $remote_url = $element->getElementOptionQuery();
            $criteria = $element->getElementFieldName();
            $remote_template = $element->getElementFieldValue();
            $remote_username = $element->getElementRemoteUsername();
            $remote_password = $element->getElementRemotePassword();


            $ch = curl_init();

            $pos = strpos($remote_url, '$value');

            if($pos === false)
            {
                //dont' do anything
            }
            else
            {
                $remote_url = str_replace('$value', curl_escape($ch, $entry_data['element_'.$element->getElementId()]), $remote_url);
            }

            curl_setopt($ch, CURLOPT_URL, $remote_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

            if(!empty($remote_username) && !empty($remote_password))
            {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_USERPWD, "$remote_username:$remote_password");
            }

            $results = curl_exec($ch);

            error_log("Remote Results: ".$results);

            if(curl_error($ch))
            {
                $error = "error:" . curl_error($ch) . "<br />";
            }

            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if(empty($error))
            {
                $values = json_decode($results);
                if($criteria == "records")
                {
                    //If count is = 0, fail
                    if($values->{'count'} == 0)
                    {
                        $error = "No records found on server";
                    }
                }
                else if($criteria == "norecords")
                {
                    //If count is greater than 0, then pass
                    if($values->{'count'} > 0)
                    {
                        $error = "Existing records found on server";
                    }
                }

                return $results;
            }
        }
    }

    //generate new uuid code for a record
    public function generate_uuid()
    {
      try {

        // Generate a version 1 (time-based) UUID object
        $uuid1 = Uuid::uuid1();
        return $uuid1->toString(); // e4eaaaf2-d142-11e1-b3e4-080027620cdd

      } catch (UnsatisfiedDependencyException $e) {

        // Some dependency was not met. Either the method cannot be called on a
        // 32-bit system, or it can, but it relies on Moontoast\Math to be present.
        error_log('Caught exception: ' . $e->getMessage());

      }
    }

    //Update the expiry dates of all permits related to this application
    public function update_expiry_single($application_id)
    {   
        $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->where("a.application_id = ?", $application_id);
        $application = $q->fetchOne();

        $permits = $application->getSavedPermit();

        foreach($permits as $permit)
        {
            $q = Doctrine_Query::create()
                ->from("Permits a")
                ->where("a.id = ?", $permit->getTypeId());
            $template = $q->fetchOne();

            $max_duration = $template->getMaxDuration();

            if($max_duration > 0 && $template->getExpirationType() == 1)
            {
                    //If permit expires after a specified number of days then update the expiry of the permit
                    $date = strtotime("+".$max_duration." day");
                    $expiry_date = date('Y-m-d', $date);

                    $permit->setDateOfExpiry($expiry_date);
            }
            else if($template->getExpirationType() == 2)
            {
                $expiry_date = date('Y-m-t');

                $permit->setDateOfExpiry($expiry_date);
            }
            else if($template->getExpirationType() == 3)
            {
                $expiry_date = date('Y-12-t');

                $permit->setDateOfExpiry($expiry_date);
            }
            else {
                    //If the date of expiry is less than the max duration from the day the permit was created then update the expiry date of the permit
                    $date_created = $permit->getDateOfIssue();
                    $date_of_expiry = $permit->getDateOfExpiry();

                    //Get number of days between expiry and the date the permit was generated
                    $days_to_expiry = floor((strtotime($date_of_expiry) - strtotime($date_created))/(60*60*24));

                    //If not equal then update the expiry date
                    if(($days_to_expiry+5) < $max_duration)
                    {
                        $new_expiry = date('Y-m-d', strtotime("+".$max_duration." day"));
                        $permit->setDateOfExpiry($new_expiry);
                    }
            }

            $permit->save();
        }
    }

    //Update the expiry dates of all permits related to this application
    public function clean_permits($application_id)
    {   
        $q = Doctrine_Query::create()
           ->from("FormEntry a")
           ->where("a.application_id = ?", $application_id);
        $application = $q->fetchOne();

        if($application)
        {

            $permits = $application->getSavedPermit();

            foreach($permits as $permit)
            {
                $q = Doctrine_Query::create()
                    ->from("Permits a")
                    ->where("a.id = ?", $permit->getTypeId());
                
                if($q->count() == 0)
                {
                    error_log("Deleting permit ".$permit->getId()." for ".$application->getApplicationId());
                    $permit->delete();
                }

            }

        }
    }
	//OTB Add - Get issue date
	public function get_year_of_issue($application_id)
	{
		$submission=$this->get_application_by_id($application_id);
		if($submission){
			$current_year_plus_one=0;
			$current_year=0;
			$last_year=0;
			$prev_year=0;
			foreach($submission->getMfInvoice() as $invoice){
				if(date('Y',strtotime($invoice->getCreatedAt())) == (intval(date('Y'))+1) && $invoice->getPaid() == 2){
					//total for current year +1
					$current_year_plus_one+=$invoice->getTotalAmount();
				}
				if(date('Y',strtotime($invoice->getCreatedAt())) == date('Y') && $invoice->getPaid() == 2){
					//total for current year
					$current_year+=$invoice->getTotalAmount();
				}
				if(date('Y',strtotime($invoice->getCreatedAt())) == (intval(date('Y'))-1) && $invoice->getPaid() == 2){
					//total for current year
					$last_year+=$invoice->getTotalAmount();
				}
				if(date('Y',strtotime($invoice->getCreatedAt())) == (intval(date('Y'))-2) && $invoice->getPaid() == 2){
					//total for current year
					$prev_year+=$invoice->getTotalAmount();
				}
			}
			error_log('-------Current year +1-----'.$current_year_plus_one.'-------Current year-----'.$current_year.'-------Last year----'.$last_year.'------previous---'.$prev_year);
			$application_manager=new ApplicationManager();
			/*$migrated=$application_manager->check_if_migrated($submission->getEntryId());
			if($migrated){
				switch($submission->getFormId()){
					case 939:
						$migrated_application=Doctrine_Query::create()->from('FormEntry e')->where('e.form_id = ? and e.entry_id = ?', array(7283,$submission->getEntryId()))->fetchOne();
						break;
					case 7283:
						$migrated_application=Doctrine_Query::create()->from('FormEntry e')->where('e.form_id = ? and e.entry_id = ?', array(939,$submission->getEntryId()))->fetchOne();
						break;
				}
			}*/
			//Check if current year has been paid
			if($current_year_plus_one > 0){
				return (date('Y')+1);
			}elseif($current_year > 0){
				return date('Y');
			}elseif($last_year > 0){
				return (intval(date('Y'))-1);
			}elseif($prev_year > 0){
				return (intval(date('Y'))-2);
			}/*elseif($migrated && $current_year == 0 && $last_year == 0){
				//USE INVOICES OF MIGRATED APP
				$current_year_plus_one=0;
				$current_year=0;
				$last_year=0;
				$prev_year=0;
				foreach($migrated_application->getMfInvoice() as $invoice){
					if(date('Y',strtotime($invoice->getCreatedAt())) == (intval(date('Y'))+1) && $invoice->getPaid() == 2){
						//total for current year
						$current_year_plus_one+=$invoice->getTotalAmount();
					}
					if(date('Y',strtotime($invoice->getCreatedAt())) == date('Y') && $invoice->getPaid() == 2){
						//total for current year
						$current_year+=$invoice->getTotalAmount();
					}
					if(date('Y',strtotime($invoice->getCreatedAt())) == (intval(date('Y'))-1) && $invoice->getPaid() == 2){
						//total for current year
						$last_year+=$invoice->getTotalAmount();
					}
					if(date('Y',strtotime($invoice->getCreatedAt())) == (intval(date('Y'))-2) && $invoice->getPaid() == 2){
						//total for current year
						$prev_year+=$invoice->getTotalAmount();
					}
				}
				error_log('-------Current year migrated +1-----'.$current_year_plus_one.'-------Current year migrated-----'.$current_year.'-------Last year----'.$last_year.'-----previous---'.$prev_year);
				if($current_year_plus_one > 0){
					return (date('Y')+1);
				}elseif($current_year > 0){
					return date('Y');
				}elseif($last_year > 0){
					return (intval(date('Y'))-1);
				}elseif($prev_year > 0){
					return (intval(date('Y'))-2);
				}
			}*/
		}
    }
    public function permit_file_name($permit){
        $file_name  = str_replace('/','-',$permit->getFormEntry()->getApplicationId()).'-'.$permit->getId();
        return "$file_name.pdf";
    }
    public function is_signed($permit_id){
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $savedpermit = $q->fetchOne();
        if(!$savedpermit)
            return $savedpermit;
        $file_name = $this->permit_file_name($savedpermit);
        return file_exists("apps/permits/signed/$file_name");
    }


    public function is_applications_permit_signed($permit){
        $file_name = $this->permit_file_name($permit);
        return file_exists("apps/permits/signed/$file_name");
    }
    public function get_pdf_output($permit_id)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id)
            ->limit(1);
        $permit = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("Permits a")
            ->where("a.id = ?", $permit->getTypeId())
            ->limit(1);
        $template = $q->fetchOne();

        $html = $this->generate_permit_template($permit_id, true);

        //$html = str_replace('http://localhost:8000/barcode/barcode.processor.php?','',$html);
        #require_once(dirname(__FILE__)."/vendor/dompdf/dompdf_config.inc.php");

        $hidden_signature_holder = '<p style="color:white; margin-top: .5rem; margin-bottom: .5rem"> [sig|req|signer1] </p>';
        $html = str_ireplace('#SIGNATURE#', $hidden_signature_holder, $html);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', TRUE);
        $dompdf->loadHtml($html);
        error_log('-----' . $html);
        //Define the PDF page settings
        if ($template->getPageType() == "A5") {
            if ($template->getPageOrientation() == "landscape") {
                $dompdf->setPaper("A5", "landscape");
            } else {
                $dompdf->setPaper("A5", "potrait");
            }
        } else {
            if ($template->getPageOrientation() == "landscape") {
                $dompdf->setPaper("A4", "landscape");
            } else {
                $dompdf->setPaper("A4", "potrait");
            }
        }

        $dompdf->render();
        return $dompdf->output();
    }
}
