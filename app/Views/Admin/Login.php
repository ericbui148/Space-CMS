<div class="login-box">
	
	<h3><?php __('adminLogin'); ?></h3>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Login" method="post" id="frmLoginAdmin" class="form">
		<input type="hidden" name="login_user" value="1" />
		<p>
			<label class="title"><?php __('email'); ?>:</label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
				<input type="text" name="login_email" id="login_email" class="form-field required email w250" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('pass'); ?>:</label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-password"></abbr></span>
				<input type="password" name="login_password" id="login_password" class="form-field required w250" autocomplete="off" />
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<button class="button"><i class="fa fa-sign-in" aria-hidden="true"></i>
			 <?php __('btnLogin', false, true); ?></button>
			<a class="no-decor l10" href="<?php echo $controller->baseUrl(); ?>index.php?controller=Admin&action=Forgot"><?php __('lblForgot'); ?></a>
		</p>
		<?php
		if (isset($_GET['err']))
		{
			$err = __('login_err', true);
			switch ($_GET['err'])
			{
				case 1:
					?><em><label class="err" style="display: inline;"><?php echo $err[1]; ?></label></em><?php
					break;
				case 2:
					?><em><label class="err" style="display: inline;"><?php echo $err[2]; ?></label></em><?php
					break;
				case 3:
					?><em><label class="err" style="display: inline;"><?php echo $err[3]; ?></label></em><?php
					break;
			}
		}
		?>
	</form>
</div>