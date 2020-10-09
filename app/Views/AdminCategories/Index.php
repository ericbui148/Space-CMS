<?php
use App\Controllers\Components\UtilComponent;

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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		//UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	<?php
	UtilComponent::printNotice("Danh mục sản phẩm", "Dưới đây là tất cả danh mục sản phẩm. Bạn có thể tìm kiếm danh mục bằng tên, để sửa thông tin danh mục bạn chọn biểu tượng bút chì trên danh mục tương ứng.");
	?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminCategories" />
			<input type="hidden" name="action" value="Create" />
			<input type="submit" class="button" value="<?php __('btnAddCategory'); ?>" />
		</form>
		<br class="clear_both" />
	</div>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	var myLabel = myLabel || {};
	myLabel.name = "Tên danh mục";
	myLabel.products = "Số lượng sản phẩm";
	myLabel.down = "<?php __('_down'); ?>";
	myLabel.up = "<?php __('_up'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
	</script>
	<?php
}
?>