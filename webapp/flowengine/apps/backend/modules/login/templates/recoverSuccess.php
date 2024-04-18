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
         <p class="account-subtitle"> Enter the temporary password we sent you in the email.</p>
         <form method="post" action="/backend.php/login/recover/code/<?php echo $token; ?>" autocomplete="off">
            <?php if ($form->isCSRFProtected()) : ?>
                <?php echo $form['_csrf_token']->render(); ?>
            <?php endif; ?>
            <?php echo $form->renderGlobalErrors() ?>
            <div style="color: #F80000;"><?php echo $recoveryerror; ?></div>
            <div class="form-group">
                <input type="password" id="recovery_password" name="recovery[password]" required="required" placeholder="Enter Password" class="form-control" />
            </div>
            <div class="form-group mb-1">
                <button class="btn btn-success btn-block active" type="submit">Recover Account</button>
            </div>
         </form>
         <?php
          }
         ?>
        </div>
    </div><!-- lockedpanel -->

</section>