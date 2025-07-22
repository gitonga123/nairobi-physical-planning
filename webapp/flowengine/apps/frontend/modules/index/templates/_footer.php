<?php
/**
 * _footer.php template.
 *
 * Displays Footer
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 $site_settings = Functions::site_settings();
?>
        <footer class="footer_02">
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-md-4 col-lg-4">
                        <aside class="widget">
                            <div class="about_widget_2">
                                <a href="<?php echo url_for('@dashboard') ?>">
                                <?php if($site_settings->getAdminImageUrl()): ?>
                                <img src="<?php echo '/'.$site_settings->getUploadDir().'/'.$site_settings->getAdminImageUrl() ?>" alt="Logo" >
                                <?php else: ?>
                                <img src="/assets_frontend_amkatek/images/award-logo/ug_logo.svg" alt="Logo" >
                                <?php endif; ?>
                                </a>
                                <p>
                                  <?php echo $site_settings->getOrganisationDescription() ?>
                                </p>
                               
                            </div>
                        </aside>
                    </div>
                    <div class="col-xl-4 col-md-4 col-lg-4">
                        <aside class="widget pdl58">
                            <h3 class="widget_title">Important Links<span>.</span></h3>
                            <ul>
                              <li><a href="<?php echo url_for('signon/login') ?>"><?php echo __('Log in'); ?></a></li>
                              <li><a href="<?php echo url_for('signon/register'); ?>"><?php echo __('Register'); ?></a></li>
                              <li><a href="<?php echo url_for('news/index') ?>"><?php echo __('News'); ?></a></li>
                              <li><a href="<?php echo url_for('help/faq'); ?>"><?php echo __('FAQs'); ?></a></li>
                              <li><a href="<?php echo url_for('help/contact'); ?>"><?php echo __('Contact Us'); ?></a></li>
                            </ul>
                        </aside>
                    </div>
                    <div class="col-xl-4 col-md-4 col-lg-4">
                        <aside class="widget pdl20">
                            <h3 class="widget_title">Services<span>.</span></h3>
                            <ul>
                                <li><a href="#building">Building permit/Development Approval</a></li>
                                <li><a href="#subdiv">Subdivision/Consolidation</a></li>
                                <li><a href="#exten">Extension of lease/use</a></li>
                                <li><a href="#inspec">Inspections/ Occupation permit</a></li>
                            </ul>
                        </aside>
                    </div>
                </div>
            </div>
        </footer>
        <section class="copyright_section">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="siteinfo">
                            Copyright By &COPY;<a href="https://https://info@uasingishu.go.ke"><?php echo $site_settings->getOrganisationName() ?></a> - <?php echo date("Y") ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>
        <a href="#" id="backtotop"><i class="fal fa-angle-double-up"></i></a>