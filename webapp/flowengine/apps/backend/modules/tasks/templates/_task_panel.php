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
    <form class="form-bordered" id="feeform" method="post"
        action="/backend.php/tasks/saveinvoice/id/<?php echo $task->getId(); ?>" id="MailContentForm" name="MailContentForm"
        onSubmit="return validate_editfield();" autocomplete="off" data-ajax="false">
        <?php
        $grandtotal = 0;
        $q = Doctrine_Query::create()
            ->from('Invoicetemplates a')
            ->where('a.applicationform = ?', $application->getFormId())
            ->andWhere('a.applicationstage = ?', $application->getApproved());
        $invoicetemplate = $q->fetchOne();
        $invoicemanager = new InvoiceManager();

        $feeselect = "<option value='0' selected>" . __("Choose Fee") . "</option>";

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
            function getFee(id, feecode, application_id) {
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
                self.xmlHttpReq1.open('POST', '/backend.php/fees/getfee', true);
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
                    self.xmlHttpReq1.send('code' + '=' + feecode + "&application=" + application_id);
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
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control input-md'
                        onChange='getFee("inv_1", this.value, <?php echo $application->getId(); ?>)' id="select_fee_1"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_1' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_1')">X</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control input-md'
                        onChange='getFee("inv_2", this.value, <?php echo $application->getId(); ?>)' id="select_fee_2"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_2' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_2')">X</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_3", this.value, <?php echo $application->getId(); ?>)' id="select_fee_3"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_3' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_3')">X</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_4", this.value, <?php echo $application->getId(); ?>)' id="select_fee_4"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_4' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_4')">X</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_5", this.value, <?php echo $application->getId(); ?>)' id="select_fee_5"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_5' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_5')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_6", this.value, <?php echo $application->getId(); ?>)' id="select_fee_6"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_6' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_6')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_7", this.value, <?php echo $application->getId(); ?>)' id="select_fee_7"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_7' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_7')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_8", this.value, <?php echo $application->getId(); ?>)' id="select_fee_8"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_8' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_8')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_9", this.value, <?php echo $application->getId(); ?>)' id="select_fee_9"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_9' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_9')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_10", this.value, <?php echo $application->getId(); ?>)' id="select_fee_10"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_10' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_10')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_11", this.value, <?php echo $application->getId(); ?>)' id="select_fee_11"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_11' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_11')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_12", this.value, <?php echo $application->getId(); ?>)' id="select_fee_12"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_12' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_12')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_13", this.value, <?php echo $application->getId(); ?>)' id="select_fee_13"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_13' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_13')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_14", this.value, <?php echo $application->getId(); ?>)' id="select_fee_14"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_14' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_14')">X</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class='form-group fee-group formgroup'>
                <label class='col-sm-4'>
                    <select name='feetitle[]' class='form-control'
                        onChange='getFee("inv_15", this.value, <?php echo $application->getId(); ?>)' id="select_fee_15"
                        data-width="100%">
                        <?php echo $feeselect; ?>
                    </select>
                </label>
                <div class='col-sm-8'>
                    <div class="input-group">
                        <input type='number' id='inv_15' onkeyup='updateTotal();' name='feevalue[]' disabled='disabled'
                            class='form-control' value="0" />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-danger remove-fee custom-delete-button"
                                onclick="clearField('inv_15')">X</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class='form-group fee-group formgroup'>
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
        foreach ($submenubuttons as $submenubutton) {
            $q = Doctrine_Query::create()
                ->from('Buttons a')
                ->where('a.id = ?', $submenubutton->getButtonId());
            $buttons = $q->execute();

            $action_string = "";

            foreach ($buttons as $button) {
                if ($sf_user->mfHasCredential("accessbutton" . $button->getId())) {
                    $pos = strpos($button->getLink(), "decline");
                    if ($pos === false) {
                        $pos = strpos($button->getTitle(), "delete");
                        if ($pos === false) {
                            $action_count++;

                            if ($pending_assessment == false) {
                                $action_string .= "<li><a class='btn btn-primary' onClick=\"if(confirm('Are you sure?')){ document.getElementById('warning').value = 0; window.location='" . $button->getLink() . "&id=" . $task->getId() . "'; }else{ return false; }\">" . $button->getTitle() . "</a></li>";
                            } else {
                                $action_string .= "<li><a class='btn btn-primary' onClick=\"alert('Please complete your task first'); return false;\">" . $button->getTitle() . "</a></li>";
                            }
                        } else {
                            $action_count++;

                            if ($pending_assessment == false) {
                                $action_string .= "<li><a  class='btn btn-danger'onClick=\"if(confirm('Are you sure?')){ document.getElementById('warning').value = 0; window.location='" . $button->getLink() . "&id=" . $task->getId() . "'; }else{ return false; }\">" . $button->getTitle() . "</a></li>";
                            } else {
                                $action_string .= "<li><a class='btn btn-danger' onClick=\"alert('Please complete your task first'); return false;\">" . $button->getTitle() . "</a></li>";
                            }
                        }
                    } else {
                        $action_count++;

                        if ($pending_assessment == false) {
                            $action_string .= "<li><a class='btn btn-danger' onClick=\"if(confirm('Are you sure?')){ document.getElementById('warning').value = 0; window.location='" . $button->getLink() . "&id=" . $task->getId() . "'; }else{ return false; }\">" . $button->getTitle() . "</a></li>";
                        } else {
                            $action_string .= "<li><a class='btn btn-danger' onClick=\"alert('Please complete your task first'); return false;\">" . $button->getTitle() . "</a></li>";
                        }
                    }
                }
            }

            ?>
            <ul>
                <?php echo $action_string; ?>
            </ul>
            <?php
        }
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

    .custom-delete-button {
        height: 100%;
        padding-top: 8px;
        padding-bottom: 9px;
    }
