<?php
include dirname(__FILE__) . '/elements/progress.php';
$STORAGE = &$_SESSION[$controller->defaultInstaller];
?>
<div class="i-wrap">
	<?php
	if (isset($_GET['err']) && !empty($_GET['err']) && isset($_SESSION[$controller->defaultErrors][$_GET['err']]))
	{
		?>
		<div class="i-status i-status-error">
			<div class="i-status-icon"><abbr></abbr></div>
			<div class="i-status-txt">
				<h2>Installation error!</h2>
				<p class="t10"><?php echo @$_SESSION[$controller->defaultErrors][$_GET['err']]; ?></p>
			</div>
		</div>
		<?php
	}
	?>
	
	<p>Enter login details for product administration page. Once product is installed and you log in the administration page you will be able to change these details.</p>
	
	<form action="index.php?controller=Installer&amp;action=Step5&amp;install=1" method="post" id="frmStep4" class="i-form">
		<input type="hidden" name="step4" value="1" />

		<table cellpadding="0" cellspacing="0" class="i-table t20">
		  <thead>
				<tr>
					<th>Administrator Login</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<p>
							<label class="i-title"><span class="i-red">*</span> E-Mail</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
								<input type="text" tabindex="1" name="admin_email" id="admin_email" class="form-field w200" value="<?php echo isset($STORAGE['admin_email']) ? htmlspecialchars($STORAGE['admin_email']) : NULL; ?>" />
							</span>
						</p>
						<p>
							<label class="i-title"><span class="i-red">*</span> Password</label>
							<span class="form-field-custom form-field-custom-before">
								<span class="form-field-before"><abbr class="form-field-icon-password"></abbr></span>
								<input type="text" tabindex="2" name="admin_password" id="admin_password" class="form-field w200" value="<?php echo isset($STORAGE['admin_password']) ? htmlspecialchars($STORAGE['admin_password']) : NULL; ?>" />
							</span>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		
		<div class="t20">
			<p class="float_left pt5">Need help? <a href="http://www.phabbers.com/contact.php" target="_blank">Contact us</a></p>
			<input type="submit" tabindex="3" value="Continue &raquo;" class="button float_right l5" />
			<input type="button" tabindex="4" value="&laquo; Back" class="button float_right" onclick="window.location='index.php?controller=Installer&amp;action=Step3'" />
			<br class="clear_both" />
		</div>
	</form>
</div>