<?php
use Core\Framework\Components\SanitizeComponent;

foreach ($tpl['address_arr'] as $address)
{
	?>
	<div class="boxAddress">
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients" class="button float_right btnDeleteAddress" data-id="<?php echo $address['id']; ?>" data-client_id="<?php echo $address['client_id']; ?>"><?php __('client_del_address'); ?></a>
		<p>
			<label class="title"><?php __('client_name'); ?></label>
			<input type="text" name="name[<?php echo $address['id']; ?>]" class="form-field w300" value="<?php echo SanitizeComponent::html($address['name']); ?>" />
		</p>
		<p>
			<label class="title"><?php __('client_country'); ?></label>
			<select name="country_id[<?php echo $address['id']; ?>]" class="form-field custom-chosen">
				<option value=""><?php __('client_choose'); ?></option>
				<?php
				foreach ($tpl['country_arr'] as $country)
				{
					?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] == $address['country_id'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
				}
				?>
			</select>
		</p>
		<p>
			<label class="title"><?php __('client_state'); ?></label>
			<input type="text" name="state[<?php echo $address['id']; ?>]" class="form-field w200" value="<?php echo SanitizeComponent::html($address['state']); ?>" />
		</p>
		<p>
			<label class="title"><?php __('client_city'); ?></label>
			<input type="text" name="city[<?php echo $address['id']; ?>]" class="form-field w200" value="<?php echo SanitizeComponent::html($address['city']); ?>" />
		</p>
		<p>
			<label class="title"><?php __('client_zip'); ?></label>
			<input type="text" name="zip[<?php echo $address['id']; ?>]" class="form-field w80" value="<?php echo SanitizeComponent::html($address['zip']); ?>" />
		</p>
		<p>
			<label class="title"><?php __('client_address_1'); ?></label>
			<input type="text" name="address_1[<?php echo $address['id']; ?>]" class="form-field w350" value="<?php echo SanitizeComponent::html($address['address_1']); ?>" />
		</p>
		<p>
			<label class="title"><?php __('client_address_2'); ?></label>
			<input type="text" name="address_2[<?php echo $address['id']; ?>]" class="form-field w350" value="<?php echo SanitizeComponent::html($address['address_2']); ?>" />
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<span class="left">
				<label><input type="radio" name="is_default_shipping" value="<?php echo $address['id']; ?>"<?php echo (int) $address['is_default_shipping'] === 1 ? ' checked="checked"' : NULL; ?> /> <?php __('client_default_shipping'); ?></label>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<span class="left">
				<label><input type="radio" name="is_default_billing" value="<?php echo $address['id']; ?>"<?php echo (int) $address['is_default_billing'] === 1 ? ' checked="checked"' : NULL; ?> /> <?php __('client_default_billing'); ?></label>
			</span>
		</p>
	</div>
	<?php
}
?>