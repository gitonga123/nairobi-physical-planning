<?php

/**
 * _javascripts.php template.
 *
 * Displays Javascripts
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>





<!-- End Document
================================================== -->

<!-- Include All JS -->
<script src="/theme/js/jquery.js"></script>
<script src="/theme/js/jquery-ui.js"></script>
<script src="/theme/js/bootstrap.min.js"></script>
<script src="/theme/js/jquery.appear.js"></script>
<script src="/theme/js/owl.carousel.min.js"></script>
<script src="/theme/js/slick.min.js"></script>
<script src="/theme/js/jquery.shuffle.min.js"></script>
<script src="/theme/js/jquery.magnific-popup.min.js"></script>

<script src="/theme/js/modernizr.custom.js"></script>
<script src="/theme/js/dlmenu.js"></script>
<script src="/theme/js/jquery.easing.1.3.js"></script>

<script src="/theme/js/jquery.themepunch.tools.min.js"></script>
<script src="/theme/js/jquery.themepunch.revolution.min.js"></script>

<script src="/theme/js/extensions/revolution.extension.actions.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.carousel.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.kenburn.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.layeranimation.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.migration.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.navigation.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.parallax.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.slideanims.min.js"></script>
<script src="/theme/js/extensions/revolution.extension.video.min.js"></script>

<script src="/theme/js/theme.js"></script>
<!-- Include All JS -->

<?php
if (sfConfig::get('app_google_analytics_id')) {
?>
  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-76G9WVF63L"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', '<?php echo sfConfig::get('app_google_analytics_id'); ?>');
  </script>
<?php
}
?>


<?php
//Only display livechat if it is enabled
if (sfConfig::get('app_enable_livechat')) {
?>
  <!-- Start of LiveChat (www.livechatinc.com) code -->
  <script type="text/javascript">
    window.__lc = window.__lc || {};
    window.__lc.license = 7766711;
    (function() {
      var lc = document.createElement('script');
      lc.type = 'text/javascript';
      lc.async = true;
      lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(lc, s);
    })();
  </script>
  <!-- End of LiveChat code -->
<?php
}
?>

<!--Start of Tawk.to Script-->
<script type="text/javascript">
  /* var Tawk_API = Tawk_API || {},
    Tawk_LoadStart = new Date();
  (function() {
    var s1 = document.createElement("script"),
      s0 = document.getElementsByTagName("script")[0];
    s1.async = true;
    s1.src = 'https://embed.tawk.to/64e7723d94cf5d49dc6c4c11/1h8k1ch3o';
    s1.charset = 'UTF-8';
    s1.setAttribute('crossorigin', '*');
    s0.parentNode.insertBefore(s1, s0);
  })(); */
</script>
<!--End of Tawk.to Script-->