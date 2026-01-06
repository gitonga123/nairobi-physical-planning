<!-- Form -->
<form method="post" action="<?php echo url_for('/backend.php/login') ?>">
    <?php if ($form->isCSRFProtected()): ?>
        <?php echo $form['_csrf_token']->render(); ?>
    <?php endif; ?>
    <?php echo $form->renderGlobalErrors() ?>

    <?php if ($form->isCSRFProtected()): ?>
        <?php echo $form['_csrf_token']->render(); ?>
    <?php endif; ?>
    <?php echo $form->renderGlobalErrors() ?>

    <?php if ($loginError): ?>
        <p class="mt5 mb20" style="color: #F00;">
            <?php echo ('The username or password you entered is incorrect.'); ?>
        </p>
    <?php endif; ?>
    <div class="form-group">
        <input id="login_username" name="login[username]" class="form-control" type="text" placeholder="Email">
        <font color="#FF0000"><?php echo $form['username']->renderError() ?></font>
    </div>
    <div class="form-group">
        <input id="login_password" name="login[password]" class="form-control" type="password" placeholder="Password">
        <font color="#FF0000"><?php echo $form['password']->renderError() ?></font>
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-block w-100" type="submit">Login</button>
    </div>

    <div class="text-center forgotpass m-1"><a class="text-primary" href="/backend.php/login/forgot">Forgot
            Password?</a></div>


</form>
<!-- /Form -->