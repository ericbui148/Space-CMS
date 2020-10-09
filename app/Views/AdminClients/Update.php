<?php
use App\Controllers\Components\UtilComponent;
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
	
	UtilComponent::printNotice(__('infoUpdateClientTitle', true), __('infoUpdateClientDesc', true));
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients&amp;action=Update" method="post" class="form form" id="frmUpdateClient">
		<input type="hidden" name="update_form" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<fieldset class="fieldset white">
			<legend><?php __('client_general'); ?></legend>
			<div class="float_left">
				<p>
					<label class="title"><?php __('client_email'); ?></label>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
						<input type="text" name="email" id="email" class="form-field email w200" value="<?php echo SanitizeComponent::html($tpl['arr']['email']); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('client_password'); ?></label>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-password"></abbr></span>
						<input type="text" name="password" id="password" class="form-field w200" value="<?php echo SanitizeComponent::html($tpl['arr']['password']); ?>" />
					</span>
				</p>
			</div>
			<div class="float_right w300">
				<p>
					<label class="title"><?php __('client_created'); ?></label>
					<span class="left"><?php echo date($tpl['option_arr']['o_datetime_format'], strtotime($tpl['arr']['created'])); ?></span>
				</p>
				<p>
					<label class="title"><?php __('client_last_login'); ?></label>
					<span class="left"><?php echo !empty($tpl['arr']['last_login']) ? date($tpl['option_arr']['o_datetime_format'], strtotime($tpl['arr']['last_login'])) : '---'; ?></span>
				</p>
				<p>
					<label class="title"><?php __('client_orders'); ?></label>
					<span class="left"><?php
					if ((int) $tpl['arr']['orders'] === 0)
					{
						?>0<?php
					} else {
						?><a href="<?php echo BASE_URL; ?>index.php?controller=AdminOrders&amp;action=Index&client_id=<?php echo $tpl['arr']['id']; ?>"><?php echo (int) $tpl['arr']['orders']; ?></a><?php
					}
					?></span>
				</p>
			</div>
			<br class="clear_both" />
			<p>
				<label class="title"><?php __('client_client_name'); ?></label>
				<span class="inline_block">
					<input type="text" name="client_name" id="client_name" class="form-field w200" value="<?php echo SanitizeComponent::html($tpl['arr']['client_name']); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_phone'); ?></label>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
					<input type="text" name="phone" id="phone" class="form-field w200" value="<?php echo SanitizeComponent::html($tpl['arr']['phone']); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_url'); ?></label>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
					<input type="text" name="url" id="url" class="form-field w350" value="<?php echo SanitizeComponent::html($tpl['arr']['url']); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_status'); ?></label>
				<span class="inline_block">
					<select name="status" id="status" class="form-field required">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach (__('u_statarr', true) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php echo $k != $tpl['arr']['status'] ? NULL : ' selected="selected"'; ?>><?php echo $v; ?></option><?php
						}
						?>
					</select>
				</span>
			</p>
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
				<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminClients&action=Index';" />
			</p>
		</fieldset>
		<?php UtilComponent::printNotice(@$titles['AC10'], @$bodies['AC10']); ?>
		<fieldset class="fieldset white">
			<legend><?php __('client_address_book'); ?></legend>
			<div id="boxAddresses"><?php include dirname(__FILE__) . '/GetAddresses.php'; ?></div>
			<p style="padding-top: 10px !important">
				<label class="title">&nbsp;</label>
				<a href="#" class="button btnAddAddress"><?php __('client_add_address'); ?></a>
			</p>
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
				<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminClients&action=Index';" />
			</p>
		</fieldset>
	</form>
	
	<div id="dialogDeleteAddress" style="display: none" title="<?php __('client_da_title'); ?>"><?php __('client_da_body'); ?></div>
	<div id="boxCloneAddress" style="display: none"><?php include dirname(__FILE__) . '/elements/address.php'; ?></div>
	<?php
}
?>