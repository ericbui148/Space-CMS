<?php
use Core\Framework\Components\SanitizeComponent;
?>
<div class="boxAddress">
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients" class="button float_right btnRemoveAddress"><?php __('client_del_address'); ?></a>
	<p>
		<label class="title"><?php __('client_name'); ?></label>
		<input type="text" name="name[{INDEX}]" class="form-field w300" />
	</p>
	<p>
		<label class="title"><?php __('client_country'); ?></label>
		<select name="country_id[{INDEX}]" class="form-field">
			<option value=""><?php __('client_choose'); ?></option>
			<?php
			foreach ($tpl['country_arr'] as $country)
			{
				?><option value="<?php echo $country['id']; ?>"><?php echo SanitizeComponent::html($country['name']); ?></option><?php
			}
			?>
		</select>
	</p>
	<p>
		<label class="title"><?php __('client_state'); ?></label>
		<input type="text" name="state[{INDEX}]" class="form-field w200" />
	</p>
	<p>
		<label class="title"><?php __('client_city'); ?></label>
		<input type="text" name="city[{INDEX}]" class="form-field w200" />
	</p>
	<p>
		<label class="title"><?php __('client_zip'); ?></label>
		<input type="text" name="zip[{INDEX}]" class="form-field w80" />
	</p>
	<p>
		<label class="title"><?php __('client_address_1'); ?></label>
		<input type="text" name="address_1[{INDEX}]" class="form-field w350" />
	</p>
	<p>
		<label class="title"><?php __('client_address_2'); ?></label>
		<input type="text" name="address_2[{INDEX}]" class="form-field w350" />
	</p>
	<p>
		<label class="title">&nbsp;</label>
		<span class="left">
			<label><input type="radio" name="is_default_shipping" value="{INDEX}" /> <?php __('client_default_shipping'); ?></label>
		</span>
	</p>
	<p>
		<label class="title">&nbsp;</label>
		<span class="left">
			<label><input type="radio" name="is_default_billing" value="{INDEX}" /> <?php __('client_default_billing'); ?></label>
		</span>
	</p>
</div>