<?php use_helper('I18N'); ?>
<!DOCTYPE html>
<html>	
<head>
	<?php
	include_component('dashboard', 'stylesheets');
        

	//Displays all required javascripts
	include_component('dashboard', 'javascripts');
	?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
  <meta name="description" content="<?php echo sfConfig::get('app_organisation_name').' '.sfConfig::get('app_organisation_description') ?>">
  <meta name="author" content="<?php echo sfConfig::get('app_organisation_name') ?>">
  <title><?php echo sfConfig::get('app_organisation_name').' '.sfConfig::get('app_organisation_description'); ?></title>
   	<link rel="shortcut icon" href="./nairobi_template/images/nairobi_logo.png" />	
</head>
<body class="body">
<?php echo $sf_content ?>
</body>
</html>