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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Index"><?php __('lblListPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Create"><?php __('lblAddPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageCategories&amp;action=Index"><?php __('menuPageCategories'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Create"><?php __('lblAddPageCategory'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=History"><?php __('lblChanges'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(@$titles['SPRO04'], @$bodies['SPRO04']);
	?>
	
	<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
	<div class="multilang"></div>
	<?php endif; ?>
	
	<?php //UtilComponent::printNotice($SC_LANG['info']['add_category']); ?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageCategories&action=Create" method="post" id="frmCreateCategory" class="form form">
		<input type="hidden" name="category_create" value="1" />
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title"><?php __('stock_category_name'); ?>:</label>
				<span class="inline_block">
					<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
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
					?><option value="<?php echo $node['data']['id']; ?>"><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
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
			<p>
				<label class="title">Template</label>
				<span class="inline_block">
					<select name="template" id="template" class="form-field w200">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ($templateFiles as $template)
						{
							?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
						}
						?>
					</select>
				</span>
			</p>	
		<?php endif;?>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo $controller->baseUrl(); ?>index.php?controller=AdminPageCategories&action=Index';" />
		</p>
	</form>
	
	<script type="text/javascript">
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