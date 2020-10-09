&lt;div class="row"&gt;
	&lt;div class="col-sm-6 col-xs-12"&gt;
		&lt;input type="text" name="captcha" maxlength="6" class="form-control CF-form-field required" /&gt;
	&lt;/div&gt;
	&lt;div class="col-sm-6 col-xs-12"&gt;
		&lt;img id="CF_captcha_img" src="<?php echo BASE_URL; ?>index.php?controller=Front&amp;action=Captcha&amp;id=<?php echo $tpl['arr']['id']?>&amp;rand=<?php echo rand(1, 999999); ?>" alt="Captcha" /&gt;
	&lt;/div&gt;
&lt;/div&gt;