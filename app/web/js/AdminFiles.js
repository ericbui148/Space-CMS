var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $frmCreateFile = $("#frmCreateFile"),
			$frmUpdateFile = $("#frmUpdateFile"),
			multiselect = ($.fn.multiselect !== undefined),
			validate = ($.fn.validate !== undefined),
			datagrid = ($.fn.datagrid !== undefined);
		
		if (multiselect) {
			$("#user_id").multiselect({
				noneSelectedText: myLabel.select_users
			});
		}
		if ($frmCreateFile.length > 0 && validate) 
		{
			$frmCreateFile.validate({
				rules: {
					"file":{
						extension: myLabel.allowed_extension
					}
				},
				messages:{
					"file":{
						required: myLabel.field_required,
						extension: myLabel.extension_message
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
		if ($frmUpdateFile.length > 0 && validate) {
			$frmUpdateFile.validate({
				rules: {
					"file":{
						extension: myLabel.allowed_extension
					}
				},
				messages:{
					"file":{
						required: myLabel.field_required,
						extension: myLabel.extension_message
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
		
		if ($("#grid").length > 0 && datagrid) 
		{
			function formatFileName(val, obj) {
				return '<a href="file.php?id='+obj.id+'&hash='+obj.hash+'" target="_blank">' + val + '</a>';
			}
			
			function onBeforeShow (obj) {
				return true;
			}
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=AdminFiles&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminFiles&action=DeleteFile&id={:id}", beforeShow: onBeforeShow}],
						  
				columns: [
				          	{text: myLabel.file_name, type: "text", sortable: true, editable: false, width: 270, renderer: formatFileName},
				          	{text: myLabel.uploaded_on, type: "text", sortable: true, editable: false, width: 130},
				          	{text: myLabel.uploaded_by, type: "text", sortable: true, editable: false, width: 120},
				          	{text: myLabel.size, type: "text", sortable: true, editable: false, width: 80}
				         ],
				dataUrl: "index.php?controller=AdminFiles&action=GetFile",
				dataType: "json",
				fields: ['file_name', 'created', 'name', 'size'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminFiles&action=DeleteFileBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.exported, url: "index.php?controller=AdminFiles&action=ExportFile", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminFiles&action=SaveFile&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		$(document).on("submit", ".frm-filter", function (e) {
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
			$grid.datagrid("load", "index.php?controller=AdminFiles&action=GetFile", "created", "DESC", content.page, content.rowCount);
			return false;
		});
	});
})(jQuery_1_8_2);