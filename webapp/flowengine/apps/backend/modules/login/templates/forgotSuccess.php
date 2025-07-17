<section>

    <div class="lockedpanel mt-2">
        <div class="loginuser">
            <p class="account-subtitle">Enter your email address below. We will send an email with instruction on how to reset your password</p>
        </div>
        <form method="post" action="/plan/login/forgot" autocomplete="off">
            <?php if ($form->isCSRFProtected()) : ?>
                <?php echo $form['_csrf_token']->render(); ?>
            <?php endif; ?>
            <?php echo $form->renderGlobalErrors() ?>
            
            <div style="color: #F80000; mb-1"><?php echo $forgoterror; ?></div>
            <div class="form-group">
                <input type="text" id="forgot_email" name="forgot[email]" required="required" placeholder="Enter Your Email" class="form-control uname" value="<?php echo $email;?>"/>
            </div>
            <div class="form-group mb-0">
                <button class="btn btn-success btn-block active" type="submit">Send Recovery Code</button>
            </div>
        </form>

        <div class="text-center dont-have forgotpass m-1 mt-1"><a class="link text-primary" href="/plan/login">Remember Password?</a></div>
    </div><!-- lockedpanel -->

</section>  