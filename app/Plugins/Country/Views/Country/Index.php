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
	UtilComponent::printNotice(@$titles['PCY12'], @$bodies['PCY12']);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	$statuses = __('plugin_country_statuses', true);
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<input type="hidden" name="controller" value="Country" />
		<input type="hidden" name="action" value="Create" />
		<input type="submit" class="button" value="<?php __('plugin_country_btn_add'); ?>" />
		<p>&nbsp;</p>
	</form>
	
	<div class="b10">
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('plugin_country_btn_search'); ?>" />
		</form>
		<div class="float_right t5">
			<a href="#" class="button btn-all"><?php __('plugin_country_btn_all'); ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="T"><?php echo $statuses['T']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="F"><?php echo $statuses['F']; ?></a>
		</div>
		<br class="clear_both" />
	</div>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.country = "<?php __('plugin_country_name'); ?>";
	myLabel.alpha_2 = "<?php __('plugin_country_alpha_2'); ?>";
	myLabel.alpha_3 = "<?php __('plugin_country_alpha_3'); ?>";
	myLabel.status = "<?php __('plugin_country_status'); ?>";
	myLabel.active = "<?php echo $statuses['T']; ?>";
	myLabel.inactive = "<?php echo $statuses['F']; ?>";
	myLabel.delete_confirmation = "<?php __('plugin_country_delete_confirmation'); ?>";
	myLabel.delete_selected = "<?php __('plugin_country_delete_selected'); ?>";
	</script>
	<?php
}
?>