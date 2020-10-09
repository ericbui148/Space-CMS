<div id="dialogCopyAttr" style="display: none" title="<?php __('product_attr_copy_title'); ?>"></div>
<div id="dialogAttrGroupDelete" style="display: none" title="<?php __('product_attr_group_delete'); ?>"><?php __('product_attr_group_delete_body'); ?></div>
<div id="dialogAttrDelete" style="display: none" title="<?php __('product_attr_erase'); ?>"><?php __('product_attr_delete_body'); ?></div>

<div id="boxAddAttr" style="display: none">
	<div id="attrBoxRowItems_{X}" class="attrBoxRowItems">
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>
		<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
			<span class="inline_block">
				<input type="text" name="i18n[<?php echo $v['id']; ?>][attr_item][{INDEX}][{X}]" class="form-field w80<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
				<a href="#" class="icon-delete align_top btnAttrRemove"></a>
			</span>
		</p>
		<?php
	}
	?>
	<a href="javascript:void(0);" class="item-move-icon"></a>
	</div>
</div>

<div id="boxAddAttribute" style="display: none">
	<div id="attrBox_{INDEX}" class="attrBox">
		<input type="hidden" name="attr[{INDEX}]" value="1" />
		<div class="attrBoxRow" style="position: relative;">
			<?php
			foreach ($tpl['lp_arr'] as $v)
			{
				?>
				<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
					<label class="title">Tên nhóm thuộc tính</label>
					<span class="inline_block">
						<input type="text" name="i18n[<?php echo $v['id']; ?>][attr_group][{INDEX}]" class="form-field w400<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
						<a href="#" class="icon-delete align_top btnAttrGroupRemove"></a>
					</span>
				</p>
				<?php
			}
			?>
			<a href="javascript:void(0);" class="group-move-icon"></a>
		</div>
		<input type="hidden" id="orderItems_{INDEX}" name="orderItems_{INDEX}" value="" class="w300"/>
		<div class="attrBoxRow">
			<label class="title">Tên thuộc tính</label>
			<div id="attrBoxRowStick_{INDEX}" class="attrBoxRowStick" data-id="{INDEX}">
				<div id="attrBoxRowItems_{X}" class="attrBoxRowItems">
					<?php
					foreach ($tpl['lp_arr'] as $v)
					{
						?>
						<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
							<span class="inline_block">
								<input type="text" name="i18n[<?php echo $v['id']; ?>][attr_item][{INDEX}][{X}]" class="form-field r5 w80<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
							</span>
						</p>
						<?php
					}
					?>
					<a href="javascript:void(0);" class="item-move-icon"></a>
				</div>
			</div>
		</div>
		<div>
			<label class="title">&nbsp;</label>
			<a href="#" class="button btnAddAttr" rel="{INDEX}">Tạo thuộc tính</a>
		</div>
	</div>
</div>