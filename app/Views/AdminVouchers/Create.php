<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\TimeComponent;
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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$stringDate = explode(',', $tpl['option_arr']['o_date_format']);
	$jqDateFormat = UtilComponent::jqDateFormat($stringDate[0]);
	?>
	<style type="text/css">
    .ui-autocomplete-loading { background: white url('<?php echo BASE_URL . IMG_PATH; ?>backend/ajax-loader.gif') right center no-repeat; }
    .ui-helper-hidden-accessible{position: static !important;}
    </style>
    
	<?php UtilComponent::printNotice(@$titles['AV10'], @$bodies['AV10']); ?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminVouchers&amp;action=Create" method="post" id="frmCreateVoucher" class="form">
		<input type="hidden" name="voucher_create" value="1" />
		<p>
			<label class="title"><?php __('voucher_code'); ?></label>
			<span class="inline_block"><input type="text" name="code" id="code" class="form-field w100 required" /></span>
		</p>
		<p>
			<label class="title"><?php __('voucher_discount'); ?></label>
			<span class="inline_block">
				<input type="text" name="discount" id="discount" class="form-field w80 align_right number required" />
				<select name="type" id="type" class="form-field required">
					<option value=""><?php __('voucher_choose'); ?></option>
					<?php
					foreach (__('voucher_types', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $k == 'amount' ? $tpl['option_arr']['o_currency'] : $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<div class="p" style="overflow: visible; position: relative;">
			<label class="title"><?php __('voucher_products'); ?></label>
			<select id="product_id" name="product_id[]" class="form-field w400" multiple="multiple">
				<option value="">-- <?php __('voucher_choose'); ?> --</option>
				<?php
				foreach ($tpl['product_arr'] as $product)
				{
					?><option value="<?php echo $product['id']; ?>"><?php echo SanitizeComponent::html($product['name'] . " (" . $product['sku'] . ")"); ?></option><?php
				}
				?>
			</select>
		</div>
		<p>
			<label class="title"><?php __('voucher_valid'); ?></label>
			<span class="inline_block">
				<select name="valid" id="valid" class="form-field required">
					<option value=""><?php __('voucher_choose'); ?></option>
					<?php
					foreach (__('voucher_valids', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<div id="vFixed" class="vBox" style="display: none">
			<p>
				<label class="title"><?php __('voucher_date'); ?></label>
				<span class="form-field-custom form-field-custom-after">
					<input type="text" name="f_date" class="form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
					<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
				</span>
			</p>
			<p>
				<label class="title"><?php __('voucher_time_from'); ?></label>
				<?php echo TimeComponent::factory()
					->attr('name', 'f_hour_from')
					->attr('id', 'f_hour_from')
					->attr('class', 'form-field')
					->hour(); ?>
				<?php echo TimeComponent::factory()
					->prop('step', 5)
					->attr('name', 'f_minute_from')
					->attr('id', 'f_minute_from')
					->attr('class', 'form-field')
					->minute(); ?>
			</p>
			<p>
				<label class="title"><?php __('voucher_time_to'); ?></label>
				<?php echo TimeComponent::factory()
					->attr('name', 'f_hour_to')
					->attr('id', 'f_hour_to')
					->attr('class', 'form-field')
					->hour(); ?>
				<?php echo TimeComponent::factory()
					->prop('step', 5)
					->attr('name', 'f_minute_to')
					->attr('id', 'f_minute_to')
					->attr('class', 'form-field')
					->minute(); ?>
			</p>
		</div>
		<div id="vPeriod" class="vBox" style="display: none">
			<p>
				<label class="title"><?php __('voucher_date_from'); ?></label>
				<span class="float_left form-field-custom form-field-custom-after">
					<input type="text" name="p_date_from" class="form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
					<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
				</span>
				<span class="float_left l5">
				<?php echo TimeComponent::factory()
					->attr('name', 'p_hour_from')
					->attr('id', 'p_hour_from')
					->attr('class', 'form-field')
					->hour(); ?>
				<?php echo TimeComponent::factory()
					->prop('step', 5)
					->attr('name', 'p_minute_from')
					->attr('id', 'p_minute_from')
					->attr('class', 'form-field')
					->minute(); ?>
				</span>
			</p>
			<p>
				<label class="title"><?php __('voucher_date_to'); ?></label>
				<span class="float_left form-field-custom form-field-custom-after">
					<input type="text" name="p_date_to" class="form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
					<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
				</span>
				<span class="float_left l5">
				<?php echo TimeComponent::factory()
					->attr('name', 'p_hour_to')
					->attr('id', 'p_hour_to')
					->attr('class', 'form-field')
					->hour(); ?>
				<?php echo TimeComponent::factory()
					->prop('step', 5)
					->attr('name', 'p_minute_to')
					->attr('id', 'p_minute_to')
					->attr('class', 'form-field')
					->minute(); ?>
				</span>
			</p>
		</div>
		<?php
		extract(__('daynames', true));
		switch ((int) $tpl['option_arr']['o_week_start'])
		{
			case 0:
				$daynames = compact('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
				break;
			case 1:
				$daynames = compact('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
				break;
			case 2:
				$daynames = compact('tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'monday');
				break;
			case 3:
				$daynames = compact('wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'monday', 'tuesday');
				break;
			case 4:
				$daynames = compact('thursday', 'friday', 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday');
				break;
			case 5:
				$daynames = compact('friday', 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday');
				break;
			case 6:
				$daynames = compact('saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday');
				break;
		}
		//$daynames = compact('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		?>
		<div id="vRecurring" class="vBox" style="display: none">
			<p>
				<label class="title"><?php __('voucher_every'); ?></label>
				<select name="r_every" id="r_every" class="form-field">
					<option value=""><?php __('voucher_choose'); ?></option>
					<?php
					foreach ($daynames as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</p>
			<p>
				<label class="title"><?php __('voucher_time_from'); ?></label>
				<?php echo TimeComponent::factory()
					->attr('name', 'r_hour_from')
					->attr('id', 'r_hour_from')
					->attr('class', 'form-field')
					->hour(); ?>
				<?php echo TimeComponent::factory()
					->prop('step', 5)
					->attr('name', 'r_minute_from')
					->attr('id', 'r_minute_from')
					->attr('class', 'form-field')
					->minute(); ?>
			</p>
			<p>
				<label class="title"><?php __('voucher_time_to'); ?></label>
				<?php echo TimeComponent::factory()
					->attr('name', 'r_hour_to')
					->attr('id', 'r_hour_to')
					->attr('class', 'form-field')
					->hour(); ?>
				<?php echo TimeComponent::factory()
					->prop('step', 5)
					->attr('name', 'r_minute_to')
					->attr('id', 'r_minute_to')
					->attr('class', 'form-field')
					->minute(); ?>
			</p>
		</div>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminVouchers&action=Index';" />
		</p>
	</form>
	<?php
}
?>
<script type="text/javascript">
var myLabel = myLabel || {};
myLabel.same_code = "<?php __('lblSameVoucherCode'); ?>";
</script>