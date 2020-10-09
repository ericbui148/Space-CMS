var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $frmCreateGroup = $("#frmCreateGroup"),
			$frmUpdateGroup = $("#frmUpdateGroup"),
			
			tabs = ($.fn.tabs !== undefined),
			validate = ($.fn.validate !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			$tabs = $("#tabs"),
			tOpt = {
				select: function (event, ui) {
					$(":input[name='tab_id']").val(ui.panel.id);
				}
			};
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs(tOpt);
		}
		if ($frmCreateGroup.length > 0 && validate) {
			
			$frmCreateGroup.validate({
				rules: {
					"group_title": {
						required: true,
						remote: "index.php?controller=AdminGroups&action=ActionCheckGroupName"
					}
				},
				messages:{
					"group_title": {
						remote: myLabel.same_group
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
		if ($frmUpdateGroup.length > 0 && validate) {
			$frmUpdateGroup.validate({
				rules: {
					"group_title": {
						required: true,
						remote: "index.php?controller=AdminGroups&action=ActionCheckGroupName&id=" + $frmUpdateGroup.find("input[name='id']").val()
					}
				},
				messages:{
					"group_title": {
						remote: myLabel.same_group
					},
					"selected_data": {
						remote: myLabel.select_field
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: '',
				invalidHandler: function (event, validator) {
				    if (validator.numberOfInvalids()) {
				    	var index = $(validator.errorList[0].element, this).closest("div[id^='tabs-']").index();
				    	if ($tabs.length > 0 && tabs && index !== -1) {
				    		$tabs.tabs(tOpt).tabs("option", "active", index-1);
				    	}
				    }
				}
			});
			
			tinymce.init({
				document_base_url : myLabel.install_url,
				relative_urls : false,
				remove_script_host : false,
			    selector: "textarea.mceEditor",
			    theme: "modern",
			    width: 520,
			    height: 300,
			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak",
			         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor"
			   ],
			   toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons"
			 });
		}
		
		if ($("#grid").length > 0 && datagrid) 
		{
			function onBeforeShow (obj) {
				return true;
			}
			function formatTotal(str, obj){
				if(str == '0')
				{
					return 0;
				}else{
					return '<a href="index.php?controller=AdminSubscribers&action=ActionIndex&group_id='+obj.id+'">'+str+'</a>';
				}
			}
			function formatSubscribed(str, obj){
				if(str == '0')
				{
					return 0;
				}else{
					return '<a href="index.php?controller=AdminSubscribers&action=ActionIndex&group_id='+obj.id+'&subscribed=T">'+str+'</a>';
				}
			}
			function formatUnsubscribed(str, obj){
				if(str == '0')
				{
					return 0;
				}else{
					return '<a href="index.php?controller=AdminSubscribers&action=ActionIndex&group_id='+obj.id+'&subscribed=F">'+str+'</a>';
				}
			}
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", title: nsApp.locale.titles.edit, url: "index.php?controller=AdminGroups&action=ActionUpdate&id={:id}"},
				          {type: "delete", title: nsApp.locale.titles.delete, url: "index.php?controller=AdminGroups&action=ActionDeleteGroup&id={:id}", beforeShow: onBeforeShow},
				          {type: "send", title: nsApp.locale.titles.send, url: "index.php?controller=AdminMessages&action=ActionSend&group_id={:id}"},
				          {type: "import", title: nsApp.locale.titles.import, url: "index.php?controller=AdminSubscribers&action=ActionImport&group_id={:id}"}],
						  
				columns: [{text: myLabel.group, type: "text", sortable: true, editable: true, width: 220, editableWidth: 200},
				          {text: myLabel.total, type: "text", sortable: false, editable: false, renderer: formatTotal, width: 50},
				          {text: myLabel.subscribed, type: "text", sortable: false, editable: false, renderer: formatSubscribed, width: 80},
				          {text: myLabel.unsubscribed, type: "text", sortable: false, editable: false, renderer: formatUnsubscribed, width: 90},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, options: [
				                                                                                     {label: myLabel.active, value: "T"}, 
				                                                                                     {label: myLabel.inactive, value: "F"}
				                                                                                     ], applyClass: "status"}],
				dataUrl: "index.php?controller=AdminGroups&action=ActionGetGroup",
				dataType: "json",
				fields: ['group_title', 'total', 'subscribed', 'unsubscribed', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminGroups&action=ActionDeleteGroupBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.revert_status, url: "index.php?controller=AdminGroups&action=ActionStatusGroup", render: true},
					   {text: myLabel.exported, url: "index.php?controller=AdminGroups&action=ActionExportGroup", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminGroups&action=ActionSaveGroup&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		$(document).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				status: "",
				q: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminGroups&action=ActionGetGroup", "group_title", "ASC", content.page, content.rowCount);
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
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminGroups&action=ActionGetGroup", "group_title", "ASC", content.page, content.rowCount);
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
			$.post("index.php?controller=AdminGroups&action=ActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=AdminGroups&action=ActionGetGroup");
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
			$grid.datagrid("load", "index.php?controller=AdminGroups&action=ActionGetGroup", "group_title", "ASC", content.page, content.rowCount);
			return false;
		}).on("click", ".checkbox", function () {
			var $this = $(this);
			if ($this.find("input[type='checkbox']").is(":checked")) {
				$this.addClass("checkbox-checked");
			} else {
				$this.removeClass("checkbox-checked");
			}
			
			var field_str = $('.subscriber-data:checked').map(function(e){
				 return $(this).val();
			}).get();
			$('#subscribed_fields').val(field_str);
		}).on("focusin", ".textarea_install", function (e) {
			$(this).select();
		});
	});
})(jQuery_1_8_2);