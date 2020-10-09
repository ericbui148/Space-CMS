<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\Components\UtilComponent;
?>
<table id="boxStockCloneTbl" style="display: none">
	<tbody>
		<tr>
			<td>
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnImageStock">Chọn ảnh</a>
				<span class="boxStockImageId"><span><input type="hidden" name="stock_image_id[{INDEX}]" value="" class="required" /></span></span>
			</td>
			<?php
			if (isset($tpl['attr_arr']))
			{
				foreach ($tpl['attr_arr'] as $attr)
				{
					if(isset($attr['child']) && count(($attr['child'])) > 0)
					{
						?>
						<td>
							<span>
							<select name="stock_attribute[{INDEX}][<?php echo $attr['id']; ?>]" class="form-field required">
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
			}
			?>
			<td><span><input type="text" name="stock_qty[{INDEX}]" class="form-field w40 align_right required digits" /></span></td>
			<td>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
					<input type="text" name="stock_price[{INDEX}]" class="form-field w60 align_right required number" />
				</span>
			</td>
			<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="table-icon-delete btnRemoveStock"></a></td>
		</tr>
	</tbody>
</table>

<div id="dialogDeleteStock" style="display: none" title="<?php __('product_stock_delete_title'); ?>">
<?php __('product_stock_delete_desc'); ?>
</div>
<div id="dialogImageStock" style="display: none" title="<?php __('product_stock_img_title'); ?>"></div>