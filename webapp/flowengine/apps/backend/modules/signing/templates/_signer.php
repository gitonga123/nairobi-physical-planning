<?php if (isset($signing_tasks)):
    $default_signing_agent = sfConfig::get('app_signing_agent'); ?>
    <div class="row alert alert-info">
        <div class="col-md-10">
                    <span style="font-size: 1.4em; font-weight: 100">
                    You have have <?php echo $signing_tasks ?> document(s) to sign
                    </span>
        </div>
        <div class="col-md-2" style="text-align: right">
            <?php if ($default_signing_agent == 'docusign'): ?>
                <a href="/backend.php/signing?permitaction=signdocument"
                   class="btn btn-primary">
                    <i class="fa fa-pencil"></i> Proceed to Sign
                </a>
            <?php elseif ($default_signing_agent == 'hellosign'): ?>
                <?php if (in_array('sign_url', array_keys($_GET))):
                    include_partial('signing/hello_sign_embed');
                endif; ?>

                <a href="/backend.php/signing/embeddedsigningrequest?permitaction=signdocument&redirect_to=<?php echo base64_encode("/backend.php/dashboard?") ?>"
                   class="btn btn-primary">
                    <i class="fa fa-pencil"></i> Proceed to Sign
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
