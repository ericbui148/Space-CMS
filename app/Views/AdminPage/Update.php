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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	?>
	
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Index"><i class="fa fa-list" aria-hidden="true"></i> <?php __('lblListPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Create"><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php __('lblAddPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Update&amp;id=<?php echo $tpl['arr']['id'];?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
			 Cập nhật</a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageCategories&amp;action=Index"><i class="fa fa-book" aria-hidden="true"></i> <?php __('menuPageCategories'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=History"><i class="fa fa-history" aria-hidden="true"></i> Lịch sử trang</a></li>
		</ul>
	</div>
	
	<?php 
	UtilComponent::printNotice("Cập nhật trang", "Sử dụng form dưới đây để cập nhật thông tin trang."); 
	if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
	{
		?><div class="multilang"></div><?php
	} 
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Update" method="post" id="frmUpdatePage" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="page_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id'];?>" />
		<?php if($controller->isAdmin()):?>
			<p>
				<label class="title">Danh mục</label>
				<span class="inline_block">
					<select name="category_id[]" id="category_id" class="form-field w400" multiple="multiple">
    				<?php
    				foreach ($tpl['category_arr'] as $category)
    				{
    					?><option value="<?php echo $category['data']['id']; ?>"<?php echo in_array($category['data']['id'], $tpl['sc_arr']) ? ' selected="selected"' : NULL; ?>><?php echo str_repeat("-----", $category['deep']) . " " . SanitizeComponent::html($category['data']['name']); ?></option><?php
    				}
    				?>
					</select>
				</span>
			</p>		
		<?php endif;?>		
		<?php if(!empty($tpl['arr']['avatar_file'])): ?>
			<?php $image_url = $controller->baseUrl() . $tpl['arr']['avatar_file'];?>
			<p id="image_container">
				<label class="title">&nbsp;</label>
				<span class="inline_block">
					<img class="fd-image" src="<?php echo $image_url; ?>" width="118px" height="91px" />
				</span>
			</p>
		<?php endif;?>
		<p>
			<label class="title"><?php __('lblContentFile'); ?></label>
			<span class="inline_block">
				<input name="file" type="file" class="form-field w350"/>
			</span>
		</p>			
			
		<p>
			<label class="title"><?php __('lblOnDate'); ?></label>
			<span class="form-field-custom form-field-custom-after">
				<input type="text" name="on_date" class="form-field w80 datepick pointer required"  rev="<?php echo $jqDateFormat; ?>" value="<?php echo UtilComponent::formatDate(date('Y-m-d', strtotime($tpl['arr']['on_date'])), 'Y-m-d', $tpl['option_arr']['o_date_format']);;?>" />
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
						?><option value="<?php echo $k; ?>"<?php echo $tpl['arr']['status'] == $k ? ' selected="selected"' : null;?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>					
		<p>
			<label class="title"><?php __('lblLastChanged'); ?></label>
			<span class="inline_block">
				<label class="content">
				<?php
				if($tpl['arr']['changes'] > 0)
				{ 
					$all_changes = str_replace("{cnt}", $tpl['arr']['changes'], __('lblViewAllChanges', true, false));
					$_arr = array();
					$_arr[] = UtilComponent::formatDate(date('Y-m-d', strtotime($tpl['arr']['modified'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ', ' . UtilComponent::formatTime(date('H:i:s', strtotime($tpl['arr']['modified'])), 'H:i:s', $tpl['option_arr']['o_time_format']);
					$_arr[] = __('lblBy', true, false);
					$_arr[] = @$tpl['history_arr']['name'];
					if($controller->isAdmin())
					{
						$_arr[] = ' (<a href="'. $_SERVER['PHP_SELF'] .'?controller=AdminPage&amp;action=History&amp;page_id='.$tpl['arr']['id'].'">' . ($tpl['arr']['changes'] != 1 ? $all_changes : __('lblOneChange', true, false)) .'</a>)';
					} 
					echo join(' ', $_arr);
				}else{
					__('lblNA');
				}
				?>
			</label>
			</span>
		</p>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tiêu đề</label>
				<span class="inline_block">
					<input type="text" id="i18n_page_name_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][page_name]" class="form-field w550<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['page_name'])); ?>" lang="<?php echo $v['id']; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Tiêu đề phụ</label>
				<span class="inline_block">
					<input type="text" id="i18n_sub_title_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][sub_title]" class="form-field w550" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['sub_title'])); ?>" lang="<?php echo $v['id']; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
					<?php endif; ?>
				</span>
			</p>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
			<label class="title">URL</label>
			<span class="inline_block">
				<?php $url = !empty($tpl['arr']['url'][$v['id']])? $tpl['arr']['url'][$v['id']] : __('prefix_page', true, false).'/'.UtilComponent::post_slug($tpl['arr']['i18n'][$v['id']]['page_name']).'-'.$tpl['arr']['id'].'.html';?>
				<label style="font-size: 14px;"><?php echo $controller->baseUrl();?></label><input type="text"  name="i18n[<?php echo $v['id'];?>][url]" class="form-field w400" value="<?php echo $url;?>" lang="<?php echo $v['id']; ?>" />
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
				<?php endif; ?>
				<a target="_blank" href="<?php echo $controller->baseUrl() . $url;?>"><i class="fa fa-external-link-square" aria-hidden="true"></i></a>
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
							<textarea id="i18n_page_short_description_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][page_short_description]" class="form-field w550 h150" lang="<?php echo $v['id']; ?>"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['page_short_description'])); ?></textarea>
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
						<textarea name="i18n[<?php echo $v['id']; ?>][page_content]" class="mceEditor" style="width: 550px; height: 300px" lang="<?php echo $v['id']; ?>" ><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['page_content'])); ?></textarea>
					</span>
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
	<?php 
	if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
	{ 
		?>
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
		<?php
	} 
	?>
	</script>
	<?php
}
?>