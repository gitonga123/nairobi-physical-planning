<?php
/**
 * Frontend Layout.
 *
 * Main layout for the frontend
 *
 * @package		Frontend
 * @theme		eCitizen
 * @author		Webmasters Africa (info@webmastersafrica.com)
 */
 use_helper("I18N");

 $site_settings = Functions::site_settings();
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
  <!-- Mobile Specific Metas
  ================================================== -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $site_settings->getOrganisationDescription(); ?>">
  <meta name="author" content="<?php echo $site_settings->getOrganisationName(); ?>">
  <title><?php echo $site_settings->getOrganisationName(); ?></title>
  <link rel="shortcut icon" href="/asset_mentor/assets/img/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/asset_mentor/assets/img/favicon.ico" type="image/x-icon"> 
 <!-- Keeping all css in one file. Keeping the layout tidy. -->
  <?php include_component('index', 'stylesheets') ?>

</head>

<body>

  <?php include_component('index', 'header') ?>

    <?php
     if($sf_user->hasFlash("notice"))
     {
     ?>
     <div class="alert alert-error">
          <button class="close" data-dismiss="alert">&times;</button>
          <?php echo __("Sorry! The username or password you entered is incorrect."); ?>
          <a href="/plan/reset-request">
          <?php echo __("Did you forget?"); ?>
           </a>
     </div>
     <?php
     }
     ?>

    <!-- BEGIN CONTAINER -->
    <?php echo $sf_content ?>
    <!-- END CONTAINER -->


<!-- Keeping all javascripts in one file. Keeping the layout tidy. -->
<?php include_component('index', 'footer') ?>

<!-- Keeping all javascripts in one file. Keeping the layout tidy. -->
<?php include_component('index', 'javascripts') ?>

</body>
</html>
