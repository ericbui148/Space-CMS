<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\Components\UtilComponent;

$titles = __('error_titles', true);
$bodies = __('error_bodies', true);
UtilComponent::printNotice(@$titles['AOR07'], @$bodies['AOR07']);
?>
<table class="table" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 10px">
	<thead>
		<tr>
			<?php
			if (isset($tpl['attr_arr']) && !empty($tpl['attr_arr']))
			{
				foreach ($tpl['attr_arr'] as $attr)
				{
					?><th><?php echo SanitizeComponent::html($attr['name']); ?></th><?php
				}
			}
			?>
			<th class="align_center"><?php __('order_p_qty'); ?></th>
			<th class="align_center"><?php __('order_current_stock'); ?></th>
			<th><?php __('order_unit_price'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if (isset($tpl['stock_arr']))
	{
		$cnt = isset($tpl['attr_arr']) ? count($tpl['attr_arr']) : 0;
		$xtras = !empty($tpl['extra_arr']);
		$inStock = isset($tpl['stock_attr_arr']) && !empty($tpl['stock_attr_arr']);
		if ($inStock)
		{
			?>
			<tr>
				<?php
				if (isset($tpl['attr_arr']) && !empty($tpl['attr_arr']))
				{
					foreach ($tpl['attr_arr'] as $row => $attr)
					{
						?>
						<td>
							<select name="attr[<?php echo $attr['id']; ?>]" class="form-field scSelectorAttr" data-row="<?php echo $row; ?>" data-id="<?php echo $attr['id']; ?>">
								<?php
								if (isset($attr['child']) && !empty($attr['child']))
								{
									foreach ($attr['child'] as $child_index => $child)
									{
										foreach ($tpl['stock_attr_arr'] as $stock_id => $stock)
										{
											if (in_array($child['id'], $stock) || (isset($stock[$child['parent_id']]) && (int) $stock[$child['parent_id']] === 0))
											{
												if ($row == 0 && $child_index == 0)
												{
													$tmp_stock_id = $stock_id;
												}
												if ($row > 0)
												{
													if (!isset($tpl['stock_attr_arr'][$tmp_stock_id][$child['parent_id']]) ||
														$tpl['stock_attr_arr'][$tmp_stock_id][$child['parent_id']] != $child['id'])
													{
														continue;
													}
												}
												?><option value="<?php echo $child['id']; ?>"><?php echo SanitizeComponent::html($child['name']); ?></option><?php
												break;
											}
										}
									}
								} else {
									?><option value=""><?php __('front_not_available'); ?></option><?php
								}
								?>
							</select>
						</td>
						<?php
					}
				}
				?>
				<td class="align_center"><input type="text" name="qty" value="1" class="form-field w60" data-max="<?php echo (int) $tpl['stock_arr'][0]['qty']; ?>" maxlength="<?php echo strlen($tpl['stock_arr'][0]['qty']); ?>" readonly="readonly" /></td>
				<td class="align_center"><input type="hidden" name="current_qty" value="" /><span class="scSelectorCurrentQty"></span></td>
				<td><input type="hidden" name="price" value="" /><span class="scSelectorPrice"><?php echo UtilComponent::formatCurrencySign(number_format($tpl['product_arr']['price'], 2), $tpl['option_arr']['o_currency']);?></span></td>
			</tr>
			<?php
		}
		if ($xtras)
		{
			?>
			<tr>
				<td colspan="<?php echo 3 + $cnt; ?>">
				<?php
				foreach ($tpl['extra_arr'] as $extra)
				{
					switch ($extra['type'])
					{
						case 'single':
							?>
							<div class="b5">
								<label><input type="checkbox" class="scSelectorExtra" name="extra_id[<?php echo $extra['id']; ?>]" data-price="<?php echo $extra['price'];?>" value="<?php echo $extra['type']; ?>|<?php echo $extra['price']; ?>" /> <?php echo SanitizeComponent::html($extra['name']); ?>
								(<?php echo UtilComponent::formatCurrencySign(number_format($extra['price'], 2), $tpl['option_arr']['o_currency']); ?>)</label>
							</div>
							<?php
							break;
						case 'multi':
							?><select name="extra_id[<?php echo $extra['id']; ?>]" class="form-field scSelectorExtra">
								<option value="" data-price="0">-- Select --</option>
								<?php
								foreach ($extra['extra_items'] as $k => $item)
								{
									?><option value="<?php echo $extra['type']; ?>|<?php echo $item['price']; ?>|<?php echo $item['id']; ?>" data-price="<?php echo $item['price'];?>"><?php echo SanitizeComponent::html($item['name']); ?> (<?php echo UtilComponent::formatCurrencySign(number_format($item['price'], 2), $tpl['option_arr']['o_currency']); ?>)</option><?php
								}
								?>
								</select>
							<?php
							break;
					}
				}
				?>
				</td>
			</tr>
			<?php
		}
	}
	?>
	</tbody>
</table>