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

	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	
	UtilComponent::printNotice(__('infoReportTitle', true), __('infoReportDesc', true));
	
	
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="form form">
		<input type="hidden" name="controller" value="AdminReports" />
		<input type="hidden" name="action" value="Index" />
		<p>
			<label class="float_left w180 t5"><?php __('lblFromDate'); ?></label>
			<span class="form-field-custom form-field-custom-after">
				<input type="text" name="date_from" class="form-field pointer w100 datepick" value="<?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['date_from']));?>" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>"/>
				<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
			</span>
		</p>
		<p>
			<label class="float_left w180 t5"><?php __('lblToDate'); ?></label>
			<span class="form-field-custom form-field-custom-after">
				<input type="text" name="date_to" class="form-field pointer w100 datepick" value="<?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['date_to']));?>" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>"/>
				<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
			</span>
		</p>
		<p>
			<label class="float_left w180">&nbsp;</label>
			<input type="submit" value="<?php __('btnReport', false, true); ?>" class="button" />
		</p>
	</form>
	
	<br/>
	<br/>
	<div class="form form">
		<p>
			<label class="float_left w180"><?php __('lblUpTotalOrders');?>:</label>
			<span class="float_left">
				<?php
				if($tpl['total_orders'] > 0)
				{ 
					?>
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders&action=Index&status=completed&date_from=<?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['date_from']));?>&date_to=<?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['date_to']));?>"><?php echo $tpl['total_orders'];?></a>
					<?php
				}else{
					echo $tpl['total_orders'];
				} 
				?>
			</span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblTotalAmount');?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['total_amount'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php __('lblProductsPrice');?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['sub_arr']['price'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php echo mb_strtolower(__('order_discount', true), "UTF-8");?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['sub_arr']['discount'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php echo mb_strtolower(__('order_insurance', true), "UTF-8");?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['sub_arr']['insurance'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php echo mb_strtolower(__('order_shipping', true), "UTF-8");?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['sub_arr']['shipping'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php echo mb_strtolower(__('order_tax', true), "UTF-8");?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['sub_arr']['tax'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<br/>
		<p>
			<label class="float_left w180"><?php __('lblUniqueClients');?>:</label>
			<span class="float_left">
				<?php
				if($tpl['unique_clients'] > 0)
				{ 
					?>
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients&action=Index&client_ids=<?php echo $tpl['unique_client_ids'];?>"><?php echo $tpl['unique_clients'];?></a>
					<?php
				}else{
					echo $tpl['unique_clients'];
				} 
				?>
			</span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblFirstTimeClients');?>:</label>
			<span class="float_left">
				<?php
				if($tpl['first_time_clients'] > 0)
				{ 
					?>
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients&action=Index&client_ids=<?php echo $tpl['first_time_client_ids'];?>"><?php echo $tpl['first_time_clients'];?></a>
					<?php
				}else{
					echo $tpl['first_time_clients'];
				} 
				?>
			</span>
		</p>
		<br/>
		<p>
			<label class="float_left w180"><?php __('lblAvgOrderAmount');?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['avg_amount'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblMinOrderAmount');?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['min_amount'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblMaxOrderAmount');?>:</label>
			<span class="float_left"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['max_amount'], 2, '.', ' '), $tpl['option_arr']['o_currency']);?></span>
		</p>
		<br/>
		<p>
			<label class="float_left w180"><?php __('lblAverageProductsPerOrder');?>:</label>
			<span class="float_left"><?php echo number_format($tpl['avg_product'], 2, '.', ' ');?></span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblMinProductsPerOrder');?>:</label>
			<span class="float_left"><?php echo $tpl['min_product'];?></span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblMaxProductsPerOrder');?>:</label>
			<span class="float_left"><?php echo $tpl['max_product'];?></span>
		</p>
		<p>
			<label class="float_left w180"><?php __('lblMostPopularProduct');?>:</label>
			<span class="float_left">
				<?php
				if(!empty($tpl['popular_arr']['name']))
				{ 
					?>
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&action=Update&id=<?php echo $tpl['popular_arr']['id'];?>"><?php echo SanitizeComponent::html($tpl['popular_arr']['name']);?></a> / <?php echo str_replace("{NUM}", $tpl['times'], __('lblSoldTimes', true))?>
					<?php
				}
				?>
			</span>
		</p>
	</div>
	<div class="clear_both"></div>
	<?php
}
?>