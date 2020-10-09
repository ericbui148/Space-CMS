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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	<?php UtilComponent::printNotice(__('infoThemeTitle', true), __('infoThemeDesc', true), false, false); ?>

	<div class="theme-holder loader-outer">
		<?php include VIEWS_PATH . 'AdminOptions/elements/theme.php'; ?>
	</div>
	<div class="clear_both"></div>
	<?php
}
?>