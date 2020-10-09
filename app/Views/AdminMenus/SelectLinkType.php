<?php
use App\Models\MenuItemModel;
$linkType = @$_POST['link_type'];
$linkData = @$_POST['link_data'];
switch ($linkType) {
	case MenuItemModel::LINK_TYPE_DEFAULT:
		?>
		<p id="p_link">
			<label class="title"><?php __('lblMenuLink'); ?>:</label> 
			<span class="inline_block"> <input type="text" name="link" value="<?php echo $linkData;?>" id="link" class="form-field w400 required" /></span>
		</p>
		<?php		
		break;
	case MenuItemModel::LINK_TYPE_SINGLE_ARTICLE:
		?>
		<?php
			if(!empty($tpl['article_arr']))
					{ 
						?>
						<p id="p_section"><label class="title"><?php __('cms_article'); ?></label> 
						<span class="inline-block"> 
							<select name="article_id" id="article_id" class="form-field w400">
									<option value="">-- <?php __('lblChoose'); ?>--</option>
									<?php
									foreach ($tpl['article_arr'] as $v)
									{
										?><option value="<?php echo $v['id']; ?>" <?php echo $linkData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['article_name']); ?></option><?php
									}
									?>
							</select>
						</span>
						</p>
					<?php
		} ?>		
		<?php
		break;
	case MenuItemModel::LINK_TYPE_ARTICE_CATEGORY:
	case MenuItemModel::LINK_TYPE_PAGE_CATEGORY:
		?>
		<?php
			if(!empty($tpl['category_arr']))
			{ 
				?>
				<p id="p_category">
					<label class="title"><?php __('lblSectionCategory'); ?></label> 
					<span class="inline-block"> 
						<select name="category_id" id="category_id" class="form-field w400">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
									foreach ($tpl['category_arr'] as $node)
									{
										?><option value="<?php echo $node['data']['id']; ?>" <?php echo $linkData == $node['data']['id']? 'selected' : NULL;?>><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
									}
								?>
						</select>
					</span>
				</p>
				<?php
		} ?>		
		<?php
		break;
	case MenuItemModel::LINK_TYPE_PAGE:
		?>
		<?php
		if(!empty($tpl['page_arr']))
					{ 
						?>
						<p><label class="title"><?php __('cms_page'); ?></label> 
						<span class="inline-block"> 
							<select name="page_id" id="page_id" class="form-field w400">
									<option value="">-- <?php __('lblChoose'); ?>--</option>
									<?php
									foreach ($tpl['page_arr'] as $v)
									{
										?><option value="<?php echo $v['id']; ?>" <?php echo $linkData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['page_name']); ?></option><?php
									}
									?>
							</select>
						</span>
						</p>
					<?php
		} ?>		
		<?php
		break;
	case MenuItemModel::LINK_TYPE_PRODUCT:
		?>
				<?php
				if(!empty($tpl['product_arr']))
							{ 
								?>
								<p><label class="title"><?php __('menuStockProduct'); ?></label> 
								<span class="inline-block"> 
									<select name="product_id" id="product_id" class="form-field w400">
											<option value="">-- <?php __('lblChoose'); ?>--</option>
											<?php
											foreach ($tpl['product_arr'] as $v)
											{
												?><option value="<?php echo $v['id']; ?>" <?php echo $linkData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['name']); ?></option><?php
											}
											?>
									</select>
								</span>
								</p>
							<?php
				} ?>		
				<?php
		break;
	case MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY:
		?>
		<?php
			if(!empty($tpl['node_arr']))
			{ 
				?>
				<p>
				<label class="title"><?php __('widget_type_product_cat'); ?>:</label>
				<select name="product_category_id" id="product_category_id" class="form-field w400">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach ($tpl['node_arr'] as $node)
					{
						$disabled = NULL;
						if ($node['data']['id'] == $tpl['arr']['id'] || in_array($node['data']['id'], $child_ids))
						{
							$disabled = ' disabled="disabled"';
						}
						?><option value="<?php echo $node['data']['id']; ?>"<?php echo $linkData == $node['data']['id']? 'selected' : NULL;?>><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
					}
					?>
				</select>
				</p>
				<?php
		} ?>		
		<?php
		break;
	case MenuItemModel::LINK_TYPE_TAG:
		?>
		<?php
			if(!empty($tpl['tag_arr']))
			{ 
				?>
				<p>
				<label class="title">Tag:</label>
				<select name="tag_id" id="tag_id" class="form-field w400">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ($tpl['tag_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>" <?php echo $linkData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['name']); ?></option><?php
						}
						?>
				</select>
				</p>
				<?php
		} ?>		
		<?php
		break;		
	case MenuItemModel::LINK_TYPE_GALLERY:
		?>
		<?php
			if(!empty($tpl['gallery_arr']))
			{ 
				?>
				<p>
					<label class="title"><?php __('lblGallery'); ?></label> 
					<span class="inline-block"> 
						<select name="gallery_id" id="gallery_id" class="form-field w400">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
									foreach ($tpl['gallery_arr'] as $v)
									{
										?><option value="<?php echo $v['id']; ?>" <?php echo $linkData == $v['id']? 'selected' : NULL;?>><?php echo stripslashes($v['name']); ?></option><?php
									}
								?>
						</select>
					</span>
				</p>
				<?php
		} ?>		
		<?php
		break;
}
