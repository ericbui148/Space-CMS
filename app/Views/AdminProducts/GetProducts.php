<?php
use Core\Framework\Components\SanitizeComponent;
?>
<table class="table" cellpadding="0" cellspacing="0" style="width: 100%">
	<thead>
		<tr>
			<th><?php __('product_name'); ?></th>
			<th><?php __('product_sku'); ?></th>
			<th class="w50">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?php
	if (count($tpl['arr']) > 0)
	{
		foreach ($tpl['arr'] as $k => $product)
		{
			?>
			<tr class="<?php echo $k % 2 === 0 ? 'table-row-odd' : 'table-row-even'; ?>">
				<td><?php echo SanitizeComponent::html($product['name']); ?></td>
				<td><?php echo SanitizeComponent::html($product['sku']); ?></td>
				<td><button value="<?php echo $product['id']; ?>" class="btnCopy btnCopy<?php echo @$_GET['copy']; ?>"><?php __('product_attr_copy_btn'); ?></button></td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td colspan="3"><?php __('product_empty');?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>