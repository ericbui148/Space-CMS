<!doctype html>
<html>
	<head>
		<title><?php __('plugin_invoice_menu_invoices'); ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9" />
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.$css['path'].$css['file'].'" />';
		}
		?>
	</head>
	<body>
		<div id="container">
		<?php echo $tpl['template']; ?>
		</div>
		
		<script type="text/javascript">
		window.onload = function () {
			window.print();
		};
		</script>
	</body>
</html>