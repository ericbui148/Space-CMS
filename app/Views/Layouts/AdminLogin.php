
<!doctype html>
<html>
	<head>
		<title>Admin - Đăng Nhập</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : $controller->baseUrl()).$css['path'].$css['file'].'" />';
		}
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $controller->baseUrl()?>app/web/css/font-awesome/css/font-awesome.min.css">
		<?php
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : $controller->baseUrl()).$js['path'].$js['file'].'"></script>';
		}
		?>
	</head>
	<body>
		<div id="container">
			<div id="header">
				<div class="admin_logo_box">
				<a  href="/" id="logo" style="background-image: url(<?php echo $controller->baseUrl() . IMG_PATH; ?>backend/logo.png)">
					<span><?php echo @$tpl['cms_setting']['logo_text'];?></span>
				</a>
				</div>
			</div>
			<div id="middle">
				<div id="login-content">
				<?php require $content_tpl; ?>
				</div>
			</div> <!-- middle -->
		</div> <!-- container -->
		<div id="footer-wrap">
			<div id="footer">
			   	<p><?php echo @$tpl['cms_setting']['copyright'];?></p>
	        </div>
        </div>
	</body>
</html>