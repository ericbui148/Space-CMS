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
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], !isset($_GET['errTime']) ? @$bodies[$_GET['err']] : $_SESSION[$controller->invoiceErrors][$_GET['errTime']]);
	}
	?>
	<form action="<?php echo BASE_URL; ?>index.php?controller=Invoice&amp;action=Index" id="frmInvoiceConfig" method="post" class="form form" enctype="multipart/form-data">
		<input type="hidden" name="invoice_post" value="1" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && !empty($_GET['tab_id']) ? $_GET['tab_id'] : 'tabs-1'; ?>" />
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('plugin_invoice_i_company_information');?></a></li>
				<li><a href="#tabs-2"><?php __('plugin_invoice_i_invoice_config');?></a></li>
				<li><a href="#tabs-3"><?php __('plugin_invoice_i_invoice_template');?></a></li>
			</ul>
			<div id="tabs-1">
				<?php
				UtilComponent::printNotice($titles['PIN13'], $bodies['PIN13'], false);
				?>
				<p>
					<label class="title"><?php __('plugin_invoice_i_logo'); ?></label>
					<span class="left" id="plugin_invoice_box_logo">
						<?php
						if (!empty($tpl['arr']['y_logo']) && is_file($tpl['arr']['y_logo']))
						{
							?><img src="<?php echo $tpl['arr']['y_logo']; ?>" alt="" class="align_middle" />
							<input type="button" class="button plugin_invoice_delete_logo" value="<?php __('lblDelete'); ?>" /><?php
						} else {
							?><input type="file" name="y_logo" id="y_logo" class="form-field w350"/><?php
						}
						?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_company'); ?></label>
					<span class="left">
						<input type="text" name="y_company" id="y_company" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_company'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_name'); ?></label>
					<span class="left">
						<input type="text" name="y_name" id="y_name" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_name'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_street_address'); ?></label>
					<span class="left">
						<input type="text" name="y_street_address" id="y_street_address" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_street_address'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_country'); ?></label>
					<span class="left">
						<select name="y_country" class="form-field w400">
							<option value="">----</option>
							<?php
							foreach ($tpl['country_arr'] as $country)
							{
								?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] == @$tpl['arr']['y_country'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_city'); ?></label>
					<span class="left">
						<input type="text" name="y_city" id="y_city" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_city'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_state'); ?></label>
					<span class="left">
						<input type="text" name="y_state" id="y_state" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_state'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_zip'); ?></label>
					<span class="left">
						<input type="text" name="y_zip" id="y_zip" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_zip'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_phone'); ?></label>
					<span class="left">
						<input type="text" name="y_phone" id="y_phone" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_phone'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_fax'); ?></label>
					<span class="left">
						<input type="text" name="y_fax" id="y_fax" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_fax'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_email'); ?></label>
					<span class="left">
						<input type="text" name="y_email" id="y_email" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_email'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('plugin_invoice_i_url'); ?></label>
					<span class="left">
						<input type="text" name="y_url" id="y_url" class="form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_url'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button float_left align_middle" />
				</p>
			</div><!-- tabs-1 -->
			<div id="tabs-2">
				<?php
				UtilComponent::printNotice($titles['PIN14'], $bodies['PIN14'], false);
				?>
				<table class="table b10" cellpadding="0" cellspacing="0" style="width: 100%">
					<thead>
						<tr>
							<th><?php __('plugin_invoice_i_option'); ?></th>
							<th><?php __('plugin_invoice_i_value'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php __('plugin_invoice_i_accept_payments'); ?></td>
							<td><input type="checkbox" class="align_middle" name="p_accept_payments" value="1"<?php echo (int) $tpl['arr']['p_accept_payments'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_accept_paypal'); ?></td>
							<td><input type="checkbox" name="p_accept_paypal" data-box=".boxPaypal" value="1"<?php echo (int) $tpl['arr']['p_accept_paypal'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr class="boxPaypal" style="display: <?php echo (int) $tpl['arr']['p_accept_paypal'] === 1 ? NULL : 'none'; ?>">
							<td><?php __('plugin_invoice_i_paypal_address'); ?></td>
							<td><input type="text" name="p_paypal_address" class="form-field w200" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['p_paypal_address'])); ?>" /></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_accept_authorize'); ?></td>
							<td><input type="checkbox" name="p_accept_authorize" data-box=".boxAuthorize" value="1"<?php echo (int) $tpl['arr']['p_accept_authorize'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr class="boxAuthorize" style="display: <?php echo (int) $tpl['arr']['p_accept_authorize'] === 1 ? NULL : 'none'; ?>">
							<td><?php __('plugin_invoice_i_authorize_tz'); ?></td>
							<td>
								<select name="p_authorize_tz" class="form-field">
								<?php
								foreach ($tpl['timezones'] as $k => $v)
								{
									?><option value="<?php echo $k; ?>"<?php echo $k == $tpl['arr']['p_authorize_tz'] ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
								}
								?>
								</select>
							</td>
						</tr>
						<tr class="boxAuthorize" style="display: <?php echo (int) $tpl['arr']['p_accept_authorize'] === 1 ? NULL : 'none'; ?>">
							<td><?php __('plugin_invoice_i_authorize_key'); ?></td>
							<td><input type="text" name="p_authorize_key" class="form-field w200" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['p_authorize_key'])); ?>" /></td>
						</tr>
						<tr class="boxAuthorize" style="display: <?php echo (int) $tpl['arr']['p_accept_authorize'] === 1 ? NULL : 'none'; ?>">
							<td><?php __('plugin_invoice_i_authorize_mid'); ?></td>
							<td><input type="text" name="p_authorize_mid" class="form-field w200" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['p_authorize_mid'])); ?>" /></td>
						</tr>
						<tr class="boxAuthorize" style="display: <?php echo (int) $tpl['arr']['p_accept_authorize'] === 1 ? NULL : 'none'; ?>">
							<td><?php __('plugin_invoice_i_authorize_hash'); ?></td>
							<td><input type="text" name="p_authorize_hash" class="form-field w200" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['p_authorize_hash'])); ?>" /></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_accept_creditcard'); ?></td>
							<td><input type="checkbox" name="p_accept_creditcard" value="1"<?php echo (int) $tpl['arr']['p_accept_creditcard'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_accept_cash'); ?></td>
							<td><input type="checkbox" name="p_accept_cash" value="1"<?php echo (int) $tpl['arr']['p_accept_cash'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_accept_bank'); ?></td>
							<td><input type="checkbox" name="p_accept_bank" data-box=".boxBank" value="1"<?php echo (int) $tpl['arr']['p_accept_bank'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr class="boxBank" style="display: <?php echo (int) $tpl['arr']['p_accept_bank'] === 1 ? NULL : 'none'; ?>">
							<td><?php __('plugin_invoice_i_bank_account'); ?></td>
							<td><textarea name="p_bank_account" class="form-field w250 h50"><?php echo htmlspecialchars(stripslashes($tpl['arr']['p_bank_account'])); ?></textarea></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_use_shipping_details');?></td>
							<td><input type="checkbox" class="align_middle" name="si_include" value="1"<?php echo (int) $tpl['arr']['si_include'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
						<tr>
							<td><?php __('plugin_invoice_i_use_qty_unit_price');?></td>
							<td><input type="checkbox" class="align_middle" name="o_use_qty_unit_price" value="1"<?php echo (int) $tpl['arr']['o_use_qty_unit_price'] === 1 ? ' checked="checked"' : NULL; ?> /></td>
						</tr>
					</tbody>
				</table>
				
				<p>
					<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button float_left align_middle" />
				</p>
			</div><!-- tabs-2 -->
			<div id="tabs-3">
				<?php
				UtilComponent::printNotice($titles['PIN15'], $bodies['PIN15'], false);
				?>
				
				<p>
					<textarea name="y_template" id="y_template" class="form-field w700 h600 mceEditor"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['y_template'])); ?></textarea>
				</p>
				<p>
					<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button float_left align_middle" />
				</p>
			</div><!-- tabs-3 -->
		</div>
	</form>
	
	<div id="dialogDeleteLogo" style="display: none" title="<?php __('plugin_invoice_delete_logo_title'); ?>"><?php __('plugin_invoice_delete_logo_body'); ?></div>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
	myLabel.btn_delete = "<?php __('lblDelete'); ?>";
	(function ($) {
	$(function() {
		<?php
		if (isset($_GET['tab_id']) && !empty($_GET['tab_id']))
		{		
			$tab_id = $_GET['tab_id'];
			$tab_id = $tab_id < 0 ? 0 : $tab_id; 
			?>$("#tabs").tabs("option", "selected", <?php echo str_replace("tabs-", "", $tab_id) - 1;?>);<?php
		}
		?>
	});
	})(jQuery_1_8_2);
	</script>
	<?php
}
?>