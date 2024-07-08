<?php

/**
 * _header.php template.
 *
 * Displays the header on the layout
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

$site_settings = Functions::site_settings();
?>
<div class="leftpanel">
  <div class="logopanel">
    <?php
    // $agency_manager = new AgencyManager();
    // $logo = $agency_manager->getLogo($sf_user->getAttribute('userid'));
    ?>
    <h1> <img src="/asset_mentor/admin/assets/img/logo2.png" alt="" /></h1>
  </div><!-- logopanel -->
  <?php
  //Displays the sidemenu
  include_component('dashboard', 'sidemenu');
  ?>

  <div class="mainpanel">

    <div class="headerbar">

      <a class="menutoggle"><i class="fa fa-bars"></i></a>

      <form class="searchform" action="/backend.php/applications/search" method="post">
        <input type="text" name="applicationid" class="form-control" placeholder="<?php echo __('Enter Application Number to Search'); ?>" onfocus="this.placeholder = ''" onblur="this.placeholder = '<?php echo __('Search'); ?>'" />
      </form>

      <form class="searchform" action="/backend.php/applications/search" method="post">
        <a href="/backend.php/applications/search?search=adv" class="btn btn-default btn-advanced m-t-8 pull-left"><?php echo __('More Filters'); ?></a>
      </form>
      <div class="header-right">
        <ul class="headermenu">
          <?php
          if (sizeof($languages) > 1) {
          ?>
            <li>
              <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <span class="fa fa-language"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                  <?php
                  foreach ($languages as $locale) {
                    $selected = "";
                    if ($locale->getLocaleIdentifier() == $sf_user->getCulture()) {
                      $selected = "active";
                    }
                  ?>
                    <li class="<?php echo $selected; ?>"><a href="/backend.php/languages/setlocale/code/<?php echo $locale->getLocaleIdentifier(); ?>"><i class="glyphicon glyphicon-cog"></i> <?php echo $locale->getLocalTitle(); ?></a></li>
                  <?php
                  }
                  ?>
                </ul>
              </div>
            </li>
          <?php
          }
          ?>
          <li>
            <div class="btn-group">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <?php
                if ($logged_reviewer->getProfilePic()) {
                ?>
                  <img src="<?php echo $site_settings->getUploadDirWeb(); ?><?php echo $logged_reviewer->getProfilePic(); ?>" alt="" />
                <?php
                } else {
                ?>
                  <img src="/assets_backend/images/avatar.jpeg" alt="" />
                <?php
                }
                ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                <li><a href="/backend.php/dashboard/profile"><i class="glyphicon glyphicon-cog"></i> <?php echo $logged_reviewer->getStrfirstname() . " " . $logged_reviewer->getStrlastname(); ?></a></li>
                <li><a href="/backend.php/login/logout"><i class="glyphicon glyphicon-log-out"></i> <?php echo __('Log Out'); ?></a></li>
              </ul>
            </div>
          </li>
        </ul>
      </div><!-- header-right -->

    </div><!-- headerbar -->