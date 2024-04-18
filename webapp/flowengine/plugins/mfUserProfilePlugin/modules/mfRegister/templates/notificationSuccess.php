<?php
  use_helper('I18N');
?>


<section id="headline">
    <div class="container">
      <h3><?php echo __('Registration Successful'); ?></h3>
    </div>
  </section>
<div class="signinpanel">



<div class="row">
<div class="container">
<div class="twelve columns offset-by-three">
    <?php
    if($_GET['done'] == 2)
    {
    ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title"><?php echo __('Profile Updated'); ?></h4>
                <p><?php echo __('You have updated your profile successfully'); ?></p>
            </div><!-- panel-heading -->
            <div class="panel-body">
                <div class="alert alert-info">
                    <strong><?php echo __('Profile Updated'); ?>!</strong> <?php echo __('Thank you for updating your user profile. <a href="/index.php/dashboard">Click here to go to your dashboard.</a>'); ?><br>
                </div>
            </div>
        </div><!-- panel -->
    <?php
    }
    else
    {
    ?>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"><?php echo __('Check Your Email'); ?></h4>
              <p><?php echo __('Your registration is successful'); ?></p>
            </div><!-- panel-heading -->
            <div class="panel-body">
              <div class="alert alert-info">
                <strong><?php echo __('Check your Email'); ?>!</strong> <?php echo __('Thank you for Registering. An email has been sent to your account. Go to your email to verify your account. <a href="/index.php/dashboard">Click here to go to your dashboard.</a>'); ?><br>
              </div>
            </div>
          </div><!-- panel -->
    <?php
    }
    ?>
</div>
</div>
</div>
</div>
