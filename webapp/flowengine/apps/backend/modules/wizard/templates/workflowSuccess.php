<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 31/12/2014
 * Time: 00:29
 */

use_helper('I18N');

$wizard_manager = new WizardManager();
?>
<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __("Service Creation Wizard"); ?></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __("You are here"); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("plan/dashboard"); ?>"><?php echo __("Home"); ?></a></li>
            <li class="active"><?php echo __("Workflow"); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?php echo __("Service Creation Wizard"); ?></h4>
                    <p><?php echo __("This wizard will help you configure a basic workflow in minutes. Please take some time to complete the configuration wizards before you use the system."); ?></p>

                </div>
                <div class="panel-body padding-0">

                    <!-- BASIC WIZARD -->
                    <div id="basicWizard" class="basic-wizard">

                        <div class="tab-content">
                            <div class="tab-pane <?php if($wizard_manager->postsetup_resume_step() >=1 && $wizard_manager->postsetup_resume_step() < 5){ echo "active"; } ?>" id="tab2">
                                <div class="contentpanel">

                                    <ul class="nav nav-tabs nav-dark">
                                        <li <?php if($wizard_manager->postsetup_resume_step() == 1){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabdepartments"> <?php if($wizard_manager->postsetup_resume_step() == 3){ echo "&gt;&gt;"; } ?> <strong>1: Departments</strong></a></li>
                                        <li <?php if($wizard_manager->postsetup_resume_step() == 2){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabassignreviewers"> <?php if($wizard_manager->postsetup_resume_step() == 4){ echo "&gt;&gt;"; } ?> <strong>2: Reviewers</strong></a></li>
                                        <li <?php if($wizard_manager->postsetup_resume_step() == 3){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabworkflows"> <?php if($wizard_manager->postsetup_resume_step() == 5){ echo "&gt;&gt;"; } ?> <strong>3: Workflows</strong></a></li>
                                        <li <?php if($wizard_manager->postsetup_resume_step() == 4){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabactions"> <?php if($wizard_manager->postsetup_resume_step() == 6){ echo "&gt;&gt;"; } ?> <strong>4: Transitions</strong></a></li>
                                    </ul>
                                    <div class="tab-content">

                                        <div id="tabdepartments" class="tab-pane <?php if($wizard_manager->postsetup_resume_step() == 1){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/workflow"); ?>">
                                                <input type="hidden" name="step" value="1" />
                                                <div id="departments">
                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label"><?php echo __("Department Name"); ?></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="departmentname[]" onkeyup='checkdepartment(this.value);' class="form-control" required='required'/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-2">
                                                        <button type="button" class="btn btn-primary pull-right" id="adddepartment"><?php echo __("Add Department"); ?></button>
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    <a class="btn btn-primary pull-right" style="margin-left: 10px;" href="/backend.php/wizard/workflow/skip/1" id="submitgroups"><?php echo __("Skip"); ?></a>
                                                    <button type="submit" class="btn btn-success pull-right" id="submitgroups"><?php echo __("Next"); ?></button>
                                                    <script language="javascript">
                                                        jQuery(document).ready(function(){
                                                            $("#adddepartment" ).click(function() {
                                                                $("#departments").append("<div class='form-group'><label class='col-sm-2 control-label'><?php echo __("Department Name"); ?></label><span class='col-sm-8'> <input type='text' name='departmentname[]' onkeyup='checkdepartment(this.value);' class='form-control' required='required'/><a style='float: right; margin-top: 10px margin-right:10px;' href='#' class='panel-close' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
                                                            });
                                                        });
                                                    </script>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabassignreviewers" class="tab-pane <?php if($wizard_manager->postsetup_resume_step() == 2){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/workflow"); ?>">
                                                <input type="hidden" name="step" value="2" />
                                                <div id="users">
                                                    <?php
                                                    $q = Doctrine_Query::create()
                                                        ->from("Department a")
                                                        ->orderBy("a.department_name ASC");
                                                    $departments = $q->execute();

                                                    foreach($departments as $department)
                                                    {
                                                        ?>
                                                        <div class="form-group">
                                                            <label class="col-sm-2 control-label"><?php echo $department->getDepartmentName(); ?></label>
                                                            <div class="col-sm-8">
                                                                <select multiple name="department<?php echo $department->getId(); ?>[]" id="department_<?php echo $department->getId(); ?>_reviewers">
                                                                    <?php
                                                                    $q = Doctrine_Query::create()
                                                                        ->from("CfUser a")
                                                                        ->where("a.bdeleted = 0")
                                                                        ->andWhere("a.strdepartment = ?", '')
                                                                        ->orderBy("a.strfirstname ASC");
                                                                    $reviewers = $q->execute();

                                                                    foreach($reviewers as $reviewer)
                                                                    {
                                                                    ?>
                                                                        <option value="<?php echo $reviewer->getNid() ?>"><?php echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname(); ?></option>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <script language="javascript">
                                                            jQuery(document).ready(function(){

                                                                var department<?php echo $department->getId(); ?> = $('[id="department_<?php echo $department->getId(); ?>_reviewers"]').bootstrapDualListbox();

                                                            });
                                                        </script>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="panel-footer">
                                                    <a class="btn btn-primary pull-right" style="margin-left: 10px;" href="/backend.php/wizard/workflow/skip/2" id="submitgroups"><?php echo __("Skip"); ?></a>
                                                    <button type="submit" class="btn btn-success pull-right" id="submitusers"><?php echo __("Next"); ?></button>
                                                    <a class="btn btn-primary pull-right" style="margin-right: 10px;" href="/backend.php/wizard/workflow/skip/0" id="submitgroups"><?php echo __("Previous"); ?></a>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabworkflows" class="tab-pane <?php if($wizard_manager->postsetup_resume_step() == 3){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/workflow"); ?>">
                                                <input type="hidden" name="step" value="3" />
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label"><h5>Service Title</h5></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="workflow_title" class="form-control" placeholder="Type a title for your workflow..." required='required'/>
                                                    </div>
                                                </div>

                                                <hr>

                                                <div id="stages">

                                                </div>

                                                <div class="form-group">
                                                    <div class="col-sm-2">
                                                        <button type="button" class="btn btn-primary pull-right" id="addstage"><?php echo __("Add Stage"); ?></button>

                                                        <script language="javascript">
                                                            jQuery(document).ready(function(){
                                                                $( "#addstage" ).click(function() {
                                                                    $("#stages").append("<div class='form-group'><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Stage Name'); ?></label><span class='col-sm-8'> <input type='text' name='stagename[]' class='form-control' /></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Stage Type'); ?></label><span class='col-sm-8'><select name='stagetype[]'><option>Choose a stage type...</option><option value='1'>Default</option><option value='8'>Dispatch</option><option value='2'>Assessment</option><option value='3'>Invoicing</option><option value='4'>Approved</option><option value='5'>Corrections</option><option value='6'>Rejected</option><option value='7'>Archived</option></select></span></div><span class='col-sm-10'><a href='#' class='panel-close pull-right' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
                                                                });
                                                            });
                                                        </script>
                                                    </div>
                                                </div>

                                                <div class="panel-footer">
                                                    <a class="btn btn-primary pull-right" style="margin-left: 10px;" href="/backend.php/wizard/workflow/skip/3" id="submitgroups"><?php echo __("Skip"); ?></a>
                                                    <button type="submit" class="btn btn-success pull-right" id="submitusers"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabactions" class="tab-pane <?php if($wizard_manager->postsetup_resume_step() == 4){ echo "active"; } ?>">
                                            <div class="demo statemachine-demo" id="statemachine-demo">
                                                <?php
                                                $q = Doctrine_Query::create()
                                                    ->from("Menus a")
                                                    ->orderBy("a.id DESC");
                                                $workflow = $q->fetchOne();

                                                if($workflow) {
                                                    ?>
                                                    <h3 style="margin-left: 20px;"><?php echo $workflow->getTItle(); ?></h3>
                                                    <?php
                                                    $q = Doctrine_Query::create()
                                                       ->from("SubMenus a")
                                                       ->where("a.menu_id = ?", $workflow->getId())
                                                       ->andWhere("a.deleted = ?", 0);
                                                    $stages = $q->execute();

                                                    $count = 20;
                                                    foreach($stages as $stage) {
                                                        $count += 100;
                                                        ?>
                                                        <div class="w" style="left: <?php echo rand(200, 800); ?>px; top:  <?php echo $count; ?>px;" id="<?php echo $stage->getId(); ?>"><?php echo $stage->getTitle(); ?>
                                                            <div class="ep"></div>
                                                        </div>
                                                    <?php
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <div class="panel-footer">
                                                <a href="/backend.php/wizard/workflow/skip/4" class="btn btn-success pull-right" style="color: #FFF;" id="submitusers"><?php echo __("Next"); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div><!-- tab-content -->
                    </div><!-- #basicWizard -->

                </div><!-- panel-body -->
            </div><!-- panel -->
        </div><!-- col-md-6 -->
    </div>
</div>

    <script>
        jQuery(document).ready(function () {
            <?php
            $q = Doctrine_Query::create()
                ->from('SubMenuButtons a');
            $menu_buttons = $q->execute();
            foreach($menu_buttons as $menu_button)
            {
                 $q = Doctrine_Query::create()
                    ->from('Buttons a')
                    ->where('a.title LIKE ?','accessbutton'.$menu_button->getSubMenuId().'to%');
                $existing_action = $q->fetchOne();

                if($existing_action)
                {
                    $action_title = $existing_action->getTitle();

                    $target_id = str_replace($action_title,'accessbutton'.$menu_button->getSubMenuId(),'');

                    error_log('Debug-b: Deleting '.$action_title.', target '.$target_id);
                    ?>
            jsPlumb.connect({
                source: "<?php echo $menu_button->getSubMenuId(); ?>",
                target: "<?php echo $target_id; ?>"
            });
            <?php
        }
    }
    ?>
        });
    </script>
