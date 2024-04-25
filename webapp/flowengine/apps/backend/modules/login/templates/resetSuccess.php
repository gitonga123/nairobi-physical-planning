<section>

    <div class="lockedpanel">
        <div class="loginuser">
        <?php
          if($token == "")
          {
            ?>
            Cannot recover your account
            <?php
          }
          else
          {
        ?>
        <p class="account-subtitle mb-2">Enter and confirm your new password in the form below.</p>
         <form method="post" action="/backend.php/login/reset/code/<?php echo $token; ?>" autocomplete="off">
            <?php if ($form->isCSRFProtected()) : ?>
                <?php echo $form['_csrf_token']->render(); ?>
            <?php endif; ?>
            <?php echo $form->renderGlobalErrors() ?>
            <div style="color: #F80000;"><?php echo $recoveryerror; ?></div>
            <div class="form-group">
                <input type="password" id="reset_password1" name="reset[password]" required="required" placeholder="Enter New Password" class="form-control" />
            </div>
            <div class="form-group">
                <input type="password" id="reset_password2" name="reset[password2]" required="required" placeholder="Confirm Password" class="form-control" />
            </div>
            <div class="form-group mb-0">
                <button class="btn btn-success btn-block active" type="submit">Change Password</button>
            </div>
         </form>
         <?php
          }
         ?>
        </div>
    </div><!-- lockedpanel -->

</section>