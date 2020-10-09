<?php
use App\Controllers\Components\UtilComponent;

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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminTags&amp;action=Index">Danh sách Tag</a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminContacts&amp;action=Create">Thêm mới</a></li>
		</ul>
	</div>
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	UtilComponent::printNotice('Thêm mới tag', 'Sử dụng form dưới đây thêm mới tag');
	if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
	{
	    ?><div class="multilang"></div><?php
	} 
	?>	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminTags&amp;action=Create" method="post" id="frmUpdateTag" class="form form" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="tag_create" value="1" />
			<?php
    		foreach ($tpl['lp_arr'] as $v)
    		{
    		?>
    			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
    				<label class="title">Tag</label>
    				<span class="inline_block">
    					<input type="text" id="i18n_article_name_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w400<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo isset($tpl['arr']) ? htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['name'])) : null;?>" lang="<?php echo $v['id']; ?>" />
    					<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
    					<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
    					<?php endif; ?>
    				</span>
    			</p>
    			<?php
    		}
    		?>
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
			</p>

	</form>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.checkAllText = "<?php __('multiselect_check_all', false, true); ?>";
	myLabel.uncheckAllText = "<?php __('multiselect_uncheck_all', false, true); ?>";
	myLabel.noneSelectedText = "<?php __('multiselect_none_selected', false, true); ?>";
	myLabel.selectedText = "<?php __('multiselect_selected', false, true); ?>";
	myLabel.positiveNumber = "<?php __('positive_number', false, true); ?>";
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
	<?php endif; ?>
	</script>
	<?php
}
?>