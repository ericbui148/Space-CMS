<?php
use App\Controllers\Components\UtilComponent;
?>
<form action="" method="post" class="form form">
	<input type="hidden" name="invoice_edit" value="1" />
	<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
	<p>
		<label class="title"><?php __('plugin_invoice_i_name'); ?></label>
		<span><input type="text" name="name" class="form-field w300" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['name'])); ?>" /></span>
	</p>
	<p>
		<label class="title"><?php __('plugin_invoice_i_description'); ?></label>
		<span><textarea name="description" class="form-field w350 h120"><?php echo htmlspecialchars(stripslashes($tpl['arr']['description'])); ?></textarea></span>
	</p>
	<?php
	if($tpl['config_arr']['o_use_qty_unit_price'] == 1)
	{ 
		?>
		<p>
			<label class="title"><?php __('plugin_invoice_i_qty'); ?></label>
			<input type="text" name="qty" class="form-field w100" value="<?php echo (float) $tpl['arr']['qty']; ?>" />
		</p>
		<p>
			<label class="title"><?php __('plugin_invoice_i_unit'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, !empty($tpl['arr']['currency']) ? $tpl['arr']['currency'] : $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
				<input type="text" name="unit_price" class="form-field w80 align_right" value="<?php echo (float) $tpl['arr']['unit_price']; ?>" />
			</span>
		</p>
		<?php
	} 
	?>
	<p>
		<label class="title"><?php __('plugin_invoice_i_amount'); ?></label>
		<span class="form-field-custom form-field-custom-before">
			<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, !empty($tpl['arr']['currency']) ? $tpl['arr']['currency'] : $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
			<input type="text" name="amount" class="form-field w80 align_right" value="<?php echo (float) $tpl['arr']['amount']; ?>" />
		</span>
	</p>
</form>