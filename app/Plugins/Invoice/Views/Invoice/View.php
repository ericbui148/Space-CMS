<?php
use Core\Framework\Components\TimeComponent;
?>
<!doctype html>
<html>
	<head>
		<title><?php __('plugin_invoice_menu_invoices'); ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9" />
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.$css['path'].$css['file'].'" />';
		}
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : BASE_URL).$js['path'].htmlspecialchars($js['file']).'"></script>';
		}
		?>
	</head>
	<body>
		<div id="container">
		<?php echo $tpl['template']; ?>
		<br/><br/><br/><br/>
		<?php
		if ($tpl['arr']['status'] == 'not_paid' && (int) $tpl['config_arr']['p_accept_payments'] === 1 && (
			(int) $tpl['config_arr']['p_accept_paypal'] === 1 || (int) $tpl['config_arr']['p_accept_authorize'] === 1 ||
			(int) $tpl['config_arr']['p_accept_creditcard'] === 1 || (int) $tpl['config_arr']['p_accept_cash'] === 1 || (int) $tpl['config_arr']['p_accept_bank'] === 1
		) && (float) $tpl['arr']['total'] > 0)
		{
			?>
			<form id="frmInvoicePayment" action="<?php echo BASE_URL; ?>index.php?controller=Invoice&amp;action=Payment" method="post">
				<input type="hidden" name="payment_post" value="1" />
				<input type="hidden" name="uuid" value="<?php echo $tpl['arr']['uuid']; ?>" />
				<?php
				$checked = false;
				if ((int) $tpl['config_arr']['p_accept_paypal'] === 1)
				{
					?><p><label><input type="radio" name="payment_method" value="paypal"<?php echo $tpl['arr']['payment_method']=='paypal' ? ' checked="checked"' : null; ?> /> <?php __('plugin_invoice_pay_with_paypal'); ?></label></p><?php
				}
				if ((int) $tpl['config_arr']['p_accept_authorize'] === 1)
				{
					?><p><label><input type="radio" name="payment_method" value="authorize"<?php echo $tpl['arr']['payment_method']=='authorize' ? ' checked="checked"' : null; ?> /> <?php __('plugin_invoice_pay_with_authorize'); ?></label></p><?php
				}
				if ((int) $tpl['config_arr']['p_accept_creditcard'] === 1)
				{
					?>
					<p><label><input type="radio" name="payment_method" value="creditcard"<?php echo $tpl['arr']['payment_method']=='creditcard' ? ' checked="checked"' : null; ?> /> <?php __('plugin_invoice_pay_with_creditcard'); ?></label></p>
					<div class="boxCC" style="padding-left: 24px;display:<?php echo $tpl['arr']['payment_method'] == 'creditcard' ? 'block' : 'none';?>">
						<p>
							<label style="display: block;float:left;width: 100px;"><?php __('plugin_invoice_i_cc_type');?></label>
							<select name="cc_type">
								<?php
								foreach(__('plugin_invoice_cc_types') as $k => $v)
								{
									?><option value="<?php echo $k?>"<?php echo $controller->isLoged() ? ($tpl['arr']['payment_method'] == 'creditcard' && $tpl['arr']['cc_type'] == $k ? ' selected="selected"' : null): null;?>><?php echo $v;?></option><?php
								} 
								?>
							</select>					
						</p>
						<p>
							<label style="display: block;float:left;width: 100px;"><?php __('plugin_invoice_i_cc_num');?></label>
							<input type="text" name="cc_num" value="<?php echo $controller->isLoged() ? ($tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_num'] : null): null;?>" style="width: 150px;"/>					
						</p>
						<p>
							<label style="display: block;float:left;width: 100px;"><?php __('plugin_invoice_i_cc_code');?></label>
							<input type="text" name="cc_code" value="<?php echo $controller->isLoged() ? ($tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_code'] : null): null;?>" style="width: 150px;"/>					
						</p>
						<p>
							<label style="display: block;float:left;width: 100px;"><?php __('plugin_invoice_i_cc_exp');?></label>
							<?php
							$month = $controller->isLoged() ? ($tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_exp_month'] : null) : null;
							$year = $controller->isLoged() ? ($tpl['arr']['payment_method'] == 'creditcard' ? $tpl['arr']['cc_exp_year'] : null) : null;
							echo TimeComponent::factory()
								->attr('name', 'cc_exp_month')
								->prop('format', 'M')
								->prop('selected', $month)
								->month();
							?>
							<?php
							echo TimeComponent::factory()
								->attr('name', 'cc_exp_year')
								->prop('left', 0)
								->prop('right', 10)
								->prop('selected', $year)
								->year();
							?>			
						</p>
					</div>
					<?php
				}
				if ((int) $tpl['config_arr']['p_accept_cash'] === 1)
				{
					?><p><label><input type="radio" name="payment_method" value="cash"<?php echo $tpl['arr']['payment_method']=='cash' ? ' checked="checked"' : null; ?> /> <?php __('plugin_invoice_pay_with_cash'); ?></label></p><?php
				}
				if ((int) $tpl['config_arr']['p_accept_bank'] === 1)
				{
					?><p><label><input type="radio" name="payment_method" value="bank"<?php echo $tpl['arr']['payment_method']=='bank' ? ' checked="checked"' : null; ?> /> <?php __('plugin_invoice_pay_with_bank'); ?></label></p><?php
				}
				?>
				<input type="submit" value="<?php __('plugin_invoice_pay_now'); ?>" />
			</form>
			<?php
		}
		?>
		</div>
	</body>
</html>