var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateTag = $("#frmCreateTag"),
			$frmUpdateTag = $("#frmUpdateTag"),
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
		
		if ($("#grid").length > 0 && datagrid) {
			
			var options = {
				buttons: [{type: "edit", url: "index.php?controller=AdminTags&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminTags&action=DeleteTag&id={:id}"}
				          ],
				columns: [
				          {text: myLabel.name, type: "text", sortable: true, editable: true, width: 500}
				        ],
				dataUrl: "index.php?controller=AdminTags&action=GetTag",
				dataType: "json",
				fields: ['name'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminTags&action=DeleteTagBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminTags&action=SaveTag&id={:id}",
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
		
		$(document).on("click", ".btn-all", function (e) {
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
			$grid.datagrid("load", "index.php?controller=AdminTags&action=GetTag", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminTags&action=GetTag", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminTags&action=GetTag", "id", "ASC", content.page, content.rowCount);
			return false;
		});

	});
})(jQuery_1_8_2);