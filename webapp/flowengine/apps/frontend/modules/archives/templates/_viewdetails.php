<?php
/**
 * _viewdetails.php partial.
 *
 * Displays application details
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 
 $pdf_path = null;
 
 if($application->getPdfPath())
 {
     $pdf_path = $application->getPdfPath();
 }
 else
 {
    include_partial('libforms');
    //get form id and entry id
    $form_id  = $application->getFormId();
    $entry_id = $application->getEntryId();
    
    
    $nav = trim($_GET['nav']);
    
    if(empty($form_id) || empty($entry_id)){
        echo '<div class="alert alert-error fade in nomargin">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4>Submission Error!</h4>
            <p>An error occurred during the submission of this application. Use the below recovery tool to recreate the application with the help of the client or tell the client to edit the application from their account.</p>
            </div>';
        exit;
    }
    else {
        
        $application_manager = new ApplicationManager();
        $pdf_path = $application_manager->save_archive_to_pdf_locally($application->getId());
    }
 }
 
$q = Doctrine_Query::create()
    ->from("ApSettings a");
$settings = $q->fetchOne();

if($settings->getUploadDir() == "/mnt/gv0/ntsa-prod/uploads")
{
    $pdf_path = "/uploads/".$pdf_path;
}
else
{
    $pdf_path = $settings->getUploadDir()."/".$pdf_path;
}
?>
<div align="center">
    <?php
    if(substr($pdf_path, 1) != "/")
    {
        $pdf_path  = "/".$pdf_path;
    }
    ?>
    <iframe src = "/ViewerJS/#..<?php echo $pdf_path; ?>" width='1024' height='900' allowfullscreen webkitallowfullscreen></iframe>
</div>