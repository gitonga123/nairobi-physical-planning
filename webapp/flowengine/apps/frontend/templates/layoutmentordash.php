<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<title>Uasin Gishu County - eServices Portal</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/asset_mentor/assets/img/favicon.png">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/bootstrap.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/all.min.css">
	<!-- Datatables CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/plugins/datatables/datatables.min.css">
	<!-- Main CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/style.css">
	<!-- jQuery -->
	<script src="/asset_mentor/assets/js/jquery-3.6.0.min.js"></script>

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
						<a href="/index.php/dashboard" class="navbar-brand logo">
							<img src="/assets_frontend_amkatek/images/award-logo/logo2.png" class="img-fluid" alt="Logo">
						</a>
					</div>
					<div class="main-menu-wrapper">
						<div class="menu-header">
							<a href="/index.php/dashboard" class="menu-logo">
								<img src="/assets_frontend_amkatek/images/award-logo/logo2.png" class="img-fluid" alt="Logo">
							</a>
							<a id="menu_close" class="menu-close" href="javascript:void(0);">
								<i class="fas fa-times"></i>
							</a>
						</div>
						<ul class="main-nav">
							<li class="<?php echo $sf_context->getModuleName() == 'dashboard'  && $sf_context->getActionName() == "index" ? 'active' : ''; ?>">
								<a href="/index.php/dashboard">Dashboard</a>

							</li>
							<li class="has-submenu <?php echo $sf_context->getModuleName() == 'dashboard' && ($sf_context->getActionName() == "applicationslist" || $sf_context->getActionName() == "correctionsList") ? 'active' : ''; ?>">
								<a href="">Applications <i class="fas fa-chevron-down"></i></a>
								<ul class="submenu">
									<li class="<?php echo $sf_context->getModuleName() == 'dashboard' ? 'active' : ''; ?>"><a href="/index.php/forms/groups/">Submit New</a></li>
									<li class="<?php echo $sf_context->getModuleName() == 'dashboard'  && $sf_context->getActionName() == "correctionsList" ? 'active' : ''; ?>"><a href="/index.php/dashboard/correctionsList">Corrections Applications</a></li>
									<li class="<?php echo $sf_context->getModuleName() == 'dashboard'  && $sf_context->getActionName() == "applicationslist" ? 'active' : ''; ?>"><a href="/index.php/dashboard/applicationslist">All Applications</a></li>
								</ul>
							</li>

							<li class="has-submenu <?php echo ($sf_context->getModuleName() == 'dashboard' || $sf_context->getModuleName() == 'invoices')  && ($sf_context->getActionName() == "invoiceslist" || $sf_context->getActionName() == "paidinvoices" || $sf_context->getActionName() == "view") ? 'active' : ''; ?>">
								<a href="/index.php/dashboard/invoiceslist">Invoices <i class="fas fa-chevron-down"></i></a>
								<ul class="submenu">
									<li class="<?php echo ($sf_context->getModuleName() == 'dashboard'  && $sf_context->getActionName() == "invoiceslist") ? 'active' : ''; ?>"><a href="/index.php/dashboard/invoiceslist">Pending Invoices</a></li>
									<li class="<?php echo $sf_context->getModuleName() == 'dashboard'  && $sf_context->getActionName() == "paidinvoices" ? 'active' : ''; ?>"><a href="/index.php/dashboard/paidinvoices">Paid Invoices</a></li>
								</ul>
							</li>
							<li class="<?php echo $sf_context->getModuleName() == 'permits'  && ($sf_context->getActionName() == "index" || $sf_context->getActionName() == "view") ? 'active' : ''; ?>">
								<a href="/index.php/permits">Permits & Licenses</a>

							</li>
							<li class="<?php echo $sf_context->getModuleName() == 'support'  && ($sf_context->getActionName() == "index" || $sf_context->getActionName() == "view" || $sf_context->getActionName() == "create") ? 'active' : ''; ?>">
								<a href="/index.php/support">Messages</a>

							</li>
							<li>
								<a href="/index.php/feedback">Help Center</a>

							</li>
						</ul>
					</div>
					<ul class="nav header-navbar-rht">

						<!-- User Menu -->
						<li class="nav-item dropdown has-arrow logged-item">
							<a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
								<span class="user-img">
									<img class="rounded-circle" src="/asset_mentor/assets/img/user/user.jpg" width="31" alt="Darren Elder">
								</span>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<div class="user-header">
									<div class="avatar avatar-sm">
										<img src="/asset_mentor/assets/img/user/user.jpg" alt="User Image" class="avatar-img rounded-circle">
									</div>
									<div class="user-text">
										<h6><?php echo $sf_user->getProfile()->getFullname(); ?></h6>
										<p class="text-muted mb-0"><?php echo $sf_user->getProfile()->getEmail(); ?></p>
									</div>
								</div>
								<a class="dropdown-item" href="/index.php/settings">Account Settings</a>
								<a class="dropdown-item text-primary" href="/index.php/signon/logout">Logout</a>
							</div>
						</li>
						<!-- /User Menu -->

					</ul>
				</nav>
			</div>
		</header>
		<!-- /Header -->

		<!-- Breadcrumb -->
		<div class="breadcrumb-bar">
			<div class="container-fluid">
				<div class="row align-items-center">
					<div class="col-md-12 col-12">
						<nav aria-label="breadcrumb" class="page-breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="/index.php">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
							</ol>
						</nav>
						<h2 class="breadcrumb-title">Dashboard</h2>
					</div>
				</div>
			</div>
		</div>
		<!-- /Breadcrumb -->

		<!-- Page Content -->
		<div class="content">
			<div class="container-fluid">

				<div class="row">
					<div class="col-md-4 col-lg-3 col-xl-2 theiaStickySidebar">
						<?php
						function getInitials($string)
						{
							$words = explode(" ", $string);
							$initials = "";

							foreach ($words as $word) {
								$initials .= strtoupper(substr($word, 0, 1));
							}

							return $initials;
						}
						?>
						<!-- Sidebar -->
						<div class="profile-sidebar">
							<div class="user-widget">
								<div class="pro-avatar"><?php echo getInitials($sf_user->getProfile()->getFullname()); ?></div>
								<div class="rating">
								</div>

								<div class="user-info-cont">
									<h4 class="usr-name"><?php echo $sf_user->getProfile()->getFullname(); ?></h4>
									<div class="justify-content-start">

										<p class="mentor-type"><strong class="px-2"><?php echo ("Phone"); ?>:</strong> <?php echo $sf_user->getProfile()->getMobile(); ?></p>

										<p class="mentor-type"><strong class="px-2"><?php echo ("Email"); ?>:</strong> <?php echo $sf_user->getProfile()->getEmail(); ?></p>

									</div>
								</div>
							</div>

							<hr />

							<div class="custom-sidebar-nav mt-5">
								<ul>
									<li><a href="/index.php/settings"><i class="fas fa-user-cog"></i>Profile <span><i class="fas fa-chevron-right"></i></span></a></li>
									<li><a href="/index.php/signon/logout"><i class="fas fa-sign-out-alt"></i>Logout <span><i class="fas fa-chevron-right"></i></span></a></li>
								</ul>
							</div>
						</div>
						<!-- /Sidebar -->

					</div>

					<?php echo $sf_content ?>

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
									<img src="/assets_frontend_amkatek/images/award-logo/logo2.png" alt="logo">
								</div>
								<div class="footer-about-content">
									<p> Access your applications, invoices received, download permits and much more.. </p>
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
									<li><a href="https://nakuru.go.ke/finance-documents/">Finance Bill</a></li>
									<li><a href="https://nakuru.go.ke/lands-physical-planning-housing-urban-development/">Lands Department</a></li>
									<li><a href="https://nakuru.go.ke/">County Website</a></li>
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
											(05320160000)
									</div>
									<!-- <p>
										<i class="fas fa-phone-alt"></i>
										05320160000
									</p> -->
									<p class="mb-0">
										<i class="fas fa-envelope"></i>

										Email: info@uasingishu.go.ke


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
									<p class="mb-0">&copy; <?php date('Y') ?> Nakuru County eServices. All rights reserved.</p>
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

	<!-- Bootstrap Core JS -->
	<script src="/asset_mentor/assets/js/bootstrap.bundle.min.js"></script>
	<!-- Sticky Sidebar JS -->
	<script src="/asset_mentor/assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
	<script src="/asset_mentor/assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>

	<!-- Slimscroll JS -->
	<script src="/asset_mentor/admin/assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

	<!-- Datatables JS -->
	<script src="/asset_mentor/admin/assets/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="/asset_mentor/admin/assets/plugins/datatables/datatables.min.js"></script>
	<!-- Custom JS -->
	<script src="/asset_mentor/assets/js/script.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.core.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.tabs.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.mouse.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.sortable.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.draggable.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.position.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery-ui/ui/jquery.ui.dialog.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery.mini_colors.js"></script>
	<script type="text/javascript" src="/form_builder/js/uploadify/swfobject.js"></script>
	<script type="text/javascript" src="/form_builder/js/uploadify/jquery.uploadify.js"></script>
	<script type="text/javascript" src="/form_builder/js/uploadifive/jquery.uploadifive.js"></script>
	<script type="text/javascript" src="/form_builder/js/jquery.jqplugin.min.js"></script>
	<script type="text/javascript" src="/form_builder/js/theme_editor.js"></script>
	<script>
		$('.datatable').DataTable({
			"bFilter": true,
		});
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