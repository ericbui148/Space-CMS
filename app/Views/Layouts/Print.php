<!doctype html>
<html>
	<head>
		<title>WEBSITE BUILDER | Print</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link type="text/css" rel="stylesheet" href="<?php echo $controller->baseUrl() . CSS_PATH; ?>print.css" media="screen, print" />
	</head>
	<body>
		<div id="container">
			<?php require $content_tpl; ?>
		</div>
	</body>
</html>

<script type="text/javascript">
if (window.print) {
	window.print();
}
</script>