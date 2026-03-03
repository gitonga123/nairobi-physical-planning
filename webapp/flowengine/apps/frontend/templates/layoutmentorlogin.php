<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<title>Login - Access your account</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/asset_mentor/assets/img/favicon.ico">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/bootstrap.min.css">
	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="/asset_mentor/assets/plugins/fontawesome/css/all.min.css">
	<!-- Main CSS -->
	<link rel="stylesheet" href="/asset_mentor/assets/css/style.css">
</head>

<body class="account-page">
	<!-- Main Wrapper -->
	<div class="main-wrapper">
		<!-- Page Content -->
		<div class="bg-pattern-style">
			<div class="content">

				<!-- Login Tab Content -->
				<div class="account-content">
					<div class="account-box">
						<div class="login-right">
							<div class="login-header">
								<h3>Welcome to <span>Nairobi County Physical Planning </span></h3>
								<p class="text-muted">Please Login to your personal account to submit applications..</p>
							</div>
							<?php echo $sf_content ?>
						</div>
					</div>
				</div>
				<!-- /Login Tab Content -->

			</div>

		</div>
		<!-- /Page Content -->

	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->
	<script src="/asset_mentor/assets/js/jquery-3.6.0.min.js"></script>

	<!-- Bootstrap Core JS -->
	<script src="/asset_mentor/assets/js/bootstrap.bundle.min.js"></script>

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