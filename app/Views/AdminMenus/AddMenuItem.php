<form
	action="<?php echo $_SERVER['PHP_SELF']; ?>controller=AdminMenus&action=CreateMenuItem"
	method="post" id="frmCreateMenuItem" class="form form"
	autocomplete="off" enctype="multipart/form-data">
	<input type="hidden" name="menu_id" id="menu_id"
		value="<?php echo @$tpl['menu_id'];?>" /> <input type="hidden"
		id="menu_item_add" name="menu_item_add" value="1" />
		<?php 
			if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
			{
				?><div class="multilang"></div><?php
			} 
		?>
		<br /><br /><br />
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
		<label class="title"><?php __('lblMenuItemName'); ?><span class="required">(*)</span></label> <span
			class="inline_block"> <input type="text"
			id="i18n_name_<?php echo $v['id'];?>"
			name="i18n[<?php echo $v['id']; ?>][name]"
			class="form-field w400<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"
			value="<?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['section_name'])) : null;?>"
			lang="<?php echo $v['id']; ?>" />
					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
					<span class="multilang-input"><img
				src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>"
				alt="" /></span>
					<?php endif; ?>
				</span>
	</p>
			<?php
		}?>
		<p>
			<label class="title">Avatar</label>
			<span class="left" id="box_avatar">
				<?php
				if (!empty($tpl['arr']['avatar']))
				{
					?><img src="<?php echo $tpl['arr']['avatar']; ?>" alt="" class="align_middle" />
					<input type="button" class="button delete-avatar" value="<?php __('lblDelete'); ?>" /><?php
				} else {
					?><input type="file" name="avatar" id="y_avatar" class="form-field w80"/><?php
				}
				?>
			</span>
		</p>
		<p>
			<label class="title">Class:</label>
			<span class="inline_block">
				<input type="text" name="class" class="form-field w400 required" />
			</span>
		</p>		
		<p>
		<label class="title"><?php __('lblMenuLinkType'); ?><span class="required">(*)</span>:</label> <span
			class="inline_block">
			<select name="link_type" id="link_type" class="form-field required w400 link_type">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ( $tpl['link_type_arr'] as $k => $v ) {
							
							?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
							
						}
						?>				
				
			</select>
			</span>
		</p>
		<div id="link_type_content" ></div>
		<p>
			<label class="title"><?php __('lblMenuItemParent'); ?>:</label> <span
				class="inline_block"> <select name="parent_id" id="parent_id"
				class="form-field required w400">
				<option value="1">-- <?php __('menu_no_parent'); ?>--</option>
						<?php
						foreach ($tpl['node_arr'] as $node)
						{
							?><option value="<?php echo $node['data']['id']; ?>"><?php echo str_repeat('------', $node['deep']) . " " . $node['data']['name']; ?></option><?php
						}
						?>
					</select>
			</span>
		</p>
		<p>
			<label class="title">Metadata:</label>
			<span class="inline_block">
			<textarea rows="4" cols="50" name="metadata"></textarea>
			</span>
		</p>
</form>
<div id="dialogDeleteAvatar"></div>
<script type="text/javascript">
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
		<?php endif;?>	
	</script>
