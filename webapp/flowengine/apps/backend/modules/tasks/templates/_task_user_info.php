<?php
/**
 * _userpanel partial.
 *
 * Display user information
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="media media-pro">
    <div class="media-left">
      <?php
      $user = $application->getSfGuardUser();

      $user_profile = $user->getSfGuardUserProfile();

      if(sfConfig::get('app_sso'))
      {
          if($user_profile->getRegisteras() == 1)
          {
              $account_type = "citizen";
          }
          elseif($user_profile->getRegisteras() == 3)
          {
              $account_type = "alien";
          }
          elseif($user_profile->getRegisteras() == 4)
          {
              $account_type = "visitor";
          }

          ?>
          <a href="#">
          <img src="https://account.ecitizen.go.ke/profile-picture/<?php echo $user->getUsername(); ?>?t=<?php echo $account_type; ?>" class="thumbnail" alt="" />
          </a>
          <?php
      }
      else{
		  if(strlen($user_profile->getProfilePic())){
		?>
          <a href="<?php echo url_for('/plan/frusers/show/id/'.$user_profile->getUserId()); ?>">
          <img src="/assets_frontend/images/<?php echo $user_profile->getProfilePic(); ?>" class="thumbnail" alt="Profile pic" />
          </a>
		<?php
		  }else{
		?>
          <a href="<?php echo url_for('/plan/frusers/show/id/'.$user_profile->getUserId()); ?>">
          <img src="/assets_frontend/images/avatar.png" class="thumbnail" alt="Profile pic" />
          </a>
		<?php
		  }
      }
      ?>
    </div>
    <div class="media-body">
        <!--User Details-->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">
              <?php echo __("User Details"); ?>
              <a href="/plan/applications/transfer/id/<?php echo $application->getId(); ?>" class="btn btn-primary btn-sm pull-right" style="margin-top: -8px; color: #FFF;"><?php echo __("Transfer Application"); ?></a>
            </h3>
          </div>
          <div class="panel-body padding-0">
                <table class="table table-vertical m-b-0">
                  <thead>
                  <tr>
                    <th class="hidden-sm"><?php echo __("Name"); ?></th>
                    <th class="hidden-sm"><?php echo __("Usename"); ?></th>
                    <th class="hidden-sm"><?php echo __("Mobile Number"); ?></th>
                    <th class="hidden-sm"><?php echo __("Email"); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <?php if($sf_user->mfHasCredential("access_members")){ ?>
                    <td><a href="/plan/frusers/show/id/<?php echo $user_profile->getUserId(); ?>"><?php echo $user_profile->getFullname(); ?></a></td>
                    <?php }else{ ?>
                    <td><?php echo $user_profile->getFullname(); ?></td>
                    <?php } ?>
                    <td><?php echo $user->getUsername(); ?></td>
                    <td><?php echo $user_profile->getMobile(); ?></td>
                    <td><?php echo $user_profile->getEmail(); ?></td>
                  </tr>
                </tbody>
              </table>
          </div>
        </div>
        <!--End User Details-->
    </div>
</div>