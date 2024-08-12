<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>Uasin Gishu County - Admin Portal</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/asset_mentor/assets/img/favicon.ico">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/bootstrap.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/font-awesome.min.css">

	<!-- Main CSS -->
	<link rel="stylesheet" href="/asset_mentor/admin/assets/css/style.css">
</head>

<body>

	<!-- Main Wrapper -->
	<div class="main-wrapper login-body">
		<div class="login-wrapper">
			<div class="container">
				<div class="loginbox">
					<div class="login-left">
						<img class="img-fluid" src="/asset_mentor/admin/assets/img/logo2.png" alt="Logo - Uasin Gishu County">
					</div>
					<div class="login-right">
						<div class="login-right-wrap">
							<h1>Physical Planning, and Urban Development</h1>

							<?php echo $sf_content ?>

							<div class="login-or">
								<span class="or-line"></span>
								<span class="span-or">or</span>
							</div>



							<div class="text-center dont-have">Don’t have an account? <a href="mailto:lands@info@uasingishu.go.ke">Contact System Admin - lands@info@uasingishu.go.ke</a></div>
							<div class="col-md-12 ">
								<div class="copyright-text">
									<p class="mb-0">&copy; <?php echo date("Y") ?> Uasin Gishu County Physical Planning. All rights reserved.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->
	<script src="/asset_mentor/admin/assets/js/jquery-3.6.0.min.js"></script>

	<!-- Bootstrap Core JS -->
	<script src="/asset_mentor/admin/assets/js/bootstrap.bundle.min.js"></script>

	<!-- Feather Icon JS -->
	<script src="/asset_mentor/admin/assets/js/feather.min.js"></script>

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