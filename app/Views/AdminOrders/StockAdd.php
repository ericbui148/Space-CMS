<?php
use Core\Framework\Components\SanitizeComponent;
?>
<form action="" method="post" class="form form">
	<input type="hidden" name="stock_add" value="1" />
	<input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>" />
	<div class="b10">
		<span class="inline_block align_top lh20 r5"><?php __('order_select_product'); ?></span>
		<select name="product_id" class="form-field w300 stock-product">
			<option value="">-- <?php __('order_p_name'); ?> --</option>
			<?php
			foreach ($tpl['product_arr'] as $product)
			{
				?><option value="<?php echo $product['id']; ?>"><?php echo SanitizeComponent::html($product['name'] . " (" . $product['sku'] . ")"); ?></option><?php
			}
			?>
		</select>
	</div>
	
	<div class="p stock-products"></div>
</form>