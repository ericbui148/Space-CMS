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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	
	UtilComponent::printNotice(__('infoCreateClientTitle', true), __('infoCreateClientDesc', true));
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients&amp;action=Create" method="post" class="form form" id="frmCreateClient">
		<input type="hidden" name="create_form" value="1" />
		<fieldset class="fieldset white">
			<legend><?php __('client_general'); ?></legend>
			<p>
				<label class="title"><?php __('client_email'); ?></label>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
					<input type="text" name="email" id="email" class="form-field email w200" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_password'); ?></label>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-password"></abbr></span>
					<input type="text" name="password" id="password" class="form-field w200" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_client_name'); ?></label>
				<span class="inline_block">
					<input type="text" name="client_name" id="client_name" class="form-field w200" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_phone'); ?></label>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
					<input type="text" name="phone" id="phone" class="form-field w200" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('client_url'); ?></label>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
					<input type="text" name="url" id="url" class="form-field w350" />
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
							?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
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
	<div id="boxCloneAddress" style="display: none"><?php include dirname(__FILE__) . '/elements/address.php'; ?></div>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.email_taken = "<?php __('vr_email_taken'); ?>";
	</script>
	<?php
}
?>