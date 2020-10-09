<?php
use Core\Framework\Components\SanitizeComponent;
?>
<p>
	<label class="title"><?php __('order_address'); ?>:</label>
	<select name="address_id" id="address_id" class="form-field w200">
		<option value=""><?php __('order_choose'); ?></option>
		<?php
		$disabled = ' disabled="disabled"';
		foreach ($tpl['address_arr'] as $address)
		{
			$selected = NULL;
			if ($address['id'] == @$tpl['order_arr']['address_id'])
			{
				$selected = ' selected="selected"';
				$disabled = NULL;
			}
			?><option value="<?php echo $address['id']; ?>"<?php echo $selected; ?>><?php echo SanitizeComponent::html($address['address_1']); ?></option><?php
		}
		?>
	</select>
	<input type="button" value="<?php __('order_copy_b'); ?>" class="button btnCopy btnCopyBilling"<?php echo $disabled; ?> />
	<input type="button" value="<?php __('order_copy_s'); ?>" class="button btnCopy btnCopyShipping"<?php echo $disabled; ?> />
</p>
<div id="boxAddress"></div>