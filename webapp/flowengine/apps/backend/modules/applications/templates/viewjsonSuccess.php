<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit($application->getId(), "Accessed application no ".$application->getApplicationId());

include_partial('dashboard/checksession');
/**
 * view template.
 *
 * Displays a single application and all of its review history
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

//include external libraries for form details and workflow management
include_partial('libforms');
include_partial('libworkflows');
include_partial('libcustom');

//Sanitize the URLs
function replaceLinks($value) {
    $linktext = preg_replace('/(([a-zA-Z]+:\/\/)([a-zA-Z0-9?&%.;:\/=+_-]*))/i', "<a href=\"$1\" target=\"_blank\">$1</a>", $value);
    return $linktext;
}

//Check for application number/identifier change
UpdateIdentifier($application);


$q = Doctrine_Query::create()
    ->from("FormEntry a")
    ->where("a.parent_submission = ?", $application->getId())
    ->orderBy("a.id DESC");
$prevsubmissions = $q->execute();

$q = Doctrine_Query::create()
    ->from("SubMenus a")
    ->where("a.id = ?", $application->getApproved());
$stage = $q->fetchOne();
if($stage)
{
    $q = Doctrine_Query::create()
        ->from("Menus a")
        ->where("a.id = ?", $stage->getMenuId());
    $parentstage = $q->fetchOne();
    $dataOutput['summary']['stage'] = $parentstage->getTitle();
}

$q = Doctrine_Query::create()
    ->from("SfGuardUserProfile a")
    ->where("a.user_id = ?", $application->getUserId());
$architect = $q->fetchOne();
$dataOutput['summary']['submitted_by'] = $architect->getFullname();
$dataOutput['summary']['submission_date'] = str_replace(" ", " @ ", $application->getDateOfSubmission());
if($application->getDateOfResponse()) {
    $dataOutput['summary']['architectural_approval_date'] = str_replace(" ", " @ ", $application->getDateOfResponse());
}
//Mombasa OTB Start - System now only accounts for the number of days within the working days of the week. i.e, excludes weekends
function getWorkingDays($startDate,$endDate,$holidays=[]){
    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = strtotime($startDate);


    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
    //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
    $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
        $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
            $workingDays--;
    }

    return $workingDays;
}
//Mombasa OTB End - System now only accounts for the number of days within the working days of the week. i.e, excludes weekends
function GetDaysSince($sStartDate, $sEndDate){
    $working_days = round(getWorkingDays($sStartDate,$sEndDate));// -1;//Do not include start day
    $working_days = $working_days>0?$working_days-1:$working_days;
    error_log("otb sStartDate: ".$sStartDate." end date: ".$sEndDate." Working days: ".$working_days);
    return floor($working_days);//Mombasa OTB - System now only accounts for the number of days within the working days of the week. i.e, excludes weekends
    /*$start_ts = strtotime($sStartDate);
    $end_ts = strtotime($sEndDate);
    $diff = $end_ts - $start_ts;
    return round($diff / 86400);*/
}

function GetLastDayWorkedOn($application){//OTB - Use last day of action on application. Work can still continue after a permit has been issued
    $q = Doctrine_Query::create()
        ->from('ApplicationReference b')
        ->where('b.application_id = ?', $application->getId())
        ->andWhere('b.start_date IS NOT NULL')
        ->andWhere('b.end_date IS NOT NULL')
        ->andWhere('b.start_date <= b.end_date')
        ->orderBy("b.id DESC");
    $application_reference_list = $q->execute();
    return $application_reference_list[0]->getEndDate();
}

$days = 0;
if($application->getDateOfResponse())
{
    //$days = GetDaysSince($application->getDateOfSubmission(), $application->getDateOfResponse());
    $days = GetDaysSince($application->getDateOfSubmission(), GetLastDayWorkedOn($application));//OTB - Use last day of action on application. Work can still continue after a permit has been issued
    error_log("ot b last day #".GetLastDayWorkedOn($application)." difference ".$days);
}
else {
    $days = GetDaysSince($application->getDateOfSubmission(), date("Y-m-d H:i:s"));
}

$days_color = "";
$maximum_duration = 0;

//get maximum duration of current stage
$q = Doctrine_Query::create()
    ->from("SubMenus a")
    ->where("a.id = ?", $application->getApproved());
$current_stage = $q->fetchOne();
if($current_stage)
{
    $maximum_duration = $current_stage->getMaxDuration();
}

if($days < $maximum_duration || $maximum_duration == 0){
    $days_color = "success";
}
elseif($days >= $maximum_duration){
    $days_color = "danger";
}
$dataOutput['summary']['days_in_progress'] = array($days, $days_color);
#<!--OTB Start - Show days in which application has been worked on internally, excluding days in which client action was required-->
$q = Doctrine_Query::create()
    ->from('ApplicationReference b')
    ->where('b.application_id = ?', $application->getId())
    ->andWhere('b.start_date IS NOT NULL')
    ->andWhere('b.end_date IS NOT NULL')
    ->andWhere('b.start_date <= b.end_date')
    ->andWhereNotIn('b.stage_id',array(46,112,120,175,114,168,118));
