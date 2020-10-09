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
	UtilComponent::printNotice(@$titles['PBU01'], @$bodies['PBU01']);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=Backup&amp;action=Index" method="post" class="form form">
		<input type="hidden" name="backup" value="1" />
		<fieldset class="fieldset white overflow">
			<legend><?php __('plugin_backup_menu_backup'); ?></legend>
			<div class="float_left w200">
				<p>
					<label class="title"><?php __('plugin_backup_database'); ?></label>
					<span class="left">
						<input type="checkbox" name="db" value="1" checked="checked" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_backup_files'); ?></label>
					<span class="left">
						<input type="checkbox" name="files" value="1" checked="checked" />
					</span>
				</p>
			</div>
			<p class="float_left">
				<input type="submit" value="<?php __('plugin_backup_btn_backup'); ?>" class="button" />
			</p>
		</fieldset>
	</form>
	
	<div id="grid"></div>
	<?php
}
?>