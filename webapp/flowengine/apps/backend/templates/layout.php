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
include_component('dashboard', 'checksession');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="<?php echo $site_settings->getOrganisationDescription(); ?>">
  <meta name="author" content="<?php echo $site_settings->getOrganisationName(); ?>">
  <title><?php echo $site_settings->getOrganisationName(); ?></title>

  <link rel="shortcut icon" href="/assets_backend/images/favicon.png" type="image/png">
  <?php
	//Displays all required stylesheets
	include_component('dashboard', 'stylesheets');

	//Displays all required javascripts
	include_component('dashboard', 'javascripts');
?>
</head>
<body id="body">

    <section>
        <?php
        //Displays the header
        include_component('dashboard', 'header');
        ?>
		<?php if($sf_user->hasFlash('notice')): ?>
		<div class="alert alert-info">
		<p><?php echo $sf_user->getFlash('notice'); ?></p>
		</div>
		<?php endif; ?>
        <?php echo $sf_content ?>
        </div><!-- mainpanel -->
	</section>

</body>
</html>
