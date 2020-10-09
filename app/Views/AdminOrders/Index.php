<?php
use App\Controllers\Components\UtilComponent;
use App\Controllers\AppController;
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
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	?>

	<?php UtilComponent::printNotice(__('infoOrdersTitle', true), __('infoOrdersDesc', true)); ?>
	<div class="b10">
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
			<button type="button" class="button button-detailed"><span class="button-detailed-arrow"></span></button>
		</form>
		<div class="float_right t5">
			<a href="#" class="button btn-all<?php echo !isset($_GET['status']) ? ' button-active' : null;?>"><?php __('lblAll'); ?></a>
			<?php
			foreach (__('order_statuses', true) as $k => $v)
			{
				?><a href="#" class="button btn-filter btn-status<?php echo isset($_GET['status']) ? ($_GET['status'] == $k ? ' button-active' : null) : null;?>" data-column="status" data-value="<?php echo $k; ?>"><?php echo $v; ?></a>
				<?php
			}
			?>
		</div>
		<br class="clear_both" />
	</div>
	
	<div class="form-filter-advanced" style="display: none">
		<span class="menu-list-arrow"></span>
		<form action="" method="get" class="form form form-search frm-filter-advanced">
			<div class="float_left w400">
				<p>
					<label class="title"><?php __('order_client'); ?></label>
					<input type="text" name="q" class="form-field w150" />
				</p>
				<p>
					<label class="title"><?php __('order_products'); ?></label>
					<select name="product_id" class="form-field w150 custom-chosen">
					<option value="">-- <?php __('lblChoose'); ?> --</option>
					<?php
					foreach ($tpl['product_arr'] as $item)
					{
						?><option value="<?php echo $item['id']; ?>"><?php echo SanitizeComponent::html($item['name']); ?></option><?php
					}
					?>
					</select>
				</p>
				<p>
					<label class="title"><?php __('order_created'); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" name="date_from" class="form-field pointer w80 datepick" value="<?php echo (isset($_GET['date_from']) && $_GET['date_from'] != '') ? $_GET['date_from'] : null;?>" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
						<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
					</span>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" name="date_to" class="form-field pointer w80 datepick" value="<?php echo (isset($_GET['date_to']) && $_GET['date_to'] != '') ? $_GET['date_to'] : null;?>" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
						<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSearch'); ?>" class="button" />
					<input type="reset" value="<?php __('btnCancel'); ?>" class="button" />
				</p>
			</div>
			<div class="float_right w300">
				<?php 
				$order_statuses = [
				    'new' => 'Mới',
				    'pending' => 'Chờ',
				    'cancelled' => 'Bỏ qua',
				    'completed' => 'Hoàn thành'
				];
				$payment_methods = [
				    'creditcart' => 'VISA',
				    'bank' => 'Chuyển khoản',
				    'cod' => 'Tiền mặt',
				    'paypal' => 'Paypal',
				    'authorize' => 'Authorize.net'
				];
				?>
				<p>
					<label class="title" style="width: 110px"><?php __('order_status'); ?></label>
					<select name="status" class="form-field w150">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($order_statuses as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php echo isset($_GET['status']) && $_GET['status'] == $k ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($v); ?></option><?php
						}
						?>
					</select>
				</p>
				<p>
					<label class="title" style="width: 110px"><?php __('order_payment'); ?></label>
					<select name="payment_method" class="form-field w150">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($payment_methods as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php echo isset($_GET['payment_method']) && $_GET['payment_method'] == $k ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($v); ?></option><?php
						}
						?>
					</select>
				</p>
				<p>
					<label class="title" style="width: 110px"><?php __('order_total'); ?></label>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
						<input type="text" name="total_from" class="form-field number w50" />
					</span>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
						<input type="text" name="total_to" class="form-field number w50" />
					</span>
				</p>
			</div>
			<br class="clear_both" />
		</form>
	</div>

	<div id="grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.jsDateFormat = "<?php echo UtilComponent::jsDateFormat($tpl['option_arr']['o_date_format']); ?>";
	Grid.queryString = "";
	<?php
	if (isset($_GET['client_id']) && (int) $_GET['client_id'] > 0)
	{
		?>Grid.queryString += "&client_id=<?php echo (int) $_GET['client_id']; ?>";<?php
	}
	if (isset($_GET['date_from']) && $_GET['date_from'] != '')
	{
		?>Grid.queryString += "&date_from=<?php echo $_GET['date_from']; ?>";<?php
	}
	if (isset($_GET['date_to']) && $_GET['date_to'] != '')
	{
		?>Grid.queryString += "&date_to=<?php echo $_GET['date_to']; ?>";<?php
	}
	if (isset($_GET['status']) && $_GET['status'] != '')
	{
		?>Grid.queryString += "&status=<?php echo $_GET['status']; ?>";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.uuid = "<?php __('order_uuid'); ?>";
	myLabel.client = "<?php __('order_client'); ?>";
	myLabel.created = "<?php __('order_created'); ?>";
	myLabel.status = "<?php __('order_status'); ?>";
	myLabel.total = "<?php __('order_total'); ?>";
	myLabel.statuses = <?php echo AppController::jsonEncode($order_statuses); ?>;
	myLabel.exported = "<?php __('lblExport'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
	</script>
	<?php
}
?>