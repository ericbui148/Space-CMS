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
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	UtilComponent::printNotice(__('plugin_locale_index_title', true), __('plugin_locale_index_body', true));
	?>
	<div class="b10">
		<a href="#" class="button btn-add"><?php __('plugin_locale_add_lang'); ?></a>
	</div>
	
	<div id="grid"></div>
	<?php
	$languages = array();
	foreach ($tpl['language_arr'] as $item)
	{
		$languages[] = '{value: "'.$item['iso'].'", label: "'.$item['title'].'"}';
	}
	?>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.languages = [];
	<?php
	if (count($languages) > 0)
	{
		printf('Grid.languages.push('.join(",", $languages).');');
	}
	?>
	var myLabel = myLabel || {};
	myLabel.title = "<?php __('plugin_locale_lbl_title'); ?>";
	myLabel.flag = "<?php __('plugin_locale_lbl_flag'); ?>";
	myLabel.is_default = "<?php __('plugin_locale_lbl_is_default'); ?>";
	myLabel.order = "<?php __('plugin_locale_lbl_order'); ?>";
	</script>
	<?php
}
?>