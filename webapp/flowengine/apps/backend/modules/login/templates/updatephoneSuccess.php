<?php
  use_helper('I18N');
?>


                <form method="post" action="/plan/login/updatephone" autocomplete="off">
                    <p class="account-subtitle"><?php echo __('Verify your Phone Number'); ?></p>
                        <?php
                            if($twofactor_error)
                            {
                            ?>
                            <p class="mt5 mb20" style="color: #F00;">
                            <?php echo $twofactor_error; ?>
                            </p>
                            <?php
                            }
                            else
                            {
                            ?>
                            <p class="mt5 mb20"><?php echo __('Please provide a phone number for account verification.'); ?></p>
                            <?php
                            }
                    ?>
                    <div class="form-group">
                    <input type="text" id="phone" placeholder="0700 000 000" class="form-control uname" size="60" name="phone">
                </div>
                <div class="form-group">
                    <button class="btn btn-success btn-block active" type="submit" <?php echo $disabled; ?>><?php echo __('Submit'); ?></button>
                </div>
                </form>