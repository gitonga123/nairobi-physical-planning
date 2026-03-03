<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>Nairobi County Revenue Collection - Admin Portal Dashboard</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/asset_mentor/assets/img/favicon.ico">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/bootstrap.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/font-awesome.min.css">

	<!-- Feathericon CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/feathericon.min.css">

	<!-- Morris CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/plugins/morris/morris.css">
	<!-- Datatables CSS -->
	<link rel="stylesheet" href="assets/plugins/datatables/datatables.min.css">
	<!-- Main CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/style.css">
</head>

<body>

	<!-- Main Wrapper -->
	<div class="main-wrapper">

		<!-- Header -->
		<?php include_component('dashboard', 'headermentor') ?>
		<!-- header -->

		<!-- Sidebar -->
		<?php include_component('dashboard', 'sidebarmentor') ?>
		<!-- /Sidebar -->

		<!-- Page Wrapper -->
		<?php echo $sf_content ?>
		<!-- /Page Wrapper -->

	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->
	<script src="/asset_mentor/admin/assets/js/jquery-3.6.0.min.js"></script>

	<!-- Bootstrap Core JS -->
	<script src="/asset_mentor/admin/assets/js/bootstrap.bundle.min.js"></script>

	<!-- Feather Icon JS -->
	<script src="/asset_mentor/admin/assets/js/feather.min.js"></script>

	<!-- Slimscroll JS -->
	<script src="/asset_mentor/admin/assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

	<!-- Raphael JS -->
	<script src="/asset_mentor/admin/assets/plugins/raphael/raphael.min.js"></script>

	<!-- Morris JS -->
	<script src="/asset_mentor/admin/assets/plugins/morris/morris.min.js"></script>

	<!-- Chart JS -->
	<script src="/asset_mentor/admin/assets/js/chart.morris.js"></script>
	<!-- Slimscroll JS -->
	<script src="/asset_mentor/admin/assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

	<!-- Datatables JS -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
	<script src="/asset_mentor/admin/assets/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="/asset_mentor/admin/assets/plugins/datatables/datatables.min.js"></script>
	<script src="//cdn.datatables.net/plug-ins/1.10.12/sorting/datetime-moment.js"></script>

	<!-- Custom JS -->
	<script src="/asset_mentor/admin/assets/js/script.js"></script>

	<script async src="https://www.googletagmanager.com/gtag/js?id=G-Z4BM5P1Z0W"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'G-Z4BM5P1Z0W');
	</script>

</body>

</html>