var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	if (window.tinyMCE !== undefined) {
		tinymce.init({
		    selector: "textarea.selector-full-desc",
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
		
		tinymce.init({
			    selector: "textarea.selector-short-desc",
			    theme: "modern",
			    extended_valid_elements : 'i[class], span',
			    custom_elements : 'i[class], span',
			    force_br_newlines : false,
			    force_p_newlines : false,
			    forced_root_block : '',
			    language: "vi_VN",
			    width: 550,
			    height: 120,
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
		    url: 'index.php?controller=Gallery&action=UploadProductImage',
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
	}
	$(function () {
		"use strict";
		
		var $frmCreateProduct = $('#frmCreateProduct'),
			$frmUpdateProduct = $('#frmUpdateProduct'),
			$frmProduct = $('.frmProduct'),
			$frmPrintSelectedStock = $('#frmPrintSelectedStock'),
			$tabs = $("#tabs"),
			chosen = ($.fn.chosen !== undefined),
			$gallery = $("#gallery"),
			$dialogDeleteExtra = $("#dialogDeleteExtra"),
			$dialogCopyExtra = $("#dialogCopyExtra"),
			$dialogCopyAttr = $("#dialogCopyAttr"),
			$dialogDeleteStock = $("#dialogDeleteStock"),
			$dialogImageStock = $("#dialogImageStock"),
			$dialogDeleteDigital = $("#dialogDeleteDigital"),
			
			$dialogAttrGroupDelete = $("#dialogAttrGroupDelete"),
			$dialogAttrDelete = $("#dialogAttrDelete"),
			$dialogDeleteProduct = $("#dialogDeleteProduct"),
		
			$content = $("#content"),
			$datepick = $(".datepick"),
			gallery = ($.fn.gallery !== undefined),
			multiselect = ($.fn.multiselect !== undefined),
			dialog = ($.fn.dialog !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			autocomplete = ($.fn.autocomplete !== undefined),
			dOpts = {},
			vOpts = {
				ignore: ".ignore",
				rules: {
					name: "required",
					sku: {
						required: true,
						remote: "index.php?controller=AdminProducts&action=CheckSku"
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				invalidHandler: function (event, validator) {
				    if (validator.numberOfInvalids()) {
				    	var index = $(validator.errorList[0].element, this).closest("div[id^='tabs-']").index();
				    	if ($tabs.length > 0 && tabs && index !== -1) 
				    	{
				    		$tabs.tabs(tOpt).tabs("option", "active", index-1);
				    	}
				    };
				}
			},
			m = window.location.href.match(/&id=(\d+)/),
			product_id, $similar
		;
		
		if (m !== null) {
			product_id = m[1];
		}
		
		if (chosen) {
			$("#category_id").chosen();
			$("#tag_id").chosen();
		}
		
		if ($datepick.length > 0) {
			dOpts = $.extend(dOpts, {
				firstDay: $datepick.attr("rel"),
				dateFormat: $datepick.attr("rev")
			});
		}
		
		if ($gallery.length > 0 && gallery) {
			$gallery.gallery({
				compressUrl: "index.php?controller=Gallery&action=CompressProductGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				getUrl: "index.php?controller=Gallery&action=GetProductGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				deleteUrl: "index.php?controller=Gallery&action=DeleteProductGallery",
				emptyUrl: "index.php?controller=Gallery&action=EmptyProductGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				rebuildUrl: "index.php?controller=Gallery&action=RebuildProductGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				resizeUrl: "index.php?controller=Gallery&action=ResizeProductGallery&id={:id}&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash + ($frmUpdateProduct.length > 0 ? "&query_string=" + encodeURIComponent("controller=AdminProducts&action=Update&id=" + myGallery.foreign_id + "&tab=4") : ""),
				rotateUrl: "index.php?controller=Gallery&action=RotateProductGallery",
				sortUrl: "index.php?controller=Gallery&action=SortProductGallery",
				updateUrl: "index.php?controller=Gallery&action=UpdateProductGallery",
				uploadUrl: "index.php?controller=Gallery&action=UploadProductGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				watermarkUrl: "index.php?controller=Gallery&action=WatermarkProductGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash
			});
		}
		
		$content.delegate(".datepick", "focusin", function (e) {
			$(this).datepicker(dOpts);
		}).delegate("input[name='digital_choose']", "change", function () {
			switch (parseInt($(this).val(), 10)) {
			case 1:
				$(".digitalFile").show();
				$(".digitalPath").hide();
				break;
			case 2:
				$(".digitalFile").hide();
				$(".digitalPath").show();
				break;
			}
		}).delegate(".btnDigitalDelete", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("#dialogDeleteDigital").data("id", $(this).attr("rel")).dialog("open");
			return false;
		// Images
		}).delegate(".btnImageStock", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("#dialogImageStock").data("lnk", $(this)).dialog("open");
			return false;
		// -- Extras
		}).delegate(".btnAddExtra", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $clone = $("#boxClone").clone(),
				c = $clone.html(),
				index1 = Math.ceil(Math.random() * 999999).toString(),
				index2 = Math.ceil(Math.random() * 999999).toString();
			c = c.replace(/\{INDEX\}/g, "x_" + index1);
			c = c.replace(/\{X\}/g, "y_" + index2);
			//$(this).before(c);
			if($('#boxExtras').find('.extraBox').length > 0)
			{
				$(this).parent().prev().append(c);
			}else{
				$(this).parent().prev().html(c);
			}
			return false;
		}).delegate(".btnAddExtraItem", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $clone = $("#boxCloneTbl").find("tbody").clone(),
				c = $clone.html(),
				index2 = Math.ceil(Math.random() * 999999).toString();
			c = c.replace(/\{INDEX\}/g, $(this).data("index"));
			c = c.replace(/\{X\}/g, "y_" + index2);
			$(c).appendTo($(this).siblings("table").eq(0).find("tbody"));
			return false;
		}).delegate(".btnDeleteExtra", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("#dialogDeleteExtra").data("lnk", $(this)).dialog("open");
			return false;
		}).delegate(".btnDeleteExtraTmp", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).closest(".extraBox").remove();
			if($('#boxExtras').find('.extraBox').length == 0)
			{
				$('#boxExtras').html(myLabel.no_extras);
			}
			return false;
		}).delegate(".btnRemoveExtraItem", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).parent().parent().remove();
			return false;
		}).delegate(":input[name^='extra_type[']", "change", function () {
			var $this = $(this),
				$boxSingle = $this.closest(".extraBox").find(".boxSingle"),
				$boxMulti = $this.closest(".extraBox").find(".boxMulti");
			switch ($("option:selected", $this).val()) {
			case 'single':
				$boxSingle.find(":input").prop("disabled", false);
				$boxSingle.show();
				$boxMulti.hide();
				$boxMulti.find(":input").prop("disabled", true);
				break;
			case 'multi':
				$boxSingle.hide();
				$boxSingle.find(":input").prop("disabled", true);
				$boxMulti.find(":input").prop("disabled", false);
				$boxMulti.show();
				break;
			}
		}).on("click", ".btnCopyExtra", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if (dialog && $dialogCopyExtra.length > 0) {
				$dialogCopyExtra.dialog("open");
			}
			return false;
		// -- Digital
		}).on("change", "input[name='is_digital']", function (e) {
			if ($(this).is(":checked")) {
				$tabs.tabs("option", "disabled", [2]);
				$('#boxDigitalOuter').show();
			} else {
				$tabs.tabs("option", "disabled", []);
				$('#boxDigitalOuter').hide();
			}
		// -- Attributes
		}).on("click", ".btnAddAttribute", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $clone = $("#boxAddAttribute").clone(),
				$this = $(this),
				c = $clone.html(),
				index1 = Math.ceil(Math.random() * 999999).toString(),
				index2 = Math.ceil(Math.random() * 999999).toString();
			c = c.replace(/\{INDEX\}/g, "x_" + index1).replace(/\{X\}/g, "y_" + index2);
			if($('#boxAttributes').find('.attrBox') > 0)
			{
				$(c).appendTo("#boxAttributes");
			}else{
				$('#boxAttributes').html(c);
			}
			fireGroupSortable();
			fireItemSortable();
			return false;
		}).on("click", ".btnAddAttr", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $clone = $("#boxAddAttr").clone(),
				$this = $(this),
				c = $clone.html(),
				index = Math.ceil(Math.random() * 999999).toString();
			c = c.replace(/\{INDEX\}/g, $this.attr("rel")).replace(/\{X\}/g, "y_" + index);
			$(c).appendTo( $this.closest(".attrBox").find(".attrBoxRowStick") );
			fireItemSortable();
			return false;
		}).on("click", ".btnAttrGroupDelete", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$dialogAttrGroupDelete.data("id", $(this).data("id")).dialog("open");
			return false;
		}).on("click", ".btnAttrGroupRemove", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).closest(".attrBox").remove();
			if($('#boxAttributes').find('.attrBox').length == 0)
			{
				$('#boxAttributes').html(myLabel.no_attrs);
			}
			fireGroupSortable();
			return false;
		}).on("click", ".btnAttrDelete", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$dialogAttrDelete.data("id", $(this).data("id")).dialog("open");
			return false;
		}).on("click", ".btnAttrRemove", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).closest(".attrBoxRowItems").remove();
			fireItemSortable();
			return false;
		}).delegate(".btnCopyAttribute", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("#dialogCopyAttr").dialog("open");
			return false;
		// -- Attributes
			
		// Stock
		}).delegate(".btnStockAdd", "click", function (e) {			
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $clone, c, index,
				$tbody = $(this).parent().siblings('.stockContainer').children(".table").find("tbody");
			
			handleDigitalInStock.call(null, function () {
				$clone = $("#boxStockCloneTbl").find("tbody").clone();
				c = $clone.html();
				index = Math.ceil(Math.random() * 999999).toString();
				c = c.replace(/\{INDEX\}/g, "x_" + index);
				$(c).appendTo($tbody);
				if($('#scHiddenImageId').length > 0)
				{
					var $btnImage = $tbody.find('tr:last').find('.btnImageStock'),
						id = $('#scHiddenImageId').val(),
						src = $('#scHiddenImageId').attr('data-src');
					
					var $a = $("<a>", {
							"href": "#"
						}).addClass("btnImageStock").attr("rel", id);
					
					$("<img>", {
						"src": src
					}).addClass("in-stock").appendTo($a);
					
					$btnImage.siblings("span").find("input[name^='stock_image_id']").val(id).valid();
					$btnImage.replaceWith($a);
				}
			});
			return false;
		}).delegate(".btnRemoveStock", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).parent().parent().remove();
			return false;
		}).delegate(".btnDeleteStock", "click", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("#dialogDeleteStock").data("lnk", $(this)).dialog("open");
			return false;
		});
		
		function closeAttr() {
			$("#boxAttributes").find(".attrBox").find(".attr, .attr_item").each(function (i, el) {
				$(el).find("input, a").hide().end().find("abbr").show();
			});
		}
		
		if ($frmCreateProduct.length > 0) {
			var validator = $frmCreateProduct.submit(function() {
				// update underlying textarea before submit validation
				tinymce.activeEditor.editorManager.triggerSave();
			}).validate(vOpts);
		}
		
		if ($frmUpdateProduct.length > 0) {
			//$.validator.messages.required = "";
			vOpts.rules.sku.remote += "&id=" + $frmUpdateProduct.find("input[name='id']").val()
			var validator = $frmUpdateProduct.submit(function() {
				handleDigitalInStock.call(null);
				// update underlying textarea before submit validation
				tinymce.activeEditor.editorManager.triggerSave();
			}).validate(vOpts);
			fireGroupSortable();
			fireItemSortable();
		}
		
		function handleDigitalInStock(callback) {
			var $tbody = $(".btnStockAdd").parent().siblings(".table").find("tbody");
			if ($("input[name='is_digital']").is(":checked") && $tbody.find("tr").length > 0) {
				$tbody.find("tr:gt(0)").remove();
			} else {
				if (callback !== undefined && typeof callback === "function") {
					callback.call(null);
				}
			}
		}
		
		var tOpt = {};
		if ($tabs.length > 0) {
			tOpt = {
				select: function (event, ui) {
					$("input[name='tab']").val(ui.index);
					switch (ui.tab.hash) {
						case "#tabs-5":
							handleDigitalInStock.call(null);
							break;
						case "#tabs-7":
							if ($("#boxSimilar").length > 0 && datagrid) {
								
								$similar = $("#boxSimilar").datagrid({
									buttons: [{type: "delete", url: "index.php?controller=AdminProducts&action=DeleteSimilar&id={:id}"}],
									columns: [{text: myLabel.name, type: "text", sortable: true, editable: false},
									          {text: myLabel.sku, type: "text", sortable: true, editable: false},
									          {text: myLabel.status, type: "select", sortable: true, editable: false, options: [
									                                                                                     {label: $.datagrid.messages.pr_status_1, value: 1}, 
									                                                                                     {label: $.datagrid.messages.pr_status_2, value: 2},
									                                                                                     {label: $.datagrid.messages.pr_status_3, value: 3}
									                                                                                     ], applyClass: "status"}],
									dataUrl: "index.php?controller=AdminProducts&action=GetSimilar&id=" + product_id,
									dataType: "json",
									fields: ['name', 'sku', 'status'],
									paginator: {
										actions: [
										   {text: myLabel.delete_selected, url: "index.php?controller=AdminProducts&action=DeleteSimilarBulk", render: true, confirmation: myLabel.delete_confirmation}
										],
										gotoPage: true,
										paginate: true,
										total: true,
										rowCount: true
									},
									saveUrl: null,
									select: {
										field: "id",
										name: "record[]"
									}
								});
								
							}
							break;
					}
				},
				ajaxOptions: {
					success: function () {}
				}
			};
			if ($("input[name='is_digital']").is(":checked")) {
				tOpt.disabled = [2];
			}
			$tabs.tabs(tOpt);
			
			var m = window.location.href.match(/&tab=(\d+)/);
			if (m !== null) {
				$tabs.tabs("option", "active", m[1]);
			}
		}
		
		if ($dialogAttrGroupDelete && dialog) {
			$dialogAttrGroupDelete.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: {
					'Delete': function () {
						$.post("index.php?controller=AdminProducts&action=AttrGroupDelete", {
							"id": $dialogAttrGroupDelete.data("id")
						}).done(function () {
							getAttributes.call(null);
							$dialogAttrGroupDelete.dialog("close");
						});
					},
					'Cancel': function () {
						$(this).dialog("close");
					}
				}
			});
		}
				
		if ($dialogAttrDelete && dialog) {
			$dialogAttrDelete.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: {
					'Delete': function () {
						$.post("index.php?controller=AdminProducts&action=AttrDelete", {
							"id": $dialogAttrDelete.data("id")
						}).done(function () {
							getAttributes.call(null);
							$dialogAttrDelete.dialog("close");
						});
					},
					'Cancel': function () {
						$(this).dialog("close");
					}
				}
			});
		}
		
		if ($dialogDeleteExtra.length > 0 && dialog) {
			$dialogDeleteExtra.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: {
					'Delete': function() {
						var $lnk = $(this).data('lnk');
						$.post("index.php?controller=AdminProducts&action=DeleteExtra", {
							id: $lnk.attr("rel")
						}).done(function (data) {
							if (!data.code) {
								return;
							}
							switch (parseInt(data.code, 10)) {
								case 200:
									$lnk.closest(".extraBox").remove();
									if($('#boxExtras').find('.extraBox').length == 0)
									{
										$('#boxExtras').html(myLabel.no_extras);
									}
									break;
							}
						});
						$(this).dialog('close');			
					},
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
		if ($dialogDeleteStock.length > 0 && dialog) {
			$dialogDeleteStock.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: {
					'Delete': function() {
						var $lnk = $(this).data('lnk');
						$.post("index.php?controller=AdminProducts&action=DeleteStock", {
							id: $lnk.attr("rel")
						}).done(function (data) {
							if (!data.code) {
								return;
							}
							switch (parseInt(data.code, 10)) {
								case 200:
									$lnk.parent().parent().remove();
									break;
							}
						});
						$(this).dialog('close');			
					},
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
		if ($dialogImageStock.length > 0 && dialog) {
			$dialogImageStock.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 600,
				height: 400,
				open: function () {
					var $lnk = $(this).data("lnk");
					$.get("index.php?controller=AdminProducts&action=LoadImages", {
						product_id: $(":input[name='id']").val(),
						image_id: $lnk.attr("rel")
					}).done(function (data) {
						$dialogImageStock.html(data);
					});
				},
				close: function () {
					$dialogImageStock.html("");
				},
				buttons: {
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
		if ($dialogDeleteDigital.length > 0 && dialog) {
			$dialogDeleteDigital.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: {
					'Delete': function() {
						var $this = $(this);
						$.post("index.php?controller=AdminProducts&action=DeleteDigital", {
							id: $this.data("id")
						}).done(function (data) {
							$("#boxDigital").html(data);
						});
						$(this).dialog('close');			
					},
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
		if ($dialogCopyExtra.length > 0 && dialog) {
			$dialogCopyExtra.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 640,
				open: function () {
					$dialogCopyExtra.html("");
					var cOpt = {};
					if ($frmUpdateProduct.length > 0) {
						cOpt.product_id = $frmUpdateProduct.find("input[name='id']").val();
					}
					$.get("index.php?controller=AdminProducts&action=GetProducts&copy=Extra", cOpt).done(function (data) {
						$dialogCopyExtra.html(data);
						$dialogCopyExtra.dialog("option", "position", "center");
						$dialogCopyExtra.find(".btnCopy").button();
					});
				},
				buttons: {
					'Close': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
		if ($dialogCopyAttr.length > 0 && dialog) {
			$dialogCopyAttr.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 640,
				open: function () {
					$dialogCopyAttr.html("");
					var cOpt = {};
					if ($frmUpdateProduct.length > 0) {
						cOpt.product_id = $frmUpdateProduct.find("input[name='id']").val();
					}
					$.get("index.php?controller=AdminProducts&action=GetProducts&copy=Attr", cOpt).done(function (data) {
						$dialogCopyAttr.html(data);
						$dialogCopyAttr.dialog("option", "position", "center");
						$dialogCopyAttr.find(".btnCopy").button();
					});
				},
				buttons: {
					'Close': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
		if ($dialogDeleteProduct.length > 0 && dialog) {
			$dialogDeleteProduct.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 450,
				buttons: {
					'Make inactive': function () {
						$.get("index.php?controller=AdminProducts&action=Deactivate&id=" + $dialogDeleteProduct.data().id).done(function (data) {
							if(data.status == 'OK')
							{
								if ($("#grid").length > 0 && datagrid) 
								{
									var content = $grid.datagrid("option", "content"),
										cache = $grid.datagrid("option", "cache");
									$grid.datagrid("option", "cache", cache);
									$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "name", "ASC", content.page, content.rowCount);
								}
								$dialogDeleteProduct.dialog("close");
							}
						});
					},
					'Delete': function () {
						$.get($dialogDeleteProduct.data().href).done(function (data) {
							if(data.status == 'OK')
							{
								if ($("#grid").length > 0 && datagrid) 
								{
									var content = $grid.datagrid("option", "content"),
										cache = $grid.datagrid("option", "cache");
									$grid.datagrid("option", "cache", cache);
									$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "name", "ASC", content.page, content.rowCount);
								}
								$dialogDeleteProduct.dialog("close");
							}
						});
					},
					'Cancel': function () {
						$dialogDeleteProduct.dialog("close");
					}
				}
			});
		}
		
		if ($("#similar_id").length > 0 && autocomplete) {
			
			var cache = {}, lastXhr;
			$("#similar_id").autocomplete({
				minLength: 2,
				source: function(request, response) {
					var term = request.term;
					//if (term in cache) {
						//response(cache[term]);
						//return;
					//}
					lastXhr = $.getJSON("index.php?controller=AdminProducts&action=SearchProducts&id=" + product_id, request, function(data, status, xhr) {
						//cache[term] = data;
						if (xhr === lastXhr) {
							response(data);
						}
					});
				},
				select: function(event, ui) {
					$.post("index.php?controller=AdminProducts&action=AddSimilar", {
						"product_id": product_id,
						"similar_id": ui.item.value
					}).done(function (data) {
						$("#similar_id").val("");
						var content = $similar.datagrid("option", "content");
						$similar.datagrid("load", "index.php?controller=AdminProducts&action=GetSimilar&id=" + product_id, "name", "ASC", content.page, content.rowCount);
					});
					event.preventDefault();
				}
			});
			
		}
		
		function formatDefault (str, obj) {
			if (obj.role_id == 3) {
				return '<a href="#" class="status-icon status-' + (str == 'F' ? '0' : '1') + '" style="cursor: ' +  (str == 'F' ? 'pointer' : 'default') + '"></a>';
			} else {
				return '<a href="#" class="status-icon status-1" style="cursor: default"></a>';
			}
		}
		
		function formatImage (path, obj) {
			var src = 'app/web/img/frontend/80x106.png';
			if (path !== null && path.length > 0) {
				src = path;
			}
			return ['<a href="index.php?controller=AdminProducts&action=Update&id=', obj.id, '"><img src="', src, '" alt="" class="s-Img" /></a>'].join('');
		}
		
		function formatMinPrice (price, obj) {
			return obj.min_price_format;
		}
		function formatStock (stock, obj) {
			if(stock == 0 || stock == '0')
			{
				return '<span class="bold red">'+stock+'</span>';
			}else{
				return stock;
			}
		}
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminProducts&action=Update&id={:id}"},
				          {type: "scdelete", url: "index.php?controller=AdminProducts&action=DeleteProduct&id={:id}&orders={:cnt_orders}"}
				          ],
				columns: [{text: myLabel.image, type: "text", sortable: true, editable: false, renderer: formatImage},
				          {text: myLabel.name, type: "text", sortable: true, editable: true, width: 180},
				          {text: myLabel.sku, type: "text", sortable: true, editable: true},
				          {text: myLabel.stock, type: "text", sortable: true, editable: false, align: "right", renderer: formatStock},
				          {text: myLabel.price, type: "text", sortable: true, editable: false, renderer: formatMinPrice, align: "right", width: 70},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, options: [
				                                                                                     {label: $.datagrid.messages.pr_status_1, value: 1}, 
				                                                                                     {label: $.datagrid.messages.pr_status_2, value: 2}
				                                                                                     ], applyClass: "status"}],
				dataUrl: "index.php?controller=AdminProducts&action=GetProduct" + Grid.queryString,
				dataType: "json",
				fields: ['pic', 'name', 'sku', 'total_stock', 'min_price', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminProducts&action=DeleteProductBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.exported, url: "index.php?controller=AdminProducts&action=ExportProduct", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminProducts&action=SaveProduct&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
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
		function formatQty(qty, obj) {
			if(qty == 0 || qty == '0')
			{
				return '<span class="bold red">'+qty+'</span>';
			}else{
				return qty;
			}
		}
		if ($("#grid_stock").length > 0 && datagrid) {
			
			var $grid_stock = $("#grid_stock").datagrid({
				buttons: [],
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: false, width: 475, renderer: formatName},
				          {text: myLabel.price, type: "text", sortable: true, editable: true, width: 100, editableWidth: 70, renderer: formatPrice},
				          {text: myLabel.qty, type: "spinner", renderer: formatQty, min: 0, max: 4294967295, step: 1, sortable: true, editable: true, width: 100, editableWidth: 70}],
				dataUrl: "index.php?controller=AdminProducts&action=GetStock",
				dataType: "json",
				fields: ['name', 'price', 'qty'],
				paginator: {
					actions: [
					    {text: myLabel.print_selected, url: "javascript:void(0);", render: false},
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminProducts&action=SaveStock&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		function getAttributes() {
			var product_id, hash, obj = {};
			product_id = $frmProduct.find("input[name='id']").val();
			hash = $frmProduct.find("input[name='hash']").val();
			if (product_id !== undefined && product_id !== "") {
				obj.product_id = product_id;
			} else if (hash !== undefined && hash !== "") {
				obj.hash = hash;
			}
			
			$.get("index.php?controller=AdminProducts&action=GetAttributes", obj).done(function (data) {
				$("#boxAttributes").html(data);
				fireGroupSortable();
				fireItemSortable();
			});
		}
		
		function getExtras() {
			$.get("index.php?controller=AdminProducts&action=GetExtras", {
				"product_id": $frmProduct.find("input[name='id']").val()
			}).done(function (data) {
				$("#boxExtras").html(data);
			});
		}

		$(document).on("click", ".btnCopy", function () {
			var product_id, hash,
				$this = $(this),
				obj = {"from_product_id": $this.val()};
			product_id = $frmProduct.find("input[name='id']").val();
			hash = $frmProduct.find("input[name='hash']").val();
			if (product_id !== undefined && product_id !== "") {
				obj.product_id = product_id;
			} else if (hash !== undefined && hash !== "") {
				obj.hash = hash;
			}
			
			if ($this.hasClass("btnCopyAttr")) {
				
				$.post("index.php?controller=AdminProducts&action=AttrCopy", obj).done(function (data) {
					getAttributes.call(null);
				});
				$dialogCopyAttr.dialog('close');
				
			} else if ($this.hasClass("btnCopyExtra")) {
				$.post("index.php?controller=AdminProducts&action=ExtraCopy", obj).done(function (data) {
					getExtras.call(null);
				});
				$dialogCopyExtra.dialog('close');
			}
		}).on("click", ".stock-image", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				id = $this.attr("rel"),			
				$a = $("<a>", {
					"href": "#"
				}).addClass("btnImageStock").attr("rel", id);
			
			$("<img>", {
				"src": $this.find("img").attr("src")
			}).addClass("in-stock").appendTo($a);
			
			$dialogImageStock.data("lnk").siblings("span").find("input[name^='stock_image_id']").val(id).valid();
			$dialogImageStock.data("lnk").replaceWith($a);
			$dialogImageStock.dialog("close");
			return false;
		}).keyup(function (e) {
			var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
			if (key == 27) {
				closeAttr.apply(null, []);
			}
		}).delegate("body", "click", function (e) {
			var $target = $(e.target);
			if (!$target.hasClass("attr_sign")) {
				closeAttr.apply(null, []);
			}
		}).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				status: "",
				q: "",
				is_out: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "name", "ASC", content.page, content.rowCount);
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
			if($this.data("value") == '3' && $this.data("column") == 'status')
			{
				obj['is_out'] = 'yes';
			}else{
				obj['is_out'] = '';
				obj[$this.data("column")] = $this.data("value");
			}
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "name", "ASC", content.page, content.rowCount);
			return false;
		}).on("click", ".status-1", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			return false;
		}).on("click", ".status-0", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminProducts&action=SetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct");
			});
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
			$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "id", "ASC", content.page, content.rowCount);
			return false;
		}).on("submit", ".frm-filter-stock", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid_stock.datagrid("option", "content"),
				cache = $grid_stock.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val()
			});
			$grid_stock.datagrid("option", "cache", cache);
			$grid_stock.datagrid("load", "index.php?controller=AdminProducts&action=GetStock", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "id", "DESC", content.page, content.rowCount);
			return false;
		}).on("reset", ".frm-filter-advanced", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			
			$(this).find(":input")
				.not(':button, :submit, :reset, :hidden')
				.removeAttr("checked").removeAttr("selected")
				.not(':checkbox, :radio')
				.val("");
		
			
			$grid.datagrid("option", "cache", {});
			$grid.datagrid("load", "index.php?controller=AdminProducts&action=GetProduct", "id", "DESC", 1, 10);
			
			$(".button-detailed").trigger("click");
			
			return false;
		});
		
		$("#grid").on("click", "a.table-icon-scdelete", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var url = $(this).attr('href'),
				cnt_orders = parseInt(getUrlParameter('orders', url), 10),
				id = parseInt(getUrlParameter('id', url), 10),
				$row = $(this).parent().parent(),
				$td = $row.find("td:eq(2)"),
				product_name = $td.find("span:eq(0)").html();
			
			if(cnt_orders > 0)
			{
				var text = myLabel.sc_delete_confirmation;
				
				text = text.replace(/\[PRODUCT\]/g, product_name);
				text = text.replace(/\[X\]/g, cnt_orders);
				$dialogDeleteProduct.html(text);
			}else{
				$dialogDeleteProduct.html(myLabel.sc_delete_product);
			}
			$dialogDeleteProduct.data('href', url).data('id', id);
			$dialogDeleteProduct.dialog('open');
			
			return false;
		});
		
		if($frmPrintSelectedStock.length > 0)
		{
			$("#grid_stock").on("click", '.paginator-action', function (e) {
				e.preventDefault();
				var stock_id = $('.table-select-row:checked').map(function(e){
					 return $(this).val();
				}).get();
				if(stock_id != '' && stock_id != null)
				{
					$('.scStockIdHidden').remove();
					$.each( stock_id, function( key, value ) {
						$frmPrintSelectedStock.append('<input type="hidden" name="record[]" value="'+value+'" class="scStockIdHidden" />');
					});
					$frmPrintSelectedStock.submit();
				}	
				return false;
			});
		}
		
		function getUrlParameter(sParam, sPageURL)
		{
		    var sURLVariables = sPageURL.split('&');
		    for (var i = 0; i < sURLVariables.length; i++) 
		    {
		        var sParameterName = sURLVariables[i].split('=');
		        if (sParameterName[0] == sParam) 
		        {
		            return sParameterName[1];
		        }
		    }
		}   
		
		function fireGroupSortable()
		{
			$("#boxAttributes").sortable({
				handle : '.group-move-icon',
			    update : function () {
			    	var data = $(this).sortable('toArray');
			    	$('#orderAttributes').val(data.join("|")); 
			    }
		    });
			var data = $("#boxAttributes").sortable('toArray');
	    	$('#orderAttributes').val(data.join("|"));
		}
		function fireItemSortable()
		{
			$('#frmUpdateProduct').find(".attrBoxRowStick").each(function (index, e) {
				var index = $(e).attr('data-id');
				$("#attrBoxRowStick_" + index).sortable({
					handle : '.item-move-icon',
					helper: 'clone',
				    update : function () {
				    	var data = $("#attrBoxRowStick_" + index).sortable('toArray');
				    	$('#orderItems_' + index).val(data.join("|")); 
				    }
			    });

				var data = $("#attrBoxRowStick_" + index).sortable('toArray');
		    	$('#orderItems_' + index).val(data.join("|"));
			});
			
		}
	});
})(jQuery_1_8_2);