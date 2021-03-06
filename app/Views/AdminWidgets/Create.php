<?php
use App\Controllers\Components\UtilComponent;
use App\Controllers\AdminWidgetsController;


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
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminWidgets&amp;action=Index"><?php __('cms_menu_widget'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminWidgets&amp;action=Create"><?php __('cms_menu_add_widget'); ?></a></li>
		</ul>
	</div>
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1)
	{
		?><div class="multilang"></div><?php
	} 
	UtilComponent::printNotice(@$titles['WIDGET02'], @$bodies['WIDGET02']);
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminWidgets&amp;action=Create" method="post" id="frmCreateWidget" class="form form" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="widget_create" value="1" />
			<p>
				<label class="title"><?php __('lblWidgetType'); ?></label>
				<span class="inline_block">
					<select name="type" id="type" class="form-field required w250 widget_type">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php 
							$widget_arr = AdminWidgetsController::getWidgetList();
						?>
						<?php foreach ($widget_arr as $key => $value):?>
							<option value="<?php echo $key;?>"><?php echo $value;?></option>
						<?php endforeach;?>
					</select>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblWidgetName'); ?>:</label>
				<span class="inline_block">
					<input type="text" name="name" class="form-field w400 required" />
				</span>
			</p>
		<div id="widget_type_content"></div>
		<p>
			<label class="title">Metadata:</label>
			<span class="inline_block">
			<textarea id="meta_textarea" name="params"></textarea>
			</span>
		</p>
		<?php 
			$themePath = THEME_PATH_PUBLIC;
			$showSelectTemplate = is_dir($themePath.'/widgets')
		?>	
		<?php if ($showSelectTemplate): ?>
			<?php $templateFiles = UtilComponent::getFileList($themePath.'/widgets');?>
			<p>
				<label class="title">Template</label>
				<span class="inline_block">
					<select name="template" id="template" class="form-field w200">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ($templateFiles as $template)
						{
							?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
						}
						?>
					</select>
				</span>
			</p>	
		<?php endif;?>						
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="status" id="status" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('u_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>		
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
	var metaTextArea = document.getElementById('meta_textarea');
	var editor = CodeMirror.fromTextArea(metaTextArea, {
        matchBrackets: true,
	    lineNumbers: true,
        autoCloseBrackets: true,
        mode: "application/ld+json",
        lineWrapping: true
	});
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
					function loadMceEditor() {
						tinymce.editors = [];
						tinymce.init({
							selector: "#mceEditor_" + lang,
						    theme: "modern",
						    language: "vi_VN",
						    width: 550,
						    height: 400,
						    verify_html: false,
						    plugins: [
						         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
						         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
						         "save table contextmenu directionality emoticons template paste textcolor"
					        ],
						    extended_valid_elements : 'i[class], span',
						    custom_elements : 'i[class], span',
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
			<?php endif;?>			
	</script>
	<?php
}
?>