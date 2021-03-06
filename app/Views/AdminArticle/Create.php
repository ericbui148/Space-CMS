<?php

use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;

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
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	$currentDate = UtilComponent::formatDate(date('Y-m-d'), 'Y-m-d', $tpl['option_arr']['o_date_format']);
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle">Danh sách bài viết</a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Create"><?php __('lblAddArticle'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticleCategories&amp;action=Index"><?php __('menuArticleCategories'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=History">Lịch sử bài viết</a></li>
		</ul>
	</div>
	
	<?php 
	UtilComponent::printNotice("Thêm bài viết", "Điền thông tin vào form dưới đây để thêm bài viết mới."); 
	if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
	{
		?><div class="multilang"></div><?php
	} 
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Create" method="post" id="frmCreateArticle" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="article_create" value="1" />
		<p>
			<label class="title">Danh mục</label>
			<span class="inline_block">
				<select name="category_id[]" id="category_id" class="form-field w400" multiple="multiple">
					<?php
					foreach ($tpl['category_arr'] as $category)
					{
						?><option value="<?php echo $category['data']['id']; ?>"><?php echo str_repeat("-----", $category['deep']) . " " .SanitizeComponent::html($category['data']['name']); ?></option><?php
					}
					?>
				</select>
			</span>
		</p>		
		<p>
			<label class="title"><?php __('lblContentFile'); ?></label>
			<span class="inline_block">
				<input name="file" type="file" class="form-field w200"/>
			</span>
		</p>
		
		<p>
			<label class="title"><?php __('lblOnDate'); ?></label>
			<span class="form-field-custom form-field-custom-after">
				<input type="text" name="on_date" class="form-field w80 datepick pointer required"  rev="<?php echo $jqDateFormat; ?>" value="<?php echo $currentDate;?>" />
				<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="status" id="status" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('u_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>					
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tiêu đề</label>
				<span class="inline_block">
					<input type="text" id="i18n_article_name_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][article_name]" class="form-field w550<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['article_name'])) : null;?>" lang="<?php echo $v['id']; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tiêu đề phụ</label>
				<span class="inline_block">
					<input type="text" id="i18n_sub_title_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][sub_title]" class="form-field w550" value="<?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['sub_title'])) : null;?>" lang="<?php echo $v['id']; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		?>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
			?>
					<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<label class="title"><?php __('lblShortDescription'); ?></label>
						<span class="inline_block">
							<textarea id="i18n_article_short_description_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][article_short_description]" class="mceEditor_short" lang="<?php echo $v['id']; ?>"></textarea>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</span>
					</p>
					<?php
		}
		?>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title"><?php __('lblContent'); ?></label>
				<span class="inline_block">
					<span class="block float_left r5">
						<textarea name="i18n[<?php echo $v['id']; ?>][article_content]" class="mceEditor" style="width: 550px; height: 300px" lang="<?php echo $v['id']; ?>"><?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['article_content'])) : null; ?></textarea>
					</span>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		?>
		<?php if (!empty($tpl['galleries'])):?>
		<p>
			<label class="title">Gallery</label>
			<span class="inline_block">
			<select name="slider_id" id="slider_id" class="form-field w400 chosen-select" size="5">
				<?php
				foreach ($tpl['galleries'] as $gallery)
				{
				    ?><option value="<?php echo $gallery['id']; ?>"><?php echo $gallery['name']; ?></option><?php
				}
				?>
			</select>
			</span>
		</p>
		<?php endif;?>		
		<?php if (!empty($tpl['tag_arr'])):?>
		<p>
			<label class="title">Tags</label>
			<span class="inline_block">
			<select name="tag_id[]" id="tag_id" class="form-field w400 chosen-select" multiple="multiple" size="5">
				<?php
				foreach ($tpl['tag_arr'] as $tag)
				{
					?><option value="<?php echo $tag['id']; ?>"><?php echo $tag['name']; ?></option><?php
				}
				?>
			</select>
			</span>
		</p>
		<?php endif;?>	
		
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
			?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Meta title</label>
				<span class="inline_block">
					<input name="i18n[<?php echo $v['id']; ?>][meta_title]" class="form-field w550" value="<?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['meta_title']); ?>"/>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>			
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Meta description</label>
				<span class="inline_block">
					<textarea name="i18n[<?php echo $v['id']; ?>][meta_description]" class="form-field w550 h50"><?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['meta_description']); ?></textarea>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Meta keywords</label>
				<span class="inline_block">
					<textarea name="i18n[<?php echo $v['id']; ?>][meta_keyword]" class="form-field w550 h50"><?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['meta_keyword']); ?></textarea>
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<?php
		}
		?>	
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
			<button class="button"><i class="fa fa-floppy-o" aria-hidden="true"></i>&nbsp;<?php __('btnSave'); ?></button>
		</p>
	</form>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.select_users = "<?php __('lblSelectUsers');?>";
	myLabel.field_required = "<?php __('lblFieldRequired'); ?>";
	myLabel.invalid_url = "<?php __('lblInvalidUrl');?>";
	var locale_array = new Array();
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>locale_array.push(<?php echo $v['id'];?>);<?php
	} 
	?>
	myLabel.locale_array = locale_array; 
	<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
	var Locale = Locale || {};
	Locale.langs = <?php echo $tpl['locale_str']; ?>;
	Locale.flagPath = "<?php echo FRAMEWORK_LIBS_PATH; ?>/img/flags/";
	
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: Locale.langs,
				flagPath: Locale.flagPath,
				select: function (event, ui) {
					
				}
			});
		});
	})(jQuery_1_8_2);
	<?php endif; ?>
	</script>
	<?php
}
?>