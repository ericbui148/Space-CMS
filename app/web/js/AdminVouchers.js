var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
	
		var $frmCreateVoucher = $('#frmCreateVoucher'),
			$frmUpdateVoucher = $('#frmUpdateVoucher'),
			voucher_id = "",
			$dialogDelete = $("#dialogDelete"),
			$dialogProductUnlink = $("#dialogProductUnlink"),
			$datepick = $(".datepick"),
			datagrid = ($.fn.datagrid !== undefined),
			validate = ($.fn.validate !== undefined),
			dialog = ($.fn.dialog !== undefined),
			chosen = ($.fn.chosen !== undefined),
			dOpts = {};
			
		if ($datepick.length > 0) {
			dOpts = $.extend(dOpts, {
				firstDay: $datepick.attr("rel"),
				dateFormat: $datepick.attr("rev")
			});
		}
		if (chosen) {
			$("#product_id").chosen();
			$(".select_all").on('click', function(e){
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				$('#product_id option').prop('selected', true); 
				$('#product_id').trigger('chosen:updated');
			});
			
			
			$(".clear_all").on('click', function(e){
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				$('#product_id option').prop('selected', false);
				$('#product_id').trigger('chosen:updated');
			});
		}
		function formatDiscount(str, obj) {
			return obj.discount_f;
		}
		function formatValid(str, obj) {
			return obj.valid_f;
		}
		
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminVouchers&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminVouchers&action=DeleteVoucher&id={:id}"}
				          ],
				columns: [{text: myLabel.code, type: "text", sortable: true, editable: true, width: 230},
				          {text: myLabel.discount, type: "text", sortable: true, editable: true, align: "right", renderer: formatDiscount, editableWidth: 70, width: 120},
				          {text: myLabel.valid, type: "text", sortable: false, editable: false, renderer: formatValid, width: 250}
				       ],
				dataUrl: "index.php?controller=AdminVouchers&action=GetVoucher",
				dataType: "json",
				fields: ['code', 'discount', 'valid'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminVouchers&action=DeleteVoucherBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminVouchers&action=SaveVoucher&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		$("#content").on("focusin", ".datepick", function (e) {
			$(this).datepicker(dOpts);
		}).delegate("#valid", "change", function () {
			switch ($("option:selected", this).val()) {
				case 'fixed':
					$(".vBox").hide();
					$("#vFixed").show();
					break;
				case 'period':
					$(".vBox").hide();
					$("#vPeriod").show();
					break;
				case 'recurring':
					$(".vBox").hide();
					$("#vRecurring").show();
					break;
			}
		}).on("click", ".productRemove", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).parent().remove();
			return false;
		}).on("click", ".productDelete", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("#dialogProductUnlink").data("lnk", $(this)).dialog("open");
			return false;
		});
		
		$(document).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				valid: "",
				q: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminVouchers&action=GetVoucher", "code", "DESC", content.page, content.rowCount);
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
			obj.valid = "";
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminVouchers&action=GetVoucher", "code", "DESC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminVouchers&action=GetVoucher", "id", "ASC", content.page, content.rowCount);
			return false;
		});
		
		if ($frmCreateVoucher.length > 0 && validate) {
			$frmCreateVoucher.validate({
				rules: {
					"code": {
						required: true,
						remote: "index.php?controller=AdminVouchers&action=CheckCode"
					}
				},
				messages: {
					"code": {
						remote: myLabel.same_code
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				}
			});
		}
		
		if ($frmUpdateVoucher.length > 0 && validate) {
			$frmUpdateVoucher.validate({
				rules: {
					"code": {
						required: true,
						remote: "index.php?controller=AdminVouchers&action=CheckCode&id=" + $frmUpdateVoucher.find("input[name='id']").val()
					}
				},
				messages: {
					"code": {
						remote: myLabel.same_code
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				}
			});
			voucher_id = $frmUpdateVoucher.find("input[name='id']").val();
		}
		
		if ($.fn.autocomplete !== undefined) {
			var lastXhr,
				$product = $("#product"),
				cache = {};
			
			$product.autocomplete({
				minLength: 3,
				source: function(request, response) {
					var term = request.term;
					if (term in cache) {
						response(cache[term]);
						return;
					}
					lastXhr = $.getJSON("index.php?controller=AdminVouchers&action=GetProducts&voucher_id=" + voucher_id, request, function(data, status, xhr) {
						cache[term] = data;
						if (xhr === lastXhr) {
							response(data);
						}
					});
				},
				select: function(event, ui) {
					$('<span class="vProduct"><input type="hidden" name="product_id[]" value="'+ui.item.value+'" /><a href="#" class="icon-delete align_middle productRemove"></a> ' + ui.item.label + '<br /></span>').appendTo("#boxProducts");
					$("#boxProducts").show();
					$product.val("");
					return false;
				}
			});
		}

		if ($dialogProductUnlink.length > 0 && dialog) {
			$dialogProductUnlink.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: {
					'Delete': function() {
						var $lnk = $(this).data("lnk");
						$.post("index.php?controller=AdminVouchers&action=UnlinkProduct", {
							voucher_id: $lnk.data('voucher_id'),
							product_id: $lnk.data('product_id')
						}).done(function (data) {
							$lnk.parent().remove();
						});
						$(this).dialog('close');			
					},
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			});
		}
		
	});
})(jQuery_1_8_2);