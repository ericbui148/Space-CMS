var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
	
		var $frmCreateCategory = $('#frmCreateCategory'),
			$frmUpdateCategory = $('#frmUpdateCategory'),
			datagrid = ($.fn.datagrid !== undefined),
			chosen = ($.fn.chosen !== undefined),
			validate = ($.fn.validate !== undefined);
		
		if (chosen) {
			$("#parent_id").chosen();
			$("#template").chosen();
		}
			
		function formatName(val, obj) {
			return Array(obj.deep+1).join('-------') + " " + val.name;
		}
		function formatDown(val, obj) {
			return (obj.down === 1) ? ['<a href="index.php?controller=AdminPageCategories" class="arrow_down" rev="down" rel="', val.id , '" title="', myLabel.down, '"></a>'].join("") : '';
		}
		function formatUp(val, obj) {
			return (obj.up === 1) ? ['<a href="index.php?controller=AdminPageCategories" class="arrow_up" rev="up" rel="', val.id , '" title="', myLabel.up, '"></a>'].join("") : '';
		}
		
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminPageCategories&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminPageCategories&action=DeleteCategory&id={:id}"}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: false, editable: false, renderer: formatName, width: 500},
				          {text: myLabel.page, type: "text", sortable: false, editable: false, align: "center"},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatUp, width: 21}
				       ],
				dataUrl: "index.php?controller=AdminPageCategories&action=GetCategory" + "&rowCount=50",
				dataType: "json",
				fields: ['data', 'sections', 'data', 'data'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminPageCategories&action=DeleteCategoryBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminPageCategories&action=SaveCategory&id={:id}",
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
		
		function formatPageImage (path, obj) {
			var src = 'app/web/img/frontend/noimg.jpg';
			if (path !== null) {
				src = path;
			}
			return ['<a href="index.php?controller=AdminPage&action=Update&id=', obj.id, '"><img src="', src, '" width="120" height="90" alt="" class="s-Img" /></a>'].join('');
		}
		function formatPageDown(obj) {
			return (obj.down === 1) ? ['<a href="index.php?controller=AdminItemSorts&action=GetItemSorts'+Grid.queryString+'" class="p_arrow_down" rev="down" rel="', obj.id , '" title="', myLabel.down, '"></a>'].join("") : '';
		}
		
		function formatPageUp(obj) {
			return (obj.up === 1) ? ['<a href="index.php?controller=AdminItemSorts&action=GetItemSorts'+Grid.queryString+'" class="p_arrow_up" rev="up" rel="', obj.id , '" title="', myLabel.up, '"></a>'].join("") : '';
		}
		
		if ($("#page_grid").length > 0 && datagrid) {
			
			var $grid = $("#page_grid").datagrid({
				columns: [{text: myLabel.image, type: "text", sortable: false, editable: false, renderer: formatPageImage, width: 180},
				          {text: myLabel.name, type: "text", sortable: false, editable: false, width: 500},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatPageDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatPageUp, width: 21}
				          ],
				dataUrl: "index.php?controller=AdminItemSorts&action=GetItemSorts&rowCount=50" + Grid.queryString,
				dataType: "json",
				fields: ['avatar_file', 'page_name', 'item_sort', 'item_sort'],
				paginator: {
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				}
			});
		}
		
		$("#content").on("click", ".arrow_up, .arrow_down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminPageCategories&action=SetOrder", {
				"id": $(this).attr("rel"),
				"direction": $(this).attr("rev")
			}).done(function (data) {
				var content = $grid.datagrid("option", "content");
				$grid.datagrid("load", "index.php?controller=AdminPageCategories&action=GetCategory", "id", "ASC", content.page, content.rowCount);
			});
			return false;
		}).on("click", ".p_arrow_up, .p_arrow_down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminItemSorts&action=SetOrder", {
				"id": $(this).attr("rel"),
				"direction": $(this).attr("rev")
			}).done(function (data) {
				var content = $grid.datagrid("option", "content");
				$grid.datagrid("load", "index.php?controller=AdminItemSorts&action=GetItemsorts&rowCount=50" + Grid.queryString);
			});
			return false;
		});
	});
})(jQuery_1_8_2);