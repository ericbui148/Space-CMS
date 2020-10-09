<?php
use Core\Framework\Components\SanitizeComponent;
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
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	if (isset($_GET['key']))
	{
		?>
		<fieldset class="fieldset white">
			<legend><?php __('plugin_locale_import'); ?></legend>
			<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=Locale&amp;action=Import" method="post" class="form form">
				<input type="hidden" name="import" value="1" />
				<input type="hidden" name="key" value="<?php echo SanitizeComponent::html($_GET['key']); ?>" />
				<?php
				$STORE = @$_SESSION[$_GET['key']];
				if (isset($tpl['locale_arr']) && !empty($tpl['locale_arr']))
				{
					foreach ($tpl['locale_arr'] as $locale)
					{
						?><p><label><input type="checkbox" name="locale[]" value="<?php echo $locale['id']; ?>" checked="checked"<?php echo !is_array(@$STORE['locales']) || !in_array($locale['id'], $STORE['locales']) ? ' disabled="disabled"' : NULL; ?> /> <?php echo SanitizeComponent::html($locale['title']); ?></label></p><?php
					}
				}
				?>
				<p>
					<input type="submit" value="<?php __('plugin_locale_import'); ?>" class="button" />
				</p>
			</form>
		</fieldset>
		<?php
	}
}
?>