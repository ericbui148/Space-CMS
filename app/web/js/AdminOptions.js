var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		
		var tabs = ($.fn.tabs !== undefined),
			$tabs = $("#tabs"),
			dialog = ($.fn.dialog !== undefined),
			$frmUpdateOptions = $('#frmUpdateOptions'),
			$frmEmailNotify = $('#frmEmailNotify'),
			$dialogDeleteShipping = $("#dialogDeleteShipping");
		
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs();
		}
		
		$(".field-int").spinner({
			min: 0
		});
		$(".positiveNumber").spinner({
			min: 1
		});
		
		if ($frmUpdateOptions.length > 0) {
			$.validator.addMethod('positiveNumber',
			    function (value) { 
			        return Number(value) > 0;
			    }, myLabel.positive_number);
			
			$frmUpdateOptions.validate({
				messages: {
					"value-int-o_products_per_page": {
						positiveNumber: myLabel.positive_number
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		if ($frmEmailNotify.length > 0) {
			var type = $("select[name='email_types']").val();
			$("div[class^='emailBox']").hide();
			$('.emailBox' + type).show();
		}
		function reDrawCode() {
			var code = $("#hidden_code").text(),
				category = $("select[name='install_category']").find("option:selected").val(),
				locale = "",
				hide = "";
			category = parseInt(category.length, 10) > 0 ? "&category_id=" + category : "";
			if($("select[name='install_locale']").length > 0)
			{
				var locale_id = $("select[name='install_locale']").val();
				locale = parseInt(locale_id, 10) > 0 ? "&locale=" + locale_id : "";
			}
			if($("select[name='install_hide']").length > 0)
			{
				hide = $("input[name='install_hide']").is(":checked") ? "&hide=1" : "";
			}	
						
			$("#install_code").text(code.replace(/&action=Load"/g, function(match) {
	            return ['&action=Load', category, locale, hide, '"'].join("");
	        }));
		}
		
		$("#content").on("focus", ".textarea_install", function (e) {
			var $this = $(this);
			$this.select();
			$this.mouseup(function() {
				$this.unbind("mouseup");
				return false;
			});
		}).on("keyup", "#uri_page", function (e) {
			var tmpl = $("#hidden_htaccess").text(),
				tmpl_remote = $("#hidden_htaccess_remote").text(),
				index = this.value.indexOf("?");
			$("#install_htaccess").text(tmpl.replace('::URI_PAGE::', index >= 0 ? this.value.substring(0, index) : this.value));
			$("#install_htaccess_remote").text(tmpl_remote.replace('::URI_PAGE::', index >= 0 ? this.value.substring(0, index) : this.value));
		}).on("change", "select[name='install_category']", function(e) {
            
            reDrawCode.call(null);
            
		}).on("change", "select[name='install_locale']", function(e) {
            
            reDrawCode.call(null);
            
		}).on("change", "input[name='install_hide']", function (e) {
			
			reDrawCode.call(null);
			
		}).on("change", "select[name='value-enum-o_send_email']", function (e) {
			switch ($("option:selected", this).val()) {
			case 'mail|smtp::mail':
				$(".boxSmtp").hide();
				break;
			case 'mail|smtp::smtp':
				$(".boxSmtp").show();
				break;
			}
		}).on("change", "input[name='value-bool-o_allow_paypal']", function (e) {
			if ($(this).is(":checked")) {
				$(".boxPaypal").show();
			} else {
				$(".boxPaypal").hide();
			}
		}).on("change", "input[name='value-bool-o_allow_authorize']", function (e) {
			if ($(this).is(":checked")) {
				$(".boxAuthorize").show();
			} else {
				$(".boxAuthorize").hide();
			}
		}).on("change", "input[name='value-bool-o_allow_nganluong']", function (e) {
			if ($(this).is(":checked")) {
				$(".boxNganLuong").show();
			} else {
				$(".boxNganLuong").hide();
			}
		}).on("change", "input[name='value-bool-o_allow_bank']", function (e) {
			if ($(this).is(":checked")) {
				$(".boxBank").show();
			} else {
				$(".boxBank").hide();
			}
		}).on("click", ".btnAddShipping", function () {
			var $c = $("#tmplShipping tbody").clone(),
				r = $c.html().replace(/\{INDEX\}/g, 'new_' + Math.ceil(Math.random() * 99999));
			$("#tblShipping").find("tbody").append(r);
		}).on("click", ".btnDeleteShipping", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if ($dialogDeleteShipping.length > 0 && dialog) {
				$dialogDeleteShipping.data("link", $(this)).dialog("open");
			}
			return false;
		}).on("click", ".btnRemoveShipping", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $tr = $(this).closest("tr");
			$tr.css("backgroundColor", "#FFB4B4").fadeOut("slow", function () {
				$tr.remove();
			});		
			return false;
		}).on("click", ".btnRemoveShipping", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $tr = $(this).closest("tr");
			$tr.css("backgroundColor", "#FFB4B4").fadeOut("slow", function () {
				$tr.remove();
			});		
			return false;
		}).on("change", "select[name='email_types']", function (e) {
			var type = $(this).val();
			$("div[class^='emailBox']").hide();
			$('.emailBox' + type).show();	
			return false;
		}).on("click", ".use-theme", function (e) {
			var theme = $(this).attr('data-theme');
			$('.loader').css('display', 'block');
			$.ajax({
				type: "GET",
				async: false,
				url: 'index.php?controller=AdminOptions&action=UpdateTheme&theme=' + theme,
				success: function (data) {
					$('.theme-holder').html(data);
					$('.loader').css('display', 'none');
				}
			});
		});
		
		if ($dialogDeleteShipping.length > 0 && dialog) {
			var buttons = {};
			buttons[myLabel.btn_delete] = function () {
				var $this = $(this),
					$link = $this.data("link"),
					$tr = $link.closest("tr"),
					id = $link.data("id");
			
				$.post("index.php?controller=AdminOptions&action=DeleteShipping", {
					"id": id
				}).done(function (data) {
					if (data.code === undefined) {
						return;
					}
					switch (data.code) {
						case 200:
							$tr.css("backgroundColor", "#FFB4B4").fadeOut("slow", function () {
								$tr.remove();
								$this.dialog("close");
							});
							break;
					}
				});
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			
			$dialogDeleteShipping.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				buttons: buttons
			});
		}
		
		if (window.tinyMCE !== undefined) {
			tinymce.init({
			    selector: "textarea.mceSelector",
			    extended_valid_elements : 'i[class]',
			    custom_elements : 'i[class]',
			    plugins: [
			        "advlist autolink lists link image charmap print preview anchor",
			        "searchreplace visualblocks code fullscreen",
			        "insertdatetime media table contextmenu paste"
			    ],
			    width: 540,
			    height: 300,
			    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
			    setup: function (editor) {
			    	editor.on('change', function (e) {
			    		editor.editorManager.triggerSave();
			    		$(":input[name='" + editor.id + "']").valid();
			    	});
			    }
			});
		}
	});
})(jQuery_1_8_2);