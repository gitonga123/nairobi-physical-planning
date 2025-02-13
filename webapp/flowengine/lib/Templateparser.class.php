<?php

use CodeItNow\BarcodeBundle\Utils\QrCode;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;

$prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
require_once($prefix_folder . 'includes/init.php');

require_once($prefix_folder . '../../../config/form_builder_config.php');
require_once($prefix_folder . 'includes/db-core.php');
require_once($prefix_folder . 'includes/helper-functions.php');

class DateFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        return date('d/m/Y', (new DateTime(urldecode($value)))->getTimestamp());
    }
}

class Date2Filter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        return date('d-m-Y', (new DateTime(urldecode($value)))->getTimestamp());
    }
}

class NumFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}

class NullFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        if ($value == null || $value = "") {
            return '""';
        } else {
            return $value;
        }
    }
}

class CountryFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT Country_Id FROM country WHERE Country_Name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return $row['Country_Id'];
        }

        return $value;
    }
}

class CountyFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT county_id FROM county WHERE county_name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return $row['county_id'];
        }

        return $value;
    }
}

class DistrictFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT District_Id FROM district WHERE District_Name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return $row['District_Id'];
        }

        return $value;
    }
}

class LocalityFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT Locality_Id FROM locality WHERE District_Name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return $row['Locality_Id'];
        }

        return $value;
    }
}

class PostalCodeFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT Postal_Code FROM postal_code WHERE Postal_Area_Name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return substr($row['Postal_Code'], 0, 5);
        }

        return substr($value, 0, 5);
    }
}

class StationFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT Station_Id FROM station WHERE Station_Name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return $row['Station_Id'];
        }

        return $value;
    }
}

class EconomicActivityFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        $sql = "SELECT Eco_Act_Mst_id FROM economic_activity WHERE Economic_Activity_Name LIKE ?";
        $params = array("%" . $value . "%");
        $sth = mf_do_query($sql, $params, $dbh);

        while ($row = mf_do_fetch_result($sth)) {
            return $row['Eco_Act_Mst_id'];
        }

        return $value;
    }
}

class TabFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        return preg_replace("/	/", "", $value);
    }
}

class EncodeFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        return urlencode($value);
    }
}

class ThirtyDayFilter implements \Dust\Filter\Filter
{
    public function apply($value)
    {
        return date('d F Y', strtotime($value . " +30days"));
    }
}

class Templateparser
{
    public $invoice_details = '';
    public function setup($application_id, $form_id, $entry_id)
    {
        $this->application = Doctrine_Core::getTable("FormEntry")->find($application_id);
        $this->user = Doctrine_Core::getTable("SfGuardUser")->find($this->application->getUserId());
        $this->user_profile = $this->user->getSfGuardUserProfile();
        $this->invoice_details = '';

        $q = Doctrine_Query::create()
            ->from('mfUserProfile a')
            ->where('a.user_id = ?', $this->application->getUserId())
            ->limit(1);
        $this->formprofile = $q->fetchOne();
        $this->form_id = $this->application->getFormId();
        $this->entry_id = $this->application->getEntryId();
        if ($this->formprofile) {
            $this->prof_entry_id = $this->formprofile->getEntryId();

            $sql = "SELECT * FROM ap_form_" . $this->formprofile->getFormId() . " WHERE id = ?";
            $params = array($this->prof_entry_id);
            $sth = mf_do_query($sql, $params, $dbh);

            $this->profileform = mf_do_fetch_result($sth);
        }

        $this->app_entry_id = $this->application->getEntryId();

        $sql = "SELECT * FROM ap_form_" . $this->form_id . " WHERE id = ?";
        $params = array($this->entry_id);
        $sth = mf_do_query($sql, $params, $dbh);

        $this->apform = mf_do_fetch_result($sth);

        $this->conditions = "";
        $this->miniconditions = "";

        $this->invoice_total = 0;

        $q = Doctrine_Query::create()
            ->from('ApprovalCondition a')
            ->where('a.entry_id = ?', $this->application->getId());
        $this->approvalconditions = $q->execute();
        $this->conditions = "<ul>";
        foreach ($this->approvalconditions as $approval) {
            $this->conditions = $this->conditions . "<li>" . $approval->getCondition()->getShortName() . ". " . $approval->getCondition()->getDescription() . "</li>";
            $this->miniconditions = $this->miniconditions . $approval->getCondition()->getShortName() . ", ";
        }
        $q = Doctrine_Query::create()
            ->from('Conditions a')
            ->where('a.circulation_id = ?', $this->application->getCirculationId());
        $conds = $q->execute();
        foreach ($conds as $cond) {
            $this->conditions = $this->conditions . "<li>- " . $cond->getConditionText() . "</li>";
        }
        $this->conditions = $this->conditions . "</ul>";

        $q = Doctrine_Query::create()
            ->from('EntryDecline a')
            ->where('a.entry_id = ?', $this->application->getId());
        $this->comments = $q->execute();
        $this->decline = "<ul>";
        foreach ($this->comments as $comment) {
            $this->decline = $this->decline . "<li>-" . $comment->getDescription() . "</li>";
        }
        $this->decline = $this->decline . "</ul>";

        $this->invoices = $this->application->getMfInvoice();
        foreach ($this->invoices as $invoice) {
            $q = Doctrine_Query::create()
                ->from('mfInvoiceDetail a')
                ->where('a.invoice_id = ? AND a.description = ?', array($invoice->getId(), 'Total'))
                ->limit(1);
            $this->details = $q->fetchOne();
            $this->invoice_total = $this->invoice_total + $this->details->getAmount();
        }

        $q = Doctrine_Query::create()
            ->from('PlotActivity a')
            ->where('a.entry_id = ?', $this->application->getId())
            ->limit(1);
        $this->plotactivity = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $this->profile_form);

