(function ($, undefined) {
	$(function () {
		if ($('#frmStep1').length > 0) {

			$.validator.addMethod("version", function (value, element, param) {
				if (value.length !== 1) {
					return false;
				}
				return value === "1";
			}, "The system does not support minimum software requirements");
			
			$('#frmStep1').validate({
				rules: {
					php_version: "version",
					php_session: "version"
				},
				errorClass: "i-error-clean",
				validClass: "i-valid"
			});
			
			var $mysql = $("input[name='mysql_version']");
			if ($mysql.length > 0) {
				$mysql.rules("add", {
					"version": true
				});
			}
		}		
		if ($('#frmStep2').length > 0) {
			
			$.validator.addMethod("prefix", function (value, element, param) {
				if (value.length == 0) {
					return true;
				}
				if (value.length > 30) {
					return false;
				}
				var re = /\.|\/|\\|\s|\W/;
				return !re.test(value)
			}, "Prefix must be no more than 30 characters long and could contain only digits, letters, and '_'");
			
			$('#frmStep2').validate({
				rules: {
					prefix: "prefix"
				},
				errorClass: "i-error",
				validClass: "i-valid",
				submitHandler: function(form) {
					$("input[type='submit'], input[type='button']").prop("disabled", true).addClass("button-disabled");
					form.submit();
				}
			});			
		}
		
		if ($('#frmStep3').length > 0) {
			$('#frmStep3').validate({
				errorClass: "i-error",
				validClass: "i-valid",
				submitHandler: function(form) {
					$("input[type='submit'], input[type='button']").prop("disabled", true).addClass("button-disabled");
					form.submit();
				}				
			});			
		}
		
		if ($('#frmStep4').length > 0) {
			$('#frmStep4').validate({
				rules: {
					admin_email: {
						required: true,
						email: true
					},
					admin_password: "required"
				},
				errorClass: "i-error-clean",
				validClass: "i-valid",
				submitHandler: function(form) {
					$("input[type='submit'], input[type='button']").prop("disabled", true).addClass("button-disabled");
					form.submit();
				}				
			});			
		}
		
		if ($('#frmStep5').length > 0) {
			$('#frmStep5').validate({
				errorClass: "i-error",
				validClass: "i-valid",
				submitHandler: function(form) {
					$("input[type='submit'], input[type='button']").prop("disabled", true).addClass("button-disabled");
					form.submit();
				}				
			});			
		}
		
		if ($('#frmStep6').length > 0) {
			$('#frmStep6').validate({
				errorClass: "i-error",
				validClass: "i-valid",
				submitHandler: function(form) {
					$(".i-status").hide().find("p").html("");
					$("input[type='submit'], input[type='button']").prop("disabled", true).addClass("button-disabled");
					var $ready = $(".i-option");
					$ready.eq(0).addClass("i-option-load");
					$.post("index.php?controller=Installer&action=SetConfig&install=1").done(function (data) {
						if (data.code == 200) { 
							$ready.eq(0).addClass("i-option-ok").removeClass("i-option-load i-option-ready");
							$ready.eq(1).addClass("i-option-load");
							$.post("index.php?controller=Installer&action=SetDb&install=1").done(function (data) {
								if (data.code == 200) {
									$ready.eq(1).addClass("i-option-ok").removeClass("i-option-load i-option-ready");
									form.submit();
								} else {
									$ready.eq(1).addClass("i-option-err").removeClass("i-option-load i-option-ready");
									$("input[type='submit'], input[type='button']").prop("disabled", false).removeClass("button-disabled");
									$(".i-status").find("p").html(data.text).end().show();
								}
							});
						} else {
							$ready.eq(0).addClass("i-option-err").removeClass("i-option-load i-option-ready");
							$("input[type='submit'], input[type='button']").prop("disabled", false).removeClass("button-disabled");
							$(".i-status").find("p").html(data.text).end().show();
						}
					});
				}				
			});			
		}
	});
})(jQuery);