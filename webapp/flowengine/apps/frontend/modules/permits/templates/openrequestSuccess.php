<?php
	use_helper('I18N');
?>
<div class="breadcrumb-box">
<div class="container">
<ul class="breadcrumb">
<li>
<a href="/plan/"><?php echo __('Home'); ?></a>
<span class="divider">/</span>
</li>
<li class="active"><?php echo $_GET['reference']; ?></li>
</ul>
</div>
</div>
<br>
<section class="container page-content" >
<?php 
    echo html_entity_decode($template);
?>
<div class="white-space"></div>
</section>