        $this->profileelements = $q->execute();

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $this->form_id);

        $this->formelements = $q->execute();
    }

    public function parse($application_id, $form_id, $entry_id, $content)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $saved_application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        if ($this->find('{fm_id}', $content)) {
            $content = str_replace('{fm_id}', $saved_application->getId(), $content);
        }

        if ($this->find('{fm_application_id}', $content)) {
            $content = str_replace('{fm_application_id}', $saved_application->getApplicationId(), $content);
        }

        if ($this->find('{fm_date_of_notice}', $content)) {
            $q = Doctrine_Query::create()
                ->from("ApplicationReference a")
                ->where("a.application_id = ?", $application_id)
                ->orderBy("a.id DESC")
                ->limit(1);
            $app_ref = $q->fetchOne();

            if ($app_ref) {
                $content = str_replace('{fm_date_of_notice}', date('d F Y', strtotime(substr($app_ref->getStartDate(), 0, 11))), $content);
            } else {
                $content = str_replace('{fm_date_of_notice}', "", $content);
            }
        }

        if ($this->find('{current_stage_date}', $content)) {
            $q = Doctrine_Query::create()
                ->from("ApplicationReference a")
                ->where("a.application_id = ?", $application_id)
                ->orderBy("a.id DESC")
                ->limit(1);
            $app_ref = $q->fetchOne();

            if ($app_ref) {
                $content = str_replace('{current_stage_date}', date('d F Y', strtotime(substr($app_ref->getStartDate(), 0, 11))), $content);
            } else {
                $content = str_replace('{current_stage_date}', "", $content);
            }
        }

        if ($this->find('{fm_notice_no}', $content)) {
            $content = str_replace('{fm_notice_no}', $saved_application->getApplicationId(), $content);
        }

        $user = Doctrine_Core::getTable("SfGuardUser")->find($saved_application->getUserId());

        if ($user) {

            $user_profile = $user->getSfGuardUserProfile();

            $q = Doctrine_Query::create()
                ->from('mfUserProfile a')
                ->where('a.user_id = ?', $saved_application->getUserId())
                ->limit(1);
            $formprofile = $q->fetchOne();

            if ($formprofile) {
                $prof_entry_id = $formprofile->getEntryId();

                $sql = "SELECT * FROM ap_form_" . $formprofile->getFormId() . " WHERE id = ?";
                $params = array($prof_entry_id);
                $sth = mf_do_query($sql, $params, $dbh);

                $profile_form = mf_do_fetch_result($sth);

                $q = Doctrine_Query::create()
                    ->from('ApFormElements a')
                    ->where('a.form_id = ?', $formprofile->getFormId());

                $elements = $q->execute();

                foreach ($elements as $element) {
                    $childs = $element->getElementTotalChild();
                    if ($childs == 0) {
                        if ($this->find('{sf_element_' . $element->getElementId() . '}', $content)) {
                            if ($element->getElementType() == "select") {
                                if ($element->getElementSelectOptions() == "table") {
                                    $query = "SELECT {$element->getElementFieldValue()}, {$element->getElementFieldName()} FROM {$element->getElementTableName()} WHERE {$element->getElementFieldValue()} = {$profile_form['element_' . $element->getElementId()]} limit 1";
                                    $table_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                                    foreach ($table_rows as $option) {
                                        $content = str_replace('{sf_element_' . $element->getElementId() . '}', $option[$element->getElementFieldName()], $content);
                                    }
                                } elseif ($element->getElementSelectOptions() == 'query') {
                                    $query_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($element->getElementOptionQuery());
                                    foreach ($query_rows as $option) {
                                        if ($option[$element->getElementFieldValue()] == $profile_form['element_' . $element->getElementId()]) {
                                            $content = str_replace('{sf_element_' . $element->getElementId() . '}', $option[$element->getElementFieldName()], $content);
                                        }
                                    }
                                } elseif ($element->getElementSelectOptions() == 'application') {
                                    $application = Doctrine_Core::getTable("FormEntry")->find($profile_form['element_' . $element->getElementId()]);
                                    if ($application) {
                                        $content = str_replace('{sf_element_' . $element->getElementId() . '}', $application->getApplicationId(), $content);
                                    }
                                } else {
                                    $q = Doctrine_Query::create()
                                        ->from('ApElementOptions a')
                                        ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($formprofile->getFormId(), $element->getElementId(), $profile_form['element_' . $element->getElementId()]))
                                        ->limit(1);
                                    $option = $q->fetchOne();

                                    if ($option) {
                                        $content = str_replace('{sf_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                                    }
                                }
                            } else {
                                $content = str_replace('{sf_element_' . $element->getElementId() . '}', $profile_form['element_' . $element->getElementId()], $content);
                            }
                        }
                        for ($x = 1; $x < 9; $x++) {
                            if ($this->find('{sf_element_' . $element->getElementId() . '_' . ($x) . '}', $content)) {
                                $content = str_replace('{sf_element_' . $element->getElementId() . '_' . ($x) . '}', $profile_form['element_' . $element->getElementId() . "_" . ($x)], $content);
                            }
                        }
                    } else {
                        for ($x = 0; $x < ($childs + 1); $x++) {
                            if ($this->find('{sf_element_' . $element->getElementId() . '_' . ($x + 1) . '}', $content)) {
                                $content = str_replace('{sf_element_' . $element->getElementId() . '_' . ($x + 1) . '}', $profile_form['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                            }
                        }

                        if ($this->find('{sf_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{sf_element_' . $element->getElementId() . '}', $profile_form['element_' . $element->getElementId()], $content);
                        }
                    }
                }
            }


            //Get User Information (anything starting with sf_ )
            //sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
            if ($this->find('{sf_username}', $content)) {
                $content = str_replace('{sf_username}', $user->getUsername(), $content);
            }
            if ($this->find('{sf_email}', $content)) {
                $content = str_replace('{sf_email}', $user_profile->getEmail(), $content);
            }
            if ($this->find('{sf_fullname}', $content)) {
                $content = str_replace('{sf_fullname}', $user_profile->getFullname(), $content);
            }
        }

        $app_entry_id = $saved_application->getEntryId();

        $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = ?";
        $params = array($entry_id);
        $sth = mf_do_query($sql, $params, $dbh);

        $apform = mf_do_fetch_result($sth);

        $conditions = "";
        $miniconditions = "";

        $invoice_total = 0;

        $q = Doctrine_Query::create()
            ->from('ApprovalCondition a')
            ->where('a.entry_id = ?', $saved_application->getId());
        $approvalconditions = $q->execute();
        $conditions = "<ul>";
        foreach ($approvalconditions as $approval) {
            $conditions = $conditions . "<li>" . $approval->getCondition()->getShortName() . ". " . $approval->getCondition()->getDescription() . "</li>";
            $miniconditions = $miniconditions . $approval->getCondition()->getShortName() . ", ";
        }
        $q = Doctrine_Query::create()
            ->from('Conditions a')
            ->where('a.circulation_id = ?', $saved_application->getCirculationId());
        $conds = $q->execute();
        foreach ($conds as $cond) {
            $conditions = $conditions . "<li>- " . $cond->getConditionText() . "</li>";
        }
        $conditions = $conditions . "</ul>";

        $q = Doctrine_Query::create()
            ->from('EntryDecline a')
            ->where('a.entry_id = ?', $saved_application->getId());
        $comments = $q->execute();
        $decline = "<ul>";
        foreach ($comments as $comment) {
            $decline = $decline . "<li>-" . $comment->getDescription() . "</li>";
        }
        $decline = $decline . "</ul>";

        $invoices = $saved_application->getMfInvoice();
        foreach ($invoices as $invoice) {
            $q = Doctrine_Query::create()
                ->from('mfInvoiceDetail a')
                ->where('a.invoice_id = ? AND a.description = ?', array($invoice->getId(), 'Total'))
                ->limit(1);
            $details = $q->fetchOne();
            if ($details) {
                $invoice_total = $invoice_total + $details->getAmount();
            }
        }

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        if ($this->find('{ap_application_id}', $content)) {
            $content = str_replace('{ap_application_id}', $saved_application->getApplicationId(), $content);
        }

        if ($this->find('{fm_id}', $content)) {
            $content = str_replace('{fm_id}', $saved_application->getId(), $content);
        }

        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1


        if ($this->find('{fm_created_at}', $content)) {
            if ($saved_application->getDateOfSubmission()) {
                $content = str_replace('{fm_created_at}', date('d F Y', strtotime(substr($saved_application->getDateOfSubmission(), 0, 11))), $content);
            } else {
                $content = str_replace('{fm_created_at}', "", $content);
            }
        }
        if ($this->find('{fm_updated_at}', $content)) {
            if ($saved_application->getDateOfResponse()) {
                $content = str_replace('{fm_updated_at}', date('d F Y', strtotime(substr($saved_application->getDateOfResponse(), 0, 11))), $content);
            } else {
                $content = str_replace('{fm_updated_at}', "", $content);
            }
        }

        if ($this->find('{fm_date_created}', $content)) {
            if ($saved_application->getDateOfSubmission()) {
                $content = str_replace('{fm_date_created}', date('d F Y', strtotime(substr($saved_application->getDateOfSubmission(), 0, 11))), $content);
            } else {
                $content = str_replace('{fm_date_created}', "", $content);
            }
        }
        if ($this->find('{fm_last_updated}', $content)) {
            if ($saved_application->getDateOfResponse()) {
                $content = str_replace('{fm_last_updated}', date('d F Y', strtotime(substr($saved_application->getDateOfResponse(), 0, 11))), $content);
            } else {
                $content = str_replace('{fm_last_updated}', "", $content);
            }
        }
        if ($this->find('{current_date}', $content)) {
            $content = str_replace('{current_date}', date('d F Y', strtotime(date('Y-m-d'))), $content);
        }

        $q = Doctrine_Query::create()
            ->from('ApFormElements a')
            ->where('a.form_id = ?', $form_id);

        $elements = $q->execute();

        foreach ($elements as $element) {
            $childs = $element->getElementTotalChild();

            if ($childs == 0) {
                if ($element->getElementType() == "select") {
                    if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                        if ($element->getElementSelectOptions() == "table") {
                            $query = "SELECT {$element->getElementFieldValue()}, {$element->getElementFieldName()} FROM {$element->getElementTableName()} WHERE {$element->getElementFieldValue()} = {$apform['element_' . $element->getElementId()]} limit 1";
                            $table_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                            foreach ($table_rows as $option) {
                                $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option[$element->getElementFieldName()], $content);
                            }
                        } elseif ($element->getElementSelectOptions() == 'query') {
                            $query_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($element->getElementOptionQuery());
                            foreach ($query_rows as $option) {
                                if ($option[$element->getElementFieldValue()] == $apform['element_' . $element->getElementId()]) {
                                    $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option[$element->getElementFieldName()], $content);
                                }
                            }
                        } elseif ($element->getElementSelectOptions() == 'application') {
                            $application = Doctrine_Core::getTable("FormEntry")->find($apform['element_' . $element->getElementId()]);
                            if ($application) {
                                $content = str_replace('{fm_element_' . $element->getElementId() . '}', $application->getApplicationId(), $content);
                            }
                        } else {
                            $q = Doctrine_Query::create()
                                ->from('ApElementOptions a')
                                ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $apform['element_' . $element->getElementId()]))
                                ->limit(1);
                            $option = $q->fetchOne();

                            if ($option) {
                                $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                            }
                        }
                    }
                    if ($this->find('{fm_element_' . $element->getElementId() . '_zone}', $content)) //return zone_id
                    {
                        if ($element->getElementSelectOptions() == "table" && $element->getElementTableName() == "zones") {
                            $query = "SELECT zone_id FROM {$element->getElementTableName()} WHERE {$element->getElementFieldValue()} = {$apform['element_' . $element->getElementId()]} limit 1";
                            $table_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                            foreach ($table_rows as $option) {
                                $content = str_replace('{fm_element_' . $element->getElementId() . '_zone}', $option['zone_id'], $content);
                            }
                        }
                    }
                } else {
                    if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                        $content = str_replace('{fm_element_' . $element->getElementId() . '}', $apform['element_' . $element->getElementId()], $content);
                    }
                    for ($x = 1; $x < 9; $x++) {
                        if ($this->find('{fm_element_' . $element->getElementId() . '_' . ($x) . '}', $content) && $apform['element_' . $element->getElementId() . '_' . $x]) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . '_' . ($x) . '}', $apform['element_' . $element->getElementId() . '_' . $x], $content);
                        }
                    }
                }
            } else {
                for ($x = 0; $x < ($childs + 1); $x++) {
                    if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                        $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                    }
                }
            }
        }

        //Get Conditions of Approval (anything starting with ca_ )
        //ca_conditions
        if ($this->find('{ca_conditions}', $content)) {
            $content = str_replace('{ca_conditions}', $conditions, $content);
        }

        //ca_conditions
        if ($this->find('{ap_comments}', $content)) {
            $content = str_replace('{ap_comments}', $decline, $content);
        }

        //mini_ca_conditions
        if ($this->find('{mini_ca_conditions}', $content)) {
            $content = str_replace('{mini_ca_conditions}', $miniconditions, $content);
        }

        //Get Invoice Details (anything starting with in_ )
        //in_total

        if ($this->find('{in_total}', $content)) {
            $content = str_replace('{in_total}', $invoice_total, $content);
        }

        if ($this->find('{merchant_identifier}', $content)) {
            $content = str_replace('{merchant_identifier}', trim($saved_application->getMerchantIdentifier()), $content);
        }

        $comment_values = $this->getCommentSheetDetails($application_id);

        $content = static::parseWithDust($content, $comment_values);

        return $content;
    }


    /***
     * Used to parse values into a permit template
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $application_id the id of the application
     * @return string
     */
    public function parseApplication($application_id, $content)
    {
        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        $user_id = $application->getUserId();

        //User Details
        $user_details = $this->getUserDetails($user_id);

        //Application Details
        $application_details = $this->getApplicationDetails($application_id);

        $values = array_merge($user_details, $application_details);

        $content = static::parseWithDust($content, $values);

        return $content;
    }


    /***
     * Used to parse values into a permit template
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $application_id the id of the application
     * @param $form_id the id of the form
     * @param $entry_id the id of the entry in the form table
     * @param $permit_id the id of the permit
     * @return string
     */
    public function parsePermit($application_id, $form_id, $entry_id, $permit_id, $content)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $application = Doctrine_Core::getTable("FormEntry")->findOneBy('id', $application_id);

        $saved_permit = Doctrine_Core::getTable("SavedPermit")->findOneBy('id', $permit_id);

        $user_id = $application->getUserId();

        //User Details
        $user_details = $this->getUserDetails($user_id);

        //Application Details
        $application_details = $this->getApplicationDetails($application_id);

        //Permit Details
        $permit_details = $this->getPermitDetails($permit_id);
        // The comment sheet data
        $comment_sheet = $this->getCommentSheetDetails($application_id);

        $values = array_merge($user_details, $application_details, $comment_sheet);

        //Merge first invoice details
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.app_id = ?", $application_id)
            ->andWhere("a.paid = 2")
            ->orderBy("a.id ASC");
        $invoice = $q->fetchOne();

        if ($invoice) {
            //Invoice Details
            $invoice_details = $this->getInvoiceDetails($invoice->getId());

            $values = array_merge($values, $invoice_details);
        }

        $values = array_merge($values, $permit_details);

        //Check if any field has remote data, pull and integrate results into values
        // $q = Doctrine_Query::create()
        //     ->from('apFormElements a')
        //     ->where('a.form_id = ?', $application->getFormId())
        //     ->andWhere('a.element_option_query <> ?', "")
        //     ->andWhere('a.element_status = 1');
        // $elements = $q->execute();

        // foreach ($elements as $element) {
        //     // $updater = new UpdatesManager();
        //     // $remote_url = $element->getElementOptionQuery();
        //     // $remote_username = $element->getElementRemoteUsername();
        //     // $remote_password = $element->getElementRemotePassword();
        //     // $remote_value = $element->getElementRemoteValue();
        //     // $remote_post = $element->getElementRemotePost();

        //     //Replace url fields
        //     // $remote_url = $this->parseURL($application->getId(), $remote_url);

        //     //Replace remote_value with actual database value
        //     // $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = ?";
        //     // $params = array($application->getEntryId());
        //     // $sth = mf_do_query($sql, $params, $dbh);

        //     // $apform = mf_do_fetch_result($sth);

        //     // $remote_value = $apform["element_" . $element->getElementId()];

        //     // $pos = strpos($remote_url, '$value');

        //     // if ($pos === false) {
        //     //     //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
        //     // } else {
        //     //     $remote_url = str_replace('$value', urlencode($remote_value), $remote_url);
        //     // }

        //     // if ($remote_post) {
        //     //     //Do nothing TODO: Integrate remote post
        //     // } else {
        //     //     $remote_values = $updater->pull_raw_results($remote_url, $remote_username, $remote_password, $remote_value);
        //     //     $array_results = array();

        //     //     foreach ($remote_values['records'][0] as $key => $value) {
        //     //         $array_results[$key] = $value;
        //     //     }

        //     //     if (sizeof($array_results) > 0) {
        //     //         $values = array_merge($values, $array_results);
        //     //     }
        //     // }
        // }

        $content = static::parseWithDust($content, $values);

        return $content;
    }

    /***
     * Used to parse values into a permit template
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $application_id the id of the application
     * @param $form_id the id of the form
     * @param $entry_id the id of the entry in the form table
     * @param $permit_id the id of the permit
     * @return string
     */
    public function parseArchivePermit($application_id, $form_id, $entry_id, $permit_id, $content)
    {
        $application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);
        $saved_permit = Doctrine_Core::getTable("SavedPermitArchive")->find($permit_id);

        $user_id = $application->getUserId();

        //User Details
        $user_details = $this->getUserDetails($user_id);

        //Application Details
        $application_details = $this->getArchiveApplicationDetails($application_id);

        //Permit Details
        $permit_details = $this->getArchivePermitDetails($permit_id);

        $values = array_merge($user_details, $application_details);
        $values = array_merge($values, $permit_details);

        $content = static::parseWithDust($content, $values);

        return $content;
    }

    public function parsePublicPermit($content, $reference, $remote_data)
    {
        if ($this->find('{current_date}', $content)) {
            $content = str_replace('{current_date}', date('d F Y'), $content);
        }

        $values = json_decode($remote_data, true);
        $content = static::parseWithDust($content, $values['records'][0]);
        return $content;
    }

    /***
     * Use Dust template engine to parse the template
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $values array Array of values to be used to replace placeholders
     * @return string
     */
    public static function parseWithDust($content, $values)
    {
        //create object
        $dust = new \Dust\Dust();
        // attach a date filter
        $dust->filters['date'] = new DateFilter();

        $dust->filters['date2'] = new Date2Filter();

        $dust->filters['num'] = new NumFilter();
        $dust->filters['null'] = new NullFilter();

        $dust->filters['country'] = new CountryFilter();
        $dust->filters['county'] = new CountyFilter();
        $dust->filters['district'] = new DistrictFilter();
        $dust->filters['locality'] = new LocalityFilter();
        $dust->filters['postal_code'] = new PostalCodeFilter();
        $dust->filters['station'] = new StationFilter();
        $dust->filters['economic_activity'] = new EconomicActivityFilter();

        $dust->filters['tab'] = new TabFilter();

        $dust->filters['encode'] = new EncodeFilter();

        $dust->filters['thirtyday'] = new ThirtyDayFilter();

        $dust->helpers['hash'] = function ($chunk, $context, $bodies, $params) {
            $algo = $params->algo ?: 'sha256';
            $block = $bodies->block;
            // clone chunk and render block
            $newChunk = $chunk->newChild();
            $output = $newChunk->render($block, $context)->out;
            $hash = hash($algo, $output);
            return $chunk->write($hash);
        };
        //compile a template
        $template = $dust->compile($content);

        if ($content == "") {
            return $content;
        } else {
            //render the template
            return $dust->renderTemplate($template, $values);
        }
    }

    public function parseURL($application_id, $url)
    {

        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        //Application Details
        $application_details = $this->getRemoteApplicationDetails($application_id);

        $content = static::parseWithDust($url, $application_details);

        return $content;
    }

    public function parseRemote($application_id, $form_id, $entry_id, $permit_id, $content)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        $saved_permit = Doctrine_Core::getTable("SavedPermit")->find($permit_id);

        $user_id = $application->getUserId();

        //User Details
        $user_details = $this->getRemoteUserDetails($user_id);

        //Application Details
        $application_details = $this->getRemoteApplicationDetails($application_id);

        //Permit Details
        $permit_details = $this->getPermitDetails($permit_id);

        $values = array_merge($user_details, $application_details);
        $values = array_merge($values, $permit_details);

        $invoices = $application->getMfInvoice();

        foreach ($invoices as $invoice) {
            if ($invoice->getPaid() == 2) {
                //Invoice Details
                $invoice_details = $this->getInvoiceDetails($invoice->getId());
                $values = array_merge($values, $invoice_details);
            }
        }

        //Check if any field has remote data, pull and integrate results into values
        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $application->getFormId())
            ->andWhere('a.element_option_query <> ?', "");
        $elements = $q->execute();

        foreach ($elements as $element) {
            $updater = new UpdatesManager();
            $remote_url = $element->getElementOptionQuery();
            $remote_username = $element->getElementRemoteUsername();
            $remote_password = $element->getElementRemotePassword();
            $remote_value = $element->getElementRemoteValue();
            $remote_post = $element->getElementRemotePost();

            //Replace url fields
            $remote_url = $this->parseURL($application->getId(), $remote_url);

            //Replace remote_value with actual database value
            $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id =  ?";
            $params = array($application->getEntryId());
            $sth = mf_do_query($sql, $params, $dbh);

            $apform = mf_do_fetch_result($sth);

            $remote_value = $apform["element_" . $element->getElementId()];

            $pos = strpos($remote_url, '$value');

            if ($pos === false) {
                //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
            } else {
                $remote_url = str_replace('$value', urlencode($remote_value), $remote_url);
            }

            if ($remote_post) {
                //Do nothing TODO: Integrate remote post
            } else {
                $remote_values = $updater->pull_raw_results($remote_url, $remote_username, $remote_password, $remote_value);

                $array_results = array();

                foreach ($remote_values['records'][0] as $key => $value) {
                    $array_results[$key] = $value;
                }

                if (sizeof($array_results) > 0) {
                    $values = array_merge($values, $array_results);
                }
            }
        }

        $content = static::parseWithDust($content, $values);

        return $content;
    }

    public function parseRemoteArchive($application_id, $form_id, $entry_id, $permit_id, $content)
    {
        $saved_application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);

        $saved_permit = Doctrine_Core::getTable("SavedPermitArchive")->find($permit_id);

        $app_entry_id = $saved_application->getEntryId();

        $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id =  ?";
        $params = array($app_entry_id);
        $sth = mf_do_query($sql, $params, $dbh);

        $apform = mf_do_fetch_result($sth);

        $sql = "SELECT * FROM ap_settings WHERE id = 1";
        $app_results = mysql_query($sql, $dbconn);

        $apsettings = mysql_fetch_assoc($app_results);

        $sql = "SELECT * FROM ap_settings WHERE id =  ?";
        $params = array(1);
        $sth = mf_do_query($sql, $params, $dbh);

        $apsettings = mf_do_fetch_result($sth);

        if ($this->find('{ap_issue_date}', $content)) {
            if ($saved_permit->getDateOfIssue()) {
                $content = str_replace('{ap_issue_date}', date('d F Y', strtotime($saved_permit->getDateOfIssue())), $content);
            } else {
                $content = str_replace('{ap_issue_date}', "", $content);
            }
        }

        if ($this->find('{ap_application_id}', $content)) {
            $content = str_replace('{ap_application_id}', $saved_application->getApplicationId(), $content);
        }

        if ($this->find('{fm_id}', $content)) {
            $content = str_replace('{fm_id}', $saved_application->getId(), $content);
        }

        if ($this->find('{ap_permit_id}', $content)) {
            $content = str_replace('{ap_permit_id}', $saved_permit->getPermitId(), $content);
        }

        if ($this->find('{ap_expire_date}', $content)) {
            if ($saved_permit->getDateOfExpiry()) {
                $content = str_replace('{ap_expire_date}', date('d F Y', strtotime($saved_permit->getDateOfExpiry())), $content);
            } else {
                $content = str_replace('{ap_expire_date}', "", $content);
            }
        }

        $user = Doctrine_Core::getTable("SfGuardUser")->find($saved_application->getUserId());

        $user_profile = $user->getSfGuardUserProfile();

        //Get User Information (anything starting with sf_ )
        //sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
        if ($this->find('{sf_username}', $content)) {
            $content = str_replace('{sf_username}', $user->getUsername(), $content);
        }
        if ($this->find('{sf_email}', $content)) {
            $content = str_replace('{sf_email}', $user_profile->getEmail(), $content);
        }
        if ($this->find('{sf_mobile}', $content)) {
            $content = str_replace('{sf_mobile}', $user_profile->getMobile(), $content);
        }
        if ($this->find('{sf_fullname}', $content)) {
            $content = str_replace('{sf_fullname}', $user_profile->getFullname(), $content);
        }


        if ($this->find('{current_date}', $content)) {
            $content = str_replace('{current_date}', date('d F Y', strtotime(date('Y-m-d'))), $content);
        }

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $form_id);

        $elements = $q->execute();

        foreach ($elements as $element) {
            if ($element->getElementType() == "simple_name") {
                if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"], $content);
                }
                continue;
            }

            if ($element->getElementType() == "simple_name_wmiddle") {
                if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . "_3"], $content);
                }
                continue;
            }

            if ($element->getElementType() == "file") {
                if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", "https://" . $_SERVER['HTTP_HOST'] . "/" . $apsettings['data_dir'] . "/form_" . $element->getFormId() . "/files/" . $apform['element_' . $element->getElementId()], $content);
                }
                continue;
            }

            if ($element->getElementType() == "checkbox") {
                if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                    $q = Doctrine_Query::create()
                        ->from('ApElementOptions a')
                        ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()));
                    $options = $q->execute();

                    $options_text = "";
                    foreach ($options as $option) {
                        if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                            $options_text .= "" . $option->getOptionText() . "";
                        }
                    }
                    $options_text .= "";

                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", $options_text, $content);
                }
                continue;
            }

            if ($element->getElementType() == "select") {
                if ($element->getElementExistingForm() && $element->getElementExistingStage()) {
                    $q = Doctrine_Query::create()
                        ->from("FormEntry a")
                        ->where("a.id = ?", $apform['element_' . $element->getElementId()])
                        ->limit(1);
                    $linked_application = $q->fetchOne();
                    if ($q->count() > 0) {

                        //            $q = Doctrine_Query::create()
                        //               ->from("SavedPermit a")
                        //               ->leftJoin("a.FormEntry b")
                        //               ->where("b.form_id = ?", $linked_application->getFormId());
                        //            $permits = $q->execute();
                        //
                        //            foreach($permits as $saved_permit)
                        //            {
                        //                if($this->find("{ap_permit_id_".$saved_permit->getTypeId()."_element_child}", $content))
                        //                {
                        //                  $content = str_replace("{ap_permit_id_".$saved_permit->getTypeId()."_element_child}", ($saved_permit->getPermitId()?$saved_permit->getPermitId():$linked_application->getApplicationId()), $content);
                        //                }
                        //            }

                        if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . '}', $linked_application->getApplicationId(), $content);
                        }

                        $q = Doctrine_Query::create()
                            ->from('apFormElements a')
                            ->where('a.form_id = ?', $element->getElementExistingForm())
                            ->andWhere('a.element_status = ?', 1);

                        $child_elements = $q->execute();

                        foreach ($child_elements as $child_element) {
                            $sql = "SELECT * FROM ap_form_" . $linked_application->getFormId() . " WHERE id = ?";
                            $params = array($linked_application->getEntryId());
                            $sth = mf_do_query($sql, $params, $dbh);

                            $child_apform = mf_do_fetch_result($sth);

                            if ($this->find('{ap_child_application_id}', $content)) {
                                $content = str_replace('{ap_child_application_id}', $linked_application->getApplicationId(), $content);
                            }

                            if ($this->find('{fm_child_created_at}', $content)) {
                                if ($linked_application->getDateOfSubmission()) {
                                    $content = str_replace('{fm_child_created_at}', date('d F Y', strtotime($linked_application->getDateOfSubmission())), $content);
                                } else {
                                    $content = str_replace('{fm_child_created_at}', "", $content);
                                }
                            }

                            if ($this->find('{fm_child_updated_at}', $content)) {
                                if ($linked_application->getDateOfResponse()) {
                                    $content = str_replace('{fm_child_updated_at}', date('d F Y', strtotime(substr($linked_application->getDateOfResponse(), 0, 11))), $content);
                                } else {
                                    $content = str_replace('{fm_child_updated_at}', "", $content);
                                }
                            }

                            //START CHILD ELEMENTS
                            $childs = $child_element->getElementTotalChild();
                            if ($childs == 0) {
                                if ($child_element->getElementType() == "select") {
                                    if ($child_element->getElementExistingForm() && $child_element->getElementExistingStage()) {

                                        $q = Doctrine_Query::create()
                                            ->from("FormEntry a")
                                            ->where("a.id = ?", $child_apform['element_' . $child_element->getElementId()])
                                            ->limit(1);
                                        $linked_grand_application = $q->fetchOne();
                                        if ($linked_grand_application) {

                                            if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                                $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $linked_grand_application->getApplicationId(), $content);
                                            }

                                            $q = Doctrine_Query::create()
                                                ->from('apFormElements a')
                                                ->where('a.form_id = ?', $child_element->getElementExistingForm())
                                                ->andWhere('a.element_status = ?', 1);

                                            $grand_child_elements = $q->execute();

                                            foreach ($grand_child_elements as $grand_child_element) {
                                                $sql = "SELECT * FROM ap_form_" . $child_element->getElementExistingForm() . " WHERE id = ?";
                                                $params = array($linked_grand_application->getEntryId());
                                                $sth = mf_do_query($sql, $params, $dbh);

                                                $grand_child_apform = mf_do_fetch_result($sth);

                                                if ($this->find('{ap_grand_child_application_id}', $content)) {
                                                    $content = str_replace('{ap_grand_child_application_id}', $linked_grand_application->getApplicationId(), $content);
                                                }

                                                if ($this->find('{fm_grand_child_created_at}', $content)) {
                                                    if ($linked_grand_application->getDateOfSubmission()) {
                                                        $content = str_replace('{fm_grand_child_created_at}', date('d F Y', strtotime($linked_grand_application->getDateOfSubmission())), $content);
                                                    } else {
                                                        $content = str_replace('{fm_grand_child_created_at}', "", $content);
                                                    }
                                                }

                                                if ($this->find('{fm_grand_child_updated_at}', $content)) {
                                                    if ($linked_grand_application->getDateOfResponse()) {
                                                        $content = str_replace('{fm_grand_child_updated_at}', date('d F Y', strtotime(substr($linked_grand_application->getDateOfResponse(), 0, 11))), $content);
                                                    } else {
                                                        $content = str_replace('{fm_grand_child_updated_at}', "", $content);
                                                    }
                                                }

                                                //START GRAND CHILD ELEMENTS
                                                $childs = $grand_child_element->getElementTotalChild();
                                                if ($childs == 0) {
                                                    if ($grand_child_element->getElementType() == "select") { //select
                                                        if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                            $opt_value = 0;
                                                            if ($grand_child_apform['element_' . $grand_child_element->getElementId()] == "0") {
                                                                $opt_value++;
                                                            } else {
                                                                $opt_value = $grand_child_apform['element_' . $grand_child_element->getElementId()];
                                                            }

                                                            $q = Doctrine_Query::create()
                                                                ->from('ApElementOptions a')
                                                                ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($child_element->getElementExistingForm(), $grand_child_element->getElementId(), $opt_value))
                                                                ->limit(1);
                                                            $option = $q->fetchOne();

                                                            if ($option) {
                                                                $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $option->getOptionText(), $content);
                                                            }
                                                        }
                                                    } elseif ($grand_child_element->getElementType() == "checkbox") {
                                                        if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                            $q = Doctrine_Query::create()
                                                                ->from('ApElementOptions a')
                                                                ->where('a.form_id = ? AND a.element_id = ?', array($child_element->getElementExistingForm(), $grand_child_element->getElementId()));
                                                            $options = $q->execute();

                                                            $last_option = "";

                                                            $options_text = "";
                                                            foreach ($options as $option) {
                                                                if ($grand_child_apform['element_' . $grand_child_element->getElementId() . "_" . $option->getOptionId()]) {
                                                                    $options_text .= "" . $option->getOptionText() . "";
                                                                }
                                                                $last_option = $option->getOptionText();
                                                            }

                                                            //if other option is filled then get last option and increment by 1
                                                            if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '_other}', $content)) {
                                                                //Ensure other field is not empty before adding option
                                                                if ($grand_child_apform['element_' . $grand_child_element->getElementId() . '_other']) {
                                                                    $options_text .= "I";
                                                                }
                                                            }


                                                            $options_text .= "";

                                                            $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . "}", $options_text, $content);
                                                        }

                                                        if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '_other}', $content)) {
                                                            $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . "_other}", $grand_child_apform['element_' . $grand_child_element->getElementId() . "_other"], $content);
                                                        }
                                                    } else { //text
                                                        if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                            $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $grand_child_apform['element_' . $grand_child_element->getElementId()], $content);
                                                        }
                                                    }
                                                } else {
                                                    for ($x = 0; $x < ($childs + 1); $x++) {
                                                        if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                            $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . "}", $grand_child_apform['element_' . $grand_child_element->getElementId() . "_" . ($x + 1)], $content);
                                                        }
                                                    }
                                                }
                                                //END GRAND CHILD ELEMENTS
                                            }
                                        }
                                    } else { //select
                                        if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                            $opt_value = 0;
                                            if ($child_apform['element_' . $child_element->getElementId()] == "0") {
                                                $opt_value++;
                                            } else {
                                                $opt_value = $child_apform['element_' . $child_element->getElementId()];
                                            }

                                            $q = Doctrine_Query::create()
                                                ->from('ApElementOptions a')
                                                ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($linked_application->getFormId(), $child_element->getElementId(), $opt_value))
                                                ->limit(1);
                                            $option = $q->fetchOne();

                                            if ($option) {
                                                $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $option->getOptionText(), $content);
                                            }
                                        }
                                    }
                                } elseif ($child_element->getElementType() == "checkbox") {
                                    if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                        $q = Doctrine_Query::create()
                                            ->from('ApElementOptions a')
                                            ->where('a.form_id = ? AND a.element_id = ?', array($linked_application->getFormId(), $child_element->getElementId()));
                                        $options = $q->execute();

                                        $options_text = "";
                                        foreach ($options as $option) {
                                            if ($child_apform['element_' . $child_element->getElementId() . "_" . $option->getOptionId()]) {
                                                $options_text .= "" . $option->getOptionText() . ",";
                                            }
                                        }
                                        $options_text .= "";

                                        $content = str_replace('{fm_child_element_' . $child_element->getElementId() . "}", $options_text, $content);
                                    }
                                    continue;
                                } else { //text
                                    if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                        if ($child_apform['element_' . $child_element->getElementId()]) {
                                            $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $child_apform['element_' . $child_element->getElementId()], $content);
                                        } else {
                                            $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $child_apform['element_' . $child_element->getElementId() . '_1'], $content);
                                        }
                                    }
                                }
                            } else {
                                for ($x = 0; $x < ($childs + 1); $x++) {
                                    if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                        $content = str_replace('{fm_child_element_' . $child_element->getElementId() . "}", $child_apform['element_' . $child_element->getElementId() . "_" . ($x + 1)], $content);
                                    }
                                }
                            }
                            //END CHILD ELEMENTS
                        }


                        continue;
                    }
                } elseif ($element->getElementTableName()) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . '}', $apform['element_' . $element->getElementId()], $content);
                } else {
                    if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                        $opt_value = 0;
                        if ($apform['element_' . $element->getElementId()] == "0") {
                            $opt_value++;
                        } else {
                            $opt_value = $apform['element_' . $element->getElementId()];
                        }


                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                        }
                    }
                }
            }

            $childs = $element->getElementTotalChild();

            if ($childs == 0) {
                if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                    if ($element->getElementType() == "date") {
                        $date = "";
                        if ($apform['element_' . $element->getElementId()]) {
                            $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                        }
                        $content = str_replace('{fm_element_' . $element->getElementId() . '}', $date, $content);
                    } elseif ($element->getElementType() == "europe_date") {
                        $date = "";
                        if ($apform['element_' . $element->getElementId()]) {
                            $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                        }
                        $content = str_replace('{fm_element_' . $element->getElementId() . '}', $date, $content);
                    } elseif ($element->getElementType() == "select" || $element->getElementType() == "radio") {
                        $opt_value = $apform['element_' . $element->getElementId()];

                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", $option->getOptionText(), $content);
                        } {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", "-", $content);
                        }
                    } else {
                        $content = str_replace('{fm_element_' . $element->getElementId() . '}', $apform['element_' . $element->getElementId()], $content);
                    }
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        }
                    }
                }
            } else {

                if ($element->getElementType() == "select") {
                    if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                        $opt_value = 0;
                        if ($apform['element_' . $element->getElementId()] == "0") {
                            $opt_value++;
                        } else {
                            $opt_value = $apform['element_' . $element->getElementId()];
                        }


                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                        }
                    }
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        }
                    }
                }
            }
            for ($x = 0; $x < ($childs + 1); $x++) {
                if ($this->find('{fm_element_' . $element->getElementId() . "_" . ($x + 1) . '}', $content)) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "_" . ($x + 1) . '}', $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                }
            }
        }

        //Comment Sheets
        $q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.application_id = ?", $saved_application->getId());
        $tasks = $q->execute();

        foreach ($tasks as $task) {
            $q = Doctrine_Query::create()
                ->from("TaskForms a")
                ->where("a.task_id = ?", $task->getId())
                ->limit(1);
            $taskform = $q->fetchOne();
            if ($taskform) {
                $sql = "SELECT * FROM ap_form_" . $taskform->getFormId() . " WHERE id = ?";
                $params = array($taskform->getEntryId());
                $sth = mf_do_query($sql, $params, $dbh);

                $apform = mf_do_fetch_result($sth);

                $q = Doctrine_Query::create()
                    ->from('apFormElements a')
                    ->where('a.form_id = ?', $taskform->getFormId());

                $elements = $q->execute();

                foreach ($elements as $element) {
                    if ($element->getElementType() == "simple_name") {
                        if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"], $content);
                        }
                        continue;
                    }
                    if ($element->getElementType() == "simple_name_wmiddle") {
                        if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . "_3"], $content);
                        }
                        continue;
                    }
                    if ($element->getElementType() == "checkbox") {
                        if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $taskform->getFormId() . '}', $content)) {
                            $q = Doctrine_Query::create()
                                ->from('ApElementOptions a')
                                ->where('a.form_id = ? AND a.element_id = ?', array($taskform->getFormId(), $element->getElementId()));
                            $options = $q->execute();

                            $options_text = "";
                            foreach ($options as $option) {
                                if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                                    $options_text .= "" . $option->getOptionText() . ",";
                                }
                            }
                            $options_text .= "";

                            $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "}", $options_text, $content);
                        }
                        continue;
                    }

                    if ($element->getElementType() == "select") {
                        $opt_value = 0;
                        if ($apform['element_' . $element->getElementId()] == "0") {
                            $opt_value++;
                        } else {
                            $opt_value = $apform['element_' . $element->getElementId()];
                        }


                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($taskform->getFormId(), $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                        }
                    }


                    $childs = $element->getElementTotalChild();

                    if ($childs == 0) {
                        if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $apform['element_' . $element->getElementId()], $content);
                        } else {
                            for ($x = 0; $x < ($childs + 1); $x++) {
                                if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $content)) {
                                    $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                                }
                            }
                        }
                    } else {

                        if ($element->getElementType() == "select") {
                            if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $content)) {
                                $opt_value = 0;
                                if ($apform['element_' . $element->getElementId()] == "0") {
                                    $opt_value++;
                                } else {
                                    $opt_value = $apform['element_' . $element->getElementId()];
                                }


                                $q = Doctrine_Query::create()
                                    ->from('ApElementOptions a')
                                    ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($taskform->getFormId(), $element->getElementId(), $opt_value))
                                    ->limit(1);
                                $option = $q->fetchOne();

                                if ($option) {
                                    $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                                }
                            }
                        } else {
                            for ($x = 0; $x < ($childs + 1); $x++) {
                                if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . '}', $content)) {
                                    $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                                }
                            }
                        }
                    }
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        if ($this->find('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "_" . ($x + 1) . '}', $content)) {
                            $content = str_replace('{fm_c' . $taskform->getFormId() . '_element_' . $element->getElementId() . "_" . ($x + 1) . '}', $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        }
                    }
                }
            }
        }

        //Get Conditions of Approval (anything starting with ca_ )
        //ca_conditions
        if ($this->find('{ca_conditions}', $content)) {
            $content = str_replace('{ca_conditions}', $conditions, $content);
        }

        if ($this->find('{uuid}', $content)) {
            if (empty($saved_permit->getRemoteUpdateUuid())) {
                $permit_manager = new PermitManager();
                $uuid = $permit_manager->generate_uuid();
                error_log("UUID Log: " . $uuid);

                $sql = "UPDATE saved_permit SET remote_update_uuid = '" . $uuid . "' WHERE id = " . $saved_permit->getId();
                $sth = mf_do_query($sql, array(), $dbh);
            }

            $content = str_replace('{uuid}', $saved_permit->getRemoteUpdateUuid(), $content);
        }

        if ($this->find('{inv_total}', $content)) {
            $inv_total_amount = 0;

            $invoices = $saved_application->getMfInvoiceArchive();
            foreach ($invoices as $invoice) {
                $inv_total_amount = 0;

                $q = Doctrine_Query::create()
                    ->from('mfInvoiceDetailArchive a')
                    ->where('a.invoice_id = ?', $invoice->getId());
                $details = $q->execute();

                foreach ($details as $detail) {
                    if ($detail->getDescription() != "Total" && $detail->getDescription() != "Convenience fee") {
                        $inv_total_amount = $inv_total_amount + $detail->getAmount();
                    }
                }
            }

            $content = str_replace('{inv_total}', $inv_total_amount, $content);
        }



        $content = str_replace('First option', "", $content);

        return $content;
    }

    /***
     * Retrieves all the user details as an array
     *
     * @param $user_id the id of the user
     * @return array
     */
    function getUserDetails($user_id)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $user = Doctrine_Core::getTable("SfGuardUser")->find($user_id);

        $values['sf_username'] = $user->getUsername();
        $values['sf_mobile'] = $user->getSfGuardUserProfile()->getMobile();
        $values['sf_email'] = $user->getSfGuardUserProfile()->getEmail();
        $values['sf_fullname'] = $user->getSfGuardUserProfile()->getFullname();

        $q = Doctrine_Query::create()
            ->from('sfGuardUserCategories a')
            ->where('a.id = ?', $user->getSfGuardUserProfile()->getRegisteras())
            ->limit(1);
        $category = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('mfUserProfile a')
            ->where('a.user_id = ?', $user->getId())
            ->limit(1);
        $form_profile = $q->fetchOne();

        if ($category && $form_profile && $category->getFormId() != "0") {
            $values['sf_category'] = $category->getName();

            $q = Doctrine_Query::create()
                ->from('ApFormElements a')
                ->where('a.form_id = ?', $category->getFormId());
            $elements = $q->execute();

            $sql = "SELECT * FROM ap_form_" . $category->getFormId() . " WHERE id = ?";
            $params = array($form_profile->getEntryId());
            $sth = mf_do_query($sql, $params, $dbh);

            $user_profile_details = mf_do_fetch_result($sth);

            foreach ($elements as $element) {
                $childs = $element->getElementTotalChild();
                if ($childs == 0) {
                    if ($element->getElementType() == "select") {
                        if ($element->getElementSelectOptions() == "table") {
                            $query = "SELECT {$element->getElementFieldValue()}, {$element->getElementFieldName()} FROM {$element->getElementTableName()} WHERE {$element->getElementFieldValue()} = {$user_profile_details['element_' . $element->getElementId()]} limit 1";
                            $table_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                            foreach ($table_rows as $option) {
                                $values['sf_element_' . $element->getElementId()] = $option[$element->getElementFieldName()];
                            }
                        } elseif ($element->getElementSelectOptions() == 'query') {
                            $query_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($element->getElementOptionQuery());
                            foreach ($query_rows as $option) {
                                if ($option[$element->getElementFieldValue()] == $user_profile_details['element_' . $element->getElementId()]) {
                                    $values['sf_element_' . $element->getElementId()] = $option[$element->getElementFieldName()];
                                }
                            }
                        } elseif ($element->getElementSelectOptions() == 'application') {
                            $application = Doctrine_Core::getTable("FormEntry")->find($user_profile_details['element_' . $element->getElementId()]);
                            if ($application) {
                                $values['sf_element_' . $element->getElementId()] = $application->getApplicationId();
                            }
                        } else {
                            $q = Doctrine_Query::create()
                                ->from('ApElementOptions a')
                                ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($category->getFormId(), $element->getElementId(), $user_profile_details['element_' . $element->getElementId()]))
                                ->limit(1);
                            $option = $q->fetchOne();

                            if ($option) {
                                $values['sf_element_' . $element->getElementId()] = $option->getOptionText();
                            }
                        }
                    } else {
                        $values['sf_element_' . $element->getElementId()] = $user_profile_details['element_' . $element->getElementId()];

                        for ($x = 1; $x < 9; $x++) {
                            if ($user_profile_details['element_' . $element->getElementId() . '_' . $x]) {
                                $values['sf_element_' . $element->getElementId() . '_' . $x] = $user_profile_details['element_' . $element->getElementId() . '_' . $x];
                            }
                        }
                    }
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        $values['sf_element_' . $element->getElementId()] = $user_profile_details['element_' . $element->getElementId() . "_" . ($x + 1)];
                    }
                }
            }
        }

        return $values;
    }


    /***
     * Retrieves all the user details as an array
     *
     * @param $user_id the id of the user
     * @return array
     */
    function getRemoteUserDetails($user_id)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $user = Doctrine_Core::getTable("SfGuardUser")->find($user_id);

        $values['sf_username'] = $user->getUsername();
        $values['sf_mobile'] = $user->getSfGuardUserProfile()->getMobile();
        $values['sf_email'] = $user->getSfGuardUserProfile()->getEmail();
        $values['sf_fullname'] = $user->getSfGuardUserProfile()->getFullname();

        //Handle quotes (')
        $values['sf_fullname'] = str_replace("'", "", $values['sf_fullname']);

        $q = Doctrine_Query::create()
            ->from('sfGuardUserCategories a')
            ->where('a.id = ?', $user->getSfGuardUserProfile()->getRegisteras())
            ->limit(1);
        $category = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from('mfUserProfile a')
            ->where('a.user_id = ?', $user->getId())
            ->limit(1);
        $form_profile = $q->fetchOne();

        if ($category && $form_profile && $category->getFormId() != "0") {
            $q = Doctrine_Query::create()
                ->from('apFormElements a')
                ->where('a.form_id = ?', $category->getFormId());

            $elements = $q->execute();

            $sql = "SELECT * FROM ap_form_" . $category->getFormId() . " WHERE id = ?";
            $params = array($form_profile->getEntryId());
            $sth = mf_do_query($sql, $params, $dbh);

            $user_profile_details = mf_do_fetch_result($sth);

            foreach ($elements as $element) {
                $childs = $element->getElementTotalChild();
                if ($childs == 0) {
                    $values['sf_element_' . $element->getElementId()] = $user_profile_details['element_' . $element->getElementId()];
                } else {
                    if ($element->getElementType() == "select") {
                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($category->getFormId(), $element->getElementId(), $user_profile_details['element_' . $element->getElementId()]))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $values['sf_element_' . $element->getElementId()] = $option->getOptionText();
                        }
                    } else {
                        for ($x = 0; $x < ($childs + 1); $x++) {
                            $values['sf_element_' . $element->getElementId()] = $user_profile_details['element_' . $element->getElementId() . "_" . ($x + 1)];
                        }
                    }
                }
            }
        }

        return $values;
    }

    /***
     * Retrieves all the application details as an array
     *
     * @param $application_id the id of the application (form_entry)
     * @return array
     */
    function getApplicationDetails($application_id, $prefix = "")
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        if (empty($application)) {
            $application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);
            if (empty($application)) {
                error_log("Debug-t: Could not find linked application " . $application_id . " for prefix " . $prefix);
                return $values;
            }
        }

        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        $values['ap' . $prefix . '_application_id'] = $application->getApplicationId();
        $values['ap' . $prefix . '_merchant_identifier'] = $application->getMerchantIdentifier();
        $values['fm_id'] = $application->getId();
        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1

        if ($application->getDateOfSubmission()) {
            $values['fm' . $prefix . '_created_at'] = date('d F Y', strtotime($application->getDateOfSubmission()));
        } else {
            $values['fm' . $prefix . '_created_at'] = "";
        }


        if ($application->getDateOfResponse()) {
            $values['fm' . $prefix . '_updated_at'] = date('d F Y', strtotime(substr($application->getDateOfResponse(), 0, 11)));
        } else {
            $values['fm' . $prefix . '_updated_at'] = "";
        }

        $values['current_date' . $prefix] = date('d F Y', strtotime(date('Y-m-d')));

        // if($application->getFormData())
        // {
        //     $application_json = html_entity_decode($application->getFormData());

        //     $application_data = json_decode($application_json, true);

        //     foreach($application_data as $row)
        //     {
        //         $values['fm_element_'.$row['element_id']] = $row['value']; 
        //     }
        //}
        //else 
        //{
        $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = ?";
        $params = array($application->getEntryId());
        $sth = mf_do_query($sql, $params, $dbh);

        $apform = mf_do_fetch_result($sth);

        $q = Doctrine_Query::create()
            ->from('ApFormElements a')
            ->where('a.form_id = ?', $application->getFormId())
            ->andWhere("a.element_status = ?", 1);

        $elements = $q->execute();

        foreach ($elements as $element) {
            if ($element->getElementType() == "simple_name") {
                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"];
                continue;
            }

            if ($element->getElementType() == "textarea") {
                if ($element->getElementJsondef()) {
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = json_decode($apform['element_' . $element->getElementId()], true);
                    continue;
                } else {
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()];
                    continue;
                }
            }

            if ($element->getElementType() == "simple_name_wmiddle") {
                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . '_3'];
                continue;
            }

            if ($element->getElementType() == "file") {
                $q = Doctrine_Query::create()
                    ->from("ApSettings a")
                    ->where("a.id = 1")
                    ->orderBy("a.id DESC");
                $aplogo = $q->fetchOne();

                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $aplogo->getDataDirWeb() . "form_" . $application->getFormId() . "/files/" . $apform['element_' . $element->getElementId()];
                continue;
            }

            if ($element->getElementType() == "checkbox" || $element->getElementType() == "radio") {
                $q = Doctrine_Query::create()
                    ->from('ApElementOptions a')
                    ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()));
                $options = $q->execute();

                $options_text = "";
                foreach ($options as $option) {
                    if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                        $options_text .= "" . $option->getOptionText() . "";
                    }
                }


                if ($apform['element_' . $element->getElementId() . "_other"] != "") {
                    $options_text .= "" . $apform['element_' . $element->getElementId() . '_other'] . "";
                }

                $options_text .= "";

                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $options_text;
                continue;
            }

            $childs = $element->getElementTotalChild();

            if ($childs == 0) {
                if ($element->getElementType() == "date") {
                    $date = "";
                    if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                        $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                    } else {
                        $date = "";
                    }

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                } elseif ($element->getElementType() == "europe_date") {
                    $date = "";
                    if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                        $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                    } else {
                        $date = "";
                    }

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                } elseif ($element->getElementType() == "select") {
                    if ($element->getElementSelectOptions() == "table") {
                        $query = "SELECT {$element->getElementFieldValue()}, {$element->getElementFieldName()} FROM {$element->getElementTableName()} WHERE {$element->getElementFieldValue()} = {$apform['element_' . $element->getElementId()]} limit 1";
                        $table_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                        foreach ($table_rows as $option) {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = $option[$element->getElementFieldName()];
                        }
                    } elseif ($element->getElementSelectOptions() == 'query') {
                        $query_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($element->getElementOptionQuery());
                        foreach ($query_rows as $option) {
                            if ($option[$element->getElementFieldValue()] == $apform['element_' . $element->getElementId()]) {
                                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $option[$element->getElementFieldName()];
                            }
                        }
                    } elseif ($element->getElementSelectOptions() == 'application') {
                        $application_select = Doctrine_Core::getTable("FormEntry")->find($apform['element_' . $element->getElementId()]);
                        if ($application_select) {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = $application_select->getApplicationId();
                        }
                    } else {
                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($application->getFormId(), $element->getElementId(), $apform['element_' . $element->getElementId()]))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = $option->getOptionText();
                        }
                    }
                } else {
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()];
                    for ($x = 1; $x < 9; $x++) {
                        if ($apform['element_' . $element->getElementId() . '_' . $x]) {
                            $values['fm' . $prefix . '_element_' . $element->getElementId() . '_' . $x] = $apform['element_' . $element->getElementId() . '_' . $x];
                        }
                    }
                }
            } else {
                for ($x = 0; $x < ($childs + 1); $x++) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_" . ($x + 1)];
                }
            }
        }

        $invoices = $application->getMfInvoice();


        if ($invoices) {
            $total = 0;
            $currency = 'KES';
            foreach ($invoices as $invoice) {
                if ($invoice->getPaid() == 1) {
                    $currency = $invoice->getCurrency();
                    $total += $invoice->getTotalAmount();
                }
            }

            $values['inv_total'] = $currency . ' ' . $total;
            $values['in_total'] = $currency . ' ' . $total;
        }


        //}

        return $values;
    }

    /***
     * Retrieves all the application details as an array (Includes urlencode)
     *
     * @param $application_id the id of the application (form_entry)
     * @return array
     */
    function getRemoteApplicationDetails($application_id, $prefix = "")
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $skip_keys = array();

        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        if (empty($application)) {
            $application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);
            if (empty($application)) {
                error_log("Debug-t: Could not find linked application " . $application_id . " for prefix " . $prefix);
                return $values;
            }
        }

        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();

        $user = Doctrine_Core::getTable("SfGuardUser")->find($application->getUserId());

        $values['sf_username'] = $user->getUsername();
        $values['sf_mobile'] = $user->getSfGuardUserProfile()->getMobile();
        $values['sf_email'] = $user->getSfGuardUserProfile()->getEmail();
        $values['sf_fullname'] = $user->getSfGuardUserProfile()->getFullname();

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        $values['ap' . $prefix . '_application_id'] = $application->getApplicationId();

        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1

        if ($application->getDateOfSubmission()) {
            $values['fm' . $prefix . '_created_at'] = date('d F Y', strtotime($application->getDateOfSubmission()));
        } else {
            $values['fm' . $prefix . '_created_at'] = "";
        }


        if ($application->getDateOfResponse()) {
            $values['fm' . $prefix . '_updated_at'] = date('d F Y', strtotime(substr($application->getDateOfResponse(), 0, 11)));
        } else {
            $values['fm' . $prefix . '_updated_at'] = "";
        }

        $values['current_date' . $prefix] = date('d F Y', strtotime(date('Y-m-d')));

        if ($application->getFormData()) {
            $application_json = html_entity_decode($application->getFormData());

            $application_data = json_decode($application_json, true);

            foreach ($application_data as $row) {
                $values['fm_element_' . $row['element_id']] = $row['value'];
            }
        } else {
            $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = ?";
            $params = array($application->getEntryId());
            $sth = mf_do_query($sql, $params, $dbh);

            $apform = mf_do_fetch_result($sth);

            $q = Doctrine_Query::create()
                ->from('apFormElements a')
                ->where('a.form_id = ?', $application->getFormId())
                ->andWhere("a.element_status = ?", 1);

            $elements = $q->execute();

            foreach ($elements as $element) {
                if ($element->getElementType() == "simple_name") {
                    $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"];
                    continue;
                }

                if ($element->getElementType() == "textarea") {
                    if ($element->getElementJsondef()) {
                        $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId()];
                        continue;
                    } else {
                        $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId()];
                        continue;
                    }
                }

                if ($element->getElementType() == "simple_name_wmiddle") {
                    $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . '_3'];
                    continue;
                }

                if ($element->getElementType() == "file") {
                    $q = Doctrine_Query::create()
                        ->from("ApSettings a")
                        ->where("a.id = 1")
                        ->orderBy("a.id DESC");
                    $aplogo = $q->fetchOne();

                    $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $aplogo->getDataDirWeb() . "form_" . $application->getFormId() . "/files/" . $apform['element_' . $element->getElementId()];
                    continue;
                }

                if ($element->getElementType() == "checkbox") {
                    $q = Doctrine_Query::create()
                        ->from('ApElementOptions a')
                        ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()));
                    $options = $q->execute();

                    $options_text = "";
                    foreach ($options as $option) {
                        if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                            $options_text .= "" . $option->getOptionText() . "";
                        }
                    }


                    if ($apform['element_' . $element->getElementId() . "_other"] != "") {
                        $options_text .= "" . $apform['element_' . $element->getElementId() . '_other'] . "";
                    }

                    $options_text .= "";

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $options_text;
                    continue;
                }

                $childs = $element->getElementTotalChild();

                if ($childs == 0) {
                    if ($element->getElementType() == "date") {
                        $date = "";
                        if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                            $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                        } else {
                            $date = "";
                        }

                        $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                    } elseif ($element->getElementType() == "europe_date") {
                        $date = "";
                        if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                            $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                        } else {
                            $date = "";
                        }

                        $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                    } elseif ($element->getElementType() == "select" || $element->getElementType() == "radio") {
                        if ($element->getElementExistingForm() && $element->getElementExistingStage() && $element->getElementType() == "select") {
                            $application_id = $apform['element_' . $element->getElementId()];

                            if ($prefix == "_child") {
                                $child_values = $this->getApplicationDetails($application_id, "_grand_child");
                                $values = array_merge($values, $child_values);
                            } elseif ($prefix == "_grand_child") {
                                $child_values = $this->getApplicationDetails($application_id, "_great_grand_child");
                                $values = array_merge($values, $child_values);
                            } else {
                                $child_values = $this->getApplicationDetails($application_id, "_child");
                                $values = array_merge($values, $child_values);
                            }
                        } else {
                            $opt_value = $apform['element_' . $element->getElementId()];

                            $q = Doctrine_Query::create()
                                ->from('ApFormElements a')
                                ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()))
                                ->limit(1);
                            $element = $q->fetchOne();

                            if ($element->getElementTableName()) {
                                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $opt_value;
                            } else {
                                $q = Doctrine_Query::create()
                                    ->from('ApElementOptions a')
                                    ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                                    ->limit(1);
                                $option = $q->fetchOne();

                                if ($option) {
                                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $option->getOptionText();
                                } else {
                                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = "-";
                                }
                            }
                        }
                    } else {
                        $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()]; //." ";
                    }
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_" . ($x + 1)];
                    }
                }
            }
        }

        $invoices = $application->getMfInvoice();

        foreach ($invoices as $invoice) {
            if ($invoice->getPaid() == 2) {
                //Invoice Details
                $invoice_details = $this->getInvoiceDetails($invoice->getId());
                $values = array_merge($values, $invoice_details);
            }
        }

        //URL Encode all values in this array
        foreach ($values as $key => $value) {
            $values[$key] = urlencode($value);
        }

        return $values;
    }

    /***
     * Retrieves all the application details as an array for remote push
     *
     * @param $application_id the id of the application (form_entry)
     * @return array
     */
    function getApplicationRemoteDetails($application_id, $prefix = "")
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        if (empty($application)) {
            $application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);
            if (empty($application)) {
                error_log("Debug-t: Could not find linked application " . $application_id . " for prefix " . $prefix);
                return $values;
            }
        }

        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        $values['ap' . $prefix . '_application_id'] = $application->getApplicationId();

        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1

        if ($application->getDateOfSubmission()) {
            $values['fm' . $prefix . '_created_at'] = date('d F Y', strtotime($application->getDateOfSubmission()));
        } else {
            $values['fm' . $prefix . '_created_at'] = "";
        }


        if ($application->getDateOfResponse()) {
            $values['fm' . $prefix . '_updated_at'] = date('d F Y', strtotime(substr($application->getDateOfResponse(), 0, 11)));
        } else {
            $values['fm' . $prefix . '_updated_at'] = "";
        }

        $values['current_date' . $prefix] = date('d F Y', strtotime(date('Y-m-d')));

        $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = ?";
        $params = array($application->getEntryId());
        $sth = mf_do_query($sql, $params, $dbh);

        $apform = mf_do_fetch_result($sth);

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $application->getFormId())
            ->andWhere("a.element_status = ?", 1);

        $elements = $q->execute();

        foreach ($elements as $element) {
            if ($element->getElementType() == "simple_name") {
                $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"];
                continue;
            }

            if ($element->getElementType() == "textarea") {
                if ($element->getElementJsondef()) {
                    $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = urlencode($apform['element_' . $element->getElementId()]);
                    continue;
                } else {
                    $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId()];
                    continue;
                }
            }

            if ($element->getElementType() == "simple_name_wmiddle") {
                $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . '_3'];
                continue;
            }

            if ($element->getElementType() == "file") {
                $q = Doctrine_Query::create()
                    ->from("ApSettings a")
                    ->where("a.id = 1")
                    ->orderBy("a.id DESC");
                $aplogo = $q->fetchOne();

                $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $aplogo->getDataDirWeb() . "form_" . $application->getFormId() . "/files/" . $apform['element_' . $element->getElementId()];
                continue;
            }

            if ($element->getElementType() == "checkbox") {
                $q = Doctrine_Query::create()
                    ->from('ApElementOptions a')
                    ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()));
                $options = $q->execute();

                $options_text = "";
                foreach ($options as $option) {
                    if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                        $options_text .= "" . $option->getOptionText() . "";
                    }
                }


                if ($apform['element_' . $element->getElementId() . "_other"] != "") {
                    $options_text .= "" . $apform['element_' . $element->getElementId() . '_other'] . "";
                }

                $options_text .= "";

                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $options_text;
                continue;
            }

            $childs = $element->getElementTotalChild();

            if ($childs == 0) {
                if ($element->getElementType() == "date") {
                    $date = "";
                    if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                        $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                    } else {
                        $date = "";
                    }

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                } elseif ($element->getElementType() == "europe_date") {
                    $date = "";
                    if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                        $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                    } else {
                        $date = "";
                    }

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                } elseif ($element->getElementType() == "select" || $element->getElementType() == "radio") {
                    if ($element->getElementExistingForm() && $element->getElementExistingStage() && $element->getElementType() == "select") {
                        $application_id = $apform['element_' . $element->getElementId()];

                        if ($prefix == "_child") {
                            $child_values = $this->getApplicationDetails($application_id, "_grand_child");
                            $values = array_merge($values, $child_values);
                        } elseif ($prefix == "_grand_child") {
                            $child_values = $this->getApplicationDetails($application_id, "_great_grand_child");
                            $values = array_merge($values, $child_values);
                        } else {
                            $child_values = $this->getApplicationDetails($application_id, "_child");
                            $values = array_merge($values, $child_values);
                        }
                    } else {
                        $opt_value = $apform['element_' . $element->getElementId()];

                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = $option->getOptionText();
                        } else {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = "-";
                        }
                    }
                } else {
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()]; //." ";
                }
            } else {
                for ($x = 0; $x < ($childs + 1); $x++) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_" . ($x + 1)];
                }
            }
        }

        $invoices = $application->getMfInvoice();

        foreach ($invoices as $invoice) {
            if ($invoice->getPaid() == 2) {
                //Invoice Details
                $invoice_details = $this->getInvoiceDetails($invoice->getId());
                $values = array_merge($values, $invoice_details);
            }
        }

        return $values;
    }

    /***
     * Retrieves all the application details as an array
     *
     * @param $application_id the id of the application (form_entry)
     * @return array
     */
    function getArchiveApplicationDetails($application_id, $prefix = "")
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);

        if (empty($application)) {
            error_log("Debug-t: Could not find linked application " . $application_id . " for prefix " . $prefix);
            return $values;
        }

        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        $values['ap' . $prefix . '_application_id'] = $application->getApplicationId();

        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1

        if ($application->getDateOfSubmission()) {
            $values['fm' . $prefix . '_created_at'] = date('d F Y', strtotime($application->getDateOfSubmission()));
        } else {
            $values['fm' . $prefix . '_created_at'] = "";
        }


        if ($application->getDateOfResponse()) {
            $values['fm' . $prefix . '_updated_at'] = date('d F Y', strtotime(substr($application->getDateOfResponse(), 0, 11)));
        } else {
            $values['fm' . $prefix . '_updated_at'] = "";
        }

        $values['current_date' . $prefix] = date('d F Y', strtotime(date('Y-m-d')));

        $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = ?";
        $params = array($application->getEntryId());
        $sth = mf_do_query($sql, $params, $dbh);

        $apform = mf_do_fetch_result($sth);

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $application->getFormId())
            ->andWhere("a.element_status = ?", 1);

        $elements = $q->execute();

        foreach ($elements as $element) {
            if ($element->getElementType() == "simple_name") {
                $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"];
                continue;
            }

            if ($element->getElementType() == "simple_name_wmiddle") {
                $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . '_3'];
                continue;
            }

            if ($element->getElementType() == "file") {
                $q = Doctrine_Query::create()
                    ->from("ApSettings a")
                    ->where("a.id = 1")
                    ->orderBy("a.id DESC");
                $aplogo = $q->fetchOne();

                $values['fm' . $prefix . '_element_' . $element->getElementId() . $prefix] = $aplogo->getDataDirWeb() . "form_" . $application->getFormId() . "/files/" . $apform['element_' . $element->getElementId()];
                continue;
            }

            if ($element->getElementType() == "checkbox") {
                $q = Doctrine_Query::create()
                    ->from('ApElementOptions a')
                    ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()));
                $options = $q->execute();

                $options_text = "";
                foreach ($options as $option) {
                    if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                        $options_text .= "" . $option->getOptionText() . "";
                    }
                }


                if ($apform['element_' . $element->getElementId() . "_other"] != "") {
                    $options_text .= "" . $apform['element_' . $element->getElementId() . '_other'] . "";
                }

                $options_text .= "";

                $values['fm' . $prefix . '_element_' . $element->getElementId()] = $options_text;
                continue;
            }

            $childs = $element->getElementTotalChild();

            if ($childs == 0) {
                if ($element->getElementType() == "date") {
                    $date = "";
                    if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                        $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                    } else {
                        $date = "";
                    }

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                } elseif ($element->getElementType() == "europe_date") {
                    $date = "";
                    if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                        $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                    } else {
                        $date = "";
                    }

                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $date;
                } elseif ($element->getElementType() == "select" || $element->getElementType() == "radio") {
                    if ($element->getElementExistingForm() && $element->getElementExistingStage() && $element->getElementType() == "select") {
                        $application_id = $apform['element_' . $element->getElementId()];

                        if ($prefix == "_child") {
                            $child_values = $this->getApplicationDetails($application_id, "_grand_child");
                            $values = array_merge($values, $child_values);
                        } elseif ($prefix == "_grand_child") {
                            $child_values = $this->getApplicationDetails($application_id, "_great_grand_child");
                            $values = array_merge($values, $child_values);
                        } else {
                            $child_values = $this->getApplicationDetails($application_id, "_child");
                            $values = array_merge($values, $child_values);
                        }
                    } else {
                        $opt_value = $apform['element_' . $element->getElementId()];

                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = $option->getOptionText();
                        } else {
                            $values['fm' . $prefix . '_element_' . $element->getElementId()] = "-";
                        }
                    }
                } else {
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()] . " ";
                }
            } else {
                for ($x = 0; $x < ($childs + 1); $x++) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                    $values['fm' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_" . ($x + 1)];
                }
            }
        }

        return $values;
    }

    /***
     * Retrieves all the invoice details as an array
     *
     * @param $invoice_id the id of the invoice
     * @return array
     */
    function getInvoiceDetails($invoice_id)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $invoice_manager = new InvoiceManager();

        $otb_helper = new OTBHelper();

        $invoice = Doctrine_Core::getTable("MfInvoice")->find($invoice_id);
        $application = Doctrine_Core::getTable("FormEntry")->find($invoice->getAppId());

        $sql = "select * from ap_form_payments where invoice_id = " . $invoice->getId();
        $sth = mf_do_query($sql, array(), $dbh);

        $payment_row = mf_do_fetch_result($sth);

        $inv_title = $invoice->getInvoiceNumber();
        $inv_created_at = substr($invoice->getCreatedAt(), 0, 10);
        $inv_expires_at = "";

        if ($invoice->getExpiresAt()) {
            $inv_expires_at = substr($invoice->getExpiresAt(), 0, 10);
        } else {
            $q = Doctrine_Query::create()
                ->from("Invoicetemplates a")
                ->where("a.id = ?", $invoice->getTemplateId())
                ->limit(1);
            $template = $q->fetchOne();
            if ($template) {
                if ($template->getMaxDuration() > 0) {
                    $date = strtotime("+" . $template->getMaxDuration() . " day");
                    $inv_expires_at = date('Y-m-d', $date);
                }
            }
        }

        $currency = "";

        $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $application->getFormId());
        $form = $q->fetchOne();

        if ($form) {
            $currency = $form->getPaymentCurrency();
        } else {
            $currency = sfConfig::get("app_currency");
        }

        $inv_fees = "";

        $inv_fees = $inv_fees . '
                 <table width="2000px" cellspacing="0" cellpadding="0" border-spacing="0" style="border: 1px solid #d2d2d2; margin-bottom: 20px; width:100%;">
                 <tbody><tr><th width="20%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; text-align:left;" nowrap>Item</th><th style="padding:5px 12px; text-align:left;" nowrap>Total Amount (' . $currency . ')</th></tr>';
        $grand_total = 0;
        $inv_details = $invoice->getMfInvoiceDetail();
        $total_found = false;
        //OTB ADD
        $service_fees = [];
        $invoice_type = 'application';
        foreach ($inv_details as $inv_detail) {
            //All fees
            $desc_exploded = explode(":", $inv_detail->getDescription());
            if (count($desc_exploded) > 1) {
                $service_fees[] = ['amount' => floatval($inv_detail->getAmount()), 'serviceId' => intval($desc_exploded[0])];
                $invoice_type = "approval";
            } else {
                //placeholder for the submission fee 
                if (intval($inv_detail->getDescription())) {
                    $service_fees[] = ['amount' => floatval($inv_detail->getAmount()), 'serviceId' => $inv_detail->getDescription()];
                }
            }
            if ($inv_detail->getDescription() == "Attached Invoice") {
                $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $inv_detail->getDescription() . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
            } else {
                if ($inv_detail->getDescription() == "Choose Fee" || $inv_detail->getAmount() == "0") {
                    //Dont display
                } else {
                    $pieces = explode(":", $inv_detail->getDescription());
                    if (sizeof($pieces) > 1) {
                        $description = $pieces[1];

                        $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $description . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
                    } else {
                        $inv_fees = $inv_fees . '<td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $inv_detail->getDescription() . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
                    }
                    $grand_total += $inv_detail->getAmount();
                }
            }

            if ($inv_detail->getDescription() == "Total") {
                $total_found = true;
            }
        }

        if ($total_found == false) {
            $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;"><b>Total (' . $currency . ')</b></td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . number_format($grand_total) . '</td></tr>';
        }

        $inv_fees = $inv_fees . '</tbody>
                 </table>';

        $status = "";
        $payment_date = "";

        $db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

        $db_date_event = strtotime($db_date_event);

        $plain_status = '';

        if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3")) {
            $status = '<font color="#D00000">EXPIRED</font>';
            $plain_status = 'EXPIRED';
        } else if ($invoice->getPaid() == "2") {
            $status = '<font color="#00CC00">PAID</font>';
            $plain_status = 'PAID';
            $payment_date = $payment_row['payment_date'] ? '<font color="#00CC00">' . date('Y-m-d H:i:s', strtotime($payment_row['payment_date'])) . '</font>' : '<font color="#D00000">NOT PAID</font>';
        } else if ($invoice->getPaid() == "15") {
            $status = "Part Payment";
            $plain_status = 'Part Payment';
        } else if ($invoice->getPaid() == "1") {
            $status = '<font color="#D00000">NOT PAID</font>';
            $payment_date = '<font color="#D00000">NOT PAID</font>';
            $plain_status = 'NOT PAID';
        }

        if ($invoice->getPaid() == "3") {
            $status = '<font color="#D00000">CANCELLED</font>';
        }

        $values['inv_status'] = $status;

        $values['inv_no'] = $inv_title;

        $values['in_total'] = number_format($invoice->getTotalAmount());

        $values['inv_total'] = number_format($invoice->getTotalAmount());

        $values['inv_date_created'] = date('Y-m-d', strtotime($inv_created_at));
        $values['payment_date'] = $payment_date;
        $values['jambo_pay_ref'] = $invoice->getDocRefNumber();
        $values['inv_total_words'] = $otb_helper->convert_number_to_words($values['inv_total']) . " SHILLINGS ONLY.";
        $values['inv_date_created_yyymmdd'] = date('Y-m-d H:i:s', strtotime($inv_created_at));

        if ($inv_expires_at) {
            $values['inv_expires_at'] = date('d F Y', strtotime($inv_expires_at));
        } else {
            $values['inv_expires_at'] = "";
        }

        $values['inv_fee_table'] = $inv_fees;

        $reference = $invoice->getFormEntry()->getFormId() . "/" . $invoice->getFormEntry()->getEntryId() . "/" . $invoice->getId();
        $values['inv_payment_id'] = $reference;

        $ssl_suffix = "s";

        if (empty($_SERVER['HTTPS'])) {
            $ssl_suffix = "";
        }

        $values['invoice_template_id'] = $invoice->getTemplateId();
        $values['invoice_number'] = $invoice->getInvoiceNumber();
        //require_once dirname(__FILE__).'/../web/barcode/barcode.class.php';
        //$bar	= new BARCODE();
        $qrCode = new QrCode();
        $qrCode
            ->setText($invoice->getCurrency() . ' ' . $invoice->getTotalAmount())
            ->setSize(200)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel($invoice->getInvoiceNumber())
            ->setLabelFontSize(15)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        ;
        $values['qr_code'] = '<img src="data:' . $qrCode->getContentType() . ';base64,' . $qrCode->generate() . '" />';
        $qrCode = new QrCode();
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $is_http = "https";
        } else {
            $is_http = "http";
        }
        $invoice_verification_link = "{$is_http}://{$_SERVER['HTTP_HOST']}/plan/permitchecker/invoiceRequest?invoiceref={$invoice->getId()}";
        $qrCode
            ->setText("Amount: " . $invoice->getCurrency() . ' ' . $invoice->getTotalAmount() . "\n" . "STATUS: " . $plain_status . "\n" . "VIEW MORE..." . "\n" . $invoice_verification_link)
            ->setSize(100)
            ->setPadding(5)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel($invoice->getInvoiceNumber())
            ->setLabelFontSize(10)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        $values['qr_code_small'] = '<img src="data:' . $qrCode->getContentType() . ';base64,' . $qrCode->generate() . '" />';

        $barcode = new BarcodeGenerator();
        $barcode->setText($invoice->getCurrency() . ' ' . $invoice->getTotalAmount());
        $barcode->setType(BarcodeGenerator::Code128);
        $barcode->setScale(2);
        $barcode->setThickness(25);
        $barcode->setFontSize(10);
        $code = $barcode->generate();

        $values['bar_code'] = '<img src="data:image/png;base64,' . $code . '" />';
        $barcode = new BarcodeGenerator();
        $barcode->setText($invoice->getCurrency() . ' ' . $invoice->getTotalAmount());
        $barcode->setType(BarcodeGenerator::Code128);
        $barcode->setScale(2);
        $barcode->setThickness(15);
        $barcode->setFontSize(10);
        $code = $barcode->generate();
        $values['bar_code_small'] = '<img src="data:image/png;base64,' . $code . '" />';

        $values['inv_services'] = json_encode($service_fees);
        $values['inv_invoice_type'] = $invoice_type;
        $values['inv_payment_id'] = $payment_row['payment_id'];
        $values['inv_payment_merchant_type'] = ucwords($payment_row['payment_merchant_type']);
        $values['inv_payment_billing_state'] = $payment_row['billing_state'];
        $values['inv_payment_amount'] = $payment_row['payment_amount'];
        $values['inv_payment_status'] = $payment_row['payment_status'];

        return $values;
    }

    /***
     * Retrieves all the invoice details as an array
     *
     * @param $invoice_id the id of the invoice
     * @return array
     */
    function getArchiveInvoiceDetails($invoice_id)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $values = array();

        $invoice_manager = new InvoiceManager();
        $otb_helper = new OTBHelper();

        $invoice = Doctrine_Core::getTable("MfInvoiceArchive")->find($invoice_id);
        $application = Doctrine_Core::getTable("FormEntryArchive")->find($invoice->getAppId());

        $sql = "select * from ap_form_payments where form_id = " . $application->getFormId() . " and record_id = " . $application->getEntryId();
        $sth = mf_do_query($sql, array(), $dbh);

        $payment_row = mf_do_fetch_result($sth);

        $inv_title = $invoice->getInvoiceNumber();
        $inv_created_at = substr($invoice->getCreatedAt(), 0, 10);
        $inv_expires_at = "";

        if ($invoice->getExpiresAt()) {
            $inv_expires_at = substr($invoice->getExpiresAt(), 0, 10);
        } else {
            $q = Doctrine_Query::create()
                ->from("Invoicetemplates a")
                ->where("a.applicationform = ?", $application->getFormId())
                ->limit(1);
            $template = $q->fetchOne();
            if ($template) {
                if ($template->getMaxDuration() > 0) {
                    $date = strtotime("+" . $template->getMaxDuration() . " day");
                    $inv_expires_at = date('Y-m-d', $date);
                }
            }
        }

        $currency = "";

        $q = Doctrine_Query::create()
            ->from("Invoicetemplates a")
            ->where("a.applicationform = ?", $application->getFormId())
            ->limit(1);
        $invoice_template = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $invoice_template->getApplicationform());
        $form = $q->fetchOne();

        if ($form) {
            $currency = $form->getPaymentCurrency();
        } else {
            $currency = sfConfig::get("app_currency");
        }

        $inv_fees = "";

        $inv_fees = $inv_fees . '
                 <table width="100%" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                 <tbody><tr><th width="20%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; text-align:left;" nowrap>Item</th><th style="padding:5px 12px; text-align:left;" nowrap>Total Amount (' . $currency . ')</th></tr>';
        $grand_total = 0;
        $inv_details = $invoice->getMfInvoiceDetail();
        $total_found = false;
        foreach ($inv_details as $inv_detail) {
            if ($inv_detail->getDescription() == "Attached Invoice") {
                $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $inv_detail->getDescription() . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
            } else {
                if ($inv_detail->getDescription() == "Fee" && $inv_detail->getAmount() == "0") {
                    //Dont display
                } else {
                    $pieces = explode(" - ", $inv_detail->getDescription());
                    if (sizeof($pieces) > 1) {
                        $description = $pieces[1];

                        $q = Doctrine_Query::create()
                            ->from("ApForms a")
                            ->where("a.form_code = ?", $pieces[0])
                            ->limit(1);
                        $apform = $q->fetchOne();

                        if ($apform) {
                            $description = $apform->getFormName();
                        }

                        $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $description . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
                    } else {
                        $inv_fees = $inv_fees . '<td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $inv_detail->getDescription() . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
                    }
                    $grand_total += $inv_detail->getAmount();
                }
            }

            if ($inv_detail->getDescription() == "Total") {
                $total_found = true;
            }
        }

        if ($total_found == false) {
            $currency = "";

            $q = Doctrine_Query::create()
                ->from("Invoicetemplates a")
                ->where("a.applicationform = ?", $application->getFormId())
                ->limit(1);
            $invoice_template = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from("ApForms a")
                ->where("a.form_id = ?", $invoice_template->getApplicationform());
            $form = $q->fetchOne();

            if ($form) {
                $currency = $form->getPaymentCurrency();
            } else {
                $currency = sfConfig::get("app_currency");
            }

            $inv_fees = $inv_fees . '<tr><td align="right" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;"><b>Total (' . $currency . ')</b></td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $grand_total . '</td></tr>';
        }

        $inv_fees = $inv_fees . '</tbody>
                 </table>';

        $status = "";
        $payment_date = "";

        $db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

        $db_date_event = strtotime($db_date_event);

        if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3")) {
            $status = '<font color="#D00000">Expired</font>';
        } else if ($invoice->getPaid() == "2") {
            $status = '<font color="#00CC00">Paid</font>';
            $payment_date = $payment_row['payment_date'] ? date('Y-m-d H:i:s', strtotime($payment_row['payment_date'])) : '<font color="#D00000">NOT PAID</font>';
        } else if ($invoice->getPaid() == "15") {
            $status = "Part Payment";
        } else if ($invoice->getPaid() == "1") {
            $status = '<font color="#D00000">Not Paid</font>';
            $payment_date = '<font color="#D00000">NOT PAID</font>';
        }

        if ($invoice->getPaid() == "3") {
            $status = '<font color="#D00000">Cancelled</font>';
        }

        $values['inv_status'] = $status;

        $values['inv_no'] = $inv_title;

        $values['in_total'] = $invoice->getTotalAmount();

        $values['inv_total'] = $invoice->getTotalAmount();

        $values['inv_balance'] = $invoice_manager->get_invoice_total_owed($invoice->getId());

        $values['inv_date_created'] = date('d F Y', strtotime($inv_created_at));

        $values['payment_date'] = $payment_date;
        if ($inv_expires_at) {
            $values['inv_expires_at'] = date('d F Y', strtotime($inv_expires_at));
        } else {
            $values['inv_expires_at'] = "";
        }
        $values['inv_total_words'] = $otb_helper->convert_number_to_words($values['inv_total']). " SHILLINGS ONLY.";

        $values['jambo_pay_ref'] = $invoice->getDocRefNumber();

        $values['inv_fee_table'] = $inv_fees;

        $reference = $invoice->getFormEntry()->getFormId() . "/" . $invoice->getFormEntry()->getEntryId() . "/" . $invoice->getId();
        $values['inv_payment_id'] = $reference;


        return $values;
    }

    /***
     * Retrieves all the permit details as an array
     *
     * @param $permit_id the id of the permit
     * @return array
     */
    function getPermitDetails($permit_id)
    {
        $saved_permit = Doctrine_Core::getTable("SavedPermit")->find($permit_id);

        if ($saved_permit->getDateOfIssue()) {
            $values['ap_issue_date'] = date('d F Y', strtotime($saved_permit->getDateOfIssue()));
        } else {
            $values['ap_issue_date'] = "";
        }

        $values['ap_permit_id'] = $saved_permit->getPermitId();
        $values['uuid'] = $saved_permit->getRemoteUpdateUuid();

        if (empty($saved_permit->getRemoteUpdateUuid())) {
            $permit_manager = new PermitManager();
            $values['uuid'] = $permit_manager->generate_uuid();
            error_log("UUID Log: " . $uuid);

            $saved_permit->setRemoteUpdateUuid($uuid);
            $saved_permit->save();
        } else {
            $values['uuid'] = $saved_permit->getRemoteUpdateUuid();
        }

        if ($saved_permit->getDateOfExpiry()) {
            $values['ap_expire_date'] = date('d F Y', strtotime($saved_permit->getDateOfExpiry()));
        } else {
            $values['ap_expire_date'] = "";
        }

        $ssl_suffix = "s";

        if (empty($_SERVER['HTTPS'])) {
            $ssl_suffix = "";
        }

        #require_once dirname(__FILE__).'/../web/barcode/barcode.class.php';
        #$bar	= new BARCODE();
        $qrCode = new QrCode();
        $qrCode
            ->setText("http://" . $_SERVER['HTTP_HOST'] . "/plan/permitchecker/openrequest?permitref=" . $saved_permit->getId())
            ->setSize(200)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel('Scan Qr Code')
            ->setLabelFontSize(15)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        #$qr_values[0] 	= "http://".$_SERVER['HTTP_HOST']."/plan/permitchecker/openrequest?permitref=".$saved_permit->getId();

        $values['qr_code'] = '<img src="data:' . $qrCode->getContentType() . ';base64,' . $qrCode->generate() . '" />';
        $qrCode = new QrCode();
        $qrCode
            ->setText("http://" . $_SERVER['HTTP_HOST'] . "/plan/permitchecker/openrequest?permitref=" . $saved_permit->getId())
            ->setSize(100)
            ->setPadding(5)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel('Scan Qr Code')
            ->setLabelFontSize(10)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        $values['qr_code_small'] = '<img src="data:' . $qrCode->getContentType() . ';base64,' . $qrCode->generate() . '" />';

        $barcode = new BarcodeGenerator();
        $barcode->setText($saved_permit->getFormEntry()->getApplicationId());
        $barcode->setType(BarcodeGenerator::Code128);
        $barcode->setScale(2);
        $barcode->setThickness(25);
        $barcode->setFontSize(10);
        $code = $barcode->generate();

        $values['bar_code'] = '<img src="data:image/png;base64,' . $code . '" />';

        $barcode = new BarcodeGenerator();
        $barcode->setText($saved_permit->getFormEntry()->getApplicationId());
        $barcode->setType(BarcodeGenerator::Code128);
        $barcode->setScale(2);
        $barcode->setThickness(15);
        $barcode->setFontSize(10);
        $code = $barcode->generate();
        $values['bar_code_small'] = '<img src="data:image/png;base64,' . $code . '" />';

        $q = Doctrine_Query::create()
            ->from('SfGuardUser a')
            ->where('a.id = ?', $saved_permit->getFormEntry()->getUserId());
        $user = $q->fetchOne();

        if (sfConfig::get('app_enable_categories') == "no") {
            $account_type = "";

            if ($user->getSfGuardUserProfile()->getRegisteras() == 1) {
                $account_type = "citizen";
            } elseif ($user->getSfGuardUserProfile()->getRegisteras() == 3) {
                $account_type = "alien";
            } elseif ($user->getSfGuardUserProfile()->getRegisteras() == 4) {
                $account_type = "visitor";
            }

            $values['profile_pic'] = '<img src="https://account.ecitizen.go.ke/profile-picture/' . $user->getUsername() . '?t=' . $account_type . '" width="120px" style="border-radius: 5px;border: 2px solid;">';
        }
        $conditions = "";
        $miniconditions = "";

        $invoice_total = 0;

        $q = Doctrine_Query::create()
            ->from('ApprovalCondition a')
            ->where('a.entry_id = ?', $saved_permit->getApplicationId());
        $approvalconditions = $q->execute();
        $conditions = "<ol type=\"1\">";
        foreach ($approvalconditions as $approval) {
            $conditions = $conditions . "<li>" . $approval->getCondition()->getDescription() . "</li>";
            $miniconditions = $miniconditions . $approval->getCondition()->getShortName() . ", ";
        }
        $q = Doctrine_Query::create()
            ->from('Conditions a')
            ->where('a.circulation_id = ?', $saved_permit->getFormEntry()->getCirculationId());
        $conds = $q->execute();
        foreach ($conds as $cond) {
            $conditions = $conditions . "<li>- " . $cond->getConditionText() . "</li>";
        }
        $conditions = $conditions . "</ol>";
        error_log('------------conditions-------' . $conditions);
        error_log('------------miniconditions-------' . $miniconditions);

        $values['ca_conditions'] = $conditions;
        $values['mini_ca_conditions'] = $miniconditions;


        //Get invoice total
        //$application = $saved_permit->getFormEntry();

        $values['inv_total'] = 0;

        $values['inv_first_description'] = 0;
        $values['inv_first_amount'] = 0;

        $values['inv_last_description'] = 0;
        $values['inv_last_amount'] = 0;

        //Get invoice total
        $application = $saved_permit->getFormEntry();

        $inv_count = 0;
        //OTB ADD - CHECK PERMIT EXPIRY DATE and show payment for the service
        $permit_expiry_year = date('Y', strtotime($saved_permit->getDateOfExpiry()));
        $start_of_permit_year = $permit_expiry_year . '-01-01';
        $end_of_permit_year = $permit_expiry_year . '-12-31';
        //error_log('-----Permit year of expiry---'.$permit_expiry_year.'-----Start-----'.$start_of_permit_year.'-------End-----'.$end_of_permit_year);
        $q = Doctrine_Query::create()
            ->from("MfInvoice a")
            ->where("a.app_id = ? and a.paid = ? and a.created_at >= ? and a.created_at <= ?", array($application->getId(), 2, $start_of_permit_year, $end_of_permit_year))
            ->orderBy("a.id ASC");
        error_log('----query----' . $q->count());
        $invoices = $q->execute();

        foreach ($invoices as $invoice) {
            //error_log('-------Invoice found----'.$invoice->getId());
            $inv_count++;

            /*if($invoice->getPaid() <> "2")
            {
                continue;
            }
            else
            {*/
            $values['inv_total'] = $values['inv_total'] + $invoice->getTotalAmount();
            //}

            $inv_details = $invoice->getMfInvoiceDetail();

            $inv_desc_count = 0;

            foreach ($inv_details as $inv_detail) {
                if ($this->find("Convenience", $inv_detail->getDescription())) {
                    continue;
                } elseif ($this->find("Refuse collection", $inv_detail->getDescription())) {
                    continue;
                } elseif ($this->find("Penalt", $inv_detail->getDescription())) {
                    continue;
                } elseif ($this->find("Arrear", $inv_detail->getDescription())) {
                    continue;
                } else {
                    $inv_desc_count++;

                    if ($inv_desc_count == 1) {
                        $q = Doctrine_Query::create()
                            ->from("Fee a")
                            ->where("a.id = ?", $inv_detail->getDescription());
                        $fee = $q->fetchOne();
                        if ($fee) {
                            $values['inv_first_description'] = $fee->getTitle();
                        } else {
                            $values['inv_first_description'] = $inv_detail->getDescription();
                        }
                        $values['inv_first_amount'] = $inv_detail->getAmount();
                    } else {
                        $q = Doctrine_Query::create()
                            ->from("Fee a")
                            ->where("a.id = ?", $inv_detail->getDescription());
                        $fee = $q->fetchOne();
                        if ($fee) {
                            $values['inv_last_description'] = $fee->getTitle();
                        } else {
                            $values['inv_last_description'] = $inv_detail->getDescription();
                        }
                        $values['inv_last_amount'] = $inv_detail->getAmount();
                    }
                }
                //OTB search code payment - activity name
                if ($this->find(' - ', $inv_detail->getDescription())) {
                    error_log('--------Desc found-----');
                    //explode
                    $fee_desc = explode(' - ', $inv_detail->getDescription());
                    //check for activity desc
                    if ($fee_desc[0] != '') {
                        //Explode check if first character is numeric & last string
                        error_log('--------Char----1' . substr($fee_desc[0], 0, 1));
                        error_log('--------Char-----1' . substr($fee_desc[0], -1));
                        if (is_numeric(substr($fee_desc[0], 0, 1)) && !is_numeric(substr($fee_desc[0], -1))) {
                            //new code
                            error_log('--------activity-----' . $inv_detail->getDescription());
                            $values['inv_activity_desc'] = $inv_detail->getDescription();
                            $values['inv_activity_amt'] = $inv_detail->getAmount();
                        }
                    } else {
                        if ($fee_desc[1] != '') {
                            //explode
                            $desc = explode(' ', $fee_desc[1]);
                            if ($this->find('.', $desc[0])) {
                                //old code
                                $values['inv_activity_desc'] = $inv_detail->getDescription();
                                $values['inv_activity_amt'] = $inv_detail->getAmount();
                            }
                        }
                    }
                }
            }
        }
        //OTB ADD - MIGRATED

        //$application_manager=new ApplicationManager();
        //$migrated=$application_manager->check_if_migrated($application->getEntryId());

        //Check if migrated
        /*if($migrated){
            
            //if application is migrated 
            switch($application->getFormId()){
                case 939:
                    $migrated_application=Doctrine_Query::create()->from('FormEntry e')->where('e.form_id = ? and e.entry_id = ?', array(7283,$application->getEntryId()))->fetchOne();
                    break;
                case 7283:
                    $migrated_application=Doctrine_Query::create()->from('FormEntry e')->where('e.form_id = ? and e.entry_id = ?', array(939,$application->getEntryId()))->fetchOne();
                    break;
            }
            //check if values have been filled
            foreach($migrated_application->getMfInvoice() as $invoice){
                if(strtotime($invoice->getCreatedAt()) >= strtotime($start_of_permit_year) && strtotime($invoice->getCreatedAt()) <= strtotime($end_of_permit_year)){
                    //show total of invoices together - due to clients billed on migrated app
                    $values['inv_total']+=$invoice->getTotalAmount();
                    foreach($invoice->getMfInvoiceDetail() as $inv_detail){
                        if($this->find("Convenience",$inv_detail->getDescription()))
                        {
                            continue;
                        }
                        elseif($this->find("Refuse collection",$inv_detail->getDescription()))
                        {
                            continue;
                        }
                        elseif($this->find("Penalt",$inv_detail->getDescription()))
                        {
                            continue;
                        }
                        elseif($this->find("Arrear",$inv_detail->getDescription()))
                        {
                            continue;
                        }elseif($this->find(' - ',$inv_detail->getDescription())){
                            error_log('--------Desc found-----');
                            //explode
                            $fee_desc=explode(' - ',$inv_detail->getDescription());
                            //check for activity desc
                            if(strlen($values['inv_activity_desc']) == 0 && strlen($values['inv_activity_amt']) == 0){
                                if($fee_desc[0] != ''){
                                    //Explode check if first character is numeric & last string
                                    error_log('--------Char----1'.substr($fee_desc[0],0,1));
                                    error_log('--------Char-----1'.substr($fee_desc[0],-1));
                                    if(is_numeric(substr($fee_desc[0],0,1)) && !is_numeric(substr($fee_desc[0],-1))){
                                        //new code
                                        error_log('--------activity-----'.$inv_detail->getDescription());
                                        $values['inv_activity_desc']=$inv_detail->getDescription();
                                        $values['inv_activity_amt']=$inv_detail->getAmount();
                                    }
                                }elseif($fee_desc[1] != ''){
                                    //explode
                                    $desc=explode(' ',$fee_desc[1]);
                                    if($this->find('.',$desc[0])){
                                        //old code
                                        $values['inv_activity_desc']=$inv_detail->getDescription();
                                        $values['inv_activity_amt']=$inv_detail->getAmount();
                                    }
                                }
                            }
                        }					
                        
                        
                    }
                }
            }
        }*/

        if ($values['inv_total'] > 0) {
            //Get menu id
            $menu = Doctrine_Core::getTable('Menus')->findByServiceForm($application->getFormId());
            if ($menu && $menu[0]['id']) {
                $application_manager = new ApplicationManager();
                $service_fee = $application_manager->get_service_fee_desc($menu[0]['id'], $application->getId());
                //error_log(print_r($service_fee,true));
                if (strlen($service_fee['fee_desc']) != 0 && strlen($service_fee['fee_amt']) != 0 && ($values['inv_activity_desc'] != $service_fee['fee_desc'] || $values['inv_activity_amt'] != $service_fee['fee_amt'])) {
                    //set value
                    $values['inv_activity_desc'] = $service_fee['fee_desc'];
                    $values['inv_activity_amt'] = $service_fee['fee_amt'];
                }
            }
        }

        $found_html = false;

        if ($this->find("<html", $saved_permit->getRemoteResult())) {
            $found_html = true;
        }

        //Check if permit has a saved_result, if it does then try parsing its variables
        if ($saved_permit->getRemoteResult() && strlen($saved_permit->getRemoteResult()) > 10 && $found_html == false) {
            if ($saved_permit->getRemoteResult() == '{"message":"Cannot update more than one record"}') {
                $permit_manager = new PermitManager();

                $results = $permit_manager->get_remote_result($saved_permit->getId());

                $remote_values = json_decode($results, true); // decode to arrays not stdclass to allow parsing nested loops

                $values = array_merge($values, $remote_values['records'][0]);
            } elseif ($saved_permit->getRemoteResult() == '"Provide a uuid when creating a new record"') {
            } elseif ($saved_permit->getRemoteResult() == '"The server is under maintenance, please check again ina few minutes."') {
            } elseif (strlen($saved_permit->getRemoteResult()) > 5000) {
            } else {
                $results = $saved_permit->getRemoteResult();

                $remote_values = json_decode($results, true); // decode to arrays not stdclass to allow parsing nested loops

                $remote_values = json_decode($remote_values, true);

                if (!is_array($remote_values)) {
                    $remote_values = json_decode($remote_values, true);
                }

                error_log("Remote Values " . $saved_permit->getId() . ": " . $results);

                if ($remote_values == null) {
                    $results = $saved_permit->getRemoteResult();
                    $remote_values = json_decode($results, true);

                    if (is_array($remote_values['records'])) {
                        foreach ($remote_values['records'] as $record) {
                            $values = array_merge($values, $record);
                        }
                    } elseif (is_array($remote_values)) {
                        $values = array_merge($values, $remote_values);
                    } else {
                        error_log("Remote Details: No Array Found.");
                    }


                    if (strlen($saved_permit->getRemoteResult()) > 5 && sfContext::getInstance()->getUser()->getAttribute('userid') == 1) {
                        echo "<pre>Remote details: ", var_dump($saved_permit->getRemoteResult()), "</pre>";
                    }
                } else {
                    $values = array_merge($values, $remote_values);

                    if (strlen($saved_permit->getRemoteResult()) > 5 && sfContext::getInstance()->getUser()->getAttribute('userid') == 1) {
                        echo "<pre>Error in remote details: ", var_dump($saved_permit->getRemoteResult()), "</pre>";

                        echo "<pre>Attempted decode values: ", var_dump($values), "</pre>";

                        error_log("Remote Details: " . $saved_permit->getRemoteResult());
                    }
                }
            }
        }

        //If observation has a json result then try and parse it
        if ($saved_permit->getFormEntry()->getObservation()) {
            $json_array = json_decode($saved_permit->getFormEntry()->getObservation(), true);
            if (is_array($json_array)) {
                $values = array_merge($values, $json_array);
            }
        }

        return $values;
    }

    /***
     * Retrieves all the permit details as an array
     *
     * @param $permit_id the id of the permit
     * @return array
     */
    function getArchivePermitDetails($permit_id)
    {
        $saved_permit = Doctrine_Core::getTable("SavedPermitArchive")->find($permit_id);

        if ($saved_permit->getDateOfIssue()) {
            $values['ap_issue_date'] = date('d F Y', strtotime($saved_permit->getDateOfIssue()));
        } else {
            $values['ap_issue_date'] = "";
        }

        $values['ap_permit_id'] = $saved_permit->getPermitId();
        $values['uuid'] = $saved_permit->getRemoteUpdateUuid();

        if (empty($saved_permit->getRemoteUpdateUuid())) {
            $permit_manager = new PermitManager();
            $values['uuid'] = $permit_manager->generate_uuid();
            error_log("UUID Log: " . $uuid);

            $saved_permit->setRemoteUpdateUuid($uuid);
            $saved_permit->save();
        } else {
            $values['uuid'] = $saved_permit->getRemoteUpdateUuid();
        }

        if ($saved_permit->getDateOfExpiry()) {
            $values['ap_expire_date'] = date('d F Y', strtotime($saved_permit->getDateOfExpiry()));
        } else {
            $values['ap_expire_date'] = "";
        }

        $q = Doctrine_Query::create()
            ->from('SfGuardUser a')
            ->where('a.id = ?', $saved_permit->getFormEntry()->getUserId());
        $user = $q->fetchOne();

        if (sfConfig::get('app_enable_categories') == "no") {
            $account_type = "";

            if ($user->getSfGuardUserProfile()->getRegisteras() == 1) {
                $account_type = "citizen";
            } elseif ($user->getSfGuardUserProfile()->getRegisteras() == 3) {
                $account_type = "alien";
            } elseif ($user->getSfGuardUserProfile()->getRegisteras() == 4) {
                $account_type = "visitor";
            }

            $values['profile_pic'] = '<img src="https://account.ecitizen.go.ke/profile-picture/' . $user->getUsername() . '?t=' . $account_type . '" width="120px" style="border-radius: 5px;border: 2px solid;">';
        }

        //Get invoice total
        $application = $saved_permit->getFormEntry();

        foreach ($application->getMfInvoiceArchive() as $invoice) {
            if ($invoice->getPaid() == "2") {
                $values['inv_total'] = $invoice->getTotalAmount();
            }
        }

        //Check if permit has a saved_result, if it does then try parsing its variables
        if ($saved_permit->getRemoteResult() && strlen($saved_permit->getRemoteResult()) > 10) {
            if ($saved_permit->getRemoteResult() == '{"message":"Cannot update more than one record"}') {
                $permit_manager = new PermitManager();

                $results = $permit_manager->get_remote_result($saved_permit->getId());

                $remote_values = json_decode($results, true); // decode to arrays not stdclass to allow parsing nested loops

                $values = array_merge($values, $remote_values['records'][0]);
            } elseif ($saved_permit->getRemoteResult() == '"Provide a uuid when creating a new record"') {
            } elseif ($saved_permit->getRemoteResult() == '"The server is under maintenance, please check again ina few minutes."') {
            } elseif (strlen($saved_permit->getRemoteResult()) > 5000) {
            } else {
                $results = $saved_permit->getRemoteResult();

                $remote_values = json_decode($results, true); // decode to arrays not stdclass to allow parsing nested loops

                $remote_values = json_decode($remote_values, true);

                if (!is_array($remote_values)) {
                    $remote_values = json_decode($remote_values, true);
                }

                error_log("Remote Values: " . $results);
                $values = array_merge($values, $remote_values);
            }
        }

        return $values;
    }

    /***
     * Used to parse values into a user string e.g automatic submission
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $user_id the id of the user
     * @return string
     */
    public function parseUser($user_id, $content)
    {
        //User Details
        $user_details = $this->getUserDetails($user_id);

        $content = static::parseWithDust($content, $user_details);

        return $content;
    }

    public function cleanInvoiceQrCode($str)
    {
        $str = utf8_decode($str);
        $str = str_replace("&nbsp;", "", $str);
        $str = preg_replace("/\s+/", " ", $str);
        $str = preg_replace("/<br\W*?\/>/", "\n", $str);
        $str = trim($str);
        return $str;
    }

    /***
     * Used to parse values into an invoice template
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $application_id the id of the application
     * @param $form_id the id of the form
     * @param $entry_id the id of the entry in the form table
     * @param $invoice_id the id of the invoice
     * @return string
     */
    public function parseInvoice($application_id, $form_id, $entry_id, $invoice_id, $content)
    {
        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        $application = Doctrine_Core::getTable("FormEntry")->find($application_id);
        $user_id = $application->getUserId();

        //User Details
        $user_details = $this->getUserDetails($user_id);

        //Application Details
        $application_details = $this->getApplicationDetails($application_id);

        //Invoice Details
        $invoice_details = $this->getInvoiceDetails($invoice_id);

        $values = array_merge($user_details, $application_details);
        $values = array_merge($values, $invoice_details);

        //Check if any field has remote data, pull and integrate results into values
        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $application->getFormId())
            ->andWhere('a.element_option_query <> ?', "")
            ->andWhere('a.element_status = 1');
        $elements = $q->execute();

        foreach ($elements as $element) {
            $updater = new UpdatesManager();
            $remote_url = $element->getElementOptionQuery();
            $remote_username = $element->getElementRemoteUsername();
            $remote_password = $element->getElementRemotePassword();
            $remote_value = $element->getElementRemoteValue();
            $remote_post = $element->getElementRemotePost();

            //Replace url fields
            $remote_url = $this->parseURL($application->getId(), $remote_url);

            //Replace remote_value with actual database value
            $sql = "SELECT * FROM ap_form_" . $application->getFormId() . " WHERE id = ?";
            $params = array($application->getEntryId());
            $sth = mf_do_query($sql, $params, $dbh);

            $apform = mf_do_fetch_result($sth);

            $remote_value = $apform["element_" . $element->getElementId()];

            $pos = strpos($remote_url, '$value');

            if ($pos === false) {
                //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
            } else {
                $remote_url = str_replace('$value', urlencode($remote_value), $remote_url);
            }

            // if ($remote_post) {
            //     //Do nothing TODO: Integrate remote post
            // } else {
            //     $remote_values = $updater->pull_raw_results($remote_url, $remote_username, $remote_password, $remote_value);
            //     if (!empty($remote_values) || !is_null($remote_values)) {
            //         $array_results = array();

            //         foreach ($remote_values['records'][0] as $key => $value) {
            //             $array_results[$key] = $value;
            //         }

            //         if (sizeof($array_results) > 0) {
            //             $values = array_merge($values, $array_results);
            //         }
            //     }
            // }
        }

        $content = static::parseWithDust($content, $values);

        return $content;
    }

    /***
     * Used to parse values into an invoice template
     *
     * @param $content string the string that contains placeholders to be replaced
     * @param $application_id the id of the application
     * @param $form_id the id of the form
     * @param $entry_id the id of the entry in the form table
     * @param $invoice_id the id of the invoice
     * @return string
     */
    public function parseArchiveInvoice($application_id, $form_id, $entry_id, $invoice_id, $content)
    {
        $application = Doctrine_Core::getTable("FormEntryArchive")->find($application_id);
        $user_id = $application->getUserId();

        //User Details
        $user_details = $this->getUserDetails($user_id);

        //Application Details
        $application_details = $this->getArchiveApplicationDetails($application_id);

        //Invoice Details
        $invoice_details = $this->getArchiveInvoiceDetails($invoice_id);

        $values = array_merge($user_details, $application_details);
        $values = array_merge($values, $invoice_details);

        $content = static::parseWithDust($content, $values);

        return $content;
    }

    public function parseInvoicePDF($application_id, $form_id, $entry_id, $invoice_id, $content)
    {
        $saved_application = Doctrine_Core::getTable("FormEntry")->find($application_id);

        $user = Doctrine_Core::getTable("SfGuardUser")->find($saved_application->getUserId());

        $user_profile = $user->getSfGuardUserProfile();

        $q = Doctrine_Query::create()
            ->from('mfUserProfile a')
            ->where('a.user_id = ?', $user->getId())
            ->limit(1);
        $formprofile = $q->fetchOne();

        $profile_form = "";


        if ($formprofile) {
            $prof_entry_id = $formprofile->getEntryId();

            $sql = "SELECT * FROM ap_form_" . $formprofile->getFormId() . " WHERE id = " . $prof_entry_id;
            $prof_results = mysql_query($sql, $dbconn);

            $profile_form = mysql_fetch_assoc($prof_results);
        }

        $app_entry_id = $saved_application->getEntryId();

        $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = " . $app_entry_id;
        $app_results = mysql_query($sql, $dbconn);

        $apform = mysql_fetch_assoc($app_results);

        $conditions = "";

        $invoice_total = 0;

        $otb_helper = new OTBHelper();

        /*$q = Doctrine_Query::create()
            ->from('ApprovalCondition a')
            ->where('a.entry_id = ?', $saved_application->getId());
        $approvalconditions = $q->execute();
        $conditions = "<ul>";
        foreach($approvalconditions as $approval)
        {
            $conditions = $conditions."<li>".$approval->getCondition()->getShortName().". ".$approval->getCondition()->getDescription()."</li>";
        }*/

        $q = Doctrine_Query::create()
            ->from('mfInvoiceDetail a')
            ->where('a.invoice_id = ? AND a.description = ?', array($invoice_id, 'Total'));
        $details = $q->execute();
        foreach ($details as $detail) {
            $invoice_total = $invoice_total + $detail->getAmount();
        }

        //Get User Information (anything starting with sf_ )
        //sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
        if ($this->find('{sf_username}', $content)) {
            $content = str_replace('{sf_username}', $user->getUsername(), $content);
        }
        if ($this->find('{sf_email}', $content)) {
            $content = str_replace('{sf_email}', $user_profile->getEmail(), $content);
        }
        if ($this->find('{sf_mobile}', $content)) {
            $content = str_replace('{sf_mobile}', $user_profile->getMobile(), $content);
        }
        if ($this->find('{sf_fullname}', $content)) {
            $content = str_replace('{sf_fullname}', $user_profile->getFullname(), $content);
        }

        $q = Doctrine_Query::create()
            ->from('ApFormElements a')
            ->where('a.form_id = ?', $formprofile->getFormId());

        $elements = $q->execute();

        foreach ($elements as $element) {
            $childs = $element->getElementTotalChild();
            if ($childs == 0) {
                if ($this->find('{sf_element_' . $element->getElementId() . '}', $content)) {
                    $content = str_replace('{sf_element_' . $element->getElementId() . '}', $profile_form['element_' . $element->getElementId()], $content);
                }
            } else {
                if ($element->getElementType() == "select") {
                    if ($this->find('{sf_element_' . $element->getElementId() . '}', $content)) {
                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($profile_form, $element->getElementId(), $profile_form['element_' . $element->getElementId()]))
                            ->limit(1);
                        $option = $q->fetchOne();

                        if ($option) {
                            $content = str_replace('{sf_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                        }
                    }
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        if ($this->find('{sf_element_' . $element->getElementId() . '_' . ($x + 1) . '}', $content)) {
                            $content = str_replace('{sf_element_' . $element->getElementId() . '_' . ($x + 1) . '}', $profile_form['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        }
                    }
                }
            }
        }

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        if ($this->find('ap_application_id', $content)) {
            $content = str_replace('ap_application_id', $saved_application->getApplicationId(), $content);
        }

        if ($this->find('fm_id', $content)) {
            $content = str_replace('{fm_id}', $saved_application->getId(), $content);
        }

        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1


        if ($this->find('fm_created_at', $content)) {
            if ($saved_application->getDateOfSubmission()) {
                $content = str_replace('fm_created_at', date('d F Y', strtotime(substr($saved_application->getDateOfSubmission(), 0, 11))), $content);
            } else {
                $content = str_replace('fm_created_at', "", $content);
            }
        }
        if ($this->find('fm_updated_at', $content)) {
            if ($saved_application->getDateOfResponse()) {
                $content = str_replace('fm_updated_at', date('d F Y', strtotime(substr($saved_application->getDateOfResponse(), 0, 11))), $content);
            } else {
                $content = str_replace('fm_updated_at', "", $content);
            }
        }
        if ($this->find('current_date', $content)) {
            $content = str_replace('current_date', date('d F Y', strtotime(date('Y-m-d'))), $content);
        }

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $form_id);

        $elements = $q->execute();

        foreach ($elements as $element) {
            $childs = $element->getElementTotalChild();

            if ($childs == 0) {
                if ($element->getElementType() == "select") {
                    if ($element->getElementExistingForm() && $element->getElementExistingStage()) {
                        $q = Doctrine_Query::create()
                            ->from("FormEntry a")
                            ->where("a.id = ?", $apform['element_' . $element->getElementId()])
                            ->limit(1);
                        $linked_application = $q->fetchOne();
                        if ($linked_application) {

                            $q = Doctrine_Query::create()
                                ->from("SavedPermit a")
                                ->leftJoin("a.FormEntry b")
                                ->where("b.form_id = ?", $linked_application->getFormId());
                            $permits = $q->execute();

                            foreach ($permits as $saved_permit) {
                                if ($this->find("{ap_permit_id_" . $saved_permit->getTypeId() . "_element_child}", $content)) {
                                    $content = str_replace("{ap_permit_id_" . $saved_permit->getTypeId() . "_element_child}", ($saved_permit->getPermitId() ? $saved_permit->getPermitId() : $linked_application->getApplicationId()), $content);
                                }
                            }

                            if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                                $content = str_replace('{fm_element_' . $element->getElementId() . '}', $linked_application->getApplicationId(), $content);
                            }

                            $q = Doctrine_Query::create()
                                ->from('apFormElements a')
                                ->where('a.form_id = ?', $element->getElementExistingForm())
                                ->andWhere('a.element_status = ?', 1);

                            $child_elements = $q->execute();

                            foreach ($child_elements as $child_element) {
                                $sql = "SELECT * FROM ap_form_" . $linked_application->getFormId() . " WHERE id = " . $linked_application->getEntryId();
                                $child_app_results = mysql_query($sql, $dbconn);

                                $child_apform = mysql_fetch_assoc($child_app_results);

                                if ($this->find('{ap_child_application_id}', $content)) {
                                    $content = str_replace('{ap_child_application_id}', $linked_application->getApplicationId(), $content);
                                }

                                if ($this->find('{fm_child_created_at}', $content)) {
                                    if ($linked_application->getDateOfSubmission()) {
                                        $content = str_replace('{fm_child_created_at}', date('d F Y', strtotime($linked_application->getDateOfSubmission())), $content);
                                    } else {
                                        $content = str_replace('{fm_child_created_at}', "", $content);
                                    }
                                }

                                if ($this->find('{fm_child_updated_at}', $content)) {
                                    if ($linked_application->getDateOfResponse()) {
                                        $content = str_replace('{fm_child_updated_at}', date('d F Y', strtotime(substr($linked_application->getDateOfResponse(), 0, 11))), $content);
                                    } else {
                                        $content = str_replace('{fm_child_updated_at}', "", $content);
                                    }
                                }

                                //START CHILD ELEMENTS
                                $childs = $child_element->getElementTotalChild();
                                if ($childs == 0) {
                                    if ($child_element->getElementType() == "select") {
                                        if ($child_element->getElementExistingForm() && $child_element->getElementExistingStage()) {

                                            $q = Doctrine_Query::create()
                                                ->from("FormEntry a")
                                                ->where("a.id = ?", $child_apform['element_' . $child_element->getElementId()])
                                                ->limit(1);
                                            $linked_grand_application = $q->fetchOne();
                                            if ($linked_grand_application) {

                                                $q = Doctrine_Query::create()
                                                    ->from("SavedPermit a")
                                                    ->leftJoin("a.FormEntry b")
                                                    ->where("b.form_id = ?", $linked_grand_application->getFormId());
                                                $permits = $q->execute();

                                                foreach ($permits as $saved_permit) {
                                                    if ($this->find("{ap_permit_id_" . $saved_permit->getTypeId() . "_element_grand_child}", $content)) {
                                                        $content = str_replace("{ap_permit_id_" . $saved_permit->getTypeId() . "_element_grand_child}", ($saved_permit->getPermitId() ? $saved_permit->getPermitId() : $linked_grand_application->getApplicationId()), $content);
                                                    }
                                                }

                                                if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                                    $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $linked_grand_application->getApplicationId(), $content);
                                                }

                                                $q = Doctrine_Query::create()
                                                    ->from('apFormElements a')
                                                    ->where('a.form_id = ?', $child_element->getElementExistingForm())
                                                    ->andWhere('a.element_status = ?', 1);

                                                $grand_child_elements = $q->execute();

                                                foreach ($grand_child_elements as $grand_child_element) {

                                                    $sql = "SELECT * FROM ap_form_" . $child_element->getElementExistingForm() . " WHERE id = " . $linked_grand_application->getEntryId();
                                                    $child_app_results = mysql_query($sql, $dbconn);

                                                    $grand_child_apform = mysql_fetch_assoc($child_app_results);

                                                    if ($this->find('{ap_grand_child_application_id}', $content)) {
                                                        $content = str_replace('{ap_grand_child_application_id}', $linked_grand_application->getApplicationId(), $content);
                                                    }

                                                    if ($this->find('{fm_grand_child_created_at}', $content)) {
                                                        $date = "";
                                                        if ($linked_grand_application->getDateOfSubmission()) {
                                                            $date = date('d F Y', strtotime($linked_grand_application->getDateOfSubmission()));
                                                        }
                                                        $content = str_replace('{fm_grand_child_created_at}', $date, $content);
                                                    }

                                                    if ($this->find('{fm_grand_child_updated_at}', $content)) {
                                                        $date = "";
                                                        if ($linked_grand_application->getDateOfResponse()) {
                                                            $date = date('d F Y', strtotime(substr($linked_grand_application->getDateOfResponse(), 0, 11)));
                                                        }
                                                        $content = str_replace('{fm_grand_child_updated_at}', $date, $content);
                                                    }

                                                    //START GRAND CHILD ELEMENTS
                                                    $childs = $grand_child_element->getElementTotalChild();
                                                    if ($childs == 0) {
                                                        if ($grand_child_element->getElementType() == "select") { //select
                                                            if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                                $opt_value = 0;
                                                                if ($grand_child_apform['element_' . $grand_child_element->getElementId()] == "0") {
                                                                    $opt_value++;
                                                                } else {
                                                                    $opt_value = $grand_child_apform['element_' . $grand_child_element->getElementId()];
                                                                }

                                                                $q = Doctrine_Query::create()
                                                                    ->from('ApElementOptions a')
                                                                    ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($child_element->getElementExistingForm(), $grand_child_element->getElementId(), $opt_value))
                                                                    ->limit(1);
                                                                $option = $q->fetchOne();

                                                                if ($option) {
                                                                    $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $option->getOptionText(), $content);
                                                                }
                                                            }
                                                        } elseif ($grand_child_element->getElementType() == "checkbox") {
                                                            if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                                $q = Doctrine_Query::create()
                                                                    ->from('ApElementOptions a')
                                                                    ->where('a.form_id = ? AND a.element_id = ?', array($child_element->getElementExistingForm(), $grand_child_element->getElementId()));
                                                                $options = $q->execute();

                                                                $options_text = "<ul>";
                                                                foreach ($options as $option) {
                                                                    if ($grand_child_apform['element_' . $grand_child_element->getElementId() . "_" . $option->getOptionId()]) {
                                                                        $options_text .= "<li>" . $option->getOptionText() . "</li>";
                                                                    }
                                                                }
                                                                $options_text .= "</ul>";

                                                                $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . "}", $options_text, $content);
                                                            }
                                                        } else { //text
                                                            if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                                $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $grand_child_apform['element_' . $grand_child_element->getElementId()], $content);
                                                            }
                                                        }
                                                    } else {
                                                        for ($x = 0; $x < ($childs + 1); $x++) {
                                                            if ($this->find('{fm_grand_child_element_' . $grand_child_element->getElementId() . '}', $content)) {
                                                                $content = str_replace('{fm_grand_child_element_' . $grand_child_element->getElementId() . "}", $grand_child_apform['element_' . $grand_child_element->getElementId() . "_" . ($x + 1)], $content);
                                                            }
                                                        }
                                                    }
                                                    //END GRAND CHILD ELEMENTS
                                                }
                                            }
                                        } else { //select
                                            if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                                $opt_value = 0;
                                                if ($child_apform['element_' . $child_element->getElementId()] == "0") {
                                                    $opt_value++;
                                                } else {
                                                    $opt_value = $child_apform['element_' . $child_element->getElementId()];
                                                }

                                                $q = Doctrine_Query::create()
                                                    ->from('ApElementOptions a')
                                                    ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($linked_application->getFormId(), $child_element->getElementId(), $opt_value))
                                                    ->limit(1);
                                                $option = $q->fetchOne();

                                                if ($option) {
                                                    $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $option->getOptionText(), $content);
                                                }
                                            }
                                        }
                                    } elseif ($child_element->getElementType() == "checkbox") {
                                        if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                            $q = Doctrine_Query::create()
                                                ->from('ApElementOptions a')
                                                ->where('a.form_id = ? AND a.element_id = ?', array($linked_application->getFormId(), $child_element->getElementId()));
                                            $options = $q->execute();

                                            $options_text = "<ul>";
                                            foreach ($options as $option) {
                                                if ($child_apform['element_' . $child_element->getElementId() . "_" . $option->getOptionId()]) {
                                                    $options_text .= "<li>" . $option->getOptionText() . "</li>";
                                                }
                                            }
                                            $options_text .= "</ul>";

                                            $content = str_replace('{fm_child_element_' . $child_element->getElementId() . "}", $options_text, $content);
                                        }
                                        continue;
                                    } else { //text
                                        if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                            $content = str_replace('{fm_child_element_' . $child_element->getElementId() . '}', $child_apform['element_' . $child_element->getElementId()], $content);
                                        }
                                    }
                                } else {
                                    for ($x = 0; $x < ($childs + 1); $x++) {
                                        if ($this->find('{fm_child_element_' . $child_element->getElementId() . '}', $content)) {
                                            $content = str_replace('{fm_child_element_' . $child_element->getElementId() . "}", $child_apform['element_' . $child_element->getElementId() . "_" . ($x + 1)], $content);
                                        }
                                    }
                                }
                                //END CHILD ELEMENTS
                            }


                            continue;
                        }
                    } else {
                        if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                            $opt_value = 0;
                            if ($apform['element_' . $element->getElementId()] == "0") {
                                $opt_value++;
                            } else {
                                $opt_value = $apform['element_' . $element->getElementId()];
                            }

                            $q = Doctrine_Query::create()
                                ->from('ApElementOptions a')
                                ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                                ->limit(1);
                            $option = $q->fetchOne();


                            if ($option) {
                                $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                            }
                        }
                    }
                }

                if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . '}', $apform['element_' . $element->getElementId()], $content);
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        }
                    }
                }
            } else {

                if ($element->getElementType() == "select") {
                    if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                        $opt_value = 0;
                        if ($apform['element_' . $element->getElementId()] == "0") {
                            $opt_value++;
                        } else {
                            $opt_value = $apform['element_' . $element->getElementId()];
                        }

                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $opt_value))
                            ->limit(1);
                        $option = $q->fetchOne();


                        if ($option) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . '}', $option->getOptionText(), $content);
                        }
                    }
                } else {
                    for ($x = 0; $x < ($childs + 1); $x++) {
                        if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                        }
                    }
                }
            }
            for ($x = 0; $x < ($childs + 1); $x++) {
                if ($this->find('{fm_element_' . $element->getElementId() . "_" . ($x + 1) . '}', $content)) {
                    $content = str_replace('{fm_element_' . $element->getElementId() . "_" . ($x + 1) . '}', $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                }
            }
        }

        //Get Conditions of Approval (anything starting with ca_ )
        //ca_conditions
        /*if($this->find('{ca_conditions}', $content))
        {
            $content = str_replace('{ca_conditions}', $conditions , $content);
        }*/

        //Get Invoice Details (anything starting with in_ )
        //in_total
        $q = Doctrine_Query::create()
            ->from('MfInvoice a')
            ->where('a.id = ?', $invoice_id)
            ->limit(1);
        $invoice = $q->fetchOne();

        $inv_title = $invoice->getInvoiceNumber();
        $inv_created_at = substr($invoice->getCreatedAt(), 0, 10);
        if ($invoice->getExpiresAt()) {
            $inv_expires_at = substr($invoice->getExpiresAt(), 0, 10);
        } else {
            $q = Doctrine_Query::create()
                ->from("Invoicetemplates a")
                ->where("a.id = ?", $invoice->getTemplateId());
            $template = $q->fetchOne();
            if ($template) {
                if ($template->getMaxDuration() > 0) {
                    $date = strtotime("+" . $template->getMaxDuration() . " day");
                    $inv_expires_at = date('Y-m-d', $date);
                }
            }
        }

        $currency = "";

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.id = ?", $invoice->getAppId());
        $application = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("ApForms a")
            ->where("a.form_id = ?", $application->getFormId());
        $form = $q->fetchOne();

        if ($form) {
            $currency = $form->getPaymentCurrency();
        } else {
            $currency = sfConfig::get("app_currency");
        }

        $inv_fees = "";

        $inv_fees = $inv_fees . '
            <table width="100%" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tbody><tr><th width="20%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; text-align:left;" nowrap>Item</th><th style="padding:5px 12px; text-align:left;" nowrap>Total Amount (' . $currency . ')</th></tr>';
        $grand_total = 0;
        $inv_details = $invoice->getMfInvoiceDetail();
        $total_found = false;
        foreach ($inv_details as $inv_detail) {
            if ($inv_detail->getDescription() == "Attached Invoice") {
                $url = html_entity_decode($detail->getAmount());
                $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $inv_detail->getDescription() . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
            } else {
                if ($inv_detail->getAmount() == "0") {
                    //Dont display
                } else {
                    $pieces = explode(":", $inv_detail->getDescription());
                    if (sizeof($pieces) > 1) {
                        $description = $pieces[1];

                        $inv_fees = $inv_fees . '<tr><td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $description . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
                    } else {
                        $inv_fees = $inv_fees . '<td align="left" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;">' . $inv_detail->getDescription() . '</td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $inv_detail->getAmount() . '</td></tr>';
                    }
                    $grand_total += $inv_detail->getAmount();
                }
            }

            if ($inv_detail->getDescription() == "Total") {
                $total_found = true;
            }
        }

        if ($total_found == false) {
            $inv_fees = $inv_fees . '<tr><td align="right" width="80%" style="border-right: 1px solid #d2d2d2; padding:5px 12px; border-top: 1px solid #d2d2d2;"><b>Total (' . $currency . ')</b></td><td align="left" style="border-top: 1px solid #d2d2d2; padding:5px 12px;">' . $grand_total . '</td></tr>';
        }

        $inv_fees = $inv_fees . "</tbody>
             </table>";


        if ($this->find('{in_total}', $content)) {
            $content = str_replace('{in_total}', $invoice_total, $content);
        }

        if ($this->find('{inv_no}', $content)) {
            $content = str_replace('{inv_no}', $inv_title, $content);
        }

        if ($this->find('{inv_date_created}', $content)) {
            $content = str_replace('{inv_date_created}', date('d F Y', strtotime($inv_created_at)), $content);
        }

        if ($this->find('{inv_expires_at}', $content)) {
            if ($inv_expires_at) {
                $content = str_replace('{inv_expires_at}', date('d F Y', strtotime($inv_expires_at)), $content);
            } else {
                $content = str_replace('{inv_expires_at}', "", $content);
            }
        }

        if ($this->find('{inv_fee_table}', $content)) {
            $content = str_replace('{inv_fee_table}', $inv_fees, $content);
        }

        if ($this->find('{payment_date}', $content)) {
            $content = str_replace('{payment_date}', date('Y-m-d H:i:s', strtotime($invoice->getCreatedAt())), $content);
        }
        if ($this->find('{jambo_pay_ref}', $content)) {
            $content = str_replace('{jambo_pay_ref}', $invoice->getDocRefNumber(), $content);
        }
        if ($this->find('{inv_total_words}', $content)) {
            $content = str_replace('{inv_total_words}', $otb_helper->convert_number_to_words($grand_total). " SHILLINGS ONLY.", $content);
        }

        if ($this->find('{inv_status}', $content)) {
            $status = "";

            $expired = false;
            $cancelled = false;

            $db_date_event = str_replace('/', '-', $invoice->getExpiresAt());

            $db_date_event = strtotime($db_date_event);

            if (time() > $db_date_event && !($invoice->getPaid() == "15" || $invoice->getPaid() == "2" || $invoice->getPaid() == "3")) {
                $status = "Expired";
            } else if ($invoice->getPaid() == "2") {
                $status = "Paid";
            } else if ($invoice->getPaid() == "15") {
                $status = "Part Payment";
            } else if ($invoice->getPaid() == "1") {
                $status = "Not Paid";
            }

            if ($invoice->getPaid() == "3") {
                $status = 'Cancelled';
            }

            $content = str_replace('{inv_status}', $status, $content);
        }

        $query = "select * from ap_form_payments where invoice_id = " . $invoice->getId();
        $payment_results = mysql_query($query, $dbconn);
        $payment_row = mysql_fetch_assoc($payment_results);

        if ($this->find('{inv_payment_id}', $content)) {
            $payment_id = "";

            if ($payment_row) {
                $payment_id = ucwords($payment_row['payment_id']);
            }

            $content = str_replace('{inv_payment_id}', $payment_id, $content);
        }

        if ($this->find('{inv_payment_merchant_type}', $content)) {
            $merchant_type = "";

            if ($payment_row) {
                $merchant_type = ucwords($payment_row['payment_merchant_type']);
            }

            $content = str_replace('{inv_payment_merchant_type}', $merchant_type, $content);
        }
        if ($this->find('{inv_payment_billing_state}', $content)) {
            $billing_state = "";

            if ($payment_row) {
                $billing_state = $payment_row['billing_state'];
            }

            $content = str_replace('{inv_payment_billing_state}', $billing_state, $content);
        }
        if ($this->find('{inv_payment_amount}', $content)) {
            $payment_amount = "";

            if ($payment_row) {
                $payment_amount = $payment_row['payment_amount'];
            }

            $content = str_replace('{inv_payment_amount}', $payment_amount, $content);
        }
        if ($this->find('{inv_payment_status}', $content)) {
            $payment_status = "";

            if ($payment_row) {
                $payment_status = $payment_row['payment_status'];
            }

            $content = str_replace('{inv_payment_status}', $payment_status, $content);
        }

        return $content;
    }

    public function parseHeaders($form_id, $content)
    {

        //Get User Information (anything starting with sf_ )
        //sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
        if ($this->find('{sf_username}', $content)) {
            $content = str_replace('{sf_username}', "Architect's Username", $content);
        }
        if ($this->find('{sf_email}', $content)) {
            $content = str_replace('{sf_email}', "Architect's Email", $content);
        }
        if ($this->find('{sf_fullname}', $content)) {
            $content = str_replace('{sf_fullname}', "Architect's Fullname", $content);
        }

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', 15);

        $elements = $q->execute();

        foreach ($elements as $element) {
            if ($this->find('{sf_element_' . $element->getElementId() . '}', $content)) {
                $content = str_replace('{sf_element_' . $element->getElementId() . '}', $element->getElementTitle(), $content);
            }
        }

        //Get Application Information (anything starting with ap_ )
        //ap_application_id
        if ($this->find('{ap_application_id}', $content)) {
            $content = str_replace('{ap_application_id}', "Application Number", $content);
        }

        //Get Form Details (anything starting with fm_ )
        //fm_created_at, fm_updated_at.....fm_element_1


        if ($this->find('{fm_created_at}', $content)) {
            $content = str_replace('{fm_created_at}', "Submitted On", $content);
        }
        if ($this->find('{fm_updated_at}', $content)) {
            $content = str_replace('{fm_updated_at}', "Last Updated", $content);
        }
        if ($this->find('{current_date}', $content)) {
            $content = str_replace('{current_date}', "Current Date", $content);
        }

        $q = Doctrine_Query::create()
            ->from('apFormElements a')
            ->where('a.form_id = ?', $form_id);

        $elements = $q->execute();

        foreach ($elements as $element) {
            if ($this->find('{fm_element_' . $element->getElementId() . '}', $content)) {
                $content = str_replace('{fm_element_' . $element->getElementId() . '}', $element->getElementTitle(), $content);
            }
        }

        //Get Conditions of Approval (anything starting with ca_ )
        //ca_conditions
        if ($this->find('{ca_conditions}', $content)) {
            $content = str_replace('{ca_conditions}', "Conditions Of Approval", $content);
        }

        //mini_ca_conditions
        if ($this->find('{mini_ca_conditions}', $content)) {
            $content = str_replace('{mini_ca_conditions}', "Conditions Of Approval", $content);
        }

        //Get Invoice Details (anything starting with in_ )
        //in_total

        if ($this->find('{in_total}', $content)) {
            $content = str_replace('{in_total}', "Submission Fee", $content);
        }

        return $content;
    }

    public function merge_array($element)
    {
        foreach ($element as $key1 => $value1) {
            if (is_array($value1))
                $this->merge_array($value1);
            else
                $this->final_array[] = $value1;
        }
    }

    public function find($needle, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return false;
        } else {
            return true;
        }
    }
    //OTB Patch get comment sheet details to display on the permit
    public function getCommentSheetDetails($application_id)
    {
        $values = array();

        //get all tasks that have been completed for this application
        $q = Doctrine_Query::create()
            ->from('Task a')
            ->where('a.application_id = ?', $application_id)
            ->andWhere("a.status = ?", 25); //only for completed tasks


        $tasks = $q->execute();

        foreach ($tasks as $task) {
            //get form_id and entry_id from task_forms
            $q = Doctrine_Query::create()
                ->from('TaskForms a')
                ->where('a.task_id =?', $task->getId())
                ->orderBy('a.id DESC');

            if ($q->count() > 0) //need improvement for those tasks that are missing in the task forms table
            {


                $task_forms = $q->fetchOne();


                $form_id = $task_forms->getFormId();
                $entry_id = $task_forms->getEntryId();
                $sql = "SELECT * FROM ap_form_" . $form_id . " WHERE id = " . $entry_id;



                $apform = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($sql)->fetchAll();
                $apform = $apform[0];

                $q = Doctrine_Query::create()
                    ->from('ApFormElements a')
                    ->where('a.form_id = ?', $form_id)
                    ->andWhere("a.element_status = ?", 1);

                $elements = $q->execute();
                $prefix = $form_id;

                //get all the items starting with fm_c 	{fm_c15372_element_2}fm_c8205_element_2 fm_c_10894_element_2
                foreach ($elements as $element) {

                    if ($element->getElementType() == "simple_name") {
                        $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"];
                        continue;
                    }

                    if ($element->getElementType() == "textarea") {
                        if ($element->getElementJsondef()) {
                            $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = json_decode($apform['element_' . $element->getElementId()], true);
                            continue;
                        } else {
                            $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()];
                            continue;
                        }
                    }

                    if ($element->getElementType() == "simple_name_wmiddle") {
                        $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_1"] . " " . $apform['element_' . $element->getElementId() . "_2"] . " " . $apform['element_' . $element->getElementId() . '_3'];
                        continue;
                    }

                    if ($element->getElementType() == "file") {
                        $q = Doctrine_Query::create()
                            ->from("ApSettings a")
                            ->where("a.id = 1")
                            ->orderBy("a.id DESC");
                        $aplogo = $q->fetchOne();

                        $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = "/" . $aplogo->getUploadDir() . "/" . "form_" . $form_id . "/files/" . $apform['element_' . $element->getElementId()];
                        continue;
                    }

                    if ($element->getElementType() == "checkbox" || $element->getElementType() == "radio") {
                        $q = Doctrine_Query::create()
                            ->from('ApElementOptions a')
                            ->where('a.form_id = ? AND a.element_id = ?', array($form_id, $element->getElementId()));
                        $options = $q->execute();

                        $options_text = "";
                        foreach ($options as $option) {
                            if ($apform['element_' . $element->getElementId() . "_" . $option->getOptionId()]) {
                                $options_text .= "" . $option->getOptionText() . "";
                            }
                        }


                        if ($apform['element_' . $element->getElementId() . "_other"] != "") {
                            $options_text .= "" . $apform['element_' . $element->getElementId() . '_other'] . "";
                        }

                        $options_text .= "";

                        $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $options_text;
                        continue;
                    }

                    $childs = $element->getElementTotalChild();


                    if ($childs == 0) {

                        if ($element->getElementType() == "date") {
                            $date = "";
                            if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                                $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                            } else {
                                $date = "";
                            }

                            $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $date;
                        } elseif ($element->getElementType() == "europe_date") {
                            $date = "";
                            if ($apform['element_' . $element->getElementId()] && $apform['element_' . $element->getElementId()] != "0000-00-00") {
                                $date = date('d F Y', strtotime($apform['element_' . $element->getElementId()]));
                            } else {
                                $date = "";
                            }

                            $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $date;
                        } elseif ($element->getElementType() == "select") {
                            if ($element->getElementSelectOptions() == "table") {
                                $query = "SELECT {$element->getElementFieldValue()}, {$element->getElementFieldName()} FROM {$element->getElementTableName()} WHERE {$element->getElementFieldValue()} = {$apform['element_' . $element->getElementId()]} limit 1";
                                $table_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($query);
                                foreach ($table_rows as $option) {
                                    $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $option[$element->getElementFieldName()];
                                }
                            } elseif ($element->getElementSelectOptions() == 'query') {
                                $query_rows = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($element->getElementOptionQuery());
                                foreach ($query_rows as $option) {
                                    if ($option[$element->getElementFieldValue()] == $apform['element_' . $element->getElementId()]) {
                                        $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $option[$element->getElementFieldName()];
                                    }
                                }
                            } elseif ($element->getElementSelectOptions() == 'application') {
                                $application = Doctrine_Core::getTable("FormEntry")->find($apform['element_' . $element->getElementId()]);
                                if ($application) {
                                    $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $application->getApplicationId();
                                }
                            } else {
                                $q = Doctrine_Query::create()
                                    ->from('ApElementOptions a')
                                    ->where('a.form_id = ? AND a.element_id = ? AND a.option_id = ?', array($form_id, $element->getElementId(), $apform['element_' . $element->getElementId()]))
                                    ->limit(1);
                                $option = $q->fetchOne();

                                if ($option) {
                                    $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $option->getOptionText();
                                }
                            }
                        } else {

                            $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId()];
                            for ($x = 1; $x < 9; $x++) {
                                if ($apform['element_' . $element->getElementId() . '_' . $x]) {
                                    $values['fm_c' . $prefix . '_element_' . $element->getElementId() . '_' . $x] = $apform['element_' . $element->getElementId() . '_' . $x];
                                }
                            }
                        }
                    } else {
                        for ($x = 0; $x < ($childs + 1); $x++) {
                            $content = str_replace('{fm_element_' . $element->getElementId() . "}", $apform['element_' . $element->getElementId() . "_" . ($x + 1)], $content);
                            $values['fm_c' . $prefix . '_element_' . $element->getElementId()] = $apform['element_' . $element->getElementId() . "_" . ($x + 1)];
                        }
                    }
                }
            }
        }

        return $values;
    }
}
