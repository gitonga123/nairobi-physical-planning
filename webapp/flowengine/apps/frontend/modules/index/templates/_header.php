<?php
/**
 * _header.php template.
 *
 * Displays Header
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

$site_settings = Functions::site_settings();
?>
<!-- Preloading -->
<div class="preloader text-center">
    <div class="la-ball-scale-multiple la-2x">
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
<!-- Preloading -->
<header class="header_02" id="fix_nav">
    <div class="header-container">
        <div class="row">
            <div class="col-xl-2 col-lg-2 col-md-12">
                <div class="logo_02">
                    <a href="/">
                        <?php if ($site_settings->getAdminImageUrl()): ?>
                            <img src="<?php echo '/' . $site_settings->getUploadDir() . '/' . $site_settings->getAdminImageUrl() ?>"
                                alt="">
                        <?php else: ?>
                            <img src="/assets_frontend_amkatek/images/award-logo/logo2.png" alt="Logo">
                        <?php endif; ?>
                    </a>
                </div>
                <div class="mobileMenuBar">
                    <a href="javascript: void(0);"><span>Menu</span><i class="fa fa-bars"></i></a>
                </div>
            </div>
            <?php include_component('index', 'menu') ?>
        </div>
    </div>
</header>
<section class="topbar_02">
    <div class="header-container">
        <div class="row">
            <div class="col-lg-6 col-md-7">
                <div class="topbar_left text-left">
                    <div class="topbar_element info_element">
                        <i class="fa fa-envelope"></i>
                        <h5>Email Address</h5>
                        <p><a
                                href="mailto:<?php echo $site_settings->getOrganisationEmail() ?>"><?php echo $site_settings->getOrganisationEmail() ?></a>
                        </p>
                    </div>
                    <div class="topbar_element info_element">
                        <i class="fa fa-phone"></i>
                        <h5>Phone Number</h5>
                        <p>+254 58 30005/30081</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>