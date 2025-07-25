<?php
  use_helper('I18N');
?>
<section>
    <div class="row">

        <div class="col-md-9" style="padding: 50px; margin-left: 13%; margin-right: auto;">
            <div class="panel panel-default">
            <div class="panel-heading">
                <img src="/assets_backend/images/logo.png">
                <h4 class="panel-title">PermitFlow: Installation Wizard</h4>
            </div>
            <div class="panel-body padding-0">

                <!-- BASIC WIZARD -->
                <div id="validationWizard" class="basic-wizard">

                <ul class="nav nav-pills nav-justified">
                    <li><a href="#vtab0" data-toggle="tab"><span>Step 1:</span> Checklist</a></li>
                    <li><a href="#vtab3" data-toggle="tab"><span>Step 2:</span> Organisation</a></li>
                    <li><a href="#vtab4" data-toggle="tab"><span>Step 3:</span> Language</a></li>
                    <li><a href="#vtab5" data-toggle="tab"><span>Step 4:</span> System Administrator</a></li>
                </ul>

                <form class="form" id="firstForm" name="firstForm" action="/plan/install/update" method="post">
                <div class="tab-content tab-content-nopadding">

                    <div class="tab-pane" id="vtab0" style="padding: 20px;">
                    <?php

                    if (!isset($_SERVER['HTTP_HOST'])) {
                        exit('This script cannot be run from the CLI. Run it from a browser.');
                    }

                    $symfonyRequirements = new SymfonyRequirements();

                    $allRequirements = $symfonyRequirements->getRequirements();
                    $majorProblems = $symfonyRequirements->getFailedRequirements();
                    $minorProblems = $symfonyRequirements->getFailedRecommendations();

                ?>

                <div class="symfony-block-content">
                    <p>Welcome to your new Permitflow project.</p>
                    <p>
                        This script will guide you through the basic configuration of your project.

                    </p>
                    <?php if (count($allRequirements)): ?>
                        <h2 class="ko">System Requirements</h2>
                        <p>The following are the system requirements to run this application. You may not need
                            to configure them. In most environments they will be already set by default. Any issues you
                            need to configure will be highlighted in the major problems section:</p>
                        <ol>
                            <?php foreach ($allRequirements as $problem): ?>
                                <li><?php echo $problem->getHelpHtml() ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php if (count($majorProblems)): ?>
                        <h2 class="ko">Major problems</h2>
                        <p>Major problems have been detected and <strong>must</strong> be fixed before continuing:</p>
                        <ol>
                            <?php foreach ($majorProblems as $problem): ?>
                                <li><?php echo $problem->getHelpHtml() ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php if (count($minorProblems)): ?>
                        <h2>Recommendations</h2>
                        <p>
                            <?php if (count($majorProblems)): ?>Additionally, to<?php else: ?>To<?php endif; ?> enhance your Permitflow experience,
                            it’s recommended that you fix the following:
                        </p>
                        <ol>
                            <?php foreach ($minorProblems as $problem): ?>
                                <li><?php echo $problem->getHelpHtml() ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php if ($symfonyRequirements->hasPhpIniConfigIssue()): ?>
                        <p id="phpini">*
                            <?php if ($symfonyRequirements->getPhpIniConfigPath()): ?>
                                Changes to the <strong>php.ini</strong> file must be done in "<strong><?php echo $symfonyRequirements->getPhpIniConfigPath() ?></strong>".
                            <?php else: ?>
                                To change settings, create a "<strong>php.ini</strong>".
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!count($majorProblems) && !count($minorProblems)): ?>
                        <p class="ok" style="color: #66CC00;">Your configuration looks good to run Symfony. <span class="fa fa-check"></span></p>
                    <?php endif; ?>

                </div>
                    </div>

                    <div class="tab-pane form-bordered form-horizontal pt20" id="vtab3">
                        <p>Below are required information about the organisation that the system is being
                        deployed for</p>
                        <div class="form-group">
                        <label class="col-sm-2 control-label">Organisation Name</label>
                        <div class="col-sm-8">
                            <input type="text" name="organisationname" class="form-control" required />
                        </div>
                        </div>

                        <div class="form-group">
                        <label class="col-sm-2 control-label">Organisation Email</label>
                        <div class="col-sm-8">
                            <input type="text" name="organisationemail" class="form-control" required />
                        </div>
                        </div>
                        
                    </div>

                    <div class="tab-pane form-bordered form-horizontal pt20" id="vtab4">
                        <p>
                            Select the default system language that user's of the system will see. Choose
                            whether you would want to install a default database or not to install a default database
                            and use existing data (e.g. when transferring from one server to another)
                        </p>
                        <div class="form-group">
                        <label class="col-sm-2 control-label">Select Default Language</label>
                        <div class="col-sm-8">
                                <div class="rdio rdio-primary">
                                <input type="radio" value="ar_IQ" id="locale2" name="locale"/>
                                <label for="locale2">Arabic</label>
                                </div>
                            <div class="rdio rdio-primary">
                                <input type="radio" value="fr_FR" id="locale3" name="locale"/>
                                <label for="locale3">French</label>
                            </div>
                                <div class="rdio rdio-primary">
                                <input type="radio" value="en_US" id="locale1" name="locale"/>
                                <label for="locale1">English</label>
                                </div>
                        </div>
                        </div>
                    </div>

                    <div class="tab-pane form-bordered form-horizontal pt20" id="vtab5">
                        <p>Below are required information about the system administrator</p>

                        <div class="form-group">
                        <label class="col-sm-2 control-label">Administrator's Email</label>
                        <div class="col-sm-8">
                            <input type="text" name="admin_email" class="form-control" required />
                        </div>
                        </div>

                        <div class="form-group">
                        <label class="col-sm-2 control-label">Administrator's Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="admin_password" class="form-control" required />
                        </div>
                        </div>

                        <div class="form-group">
                        <div class="col-sm-12" align="right">
                            <button type="submit" class="btn btn-success">Finish Setup</button>
                        </div>
                        </div>

                    </div>


                </div><!-- tab-content -->
                </form>

                <ul class="pager wizard">
                    <li class="previous"><a href="javascript:void(0)">Previous</a></li>
                    <li class="next"><a href="javascript:void(0)">Next</a></li>
                    </ul>

                </div><!-- #validationWizard -->

            </div><!-- panel-body -->
            </div><!-- panel -->
        </div><!-- col-md-6 -->

    </div>
