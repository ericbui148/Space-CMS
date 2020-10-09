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
	}
} else {
	$info = __('info', true);
	
	?>
	<style type="text/css">
	.mce-tinymce{
		float: left;
	}
	</style>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=Create" method="post" id="frmCreateProduct" class="form form frmProduct" enctype="multipart/form-data">
		<input type="hidden" name="product_create" value="1" />
	    
		<?php UtilComponent::printNotice("Thêm sản phẩm", "Sử dụng form dưới đây để thêm mới một sản phẩm."); ?>
		
		<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
		<div class="multilang"></div>
		<?php endif; ?>
		
		<p><label class="title">Trạng thái</label><select name="status" id="status" class="form-field w200">
			<?php
			foreach (__('product_statuses', true) as $k => $v)
			{
				?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
			}
			?>
		</select></p>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tên sản phẩm</label>
				<span class="inline_block">
					<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w400<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		?>
		<p><label class="title">Danh mục sản phẩm</label>
			<?php
			if(!empty($tpl['category_arr']))
			{ 
				?>
				<select name="category_id[]" id="category_id" class="form-field w400" multiple="multiple">
				<?php
				foreach ($tpl['category_arr'] as $category)
				{
					?><option value="<?php echo $category['data']['id']; ?>"><?php echo str_repeat("-----", $category['deep']) . " " .SanitizeComponent::html($category['data']['name']); ?></option><?php
				}
				?>
				</select>
				<?php
			}else{
				$add_category = __('lblAddCategoryText', true);
				$add_category = str_replace("{STAG}", '<a href="'.$_SERVER['PHP_SELF'].'?controller=AdminCategories&amp;action=Create">', $add_category);
				$add_category = str_replace("{ETAG}", "</a>", $add_category);
				?>
				<span class="inline_block">
					<label class="content"><?php echo $add_category;?></label>
				</span>
				<?php
			} 
			?>
		</p>
		<p>
			<label class="title">SKU</label>
			<span class="inline_block"><input type="text" name="sku" id="sku" class="form-field w300" data-msg-remote="<?php __('product_v_sku', false, true); ?>" /></span>
		</p>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Miêu tả ngắn</label>
				<span class="inline_block">
					<textarea name="i18n[<?php echo $v['id']; ?>][short_desc]" class="form-field w500 h100 selector-short-desc<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"></textarea>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Miêu tả</label>
				<span class="block overflow">
					<textarea name="i18n[<?php echo $v['id']; ?>][full_desc]" class="form-field w500 h200 selector-full-desc<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"></textarea>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
					<br class="clear_both" />
				</span>
			</p>
			<?php
		}
		?>
		<p><label class="title">Sản phẩm nổi bật</label><span class="left"><input type="checkbox" name="is_featured" id="is_featured" value="1" /></span></p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminProducts&action=Index';" />
		</p>
	</form>
	
	<script type="text/javascript">
	var Locale = Locale || {};
	Locale.langs = <?php echo $tpl['locale_str']; ?>;
	Locale.flagPath = "<?php echo BASE_URL . FRAMEWORK_LIBS_PATH; ?>/img/flags/";
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: Locale.langs,
				flagPath: Locale.flagPath,
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