<?php
 use_helper("I18N");
include_component('index', 'checksession');
$site_settings = Functions::site_settings();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <meta name="description" content="<?php echo $site_settings->getOrganisationDescription(); ?>">
    <meta name="author" content="<?php echo $site_settings->getOrganisationName(); ?>">
    <title><?php echo $site_settings->getOrganisationName(); ?></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <?php include_component('index', 'stylesheetsdash') ?>
</head>
<body>
<?php include_component('index', 'headerdash'); ?>
<?php echo $sf_content ?>
</body>
</html>