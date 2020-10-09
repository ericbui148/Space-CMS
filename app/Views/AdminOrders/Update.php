<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\AppController;
use Core\Framework\Objects;
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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders&amp;action=Index"><?php __('menuOrders'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Invoice&amp;action=Invoices"><?php __('plugin_invoice_menu_invoices'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders&amp;action=Update&amp;id=<?php echo $tpl['arr']['id']; ?>"><?php __('order_update'); ?></a></li>
		</ul>
	</div>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders&amp;action=Update" method="post" class="form form" id="frmUpdateOrder">
		<input type="hidden" name="update_form" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('order_tab_order'); ?></a></li>
				<li><a href="#tabs-2"><?php __('order_tab_client'); ?></a></li>
				<li><a href="#tabs-3"><?php __('order_tab_shipping'); ?></a></li>
				<li><a href="#tabs-4"><?php __('order_tab_invoices'); ?></a></li>
			</ul>
			
			<div id="tabs-1">
				<?php UtilComponent::printNotice(@$titles['AO10'], @$bodies['AO10']); ?>
				<fieldset class="fieldset white">
					<legend><?php __('order_general'); ?></legend>
					
					<div class="overflow pt5 b5">
						<label class="title">&nbsp;</label>
						<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders" data-id="<?php echo $tpl['arr']['id']; ?>" class="button btn-confirm"><?php __('order_send_confirm'); ?></a>
						<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders" data-id="<?php echo $tpl['arr']['id']; ?>" class="button btn-payment"><?php __('order_send_payment'); ?></a>
					</div>
					
					<div class="float_left">
						<p>
							<label class="title"><?php __('order_created'); ?>:</label>
							<span class="left"><?php echo date($tpl['option_arr']['o_datetime_format'], strtotime($tpl['arr']['created'])); ?></span>
						</p>
						<p>
							<label class="title"><?php __('order_uuid'); ?>:</label>
							<input type="text" name="uuid" id="uuid" class="form-field w100" value="<?php echo SanitizeComponent::html($tpl['arr']['uuid']); ?>" />
						</p>
						<p>
							<label class="title"><?php __('order_status'); ?>:</label>
							<select name="status" id="status" class="form-field">
								<option value=""><?php __('order_choose'); ?></option>
								<?php
								foreach (__('order_statuses', true) as $k => $v)
								{
									?><option value="<?php echo $k; ?>"<?php echo $tpl['arr']['status'] == $k ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</p>
						<p>
							<label class="title"><?php __('order_payment'); ?>:</label>
							<select name="payment_method" id="payment_method" class="form-field">
								<option value=""><?php __('order_choose'); ?></option>
								<?php
								foreach (__('payment_methods', true) as $k => $v)
								{
									?><option value="<?php echo $k; ?>"<?php echo $tpl['arr']['payment_method'] == $k ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</p>
						<p class="sscCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
							<label class="title"><?php __('bf_cc_type'); ?></label>
							<span class="inline_block">
								<select name="cc_type" class="form-field w140">
									<option value="">---</option>
									<?php
									foreach (__('cc_types', true) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php echo $k != $tpl['arr']['cc_type'] ? NULL : ' selected="selected"'; ?>><?php echo $v; ?></option><?php
									}
									?>
								</select>
							</span>
						</p>
						<p class="sscCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
							<label class="title"><?php __('bf_cc_num'); ?></label>
							<span class="inline_block">
								<input type="text" name="cc_num" id="cc_num" class="form-field w120 digits" value="<?php echo SanitizeComponent::html($tpl['arr']['cc_num']); ?>" />
							</span>
						</p>
						<p class="sscCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
							<label class="title"><?php __('bf_cc_sec'); ?></label>
							<span class="inline_block">
								<input type="text" name="cc_code" id="cc_code" class="form-field w120 digits" value="<?php echo SanitizeComponent::html($tpl['arr']['cc_code']); ?>" />
							</span>
						</p>
						<p class="sscCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
							<label class="title"><?php __('bf_cc_exp'); ?></label>
							<span class="inline_block">
								<?php
								echo TimeComponent::factory()
									->attr('name', 'cc_exp_month')
									->attr('id', 'cc_exp_month')
									->attr('class', 'form-field')
									->prop('format', 'M')
									->prop('selected', $tpl['arr']['cc_exp_month'])
									->month();
								?>
								<?php
								echo TimeComponent::factory()
									->attr('name', 'cc_exp_year')
									->attr('id', 'cc_exp_year')
									->attr('class', 'form-field')
									->prop('left', 0)
									->prop('right', 10)
									->prop('selected', $tpl['arr']['cc_exp_year'])
									->year();
								?>
							</span>
						</p>
						<p>
							<label class="title"><?php __('order_voucher'); ?>:</label>
							<input type="text" name="voucher" id="voucher" class="form-field w100" value="<?php echo SanitizeComponent::html($tpl['arr']['voucher']); ?>" />
						</p>
						<p>
							<label class="title"><?php __('order_shipping_location'); ?></label>
							<select name="tax_id" class="form-field w150">
								<option value=""><?php __('order_choose'); ?></option>
								<?php
								foreach ($tpl['tax_arr'] as $item)
								{
									?><option value="<?php echo $item['id']; ?>"<?php echo $tpl['arr']['tax_id'] != $item['id'] ? NULL : ' selected="selected"'; ?>><?php echo SanitizeComponent::html($item['location']); ?></option><?php
								}
								?>
							</select>
						</p>
					</div>
					<div class="float_right">
						<p>
							<label class="title"><?php __('order_price'); ?>:</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
								<input type="text" name="price" id="price" class="form-field number w80" value="<?php echo number_format(@$tpl['arr']['price'], 2, ".", ""); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('order_discount'); ?>:</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
								<input type="text" name="discount" id="discount" class="form-field number w80" value="<?php echo number_format(@$tpl['arr']['discount'], 2, ".", ""); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('order_insurance'); ?>:</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
								<input type="text" name="insurance" id="insurance" class="form-field number w80" value="<?php echo number_format(@$tpl['arr']['insurance'], 2, ".", ""); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('order_shipping'); ?>:</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
								<input type="text" name="shipping" id="shipping" class="form-field number w80" value="<?php echo number_format(@$tpl['arr']['shipping'], 2, ".", ""); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('order_tax'); ?>:</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
								<input type="text" name="tax" id="tax" class="form-field number w80" value="<?php echo number_format(@$tpl['arr']['tax'], 2, ".", ""); ?>" />
							</span>
						</p>
						<p>
							<label class="title"><?php __('order_total'); ?>:</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
								<input type="text" name="total" id="total" class="form-field number w80" value="<?php echo number_format(@$tpl['arr']['total'], 2, ".", ""); ?>" />
							</span>
						</p>
					</div>
					<br class="clear_both" />
					<p>
						<label class="title"><?php __('order_notes'); ?>:</label>
						<textarea name="notes" id="notes" class="form-field w500 h100"><?php echo SanitizeComponent::html($tpl['arr']['notes']); ?></textarea>
					</p>
					<div class="p">
						<label class="title"><?php __('order_products'); ?>:</label>
						<div id="boxStockProducts"></div>
						
						<div id="dialogStockDelete" title="Delete confirmation" style="display: none">Are you sure you want to delete selected stock?</div>
						<div id="dialogStockEdit" title="Edit Stock" style="display: none"></div>
						<div id="dialogStockAdd" title="Add Stock" style="display: none"></div>
					</div>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
						<input type="button" value="<?php __('btnAddProduct');?>" class="button stock-add" />
						<input type="button" value="<?php __('btnRecalcualteThePrice');?>" class="button order-calc" />
					</p>
				</fieldset>
			</div>
			<div id="tabs-2">
				<?php UtilComponent::printNotice(@$titles['AO11'], @$bodies['AO11']); ?>
				<fieldset class="fieldset white">
					<legend><?php __('order_customer'); ?></legend>
					<p>
						<label class="title"><?php __('order_client'); ?>:</label>
						<span class="float_left r5">
							<select name="client_id" id="client_id" class="form-field w200 custom-chosen">
								<option value=""><?php __('order_choose'); ?></option>
								<?php
								foreach ($tpl['client_arr'] as $client)
								{
									?><option value="<?php echo $client['id']; ?>"<?php echo $client['id'] == $tpl['arr']['client_id'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($client['client_name']); ?></option><?php
								}
								?>
							</select>
						</span>
						<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients&action=Update&id=<?php echo $tpl['arr']['client_id']; ?>" class="icon-edit"></a>
					</p>
					<div id="boxClient">
					<?php
					if ($tpl['arr']['client_id'] > 0)
					{
						?>
						<p>
							<label class="title"><?php __('order_email'); ?>:</label>
							<span class="left"><?php echo SanitizeComponent::html($tpl['arr']['client_email']); ?></span>
						</p>
						<p>
							<label class="title"><?php __('order_phone'); ?>:</label>
							<span class="left"><?php echo SanitizeComponent::html($tpl['arr']['client_phone']); ?></span>
						</p>
						<p>
							<label class="title"><?php __('order_url'); ?>:</label>
							<span class="left"><?php echo SanitizeComponent::html($tpl['arr']['client_url']); ?></span>
						</p>
						<?php
					}
					?>
					</div>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
					</p>
				</fieldset>
				
				<fieldset class="fieldset white" style="position: static">
					<legend><?php __('order_all_list'); ?></legend>
					<div id="grid_client_orders"></div>
				</fieldset>
			</div>
			<div id="tabs-3">
				<?php UtilComponent::printNotice(@$titles['AO12'], @$bodies['AO12']); ?>
				<fieldset class="fieldset white">
					<legend><?php __('client_address_book'); ?></legend>
					<div id="boxAddressBook">
					<?php
					if ($tpl['arr']['client_id'] > 0)
					{
						?>
						<p>
							<label class="title"><?php __('order_address'); ?>:</label>
							<?php
							if(count($tpl['address_arr']))
							{ 
								?>
								<select name="address_id" id="address_id" class="form-field w200">
									<option value=""><?php __('order_choose'); ?></option>
									<?php
									$disabled = ' disabled="disabled"';
									foreach ($tpl['address_arr'] as $address)
									{
										$selected = NULL;
										if ($address['id'] == $tpl['arr']['address_id'])
										{
											$selected = ' selected="selected"';
											$disabled = NULL;
										}
										?><option value="<?php echo $address['id']; ?>"<?php echo $selected; ?>><?php echo SanitizeComponent::html($address['name']); ?></option><?php
									}
									?>
								</select>
								<input type="button" value="<?php __('order_copy_b'); ?>" class="button btnCopy btnCopyBilling"<?php echo $disabled; ?> />
								<input type="button" value="<?php __('order_copy_s'); ?>" class="button btnCopy btnCopyShipping"<?php echo $disabled; ?> />
								<?php
							}else{
								$no_address_book = __('lblNoAddressBook', true);
								$no_address_book = str_replace("[STAG]", '<a href="'.$_SERVER['PHP_SELF'].'?controller=AdminClients&amp;action=Index">', $no_address_book);
								$no_address_book = str_replace("[ETAG]", '</a>', $no_address_book);
								?><label class="content"><?php echo $no_address_book;?></label><?php
							} 
							?>
						</p>
						<div id="boxAddress">
						<?php
						foreach ($tpl['address_arr'] as $address)
						{
							if ($address['id'] == $tpl['arr']['address_id'])
							{
								$tpl['address_arr'] = $address;
								include dirname(__FILE__) . '/GetAddress.php';
								break;
							}
						}
						?>
						</div>
						<?php
					}
					?>
					</div>
				</fieldset>
				<fieldset class="fieldset white">
					<legend><?php __('order_billing_details'); ?></legend>
					<div class="float_left w360">
						<p>
							<label class="title"><?php __('order_country'); ?>:</label>
							<select name="b_country_id" id="b_country_id" class="form-field w180 custom-chosen">
								<option value=""><?php __('order_choose'); ?></option>
								<?php
								foreach ($tpl['country_arr'] as $country)
								{
									?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] == $tpl['arr']['b_country_id'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
								}
								?>
							</select>
						</p>
						<p>
							<label class="title"><?php __('order_state'); ?>:</label>
							<input type="text" name="b_state" id="b_state" class="form-field w180" value="<?php echo SanitizeComponent::html($tpl['arr']['b_state']); ?>" />
						</p>
					</div>
					<div class="float_right w350">
						<p>
							<label class="title"><?php __('order_city'); ?>:</label>
							<input type="text" name="b_city" id="b_city" class="form-field w160" value="<?php echo SanitizeComponent::html($tpl['arr']['b_city']); ?>" />
						</p>
						<p>
							<label class="title"><?php __('order_zip'); ?>:</label>
							<input type="text" name="b_zip" id="b_zip" class="form-field w80" value="<?php echo SanitizeComponent::html($tpl['arr']['b_zip']); ?>" />
						</p>
					</div>
					<br class="clear_both" />
					<p>
						<label class="title"><?php __('order_name'); ?>:</label>
						<input type="text" name="b_name" id="b_name" class="form-field w300" value="<?php echo SanitizeComponent::html($tpl['arr']['b_name']); ?>" />
					</p>
					<p>
						<label class="title"><?php __('order_address_1'); ?>:</label>
						<input type="text" name="b_address_1" id="b_address_1" class="form-field w500" value="<?php echo SanitizeComponent::html($tpl['arr']['b_address_1']); ?>" />
					</p>
					<p>
						<label class="title"><?php __('order_address_2'); ?>:</label>
						<input type="text" name="b_address_2" id="b_address_2" class="form-field w500" value="<?php echo SanitizeComponent::html($tpl['arr']['b_address_2']); ?>" />
					</p>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
					</p>
				</fieldset>
				
				<fieldset class="fieldset white">
					<legend><?php __('order_shipping_details'); ?></legend>
					<?php
					$isSame = false;
					if ((int) $tpl['arr']['same_as'] === 1)
					{
						$isSame = true;
					}
					?>
					<p>
						<input type="checkbox" name="same_as" id="same_as" value="1"<?php echo $isSame ? ' checked="checked"' : NULL; ?> /> <label for="same_as"><?php __('order_same'); ?></label>
					</p>
					<div class="boxSame" style="display: <?php echo $isSame ? 'none' : NULL; ?>">
						<div class="float_left w360">
							<p>
								<label class="title"><?php __('order_country'); ?>:</label>
								<select name="s_country_id" id="s_country_id" class="form-field w180 custom-chosen">
									<option value=""><?php __('order_choose'); ?></option>
									<?php
									foreach ($tpl['country_arr'] as $country)
									{
										?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] == $tpl['arr']['s_country_id'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
									}
									?>
								</select>
							</p>
							<p>
								<label class="title"><?php __('order_state'); ?>:</label>
								<input type="text" name="s_state" id="s_state" class="form-field w180" value="<?php echo SanitizeComponent::html($tpl['arr']['s_state']); ?>" />
							</p>
						</div>
						<div class="float_right w350">
							<p>
								<label class="title"><?php __('order_city'); ?>:</label>
								<input type="text" name="s_city" id="s_city" class="form-field w160" value="<?php echo SanitizeComponent::html($tpl['arr']['s_city']); ?>" />
							</p>
							<p>
								<label class="title"><?php __('order_zip'); ?>:</label>
								<input type="text" name="s_zip" id="s_zip" class="form-field w80" value="<?php echo SanitizeComponent::html($tpl['arr']['s_zip']); ?>" />
							</p>
						</div>
						<br class="clear_both" />
						<p>
							<label class="title"><?php __('order_name'); ?>:</label>
							<input type="text" name="s_name" id="s_name" class="form-field w300" value="<?php echo SanitizeComponent::html($tpl['arr']['s_name']); ?>" />
						</p>
						<p>
							<label class="title"><?php __('order_address_1'); ?>:</label>
							<input type="text" name="s_address_1" id="s_address_1" class="form-field w500" value="<?php echo SanitizeComponent::html($tpl['arr']['s_address_1']); ?>" />
						</p>
						<p>
							<label class="title"><?php __('order_address_2'); ?>:</label>
							<input type="text" name="s_address_2" id="s_address_2" class="form-field w500" value="<?php echo SanitizeComponent::html($tpl['arr']['s_address_2']); ?>" />
						</p>
					</div>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
					</p>
				</fieldset>
			</div>
			<div id="tabs-4">
				<?php
				if (Objects::getPlugin('Invoice') !== NULL)
				{
					?>
					
					<input type="button" class="button btnCreateInvoice" value="<?php __('order_create_invoice'); ?>" />
					
					<div id="grid_invoices" class="t10 b10"></div>
				
					<?php
				}
				?>
			</div>
		</div>
	</form>
	
	<?php
	if (Objects::getPlugin('Invoice') !== NULL)
	{
		$map = array(
			'completed' => 'paid',
			'pending' => 'not_paid',
			'new' => 'not_paid',
			'cancelled' => 'cancelled'
		);
		?>
		<form action="<?php echo BASE_URL; ?>index.php" method="get" target="_blank" style="display: inline" id="frmCreateInvoice">
			<input type="hidden" name="controller" value="Invoice" />
			<input type="hidden" name="action" value="CreateInvoice" />
			<input type="hidden" name="tmp" value="<?php echo md5(uniqid(rand(), true)); ?>" />
			<input type="hidden" name="uuid" value="<?php echo UtilComponent::uuid(); ?>" />
			<input type="hidden" name="order_id" value="<?php echo SanitizeComponent::html($tpl['arr']['uuid']); ?>" />
			<input type="hidden" name="issue_date" value="<?php echo date('Y-m-d'); ?>" />
			<input type="hidden" name="due_date" value="<?php echo date('Y-m-d'); ?>" />
			<input type="hidden" name="status" value="<?php echo @$map[$tpl['arr']['status']]; ?>" />
			<input type="hidden" name="subtotal" value="<?php echo $tpl['arr']['price'] + $tpl['arr']['insurance'] + $tpl['arr']['shipping']; ?>" />
			<input type="hidden" name="discount" value="<?php echo $tpl['arr']['discount']; ?>" />
			<input type="hidden" name="tax" value="<?php echo $tpl['arr']['tax']; ?>" />
			<input type="hidden" name="shipping" value="<?php echo $tpl['arr']['shipping']; ?>" />
			<input type="hidden" name="total" value="<?php echo $tpl['arr']['total']; ?>" />
			<input type="hidden" name="paid_deposit" value="0.00" />
			<input type="hidden" name="amount_due" value="0.00" />
			<input type="hidden" name="currency" value="<?php echo SanitizeComponent::html($tpl['option_arr']['o_currency']); ?>" />
			<input type="hidden" name="notes" value="<?php echo SanitizeComponent::html($tpl['arr']['notes']); ?>" />
			<input type="hidden" name="b_billing_address" value="<?php echo SanitizeComponent::html($tpl['arr']['b_address_1']); ?>" />
			<input type="hidden" name="b_name" value="<?php echo SanitizeComponent::html($tpl['arr']['b_name']); ?>" />
			<input type="hidden" name="b_address" value="<?php echo SanitizeComponent::html($tpl['arr']['b_address_1']); ?>" />
			<input type="hidden" name="b_street_address" value="<?php echo SanitizeComponent::html($tpl['arr']['b_address_2']); ?>" />
			<input type="hidden" name="b_city" value="<?php echo SanitizeComponent::html($tpl['arr']['b_city']); ?>" />
			<input type="hidden" name="b_state" value="<?php echo SanitizeComponent::html($tpl['arr']['b_state']); ?>" />
			<input type="hidden" name="b_zip" value="<?php echo SanitizeComponent::html($tpl['arr']['b_zip']); ?>" />
			<input type="hidden" name="b_phone" value="<?php echo SanitizeComponent::html($tpl['arr']['client_phone']); ?>" />
			<input type="hidden" name="b_email" value="<?php echo SanitizeComponent::html($tpl['arr']['client_email']); ?>" />
			<input type="hidden" name="b_url" value="<?php echo SanitizeComponent::html($tpl['arr']['client_url']); ?>" />
			<input type="hidden" name="s_shipping_address" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_address_1'] : $tpl['arr']['s_address_1']); ?>" />
			<input type="hidden" name="s_name" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_name'] : $tpl['arr']['s_name']); ?>" />
			<input type="hidden" name="s_address" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_address_1'] : $tpl['arr']['s_address_1']); ?>" />
			<input type="hidden" name="s_street_address" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_address_2'] : $tpl['arr']['s_address_2']); ?>" />
			<input type="hidden" name="s_city" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_city'] : $tpl['arr']['s_city']); ?>" />
			<input type="hidden" name="s_state" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_state'] : $tpl['arr']['s_state']); ?>" />
			<input type="hidden" name="s_zip" value="<?php echo SanitizeComponent::html((int) $tpl['arr']['same_as'] === 1 ? $tpl['arr']['b_zip'] : $tpl['arr']['s_zip']); ?>" />
			<input type="hidden" name="s_phone" value="<?php echo SanitizeComponent::html($tpl['arr']['client_phone']); ?>" />
			<input type="hidden" name="s_email" value="<?php echo SanitizeComponent::html($tpl['arr']['client_email']); ?>" />
			<input type="hidden" name="s_url" value="<?php echo SanitizeComponent::html($tpl['arr']['client_url']); ?>" />
			
			<?php
			if (isset($tpl['os_arr']) && !empty($tpl['os_arr']))
			{
				$total = 0;
				foreach ($tpl['os_arr'] as $i => $item)
				{
					$desc = array();
					$extra_price = 0;
					if (isset($item['attr']) && !empty($item['attr']))
					{
						$at = array();
						$a = explode(",", $item['attr']);
						foreach ($a as $v)
						{
							$t = explode("_", $v);
							$at[$t[1]] = $t[0];
						}
						foreach ($at as $attr_parent_id => $attr_id)
						{
							foreach ($tpl['attr_arr'] as $attr)
							{
								if ($attr['id'] == $attr_parent_id)
								{
									foreach ($attr['child'] as $child)
									{
										if ($child['id'] == $attr_id)
										{
											$desc[] = sprintf('%s: %s', $attr['name'], SanitizeComponent::html($child['name']));
											break;
										}
									}
								}
							}
						}
					}
					//Extras
					if (isset($item['extra']) && !empty($item['extra']))
					{
						$a = explode(",", $item['extra']);
						foreach ($a as $eid)
						{
							if (strpos($eid, ".") === FALSE)
							{
								//single
								foreach ($tpl['extra_arr'] as $extra)
								{
									if ($extra['id'] == $eid)
									{
										$desc[] = sprintf('Extra: %s (%s)', $extra['name'], UtilComponent::formatCurrencySign(number_format($extra['price'], 2), $tpl['option_arr']['o_currency']));
										$extra_price += $extra['price'];
										break;
									}
								}
							} else {
								//multi
								list($e_id, $ei_id) = explode(".", $eid);
								foreach ($tpl['extra_arr'] as $extra)
								{
									if ($extra['id'] == $e_id && isset($extra['extra_items']) && !empty($extra['extra_items']))
									{
										foreach ($extra['extra_items'] as $extra_item)
										{
											if ($extra_item['id'] == $ei_id)
											{
												$desc[] = sprintf('Extra: %s (%s)', $extra_item['name'], UtilComponent::formatCurrencySign(number_format($extra_item['price'], 2), $tpl['option_arr']['o_currency']));
												$extra_price += $extra_item['price'];
												break;
											}
										}
										break;
									}
								}
							}
						}
					}
					$price = $item['price'] + $extra_price;
					$subtotal = $price * (int) $item['qty'];
					$total += $subtotal;
					?>
					<input type="hidden" name="items[<?php echo $i; ?>][name]" value="<?php echo SanitizeComponent::html($item['name']); ?>" />
					<input type="hidden" name="items[<?php echo $i; ?>][description]" value="<?php echo SanitizeComponent::html(join("; ", $desc)); ?>" />
					<input type="hidden" name="items[<?php echo $i; ?>][qty]" value="<?php echo (int) $item['qty']; ?>" />
					<input type="hidden" name="items[<?php echo $i; ?>][unit_price]" value="<?php echo $price; ?>" />
					<input type="hidden" name="items[<?php echo $i; ?>][amount]" value="<?php echo $subtotal; ?>" />
					<?php
				}
				?>
				<input type="hidden" name="items[<?php echo $i+1; ?>][name]" value="<?php echo SanitizeComponent::html(__('order_insurance', true)); ?>" />
				<input type="hidden" name="items[<?php echo $i+1; ?>][description]" value="" />
				<input type="hidden" name="items[<?php echo $i+1; ?>][qty]" value="1" />
				<input type="hidden" name="items[<?php echo $i+1; ?>][unit_price]" value="<?php echo $tpl['arr']['insurance']; ?>" />
				<input type="hidden" name="items[<?php echo $i+1; ?>][amount]" value="<?php echo $tpl['arr']['insurance']; ?>" />
				
				<input type="hidden" name="items[<?php echo $i+2; ?>][name]" value="<?php echo SanitizeComponent::html(__('order_shipping', true)); ?>" />
				<input type="hidden" name="items[<?php echo $i+2; ?>][description]" value="" />
				<input type="hidden" name="items[<?php echo $i+2; ?>][qty]" value="1" />
				<input type="hidden" name="items[<?php echo $i+2; ?>][unit_price]" value="<?php echo $tpl['arr']['shipping']; ?>" />
				<input type="hidden" name="items[<?php echo $i+2; ?>][amount]" value="<?php echo $tpl['arr']['shipping']; ?>" />
				<?php
			} else {
				?>
				<input type="hidden" name="items[0][name]" value="Order payment" />
				<input type="hidden" name="items[0][description]" value="" />
				<input type="hidden" name="items[0][qty]" value="1" />
				<input type="hidden" name="items[0][unit_price]" value="<?php echo $tpl['arr']['total']; ?>" />
				<input type="hidden" name="items[0][amount]" value="<?php echo $tpl['arr']['total']; ?>" />
				<?php
			}
			?>
		</form>
		<?php
	}
	$statuses = __('plugin_invoice_statuses', true);
	?>
	
	<div id="dialogDeleteAddress" style="display: none" title="<?php __('client_da_title'); ?>"><?php __('client_da_body'); ?></div>
	<div id="boxCloneAddress" style="display: none"><?php include VIEWS_PATH . 'AdminClients/elements/address.php'; ?></div>
	
	<div id="dialogConfirm" title="<?php __('order_confirm_title'); ?>" style="display: none">
		<form action="" method="post" class="form form dialogForm">
			<input type="hidden" name="form_send" value="1" />
			<input type="hidden" name="to" value="<?php echo @$tpl['to']; ?>" />
			<input type="hidden" name="from" value="<?php echo @$tpl['from']; ?>" />
			<p><label><?php __('order_send_subject'); ?></label></p>
			<p><input type="text" id="confirm_subject" name="subject" class="form-field w550 required" value="<?php echo SanitizeComponent::html(@$tpl['confirm_subject']); ?>" /></p>
			<p><label><?php __('order_send_body'); ?></label></p>
			<p><textarea id="confirm_body" name="body" class="form-field w550 h300 required mceEditorConfirm"><?php echo UtilComponent::textToHtml(@$tpl['confirm_body']); ?></textarea></p>
		</form>
	</div>
	<div id="dialogPayment" title="<?php __('order_payment_title'); ?>" style="display: none">
		<form action="" method="post" class="form form dialogForm">
			<input type="hidden" name="form_send" value="1" />
			<input type="hidden" name="to" value="<?php echo @$tpl['to']; ?>" />
			<input type="hidden" name="from" value="<?php echo @$tpl['from']; ?>" />
			<p><label><?php __('order_send_subject'); ?></label></p>
			<p><input type="text" id="payment_subject" name="subject" class="form-field w550 required" value="<?php echo SanitizeComponent::html(@$tpl['payment_subject']); ?>" /></p>
			<p><label><?php __('order_send_body'); ?></label></p>
			<p><textarea id="payment_body" name="body" class="form-field w550 h300 required mceEditorPayment"><?php echo UtilComponent::textToHtml(@$tpl['payment_body']); ?></textarea></p>
		</form>
	</div>
	
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.jqDateFormat = "<?php echo UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']); ?>";
	Grid.jsDateFormat = "<?php echo UtilComponent::jsDateFormat($tpl['option_arr']['o_date_format']); ?>";
	var myLabel = myLabel || {};
	myLabel.uuid = "<?php __('order_uuid'); ?>";
	myLabel.client = "<?php __('order_client'); ?>";
	myLabel.created = "<?php __('order_created'); ?>";
	myLabel.status = "<?php __('order_status'); ?>";
	myLabel.total = "<?php __('order_total'); ?>";
	myLabel.statuses = <?php echo AppController::jsonEncode(__('order_statuses', true)); ?>;
	myLabel.exported = "<?php __('lblExport'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";

	myLabel.num = "<?php __('plugin_invoice_i_num'); ?>";
	myLabel.order_id = "<?php __('plugin_invoice_i_order_id'); ?>";
	myLabel.issue_date = "<?php __('plugin_invoice_i_issue_date'); ?>";
	myLabel.due_date = "<?php __('plugin_invoice_i_due_date'); ?>";
	myLabel.created = "<?php __('plugin_invoice_i_created'); ?>";
	myLabel.status = "<?php __('plugin_invoice_i_status'); ?>";
	myLabel.total = "<?php __('plugin_invoice_i_total'); ?>";
	myLabel.delete_title = "<?php __('plugin_invoice_i_delete_title'); ?>";
	myLabel.delete_body = "<?php __('plugin_invoice_i_delete_body'); ?>";
	myLabel.paid = "<?php echo $statuses['paid']; ?>";
	myLabel.not_paid = "<?php echo $statuses['not_paid']; ?>";
	myLabel.cancelled = "<?php echo $statuses['cancelled']; ?>";
	myLabel.empty_date = "<?php __('gridEmptyDate'); ?>";
	myLabel.invalid_date = "<?php __('gridInvalidDate'); ?>";
	myLabel.empty_datetime = "<?php __('gridEmptyDatetime'); ?>";
	myLabel.invalid_datetime = "<?php __('gridInvalidDatetime'); ?>";
	myLabel.currency = "<?php echo $tpl['option_arr']['o_currency']; ?>";
	myLabel.currencysign = "<?php echo UtilComponent::getCurrencySign($tpl['option_arr']['o_currency'], false); ?>";
	</script>
	<?php
}
?>