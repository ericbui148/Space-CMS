<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;

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
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	UtilComponent::printNotice(__('plugin_locale_ie_title', true), __('plugin_locale_ie_body', true));
	?>
	
	<fieldset class="fieldset white">
		<legend><?php __('plugin_locale_import'); ?></legend>
		<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=Locale&amp;action=ImportConfirm" method="post" class="form form" enctype="multipart/form-data">
			<input type="hidden" name="import" value="1" />
			<p>
				<label class="title"><?php __('plugin_locale_separator'); ?></label>
				<select name="separator" class="form-field">
				<?php
				foreach (__('plugin_locale_separators', true) as $k => $v)
				{
					?><option value="<?php echo $k; ?>"><?php echo SanitizeComponent::html($v); ?></option><?php
				}
				?>
				</select>
			</p>
			<p>
				<label class="title"><?php __('plugin_locale_browse'); ?></label>
				<input type="file" name="file" class="form-field" />
			</p>
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('plugin_locale_import'); ?>" class="button" />
			</p>
		</form>
	</fieldset>
	
	<fieldset class="fieldset white">
		<legend><?php __('plugin_locale_export'); ?></legend>
		<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=Locale&amp;action=Export" method="post" class="form form">
			<input type="hidden" name="export" value="1" />
			<p>
				<label class="title"><?php __('plugin_locale_separator'); ?></label>
				<select name="separator" class="form-field">
				<?php
				foreach (__('plugin_locale_separators', true) as $k => $v)
				{
					?><option value="<?php echo $k; ?>"><?php echo SanitizeComponent::html($v); ?></option><?php
				}
				?>
				</select>
			</p>
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('plugin_locale_export'); ?>" class="button" />
			</p>
		</form>
	</fieldset>
	<?php
}
?>