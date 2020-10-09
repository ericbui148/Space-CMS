var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var 
			datagrid = ($.fn.datagrid !== undefined),
			dialog = ($.fn.dialog !== undefined),
			$dialogView = $("#dialogView");
		
		if ($("#grid").length > 0 && datagrid) {
			var gridOpts = {
				buttons: [{type: "view", url: "index.php?controller=AdminSubmissions&action=View&submission_id={:id}"},
				          {type: "print", url: "index.php?controller=AdminSubmissions&action=Print&submission_id={:id}", target: "_blank"},
				          {type: "delete", url: "index.php?controller=AdminSubmissions&action=DeleteSubmission&id={:id}"}],
				columns: [{text: myLabel.form_name, type: "text", sortable: true, editable: false, width: 300},
				          {text: myLabel.ip_address, type: "text", sortable: true, editable: false, width: 120},
				          {text: myLabel.date_time, type: "text", sortable: true, editable: false, width: 150}],
				          
				dataUrl: "index.php?controller=AdminSubmissions&action=GetSubmissions" + Grid.queryString ,
				dataType: "json",
				fields: ['form_title', 'ip', 'submitted_date'],
				paginator: {
					actions: [
							   {text: myLabel.delete_selected, url: "index.php?controller=AdminSubmissions&action=DeleteSubmissionBulk", render: true, confirmation: myLabel.delete_confirmation}
					   		],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				select: {
					field: "id",
					name: "record[]"
				}
			};
			if (Grid.roleId === 2) {
				gridOpts = {
					buttons: [{type: "view", url: "index.php?controller=AdminSubmissions&action=View&submission_id={:id}", target: "_blank"}],
					columns: [{text: myLabel.form_name, type: "text", sortable: true, editable: false, width: 350},
					          {text: myLabel.ip_address, type: "text", sortable: true, editable: false, width: 120},
					          {text: myLabel.date_time, type: "text", sortable: true, editable: false, width: 150}],
					          
					dataUrl: "index.php?controller=AdminSubmissions&action=GetSubmissions"+Grid.queryString,
					dataType: "json",
					fields: ['form_title', 'ip', 'submitted_date'],
					paginator: {
						gotoPage: true,
						paginate: true,
						total: true,
						rowCount: true
					},
					select: {
						field: "id",
						name: "record[]"
					}
				};
			}
			var $grid = $("#grid").datagrid(gridOpts);
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
			$grid.datagrid("load", "index.php?controller=AdminSubmissions&action=GetSubmissions", "submitted_date", "DESC", content.page, content.rowCount);
			return false;
		}).on("change", "select[name='form_id']", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				form_id: $this.val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminSubmissions&action=GetSubmissions", "submitted_date", "DESC", content.page, content.rowCount);
			return false;
		}).on("click", ".table-icon-view", function (e) {
			var ajax_url = $(this).attr('href');
			
			if(ajax_url.indexOf("submission_id") >= 0){
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				$.ajax({
					type: "POST",
					dataType: 'json',
					url: ajax_url,
					success: function (data) {
						$('#dialogView').html(data.ob_view);
						$dialogView.dialog('option', 'title', data.form_title);
						$dialogView.dialog('open');
					}
				});
			}
		});
		
		if ($dialogView.length > 0 && dialog) {
			$dialogView.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				width: 'auto',
				buttons: {
					'Close': function() {
						$dialogView.dialog('close');
					}
				}
			});
		}
	});
})(jQuery_1_8_2);