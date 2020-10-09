<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
<div class="multilang"></div>
<?php endif; ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&amp;action=Update" method="post" class="form form clear_both">
	<input type="hidden" name="options_update" value="1" />
	<input type="hidden" name="next_action" value="Index" />
	<input type="hidden" name="tab" value="<?php echo @$_GET['tab']; ?>" />
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>
		<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
			<div class="t10">
				<span class="inline_block pt5"><?php __('terms_url'); ?></span>
			</div>
			<span class="block t5">
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
					<input name="i18n[<?php echo $v['id']; ?>][terms_url]" type="text" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['terms_url'])); ?>" class="form-field w500" />
				</span>
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
				<?php endif; ?>
			</span>
			<div class="t10">
				<span class="inline_block pt5"><?php __('terms_content'); ?></span>
			</div>
			<span class="block t5 b10">
				<textarea name="i18n[<?php echo $v['id']; ?>][terms_body]" class="form-field w700 h300"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['terms_body']); ?></textarea>
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
				<?php endif; ?>
			</span>
		</div>
		<?php
	}
	?>
	<p>
		<input type="submit" class="button" value="<?php __('btnSave', false, true); ?>" />
	</p>
</form>

<?php $locale = isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : @$tpl['lp_arr'][0]['id']; ?>
<script type="text/javascript">
<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1) : ?>
(function ($) {
	$(function() {
		$(".multilang").multilang({
			langs: <?php echo $tpl['locale_str']; ?>,
			flagPath: "<?php echo FRAMEWORK_LIBS_PATH; ?>/img/flags/",
			select: function (event, ui) {
				//callback
			}
		});
		$(".multilang").find("a[data-index='<?php echo $locale; ?>']").trigger("click");
	});
})(jQuery_1_8_2);
<?php endif; ?>
</script>