<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			//UtilComponent::printNotice(NULL, $status[2]);
			break;
	}
} else {
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	?>
	<?php
	UtilComponent::printNotice("Thêm danh mục", "Sử dụng form dưới đây để thêm danh mục mới.");
	?>
	
	<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
	<div class="multilang"></div>
	<?php endif; ?>
	
	<?php //UtilComponent::printNotice($SC_LANG['info']['add_category']); ?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminCategories&amp;action=Create" method="post" id="frmCreateCategory" class="form form">
		<input type="hidden" name="category_create" value="1" />
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tên danh mục:</label>
				<span class="inline_block">
					<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title"><?php __('lblContent'); ?></label>
				<span class="inline_block">
					<textarea id="i18n_description_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][description]" class="mceEditor" lang="<?php echo $v['id']; ?>"></textarea>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		?>
		<p><label class="title">Danh mục cha:</label>
			<select name="parent_id" id="parent_id" class="form-field">
				<option value="1">Không có danh mục cha</option>
				<?php
				foreach ($tpl['node_arr'] as $node)
				{
					?><option value="<?php echo $node['data']['id']; ?>"><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
				}
				?>
			</select>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminCategories&action=Index';" />
		</p>
	</form>
	
	<script type="text/javascript">
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: <?php echo $tpl['locale_str']; ?>,
				flagPath: "<?php echo BASE_URL . FRAMEWORK_LIBS_PATH; ?>/img/flags/",
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