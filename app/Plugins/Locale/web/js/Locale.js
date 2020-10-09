var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $tabs = $("#tabs"),
			tabs = ($.fn.tabs !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			dialog = ($.fn.dialog !== undefined),
			$frmUpdateShowID = $("#frmUpdateShowID"),
			$dialogShowID = $("#dialogShowID");
		
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs({
				select: function (event, ui) {
					switch (ui.index) {
						case 0:
							window.location.href = 'index.php?controller=AdminForumOptions&action=Index&tab=0';
							break;
					}
				}
			});
		}
		
		$(".plugin_locale_wrapper_top").scroll(function(){
	        $(".plugin_locale_wrapper_bottom")
	            .scrollLeft($(".plugin_locale_wrapper_top").scrollLeft());
	    });
	    $(".plugin_locale_wrapper_bottom").scroll(function(){
	        $(".plugin_locale_wrapper_top")
	            .scrollLeft($(".plugin_locale_wrapper_bottom").scrollLeft());
	    });
		
	    if ($dialogShowID.length > 0 && dialog) 
		{
	    	$dialogShowID.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				width: 400,
				buttons: (function () {
					var buttons = {};
					buttons[myLabel.btnConfirm] = function () {
						$frmUpdateShowID.submit();
					};
					buttons[myLabel.btnCancel] = function () {
						$('#show_id').attr('checked', false);
						$dialogShowID.dialog("close");
					};
					return buttons;
				})()
			});
		}
	    
	    if($frmUpdateShowID.length > 0)
	    {
	    	$('.show-id-save').click(function(e){
	    		if($("#show_id").is(':checked'))
	    		{
	    			$dialogShowID.dialog('open');
	    		}else{
	    			$frmUpdateShowID.submit();
	    		}
	    	});
	    }
	    
		$("#content").on("change", "input[name='toggle']", function (e) {
			var $this = $(this),
				$tbody = $this.closest("table").find("tbody");
			if ($this.is(":checked")) {
				$tbody.find("input[name='field_id[]']").attr("checked", "checked");
			} else {
				$tbody.find("input[name='field_id[]']").removeAttr("checked");
			}
		}).on("change", "select[name='row_count']", function () {
			var h = window.location.href,
				m = h.match(/row_count=\d+/),
				row_count = $(this).find("option:selected").val();
			if (m !== null) {
				window.location.href = h.replace(/row_count=\d+/, 'row_count=' + row_count);
			} else {
				window.location.href += h.indexOf('?') !== -1 ? '&row_count=' + row_count : '?row_count=' + row_count;
			}
		}).on("click", ".table-sort-up, .table-sort-down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var h = window.location.href,
				pattern = /column=\w+&direction=(ASC|DESC)/,
				m = h.match(pattern),
				$this = $(this),
				column = $this.data("column"),
				direction = $this.hasClass("table-sort-up") ? "ASC" : "DESC";

			if (m !== null) {
				window.location.href = h.replace(pattern, 'column=' + column + '&direction=' + direction);
			} else {
				window.location.href += h.indexOf('?') !== -1 ? '&column=' + column + '&direction=' + direction : '?column=' + column + '&direction=' + direction;
			}
			return false;
		});
				
		if ($("#grid").length > 0 && datagrid) {
			
			function formatImage (str) {
				return (str && str.length > 0) ? '<img alt="" src="core/framework/libs/wp/img/flags/' + str + '" />' : '';
			}
			
			function formatDefault (str) {
				return '<a href="#" class="status-icon status-' + str + '" style="cursor: ' +  (parseInt(str, 10) === 0 ? 'pointer' : 'default') + '"></a>';
			}
			
			function onBeforeShow(obj) {
				if (parseInt(obj.is_default, 10) === 1) {
					return false;
				}
				return true;
			}

			var $grid = $("#grid").datagrid({
				buttons: [{type: "delete", url: "index.php?controller=Locale&action=DeleteLocale&id={:id}", beforeShow: onBeforeShow}],
				columns: [{text: myLabel.title, type: "select", sortable: true, editable: true, width: 480, options: Grid.languages},
				          {text: myLabel.flag, type: "text", sortable: false, editable: false, width: 40, renderer: formatImage, align: "center"},
				          {text: myLabel.is_default, type: "text", sortable: true, editable: false, width: 80, renderer: formatDefault, align: "center"},
				          {text: myLabel.order, type: "text", sortable: true, editable: false, align: "center", width: 55, css: {
				        	  cursor: "move"
				          }}],
				dataUrl: "index.php?controller=Locale&action=GetLocale",
				dataType: "json",
				fields: ['language_iso', 'file', 'is_default', 'sort'],
				paginator: false,
				saveUrl: "index.php?controller=Locale&action=SaveLocale&id={:id}",
				sortable: true,
				sortableUrl: "index.php?controller=Locale&action=SortLocale"
			});
			
			$(document).on("click", ".btn-add", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				$.post("index.php?controller=Locale&action=SaveLocale").done(function (data) {
					$grid.datagrid("option", "onRender", function () {
						var $td = $("tr[data-id='id_" + data.id + "']").find(".table-cell-editable").filter(":first");
						$td.trigger("click");
						$td.find("select option:not([disabled])").first().attr("selected", "selected");
						$grid.datagrid("option", "onRender", null);
					});
					$grid.datagrid("load", "index.php?controller=Locale&action=GetLocale");
				});
				return false;
			}).on("focus", "select[data-name='language_iso']", function () {
				var $this = $(this), values = [];
				$this.closest("tbody").find("select[data-name='language_iso']").not(this).each(function (i) {
					values.push($(this).find("option:selected").val());
				});
				$(this).find("option").removeAttr("disabled").filter(function (index) {
					return $.inArray(this.value, values) != -1;
				}).attr("disabled", "disabled");
			}).on("click", ".status-1", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				return false;
			}).on("click", ".status-0", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				$.post("index.php?controller=Locale&action=SaveDefault", {
					id: $(this).closest("tr").data("object")['id']
				}).done(function (data) {
					$grid.datagrid("load", "index.php?controller=Locale&action=GetLocale");
				});
				return false;
			});
		}
	});
})(jQuery_1_8_2);