<?php

/**
 * _stylesheets.php template.
 *
 * Displays stylesheets on the layout
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

$translation = new Translation();
if ($translation->IsLeftAligned()) {
	//If language is left aligned
?>
<?php
} else {
	//If language is right aligned

	?>
	<!-- <link href="/assets_backend/css3.0/style.default-rtl.css" rel="stylesheet">
	<link href="/assets_backend/css3.0/bootstrap-override-rtl.css" rel="stylesheet">
	<link href="/assets_backend/css3.0/bootsrap-rtl.min.css" rel="stylesheet"> -->

	<?php
}
?>

<link href="/assets_backend/css3.0/style.default.css?<?php echo md5(date("Y-m-d H:m:s")); ?>" rel="stylesheet">
<link href="/assets_backend/css3.0/fullcalendar.css" rel="stylesheet">
<link rel="stylesheet" href="/asset_mentor/admin/assets//plugins/select2/css/select2.min.css">
<link href="/assets_backend/css3.0/bootstrap-timepicker.min.css" rel="stylesheet" />
<link href="/assets_backend/css3.0/dropzone.css" rel="stylesheet" />
<link href="/assets_backend/css3.0/jquery.datatables.css" rel="stylesheet">
<!--
<link href="/assets_backend/css3.0/ectzn/style.ecitizenbackend.css" rel="stylesheet" />
<link href="/assets_backend/css3.0/ectzn/custom.css" rel="stylesheet" />
-->
<!-- Admin template for doctrine cruds -->
<link href="/assets_backend/css3.0/2.5/custom-backend.css?<?php echo md5(date("Y-m-d H:m:s")); ?>" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="/assets_backend/css/bootstrap-duallistbox.min.css">

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="/js/html5shiv.js"></script>
<script src="/js/respond.min.js"></script>
<![endif]-->

<!-- force - amkatek -->
<link href="/assets_backend/custom.css" rel="stylesheet" />