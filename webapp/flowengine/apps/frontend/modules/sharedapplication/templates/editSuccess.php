<?php
/**
 * editSuccess.php template.
 *
 * Allows client and resubmit an application
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper("I18N");
$prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
require($prefix_folder.'includes/init.php');

require($prefix_folder.'../../../config/form_builder_config.php');
require($prefix_folder.'includes/db-core.php');
require($prefix_folder.'includes/helper-functions.php');
require($prefix_folder.'includes/check-session.php');

require($prefix_folder.'includes/language.php');
require($prefix_folder.'includes/common-validator.php');
require($prefix_folder.'includes/post-functions.php');
require($prefix_folder.'includes/filter-functions.php');
require($prefix_folder.'includes/entry-functions.php');
require($prefix_folder.'includes/view-functions.php');
require($prefix_folder.'includes/users-functions.php');

$invoice_manager = new InvoiceManager();

//Get site config properties
$q = Doctrine_Query::create()
    ->from("ApSettings a")
    ->where("a.id = 1")
    ->orderBy("a.id DESC");
$apsettings = $q->fetchOne();


/*$q = Doctrine_Query::create()
    ->from("FormEntry a")
    ->where("a.id =?", $_GET['application_id']);
$submission = $q->fetchOne();

//OTB Start Patch - Only allow to edit if stage is a corrections stage
$q = Doctrine_Query::create()
    ->from("SubMenus a")
    ->where("a.id =?", $submission->getApproved());
$stage = $q->fetchOne();
$stage_type = $stage ? $stage->getStageType() : "-1";
//OTB End Patch - Only allow to edit if stage is a corrections stage

//OTB Patch - Only allow to edit if stage is a corrections stage, added stage type check for corrections
if((($submission->getDeclined() != "1" && ($stage_type != "5" || $stage_type != "9")) && $submission->getApproved() != "0"))
{
    header("Location: ".public_path()."index.php/application/view/id/".$submission->getId());
    exit;
}*/

$form_id = null;
$entry_id = null;

/*if($_GET['link'])
{*/
    $q = Doctrine_Query::create()
        ->from("FormEntryLinks a")
        ->where("a.id = ?", $_GET['link']);
    $link = $q->fetchOne();

    $form_id = (int)trim($link->getFormId());
    $entry_id = (int)trim($link->getEntryId());
/*}
else {
    $form_id = (int)trim($submission->getFormId());
    $entry_id = (int)trim($submission->getEntryId());
}*/
$nav = trim($_GET['nav']);


$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

//get form name
$query 	= "select
					 form_name
			     from
			     	 ".MF_TABLE_PREFIX."forms
			    where
			    	 form_id = ?";
$params = array($form_id);

$sth = mf_do_query($query,$params,$dbh);
$row = mf_do_fetch_result($sth);

if(!empty($row)){
    $form_name = htmlspecialchars($row['form_name']);
}

//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
if(!empty($nav)){
    $all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id);
    $entry_key = array_keys($all_entry_id_array,$entry_id);
    $entry_key = $entry_key[0];

    if($nav == 'prev'){
        $entry_key--;
    }else{
        $entry_key++;
    }

    $entry_id = $all_entry_id_array[$entry_key];

    //if there is no entry_id, fetch the first/last member of the array
    if(empty($entry_id)){
        if($nav == 'prev'){
            $entry_id = array_pop($all_entry_id_array);
        }else{
            $entry_id = $all_entry_id_array[0];
        }
    }
}

