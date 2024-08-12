<?php
/**
 * Frontend Layout.
 *
 * Main layout for the frontend
 *
 * @package		Frontend
 * @author		Webmasters Africa (info@webmastersafrica.com)
 */
 use_helper("I18N");

 //Logout backend users so they don't clash with frontend security & set language
 include_component('index', 'checksession');

 $site_settings = Functions::site_settings();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="<?php echo $site_settings->getOrganisationDescription(); ?>">
    <meta name="author" content="<?php echo $site_settings->getOrganisationName(); ?>">
    <title><?php echo $site_settings->getOrganisationName(); ?></title>
    <link rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">      <!-- Keeping all css in one file. Keeping the layout tidy. -->
      <?php include_component('index', 'stylesheetsdash') ?>

  </head>

  <body>
    <?php include_component('index', 'headerprofile'); ?>

    <div class="container">
        <div class="row">

          <div class="col-sm-2">
              <div class="nav-sidebar hidden-xs">
                  <div class="profile-card">
                      <div class="thumbnail thumbnail-reveal">
                        <img class="img-responsive hidden-xs" src="/assets_frontend/images/avatar.png">
                      </div>
                      <div class="profile-info">
                          <?php
                          $profile = Functions::get_current_profile();
                          ?>
                          <h4><?php echo $profile->getTitle(); ?></h4>
                          <ul class="nav user-profile-info-list">
                              <li class="user-profile-info-list-item">Name. <?php echo $sf_user->getProfile()->getFullname(); ?></li>
                              <li class="user-profile-info-list-item">ID No. <?php echo $sf_user->getUsername(); ?></li>
                              <li class="user-profile-info-list-item">Phone. <?php echo $sf_user->getProfile()->getMobile(); ?></li>
                              <li class="user-profile-info-list-item truncate"><?php echo $sf_user->getEmail(); ?></li>
                          </ul>
                      </div>
                  </div>
              </div>
          </div>

          <div class="<?php echo (($site_settings->getOrganisationHelp() || $site_settings->getOrganisationSidebar()) && $sf_context->getModuleName() != "application")?'col-sm-8':'col-sm-10'; ?>">
            <?php echo $sf_content ?>
          </div>

          <?php
          if(($site_settings->getOrganisationHelp() || $site_settings->getOrganisationSidebar()) && $sf_context->getModuleName() != "application")
          {
            ?>
            <div class="col-sm-2">
                <?php
                if($site_settings->getOrganisationHelp())
                {
                    ?>
                        <div class="panel panel-default">
                                <?php echo $site_settings->getOrganisationHelp(); ?>
                        </div>
                    <?php
                }

                if($site_settings->getOrganisationSidebar())
                {
                    ?>
                        <div class="panel panel-default">
                                <?php echo $site_settings->getOrganisationSidebar(); ?>
                        </div>
                    <?php
                }
                ?>
            </div>
            <?php
          }
          ?>

      </div> <!-- end container -->
    </div>

    <!-- Keeping all javascripts in one file. Keeping the layout tidy. -->
    <?php include_component('index', 'javascriptsdash') ?>

  <!-- End wrapper -->
</body>
</html>
