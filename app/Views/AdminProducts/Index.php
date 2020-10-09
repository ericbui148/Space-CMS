<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;

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
	if (isset($_GET['err']))
	{
		$errors = __('errors', true);
		$titles = __('titles', true);
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$errors[$_GET['err']]);
		UtilComponent::printNotice(@$titles[$_GET['err']], $bodies_text);
	}
	
	?>
	<style type="text/css">
	.status{
		width: 83px !important;
	}
	.status-1{
		background-position: 70px 3px !important;
	}
	.s-Img{
		background-color: #fff;
		border: solid 1px #ccc;
		max-height: 75px;
		max-width: 75px;
		padding: 1px;
		vertical-align: middle;
	}
	</style>
	<?php
	UtilComponent::printNotice("Danh sách sản phẩm", "Dưới đây là danh sách tất cả sản phẩm. Bạn có thể tìm kiếm sản phẩm theo tên và tìm kiếm nâng cao ở đây. Để cập nhật thông tin sản phẩm, click vào biểu tượng bút chì trên sản phẩm tương ứng.");
	?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminProducts" />
			<input type="hidden" name="action" value="Create" />
			<input type="submit" class="button" value="Thêm sản phẩm" />
		</form>
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
			<button type="button" class="button button-detailed"><span class="button-detailed-arrow"></span></button>
		</form>
		<?php
		$product_statuses = __('product_statuses', true);
		?>
		<div class="float_right t5">
			<a href="#" class="button btn-all<?php echo !isset($_GET['is_active_out']) && !isset($_GET['is_out']) ? ' button-active' : null;?>"><?php __('lblAll'); ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="1"><?php echo $product_statuses[1]; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="2"><?php echo $product_statuses[2]; ?></a>
			<a href="#" class="button btn-filter btn-status<?php echo isset($_GET['is_out']) || isset($_GET['is_active_out']) ? ' button-active' : NULL;?>" data-column="status" data-value="3"><?php echo __('lblOutOfStock'); ?></a>
		</div>
		<br class="clear_both" />
	</div>
	
	<div class="form-filter-advanced" style="display: none">
		<span class="menu-list-arrow"></span>
		<form action="" method="get" class="form form form-search frm-filter-advanced">
			<div class="float_left w400">
				<p>
					<label class="title">Tên sản phẩm</label>
					<input type="text" name="name" class="form-field w150" value="<?php echo isset($_GET['name']) ? SanitizeComponent::html($_GET['name']) : NULL; ?>" />
				</p>
				<p>
					<label class="title">SKU</label>
					<input type="text" name="sku" class="form-field w150" value="<?php echo isset($_GET['sku']) ? SanitizeComponent::html($_GET['sku']) : NULL; ?>" />
				</p>
				<p>
					<label class="title">Danh mục sản phẩm</label>
					<select name="category_id" class="form-field w150">
					<option value="">-- Lựa chọn --</option>
					<?php
					foreach ($tpl['category_arr'] as $category)
					{
						?><option value="<?php echo $category['data']['id']; ?>"<?php echo isset($_GET['category_id']) && $_GET['category_id'] == $category['data']['id'] ? ' selected="selected"' : NULL; ?>><?php echo str_repeat("-----", $category['deep']) . " " . SanitizeComponent::html($category['data']['name']); ?></option><?php
					}
					?>
					</select>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="Tìm kiếm" class="button" />
					<input type="reset" value="Bỏ qua" class="button" />
				</p>
			</div>
			<div class="float_right w300">
				<p>
					<label class="title" style="width: 110px">Trạng thái</label>
					<select name="status" class="form-field w150">
						<option value="">-- Lựa chọn --</option>
						<?php
						foreach ($product_statuses as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php echo isset($_GET['status']) && $_GET['status'] == $k ? ' selected="selected"' : NULL; ?>><?php echo SanitizeComponent::html($v); ?></option><?php
						}
						?>
					</select>
				</p>
				<p>
					<label class="title" style="width: 110px">Sản phẩm số</label>
					<span class="left"><input type="checkbox" name="is_digital" value="1"<?php echo isset($_GET['is_digital']) ? ' checked="checked"' : NULL; ?> /></span>
				</p>
				<p>
					<label class="title" style="width: 110px">Sản phẩm nổi bật</label>
					<span class="left"><input type="checkbox" name="is_featured" value="1"<?php echo isset($_GET['is_featured']) ? ' checked="checked"' : NULL; ?> /></span>
				</p>
			</div>
			<br class="clear_both" />
		</form>
	</div>

	<div id="grid"></div>
	
	<div id="dialogDeleteProduct" style="display: none" title="<?php __('delete_confirmation'); ?>"></div>
	
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.queryString = "";
	<?php
	if (isset($_GET['is_out']))
	{
		?>Grid.queryString += "&is_out=yes";<?php
	}
	if (isset($_GET['is_active_out']))
	{
	?>Grid.queryString += "&is_active_out=yes";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.name = "Tên";
	myLabel.sku = "SKU";
	myLabel.exported = "Đã xuất";
	myLabel.delete_selected = "Xoá";
	myLabel.delete_confirmation = "Xác nhận xoá";
	myLabel.sc_delete_product = "Xác nhận xoá sản phẩm";
	myLabel.sc_delete_confirmation = "<?php __('sc_delete_confirmation'); ?>";
	myLabel.status = "Trạng thái";
	myLabel.image = "Ảnh";
	myLabel.stock = "Kho";
	myLabel.price = "Giá";
	</script>
	<?php
}
?>