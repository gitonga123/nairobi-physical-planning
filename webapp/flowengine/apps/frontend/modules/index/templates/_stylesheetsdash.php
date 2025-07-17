<?php
/**
 * _stylesheets.php template.
 *
 * Displays Stylesheets
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

$translation = new Translation();
if($translation->IsLeftAligned())
{
	?>
	<!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="/assets_frontend/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets_frontend/css/docs.min.css" crossorigin="anonymous">

    <!-- Font Icons -->
    <link rel="stylesheet" href="/assets_frontend/css/font-awesome.min.css" crossorigin="anonymous">

    <!-- Custom -->
    <link rel="stylesheet" href="/assets_frontend/css/custom.css" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
	<?php
}
else
{
	?>
	<!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="/assets_frontend/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets_frontend/css/docs.min.css" crossorigin="anonymous">

    <!-- Font Icons -->
    <link rel="stylesheet" href="/assets_frontend/css/font-awesome.min.css" crossorigin="anonymous">

    <!-- Custom -->
    <link rel="stylesheet" href="/assets_frontend/css/custom.css" crossorigin="anonymous">

    <!-- <link rel="stylesheet" href="/assets_frontend/css/style.default.rtl.css" crossorigin="anonymous"> -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">

	<?php
}
?>