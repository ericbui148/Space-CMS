var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreatePage = $("#frmCreatePage"),
			$frmUpdatePage = $("#frmUpdatePage"),
			$dialogView = $("#dialogView"),
			chosen = ($.fn.chosen !== undefined),
			$dialogRestore = $("#dialogRestore"),
			dialog = ($.fn.dialog !== undefined),
			multiselect = ($.fn.multiselect !== undefined),
			datagrid = ($.fn.datagrid !== undefined);
		
		if (multiselect) {
			$("#user_id").multiselect({
				noneSelectedText: myLabel.select_users
			});
		}
		
		if (chosen) {
			$("#page_id").chosen();
			$("#category_id").chosen();
			$("#template").chosen();
		}
		
		if ($frmCreatePage.length > 0 || $frmUpdatePage.length > 0) 
		{
			$.validator.addMethod("scmsURL", function(val, elem) {
			    
				if(val != '')
				{
					var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
					if(!regexp.test(val)) 
					{
						return false;
					} else {
						return true;
					}
				}else{
					return true;
				}
				
			}, myLabel.invalid_url);
		}
		if ($frmCreatePage.length > 0) 
		{
			$frmCreatePage.validate({
				rules: {
					url:{
						scmsURL: true
					}
			    },
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
			if(myLabel.locale_array.length > 0)
			{
				var locale_array = myLabel.locale_array;
				for(var i = 0; i < locale_array.length; i++)
				{
					var element = $("#i18n_page_name_" + locale_array[i]),
						locale = element.attr('lang');
					element.rules('add', {
						messages: {
					    	required: myLabel.field_required
					    }
					});
				}
			}
		}
		if ($frmUpdatePage.length > 0) {
			$frmUpdatePage.validate({
				rules: {
					url:{
						scmsURL: true
					}
			    },
				errorPlacement: function (error, element) {
					if(element.attr('name') == 'url')
					{
						error.insertAfter(element.parent().parent());
					}else{
						error.insertAfter(element.parent());
					}
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
		}
		
		function setInstallCode() 
		{
			var js_install_code = $('#js_hidden_code').val(),
				php_install_code = $('#php_hidden_code').val(),
				locale = $('#install_locale').val();
			
			if(locale != '')
			{
				js_install_code = js_install_code.replace(/\{LOCALE\}/g, '&locale=' + locale);
				php_install_code = php_install_code.replace(/\{php_LOCALE\}/g, '$Locale = ' + locale + '; ');
			}else{
				js_install_code = js_install_code.replace(/\{LOCALE\}/g, '');
				php_install_code = php_install_code.replace(/\{php_LOCALE\}/g, '');
			}
			
			if($("#install_hide").is(":checked"))
			{
				js_install_code = js_install_code.replace(/\{HIDE\}/g, '&hide=1');
				php_install_code = php_install_code.replace(/\{php_HIDE\}/g, '$Hide = 1; ');
			}else{
				js_install_code = js_install_code.replace(/\{HIDE\}/g, '');
				php_install_code = php_install_code.replace(/\{php_HIDE\}/g, '');
			}
			
			$('#js_install_code').val(js_install_code);
			$('#php_install_code').val(php_install_code);
		}
		
		if($('#js_install_code').length > 0)
		{
			setInstallCode();
		}
		
		function formatDefault (str, obj) {
			if (obj.role_id == 3) {
				return '<a href="#" class="status-icon status-' + (str == 'F' ? '0' : '1') + '" style="cursor: ' +  (str == 'F' ? 'pointer' : 'default') + '"></a>';
			} else {
				return '<a href="#" class="status-icon status-1" style="cursor: default"></a>';
			}
		}
		
		if ($("#grid").length > 0 && datagrid) {
			var gridOpts = {
				buttons: [{type: "edit", url: "index.php?controller=AdminPage&action=Update&id={:id}", title: myLabel.edit},
				          {type: "delete", url: "index.php?controller=AdminPage&action=DeletePage&id={:id}", title: myLabel.delete}],
				columns: [{text: myLabel.page, type: "text", sortable: true, editable: true, width: 300, editableWidth: 280},
						  {text: "Template", type: "text", sortable: true, editable: true, width: 100},
				          {text: myLabel.category, type: "text", sortable: true, editable: false, width: 100},
				          {text: myLabel.last_changed, type: "text", sortable: true, editable: false, width: 150},	
				          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 100, options: [
				                                                                                     {label: myLabel.active, value: "T"}, 
				                                                                                     {label: myLabel.inactive, value: "F"}
				                                                                                     ], applyClass: "status"}],
				dataUrl: "index.php?controller=AdminPage&action=GetPage",
				dataType: "json",
				fields: ['page_name', 'template', 'category', 'modified', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminPage&action=DeletePageBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminPage&action=SavePage&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			};
			if(Grid.isEditor == true)
			{
				gridOpts.buttons = [{type: "edit", url: "index.php?controller=AdminPage&action=Update&id={:id}", title: myLabel.edit},
			   				          {type: "delete", url: "index.php?controller=AdminPage&action=DeletePage&id={:id}", title: myLabel.delete},
							          {type: "menu", url: "#", text: myLabel.more, items:[
							              {text: myLabel.duplicate, url: "index.php?controller=AdminPage&action=Create&id={:id}"},
							              {text: myLabel.install, url: "index.php?controller=AdminPage&action=Install&id={:id}"},
							              {text: myLabel.preview, url: "{:url}", target: '_blank'}
							          ]}];
				if(Grid.isPageAllowed == false)
				{
					gridOpts.buttons = [{type: "edit", url: "index.php?controller=AdminPage&action=Update&id={:id}", title: myLabel.edit},
				   				          {type: "delete", url: "index.php?controller=AdminPage&action=DeletePage&id={:id}", title: myLabel.delete},
								          {type: "menu", url: "#", text: myLabel.more, items:[
								              {text: myLabel.install, url: "index.php?controller=AdminPage&action=Install&id={:id}"},
								              {text: myLabel.preview, url: "{:url}", target: '_blank'}
								          ]}];
				}
			}
			var $grid = $("#grid").datagrid(gridOpts);
		}
		
		if ($("#history_grid").length > 0 && datagrid) {
			
			var gridOpts = {
				buttons: [{type: "view", url: "index.php?controller=AdminPage&action=View&id={:id}&page_id={:page_id}", title: myLabel.view},
				          {type: "edit", url: "index.php?controller=AdminPage&action=Update&id={:page_id}", title: myLabel.edit},
				          {type: "delete", url: "index.php?controller=AdminPage&action=DeleteHistory&id={:id}", title: myLabel.delete}
				         ],
				columns: [{text: myLabel.page, type: "text", sortable: true, editable: false, width: 220},
				          {text: myLabel.datetime, type: "text", sortable: true, editable: false, width: 140},
				          {text: myLabel.user, type: "text", sortable: true, editable: false, width: 120},
				          {text: myLabel.ip, type: "text", sortable: true, editable: false, width: 90}],
				dataUrl: "index.php?controller=AdminPage&action=GetHistory" + Grid.queryString,
				dataType: "json",
				fields: ['page_name', 'modified', 'name', 'ip'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminPage&action=DeleteHistoryBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminPage&action=SaveHistory&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			};
			if(Grid.queryString != "")
			{
				gridOpts.buttons = [
				                    {type: "view", url: "index.php?controller=AdminPage&action=View&id={:id}&page_id={:page_id}", title: myLabel.view},
				                    {type: "edit", url: "index.php?controller=AdminPage&action=Update&id={:page_id}", title: myLabel.edit},
							        {type: "delete", url: "index.php?controller=AdminPage&action=DeleteHistory&id={:id}", title: myLabel.delete}
		        ];
				gridOpts.columns = [
				          {text: myLabel.datetime, type: "text", sortable: true, editable: false, width: 250},
				          {text: myLabel.user, type: "text", sortable: true, editable: false, width: 200},
				          {text: myLabel.ip, type: "text", sortable: true, editable: false, width: 120}
				];
				gridOpts.fields = ['modified', 'name', 'ip'];
			}
			var $history_grid = $("#history_grid").datagrid(gridOpts);
		}
		
		$(document).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			var content = $grid.datagrid("option", "content"),
				cache = {};
			$.extend(cache, {
				status: "",
				q: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminPage&action=GetPage", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("click", ".btn-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache"),
				obj = {};
			$this.addClass("button-active").siblings(".button").removeClass("button-active");
			obj.status = "";
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminPage&action=GetPage", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminPage&action=GetPage", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("submit", ".frm-history-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminPage&action=GetHistory", "modified", "DESC", content.page, content.rowCount);
			return false;
		}).on("click", ".button-detailed, .button-detailed-arrow", function (e) {
			e.stopPropagation();
			$(".form-filter-advanced").toggle();
		}).on("submit", ".frm-filter-advanced", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var obj = {},
				$this = $(this),
				arr = $this.serializeArray(),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			for (var i = 0, iCnt = arr.length; i < iCnt; i++) {
				obj[arr[i].name] = arr[i].value;
			}
			cache.q = "";
			if (cache.is_digital) {
				delete cache.is_digital;
			}
			if (cache.is_featured) {
				delete cache.is_featured;
			}
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminArticle&action=GetPage", "id", "DESC", content.page, content.rowCount);
			return false;
		}).on("change", "#page_id", function (e) {
			if($(this).val() == '')
			{
				window.location.href = "index.php?controller=AdminPage&action=History";
			}else{
				window.location.href = "index.php?controller=AdminPage&action=History&page_id=" + $(this).val();
			}
		}).on("change", "#install_method", function (e) {
			var method = $(this).val();
			
			if(method == 'php')
			{
				$('.scmsPhpBox').show();
				$('.scmsJsBox').hide();
			}else{
				$('.scmsPhpBox').hide();
				$('.scmsJsBox').show();
			}
		}).on("focusin", ".textarea_install", function (e) {
			$(this).select();
		}).on("change", "#install_locale", function (e) {
			setInstallCode();
		}).on("click", "#install_hide", function (e) {
			setInstallCode();
		});
		
		if($('#install_method').length > 0)
		{
			if($('#install_method').val() == 'php')
			{
				$('.scmsPhpBox').show();
				$('.scmsJsBox').hide();
			}else{
				$('.scmsPhpBox').hide();
				$('.scmsJsBox').show();
			}
		}
		if ($dialogView.length > 0 && dialog) 
		{
			$dialogView.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				width: 650,
				height: 450,
				open: function (event, ui) {
					$.get($dialogView.data('href')).done(function (data) {
						var title = $dialogView.attr('data-title');
						title = title.replace("{DATETIME}", $dialogView.data('datetime'));
						title = title.replace("{USER}", $dialogView.data('user'));
						$dialogView.dialog('option', 'title', title);
						$dialogView.html(data);
					});
					$(".scmsPreviewLink").remove();
					$('<a />', {
		                'class': 'scmsPreviewLink',
		                text: myLabel.preview,
		                href: 'index.php?controller=AdminPage&action=PreviewHistory&id=' + $dialogView.data('id'),
		                target: '_blank'
		            })
		            .appendTo($(".ui-dialog-buttonpane"));
				},
				buttons: (function () {
					var buttons = {};
					buttons[scApp.locale.button.restore] = function () {
						var restore_href = $dialogView.data('href');
						restore_href = restore_href.replace('ActionView', 'ActionRestore');
						$dialogRestore.data('href', restore_href).dialog("open");
					};
					buttons[scApp.locale.button.close] = function () {
						$dialogView.dialog("close");
					};
					
					return buttons;
				})()
			});
		}
		if ($dialogRestore.length > 0 && dialog) 
		{
			$dialogRestore.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				width: 450,
				height: 150,
				open: function () {
					
				},
				buttons: (function () {
					var buttons = {};
					buttons[scApp.locale.button.yes] = function () {
						$.get($dialogRestore.data('href')).done(function (data) {
							$dialogRestore.dialog("close");
						});
					};
					buttons[scApp.locale.button.no] = function () {
						$dialogRestore.dialog("close");
					};
					return buttons;
				})()
			});
		}
		$('#history_grid').on("click", '.table-icon-view', function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var i = 0;
			$(this).parent().siblings().each(function(e){
				if(i == 0)
				{
					$dialogView.data('id', $(this).find("input.table-select-row").val());
				}
				if(i == 1)
				{
					$dialogView.data('datetime', $(this).find(">:first-child").html());
				}
				if(i == 2)
				{
					$dialogView.data('user', $(this).find(">:first-child").html());
				}
				i++;
			});
			$dialogView.data('href', $(this).attr('href')).dialog("open");
			return false;
		});
		
		if ($frmCreatePage.length > 0 || $frmUpdatePage.length > 0) 
		{
			loadMceEditor();
		}
		
		function loadMceEditor() {
			tinymce.editors = [];
			tinymce.init({
			    selector: "textarea.mceEditor",
			    theme: "modern",
			    extended_valid_elements : 'i[class], span',
			    custom_elements : 'i[class], span',
			    force_br_newlines : false,
			    force_p_newlines : false,
			    forced_root_block : '',
			    language: "vi_VN",
			    width: 550,
			    height: 400,
			    verify_html: false,

			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor",
			         "responsivefilemanager codemirror"
		        ],
		        toolbar: "insertfile undo redo | styleselect | bold italic | fontselect | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | print preview media fullpage | forecolor backcolor emoticons | code | imageupload | link",
		        entity_encoding : "raw",
		        codemirror: {
		            indentOnInit: true, // Whether or not to indent code on init.
		            fullscreen: false,   // Default setting is false
		            path: 'codemirror', // Path to CodeMirror distribution
		            config: {           // CodeMirror config object
		               mode: 'application/x-httpd-php',
		               lineNumbers: false
		            },
		            width: 800,         // Default value is 800
		            height: 550,        // Default value is 550
		            jsFiles: [          // Additional JS files to load
		               'mode/clike/clike.js',
		               'mode/php/php.js'
		            ]
		         },		        
		        image_advtab: true,
			    menubar: "file edit insert view table tools",
			    setup: function (editor) {
			    	initImageUpload(editor);
			    	editor.on('change', function (e) {
			    		editor.editorManager.triggerSave();
			    		$(":input[name='" + editor.id + "']").valid();
			    	});
			    },
			    fontsize_formats: "8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 30pt 32pt 34pt 36pt 38pt 40pt 42pt",
			    style_formats: [
	    	      {title: 'Header 1', format: 'h1'},
			      {title: 'Header 2', format: 'h2'},
			      {title: 'Header 3', format: 'h3'},
			      {title: 'Header 4', format: 'h4'},
			      {title: 'Header 5', format: 'h5'},
			      {title: 'Header 6', format: 'h6'}
			    ],
			    external_filemanager_path: "core/third-party/filemanager/",
			    filemanager_title: "Responsive Filemanager" ,
			    external_plugins: {"filemanager": "../filemanager/plugin.min.js"}
			});			
		}
		function initImageUpload(editor) {
		  // create input and insert in the DOM
		  var inp = $('<input id="tinymce-uploader" type="file" name="image" accept="image/*" style="display:none">');
		  $(editor.getElement()).parent().append(inp);

		  // add the image upload button to the editor toolbar
		  editor.addButton('imageupload', {
		    text: '',
		    icon: 'image',
		    onclick: function(e) { // when toolbar button is clicked, open file select modal
		      inp.trigger('click');
		    }
		  });

		  // when a file is selected, upload it to the server
		  inp.on("change", function(e){
		    uploadFile($(this), editor);
		  });
		}

		function uploadFile(inp, editor) {
		  var input = inp.get(0);
		  var data = new FormData();
		  data.append('image', input.files[0]);

		  $.ajax({
		    url: 'index.php?controller=Gallery&action=UploadPageImage',
		    type: 'POST',
		    data: data,
		    processData: false, // Don't process the files
		    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
		    success: function(data, textStatus, jqXHR) {
		       var json = JSON.parse(data);
		      editor.insertContent('<img class="img-responsive" src="' + json.url + '"/>');
		    },
		    error: function(jqXHR, textStatus, errorThrown) {
		      if(jqXHR.responseText) {
		        errors = JSON.parse(jqXHR.responseText).errors
		        alert('Error uploading image: ' + errors.join(", ") + '. Make sure the file is an image and has extension jpg/jpeg/png.');
		      }
		    }
		  });
		}
	});
})(jQuery_1_8_2);