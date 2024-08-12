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
  <link rel="shortcut icon" href="/asset_mentor/assets/img/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/asset_mentor/assets/img/favicon.ico" type="image/x-icon">
  <!-- Keeping all css in one file. Keeping the layout tidy. -->
	<?php include_component('index', 'stylesheetsdash') ?>
</head>

<body>

  <!-- The following div must have id='content' or else file uploads will fail -->
  <span class="thecontent" id="content">
      <?php echo $sf_content ?>
  </span>

	<!-- Keeping all javascripts in one file. Keeping the layout tidy. -->
	<?php include_component('index', 'javascriptsdash') ?>
</body>
</html>
