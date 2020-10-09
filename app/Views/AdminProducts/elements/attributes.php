<br class="clear_both" />
<input type="hidden" id="orderAttributes" name="orderAttributes" value="" class="w300"/>
<div id="boxAttributes" class="b10 t10">
	<?php include dirname(__FILE__) . '/attributes_only.php'; ?>
</div>

<div class="h30">
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnAddAttribute">Tạo nhóm thuộc tính</a>
	<?php __('product_attr_or'); ?>
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnCopyAttribute">Copy thuộc tính</a>
</div>
<input type="submit" class="button" value="<?php __('btnSave', false, true); ?>" />
<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminProducts&action=Index';" />