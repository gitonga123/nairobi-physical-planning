<?php

/**
 * _sidemenu.php partial.
 *
 * Displays the main menu that is located on the left of the screen
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use CodeItNow\BarcodeBundle\Utils\QrCode;

use_helper('I18N');

$site_settings = Functions::site_settings();
?>
<div class="leftpanelinner">

  <!-- This is only visible to small devices -->
  <div class="visible-xs hidden-sm hidden-md hidden-lg">
    <div class="media userlogged">
      <img alt="" src="/assets_backend/images/photos/loggeduser.png" class="media-object">

      <div class="media-body">
        <h4><?php echo $logged_reviewer->getStrfirstname() . " " . $logged_reviewer->getStrlastname(); ?> </h4>
      </div>
    </div>

    <h5 class="sidebartitle actitle"><?php echo __("Account"); ?></h5>
    <ul class="nav nav-pills nav-stacked nav-bracket mb30">
      <li><a href="/plan/users/viewuser/userid/<?php echo $sf_user->getAttribute('userid'); ?>"><i class="fa fa-cog"></i> <span><?php echo __("Account Settings"); ?></span></a></li>
      <li><a href="/plan/help/index"><i class="fa fa-question-circle"></i> <span><?php echo __("Help"); ?></span></a></li>
      <li><a href="/plan/login/logout"><i class="fa fa-sign-out"></i> <span><?php echo __("Sign Out"); ?></span></a></li>
    </ul>
  </div>


  <ul class="nav nav-pills nav-stacked nav-bracket">
    <?php if ($sf_user->mfHasCredential("access_applications")) : ?>
      <li <?php if ($sf_context->getModuleName() == "dashboard" && $sf_context->getActionName() == "index") {
            echo "class='active'";
          } ?>><a href="/plan/dashboard"><i class="fa fa-tasks"></i> <span><?php echo __('Applications'); ?></span></a></li>
    <?php endif; ?>
    <?php
    if ($sf_user->mfHasCredential("access_messages")) {
    ?>
      <!--li <?php if ($sf_context->getModuleName() == "messages") {
                echo "class='active'";
              } ?>><a href="/plan/messages/index"><i class="fa fa-envelope"></i> <span><?php echo __('Messages'); ?></span></a></li-->

    <?php
    }

    if ($sf_user->mfHasCredential("access_reviewers")) {
    ?>
      <li <?php if ($sf_context->getModuleName() == "users") {
            echo "class='active'";
          } ?>><a href="/plan/users"><i class="fa fa-th-large"></i> <span><?php echo __('Departments'); ?></span></a></li>
      <?php
    }

    if ($sf_user->mfHasCredential("access_members")) {
      if (Functions::client_can_add_businesses()) {
        //Iterate through any profiles that have been configured
        $q = Doctrine_Query::create()
          ->select('distinct(a.formid) as formid')
          ->from("SfGuardUserCategories a")
          ->where("a.formid <> 0");
        $profiles = $q->execute();

        foreach ($profiles as $profile) {
          //Get form name
      ?>
          <li <?php if ($sf_context->getModuleName() == "profiles") {
                echo "class='active'";
              } ?>><a href="/plan/profiles/index/filter/<?php echo $profile->getFormid(); ?>"><i class="fa fa-briefcase"></i> <span><?php echo $profile->getForm()->getFormName(); ?></span></a></li>
      <?php
        }
      }
      ?>
      <?php if ($sf_user->mfHasCredential("manageplotsdisabled")) {
      ?>
        <li <?php if ($sf_context->getModuleName() == "plot") {
              echo "class='active'";
            } ?>>
          <a href="<?php echo public_path('plan/plot/index'); ?>"><i class="fa fa-caret-right"></i><?php echo __('Plot Information'); ?></a>
        </li>
      <?php
      } ?>

      <li <?php if ($sf_context->getModuleName() == "frusers") {
            echo "class='active'";
          } ?>><a href="/plan/frusers/index"><i class="fa fa-user"></i> <span><?php echo __('Users'); ?></span></a></li>
    <?php
    }

    if ($sf_user->mfHasCredential("access_billing")) {
    ?>
      <li class="nav-parent <?php if ($sf_context->getModuleName() == "invoices") {
                              echo "nav-active active";
                            } ?>"><a href="#"><i class="fa fa-bar-chart-o"></i> <span><?php echo __('Billing'); ?></span></a>
        <ul class="children" <?php if ($sf_context->getModuleName() == "invoices") {
                                echo "style='display: block'";
                              } ?>>
          <li <?php if ($sf_context->getModuleName() == "invoices" && $sf_context->getActionName() == "index") {
                echo 'class="active"';
              } ?>>
            <a href="/plan/invoices/index"><i class="fa fa-money"></i> <span><?php echo __('Invoices'); ?></span></a>
          </li>
          <li <?php if ($sf_context->getModuleName() == "invoices" && $sf_context->getActionName() == "transactions") {
                echo 'class="active"';
              } ?>>
            <a href="/plan/invoices/transactions"><i class="fa fa-money"></i> <span><?php echo __('Payments'); ?></span></a>
          </li>
        </ul>
      </li>
    <?php
    }

    if ($sf_user->mfHasCredential("access_permits")) {
    ?>
      <li <?php if ($sf_context->getModuleName() == "permits") {
            echo "class='active'";
          } ?>><a href="/plan/permits/list"><i class="fa fa-certificate"></i> <span><?php echo __('Permits'); ?></span></a></li>
    <?php
    }

    if ($sf_user->mfHasCredential("accessfeedback")) {
    ?>
      <li <?php if ($sf_context->getModuleName() == "feedback") {
            echo "class='active'";
          } ?>><a href="/plan/feedback/index"><i class="fa fa-certificate"></i> <span><?php echo __('Feedback'); ?></span></a></li>
    <?php
    }

    if ($sf_user->mfHasCredential("access_reports")) {
    ?>
      <li class="nav-parent <?php if ($sf_context->getModuleName() == "reports") {
                              echo "nav-active active";
                            } ?>"><a href="#"><i class="fa fa-bar-chart-o"></i> <span><?php echo __('Reports'); ?></span></a>
        <ul class="children" <?php if ($sf_context->getModuleName() == "reports") {
                                echo "style='display: block'";
                              } ?>>
          <li <?php if ($sf_context->getModuleName() == "reports" && $sf_context->getActionName() == "managementR" && $sf_user->mfHasCredential("access_reports_m")) {
                echo 'class="active"';
              } ?>>
            <a href="<?php echo url_for('/plan/reports/managementR?dashboard=788ea657-2af2-4afd-b7a0-1a566dba7373') ?>"><i class="fa fa-area-chart"></i> <span><?php echo __('Management Reports'); ?></span></a>

          </li>
          <li <?php if ($sf_context->getModuleName() == "reports" && $sf_context->getActionName() == "professionals" && $sf_user->mfHasCredential("access_reports_m")) {
                echo 'class="active"';
              } ?>>
            <a href="<?php echo url_for('/plan/reports/professionals?dashboard=0c8a1850-860b-45c5-87e4-e2f405ed618c') ?>"><i class="fa fa-bar-chart-o"></i><span><?php echo __('Registered Users') ?></span></a>
          </li>
          <li <?php if ($sf_context->getModuleName() == "reports" && $sf_context->getActionName() == "revenue" && $sf_user->mfHasCredential("access_reports_m")) {
                echo 'class="active"';
              } ?>>
            <a href="<?php echo url_for('/plan/reports/revenue?dashboard=b6ebd20e-595e-4364-9281-b5e9590b10b4') ?>"><i class="fa fa-bar-chart-o"></i><span><?php echo __('Revenue (Paid Bills)') ?></span></a>
          </li>

          <li <?php if ($sf_context->getModuleName() == "reports" && $sf_context->getActionName() == "pending" && $sf_user->mfHasCredential("access_reports_m")) {
                echo 'class="active"';
              } ?>>
            <a href="#"></a>
          </li>

          <li <?php if ($sf_context->getModuleName() == "reports" && $sf_context->getActionName() == "subcountyReports") {
                echo 'class="active"';
              } ?>>
            <a  href="#"></a>
          </li>
        </ul>
      </li>

    <?php
    }


    //Allow users to access the customer support link
    ?>


    <?php
    if ($sf_user->mfHasCredential("access_settings") && $sf_user->mfHasCredential("code_access_rights")) {
    ?>
      <li>
        <hr>
      </li>
      <li><a href="/plan/settings/index"><i class="fa fa-cog"> </i> <span><?php echo __("System Settings"); ?></span></a></li>
    <?php
    }
    ?>

  </ul>

</div><!-- leftpanelinner -->
</div><!-- leftpanel -->