<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\Components\UtilComponent;
?>
<form action="" method="post" class="form form">
	<input type="hidden" name="stock_edit" value="1" />
	<input type="hidden" name="order_id" value="<?php echo $tpl['os_arr']['order_id']; ?>" />
	<input type="hidden" name="order_stock_id" value="<?php echo $tpl['os_arr']['id']; ?>" />
<?php
if (isset($tpl['os_arr']) && !empty($tpl['os_arr']))
{
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
			?>
			<tr class="table-row-odd">
				<?php
				if (isset($tpl['attr_arr']) && !empty($tpl['attr_arr']))
				{
					foreach ($tpl['attr_arr'] as $attr)
					{
						?>
						<td>
						<?php
						foreach ($attr['child'] as $child)
						{
							if (isset($tpl['stock_arr']['attrs'][$attr['id']]) && $tpl['stock_arr']['attrs'][$attr['id']] == $child['id'])
							{
								echo SanitizeComponent::html($child['name']);
								break;
							}
						}
						?>
						</td>
						<?php
					}
				}
				?>
				<td class="align_center"><input type="text" name="qty" value="<?php echo $tpl['os_arr']['qty']; ?>" class="form-field w60" data-max="<?php echo $tpl['os_arr']['qty'] + @$tpl['stock_arr']['qty']; ?>" readonly="readonly" /></td>
				<td class="align_center"><input type="hidden" name="current_qty" value="<?php echo @$tpl['stock_arr']['qty']; ?>" /><?php echo @$tpl['stock_arr']['qty']; ?></td>
				<td><input type="hidden" name="price" value="<?php echo $tpl['os_arr']['price']; ?>" /><?php echo UtilComponent::formatCurrencySign(number_format($tpl['os_arr']['price'], 2), $tpl['option_arr']['o_currency']); ?></td>
			</tr>
			<?php
			if (!empty($tpl['extra_arr']))
			{
				?>
				<tr class="table-row-odd">
					<td colspan="<?php echo 3 + $cnt; ?>">
					<?php
					foreach ($tpl['extra_arr'] as $extra)
					{
						switch ($extra['type'])
						{
							case 'single':
								?>
								<div class="b5">
									<label><input type="checkbox" name="extra_id[<?php echo $extra['id']; ?>]" value="<?php echo $extra['type']; ?>|<?php echo $extra['price']; ?>"<?php echo array_key_exists($extra['id'], $tpl['oe_arr']) ? ' checked="checked"' : NULL; ?> /> <?php echo SanitizeComponent::html($extra['name']); ?>
									(<?php echo UtilComponent::formatCurrencySign(number_format($extra['price'], 2), $tpl['option_arr']['o_currency']); ?>)</label>
								</div>
								<?php
								break;
							case 'multi':
								?><select name="extra_id[<?php echo $extra['id']; ?>]" class="form-field">
									<option value="">-- Select --</option>
									<?php
									foreach ($extra['extra_items'] as $k => $item)
									{
										?><option value="<?php echo $extra['type']; ?>|<?php echo $item['price']; ?>|<?php echo $item['id']; ?>"<?php echo isset($tpl['oe_arr'][$extra['id']]) && $item['id'] == $tpl['oe_arr'][$extra['id']] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($item['name']); ?> (<?php echo UtilComponent::formatCurrencySign(number_format($item['price'], 2), $tpl['option_arr']['o_currency']); ?>)</option><?php
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
	<?php
}
?>
</form>