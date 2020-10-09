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
	$u_statarr = __('u_statarr', true);
	UtilComponent::printNotice(__('infoClientsTitle', true), __('infoClientsDesc', true));
	?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminClients" />
			<input type="hidden" name="action" value="Create" />
			<input type="submit" class="button" value="<?php __('btnPlusAddClient'); ?>" />
		</form>
		<form action="" method="get" class="form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
		</form>
	</div>

	<div id="grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.jsDateFormat = "<?php echo UtilComponent::jsDateFormat($tpl['option_arr']['o_datetime_format']); ?>";
	Grid.queryString = "";
	<?php
	if (isset($_GET['client_ids']) && $_GET['client_ids'] != '')
	{
		?>Grid.queryString += "&client_ids=<?php echo $_GET['client_ids']; ?>";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('client_client_name'); ?>";
	myLabel.email = "<?php __('client_email'); ?>";
	myLabel.last_order = "<?php __('client_last_order'); ?>";
	myLabel.orders = "<?php __('client_orders'); ?>";
	myLabel.status = "<?php __('client_status'); ?>";
	myLabel.active = "<?php echo $u_statarr['T']; ?>";
	myLabel.inactive = "<?php echo $u_statarr['F']; ?>";
	myLabel.exported = "<?php __('lblExport'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
	</script>
	<?php
}
?>