$application_reference_list = $q->execute();
$days_internally = 0;
$app_ref_count = 0;
foreach($application_reference_list as $application_reference){
    if($app_ref_count==0 or ($application_reference->getStartDate() >= $last_ref_date)){//OTB - Only add days where current start date is equal to or greater than last end date. Done because appref currently has bug that gives misguiding information
        if($app_ref_count==0){//Include days spent in first stage before any action was taken
            $days_internally = $days_internally + GetDaysSince($application->getDateOfSubmission(), $application_reference->getStartDate());//Include days spent in first stage before any action was taken
        }

        if($application_reference->getApprovedBy()){//Only add days where action was carried out by staff member
            $days_internally = $days_internally + GetDaysSince($application_reference->getStartDate(), $application_reference->getEndDate());//add days
        }

        /*if($last_ref_date && ($application_reference->getStartDate() > $last_ref_date)){
           $days_internally = $days_internally + GetDaysSince($last_ref_date, $application_reference->getStartDate());//add days since last action to current action
        }Commented because not needed after fixing data*/
    }
    $last_ref_date = $application_reference->getEndDate();
    error_log("Record ".$application_reference->getID()." --- otb app ref days internally = ".$days_internally);
    $app_ref_count++;
}
if(count($application_reference_list)<=0){//Include days spent in first stage if no action has been taken yet
    $days_internally = GetDaysSince($application->getDateOfSubmission(), date("Y-m-d H:i:s"));
}

//$dataOutput['summary']['days_with_staff'] = str_replace($days_internally, 'badge-success');
$dataOutput['summary']['days_with_staff'] = $days_internally;
#<!--OTB End - Show days in which application has been worked on internally, excluding days in which client action was required-->
if($stage){
$q = Doctrine_Query::create()
    ->from("Menus a")
    ->where("a.id = ?", $stage->getMenuId());
$parent_stage = $q->fetchOne();
if($parent_stage){
    $dataOutput['summary']['parent_stage'] = $parent_stage->getTitle();
}

$dataOutput['summary']['application_status'] = $stage->getTitle();
$dataOutput['summary']['prev_application_nos'] = array();
}


$q=Doctrine_Query::create()
	->from('ApplicationNumberHistory h')
	->where('h.form_entry_id = ?',$application->getId());
$histories=$q->execute();
if($histories)
{
    foreach($histories as $rec){
        $dataOutput['summary']['prev_application_nos'][] = $rec->getApplicationNumber();
    }
}
$dataOutput['summary']['prev_submissions_nos'] = array();
foreach($prevsubmissions as $currentapplication)
{
    $dataOutput['summary']['prev_submissions_nos'][] = $architect->getFullname() . '(' . $currentapplication->getDateOfSubmission(). ')';
}
$q = Doctrine_Query::create()
    ->from("ApForms a")
    ->where("a.form_id = ?", $application->getFormId());
$form = $q->fetchOne();
if($form){
    $name = $form->getFormName();

	$q = Doctrine_Query::create()
	   ->from("ExtTranslations a")
	   ->where("a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($form->getFormId(), 'form_name', 'ap_forms'))
	   ->andWhere("a.locale = ?", $sf_user->getCulture());
	$translation = $q->fetchOne();
    if($translation)
    {
        $name = $translation->getTrlContent();
    }
    $dataOutput['name'] = $name;
}

$q = Doctrine_Query::create()
    ->from("FormEntryLinks a")
    ->where("a.formentryid = ?", $application->getId());
$links = $q->execute();
//Display control buttons that manipulate the application
if($links){
    $q = Doctrine_Query::create()
        ->from("FormEntryLinks a")
        ->where("a.formentryid = ?", $application->getId());
    $links = $q->execute();
    $count = 0;
    foreach($links as $link)
    {
        $count++;
        $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $link->getFormId());
        $linkedform = $q->fetchOne();
        if($linkedform)
        {
            //Display control buttons that manipulate the application
            try {
                $dataOutput['aditional_details'] = include __DIR__ . DIRECTORY_SEPARATOR . '_viewformlinksjson.php';
            } catch(\Exception $e){
                $dataOutput['aditional_details'] = $e->getMessage();
            }
        }
    }
}
//Display information about the user that submitted this application
$dataOutput['user_details'] = include __DIR__ . DIRECTORY_SEPARATOR . '_viewclientjson.php';
//Display comments submitted by reviewers #may eventually decide to merge this with the _viewreviewers if i manage to seperate individual reviewer comments
//include_partial('viewcomments', array('application' => $application));
//Display control buttons that manipulate the application
if($application->getMfInvoice()){
    //Displays any information attached to this application
    #include_partial('viewinvoices', array('application' => $application));
    try {
        $dataOutput['billing_history'] = include __DIR__ . DIRECTORY_SEPARATOR . '_viewinvoicesjson.php';
    } catch(\Exception $e){
        $dataOutput['billing_history'] = $e->getMessage();
    }
}
//Display scanned files attached on the comment sheet using a scanning task
#include_partial('viewhistory', array('application' => $application,'fromdate' => $fromdate,'fromtime' => $fromtime,'todate' => $todate,'totime' => $totime,'apppage' => $apppage));

//Displays a message trail between the client and the reviewers
#include_partial('viewmessages', array('application' => $application));

//Displays a message trail between the reviewers
#include_partial('viewreviewermessages', array('application' => $application));

echo json_encode($dataOutput);