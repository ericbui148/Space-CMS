<?php
if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
{
    ?><p><div class="multilang"></div></p><?php
	} 
?>
<?php
foreach ($tpl['lp_arr'] as $v)
{
?>
<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
	<label class="title"><?php __('lblImageTitle'); ?></label> <input type="text"
		id="i18n_title_<?php echo $v['id'];?>"
		name="i18n[<?php echo $v['id']; ?>][title]" class="form-field w250"
		value="<?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['title'])) : null;?>"
		lang="<?php echo $v['id']; ?>" />
			<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
			<span class="multilang-input"><img
			src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>"
			alt="" /></span>
			<?php endif; ?>
</p>
<?php
}?>
<p>
	<label class="title"><?php __('lblImageLink'); ?></label> 
	<input type="text" name="link" class="form-field w250" value="<?php echo $tpl['arr']['link'];?>">	
</p>

<?php
foreach ($tpl['lp_arr'] as $v)
{
?>
<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
	<label class="title"><?php __('lblImageSlideText'); ?></label> 
		 <textarea id="i18n_description_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][description]" class="form-field w350 h50"
		lang="<?php echo $v['id']; ?>" ><?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['description'])) : null;?>
		</textarea>
			<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
			<span class="multilang-input"><img
			src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>"
			alt="" /></span>
			<?php endif; ?>
</p>
<?php
}?>

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
