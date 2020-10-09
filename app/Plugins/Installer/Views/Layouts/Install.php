<?php 
use Core\Framework\Objects;

?>
<!doctype html>
<html>
	<head>
		<title>Install Wizard</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.$css['path'].htmlspecialchars($css['file']).'" />';
		}
		
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.$js['path'].htmlspecialchars($js['file']).'"></script>';
		}
		?>
	</head>
	<body>
		<div id="container">
    		<div id="header">
				<a href="http://www.phabbers.com/" id="logo" target="_blank"><img src="<?php echo Objects::getConstant('Installer', 'PLUGIN_IMG_PATH'); ?>install-logo.png" alt="Install Wizard" /></a>
			</div>
			<div id="middle">
			<?php require $content_tpl; ?>
			</div>
		</div>
	</body>
</html>