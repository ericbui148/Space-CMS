var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateMenu = $("#frmCreateMenu"),
			$frmUpdateMenu = $("#frmUpdateMenu"),
			dialog = ($.fn.dialog !== undefined),
			spinner = ($.fn.spinner !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			$dialogMenuItemAdd = $("#dialogMenuItemAdd"),
			$dialogMenuItemEdit = $("#dialogMenuItemEdit"),
			chosen = ($.fn.chosen !== undefined),
			tipsy = ($.fn.tipsy !== undefined);
		
		if (chosen) {
			if($frmCreateMenu.length > 0 || $frmUpdateMenu.length > 0)
			{
				$("#section_id").chosen();
				$("#parent").chosen();
				$("#category_id").chosen();
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
		if ($frmCreateMenu.length > 0) {
			$frmCreateMenu.validate({
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
		if ($frmUpdateMenu.length > 0) {
			$frmUpdateMenu.validate({
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
		
		
		
		function formatPrice(str, obj) {
			return obj.price_format;
		}
		
		if ($("#grid").length > 0 && datagrid) {
			
			var options = {
				buttons: [{type: "edit", url: "index.php?controller=AdminMenus&action=Update&id={:id}"},
				          {type: "delete", url: "index.php?controller=AdminMenus&action=DeleteMenu&id={:id}"}
				          ],
				columns: [
				          {text: myLabel.name, type: "text", sortable: true, editable: true, width: 150},
				          {text: myLabel.created, type: "text", sortable: true, editable: false, width: 100	},
				          {text: myLabel.modified, type: "text", sortable: true, editable: false, width: 100	},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 120, options: [
									                                                                                     {label: myLabel.active, value: "T"}, 
									                                                                                     {label: myLabel.inactive, value: "F"}
									                                                                                     ], applyClass: "status"}
				        ],
				dataUrl: "index.php?controller=AdminMenus&action=GetMenu",
				dataType: "json",
				fields: ['name','created','modified', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=AdminMenus&action=DeleteMenuBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=AdminMenus&action=SaveMenu&id={:id}",
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
			$grid.datagrid("load", "index.php?controller=AdminMenus&action=GetMenu", "name", "ASC", content.page, content.rowCount);
			return false;
		}).on("change", ".link_type",function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminMenus&action=SelectLinkType", {
				"link_type": $(this).val()
			}).done(function (data) {
				$("#link_type_content").html(data);
				if($("#article_id").length > 0) {
					$("#article_id").chosen();	
				}
				if($("#category_id").length > 0) {
					$("#category_id").chosen();	
				}
				if($("#page_id").length > 0) {
					$("#page_id").chosen();	
				}
				if($("#product_id").length > 0) {
					$("#product_id").chosen();	
				}
				if($("#product_category_id").length > 0) {
					$("#product_category_id").chosen();	
				}
				if($("#gallery_id").length > 0) {
					$("#gallery_id").chosen();	
				}
				if($("#tag_id").length > 0) {
					$("#tag_id").chosen();	
				}
			});
			return false;
		}).on("click", ".btnCreateMenuItem", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if ($dialogMenuItemAdd.length > 0 && dialog) {
				$dialogMenuItemAdd.dialog("open");
			}
			return false;
		}).on("click", ".tree-edit", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if ($dialogMenuItemEdit.length > 0 && dialog) {
				$dialogMenuItemEdit.data("id", $(this).data("id")).dialog("open");
			}
			return false;
		}).on("click", ".item-tree-delete", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if ($dialogItemDelete.length > 0 && dialog) {
				$dialogItemDelete.data("id", $(this).data("id")).dialog("open");
			}
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
			$grid.datagrid("load", "index.php?controller=AdminMenus&action=GetMenu", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=AdminMenus&action=GetMenu", "id", "ASC", content.page, content.rowCount);
			return false;
		});
		
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
				columns: [{text: myLabel.name, type: "text", sortable: false, editable: false, renderer: formatName, width: 500},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatDown, width: 21},
				          {text: "", type: "text", sortable: false, editable: false, renderer: formatUp, width: 21}
				       ],
				dataUrl: "index.php?controller=AdminMenuItems&action=GetMenuItem&menu_id=" + $frmUpdateMenu.find("input[name='menu_id']").val()+ "&rowCount=50",
				dataType: "json",
				fields: ['data', 'data', 'data'],
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
		
		
		$("#grid_menu_item").on("click", ".arrow_up, .arrow_down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=AdminMenuItems&action=SetOrder", {
				"id": $(this).attr("rel"),
				"direction": $(this).attr("rev")
			}).done(function (data) {
				var $grid =  $("#grid_menu_item");
				var content = $grid.datagrid("option", "content");
				$grid.datagrid("load", "index.php?controller=AdminMenuItems&action=GetMenuItem&menu_id="+ $frmUpdateMenu.find("input[name='menu_id']").val(), "id", "ASC", content.page, content.rowCount);
			});
			return false;
		}).on("click", ".table-icon-edit", function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var data_id = $(this).closest('tr').data('id');
			var menu_item_id = data_id.match(/\d+$/)[0];
			if ($dialogMenuItemEdit.length > 0 && dialog) {
				$dialogMenuItemEdit.data("id", menu_item_id).dialog("open");
			}
			return false;
			
		});
		
		if ($dialogMenuItemAdd.length > 0 && dialog) {
			$dialogMenuItemAdd.dialog({
				modal: true,
				resizable: false,
				draggable: false,
				autoOpen: false,
				title: fdApp.locale.button.create_element,
				width: 650,
				open: function () {
					$dialogMenuItemAdd.html("");
					$.get("index.php?controller=AdminMenus&action=AddMenuItem", $frmUpdateMenu.find("input[name='menu_id']").serialize()).done(function (data) {
						$dialogMenuItemAdd.html(data);
						$dialogMenuItemAdd.dialog("option", "position", "center");
						if($('#link_type').length > 0) {
							$("#link_type").chosen();
						}
						if($("#parent_id").length > 0) {
							$("#parent_id").chosen();	
						}
					});
				},
				buttons: (function () {
					var buttons = {};
					buttons[fdApp.locale.button.add] = function () {
						var $this = $(this);
							$.post("index.php?controller=AdminMenuItems&action=Create", $dialogMenuItemAdd.find("form").serialize()).done(function (data) {
								var $grid = $("#grid_menu_item")
								var content = $grid.datagrid("option", "content");
								$grid.datagrid("load", "index.php?controller=AdminMenuItems&action=GetMenuItem&menu_id="+ $frmUpdateMenu.find("input[name='menu_id']").val(), "id", "ASC", content.page, content.rowCount);
							});
						$this.dialog("close");
					};
					buttons[fdApp.locale.button.cancel] = function () {
						$dialogMenuItemAdd.dialog("close");
					};
					
					return buttons;
				})()
			});
		}
		

		if ($dialogMenuItemEdit.length > 0 && dialog) {
			$dialogMenuItemEdit.dialog({
				modal: true,
				resizable: false,
				draggable: false,
				autoOpen: false,
				width: 650,
				title: fdApp.locale.button.edit,
				open: function (booking_service_id) {
					$dialogMenuItemEdit.html("");
					$.post("index.php?controller=AdminMenus&action=EditMenuItem&id="+$dialogMenuItemEdit.data("id"), $frmUpdateMenu.find("input[name='menu_id']").serialize()).done(function (data) {
						$dialogMenuItemEdit.html(data);
						$dialogMenuItemEdit.dialog("option", "position", "center");
						$.post("index.php?controller=AdminMenus&action=SelectLinkType", {
							"link_type": $("#data_link_type").val(),
							'link_data': $("#data_link_data").val()
						}).done(function (data) {
							$("#link_type_content").html(data);
							if($('#link_type').length > 0) {
								$("#link_type").chosen();
							}
							if($("#parent_id").length > 0) {
								$("#parent_id").chosen();	
							}							
							if($("#article_id").length > 0) {
								$("#article_id").chosen();	
							}
							if($("#category_id").length > 0) {
								$("#category_id").chosen();	
							}
							if($("#page_id").length > 0) {
								$("#page_id").chosen();	
							}
							if($("#product_id").length > 0) {
								$("#product_id").chosen();	
							}
							if($("#product_category_id").length > 0) {
								$("#product_category_id").chosen();	
							}
							if($("#gallery_id").length > 0) {
								$("#gallery_id").chosen();	
							}
							if($("#tag_id").length > 0) {
								$("#tag_id").chosen();	
							}
						});
					});
					if($('#link_type').length > 0) {
						$("#link_type").chosen();
					}
					if($("#parent_id").length > 0) {
						$("#parent_id").chosen();	
					}
					if($("#article_id").length > 0) {
						$("#article_id").chosen();	
					}
					if($("#category_id").length > 0) {
						$("#category_id").chosen();	
					}
					if($("#page_id").length > 0) {
						$("#page_id").chosen();	
					}
					if($("#product_id").length > 0) {
						$("#product_id").chosen();	
					}
					if($("#product_category_id").length > 0) {
						$("#product_category_id").chosen();	
					}
					if($("#gallery_id").length > 0) {
						$("#gallery_id").chosen();	
					}

					if($("#tag_id").length > 0) {
						$("#tag_id").chosen();	
					}
				},
				buttons: (function () {
					var buttons = {};
					buttons[fdApp.locale.button.edit] = function () {
					var $this = $(this);
						var formData = new FormData($("#frmUpdateMenuItem")[0]);
						$.ajax({
						    url : "index.php?controller=AdminMenuItems&action=Update",
						    type: "POST",
						    data : formData,
						    processData: false,
						    contentType: false,
						    success:function(data, textStatus, jqXHR){
								if(data.status == 'ERR' && data.code == 250) {
									alert('This element cannot be child of itsself');
								}
								var $grid = $("#grid_menu_item")
								var content = $grid.datagrid("option", "content");
								$grid.datagrid("load", "index.php?controller=AdminMenuItems&action=GetMenuItem&menu_id="+ $frmUpdateMenu.find("input[name='menu_id']").val(), "id", "ASC", content.page, content.rowCount);
								
						    },
						    error: function(jqXHR, textStatus, errorThrown){
						    	alert('There are error when you update data');
						    }
						});
						$this.dialog("close");
					};
					buttons[fdApp.locale.button.cancel] = function () {
						$dialogMenuItemEdit.dialog("close");
					};
					
					return buttons;
				})()
			});
		}
		
		$(document).on("click", ".delete-avatar", function (e) {
			var $dialogDeleteAvatar = $("#dialogDeleteAvatar");
			if ($dialogDeleteAvatar.length > 0 && dialog) {
				
				var buttons = {};
				buttons[myLabel.btn_delete] = function () {
					var $this = $(this);
					$.post("index.php?controller=AdminMenuItems&action=DeleteAvatar", {"menu_item_id": $("#frmUpdateMenuItem").find("input[name='id']").val()}).done(function () {
						$("#box_avatar").html('<input type="file" name="avatar" id="y_avatar" />')
					}).always(function () {
						$this.dialog("close");
					});
				};
				buttons[myLabel.btn_cancel] = function () {
					$(this).dialog("close");
				};
				
				$dialogDeleteAvatar.dialog({
					title: myLabel.delete_logo,
					modal: true,
					autoOpen: false,
					draggable: false,
					resizable: false,
					buttons: buttons
				});
			}			
			if ($dialogDeleteAvatar.length > 0 && dialog) {
				$dialogDeleteAvatar.dialog("open");
				$dialogDeleteAvatar.html(myLabel.delete_image_confirm);
			}
		});
	});
})(jQuery_1_8_2);