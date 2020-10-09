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
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$bodies[$_GET['err']]);
		UtilComponent::printNotice(@$titles[$_GET['err']], $bodies_text);
	}
	UtilComponent::printNotice(__('infoFileListTitle', true), __('infoFileListDesc', true));
	?>
	<div class="b10">
	<iframe  width="732" height="550" frameborder="0"
		src="app/web/third-party/filemanager/dialog.php?type=0&fldr=">
	</iframe>	
	</div>

	<?php
}
?>