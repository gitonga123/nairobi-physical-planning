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
?>

 <!-- Fixed navbar with logout -->
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="row">


            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar1" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only"><?php echo __("Toggle navigation"); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>

        </button>
			<?php $site_settings=Functions::site_settings(); ?>
				<?php if(!strlen($site_settings->getAdminImageUrl())): ?> 
                <a class="navbar-brand" href="/index.php"><span class="hidden-xs"></span><?php echo $site_settings->getOrganisationName(); ?></a>
				<?php else: ?>
                <a class="navbar-brand" href="/index.php"><span class="hidden-xs"></span><img src="<?php echo '/'.$site_settings->getUploadDir().'/'.$site_settings->getAdminImageUrl() ?>" style="margin-top:-10px" class="logo" width="150px" height="40px" alt="" /></a>
				<?php endif; ?>
            </div>
            <div id="navbar1" class="navbar-collapse collapse">
                <!-- User Profile and Sign Out -->
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown hidden-xs"><a><?php echo __("Welcome"); ?>, <?php echo $sf_user->getProfile()->getFullname(); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "dashboard"){ ?>active<?php } ?>"><a href="/index.php"><span class="fa fa-dashboard"></span> <?php echo __("Dashboard"); ?></a></li>
                    <?php if(Functions::has_accessible_forms()){ ?>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "forms"){ ?>active<?php } ?>"><a href="/index.php/forms/groups"><span class="fa fa-plus-circle"></span> <?php echo __("Make Application"); ?></a></li>
                    <?php } ?>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "dashboard" && $sf_context->getActionName() == "applicationslist"){ ?>active<?php } ?>"><a href="/index.php/dashboard/applicationslist"><span class="fa fa-bars"></span> <?php echo __("Applications"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "dashboard" && $sf_context->getActionName() == "invoiceslist"){ ?>active<?php } ?>"><a href="/index.php/dashboard/invoiceslist"><span class="fa fa-bars"></span> <?php echo __("Bills"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "feedback"){ ?>active<?php } ?>"><a href="/index.php/feedback"><span class="fa fa-plus-circle"></span> <?php echo __("Feedback"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "calculator"){ ?>active<?php } ?>"><a href="/index.php/calculator"><span class="fa fa-plus-circle"></span> <?php echo __("Get estimate"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg <?php if($sf_context->getModuleName() == "support"){ ?>active<?php } ?>"><a href="/index.php/support/index"><span class="fa fa-question-circle"></span> <?php echo __("Support"); ?><?php if($messages > 0){ echo " <font color='red'>(".$messages.")</font>"; } ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg"><a href="/index.php/settings"><?php echo __("My Profile"); ?></a></li>
                    <li class="hidden-sm hidden-md hidden-lg"><a href="<?php echo url_for('signon/logout') ?>"><?php echo __("Logout"); ?></a></li>
                    <li class="dropdown hidden-xs m-r-30 dropdown-bordered">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fa fa-sign-out" aria-hidden="true"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-header"><?php echo __("Security"); ?></li>
                            <li><a href="/index.php/settings"><?php echo __("Account Settings"); ?></a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?php echo url_for('signon/logout') ?>"><?php echo __("Logout"); ?></a></li>
                        </ul>
                    </li>
                </ul>

                <?php
                $languages = Functions::get_languages();
                if(sizeof($languages) > 1)
                {
                ?>
                <!-- Language Switcher -->
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown hidden-xs m-r-30 dropdown-bordered">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fa fa-language" aria-hidden="true"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-header"><?php echo __("Languages"); ?></li>
                            <?php foreach($languages as $language){ ?>
                            <li <?php if($language->getLocaleIdentifier() == $sf_user->getCulture()){ echo "class='active'"; } ?>><a href="/index.php/index/setlocale/code/<?php echo $language->getLocaleIdentifier(); ?>"><?php echo $language->getLocalTitle(); ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                <form class="navbar-form navbar-right" action="<?php echo url_for('/index.php/application/search') ?>" method="get">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo __('Enter Application Number..')?>" name="query" value="<?php echo $sf_request->getParameter('query') ?>" id="search_keywords" />
                    </div>
                    <button class="btn btn-primary" type="submit"><?php echo __('Search') ?></button>
                </form>

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
                <a class="navbar-brand" href="/index.php"><?php echo __("Welcome"); ?>, <?php echo $sf_user->getProfile()->getFullname(); ?></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="<?php if($sf_context->getModuleName() == "dashboard"){ ?>active<?php } ?>"><a href="/index.php"><span class="fa fa-dashboard"></span> <?php echo __("Dashboard"); ?></a></li>
                    <?php if(Functions::has_accessible_forms()){ ?>
                    <li class="<?php if($sf_context->getModuleName() == "forms"){ ?>active<?php } ?>"><a href="/index.php/forms/groups"><span class="fa fa-plus-circle"></span> <?php echo __("Submit Application"); ?></a></li>
                    <?php } ?>
                    <li class="<?php if($sf_context->getModuleName() == "dashboard" && $sf_context->getActionName() == "applicationslist"){ ?>active<?php } ?>"><a href="/index.php/dashboard/applicationslist"><span class="fa fa-bars"></span> <?php echo __("Submissions"); ?></a></li>
                    <li class="<?php if($sf_context->getModuleName() == "dashboard" && $sf_context->getActionName() == "invoiceslist"){ ?>active<?php } ?>"><a href="/index.php/dashboard/invoiceslist"><span class="fa fa-bars"></span> <?php echo __("Your Bills"); ?></a></li>
                    <li class="<?php if($sf_context->getModuleName() == "support"){ ?>active<?php } ?>"><a href="/index.php/support/index"><span class="fa fa-question-circle"></span> <?php echo __("Get Help ?"); ?><?php if($messages > 0){ echo " <font color='red'>(".$messages.")</font>"; } ?></a></li>
                    <li class="<?php if($sf_context->getModuleName() == "feedback"){ ?>active<?php } ?>"><a href="<?php echo url_for('feedback/index') ?>"><span class="fa fa-plus-circle"></span> <?php echo __("Suggestions"); ?></a></li>
                
					<li class="<?php if($sf_context->getModuleName() == "sharedapplication"){ ?>active<?php } ?>">
						<a href="<?php echo public_path('/index.php/sharedapplication') ?>"><i class="fa fa-share"></i>
							<span> <?php echo __('Share'); ?></span></a>
					</li>
				
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>
</nav>
