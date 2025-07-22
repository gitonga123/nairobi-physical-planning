<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script type="text/javascript" src="/assets_backend/js/bootstrap-datetimepicker.min.js"></script>
<?php
/**
 * newSuccess templates.
 *
 * Allows current reviewer to assign a new task to other reviewers or to themselves
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

$q = Doctrine_Query::create()
    ->from('CfUser a')
    ->where('a.nid = ?', $sf_user->getAttribute('userid'));
$logged_in_reviewer = $q->fetchOne();

if (empty($applications)) {
    $q = Doctrine_Query::create()
        ->from("FormEntry a")
        ->where("a.id = ?", $appid);
    $application = $q->fetchOne();


    $q = Doctrine_Query::create()
        ->from("ApForms a")
        ->where("a.form_active = 1")
        ->andWhere("a.form_type = 2")
        //->andWhere("a.form_department_stage = ?", $application->getApproved())
        ->orderBy("a.form_name ASC");
    $commentsheets = $q->execute();

    $q = Doctrine_Query::create()
        ->from("Invoicetemplates a")
        ->where("a.applicationstage = ?", $application->getApproved());
    $invoices = $q->execute();
}

$allowed_commentsheets = 1;

foreach ($commentsheets as $commentsheet) {
    if ($commentsheet->getFormDepartment() == "") {
        $allowed_commentsheets++;
    } else {
        $q = Doctrine_Query::create()
            ->from("Department a")
            ->where("a.department_name = ?", $logged_in_reviewer->getStrdepartment());
        $department = $q->fetchOne();

        if ($department) {
            //if($commentsheet->getFormDepartment() == $department->getId())
            {
                $allowed_commentsheets++;
            }
        }
    }
}

?>
<div id="basicWizard" class="basic-wizard">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#tabs-1" data-toggle="tab"><?php echo __('Task Details'); ?></a></li>
        <?php
        if (!empty($applications)) {
        ?>
            <li><a href="#tabs-2" data-toggle="tab"><?php echo __('Applications'); ?></a></li>
        <?php
        }
        ?>
        <li><a href="#tabs-3" data-toggle="tab"><?php echo __('Reviewers'); ?></a></li>
    </ul>
    <div class="tab-content tab-content-nopadding">
        <br>
        <div class="tab-pane active" id="tabs-1">
            <div class="form-group">
                <label class="col-sm-4"><?php echo __('Type of task'); ?></label>
                <div class="col-sm-8">
                    <select name="task_type" id="task_type" class="form-control">
                        <?php if ($allowed_commentsheets || !empty($applications)) { ?>
                            <option value="2"><?php echo __('Assessment'); ?></option>
                        <?php } ?>
                        <option value="3"><?php echo __('Invoicing'); ?></option>
                        <?php if ($allowed_commentsheets) { ?>
                            <option value="6"><?php echo __('Inspection'); ?></option>
                            <option value="4"><?php echo __('Scanning'); ?></option>
                            <option value="5"><?php echo __('Collection'); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group" <?php if (!$allowed_commentsheets) {
                                        echo "style='display: none;'";
                                    } ?>>
                <label class="col-sm-4"><?php echo __('Comment sheet'); ?></label>
                <div class="col-sm-8">
                    <select name="task_sheet" id="task_sheet" class="form-control">
                        <option value="0"><?php echo __('Default'); ?></option>
                        <?php
                        foreach ($commentsheets as $commentsheet) {
                            if ($commentsheet->getFormDepartment() == "") {
                                echo "<option value='" . $commentsheet->getFormId() . "'>" . $commentsheet->getFormName() . "</option>";
                            } else {
                                $q = Doctrine_Query::create()
                                    ->from("Department a")
                                    ->where("a.department_name = ?", $logged_in_reviewer->getStrdepartment());
                                $department = $q->fetchOne();

                                if ($department) {
                                    if ($commentsheet->getFormDepartment() == $department->getId()) {
                                        echo "<option value='" . $commentsheet->getFormId() . "'>" . $commentsheet->getFormName() . "</option>";
                                    }
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4"><?php echo __('Description of task'); ?></label>
                <div class="col-sm-8"><textarea id="description" name="description" class="form-control"></textarea></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4"><?php echo __('Start date'); ?></label>
                <div class="col-sm-8"><input type="text" name="start_date" class="form-control" placeholder="yyyy-mm-dd" id="datepicker1">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4"><?php echo __('End date'); ?></label>
                <div class="col-sm-8"><input type="text" name="end_date" class="form-control" placeholder="yyyy-mm-dd" id="datepicker2">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4"><?php echo __('Priority'); ?></label>
                <div class="col-sm-8">
                    <select name="priority" id="priority" class="form-control">
                        <option value="3"><?php echo __('Normal'); ?></option>
                        <option value="2"><?php echo __('Important'); ?></option>
                        <option value="1"><?php echo __('Critical'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tabs-2">
            <?php
            if (empty($applications)) {
            ?>
                <input type="hidden" name="application" id="application" value="<?php echo $appid ?>">
            <?php
            } else {
            ?>
                <div class="form-group">
                    <label class="col-sm-4"><?php echo __('Applications'); ?></label>

                    <div class="col-sm-8">
                        <select name="applications[]" id="applications" multiple data-placeholder="<?php echo __('Choose'); ?>...">
                            <?php
                            foreach ($applications as $application) {
                                echo "<option value='" . $application->getId() . "'>" . $application->getApplicationId() . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <div class="tab-pane" id="tabs-3">

            <div id='concurrent' name='concurrent'>
                <div class="form-group">
                    <label class="col-sm-4"><?php echo __('Reviewers'); ?></label>
                    <div class="col-sm-8">
                        <select name="reviewers[]" id="reviewers" multiple data-placeholder="<?php echo __('Choose'); ?>...">
                            <?php
                            //Add reviewers from same department to the list

                            $q = Doctrine_Query::create()
                                ->from('CfUser a')
                                ->where('a.bdeleted = 0')
                                ->andWhere('a.strdepartment = ?', $logged_in_reviewer->getStrdepartment())
                                ->orderBy('a.strfirstname ASC');
                            $reviewers = $q->execute();
                            
                            foreach ($reviewers as $reviewer) {
                                if ($reviewer->getStrdepartment() == $logged_in_reviewer->getStrdepartment() && $reviewer->confirmCredential("accesssubmenu" . $application->getApproved())) {
                                    echo "<option value='" . $reviewer->getNid() . "'>" . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . "</option>";
                                }
                            }

                            //Get HODs from other departments and assign them to the list as well.
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4"><?php echo __('Other departments'); ?></label>
                    <div class="col-sm-8">
                        <select name="otherreviewers[]" id="otherreviewers" multiple data-placeholder="<?php echo __('Choose'); ?>...">
                            <?php
                            //Get HODs from other departments and assign them to the list as well.
                            $q = Doctrine_Query::create()
                                ->from('Department a')
                                ->where('a.id <> ?', $logged_in_reviewer->getStrdepartment());
                            $departments = $q->execute();
                            foreach ($departments as $department) {
                                if ($department->getDepartmentHead()) {
                                    echo "<option value='" . $department->getDepartmentHead() . "'>" . $department->getDepartmentName() . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id='sequential' name='sequential' style="display: none;">

                <div class="form-group">
                    <label class="col-sm-4"><?php echo __('Preconfigured workflow'); ?></label>
                    <div class="col-sm-8">
                        <select name="workflow" id="workflow" class="form-control" onChange="if(this.value == 'none'){ document.getElementById('reviewers_div').style.display = 'block'; }else{ document.getElementById('reviewers_div').style.display = 'none';  }">
                            <option value="none"><?php echo __('None'); ?></option>
                            <?php
                            $q = Doctrine_Query::create()
                                ->from('Workflow a')
                                ->orderBy('a.workflow_title ASC');
                            $workflows = $q->execute();
                            foreach ($workflows as $workflow) {
                                echo "<option value='" . $workflow->getId() . "'>" . $workflow->getWorkflowTitle() . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <?php

                if (empty($applications)) {
                    $q = Doctrine_Query::create()
                        ->from("SubMenus a")
                        ->where("a.id = ?", $application->getApproved());
                    $child_stage = $q->fetchOne();

                    $q = Doctrine_Query::create()
                        ->from("SubMenus a")
                        ->where("a.menu_id = ?", $child_stage->getMenuId())
                        ->orderBy("a.order_no ASC");
                    $stages = $q->execute();
                }

                foreach ($stages as $stage) {
                ?>
                    <div class="form-group">
                        <label class="col-sm-4"><?php echo $stage->getTitle(); ?></label>
                        <div class="col-sm-8">
                            <select name="reviewers_<?php echo $stage->getId() ?>[]" id="reviewers" multiple data-placeholder="<?php echo __('Choose'); ?>...">
                                <?php

                                //Add reviewers from same department to the list
                                $q = Doctrine_Query::create()
                                    ->from('CfUser a')
                                    ->where('a.bdeleted = 0')
                                    ->andWhere('a.strdepartment LIKE ?', '%' . $logged_in_reviewer->getStrdepartment() . '%')
                                    ->orderBy('a.Strfirstname ASC');
                                $reviewers = $q->execute();
                                foreach ($reviewers as $reviewer) {
                                    echo "<option value='" . $reviewer->getNid() . "'>" . $reviewer->getStrfirstname() . " " . $reviewer->getStrlastname() . "</option>";
                                }

                                $q = Doctrine_Query::create()
                                    ->from('Department a')
                                    ->where('a.department_name <> ?', $logged_in_reviewer->getStrdepartment());
                                $departments = $q->execute();
                                foreach ($departments as $department) {
                                    if ($department->getDepartmentHead()) {
                                        $q = Doctrine_Query::create()
                                            ->from('CfUser a')
                                            ->where('a.nid = ?', $department->getDepartmentHead());
                                        $reviewer = $q->fetchOne();
                                        if ($reviewer) {
                                            echo "<option value='" . $reviewer->getNid() . "'>" . $department->getDepartmentName() . "</option>";
                                        }
                                    }
                                }

                                //Get HODs from other departments and assign them to the list as well.
                                ?>
                            </select>
                        </div>
                    </div>
                <?php
                }
                ?>

            </div>

            <br>

            <div class="form-group" style="display: none;">
                <label class="col-sm-4"><?php echo __('Workflow'); ?></label>
                <div class="col-sm-8">
                    <select name='workflow_type' id='workflow_type' class="form-control" onChange="if(this.value == 1){ document.getElementById('sequential').style.display = 'block'; document.getElementById('concurrent').style.display = 'none'; }else{ document.getElementById('sequential').style.display = 'none'; document.getElementById('concurrent').style.display = 'block'; }">
                        <option value="0"><?php echo __('Concurrent'); ?></option>
                        <option value="1"><?php echo __('Sequential'); ?></option>
                    </select>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(function() {
        var demo1 = $('[id="applications"]').bootstrapDualListbox();
        var demo2 = $('[id="reviewers"]').bootstrapDualListbox();
        var demo3 = $('[id="supporters"]').bootstrapDualListbox();
        var demo4 = $('[id="otherreviewers"]').bootstrapDualListbox();

        // Date Picker

        $('#datepicker1').datetimepicker({
            minDate: 0,
            numberOfMonths: 2,
            onSelect: function(selected) {
                $("#datepicker2").datetimepicker("option", "minDate", selected)
            }
        });
        $('#datepicker2').datetimepicker({
            minDate: 0,
            numberOfMonths: 2,
            onSelect: function(selected) {
                $("#datepicker1").datetimepicker("option", "maxDate", selected)
            }
        });
    });
</script>