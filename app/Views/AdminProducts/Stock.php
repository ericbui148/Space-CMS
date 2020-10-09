<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 1:
			UtilComponent::printNotice($status[1]);
			break;
		case 2:
			UtilComponent::printNotice($status[2]);
			break;
		case 9:
			UtilComponent::printNotice($status[9]);
			break;
	}
} else {
	$errors = __('errors', true);
	$titles = __('titles', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$errors[$_GET['err']], @$titles[$_GET['err']]);
	}
	?>
	<style type="text/css">
	.s-Pic{
		display: inline-block;
		float: left;
		margin: 0 10px 0 0;
	}
	.s-Img{
		background-color: #fff;
		border: solid 1px #ccc;
		max-height: 75px;
		max-width: 75px;
		padding: 1px;
		vertical-align: middle;
	}
	.s-Name{
		color: #306dab;
		display: block;
		font: bold 14px/18px ArchivoNarrowBold, "Myriad Pro", "Trebuchet MS", Helvetica, Arial, sans-serif;
		text-transform: uppercase;
	}
	.s-Attr{
		color: #babcbe;
		display: block;
		margin: 3px 0 0;
	}
	</style>
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	UtilComponent::printNotice("Kho hàng", "Dưới đây là danh sách tất cả sản phẩm trong kho. Bạn có thể tìm kiếm và kiểm tra số lượng tồn kho ở đây.");
	?>
	
	<div class="b10">
		<form action="" method="get" class="form frm-filter-stock float_left">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
		</form>
		
		<form action="<?php echo BASE_URL; ?>index.php" method="get" target="_blank" class="form float_right">
			<input type="hidden" name="controller" value="AdminProducts" />
			<input type="hidden" name="action" value="PrintStock" />
			<input type="submit" value="<?php __('product_stock_print_all', false, true); ?>" class="button" />
		</form>
		<form id="frmPrintSelectedStock" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=PrintStock" method="post" target="_blank" class="form float_right">
		</form>
		<br class="clear_left" />
	</div>

	<div id="grid_stock"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('lblName', false, true); ?>";
	myLabel.price = "<?php __('product_stock_price', false, true); ?>";
	myLabel.qty = "<?php __('product_stock_qty', false, true); ?>";
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	myLabel.print_selected = "<?php __('product_stock_print_selected', false, true); ?>";
	</script>
	<?php
}
?>