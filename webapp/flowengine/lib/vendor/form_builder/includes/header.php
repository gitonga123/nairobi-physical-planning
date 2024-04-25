<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>2.5 Form Builder Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="index, nofollow" />
<link rel="stylesheet" type="text/css" href="/form_builder/css/main.css" media="screen" />

<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="/form_builder/css/ie7.css" media="screen" />
<![endif]-->

<!--[if IE 8]>
	<link rel="stylesheet" type="text/css" href="/form_builder/css/ie8.css" media="screen" />
<![endif]-->

<!--[if IE 9]>
	<link rel="stylesheet" type="text/css" href="/form_builder/css/ie9.css" media="screen" />
<![endif]-->

<link href="/form_builder/css/theme.css" rel="stylesheet" type="text/css" />
<?php
	if(!empty($mf_settings['admin_theme'])){
		echo '<link href="/form_builder/css/themes/theme_'.$mf_settings['admin_theme'].'.css" rel="stylesheet" type="text/css" />';
	}
?>
<link href="/form_builder/css/bb_buttons.css" rel="stylesheet" type="text/css" />
<?php if(!empty($header_data)){ echo $header_data; } ?>
<link href="/form_builder/css/override.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="bg" class="contentpanel">

<div id="container">

	<div id="header">
	<?php
		if(!empty($mf_settings['admin_image_url'])){
			$machform_logo_main = htmlentities($mf_settings['admin_image_url']);
		}else{
			if(!empty($mf_settings['admin_theme'])){
				$machform_logo_main = '/form_builder/images/machform_logo_'.$mf_settings['admin_theme'].'.png';
			}else{
				$machform_logo_main = '/form_builder/images/machform_logo.png';
			}

			$logo_width_attr = 'width="158"';
		}
	?>
		<div class="clear"></div>

	</div><!-- /#header -->
	<div id="main">

		<div id="navigation">

			<div class="clear"></div>


		</div><!-- /#navigation -->
