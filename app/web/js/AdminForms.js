var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateForm = $("#frmCreateForm"),
			$frmUpdateForm = $("#frmUpdateForm"),
			chosen = ($.fn.chosen !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			dialog = ($.fn.dialog !== undefined),
			$dialogDelete = $("#dialogDelete"),
			$dialogDeleteField = $("#dialogDeleteField"),
			$dialogView = $("#dialogView"),
			spinner = ($.fn.spinner !== undefined),
			tipsy = ($.fn.tipsy !== undefined),
			tabs = ($.fn.tabs !== undefined),
			miniColors = ($.fn.miniColors !== undefined),
			$tabs = $("#tabs"),
			$install_tabs = $("#install_tabs"),
			tOpt = {
				select: function (event, ui) {
					$(":input[name='tab_id']").val(ui.panel.id);
					if(ui.panel.id == 'tabs-5'){
						$.ajax({
							type: "GET",
							dataType: 'html',
							url: 'index.php?controller=AdminForms&action=Code&id=' + $('#id').val(),
							success: function (res) {
								$('#install_html_code').html(res);
							}
						});
					}
				}
			};
		
		$(".field-int").spinner({
			min: 0
		});
		if (tipsy) {
			$(".listing-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				gravity: "nw",
				className: "tipsy-listing"
			});
			$(".center-langbar-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				className: "tipsy-listing-center"
			});
		}
		if (chosen) {
			$("#user_id").chosen();
		}
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs(tOpt);
		}
		if ($install_tabs.length > 0 && tabs) {
			$install_tabs.tabs({});
		}
		if ($frmCreateForm.length > 0) {
			$frmCreateForm.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		if ($frmUpdateForm.length > 0) {
			$.validator.addMethod("checkURL", function(value, element) {
				var urlregex = new RegExp("^(http:\/\/|https:\/\/){1}([0-9A-Za-z]+\.)");
				if(value == ''){
					return true;
				}
			    if (urlregex.test(value)) {
			    	
			        return (true);
			    }
			    return (false);
			}, myLabel.valid_url);
			$frmUpdateForm.validate({
				rules: {
					"thankyou_page": {
						required: function(){
							if($('#confirm_options').val() == 'redirect'){
								return true;
							}else{
								return false;
							}
						},
						checkURL: true
					},
					"confirm_message": {
						required: function(){
							if($('#confirm_options').val() == 'message'){
								return true;
							}else{
								return false;
							}
						}
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
			if (miniColors) {
				$(".field-color").miniColors();
			}
						
			tinymce.init({
				relative_urls : false,
				remove_script_host : false,
			    selector: "textarea.mceEditor",
			    theme: "modern",
			    width: 550,
			    height: 300,
			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor"
			   ],
			   toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons"
			 });
		}
		
		
		function formatDefault (str, obj) {
			if (obj.role_id == 3) {
				return '<a href="#" class="status-icon status-' + (str == 'F' ? '0' : '1') + '" style="cursor: ' +  (str == 'F' ? 'pointer' : 'default') + '"></a>';
			} else {
				return '<a href="#" class="status-icon status-1" style="cursor: default"></a>';
			}
		}
		function formatSubmissions(str, obj){
			if(str == '0')
			{
				return 0;
			}else{
				return '<a href="index.php?controller=AdminSubmissions&action=Index&form_id='+obj.id+'">'+str+'</a>';
			}
		}
		function formatDateTime(str, obj){
			if(str == '' || str == null)
			{
				return '';
			}else{
				return '<a href="index.php?controller=AdminSubmissions&action=View&submission_id='+obj.submission_id+'" class="CfViewSubmission">'+str+'</a>';
			}
		}
		if ($("#grid").length > 0 && datagrid) {
			var gridOpts = {
					buttons: [{type: "edit", url: "index.php?controller=AdminForms&action=Update&id={:id}"},
					          {type: "view", url: "preview.php?id={:id}", target: "_blank"},
					          {type: "delete", url: "index.php?controller=AdminForms&action=DeleteForm&id={:id}"}
					          ],
					columns: [{text: myLabel.form_name, type: "text", sortable: true, editable: true, width: 230, editableWidth: 220},
					          {text: myLabel.last_submission, type: "text", sortable: false, editable: false, width: 135, renderer: formatDateTime},
					          {text: myLabel.submissions, type: "text", sortable: true, editable: false, width: 100, renderer: formatSubmissions},
					          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 100,options: [
					                                                                                     {label: myLabel.active, value: "T"}, 
					                                                                                     {label: myLabel.inactive, value: "F"}
					                                                                                     ], applyClass: "status"}],
					dataUrl: "index.php?controller=AdminForms&action=GetForm",
					dataType: "json",
					fields: ['form_title', 'date_time', 'cnt_submissions', 'status'],
					paginator: {
						actions: [
						   {text: myLabel.delete_selected, url: "index.php?controller=AdminForms&action=DeleteFormBulk", render: true, confirmation: myLabel.delete_confirmation},
						   {text: myLabel.revert_status, url: "index.php?controller=AdminForms&action=StatusForm", render: true}
						],
						gotoPage: true,
						paginate: true,
						total: true,
						rowCount: true
					},
					saveUrl: "index.php?controller=AdminForms&action=SaveForm&id={:id}",
					select: {
						field: "id",
						name: "record[]"
					}
				};
			if (Grid.roleId === 2) {
				gridOpts = {
					buttons: [
					          {type: "view", url: "preview.php?id={:id}", target: "_blank"}
					          ],
					columns: [{text: myLabel.form_name, type: "text", sortable: true, editable: false, width: 270, editableWidth: 260},
					          {text: myLabel.last_submission, type: "text", sortable: false, editable: false, width: 135, renderer: formatDateTime},
					          {text: myLabel.submissions, type: "text", sortable: true, editable: false, width: 100, renderer: formatSubmissions},
					          {text: myLabel.status, type: "select", sortable: true, editable: false, width: 100,options: [
					                                                                                     {label: myLabel.active, value: "T"}, 
					                                                                                     {label: myLabel.inactive, value: "F"}
					                                                                                     ], applyClass: "status"}],
					dataUrl: "index.php?controller=AdminForms&action=GetForm",
					dataType: "json",
					fields: ['form_title', 'date_time', 'cnt_submissions', 'status'],
					paginator: {
						gotoPage: true,
						paginate: true,
						total: true,
						rowCount: true
					},
					saveUrl: "index.php?controller=AdminForms&action=SaveForm&id={:id}",
					select: {
						field: "id",
						name: "record[]"
					}
				};
			}
			var $grid = $("#grid").datagrid(gridOpts);
		}
		
		if ($("#confirm_options").length > 0) {
			getConfirmOptions($("#confirm_options"));
		}
		function getConfirmOptions($option)
		{
			if($option.val() == 'message')
			{
				$('.confirm-message').css('display', 'block');
				$('.confirm-redirect').css('display', 'none');
			}else{
				$('.confirm-message').css('display', 'none');
				$('.confirm-redirect').css('display', 'block');
			}
		}
		if ($("#captcha").length > 0) {
			getCaptchaOptions($("#captcha").val());
		}
		function getCaptchaOptions($option)
		{
			if($option == 'T')
			{
				$('.captcha-message').css('display', 'block');
			}else{
				$('.captcha-message').css('display', 'none');
			}
		}
						
		$(document).on("focusin", ".textarea_install", function (e) {
			$(this).select();
		}).on("click", ".btn-all", function (e) {
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
			$grid.datagrid("load", "index.php?controller=AdminForms&action=GetForm", "created", "DESC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminForms&action=GetForm", "created", "DESC", content.page, content.rowCount);
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
			$.post("index.php?controller=AdminForms&action=SetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=AdminForms&action=GetForm");
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
			$grid.datagrid("load", "index.php?controller=AdminForms&action=GetForm", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("change", "#confirm_options", function (e) {
			getConfirmOptions($(this));
		}).on("change", "#captcha", function (e) {
			getCaptchaOptions($(this).val());
		}).on("click", ".element-item", function (e) {
			addField($(this).attr('rev'));
		}).on("click", ".field-edit-icon", function (e) {
			var field_id = $(this).attr('rev');
			$('.field-row').removeClass('focus');
			$('#field_item_' + field_id).addClass('focus');
			editField(field_id);
		}).on("click", ".field-delete-icon", function (e) {
			var field_id = $(this).attr('rev');
			$('#record_id').text(field_id);
			$('#field_type').text($(this).attr('rel'));
			$dialogDeleteField.dialog('open');
		}).on("click", ".cancel-property", function (e) {
			$('#toolbox_panel').css('display', 'block');
			$('#property_panel').css('display', 'none');
			$('.field-row').removeClass('focus');
		}).on("click", ".save-field-property", function (e) {
			var post_data = $('#property_list :input').serialize(),
				form_id = $(this).attr('lang');
			$.ajax({
				type: "POST",
				dataType: 'html',
				data: post_data,
				url: "index.php?controller=AdminForms&action=SaveField&id=" + $('#form_id').text(),
				success: function (res) {
					$('#designer_panel').html(res);
					fireSortable();
					$('#toolbox_panel').css('display', 'block');
					$('#property_panel').css('display', 'none');
				}
			});
		}).on("change", ".field-required", function (e) {
			if($(this).val() == 'T'){
				$('.required-message-container').css('display', 'block');
			}else{
				$('.required-message-container').css('display', 'none');
			}
		}).on("click", ".CfViewSubmission", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
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
		function loadForm(form_id)
		{
			$.ajax({
				type: "GET",
				dataType: 'html',
				url: "index.php?controller=AdminForms&action=LoadForm&id=" + form_id,
				success: function (res) {
					$('#designer_panel').html(res);
					fireSortable();
				}
			});
		}
		
		fireSortable();
		function fireSortable()
		{
			$("#field_list").sortable({
				handle : '.field-move-icon',
			    update : function () {
			    	$.ajax({
						type: "POST",
						dataType: 'json',
						data: $('#field_list').sortable('serialize'),
						url: 'index.php?controller=AdminForms&action=SortFields',
						success: function (res) {
							
						}
					});
			    }
		    });
		}
		
		function addField(type)
		{
			var id = $('#id').val();
			$.ajax({
				type: "GET",
				dataType: 'html',
				url: "index.php?controller=AdminForms&action=AddField&id=" + id + "&type=" + type,
				success: function (res) {
					$('#designer_panel').html(res);
					if(type == 'captcha'){
						$('.captcha-icon').parent().css('display', 'none');
					}					
					fireSortable();
					$('.field-row').each(function(e){
						if($(this).hasClass('focus') == true){
							var ele_id = $(this).attr('id'),
								field_id = ele_id.replace('field_item_', '');
							editField(field_id);
						}
					});
				}
			});
		}
		
		function editField(field_id){
			$.ajax({
				type: "GET",
				dataType: 'html',
				url: "index.php?controller=AdminForms&action=EditField&id=" + field_id,
				success: function (res) {
					$('#property_list').html(res);
					$('#toolbox_panel').css('display', 'none');
					$('#property_panel').css('display', 'block');
					$('html, body').animate({
				        scrollTop: $("#property_panel").offset().top
				    }, 300);
				}
			});
		}
		if ($dialogDeleteField.length > 0 && dialog) {
			$dialogDeleteField.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				width: 450,
				buttons: {
					'Delete': function() {
						$.ajax({
							type: "GET",
							dataType: 'html',
							url: "index.php?controller=AdminForms&action=DeleteField&id=" + $('#form_id').text() + "&field_id=" + $('#record_id').text(),
							success: function (res) {
								$('#designer_panel').html(res);
								fireSortable();
								if($('#field_type').text() == 'captcha'){
									$('.captcha-icon').parent().css('display', 'block');
								}
								$('#property_panel').css('display', 'none');
								$('#toolbox_panel').css('display', 'block');
								$dialogDeleteField.dialog('close');
							}
						});
					},
					'Cancel': function() {
						$dialogDeleteField.dialog('close');
					}
				}
			});
		}
	});
})(jQuery_1_8_2);