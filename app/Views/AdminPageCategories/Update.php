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
	
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage"><?php __('lblListPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageProducts&amp;action=Stock"><?php __('product_stock_tab'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageCategories&amp;action=Index"><?php __('menuStockCategories'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=History"><?php __('lblChanges'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(@$titles['SPRO07'], @$bodies['SPRO07']);
	?>
	
	<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
	<div class="multilang"></div>
	<?php endif; ?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageCategories&amp;action=Update&amp;id=<?php echo $tpl['arr']['id']; ?>" method="post" id="frmUpdateCategory" class="form form">
		<input type="hidden" name="category_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
			?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title"><?php __('stock_category_name'); ?>:</label>
				<span class="inline_block">
					<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['name']); ?>" />
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
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
			<label class="title">URL</label>
			<span class="inline_block">
				<?php $url = !empty($tpl['arr']['i18n'][$v['id']]['url'])? $tpl['arr']['i18n'][$v['id']]['url'] : '';?>
				<label style="font-size: 14px;"><?php echo $controller->baseUrl();?></label><input type="text"  name="i18n[<?php echo $v['id']; ?>][url]" class="form-field w400" value="<?php echo $url;?>" lang="<?php echo $v['id']; ?>" />
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
				<a target="_blank" href="<?php echo $controller->baseUrl() . $url;?>"><i class="fa fa-external-link-square" aria-hidden="true"></i></a>
				<?php endif; ?>
			</span>
			</p>			
			<?php
		}
		?>
		<p><label class="title"><?php __('stock_category_parent'); ?>:</label>
			<select name="parent_id" id="parent_id" class="form-field">
				<option value="1"><?php __('stock_category_no_parent'); ?></option>
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
			$themePath = THEME_PATH_PUBLIC;
			$showSelectTemplate = is_dir($themePath.'/templates')
		?>	
		<?php if ($showSelectTemplate): ?>
			<?php $templateFiles = UtilComponent::getFileList($themePath.'/templates');?>
			<?php if (!empty($templateFiles)):?>
				<p>
					<label class="title">Template</label>
					<span class="inline_block">
						<select name="template" id="template" class="form-field w200">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach ($templateFiles as $template)
							{
								?><option value="<?php echo $template; ?>"<?php echo $tpl['arr']['template'] == $template ? ' selected="selected"' : null;?>><?php echo $template; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
			<?php endif;?>	
		<?php endif;?>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo $controller->baseUrl(); ?>index.php?controller=AdminPageCategories&action=Index';" />
		</p>
	</form>
	<?php
	UtilComponent::printNotice("Danh sách bài viết", "Dưới đây là danh sách tất cả trang của danh mục này, bạn có thể thêm trang vào danh mục và đổi thứ tự hiển thị ở đây.");
	?>
	<div id="page_grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.queryString = "&foreign_type_id=<?php echo $tpl['arr']['id'];?>&type=<?php echo ItemSortModel::TYPE_PAGE_CATEGORY;?>"
	var myLabel = myLabel || {};
	myLabel.name = "Tên";
	myLabel.image = "Ảnh";
	myLabel.down = "<?php __('_down'); ?>";
	myLabel.up = "<?php __('_up'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
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