</section>

<script>
    jQuery(document).ready(function(){

      //Check path
      $( "input[name='path']" ).keyup(function() {
        $.ajax({
                type: "POST",
                url: "checkpath.php",
                data: {
                    'path' : $('input:text[name=path]').val()
                },
                dataType: "text",
                success: function(msg){
                      //Receiving the result of search here
                      $("#pathresult").html(msg);
                }
            });
      });

      // Basic Wizard
      jQuery('#basicWizard').bootstrapWizard();

      // Progress Wizard
      $('#progressWizard').bootstrapWizard({
        'nextSelector': '.next',
        'previousSelector': '.previous',
        onNext: function(tab, navigation, index) {
          var $total = navigation.find('li').length;
          var $current = index+1;
          var $percent = ($current/$total) * 100;
          jQuery('#progressWizard').find('.progress-bar').css('width', $percent+'%');
        },
        onPrevious: function(tab, navigation, index) {
          var $total = navigation.find('li').length;
          var $current = index+1;
          var $percent = ($current/$total) * 100;
          jQuery('#progressWizard').find('.progress-bar').css('width', $percent+'%');
        },
        onTabShow: function(tab, navigation, index) {
          var $total = navigation.find('li').length;
          var $current = index+1;
          var $percent = ($current/$total) * 100;
          jQuery('#progressWizard').find('.progress-bar').css('width', $percent+'%');
        }
      });

      // Disabled Tab Click Wizard
      jQuery('#disabledTabWizard').bootstrapWizard({
        tabClass: 'nav nav-pills nav-justified nav-disabled-click',
        onTabClick: function(tab, navigation, index) {
          return false;
        }
      });


      jQuery('#validationWizard').bootstrapWizard({
        tabClass: 'nav nav-pills nav-justified nav-disabled-click',
        onTabClick: function(tab, navigation, index) {
          return false;
        },
        onNext: function(tab, navigation, index) {
          var $valid = jQuery('#firstForm').valid();
          if(!$valid) {
            $validator.focusInvalid();
            return false;
          }
        }
      });

      $("#database").keyup(function () {
        var that = this,
        value = $(this).val();

        if (value.length >= 1 ) {
            $.ajax({
                type: "POST",
                url: "checkdb.php",
                data: {
                    'dbname' : $('input:text[id=database]').val(),
                    'dbuser' : $('input:text[id=username]').val(),
                    'dbpass' : $('input:password[id=password]').val(),
                    'dbhost' : $('input:text[id=host]').val()
                },
                dataType: "text",
                success: function(msg){
                      //Receiving the result of search here
                      $("#dbresult").html(msg);
                }
            });
        }
    });

      $("#username").keyup(function () {
        var that = this,
        value = $(this).val();

        if (value.length >= 1 ) {
            $.ajax({
                type: "POST",
                url: "checkdb.php",
                data: {
                    'dbname' : $('input:text[id=database]').val(),
                    'dbuser' : $('input:text[id=username]').val(),
                    'dbpass' : $('input:password[id=password]').val(),
                    'dbhost' : $('input:text[id=host]').val()
                },
                dataType: "text",
                success: function(msg){
                      //Receiving the result of search here
                      $("#dbresult").html(msg);
                }
            });
        }
    });

      $("#password").keyup(function () {
        var that = this,
        value = $(this).val();

        if (value.length >= 1 ) {
            $.ajax({
                type: "POST",
                url: "checkdb.php",
                data: {
                    'dbname' : $('input:text[id=database]').val(),
                    'dbuser' : $('input:text[id=username]').val(),
                    'dbpass' : $('input:password[id=password]').val(),
                    'dbhost' : $('input:text[id=host]').val()
                },
                dataType: "text",
                success: function(msg){
                    //we need to check if the value is the same
                      $("#dbresult").html(msg);
                }
            });
        }
    });

      $("#host").keyup(function () {
        var that = this,
        value = $(this).val();

        if (value.length >= 1 ) {
            $.ajax({
                type: "POST",
                url: "checkdb.php",
                data: {
                    'dbname' : $('input:text[id=database]').val(),
                    'dbuser' : $('input:text[id=username]').val(),
                    'dbpass' : $('input:password[id=password]').val(),
                    'dbhost' : $('input:text[id=host]').val()
                },
                dataType: "text",
                success: function(msg){
                      //Receiving the result of search here
                      $("#dbresult").html(msg);
                }
            });
        }
    });

    });
    </script>