<?php
  use_helper('I18N');
?>

<form method="post" action="/plan/login/twofactor" autocomplete="off">
  <p class="account-subtitle"><?php echo __('Enter verification code'); ?></p>
  <p class="mt5 mb20"><?php echo __('Please provide the verification code that has been sent to your phone'); ?></p>
  <div id="resend" style="display: none;" align='center'><a href='/plan/login/twofactor/resend/1'>Click here to resend the code</a></div>
  <div class="form-group">
    <input type="text" id="code" placeholder="XXXXXX" class="form-control uname" size="60" name="code">
  </div>
  <div class="form-group">
    <button class="btn btn-success btn-block active" type="submit"><?php echo __('Submit'); ?></button>
  </div>
</form>