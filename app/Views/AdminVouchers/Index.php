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
// 	if (isset($_GET['err']))
// 	{
// 		$titles = __('error_titles', true);
// 		$bodies = __('error_bodies', true);
// 		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
// 	}
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	$vt = __('voucher_types', true);
	$vv = __('voucher_valids', true);
	
	UtilComponent::printNotice(__('infoVouchersTitle', true), __('infoVouchersDesc', true));
	?>
	
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminVouchers" />
			<input type="hidden" name="action" value="Create" />
			<input type="submit" class="button" value="+ <?php __('voucher_create'); ?>" />
		</form>
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
		</form>
		<div class="float_right t5">
			<a href="#" class="button btn-all button-active"><?php __('lblAll'); ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="valid" data-value="fixed"><?php echo $vv['fixed']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="valid" data-value="period"><?php echo $vv['period']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="valid" data-value="recurring"><?php echo $vv['recurring']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.jsDateFormat = "<?php echo UtilComponent::jsDateFormat($tpl['option_arr']['o_date_format']); ?>";
	var myLabel = myLabel || {};
	myLabel.code = "<?php __('voucher_code'); ?>";
	myLabel.discount= "<?php __('voucher_discount'); ?>";
	myLabel.type = "<?php __('voucher_type'); ?>";
	myLabel.valid = "<?php __('voucher_valid'); ?>";
	myLabel.amount = "<?php echo $vt['amount']; ?>";
	myLabel.percent = "<?php echo $vt['percent']; ?>";
	myLabel.fixed = "<?php echo $vv['fixed']; ?>";
	myLabel.period = "<?php echo $vv['period']; ?>";
	myLabel.recurring = "<?php echo $vv['recurring']; ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
	myLabel.currency = "<?php echo $tpl['option_arr']['o_currency']; ?>";
	</script>
	<?php
}
?>