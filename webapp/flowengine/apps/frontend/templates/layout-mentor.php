<?php
/**
 * Frontend Layout.
 *
 * Main layout for the frontend
 *
 * @package		Frontend
 * @theme		eCitizen
 * @author		Webmasters Africa (info@webmastersafrica.com)
 */
use_helper("I18N");

//Logout backend users so they don't clash with frontend security & set language
include_component('index', 'checksession');

$site_settings = Functions::site_settings();
?>

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

</head>

<body class="body-home-one">
    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Header -->
        <header class="header  header-four">
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
                                    src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg" class="img-fluid"
                                    alt="Uasin Gishu County Government">
                            </a>
                        </div>
                        <div class="main-menu-wrapper">
                            <div class="menu-header">
                                <a href="#" class="menu-logo">
                                    <img width="50px" height="50px"
                                        src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg" class="img-fluid"
                                        alt="Logo">
                                </a>
                                <a id="menu_close" class="menu-close" href="javascript:void(0);">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            <ul class="main-nav">
                                <li class="active has-submenu">
                                    <a href="/index.php/dashboard">Getting Started</a>
                                </li>
                                <li class="has-submenu">
                                    <a href="">Development<i class="fas fa-chevron-down"></i></a>
                                    <ul class="submenu">
                                        <li class="has-submenu">
                                            <a href="/index.php/dashboard">Construction Permit</a>
                                            <ul class="submenu">
                                                <li><a href="/index.php/dashboard">Requirements</a></li>
                                                <li><a href="/index.php/dashboard">Apply</a></li>
                                                <li><a href="add-/index.php/dashboard">Report an Issue</a></li>
                                                <li><a href="edit-/index.php/dashboard">Learn</a></li>
                                            </ul>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="/index.php/dashboard">Land rates Clearance</a>
                                            <ul class="submenu">
                                                <li><a href="/index.php/dashboard">Requirements</a></li>
                                                <li><a href="/index.php/dashboard">Apply</a></li>
                                                <li><a href="add-/index.php/dashboard">Report an Issue</a></li>
                                                <li><a href="edit-/index.php/dashboard">Learn</a></li>
                                            </ul>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="/index.php/dashboard">Change of User</a>
                                            <ul class="submenu">
                                                <li><a href="/index.php/dashboard">Requirements</a></li>
                                                <li><a href="/index.php/dashboard">Apply</a></li>
                                                <li><a href="add-/index.php/dashboard">Report an Issue</a></li>
                                                <li><a href="edit-/index.php/dashboard">Learn</a></li>
                                            </ul>
                                        </li>

                                        <li class="has-submenu">
                                            <a href="/index.php/dashboard">Subdivision</a>
                                            <ul class="submenu">
                                                <li><a href="/index.php/dashboard">Requirements</a></li>
                                                <li><a href="/index.php/dashboard">Apply</a></li>
                                                <li><a href="add-/index.php/dashboard">Report an Issue</a></li>
                                                <li><a href="edit-/index.php/dashboard">Learn</a></li>
                                            </ul>
                                        </li>

                                        <li class="has-submenu">
                                            <a href="/index.php/dashboard">Amalgamation</a>
                                            <ul class="submenu">
                                                <li><a href="/index.php/dashboard">Requirements</a></li>
                                                <li><a href="/index.php/dashboard">Apply</a></li>
                                                <li><a href="add-/index.php/dashboard">Report an Issue</a></li>
                                                <li><a href="edit-/index.php/dashboard">Learn</a></li>
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
                                                <li><a href="/index.php/dashboard">Apply</a></li>
                                                <li><a href="/index.php/dashboard">Learn</a></li>
                                            </ul>
                                        </li>
                                    </ul>

                                </li>
                                <li class="has-submenu">
                                    <a href="">Help<i class="fas fa-chevron-down"></i></a>
                                    <ul class="submenu">
                                        <li><a href="/index.php/dashboard">About</a></li>
                                        <li><a href="/index.php/help/contact">Contact Support</a></li>
                                        <li><a href="/index.php/help/contact">Report an Issue</a></li>
                                    </ul>
                                </li>

                                <li class="login-link">
                                    <a href="/index.php/login">Login / Signup</a>
                                </li>
                            </ul>
                        </div>
                        <ul class="nav header-navbar-rht">
                            <li class="nav-item">
                                <a class="nav-link header-login-two" href="/index.php/login">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link header-login" href="/index.php/apply">Sign up</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>
        <!-- /Header -->

        <!-- Home Banner -->
        <?php echo $sf_content ?>
        <!-- /Home Banner -->

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
                                    <img src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg"
                                        alt="Uasin Gishu County">
                                </div>
                                <div class="footer-about-content">
                                    <p class="footer-sub-text">Uasin Gishu County Physical Planning portal - Access
                                        County services
                                        now from the comfort of your desk!</p>

                                </div>
                                <div class="footer-three-contact">
                                    <p><span>info@info@uasingishu.go.ke</span></p>
                                    <p>
                                        <span tel="05320160000">05320160000</span>
                                        <br />
                                        <span tel="05320130148">05320130148</span>
                                        <br />
                                        <span tel="+254710646464">+254710 64 64 64</span>
                                        <br />
                                    </p>
                                </div>
                            </div>
                            <!-- /Footer Widget -->

                        </div>
                        <div class="col-lg-2 col-md-6">

                            <!-- Footer Widget -->
                            <div class="footer-widget footer-menu">
                                <h2 class="footer-title">For Developers</h2>
                                <ul>
                                    <li><a href="/index.php/login">Construction Permits</a></li>
                                    <li><a href="/index.php/login">Planning</a></li>
                                </ul>
                            </div>
                            <!-- /Footer Widget -->

                        </div>
                        <div class="col-lg-2 col-md-6">

                            <!-- Footer Widget -->
                            <div class="footer-widget footer-menu">
                                <h2 class="footer-title">For Businesses</h2>
                                <ul>
                                    <li><a href="/index.php/login">Outdoor Advertising</a></li>
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
                                    <p class="mb-0">&copy; <?php echo date("Y") ?> Uasin Gishu County Physical Planning.
                                        All
                                        rights
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