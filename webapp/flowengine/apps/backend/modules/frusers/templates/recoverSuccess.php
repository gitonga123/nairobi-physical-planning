<?php
use_helper("I18N");
if($sf_user->mfHasCredential('createapplications')) {
    ?>

    <div class="pageheader">
        <h2><i class="fa fa-home"></i> Recover Application <span>Security</span></h2>

        <div class="breadcrumb-wrapper">
            
            <ol class="breadcrumb">
                <li><a href="/backend.php">Home</a></li>
                <li>Users</li>
                <li class="active">Recover Application</li>
            </ol>
        </div>
    </div>

    <div class="contentpanel">
    <div class="row">
    <div class="col-md-12">

    <!-- BASIC WIZARD -->
    <div id="progressWizard" class="basic-wizard">

    <ul class="nav nav-pills nav-justified">
        <li <?php if ($step == 1){ ?>class="active"<?php } ?>><a href="#ptab1"
                                                                 <?php if ($step == 1){ ?>data-toggle="tab"<?php } ?>><span>Step 1:</span>
                Search the Reference Number</a></li>
        <li <?php if ($step == 2){ ?>class="active"<?php } ?>><a href="#ptab2"
                                                                 <?php if ($step == 2){ ?>data-toggle="tab"<?php } ?>><span>Step 2:</span>
                Recover the Application</a></li>
    </ul>

    <div class="tab-content">
    <div class="tab-pane <?php if ($step == 1) { ?>active<?php } ?>" id="ptab1">
        <form class="form-horizontal form-bordered" method="post"
              action="/backend.php/frusers/recover/id/<?php echo $user_id; ?>">
            <input type="hidden" name="step" value="2"/>

            <?php
            if($found_entry_error == true)
            {
                ?>
                <div class="alert alert-danger" id="alertdiv" name="alertdiv">
                    <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
                    <strong><?php echo __('Sorry'); ?>!</strong> <?php echo __('Could not find the entry'); ?></a>.
                </div>
                <?php
            }

            if($existing_application)
            {
                ?>
                <div class="alert alert-danger" id="alertdiv" name="alertdiv">
                    <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
                    <strong><?php echo __('Sorry'); ?>!</strong> <?php echo __('An application already exists with ID: '.$existing_application_id); ?></a>.
                </div>
            <?php
            }
            ?>

            <div class="panel-body padding-0" style="border-top:none;">
                <div class="form-group">
                    <label class="col-sm-2"><?php echo __('Search for a lost application by the reference number'); ?></label>

                    <div class="col-sm-8">
                        <input class="form-control" type='text' name='reference_number' id='reference_number' style="width:80%;"
                               value="<?php echo $_POST['reference_number']; ?>">
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-primary" type="submit" name="submitbuttonname"
                        value="submitbuttonvalue"><?php echo __('Search'); ?></button>
            </div>
        </form>
    </div>
    <div class="tab-pane <?php if ($step == 2) { ?>active<?php } ?>" id="ptab2">
        <?php
        if($step == 2) {

            //We will use the application manager to create new applications or drafts from form submissions
            $application_manager = new ApplicationManager();

            //Check if an application already exists for the form submission to prevent double entry
            if($application_manager->application_exists($form_id, $entry_id)) {
                //If save as draft/resume later was clicked then do nothing
                $submission = $application_manager->get_application($form_id, $entry_id);
            }
            else {
                //If save as draft/resume later was clicked then create draft application
                $submission = $application_manager->create_application($form_id, $entry_id, $user_id, true);
            }

            $application_manager->update_invoices($submission->getId());
            ?>
            <div class="alert alert-success" id="alertdiv" name="alertdiv">
                <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
                <strong><?php echo __('Success'); ?>!</strong> <?php echo __('You have recovered an application: <a href="/backend.php/applications/view/id/'.$submission->getId().'">'.$submission->getApplicationId().'</a>'); ?></a>.
            </div>
            <?php
            $_SESSION['formid'] = "";

        }
        ?>
    </div>

    </div>
    <!-- tab-content -->

    </div>
    <!-- #basicWizard -->

    </div>
    </div>
    </div>



    <script>
        jQuery(document).ready(function () {

            // Basic Wizard
            jQuery('#basicWizard').bootstrapWizard();

            // Progress Wizard
            $('#progressWizard').bootstrapWizard({
                'nextSelector': '.next',
                'previousSelector': '.previous',
                onNext: function (tab, navigation, index) {
                    var $total = navigation.find('li').length;
                    var $current = index + 1;
                    var $percent = ($current / $total) * 100;
                    jQuery('#progressWizard').find('.progress-bar').css('width', $percent + '%');
                },
                onPrevious: function (tab, navigation, index) {
                    var $total = navigation.find('li').length;
                    var $current = index + 1;
                    var $percent = ($current / $total) * 100;
                    jQuery('#progressWizard').find('.progress-bar').css('width', $percent + '%');
                },
                onTabShow: function (tab, navigation, index) {
                    var $total = navigation.find('li').length;
                    var $current = index + 1;
                    var $percent = ($current / $total) * 100;
                    jQuery('#progressWizard').find('.progress-bar').css('width', $percent + '%');
                }
            });

            // Disabled Tab Click Wizard
            jQuery('#disabledTabWizard').bootstrapWizard({
                tabClass: 'nav nav-pills nav-justified nav-disabled-click',
                onTabClick: function (tab, navigation, index) {
                    return false;
                }
            });

            // With Form Validation Wizard
            var $validator = jQuery("#firstForm").validate({
                highlight: function (element) {
                    jQuery(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                }
            });

            jQuery('#validationWizard').bootstrapWizard({
                tabClass: 'nav nav-pills nav-justified nav-disabled-click',
                onTabClick: function (tab, navigation, index) {
                    return false;
                },
                onNext: function (tab, navigation, index) {
                    var $valid = jQuery('#firstForm').valid();
                    if (!$valid) {

                        $validator.focusInvalid();
                        return false;
                    }
                }
            });


        });
    </script>
<?php
}
?>
