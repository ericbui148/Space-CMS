<?php
use App\Controllers\Components\UtilComponent;
use App\Models\WidgetModel;
?>

<?php
if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
{
	?><div class="multilang"></div><?php
} 
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminWidgets&amp;action=UpdateFrontendWidget" method="post" id="frmUpdateWidget" class="form form" autocomplete="off" enctype="multipart/form-data" >
	<input type="hidden" name="widget_update" value="1" />
	<input type="hidden" id="widget_id" name="id" value="<?php echo $tpl['arr']['id'];?>" />
	<input type="hidden" id="widget_data" name="widget_data" value="<?php echo $tpl['arr']['value'];?>" />
	<p>
		<label class="title"><?php __('lblStatus'); ?></label>
		<select name="status" id="status" class="form-field required">
			<option value="">-- <?php __('lblChoose'); ?>--</option>
			<?php
			foreach (__('u_statarr', true) as $k => $v)
			{
				?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['status']? 'selected' : NULL;?>><?php echo $v; ?></option><?php
			}
			?>
		</select>
	</p>
	<p>
		<label class="title"><?php __('lblWidgetType'); ?></label>
		<select name="type" id="type" class="form-field required w250 widget_type" disabled>
			<option value="">-- <?php __('lblChoose'); ?>--</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_HTML;?>" <?php echo WidgetModel::WIDGET_TYPE_HTML == $tpl['arr']['type']?"selected":NULL;?>> <?php __('widget_type_html'); ?></option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_MENU;?>" <?php echo WidgetModel::WIDGET_TYPE_MENU == $tpl['arr']['type']?"selected":NULL;?>> <?php __('widget_type_menu'); ?></option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_SLIDER;?>" <?php echo WidgetModel::WIDGET_TYPE_SLIDER == $tpl['arr']['type']?"selected":NULL;?>> <?php __('widget_type_slider'); ?></option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_ARTICLE_CAT;?>" <?php echo WidgetModel::WIDGET_TYPE_ARTICLE_CAT == $tpl['arr']['type']?"selected":NULL;?>> <?php __('widget_type_news_cat'); ?></option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES;?>" <?php echo WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES == $tpl['arr']['type']?"selected":NULL;?>>Bài viết mới</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_PRODUCT_CAT;?>" <?php echo WidgetModel::WIDGET_TYPE_PRODUCT_CAT == $tpl['arr']['type']?"selected":NULL;?>> <?php __('widget_type_product_cat'); ?></option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_BEST_SELLING_PRODUCTS;?>" <?php echo WidgetModel::WIDGET_TYPE_BEST_SELLING_PRODUCTS == $tpl['arr']['type']?"selected":NULL;?>> Sản phẩm bán chạy</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_FEATURED_PRODUCTS;?>" <?php echo WidgetModel::WIDGET_TYPE_FEATURED_PRODUCTS == $tpl['arr']['type']?"selected":NULL;?>> Sản phẩm nổi bật</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_TAG;?>" <?php echo WidgetModel::WIDGET_TYPE_TAG == $tpl['arr']['type']?"selected":NULL;?>>TAGS</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_SOCIAL_LINK;?>" <?php echo WidgetModel::WIDGET_TYPE_SOCIAL_LINK == $tpl['arr']['type']?"selected":NULL;?>>Liên kết mạng xã hội</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_GOOLE_MAP;?>" <?php echo WidgetModel::WIDGET_TYPE_GOOLE_MAP == $tpl['arr']['type']?"selected":NULL;?>>Google Map</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_FORM_SEARCH;?>" <?php echo WidgetModel::WIDGET_TYPE_FORM_SEARCH == $tpl['arr']['type']?"selected":NULL;?>>Form tìm kiếm</option>
			<option value="<?php echo WidgetModel::WIDGET_TYPE_MULTIPLE_LANGUAGE;?>" <?php echo WidgetModel::WIDGET_TYPE_MULTIPLE_LANGUAGE == $tpl['arr']['type']?"selected":NULL;?>>Ngôn ngữ</option>					
		</select>
	</p>
	
	<div id="widget_type_content"></div>							
	<p>
		<label class="title">&nbsp;</label>
		<button type="submit" class="button"><?php __('btnSave', false, true); ?></button>
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
	$(".multilang").multilang({
		langs: Locale.langs,
		flagPath: Locale.flagPath,
		select: function (event, ui) {
			var index = $(this).data('index');
			loadMceEditor(index);
		}
	});
	function loadMceEditor(lang) {
		tinymce.editors = [];
		tinymce.init({
			selector: "#mceEditor_" + lang,
			theme: "modern",
			language: "vi_VN",
			width: 600,
			height: 300,
			verify_html: false,
			plugins: [
					"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
					"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
					"save table contextmenu directionality emoticons template paste textcolor"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
			image_advtab: true,
			force_br_newlines : false,
			force_p_newlines : false,
			forced_root_block : '',
			menubar: "file edit insert view table tools",
			setup: function (editor) {
				editor.on('change', function (e) {
					editor.editorManager.triggerSave();
					$(":input[name='" + editor.id + "']").valid();
				});
			},
			fontsize_formats: "8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 30pt 32pt 34pt 36pt 38pt 40pt 42pt",
			style_formats: [
				{title: 'Bold text', inline: 'span', styles: {"font-weight": "bold"}},
				{title: 'Italic text', inline: 'span', styles: {"font-style": "italic"}},
				{title: 'Black text', inline: 'span', styles: {"color": "#000"}}
			],
			external_filemanager_path: "core/third-party/filemanager/",
			filemanager_title: "Responsive Filemanager" ,
			external_plugins: {"filemanager": "../filemanager/plugin.min.js"}
		});			
	}			
})(jQuery_1_8_2);
<?php endif; ?>	
</script>