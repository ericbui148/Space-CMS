var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $frmCreateSubscriber = $("#frmCreateSubscriber"),
			$frmUpdateSubscriber = $("#frmUpdateSubscriber"),
			$frmImportSubscriber = $("#frmImportSubscriber"),
			$dialogSend = $("#dialogSend"),
			$dialogSendResult = $("#dialogSendResult"),
			$dialogAddGroup = $("#dialogAddGroup"),
			datepicker = ($.fn.datepicker !== undefined),
			chosen = ($.fn.chosen !== undefined),
			validate = ($.fn.validate !== undefined),
			datagrid = ($.fn.datagrid !== undefined);
		
		function checkSource()
		{
			var source = $("input:radio[name='source']:checked").val();
			$('.nlSource').hide();
			switch(source)
			{
				case 'csv':
					$('.nlSourceCSV').show();
					break;
				case 'excel':
					$('.nlSourceExcel').show();
					break;
			}
		}
		
		if ($frmImportSubscriber.length > 0 && validate) {
			
			$frmImportSubscriber.validate({
				rules: {
					"csv":{
						required: function(){
							if($("input:radio[name='source']:checked").val() == 'csv'){
								return true;
							}else{
								return false;
							}
						},
						extension: "csv"
					},
					"subscribers":{
						required: function(){
							if($("input:radio[name='source']:checked").val() == 'excel'){
								return true;
							}else{
								return false;
							}
						}
					}
				},
				messages: {
					csv:{
						extension: myLabel.csv_allowed
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
			checkSource.call(null);
		}

		if ($frmCreateSubscriber.length > 0 && validate) {
			$frmCreateSubscriber.validate({
				rules: {
					"email": {
						required: true,
						email: true,
						remote: "index.php?controller=AdminSubscribers&action=ActionCheckEmail"
					}
				},
				messages: {
					"group_id[]":{
						required: myLabel.group_required
					},
					"email": {
						remote: myLabel.email_taken
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
		}
		if ($frmUpdateSubscriber.length > 0 && validate) {
			$frmUpdateSubscriber.validate({
				rules: {
					"email": {
						required: true,
						email: true,
						remote: "index.php?controller=AdminSubscribers&action=ActionCheckEmail&id=" + $frmUpdateSubscriber.find("input[name='id']").val()
					}
				},
				messages: {
					"group_id[]":{
						required: myLabel.group_required
					},
					"email": {
						remote: myLabel.email_taken
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
		}
		
		if (chosen) {
			$("#group_id").chosen();
			$("#country_id").chosen();
			$("#message_id").chosen();
			$("#add_group_id").chosen();
		}
		$(".field-int").spinner({
			min: 0
		});
		
		if ($("#grid").length > 0 && datagrid) 
		{
			function onBeforeShow (obj) {
				return true;
			}
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", title: nsApp.locale.titles.edit, url: "index.php?controller=AdminSubscribers&action=ActionUpdate&id={:id}"},
				          {type: "delete", title: nsApp.locale.titles.delete, url: "index.php?controller=AdminSubscribers&action=ActionDeleteSubscriber&id={:id}", beforeShow: onBeforeShow},
				          {type: "send", title: nsApp.locale.titles.send, url: "index.php?controller=AdminSubscribers&action=ActionSend&id={:id}"}],
						  
				columns: [{text: myLabel.name_email, type: "text", sortable: false, editable: false, width: 140},
				          {text: myLabel.group, type: "text", sortable: false, editable: false, width: 110},
				          {text: myLabel.total_sent, type: "text", sortable: false, editable: false, width: 70},
				          {text: myLabel.last_sent, type: "text", sortable: false, editable: false, width: 125},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 110, options: [
				                                                                                     {label: myLabel.subscribed, value: "T"}, 
				                                                                                     {label: myLabel.unsubscribed, value: "F"}
				                                                                                     ], applyClass: "subscribed"}],
				dataUrl: "index.php?controller=AdminSubscribers&action=ActionGetSubscriber" + Grid.queryString,
				dataType: "json",
				fields: ['name_email', 'groups', 'total_sent', 'last_sent', 'subscribed'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminSubscribers&action=ActionDeleteSubscriberBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.exported_selected, url: "index.php?controller=AdminSubscribers&action=ActionExportSubscriber", ajax: false},
					   {text: myLabel.add_to_group, url: "javascript:void(0);", render: false},
					   {text: myLabel.send_message, url: "javascript:void(0);", render: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminSubscribers&action=ActionSaveSubscriber&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		if ($dialogSendResult.length > 0) 
		{
			$dialogSendResult.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				height:150,
				width: 480,
				modal: true,
				
				buttons: {
					'OK': function() {
						$(this).dialog('close');
					}
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
				q: "",
				first_name: "",
				last_name: "",
				email: "",
				age_from: "",
				age_to: "",
				subscribed_from: "",
				subscribed_to: "",
				group_id: "",
				country_id: "",
				gender: "",
				subscribed: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminSubscribers&action=ActionGetSubscriber", "first_name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminSubscribers&action=ActionGetSubscriber", "first_name", "ASC", content.page, content.rowCount);
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
			$.post("index.php?controller=AdminSubscribers&action=ActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=AdminSubscribers&action=ActionGetSubscriber");
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
				q: $this.find("input[name='q']").val(),
				first_name: "",
				last_name: "",
				email: "",
				age_from: "",
				age_to: "",
				subscribed_from: "",
				subscribed_to: "",
				group_id: "",
				country_id: "",
				gender: "",
				subscribed: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminSubscribers&action=ActionGetSubscriber", "first_name", "ASC", content.page, content.rowCount);
			return false;
		}).on("click", ".form-field-icon-date", function (e) {
			var $dp = $(this).parent().siblings("input[type='text']");
			if ($dp.hasClass("hasDatepicker")) {
				$dp.datepicker("show");
			} else {
				$dp.trigger("focusin").datepicker("show");
			}
		}).on("focusin", ".datepick-birthday", function (e) {
			var $this = $(this),
				custom = {},
				o = {
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev"),
					changeMonth: true,
					changeYear: true,
					yearRange: '1900:' + myLabel.current_year
			};
			$this.not('.hasDatepicker').datepicker($.extend(o, custom));
		}).on("focusin", ".datepick-search", function (e) {
			var $this = $(this),
				custom = {},
				o = {
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
			};
			switch ($this.attr("name")) {
			case "subscribed_from":
				maxDate = $(".datepick-search[name='subscribed_to']").datepicker({
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
				}).datepicker("getDate");
				$(".datepick-search[name='subscribed_to']").datepicker("destroy").removeAttr("id");
				if (maxDate !== null) {
					custom.maxDate = maxDate;
				}
				break;
			case "subscribed_to":
				minDate = $(".datepick-search[name='subscribed_from']").datepicker({
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
				}).datepicker("getDate");
				$(".datepick-search[name='subscribed_from']").datepicker("destroy").removeAttr("id");
				if (minDate !== null) {
					custom.minDate = minDate;
				}
				break;
			}
			$this.not('.hasDatepicker').datepicker($.extend(o, custom));
		}).on("click", ".button-detailed, .button-detailed-arrow", function (e) {
			e.stopPropagation();
			$("input[name='q']").val('');
			$(".form-filter-advanced").toggle();
		}).on("submit", ".frm-filter-advanced", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$("input[name='q']").val('');
			var obj = {},
				$this = $(this),
				arr = $this.serializeArray(),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			cache.q = '';
			for (var i = 0, iCnt = arr.length; i < iCnt; i++) {
				obj[arr[i].name] = arr[i].value;
			}
			
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=AdminSubscribers&action=ActionGetSubscriber", "first_name", "ASC", content.page, content.rowCount);
			return false;
		}).on("change", "input:radio", function (e) {
			checkSource.call(null);
			return false;
		}).on("reset", ".frm-filter-advanced", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(".button-detailed").trigger("click");
			if (chosen) {
				$("#country_id").val('').trigger("liszt:updated");
				$("#group_id").val('').trigger("liszt:updated");
			}
			$('#first_name').val('');
			$('#last_name').val('');
			$('#email').val('');
			$('#age_from').val('');
			$('#age_to').val('');
			$('.datepick-search').val('');
			$('#gender').val('');
			$('#subscribed').val('');
		}).on("click", ".table-icon-send", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$('#message_id').val('').trigger("liszt:updated");
			
			var ajax_url = $(this).attr('href');
			$dialogSend.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 480,
				buttons: {
					'Send': function() {
						if($('#message_id').val() != '')
						{
							$('#result_message').html(myLabel.send_progress);
							
							$.ajax({
								type: "POST",
								data: {
									message_id: $('#message_id').val()
								},
								dataType: 'json',
								url: ajax_url,
								success: function (res) {
									if(res.code == 200)
									{
										$('#result_message').html(myLabel.sent_ok);
									}else{
										$('#result_message').html(myLabel.sent_error);
									}
								}
							});
							
							$(this).dialog('close');
							$dialogSendResult.dialog('open');
						}	
					},
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			});
			$dialogSend.dialog('open');
		});
		
		
		$("#grid").on("click", '.paginator-action', function (e) {
			if($(this).html() == myLabel.add_to_group)
			{
				e.preventDefault();
				var subscriber_id = $('.table-select-row:checked').map(function(e){
					 return $(this).val();
				}).get();
				if(subscriber_id != '' && subscriber_id != null)
				{
					$dialogAddGroup.dialog({
						autoOpen: false,
						resizable: false,
						draggable: false,
						modal: true,
						width: 480,
						buttons: {
							'Assign': function() {
								var group_id = $('#add_group_id').val(),
									remove = 0;
								if ($('#remove').is(':checked')) 
								{
									remove = 1;
								}
								if(group_id != '' && group_id != null)
								{
									$.ajax({
										type: "POST",
										data: {
											group_id: group_id,
											remove: remove,
											subscriber_id: subscriber_id
										},
										dataType: 'json',
										url: "index.php?controller=AdminSubscribers&action=ActionAddToGroup",
										success: function (res) {
											if(res.code == 200)
											{
												$grid.datagrid("load", "index.php?controller=AdminSubscribers&action=ActionGetSubscriber");
											}
											$dialogAddGroup.dialog('close');
										}
									});
								}
							},
							'Cancel': function() {
								$(this).dialog('close');
							}
						}
					});
					$("#add_group_id").val('').trigger("liszt:updated"); 
					$("#remove").attr('checked', false);
					$dialogAddGroup.dialog('open');
					$(".menu-list-wrap").css('display', 'none');
				}	
				return false;
			}//add to group
			if($(this).html() == myLabel.send_message)
			{
				e.preventDefault();
				var subscriber_id = $('.table-select-row:checked').map(function(e){
					 return $(this).val();
				}).get();
				if(subscriber_id != '' && subscriber_id != null)
				{
					$('#message_id').val('').trigger("liszt:updated");
					
					$dialogSend.dialog({
						autoOpen: false,
						resizable: false,
						draggable: false,
						modal: true,
						width: 480,
						buttons: {
							'Send': function() {
								if($('#message_id').val() != '')
								{
									$('#result_message').html(myLabel.send_progress);
									
									$.ajax({
										type: "POST",
										data: {
											message_id: $('#message_id').val()
										},
										dataType: 'json',
										url: "index.php?controller=AdminSubscribers&action=ActionSend&id=" + subscriber_id,
										success: function (res) {
											if(res.code == 200)
											{
												$('#result_message').html(myLabel.sent_ok);
											}else{
												$('#result_message').html(myLabel.sent_error);
											}
										}
									});
									
									$(this).dialog('close');
									$dialogSendResult.dialog('open');
								}	
							},
							'Cancel': function() {
								$(this).dialog('close');
							}
						}
					});
					$dialogSend.dialog('open');
					$(".menu-list-wrap").css('display', 'none');
				}
			}//send message
		});
	});
})(jQuery_1_8_2);