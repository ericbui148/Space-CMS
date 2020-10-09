<!doctype html>
<html>
	<head>
		<title>App7 - Hệ thống website builder</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/apple-icon-180x180.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/android-icon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="96x96" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/favicon-16x16.png">
		<link rel="manifest" href="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/manifest.json">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="<?php echo $controller->baseUrl()?>app/themes/food_cloud/web/img/favicon/ms-icon-144x144.png">
		<meta name="theme-color" content="#ffffff">   		
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : $controller->baseUrl()).$css['path'].htmlspecialchars($css['file']).'" />';
		}
		?>
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
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
	<body>
		<div id="container">
    		<div id="header">
				<div class="admin_logo_box">
    				<a  href="/" id="logo" style="background-image: url(<?php echo $controller->baseUrl() . IMG_PATH; ?>backend/logo.png)">
    					<span><?php echo @$tpl['cms_setting']['logo_text'];?></span>
    				</a>
				</div>
				<div class="top_profile">
					<div class="avatar">
						<?php if(!empty($_SESSION['admin_user']['avatar'])):?>
							<img src="<?php echo $_SESSION['admin_user']['avatar'];?>"/>
						<?php else:?>
							<img src="app/web/img/backend/avatar.jpg"/>
						<?php endif;?>
					</div>
					<div class="dropdown">
						<span><?php echo $_SESSION['admin_user']['name']?></span> &nbsp;<span class="fa fa-angle-down"></span>
						<ul>
							<li>&nbsp;<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Profile"><?php __('menuProfile'); ?></a></li>
							<li>&nbsp;<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Logout"><?php __('menuLogout'); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
			
			<div id="middle">
				<div class="content-top"></div>
				<div class="content-middle" id="content">
				<?php require $content_tpl; ?>
				</div>
				<div class="content-bottom"></div>
				<div class="clear_both"></div>
			</div> <!-- middle -->
		
		</div> <!-- container -->
		<div id="footer-wrap">
			<div id="footer">
			   	<p>Copyright &copy; <?php echo date("Y"); ?> by App7.VN</p>
	        </div>
        </div>
	</body>
</html>