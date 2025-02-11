<?php

/**
 * _taskspanel partial.
 *
 * Display commentsheets or invoice forms related to the task
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

//Invoicing Task
if ($task->getType() == "3" && $task->getStatus() != 25) {
    ?>
    <div class="alert alert-success">
        <strong>Please!</strong> Select a fee(s) from the list to create an invoice.
    </div>
    <form class="form-bordered" id="feeform" method="post" action="/plan/tasks/saveinvoice/id/<?php echo $task->getId(); ?>"
        id="MailContentForm" name="MailContentForm" onSubmit="return validate_editfield();" autocomplete="off"
        data-ajax="false">
        <?php
        $grandtotal = 0;
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where('a.applicationform = ?', $application->getFormId())
            ->andWhere('a.applicationstage = ?', $application->getApproved());
        $invoicetemplate = $q->fetchOne();
        $invoicemanager = new InvoiceManager();

        $feeselect = "<option>" . __("Choose Fee") . "</option>";

        $q = Doctrine_Query::create()
            ->from("FeeCategory a")
            ->orderBy("a.id ASC");
        $categories = $q->execute();

        foreach ($categories as $category) {


            $feeselect .= "<optgroup label='--------------------------------------------------------------------------------------'></optgroup>";

            $feeselect .= "<optgroup label='" . $category->getTitle() . "'>";

            $q = Doctrine_Query::create()
                ->from("Fee a")
                ->where("a.fee_category = ?", $category->getId())
                ->orderBy("a.description ASC");
            $fees = $q->execute();
            foreach ($fees as $fee) {
                $feeselect .= "<option value='" . $fee->getId() . "'>" . $fee->getFeeCode() . ": " . $fee->getDescription() . "</option>";
            }

            $feeselect .= "</optgroup>";
        }
        ?>

        <script language="javascript">
            function getFee(id, feecode) {
                var xmlHttpReq1 = false;
                var self1 = this;
                // Mozilla/Safari

                if (window.XMLHttpRequest) {
                    self.xmlHttpReq1 = new XMLHttpRequest();
                }
                // IE
                else if (window.ActiveXObject) {
                    self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
                }
                self.xmlHttpReq1.open('POST', '/plan/fees/getfee', true);
                self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                self.xmlHttpReq1.onreadystatechange = function () {
                    if (self.xmlHttpReq1.readyState == 4) {
                        if (document.getElementById(id).value != "") {
                            document.getElementById(id).value = self.xmlHttpReq1.responseText;
                            document.getElementById(id).disabled = false;
                            updatefee();
                        } else {
                            document.getElementById(id).disabled = true;
                        }
                    }
                }

                if (feecode == "Choose Fee") {
                    document.getElementById(id).disabled = true;
                } else {
                    self.xmlHttpReq1.send('code' + '=' + feecode);
                }
            }


            function updatefee() {
                updateTotal();
            }

            function updateTotal() {
                var total = 0;

                $("#feeform input[type=number]").each(function () {
                    if ($(this).attr('id') == 'servicefee' || $(this).attr('id') == 'totalfee') {

                    } else {
                        total = total + parseInt(this.value);
                    }
                });

                $("#totalfee").val(total);
            }
        </script>
        <hr />
        <div class="more_fees_id">
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control input-md' onChange='getFee("inv_1", this.value)'
                        id="select_fee_1" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_1' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>

            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control input-md' onChange='getFee("inv_2", this.value)'
                        id="select_fee_2" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_2' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>

            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_3", this.value)' id="select_fee_3"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_3' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>

            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_4", this.value)' id="select_fee_4"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_4' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>

            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_5", this.value)' id="select_fee_5"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_5' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_6", this.value)' id="select_fee_6"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_6' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_7", this.value)' id="select_fee_7"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_7' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_8", this.value)' id="select_fee_8"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_8' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_9", this.value)' id="select_fee_9"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_9' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_10", this.value)'
                        id="select_fee_10" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_10' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_11", this.value)'
                        id="select_fee_11" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_11' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_12", this.value)'
                        id="select_fee_12" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_12' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_13", this.value)'
                        id="select_fee_13" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_13' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_14", this.value)'
                        id="select_fee_14" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_14' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
            <div class='form-group' class='formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control' onChange='getFee("inv_15", this.value)'
                        id="select_fee_15" data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'> <input type='number' id='inv_15' onkeyup='updateTotal();' name='feevalue[]'
                        disabled='disabled' class='form-control' value="0" /></div>
            </div>
        </div>

        <div class='form-group' class='formgroup'>
            <label class='col-sm-4'>
                <?php echo __("Total"); ?>
            </label>

            <div class='col-sm-8'>
                <input type="text" readonly="readonly" id='totalfee' value="<?php echo $grandtotal; ?>">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-12" style="padding: 10px;" align="right">
                <button class="btn btn-primary" type="submit" name="submitbuttonname" value="submitbuttonvalue">
                    <?php echo __("Submit"); ?> </button>
            </div>
        </div>
    </form>
    <?php
    $pending_assessment = true;
} else //Assessment Task
{
    //If task is marked as completed, display read only comments
    if ($task->getStatus() == "25") {
        $q = Doctrine_Query::create()
            ->from('SubMenuButtons a')
            ->where('a.sub_menu_id = ?', $application->getApproved());
        $submenubuttons = $q->execute();
        $action_string = "";
        foreach ($submenubuttons as $submenubutton) {
            $q = Doctrine_Query::create()
                ->from('Buttons a')
                ->where('a.id = ?', $submenubutton->getButtonId());
            $buttons = $q->execute();

            foreach ($buttons as $button) {
                if ($sf_user->mfHasCredential("accessbutton" . $button->getId())) {
                    $pos = strpos($button->getLink(), "decline");

                    static $colorToggle = 0; // Static variable to track alternating colors

                    if (
                        strpos($button->getLink(), 'decline') !== false ||
                        strpos($button->getTitle(), 'delete') !== false ||
                        strpos($button->getLink(), 'reject') !== false
                    ) {

                        $panelClass = "panel-danger";
                    } else {
                        $panelClass = ($colorToggle % 2 == 0) ? "panel-primary" : "panel-success";
                        $colorToggle++; 
                    }

                    if ($pos === false) {
                        $pos = strpos($button->getTitle(), "delete");
                        if ($pos === false) {
                            $action_count++;

                            if ($pending_assessment == false) {
                                $action_string .= "<div class='col-lg-4 col-sm-6' style='margin-bottom: 15px;'>
                                    <a href='" . $button->getLink() . "&id=" . $task->getId() . "' 
                                    class='panel $panelClass text-center' 
                                    style='display: block; text-decoration: none; color: inherit;'
                                    onClick=\"if(confirm('Are you sure?')){ 
                                        document.getElementById('warning').value = 0; 
                                    } else { return false; }\">
                                        <div class='panel-heading'>
                                            <h3 class='panel-title'>" . htmlspecialchars($button->getTitle()) . "</h3>
                                        </div>
                                    </a>
                                </div>";

                            } else {
                                $action_string .= "<div class='col-lg-4 col-sm-6' style='margin-bottom: 15px;'>
                                    <a href='#' class='panel $panelClass text-center' 
                                    style='display: block; text-decoration: none; color: inherit;'
                                    onClick=\"alert('Please complete your task first'); return false;\">
                                        <div class='panel-heading'>
                                            <h3 class='panel-title'>" . htmlspecialchars($button->getTitle()) . "</h3>
                                        </div>
                                    </a>
                                </div>";
                            }
                        } else {
                            $action_count++;

                            if ($pending_assessment == false) {
                                $action_string .= "<div class='col-lg-4 col-sm-6' style='margin-bottom: 15px;'>
                                <a href='" . $button->getLink() . "&id=" . $task->getId() . "' 
                                class='panel $panelClass text-center' 
                                style='display: block; text-decoration: none; color: inherit;'
                                onClick=\"if(confirm('Are you sure?')){ 
                                    document.getElementById('warning').value = 0; 
                                } else { return false; }\">
                                    <div class='panel-heading'>
                                        <h3 class='panel-title'>" . htmlspecialchars($button->getTitle()) . "</h3>
                                    </div>
                                </a>
                            </div>";

                            } else {
                                $action_string .= "<div class='col-lg-4 col-sm-6' style='margin-bottom: 15px;'>
                                    <a href='#' class='panel $panelClass text-center' 
                                    style='display: block; text-decoration: none; color: inherit;'
                                    onClick=\"alert('Please complete your task first'); return false;\">
                                        <div class='panel-heading'>
                                            <h3 class='panel-title'>" . htmlspecialchars($button->getTitle()) . "</h3>
                                        </div>
                                    </a>
                                </div>";
                            }
                        }
                    } else {
                        $action_count++;

                        if ($pending_assessment == false) {
                            $action_string .= "<div class='col-lg-4 col-sm-6' style='margin-bottom: 15px;'>
                                <a href='" . $button->getLink() . "&id=" . $task->getId() . "' 
                                class='panel $panelClass text-center' 
                                style='display: block; text-decoration: none; color: inherit;'
                                onClick=\"if(confirm('Are you sure?')){ 
                                    document.getElementById('warning').value = 0; 
                                } else { return false; }\">
                                    <div class='panel-heading'>
                                        <h3 class='panel-title'>" . htmlspecialchars($button->getTitle()) . "</h3>
                                    </div>
                                </a>
                            </div>";
                        } else {
                            $action_string .= "<div class='col-lg-4 col-sm-6' style='margin-bottom: 15px;'>
                            <a href='#' class='panel $panelClass text-center' 
                            style='display: block; text-decoration: none; color: inherit;'
                            onClick=\"alert('Please complete your task first'); return false;\">
                                <div class='panel-heading'>
                                    <h3 class='panel-title'>" . htmlspecialchars($button->getTitle()) . "</h3>
                                </div>
                            </a>
                        </div>";
                        }
                    }
                }
            }
        }
        ?>
        <div class="row">
            <?php echo $action_string; ?>
        </div>
        <?php
    } else {
        //If task is marked as pending, display form for entering comments
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $sf_user->getAttribute('userid'));
        $reviewer = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("TaskForms a")
            ->where("a.task_id = ?", $task->getId());
        $taskform = $q->fetchOne();

        if ($taskform && $taskform->getFormId()) {
            $form_id = $taskform->getFormId();
            $entry_id = $taskform->getEntryId();

            include_partial('task_form', array('form_id' => $form_id, 'id' => $entry_id, 'task' => $task, 'application' => $application));
        } else {
            $form = null;

            $department_id = $reviewer->getStrdepartment();



            if (!is_numeric($department_id)) {
                $q = Doctrine_Query::create()
                    ->from("Department a")
                    ->where("a.department_name = ?", $department_id);
                $department_id = $q->fetchOne()->getId();
            }

            $q_d = Doctrine_Query::create()
                ->from('Department a')
                ->where('a.id = ?', intval($department_id));
            $department = $q_d->fetchOne();

            if ($q_d->count() > 0) {
                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_department = ?", $department->getId())
                    ->andWhere("a.form_department_stage = ?", $application->getApproved())
                    ->andWhere("a.form_active = 1 AND form_type = 2")
                    ->orderBy("a.form_id DESC");
                $form = $q->fetchOne();
            }

            if (empty($form)) {
                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_department_stage = ?", $application->getApproved())
                    ->andWhere("a.form_active = 1 AND form_type = 2")
                    ->orderBy("a.form_id DESC");
                $form = $q->fetchOne();
            }

            if (empty($form)) {
                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_department_stage = ?", $application->getApproved())
                    ->andWhere("(a.form_department IS NULL OR a.form_department = 0)")
                    ->andWhere("a.form_active = 1 AND form_type = 2")
                    ->orderBy("a.form_id DESC");
                $form = $q->fetchOne();
            }

            if (empty($form) && $department) {
                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_department = ?", $department->getId())
                    ->andWhere("a.form_active = 1 AND form_type = 2")
                    ->orderBy("a.form_id DESC");
                $form = $q->fetchOne();
            }

            if (empty($form)) {
                $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_active = 1 AND form_type = 2")
                    ->orderBy("a.form_id DESC");
                $form = $q->fetchOne();
            }

            if ($form) {
                $form_id = $form->getFormId();

                include_partial('task_form', array('form_id' => $form_id, 'task' => $task, 'application' => $application));

                $pending_assessment = true;
            }
        }
    }
}
?>

<style>
    #assessmentModal {
        max-height: max-content;
    }

    .more_fees_id {
        min-width: auto;
        max-height: 60vh;
        overflow: auto;
    }


    .select2-selection__rendered {
        line-height: 31px !important;
    }

    .select2-container .select2-selection--single {
        height: 35px !important;
    }

    .select2-selection__arrow {
        height: 34px !important;
    }
</style>