if(mf_is_form_submitted()){ //if form submitted

    if($_POST['save_as_draft'] || $_POST['save_as_draft2'])
    {
        $_SESSION['save_as_draft'] = true;
    }
    else
    {
        $_SESSION['save_as_draft'] = false;
    }

    $input_array   = mf_sanitize($_POST);
    $submit_result = mf_process_form($dbh,$input_array);

    if($submit_result['status'] === true){

        $application_manager = new ApplicationManager();
        $invoice_manager = new InvoiceManager();
        $payments_manager = new PaymentsManager();

        /*$payments_manager->validate_all_invoices($submission->getId());

        //If this is a draft submission being submitted then attempt to publish it to the workflow
        if($submission->getApproved() == "0")
        {
            //Return application back to initial stage else if not final then just redirect back to view application
            if($_SESSION["save_as_draft"] != 1)
            {
                if($invoice_manager->has_unpaid_invoice($submission->getId()))
                {
                    $invoice = $invoice_manager->get_unpaid_invoice($submission->getId());

                    //If invoice is pending, confirm payment status if IPN exists
                    if($invoice->getPaid() == 15)
                    {
                        $invoice = $invoice_manager->update_payment_status($invoice->getId());
                    }
                }

                //If there are still unpaid invoices then redirect to payment API
                if($invoice_manager->get_unpaid_invoice($submission->getId()) || !$invoice_manager->has_invoice($submission->getId()))
                {
                    $query  = "select
                    payment_enable_merchant,
                    payment_enable_invoice
                    from
                       `".MF_TABLE_PREFIX."forms`
                   where
                      form_id=?";

                    $params = array($submission->getFormId());

                    $sth = mf_do_query($query,$params,$dbh);
                    $row = mf_do_fetch_result($sth);

                    $payment_merchant_enable      = $row['payment_enable_merchant'];

                    //If online payment is enabled then redirect to payment API and publish draft later
                    if($payment_merchant_enable == 1 && $row['payment_enable_invoice'] == 0)
                    {
                        if(!$invoice_manager->has_invoice($submission->getId()))
                        {
                            $invoice = $invoice_manager->create_invoice_from_submission($submission->getId());

                            $sf_user->setAttribute('form_id', $submission->getFormId());
                            $sf_user->setAttribute('entry_id', $submission->getEntryId());
                            $sf_user->setAttribute('invoice_id', $invoice->getId());

                            header("Location: ".public_path()."index.php/forms/payment");
                            exit;

                        }
                        else
                        {
                            $invoice = $invoice_manager->get_unpaid_invoice($submission->getId());

                            $sf_user->setAttribute('form_id', $submission->getFormId());
                            $sf_user->setAttribute('entry_id', $submission->getEntryId());
                            $sf_user->setAttribute('invoice_id', $invoice->getId());

                            header("Location: ".public_path()."index.php/forms/payment");
                            exit;
                        }
                    }
                    else
                    {
                        //If online payment is not enabled then publish the draft to a live workflow
                        $submission = $application_manager->publish_draft($submission->getId());

                        header("Location: ".public_path()."index.php/application/view/id/".$submission->getId());
                        exit;
                    }

                }
                else
                {
                    //If invoices are already paid then publish the draft to a live workflow
                    $submission = $application_manager->publish_draft($submission->getId());

                    header("Location: ".public_path()."index.php/application/view/id/".$submission->getId());
                    exit;
                }
            }
            else
            {
                //If save as draft/resume later has been clicked then don't publish the draft
                header("Location: ".public_path()."index.php/application/view/id/".$submission->getId());
                exit;
            }
        }
        else
        {*/
            //If the application was declined then attempt to make a resubmission
            $submission = $application_manager->resubmit_application($link->getFormentryid());

            header("Location: ".public_path()."index.php/sharedapplication/view/id/".$link->getFormentryid());
            exit;
        //}

    }else if($submit_result['status'] === false){ //there are errors, display the form again with the errors
        $old_values 	= $submit_result['old_values'];
        $custom_error 	= @$submit_result['custom_error'];
        $error_elements = $submit_result['error_elements'];

        $form_params = array();
        $form_params['populated_values'] = $old_values;
        $form_params['error_elements']   = $error_elements;
        $form_params['custom_error'] 	 = $custom_error;
        $form_params['edit_id']			 = $input_array['edit_id'];
        $form_params['integration_method'] = 'php';
        $form_params['is_application'] = true;
        $form_params['page_number'] = 0; //display all pages (if any) as a single page
         //OTB patch, pass locale value 
        $form_markup = mf_display_form($dbh,$input_array['form_id'],$form_params,$sf_user->getCulture());
    }

}else{ //otherwise, display the form with the values
    //set session value to override password protected form
    $_SESSION['user_authenticated'] = $form_id;

    //set session value to bypass unique checking
    $_SESSION['edit_entry']['form_id']  = $form_id;
    $_SESSION['edit_entry']['entry_id'] = $entry_id;

    $form_values = mf_get_entry_values($dbh,$form_id,$entry_id);

    $form_params = array();
    $form_params['populated_values'] = $form_values;
    $form_params['edit_id']			 = $entry_id;
    $form_params['integration_method'] = 'php';
    $form_params['is_application'] = true;
    $form_params['page_number'] = 0; //display all pages (if any) as a single page
    //OTB patch, pass locale value
    $form_markup = mf_display_form($dbh,$form_id,$form_params,$sf_user->getCulture());
}
?>
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title"><?php echo __('Edit Application') ?></h4>
    </div>
</div>
<!-- Page-Title -->

<?php
if($_SESSION['draft_edit'])
{
    ?>
    <div class="alert alert-success">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <?php echo __('You had an incomplete entry that was saved as draft. Please edit and resubmit') ?>.
    </div>
<?php
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card-box p-b-0">
            <?php

            if($_GET['bill_error'])
            {
                ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong><?php echo __('Error') ?></strong> <?php echo __('Could not find records. Please confirm application details') ?>.
                </div>
                <?php
            }

            header("Content-Type: text/html; charset=UTF-8");
            echo $form_markup;
            ?>
        </div>
    </div>

    <!--Display a sidebar with information from the site config-->
    <?php if($apsettings){ ?>
        <div class="col-lg-4">
            <div class="card-box widget-user">
                <div>
                    <img src="/asset_unified/images/users/avatar-1.jpg" class="img-responsive img-circle" alt="user">
                    <div class="wid-u-info">
                        <h4 class="m-t-0 m-b-5"><?php echo $sf_user->getGuardUser()->getProfile()->getFullname(); ?></h4>
                        <p class="m-b-5 font-13"><?php echo $sf_user->getGuardUser()->getProfile()->getEmail(); ?><br>
                            ID: <?php echo $sf_user->getGuardUser()->getUsername(); ?>
                        </p>
                        <a class="btn btn-primary" href="/plan/signon/logout">Logout</a>
                    </div>
                </div>
            </div>

            <?php echo html_entity_decode($apsettings->getOrganisationSidebar()); ?>
        </div>
    <?php } ?>
</div>
<?php //if($submission->getDeclined()): ?>
<script>
$(function(){
	$('.form-control').attr('readonly',false);
	$('input[value="Save my progress and resume later"]').prop('disabled',true);
});
</script>
<?php //endif; ?>