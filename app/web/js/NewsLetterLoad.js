(function (window, undefined) {
	var d = window.document;
	JABB.Ajax.xhrFields = {
		withCredentials: true
	};
	function stivaNSL(options) {
		if (!(this instanceof stivaNSL)) {
			return new stivaNSL(options);
		}
		this.options = {};
		this.error_container = null;
		this.message_container = null;
		this.day = null;
		this.month = null;
		this.year = null;
		this.group_id = null;
		this.btnSubmit = null
		this.init(options);
		return this;
	}
	
	stivaNSL.prototype = {
		resetForm: function()
		{
			var self = this,
				frm = d.forms[self.options.subscribe_form_name],
				first_name = frm.first_name,
				last_name = frm.last_name,
				email = frm.email,
				phone = frm.phone,
				website = frm.website,
				gender = frm.gender,
				age = frm.age,
				day = frm.day,
				month = frm.month,
				year = frm.year,
				address = frm.address,
				city = frm.city,
				state = frm.state,
				country = frm.country_id,
				zip = frm.zip,
				company_name = frm.company_name,
				captcha = frm.captcha;
			
			captcha.value = '';
			if(first_name)
			{
				first_name.value = '';
			}
			if(last_name)
			{
				last_name.value = '';
			}
			if(email)
			{
				email.value = '';
			}
			if(phone)
			{
				phone.value = '';
			}
			if(website)
			{
				website.value = '';
			}
			if(gender)
			{
				gender.value = '';
			}
			if(age)
			{
				age.value = '';
			}
			if(day)
			{
				day.value = '';
				month.value = '';
				year.value = '';
				self.day = null;
				self.month = null;
				self.year = null;
			}
			if(address)
			{
				address.value = '';
			}
			if(city)
			{
				city.value = '';
			}
			if(state)
			{
				state.value = '';
			}
			if(country)
			{
				country.value = '';
			}
			if(zip)
			{
				zip.value = '';
			}
			if(company_name)
			{
				company_name.value = '';
			}
		},
		submitForm: function(post)
		{
			var self = this;
			self.error_container.style.display = "none";
			self.message_container.innerHTML = '<label class="message nsl-l120 info">' + self.options.message.info + '</label>';
			JABB.Ajax.sendRequest(self.options.subscribe_url, function (req) {
				var code = req.responseText;
				if(code == '100')
				{	
					self.message_container.innerHTML = '<label class="message nsl-l120 success">' + self.options.message.success + '</label>';
					self.loadCaptcha();
					self.resetForm();
				}else if(code == '101'){
					self.message_container.innerHTML = '<label class="message nsl-l120 error">' + self.options.message.error + '</label>';
				}else{
					self.message_container.innerHTML = '<label class="message nsl-l120 error">' + self.options.validate[code] + '</label>';
				}
				self.btnSubmit.disabled = false;
			}, post);
			self.message_container.style.display = "block";
		},
		checkCaptcha: function()
		{
			var self = this,
				frm = d.forms[self.options.subscribe_form_name],
				captcha = frm.captcha.value;
			
			JABB.Ajax.sendRequest(self.options.check_captcha_url + "&captcha=" + captcha + "&group_id=" + self.group_id, function (req) {
				var code = req.responseText;
				if(code == '100')
				{
					self.submitForm(JABB.Utils.serialize(frm));
				}else{
					self.error_container.innerHTML = '<ul><li>' + self.options.validation.error_captcha_incorrect + '</li></ul>';
					self.error_container.style.display = "block";
					self.btnSubmit.disabled = false;
				}
			});
		},
		checkEmail: function()
		{
			var self = this,
				frm = d.forms[self.options.subscribe_form_name],
				email = frm.email.value,
				captcha = frm.captcha.value;
			
			JABB.Ajax.sendRequest(self.options.check_email_url + "&email=" + email, function (req) {
				var code = req.responseText;
				if(code == '100')
				{
					self.checkCaptcha();
				}else{
					self.error_container.innerHTML = '<ul><li>' + self.options.validation.error_email_used + '</li></ul>';
					self.error_container.style.display = "block";
					self.btnSubmit.disabled = false;
				}
			});
		},
		disableForm: function()
		{
			var self = this,
				frm = d.forms[self.options.subscribe_form_name];
			for (var i = 0, len = frm.elements.length; i < len; i++) 
			{
				frm.elements[i].disabled = true;
			}
		},
		enableForm: function()
		{
			var self = this,
				frm = d.forms[self.options.subscribe_form_name];
			for (var i = 0, len = frm.elements.length; i < len; i++) 
			{
				frm.elements[i].disabled = false;
			}
		},
		validateForm: function()
		{
			var self = this,
				re = /([0-9a-zA-Z\.\-\_]+)@([0-9a-zA-Z\.\-\_]+)\.([0-9a-zA-Z\.\-\_]+)/,
				message = "",
				frm = d.forms[self.options.subscribe_form_name],
				day = frm.day,
				month = frm.month,
				year = frm.year;
			
			for (var i = 0, len = frm.elements.length; i < len; i++) 
			{
				var cls = frm.elements[i].className;
				if (cls.indexOf("required") !== -1 && frm.elements[i].disabled === false) {
					switch (frm.elements[i].nodeName) {
					case "INPUT":
						switch (frm.elements[i].type) {
						case "checkbox":
						case "radio":
							if (!frm.elements[i].checked && frm.elements[i].getAttribute("lang")) {
								message += "<li>" + frm.elements[i].getAttribute("lang") + "</li>"; 
							}
							break;
						default:
							if (frm.elements[i].value.length === 0 && frm.elements[i].getAttribute("lang")) {
								message += "<li>" + frm.elements[i].getAttribute("lang") + "</li>";
							}else{
								if(frm.elements[i].getAttribute("name") == 'birthday')
								{
									if (self.year == null || self.month == null && self.day == null)
									{
										message += '<li>' + self.options.validation.error_birthday_invalid + '</li>';
									}
								}
							}
							break;
						}
						break;
					case "TEXTAREA":
						if (frm.elements[i].value.length === 0 && frm.elements[i].getAttribute("lang")) {						
							message += "<li>" + frm.elements[i].getAttribute("lang") + "</li>";
						}
						break;
					case "SELECT":
						switch (frm.elements[i].type) {
						case 'select-one':
							if (frm.elements[i].value.length === 0 && frm.elements[i].getAttribute("lang")) {
								message += "<li>" + frm.elements[i].getAttribute("lang") + "</li>"; 
							}
							break;
						case 'select-multiple':
							var has = false;
							for (j = frm.elements[i].options.length - 1; j >= 0; j = j - 1) {
								if (frm.elements[i].options[j].selected) {
									has = true;
									break;
								}
							}
							if (!has && frm.elements[i].getAttribute("lang")) {
								message += "<li>" + frm.elements[i].getAttribute("lang") + "</li>";
							}
							break;
						}
						break;
					default:
						break;
					}
				}
				if (cls.indexOf("email") !== -1) {
					if (frm.elements[i].nodeName === "INPUT" && frm.elements[i].value.length > 0 && frm.elements[i].value.match(re) == null) {
						message += "<li>" + self.options.validation.error_email_invalid + "</li>";
					}
				}
			}
			if (message != '') {
				self.error_container.innerHTML = '<ul>' + message + '</ul>';
				self.error_container.style.display = "block";
				self.btnSubmit.disabled = false;
				
			}else{
				self.checkEmail();
			}
		},
		bindForm: function()
		{
			var self = this,
				frm = d.forms[self.options.subscribe_form_name],
				day = frm.day,
				month = frm.month,
				year = frm.year,
				error_container = JABB.Utils.getElementsByClass("nsl-error-container", d.forms[self.options.subscribe_form_name], "DIV"),
				message_container = JABB.Utils.getElementsByClass("nsl-message-container", d.forms[self.options.subscribe_form_name], "DIV");
			
			self.error_container = error_container[0];
			self.message_container = message_container[0];
			
			if (d.forms[self.options.subscribe_form_name] && d.forms[self.options.subscribe_form_name][self.options.subscribe_form_subscribe_name]) {
				JABB.Utils.addEvent(d.forms[self.options.subscribe_form_name][self.options.subscribe_form_subscribe_name], "click", function (event) {
					this.disabled = true;
					self.btnSubmit = this;
					self.validateForm();
				});
			}
			
			var date_arr = JABB.Utils.getElementsByClass("nsl-birthday", d.forms[self.options.subscribe_form_name], "SELECT");
			for (i = 0, len = date_arr.length; i < len; i++) {
				date_arr[i].onchange = function () {
					if(this.getAttribute("name") == 'day')
					{
						self.day = this.value;
					}
					if(this.getAttribute("name") == 'month')
					{
						self.month = this.value;
					}
					if(this.getAttribute("name") == 'year')
					{
						self.year = this.value;
					}
					d.forms[self.options.subscribe_form_name]['birthday'].value = self.year + '-' + self.month + '-' + self.day;
				};
			}
			if (day && day.value != '')
			{
				self.day = day.value;
			}
			if (month && month.value != '')
			{
				self.month = month.value;
			}
			if (year && year.value != '')
			{
				self.year = year.value;
			}
		},
		loadFields: function(group_id_ele)
		{
			var self = this;
			self.disableForm();
			JABB.Ajax.sendRequest(self.options.load_field_url + "&index="+self.options.index+ (group_id_ele.value != '' ? "&group_id=" + group_id_ele.value : ''), function (req) {
				var field_container = d.getElementById('nsl_form_field_' + self.options.index);
				field_container.innerHTML = req.responseText;
				group_id_ele.disabled = false;
				self.enableForm();
				self.bindForm();
			});
		},
		loadCaptcha: function()
		{
			var self = this;
			captcha_img = d.getElementById('nsl_captcha_image_' + self.options.index);
			captcha_img.src = self.options.load_captcha_url + Math.floor(Math.random() * (999999 - 1 + 1)) + "&group_id=" + self.group_id ;
		},
		init: function (stivaObj) {
			var self = this,
				frm = d.getElementById('nsl_subscribe_form_' + stivaObj.index);
			
			self.options = stivaObj;
			self.group_id = self.options.group_id;
			if (frm)
			{
				if (d.forms[self.options.subscribe_form_name] && d.forms[self.options.subscribe_form_name]["group_id"]) {
					var group_id_ele = d.forms[self.options.subscribe_form_name]["group_id"];
					JABB.Utils.addEvent(group_id_ele, "change", function (event) {
						self.group_id = group_id_ele.value;
						self.loadFields(group_id_ele);
					});
				}
				self.bindForm();
			}
		}
	}
	return (window.stivaNSL = stivaNSL);
})(window);