<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\Components\UtilComponent;

if (isset($tpl['os_arr']) && !empty($tpl['os_arr']))
{
	?>
	<table class="table b10 float_left" cellpadding="0" cellspacing="0" style="width: 79%">
		<thead>
			<tr>
				<th><?php __('order_p_name'); ?></th>
				<th><?php __('order_p_sku'); ?></th>
				<th class="w40 align_right"><?php __('order_p_qty'); ?></th>
				<th class="w70 align_right"><?php __('order_p_price'); ?></th>
				<th class="w70 align_right"><?php __('order_p_subtotal'); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$total = 0;
		foreach ($tpl['os_arr'] as $item)
		{
			$extra_price = 0;
			?>
			<tr>
				<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=Update&amp;id=<?php echo $item['product_id']; ?>"><?php echo SanitizeComponent::html($item['name']); ?></a>
				<?php
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
										printf('<div class="fs11"><span class="bold">%s</span>: %s</div>', SanitizeComponent::html($attr['name']), SanitizeComponent::html($child['name']));
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
									printf('<div class="fs11"><span class="bold">Extra:</span> %s (%s)</div>', SanitizeComponent::html($extra['name']), UtilComponent::formatCurrencySign(number_format($extra['price'], 2), $tpl['option_arr']['o_currency']));
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
											printf('<div class="fs11"><span class="bold">Extra:</span> %s (%s)</div>', SanitizeComponent::html($extra_item['name']), UtilComponent::formatCurrencySign(number_format($extra_item['price'], 2), $tpl['option_arr']['o_currency']));
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
				</td>
				<td><?php echo SanitizeComponent::html($item['sku']); ?></td>
				<td class="align_right"><?php echo (int) $item['qty']; ?></td>
				<td class="align_right"><?php echo UtilComponent::formatCurrencySign(number_format($price, 2), $tpl['option_arr']['o_currency']); ?></td>
				<td class="align_right"><?php echo UtilComponent::formatCurrencySign(number_format($subtotal, 2), $tpl['option_arr']['o_currency']); ?></td>
				<td class="w60">
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders" class="table-icon-edit stock-edit" data-id="<?php echo $item['id']; ?>"></a><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders" class="table-icon-delete stock-delete" data-id="<?php echo $item['id']; ?>"></a>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<?php
} else {
	?><span class="left">No stocks found</span><?php
}
?>