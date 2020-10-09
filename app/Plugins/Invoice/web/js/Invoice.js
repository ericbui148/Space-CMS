var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	"use strict";
	$(function () {
		var dialog = ($.fn.dialog !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			datepicker = ($.fn.datepicker !== undefined),
			validate = ($.fn.validate !== undefined),
			buttonset = ($.fn.buttonset !== undefined),
			spinner = ($.fn.spinner !== undefined),
			invoice_id = $("#frmUpdateInvoice > input[name='id']").val(),
			tmp = $("#frmCreateInvoice > input[name='tmp']").val(),
			$dialogDeleteLogo = $("#dialogDeleteLogo"),
			$dialogSendInvoice = $("#dialogSendInvoice"),
			$dialogAddItem = $("#dialogAddItem"),
			$dialogEditItem = $("#dialogEditItem"),
			$frmCreateInvoice = $("#frmCreateInvoice"),
			$frmUpdateInvoice = $("#frmUpdateInvoice"),
			$frmInvoicePayment = $("#frmInvoicePayment"),
			$frmInvoiceConfig = $("#frmInvoiceConfig"),
			tabs = ($.fn.tabs !== undefined),
			$tabs = $("#tabs"),
			tOpt = {
				select: function (event, ui) {
					$(":input[name='tab_id']").val(ui.panel.id);
				}
			};
			
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs(tOpt);
		}
		if ($frmInvoicePayment.length > 0) {
			$frmInvoicePayment.validate({
				rules: {
					"cc_num": {
						required: true,
						digits: true
					},
					"cc_code": {
						required: true,
						digits: true
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element);
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		if ($frmCreateInvoice.length > 0) {
			$frmCreateInvoice.validate({
				rules: {
					"uuid": {
						required: true,
						remote: "index.php?controller=Invoice&action=CheckUniqueId"
					}
				},
				messages:{
					"uuid": {
						remote: myLabel.uuid_exists
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
		if ($frmUpdateInvoice.length > 0) {
			$frmUpdateInvoice.validate({
				rules: {
					"uuid": {
						required: true,
						remote: "index.php?controller=Invoice&action=CheckUniqueId&id=" + $frmUpdateInvoice.find("input[name='id']").val()
					},
					"b_email": {
						email: true
					},
					"b_url": {
						url: true
					},
					"s_email": {
						email: true
					},
					"s_url": {
						url: true
					}
				},
				messages:{
					"uuid": {
						remote: myLabel.uuid_exists
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
		if ($frmInvoiceConfig.length > 0) {
			$frmInvoiceConfig.validate({
				rules: {
					"y_email": {
						email: true
					},
					"y_url": {
						url: true
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: "",
				invalidHandler: function (event, validator) {
				    if (validator.numberOfInvalids()) {
				    	var index = $(validator.errorList[0].element, this).closest("div[id^='tabs-']").index();
				    	if ($tabs.length > 0 && tabs && index !== -1) {
				    		$tabs.tabs(tOpt).tabs("option", "active", index-1);
				    	}
				    };
				}
			});
		}
		$(document).on("search", ".frm-filter", function (e) {
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=Invoice&action=GetInvoices", "created", "DESC", content.page, content.rowCount);
			
		}).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $form = $(this),
				$q = $form.find("input[name='q']");
			$q.val($q.val().replace(/^\s+|\s+$/g, ""));
			$form.trigger("search");
			return false;
		}).on("focusout", "input[name='qty'], input[name='unit_price']", function () {
			var $form = $(this).closest("form"),
				$amount = $form.find("input[name='amount']"),
				qty = parseFloat($form.find("input[name='qty']").val()),
				unit_price = parseFloat($form.find("input[name='unit_price']").val());
			
			if (!isNaN(qty) && !isNaN(unit_price)) {
				$amount.val((qty * unit_price).toFixed(2));
			} else {
				$amount.val("");
			}
		}).on("change", "select[name='foreign_id']", function (e) {
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				foreign_id: $this.find("option:selected").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=Invoice&action=GetInvoices", "created", "DESC", content.page, content.rowCount);
		});
		
		if (buttonset) {
			$("#boxStatus").buttonset();
		}
		
		function formatOrderId (str) {
			return ['<a href="', myLabel.booking_url.replace('{ORDER_ID}', str), '">', str, '</a>'].join("");
		}
		
		function formatTotal (str, obj) {
			return obj.total_formated;
		}
		
		function formatCreated(str) {
			if (str === null || str.length === 0) {
				return myLabel.empty_datetime;
			}
			
			if (str === '0000-00-00 00:00:00') {
				return myLabel.invalid_datetime;
			}
			
			if (str.match(/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/) !== null) {
				var x = str.split(" "),
					date = x[0],
					time = x[1],
					dx = date.split("-"),
					tx = time.split(":"),
					y = dx[0],
					m = parseInt(dx[1], 10) - 1,
					d = dx[2],
					hh = tx[0],
					mm = tx[1],
					ss = tx[2];
				return $.datagrid.formatDate(new Date(y, m, d, hh, mm, ss), Grid.jsDateFormat + ", hh:mm:ss");
			}
		}
		
		if ($("#grid").length > 0 && datagrid) {
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=Invoice&action=Update&id={:id}", title: "Edit"},
				          {type: "delete", url: "index.php?controller=Invoice&action=Delete&id={:id}", title: "Delete"},
				          {type: "menu", url: "#", text: '', items:[
				                     {text: myLabel.view_invoice, target: "_blank", url: "index.php?controller=Invoice&action=View&id={:uuid}&uuid={:order_id}"},
				                     {text: myLabel.print_invoice, target: "_blank", url: "index.php?controller=Invoice&action=Print&id={:uuid}&uuid={:order_id}"}
                                ]
				          }
						 ],
				columns: [
				    {text: myLabel.order_id, type: "text", sortable: true, editable: false, renderer: formatOrderId},
				    {text: myLabel.issue_date, type: "date", sortable: true, editable: false, renderer: $.datagrid._formatDate, dateFormat: Grid.jsDateFormat},
				    {text: myLabel.due_date, type: "date", sortable: true, editable: false, renderer: $.datagrid._formatDate, dateFormat: Grid.jsDateFormat},
				    {text: myLabel.created, type: "text", sortable: true, editable: false, renderer: formatCreated},
				    {text: myLabel.status, type: "select", sortable: true, editable: true, options: [
				                                                                                     {label: myLabel.paid, value: "paid"}, 
				                                                                                     {label: myLabel.not_paid, value: "not_paid"},
				                                                                                     {label: myLabel.cancelled, value: "cancelled"}
				                                                                                     ], applyClass: "status"},	
				    {text: myLabel.total, type: "text", sortable: true, editable: false, align: "right", renderer: formatTotal}
				],
				dataUrl: "index.php?controller=Invoice&action=GetInvoices",
				dataType: "json",
				fields: ['order_id', 'issue_date', 'due_date', 'created', 'status', 'total'],
				paginator: {
					actions: [
					   {text: myLabel.delete_title, url: "index.php?controller=Invoice&action=DeleteBulk", render: true, confirmation: myLabel.delete_body}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=Invoice&action=SaveInvoice&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
			
			var m = window.location.href.match(/&q=(.*)/);
			if (m !== null) {
				$(".frm-filter").trigger("search");
			}
			
			m = window.location.href.match(/&(foreign_id=)(\d+)?/);
			if (m !== null) {
				var $this = $(this),
					content = $grid.datagrid("option", "content"),
					cache = $grid.datagrid("option", "cache");
				$.extend(cache, {
					foreign_id: m[2] !== undefined ? m[2] : ""
				});
				$grid.datagrid("option", "cache", cache);
				$grid.datagrid("load", "index.php?controller=Invoice&action=GetInvoices", "created", "DESC", content.page, content.rowCount);
			}
		}
		
		function formatItem(val, obj) {
			return ['<span class="bold">', val, '</span><br>', obj.description].join("");
		}
		
		function formatQty(val) {
			if (typeof Grid !== "undefined" && Grid.qty_is_int) {
				return parseInt(val, 10);
			}
			
			return val;
		}
		
		if ($("#grid_items").length > 0 && datagrid) {
			var columns = [
						    {text: myLabel.i_item, type: "text", sortable: true, editable: true, width: 330, editableWidth: 290, renderer: formatItem},
						    {text: myLabel.i_qty, type: "text", sortable: true, editable: false, width: 70, align: "right", renderer: formatQty},
						    {text: myLabel.i_unit, type: "text", sortable: true, editable: false, width: 100, align: "right"},
						    {text: myLabel.i_amount, type: "text", sortable: true, editable: false, width: 100, align: "right"}
						],
				fields = ['name', 'qty', 'unit_price_formated', 'amount_formated'];
			if(Grid.o_use_qty_unit_price == false)
			{
				columns = [
						    {text: myLabel.i_item, type: "text", sortable: true, editable: true, width: 400, editableWidth: 380, renderer: formatItem},
						    {text: myLabel.i_amount, type: "text", sortable: true, editable: false, width: 200, align: "right"}
						];
				fields = ['name', 'amount_formated'];
			}
			var $grid = $("#grid_items").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=Invoice&action=UpdateItem&id={:id}", title: "Edit"},
				          {type: "delete", url: "index.php?controller=Invoice&action=DeleteItem&id={:id}", title: "Delete"}],
				columns: columns,
				dataUrl: "index.php?controller=Invoice&action=GetItems&invoice_id=" + invoice_id,
				dataType: "json",
				fields: fields,
				paginator: false,
				saveUrl: "index.php?controller=Invoice&action=SaveItem&id={:id}",
				select: false
			});
			
			if (tmp !== undefined) {
				var content = $grid.datagrid("option", "content"),
					cache = $grid.datagrid("option", "cache");
				$.extend(cache, {
					tmp: tmp
				});
				$grid.datagrid("option", "cache", cache);
				window.setTimeout(function () {
					$grid.datagrid("load", "index.php?controller=Invoice&action=GetItems", "id", "ASC", content.page, content.rowCount);
				}, 2000);
			}
		}
		
		$("#content").on("click", ".plugin_invoice_delete_logo", function () {
			if ($dialogDeleteLogo.length > 0 && dialog) {
				$dialogDeleteLogo.dialog("open");
			}
		}).on("click", ".plugin_invoice_add_item", function () {
			if ($dialogAddItem.length > 0 && dialog) {
				$dialogAddItem.dialog("open");
			}
		}).on("change", "input[name='p_accept_paypal'], input[name='p_accept_authorize'], input[name='p_accept_bank']", function () {
			var $this = $(this);
			if ($this.is(":checked")) {
				$($this.data("box")).show();
			} else {
				$($this.data("box")).hide();
			}
		}).on("focusin", ".datepick", function () {
			var minDate, maxDate,
				$this = $(this),
				custom = {},
				o = {
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
			};
			switch ($this.attr("name")) {
			case "issue_date":
				maxDate = $(".datepick[name=due_date]").datepicker({
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
				}).datepicker("getDate");
				$(".datepick[name=due_date]").datepicker("destroy").removeAttr("id");
				if (maxDate !== null) {
					custom.maxDate = maxDate;
				}
				break;
			case "due_date":
				minDate = $(".datepick[name=issue_date]").datepicker({
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
				}).datepicker("getDate");
				$(".datepick[name=issue_date]").datepicker("destroy").removeAttr("id");
				if (minDate !== null) {
					custom.minDate = minDate;
				}
				break;
			}
			$this.not('.hasDatepicker').datepicker($.extend(o, custom));
		}).on("click", ".btnInvoiceView", function () {
			var $frm = $("#frmPluginInvoiceView");
			if ($frm.length > 0) {
				$frm.submit();
			}
		}).on("click", ".btnInvoicePrint", function () {
			var $frm = $("#frmPluginInvoicePrint");
			if ($frm.length > 0) {
				$frm.submit();
			}
		}).on("click", ".btnInvoiceSend", function () {
			if ($dialogSendInvoice.length > 0 && dialog) {
				var data = $dialogSendInvoice.data();   
				data.id = $(this).attr('data-id');  
				data.uuid = $(this).attr('data-uuid');
				$dialogSendInvoice.dialog("open");
			}
		});
		
		$("li.plugin_view_invoice a").unbind('click').each(function() {
			
		});
		$(document).ajaxSuccess(function(event, xhr, settings, data) {
			if (settings.url.match(/index\.php\?controller=Invoice&action=DeleteItem&id=\d+/) !== null && data.status && data.status === "OK" && data.total) {
				$("#total").val(data.total.toFixed(2));
			}
		});
		
		$("#grid_items").on("click", ".table-icon-edit", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if ($dialogEditItem.length > 0 && dialog) {
				$dialogEditItem.data('id', $(this).data('id').id).dialog("open");
			}
			return false;
		});
		
		if ($dialogSendInvoice.length > 0 && dialog) {
			var buttons = {};
			buttons[myLabel.btn_send] = function () {
				var $this = $(this);
				$.post("index.php?controller=Invoice&action=Send", $dialogSendInvoice.find("form").serialize()).always(function () {
					$this.dialog("close");
				});
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			$dialogSendInvoice.dialog({
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				width: 770,
				open: function () {
					var $this = $(this);
					$dialogSendInvoice.html("");
					$.get("index.php?controller=Invoice&action=Send", {
						"id": $this.data("id"),
						"uuid": $this.data("uuid")
					}).done(function (data) {
						$dialogSendInvoice.html(data);
					}).always(function () {
						$dialogSendInvoice.dialog("option", "position", "center");
					});
				},
				buttons: buttons
			});
		}
		
		if ($dialogDeleteLogo.length > 0 && dialog) {
			
			var buttons = {};
			buttons[myLabel.btn_delete] = function () {
				var $this = $(this);
				$.post("index.php?controller=Invoice&action=DeleteLogo").done(function () {
					$("#plugin_invoice_box_logo").html('<input type="file" name="y_logo" id="y_logo" />')
				}).always(function () {
					$this.dialog("close");
				});
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			
			$dialogDeleteLogo.dialog({
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				buttons: buttons
			});
		}
		
		var itemOpts = {
			rules: {
				"name": "required",
				"qty": {
					required: true,
					number: true
				},
				"unit_price": {
					required: true,
					number: true
				},
				"amount": {
					required: true,
					number: true
				}
			},
			errorPlacement: function (error, element) {
				error.insertAfter(element.parent());
			}
		};
		
		var spinOpts = {
			min: 0,
			numberFormat: "n",
			step: 0.1
		};

		if (typeof Grid !== "undefined" && Grid.qty_is_int) {
			spinOpts.numberFormat = null;
			spinOpts.step = 1;
		}
		
		if ($dialogAddItem.length > 0 && dialog) {
			var buttons = {};
			buttons[myLabel.btn_save] = function () {
				var $this = $(this),
					$form = $dialogAddItem.find("form");
				
				if ($form.length > 0 && validate) {
					var validator = $form.validate(itemOpts);
					if (validator.form()) {
						$.post("index.php?controller=Invoice&action=AddItem", $form.serialize()).done(function (data) {
							if (data.status === "OK" && data.total) {
								$("#total").val(data.total.toFixed(2));
							}
							var content = $grid.datagrid("option", "content");
							$grid.datagrid("load", "index.php?controller=Invoice&action=GetItems&invoice_id=" + invoice_id, "id", "ASC", content.page, content.rowCount);
						}).always(function () {
							$this.dialog("close");
						});
					}
				}
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			$dialogAddItem.dialog({
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				width: 560,
				open: function () {
					$dialogAddItem.html("");
					$.get("index.php?controller=Invoice&action=AddItem", {
						"invoice_id": invoice_id,
						"tmp": tmp
					}).done(function (data) {
						$dialogAddItem.html(data);
						if (spinner) {
							$dialogAddItem.find("input[name='qty']").spinner(spinOpts);
						}
					}).always(function () {
						$dialogAddItem.dialog("option", "position", "center");
					});
				},
				buttons: buttons
			});
		}
		
		if ($dialogEditItem.length > 0 && dialog) {
			var buttons = {};
			buttons[myLabel.btn_update] = function () {
				var $this = $(this),
					$form = $dialogEditItem.find("form");
				
				if ($form.length > 0 && validate) {
					var validator = $form.validate(itemOpts);
					if (validator.form()) {
						$.post("index.php?controller=Invoice&action=EditItem", $form.serialize()).done(function () {
							var content = $grid.datagrid("option", "content");
							$grid.datagrid("load", "index.php?controller=Invoice&action=GetItems&invoice_id=" + invoice_id, "id", "ASC", content.page, content.rowCount);
						}).always(function () {
							$this.dialog("close");
						});
					}
				}
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			$dialogEditItem.dialog({
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				width: 560,
				open: function () {
					$dialogEditItem.html("");
					$.get("index.php?controller=Invoice&action=EditItem", {
						"id": $(this).data("id")
					}).done(function (data) {
						$dialogEditItem.html(data);
						if (spinner) {
							$dialogEditItem.find("input[name='qty']").spinner(spinOpts);
						}
					}).always(function () {
						$dialogEditItem.dialog("option", "position", "center");
					});
				},
				buttons: buttons
			});
		}
		
		if (window.tinymce !== undefined) {
			tinymce.init({
			    selector: "textarea.mceEditor",
			    theme: "modern",
			    width: 738,
			    height: 700,
			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			         "searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor"
			   ],
			   toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons"
			 });
		}
		
		if($("#frmInvoicePayment").length > 0)
		{
			var $payment_method = $("#frmInvoicePayment input[name='payment_method']");
			$payment_method.click(function(){
			    var value = $('input:radio[name=payment_method]:checked').val();
			    if(value == "creditcard"){
			        $('.boxCC').css('display','block');
			    }else{
			    	$('.boxCC').css('display','none');
			    }
			});
		}
	});
})(jQuery_1_8_2);