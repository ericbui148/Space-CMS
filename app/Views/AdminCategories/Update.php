<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
use App\Models\ItemSortModel;

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
	?>
	<?php
	UtilComponent::printNotice("Cập nhật danh mục", "Sử dụng form dưới đây để cập nhật thông tin danh mục.");
	?>
	
	<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
	<div class="multilang"></div>
	<?php endif; ?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminCategories&amp;action=Update&amp;id=<?php echo $tpl['arr']['id']; ?>" method="post" id="frmUpdateCategory" class="form form">
		<input type="hidden" name="category_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
			?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tên danh mục:</label>
				<span class="inline_block">
					<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['name']); ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title"><?php __('lblContent'); ?></label>
				<span class="inline_block">
					<textarea id="i18n__description_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][description]" class="mceEditor" lang="<?php echo $v['id']; ?>"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['description'])); ?></textarea>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		$child_ids = array();
		foreach ($tpl['child_arr'] as $child)
		{
			$child_ids[] = $child['data']['id'];
		}
		?>
		<p><label class="title">Danh mục cha:</label>
			<select name="parent_id" id="parent_id" class="form-field">
				<option value="1">Không có danh mục cha</option>
				<?php
				foreach ($tpl['node_arr'] as $node)
				{
					$disabled = NULL;
					if ($node['data']['id'] == $tpl['arr']['id'] || in_array($node['data']['id'], $child_ids))
					{
						$disabled = ' disabled="disabled"';
					}
					?><option value="<?php echo $node['data']['id']; ?>"<?php echo $disabled; ?><?php echo $tpl['arr']['parent_id'] == $node['data']['id'] ? ' selected="selected"' : NULL; ?>><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
				}
				?>
			</select>
		</p>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
			<label class="title">URL</label>
			<span class="inline_block">
				<?php $url = !empty($tpl['arr']['i18n'][$v['id']]['url'])? $tpl['arr']['i18n'][$v['id']]['url'] : '';?>
				<label style="font-size: 14px;"><?php echo $controller->baseUrl();?></label><input type="text"  name="i18n[<?php echo $v['id']; ?>][url]" class="form-field w350" value="<?php echo $url;?>" lang="<?php echo $v['id']; ?>" />
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
				<a target="_blank" href="<?php echo $controller->baseUrl() . $url;?>"><i class="fa fa-external-link-square" aria-hidden="true"></i></a>
				<?php endif; ?>
			</span>
			</p>			
			<?php
		}
		?>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminCategories&action=Index';" />
		</p>
	</form>
 <?php
	UtilComponent::printNotice("Danh sách sản phẩm", "Dưới đây là danh sách tất cả sản phẩm thuộc về danh mục này, bạn có thể thêm sản phẩm vào danh mục và đổi thứ tự hiển thị ở đây.");
	?>
	<div id="product_grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.queryString = "&foreign_type_id=<?php echo $tpl['arr']['id'];?>&type=<?php echo ItemSortModel::TYPE_PRODUCT_CATEGORY;?>"
	var myLabel = myLabel || {};
	myLabel.checkAllText = "<?php __('multiselect_check_all', false, true); ?>";
	myLabel.uncheckAllText = "<?php __('multiselect_uncheck_all', false, true); ?>";
	myLabel.noneSelectedText = "<?php __('multiselect_none_selected', false, true); ?>";
	myLabel.selectedText = "<?php __('multiselect_selected', false, true); ?>";
	myLabel.positiveNumber = "<?php __('positive_number', false, true); ?>";
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
	myLabel.down = "<?php __('_down'); ?>";
	myLabel.up = "<?php __('_up'); ?>";
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: <?php echo $tpl['locale_str']; ?>,
				flagPath: "<?php echo FRAMEWORK_LIBS_PATH; ?>/img/flags/",
				tooltip: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sit amet faucibus enim.",
				select: function (event, ui) {
					// Callback, e.g. ajax requests or whatever
				}
			});
		});
	})(jQuery_1_8_2);
	</script>
	<?php
}
?>