</style>

<script language="javascript">

    function clearField(id) {
        switch (id) {
            case 'inv_1':
                $("#inv_1").val(0);
                $("#inv_1").attr('disabled', 'disabled');
                $("#select_fee_1").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_2':
                $("#inv_2").val(0);
                $("#inv_2").attr('disabled', 'disabled');
                $("#select_fee_2").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_3':
                $("#inv_3").val(0);
                $("#inv_3").attr('disabled', 'disabled');
                $("#select_fee_3").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_4':
                $("#inv_4").val(0);
                $("#inv_4").attr('disabled', 'disabled');
                $("#select_fee_4").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_5':
                $("#inv_5").val(0);
                $("#inv_5").attr('disabled', 'disabled');
                $("#select_fee_5").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_6':
                $("#inv_6").val(0);
                $("#inv_6").attr('disabled', 'disabled');
                $("#select_fee_6").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_7':
                $("#inv_7").val(0);
                $("#inv_7").attr('disabled', 'disabled');
                $("#select_fee_7").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_8':
                $("#inv_8").val(0);
                $("#inv_8").attr('disabled', 'disabled');
                $("#select_fee_8").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_9':
                $("#inv_9").val(0);
                $("#inv_9").attr('disabled', 'disabled');
                $("#select_fee_9").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_10':
                $("#inv_10").val(0);
                $("#inv_10").attr('disabled', 'disabled');
                $("#select_fee_10").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_11':
                $("#inv_11").val(0);
                $("#inv_11").attr('disabled', 'disabled');
                $("#select_fee_11").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_12':
                $("#inv_12").val(0);
                $("#inv_12").attr('disabled', 'disabled');
                $("#select_fee_12").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_13':
                $("#inv_13").val(0);
                $("#inv_13").attr('disabled', 'disabled');
                $("#select_fee_13").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_14':
                $("#inv_14").val(0);
                $("#inv_14").attr('disabled', 'disabled');
                $("#select_fee_14").val('0').trigger('change');
                updateTotal();
                break;
            case 'inv_15':
                $("#inv_15").val(0);
                $("#inv_15").attr('disabled', 'disabled');
                $("#select_fee_15").val('0').trigger('change');
                updateTotal();
                break;

            default:
                break;
        }
    }
</script>