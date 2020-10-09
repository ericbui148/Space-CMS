<?php
use App\Models\WidgetModel;

$widgetType = (int)$_POST['widget_type'];
$widgetData = @$_POST['widget_data'];

switch ($widgetType) {
	case WidgetModel::WIDGET_TYPE_HTML:
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title"><?php __('lblContent'); ?></label>
				<span class="inline_block">
					<span class="block float_left r5">
						<textarea name="i18n[<?php echo $v['id']; ?>][content]" class="mceEditor" id="mceEditor_<?php echo $v['id']; ?>" style="width: 550px; height: 300px" lang="<?php echo $v['id']; ?>"><?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['content'])) : null; ?></textarea>
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
		break;
	case WidgetModel::WIDGET_TYPE_MENU:
		?>
		<?php
			if(!empty($tpl['menu_arr']))
					{ 
						?>
						<p><label class="title"><?php __('lblMenuName'); ?></label> 
						<span class="inline-block"> 
							<select name="menu_id" id="menu_id" class="form-field w400">
									<option value="">-- <?php __('lblChoose'); ?>--</option>
									<?php
									foreach ($tpl['menu_arr'] as $v)
									{
										?><option value="<?php echo $v['id']; ?>"  <?php echo $widgetData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['name']); ?></option><?php
									}
									?>
							</select>
						</span>
						</p>
					<?php
		} ?>		
		<?php
		break;
	case WidgetModel::WIDGET_TYPE_SLIDER:
		if(!empty($tpl['slider_arr']))
		{
			?>
			<p><label class="title"><?php __('lblSliderName'); ?></label> 
			<span class="inline-block"> 
			<select name="slider_id" id="slider_id" class="form-field w400">
				<option value="">-- <?php __('lblChoose'); ?>--</option>
				<?php
				foreach ($tpl['slider_arr'] as $v)
				{
				?><option value="<?php echo $v['id']; ?>" <?php echo $widgetData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['name']); ?></option><?php
				}
				?>
			</select>
			</span>
			</p>
			<?php
		} 		
		break;
	case WidgetModel::WIDGET_TYPE_ARTICLE_CAT:
		?>
		<p>
		<label class="title"><?php __('widget_type_news_cat'); ?>:</label>
		<select name="article_cat_id" id="article_cat_id" class="form-field w400">
			<option value="1"><?php __('lblChoose'); ?></option>
			<?php
			foreach ($tpl['node_arr'] as $node)
			{
				?><option value="<?php echo $node['data']['id']; ?>" <?php echo $widgetData == $node['data']['id'] ? ' selected="selected"' : NULL; ?>><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
			}
			?>
		</select>
		</p>
		<?php		
		break;
	case WidgetModel::WIDGET_TYPE_PRODUCT_CAT:
		?>
		<p>
		<label class="title"><?php __('widget_type_product_cat'); ?>:</label>
		<select name="product_cat_id" id="product_cat_id" class="form-field w400">
			<option value="1"><?php __('lblChoose'); ?></option>
			<?php
			foreach ($tpl['node_arr'] as $node)
			{
				?><option value="<?php echo $node['data']['id']; ?>" <?php echo $widgetData == $node['data']['id'] ? ' selected="selected"' : NULL; ?>><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
			}
			?>
		</select>
		</p>
		<?php		
		break;		
	case WidgetModel::WIDGET_TYPE_SECTION_CATEGORY_LIST:
		?>
		<p>
			<label class="title"><?php __('widget_type_section_cat_list_type'); ?>:</label>
			<select name="section_cat_list_type" id="section_cat_list_type" class="form-field w400">
				<option value="1"><?php __('lblChoose'); ?></option>
				<?php 
				foreach ($tpl['section_cat_list_type'] as $key => $value)
				{
					?><option value="<?php echo $key; ?>" <?php echo $key == $widgetData ? ' selected="selected"' : NULL; ?>><?php echo $value;?></option><?php
				}				
				?>
			</select>
		</p>
		<?php		
		break;
	case WidgetModel::WIDGET_TYPE_FORUM_TERM_CONDITION:
			?>
		<p>
			<label class="title">Trang:</label>
			<select name="page_id" id="page_id" class="form-field w400">
				<option value="1"><?php __('lblChoose'); ?></option>
				<?php 
				foreach ($tpl['page_arr'] as $key => $value)
				{
					?><option value="<?php echo $key; ?>" <?php echo $key == $widgetData ? ' selected="selected"' : NULL; ?>><?php echo $value;?></option><?php
				}				
				?>
			</select>
		</p>
		<?php		
		break;
}
?>
