<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\Components\UtilComponent;
?>
<div class="stockContainer b10">
	<?php
	$table_width = 420;
	if (isset($tpl['attr_arr']))
	{
		foreach ($tpl['attr_arr'] as $attr)
		{
			if(isset($attr['child']) && count(($attr['child'])) > 0)
			{
				$table_width += 150;
			}
		}
	}
	if($table_width < 742)
	{
		$table_width = 742;
	} 
	?>
	<table class="table tblStocks" cellpadding="0" cellspacing="0" style="width: <?php echo $table_width;?>px;">
		<thead>
			<tr>
				<th class="sub w130">Ảnh</th>
				<?php
				if (isset($tpl['attr_arr']))
				{
					foreach ($tpl['attr_arr'] as $attr)
					{
						if(isset($attr['child']) && count(($attr['child'])) > 0)
						{
							?><th class="sub w150"><?php echo SanitizeComponent::html($attr['name']); ?></th><?php
						}
					}
				}
				?>
				<th class="sub w80">Số lượng</th>
				<th class="sub w150">Giá</th>
				<?php
				if(count($tpl['attr_arr']) > 0)
				{ 
					?>
					<th class="sub w40">&nbsp;</th>
					<?php
				} 
				?>
			</tr>
		</thead>
		<tbody>
		<?php
		if (isset($tpl['stock_arr']) && count($tpl['stock_arr']) > 0)
		{
			foreach ($tpl['stock_arr'] as $stock)
			{
				?>
				<tr>
					<td>
						<?php
						if (!empty($stock['small_path']))
						{
							?><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btnImageStock" rel="<?php echo $stock['image_id']; ?>"><img src="<?php echo BASE_URL . $stock['small_path']; ?>" alt="" class="in-stock" /></a><?php
						} else {
							?><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnImageStock">Chọn ảnh</a><?php
						}
						?>
						<span class="boxStockImageId"><span><input type="hidden" name="stock_image_id[<?php echo $stock['id'] ?>]" value="<?php echo $stock['image_id']; ?>" class="required" /></span></span>
					</td>
					<?php
					foreach ($tpl['attr_arr'] as $attr)
					{
						if(isset($attr['child']) && count(($attr['child'])) > 0)
						{
							?>
							<td>
								<span>
								<select name="stock_attribute[<?php echo $stock['id'] ?>][<?php echo $attr['id']; ?>]" class="form-field required">
									<option value="">---</option>
									<?php
									foreach ($attr['child'] as $child)
									{
										?><option value="<?php echo $child['id']; ?>"<?php echo isset($stock['attrs'][$attr['id']]) && $stock['attrs'][$attr['id']] == $child['id'] ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($child['name']); ?></option><?php
									}
									?>
								</select>
								</span>
							</td>
							<?php
						}
					}
					?>
					<td><span><input type="text" name="stock_qty[<?php echo $stock['id'] ?>]" class="form-field w40 align_right required digits" value="<?php echo $stock['qty']; ?>" /></span></td>
					<td>
						<span class="form-field-custom form-field-custom-before">
							<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" name="stock_price[<?php echo $stock['id']; ?>]" class="form-field w60 align_right required number" value="<?php echo $stock['price']; ?>" />
						</span>
					</td>
					<?php
					if(count($tpl['attr_arr']) > 0)
					{ 
						?>
						<td>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>" rel="<?php echo $stock['id']; ?>" class="table-icon-delete btnDeleteStock"></a>
						</td>
						<?php
					} 
					?>
				</tr>
				<?php
			}
		} else {
			mt_srand();
			$index = 'x_' . mt_rand(0, 999999);
			?>
			<tr>
				<td>
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnImageStock">Chọn ảnh</a>
					<span class="boxStockImageId"><span><input type="hidden" name="stock_image_id[<?php echo $index; ?>]" value="" class="required"/></span></span>
				</td>
				<?php
				if (isset($tpl['attr_arr']))
				{
					foreach ($tpl['attr_arr'] as $attr)
					{
						?>
						<td>
							<span>
							<select name="stock_attribute[<?php echo $index; ?>][<?php echo $attr['id']; ?>]" class="form-field required">
								<option value="">---</option>
								<?php
								foreach ($attr['child'] as $child)
								{
									?><option value="<?php echo $child['id']; ?>"><?php echo SanitizeComponent::html($child['name']); ?></option><?php
								}
								?>
							</select>
							</span>
						</td>
						<?php
					}
				}
				?>
				<td><span><input type="text" name="stock_qty[<?php echo $index; ?>]" class="form-field w40 align_right required digits" /></span></td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
						<input type="text" name="stock_price[<?php echo $index; ?>]" class="form-field w60 align_right required number" />
					</span>
				</td>
				<?php
				if(count($tpl['attr_arr']) > 0)
				{ 
					?>
					<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="table-icon-delete btnRemoveStock"></a></td>
					<?php
				} 
				?>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
<?php
if(count($tpl['attr_arr']) > 0)
{ 
	?>
	<div class="h30">
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnStockAdd">Thêm vào kho</a>
	</div>
	<?php
} 
if(count($tpl['gallery_arr']) == 1)
{
	?><input type="hidden" id="scHiddenImageId" name="scHiddenImageId" value="<?php echo $tpl['gallery_arr'][0]['id'];?>" data-src="<?php echo BASE_URL . (!empty($tpl['gallery_arr'][0]['small_path']) ? $tpl['gallery_arr'][0]['small_path'] : IMG_PATH . 'no_image.png'); ?>?<?php echo rand(1, 9999999); ?>"/><?php
}
?>
<div>
	<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
	<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminProducts&action=Index';" />
</div>