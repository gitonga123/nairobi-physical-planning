<?php use_helper('I18N') ?>
<br />
<br />
<br />
<div class="panel">
    <legend><?php echo __('Lost Password Recovery') ?></legend>

    <div class="alert alert-error">
        That confirmation code is invalid
    </div>



    <?php echo __(
        <<<EOM
<p>
This may be because you have already confirmed your account. If so,
just click on the "Log In" button to log in.
</p>
<h4>
Other possible explanations:
</h4>

<ol>
<li>
If you copied and pasted the URL from
your confirmation email, please make sure you did so correctly and
completely.
</li>
<li>
 If you received this confirmation email a long time ago
and never confirmed your account, it is possible that your account has
been purged from the system. In that case, you should simply apply
for a new account.
</li>
</ol>
EOM
    ) ?>
    <?php include_partial('sfApply/apply') ?>






</div>