var fdApp = fdApp || {};
var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var button = ($.fn.button !== undefined);
		
		$(".table tbody tr").hover(
			function () {
				$(this).addClass("table-row-hover");
			}, 
			function () {
				$(this).removeClass("table-row-hover");
			}
		);
		$(".button").hover(
			function () {
				$(this).addClass("button-hover");
			}, 
			function () {
				$(this).removeClass("button-hover");
			}
		);
		$(".checkbox").hover(
				function () {
					$(this).addClass("checkbox-hover");
				}, 
				function () {
					$(this).removeClass("checkbox-hover");
				}
			);
		$("#content").on("click", ".notice-close", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).closest(".notice-box").fadeOut();
			return false;
		});
		
		if ($.noty !== undefined) {
			$.noty.defaults = $.extend($.noty.defaults, {
				layout: "bottomRight",
				timeout: 3000
			});
		}
		
		fdApp.enableButtons = function ($dialog) {
			if ($dialog.length > 0 && button) {
				$dialog.siblings(".ui-dialog-buttonpane").find("button").button("enable");
			}
		};
		
		fdApp.disableButtons = function ($dialog) {
			if ($dialog.length > 0 && button) {
				$dialog.siblings(".ui-dialog-buttonpane").find("button").button("disable");
			}
		};
	});
})(jQuery_1_8_2);