(function (window, undefined){
	"use strict";
	Q.$.ajaxSetup({
		xhrFields: {
			withCredentials: true
		}
	});
	var document = window.document,
		validate = (Q.$.fn.validate !== undefined),
		validate = (Q.$.fn.validate !== undefined),
		dialog = (Q.$.fn.dialog !== undefined),
		datepicker = (Q.$.fn.datepicker !== undefined);
	
	function ContactForm(opts) {
		if (!(this instanceof ContactForm)) {
			return new ContactForm(opts);
		}
		this.reset.call(this);
		this.init.call(this, opts);
		return this;
	}
	ContactForm.prototype = {
		reset: function () {
			this.opts = null;
			this.$container = null;
			this.container = null;
			this.$contactform = null;
			this.message_container = null;
			this.$message_container = null;
			this.$loader = null;
			
			return this;
		},
		init: function (opts) {
			var self = this;
			this.opts = opts;
			this.container = document.getElementById("CF_container_" + this.opts.fid);
			this.$container = Q.$(this.container);
			this.$form = Q.$(document.getElementById(this.opts.form));
			this.message_container = document.getElementById("CF_message_container_" + this.opts.fid);
			this.$message_container = Q.$(this.message_container);
			this.$loader = Q.$('#CF_loader_' + this.opts.fid);
			self.bindForm();
			
			this.$container.on("focusin.cf", ".cfDatePicker", function (e) {
				if (datepicker) {
					var $this = Q.$(this),
						dOpts = {
							dateFormat: self.opts.jq_date_format,
							firstDay: self.opts.week_start,
							changeMonth: true,
							changeYear: true,
							yearRange: '1900:' + (new Date().getFullYear() + 10)
						};
					$this.datepicker(Q.$.extend(dOpts));
					$this.datepicker('show');
					Q.$('#ui-datepicker-div').addClass('CF-ui-jquery');
				}
			}).on("click.cf", ".cfFormFieldIconAfter", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var $dp = Q.$(this).siblings("input[type='text']");
				$dp.trigger("focusin.cf").datepicker("show");
				Q.$('#ui-datepicker-div').addClass('CF-ui-jquery');
			})
		},
		checkLinks: function(){
			var self = this,
				$form = Q.$(self.opts.form),
				valid = true;
			if(self.opts.is_reject == true){
				Q.$(".CF-checked-field").each(function( index ) {
				     var content = Q.$(this).val(),
				     	 reg = /(\b(?:(https?|ftp):\/\/)?((?:www\d{0,3}\.)?([a-z0-9.-]+\.(?:[a-z]{2,4}|museum|travel)(?:\/[^\/\s]+)*))\b)/;
				     if(content.match(reg) != null){
				    	 valid = false;
				     }
				});
			}
			return valid;
		},
		checkBlockWords: function(){
			var self = this,
				$form = Q.$(self.opts.form),
				banned_word_str = self.opts.banned_words,
				valid = true;
			Q.$(".CF-checked-field").each(function( index ) {
			     var content = Q.$(this).val();
			     if(banned_word_str != ""){
			    	 banned_word_str = banned_word_str.replace(/\s+/g, '');
			    	 banned_word_str = banned_word_str.replace(new RegExp(",","g"),"|");
			    	 if(new RegExp(banned_word_str).test(content) == true){
			    		 valid = false;
			    	 }
			     }
			});
			return valid;
		},
		bindForm: function(){
			var self = this;
			if (validate) {
				
				Q.$.validator.addMethod('filesize', function(value, element, param) {
				    return this.optional(element) || (element.files[0].size <= param) 
				});
				Q.$.validator.addMethod('alphabetic', function(value, element) {
					return this.optional(element) || /^[a-z]+$/i.test(value);
				});
				Q.$.validator.addMethod('alphanumeric', function(value, element) {
					return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
				});
				self.$container.find("form").validate({
					rules: self.opts.field_rules,
					messages: self.opts.field_messages,
					onkeyup: false,
					errorElement: 'li',
					errorPlacement: function (error, element) {
						if(element.attr('name') == 'captcha')
						{
							error.appendTo(element.parent().parent().next().find('ul'));
						}else{
							if(element.parent().parent().hasClass('radio') || element.parent().parent().hasClass('checkbox'))
							{
								error.appendTo(element.parent().parent().parent().find('ul'));
							}else{
								if(element.hasClass('cfDatePicker'))
								{
									error.appendTo(element.parent().next().find('ul'));
								}else{
									error.appendTo(element.next().find('ul'));
								}
							}
						}
					},
		            highlight: function(ele, errorClass, validClass) {
		            	var element = Q.$(ele);
		            	if(element.attr('name') == 'captcha')
						{
							element.parent().parent().parent().parent().addClass('has-error');
						}else{
							if(element.parent().parent().hasClass('radio') || element.parent().parent().hasClass('checkbox'))
							{
								element.parent().parent().parent().parent().addClass('has-error');
							}else{
								if(element.hasClass('cfDatePicker'))
								{
									element.parent().parent().parent().addClass('has-error');
								}else{
									element.parent().parent().addClass('has-error');
								}
							}
						}
		            },
		            unhighlight: function(ele, errorClass, validClass) {
		            	var element = Q.$(ele);
		            	if(element.attr('name') == 'captcha')
						{
							element.parent().parent().parent().parent().removeClass('has-error').addClass('has-success');
						}else{
							if(element.parent().parent().hasClass('radio') || element.parent().parent().hasClass('checkbox'))
							{
								element.parent().parent().parent().parent().removeClass('has-error').addClass('has-success');
							}else{
								if(element.hasClass('cfDatePicker'))
								{
									element.parent().parent().parent().removeClass('has-error').addClass('has-success');
								}else{
									element.parent().parent().removeClass('has-error').addClass('has-success');
								}
							}
						}
		            },
					submitHandler: function (form) {
						Q.$('.CF-button').attr("disabled", "disabled");
						self.$message_container.removeClass('alert-success').removeClass('alert-danger');
						if(self.checkLinks() == true && self.checkBlockWords() == true){
							self.$form.ajaxSubmit({
								success: function(data) {
									if(data == '100'){
										if(self.opts.confirm_option == 'message'){
											self.$message_container.html(self.opts.confirm_message);
											if(Q.$('#CF_captcha_img').length > 0){
												var rand = Math.floor((Math.random()*999999)+1); 
												Q.$('#CF_captcha_img').attr("src", self.opts.folder + 'index.php?controller=Front&action=Captcha&id=' + self.opts.fid + "&rand=" + rand);
											}
										}else{
											self.$message_container.html(self.opts.error_message.redirect);
											window.location.href = self.opts.thankyou_page;
										}
										self.$message_container.addClass('alert-success');
										self.$form.resetForm();
										self.$form.find('.form-group').removeClass('has-success');
									}else if(data=='200'){
										self.$message_container.addClass('alert-danger');
										self.$message_container.html(self.opts.error_message.word);
									}else if(data=='201'){
										self.$message_container.addClass('alert-danger');
										self.$message_container.html(self.opts.field_messages.captcha.remote);
									}else if(data=='202'){
										self.$message_container.addClass('alert-danger');
										self.$message_container.html(self.opts.error_message.url);
									}
									Q.$('.CF-button').removeAttr("disabled");
								}
							});
						}else if(self.checkBlockWords() == false){
							self.$message_container.addClass('alert-danger');
							self.$message_container.html(self.opts.error_message.word);
							Q.$('.CF-button').removeAttr("disabled");
						}else if(self.checkLinks() == false){
							self.$message_container.addClass('alert-danger');
							self.$message_container.html(self.opts.error_message.url);
							Q.$('.CF-button').removeAttr("disabled");
						}
						return false;
					}
				});
			}
			self.$form.resetForm();
			self.$loader.css('display', 'none');
			self.$container.css('display', 'block');
		}
	};
	
	// expose
	window.ContactForm = ContactForm;
})(window);