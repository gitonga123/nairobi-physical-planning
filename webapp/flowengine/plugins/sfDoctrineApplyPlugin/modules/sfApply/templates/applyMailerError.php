<?php use_helper('I18N') ?>
<section class="container">
<?php echo __(<<<EOM
<p>
An error took place during the email delivery process. Please try
again later.
</p>
EOM
) ?>
<?php include_partial('sfApply/continue') ?>
</section>
