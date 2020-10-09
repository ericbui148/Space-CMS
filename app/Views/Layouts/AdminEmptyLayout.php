<!doctype html>
<html>
	<head>
		<title>App7 - Hệ thống website builder</title>		
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : CDN_DOMAIN.'/').$css['path'].htmlspecialchars($css['file']).'" />';
		}
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo CDN_DOMAIN;?>/app/web/css/font-awesome/css/font-awesome.min.css">
		<?php
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : $controller->baseUrl()).$js['path'].htmlspecialchars($js['file']).'"></script>';
		}
		?>
		
		<!--[if gte IE 9]>
  		<style type="text/css">.gradient {filter: none}</style>
		<![endif]-->
	</head>
	<body style="background: #FFFFFF 0 0 repeat-x;">
        <?php require $content_tpl; ?>
	</body>
</html>