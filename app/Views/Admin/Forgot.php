<div class="login-box">
	
	<h3><?php __('adminForgot'); ?></h3>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Forgot" method="post" id="frmForgotAdmin" class="form">
		<input type="hidden" name="forgot_user" value="1" />
		<p>
			<label class="title"><?php __('email'); ?>:</label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
				<input type="text" name="forgot_email" id="forgot_email" class="form-field required email w250" />
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSend', false, true); ?>" class="button" />
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Login" class="no-decor l10"><?php __('lnkBack'); ?></a>
		</p>
		<?php
		if (isset($_GET['err']))
		{
			$titles = __('error_titles', true);
			$bodies = __('error_bodies', true);
			?>
			<em><label class="err"><?php echo @$titles[$_GET['err']]; ?><br><?php echo @$bodies[$_GET['err']]; ?></label></em>
			<?php
		}
		?>
	</form>
</div>