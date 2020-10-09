var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmLoginAdmin = $("#frmLoginAdmin"),
			$frmForgotAdmin = $("#frmForgotAdmin"),
			$frmUpdateProfile = $("#frmUpdateProfile"),
			validate = ($.fn.validate !== undefined);
		
		if ($frmLoginAdmin.length > 0 && validate) {
			$frmLoginAdmin.validate({
				rules: {
					login_email: {
						required: true,
						email: true
					},
					login_password: "required"
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		
		if ($frmForgotAdmin.length > 0 && validate) {
			$frmForgotAdmin.validate({
				rules: {
					forgot_email: {
						required: true,
						email: true
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
		
		if ($frmUpdateProfile.length > 0 && validate) {
			$frmUpdateProfile.validate({
				rules: {
					"email": {
						required: true,
						email: true
					},
					"password": "required",
					"name": "required"
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		
		var dialog = ($.fn.dialog !== undefined),
		$dialogDeleteAvatar = $("#dialogDeleteAvatar");
		
		$("#content").on("click", ".delete-avatar", function () {
			if ($dialogDeleteAvatar.length > 0 && dialog) {
				$dialogDeleteAvatar.dialog("open");
				$dialogDeleteAvatar.html(myLabel.delete_image_confirm);
			}
		});
		
		if ($dialogDeleteAvatar.length > 0 && dialog) {
			
			var buttons = {};
			buttons[myLabel.btn_delete] = function () {
				var $this = $(this);
				$.post("index.php?controller=Admin&action=DeleteAvatar").done(function () {
					location.reload();
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
	});
})(jQuery_1_8_2);