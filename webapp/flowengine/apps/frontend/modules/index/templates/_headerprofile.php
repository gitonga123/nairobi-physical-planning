<?php
/**
 * _header.php template.
 *
 * Displays Header
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
  use_helper('I18N');

  $profile = Functions::get_current_profile();
?>

 <!-- Fixed navbar with logout -->
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="row">

            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar1" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
                <a class="navbar-brand" href="/plan/"><span class="hidden-xs"><?php echo Functions::site_settings()->getOrganisationName(); ?></a>
            </div>
            <div id="navbar1" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown hidden-xs"><a><?php echo __("Welcome"); ?>, <?php echo $sf_user->getProfile()->getFullname(); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "dashboard"){ ?>active<?php } ?>"><a href="/plan/"><span class="fa fa-dashboard"></span> <?php echo __("Dashboard"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "forms"){ ?>active<?php } ?>"><a href="/plan/forms/groups/profile/<?php echo $profile->getId(); ?>"><span class="fa fa-plus-circle"></span> <?php echo __("Make Application"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "support"){ ?>active<?php } ?>"><a href="/plan/support/index"><span class="fa fa-question-circle"></span> <?php echo __("Support"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg"><a href="/backend.php/settings"><?php echo __("My Profile"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg"><a href="<?php echo url_for('signon/logout') ?>"><?php echo __("Logout"); ?></a></li>
                    <li class="dropdown hidden-xs m-r-30 dropdown-bordered">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fa fa-sign-out" aria-hidden="true"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-header"><?php echo __("Security"); ?></li>
                            <li><a href="/backend.php/settings"><?php echo __("Account Settings"); ?></a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?php echo url_for('signon/logout') ?>"><?php echo __("Logout"); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>

</nav>

<!-- Unfixed nav bar -->
<nav class="navbar navbar-default">
    <div class="container">
        <div class="row">

            <div class="navbar-header hidden-sm hidden-md hidden-lg">
                <a class="navbar-brand" href="/plan/"><?php echo __("Welcome"); ?>, <?php echo $sf_user->getProfile()->getFullname(); ?></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                  <li class="<?php if($sf_context->getModuleName() == "dashboard"){ ?>active<?php } ?>"><a href="/plan/"><span class="fa fa-dashboard"></span> <?php echo __("Dashboard"); ?></a></li>
                  <li class="<?php if($sf_context->getModuleName() == "forms"){ ?>active<?php } ?>"><a href="/plan/forms/groups/profile/<?php echo $profile->getId(); ?>"><span class="fa fa-plus-circle"></span> <?php echo __("Make Application"); ?></a></li>
                  <li class="<?php if($sf_context->getModuleName() == "support"){ ?>active<?php } ?>"><a href="/plan/support/index"><span class="fa fa-question-circle"></span> <?php echo __("Support"); ?></a></li>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>
</nav>
