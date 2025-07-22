<?php
  use_helper('I18N');

  $site_settings = Functions::site_settings();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="<?php echo __('Access County of Government Uasin Gishu Services Online'); ?>">
    <meta name="author" content="Malipo Circles">
    <title><?php echo $site_settings->getOrganisationName(); ?></title>

    <link href="/assets_backend/css/style.default.css" rel="stylesheet">
    <link href="/assets_backend/css/bootstrap-override.css" rel="stylesheet">
    <link href="/assets_backend/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets_backend/css/ectzn/custom.css" rel="stylesheet">
    <?php include_stylesheets() ?>

    <script src="/assets_backend/js/jquery-1.10.2.min.js"></script>
    <script src="/assets_backend/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="/assets_backend/js/bootstrap.min.js"></script>
    <script src="/assets_backend/js/modernizr.min.js"></script>

    <script src="/assets_backend/js/bootstrap-wizard.min.js"></script>
    <script src="/assets_backend/js/jquery.validate.min.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="/assets_backend/js/html5shiv.js"></script>
    <script src="/assets_backend/js/respond.min.js"></script>
    <![endif]-->
    <?php include_javascripts() ?>
  </head>
  <body class="signin">

    <?php echo $sf_content ?>

    <?php 
    //Only display livechat if it is enabled
    if(sfConfig::get('app_enable_livechat'))
    {
    ?>
        <!-- Start of LiveChat (www.livechatinc.com) code -->
        <script type="text/javascript">
        window.__lc = window.__lc || {};
        window.__lc.license = 7766711;
        (function() {
        var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
        lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
        })();
        </script>
        <!-- End of LiveChat code -->
    <?php 
    }
    ?>
  </body>
</html>
