<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
use Core\Framework\Components\TimeComponent;

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
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	?>
	<style type="text/css">
	label[for="status_cancelled"].ui-state-active{
		background: #FEFEFE;
		color: black;
	}
	label[for="status_not_paid"].ui-state-active{
		background: red;
		color: #fff;
		text-shadow: 1px 1px 1px #444;
	}
	label[for="status_paid"].ui-state-active{
		background: green;
		color: white;
		text-shadow: 1px 1px 1px #333;
	}
	</style>
	<form action="<?php echo BASE_URL; ?>index.php?controller=Invoice&amp;action=Update" method="post" class="form form" id="frmUpdateInvoice">
		<input type="hidden" name="invoice_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<input type="hidden" name="order_id" value="<?php echo $tpl['arr']['order_id']; ?>" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && !empty($_GET['tab_id']) ? $_GET['tab_id'] : 'tabs-1'; ?>" />
		
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('plugin_invoice_i_details');?></a></li>
				<li><a href="#tabs-2"><?php __('plugin_invoice_i_client');?></a></li>
				<li class="plugin_view_invoice"><a href="<?php echo BASE_URL; ?>?controller=Invoice&amp;action=View&amp;uuid=<?php echo $tpl['arr']['uuid']; ?>" target="_blank"><?php __('plugin_invoice_i_invoice'); ?></a></li>
			</ul>
			<div id="tabs-1">
				<?php UtilComponent::printNotice(@$titles['PIN10'], @$bodies['PIN10']); ?>
				<fieldset class="fieldset light">
					<legend><?php __('plugin_invoice_general_info'); ?></legend>
					<div class="float_left w300">
						<p>
							<label class="title"><?php __('plugin_invoice_i_uuid'); ?></label>
							<span class="left h30">
								<input type="text" name="uuid" class="form-field w100" value="<?php echo @$tpl['arr']['uuid']; ?>"/>
							</span>
						</p>
						
						<p>
							<label class="title"><?php __('plugin_invoice_i_issue_date'); ?></label>
							<span class="form-field-custom form-field-custom-after">
								<input type="text" name="issue_date" class="form-field w80 datepick pointer" readonly="readonly" value="<?php echo UtilComponent::formatDate(@$tpl['arr']['issue_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
								<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_created'); ?></label>
							<span class="left"><?php echo !empty($tpl['arr']['created']) ? date($tpl['option_arr']['o_date_format'] . " H:i:s", strtotime($tpl['arr']['created'])) : '---'; ?></span>
						</p>
					</div>
					<div class="float_right w400">
						<p>
							<label class="title"><?php __('plugin_invoice_i_order_id'); ?></label>
							<span class="left h30"><?php
							if (defined("INVOICE_PLUGIN"))
							{
								?><a href="<?php echo BASE_URL . str_replace('{ORDER_ID}', $tpl['arr']['order_id'], INVOICE_PLUGIN); ?>"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['order_id'])); ?></a><?php
							} else {
								echo htmlspecialchars(stripslashes(@$tpl['arr']['order_id']));
							}
							?></span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_due_date'); ?></label>
							<span class="form-field-custom form-field-custom-after">
								<input type="text" name="due_date" class="form-field w80 datepick pointer" readonly="readonly" value="<?php echo UtilComponent::formatDate(@$tpl['arr']['due_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
								<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_modified'); ?></label>
							<span class="left"><?php echo !empty($tpl['arr']['modified']) ? date($tpl['option_arr']['o_date_format'] . " H:i:s", strtotime($tpl['arr']['modified'])) : '---'; ?></span>
						</p>
					</div>
					<br class="clear_both" />
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button" />
						<input type="button" value="<?php __('plugin_invoice_view'); ?>" class="button btnInvoiceView" />
						<input type="button" value="<?php __('plugin_invoice_print'); ?>" class="button btnInvoicePrint" />
						<input type="button" value="<?php __('plugin_invoice_send'); ?>" class="button btnInvoiceSend" data-id="<?php echo $tpl['arr']['uuid']; ?>" data-uuid="<?php echo $tpl['arr']['order_id']; ?>" />
					</p>
				</fieldset>
				
				<fieldset class="fieldset white">
					<legend><?php __('plugin_invoice_items_info'); ?></legend>
					<div id="grid_items"></div>
					<input type="button" class="t5 button plugin_invoice_add_item" value="<?php __('plugin_invoice_add'); ?>" />
				</fieldset>
				
				<fieldset class="fieldset sky">
					<legend><?php __('plugin_invoice_payment_info'); ?></legend>
					<div class="float_left w350">
						<p>
							<label class="title"><?php __('plugin_invoice_i_subtotal'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="subtotal" id="subtotal" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['subtotal'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_discount'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="discount" id="discount" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['discount'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_tax'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="tax" id="tax" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['tax'])); ?>" />
							</span>
						</p>
						<?php if ((int) $tpl['config_arr']['si_include'] === 1 && (int) $tpl['config_arr']['si_shipping'] === 1) : ?>
						<p>
							<label class="title"><?php __('plugin_invoice_i_shipping'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="shipping" id="shipping" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['shipping'])); ?>" />
							</span>
						</p>
						<?php endif; ?>
						<p>
							<label class="title"><?php __('plugin_invoice_i_total'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="total" id="total" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['total'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_paid_deposit'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="paid_deposit" id="paid_deposit" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['paid_deposit'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_amount_due'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['arr']['currency'], ""); ?></abbr></span>
								<input type="text" name="amount_due" id="amount_due" class="form-field number w80" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['amount_due'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_currency'); ?></label>
							<span class="left">
								<select name="currency" id="currency" class="form-field w100">
								<option value="">---</option>
								<?php
								foreach (__('currencies', true) as $currency)
								{
									?><option value="<?php echo $currency; ?>"<?php echo $currency == @$tpl['arr']['currency'] ? ' selected="selected"' : NULL; ?>><?php echo $currency; ?></option><?php
								}
								?>
								</select>
							</span>
						</p>
						<p>
							<label class="title">&nbsp;</label>
							<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button" />
						</p>
					</div>
					<div class="float_right w350">
						<?php
						if((int) $tpl['config_arr']['p_accept_payments'] === 1)
						{
							?>
							<p>
								<label class="title"><?php __('plugin_invoice_i_payment_method'); ?></label><br/>
								<span class="left">
									<select name="payment_method" id="payment_method" class="form-field w200">
										<option value="">---</option>
										<?php
										foreach (__('plugin_invoice_payment_methods', true) as $k => $v)
										{
											if((int) $tpl['config_arr']['p_accept_' . $k] !== 1)
											{
												continue;
											}
											?><option value="<?php echo $k; ?>"<?php echo $k == @$tpl['arr']['payment_method'] ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
										}
										?>
									</select>
								</span>
							</p>
							<?php
						} 
						?>
						<p>
							<label class="title"><?php __('plugin_invoice_i_status'); ?></label><br/>
							<span class="left h30 block" id="boxStatus">
							<?php
							foreach (__('plugin_invoice_statuses', true) as $k => $v)
							{
								?><input type="radio" name="status" id="status_<?php echo $k; ?>" value="<?php echo $k; ?>"<?php echo $tpl['arr']['status'] == $k ? ' checked="checked"' : NULL; ?> /> <label for="status_<?php echo $k; ?>"><?php echo $v; ?></label><?php
							}
							?>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_notes'); ?></label><br/>
							<span class="left">
								<textarea name="notes" id="notes" class="form-field" style="width: 325px; height: 236px"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['notes'])); ?></textarea>
							</span>
						</p>
					</div>
					<br class="clear_both" />
				</fieldset>
			</div><!-- tabs-1 -->
			<div id="tabs-2">
				<?php UtilComponent::printNotice(@$titles['PIN11'], @$bodies['PIN11']); ?>
				<fieldset class="fieldset white">
					<legend><?php __('plugin_invoice_billing_info'); ?></legend>
					<p>
						<label class="title"><?php __('plugin_invoice_i_billing_address'); ?></label>
						<span class="left">
							<input type="text" name="b_billing_address" id="b_billing_address" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_billing_address'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_company'); ?></label>
						<span class="left">
							<input type="text" name="b_company" id="b_company" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_company'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_name'); ?></label>
						<span class="left">
							<input type="text" name="b_name" id="b_name" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_name'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_address'); ?></label>
						<span class="left">
							<input type="text" name="b_address" id="b_address" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_address'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_street_address'); ?></label>
						<span class="left">
							<input type="text" name="b_street_address" id="b_street_address" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_street_address'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_country'); ?></label>
						<span class="left">
							<select name="b_country" class="form-field w500">
								<option value="">----</option>
								<?php
								foreach ($tpl['country_arr'] as $country)
								{
									?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] == @$tpl['arr']['b_country'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_city'); ?></label>
						<span class="left">
							<input type="text" name="b_city" id="b_city" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_city'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_state'); ?></label>
						<span class="left">
							<input type="text" name="b_state" id="b_state" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_state'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_zip'); ?></label>
						<span class="left">
							<input type="text" name="b_zip" id="b_zip" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_zip'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_phone'); ?></label>
						<span class="form-field-custom form-field-custom-before">
							<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
							<input type="text" name="b_phone" id="b_phone" class="form-field" style="width: 473px" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_phone'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_fax'); ?></label>
						<span class="form-field-custom form-field-custom-before">
							<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
							<input type="text" name="b_fax" id="b_fax" class="form-field" style="width: 473px" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_fax'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_email'); ?></label>
						<span class="form-field-custom form-field-custom-before">
							<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
							<input type="text" name="b_email" id="b_email" class="form-field email" style="width: 473px" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_email'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('plugin_invoice_i_url'); ?></label>
						<span class="form-field-custom form-field-custom-before">
							<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
							<input type="text" name="b_url" id="b_url" class="form-field" style="width: 473px" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['b_url'])); ?>" />
						</span>
					</p>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button float_left align_middle" />
					</p>
				</fieldset>
				<?php
				if ((int) $tpl['config_arr']['si_include'] === 1)
				{
					?>
					<fieldset class="fieldset white">
						<legend><?php __('plugin_invoice_shipping_info'); ?></legend>
						<p>
							<label class="title"><?php __('plugin_invoice_i_is_shipped'); ?></label>
							<span class="left">
								<input type="checkbox" name="s_is_shipped" value="1"<?php echo (int) $tpl['arr']['s_is_shipped'] === 1 ? ' checked="checked"' : NULL; ?> />
							</span>
						</p>						
						<p>
							<label class="title"><?php __('plugin_invoice_i_shipping_date'); ?></label>
							<span class="form-field-custom form-field-custom-after">
								<input type="text" name="s_date" id="s_date" class="form-field w80 datepick pointer" readonly="readonly" value="<?php echo UtilComponent::formatDate(@$tpl['arr']['s_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>"  rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
								<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_shipping_terms'); ?></label>
							<span class="left">
								<textarea name="s_terms" id="s_terms" class="form-field" style="width: 500px; height: 220px"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_terms'])); ?></textarea>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_shipping_address'); ?></label>
							<span class="left">
								<input type="text" name="s_shipping_address" id="s_shipping_address" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_shipping_address'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_company'); ?></label>
							<span class="left">
								<input type="text" name="s_company" id="s_company" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_company'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_name'); ?></label>
							<span class="left">
								<input type="text" name="s_name" id="s_name" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_name'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_address'); ?></label>
							<span class="left">
								<input type="text" name="s_address" id="s_address" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_address'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_street_address'); ?></label>
							<span class="left">
								<input type="text" name="s_street_address" id="s_street_address" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_street_address'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_country'); ?></label>
							<span class="left">
								<select name="s_country" class="form-field w500">
									<option value="">----</option>
									<?php
									foreach ($tpl['country_arr'] as $country)
									{
										?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] == @$tpl['arr']['s_country'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
									}
									?>
								</select>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_city'); ?></label>
							<span class="left">
								<input type="text" name="s_city" id="s_city" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_city'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_state'); ?></label>
							<span class="left">
								<input type="text" name="s_state" id="s_state" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_state'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_zip'); ?></label>
							<span class="left">
								<input type="text" name="s_zip" id="s_zip" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_zip'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_phone'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
								<input type="text" name="s_phone" id="s_phone" class="form-field" style="width: 473px;" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_phone'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_fax'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
								<input type="text" name="s_fax" id="s_fax" class="form-field" style="width: 473px;" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_fax'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_email'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
								<input type="text" name="s_email" id="s_email" class="form-field email" style="width: 473px;" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_email'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_url'); ?></label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
								<input type="text" name="s_url" id="s_url" class="form-field" style="width: 473px;" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['s_url'])); ?>" />
							</span>
						</p>
						<p>
							<label class="title">&nbsp;</label>
							<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button float_left align_middle" />
						</p>
					</fieldset>
					<?php
				}
				if ((int) $tpl['config_arr']['p_accept_payments'] === 1 && $tpl['arr']['payment_method'] == 'creditcard' && (int) $tpl['config_arr']['p_accept_creditcard'] === 1)
				{
					?>
					<fieldset class="fieldset white">
						<legend><?php __('plugin_invoice_i_cc_details'); ?></legend>
						<p>
							<label class="title"><?php __('plugin_invoice_i_cc_type'); ?></label>
							<span class="inline_block">
								<select name="cc_type" class="form-field w150">
									<option value="">----</option>
									<?php
									foreach(__('plugin_invoice_cc_types') as $k => $v)
									{
										?><option value="<?php echo $k?>"<?php echo $tpl['arr']['payment_method'] == 'creditcard' && $tpl['arr']['cc_type'] == strtolower($k) ? ' selected="selected"' : null;?>><?php echo $v;?></option><?php
									} 
									?>
								</select>
							</span>
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_cc_num');?></label>
							<input type="text" name="cc_num" class="form-field w150" value="<?php echo $tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_num'] : null;?>"/>					
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_cc_code');?></label>
							<input type="text" name="cc_code" class="form-field w150" value="<?php echo $tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_code'] : null;?>"/>					
						</p>
						<p>
							<label class="title"><?php __('plugin_invoice_i_cc_exp');?></label>
							<?php
							$month = $tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_exp_month'] : null;
							$year = $tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_exp_year'] : null;
							echo TimeComponent::factory()
								->attr('name', 'cc_exp_month')
								->attr('class', 'form-field')
								->prop('format', 'M')
								->prop('selected', $month)
								->month();
							?>
							<?php
							echo TimeComponent::factory()
								->attr('name', 'cc_exp_year')
								->attr('class', 'form-field')
								->prop('left', 0)
								->prop('right', 10)
								->prop('selected', $year)
								->year();
							?>			
						</p>
						<p>
							<label class="title">&nbsp;</label>
							<input type="submit" value="<?php __('plugin_invoice_save'); ?>" class="button float_left align_middle" />
						</p>
					</fieldset>
					<?php
				}
				?>
			</div><!-- tabs-1 -->
			<div id="tabs-3">
			</div><!-- tabs-1 -->
		</div>
	</form>
	
	<form action="<?php echo BASE_URL; ?>index.php" method="get" target="_blank" style="display: none" id="frmPluginInvoicePrint">
		<input type="hidden" name="controller" value="Invoice" />
		<input type="hidden" name="action" value="Prints" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['uuid']; ?>" />
		<input type="hidden" name="uuid" value="<?php echo $tpl['arr']['order_id']; ?>" />
	</form>
	
	<form action="<?php echo BASE_URL; ?>index.php" method="get" target="_blank" style="display: none" id="frmPluginInvoiceView">
		<input type="hidden" name="controller" value="Invoice" />
		<input type="hidden" name="action" value="View" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['uuid']; ?>" />
		<input type="hidden" name="uuid" value="<?php echo $tpl['arr']['order_id']; ?>" />
	</form>
	
	<div id="dialogAddItem" style="display: none" title="<?php __('plugin_invoice_add_item_title'); ?>"></div>
	<div id="dialogEditItem" style="display: none" title="<?php __('plugin_invoice_edit_item_title'); ?>"></div>
	<div id="dialogSendInvoice" style="display: none" title="<?php __('plugin_invoice_send_invoice_title'); ?>"></div>
	
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.qty_is_int = <?php echo (int) @$tpl['config_arr']['o_qty_is_int'] === 1 ? 'true' : 'false'; ?>;
	Grid.o_use_qty_unit_price = <?php echo (int) @$tpl['config_arr']['o_use_qty_unit_price'] === 1 ? 'true' : 'false'; ?>;
	var myLabel = myLabel || {};
	myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
	myLabel.btn_save = "<?php __('plugin_invoice_save'); ?>";
	myLabel.btn_update = "<?php __('btnUpdate'); ?>";
	myLabel.btn_send = "<?php __('btnSend'); ?>";
	myLabel.i_item = "<?php __('plugin_invoice_i_item'); ?>";
	myLabel.i_qty = "<?php __('plugin_invoice_i_qty'); ?>";
	myLabel.i_unit = "<?php __('plugin_invoice_i_unit'); ?>";
	myLabel.i_amount = "<?php __('plugin_invoice_i_amount'); ?>";
	myLabel.uuid_exists = "<?php __('plugin_invoice_i_uuid_exists'); ?>";
	</script>
	<?php
}
?>