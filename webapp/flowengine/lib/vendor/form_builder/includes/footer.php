<div class="clear"></div>

</div><!-- /#main -->


</div><!-- /#container -->

</div><!-- /#bg -->

<?php
if ($disable_jquery_loading !== true) {
	echo '<script type="text/javascript" src="/form_builder/js/jquery.min.js"></script>';
}
?>

<?php if (!empty($footer_data)) {
	echo $footer_data;
} ?>
</body>

</html>