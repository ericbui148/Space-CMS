<?php
use App\Controllers\Components\UtilComponent;
?>
<form action="" method="post" class="form form">
	<input type="hidden" name="invoice_add" value="1" />
	<input type="hidden" name="invoice_id" value="<?php echo @$_GET['invoice_id']; ?>" />
	<input type="hidden" name="tmp" value="<?php echo @$_GET['tmp']; ?>" />
	<p>
		<label class="title"><?php __('plugin_invoice_i_name'); ?></label>
		<span><input type="text" name="name" class="form-field w300" /></span>
	</p>
	<p>
		<label class="title"><?php __('plugin_invoice_i_description'); ?></label>
		<span><textarea name="description" class="form-field w350 h120"></textarea></span>
	</p>
	<?php
	if($tpl['config_arr']['o_use_qty_unit_price'] == 1)
	{ 
		?>
		<p>
			<label class="title"><?php __('plugin_invoice_i_qty'); ?></label>
			<input type="text" name="qty" class="form-field w100" />
		</p>
		<p>
			<label class="title"><?php __('plugin_invoice_i_unit'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, !empty($tpl['arr']['currency']) ? $tpl['arr']['currency'] : $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
				<input type="text" name="unit_price" class="form-field w80 align_right" />
			</span>
		</p>
		<?php
	} 
	?>
	<p>
		<label class="title"><?php __('plugin_invoice_i_amount'); ?></label>
		<span class="form-field-custom form-field-custom-before">
			<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, !empty($tpl['arr']['currency']) ? $tpl['arr']['currency'] : $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
			<input type="text" name="amount" class="form-field w80 align_right" />
		</span>
	</p>
</form>