<!doctype html>
<html>
	<head>
		<title><?php echo empty($controller->title)?'Webspace CMS' : $controller->title;?></title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL :  $controller->baseUrl()).$css['path'].htmlspecialchars($css['file']).'" />';
		}
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $controller->baseUrl()?>app/web/css/font-awesome/css/font-awesome.min.css">
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
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&action=Index" id="logo" style="background-image: url(<?php echo $controller->baseUrl() . IMG_PATH; ?>backend/logo.png)">
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
						<br/>
						<a class="button" href="<?php echo $controller->baseUrl();?>"><span style="color: #1A88C7 !important;"><i class="fa fa-external-link" aria-hidden="true"></i> Xem Website</span></a>
					</div>

				</div>
			</div>
			
			<div id="middle">
				<div id="leftmenu">
					<?php require VIEWS_PATH . 'Layouts/elements/leftmenu.php'; ?>
				</div>
				<div id="right">
					<div class="content-top"></div>
					<div class="content-middle" id="content">
					<?php require $content_tpl; ?>
					</div>
					<div class="content-bottom"></div>
				</div> <!-- content -->
				<div class="clear_both"></div>
			</div> <!-- middle -->
		
		</div> <!-- container -->
		<div id="footer-wrap">
			<div id="footer">
			   	<p><?php echo @$tpl['cms_setting']['copyright'];?></p>
	        </div>
        </div>
	</body>
</html>