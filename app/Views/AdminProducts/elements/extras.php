<br class="clear_right" />
<div id="boxExtras" class="b10 t10">
<?php include dirname(__FILE__) . '/extras_only.php'; ?>
</div>

<div class="h30">
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnAddExtra">Thêm sản phẩm</a>
	<?php __('product_attr_or'); ?>
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button btnCopyExtra">Copy sản phẩm</a>
</div>
<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminProducts&action=Index';" />