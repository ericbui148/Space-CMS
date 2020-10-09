var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		
		var $frmCreateClient = $("#frmCreateClient"),
			$frmUpdateClient = $("#frmUpdateClient"),
			$dialogDeleteAddress = $("#dialogDeleteAddress"),
			datagrid = ($.fn.datagrid !== undefined),
			dialog = ($.fn.dialog !== undefined),
			validate = ($.fn.validate !== undefined),
			chosen = ($.fn.chosen !== undefined),
			vOpts = {
				rules: {
					email: {
						required: true,
						email: true,
						remote: "index.php?controller=AdminClients&action=CheckEmail"
					},
					password: "required",
					client_name: "required"
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				messages: {
					email: {
						remote: "E-Mail address is already in use"
					}
				}
			};
		
		if (chosen) {
			$("select.custom-chosen").chosen();
		}
		function formatOrders(val, obj) {
			if(val != '0')
			{
				return ['<a href="index.php?controller=AdminOrders&action=Index&client_id=', obj.id, '">', val, '</a>'].join("");
			}else{
				return '0';
			}
		}
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminClients&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminClients&action=DeleteClient&id={:id}"}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: true},
				          {text: myLabel.email, type: "text", sortable: true, editable: true},
				          {text: myLabel.last_order, type: "date", sortable: true, editable: false},
				          {text: myLabel.orders, type: "text", sortable: true, renderer: formatOrders, align: 'center'},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, applyClass: "status", options: [{label: myLabel.active, value: "T"},{label: myLabel.inactive, value: "F"}]}
				       ],
				dataUrl: "index.php?controller=AdminClients&action=GetClient" + Grid.queryString,
				dataType: "json",
				fields: ['client_name', 'email', 'last_order', 'orders', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminClients&action=DeleteClientBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.exported, url: "index.php?controller=AdminClients&action=ExportClient", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminClients&action=SaveClient&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}

		$("#content").on("click", ".btnAddAddress", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $clone = $("#boxCloneAddress").clone(),
				index = 'new_' + Math.ceil(Math.random() * 99999);
			$(this).parent().before($clone.html().replace(/\{INDEX\}/g, index));
			if (chosen) {
				$("select[name='country_id["+index+"]']").chosen();
			}
			return false;
		}).on("click", ".btnRemoveAddress", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).parent().remove();
			return false;
		}).on("click", ".btnDeleteAddress", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this);
			$dialogDeleteAddress.data("id", $this.data("id")).data("client_id", $this.data("client_id")).dialog("open");
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
			$grid.datagrid("load", "index.php?controller=AdminClients&action=GetClient", "id", "ASC", content.page, content.rowCount);
			return false;
		});
		
		if ($frmCreateClient.length > 0 && validate) {
			$frmCreateClient.validate(vOpts);
			$(".btnAddAddress").trigger("click");
			$(".form").find("input[name='is_default_shipping']").prop("checked", true);
			$(".form").find("input[name='is_default_billing']").prop("checked", true);
		}
		
		if ($frmUpdateClient.length > 0 && validate) {
			vOpts.rules.email.remote += "&id=" + $frmUpdateClient.find("input[name='id']").val();
			$frmUpdateClient.validate(vOpts);
		}
		
		if ($dialogDeleteAddress.length > 0 && dialog) {
			$dialogDeleteAddress.dialog({
				autoOpen: false,
				modal: true,
				draggable: false,
				resizable: false,
				buttons: {
					'Delete': function () {
						var $this = $(this);
						$.post("index.php?controller=AdminClients&action=DeleteAddress", {
							id: $this.data("id")
						}).done(function (data) {
							if (!data.code) {
								return;
							}
							switch (data.code) {
								case 200:
									$.get("index.php?controller=AdminClients&action=GetAddresses", {
										id: $this.data("client_id")
									}).done(function (data) {
										$("#boxAddresses").html(data);
										if (chosen) {
											$("#boxAddresses").find("select.custom-chosen").chosen();
										}
									});
									break;
							}
						});
						$(this).dialog("close");
					},
					'Cancel': function () {
						$(this).dialog("close");
					}
				}
			});
		}
	});
})(jQuery_1_8_2);