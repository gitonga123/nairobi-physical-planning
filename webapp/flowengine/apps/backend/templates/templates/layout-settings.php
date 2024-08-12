<?php

/**
 * Layout template.
 *
 * Main Backend Template
 *
 *
 * @package    Backend
 * @theme      Bracket Response Bootstrap 3 Admin Template
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

$site_settings = Functions::site_settings();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="<?php echo __('The Gateway to all Physical Planning Services'); ?>">
  <meta name="author" content="Webmasters Africa">
  <title><?php echo $site_settings->getOrganisationName(); ?></title>

  <link rel="shortcut icon" href="/assets/img/favicon.ico" type="image/png">
  <?php
  //Displays all required stylesheets
  include_component('dashboard', 'stylesheets');

  //Displays all required javascripts
  include_component('dashboard', 'javascripts');
  ?>
</head>

<body id="body">

  <div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
  </div>

  <section>
    <?php
    //Displays the header
    include_component('dashboard', 'settingsheader');
    ?>

    <?php echo $sf_content ?>
    </div><!-- mainpanel -->
  </section>

</body>

</html>