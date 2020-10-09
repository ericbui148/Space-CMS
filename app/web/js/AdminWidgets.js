var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateWidget = $("#frmCreateWidget"),
			$frmUpdateWidget = $("#frmUpdateWidget"),
			dialog = ($.fn.dialog !== undefined),
			spinner = ($.fn.spinner !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			chosen = ($.fn.chosen !== undefined),
			tipsy = ($.fn.tipsy !== undefined);
		
		if (tipsy) {
			$(".listing-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				gravity: "nw",
				className: "tipsy-listing"
			});
		}
		if (chosen) {
			$("#type").chosen();
			$("#template").chosen();
			if ($("#page_id").length) {
				$("#page_id").chosen();
			}
			if ($("#article_cat_id").length) {
				$("#article_cat_id").chosen();
			}
			if ($("#menu_id").length) {
				$("#menu_id").chosen();
			}
			if ($("#slider_id").length) {
				$("#slider_id").chosen();
			}
			if ($("#product_cat_id").length) {
				$("#product_cat_id").chosen();
			}
		}
		if ($frmCreateWidget.length > 0) {
			$frmCreateWidget.validate({
				rules:{
					"manager_id":{
						positiveNumber: true
					}
				},
				errorPlacement: function (error, element) {
					var name = element.attr('name');
					if(name == 'manager_id')
					{
						error.insertAfter(element.parent().parent());
					}else{
						error.insertAfter(element.parent());
					}
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
			$.validator.addMethod('positiveNumber',
			    function (value) { 
			        return Number(value) >= 0;
			    }, 
			    myLabel.positiveNumber
			);
		}
		
		
		
		function formatPrice(str, obj) {
			return obj.price_format;
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
			         "codemirror"
		        ],
		        toolbar: "insertfile undo redo | styleselect | bold italic | fontselect | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | print preview media fullpage | forecolor backcolor emoticons | code | imageupload",
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
		    url: 'index.php?controller=Gallery&action=UploadWidgetImage',
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
		
		if ($("#grid").length > 0 && datagrid) {
			
			var options = {
				buttons: [{type: "edit", url: "index.php?controller=AdminWidgets&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminWidgets&action=DeleteWidget&id={:id}"}
				          ],
				columns: [
				          {text: myLabel.name, type: "text", sortable: true, editable: true, width: 150},
				          {text: "Kiểu", type: "text", sortable: true, editable: false, width: 100	},
				          {text: "Template", type: "text", sortable: true, editable: false, width: 150	},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 120, options: [
									                                                                                     {label: myLabel.active, value: "T"}, 
									                                                                                     {label: myLabel.inactive, value: "F"}
									                                                                                     ], applyClass: "status"}
				        ],
				dataUrl: "index.php?controller=AdminWidgets&action=GetWidget",
				dataType: "json",
				fields: ['name','type_name','template', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminWidgets&action=DeleteWidgetBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminWidgets&action=SaveWidget&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			};
			
			var m = window.location.href.match(/&is_active=(\d+)/);
			if (m !== null) {
				options.cache = {"is_active" : m[1]};
			}
			
			var $grid = $("#grid").datagrid(options);
		}
		
		function formatName(val, obj) {
			var parts, arr = [];
			for (var i = 0, iCnt = obj.stock_attr.length; i < iCnt; i++) 
			{
				parts = obj.stock_attr[i].split("~:~");
				if(parts.length == 2)
				{
					arr.push(parts[0] + ": " + parts[1]);
				}
			}
			return ['<a href="index.php?controller=AdminProducts&action=Update&id=', obj.product_id, '&tab=4" class="s-Pic"><img src="', obj.pic, '" alt="" class="s-Img" /></a>',
			        '<span class="s-Name"><a href="index.php?controller=AdminProducts&action=Update&id=', obj.product_id, '&tab=4">', obj.name, '</a></span>',
			        (arr.length > 0 ? ['<span class="s-Attr">(', arr.join(", "), ')</span>'].join('') : '')
			        ].join("");
		}
		
		function formatPrice(val, obj) {
			return obj.price_formated;
		}
		
		function formatImage (path, obj) {
			var src = 'app/web/img/frontend/80x106.png';
			if (path !== null && path.length > 0) {
				src = path;
			}
			return ['<a href="index.php?controller=AdminProducts&action=Update&id=', obj.id, '"><img src="', src, '" alt="" class="s-Img" /></a>'].join('');
		}
		
		function formatStock (stock, obj) {
			if(stock == 0 || stock == '0')
			{
				return '<span class="bold red">'+stock+'</span>';
			}else{
				return stock;
			}
		}
		
		function formatMinPrice (price, obj) {
			return obj.min_price_format;
		}
		
		function formatDown(obj) {
			return (typeof obj !== 'undefined' && obj.down === 1) ? ['<a href="index.php?controller=AdminItemSorts&action=GetItemSort'+Grid.queryString+'" class="arrow_down" rev="down" rel="', obj.id , '" title="', myLabel.down, '"></a>'].join("") : '';
		}
		function formatUp(obj) {
			return (typeof obj !== 'undefined' && obj.up === 1) ? ['<a href="index.php?controller=AdminItemSorts&action=GetItemSort'+Grid.queryString+'" class="arrow_up" rev="up" rel="', obj.id , '" title="', myLabel.up, '"></a>'].join("") : '';
		}
		
		if ($("#product_grid").length > 0 && datagrid) {
			
			var $grid = $("#product_grid").datagrid({
				columns: [{text: myLabel.image, type: "text", sortable: false, editable: false, renderer: formatImage},
				          {text: myLabel.name, type: "text", sortable: false, editable: false, width: 180},
				          {text: myLabel.sku, type: "text", sortable: false, editable: false},
				          {text: myLabel.price, type: "text", sortable: false, editable: false, renderer: formatMinPrice, align: "right", width: 70},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatUp, width: 21}
				          ],
				dataUrl: "index.php?controller=AdminItemSorts&action=GetItemSorts&rowCount=50" + Grid.queryString,
				dataType: "json",
				fields: ['pic', 'name', 'sku', 'min_price', 'item_sort', 'item_sort'],
				paginator: {
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		function formatArticleName(val, obj) {

			return ['<a href="index.php?controller=AdminArticle&action=Update&id=', obj.id, '">',obj.article_name,'</a>'].join('');
		}
		
		function formatArticleImage (path, obj) {
			var src = 'app/web/img/frontend/80x106.png';
			if (path !== null && path.length > 0) {
				src = path;
			}
			return ['<a href="index.php?controller=AdminArticle&action=Update&id=', obj.id, '"><img width="80" src="', src, '" alt="" class="s-Img" /></a>'].join('');
		}
		if ($("#article_grid").length > 0 && datagrid) {
			
			var $grid = $("#article_grid").datagrid({
				columns: [{text: myLabel.image, type: "text", sortable: false, editable: false, width: 80, renderer: formatArticleImage},
				          {text: myLabel.name, type: "text", sortable: false, editable: false, width: 250, renderer: formatArticleName},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatUp, width: 21}
				          ],
				dataUrl: "index.php?controller=AdminItemSorts&action=GetItemSorts&rowCount=50" + Grid.queryString,
				dataType: "json",
				fields: ['avatar_file', 'article_name', 'item_sort', 'item_sort'],
				paginator: {
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				}
			});
		}
		
		$(document).on("click", ".arrow_up, .arrow_down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminItemSorts&action=SetOrder", {
				"id": $(this).attr("rel"),
				"direction": $(this).attr("rev")
			}).done(function (data) {
				var content = $grid.datagrid("option", "content");
				$grid.datagrid("load", "index.php?controller=AdminItemSorts&action=GetItemSorts&rowCount=50" + Grid.queryString, "id", "ASC", content.page, content.rowCount);
			});
			return false;
		}).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				is_active: "",
				q: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminWidgets&action=GetWidget", "name", "ASC", content.page, content.rowCount);
			return false;
		}).on("change", ".widget_type",function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			
			var widgetType = $(this).val();
			$.post("index.php?controller=AdminWidgets&action=SelectWidgetType", {
				"widget_type": widgetType
			}).done(function (data) {
				$("#widget_type_content").html(data);
				if(widgetType == 1) {
					loadMceEditor();
				}
				
				if ($("#page_id").length) {
					$("#page_id").chosen();
				}
				
				if ($("#article_cat_id").length) {
					$("#article_cat_id").chosen();
				}
				
				if ($("#menu_id").length) {
					$("#menu_id").chosen();
				}
				if ($("#slider_id").length) {
					$("#slider_id").chosen();
				}
				if ($("#product_cat_id").length) {
					$("#product_cat_id").chosen();
				}
				
			});
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
			obj.is_active = "";
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminWidgets&action=GetWidget", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminWidgets&action=GetWidget", "id", "ASC", content.page, content.rowCount);
			return false;
		});
		
		if($frmUpdateWidget.length > 0) {
			var widgetType = $("#type").val();
			var widgetData = $("#widget_data").val();
			var widgetId = $("#widget_id").val();
			$.post("index.php?controller=AdminWidgets&action=SelectWidgetType", {
				"widget_type": widgetType,
				"widget_data": widgetData,
				"widget_id": widgetId
			}).done(function (data) {
				$("#widget_type_content").html(data);
				if(widgetType == 1) {
					loadMceEditor();
				}
				
				if ($("#page_id").length) {
					$("#page_id").chosen();
				}
				if ($("#article_cat_id").length) {
					$("#article_cat_id").chosen();
				}
				
				if ($("#menu_id").length) {
					$("#menu_id").chosen();
				}
				if ($("#slider_id").length) {
					$("#slider_id").chosen();
				}
				if ($("#product_cat_id").length) {
					$("#product_cat_id").chosen();
				}
			});
			return false;
		}
		

	});
})(jQuery_1_8_2);