<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<title>Uasin Gishu County - Physical Planning Portal</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/asset_mentor/assets/img/favicon.ico">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/bootstrap.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/all.min.css">
	<!-- Datatables CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/plugins/datatables/datatables.min.css">

	<!-- Main CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/style.css">

</head>

<body>

	<!-- Main Wrapper -->
	<div class="main-wrapper">

		<!-- Header -->
		<header class="header">
			<div class="header-fixed">
				<nav class="navbar navbar-expand-lg header-nav">
					<div class="navbar-header">
						<a id="mobile_btn" href="javascript:void(0);">
							<span class="bar-icon">
								<span></span>
								<span></span>
								<span></span>
							</span>
						</a>
						<a href="/plan/dashboard" class="navbar-brand logo">
							<img src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg" class="img-fluid"
								alt="Logo">
						</a>
					</div>
					<div class="main-menu-wrapper">
						<div class="menu-header">
							<a href="/plan/dashboard" class="menu-logo">
								<img src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg" class="img-fluid"
									alt="Logo">
							</a>
							<a id="menu_close" class="menu-close" href="javascript:void(0);">
								<i class="fas fa-times"></i>
							</a>
						</div>
						<ul class="main-nav">
							<li
								class="<?php echo $sf_context->getModuleName() == 'dashboard' && $sf_context->getActionName() == "index" ? 'active' : ''; ?>">
								<a href="/plan/dashboard">Dashboard</a>

							</li>
							<li
								class="has-submenu <?php echo $sf_context->getModuleName() == 'dashboard' && ($sf_context->getActionName() == "applicationslist" || $sf_context->getActionName() == "correctionsList") ? 'active' : ''; ?>">
								<a href="">Applications <i class="fas fa-chevron-down"></i></a>
								<ul class="submenu">
									<li
										class="<?php echo $sf_context->getModuleName() == 'dashboard' ? 'active' : ''; ?>">
										<a href="/plan/forms/groups/">Submit New</a></li>
									<li
										class="<?php echo $sf_context->getModuleName() == 'dashboard' && $sf_context->getActionName() == "correctionsList" ? 'active' : ''; ?>">
										<a href="/plan/dashboard/correctionsList">Corrections Applications</a></li>
									<li
										class="<?php echo $sf_context->getModuleName() == 'dashboard' && $sf_context->getActionName() == "applicationslist" ? 'active' : ''; ?>">
										<a href="/plan/dashboard/applicationslist">All Applications</a></li>
								</ul>
							</li>
							<li
								class="has-submenu <?php echo ($sf_context->getModuleName() == 'dashboard' || $sf_context->getModuleName() == 'invoices') && ($sf_context->getActionName() == "invoiceslist" || $sf_context->getActionName() == "paidinvoices" || $sf_context->getActionName() == "view") ? 'active' : ''; ?>">
								<a href="/plan/dashboard/invoiceslist">Invoices <i
										class="fas fa-chevron-down"></i></a>
								<ul class="submenu">
									<li
										class="<?php echo ($sf_context->getModuleName() == 'dashboard' && $sf_context->getActionName() == "invoiceslist") ? 'active' : ''; ?>">
										<a href="/plan/dashboard/invoiceslist">Pending Invoices</a></li>
									<li
										class="<?php echo $sf_context->getModuleName() == 'dashboard' && $sf_context->getActionName() == "paidinvoices" ? 'active' : ''; ?>">
										<a href="/plan/dashboard/paidinvoices">Paid Invoices</a></li>
								</ul>
							</li>
							<li
								class="<?php echo $sf_context->getModuleName() == 'permits' && ($sf_context->getActionName() == "index" || $sf_context->getActionName() == "view") ? 'active' : ''; ?>">
								<a href="/plan/permits">Permits & Licenses</a>

							</li>
							<li>
								<a href="/plan/feedback">Suggestions</a>

							</li>
							<li>
								<a href="/plan/feedback">Help Center</a>

							</li>
						</ul>
					</div>
					<ul class="nav header-navbar-rht">

						<!-- User Menu -->
						<li class="nav-item dropdown has-arrow logged-item">
							<a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
								<span class="user-img">
									<img class="rounded-circle" src="/asset_mentor/assets/img/user/user.jpg" width="31"
										alt="Darren Elder">
								</span>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<div class="user-header">
									<div class="avatar avatar-sm">
										<img src="/asset_mentor/assets/img/user/user.jpg" alt="User Image"
											class="avatar-img rounded-circle">
									</div>
									<div class="user-text">
										<h6><?php echo $sf_user->getProfile()->getFullname(); ?></h6>
										<p class="text-muted mb-0"><?php echo $sf_user->getProfile()->getEmail(); ?></p>
									</div>
								</div>

								<a class="dropdown-item" href="/plan/signon/logout">Logout</a>
							</div>
						</li>
						<!-- /User Menu -->

					</ul>
				</nav>
			</div>
		</header>
		<!-- /Header -->


		<!-- Page Content -->
		<div class="content">
			<div class="container-fluid">

				<div class="row">
					<div class="col-md-12 col-lg-12 col-xl-12">
						<?php echo $sf_content ?>
					</div>
				</div>

			</div>

		</div>
		<!-- /Page Content -->

		<!-- Footer -->
		<footer class="footer">

			<!-- Footer Top -->
			<div class="footer-top">
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-3 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-about">
								<div class="footer-logo">
									<img src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg" alt="logo">
								</div>
								<div class="footer-about-content">
									<p> Access your applications, invoices received, download permits and much more..
									</p>
									<div class="social-icon">
										<ul>
											<li>
												<a href="#" target="_blank"><i class="fab fa-facebook-f"></i> </a>
											</li>
											<li>
												<a href="#" target="_blank"><i class="fab fa-twitter"></i> </a>
											</li>
											<li>
												<a href="#" target="_blank"><i class="fab fa-linkedin-in"></i></a>
											</li>
											<li>
												<a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
											</li>
											<li>
												<a href="#" target="_blank"><i class="fab fa-dribbble"></i> </a>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<!-- /Footer Widget -->

						</div>

						<div class="col-lg-3 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-menu">
								<h2 class="footer-title">Important Links</h2>
								<ul>
									<li><a href="https://boraqs.or.ke/">Boraqs Link</a></li>
									<li><a href="https://aak.or.ke/">AoK Kenya</a></li>
									<li><a href="https://www.ebk.go.ke/">EBK</a></li>

								</ul>
							</div>
							<!-- /Footer Widget -->

						</div>

						<div class="col-lg-3 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-menu">
								<h2 class="footer-title">County Information</h2>
								<ul>
									<li><a href="https://www.info@uasingishu.go.ke/plan/files/153/Downloads/107/Uasin Gishu-COUNTY-DRAFT-FINANCE--BILL-2023.pdf">Finance Bill</a></li>
									<li><a
											href="https://www.info@uasingishu.go.ke/plan/departments/lands-physical-planning-housing-and-urban-development/department-overview-lands">Lands
											Department</a></li>
									<li><a href="https://info@uasingishu.go.ke/">County Website</a></li>
								</ul>
							</div>
							<!-- /Footer Widget -->

						</div>

						<div class="col-lg-3 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-contact">
								<h2 class="footer-title">Contact Us</h2>
								<div class="footer-contact-info">
									<div class="footer-address">
										<span><i class="fas fa-map-marker-alt"></i></span>
										<p>
											Official Contacts
											<p>
												<span tel="05320160000">05320160000</span>
												<br />
												<span tel="05320130148">05320130148</span>
												<br />
												<span tel="+254710646464">+254710 64 64 64</span>
												<br />
											</p>
									</div>

									<p class="mb-0">
										<i class="fas fa-envelope"></i>
										Email: lands@info@uasingishu.go.ke
									</p>
									<p class="mb-0">
										<i class="fas fa-envelope"></i>
										P.O. Box 40-30100, Eldoret.

									</p>
								</div>
							</div>
							<!-- /Footer Widget -->

						</div>

					</div>
				</div>
			</div>
			<!-- /Footer Top -->

			<!-- Footer Bottom -->
			<div class="footer-bottom">
				<div class="container-fluid">

					<!-- Copyright -->
					<div class="copyright">
						<div class="row">
							<div class="col-12 text-center">
								<div class="copyright-text">
									<p class="mb-0">&copy; <?php date('Y') ?> Uasin Gishu County eServices. All rights
										reserved.</p>
								</div>
							</div>
						</div>
					</div>
					<!-- /Copyright -->

				</div>
			</div>
			<!-- /Footer Bottom -->

		</footer>
		<!-- /Footer -->

	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->
	<script src="/asset_mentor/assets/js/jquery-3.6.0.min.js"></script>

	<!-- Bootstrap Core JS -->
	<script src="/asset_mentor/assets/js/bootstrap.bundle.min.js"></script>

	<!-- Sticky Sidebar JS -->
	<script src="/asset_mentor/assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
	<script src="/asset_mentor/assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>

	<!-- Slimscroll JS -->
	<script src="/asset_mentor/admin/assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

	<!-- Datatables JS -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
	<script src="/asset_mentor/admin/assets/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="/asset_mentor/admin/assets/plugins/datatables/datatables.min.js"></script>
	<script src="//cdn.datatables.net/plug-ins/1.10.12/sorting/datetime-moment.js"></script>

	<!-- Custom JS -->
	<script src="/asset_mentor/assets/js/script.js"></script>
	<script>
		if ($('.datatable').length > 0) {
			$('.datatable').DataTable({
				"bFilter": false,
			});
		}
		if ($('.datatable_applications').length > 0) {
			$.fn.dataTable.moment('DD-MM-YYYY HH:mm:ss');

			$('.datatable_applications').DataTable({
				"bFilter": true,
				order: [[2, 'desc']]
			});
		}
		if ($('.datatable_invoices').length > 0) {
			$.fn.dataTable.moment('DDo MMM YYYY HH:mm:ss');
			$('.datatable_invoices').DataTable({
				"bFilter": true,
				order: [[1, 'desc']]
			});
		}
		if ($('.datatable_permits').length > 0) {
			$.fn.dataTable.moment('DDo MMM YYYY HH:mm:ss');
			$('.datatable_permits').DataTable({
				"bFilter": true,
				order: [[4, 'desc']]
			});
		}
	</script>

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