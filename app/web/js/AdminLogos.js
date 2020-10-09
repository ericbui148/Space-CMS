var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	"use strict";
	$(function () {
		var dialog = ($.fn.dialog !== undefined),
		$dialogDeleteLogo = $("#dialogDeleteLogo");
		
		$("#content").on("click", ".delete_logo", function () {
			if ($dialogDeleteLogo.length > 0 && dialog) {
				$dialogDeleteLogo.dialog("open");
				$dialogDeleteLogo.html(myLabel.delete_image_confirm);
			}
		});
		
		if ($dialogDeleteLogo.length > 0 && dialog) {
			
			var buttons = {};
			buttons[myLabel.btn_delete] = function () {
				var $this = $(this);
				$.post("index.php?controller=AdminLogos&action=DeleteLogo").done(function () {
					$("#box_logo").html('<input type="file" name="logo" id="y_logo" />')
				}).always(function () {
					$this.dialog("close");
				});
			};
			buttons[myLabel.btn_cancel] = function () {
				$(this).dialog("close");
			};
			
			$dialogDeleteLogo.dialog({
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