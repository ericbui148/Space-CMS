<?php
use Core\Framework\Components\SanitizeComponent;
?>
<div class="multilang b20"></div>

<form action="" method="post" class="clear_both">
	<input type="hidden" name="attr_update" value="1" />
	<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>
		<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
			<label class="title">Tên thuộc tính</label>
			<span class="inline_block">
				<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['name']); ?>" />
				<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
			</span>
		</p>
		<?php
	}
	?>
</form>