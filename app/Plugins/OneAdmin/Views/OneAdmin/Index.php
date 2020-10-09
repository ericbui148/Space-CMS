<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			UtilComponent::printNotice(NULL, $status[2]);
			break;
	}
} else {
	$plugin_menu = VIEWS_PATH . sprintf('Layouts/elements/menu_%s.php', $controller->getConst('PLUGIN_NAME'));
	if (is_file($plugin_menu))
	{
		include $plugin_menu;
	}
	include dirname(__FILE__) . '/elements/menu.php';
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	UtilComponent::printNotice(@$titles['POA01'], @$bodies['POA01']);
	?>
	<div class="b10">
		<a href="#" class="button btn-add"><?php __('plugin_one_admin_btn_add'); ?></a>
	</div>

	<div id="grid"></div>
	<?php
}
?>