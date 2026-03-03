<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title><?php echo isset($sf_response) ? $sf_response->getTitle() : $site_settings->getOrganisationName(); ?></title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/asset_mentor/assets/img/favicon.ico">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/bootstrap.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/all.min.css">

	<!-- Owl Carousel CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/owl.carousel.min.css">
	<link rel="stylesheet" href="/asset_mentor/assets/css/owl.theme.default.min.css">

	<!-- Slick CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/slick/slick.css">
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/slick/slick-theme.css">

	<!-- Aos CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/aos/aos.css">

	<!-- Main CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/style.css">
	<style>
		.feature-icon-circle {
			width: 70px;
			height: 70px;
			margin: 0 auto 15px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			font-size: 26px;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
		}

		.feature-text {
			font-weight: 600;
			margin-bottom: 10px;
			font-size: 18px;
		}

		.governor-quote {
			border-left: 5px solid #0d6efd;
			padding: 20px 25px;
			background: #f8fbff;
			font-style: italic;
			font-size: 17px;
			line-height: 1.6;
			margin-top: 15px;
			position: relative;
		}

		.governor-quote::before {
			content: "“";
			font-size: 40px;
			color: #0d6efd;
			position: absolute;
			left: 15px;
			top: -10px;
		}

		.governor-quote p {
			margin: 0;
			padding-left: 10px;
		}

		.governor-quote footer {
			margin-top: 10px;
			font-style: normal;
			font-weight: 500;
			color: #555;
		}
	</style>
</head>

