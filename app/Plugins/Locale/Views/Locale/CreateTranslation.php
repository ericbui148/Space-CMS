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
		<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&action=Index&tab=1"><i class="fa fa-list" aria-hidden="true"></i> Danh sách bản dịch</a></li>
		<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&action=CreateTranslation"><i class="fa fa-plus-circle" aria-hidden="true"></i> Thêm mới</a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice('Thêm mới bản dịch', 'Sử dụng form dưới đây để thêm mới bản dịch');
	if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
	{
	    ?><div class="multilang"></div><?php
	} 
	?>	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&amp;action=CreateTranslation" method="post" id="createTransalationForm" class="form" autocomplete="off" >
		<input type="hidden" name="locale_create_translation" value="1" />
		<p>
			<label class="title">Key:</label>
			<span class="inline_block">
				<input type="text" name="key" id="key" class="form-field w350" />
			</span>
		</p>
		<p>
			<label class="title">Nhãn:</label>
			<span class="inline_block">
				<input type="text" name="label" id="label" class="form-field w350"/>
			</span>
		</p>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
		?>
			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
				<label class="title">Nội dung</label>
				<span class="inline_block">
					<input type="text" id="i18n_title_<?php echo $v['id'];?>" name="i18n[<?php echo $v['id']; ?>][title]" class="form-field w500<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="" lang="<?php echo $v['id']; ?>" />
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
			<button class="button"><i class="fa fa-floppy-o" aria-hidden="true"></i> <?php __('plugin_country_btn_save'); ?></button>
		</p>

	</form>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
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