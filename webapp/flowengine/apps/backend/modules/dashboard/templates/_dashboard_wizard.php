<?php
/**
 * _dashboard_wizard.php partial.
 *
 * Displays a system configuration wizard after system installation
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper('I18N');
?>
<link rel="stylesheet" href="/assets_backend/jsPlumb/css/jsplumb.css">
<link rel="stylesheet" href="/assets_backend/jsPlumb/demo/statemachine/demo.css">

<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __("This is your first time. Please configure your system."); ?></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __("You are here"); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="/backend.php"><?php echo __("Home"); ?></a></li>
            <li class="active"><?php echo __("Workflow"); ?></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?php echo __("System Configuration Wizard"); ?></h4>
                    <p><?php echo __("This wizard will help you configure a basic workflow in minutes. Please take some time to complete the configuration wizards before you use the system."); ?></p>
                    <p>
                        <div class="progress progress-striped">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $wizard_manager->resume_step() * 10; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo ($wizard_manager->resume_step()/14) * 100; ?>%">
                                <span class="sr-only"><?php echo ($wizard_manager->resume_step()/14) * 100; ?>% Complete (success)</span>
                            </div>
                        </div>
                    </p>
                </div>
                <div class="panel-body padding-0">

                    <!-- BASIC WIZARD -->
                    <div id="basicWizard" class="basic-wizard">

                        <ul class="nav nav-pills nav-justified nav-dark">
                            <li <?php if($wizard_manager->resume_step() < 3){ echo "class='active'"; } ?>><a href="#tab1" data-toggle="tab"><span><?php echo __("Step 1"); ?>:</span> <?php echo __("Security Configuration"); ?></a></li>
                            <li <?php if($wizard_manager->resume_step() >= 3 && $wizard_manager->resume_step() < 7){ echo "class='active'"; } ?>><a href="#tab2" data-toggle="tab"><span><?php echo __("Step 2"); ?>:</span> <?php echo __("Workflow Configuration"); ?></a></li>
                            <li <?php if($wizard_manager->resume_step() >= 7 && $wizard_manager->resume_step() <= 9){ echo "class='active'"; } ?>><a href="#tab3" data-toggle="tab"><span><?php echo __("Step 3"); ?>:</span> <?php echo __("Inputs/Outputs Configuration"); ?></a></li>
                            <li <?php if($wizard_manager->resume_step() >= 10){ echo "class='active'"; } ?>><a href="#tab4" data-toggle="tab"><span><?php echo __("Step 4"); ?>:</span> <?php echo __("Other Configuration"); ?></a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane <?php if($wizard_manager->resume_step() < 3){ echo "active"; } ?>" id="tab1">

                                <div class="contentpanel">

                                    <ul class="nav nav-tabs nav-dark">
                                        <li <?php if($wizard_manager->resume_step() == 0){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabgroups"><strong> <?php if($wizard_manager->resume_step() == 0){ echo "&gt;&gt;"; } ?> <?php echo __("1"); ?>: <?php echo __("Group"); ?>s</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 1){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabcredentials"><strong> <?php if($wizard_manager->resume_step() == 1){ echo "&gt;&gt;"; } ?> <?php echo __("2"); ?>: <?php echo __("Credentials"); ?></strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 2){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabreviewers"><strong> <?php if($wizard_manager->resume_step() == 2){ echo "&gt;&gt;"; } ?> <?php echo __("3"); ?>: <?php echo __("Reviewers"); ?></strong></a></li>
                                    </ul>
                                    <div class="tab-content">

                                        <div id="tabgroups" class="tab-pane <?php if($wizard_manager->resume_step() == 0){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/security"); ?>">
                                                <input type="hidden" name="step" value="1" />
                                                <div id="groups">
                                                    <?php
                                                    if(sizeof($departments) <= 0)
                                                    {
                                                        ?>
                                                        <div class="form-group">
                                                            <label class="col-sm-2 control-label"><?php echo __('Name'); ?></label>
                                                            <div class="col-sm-8">
                                                                <input type="text" name="name[]" readonly="readonly" class="form-control" value="System Administrators" required/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-sm-2 control-label"><?php echo __('Description'); ?></label>
                                                            <div class="col-sm-8">
                                                                <textarea name="description[]" readonly="readonly"  class="form-control" required/><?php echo __("System administrators who will manage the system"); ?></textarea>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-2">
                                                        <button type="button" class="btn btn-primary pull-right" id="addgroup"><?php echo __('Add Group'); ?></button>
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" id="submitgroups"><?php echo __('Next'); ?></button>
                                                </div>
                                                <script language="javascript">
                                                    jQuery(document).ready(function(){
                                                        $( "#addgroup" ).click(function() {
                                                            $("#groups").append("<div class='form-group'><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Name'); ?></label><span class='col-sm-8'> <input type='text' name='name[]' onKeyUp='checkgroup(this.value);'  class='form-control' /></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Description'); ?></label><span class='col-sm-8'><textarea name='description[]' class='form-control' /></textarea></span></div><span class='col-sm-10'><a href='#' class='panel-close pull-right' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
                                                        });
                                                    });
                                                </script>
                                            </form>
                                        </div><!-- tab-pane -->

                                        <div id="tabcredentials" class="tab-pane <?php if($wizard_manager->resume_step() == 1){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/security"); ?>">
                                                <input type="hidden" name="step" value="2" />
                                                <?php
                                                $count = 0;
                                                foreach($groups as $group)
                                                {
                                                    if(sizeof($group->getUsers()) > 0)
                                                    {
                                                        continue;
                                                    }
                                                    $count++;
                                                    ?>
                                                    <div class="form-group">
                                                        <label class="col-sm-2 control-label"><?php echo $group->getName(); ?></label>
                                                        <div class="col-sm-8">
                                                            <select multiple name="group<?php echo $group->getId(); ?>[]" id="group_<?php echo $group->getId(); ?>_roles">
                                                                <?php
                                                                foreach($permissions as $permission)
                                                                {
                                                                    $selected = "";
                                                                    if($count == 1)
                                                                    {
                                                                        $selected = "selected";
                                                                    }

                                                                    if($permission->getId() == 445 || $permission->getId() == 446 || $permission->getId() == 208)
                                                                    {
                                                                        $selected = "selected";
                                                                    }
                                                                    ?>
                                                                    <option value="<?php echo $permission->getId() ?>" <?php echo $selected; ?>><?php echo $permission->getDescription(); ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <script language="javascript">
                                                        jQuery(document).ready(function(){

                                                            var demo<?php echo $group->getId(); ?> = $('[id="group_<?php echo $group->getId(); ?>_roles"]').bootstrapDualListbox();

                                                        });
                                                    </script>
                                                <?php
                                                }
                                                ?>
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success" id="submitpermissions" style="float: right;"><?php echo __('Next'); ?></button>
                                                </div>
                                            </form>
                                        </div><!-- tab-pane -->

                                        <div id="tabreviewers" class="tab-pane <?php if($wizard_manager->resume_step() == 2){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/security"); ?>">
                                                <input type="hidden" name="step" value="3" />
                                                <div id="users">
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-2">
                                                        <button type="button" class="btn btn-primary pull-right" id="adduser"><?php echo __('Add Reviewer'); ?></button>
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" id="submitusers"><?php echo __('Next'); ?></button>
                                                </div>
                                                <script language="javascript">
                                                    jQuery(document).ready(function(){
                                                        $( "#adduser" ).click(function() {
                                                            $("#users").append("<div class='form-group'><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Username'); ?></label><span class='col-sm-8'><input type='text' name='username[]' onKeyUp='checkuser(this.value);' class='form-control' placeholder='<?php echo __('Username'); ?>' required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Name'); ?></label><span class='col-sm-8'><input type='text' name='firstname[]' class='form-control mb10' placeholder='<?php echo __('First Name'); ?>' required='required'/><input type='text' name='lastname[]' class='form-control' placeholder='<?php echo __('Last Name'); ?>' required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Password'); ?></label><span class='col-sm-8'><input type='password' name='userpassword[]' class='form-control'  required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Email'); ?></label><span class='col-sm-8'><input type='text' name='useremail[]' onKeyUp='checkemail(this.value);' class='form-control'  required='required'/></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Group'); ?></label><span class='col-sm-8'><select class='form-control' name='group[]' required='required'><option value=''><?php echo __('Choose One'); ?></option><?php
                                                                foreach($groups as $group)
                                                                {
                                                                ?><option value='<?php echo $group->getId() ?>'><?php echo $group->getName(); ?></option><?php
                                                                }
                                                                ?></select></span></div><span class='col-sm-10'><a href='#' class='panel-close pull-right' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
                                                        });
                                                    });
                                                </script>
                                            </form>
                                        </div><!-- tab-pane -->

                                    </div><!-- tab-content -->

                                </div>

                            </div>
                            <div class="tab-pane <?php if($wizard_manager->resume_step() >= 3 && $wizard_manager->resume_step() < 7){ echo "active"; } ?>" id="tab2">
                                <div class="contentpanel">

                                    <ul class="nav nav-tabs nav-dark">
                                        <li <?php if($wizard_manager->resume_step() == 3){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabdepartments"> <?php if($wizard_manager->resume_step() == 3){ echo "&gt;&gt;"; } ?> <strong>1: Departments</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 4){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabassignreviewers"> <?php if($wizard_manager->resume_step() == 4){ echo "&gt;&gt;"; } ?> <strong>2: Reviewers</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 5){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabworkflows"> <?php if($wizard_manager->resume_step() == 5){ echo "&gt;&gt;"; } ?> <strong>3: Workflows</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 6){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabactions"> <?php if($wizard_manager->resume_step() == 6){ echo "&gt;&gt;"; } ?> <strong>4: Transitions</strong></a></li>
                                    </ul>
                                    <div class="tab-content">

                                        <div id="tabdepartments" class="tab-pane <?php if($wizard_manager->resume_step() == 3){ echo "active"; } ?>">
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

                                        <div id="tabassignreviewers" class="tab-pane <?php if($wizard_manager->resume_step() == 4){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/workflow"); ?>">
                                                <input type="hidden" name="step" value="2" />
                                                <div id="users">
                                                    <?php
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
                                                    <button type="submit" class="btn btn-success pull-right" id="submitusers"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabworkflows" class="tab-pane <?php if($wizard_manager->resume_step() == 5){ echo "active"; } ?>">
                                            <form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("plan/wizard/workflow"); ?>">
                                                <input type="hidden" name="step" value="3" />
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label"><h5>Workflow Category</h5></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="workflow_category" class="form-control" placeholder="Enter the workflow category" required='required'/>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label"><h5>Workflow Category Description</h5></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="workflow_category_desc" class="form-control" placeholder="Enter the workflow category description" required='required'/>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label"><h5>Workflow Title</h5></label>
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
                                                                    $("#stages").append("<div class='form-group'><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Stage Name'); ?></label><span class='col-sm-8'> <input type='text' name='stagename[]' class='form-control' /></span></div><div class='form-group'><label class='col-sm-2 control-label'><?php echo __('Stage Type'); ?></label><span class='col-sm-8'><select name='stagetype[]'><option>Choose a stage type...</option><option value='1'>Default</option><option value='8'>Dispatch</option><option value='2'>Assessment</option><option value='3'>Payment</option><option value='4'>Approved</option><option value='5'>Corrections</option><option value='6'>Rejected</option><option value='7'>Archived</option><option value='10'>Renewal</option><option value='11'>Agenda</option><option value='12'>Expired</option></select></span></div><span class='col-sm-10'><a href='#' class='panel-close pull-right' onClick='$(this).closest(\"div\").remove();'><span class='badge badge-danger'>&times;</span></a></span></div>");
                                                                });
                                                            });
                                                        </script>
                                                    </div>
                                                </div>

                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" id="submitusers"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabactions" class="tab-pane <?php if($wizard_manager->resume_step() == 6){ echo "active"; } ?>">
                                            <div class="demo statemachine-demo" id="statemachine-demo">
                                                <?php
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
                                                <a href="/backend.php/dashboard" class="btn btn-success pull-right" style="color: #FFF;" id="submitusers"><?php echo __("Next"); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane <?php if($wizard_manager->resume_step() >= 7 && $wizard_manager->resume_step() <= 9){ echo "active"; } ?>" id="tab3">
                                <div class="contentpanel">
                                    <ul class="nav nav-tabs nav-dark">
                                        <li <?php if($wizard_manager->resume_step() == 7){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabforms"> <?php if($wizard_manager->resume_step() == 7){ echo "&gt;&gt;"; } ?> <strong>1: Forms</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 8){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabinvoices"> <?php if($wizard_manager->resume_step() == 8){ echo "&gt;&gt;"; } ?> <strong>2: Invoices/Receipts</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 9){ echo "class='active'"; } ?>><a data-toggle="tab" href="#taboutputs"> <?php if($wizard_manager->resume_step() == 9){ echo "&gt;&gt;"; } ?> <strong>3: Services</strong></a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div id="tabforms" class="tab-pane <?php if($wizard_manager->resume_step() == 7){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 7) {
												//OTB Add
												//Form category
												$q=Doctrine_Query::create()
													->from('FormGroups g');
												$form_groups=$q->count();
												if($form_groups){
													header("Location: /backend.php/forms/form");
													exit;
												}else{
													header("Location: /backend.php/formgroups/new");
													exit;
												}
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                            <form class="form-horizontal form-bordered" method="post" action="/backend.php/dashboard">
                                                <input type="hidden" name="step" value="8" />
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px;" id="submitgroups"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabinvoices" class="tab-pane <?php if($wizard_manager->resume_step() == 8){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 8) {
                                                ?>
                                                <h2>Creating an invoice template</h2>
                                                <form id="invoicetemplatesform" class="form-bordered" action="/backend.php/<?php echo 'invoicetemplates/'.($invoice_form->getObject()->isNew() ? 'create' : 'update'.(!$invoice_form->getObject()->isNew() ? '?id='.$invoice_form->getObject()->getId() : '')) ?>" method="post">
                                                <div class="panel-body padding-0">
                                                <?php if (!$invoice_form->getObject()->isNew()): ?>
                                                    <input type="hidden" name="sf_method" value="put" />
                                                <?php endif; ?>
                                                <?php echo $invoice_form->renderHiddenFields(false) ?>
                                                <?php echo $invoice_form->renderGlobalErrors() ?>

                                                <div class="form-group">
                                                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Invoice Title'); ?></i></label><br>
                                                    <div class="col-sm-12 rogue-input">
                                                        <?php echo $invoice_form['title']->renderError() ?>
                                                        <?php echo $invoice_form['title'] ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-sm-12">
                                                        <label class="col-sm-4"><i class="bold-label"><?php echo __('Application Form'); ?></i></label><br>
                                                        <?php echo $invoice_form['applicationform']->renderError() ?>
                                                        <select name="invoicetemplates[applicationform]" class="form-control" id="invoicetemplates_applicationform"  onChange="ajaxFetchPermitsettings(this.value);">
                                                            <option value=""></option>
                                                            <?php
                                                            $q = Doctrine_Query::create()
                                                                ->from('ApForms a')
                                                                ->where('a.form_id <> 15 AND a.form_id <> 16 AND a.form_id <> 17 AND  a.form_id <> 6 AND a.form_id <> 7')
                                                                ->andWhere('a.form_active = 1 AND a.form_type = 1')
                                                                ->orderBy('a.form_name ASC');
                                                            $applicationforms = $q->execute();
                                                            foreach($applicationforms as $appform)
                                                            {
                                                                if($invoice_form->getObject()->isNew())
                                                                {
                                                                    echo "<option value='".$appform->getFormId()."'>".$appform->getFormName()."</option>";
                                                                }
                                                                else
                                                                {
                                                                    if($invoice_form->getObject()->getApplicationform() == $appform->getFormId())
                                                                    {
                                                                        echo "<option value='".$appform->getFormId()."' selected>".$appform->getFormName()."</option>";
                                                                    }
                                                                    else
                                                                    {
                                                                        echo "<option value='".$appform->getFormId()."'>".$appform->getFormName()."</option>";
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12">
                                                        <label class="col-sm-4"><i class="bold-label"><?php echo __('Application Stage'); ?></i></label><br>
                                                        <?php echo $invoice_form['applicationstage']->renderError() ?>
                                                        <select name="invoicetemplates[applicationstage]" class="form-control" id="invoicetemplates_applicationstage">
                                                            <option><?php echo __('Choose a stage'); ?></option>
                                                            <?php
                                                            $q = Doctrine_Query::create()
                                                                ->from("Menus a")
                                                                ->orderBy("a.title ASC");
                                                            $stage_groups = $q->execute();

                                                            foreach($stage_groups as $stage_group)
                                                            {
                                                                ?>
                                                                <optgroup label="<?php echo $stage_group->getTitle(); ?>">
                                                                    <?php
                                                                    $q = Doctrine_Query::create()
                                                                        ->from("SubMenus a")
                                                                        ->where("a.menu_id = ?", $stage_group->getId())
                                                                        ->andWhere("a.deleted = 0")
                                                                        ->orderBy("a.order_no ASC");
                                                                    $stages = $q->execute();

                                                                    foreach($stages as $stage)
                                                                    {
                                                                        $selected = "";
                                                                        if(!$invoice_form->getObject()->isNew())
                                                                        {
                                                                            if($stage->getId() == $invoice_form->getObject()->getApplicationstage())
                                                                            {
                                                                                $selected = "selected='selected'";
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <option value="<?php echo $stage->getId(); ?>" <?php echo $selected; ?>><?php echo $stage->getTitle(); ?></option>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </optgroup>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Invoice number of the first invoice'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $invoice_form['invoice_number']->renderError() ?>
                                                        <input type="text" class="form-control" name="invoicetemplates[invoice_number]" id="invoicetemplates_invoice_number" value="<?php if(!$invoice_form->getObject()->isNew()){ echo $invoice_form->getObject()->getInvoiceNumber(); }else{ echo "0"; } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Maximum number of days before expiration'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $invoice_form['max_duration']->renderError() ?>
                                                        <input type="text" class="form-control" name="invoicetemplates[max_duration]" id="invoicetemplates_max_duration" value="<?php if(!$invoice_form->getObject()->isNew()){ echo $invoice_form->getObject()->getMaxDuration(); }else{ echo "0"; } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Maximum number of days till due date'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $invoice_form['due_duration']->renderError() ?>
                                                        <input type="text" class="form-control" name="invoicetemplates[due_duration]" id="invoicetemplates_due_duration" value="<?php if(!$invoice_form->getObject()->isNew()){ echo $invoice_form->getObject()->getDueDuration(); }else{ echo "0"; } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                <label class="col-sm-4"><i class="bold-label"><?php echo __('Content'); ?></i></label><br>
                                                <div class="col-sm-12">
                                                    <?php echo $invoice_form['content']->renderError() ?>
                                                    <?php
                                                    if($invoice_form->getObject()->isNew())
                                                    {
                                                        ?>
                                                        <textarea class="form-control" rows="30" name="invoicetemplates[content]" id="invoicetemplates_content" ></textarea>
                                                    <?php
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                        <textarea class="form-control" rows="30" name="invoicetemplates[content]" id="invoicetemplates_content" ><?php echo $invoice_form->getObject()->getContent(); ?></textarea>
                                                    <?php
                                                    }
                                                    ?>

                                                    <script src="/assets_backend/js/ckeditor/ckeditor.js"></script>
                                                    <script src="/assets_backend/js/ckeditor/adapters/jquery.js"></script>

                                                    <script>
                                                        jQuery(document).ready(function(){

                                                            // CKEditor
                                                            jQuery('#invoicetemplates_content').ckeditor();

                                                        });
                                                    </script>

                                                </div>
                                                </div>
                                                <div class="form-group">
                                                <div class="col-sm-12 alignright">
                                                    <button type="button" class="btn btn-primary" data-target="#fieldsModal" data-toggle="modal">View available user/form fields</button>
                                                </div>
                                                <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="fieldsModal" class="modal fade" style="display: none;">
                                                <div class="modal-dialog">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                                    <h4 id="myModalLabel" class="modal-title">View available user/form fields</h4>
                                                </div>
                                                <div class="modal-body">
                                                <div class="form-group">
                                                <?php
                                                if(!$invoice_form->getObject()->isNew())
                                                {
                                                    $appform = $invoice_form->getObject()->getApplicationform();
                                                    ?>
                                                    <?php
                                                    //Get User Information (anything starting with sf_ )
                                                    //sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
                                                    ?>

                                                    <table class="table dt-on-steroids mb0">
                                                        <thead><tr><th width="50%"><?php echo __('Applicant Details'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                        <tbody>
                                                        <tr><td><?php echo __('User ID'); ?></td><td>{sf_username}</td></tr>
                                                        <tr><td><?php echo __('Mobile Number'); ?></td><td>{sf_mobile}</td></tr>
                                                        <tr><td><?php echo __('Email'); ?></td><td>{sf_email}</td></tr>
                                                        <tr><td><?php echo __('Full Name'); ?></td><td>{sf_fullname}</td></tr>
                                                        <?php
                                                        $q = Doctrine_Query::create()
                                                            ->from('apFormElements a')
                                                            ->where('a.form_id = ?', 15)
                                                            ->andWhere('a.element_status = ?', 1);

                                                        $elements = $q->execute();

                                                        foreach($elements as $element)
                                                        {
                                                            $childs = $element->getElementTotalChild();
                                                            if($childs == 0)
                                                            {
                                                                echo "<tr><td>".$element->getElementTitle()."</td><td>{sf_element_".$element->getElementId()."}</td></tr>";
                                                            }
                                                            else
                                                            {
                                                                if($element->getElementType() == "select")
                                                                {
                                                                    echo "<tr><td>".$element->getElementTitle()."</td><td>{sf_element_".$element->getElementId()."}</td></tr>";
                                                                }
                                                                else
                                                                {
                                                                    for($x = 0; $x < ($childs + 1); $x++)
                                                                    {
                                                                        echo "<tr><td>".$element->getElementTitle()."</td><td>{sf_element_".$element->getElementId()."_".($x+1)."}</td></tr>";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                    <?php
                                                    //Get Application Information (anything starting with ap_ )
                                                    //ap_application_id

                                                    //Get Form Details (anything starting with fm_ )
                                                    //fm_created_at, fm_updated_at.....fm_element_1
                                                    ?>
                                                    <table class="table dt-on-steroids mb0">
                                                        <thead><tr><th width="50%"><?php echo __('Application Details'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                        <tbody>
                                                        <tr><td><?php echo __('Application Number'); ?></td><td>{ap_application_id}</td></tr>
                                                        <tr><td><?php echo __('Created At'); ?></td><td>{fm_created_at}</td></tr>
                                                        <tr><td><?php echo __('Approved At'); ?></td><td>{fm_updated_at}</td></tr>
                                                        <?php
                                                        if(!$invoice_form->getObject()->isNew())
                                                        {
                                                            $appform = $invoice_form->getObject()->getApplicationform();
                                                            ?>
                                                            <?php

                                                            $q = Doctrine_Query::create()
                                                                ->from('apFormElements a')
                                                                ->where('a.form_id = ?', $appform)
                                                                ->andWhere('a.element_status = ?', 1)
                                                                ->orderBy('a.element_position ASC');

                                                            $elements = $q->execute();

                                                            foreach($elements as $element)
                                                            {
                                                                $childs = $element->getElementTotalChild();
                                                                if($childs == 0)
                                                                {
                                                                    if($element->getElementType() == "select")
                                                                    {
                                                                        if($element->getElementExistingForm() && $element->getElementExistingStage())
                                                                        {
                                                                            $q = Doctrine_Query::create()
                                                                                ->from("ApForms a")
                                                                                ->where("a.form_id = ?", $element->getElementExistingForm());
                                                                            $child_form = $q->fetchOne();

                                                                            echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."} ";
                                                                            echo '<table class="table dt-on-steroids mb0">
                                                                             <thead><tr><th width="50%">'.__($child_form->getFormName().' Details').'</th><th>'.__('Tag').'</th></tr></thead>
                                                                             <tbody>';

                                                                            ?>
                                                                            <tr><td><?php echo __('Application Number'); ?></td><td>{ap_child_application_id}</td></tr>
                                                                            <tr><td><?php echo __('Created At'); ?></td><td>{fm_child_created_at}</td></tr>
                                                                            <tr><td><?php echo __('Approved At'); ?></td><td>{fm_child_updated_at}</td></tr>
                                                                            <?php
                                                                            $q = Doctrine_Query::create()
                                                                                ->from("Permits a")
                                                                                ->where("a.applicationform = ?", $element->getElementExistingForm());
                                                                            $permits = $q->execute();

                                                                            foreach($permits as $permit)
                                                                            {
                                                                                echo "<tr><td>".$permit->getTitle()." ID</td><td>{ap_permit_id_".$permit->getId()."_element_child}</td></tr>";
                                                                            }

                                                                            $q = Doctrine_Query::create()
                                                                                ->from('apFormElements a')
                                                                                ->where('a.form_id = ?', $element->getElementExistingForm())
                                                                                ->andWhere('a.element_status = ?', 1)
                                                                                ->orderBy('a.element_position ASC');

                                                                            $child_elements = $q->execute();

                                                                            foreach($child_elements as $child_element)
                                                                            {

                                                                                //START CHILD ELEMENTS
                                                                                $childs = $child_element->getElementTotalChild();
                                                                                if($childs == 0)
                                                                                {
                                                                                    if($child_element->getElementType() == "select")
                                                                                    {
                                                                                        if($child_element->getElementExistingForm() && $child_element->getElementExistingStage())
                                                                                        {

                                                                                            $q = Doctrine_Query::create()
                                                                                                ->from("ApForms a")
                                                                                                ->where("a.form_id = ?", $child_element->getElementExistingForm());
                                                                                            $grand_form = $q->fetchOne();

                                                                                            echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."} ";
                                                                                            echo '<table class="table dt-on-steroids mb0">
                                                                                           <thead><tr><th width="50%">'.__($grand_form->getFormName().' Details').'</th><th>'.__('Tag').'</th></tr></thead>
                                                                                           <tbody>';

                                                                                            $q = Doctrine_Query::create()
                                                                                                ->from('apFormElements a')
                                                                                                ->where('a.form_id = ?', $child_element->getElementExistingForm())
                                                                                                ->andWhere('a.element_status = ?', 1)
                                                                                                ->orderBy('a.element_position ASC');

                                                                                            $grand_child_elements = $q->execute();

                                                                                            foreach($grand_child_elements as $grand_child_element)
                                                                                            {
                                                                                                ?>
                                                                                                <tr><td><?php echo __('Application Number'); ?></td><td>{ap_grand_child_application_id}</td></tr>
                                                                                                <tr><td><?php echo __('Created At'); ?></td><td>{fm_grand_child_created_at}</td></tr>
                                                                                                <tr><td><?php echo __('Approved At'); ?></td><td>{fm_grand_child_updated_at}</td></tr>
                                                                                                <?php
                                                                                                $q = Doctrine_Query::create()
                                                                                                    ->from("Permits a")
                                                                                                    ->where("a.applicationform = ?", $child_element->getElementExistingForm());
                                                                                                $permits = $q->execute();

                                                                                                foreach($permits as $permit)
                                                                                                {
                                                                                                    echo "<tr><td>".$permit->getTitle()." ID</td><td>{ap_permit_id_".$permit->getId()."_element_grand_child}</td></tr>";
                                                                                                }

                                                                                                //START GRAND CHILD ELEMENTS
                                                                                                $childs = $grand_child_element->getElementTotalChild();
                                                                                                if($childs == 0)
                                                                                                {
                                                                                                    if($grand_child_element->getElementType() == "select")
                                                                                                    {
                                                                                                        echo "<tr><td>".$grand_child_element->getElementTitle()."</td><td>{fm_grand_child_element_".$grand_child_element->getElementId()."}</td></tr>";
                                                                                                    }
                                                                                                    else
                                                                                                    {
                                                                                                        echo "<tr><td>".$grand_child_element->getElementTitle()."</td><td>{fm_grand_child_element_".$grand_child_element->getElementId()."}</td></tr>";
                                                                                                    }
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    for($x = 0; $x < ($childs + 1); $x++)
                                                                                                    {
                                                                                                        echo "<tr><td>".$grand_child_element->getElementTitle()."</td><td>{fm_grand_child_element_".$grand_child_element->getElementId()."_".($x+1)."}</td></tr>";
                                                                                                    }
                                                                                                }
                                                                                                //END GRAND CHILD ELEMENTS
                                                                                            }

                                                                                            echo '</tbody></table>';
                                                                                            echo "</td></tr>";
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                            echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."}</td></tr>";
                                                                                        }
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                        echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."}</td></tr>";
                                                                                    }
                                                                                }
                                                                                else
                                                                                {
                                                                                    for($x = 0; $x < ($childs + 1); $x++)
                                                                                    {
                                                                                        echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."_".($x+1)."}</td></tr>";
                                                                                    }
                                                                                }
                                                                                //END CHILD ELEMENTS
                                                                            }

                                                                            echo '</tbody></table>';
                                                                            echo "</td></tr>";
                                                                        }
                                                                        else
                                                                        {
                                                                            echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."}</td></tr>";
                                                                        }
                                                                    }
                                                                    else
                                                                    {
                                                                        echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."}</td></tr>";
                                                                    }
                                                                }
                                                                else
                                                                {
                                                                    for($x = 0; $x < ($childs + 1); $x++)
                                                                    {
                                                                        echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."_".($x+1)."}</td></tr>";
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        <?php
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>

                                                    <table class="table dt-on-steroids mb0">
                                                        <thead><tr><th width="50%"><?php echo __('Conditions Of Approval'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                        <tbody>
                                                        <tr><td><?php echo __('Conditions Of Approval'); ?></td><td>{ca_conditions}</td></tr>
                                                        </tbody>
                                                    </table>

                                                <?php
                                                }
                                                ?>
                                                
                                                <table class="table dt-on-steroids mb0">
                                                    <thead><tr><th width="50%"><?php echo __('Invoice Details'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                    <tbody>
                                                    <tr><td><?php echo __('Invoice No'); ?></td><td>{inv_no}</td></tr>
                                                    <tr><td><?php echo __('Invoice Date'); ?></td><td>{inv_date_created}</td></tr>
                                                    <tr><td><?php echo __('Invoice Expiry Date'); ?></td><td>{inv_expires_at}</td></tr>
                                                    <tr><td><?php echo __('List of Fees'); ?></td><td>{inv_fee_table}</td></tr>
                                                    <tr><td><?php echo __('Total'); ?></td><td>{in_total}</td></tr>
                                                    <tr><td><?php echo __('Invoice Status'); ?></td><td>{inv_status}</td></tr>
                                                    <tr><td><?php echo __('Payment Mode'); ?></td><td>{inv_payment_merchant_type}</td></tr>
                                                    <tr><td><?php echo __('Payment Reference Number'); ?></td><td>{inv_payment_id}</td></tr>
                                                    <tr><td><?php echo __('QR Code'); ?></td><td>{qr_code}</td></tr>
                                                    <tr><td><?php echo __('QR Code (Small)'); ?></td><td>{qr_code_small}</td></tr>
                                                    <tr><td><?php echo __('Bar Code'); ?></td><td>{bar_code}</td></tr>
                                                    <tr><td><?php echo __('Bar Code (Small)'); ?></td><td>{bar_code_small}</td></tr>
                                                    </tbody>
                                                </table>

                                                </div>
                                                <div class="modal-footer">
                                                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                                                </div>
                                                </div><!-- modal-content -->
                                                </div><!-- modal-dialog -->
                                                </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12" id="loadinner" name="loadinner">

                                                    </div>

                                                    <?php
                                                    $invoiceid = 0;
                                                    if(!$invoice_form->getObject()->isNew())
                                                    {
                                                        $invoiceid = $invoice_form->getObject()->getId();
                                                    }
                                                    ?>
                                                </div>

                                                <div class="panel-footer">
                                                    <button class='btn btn-danger mr10'><?php echo __('Reset'); ?></button><button type="submit" class='btn btn-primary' name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button></div>
                                                </div>
                                                </div>
                                                </form>
                                            <?php
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                        </div>

                                        <div id="taboutputs" class="tab-pane <?php if($wizard_manager->resume_step() == 9){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 9) {
                                                ?>
                                                <h2>Create a permit template</h2>
                                                <form id="permitform" class="form-bordered" action="/backend.php/<?php echo 'permittemplates/'.($permit_form->getObject()->isNew() ? 'create' : 'update'.(!$permit_form->getObject()->isNew() ? '?id='.$permit_form->getObject()->getId() : '')) ?>" method="post"  autocomplete="off">
                                                <div class="panel-body padding-0">
                                                <?php if (!$permit_form->getObject()->isNew()): ?>
                                                    <input type="hidden" name="sf_method" value="put" />
                                                <?php endif; ?>
                                                <?php echo $permit_form->renderHiddenFields(false) ?>
                                                <?php echo $permit_form->renderGlobalErrors() ?>

                                                <div class="form-group">
                                                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Title'); ?></i></label><br>
                                                    <div class="col-sm-12 rogue-input">
                                                        <?php echo $permit_form['title']->renderError() ?>
                                                        <?php echo $permit_form['title'] ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Permit No Identification'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['footer']->renderError() ?>
                                                        <input type="text" class="form-control" name="permits[footer]" id="permits_footer" value="<?php if(!$permit_form->getObject()->isNew()){ echo $permit_form->getObject()->getFooter(); }else{ echo "0"; } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Permit Type'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['parttype']->renderError() ?>
                                                        <select class="form-control" name="permits[parttype]" id="permits_parttype">
                                                            <option value="1" <?php if(!$permit_form->getObject()->isNew()){ if($permit_form->getObject()->getParttype() == 1){ echo "selected='selected'"; } } ?>>Service for Clients and Reviewers</option>
                                                            <option value="3" <?php if(!$permit_form->getObject()->isNew()){ if($permit_form->getObject()->getParttype() == 3){ echo "selected='selected'"; } } ?>>Service for Reviewers Only</option>
                                                            <option value="2" <?php if(!$permit_form->getObject()->isNew()){ if($permit_form->getObject()->getParttype() == 2){ echo "selected='selected'"; } } ?>>PDF for Client to Download and Attach</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Application Form'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['applicationform']->renderError() ?>
                                                        <select class="form-control" name="permits[applicationform]" id="permits_applicationform"  onChange="ajaxFetchPermitsettings(this.value);">
                                                            <option value=""></option>
                                                            <?php
                                                            $q = Doctrine_Query::create()
                                                                ->from('ApForms a')
                                                                ->where('a.form_id <> 15 AND a.form_id <> 16 AND a.form_id <> 17 AND  a.form_id <> 6 AND a.form_id <> 7')
                                                                ->andWhere('a.form_active = 1 AND a.form_type = 1')
                                                                ->orderBy('a.form_name ASC');
                                                            $applicationforms = $q->execute();
                                                            foreach($applicationforms as $appform)
                                                            {
                                                                if($permit_form->getObject()->isNew())
                                                                {
                                                                    echo "<option value='".$appform->getFormId()."'>".$appform->getFormName()."</option>";
                                                                }
                                                                else
                                                                {
                                                                    if($permit_form->getObject()->getApplicationform() == $appform->getFormId())
                                                                    {
                                                                        echo "<option value='".$appform->getFormId()."' selected>".$appform->getFormName()."</option>";
                                                                    }
                                                                    else
                                                                    {
                                                                        echo "<option value='".$appform->getFormId()."'>".$appform->getFormName()."</option>";
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Stage at which this permit is generated'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['applicationstage']->renderError() ?>
                                                        <select class="form-control" name="permits[applicationstage]" id="permits_applicationstage">
                                                            <option value=""></option>
                                                            <?php
                                                            $q = Doctrine_Query::create()
                                                                ->from('Menus a')
                                                                ->orderBy('a.order_no ASC');
                                                            $stagegroups = $q->execute();
                                                            foreach($stagegroups as $stagegroup)
                                                            {
                                                                echo "<optgroup label='".$stagegroup->getTitle()."'>";
                                                                $q = Doctrine_Query::create()
                                                                    ->from('SubMenus a')
                                                                    ->where('a.menu_id = ?', $stagegroup->getId())
                                                                    ->andWhere('a.deleted = 0')
                                                                    ->orderBy('a.order_no ASC');
                                                                $stages = $q->execute();

                                                                foreach($stages as $stage)
                                                                {
                                                                    $selected = "";

                                                                    if(!$permit_form->getObject()->isNew()){
                                                                        if($permit_form->getObject()->getApplicationstage() == $stage->getId()){
                                                                            $selected = "selected='selected'";
                                                                        }
                                                                    }

                                                                    echo "<option value='".$stage->getId()."' ".$selected.">".$stage->getTitle()."</option>";
                                                                }
                                                                echo "</optgroup>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Maximum number of days before expiration'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['max_duration']->renderError() ?>
                                                        <input type="text" class="form-control" name="permits[max_duration]" id="permits_max_duration" value="<?php if(!$permit_form->getObject()->isNew()){ echo $permit_form->getObject()->getMaxDuration(); }else{ echo "0"; } ?>">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Content'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['content']->renderError() ?>
                                                        <?php
                                                        if($permit_form->getObject()->isNew())
                                                        {
                                                            ?>
                                                            <textarea class="form-control" rows="30" name="permits[content]" id="permits_content" ></textarea>
                                                        <?php
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                            <textarea class="form-control" rows="30" name="permits[content]" id="permits_content" ><?php echo $permit_form->getObject()->getContent(); ?></textarea>
                                                        <?php
                                                        }
                                                        ?>
                                                        <script src="/assets_backend/js/ckeditor/ckeditor.js"></script>
                                                        <script src="/assets_backend/js/ckeditor/adapters/jquery.js"></script>

                                                        <script>
                                                            jQuery(document).ready(function(){

                                                                // CKEditor
                                                                jQuery('#permits_content').ckeditor();

                                                            });
                                                        </script>

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <h4 style="padding-left: 10px;">Remote Updates</h4>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Remote URL (Update a remote database with the data from this permit)'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['remote_url']->renderError() ?>
                                                        <input type="text" class="form-control" name="permits[remote_url]" id="permits_remote_url" value='<?php if(!$permit_form->getObject()->isNew()){ echo $permit_form->getObject()->getRemoteUrl(); }else{ echo ""; } ?>'>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Remote Post Data (Actual data to be posted remotely)'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['remote_field']->renderError() ?>
                                                        <input type="text" class="form-control" name="permits[remote_field]" id="permits_remote_field" value='<?php if(!$permit_form->getObject()->isNew()){ echo $permit_form->getObject()->getRemoteField(); }else{ echo ""; } ?>'>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Remote Username (Username if the remote url requires authentication)'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['remote_username']->renderError() ?>
                                                        <input type="text" class="form-control" name="permits[remote_username]" id="permits_remote_username" value="<?php if(!$permit_form->getObject()->isNew()){ echo $permit_form->getObject()->getRemoteUsername(); }else{ echo ""; } ?>">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-12"><i class="bold-label"><?php echo __('Remote Password (Password if the remote url requires authentication)'); ?></i></label><br>
                                                    <div class="col-sm-12">
                                                        <?php echo $permit_form['remote_password']->renderError() ?>
                                                        <input type="password" class="form-control" name="permits[remote_password]" id="permits_remote_password" value="<?php if(!$permit_form->getObject()->isNew()){ echo $permit_form->getObject()->getRemotePassword(); }else{ echo ""; } ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12 alignright">
                                                        <button type="button" class="btn btn-primary" data-target="#fieldsModal" data-toggle="modal">View available user/form fields</button>
                                                    </div>
                                                </div>
                                                <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="fieldsModal" class="modal fade" style="display: none;">
                                                <div class="modal-dialog">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                                    <h4 id="myModalLabel" class="modal-title">View available user/form fields</h4>
                                                </div>
                                                <div class="modal-body">
                                                <div class="form-group">
                                                <?php
                                                //Get User Information (anything starting with sf_ )
                                                //sf_email, sf_fullname, sf_username, ... other fields in the dynamic user profile form e.g sf_element_1
                                                ?>
                                                <table class="table dt-on-steroids mb0">
                                                    <thead><tr><th width="50%"><?php echo __('User Details'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                    <tbody>
                                                    <tr><td><?php echo __('User ID'); ?></td><td>{sf_username}</td></tr>
                                                    <tr><td><?php echo __('Mobile Number'); ?></td><td>{sf_mobile}</td></tr>
                                                    <tr><td><?php echo __('Email'); ?></td><td>{sf_email}</td></tr>
                                                    <tr><td><?php echo __('Full Name'); ?></td><td>{sf_fullname}</td></tr>
                                                    <?php
                                                    $q = Doctrine_Query::create()
                                                        ->from('apFormElements a')
                                                        ->where('a.form_id = ?', 15)
                                                        ->andWhere('a.element_status = ?', 1)
                                                        ->orderBy('a.element_position ASC');

                                                    $elements = $q->execute();

                                                    foreach($elements as $element)
                                                    {
                                                        $childs = $element->getElementTotalChild();
                                                        if($childs == 0)
                                                        {
                                                            echo "<tr><td>".$element->getElementTitle()."</td><td>{sf_element_".$element->getElementId()."}</td></tr>";
                                                        }
                                                        else
                                                        {
                                                            if($element->getElementType() == "select")
                                                            {
                                                                echo "<tr><td>".$element->getElementTitle()."</td><td>{sf_element_".$element->getElementId()."}</td></tr>";
                                                            }
                                                            else
                                                            {
                                                                for($x = 0; $x < ($childs + 1); $x++)
                                                                {
                                                                    echo "<tr><td>".$element->getElementTitle()."</td><td>{sf_element_".$element->getElementId()."_".($x+1)."}</td></tr>";
                                                                }
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>


                                                <table class="table dt-on-steroids mb0">
                                                <thead><tr><th width="50%"><?php echo __('Application Details'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                <tbody>
                                                <tr><td><?php echo __('QR Code'); ?></td><td>{qr_code}</td></tr>
                                                <tr><td><?php echo __('QR Code (Small)'); ?></td><td>{qr_code_small}</td></tr>
                                                <tr><td><?php echo __('Bar Code'); ?></td><td>{bar_code}</td></tr>
                                                <tr><td><?php echo __('Bar Code (Small)'); ?></td><td>{bar_code_small}</td></tr>
                                                <tr><td><?php echo __('Application Number'); ?></td><td>{ap_application_id}</td></tr>
                                                <tr><td><?php echo __('Created At'); ?></td><td>{fm_created_at}</td></tr>
                                                <tr><td><?php echo __('Approved At'); ?></td><td>{fm_updated_at}</td></tr>
                                                <tr><td><?php echo __('Permit ID'); ?></td><td>{ap_permit_id}</td></tr>
                                                <tr><td><?php echo __('Permit Issued At'); ?></td><td>{ap_issue_date}</td></tr>
                                                <tr><td><?php echo __('Permit Expires At'); ?></td><td>{ap_expire_date}</td></tr>
                                                <?php
                                                //Get Application Information (anything starting with ap_ )
                                                //ap_application_id

                                                //Get Form Details (anything starting with fm_ )
                                                //fm_created_at, fm_updated_at.....fm_element_1
                                                ?>
                                                <?php
                                                if(!$permit_form->getObject()->isNew())
                                                {
                                                    $appform = $permit_form->getObject()->getApplicationform();
                                                    ?>
                                                    <?php

                                                    $q = Doctrine_Query::create()
                                                        ->from('apFormElements a')
                                                        ->where('a.form_id = ?', $appform)
                                                        ->andWhere('a.element_status = ?', 1)
                                                        ->orderBy('a.element_position ASC');

                                                    $elements = $q->execute();

                                                    foreach($elements as $element)
                                                    {
                                                        $childs = $element->getElementTotalChild();
                                                        if($childs == 0)
                                                        {
                                                            if($element->getElementType() == "select")
                                                            {
                                                                if($element->getElementExistingForm() && $element->getElementExistingStage())
                                                                {
                                                                    $q = Doctrine_Query::create()
                                                                        ->from("ApForms a")
                                                                        ->where("a.form_id = ?", $element->getElementExistingForm());
                                                                    $child_form = $q->fetchOne();

                                                                    echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."} ";
                                                                    echo '<table class="table dt-on-steroids mb0">
                         <thead><tr><th width="50%">'.__($child_form->getFormName().' Details').'</th><th>'.__('Tag').'</th></tr></thead>
                         <tbody>';

                                                                    ?>
                                                                    <tr><td><?php echo __('Application Number'); ?></td><td>{ap_child_application_id}</td></tr>
                                                                    <tr><td><?php echo __('Created At'); ?></td><td>{fm_child_created_at}</td></tr>
                                                                    <tr><td><?php echo __('Approved At'); ?></td><td>{fm_child_updated_at}</td></tr>
                                                                    <?php
                                                                    $q = Doctrine_Query::create()
                                                                        ->from("Permits a")
                                                                        ->where("a.applicationform = ?", $element->getElementExistingForm());
                                                                    $permits = $q->execute();

                                                                    foreach($permits as $permit)
                                                                    {
                                                                        echo "<tr><td>".$permit->getTitle()." ID</td><td>{ap_permit_id_".$permit->getId()."_element_child}</td></tr>";
                                                                    }

                                                                    $q = Doctrine_Query::create()
                                                                        ->from('apFormElements a')
                                                                        ->where('a.form_id = ?', $element->getElementExistingForm())
                                                                        ->andWhere('a.element_status = ?', 1)
                                                                        ->orderBy('a.element_position ASC');

                                                                    $child_elements = $q->execute();

                                                                    foreach($child_elements as $child_element)
                                                                    {

                                                                        //START CHILD ELEMENTS
                                                                        $childs = $child_element->getElementTotalChild();
                                                                        if($childs == 0)
                                                                        {
                                                                            if($child_element->getElementType() == "select")
                                                                            {
                                                                                if($child_element->getElementExistingForm() && $child_element->getElementExistingStage())
                                                                                {

                                                                                    $q = Doctrine_Query::create()
                                                                                        ->from("ApForms a")
                                                                                        ->where("a.form_id = ?", $child_element->getElementExistingForm());
                                                                                    $grand_form = $q->fetchOne();

                                                                                    echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."} ";
                                                                                    echo '<table class="table dt-on-steroids mb0">
                                       <thead><tr><th width="50%">'.__($grand_form->getFormName().' Details').'</th><th>'.__('Tag').'</th></tr></thead>
                                       <tbody>';

                                                                                    $q = Doctrine_Query::create()
                                                                                        ->from('apFormElements a')
                                                                                        ->where('a.form_id = ?', $child_element->getElementExistingForm())
                                                                                        ->andWhere('a.element_status = ?', 1)
                                                                                        ->orderBy('a.element_position ASC');

                                                                                    $grand_child_elements = $q->execute();

                                                                                    foreach($grand_child_elements as $grand_child_element)
                                                                                    {
                                                                                        ?>
                                                                                        <tr><td><?php echo __('Application Number'); ?></td><td>{ap_grand_child_application_id}</td></tr>
                                                                                        <tr><td><?php echo __('Created At'); ?></td><td>{fm_grand_child_created_at}</td></tr>
                                                                                        <tr><td><?php echo __('Approved At'); ?></td><td>{fm_grand_child_updated_at}</td></tr>
                                                                                        <?php
                                                                                        $q = Doctrine_Query::create()
                                                                                            ->from("Permits a")
                                                                                            ->where("a.applicationform = ?", $child_element->getElementExistingForm());
                                                                                        $permits = $q->execute();

                                                                                        foreach($permits as $permit)
                                                                                        {
                                                                                            echo "<tr><td>".$permit->getTitle()." ID</td><td>{ap_permit_id_".$permit->getId()."_element_grand_child}</td></tr>";
                                                                                        }

                                                                                        //START GRAND CHILD ELEMENTS
                                                                                        $childs = $grand_child_element->getElementTotalChild();
                                                                                        if($childs == 0)
                                                                                        {
                                                                                            if($grand_child_element->getElementType() == "select")
                                                                                            {
                                                                                                echo "<tr><td>".$grand_child_element->getElementTitle()."</td><td>{fm_grand_child_element_".$grand_child_element->getElementId()."}</td></tr>";
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                echo "<tr><td>".$grand_child_element->getElementTitle()."</td><td>{fm_grand_child_element_".$grand_child_element->getElementId()."}</td></tr>";
                                                                                            }
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                            for($x = 0; $x < ($childs + 1); $x++)
                                                                                            {
                                                                                                echo "<tr><td>".$grand_child_element->getElementTitle()."</td><td>{fm_grand_child_element_".$grand_child_element->getElementId()."_".($x+1)."}</td></tr>";
                                                                                            }
                                                                                        }
                                                                                        //END GRAND CHILD ELEMENTS
                                                                                    }

                                                                                    echo '</tbody></table>';
                                                                                    echo "</td></tr>";
                                                                                }
                                                                                else
                                                                                {
                                                                                    echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."}</td></tr>";
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                                echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."}</td></tr>";
                                                                            }
                                                                        }
                                                                        else
                                                                        {
                                                                            for($x = 0; $x < ($childs + 1); $x++)
                                                                            {
                                                                                echo "<tr><td>".$child_element->getElementTitle()."</td><td>{fm_child_element_".$child_element->getElementId()."_".($x+1)."}</td></tr>";
                                                                            }
                                                                        }
                                                                        //END CHILD ELEMENTS
                                                                    }

                                                                    echo '</tbody></table>';
                                                                    echo "</td></tr>";
                                                                }
                                                                else
                                                                {
                                                                    echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."}</td></tr>";
                                                                }
                                                            }
                                                            else
                                                            {
                                                                echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."}</td></tr>";
                                                            }
                                                        }
                                                        else
                                                        {
                                                            for($x = 0; $x < ($childs + 1); $x++)
                                                            {
                                                                echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_element_".$element->getElementId()."_".($x+1)."}</td></tr>";
                                                            }
                                                        }
                                                    }

                                                    ?>
                                                    <tr><td><strong>Comment Sheets</strong></td><td><strong>Tag</strong></td></tr>
                                                    <?php

                                                    //Comment Sheets
                                                    $q = Doctrine_Query::create()
                                                        ->from('SubMenus a')
                                                        ->where('a.id = ?', $permit_form->getObject()->getApplicationstage());
                                                    $stage = $q->fetchOne();

                                                    if($stage)
                                                    {

                                                        $q = Doctrine_Query::create()
                                                            ->from('SubMenus a')
                                                            ->where('a.menu_id = ?', $stage->getMenuId())
                                                            ->orderBy('a.order_no ASC');
                                                        $stages = $q->execute();

                                                        $filstages = "";

                                                        $filtags = "";

                                                        $count = 0;

                                                        foreach($stages as $stage)
                                                        {
                                                            $filstages[] = $stage->getId();
                                                            if($count == 0)
                                                            {
                                                                $filtags = $filtags."a.form_department_stage = ? ";
                                                            }
                                                            else
                                                            {
                                                                $filtags = $filtags."OR a.form_department_stage = ? ";
                                                            }
                                                            $count++;
                                                        }

                                                        $q = Doctrine_Query::create()
                                                            ->from("ApForms a")
                                                            ->where($filtags, $filstages);
                                                        $forms = $q->execute();

                                                        foreach($forms as $apform)
                                                        {
                                                            echo "<tr><td><strong>".$apform->getFormName()." details</strong></td><td><strong>Tag</strong></td></tr>";
                                                            $q = Doctrine_Query::create()
                                                                ->from('apFormElements a')
                                                                ->where('a.form_id = ?', $apform->getFormId())
                                                                ->andWhere('a.element_status = ?', 1)
                                                                ->orderBy('a.element_position ASC');

                                                            $elements = $q->execute();

                                                            foreach($elements as $element)
                                                            {
                                                                $childs = $element->getElementTotalChild();
                                                                if($childs == 0)
                                                                {
                                                                    if($element->getElementType() == "select")
                                                                    {
                                                                        echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_c".$apform->getFormId()."_element_".$element->getElementId()."}</td></tr>";
                                                                    }
                                                                    else
                                                                    {
                                                                        echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_c".$apform->getFormId()."_element_".$element->getElementId()."}</td></tr>";
                                                                    }
                                                                }
                                                                else
                                                                {
                                                                    for($x = 0; $x < ($childs + 1); $x++)
                                                                    {
                                                                        echo "<tr><td>".$element->getElementTitle()."</td><td>{fm_c".$apform->getFormId()."_element_".$element->getElementId()."_".($x+1)."}</td></tr>";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    ?>
                                                <?php
                                                }
                                                ?>
                                                </tbody>
                                                </table>
                                                <table class="table dt-on-steroids mb0">
                                                    <thead><tr><th width="50%"><?php echo __('Conditions Of Approval'); ?></th><th><?php echo __('Tag'); ?></th></tr></thead>
                                                    <tbody>
                                                    <tr><td><?php echo __('Conditions Of Approval'); ?></td><td>{ca_conditions}</td></tr>
                                                    </tbody>
                                                </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                                                </div>
                                                </div><!-- modal-content -->
                                                </div><!-- modal-dialog -->
                                                </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12" id="loadinner" name="loadinner">

                                                    </div>

                                                    <?php
                                                    $permitid = 0;
                                                    if(!$permit_form->getObject()->isNew())
                                                    {
                                                        $permitid = $form->getObject()->getId();
                                                    }
                                                    ?>
                                                    <script language="javascript">
                                                        jQuery(document).ready(function(){
                                                            $("#loadinner").load("/backend.php/conditionsmng/index/filter/<?php echo $permitid; ?>");
                                                        });
                                                    </script>
                                                </div>
                                                </div>
                                                <a name="end"></a>


                                                <div class="panel-footer">
                                                    <button class='btn btn-danger mr10'><?php echo __('Reset'); ?></button><button type="submit" class='btn btn-primary' name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button></div>
                                            </div>
                                        </div>
                                        </form>
                                            <?php
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
								</div>
							</div>                            
							<div class="tab-pane <?php if($wizard_manager->resume_step() >= 10){ echo "active"; } ?>" id="tab4">
                                <div class="contentpanel">
                                    <ul class="nav nav-tabs nav-dark">
                                        <li <?php if($wizard_manager->resume_step() == 10){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabcurrency"> <?php if($wizard_manager->resume_step() == 10){ echo "&gt;&gt;"; } ?> <strong>1: Currency</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 11){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabMerchant"> <?php if($wizard_manager->resume_step() == 11){ echo "&gt;&gt;"; } ?> <strong>2: Merchant</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 12){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabFeeCategory"> <?php if($wizard_manager->resume_step() == 12){ echo "&gt;&gt;"; } ?> <strong>3: Fee Category</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 13){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabFee"> <?php if($wizard_manager->resume_step() == 13){ echo "&gt;&gt;"; } ?> <strong>4: Fee</strong></a></li>
                                        <li <?php if($wizard_manager->resume_step() == 14){ echo "class='active'"; } ?>><a data-toggle="tab" href="#tabagency"> <?php if($wizard_manager->resume_step() == 14){ echo "&gt;&gt;"; } ?> <strong>4: Agency</strong></a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div id="tabcurrency" class="tab-pane <?php if($wizard_manager->resume_step() == 10){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 10) {
                                                header("Location: /backend.php/currencies/new");
                                                exit;
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                            <form class="form-horizontal form-bordered" method="post" action="/backend.php/dashboard">
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px;" id="submitgroups"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabMerchant" class="tab-pane <?php if($wizard_manager->resume_step() == 11){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 11) {
                                                header("Location: /backend.php/merchant/new");
                                                exit;
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                            <form class="form-horizontal form-bordered" method="post" action="/backend.php/dashboard">
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px;" id="submitgroups"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="tabFeeCategory" class="tab-pane <?php if($wizard_manager->resume_step() == 12){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 12) {
                                                header("Location: /backend.php/feecategories/new");
                                                exit;
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                            <form class="form-horizontal form-bordered" method="post" action="/backend.php/dashboard">
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px;" id="submitgroups"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="tabFee" class="tab-pane <?php if($wizard_manager->resume_step() == 13){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 13) {
                                                header("Location: /backend.php/fees/new");
                                                exit;
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                            <form class="form-horizontal form-bordered" method="post" action="/backend.php/dashboard">
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px;" id="submitgroups"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="tabagency" class="tab-pane <?php if($wizard_manager->resume_step() == 14){ echo "active"; } ?>">
                                            <?php
                                            if($wizard_manager->resume_step() == 14) {
                                                header("Location: /backend.php/agency/new");
                                                exit;
                                            }
                                            else{
                                                echo "Unavailable until other configuration are complete";
                                            }
                                            ?>
                                            <form class="form-horizontal form-bordered" method="post" action="/backend.php/dashboard">
                                                <div class="panel-footer">
                                                    <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px;" id="submitgroups"><?php echo __("Next"); ?></button>
                                                </div>
                                            </form>
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

<?php
if($wizard_manager->resume_step() == 6) {
    ?>
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
<?php
}

  if($wizard_manager->resume_step() == 6){ 
  ?>

    <!-- JS -->
    <!-- support lib for bezier stuff -->
    <script src="/assets_backend/jsPlumb/lib/jsBezier-0.6.js"></script>
    <!-- event adapter -->
    <script src="/assets_backend/jsPlumb/lib/mottle-0.4.js"></script>
    <!-- geometry functions -->
    <script src="/assets_backend/jsPlumb/lib/biltong-0.2.js"></script>
    <!-- drag -->
    <script src="/assets_backend/jsPlumb/lib/katavorio-0.4.js"></script>
    <!-- jsplumb util -->
    <script src="/assets_backend/jsPlumb/src/util.js"></script>
    <script src="/assets_backend/jsPlumb/src/browser-util.js"></script>
    <!-- base DOM adapter -->
    <script src="/assets_backend/jsPlumb/src/dom-adapter.js"></script>
    <!-- main jsplumb engine -->
    <script src="/assets_backend/jsPlumb/src/jsPlumb.js"></script>
    <!-- endpoint -->
    <script src="/assets_backend/jsPlumb/src/endpoint.js"></script>
    <!-- connection -->
    <script src="/assets_backend/jsPlumb/src/connection.js"></script>
    <!-- anchors -->
    <script src="/assets_backend/jsPlumb/src/anchors.js"></script>
    <!-- connectors, endpoint and overlays  -->
    <script src="/assets_backend/jsPlumb/src/defaults.js"></script>
    <!-- bezier connectors -->
    <script src="/assets_backend/jsPlumb/src/connectors-bezier.js"></script>
    <!-- state machine connectors -->
    <script src="/assets_backend/jsPlumb/src/connectors-statemachine.js"></script>
    <!-- flowchart connectors -->
    <script src="/assets_backend/jsPlumb/src/connectors-flowchart.js"></script>
    <script src="/assets_backend/jsPlumb/src/connector-editors.js"></script>
    <!-- SVG renderer -->
    <script src="/assets_backend/jsPlumb/src/renderers-svg.js"></script>


    <!-- vml renderer -->
    <script src="/assets_backend/jsPlumb/src/renderers-vml.js"></script>

    <!-- no library jsPlumb adapter -->
    <script src="/assets_backend/jsPlumb/src/base-library-adapter.js"></script>
    <script src="/assets_backend/jsPlumb/src/dom.jsPlumb.js"></script>
    <!-- /JS -->

    <!--  demo code -->
    <script src="/assets_backend/jsPlumb/demo/statemachine/demo.js"></script>

    <!-- demo list -->
    <script src="/assets_backend/jsPlumb/demo/demo-list.js"></script>


    <?php 
    }
?>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
