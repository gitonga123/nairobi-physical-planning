<script src="https://cdn.jsdelivr.net/npm/iframe-resizer@4.3.6/js/iframeResizer.min.js"></script>

<iframe src="<?php echo $iframe; ?>" frameborder="0" id="revenue_report_id" frameborder="0" width="100%" height="2300px" allowtransparency></iframe>

<script>
    iFrameResize({
        log: false
    }, '#revenue_report_id')
</script>