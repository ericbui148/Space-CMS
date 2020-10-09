var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
	
		var $frmCreateMenuItem = $('#frmCreateMenuItem'),
			$frmUpdateMenuItem = $('#frmUpdateMenuItem'),
			datagrid = ($.fn.datagrid !== undefined),
			validate = ($.fn.validate !== undefined);
		function formatName(val, obj) {
			return Array(obj.deep+1).join('-------') + " " + val.name;
		}
		function formatDown(val, obj) {
			return (obj.down === 1) ? ['<a href="index.php?controller=AdminMenuItems" class="arrow_down" rev="down" rel="', val.id , '" title="', myLabel.down, '"></a>'].join("") : '';
		}
		function formatUp(val, obj) {
			return (obj.up === 1) ? ['<a href="index.php?controller=AdminMenuItems" class="arrow_up" rev="up" rel="', val.id , '" title="', myLabel.up, '"></a>'].join("") : '';
		}
		
		function loadGridMenuItem() {
			var $grid = $("#grid_menu_item").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminMenuItems&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminMenuItems&action=DeleteMenuItem&id={:id}"}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: false, editable: false, renderer: formatName, width: 150},
				          {text: myLabel.link, type: "text", sortable: false, editable: false, width: 300},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatUp, width: 21}
				       ],
				dataUrl: "index.php?controller=AdminMenuItems&action=GetMenuItem",
				dataType: "json",
				fields: ['data', 'link', 'data', 'data'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminMenuItems&action=DeleteMenuItemBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminMenuItems&action=SaveMenuItem&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		if ($("#grid_menu_item").length > 0 && datagrid) {
			loadGridMenuItem();
		}
		
		if ($frmCreateMenuItem.length > 0 && validate) {
			$frmCreateMenuItem.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				}
			});
		}
		
		if ($frmUpdateMenuItem.length > 0 && validate) {
			$frmUpdateMenuItem.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				}
			});
		}
		
		$("#content").on("click", ".arrow_up, .arrow_down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminMenuItems&action=SetOrder", {
				"id": $(this).attr("rel"),
				"direction": $(this).attr("rev")
			}).done(function (data) {
				var content = $grid.datagrid("option", "content");
				$grid.datagrid("load", "index.php?controller=AdminMenuItems&action=GetMenuItem", "id", "ASC", content.page, content.rowCount);
			});
			return false;
		});
	});
})(jQuery_1_8_2);