var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateSlider = $("#frmCreateSlider"),
			$frmUpdateSlider = $("#frmUpdateSlider"),
			dialog = ($.fn.dialog !== undefined),
			spinner = ($.fn.spinner !== undefined),
			$gallery = $("#gallery"),
			gallery = ($.fn.gallery !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			chosen = ($.fn.chosen !== undefined),
			tipsy = ($.fn.tipsy !== undefined);
		
		if (chosen) {
			if($frmCreateSlider.length > 0 || $frmUpdateSlider.length > 0)
			{
				$("#manager_id").chosen();
			}
		}
		if (tipsy) {
			$(".listing-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				gravity: "nw",
				className: "tipsy-listing"
			});
		}
		if ($frmCreateSlider.length > 0) {
			$frmCreateSlider.validate({
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
		if ($frmUpdateSlider.length > 0) {
			$frmUpdateSlider.validate({
				rules:{
					"employee_id":{
						positiveNumber: true
					}
				},
				errorPlacement: function (error, element) {
					var name = element.attr('name');
					if(name == 'employee_id')
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
		
		if ($gallery.length > 0 && gallery) {
			$gallery.gallery({
				compressUrl: "index.php?controller=Gallery&action=CompressSliderGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				getUrl: "index.php?controller=Gallery&action=GetSliderGallery&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				deleteUrl: "index.php?controller=Gallery&action=DeleteSlider",
				emptyUrl: "index.php?controller=Gallery&action=EmptySlider&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				rebuildUrl: "index.php?controller=Gallery&action=RebuildSlider&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				resizeUrl: "index.php?controller=Gallery&action=ResizeSlider&id={:id}&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash + ($frmUpdateSlider.length > 0 ? "&query_string=" + encodeURIComponent("controller=AdminSliders&action=Update&id=" + myGallery.foreign_id + "&tab=4") : ""),
				rotateUrl: "index.php?controller=Gallery&action=RotateSlider",
				sortUrl: "index.php?controller=Gallery&action=SortSlider",
				updateUrl: "index.php?controller=Gallery&action=UpdateSlider",
				uploadUrl: "index.php?controller=Gallery&action=UploadSlider&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash,
				watermarkUrl: "index.php?controller=Gallery&action=WatermarkSlider&foreign_id=" + myGallery.foreign_id + "&hash=" + myGallery.hash
			});
		}
		
		
		function formatPrice(str, obj) {
			return obj.price_format;
		}
		
		if ($("#grid").length > 0 && datagrid) {
			
			var options = {
				buttons: [{type: "edit", url: "index.php?controller=AdminSliders&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminSliders&action=DeleteSlider&id={:id}"}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: true, width: 200	},
				          {text: myLabel.type, type: "text", sortable: true, editable: true, width: 100	},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 120, options: [
						                                                                                     {label: myLabel.active, value: "T"}, 
						                                                                                     {label: myLabel.inactive, value: "F"}
						                                                                                     ], applyClass: "status"}],
				dataUrl: "index.php?controller=AdminSliders&action=GetSlider",
				dataType: "json",
				fields: ['name','type', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminSliders&action=DeleteSliderBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminSliders&action=SaveSlider&id={:id}",
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
			$grid.datagrid("load", "index.php?controller=AdminSliders&action=GetSlider", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminSliders&action=GetSlider", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminSliders&action=GetSlider", "id", "ASC", content.page, content.rowCount);
			return false;
		});
		$("#content").on("click", ".btnDeleteImage", function () {
			if ($dialogDeleteImage.length > 0 && dialog) {
				$dialogDeleteImage.data("id", $frmUpdateSlider.find("input[name='id']").val()).dialog("open");
			}
		});
	});
})(jQuery_1_8_2);