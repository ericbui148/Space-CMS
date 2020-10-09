var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
	
		var $frmCreateCategory = $('#frmCreateCategory'),
			$frmUpdateCategory = $('#frmUpdateCategory'),
			datagrid = ($.fn.datagrid !== undefined),
			validate = ($.fn.validate !== undefined);
			
		function formatName(val, obj) {
			return Array(obj.deep+1).join('-------') + " " + val.name;
		}
		function formatDown(val, obj) {
			return (obj.down === 1) ? ['<a href="index.php?controller=AdminCategories" class="arrow_down" rev="down" rel="', val.id , '" title="', myLabel.down, '"></a>'].join("") : '';
		}
		function formatUp(val, obj) {
			return (obj.up === 1) ? ['<a href="index.php?controller=AdminCategories" class="arrow_up" rev="up" rel="', val.id , '" title="', myLabel.up, '"></a>'].join("") : '';
		}
		
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminCategories&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminCategories&action=DeleteCategory&id={:id}"}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: false, editable: false, renderer: formatName, width: 400},
				          {text: myLabel.products, type: "text", sortable: false, editable: false, align: "center"},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatUp, width: 21}
				       ],
				dataUrl: "index.php?controller=AdminCategories&action=GetCategory" + "&rowCount=50",
				dataType: "json",
				fields: ['data', 'products', 'data', 'data'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminCategories&action=DeleteCategoryBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminCategories&action=SaveCategory&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		function formatProductName(val, obj) {
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
		
		function formatProductImage (path, obj) {
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
		
		function formatProductDown(obj) {
			return (obj.down === 1) ? ['<a href="index.php?controller=AdminItemSorts&action=GetItemSort'+Grid.queryString+'" class="p_arrow_down" rev="down" rel="', obj.id , '" title="', myLabel.down, '"></a>'].join("") : '';
		}
		
		function formatProductUp(obj) {
			return (obj.up === 1) ? ['<a href="index.php?controller=AdminItemSorts&action=GetItemSort'+Grid.queryString+'" class="p_arrow_up" rev="up" rel="', obj.id , '" title="', myLabel.up, '"></a>'].join("") : '';
		}
		
		if ($("#product_grid").length > 0 && datagrid) {
			
			var $grid = $("#product_grid").datagrid({
				columns: [{text: myLabel.image, type: "text", sortable: false, editable: false, renderer: formatProductImage},
				          {text: myLabel.name, type: "text", sortable: false, editable: false, width: 180},
				          {text: myLabel.sku, type: "text", sortable: false, editable: false},
				          {text: myLabel.price, type: "text", sortable: false, editable: false, renderer: formatMinPrice, align: "right", width: 70},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatProductDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatProductUp, width: 21}
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
		
		if ($frmCreateCategory.length > 0 && validate) {
			$frmCreateCategory.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				}
			});
		}
		
		if ($frmUpdateCategory.length > 0 && validate) {
			$frmUpdateCategory.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				}
			});
		}
		
		$("#content").on("click", ".p_arrow_up, .p_arrow_down", function (e) {
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
		}).on("click", ".arrow_up, .arrow_down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminCategories&action=SetOrder", {
				"id": $(this).attr("rel"),
				"direction": $(this).attr("rev")
			}).done(function (data) {
				var content = $grid.datagrid("option", "content");
				$grid.datagrid("load", "index.php?controller=AdminCategories&action=GetCategory", "id", "ASC", content.page, content.rowCount);
			});
			return false;
		});
		
		if ($frmCreateCategory.length > 0 || $frmUpdateCategory.length > 0) 
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
			    height: 200,
			    verify_html: false,

			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor",
			         "codemirror"
		        ],
		        toolbar: "insertfile undo redo | styleselect | bold italic | fontselect | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | print preview media fullpage | forecolor backcolor emoticons | table | code | imageupload | fullscreen | link",
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
				menubar: false,
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
			tinymce.init({
			    selector: "textarea.mceEditor_short",
			    theme: "modern",
			    extended_valid_elements : 'i[class], span',
			    custom_elements : 'i[class], span',
			    force_br_newlines : false,
			    force_p_newlines : false,
			    forced_root_block : '',
			    language: "vi_VN",
			    width: 550,
			    height: 200,
			    verify_html: false,

			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor",
			         "responsivefilemanager codemirror"
		        ],
		        toolbar: "styleselect | bold italic | fontselect | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent forecolor backcolor emoticons | table | code | imageupload | fullscreen | link",
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
				menubar: false,
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
		    url: 'index.php?controller=Gallery&action=UploadProductCategoryImage',
		    type: 'POST',
		    data: data,
		    processData: false, // Don't process the files
		    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
		    success: function(data, textStatus, jqXHR) {
		       var json = JSON.parse(data);
		      editor.insertContent('<img class="img-fluid" src="' + json.url + '"/>');
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