<body class="body-home-one">
	<!-- Main Wrapper -->
	<div class="main-wrapper">

		<!-- Header -->
		<header class="header header-four">
			<div class="header-fixed">
				<nav class="navbar navbar-expand-lg header-nav scroll-sticky-three">
					<div class="container">
						<div class="navbar-header">
							<a id="mobile_btn" href="javascript:void(0);">
								<span class="bar-icon">
									<span></span>
									<span></span>
									<span></span>
								</span>
							</a>
							<a href="#" class="navbar-brand logo">
								<img width="300px" height="200px"
									src="/assets/img/Coat_of_Arms_of_Nairobi_Logo.png" class="img-fluid"
									alt="Nairobi County Government">
							</a>

						</div>
						<div class="main-menu-wrapper">
							<div class="menu-header">
								<a href="/index.php/dashboard" class="menu-logo">
									<img src="/asset_mentor/assets/img/Coat_of_Arms_of_Nairobi_Logo.png"
										class="img-fluid"
										alt="Nakuru County">
								</a>
								<a id="menu_close" class="menu-close" href="javascript:void(0);">
									<i class="fas fa-times"></i>
								</a>
							</div>
							<ul class="main-nav">
								<li class="active has-submenu">
									<a href="/plan/dashboard">Getting Started</a>
								</li>
								<li class="has-submenu">
									<a href="">Development<i class="fas fa-chevron-down"></i></a>
									<ul class="submenu">
										<li class="has-submenu">
											<a href="/plan/dashboard">Construction Permit</a>
											<ul class="submenu">
												<li><a href="/plan/dashboard">Requirements</a></li>
												<li><a href="/plan/dashboard">Apply</a></li>
												<li><a href="add-/plan/dashboard">Report an Issue</a></li>
												<li><a href="edit-/plan/dashboard">Learn</a></li>
											</ul>
										</li>
										<li class="has-submenu">
											<a href="/plan/dashboard">Change of User</a>
											<ul class="submenu">
												<li><a href="/plan/dashboard">Requirements</a></li>
												<li><a href="/plan/dashboard">Apply</a></li>
												<li><a href="add-/plan/dashboard">Report an Issue</a></li>
												<li><a href="edit-/plan/dashboard">Learn</a></li>
											</ul>
										</li>

										<li class="has-submenu">
											<a href="/plan/dashboard">Subdivision</a>
											<ul class="submenu">
												<li><a href="/plan/dashboard">Requirements</a></li>
												<li><a href="/plan/dashboard">Apply</a></li>
												<li><a href="add-/plan/dashboard">Report an Issue</a></li>
												<li><a href="edit-/plan/dashboard">Learn</a></li>
											</ul>
										</li>

										<li class="has-submenu">
											<a href="/plan/dashboard">Amalgamation</a>
											<ul class="submenu">
												<li><a href="/plan/dashboard">Requirements</a></li>
												<li><a href="/plan/dashboard">Apply</a></li>
												<li><a href="add-/plan/dashboard">Report an Issue</a></li>
												<li><a href="edit-/plan/dashboard">Learn</a></li>
											</ul>
										</li>
									</ul>
								</li>
								<li class="has-submenu">
									<a href="">Business <i class="fas fa-chevron-down"></i></a>
									<ul class="submenu">
										<li class="has-submenu">
											<a href="#">Outdoor Advertising</a>
											<ul class="submenu">
												<li><a href="/plan/dashboard">Apply</a></li>
												<li><a href="/plan/dashboard">Learn</a></li>
											</ul>
										</li>
									</ul>

								</li>
								<li class="has-submenu">
									<a href="">Help<i class="fas fa-chevron-down"></i></a>
									<ul class="submenu">
										<li><a href="/plan/dashboard">About</a></li>
										<li><a href="/plan/help/contact">Contact Support</a></li>
										<li><a href="/plan/help/contact">Report an Issue</a></li>
									</ul>
								</li>

								<li class="login-link">
									<a href="<?php echo sfConfig::get('app_sso_authorize_url') ?>">Login / Signup</a>
								</li>
							</ul>
						</div>
						<ul class="nav header-navbar-rht">
							<li class="nav-item">
								<a class="nav-link header-login-two"
									href="<?php echo sfConfig::get('app_sso_authorize_url') ?>">Login</a>
							</li>
							<li class="nav-item">
								<a class="nav-link header-login"
									href="<?php echo sfConfig::get('app_sso_register_url') ?>">Sign up</a>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</header>
		<!-- /Header -->

		<!-- Home Banner -->
		<section class="section section-search">
			<div class="container">
				<div class="banner-wrapper m-auto text-center aos" data-aos="fade-up">
					<div class="banner-header">
						<h1>Nairobi County Land and Physical Planning</h1>
						<p>Apply for development approvals, and access county services online — fast, secure, and convenient..</p>
					</div>
				</div>
			</div>
		</section>
		<!-- /Home Banner -->

		<!-- Course Categories -->
		<section class="section how-it-works">
			<div class="container">
				<div class="section-header text-center aos" data-aos="fade-up">
					<span>How it Works</span>
					<p class="sub-title">
						Access Nakuru County services online in a few simple steps. Register, apply, pay, and receive your official documents seamlessly.
					</p>
				</div>

				<div class="row">

					<!-- Step 1 -->
					<div class="col-12 col-md-6 col-lg-4 mb-3 mb-3">
						<div class="feature-box text-center aos">
							<div class="feature-icon-circle bg-primary">
								<i class="fas fa-user-plus"></i>
							</div>
							<div class="feature-text">Register an Account</div>
							<p>Create your account on Nakuru eServices to access online applications securely.</p>
						</div>
					</div>

					<!-- Step 2 -->
					<div class="col-12 col-md-6 col-lg-4 mb-3">
						<div class="feature-box text-center aos">
							<div class="feature-icon-circle bg-success">
								<i class="fas fa-tasks"></i>
							</div>
							<div class="feature-text">Select The Service</div>
							<p>Choose the service you need, whether building permits, licenses, or other services.</p>
						</div>
					</div>

					<!-- Step 3 -->
					<div class="col-12 col-md-6 col-lg-4 mb-3">
						<div class="feature-box text-center aos">
							<div class="feature-icon-circle bg-warning">
								<i class="fas fa-file-signature"></i>
							</div>
							<div class="feature-text">Fill & Submit</div>
							<p>Provide required details and documents online, then submit your application.</p>
						</div>
					</div>

					<!-- Step 4 -->
					<div class="col-12 col-md-6 col-lg-4 mb-3 mt-4 mt-lg-0">
						<div class="feature-box text-center aos">
							<div class="feature-icon-circle bg-danger">
								<i class="fas fa-credit-card"></i>
							</div>
							<div class="feature-text">Pay Online</div>
							<p>Make secure payments and instantly generate your invoice and receipt.</p>
						</div>
					</div>

					<!-- Step 5 -->
					<div class="col-12 col-md-6 col-lg-4 mb-3 mt-4 mt-lg-0">
						<div class="feature-box text-center aos">
							<div class="feature-icon-circle bg-info">
								<i class="fas fa-file-download"></i>
							</div>
							<div class="feature-text">Get Your Document</div>
							<p>Download your approved permits and licenses instantly once processing is complete.</p>
						</div>
					</div>

				</div>
			</div>
		</section>
		<!-- /Course Categories -->

		<section class="section statistics-section">
			<div class="container">
				<div class="row">

					<div class="col-12 col-md-4">
						<div class="statistics-list text-center aos" data-aos="fade-up">
							<span>5,000+</span>
							<h3>Citizens Served Online</h3>
							<p class="mb-0 text-light">Residents accessing county services digitally</p>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<div class="statistics-list text-center aos" data-aos="fade-up">
							<span>12,000+</span>
							<h3>Applications Processed</h3>
							<p class="mb-0 text-light">Permits, payments, and approvals issued online</p>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<div class="statistics-list text-center aos" data-aos="fade-up">
							<span>24/7</span>
							<h3>Access to Services</h3>
							<p class="mb-0 text-light">Apply and pay anytime, anywhere</p>
						</div>
					</div>

				</div>
			</div>
		</section>
		<!-- /Statistics Section -->
		<!-- Footer -->
		<footer class="footer footer-three">

			<!-- Footer Top -->
			<div class="footer-top footer-two-top aos " data-aos="fade-up">
				<div class="container">
					<div class="row">
						<div class="col-lg-4 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-about">
								<div class="footer-logo">
									<img src="/assets/img/Coat_of_Arms_of_Nairobi_Logo.png" alt="Nairobi County">
								</div>
								<div class="footer-about-content">
									<p class="footer-sub-text">Nairobi County - Lands, Physical Planning and Urban
										Development!</p>

								</div>
								<div class="footer-three-contact">
									<p><span>lands@nairobi.go.ke</span></p>
									<p>
										<span tel="05320160000">0XXXX0000</span>
										<br />
										<span tel="05320130148">05XXXX8</span>
										<br />
										<span tel="+254710646464">+254710 60 60 00</span>
										<br />
									</p>
								</div>
							</div>
							<!-- /Footer Widget -->

						</div>
						<div class="col-lg-2 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-menu">
								<ul>
									<li>
										<a href="/plan/login">Construction Permits

											<ul>
												<li>Building Permits</li>
												<li>Perimeter Wall</li>
												<li>Renewals</li>
												<li>Renovations</li>
												<li>Hoarding</li>
											</ul>
										</a>
									</li>

									<li><a href="/plan/login">Login</a></li>
								</ul>
							</div>
							<!-- /Footer Widget -->

						</div>
						<div class="col-lg-2 col-md-6">

							<!-- Footer Widget -->
							<div class="footer-widget footer-menu">
								<ul>
									<li><a href="/plan/login">Change of User</a></li>
									<li><a href="/plan/login">Subdivision</a></li>
									<li><a href="/plan/login">Renewal/Extension of Lease</a></li>
									<li><a href="/plan/login">Outdoor Advertising</a></li>
								</ul>
							</div>
							<!-- /Footer Widget -->

						</div>
					</div>
				</div>
			</div>
			<!-- /Footer Top -->

			<!-- Footer Bottom -->
			<div class="footer-bottom ">
				<div class="container">
					<!-- Copyright -->
					<div class="copyright-border"></div>
					<div class="copyright ">
						<div class="row align-items-center">
							<div class="col-md-6 ">
								<div class="term-privacy">
									<div class="social-icon">
										<ul class="align-items-center">
											<li>
												<a href="javascript:void(0);">Terms</a>
											</li>
											<li>
												<a href="javascript:void(0);">Privacy </a>
											</li>
											<li>
												<a href="javascript:void(0);">Cookies </a>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="col-md-6 ">
								<div class="copyright-text">
									<p class="mb-0">&copy; <?php echo date("Y") ?> Nairobi County Physical Planning and
										Urban Development. All rights
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

	<!-- Owl Carousel -->
	<script src="/asset_mentor/assets/js/owl.carousel.min.js"></script>

	<!-- Slick Slider -->
	<script src="/asset_mentor/assets/plugins/slick/slick.js"></script>

	<!-- Aos -->
	<script src="/asset_mentor/assets/plugins/aos/aos.js"></script>

	<!-- Custom JS -->
	<script src="/asset_mentor/assets/js/script.js"></script>

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