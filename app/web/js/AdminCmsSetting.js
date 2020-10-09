var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	"use strict";
	$(function () {
		var dialog = ($.fn.dialog !== undefined),
		$dialogDeleteFavicon = $("#dialogDeleteFavicon");
		
		$("#content").on("click", ".delete-favicon", function () {
			if ($dialogDeleteFavicon.length > 0 && dialog) {
				$dialogDeleteFavicon.dialog("open");
				$dialogDeleteFavicon.html(myLabel.delete_image_confirm);
			}
		}).on("click", "#is_maintain", function () {
			var checked = $(this).attr('checked');
			if (checked == 'checked') {
				$(this).val('T');
				$("#maintain_url").show();
			} else {
				$(this).val('F');
				$("#maintain_url").hide();
			}
		});
		
		if ($dialogDeleteFavicon.length > 0 && dialog) {
			
			var buttons = {};
			buttons[myLabel.btn_delete] = function () {
				var $this = $(this);
				$.post("index.php?controller=AdminCmsSettings&action=DeleteFavicon").done(function () {
					$("#box_favicon").html('<input type="file" name="favicon" id="y_favicon" />')
				}).always(function () {
					$this.dialog("close");
				});
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			
			$dialogDeleteFavicon.dialog({
				title: myLabel.delete_logo,
				modal: true,
				autoOpen: false,
				draggable: false,
				resizable: false,
				buttons: buttons
			});
		}
	});
})(jQuery_1_8_2);