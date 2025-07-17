<script src="/assets_backend/js/hello-sign.min.js"></script>
<script>
    <?php if(in_array('sign_url', array_keys($_GET)) and $sign_url = $_GET['sign_url']): ?>

    window.addEventListener('load', function () {
        <?php
        $sign_url = base64_decode($sign_url);
        $client_id = base64_decode($_GET['client_id']); ?>
        const client = new HelloSign({
            clientId: '<?php echo $client_id ?>',
            skipDomainVerification: true,
            allowCancel : false
        })

        let url = "<?php echo($sign_url) ?>";
        console.log(url);
        client.open(url);

        client.on('sign', (data) => {
            console.log(data);
        });

        client.on('cancel', (data) => {
            window.location.href = "/plan/dashboard"
            console.log(data);
        });

        client.on('finish', (data) => {
            console.log(data);

            $.ajax({
                type: 'POST',
                url: '/plan/signing/marksessionascomplete',
                data: {'session_id': '<?php echo $_GET['SESS']?>'},
                success: function (response) {
                    window.location.href = "/plan/dashboard"
                }
            });
        });


        client.on('error', (data) => {
            alert('an error occurred');
        });
    });
    <?php endif; ?>
</script>
